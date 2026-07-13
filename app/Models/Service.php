<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];
}
