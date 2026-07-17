
@extends('layouts.admin')
@section('title', 'Profil & Identitas')

@php
    $inp = 'w-full rounded-xl border border-navy-200 bg-white/95 px-4 py-3 text-sm text-navy-900 shadow-sm shadow-navy-900/5 focus:border-emerald-brand focus:outline-none focus:ring-2 focus:ring-emerald-brand/20';
    $lbl = 'mb-1.5 block text-sm font-semibold text-navy-800';
    $help = 'mt-1 text-xs text-navy-400';
    $settingValue = fn (string $field, mixed $fallback = null) => $setting->exists ? $setting->{$field} : $fallback;
    $missionItems = collect(preg_split('/\r\n|\r|\n/', old('mission', $setting->mission) ?: ''))->map(fn ($x) => trim($x))->filter()->values();
    $legacySocials = collect([
        ['label' => 'Instagram', 'url' => old('instagram_url', $setting->instagram_url)],
        ['label' => 'Facebook', 'url' => old('facebook_url', $setting->facebook_url)],
        ['label' => 'TikTok', 'url' => old('tiktok_url', $setting->tiktok_url)],
    ])->filter(fn ($x) => filled($x['url']))->values();
    // Kondisi lama memakai $setting->exists untuk memilih sumber data, padahal baris
    // site_settings biasanya sudah ada di semua environment (dibuat seeder), sehingga
    // cabang legacy tidak pernah tercapai walau social_links masih NULL — itulah
    // sebabnya form ini sempat menampilkan "0 akun" padahal tiga akun sudah live di
    // footer publik lewat kolom lama. Kondisi yang benar: pakai social_links kalau
    // sudah terisi, kalau belum baru fallback ke kolom lama.
    $socialItems = collect(old('social_links', filled($setting->social_links ?? null) ? $setting->social_links : $legacySocials->all()))
        ->map(fn ($x) => ['label' => $x['label'] ?? '', 'url' => $x['url'] ?? ''])
        ->filter(fn ($x) => filled($x['label']) || filled($x['url']))
        ->values();
    $formConfig = [
        'companyName' => old('company_name', $settingValue('company_name', config('company.name'))),
        'tagline' => old('tagline', $settingValue('tagline', config('company.tagline'))),
        'description' => old('company_description', $settingValue('company_description', config('company.about'))),
        'vision' => old('vision', $settingValue('vision', config('company.vision'))),
        'missionItems' => $missionItems->values(),
        'phone' => old('phone', $settingValue('phone', config('company.phone_display'))),
        'whatsapp' => old('whatsapp', $settingValue('whatsapp', config('company.phone_raw'))),
        'email' => old('email', $settingValue('email', config('company.email'))),
        'address' => old('address', $settingValue('address', config('company.address'))),
        'operatingHours' => old('operating_hours', $settingValue('operating_hours', config('company.operating_hours'))),
        'socialItems' => $socialItems->values(),
    ];
@endphp

@section('content')
<div class="mb-6">
    <p class="admin-page-kicker">Profil Perusahaan</p>
    <h1 class="mt-2 font-display text-2xl font-bold text-navy-900 sm:text-3xl">Profil &amp; Identitas Perusahaan</h1>
    <p class="mt-2 max-w-3xl text-sm leading-relaxed text-navy-500">Edit identitas, tentang, kontak, lokasi, dan elemen sosial di satu layar.</p>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6 pb-8"
      x-data="siteSettingsForm" data-config="{{ json_encode($formConfig, JSON_THROW_ON_ERROR) }}">
    @csrf @method('PUT')

    <div class="admin-form-shell">
        <div class="space-y-6">
            <section class="admin-form-card space-y-5 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header>
                    <h2 class="font-display text-lg font-semibold text-navy-900">Identitas Perusahaan</h2>
                    <p class="text-xs text-navy-500">Nama perusahaan dan tagline.</p>
                </header>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $lbl }}">Nama perusahaan <span class="text-tosca-500">*</span></label>
                        <input name="company_name" x-model="companyName" class="{{ $inp }}" required>
                        <p class="{{ $help }}">Dipakai sebagai teks brand di navbar, footer, judul halaman, dan meta OG.</p>
                    </div>
                    {{-- Nama konsultan/direktur dihapus dari sini: caption figur hero dikelola penuh
                         di menu Hero Utama (field "Nama figur"), sekaligus dengan foto, alt, dan role-nya. --}}
                    <div>
                        <label class="{{ $lbl }}">Tagline</label>
                        <input name="tagline" x-model="tagline" class="{{ $inp }}">
                    </div>
                </div>
            </section>

            <section class="admin-form-card space-y-5 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header>
                    <h2 class="font-display text-lg font-semibold text-navy-900">Tentang Perusahaan</h2>
                    <p class="text-xs text-navy-500">Deskripsi, visi, dan CRUD poin misi.</p>
                </header>
                <div>
                    <label class="{{ $lbl }}">Deskripsi perusahaan</label>
                    <textarea name="company_description" rows="4" x-model="description" class="{{ $inp }}" placeholder="Ceritakan profil singkat perusahaan..."></textarea>
                    <p class="{{ $help }}">Tampil di section Tentang Kami. Kosongkan untuk memakai teks bawaan.</p>
                </div>
                <div>
                    <label class="{{ $lbl }}">Visi</label>
                    <textarea name="vision" rows="3" x-model="vision" class="{{ $inp }}"></textarea>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label class="{{ $lbl }} mb-0!">Misi</label>
                        <span class="text-xs font-semibold text-navy-400" x-text="missionCountLabel"></span>
                    </div>
                    <input type="hidden" name="mission" :value="missionPayload()">
                    <div class="space-y-2">
                        <template x-for="(mission, index) in missionItems" :key="index">
                            <div class="flex items-center gap-2 rounded-2xl border border-navy-100 bg-white/70 p-2">
                                <span class="inline-flex h-8 w-8 flex-none items-center justify-center rounded-full bg-red-900 text-xs font-bold text-white" x-text="index + 1"></span>
                                <input type="text" x-model="missionItems[index]" class="{{ $inp }} py-2!" :aria-label="missionLabel(index)">
                                <button type="button" class="rounded-xl border border-red-900/20 px-3 py-2 text-xs font-bold text-red-800 hover:bg-red-900/5" @click="removeMission(index)">Hapus</button>
                            </div>
                        </template>
                    </div>
                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                        <input type="text" x-model="missionDraft" class="{{ $inp }} py-2!" placeholder="Tambah poin misi" @keydown.enter.prevent="addMission">
                        <button type="button" class="btn-outline shrink-0 py-2!" @click="addMission">Tambah Misi</button>
                    </div>
                    <p class="{{ $help }}">Tambah, ubah, atau hapus poin misi dari sini. Data tersimpan ke database.</p>
                </div>
            </section>

            <section class="admin-form-card space-y-5 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header>
                    <h2 class="font-display text-lg font-semibold text-navy-900">Kontak &amp; Operasional</h2>
                    <p class="text-xs text-navy-500">Telepon, WhatsApp, email, alamat, dan jam operasional.</p>
                </header>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label class="{{ $lbl }}">Telepon</label>
                        <input name="phone" x-model="phone" class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}">WhatsApp</label>
                        <input name="whatsapp" x-model="whatsapp" placeholder="6285xxxxxxxxx" class="{{ $inp }}">
                        <p class="{{ $help }}">Nomor internasional, hanya angka.</p>
                    </div>
                    <div>
                        <label class="{{ $lbl }}">Email</label>
                        <input name="email" type="email" x-model="email" class="{{ $inp }}">
                    </div>
                </div>
                <div>
                    <label class="{{ $lbl }}">Alamat</label>
                    <textarea name="address" rows="2" x-model="address" class="{{ $inp }}"></textarea>
                </div>
                <div>
                    <label class="{{ $lbl }}">Jam operasional</label>
                    <input name="operating_hours" x-model="operatingHours" class="{{ $inp }}" placeholder="mis. Senin-Jumat, 08.00-16.00 WIB">
                </div>
            </section>

            <section class="admin-form-card space-y-5 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header>
                    <h2 class="font-display text-lg font-semibold text-navy-900">Lokasi &amp; Sosial Media</h2>
                    <p class="text-xs text-navy-500">Tautan peta dan CRUD akun sosial media.</p>
                </header>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $lbl }}">URL Google Maps</label>
                        <input name="maps_url" value="{{ old('maps_url', $setting->maps_url) }}" class="{{ $inp }}" placeholder="https://maps.google.com/...">
                        <p class="{{ $help }}">Tautan tombol Lihat di Google Maps.</p>
                    </div>
                    <div>
                        <label class="{{ $lbl }}">URL embed Google Maps</label>
                        <input name="maps_embed_url" value="{{ old('maps_embed_url', $setting->maps_embed_url) }}" class="{{ $inp }}" placeholder="https://www.google.com/maps/embed?pb=...">
                        <p class="{{ $help }}">URL peta interaktif. Paling aman memakai src iframe dari tombol <strong>Bagikan &rarr; Sematkan peta</strong> di Google Maps. Tautan biasa juga diterima dan otomatis dikonversi. Kosongkan untuk menyembunyikan peta.</p>
                    </div>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label class="{{ $lbl }} mb-0!">Sosial media</label>
                        <span class="text-xs font-semibold text-navy-400" x-text="socialCountLabel"></span>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(social, index) in socialItems" :key="index">
                            <div class="grid gap-2 rounded-2xl border border-navy-100 bg-white/70 p-2 sm:grid-cols-[150px_minmax(0,1fr)_auto]">
                                <input type="text" x-model="socialItems[index].label" :name="socialLabelName(index)" class="{{ $inp }} py-2!" placeholder="Label">
                                <input type="url" x-model="socialItems[index].url" :name="socialUrlName(index)" class="{{ $inp }} py-2!" placeholder="https://...">
                                <button type="button" class="rounded-xl border border-red-900/20 px-3 py-2 text-xs font-bold text-red-800 hover:bg-red-900/5" @click="removeSocial(index)">Hapus</button>
                            </div>
                        </template>
                    </div>
                    <div class="mt-3 grid gap-2 sm:grid-cols-[150px_minmax(0,1fr)_auto]">
                        <input type="text" x-model="socialDraft.label" class="{{ $inp }} py-2!" placeholder="Label">
                        <input type="url" x-model="socialDraft.url" class="{{ $inp }} py-2!" placeholder="https://...">
                        <button type="button" class="btn-outline shrink-0 py-2!" @click="addSocial">Tambah Sosial</button>
                    </div>
                    <p class="{{ $help }}">Tambah, ubah, atau hapus akun sosial. Data tersimpan sebagai JSON di database.</p>
                </div>
            </section>

            <section class="admin-form-card space-y-4 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header>
                    <h2 class="font-display text-lg font-semibold text-navy-900">Media</h2>
                    <p class="text-xs text-navy-500">Logo perusahaan.</p>
                </header>
                <div>
                    <label class="{{ $lbl }}">Logo (opsional)</label>
                    @if ($setting->logo_path)
                        @php
                            $adminLogoUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($setting->logo_path)
                                ? asset('storage/'.$setting->logo_path)
                                : asset('images/logo-zzk.png');
                        @endphp
                        <img src="{{ $adminLogoUrl }}" alt="Logo" class="mb-2 h-12">
                    @endif
                    <input name="logo" type="file" accept="image/jpeg,image/png,image/webp" class="{{ $inp }}">
                    <p class="{{ $help }}">JPG/PNG/WEBP, maksimal 2 MB.</p>
                    @if ($setting->logo_path)
                        <label class="mt-3 flex items-center gap-2 text-sm text-red-800">
                            <input type="checkbox" name="remove_logo" value="1">
                            Hapus logo unggahan dan gunakan logo bawaan
                        </label>
                    @endif
                </div>
            </section>
        </div>

    </div>

    <div class="admin-savebar flex flex-col gap-3 rounded-3xl border border-navy-100 bg-white/90 p-4 backdrop-blur sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm leading-relaxed text-navy-500">Simpan perubahan untuk memperbarui profil dan identitas perusahaan.</p>
        <div class="flex flex-col gap-2 sm:flex-row">
            <button type="submit" class="btn-primary justify-center px-6">Simpan Pengaturan</button>
        </div>
    </div>
</form>
@endsection
