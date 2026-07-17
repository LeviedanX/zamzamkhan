<?php

namespace App\Console\Commands;

use App\Support\ImageUploadSecurity;
use App\Support\SuspiciousContent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ScanSecurityIntegrity extends Command
{
    protected $signature = 'security:scan {--json : Keluarkan hasil sebagai JSON}';

    protected $description = 'Deteksi webshell/upload berbahaya, SEO-spam judol, dan stored injection pada konten CMS.';

    /** @var list<array{type:string, location:string, detail:string}> */
    private array $findings = [];

    public function handle(): int
    {
        $this->scanPublicPhpFiles();
        $this->scanPublicUploads();
        $this->scanCmsContent();
        $this->checkDevelopmentMarker();

        if ($this->option('json')) {
            $this->line(json_encode([
                'ok' => $this->findings === [],
                'findings' => $this->findings,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } elseif ($this->findings === []) {
            $this->info('Security scan lulus: tidak ada indikator webshell, sisipan aktif, atau spam judol.');
        } else {
            $this->table(
                ['Jenis', 'Lokasi', 'Detail'],
                array_map(fn (array $finding) => array_values($finding), $this->findings),
            );
            $this->error('Security scan menemukan '.count($this->findings).' masalah.');
        }

        if ($this->findings !== []) {
            Log::channel('security')->critical('security_integrity_scan_failed', [
                'findings' => $this->findings,
            ]);
        }

        return $this->findings === [] ? self::SUCCESS : self::FAILURE;
    }

    private function scanPublicPhpFiles(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(public_path(), \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            $relative = str_replace('\\', '/', substr($path, strlen(public_path()) + 1));
            $extension = strtolower($file->getExtension());

            if (in_array($extension, ['php', 'phtml', 'phar', 'cgi', 'pl', 'py', 'sh'], true)
                && $relative !== 'index.php') {
                $this->add('executable-public-file', 'public/'.$relative, 'File executable tidak boleh berada di public.');
            }
        }
    }

    private function scanPublicUploads(): void
    {
        $disk = Storage::disk('public');

        foreach ($disk->allFiles() as $path) {
            if (basename($path) === '.gitignore' || basename($path) === '.htaccess') {
                continue;
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $this->add('forbidden-upload-extension', 'storage/app/public/'.$path, 'Ekstensi upload tidak diizinkan.');

                continue;
            }

            try {
                ImageUploadSecurity::inspectPath($disk->path($path));
            } catch (\Throwable $e) {
                $this->add('suspicious-upload', 'storage/app/public/'.$path, $e->getMessage());
            }
        }
    }

    private function scanCmsContent(): void
    {
        $targets = [
            'site_settings' => ['company_name', 'tagline', 'company_description', 'vision', 'mission', 'address', 'operating_hours'],
            'hero_sections' => ['title', 'subtitle', 'badge_text', 'trust_text', 'service_chips', 'portrait_alt', 'portrait_role', 'portrait_name'],
            'services' => ['title', 'summary', 'description', 'benefits', 'suitable_for', 'workflow_steps', 'whatsapp_message'],
            'faqs' => ['question', 'answer'],
            'advantages' => ['title', 'description'],
            'statistics' => ['value', 'label', 'description'],
            'clients' => ['name', 'industry'],
            'testimonials' => ['client_name', 'service_name', 'content', 'image_alt'],
            'agendas' => ['title', 'summary', 'description', 'venue'],
            'articles' => ['title', 'excerpt', 'content', 'cover_alt', 'meta_title', 'meta_description', 'og_title', 'og_description'],
            'business_process_statuses' => ['name'],
            'seo_settings' => ['meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description'],
        ];

        foreach ($targets as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $available = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn($table, $column)));
            if ($available === []) {
                continue;
            }

            foreach (DB::table($table)->select(array_merge(['id'], $available))->orderBy('id')->cursor() as $row) {
                foreach ($available as $column) {
                    $value = $row->{$column};
                    if (! is_string($value) || $value === '') {
                        continue;
                    }

                    if ($reason = SuspiciousContent::reason($value, true)) {
                        $this->add(
                            $reason,
                            "{$table}#{$row->id}.{$column}",
                            'Konten terindikasi sisipan aktif atau spam perjudian.',
                        );
                    }
                }
            }
        }
    }

    private function checkDevelopmentMarker(): void
    {
        if (file_exists(public_path('hot'))) {
            $this->add('development-marker', 'public/hot', 'Marker Vite development tidak boleh ada pada production.');
        }
    }

    private function add(string $type, string $location, string $detail): void
    {
        $this->findings[] = compact('type', 'location', 'detail');
    }
}
