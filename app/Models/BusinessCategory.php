<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessCategory extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function applications(): HasMany
    {
        return $this->hasMany(BusinessApplication::class);
    }
}
