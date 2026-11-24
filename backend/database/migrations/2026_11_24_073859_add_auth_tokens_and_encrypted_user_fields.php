<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->text('name')->change();
            $table->text('email')->change();
            $table->string('email_lookup', 64)->nullable()->unique();
        });
        DB::table('users')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'name' => Crypt::encryptString($user->name),
                    'email' => Crypt::encryptString($user->email),
                    'email_lookup' => hash_hmac('sha256', mb_strtolower(trim($user->email)), config('app.key')),
                ]);
            }
        });
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_id'));
        Schema::dropIfExists('api_tokens');
        DB::table('users')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update(['name' => Crypt::decryptString($user->name), 'email' => Crypt::decryptString($user->email)]);
            }
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email_lookup']);
            $table->dropColumn('email_lookup');
            $table->string('name')->change();
            $table->string('email')->change();
            $table->unique('email');
        });
    }
};
