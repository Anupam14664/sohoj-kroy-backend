<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'ip_address',
        'reason',
        'blocked_by'
    ];
}