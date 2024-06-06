<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'connected',
        'status',
        'currency',
        'version',
        'prices_include_tax',
        'discount_total',
        'discount_tax',
        'shipping_total',
        'shipping_tax',
        'cart_tax',
        'total',
        'total_tax',
        'customer_id',
        'order_key',
        'payment_method',
        'payment_method_title',
        'transaction_id',
        'customer_ip_address',
        'customer_user_agent',
        'created_via',
        'customer_note',
        'cart_hash',
        'number',
        'payment_url',
        'is_editable',
        'needs_payment',
        'needs_processing',
        'date_created_gmt',
        'date_modified_gmt',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function orderLineItems()
    {
        return $this->hasMany(OrderLineItem::class, 'order_id');
    }

    public function shippingAddress()
    {
        return $this->hasOne(ShippingAddressClient::class, 'order_id');
    }
}
