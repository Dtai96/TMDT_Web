<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DemoCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_receives_order_reference_and_admin_can_see_new_order(): void
    {
        $product = Product::create(['name' => 'Gà', 'slug' => 'ga', 'category' => 'Gà tươi', 'description' => 'Mẫu', 'price' => 100000, 'wholesale_price' => 90000, 'unit' => 'kg', 'image' => 'sample', 'is_active' => true]);
        $customer = $this->postJson('/api/register', ['name' => 'Khách', 'email' => 'customer@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertCreated()->json('token');
        $admin = $this->postJson('/api/register', ['name' => 'Quản trị', 'email' => 'admin@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertCreated();
        User::whereKey($admin->json('user.id'))->update(['role' => 'admin']);

        $result = $this->withToken($customer)->postJson('/api/checkout', [
            'customer_name' => 'An', 'phone' => '0900000000', 'address' => 'Đắk Lắk',
            'channel' => 'D2C', 'payment_method' => 'cod', 'items' => [['id' => $product->id, 'quantity' => 1]],
        ])->assertCreated()->assertJsonStructure(['message', 'code']);

        $this->assertDatabaseHas('orders', ['code' => $result->json('code'), 'status' => 'Chờ xác nhận']);
        $this->withToken($admin->json('token'))->getJson('/api/management/orders')
            ->assertOk()->assertJsonFragment(['code' => $result->json('code'), 'customer_name' => 'An']);
        $this->withToken($customer)->getJson('/api/management/orders')->assertForbidden();
    }

    public function test_demo_catalog_covers_all_categories_and_segments_without_overwriting_products(): void
    {
        $existing = Product::create(['name' => 'Sản phẩm đã sửa', 'slug' => 'demo-uc-ga-500g', 'category' => 'Gà tươi', 'segment' => 'economy', 'description' => 'Giữ nguyên', 'price' => 123000, 'wholesale_price' => 123000, 'unit' => 'khay', 'image' => 'sample', 'is_active' => true]);

        $this->seed(DemoCatalogSeeder::class);
        $this->seed(DemoCatalogSeeder::class);

        $this->assertDatabaseCount('products', 12);
        $this->assertSame(123000, $existing->refresh()->price);
        foreach (['Gà tươi', 'Trứng & thực phẩm', 'Chế biến sẵn', 'Combo tiết kiệm'] as $category) {
            foreach (['economy', 'standard', 'premium'] as $segment) {
                $this->assertDatabaseHas('products', ['category' => $category, 'segment' => $segment]);
            }
        }
    }

    public function test_checkout_saves_encrypted_delivery_preferences_and_server_price(): void
    {
        $product = Product::create(['name' => 'Gà', 'slug' => 'ga', 'category' => 'Gà tươi', 'description' => 'Mẫu', 'price' => 100000, 'wholesale_price' => 90000, 'unit' => 'kg', 'image' => 'sample', 'is_active' => true]);
        $token = $this->postJson('/api/register', ['name' => 'An', 'email' => 'an@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'])->json('token');
        $this->withToken($token)->postJson('/api/checkout', ['customer_name' => 'An', 'phone' => '0900000000', 'address' => 'Đắk Lắk', 'desired_time' => 'Buổi sáng', 'referral_source' => 'Bạn bè', 'channel' => 'D2C', 'payment_method' => 'cod', 'items' => [['id' => $product->id, 'quantity' => 2, 'price' => 1]]])->assertCreated()->assertJsonPath('subtotal', 200000)->assertJsonPath('shipping_fee', null)->assertJsonPath('total', null);
        $order = Order::firstOrFail();
        $this->assertSame('Buổi sáng', $order->desired_time);
        $this->assertSame('Bạn bè', $order->referral_source);
        $this->assertNotSame('Buổi sáng', DB::table('orders')->value('desired_time'));
    }

    public function test_wholesale_endpoint_is_no_longer_available(): void
    {
        $this->postJson('/api/wholesale', [])->assertNotFound();
    }

    public function test_checkout_rejects_duplicate_products_and_excessive_totals(): void
    {
        $product = Product::create(['name' => 'Gà', 'slug' => 'ga', 'category' => 'Gà tươi', 'description' => 'Mẫu', 'price' => 1000000000, 'wholesale_price' => 90000, 'unit' => 'kg', 'image' => 'sample', 'is_active' => true]);
        $token = $this->postJson('/api/register', ['name' => 'An', 'email' => 'an@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'])->json('token');
        $input = ['customer_name' => 'An', 'phone' => '0900000000', 'address' => 'Đắk Lắk', 'channel' => 'D2C', 'payment_method' => 'cod'];
        $this->withToken($token)->postJson('/api/checkout', $input + ['items' => [['id' => $product->id, 'quantity' => 1], ['id' => $product->id, 'quantity' => 1]]])->assertUnprocessable()->assertJsonValidationErrors(['items.0.id']);
        $this->withToken($token)->postJson('/api/checkout', $input + ['items' => [['id' => $product->id, 'quantity' => 4]]])->assertUnprocessable()->assertJsonValidationErrors(['items']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_complaint_returns_reference_and_encrypts_details(): void
    {
        $this->postJson('/api/complaints', [])->assertUnprocessable()->assertJsonValidationErrors(['name', 'phone', 'description']);
        $response = $this->postJson('/api/complaints', ['name' => 'An', 'phone' => '0900000000', 'description' => 'Giao thiếu hàng', 'order_code' => 'GM123']);
        $response->assertCreated()->assertJsonStructure(['code']);
        $this->assertSame($response->json('code'), Complaint::firstOrFail()->code);
        $this->assertSame('Giao thiếu hàng', Complaint::firstOrFail()->description);
        $this->assertNotSame('Giao thiếu hàng', DB::table('complaints')->value('description'));
    }
}
