<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create a new purchase order with line items.
     *
     * @param  array<int, array{product_id: string, quantity: int, unit_cost: float}>  $items
     */
    public function createPurchase(
        Supplier $supplier,
        array $items,
        float $downPayment = 0,
        ?string $userId = null,
        ?string $notes = null
    ): Purchase {
        if (empty($items)) {
            throw new Exception('Pembelian harus memiliki minimal 1 item barang.');
        }

        return DB::transaction(function () use ($supplier, $items, $downPayment, $userId, $notes) {
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_cost'];
            }

            $paymentStatus = 'unpaid';
            if ($downPayment >= $totalAmount && $totalAmount > 0) {
                $paymentStatus = 'paid';
            } elseif ($downPayment > 0) {
                $paymentStatus = 'partially_paid';
            }

            $purchase = Purchase::create([
                'store_id' => $supplier->store_id,
                'supplier_id' => $supplier->id,
                'user_id' => $userId,
                'purchase_date' => now()->toDateString(),
                'total_amount' => $totalAmount,
                'paid_amount' => $downPayment,
                'payment_status' => $paymentStatus,
                'status' => 'pending',
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['unit_cost'];
                $purchase->items()->create([
                    'store_id' => $supplier->store_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);
            }

            if ($downPayment > 0) {
                $purchase->payments()->create([
                    'store_id' => $supplier->store_id,
                    'supplier_id' => $supplier->id,
                    'user_id' => $userId,
                    'amount' => $downPayment,
                    'payment_date' => now()->toDateString(),
                    'payment_method' => 'transfer',
                    'notes' => 'Uang muka (DP) pembelian',
                ]);
            }

            return $purchase;
        });
    }

    /**
     * Confirm receipt of goods from supplier — increments stock atomically.
     */
    public function receivePurchase(Purchase $purchase, ?string $userId = null): void
    {
        if ($purchase->status === 'received') {
            throw new Exception('Pembelian ini sudah pernah diterima sebelumnya.');
        }

        if ($purchase->status === 'cancelled') {
            throw new Exception('Pembelian yang sudah dibatalkan tidak dapat diterima.');
        }

        DB::transaction(function () use ($purchase, $userId) {
            foreach ($purchase->items as $item) {
                $product = Product::withoutGlobalScopes()->findOrFail($item->product_id);
                $this->inventoryService->recordStockIn(
                    $product,
                    $item->quantity,
                    (float) $item->unit_cost,
                    $userId,
                    "Penerimaan PO #{$purchase->purchase_number}",
                    'purchase',
                    $purchase->id
                );
            }

            $purchase->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
        });
    }

    /**
     * Record payment to supplier for this purchase.
     */
    public function recordSupplierPayment(
        Purchase $purchase,
        float $amount,
        string $method = 'transfer',
        ?string $userId = null,
        ?string $refNumber = null,
        ?string $notes = null
    ): SupplierPayment {
        if ($amount <= 0) {
            throw new Exception('Jumlah pembayaran harus lebih dari 0.');
        }

        if ($purchase->remaining_debt <= 0) {
            throw new Exception('Tagihan pembelian ini sudah lunas.');
        }

        return DB::transaction(function () use ($purchase, $amount, $method, $userId, $refNumber, $notes) {
            $payment = $purchase->payments()->create([
                'store_id' => $purchase->store_id,
                'supplier_id' => $purchase->supplier_id,
                'user_id' => $userId,
                'amount' => $amount,
                'payment_date' => now()->toDateString(),
                'payment_method' => $method,
                'reference_number' => $refNumber,
                'notes' => $notes ?: 'Pembayaran tagihan pembelian',
            ]);

            $newPaid = (float) $purchase->paid_amount + $amount;
            $purchase->paid_amount = $newPaid;

            if ($newPaid >= (float) $purchase->total_amount) {
                $purchase->payment_status = 'paid';
            } else {
                $purchase->payment_status = 'partially_paid';
            }

            $purchase->save();

            return $payment;
        });
    }

    /**
     * Cancel purchase order with automatic stock reversal if goods were received.
     */
    public function cancelPurchase(Purchase $purchase, ?string $userId = null): void
    {
        if ($purchase->status === 'cancelled') {
            throw new Exception('Pembelian ini sudah dibatalkan.');
        }

        DB::transaction(function () use ($purchase, $userId) {
            // If already received, reverse stock by deducting it
            if ($purchase->status === 'received') {
                foreach ($purchase->items as $item) {
                    $product = Product::withoutGlobalScopes()->findOrFail($item->product_id);
                    $this->inventoryService->recordStockOut(
                        $product,
                        $item->quantity,
                        $userId,
                        "Reversal pembatalan PO #{$purchase->purchase_number}",
                        'purchase_cancel',
                        $purchase->id
                    );
                }
            }

            $purchase->update([
                'status' => 'cancelled',
            ]);
        });
    }
}
