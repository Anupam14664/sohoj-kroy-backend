<?php

// app/Models/HomepageSection.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $fillable = ['name', 'position', 'is_active'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'homepage_section_category')
                    ->withPivot('order')
                    ->orderBy('order');
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
