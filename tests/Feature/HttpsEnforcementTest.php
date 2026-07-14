<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use ReflectionMethod;
use Tests\TestCase;

class HttpsEnforcementTest extends TestCase
{
    private function bootHttpsEnforcement(string $env, string $appUrl): void
    {
        config()->set('app.env', $env);
        config()->set('app.url', $appUrl);
        app()->instance('env', $env);

        $method = new ReflectionMethod(AppServiceProvider::class, 'enforceHttps');
        $method->setAccessible(true);
        $method->invoke(new AppServiceProvider(app()));
    }

    public function test_produksi_dengan_app_url_https_menghasilkan_url_https(): void
    {
        $this->bootHttpsEnforcement('production', 'https://zamzamkhan.test');

        $this->assertStringStartsWith('https://', url('/artikel'));
    }

    public function test_local_tidak_dipaksa_https_agar_localhost_tetap_bisa_diakses(): void
    {
        $this->bootHttpsEnforcement('local', 'http://127.0.0.1:8000');

        $this->assertStringStartsWith('http://', url('/artikel'));
    }

    /**
     * Penjaga terhadap kesalahan konfigurasi: environment production yang masih
     * memakai APP_URL http tidak boleh menghasilkan URL https, karena URL itu
     * tidak akan bisa dijangkau siapa pun.
     */
    public function test_produksi_dengan_app_url_http_tidak_memaksa_https(): void
    {
        $this->bootHttpsEnforcement('production', 'http://127.0.0.1:8000');

        $this->assertStringStartsWith('http://', url('/artikel'));
    }

    protected function tearDown(): void
    {
        URL::forceScheme('http');

        parent::tearDown();
    }
}
