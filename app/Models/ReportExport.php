<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExport extends Model
{
    protected $guarded = [];
    protected $casts = ['filters_json' => 'array', 'columns_json' => 'array', 'generated_at' => 'datetime'];
    public function admin(): BelongsTo { return $this->belongsTo(Admin::class, 'generated_by'); }
}
