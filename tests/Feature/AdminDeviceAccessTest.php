<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeviceAccessTest extends TestCase
{
    use RefreshDatabase;

    private const DESKTOP_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/150.0 Safari/537.36';

    public function test_login_admin_tetap_tersedia_untuk_browser_desktop(): void
    {
        $this->withHeader('User-Agent', self::DESKTOP_USER_AGENT)
            ->post(route('admin.access'))
            ->assertRedirect(route('admin.login'));

        $this->withHeader('User-Agent', self::DESKTOP_USER_AGENT)
            ->get(route('admin.login'))
            ->assertOk()
            ->assertSeeText('LOGIN ADMIN')
            ->assertDontSee('Selamat datang kembali')
            ->assertDontSee('Masukkan kredensial administrator untuk melanjutkan ke dashboard.')
            ->assertSee('Masukkan kata sandi')
            ->assertDontSee('Akses terbatas')
            ->assertDontSee('Gunakan hanya akun yang telah diotorisasi.')
            ->assertDontSee('login-eyebrow', false)
            ->assertDontSee('login-grid', false)
            ->assertDontSee('login-glow', false);
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
                ->post(route('admin.access'))
                ->assertForbidden();

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
        $admin = User::create([
            'is_admin' => true,
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

    public function test_tombol_login_admin_hanya_berada_di_footer_beranda_desktop(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $response
            ->assertSee('class="footer-admin-access hidden xl:block"', false)
            ->assertSee('action="'.route('admin.access').'"', false)
            ->assertSee('class="footer-admin-login">A</button>', false)
            ->assertSeeInOrder(['footer-admin-access', '&copy;'], false)
            ->assertDontSee('nav-admin-login', false)
            ->assertSee('x-teleport="body"', false);

        $this->get(route('artikel.index'))
            ->assertOk()
            ->assertDontSee('footer-admin-access', false);
    }
}
