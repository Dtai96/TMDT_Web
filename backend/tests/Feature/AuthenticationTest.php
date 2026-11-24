<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_login_and_logout_with_encrypted_user_information(): void
    {
        $input = ['name' => 'Nguyễn An', 'email' => 'AN@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'];
        $response = $this->postJson('/api/register', $input)->assertCreated()->assertJsonStructure(['token', 'user' => ['name', 'email']]);
        $user = User::firstOrFail();
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotSame('Nguyễn An', DB::table('users')->value('name'));
        $this->assertNotSame('an@example.com', DB::table('users')->value('email'));
        $this->postJson('/api/register', $input)->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/api/login', ['email' => 'an@example.com', 'password' => 'wrong'])->assertUnprocessable();
        $this->postJson('/api/login', ['email' => 'an@example.com', 'password' => 'password123'])->assertOk()->assertJsonPath('user.name', 'Nguyễn An');
        $token = $response->json('token');
        $this->withToken($token)->getJson('/api/me')->assertOk()->assertJsonPath('user.email', 'an@example.com');
        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_checkout_requires_a_valid_token(): void
    {
        $this->postJson('/api/checkout', [])->assertUnauthorized();
        $this->withToken('invalid')->postJson('/api/checkout', [])->assertUnauthorized();
    }

    public function test_registration_requires_matching_password(): void
    {
        $this->postJson('/api/register', ['name' => 'An', 'email' => 'an@example.com', 'password' => 'password123', 'password_confirmation' => 'different'])->assertUnprocessable()->assertJsonValidationErrors('password');
    }
}
