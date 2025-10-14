<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'short_description',
        'description',
        'category_id',
        'size_id',
        'total_stock',
        'buy_price',
        'regular_price',
        'discount_price',
        'is_featured',
        'is_offer',
        'is_campaign',
        'main_image',
        'main_image',
        'status',
        'keyword_tags',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_offer' => 'boolean',
        'is_campaign' => 'boolean',
        'buy_price' => 'float',
        'size_id' => 'float',
        'regular_price' => 'float',
        'discount_price' => 'float',
        'status' => 'boolean',
        'keyword_tags' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getHasVariantsAttribute()
    {
        return $this->variants()->count() > 0;
    }

    public function updateStock()
    {
        if ($this->has_variants) {
            $this->total_stock = $this->variants->sum(function($variant) {
                return $variant->options->sum('stock');
            });
        }
        $this->save();
    }
    public function size()
{
    return $this->belongsTo(Size::class);
}
public function reviews()
{
    return $this->hasMany(Review::class)->where('is_approved', true);
}

public function allReviews()
{
    return $this->hasMany(Review::class);
}
public function scopeActive($query)
{
    return $query->where('status', true);
}

public function getImageUrlAttribute()
{
    if (!$this->main_image) {
        return null;
    }

    if (filter_var($this->main_image, FILTER_VALIDATE_URL)) {
        return $this->main_image;
    }

    return asset('storage/' . $this->main_image);
}

public function scopeLowStock($query, $threshold = 5)
{
    return $query->where('total_stock', '<', $threshold)
                ->where('total_stock', '>', 0)
                ->orderBy('total_stock', 'asc');
}

}
