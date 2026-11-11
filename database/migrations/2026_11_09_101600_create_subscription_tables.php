<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SaaS Plans
        Schema::create('plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_yearly', 12, 2)->default(0);
            $table->integer('max_technicians')->default(1);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Store Subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'expired'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
        });

        // Subscription Invoices & Payments
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignUlid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->enum('payment_method', ['transfer', 'qris', 'manual_proof'])->default('transfer');
            $table->enum('status', ['pending', 'paid', 'failed', 'rejected'])->default('pending');
            $table->string('proof_image')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
