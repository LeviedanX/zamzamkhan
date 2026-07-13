<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    /**
     * Waktu selesai efektif. Kolom ends_at nullable demi kompatibilitas data lama,
     * jadi agenda tanpa waktu selesai dianggap berakhir tepat di waktu mulainya.
     */
    private const FINISHES_AT = 'COALESCE(ends_at, starts_at)';

    protected $guarded = [];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];

    /** Agenda yang waktu selesainya sudah lewat. */
    public function scopeFinished(Builder $query): Builder
    {
        return $query->whereRaw(self::FINISHES_AT.' < ?', [now()]);
    }

    /** Agenda yang belum selesai: belum mulai atau sedang berlangsung. */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereRaw(self::FINISHES_AT.' >= ?', [now()]);
    }

    /** Waktu selesai efektif untuk dipakai di PHP. */
    public function finishesAt(): ?\Illuminate\Support\Carbon
    {
        return $this->ends_at ?? $this->starts_at;
    }
}
