<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blogimages extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_id',
        'image_url',
        'image_public_id'
    ];


    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
