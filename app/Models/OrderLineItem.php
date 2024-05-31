<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'item_id', 'name', 'product_id', 'variation_id', 'quantity',
        'subtotal', 'subtotal_tax', 'total', 'total_tax', 'sku', 'price',
        'image_url', 'parent_name'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
