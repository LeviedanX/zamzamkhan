<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_response_memakai_security_headers_dan_csp_nonce(): void
    {
        app()->instance('env', 'production');
        config()->set('security.csp_enabled', true);
        config()->set('security.hsts_enabled', true);

        $response = $this->get('https://127.0.0.1/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-[^']+' 'unsafe-eval'/", $csp);
        $this->assertStringContainsString('upgrade-insecure-requests', $csp);
        $this->assertStringContainsString('max-age=', (string) $response->headers->get('Strict-Transport-Security'));

        preg_match('/<meta name="csp-nonce" content="([^"]+)">/', $response->getContent(), $nonce);
        $this->assertNotEmpty($nonce[1] ?? null);
        $this->assertStringContainsString("'nonce-{$nonce[1]}'", $csp);
        $this->assertStringContainsString('nonce="'.$nonce[1].'"', $response->getContent());
    }

    public function test_production_http_tidak_memaksa_upgrade_sebelum_https_tersedia(): void
    {
        app()->instance('env', 'production');
        config()->set('security.csp_enabled', true);
        config()->set('security.hsts_enabled', true);

        $response = $this->get('http://127.0.0.1/')
            ->assertOk();

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString('upgrade-insecure-requests', $csp);
        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    public function test_admin_response_tidak_boleh_dicache(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Header',
            'email' => 'header@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache');
    }

    public function test_security_headers_tetap_ada_pada_404_dan_health_check(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY');

        $this->get('/up')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
