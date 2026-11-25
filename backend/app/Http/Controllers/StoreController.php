<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\WholesaleLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    public function products(Request $request)
    {
        $query = Product::where('is_active', true);
        if ($request->filled('category') && $request->category !== 'Tất cả') {
            $query->where('category', $request->category);
        }
        if ($request->filled('q')) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('description', 'like', '%'.$request->q.'%'));
        }

        return response()->json($query->orderBy('id')->get()->makeHidden('wholesale_price'));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'address' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'desired_time' => ['nullable', 'string', 'max:120'],
            'referral_source' => ['nullable', 'string', 'max:160'],
            'payment_method' => ['required', 'in:cod,bank'],
            'channel' => ['required', 'in:D2C'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $products = Product::whereIn('id', collect($data['items'])->pluck('id'))->where('is_active', true)->get()->keyBy('id');
        $subtotal = 0;
        $lines = [];
        foreach ($data['items'] as $item) {
            $product = $products->get($item['id']);
            if (! $product) {
                throw ValidationException::withMessages(['items' => 'Một sản phẩm không còn kinh doanh.']);
            }
            $price = $product->price;
            $lineTotal = $price * $item['quantity'];
            $subtotal += $lineTotal;
            if ($subtotal > 3000000000) {
                throw ValidationException::withMessages(['items' => 'Giá trị đơn hàng vượt giới hạn. Vui lòng gửi yêu cầu báo giá B2B.']);
            }
            $lines[] = ['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $item['quantity'], 'unit_price' => $price, 'line_total' => $lineTotal];
        }
        $order = DB::transaction(function () use ($data, $subtotal, $lines, $request) {
            $order = Order::create([
                'code' => 'GM'.now()->format('ymd').strtoupper(Str::random(5)),
                'customer_name' => $data['customer_name'], 'phone' => $data['phone'], 'email' => $data['email'] ?? null,
                'address' => $data['address'], 'note' => $data['note'] ?? null, 'channel' => $data['channel'],
                'desired_time' => $data['desired_time'] ?? null, 'referral_source' => $data['referral_source'] ?? null,
                'subtotal' => $subtotal, 'shipping_fee' => null, 'total' => null,
                'status' => 'Chờ xác nhận', 'payment_method' => $data['payment_method'],
                'user_id' => $request->user()->id,
            ]);
            $order->items()->createMany($lines);

            return $order;
        });

        return response()->json(['message' => 'Đã tiếp nhận đơn. Trại sẽ xác nhận phí giao và tổng tiền.', 'code' => $order->code, 'subtotal' => $order->subtotal, 'shipping_fee' => null, 'total' => null], 201);
    }

    public function wholesale(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:160'], 'contact_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'], 'email' => ['nullable', 'email', 'max:160'],
            'monthly_volume' => ['required', 'string', 'max:80'], 'message' => ['required', 'string', 'max:1000'],
            'delivery_location' => ['required', 'string', 'max:255'], 'purchase_frequency' => ['required', 'string', 'max:100'],
        ]);
        $lead = WholesaleLead::create($data + ['reference' => 'B2B'.now()->format('ymd').strtoupper(Str::random(6))]);

        return response()->json(['message' => 'Đã tiếp nhận yêu cầu báo giá. Trại sẽ liên hệ để trao đổi.', 'reference' => $lead->reference], 201);
    }

    public function complaint(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'phone' => ['required', 'string', 'max:20'],
            'order_code' => ['nullable', 'string', 'max:50'], 'received_at' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
        ]);
        $complaint = Complaint::create($data + ['code' => 'KN'.now()->format('ymd').strtoupper(Str::random(6))]);

        return response()->json(['message' => 'Đã tiếp nhận phản ánh.', 'code' => $complaint->code], 201);
    }
}
