<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BusinessProcessStatus extends Model
{
    public const TYPES = [
        'ongoing' => 'Sedang berjalan',
        'issued' => 'Sertifikat terbit',
        'cancelled' => 'Dibatalkan',
    ];

    protected $guarded = [];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(BusinessApplication::class, 'process_status', 'name');
    }

    public function newStatusHistories(): HasMany
    {
        return $this->hasMany(BusinessApplicationStatusHistory::class, 'new_status', 'name');
    }

    public function oldStatusHistories(): HasMany
    {
        return $this->hasMany(BusinessApplicationStatusHistory::class, 'old_status', 'name');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function namesForType(string $type): Collection
    {
        return static::where('type', $type)->pluck('name');
    }

    public static function defaultName(): ?string
    {
        return static::active()->where('is_default', true)->value('name')
            ?? static::active()->ordered()->value('name');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function usageCount(): int
    {
        return (int) ($this->applications_count ?? $this->applications()->count())
            + (int) ($this->new_status_histories_count ?? $this->newStatusHistories()->count())
            + (int) ($this->old_status_histories_count ?? $this->oldStatusHistories()->count());
    }
}
