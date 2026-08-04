<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentReplacementRun extends Model
{
    protected $guarded = [];

    protected $casts = [
        'case_sensitive' => 'boolean',
        'executed_at' => 'datetime',
        'reverted_at' => 'datetime',
    ];

    public function changes(): HasMany
    {
        return $this->hasMany(ContentReplacementChange::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reverter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reverted_by');
    }
}
