<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentReplacementChange extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reverted_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ContentReplacementRun::class, 'content_replacement_run_id');
    }
}
