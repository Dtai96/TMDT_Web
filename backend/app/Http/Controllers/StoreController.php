<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\RewardVoucher;
use App\Models\User;
use App\PromotionRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    public function rewards(Request $request)
    {
        $userId = $request->user()->id;

        return response()->json([
            'points' => Order::where('user_id', $userId)->where('status', 'Đã giao')->sum('points_awarded'),
            'vouchers' => RewardVoucher::where('user_id', $userId)->whereNull('used_order_id')->get(['code', 'amount']),
            'rules' => config('promotions'),
        ]);
    }

    public function preview(Request $request, PromotionRules $rules)
    {
        $data = $request->validate($this->cartValidation());
        [$subtotal, , $quantity] = $this->priceItems($data['items']);
        $result = $rules->calculate($request->user(), $subtotal, $quantity, $data['promo_code'] ?? null);
        unset($result['voucher']);

        return response()->json(['subtotal' => $subtotal] + $result);
    }

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

    public function checkout(Request $request, PromotionRules $rules)
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
        ] + $this->cartValidation());

        [$subtotal, $lines, $quantity] = $this->priceItems($data['items']);
        $order = DB::transaction(function () use ($data, $subtotal, $lines, $quantity, $request, $rules) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $promotion = $rules->calculate($user, $subtotal, $quantity, $data['promo_code'] ?? null, true);
            $order = Order::create([
                'code' => 'GM'.now()->format('ymd').strtoupper(Str::random(5)),
                'customer_name' => $data['customer_name'], 'phone' => $data['phone'], 'email' => $data['email'] ?? null,
                'address' => $data['address'], 'note' => $data['note'] ?? null, 'channel' => $data['channel'],
                'desired_time' => $data['desired_time'] ?? null, 'referral_source' => $data['referral_source'] ?? null,
                'subtotal' => $subtotal, 'shipping_fee' => null, 'total' => null,
                'discount_amount' => $promotion['discount'], 'discount_label' => $promotion['label'],
                'free_shipping_eligible' => $promotion['free_shipping_eligible'],
                'status' => 'Chờ xác nhận', 'payment_method' => $data['payment_method'],
                'user_id' => $user->id,
            ]);
            $order->items()->createMany($lines);
            if ($promotion['voucher']) {
                $promotion['voucher']->update(['used_order_id' => $order->id]);
            }

            return $order;
        });

        return response()->json(['message' => 'Đã tiếp nhận đơn. Trại sẽ xác nhận phí giao và tổng tiền.', 'code' => $order->code, 'subtotal' => $order->subtotal, 'discount' => $order->discount_amount, 'free_shipping_eligible' => $order->free_shipping_eligible, 'shipping_fee' => null, 'total' => null], 201);
    }

    private function cartValidation(): array
    {
        return [
            'promo_code' => ['nullable', 'string', 'max:40'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    private function priceItems(array $items): array
    {
        $products = Product::whereIn('id', collect($items)->pluck('id'))->where('is_active', true)->get()->keyBy('id');
        $subtotal = 0;
        $lines = [];
        $quantity = 0;
        foreach ($items as $item) {
            $product = $products->get($item['id']);
            if (! $product) {
                throw ValidationException::withMessages(['items' => 'Một sản phẩm không còn kinh doanh.']);
            }
            $price = $product->price;
            $lineTotal = $price * $item['quantity'];
            $subtotal += $lineTotal;
            $quantity += $item['quantity'];
            if ($subtotal > 3000000000) {
                throw ValidationException::withMessages(['items' => 'Giá trị đơn hàng vượt giới hạn. Vui lòng giảm số lượng hoặc liên hệ trại.']);
            }
            $lines[] = ['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $item['quantity'], 'unit_price' => $price, 'line_total' => $lineTotal];
        }

        return [$subtotal, $lines, $quantity];
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
