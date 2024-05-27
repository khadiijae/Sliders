<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendors';
    protected $primaryKey = 'id';
    public $timestamps = false; // Si votre table ne contient pas de colonnes created_at et updated_at

    protected $fillable = [
        'store_name',
        'first_name',
        'last_name',
        'fb',
        'twitter',
        'show_email',
        'street_1',
        'street_2',
        'registered',
        'banner',
        'banner_cloudinary',
        'gravatar',
        'gravatar_cloudinary',
        'categories'
    ];

    public function products()
{
    return $this->hasMany(Product::class, 'store_id');
}
}
