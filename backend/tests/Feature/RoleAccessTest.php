<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function register(string $email, string $role = 'user'): array
    {
        $response = $this->postJson('/api/register', ['name' => 'Khách', 'email' => $email, 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'admin'])->assertCreated();
        $user = User::findOrFail($response->json('user.id'));
        $this->assertSame('user', $user->role);
        $user->role = $role;
        $user->save();

        return [$response->json('token'), $user];
    }

    public function test_roles_are_enforced_for_product_and_user_management(): void
    {
        [$userToken] = $this->register('user@example.com');
        [$managerToken] = $this->register('manager@example.com', 'manager');
        [$adminToken, $admin] = $this->register('admin@example.com', 'admin');
        $this->getJson('/api/management/products')->assertUnauthorized();
        $this->withToken($userToken)->getJson('/api/management/products')->assertForbidden();
        $this->withToken($userToken)->getJson('/api/management/users')->assertForbidden();
        $this->withToken($managerToken)->getJson('/api/management/products')->assertOk();
        $this->withToken($managerToken)->getJson('/api/management/users')->assertForbidden();
        $this->withToken($adminToken)->getJson('/api/management/users')->assertOk();
        $this->withToken($adminToken)->getJson('/api/management/products')->assertOk();
        $this->withToken($adminToken)->putJson('/api/management/users/'.$admin->id, ['role' => 'user', 'is_active' => true])->assertUnprocessable();
    }

    public function test_manager_can_manage_products_but_not_roles(): void
    {
        [$managerToken] = $this->register('manager@example.com', 'manager');
        [$userToken, $user] = $this->register('user@example.com');
        $product = ['name' => 'Gà ri', 'category' => 'Gà tươi', 'description' => 'Sản phẩm', 'price' => 100000, 'wholesale_price' => 90000, 'unit' => 'kg', 'image' => 'https://example.com/ga.jpg', 'is_active' => true];
        $created = $this->withToken($managerToken)->postJson('/api/management/products', $product)->assertCreated();
        $id = $created->json('id');
        $this->withToken($userToken)->putJson('/api/management/products/'.$id, $product)->assertForbidden();
        $this->withToken($managerToken)->putJson('/api/management/products/'.$id, array_merge($product, ['price' => 120000]))->assertOk()->assertJsonPath('price', 120000);
        $this->withToken($managerToken)->deleteJson('/api/management/products/'.$id)->assertOk();
        $this->getJson('/api/products')->assertJsonMissing(['id' => $id]);
        $this->withToken($managerToken)->putJson('/api/management/users/'.$user->id, ['role' => 'admin', 'is_active' => true])->assertForbidden();
    }

    public function test_admin_can_change_and_disable_accounts_and_revokes_access(): void
    {
        [$adminToken] = $this->register('admin@example.com', 'admin');
        [$userToken, $user] = $this->register('user@example.com');
        $this->withToken($adminToken)->putJson('/api/management/users/'.$user->id, ['role' => 'manager', 'is_active' => true])->assertOk();
        $this->withToken($userToken)->getJson('/api/management/products')->assertOk();
        $this->withToken($adminToken)->putJson('/api/management/users/'.$user->id, ['role' => 'manager', 'is_active' => false])->assertOk();
        $this->withToken($userToken)->getJson('/api/me')->assertUnauthorized();
        $this->postJson('/api/login', ['email' => 'user@example.com', 'password' => 'password123'])->assertUnprocessable();
    }

    public function test_admin_can_be_assigned_to_an_existing_account_from_the_command_line(): void
    {
        [, $user] = $this->register('owner@example.com');
        $this->artisan('app:assign-admin', ['email' => 'OWNER@example.com'])->assertSuccessful();
        $this->assertSame('admin', $user->refresh()->role);
        $this->artisan('app:assign-admin', ['email' => 'missing@example.com'])->assertFailed();
    }
}
