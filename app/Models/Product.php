<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'id_product',
        'id',
        'store_id',
        'name',
        'slug',
        'date_created',
        'date_modified',
        'status',
        'featured',
        'catalog_visibility',
        'description',
        'short_description',
        'sku',
        'price',
        'regular_price',
        'sale_price',
        'date_on_sale_from',
        'date_on_sale_to',
        'total_sales',
        'tax_status',
        'tax_class',
        'manage_stock',
        'stock_quantity',
        'stock_status',
        'backorders',
        'low_stock_amount',
        'sold_individually',
        'weight',
        'length',
        'width',
        'height',
        'parent_id',
        'reviews_allowed',
        'purchase_note',
        'menu_order',
        'post_password',
        'virtuall',
        'downloadable',
        'shipping_class_id',
        'download_limit',
        'download_expiry',
        'average_rating',
        'review_count'
    ];

    protected $primaryKey = 'id_product';

    public $timestamps = false;
    public function product_images()
{
    return $this->hasMany(ProductImage::class, 'product_id');
}

public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

 
}
