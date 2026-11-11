<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Product Categories
        Schema::create('product_categories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'slug']);
        });

        // Products (Spareparts, Accessories, Units)
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->enum('type', ['sparepart', 'accessory', 'unit', 'other'])->default('sparepart');
            $table->decimal('buy_price', 12, 2)->default(0);
            $table->decimal('sell_price', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('unit')->default('pcs');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['store_id', 'sku']);
            $table->index(['store_id', 'barcode']);
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'is_active']);
        });

        // Stock Movements Audit Trail
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment', 'service_usage', 'service_return']);
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'product_id']);
            $table->index(['store_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Service Order Parts Usage Pivot
        Schema::create('service_order_parts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('sell_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['store_id', 'service_order_id']);
            $table->index(['store_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_parts');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
