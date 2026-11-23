<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = ['code', 'name', 'phone', 'order_code', 'description', 'received_at', 'status'];

    protected function casts(): array
    {
        return ['name' => 'encrypted', 'phone' => 'encrypted', 'order_code' => 'encrypted', 'description' => 'encrypted', 'received_at' => 'encrypted'];
    }
}
