<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'size_id',
        'price',
        'stock',
        'sku',
    ];
    protected $casts = [
        'variant_id' => 'float',
        'price' => 'float',
        'color_id' => 'float',
        'stock' => 'float',

    ];
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }
    public function variants()
{
    return $this->hasMany(ProductVariant::class);
}
}
