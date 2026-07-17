<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SeoSetting;
use App\Support\AdminSecurity;
use App\Support\SuspiciousContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Security',
            'email' => 'security@gmail.com',
            'password' => 'Password-Lama-123!',
            'is_active' => true,
        ]);
    }

    public function test_metadata_cms_tetap_di_escape_meskipun_database_sudah_terkontaminasi(): void
    {
        SeoSetting::create([
            'page_key' => 'home',
            'meta_title' => '"><script>alert(1)</script>',
            'meta_description' => '"><img src=x onerror=alert(1)>',
        ]);
        $this->refreshPublicSiteConfig();

        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $content);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $content);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $content);
    }

    public function test_middleware_menolak_injeksi_aktif_dan_judol_terobfuscasi(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.services.store'), [
                'title' => 'Layanan Aman',
                'description' => '"><img src=x onerror=alert(1)>',
                'display_order' => 1,
            ])
            ->assertSessionHasErrors('description');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.services.store'), [
                'title' => 'Promo j.u.d.0.l terpercaya',
                'display_order' => 1,
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_detektor_mengenali_encoding_dan_penyamaran_umum(): void
    {
        $this->assertSame('injection', SuspiciousContent::reason('%3Cscript%3Ealert(1)%3C/script%3E'));
        $this->assertSame('injection', SuspiciousContent::reason('java&#x73;cript:alert(1)'));
        $this->assertSame('gambling-spam', SuspiciousContent::reason('s.l.0.t g4c0r'));
        $this->assertNull(SuspiciousContent::reason('Konsultasi legalitas usaha dan sertifikat halal'));
    }

    public function test_gambar_polyglot_ditolak_setelah_validasi_mime_framework(): void
    {
        Storage::fake('public');
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ).'<?php system($_GET["cmd"]);';

        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.settings.update'), [
                'company_name' => 'PT Uji Aman',
                'logo' => UploadedFile::fake()->createWithContent('logo.png', $png),
            ])
            ->assertSessionHasErrors('file');

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_auth_version_dan_timeout_mencabut_sesi_admin(): void
    {
        config()->set('admin.session_idle_seconds', 300);
        config()->set('admin.session_absolute_seconds', 600);
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->withSession([
                AdminSecurity::SESSION_AUTH_VERSION => $admin->auth_version + 1,
                AdminSecurity::SESSION_STARTED_AT => now()->timestamp,
                AdminSecurity::SESSION_LAST_ACTIVITY_AT => now()->timestamp,
            ])
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('home'));
        $this->assertGuest('admin');

        $this->actingAs($admin, 'admin')
            ->withSession([
                AdminSecurity::SESSION_AUTH_VERSION => $admin->auth_version,
                AdminSecurity::SESSION_STARTED_AT => now()->subMinutes(11)->timestamp,
                AdminSecurity::SESSION_LAST_ACTIVITY_AT => now()->timestamp,
            ])
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('home'));
        $this->assertGuest('admin');
    }

    public function test_password_admin_baru_wajib_memenuhi_kebijakan_kuat(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.account.update'), [
                'current_email' => $admin->email,
                'current_password' => 'Password-Lama-123!',
                'email' => $admin->email,
                'password' => 'PasswordBaru456',
                'password_confirmation' => 'PasswordBaru456',
            ])
            ->assertSessionHasErrors('password');
    }
}
