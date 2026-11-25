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
            $table->unsignedInteger('shipping_fee')->nullable()->change();
            $table->unsignedInteger('total')->nullable()->change();
        });
        Schema::table('wholesale_leads', function (Blueprint $table) {
            $table->string('reference', 32)->nullable()->unique();
            $table->string('status', 32)->default('Mới tiếp nhận');
            $table->text('quote_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('Không thể tự động khôi phục schema cũ khi phí giao chưa được xác nhận.');
    }
};
