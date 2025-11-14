<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = ['page_id','type','position','settings'];

    protected $casts = ['settings' => 'array'];
    public function media()
    {
        return $this->hasMany(SectionMedia::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
