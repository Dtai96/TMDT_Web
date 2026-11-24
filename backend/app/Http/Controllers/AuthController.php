<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function lookup(string $email): string
    {
        return hash_hmac('sha256', mb_strtolower(trim($email)), config('app.key'));
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:160'], 'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed']]);
        $email = mb_strtolower(trim($data['email']));
        $lookup = $this->lookup($email);
        if (User::where('email_lookup', $lookup)->exists()) {
            throw ValidationException::withMessages(['email' => 'Email này đã được đăng ký.']);
        }
        $user = User::create(['name' => $data['name'], 'email' => $email, 'email_lookup' => $lookup, 'password' => $data['password']]);

        return $this->issueToken($user->refresh(), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:160'], 'password' => ['required', 'string', 'max:255']]);
        $user = User::where('email_lookup', $this->lookup($data['email']))->first();
        if (! $user || ! $user->is_active || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Email hoặc mật khẩu không chính xác.']);
        }

        return $this->issueToken($user);
    }

    private function issueToken(User $user, int $status = 200): JsonResponse
    {
        $token = bin2hex(random_bytes(32));
        DB::table('api_tokens')->insert(['user_id' => $user->id, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDays(30), 'created_at' => now(), 'updated_at' => now()]);

        return response()->json(['user' => $user, 'token' => $token], $status);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        DB::table('api_tokens')->where('token_hash', hash('sha256', $request->bearerToken()))->delete();

        return response()->json(['message' => 'Đã đăng xuất.']);
    }
}
