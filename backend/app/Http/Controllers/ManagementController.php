<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\RewardVoucher;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ManagementController extends Controller
{
    public function products(): JsonResponse
    {
        return response()->json(Product::orderByDesc('id')->get());
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $data = $this->productData($request);
        $data['wholesale_price'] = $data['price']; // Preserve the legacy non-null column for existing databases.
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(8));

        return response()->json(Product::create($data), 201);
    }

    public function updateProduct(Request $request, Product $product): JsonResponse
    {
        $product->update($this->productData($request));

        return response()->json($product->refresh());
    }

    public function hideProduct(Product $product): JsonResponse
    {
        $product->update(['is_active' => false]);

        return response()->json(['message' => 'Đã ngừng bán sản phẩm.']);
    }

    private function productData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:100'],
            'segment' => ['nullable', Rule::in(['economy', 'standard', 'premium'])],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'unit' => ['required', 'string', 'max:60'],
            'image' => ['required', 'url', 'max:255'],
            'badge' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    public function users(): JsonResponse
    {
        return response()->json(User::query()->orderBy('id')->get(['id', 'name', 'email', 'role', 'is_active', 'created_at']));
    }

    public function orders(): JsonResponse
    {
        return response()->json(Order::with('items')->latest('id')->limit(100)->get());
    }

    public function updateOrder(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'shipping_fee' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'status' => ['required', Rule::in(['Chờ xác nhận', 'Chờ khách đồng ý', 'Đã xác nhận', 'Đang giao', 'Đã giao', 'Đã hủy'])],
        ]);

        return DB::transaction(function () use ($data, $order) {
            if ($order->user_id) {
                User::whereKey($order->user_id)->lockForUpdate()->first();
            }
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (in_array($order->status, ['Đã giao', 'Đã hủy'], true) && $data['status'] !== $order->status) {
                return response()->json(['message' => 'Đơn đã giao hoặc đã hủy không thể đổi trạng thái.'], 422);
            }
            if (in_array($order->status, ['Đã giao', 'Đã hủy'], true)) {
                return response()->json($order->load('items'));
            }
            if (in_array($data['status'], ['Đã xác nhận', 'Đang giao', 'Đã giao'], true) && ($data['shipping_fee'] ?? null) === null) {
                return response()->json(['message' => 'Cần nhập phí giao đã thỏa thuận trước khi xác nhận đơn.'], 422);
            }
            if ($order->free_shipping_eligible && ($data['shipping_fee'] ?? null) !== null && (int) $data['shipping_fee'] !== 0) {
                return response()->json(['message' => 'Đơn đủ điều kiện miễn phí giao phải có phí giao bằng 0.'], 422);
            }
            $order->shipping_fee = $data['shipping_fee'] ?? null;
            $order->total = $order->shipping_fee === null ? null : $order->subtotal - $order->discount_amount + $order->shipping_fee;
            if ($data['status'] === 'Đã giao') {
                $order->delivered_at = now();
                $order->points_awarded = intdiv($order->subtotal - $order->discount_amount, config('promotions.points_per_amount'));
                if ($order->user_id) {
                    RewardVoucher::firstOrCreate(['source_order_id' => $order->id], [
                        'user_id' => $order->user_id,
                        'code' => 'MON'.strtoupper(Str::random(12)),
                        'amount' => config('promotions.return_voucher_amount'),
                    ]);
                }
            } elseif ($data['status'] === 'Đã hủy') {
                RewardVoucher::where('used_order_id', $order->id)->update(['used_order_id' => null]);
            }
            $order->status = $data['status'];
            $order->save();

            return response()->json($order->load('items'));
        });
    }

    public function complaints(): JsonResponse
    {
        return response()->json(Complaint::latest('id')->limit(100)->get());
    }

    public function updateComplaint(Request $request, Complaint $complaint): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['Mới tiếp nhận', 'Đang xác minh', 'Đã phản hồi', 'Hoàn tất'])],
            'resolution' => ['nullable', 'string', 'max:5000'],
        ]);
        if (in_array($data['status'], ['Đã phản hồi', 'Hoàn tất'], true) && ! trim($data['resolution'] ?? '')) {
            return response()->json(['message' => 'Vui lòng ghi phương án hoặc kết quả xử lý.'], 422);
        }
        $complaint->update($data);

        return response()->json($complaint->refresh());
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['user', 'manager', 'admin'])],
            'is_active' => ['required', 'boolean'],
        ]);
        if ($request->user()->id === $user->id && ($data['role'] !== 'admin' || ! $data['is_active'])) {
            return response()->json(['message' => 'Bạn không thể tự hạ quyền hoặc khóa tài khoản đang sử dụng.'], 422);
        }
        $user->role = $data['role'];
        $user->is_active = $data['is_active'];
        $user->save();
        if (! $user->is_active) {
            DB::table('api_tokens')->where('user_id', $user->id)->delete();
        }

        return response()->json(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'is_active' => $user->is_active]);
    }
}
