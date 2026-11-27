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
            $table->unsignedInteger('discount_amount')->default(0);
            $table->string('discount_label')->nullable();
            $table->boolean('free_shipping_eligible')->default(false);
            $table->unsignedInteger('points_awarded')->default(0);
            $table->timestamp('delivered_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_label', 'free_shipping_eligible', 'points_awarded', 'delivered_at']);
        });
    }
};
