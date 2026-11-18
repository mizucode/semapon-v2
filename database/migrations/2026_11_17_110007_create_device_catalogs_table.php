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
        Schema::create('device_catalogs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('brand');
            $table->string('model_name');
            $table->string('series')->nullable();
            $table->string('chipset')->nullable();
            $table->text('specs_summary')->nullable();
            $table->json('default_colors')->nullable();
            $table->json('default_variants')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->foreignUlid('store_id')->nullable()->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['brand', 'model_name']);
            $table->index(['is_verified', 'store_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_catalogs');
    }
};
