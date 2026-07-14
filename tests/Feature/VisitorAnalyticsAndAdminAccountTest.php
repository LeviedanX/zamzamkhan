<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\WebVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VisitorAnalyticsAndAdminAccountTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Uji',
            'email' => 'adminlama@gmail.com',
            'password' => 'PasswordLama123',
            'is_active' => true,
        ]);
    }

    public function test_kunjungan_publik_dicatat_tanpa_ip_mentah_dan_admin_tidak_dicatat(): void
    {
        $this->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; Mobile) Safari/604.1')
            ->get(route('home'))->assertOk();

        $visit = WebVisit::firstOrFail();
        $this->assertSame('/', $visit->path);
        $this->assertSame('mobile', $visit->device_type);
        $this->assertSame(64, strlen($visit->visitor_key));
        $this->assertArrayNotHasKey('ip_address', $visit->getAttributes());

        $this->actingAs($this->admin(), 'admin')
            ->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/150.0 Safari/537.36')
            ->get(route('admin.dashboard'))
            ->assertOk();
        $this->assertSame(1, WebVisit::count());
    }

    public function test_bot_dan_response_non_html_tidak_dicatat(): void
    {
        $this->withHeader('User-Agent', 'Googlebot/2.1')->get(route('home'))->assertOk();
        $this->get('/robots.txt')->assertOk();

        $this->assertDatabaseCount('web_visits', 0);
    }

    public function test_admin_dapat_membuka_analitik_dan_memilih_semua_periode(): void
    {
        WebVisit::create([
            'visitor_key' => str_repeat('a', 64),
            'path' => '/',
            'device_type' => 'desktop',
            'visited_at' => now(),
        ]);

        $admin = $this->admin();
        foreach (['day', 'week', 'month', 'year', 'overall'] as $period) {
            $this->actingAs($admin, 'admin')
                ->get(route('admin.analytics.index', ['period' => $period]))
                ->assertOk()
                ->assertSee('Analitik Pengunjung')
                ->assertSee('Halaman dibuka dan sesi pengunjung')
                ->assertSee('visitor-trend-chart', false);
        }
    }

    public function test_perubahan_akun_ditolak_bila_kredensial_lama_salah_atau_email_bukan_gmail(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->put(route('admin.account.update'), [
            'current_email' => $admin->email,
            'current_password' => 'salah',
            'email' => 'adminbaru@gmail.com',
        ])->assertSessionHasErrors('current_credentials');

        $this->actingAs($admin, 'admin')->put(route('admin.account.update'), [
            'current_email' => $admin->email,
            'current_password' => 'PasswordLama123',
            'email' => 'adminbaru@example.com',
        ])->assertSessionHasErrors('email');

        $this->assertSame('adminlama@gmail.com', $admin->fresh()->email);
    }

    public function test_admin_dapat_mengganti_email_dan_password_setelah_verifikasi(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->put(route('admin.account.update'), [
            'current_email' => 'adminlama@gmail.com',
            'current_password' => 'PasswordLama123',
            'email' => 'adminbaru@gmail.com',
            'password' => 'PasswordBaru456',
            'password_confirmation' => 'PasswordBaru456',
        ])->assertRedirect(route('admin.account.edit'));

        $admin->refresh();
        $this->assertSame('adminbaru@gmail.com', $admin->email);
        $this->assertTrue(Hash::check('PasswordBaru456', $admin->password));
        $this->assertAuthenticatedAs($admin, 'admin');
    }
}
