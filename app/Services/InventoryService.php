<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderPart;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Record stock-in (penerimaan barang baru / pembelian).
     */
    public function recordStockIn(
        Product $product,
        int $quantity,
        float $unitCost = 0,
        ?string $userId = null,
        ?string $notes = null,
        ?string $refType = null,
        ?string $refId = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new Exception('Jumlah penambahan stok harus lebih dari 0.');
        }

        return DB::transaction(function () use ($product, $quantity, $unitCost, $userId, $notes, $refType, $refId) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($product->id);

            $stockBefore = $lockedProduct->stock;
            $stockAfter = $stockBefore + $quantity;

            $lockedProduct->stock = $stockAfter;
            if ($unitCost > 0) {
                $lockedProduct->buy_price = $unitCost;
            }
            $lockedProduct->save();

            return StockMovement::create([
                'store_id' => $lockedProduct->store_id,
                'product_id' => $lockedProduct->id,
                'user_id' => $userId,
                'type' => 'in',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'unit_cost' => $unitCost > 0 ? $unitCost : (float) $lockedProduct->buy_price,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'notes' => $notes ?: 'Penerimaan stok masuk',
            ]);
        });
    }

    /**
     * Record stock-out (barang rusak, hilang, atau keluar manual).
     */
    public function recordStockOut(
        Product $product,
        int $quantity,
        ?string $userId = null,
        ?string $notes = null,
        ?string $refType = null,
        ?string $refId = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new Exception('Jumlah pengurangan stok harus lebih dari 0.');
        }

        return DB::transaction(function () use ($product, $quantity, $userId, $notes, $refType, $refId) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($product->id);

            if ($lockedProduct->stock < $quantity) {
                throw new Exception("Stok tidak mencukupi. Sisa stok: {$lockedProduct->stock} {$lockedProduct->unit}.");
            }

            $stockBefore = $lockedProduct->stock;
            $stockAfter = $stockBefore - $quantity;

            $lockedProduct->stock = $stockAfter;
            $lockedProduct->save();

            return StockMovement::create([
                'store_id' => $lockedProduct->store_id,
                'product_id' => $lockedProduct->id,
                'user_id' => $userId,
                'type' => 'out',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'unit_cost' => (float) $lockedProduct->buy_price,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'notes' => $notes ?: 'Pengurangan stok manual',
            ]);
        });
    }

    /**
     * Record stock adjustment (penyesuaian / opname fisik).
     */
    public function adjustStock(
        Product $product,
        int $newStock,
        ?string $userId = null,
        ?string $notes = null
    ): StockMovement {
        if ($newStock < 0) {
            throw new Exception('Jumlah stok fisik tidak boleh negatif.');
        }

        return DB::transaction(function () use ($product, $newStock, $userId, $notes) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($product->id);

            $stockBefore = $lockedProduct->stock;
            $diff = $newStock - $stockBefore;

            $lockedProduct->stock = $newStock;
            $lockedProduct->save();

            return StockMovement::create([
                'store_id' => $lockedProduct->store_id,
                'product_id' => $lockedProduct->id,
                'user_id' => $userId,
                'type' => 'adjustment',
                'quantity' => abs($diff),
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'unit_cost' => (float) $lockedProduct->buy_price,
                'reference_type' => 'stock_opname',
                'reference_id' => null,
                'notes' => $notes ?: "Penyesuaian stok opname dari {$stockBefore} ke {$newStock}",
            ]);
        });
    }

    /**
     * Deduct part from stock for a service order with atomic transaction.
     */
    public function usePartInOrder(
        ServiceOrder $order,
        Product $product,
        int $quantity = 1,
        ?string $userId = null
    ): ServiceOrderPart {
        if ($quantity <= 0) {
            throw new Exception('Jumlah sparepart harus minimal 1.');
        }

        return DB::transaction(function () use ($order, $product, $quantity, $userId) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($product->id);

            if ($lockedProduct->stock < $quantity) {
                throw new Exception("Stok sparepart '{$lockedProduct->name}' tidak mencukupi (Tersedia: {$lockedProduct->stock}).");
            }

            $stockBefore = $lockedProduct->stock;
            $stockAfter = $stockBefore - $quantity;

            $lockedProduct->stock = $stockAfter;
            $lockedProduct->save();

            // Record Movement
            StockMovement::create([
                'store_id' => $lockedProduct->store_id,
                'product_id' => $lockedProduct->id,
                'user_id' => $userId,
                'type' => 'service_usage',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'unit_cost' => (float) $lockedProduct->buy_price,
                'reference_type' => 'service_order',
                'reference_id' => $order->id,
                'notes' => "Penggunaan sparepart untuk Order #{$order->order_number}",
            ]);

            $costPrice = (float) $lockedProduct->buy_price;
            $sellPrice = (float) $lockedProduct->sell_price;
            $subtotal = $sellPrice * $quantity;

            // Record order part usage
            $orderPart = ServiceOrderPart::create([
                'store_id' => $order->store_id,
                'service_order_id' => $order->id,
                'product_id' => $lockedProduct->id,
                'quantity' => $quantity,
                'cost_price' => $costPrice,
                'sell_price' => $sellPrice,
                'subtotal' => $subtotal,
            ]);

            // Also append item into service_order_items for invoice billing
            $order->items()->create([
                'store_id' => $order->store_id,
                'service_id' => null,
                'name' => "{$lockedProduct->name} ({$quantity} {$lockedProduct->unit})",
                'price' => $subtotal,
                'technician_id' => $order->technician_id,
            ]);

            $newTotal = $order->items()->sum('price');
            $order->total_cost = $newTotal;

            if ($order->paid_amount >= $newTotal && $newTotal > 0) {
                $order->payment_status = 'paid';
            } elseif ($order->paid_amount > 0) {
                $order->payment_status = 'partially_paid';
            } else {
                $order->payment_status = 'unpaid';
            }

            $order->save();

            return $orderPart;
        });
    }

    /**
     * Reversal / return of part from service order back to inventory.
     */
    public function returnPartFromOrder(ServiceOrderPart $part, ?string $userId = null): void
    {
        DB::transaction(function () use ($part, $userId) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($part->product_id);

            $order = ServiceOrder::withoutGlobalScopes()->findOrFail($part->service_order_id);

            $stockBefore = $lockedProduct->stock;
            $stockAfter = $stockBefore + $part->quantity;

            $lockedProduct->stock = $stockAfter;
            $lockedProduct->save();

            // Record Reversal Movement
            StockMovement::create([
                'store_id' => $lockedProduct->store_id,
                'product_id' => $lockedProduct->id,
                'user_id' => $userId,
                'type' => 'service_return',
                'quantity' => $part->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'unit_cost' => (float) $part->cost_price,
                'reference_type' => 'service_order',
                'reference_id' => $order->id,
                'notes' => "Pengembalian sparepart dari Order #{$order->order_number}",
            ]);

            $part->delete();
        });
    }
}
