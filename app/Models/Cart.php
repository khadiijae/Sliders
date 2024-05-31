<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'customer_id',
        'user_ip',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
