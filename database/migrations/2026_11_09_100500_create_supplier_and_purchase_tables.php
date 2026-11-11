<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['store_id', 'is_active']);
        });

        // Purchases / Kulakan
        Schema::create('purchases', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('purchase_number');
            $table->date('purchase_date');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->enum('status', ['pending', 'received', 'cancelled'])->default('pending');
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'purchase_number']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'payment_status']);
        });

        // Purchase Items
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index(['store_id', 'purchase_id']);
            $table->index(['store_id', 'product_id']);
        });

        // Supplier Payments (Cicilan / Pelunasan Hutang Kulakan)
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignUlid('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method')->default('transfer');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'purchase_id']);
            $table->index(['store_id', 'supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
    }
};
