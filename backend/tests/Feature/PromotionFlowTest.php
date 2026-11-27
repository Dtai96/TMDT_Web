<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionFlowTest extends TestCase
{
    use RefreshDatabase;

    private function register(string $email, bool $admin = false): string
    {
        $response = $this->postJson('/api/register', ['name' => 'Khách', 'email' => $email, 'password' => 'password123', 'password_confirmation' => 'password123'])->assertCreated();
        if ($admin) {
            User::whereKey($response->json('user.id'))->update(['role' => 'admin']);
        }

        return $response->json('token');
    }

    public function test_vouchers_points_cancellation_and_account_isolation(): void
    {
        $customer = $this->register('customer@example.com');
        $other = $this->register('other@example.com');
        $admin = $this->register('admin@example.com', true);
        $product = Product::create(['name' => 'Gà', 'slug' => 'ga', 'category' => 'Gà tươi', 'description' => 'Demo', 'price' => 250000, 'wholesale_price' => 250000, 'unit' => 'kg', 'image' => 'sample', 'is_active' => true]);
        $items = [['id' => $product->id, 'quantity' => 1, 'price' => 1]];
        $input = ['customer_name' => 'An', 'phone' => '0900000000', 'address' => 'Đắk Lắk', 'channel' => 'D2C', 'payment_method' => 'cod', 'items' => $items, 'promo_code' => 'WELCOME'];

        $this->withToken($customer)->postJson('/api/checkout/preview', ['items' => $items, 'promo_code' => 'WELCOME'])->assertOk()->assertJsonPath('subtotal', 250000)->assertJsonPath('discount', 20000);
        $order = $this->withToken($customer)->postJson('/api/checkout', $input)->assertCreated()->assertJsonPath('discount', 20000);
        $id = Order::where('code', $order->json('code'))->firstOrFail()->id;
        $this->withToken($customer)->postJson('/api/checkout', $input)->assertUnprocessable()->assertJsonValidationErrors('promo_code');
        $this->withToken($admin)->putJson('/api/management/orders/'.$id, ['status' => 'Đã giao', 'shipping_fee' => 30000])->assertOk()->assertJsonPath('total', 260000)->assertJsonPath('points_awarded', 2);
        $this->withToken($admin)->putJson('/api/management/orders/'.$id, ['status' => 'Đã giao', 'shipping_fee' => 30000])->assertOk();
        $rewards = $this->withToken($customer)->getJson('/api/rewards')->assertOk()->assertJsonPath('points', 2)->assertJsonCount(1, 'vouchers');
        $code = $rewards->json('vouchers.0.code');
        $this->withToken($other)->postJson('/api/checkout/preview', ['items' => $items, 'promo_code' => $code])->assertUnprocessable();
        $second = $this->withToken($customer)->postJson('/api/checkout', array_merge($input, ['promo_code' => $code]))->assertCreated()->assertJsonPath('discount', 10000);
        $secondId = Order::where('code', $second->json('code'))->firstOrFail()->id;
        $this->withToken($customer)->getJson('/api/rewards')->assertJsonCount(0, 'vouchers');
        $this->withToken($admin)->putJson('/api/management/orders/'.$secondId, ['status' => 'Đã hủy', 'shipping_fee' => null])->assertOk();
        $this->withToken($customer)->getJson('/api/rewards')->assertJsonCount(1, 'vouchers');
        $this->withToken($admin)->putJson('/api/management/orders/'.$id, ['status' => 'Đã hủy', 'shipping_fee' => null])->assertUnprocessable();
    }

    public function test_bulk_discount_free_shipping_and_repeat_purchase_are_calculated_by_server(): void
    {
        $customer = $this->register('customer@example.com');
        $admin = $this->register('admin@example.com', true);
        $product = Product::create(['name' => 'Gà', 'slug' => 'ga', 'category' => 'Gà tươi', 'description' => 'Demo', 'price' => 150000, 'wholesale_price' => 150000, 'unit' => 'kg', 'image' => 'sample', 'is_active' => true]);
        $items = [['id' => $product->id, 'quantity' => 5]];
        $input = ['customer_name' => 'An', 'phone' => '0900000000', 'address' => 'Đắk Lắk', 'channel' => 'D2C', 'payment_method' => 'cod', 'items' => $items];
        $this->withToken($customer)->postJson('/api/checkout/preview', ['items' => $items])->assertOk()->assertJsonPath('discount', 37500)->assertJsonPath('free_shipping_eligible', true);
        $order = $this->withToken($customer)->postJson('/api/checkout', $input)->assertCreated()->assertJsonPath('discount', 37500);
        $id = Order::where('code', $order->json('code'))->firstOrFail()->id;
        $this->withToken($admin)->putJson('/api/management/orders/'.$id, ['status' => 'Đã xác nhận', 'shipping_fee' => 30000])->assertUnprocessable();
        $this->withToken($admin)->putJson('/api/management/orders/'.$id, ['status' => 'Đã giao', 'shipping_fee' => 0])->assertOk()->assertJsonPath('total', 712500);
        $this->withToken($customer)->postJson('/api/checkout/preview', ['items' => [['id' => $product->id, 'quantity' => 1]]])->assertOk()->assertJsonPath('discount', 7500)->assertJsonPath('label', 'Ưu đãi mua lại');
    }
}
