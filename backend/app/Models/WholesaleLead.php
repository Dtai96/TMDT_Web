<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleLead extends Model
{
    protected $fillable = ['business_name', 'contact_name', 'phone', 'email', 'monthly_volume', 'message', 'delivery_location', 'purchase_frequency'];

    protected function casts(): array
    {
        return [
            'business_name' => 'encrypted',
            'contact_name' => 'encrypted',
            'phone' => 'encrypted',
            'email' => 'encrypted',
            'message' => 'encrypted',
            'delivery_location' => 'encrypted',
            'purchase_frequency' => 'encrypted',
        ];
    }
}
