<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_tracking_tokens', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique()->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->string('last_accessed_ip', 45)->nullable();
            $table->timestamps();

            $table->index(['store_id', 'service_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_tracking_tokens');
    }
};
