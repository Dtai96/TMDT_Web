<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $orderColumns = ['customer_name', 'phone', 'email', 'address', 'note'];
    private array $leadColumns = ['business_name', 'contact_name', 'phone', 'email', 'message'];

    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('customer_name')->change();
            $table->text('phone')->change();
            $table->text('email')->nullable()->change();
            $table->text('address')->change();
            $table->text('note')->nullable()->change();
        });

        Schema::table('wholesale_leads', function (Blueprint $table) {
            $table->text('business_name')->change();
            $table->text('contact_name')->change();
            $table->text('phone')->change();
            $table->text('email')->nullable()->change();
            $table->text('message')->nullable()->change();
        });

        $this->transformValues('orders', $this->orderColumns, fn (string $value) => Crypt::encryptString($value));
        $this->transformValues('wholesale_leads', $this->leadColumns, fn (string $value) => Crypt::encryptString($value));
    }

    public function down(): void
    {
        $this->transformValues('orders', $this->orderColumns, fn (string $value) => Crypt::decryptString($value));
        $this->transformValues('wholesale_leads', $this->leadColumns, fn (string $value) => Crypt::decryptString($value));

        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name', 120)->change();
            $table->string('phone', 20)->change();
            $table->string('email', 160)->nullable()->change();
            $table->string('address', 255)->change();
            $table->text('note')->nullable()->change();
        });

        Schema::table('wholesale_leads', function (Blueprint $table) {
            $table->string('business_name', 160)->change();
            $table->string('contact_name', 120)->change();
            $table->string('phone', 20)->change();
            $table->string('email', 160)->nullable()->change();
            $table->text('message')->nullable()->change();
        });
    }

    private function transformValues(string $table, array $columns, callable $transform): void
    {
        DB::table($table)->select(array_merge(['id'], $columns))->orderBy('id')->chunkById(100, function ($rows) use ($table, $columns, $transform) {
            foreach ($rows as $row) {
                $values = [];
                foreach ($columns as $column) {
                    if ($row->{$column} !== null) {
                        $values[$column] = $transform($row->{$column});
                    }
                }

                if ($values !== []) {
                    DB::table($table)->where('id', $row->id)->update($values);
                }
            }
        });
    }
};
