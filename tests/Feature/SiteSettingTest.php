<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireAdminLoginEntry;
use App\Models\Admin;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\HeroSection;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(bool $active = true): Admin
    {
        return Admin::create([
            'name' => 'Admin Uji',
            'email' => 'admin@uji.test',
            'password' => 'password',
            'is_active' => $active,
        ]);
    }

    private function openAdminLogin(): void
    {
        $this->post(route('admin.access'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_tidak_bisa_membuka_pengaturan(): void
    {
        $this->get(route('admin.settings.edit'))->assertNotFound();
    }

    public function test_guest_tidak_bisa_membuka_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertNotFound();
    }

    public function test_admin_aktif_bisa_login(): void
    {
        $this->admin();
        $this->openAdminLogin();

        $this->post(route('admin.login.attempt'), [
            'email' => 'admin@uji.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs(Admin::first(), 'admin');
    }

    public function test_login_admin_tidak_menyediakan_persistent_session(): void
    {
        $this->openAdminLogin();
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertDontSee('Ingat sesi saya')
            ->assertDontSee('name="remember"', false);

        $this->admin();
        $this->openAdminLogin();

        $response = $this->post(route('admin.login.attempt'), [
            'email' => 'admin@uji.test',
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect(route('admin.dashboard'));

        $response->assertCookieMissing(Auth::guard('admin')->getRecallerName());
    }

    public function test_admin_nonaktif_ditolak(): void
    {
        $this->admin(active: false);
        $this->openAdminLogin();

        $this->from(route('admin.login'))
            ->post(route('admin.login.attempt'), [
                'email' => 'admin@uji.test',
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }

    public function test_login_admin_dibatasi_setelah_lima_kegagalan(): void
    {
        $this->admin();
        $this->openAdminLogin();
        $payload = ['email' => 'admin@uji.test', 'password' => 'salah'];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->from(route('admin.login'))
                ->post(route('admin.login.attempt'), $payload)
                ->assertRedirect(route('admin.login'));
        }

        $this->from(route('admin.login'))
            ->post(route('admin.login.attempt'), $payload)
            ->assertStatus(429)
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_login_admin_mengarahkan_pengunjung_ke_homepage_tanpa_entry_dari_tombol(): void
    {
        $this->get(route('admin.login'))->assertRedirect(route('home'));
        $this->get('/admin/login')->assertNotFound();
        $this->post(route('admin.login.attempt'))->assertRedirect(route('home'));

        $this->assertSame(url('/admin'), route('admin.login'));
        $this->assertSame(url('/admin'), route('admin.login.attempt'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('method="POST" action="'.route('admin.access').'"', false)
            ->assertDontSee('href="'.route('admin.login').'"', false);
    }

    public function test_entry_login_hanya_berlaku_untuk_satu_handoff_dari_tombol_resmi(): void
    {
        $this->post(route('admin.access'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.login'))->assertOk();

        $response = $this->get(route('admin.login'))->assertRedirect(route('home'));

        $response->assertSessionMissing(RequireAdminLoginEntry::SESSION_KEY);
        $response->assertSessionMissing(RequireAdminLoginEntry::HANDOFF_KEY);
    }

    public function test_login_gagal_tetap_bisa_kembali_ke_form_dengan_pesan_error(): void
    {
        $this->admin();
        $this->post(route('admin.access'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.login'))->assertOk();

        $this->from(route('admin.login'))
            ->post(route('admin.login.attempt'), [
                'email' => 'admin@uji.test',
                'password' => 'salah',
            ])->assertRedirect(route('admin.login'));

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Email atau kata sandi salah, atau akun tidak aktif.');
    }

    public function test_entry_login_admin_kedaluwarsa_setelah_batas_waktu(): void
    {
        $response = $this->withSession([
            RequireAdminLoginEntry::SESSION_KEY => now()->subSecond()->timestamp,
        ])->get(route('admin.login'))->assertRedirect(route('home'));

        $response->assertSessionMissing(RequireAdminLoginEntry::SESSION_KEY);
        $response->assertSessionMissing(RequireAdminLoginEntry::HANDOFF_KEY);
    }

    public function test_homepage_tetap_tampil_tanpa_site_setting(): void
    {
        $this->assertNull(SiteSetting::first());

        $this->get('/')->assertOk();
    }

    public function test_admin_menyimpan_field_profil_baru(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.settings.update'), [
                'company_name' => 'PT Zam Zam Khan',
                'company_description' => 'Deskripsi profil perusahaan.',
                'vision' => 'Visi baru perusahaan.',
                'mission' => "Misi satu.\nMisi dua.",
                'operating_hours' => 'Senin–Jumat, 08.00–16.00 WIB',
                'maps_url' => 'https://maps.google.com/?q=zzk',
                'social_links' => [
                    ['label' => 'Instagram', 'url' => 'https://instagram.com/zzk'],
                    ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/company/zzk'],
                    ['label' => '', 'url' => 'https://invalid-empty-label.test'],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('site_settings', [
            'vision' => 'Visi baru perusahaan.',
            'mission' => "Misi satu.\nMisi dua.",
            'operating_hours' => 'Senin–Jumat, 08.00–16.00 WIB',
        ]);

        $setting = SiteSetting::firstOrFail();
        $this->assertEquals([
            ['label' => 'Instagram', 'url' => 'https://instagram.com/zzk'],
            ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/company/zzk'],
        ], $setting->social_links);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('LinkedIn')
            ->assertSee('https://linkedin.com/company/zzk', false);
    }

    /**
     * Nama figur hero hanya boleh punya satu sumber: menu Hero Utama.
     * Dulu field ini terduplikasi sebagai "Nama konsultan/direktur" di Profil & Identitas.
     */
    public function test_nama_figur_hanya_dikelola_dari_menu_hero(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertDontSee('name="consultant_name"', false)
            ->assertDontSee('Nama konsultan / direktur');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.hero.edit'))
            ->assertOk()
            ->assertSee('name="portrait_name"', false);

        // Dikirim paksa pun tidak boleh tersimpan: field sudah tidak dikelola modul Profil.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.settings.update'), [
                'company_name' => 'PT Zam Zam Khan',
                'consultant_name' => 'Nama Selundupan',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('site_settings', ['consultant_name' => 'Nama Selundupan']);

        // Caption hero di homepage bersumber dari Hero Utama.
        HeroSection::create([
            'title' => 'Judul Hero',
            'portrait_name' => 'Nama Dari Menu Hero',
            'is_active' => true,
        ]);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Nama Dari Menu Hero');
    }

    public function test_artikel_draft_tidak_muncul_di_publik(): void
    {
        $cat = ArticleCategory::create(['name' => 'Umum', 'slug' => 'umum']);

        Article::create([
            'article_category_id' => $cat->id,
            'title' => 'Artikel Terbit', 'slug' => 'artikel-terbit',
            'excerpt' => 'ringkasan', 'content' => 'isi', 'status' => 'published', 'published_at' => now(),
        ]);
        Article::create([
            'article_category_id' => $cat->id,
            'title' => 'Artikel Draft', 'slug' => 'artikel-draft',
            'excerpt' => 'ringkasan', 'content' => 'isi', 'status' => 'draft',
        ]);

        $this->get(route('artikel.index'))
            ->assertOk()
            ->assertSee('Artikel Terbit')
            ->assertDontSee('Artikel Draft');
    }
}
