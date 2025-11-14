<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['name','slug','status','meta', 'product_id'];

    protected $casts = ['meta' => 'array'];

    public function sections()
    {
        return $this->hasMany(PageSection::class)->orderBy('position');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
