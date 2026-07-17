<?php

namespace App\Http\Requests\Admin;

use App\Models\BusinessProcessStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Validator;

class BusinessApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'applicant_type' => ['required', Rule::in(['company', 'individual'])],
            'business_name' => ['nullable', 'string', 'max:255', 'required_if:applicant_type,company'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255', 'required_if:applicant_type,individual'],
            'address' => ['nullable', 'string', 'max:3000'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'business_category_id' => ['nullable', $this->categoryRule()],
            'new_business_category' => ['nullable', 'string', 'max:255'],
            'process_status' => ['required', 'string', 'max:50', $this->statusRule()],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status_note' => ['nullable', 'string', 'max:2000'],
            'submitted_at' => ['nullable', 'date'],
            'certificate_issued_at' => ['nullable', 'date', 'after_or_equal:submitted_at'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $statusType = BusinessProcessStatus::where('name', $this->input('process_status'))->value('type');

            if ($statusType === 'issued' && ! $this->filled('certificate_issued_at')) {
                $validator->errors()->add('certificate_issued_at', 'Tanggal sertifikat wajib diisi untuk status penerbitan sertifikat.');
            }
        }];
    }

    /**
     * Kategori baru wajib aktif. Tapi pengajuan yang sudah terlanjur memakai
     * kategori lalu kategorinya dinonaktifkan harus tetap bisa disunting dan
     * disimpan ulang tanpa kehilangan kategorinya.
     */
    private function categoryRule(): Exists
    {
        $current = $this->route('application')?->business_category_id;

        return Rule::exists('business_categories', 'id')
            ->where(fn ($query) => $query->where(fn ($q) => $q->where('is_active', true)
                ->when($current, fn ($q2, $id) => $q2->orWhere('id', $id))));
    }

    private function statusRule(): Exists
    {
        $current = $this->route('application')?->process_status;

        return Rule::exists('business_process_statuses', 'name')
            ->where(fn ($query) => $query->where(fn ($q) => $q->where('is_active', true)
                ->when($current, fn ($q2, $name) => $q2->orWhere('name', $name))));
    }
}
