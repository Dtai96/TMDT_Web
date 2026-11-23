<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wholesale_leads', function (Blueprint $table) {
            $table->id(); $table->text('business_name'); $table->text('contact_name'); $table->text('phone');
            $table->text('email')->nullable(); $table->string('monthly_volume'); $table->text('message')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('wholesale_leads'); }
};
