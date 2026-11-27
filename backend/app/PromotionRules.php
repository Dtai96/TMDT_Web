<?php

namespace App;

use App\Models\Order;
use App\Models\RewardVoucher;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PromotionRules
{
    public function calculate(User $user, int $subtotal, int $quantity, ?string $code, bool $lock = false): array
    {
        $code = strtoupper(trim($code ?? ''));
        $voucher = null;
        $discount = 0;
        $label = null;

        if ($code === 'WELCOME') {
            $hasOrder = Order::where('user_id', $user->id)->where('status', '!=', 'Đã hủy')->exists();
            if ($hasOrder || $subtotal < config('promotions.first_order_minimum')) {
                throw ValidationException::withMessages(['promo_code' => 'Mã đơn đầu chỉ áp dụng cho tài khoản chưa có đơn đang xử lý hoặc đã giao và đạt mức tiền hàng tối thiểu.']);
            }
            $discount = config('promotions.first_order_discount');
            $label = 'WELCOME';
        } elseif ($code !== '') {
            $query = RewardVoucher::where('user_id', $user->id)->where('code', $code)->whereNull('used_order_id');
            $voucher = ($lock ? $query->lockForUpdate() : $query)->first();
            if (! $voucher) {
                throw ValidationException::withMessages(['promo_code' => 'Voucher không hợp lệ hoặc đã được sử dụng.']);
            }
            $discount = $voucher->amount;
            $label = $voucher->code;
        } else {
            $bulk = $quantity >= config('promotions.bulk_minimum_items');
            $repeat = Order::where('user_id', $user->id)->where('status', 'Đã giao')
                ->where('delivered_at', '>=', now()->subDays(config('promotions.repeat_days')))->exists();
            if ($bulk || $repeat) {
                $percent = max($bulk ? config('promotions.bulk_percent') : 0, $repeat ? config('promotions.repeat_percent') : 0);
                $discount = intdiv($subtotal * $percent, 100);
                $label = $bulk ? 'Ưu đãi số lượng' : 'Ưu đãi mua lại';
            }
        }

        $discount = min($subtotal, $discount);

        return [
            'discount' => $discount,
            'label' => $label,
            'payable_items' => $subtotal - $discount,
            'free_shipping_eligible' => $subtotal - $discount >= config('promotions.free_shipping_minimum'),
            'voucher' => $voucher,
        ];
    }
}
