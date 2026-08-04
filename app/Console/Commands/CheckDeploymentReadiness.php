<?php

namespace App\Console\Commands;

use App\Models\HeroSection;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\AdminSecurity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CheckDeploymentReadiness extends Command
{
    protected $signature = 'deploy:check {--production : Wajibkan seluruh konfigurasi production}';

    protected $description = 'Periksa kesiapan source, database, storage, scheduler, dan environment deployment.';

    /** @var array<int, array{string, string, string}> */
    private array $rows = [];

    private int $failures = 0;

    public function handle(): int
    {
        $requiredExtensions = ['pdo_mysql', 'dom', 'fileinfo', 'openssl', 'xmlreader', 'zip'];
        $missingExtensions = array_values(array_filter(
            $requiredExtensions,
            fn (string $extension): bool => ! extension_loaded($extension),
        ));

        $this->check(
            'PHP minimal 8.3',
            PHP_VERSION_ID >= 80300,
            'Gunakan PHP 8.3 atau lebih baru pada web server, CLI, dan cron.',
        );
        $this->check(
            'Ekstensi PHP hosting lengkap',
            $missingExtensions === [],
            $missingExtensions === [] ? '' : 'Belum tersedia: '.implode(', ', $missingExtensions).'.',
        );
        $this->check('APP_KEY tersedia', filled(config('app.key')));
        $this->check('Vite manifest tersedia', file_exists(public_path('build/manifest.json')));
        $this->check('public/hot tidak ada', ! file_exists(public_path('hot')), 'Hapus marker Vite development sebelum release.');
        $this->check('Storage public terhubung', $this->storageLinkIsValid(), 'Jalankan php artisan storage:link.');
        $this->check('storage writable', is_writable(storage_path()));
        $this->check('bootstrap/cache writable', is_writable(base_path('bootstrap/cache')));
        $databaseReady = $this->databaseIsReachable();
        $this->check('Database dapat diakses', $databaseReady);
        $this->check('Tidak ada migration tertunda', $databaseReady && $this->pendingMigrations() === []);
        $this->check('Admin aktif tersedia', $databaseReady && User::where('is_admin', true)->where('is_active', true)->exists());
        $this->check('SiteSetting singleton', $databaseReady && SiteSetting::count() === 1);
        $this->check('Hero singleton', $databaseReady && HeroSection::count() === 1);
        $this->check('Nomor WhatsApp tersedia', filled(config('company.whatsapp_number')));
        $this->check('Command purge Agenda tersedia', array_key_exists('agendas:purge', Artisan::all()));
        $this->check('Command purge operasional tersedia', array_key_exists('operational:purge', Artisan::all()));
        $this->check('Command security scan tersedia', array_key_exists('security:scan', Artisan::all()));
        $this->check('Dokumentasi deployment tersedia', file_exists(base_path('docs/DEPLOYMENT.md')));
        $this->check('Security log channel tersedia', config('logging.channels.security.driver') === 'daily');
        $this->check('Idle timeout admin maksimal 30 menit', (int) config('admin.session_idle_seconds') > 0
            && (int) config('admin.session_idle_seconds') <= 1800);
        $this->check('Absolute timeout admin maksimal 8 jam', (int) config('admin.session_absolute_seconds') > 0
            && (int) config('admin.session_absolute_seconds') <= 28800);

        if ($this->option('production')) {
            $url = (string) config('app.url');
            $appHost = strtolower((string) parse_url($url, PHP_URL_HOST));
            $sessionDomain = strtolower(ltrim((string) config('session.domain'), '.'));
            $sessionDomainMatches = $sessionDomain === ''
                || $sessionDomain === $appHost
                || str_ends_with($appHost, '.'.$sessionDomain);
            $dbUser = strtolower((string) config('database.connections.'.config('database.default').'.username'));
            $defaultLogChannel = (string) config('logging.default');
            $logLevel = strtolower((string) config("logging.channels.{$defaultLogChannel}.level"));

            $this->check('APP_ENV=production', app()->environment('production'));
            $this->check('APP_DEBUG=false', ! config('app.debug'));
            $this->check('APP_URL memakai HTTPS', str_starts_with($url, 'https://') && ! str_contains($url, 'example.com'));
            $this->check(
                'Domain cookie sesuai APP_URL',
                $sessionDomainMatches,
                'SESSION_DOMAIN harus kosong, sama dengan host APP_URL, atau parent domain-nya.',
            );
            $this->check('Database memakai MySQL', config('database.default') === 'mysql');
            $this->check('Session terenkripsi', (bool) config('session.encrypt'));
            $this->check('Cookie session secure', (bool) config('session.secure'));
            $this->check('Session persistent server-side', in_array(config('session.driver'), ['database', 'redis'], true));
            $this->check('CSP enforcement aktif', (bool) config('security.csp_enabled'));
            $this->check('Filter anti judol aktif', (bool) config('admin.block_gambling_content'));
            $this->check('HSTS aktif', (bool) config('security.hsts_enabled'));
            $this->check('User database bukan root', $dbUser !== '' && ! in_array($dbUser, ['root', 'sa', 'postgres'], true));
            $this->check('Log production bukan debug', $logLevel !== '' && $logLevel !== 'debug');
            $this->check('Password bootstrap admin kuat atau sudah dihapus', $this->seedPasswordIsSafe());
        }

        $this->table(['Status', 'Pemeriksaan', 'Catatan'], $this->rows);

        if (! file_exists(base_path('.git/HEAD'))) {
            $this->warn('Catatan: metadata Git project tidak mandiri. Pastikan release hanya mengambil folder project ini.');
        }

        if ($this->failures > 0) {
            $this->error("Deployment check gagal: {$this->failures} item belum memenuhi syarat.");

            return self::FAILURE;
        }

        $this->info('Deployment check aplikasi lulus.');
        if ($this->option('production')) {
            $this->warn(
                'Gate eksternal tetap wajib diverifikasi: document root public/, sertifikat TLS, cron scheduler, '
                .'konfigurasi web server, backup database/upload, dan smoke test melalui domain asli.'
            );
        }

        return self::SUCCESS;
    }

    private function check(string $label, bool $passed, string $failureNote = ''): void
    {
        $this->rows[] = [$passed ? 'PASS' : 'FAIL', $label, $passed ? '' : $failureNote];
        if (! $passed) {
            $this->failures++;
        }
    }

    private function storageLinkIsValid(): bool
    {
        $public = realpath(public_path('storage'));
        $target = realpath(storage_path('app/public'));

        return $public !== false && $target !== false && strcasecmp($public, $target) === 0;
    }

    private function databaseIsReachable(): bool
    {
        try {
            DB::select('SELECT 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array<int, string> */
    private function pendingMigrations(): array
    {
        try {
            $ran = DB::table('migrations')->pluck('migration')->all();
            $files = collect(glob(database_path('migrations/*.php')) ?: [])
                ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME));

            return $files->diff($ran)->values()->all();
        } catch (\Throwable) {
            return ['database-unavailable'];
        }
    }

    private function seedPasswordIsSafe(): bool
    {
        $password = config('admin.seed.password');
        if (! filled($password)) {
            return true;
        }

        return ! Validator::make(
            ['password' => $password],
            ['password' => ['required', AdminSecurity::passwordRule()]],
        )->fails();
    }
}
