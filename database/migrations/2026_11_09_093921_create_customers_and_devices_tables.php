<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 25)->index();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('total_orders_count')->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['store_id', 'phone']);
            $table->index(['store_id', 'name']);
        });

        Schema::create('customer_devices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('brand', 60);
            $table->string('model', 100);
            $table->string('color', 50)->nullable();
            $table->string('imei', 30)->nullable()->index();
            $table->string('serial_number', 50)->nullable();
            $table->string('passcode', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'customer_id']);
            $table->index(['store_id', 'brand']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_devices');
        Schema::dropIfExists('customers');
    }
};
