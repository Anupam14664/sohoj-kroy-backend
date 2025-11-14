<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionMedia extends Model
{
    protected $fillable = ['page_section_id','file_path','type','original_name'];

    public function section()
    {
        return $this->belongsTo(PageSection::class, 'page_section_id');
    }
}
