<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebVisit extends Model
{
    public $timestamps = false;

    protected $fillable = ['visitor_key', 'path', 'route_name', 'referrer_host', 'device_type', 'visited_at'];

    protected function casts(): array
    {
        return ['visited_at' => 'datetime'];
    }
}
