<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'name']);
        });

        // Expenses (Operasional Toko, Listrik, Sewa, Gaji, dll)
        Schema::create('expenses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('expense_number');
            $table->date('expense_date');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'expense_number']);
            $table->index(['store_id', 'expense_date']);
        });

        // Payments (Penerimaan uang servis, DP, pelunasan pelanggan)
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->foreignUlid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_number');
            $table->decimal('amount', 12, 2);
            $table->string('type')->default('service_payment'); // dp, installment, full_payment, refund
            $table->enum('payment_method', ['cash', 'transfer', 'qris', 'debit'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->enum('status', ['success', 'void', 'refunded'])->default('success');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'payment_number']);
            $table->index(['store_id', 'service_order_id']);
            $table->index(['store_id', 'status']);
        });

        // Cash Ledger (Buku Kas & Mutasi Saldo)
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 12, 2);
            $table->string('category'); // service_income, expense, supplier_payment, manual_in, manual_out
            $table->string('reference_type')->nullable(); // payment, expense, supplier_payment
            $table->string('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
