<?php

namespace Tests;

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use ReflectionMethod;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException(
                'Test dibatalkan: PHPUnit wajib memakai SQLite :memory:. Jalankan php artisan config:clear sebelum test.'
            );
        }

        return $app;
    }

    protected function refreshPublicSiteConfig(): void
    {
        // buildSiteContent() menumpuk di atas config('company') yang sedang aktif. Pada request
        // HTTP nyata basisnya selalu config statis yang baru dimuat, jadi reset dulu ke sana.
        // Tanpa reset ini, nilai yang sengaja dikosongkan admin bisa "hidup lagi" dari merge
        // sebelumnya (nilai null disaring array_filter), sehingga test tidak setia pada produksi.
        config(['company' => require config_path('company.php')]);

        // Tiru urutan boot() apa adanya: bangun payload (yang di produksi di-cache),
        // lalu materialisasi bagian yang bergantung request (URL aset & status agenda).
        // Tanpa presentForRequest(), test akan melihat path aset relatif — tidak setia
        // pada HTML yang benar-benar dirender di produksi.
        $provider = new AppServiceProvider($this->app);
        $build = new ReflectionMethod(AppServiceProvider::class, 'buildSiteContent');
        $present = new ReflectionMethod(AppServiceProvider::class, 'presentForRequest');

        config(['company' => $present->invoke($provider, $build->invoke($provider))]);
    }
}
