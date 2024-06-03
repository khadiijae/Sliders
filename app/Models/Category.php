<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'count',
        'image_cloudinary',
        'image_public_id'

    ];

    public function product()
    {
        return $this->hasMany(Product::class, 'categorie_id');
    }
}
