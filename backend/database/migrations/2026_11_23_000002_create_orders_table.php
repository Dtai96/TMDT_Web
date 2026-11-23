<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->text('customer_name'); $table->text('phone');
            $table->text('email')->nullable(); $table->text('address'); $table->text('note')->nullable();
            $table->string('channel', 8); $table->unsignedInteger('subtotal'); $table->unsignedInteger('shipping_fee');
            $table->unsignedInteger('total'); $table->string('status')->default('Chờ xác nhận'); $table->string('payment_method');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
