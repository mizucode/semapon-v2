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
        Schema::table('customer_devices', function (Blueprint $table) {
            $table->string('variant', 60)->nullable()->after('color');
            $table->text('specs')->nullable()->after('variant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_devices', function (Blueprint $table) {
            $table->dropColumn(['variant', 'specs']);
        });
    }
};
