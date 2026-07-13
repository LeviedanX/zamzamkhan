@extends('layouts.admin')
@section('title', $application->exists ? 'Edit Pengajuan' : 'Tambah Pengajuan')

@section('content')
<x-admin.page-header
    eyebrow="Operasional Internal"
    :title="$application->exists ? 'Edit Pengajuan' : 'Tambah Pengajuan'"
    description="Lengkapi identitas pemohon dan perbarui progres pengajuan secara terstruktur."
/>

<form class="admin-form-shell admin-form-standard" method="POST" action="{{ $application->exists ? route('admin.applications.update', $application) : route('admin.applications.store') }}">
    @csrf
    @if ($application->exists) @method('PUT') @endif

    <section class="admin-form-surface">
        <div class="admin-form-section">
            <div>
                <h2 class="admin-form-section__title">Identitas Pemohon</h2>
                <p class="admin-form-section__description">Nama perusahaan wajib untuk badan usaha; nama pemilik wajib untuk perorangan atau UMKM.</p>
            </div>
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Jenis pemohon <b aria-hidden="true">*</b></span>
                    <select name="applicant_type" required>
                        <option value="company" @selected(old('applicant_type', $application->applicant_type ?: 'company') === 'company')>Badan Usaha</option>
                        <option value="individual" @selected(old('applicant_type', $application->applicant_type) === 'individual')>Perorangan / UMKM</option>
                    </select>
                </label>
                <label class="admin-field">
                    <span>Kategori bisnis</span>
                    <select name="business_category_id">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('business_category_id', $application->business_category_id) == $category->id)>{{ $category->name }}{{ $category->is_active ? '' : ' (nonaktif)' }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-field">
                    <span>Nama perusahaan</span>
                    <input name="business_name" value="{{ old('business_name', $application->business_name) }}" maxlength="255">
                </label>
                <label class="admin-field">
                    <span>Nama pemilik</span>
                    <input name="owner_name" value="{{ old('owner_name', $application->owner_name) }}" maxlength="255">
                </label>
                <label class="admin-field">
                    <span>Merek usaha</span>
                    <input name="brand_name" value="{{ old('brand_name', $application->brand_name) }}" maxlength="255">
                </label>
                <label class="admin-field">
                    <span>Kategori baru (opsional)</span>
                    <input name="new_business_category" value="{{ old('new_business_category') }}" maxlength="255" placeholder="Dibuat otomatis jika diisi">
                </label>
            </div>
            <label class="admin-field">
                <span>Alamat</span>
                <textarea name="address" rows="4" maxlength="3000">{{ old('address', $application->address) }}</textarea>
            </label>
        </div>

        <div class="admin-form-section">
            <div>
                <h2 class="admin-form-section__title">Proses Pengajuan</h2>
                <p class="admin-form-section__description">Setiap perubahan status akan dicatat pada riwayat pengajuan.</p>
            </div>
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Status proses <b aria-hidden="true">*</b></span>
                    <select name="process_status" required>
                        @foreach (\App\Models\BusinessApplication::STATUSES as $status)
                            <option value="{{ $status }}" @selected(old('process_status', $application->process_status ?: 'Penawaran') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-field">
                    <span>Nomor pendaftaran</span>
                    <input name="registration_number" value="{{ old('registration_number', $application->registration_number) }}" maxlength="100">
                </label>
                <label class="admin-field">
                    <span>Tanggal masuk</span>
                    <input type="date" name="submitted_at" value="{{ old('submitted_at', $application->submitted_at?->format('Y-m-d')) }}">
                </label>
                <label class="admin-field">
                    <span>Tanggal sertifikat</span>
                    <input type="date" name="certificate_issued_at" value="{{ old('certificate_issued_at', $application->certificate_issued_at?->format('Y-m-d')) }}">
                </label>
            </div>
            <label class="admin-field">
                <span>Catatan pengajuan</span>
                <textarea name="notes" rows="5" maxlength="5000">{{ old('notes', $application->notes) }}</textarea>
            </label>
            <label class="admin-field">
                <span>Catatan perubahan status</span>
                <textarea name="status_note" rows="3" maxlength="2000">{{ old('status_note') }}</textarea>
                <small>Catatan ini disimpan pada riwayat hanya ketika status berubah.</small>
            </label>
        </div>

        <div class="admin-form-actions">
            <a class="btn-outline" href="{{ $application->exists ? route('admin.applications.show', $application) : route('admin.applications.index') }}">Batal</a>
            <button type="submit" class="btn-primary">Simpan Pengajuan</button>
        </div>
    </section>
</form>
@endsection
