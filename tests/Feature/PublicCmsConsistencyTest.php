<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class PublicCmsConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Konsistensi',
            'email' => 'konsistensi@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    public function test_section_dan_navigasi_hilang_saat_data_dinonaktifkan(): void
    {
        SiteSetting::create(['company_name' => 'PT Uji Konsistensi']);
        Service::create([
            'title' => 'Layanan Nonaktif',
            'slug' => 'layanan-nonaktif',
            'is_active' => false,
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertDontSee('081256059099');

        $this->refreshPublicSiteConfig();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="tentang"', false)
            ->assertDontSee('id="visi-misi"', false)
            ->assertDontSee('id="layanan"', false)
            ->assertDontSee('id="agenda"', false)
            ->assertDontSee('id="kontak"', false)
            ->assertDontSee('href="#layanan"', false)
            ->assertDontSee('href="#agenda"', false);
    }

    public function test_url_gambar_tidak_ikut_membeku_bersama_cache_konten(): void
    {
        // Cache konten hidup 6 jam. Bila URL aset disimpan sebagai URL absolut, host/port
        // request yang kebetulan menghangatkan cache ikut terkunci — gambar mati saat situs
        // diakses dari host lain. Cache wajib menyimpan path relatif; URL absolut dibentuk
        // ulang tiap request.
        SiteSetting::create([
            'company_name' => 'PT Uji Aset',
            'logo_path' => 'images/logo-zzk.png',
        ]);

        $provider = new AppServiceProvider($this->app);

        // Payload inilah yang masuk ke cache 6 jam: wajib bebas host.
        $cached = (new ReflectionMethod(AppServiceProvider::class, 'buildSiteContent'))->invoke($provider);

        $this->assertSame('images/logo-zzk.png', $cached['logo_url']);
        $this->assertStringNotContainsString('://', (string) $cached['logo_url']);

        // Yang dipakai Blade tetap URL absolut, dibentuk dari host request saat ini.
        $presented = (new ReflectionMethod(AppServiceProvider::class, 'presentForRequest'))->invoke($provider, $cached);

        $this->assertSame(asset('images/logo-zzk.png'), $presented['logo_url']);

        // Dan benar-benar sampai ke HTML sebagai URL absolut yang bisa dimuat browser.
        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('src="'.asset('images/logo-zzk.png').'"', false);
    }

    public function test_json_ld_aman_dari_script_breakout_dan_memakai_data_cms(): void
    {
        $dangerousName = '</script><script>window.compromised=true</script>';
        SiteSetting::create([
            'company_name' => $dangerousName,
            'company_description' => 'Deskripsi CMS resmi.',
            'address' => 'Alamat CMS resmi.',
        ]);

        $this->refreshPublicSiteConfig();
        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString($dangerousName, $content);
        $this->assertStringNotContainsString('<script>window.compromised=true</script>', $content);
        $this->assertStringContainsString('\u003C/script\u003E', $content);

        preg_match('/<script type="application\/ld\+json"[^>]*>\s*(.*?)\s*<\/script>/s', $content, $match);
        $schema = json_decode($match[1] ?? '', true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($dangerousName, $schema['name']);
        $this->assertSame('Deskripsi CMS resmi.', $schema['description']);
        $this->assertSame('Alamat CMS resmi.', $schema['address']['streetAddress']);
    }

    public function test_homepage_menampilkan_peta_saat_url_maps_cms_tersedia(): void
    {
        SiteSetting::create([
            'company_name' => 'PT Uji Peta',
            'address' => 'Alamat kantor uji.',
            'maps_url' => 'https://www.google.com/maps/search/?api=1&query=Malang',
            'maps_embed_url' => 'https://www.google.com/maps?q=Malang&output=embed',
        ]);

        $this->refreshPublicSiteConfig();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Lokasi Kami')
            ->assertSee('Buka di Google Maps')
            ->assertSee('<iframe', false)
            // Tautan biasa dinormalisasi ke endpoint embed final. URL `?output=embed` di-redirect
            // Google dengan X-Frame-Options: SAMEORIGIN sehingga peta diblokir browser.
            ->assertSee('https://www.google.com/maps/embed?origin=mfe&amp;pb=!1m2!2m1!1sMalang', false)
            ->assertDontSee('output=embed', false);
    }

    public function test_canonical_seo_menolak_skema_non_http(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.seo.update'), [
                'meta_title' => 'SEO Uji',
                'canonical_url' => 'file://localhost/etc/passwd',
            ])
            ->assertSessionHasErrors('canonical_url');
    }
}
