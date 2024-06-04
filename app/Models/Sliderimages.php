<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sliderimages extends Model
{
    use HasFactory;

    protected $fillable = [
        'slider_id',
        'image_url',
        'image_public_id'
    ];

    public function slider()
    {
        return $this->belongsTo(Slider::class);
    }
}
