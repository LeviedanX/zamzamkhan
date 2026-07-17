<?php

namespace App\Services;

use App\Models\Advantage;
use App\Models\Agenda;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Client;
use App\Models\ContentReplacementChange;
use App\Models\ContentReplacementRun;
use App\Models\Faq;
use App\Models\HeroSection;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Statistic;
use App\Models\Testimonial;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContentReplacementService
{
    private const MAX_CHANGED_FIELDS = 10000;

    private const MAX_OCCURRENCES = 100000;

    private const SAMPLE_LIMIT = 12;

    /**
     * Sumber dan kolom di bawah adalah allowlist keamanan. Kolom identitas teknis
     * seperti slug, URL, path, status, dan primary key tidak pernah dapat diubah.
     */
    private const SOURCES = [
        'profile' => [
            'label' => 'Profil & Identitas',
            'model' => SiteSetting::class,
            'fields' => [
                'company_name' => 'Nama perusahaan', 'brand_name' => 'Nama merek',
                'consultant_name' => 'Nama konsultan', 'tagline' => 'Tagline',
                'company_description' => 'Deskripsi perusahaan', 'address' => 'Alamat',
                'operating_hours' => 'Jam operasional',
            ],
        ],
        'vision_mission' => [
            'label' => 'Visi & Misi',
            'model' => SiteSetting::class,
            'fields' => ['vision' => 'Visi', 'mission' => 'Misi'],
        ],
        'hero' => [
            'label' => 'Hero Utama',
            'model' => HeroSection::class,
            'fields' => [
                'title' => 'Judul', 'subtitle' => 'Subjudul',
                'primary_button_text' => 'Tombol utama', 'secondary_button_text' => 'Tombol kedua',
                'badge_text' => 'Badge', 'trust_text' => 'Teks kepercayaan',
                'portrait_alt' => 'Alt potret', 'portrait_role' => 'Peran konsultan',
                'portrait_name' => 'Nama konsultan',
            ],
        ],
        'services' => [
            'label' => 'Layanan',
            'model' => Service::class,
            'fields' => [
                'title' => 'Judul', 'summary' => 'Ringkasan', 'description' => 'Deskripsi',
                'benefits' => 'Manfaat', 'suitable_for' => 'Cocok untuk',
                'workflow_steps' => 'Tahapan', 'whatsapp_message' => 'Pesan WhatsApp',
            ],
        ],
        'advantages' => [
            'label' => 'Keunggulan',
            'model' => Advantage::class,
            'fields' => ['title' => 'Judul', 'description' => 'Deskripsi'],
        ],
        'statistics' => [
            'label' => 'Statistik',
            'model' => Statistic::class,
            'fields' => ['value' => 'Nilai', 'label' => 'Label', 'description' => 'Deskripsi'],
        ],
        'clients' => [
            'label' => 'Klien',
            'model' => Client::class,
            'fields' => ['name' => 'Nama', 'industry' => 'Industri'],
        ],
        'testimonials' => [
            'label' => 'Testimoni',
            'model' => Testimonial::class,
            'fields' => [
                'client_name' => 'Nama klien', 'service_name' => 'Nama layanan',
                'content' => 'Isi testimoni', 'image_alt' => 'Alt gambar',
            ],
        ],
        'agendas' => [
            'label' => 'Agenda',
            'model' => Agenda::class,
            'fields' => [
                'title' => 'Judul', 'summary' => 'Ringkasan',
                'description' => 'Deskripsi', 'venue' => 'Lokasi',
            ],
        ],
        'faqs' => [
            'label' => 'FAQ',
            'model' => Faq::class,
            'fields' => ['question' => 'Pertanyaan', 'answer' => 'Jawaban'],
        ],
        'article_categories' => [
            'label' => 'Kategori Artikel',
            'model' => ArticleCategory::class,
            'fields' => ['name' => 'Nama kategori'],
        ],
        'articles' => [
            'label' => 'Artikel',
            'model' => Article::class,
            'fields' => [
                'title' => 'Judul', 'excerpt' => 'Ringkasan', 'content' => 'Isi artikel',
                'cover_alt' => 'Alt sampul',
            ],
        ],
        'article_seo' => [
            'label' => 'SEO Artikel',
            'model' => Article::class,
            'fields' => [
                'meta_title' => 'Meta title', 'meta_description' => 'Meta description',
                'og_title' => 'OG title', 'og_description' => 'OG description',
            ],
        ],
        'site_seo' => [
            'label' => 'SEO Website',
            'model' => SeoSetting::class,
            'fields' => [
                'meta_title' => 'Meta title', 'meta_description' => 'Meta description',
                'meta_keywords' => 'Meta keywords', 'og_title' => 'OG title',
                'og_description' => 'OG description',
            ],
        ],
    ];

    private const CLUSTERS = [
        'all' => ['label' => 'Seluruh Konten Website', 'sources' => ['profile', 'vision_mission', 'hero', 'services', 'advantages', 'statistics', 'clients', 'testimonials', 'agendas', 'faqs', 'article_categories', 'articles', 'article_seo', 'site_seo']],
        'profile' => ['label' => 'Profil & Identitas', 'sources' => ['profile']],
        'vision_mission' => ['label' => 'Visi & Misi', 'sources' => ['vision_mission']],
        'hero' => ['label' => 'Hero Utama', 'sources' => ['hero']],
        'services' => ['label' => 'Layanan', 'sources' => ['services']],
        'advantages' => ['label' => 'Keunggulan', 'sources' => ['advantages']],
        'statistics' => ['label' => 'Statistik', 'sources' => ['statistics']],
        'clients' => ['label' => 'Klien', 'sources' => ['clients']],
        'testimonials' => ['label' => 'Testimoni', 'sources' => ['testimonials']],
        'agendas' => ['label' => 'Agenda', 'sources' => ['agendas']],
        'faqs' => ['label' => 'FAQ', 'sources' => ['faqs']],
        'articles' => ['label' => 'Artikel', 'sources' => ['article_categories', 'articles', 'article_seo']],
        'seo' => ['label' => 'SEO Website & Artikel', 'sources' => ['site_seo', 'article_seo']],
    ];

    public function clusters(): array
    {
        return collect(self::CLUSTERS)->map(fn (array $cluster) => $cluster['label'])->all();
    }

    public function clusterLabel(string $cluster): string
    {
        return self::CLUSTERS[$cluster]['label'] ?? $cluster;
    }

    public function preview(string $cluster, string $search, string $replacement, bool $caseSensitive): array
    {
        return $this->scan($cluster, $search, $replacement, $caseSensitive, false, false);
    }

    public function execute(
        string $cluster,
        string $search,
        string $replacement,
        bool $caseSensitive,
        string $expectedFingerprint,
        ?int $adminId,
    ): ContentReplacementRun {
        return DB::transaction(function () use ($cluster, $search, $replacement, $caseSensitive, $expectedFingerprint, $adminId) {
            $result = $this->scan($cluster, $search, $replacement, $caseSensitive, true, true);

            if (! hash_equals($expectedFingerprint, $result['fingerprint'])) {
                throw new DomainException('Konten berubah setelah pratinjau dibuat. Buat pratinjau baru sebelum menerapkan perubahan.');
            }

            if ($result['affected_fields'] === 0) {
                throw new DomainException('Teks yang dicari sudah tidak ditemukan.');
            }

            $run = ContentReplacementRun::create([
                'cluster' => $cluster,
                'search_text' => $search,
                'replacement_text' => $replacement,
                'case_sensitive' => $caseSensitive,
                'affected_records' => $result['affected_records'],
                'affected_fields' => $result['affected_fields'],
                'occurrence_count' => $result['occurrence_count'],
                'status' => 'completed',
                'created_by' => $adminId,
                'executed_at' => now(),
            ]);

            $grouped = collect($result['changes'])->groupBy(fn (array $change) => $change['model'].'#'.$change['record_id']);

            foreach ($grouped as $changes) {
                $first = $changes->first();
                /** @var Model|null $record */
                $record = $first['model']::query()->lockForUpdate()->find($first['record_id']);
                if (! $record) {
                    throw new DomainException('Salah satu konten tidak lagi tersedia. Buat pratinjau baru.');
                }

                $record->forceFill($changes->pluck('after', 'field')->all())->save();
            }

            foreach (array_chunk($result['changes'], 500) as $chunk) {
                ContentReplacementChange::insert(array_map(fn (array $change) => [
                    'content_replacement_run_id' => $run->id,
                    'source_key' => $change['source'],
                    'record_id' => $change['record_id'],
                    'column_name' => $change['field'],
                    'before_text' => $change['before'],
                    'after_text' => $change['after'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $chunk));
            }

            return $run;
        }, 3);
    }

    public function undo(ContentReplacementRun $run, ?int $adminId): array
    {
        return DB::transaction(function () use ($run, $adminId) {
            $run = ContentReplacementRun::query()->lockForUpdate()->findOrFail($run->id);
            $changes = $run->changes()->whereNull('reverted_at')->lockForUpdate()->get();

            if ($changes->isEmpty()) {
                return ['restored' => 0, 'conflicts' => 0, 'status' => 'reverted'];
            }

            $restored = 0;
            $conflicts = 0;

            foreach ($changes->groupBy(fn (ContentReplacementChange $change) => $change->source_key.'#'.$change->record_id) as $recordChanges) {
                $first = $recordChanges->first();
                $source = self::SOURCES[$first->source_key] ?? null;
                if (! $source) {
                    $conflicts += $recordChanges->count();

                    continue;
                }

                /** @var Model|null $record */
                $record = $source['model']::query()->lockForUpdate()->find($first->record_id);
                if (! $record) {
                    $conflicts += $recordChanges->count();

                    continue;
                }

                $restore = [];
                $restoredChanges = [];
                foreach ($recordChanges as $change) {
                    $current = $record->getRawOriginal($change->column_name);
                    if ((string) $current !== (string) $change->after_text) {
                        $conflicts++;

                        continue;
                    }

                    $restore[$change->column_name] = $change->before_text;
                    $restoredChanges[] = $change;
                }

                if ($restore !== []) {
                    $record->forceFill($restore)->save();
                    foreach ($restoredChanges as $change) {
                        $change->update(['reverted_at' => now()]);
                        $restored++;
                    }
                }
            }

            $remaining = $run->changes()->whereNull('reverted_at')->count();
            $status = $remaining === 0 ? 'reverted' : 'partially_reverted';
            $run->update([
                'status' => $status,
                'reverted_by' => $adminId,
                'reverted_at' => now(),
            ]);

            return compact('restored', 'conflicts', 'status');
        }, 3);
    }

    private function scan(
        string $cluster,
        string $search,
        string $replacement,
        bool $caseSensitive,
        bool $includeChanges,
        bool $lock,
    ): array {
        if (! isset(self::CLUSTERS[$cluster])) {
            throw new DomainException('Kluster konten tidak valid.');
        }

        $samples = [];
        $changes = [];
        $records = [];
        $fieldCount = 0;
        $occurrences = 0;
        $fingerprint = hash_init('sha256');

        foreach (self::CLUSTERS[$cluster]['sources'] as $sourceKey) {
            $source = self::SOURCES[$sourceKey];
            $query = $source['model']::query()->orderBy('id');
            if ($lock) {
                $query->lockForUpdate();
            }

            foreach ($query->get() as $record) {
                foreach ($source['fields'] as $field => $fieldLabel) {
                    $raw = $record->getRawOriginal($field);
                    if ($raw === null || $raw === '') {
                        continue;
                    }

                    $before = (string) $raw;
                    $count = 0;
                    $after = $caseSensitive
                        ? str_replace($search, $replacement, $before, $count)
                        : str_ireplace($search, $replacement, $before, $count);

                    if ($count === 0 || $after === $before) {
                        continue;
                    }

                    $fieldCount++;
                    $occurrences += $count;
                    $records[$source['model'].'#'.$record->getKey()] = true;
                    hash_update($fingerprint, $sourceKey."\0".$record->getKey()."\0".$field."\0".hash('sha256', $before)."\0");

                    if (count($samples) < self::SAMPLE_LIMIT) {
                        $samples[] = [
                            'source' => $source['label'],
                            'record_id' => $record->getKey(),
                            'field' => $fieldLabel,
                            'before' => $this->excerptAround($before, $search, $caseSensitive),
                            'after' => $this->excerptAround($after, $replacement, $caseSensitive),
                            'occurrences' => $count,
                        ];
                    }

                    if ($includeChanges) {
                        $changes[] = [
                            'source' => $sourceKey,
                            'model' => $source['model'],
                            'record_id' => $record->getKey(),
                            'field' => $field,
                            'before' => $before,
                            'after' => $after,
                        ];
                    }

                    if ($fieldCount > self::MAX_CHANGED_FIELDS || $occurrences > self::MAX_OCCURRENCES) {
                        throw new DomainException('Cakupan perubahan terlalu besar. Gunakan kluster yang lebih spesifik atau frasa pencarian yang lebih panjang.');
                    }
                }
            }
        }

        return [
            'affected_records' => count($records),
            'affected_fields' => $fieldCount,
            'occurrence_count' => $occurrences,
            'samples' => $samples,
            'fingerprint' => hash_final($fingerprint),
            'changes' => $changes,
        ];
    }

    private function excerptAround(string $text, string $needle, bool $caseSensitive): string
    {
        $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags($text)));
        $position = $needle === '' ? 0 : ($caseSensitive ? mb_strpos($plain, $needle) : mb_stripos($plain, $needle));
        $start = $position === false ? 0 : max(0, $position - 70);
        $excerpt = mb_substr($plain, $start, 210);

        return ($start > 0 ? '…' : '').$excerpt.(mb_strlen($plain) > $start + 210 ? '…' : '');
    }
}
