<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function logoUrl(): string
    {
        return str_starts_with($this->logo_path, 'images/')
            ? asset($this->logo_path)
            : asset('storage/'.$this->logo_path);
    }
}
