<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uji coba lengkap menu admin "Profil & Identitas".
 * Setiap field diuji sampai efeknya benar-benar tampil di website publik,
 * bukan sekadar tersimpan di database.
 */
class ProfilIdentitasTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Profil',
            'email' => 'profil@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    /** PNG asli 1x1 (GD tidak tersedia, jadi tidak bisa pakai UploadedFile::fake()->image()). */
    private function pngUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'zzk').'.png';
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    /** @return array<string, mixed> */
    private function payload(array $override = []): array
    {
        return array_merge([
            'company_name' => 'PT Zam Zam Khan',
            'tagline' => 'Bisnis & Legal Konsultan',
            'company_description' => "Paragraf profil pertama.\nParagraf profil kedua.",
            'vision' => 'Visi resmi perusahaan.',
            'mission' => "Misi pertama.\nMisi kedua.",
            'phone' => '0341-555123',
            'whatsapp' => '085234797788',
            'email' => 'halo@zamzamkhan.test',
            'address' => 'Jl. Uji Coba No. 1, Malang',
            'operating_hours' => 'Senin–Jumat, 08.00–16.00 WIB',
            'maps_url' => 'https://maps.google.com/?q=zzk',
            'maps_embed_url' => 'https://www.google.com/maps?q=zzk&output=embed',
            'social_links' => [
                ['label' => 'Instagram', 'url' => 'https://instagram.com/zzk'],
                ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/company/zzk'],
            ],
        ], $override);
    }

    public function test_guest_tidak_bisa_membuka_atau_menyimpan_profil(): void
    {
        $this->get(route('admin.settings.edit'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.settings.update'), $this->payload())->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_halaman_edit_menampilkan_semua_field_yang_dikelola(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Profil &amp; Identitas Perusahaan', false)
            ->assertSee('name="company_name"', false)
            ->assertSee('name="tagline"', false)
            ->assertSee('name="company_description"', false)
            ->assertSee('name="vision"', false)
            ->assertSee('name="mission"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="whatsapp"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="operating_hours"', false)
            ->assertSee('name="maps_url"', false)
            ->assertSee('name="maps_embed_url"', false)
            ->assertSee('name="logo"', false)
            ->assertSee('Tambah Misi')
            ->assertSee('Tambah Sosial');
    }

    public function test_semua_field_tersimpan_dan_benar_benar_tampil_di_website_publik(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.settings.update'), $this->payload())
            ->assertRedirect()
            ->assertSessionHas('ok');

        $this->assertDatabaseHas('site_settings', [
            'company_name' => 'PT Zam Zam Khan',
            'tagline' => 'Bisnis & Legal Konsultan',
            'company_description' => "Paragraf profil pertama.\nParagraf profil kedua.",
            'vision' => 'Visi resmi perusahaan.',
            'mission' => "Misi pertama.\nMisi kedua.",
            'phone' => '0341-555123',
            'email' => 'halo@zamzamkhan.test',
            'address' => 'Jl. Uji Coba No. 1, Malang',
            'operating_hours' => 'Senin–Jumat, 08.00–16.00 WIB',
            'maps_url' => 'https://maps.google.com/?q=zzk',
        ]);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            // Identitas → navbar, footer, meta OG
            ->assertSee('PT Zam Zam Khan')
            ->assertSee('Bisnis &amp; Legal Konsultan', false)
            // Tentang Kami → dua paragraf terpisah
            ->assertSee('Paragraf profil pertama.')
            ->assertSee('Paragraf profil kedua.')
            // Visi & Misi
            ->assertSee('Visi resmi perusahaan.')
            ->assertSee('Misi pertama.')
            ->assertSee('Misi kedua.')
            // Kontak & operasional
            ->assertSee('halo@zamzamkhan.test')
            ->assertSee('Jl. Uji Coba No. 1, Malang')
            ->assertSee('Senin–Jumat, 08.00–16.00 WIB')
            ->assertSee('0341-555123')
            // Lokasi — URL embed dinormalisasi ke endpoint yang boleh di-iframe.
            ->assertSee('https://maps.google.com/?q=zzk', false)
            ->assertSee('https://www.google.com/maps/embed?origin=mfe&amp;pb=!1m2!2m1!1szzk', false)
            ->assertDontSee('output=embed', false)
            // Sosial media → footer + JSON-LD sameAs
            ->assertSee('Instagram')
            ->assertSee('https://instagram.com/zzk', false)
            ->assertSee('LinkedIn')
            ->assertSee('https://linkedin.com/company/zzk', false);
    }

    /**
     * Nomor WhatsApp admin adalah single source of truth untuk semua CTA publik.
     * Format lokal (0…) harus dinormalisasi ke format internasional (62…).
     */
    public function test_nomor_whatsapp_dinormalisasi_dan_dipakai_semua_cta(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.settings.update'), $this->payload(['whatsapp' => '0852-3479-7788']))
            ->assertRedirect();

        $this->refreshPublicSiteConfig();

        $this->assertSame('6285234797788', config('company.whatsapp_number'));
        $this->assertStringStartsWith('https://wa.me/6285234797788?text=', config('company.whatsapp'));

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('6285234797788', $html);
        $this->assertStringContainsString('wa.me/6285234797788', $html);
        // Tidak boleh ada sisa nomor statis lama dari config/company.php.
        $this->assertStringNotContainsString('6281256059099', $html);
    }

    public function test_crud_poin_misi_tambah_ubah_dan_hapus(): void
    {
        $admin = $this->admin();

        // CREATE — tiga poin, termasuk baris kosong & spasi berlebih yang harus dibersihkan.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload([
                'mission' => "  Misi satu.  \n\n Misi dua. \n\nMisi tiga.",
            ]))
            ->assertRedirect();

        $this->assertSame("Misi satu.\nMisi dua.\nMisi tiga.", SiteSetting::firstOrFail()->mission);

        // UPDATE — ubah isi salah satu poin.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload([
                'mission' => "Misi satu diubah.\nMisi dua.\nMisi tiga.",
            ]))
            ->assertRedirect();

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))->assertOk()->assertSee('Misi satu diubah.');

        // DELETE — hapus satu poin.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload([
                'mission' => "Misi satu diubah.\nMisi tiga.",
            ]))
            ->assertRedirect();

        $this->assertSame("Misi satu diubah.\nMisi tiga.", SiteSetting::firstOrFail()->mission);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))->assertOk()->assertDontSee('Misi dua.');

        // DELETE ALL — dikosongkan total → null, homepage kembali ke misi bawaan (tidak error).
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload(['mission' => '']))
            ->assertRedirect();

        $this->assertNull(SiteSetting::firstOrFail()->mission);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))->assertOk()->assertDontSee('Misi satu diubah.');
    }

    public function test_crud_sosial_media_tambah_hapus_dan_buang_entri_tidak_lengkap(): void
    {
        $admin = $this->admin();

        // CREATE — entri tanpa label / tanpa url harus dibuang otomatis.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload([
                'social_links' => [
                    ['label' => 'Instagram', 'url' => 'https://instagram.com/zzk'],
                    ['label' => '', 'url' => 'https://tanpa-label.test'],
                    ['label' => 'TanpaUrl', 'url' => ''],
                    ['label' => 'TikTok', 'url' => 'https://tiktok.com/@zzk'],
                ],
            ]))
            ->assertRedirect();

        $this->assertSame([
            ['label' => 'Instagram', 'url' => 'https://instagram.com/zzk'],
            ['label' => 'TikTok', 'url' => 'https://tiktok.com/@zzk'],
        ], SiteSetting::firstOrFail()->social_links);

        // DELETE — sisakan satu akun.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload([
                'social_links' => [['label' => 'Instagram', 'url' => 'https://instagram.com/zzk']],
            ]))
            ->assertRedirect();

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://instagram.com/zzk', false)
            ->assertDontSee('https://tiktok.com/@zzk', false);

        // DELETE ALL — dikosongkan → null, homepage tetap aman.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload(['social_links' => []]))
            ->assertRedirect();

        $this->assertNull(SiteSetting::firstOrFail()->social_links);
        $this->refreshPublicSiteConfig();
        $this->get(route('home'))->assertOk()->assertDontSee('https://instagram.com/zzk', false);
    }

    public function test_upload_logo_tampil_di_navbar_dan_footer_lalu_upload_baru_mengganti_file_lama(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload(['logo' => $this->pngUpload('logo-lama.png')]))
            ->assertRedirect();

        $lama = SiteSetting::firstOrFail()->logo_path;
        $this->assertStringStartsWith('branding/', $lama);
        Storage::disk('public')->assertExists($lama);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))->assertOk()->assertSee('storage/'.$lama, false);

        // Upload baru → file lama dihapus, tidak menumpuk di storage.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload(['logo' => $this->pngUpload('logo-baru.png')]))
            ->assertRedirect();

        $baru = SiteSetting::firstOrFail()->logo_path;
        $this->assertNotSame($lama, $baru);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($baru);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))->assertOk()->assertSee('storage/'.$baru, false);
    }

    public function test_menyimpan_tanpa_logo_tidak_menghapus_logo_yang_sudah_ada(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload(['logo' => $this->pngUpload('logo.png')]))
            ->assertRedirect();

        $logo = SiteSetting::firstOrFail()->logo_path;

        // Simpan ulang tanpa menyertakan file logo.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload(['company_name' => 'PT Zam Zam Khan Baru']))
            ->assertRedirect();

        $setting = SiteSetting::firstOrFail();
        $this->assertSame('PT Zam Zam Khan Baru', $setting->company_name);
        $this->assertSame($logo, $setting->logo_path);
        Storage::disk('public')->assertExists($logo);
    }

    public function test_field_opsional_yang_dikosongkan_menjadi_null_dan_sectionnya_menyesuaikan(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), $this->payload());

        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload([
                'tagline' => '   ',
                'company_description' => '',
                'vision' => '',
                'mission' => '',
                'phone' => '',
                'whatsapp' => '',
                'email' => '',
                'address' => '',
                'operating_hours' => '',
                'maps_url' => '',
                'maps_embed_url' => '',
                'social_links' => [],
            ]))
            ->assertRedirect();

        $setting = SiteSetting::firstOrFail();
        $this->assertNull($setting->tagline);
        $this->assertNull($setting->company_description);
        $this->assertNull($setting->vision);
        $this->assertNull($setting->mission);
        $this->assertNull($setting->phone);
        $this->assertNull($setting->whatsapp);
        $this->assertNull($setting->email);
        $this->assertNull($setting->address);
        $this->assertNull($setting->operating_hours);
        $this->assertNull($setting->maps_url);
        $this->assertNull($setting->maps_embed_url);

        // Jam operasional dirender kondisional → barisnya hilang, halaman tetap sehat.
        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Senin–Jumat, 08.00–16.00 WIB')
            ->assertDontSee('id="tentang"', false)
            ->assertDontSee('id="visi-misi"', false)
            ->assertDontSee('id="kontak"', false)
            ->assertDontSee('href="#tentang"', false)
            ->assertDontSee('href="#visi-misi"', false)
            ->assertDontSee('href="#kontak"', false)
            ->assertDontSee('data-whatsapp-lead', false)
            ->assertDontSee('wa.me/', false)
            ->assertSee('PT Zam Zam Khan');
    }

    public function test_validasi_menolak_input_tidak_valid_dan_tidak_menyimpan_apa_pun(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload([
                'company_name' => '',
                'email' => 'bukan-email',
                'maps_url' => 'bukan-url',
                'social_links' => [['label' => 'Instagram', 'url' => 'bukan-url']],
            ]))
            ->assertSessionHasErrors(['company_name', 'email', 'maps_url', 'social_links.0.url']);

        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_validasi_url_hanya_menerima_web_dan_google_maps_resmi(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload([
                'maps_url' => 'https://evil.example/maps',
                'maps_embed_url' => 'file://localhost/etc/passwd',
                'social_links' => [['label' => 'FTP', 'url' => 'ftp://example.com/file']],
            ]))
            ->assertSessionHasErrors(['maps_url', 'maps_embed_url', 'social_links.0.url']);

        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_perubahan_profil_langsung_tampil_tanpa_perlu_clear_cache_manual(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), $this->payload());
        $this->refreshPublicSiteConfig();
        $this->get(route('home'))->assertOk()->assertSee('Visi resmi perusahaan.');

        // Simpan lagi dengan nilai baru — cache konten harus ter-flush otomatis oleh model event.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), $this->payload(['vision' => 'Visi versi kedua.']))
            ->assertRedirect();

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Visi versi kedua.')
            ->assertDontSee('Visi resmi perusahaan.');
    }
}
