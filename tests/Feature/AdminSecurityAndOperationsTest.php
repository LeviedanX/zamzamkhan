<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ReportExport;
use App\Models\SiteSetting;
use App\Models\WebVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSecurityAndOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Operasional',
            'email' => 'operasional@gmail.com',
            'password' => 'PasswordLama123',
            'is_active' => true,
        ]);
    }

    private function png(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));
    }

    public function test_sesi_admin_langsung_ditolak_setelah_akun_dinonaktifkan(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();

        $admin->update(['is_active' => false]);

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_logo_svg_ditolak_dan_logo_raster_dapat_dihapus_eksplisit(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'company_name' => 'PT Uji',
            'logo' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ])->assertSessionHasErrors('logo');

        $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'company_name' => 'PT Uji',
            'logo' => $this->png('logo.png'),
        ])->assertSessionHasNoErrors();

        $path = SiteSetting::firstOrFail()->logo_path;
        Storage::disk('public')->assertExists($path);

        $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'company_name' => 'PT Uji',
            'remove_logo' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull(SiteSetting::firstOrFail()->logo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_retensi_operasional_menghapus_hanya_data_dan_export_lama(): void
    {
        Storage::fake('local');
        config()->set('admin.retention.web_visits_days', 30);
        config()->set('admin.retention.report_exports_days', 30);

        WebVisit::create(['visitor_key' => str_repeat('a', 64), 'path' => '/', 'device_type' => 'desktop', 'visited_at' => now()->subDays(31)]);
        WebVisit::create(['visitor_key' => str_repeat('b', 64), 'path' => '/', 'device_type' => 'mobile', 'visited_at' => now()->subDays(2)]);

        Storage::disk('local')->put('reports/lama.csv', 'lama');
        Storage::disk('local')->put('reports/baru.csv', 'baru');
        $lama = ReportExport::create(['title' => 'Lama', 'report_type' => 'applications', 'format' => 'csv', 'file_path' => 'reports/lama.csv', 'generated_at' => now()->subDays(31)]);
        $baru = ReportExport::create(['title' => 'Baru', 'report_type' => 'applications', 'format' => 'csv', 'file_path' => 'reports/baru.csv', 'generated_at' => now()->subDays(2)]);

        $this->artisan('operational:purge')->assertSuccessful();

        $this->assertDatabaseMissing('web_visits', ['visitor_key' => str_repeat('a', 64)]);
        $this->assertDatabaseHas('web_visits', ['visitor_key' => str_repeat('b', 64)]);
        $this->assertDatabaseMissing('report_exports', ['id' => $lama->id]);
        $this->assertDatabaseHas('report_exports', ['id' => $baru->id]);
        Storage::disk('local')->assertMissing('reports/lama.csv');
        Storage::disk('local')->assertExists('reports/baru.csv');
    }

    public function test_filter_pengajuan_invalid_ditolak_tanpa_error_server(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.applications.index', ['applicant_type' => 'invalid']))
            ->assertSessionHasErrors('applicant_type');
    }

    public function test_rotasi_password_mencabut_sesi_database_lain(): void
    {
        config()->set('session.driver', 'database');
        $admin = $this->admin();
        DB::table('sessions')->insert([
            'id' => 'sesi-perangkat-lama',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Uji',
            'payload' => base64_encode('uji'),
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin, 'admin')->put(route('admin.account.update'), [
            'current_email' => $admin->email,
            'current_password' => 'PasswordLama123',
            'email' => $admin->email,
            'password' => 'PasswordBaru456',
            'password_confirmation' => 'PasswordBaru456',
        ])->assertRedirect(route('admin.account.edit'));

        $this->assertDatabaseMissing('sessions', ['id' => 'sesi-perangkat-lama']);
        $this->assertAuthenticatedAs($admin->fresh(), 'admin');
    }

    public function test_seluruh_halaman_utama_crud_admin_dapat_dirender(): void
    {
        $admin = $this->admin();
        $routes = [
            'admin.dashboard', 'admin.hero.edit', 'admin.settings.edit',
            'admin.services.index', 'admin.services.create',
            'admin.faqs.index', 'admin.faqs.create',
            'admin.advantages.index', 'admin.advantages.create',
            'admin.statistics.index', 'admin.statistics.create',
            'admin.clients.index', 'admin.clients.create',
            'admin.testimonials.index', 'admin.testimonials.create',
            'admin.agendas.index', 'admin.agendas.create',
            'admin.articles.index', 'admin.articles.create',
            'admin.article-categories.index', 'admin.applications.index',
            'admin.applications.create', 'admin.business-categories.index',
            'admin.reports.index', 'admin.analytics.index', 'admin.seo.edit',
            'admin.account.edit',
        ];

        foreach ($routes as $routeName) {
            $this->actingAs($admin, 'admin')->get(route($routeName))->assertOk();
        }
    }

    public function test_command_rotasi_admin_tidak_menerima_password_dari_argumen(): void
    {
        config()->set('session.driver', 'database');
        $admin = $this->admin();
        DB::table('sessions')->insert([
            'id' => 'sesi-sebelum-rotasi-console',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Uji',
            'payload' => base64_encode('uji'),
            'last_activity' => now()->timestamp,
        ]);

        $this->artisan('admin:rotate-credentials', [
            '--email' => 'rotasi@gmail.com',
            '--name' => 'Admin Rotasi',
        ])
            ->expectsQuestion('Password baru', 'PasswordBaru456')
            ->expectsQuestion('Ulangi password baru', 'PasswordBaru456')
            ->assertSuccessful();

        $this->assertDatabaseHas('admins', ['id' => $admin->id, 'email' => 'rotasi@gmail.com']);
        $this->assertDatabaseMissing('sessions', ['id' => 'sesi-sebelum-rotasi-console']);
    }
}
