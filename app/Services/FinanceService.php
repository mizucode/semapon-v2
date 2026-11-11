<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\ServiceOrder;
use App\Models\Store;
use Exception;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Record payment for a service order with atomic cash ledger entry.
     */
    public function recordOrderPayment(
        ServiceOrder $order,
        float $amount,
        string $method = 'cash',
        ?string $userId = null,
        ?string $refNumber = null,
        ?string $notes = null
    ): Payment {
        if ($amount <= 0) {
            throw new Exception('Nominal pembayaran harus lebih dari 0.');
        }

        if ($order->status === 'cancelled') {
            throw new Exception('Tidak dapat menerima pembayaran untuk order yang dibatalkan.');
        }

        return DB::transaction(function () use ($order, $amount, $method, $userId, $refNumber, $notes) {
            $paymentType = 'installment';
            $newPaidTotal = (float) $order->paid_amount + $amount;

            if ($order->paid_amount == 0 && $newPaidTotal >= (float) $order->total_cost && $order->total_cost > 0) {
                $paymentType = 'full_payment';
            } elseif ($order->paid_amount == 0) {
                $paymentType = 'dp';
            } elseif ($newPaidTotal >= (float) $order->total_cost) {
                $paymentType = 'final_payment';
            }

            $payment = Payment::create([
                'store_id' => $order->store_id,
                'service_order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'user_id' => $userId,
                'amount' => $amount,
                'type' => $paymentType,
                'payment_method' => $method,
                'reference_number' => $refNumber,
                'status' => 'success',
                'notes' => $notes ?: "Pembayaran Order #{$order->order_number}",
            ]);

            // Update order paid amount and payment status
            $order->paid_amount = $newPaidTotal;
            if ($newPaidTotal >= (float) $order->total_cost && $order->total_cost > 0) {
                $order->payment_status = 'paid';
            } else {
                $order->payment_status = 'partially_paid';
            }
            $order->save();

            // Record cash in
            CashTransaction::create([
                'store_id' => $order->store_id,
                'user_id' => $userId,
                'type' => 'in',
                'amount' => $amount,
                'category' => 'service_income',
                'reference_type' => 'payment',
                'reference_id' => $payment->id,
                'description' => "Penerimaan servis #{$order->order_number} ({$method})",
            ]);

            return $payment;
        });
    }

    /**
     * Void / cancel a payment record and adjust the order balance.
     */
    public function voidPayment(Payment $payment, ?string $userId = null, ?string $reason = null): void
    {
        if ($payment->status !== 'success') {
            throw new Exception('Pembayaran ini sudah tidak aktif / sudah dibatalkan.');
        }

        DB::transaction(function () use ($payment, $userId, $reason) {
            $payment->update([
                'status' => 'void',
                'notes' => ($payment->notes ? $payment->notes.' | ' : '')."Void: {$reason}",
            ]);

            if ($payment->service_order_id) {
                $order = ServiceOrder::withoutGlobalScopes()->findOrFail($payment->service_order_id);
                $newPaid = max(0, (float) $order->paid_amount - (float) $payment->amount);
                $order->paid_amount = $newPaid;

                if ($newPaid == 0) {
                    $order->payment_status = 'unpaid';
                } elseif ($newPaid < (float) $order->total_cost) {
                    $order->payment_status = 'partially_paid';
                } else {
                    $order->payment_status = 'paid';
                }

                $order->save();
            }

            // Record cash ledger reversal
            CashTransaction::create([
                'store_id' => $payment->store_id,
                'user_id' => $userId,
                'type' => 'out',
                'amount' => (float) $payment->amount,
                'category' => 'refund',
                'reference_type' => 'payment_void',
                'reference_id' => $payment->id,
                'description' => "Pembatalan pembayaran #{$payment->payment_number} ({$reason})",
            ]);
        });
    }

    /**
     * Record operational expense with automatic cash ledger deduction.
     */
    public function recordExpense(
        Store $store,
        float $amount,
        ?string $categoryId = null,
        string $method = 'cash',
        ?string $userId = null,
        ?string $notes = null
    ): Expense {
        if ($amount <= 0) {
            throw new Exception('Nominal pengeluaran harus lebih dari 0.');
        }

        return DB::transaction(function () use ($store, $amount, $categoryId, $method, $userId, $notes) {
            $expense = Expense::create([
                'store_id' => $store->id,
                'category_id' => $categoryId,
                'user_id' => $userId,
                'expense_date' => now()->toDateString(),
                'amount' => $amount,
                'payment_method' => $method,
                'notes' => $notes,
            ]);

            CashTransaction::create([
                'store_id' => $store->id,
                'user_id' => $userId,
                'type' => 'out',
                'amount' => $amount,
                'category' => 'expense',
                'reference_type' => 'expense',
                'reference_id' => $expense->id,
                'description' => 'Pengeluaran operasional: '.($notes ?: 'Biaya toko'),
            ]);

            return $expense;
        });
    }

    /**
     * Get real-time financial overview and dashboard analytics.
     */
    public function getFinancialSummary(Store $store, ?string $startDate = null, ?string $endDate = null): array
    {
        $cashInQuery = CashTransaction::where('type', 'in');
        $cashOutQuery = CashTransaction::where('type', 'out');

        if ($startDate) {
            $cashInQuery->whereDate('created_at', '>=', $startDate);
            $cashOutQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $cashInQuery->whereDate('created_at', '<=', $endDate);
            $cashOutQuery->whereDate('created_at', '<=', $endDate);
        }

        $totalCashIn = (float) $cashInQuery->sum('amount');
        $totalCashOut = (float) $cashOutQuery->sum('amount');
        $cashBalance = $totalCashIn - $totalCashOut;

        // Total Receivables (Piutang Servis belum lunas)
        $totalReceivables = (float) ServiceOrder::whereNotIn('status', ['cancelled', 'unrepairable'])
            ->selectRaw('SUM(total_cost - paid_amount) as remaining')
            ->value('remaining') ?? 0;

        // Total Supplier Debt (Hutang Kulakan)
        $totalSupplierDebt = (float) Purchase::where('status', '!=', 'cancelled')
            ->selectRaw('SUM(total_amount - paid_amount) as debt')
            ->value('debt') ?? 0;

        return [
            'total_income' => $totalCashIn,
            'total_expense' => $totalCashOut,
            'net_profit' => $cashBalance,
            'total_receivables' => max(0, $totalReceivables),
            'total_supplier_debt' => max(0, $totalSupplierDebt),
        ];
    }
}
