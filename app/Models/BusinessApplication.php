<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessApplication extends Model
{
    public const STATUSES = ['Penawaran', 'Kontrak', 'Penyusunan SJPH', 'Audit Eksternal', 'Sidang Fatwa', 'Sertifikat Terbit', 'Ditunda', 'Batal'];

    protected $guarded = [];
    protected $casts = ['submitted_at' => 'date', 'certificate_issued_at' => 'date'];

    public function category(): BelongsTo { return $this->belongsTo(BusinessCategory::class, 'business_category_id'); }
    public function histories(): HasMany { return $this->hasMany(BusinessApplicationStatusHistory::class)->latest(); }
    public function creator(): BelongsTo { return $this->belongsTo(Admin::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(Admin::class, 'updated_by'); }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        $keyword = trim((string) ($filters['keyword'] ?? ''));

        return $query
            ->when($keyword !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->where('brand_name', 'like', "%{$keyword}%")
                ->orWhere('business_name', 'like', "%{$keyword}%")
                ->orWhere('owner_name', 'like', "%{$keyword}%")
                ->orWhere('registration_number', 'like', "%{$keyword}%")))
            ->when($filters['applicant_type'] ?? null, fn ($q, $v) => $q->where('applicant_type', $v))
            ->when($filters['process_status'] ?? null, fn ($q, $v) => $q->where('process_status', $v))
            ->when($filters['business_category_id'] ?? null, fn ($q, $v) => $q->where('business_category_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('submitted_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('submitted_at', '<=', $v));
    }

    public function applicantName(): string
    {
        return $this->applicant_type === 'company' ? ($this->business_name ?: '-') : ($this->owner_name ?: '-');
    }

    public function applicantTypeLabel(): string
    {
        return $this->applicant_type === 'company' ? 'Badan Usaha' : 'Perorangan / UMKM';
    }
}
