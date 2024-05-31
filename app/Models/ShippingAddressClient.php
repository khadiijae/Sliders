<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddressClient extends Model
{
    use HasFactory;

    protected $table = 'shipping_address_client';

    protected $fillable = [
        'customer_id', 'first_name', 'address_1',
        'address_2', 'city' ,'country', 'phone'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
