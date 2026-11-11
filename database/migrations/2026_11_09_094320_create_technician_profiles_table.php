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
        Schema::create('technician_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('membership_id')->constrained('store_memberships')->cascadeOnDelete();
            $table->string('specialization', 100)->nullable(); // Hardware, Software, LCD, IC Mesin, All-Round
            $table->string('commission_type', 30)->default('percentage'); // percentage, fixed, profit_split
            $table->decimal('commission_rate', 14, 2)->default(0); // e.g. 30.00 (%) or nominal Rp50.000
            $table->text('notes')->nullable();
            $table->unsignedInteger('total_jobs_completed')->default(0);
            $table->decimal('total_commission_earned', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['store_id', 'user_id']);
            $table->index(['store_id', 'specialization']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technician_profiles');
    }
};
