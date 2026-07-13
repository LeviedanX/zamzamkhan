@extends('layouts.admin')
@section('title', 'Akun Admin')

@section('content')
<x-admin.page-header
    eyebrow="Pengaturan"
    title="Akun Admin"
    description="Perbarui email login atau password. Kredensial akun lama wajib diverifikasi sebelum perubahan disimpan."
/>

<div class="account-layout">
    <form method="POST" action="{{ route('admin.account.update') }}" class="admin-form-standard admin-form-surface" autocomplete="off">
        @csrf
        @method('PUT')

        <section class="admin-form-section">
            <div>
                <h2 class="admin-form-section__title">Kredensial baru</h2>
                <p class="admin-form-section__description">Kosongkan password baru jika hanya ingin mengganti email.</p>
            </div>
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Email admin baru</span>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" required autocomplete="off" inputmode="email" placeholder="nama@gmail.com">
                    <small>Wajib berupa alamat Gmail dengan akhiran @gmail.com.</small>
                </label>
                <div class="account-current-card" aria-label="Informasi akun saat ini">
                    <span>Akun aktif saat ini</span>
                    <strong>{{ $admin->email }}</strong>
                    <small>Login terakhir: {{ $admin->last_login_at?->locale('id')->translatedFormat('d M Y, H.i') ?? 'Belum tercatat' }}</small>
                </div>
                <label class="admin-field">
                    <span>Password baru</span>
                    <input type="password" name="password" autocomplete="new-password" minlength="10" placeholder="Minimal 10 karakter">
                    <small>Minimal 10 karakter, huruf besar-kecil, dan angka.</small>
                </label>
                <label class="admin-field">
                    <span>Konfirmasi password baru</span>
                    <input type="password" name="password_confirmation" autocomplete="new-password" minlength="10" placeholder="Ulangi password baru">
                </label>
            </div>
        </section>

        <section class="admin-form-section account-verification">
            <div>
                <h2 class="admin-form-section__title">Verifikasi keamanan</h2>
                <p class="admin-form-section__description">Masukkan email dan password yang digunakan untuk login saat ini. Keduanya harus cocok.</p>
            </div>
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Email akun lama</span>
                    <input type="email" name="current_email" value="{{ old('current_email') }}" required autocomplete="off" placeholder="Email login saat ini">
                </label>
                <label class="admin-field">
                    <span>Password akun lama</span>
                    <input type="password" name="current_password" required autocomplete="current-password" placeholder="Password login saat ini">
                </label>
            </div>
        </section>

        <div class="admin-form-actions">
            <button type="submit" class="btn-primary">Verifikasi & Simpan Perubahan</button>
        </div>
    </form>

    <aside class="account-security-card">
        <span class="account-security-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.8 2.9 8.2 7 10 4.1-1.8 7-5.2 7-10V6l-7-3Zm-3 9 2 2 4-4"/></svg>
        </span>
        <p>Validasi berlapis</p>
        <h2>Perubahan tidak dapat dilakukan hanya dari sesi login.</h2>
        <ul>
            <li>Email lama harus cocok persis.</li>
            <li>Password lama diverifikasi terhadap hash tersimpan.</li>
            <li>Password baru disimpan dalam bentuk hash.</li>
            <li>Sesi diperbarui setelah kredensial berubah.</li>
        </ul>
    </aside>
</div>
@endsection
