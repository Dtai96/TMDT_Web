<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardVoucher extends Model
{
    protected $fillable = ['user_id', 'source_order_id', 'used_order_id', 'code', 'amount'];
}
