<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'cost_type',
        'amount',
        'product_buy_price',
        'comment',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
