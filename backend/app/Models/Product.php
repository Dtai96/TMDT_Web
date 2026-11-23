<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'slug', 'category', 'description', 'price', 'wholesale_price', 'unit', 'image', 'badge', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'wholesale_price' => 'integer', 'is_active' => 'boolean'];
    }
}
