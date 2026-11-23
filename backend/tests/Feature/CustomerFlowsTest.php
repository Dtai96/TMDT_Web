<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\WholesaleLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_saves_encrypted_delivery_preferences_and_server_price(): void
    {
        $product = Product::create(['name' => 'Gà', 'slug' => 'ga', 'category' => 'Gà tươi', 'description' => 'Mẫu', 'price' => 100000, 'wholesale_price' => 90000, 'unit' => 'kg', 'image' => 'sample', 'is_active' => true]);
        $this->postJson('/api/checkout', ['customer_name' => 'An', 'phone' => '0900000000', 'address' => 'Đắk Lắk', 'desired_time' => 'Buổi sáng', 'referral_source' => 'Bạn bè', 'channel' => 'D2C', 'payment_method' => 'cod', 'items' => [['id' => $product->id, 'quantity' => 2, 'price' => 1]]])->assertCreated()->assertJsonPath('total', 230000);
        $order = Order::firstOrFail();
        $this->assertSame('Buổi sáng', $order->desired_time);
        $this->assertSame('Bạn bè', $order->referral_source);
        $this->assertNotSame('Buổi sáng', DB::table('orders')->value('desired_time'));
    }

    public function test_wholesale_requires_location_and_frequency_and_encrypts_them(): void
    {
        $input = ['business_name' => 'Bếp ăn', 'contact_name' => 'An', 'phone' => '0900000000', 'monthly_volume' => '50 kg'];
        $this->postJson('/api/wholesale', $input)->assertUnprocessable()->assertJsonValidationErrors(['delivery_location', 'purchase_frequency']);
        $this->postJson('/api/wholesale', $input + ['delivery_location' => 'Đắk Lắk', 'purchase_frequency' => 'Hàng tuần'])->assertCreated();
        $this->assertSame('Đắk Lắk', WholesaleLead::firstOrFail()->delivery_location);
        $this->assertNotSame('Đắk Lắk', DB::table('wholesale_leads')->value('delivery_location'));
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
