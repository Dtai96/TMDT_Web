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
        Schema::table('orders', function (Blueprint $table) {
            $table->text('desired_time')->nullable();
            $table->text('referral_source')->nullable();
        });
        Schema::table('wholesale_leads', function (Blueprint $table) {
            $table->text('delivery_location')->nullable();
            $table->text('purchase_frequency')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['desired_time', 'referral_source']));
        Schema::table('wholesale_leads', fn (Blueprint $table) => $table->dropColumn(['delivery_location', 'purchase_frequency']));
    }
};
