<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['code', 'customer_name', 'phone', 'email', 'address', 'note', 'desired_time', 'referral_source', 'channel', 'subtotal', 'shipping_fee', 'total', 'status', 'payment_method', 'user_id', 'discount_amount', 'discount_label', 'free_shipping_eligible', 'points_awarded', 'delivered_at'];

    protected function casts(): array
    {
        return [
            'customer_name' => 'encrypted',
            'phone' => 'encrypted',
            'email' => 'encrypted',
            'address' => 'encrypted',
            'note' => 'encrypted',
            'desired_time' => 'encrypted',
            'referral_source' => 'encrypted',
            'subtotal' => 'integer',
            'shipping_fee' => 'integer',
            'total' => 'integer',
            'discount_amount' => 'integer',
            'free_shipping_eligible' => 'boolean',
            'points_awarded' => 'integer',
            'delivered_at' => 'datetime',
        ];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
