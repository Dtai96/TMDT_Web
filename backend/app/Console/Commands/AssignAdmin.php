<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:assign-admin {email : Email tài khoản đã đăng ký}')]
#[Description('Cấp quyền admin cho tài khoản hiện có')]
class AssignAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = mb_strtolower(trim($this->argument('email')));
        $lookup = hash_hmac('sha256', $email, config('app.key'));
        $user = User::where('email_lookup', $lookup)->first();
        if (! $user) {
            $this->error('Không tìm thấy tài khoản. Hãy đăng ký tài khoản trước.');

            return self::FAILURE;
        }
        $user->role = 'admin';
        $user->is_active = true;
        $user->save();
        $this->info('Đã cấp quyền admin. Hãy đăng nhập lại để cập nhật giao diện.');

        return self::SUCCESS;
    }
}
