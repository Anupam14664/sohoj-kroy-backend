<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            ProductVariant::class,
            'color_id',
            'id',
            'id',
            'product_id'
        )->distinct();
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function setCodeAttribute($value)
{
    $value = ltrim($value, '#');
    $this->attributes['code'] = '#'.$value;
}
}