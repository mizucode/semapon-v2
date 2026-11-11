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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUlid('customer_device_id')->constrained('customer_devices')->cascadeOnDelete();
            $table->string('order_number', 50);
            $table->string('tracking_token', 64)->unique();
            $table->string('status', 30)->default('received')->index();
            // received, checking, waiting_approval, waiting_parts, in_progress, testing, completed, ready_for_pickup, picked_up, cancelled, warranty_claim

            $table->text('complaints'); // keluhan pelanggan
            $table->json('physical_condition')->nullable(); // checklist kondisi fisik awal
            $table->string('accessories_included')->nullable(); // charger, dus, case, sim card
            $table->string('passcode', 100)->nullable(); // PIN / pola layar

            $table->decimal('estimated_cost', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->decimal('down_payment', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->string('payment_status', 20)->default('unpaid')->index(); // unpaid, partially_paid, paid

            $table->foreignUlid('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamp('estimated_completion_at')->nullable();
            $table->timestamp('customer_approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('warranty_ends_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'order_number']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'technician_id']);
            $table->index(['store_id', 'created_at']);
        });

        Schema::create('service_order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignUlid('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('name');
            $table->decimal('price', 14, 2)->default(0);
            $table->foreignUlid('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['store_id', 'service_order_id']);
        });

        Schema::create('service_order_diagnoses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignUlid('technician_id')->constrained('users')->cascadeOnDelete();
            $table->text('diagnosis_notes');
            $table->json('testing_checklist')->nullable(); // LCD, Touch, Mic, Speaker, Camera, Sinyal, Baterai, Charger
            $table->text('action_taken')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'service_order_id']);
        });

        Schema::create('service_order_histories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'service_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_order_histories');
        Schema::dropIfExists('service_order_diagnoses');
        Schema::dropIfExists('service_order_items');
        Schema::dropIfExists('service_orders');
    }
};
