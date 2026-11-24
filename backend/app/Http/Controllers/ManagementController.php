<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'wholesale_price' => ['required', 'integer', 'min:0', 'max:1000000000'],
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
