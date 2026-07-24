<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityScanCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_marker_vite_diizinkan_pada_scan_lokal_tetapi_ditolak_pada_mode_production(): void
    {
        $hotPath = public_path('hot');
        $hotExisted = file_exists($hotPath);
        $originalContent = $hotExisted ? file_get_contents($hotPath) : null;

        file_put_contents($hotPath, 'http://127.0.0.1:5173');

        try {
            $this->artisan('security:scan')
                ->expectsOutputToContain('Security scan lulus')
                ->expectsOutputToContain('public/hot diizinkan')
                ->assertSuccessful();

            $this->artisan('security:scan', ['--production' => true])
                ->expectsOutputToContain('development-marker')
                ->assertFailed();
        } finally {
            if ($hotExisted) {
                file_put_contents($hotPath, (string) $originalContent);
            } elseif (file_exists($hotPath)) {
                unlink($hotPath);
            }
        }
    }
}
