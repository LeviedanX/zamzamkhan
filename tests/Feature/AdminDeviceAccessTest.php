<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeviceAccessTest extends TestCase
{
    use RefreshDatabase;

    private const DESKTOP_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/150.0 Safari/537.36';

    public function test_login_admin_tetap_tersedia_untuk_browser_desktop(): void
    {
        $this->withHeader('User-Agent', self::DESKTOP_USER_AGENT)
            ->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Masukkan kata sandi');
    }

    public function test_seluruh_akses_admin_ditolak_dari_perangkat_mobile_dan_tablet(): void
    {
        $mobileUserAgents = [
            'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 Chrome/150.0 Mobile Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 18_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
        ];

        foreach ($mobileUserAgents as $userAgent) {
            $this->withHeader('User-Agent', $userAgent)
                ->get(route('admin.login'))
                ->assertForbidden()
                ->assertSee('Panel admin hanya tersedia di desktop')
                ->assertDontSee('Masukkan kata sandi');
        }
    }

    public function test_client_hint_mobile_juga_ditolak(): void
    {
        $this->withHeaders([
            'User-Agent' => self::DESKTOP_USER_AGENT,
            'Sec-CH-UA-Mobile' => '?1',
        ])->get(route('admin.login'))->assertForbidden();
    }

    public function test_admin_terautentikasi_tetap_ditolak_saat_memakai_perangkat_mobile(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Device Test',
            'email' => 'device-test@example.com',
            'password' => 'PasswordAman123!',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 Chrome/150.0 Mobile Safari/537.36')
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_drawer_mobile_publik_tidak_memuat_tombol_login_admin(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $this->assertStringNotContainsString(
            'btn-outline w-full !rounded-xl',
            $response->getContent(),
        );
        $response->assertSee('x-teleport="body"', false);
    }
}
