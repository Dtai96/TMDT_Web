<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WholesaleLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_only_admin_can_review_b2b_requests_and_save_an_encrypted_quote(): void
    {
        [$userToken] = $this->register('user@example.com');
        [$managerToken] = $this->register('manager@example.com', 'manager');
        [$adminToken] = $this->register('admin@example.com', 'admin');
        $input = ['business_name' => 'Bếp ăn', 'contact_name' => 'An', 'phone' => '0900000000', 'monthly_volume' => '50 kg', 'message' => 'Gà tươi', 'delivery_location' => 'Đắk Lắk', 'purchase_frequency' => 'Hàng tuần'];
        $response = $this->postJson('/api/wholesale', $input)->assertCreated()->assertJsonStructure(['reference']);
        $lead = WholesaleLead::where('reference', $response->json('reference'))->firstOrFail();
        $this->getJson('/api/management/wholesale')->assertUnauthorized();
        $this->withToken($userToken)->getJson('/api/management/wholesale')->assertForbidden();
        $this->withToken($managerToken)->getJson('/api/management/wholesale')->assertForbidden();
        $this->withToken($adminToken)->getJson('/api/management/wholesale')->assertOk()->assertJsonFragment(['reference' => $lead->reference]);
        $this->withToken($adminToken)->putJson('/api/management/wholesale/'.$lead->id, ['status' => 'Đã báo giá', 'quote_details' => ''])->assertUnprocessable();
        $quote = 'Giá thỏa thuận; giao hàng hằng tuần';
        $this->withToken($adminToken)->putJson('/api/management/wholesale/'.$lead->id, ['status' => 'Đã báo giá', 'quote_details' => $quote])->assertOk()->assertJsonPath('quote_details', $quote);
        $this->assertNotSame($quote, DB::table('wholesale_leads')->where('id', $lead->id)->value('quote_details'));
    }

    public function test_order_shipping_requires_admin_confirmation_and_d2c_channel(): void
    {
        [$userToken] = $this->register('user@example.com');
        [$managerToken] = $this->register('manager@example.com', 'manager');
        [$adminToken] = $this->register('admin@example.com', 'admin');
        $product = Product::create(['name' => 'Gà', 'slug' => 'ga', 'category' => 'Gà tươi', 'description' => 'Mẫu', 'price' => 500000, 'wholesale_price' => 400000, 'unit' => 'kg', 'image' => 'https://example.com/ga.jpg', 'is_active' => true]);
        $input = ['customer_name' => 'An', 'phone' => '0900000000', 'address' => 'Đắk Lắk', 'channel' => 'D2C', 'payment_method' => 'cod', 'items' => [['id' => $product->id, 'quantity' => 1]]];
        $this->withToken($userToken)->postJson('/api/checkout', array_merge($input, ['channel' => 'B2C']))->assertUnprocessable()->assertJsonValidationErrors('channel');
        $response = $this->withToken($userToken)->postJson('/api/checkout', $input)->assertCreated()->assertJsonPath('shipping_fee', null)->assertJsonPath('total', null);
        $order = Order::where('code', $response->json('code'))->firstOrFail();
        $this->assertNull($order->shipping_fee);
        $this->withToken($managerToken)->getJson('/api/management/orders')->assertForbidden();
        $this->withToken($adminToken)->getJson('/api/management/orders')->assertOk()->assertJsonFragment(['code' => $order->code]);
        $this->withToken($adminToken)->putJson('/api/management/orders/'.$order->id, ['status' => 'Đã xác nhận', 'shipping_fee' => null])->assertUnprocessable();
        $this->withToken($adminToken)->putJson('/api/management/orders/'.$order->id, ['status' => 'Chờ khách đồng ý', 'shipping_fee' => 40000])->assertOk()->assertJsonPath('total', 540000);
        $this->withToken($adminToken)->putJson('/api/management/orders/'.$order->id, ['status' => 'Đã xác nhận', 'shipping_fee' => 40000])->assertOk();
    }

    public function test_only_admin_can_handle_complaints_and_store_an_encrypted_resolution(): void
    {
        [$managerToken] = $this->register('manager@example.com', 'manager');
        [$adminToken] = $this->register('admin@example.com', 'admin');
        $result = $this->postJson('/api/complaints', ['name' => 'An', 'phone' => '0900000000', 'description' => 'Thiếu hàng'])->assertCreated();
        $complaint = Complaint::where('code', $result->json('code'))->firstOrFail();
        $this->withToken($managerToken)->getJson('/api/management/complaints')->assertForbidden();
        $this->withToken($adminToken)->getJson('/api/management/complaints')->assertOk()->assertJsonFragment(['code' => $complaint->code]);
        $this->withToken($adminToken)->putJson('/api/management/complaints/'.$complaint->id, ['status' => 'Hoàn tất', 'resolution' => ''])->assertUnprocessable();
        $this->withToken($adminToken)->putJson('/api/management/complaints/'.$complaint->id, ['status' => 'Hoàn tất', 'resolution' => 'Đã giao bù'])->assertOk()->assertJsonPath('resolution', 'Đã giao bù');
        $this->assertNotSame('Đã giao bù', DB::table('complaints')->where('id', $complaint->id)->value('resolution'));
    }
}
