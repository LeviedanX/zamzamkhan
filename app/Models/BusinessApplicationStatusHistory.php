<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessApplicationStatusHistory extends Model
{
    protected $guarded = [];
    public function application(): BelongsTo { return $this->belongsTo(BusinessApplication::class); }
    public function admin(): BelongsTo { return $this->belongsTo(Admin::class, 'changed_by'); }
}
