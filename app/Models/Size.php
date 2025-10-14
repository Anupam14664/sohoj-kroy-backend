<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', 
        'display_name',
        'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_sizes')
            ->withPivot('price_adjustment')
            ->withTimestamps();
    }

    // Helper method to get display name
    public function getDisplayNameAttribute()
    {
        return $this->display_name ?? $this->name;
    }
}