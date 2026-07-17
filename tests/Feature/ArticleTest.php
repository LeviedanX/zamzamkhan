<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $name = 'Sertifikasi Halal', string $slug = 'sertifikasi-halal'): ArticleCategory
    {
        return ArticleCategory::firstOrCreate(['slug' => $slug], ['name' => $name]);
    }

    private function makeArticle(array $overrides = []): Article
    {
        return Article::create(array_merge([
            'article_category_id' => $this->category()->id,
            'title' => 'Judul Artikel Uji',
            'slug' => 'judul-artikel-uji',
            'excerpt' => 'Ringkasan singkat.',
            'content' => "Paragraf satu.\n\nParagraf dua.",
            'status' => 'published',
            'published_at' => now(),
        ], $overrides));
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Uji',
            'email' => 'admin@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function pngUpload(string $name = 'seo.png'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'zzk').'.png';
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    public function test_halaman_index_artikel_dapat_diakses(): void
    {
        $this->get(route('artikel.index'))->assertOk();
    }

    public function test_artikel_published_tampil_di_index(): void
    {
        $this->makeArticle(['title' => 'Artikel Tampil', 'slug' => 'artikel-tampil']);

        $this->get(route('artikel.index'))
            ->assertOk()
            ->assertSee('Artikel Tampil');
    }

    public function test_artikel_draft_tidak_tampil_di_index(): void
    {
        $this->makeArticle(['title' => 'Artikel Draft', 'slug' => 'artikel-draft', 'status' => 'draft', 'published_at' => null]);

        $this->get(route('artikel.index'))
            ->assertOk()
            ->assertDontSee('Artikel Draft');
    }

    public function test_artikel_dengan_tanggal_terbit_masa_depan_belum_tampil(): void
    {
        $article = $this->makeArticle([
            'title' => 'Artikel Masa Depan',
            'slug' => 'artikel-masa-depan',
            'published_at' => now()->addDay(),
        ]);

        $this->get(route('artikel.index'))->assertOk()->assertDontSee($article->title);
        $this->get(route('artikel.show', $article->slug))->assertNotFound();
        $this->get(route('sitemap'))->assertOk()->assertDontSee($article->slug);
    }

    public function test_detail_artikel_published_dapat_diakses(): void
    {
        $a = $this->makeArticle(['slug' => 'detail-published']);

        $this->get(route('artikel.show', $a->slug))
            ->assertOk()
            ->assertSee($a->title);
    }

    public function test_detail_artikel_draft_menghasilkan_404(): void
    {
        $a = $this->makeArticle(['slug' => 'detail-draft', 'status' => 'draft', 'published_at' => null]);

        $this->get(route('artikel.show', $a->slug))->assertNotFound();
    }

    public function test_slug_tidak_ditemukan_menghasilkan_404(): void
    {
        $this->get(route('artikel.show', 'slug-tidak-ada'))->assertNotFound();
    }

    public function test_pencarian_artikel_berfungsi(): void
    {
        $this->makeArticle(['title' => 'Panduan NIB UMKM', 'slug' => 'panduan-nib']);
        $this->makeArticle(['title' => 'Kiat Ekspor Produk', 'slug' => 'kiat-ekspor-produk']);

        // Assert lewat slug unik pada href kartu, agar tidak terganggu teks JSON-LD/config.
        $res = $this->get(route('artikel.index', ['q' => 'NIB']));
        $res->assertOk()->assertSee('/artikel/panduan-nib')->assertDontSee('/artikel/kiat-ekspor-produk');
    }

    public function test_filter_kategori_berfungsi(): void
    {
        $halal = $this->category('Sertifikasi Halal', 'sertifikasi-halal');
        $haki = $this->category('HAKI', 'haki');

        $this->makeArticle(['article_category_id' => $halal->id, 'title' => 'Topik Halal', 'slug' => 'topik-halal']);
        $this->makeArticle(['article_category_id' => $haki->id, 'title' => 'Topik Haki', 'slug' => 'topik-haki']);

        $this->get(route('artikel.index', ['kategori' => 'haki']))
            ->assertOk()
            ->assertSee('Topik Haki')
            ->assertDontSee('Topik Halal');
    }

    public function test_guest_tidak_dapat_membuka_crud_artikel(): void
    {
        $this->get(route('admin.articles.index'))->assertNotFound();
        $this->get(route('admin.articles.create'))->assertNotFound();
    }

    public function test_validasi_create_artikel_berjalan(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.articles.store'), [])
            ->assertSessionHasErrors(['title', 'article_category_id', 'excerpt', 'content', 'status']);
    }

    public function test_admin_dapat_membuat_artikel_dengan_slug_otomatis(): void
    {
        $cat = $this->category();

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.articles.store'), [
                'title' => 'Artikel Tanpa Slug Manual',
                'article_category_id' => $cat->id,
                'excerpt' => 'Ringkasan.',
                'content' => 'Isi artikel.',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseHas('articles', [
            'title' => 'Artikel Tanpa Slug Manual',
            'slug' => 'artikel-tanpa-slug-manual',
            'status' => 'published',
        ]);
        $this->assertNotNull(Article::where('slug', 'artikel-tanpa-slug-manual')->first()->published_at);
    }

    public function test_slug_duplikat_ditangani_dengan_suffix(): void
    {
        $cat = $this->category();
        $this->makeArticle(['article_category_id' => $cat->id, 'title' => 'Judul Sama', 'slug' => 'judul-sama']);

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.articles.store'), [
                'title' => 'Judul Sama',
                'slug' => '',
                'article_category_id' => $cat->id,
                'excerpt' => 'Ringkasan.',
                'content' => 'Isi.',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseHas('articles', ['slug' => 'judul-sama-2']);
    }

    public function test_admin_dapat_mengedit_seo_lengkap_dan_gambar_sosial_artikel(): void
    {
        Storage::fake('public');
        $article = $this->makeArticle(['slug' => 'seo-lengkap']);

        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'article_category_id' => $article->article_category_id,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'status' => 'published',
                'published_at' => $article->published_at->format('Y-m-d'),
                'meta_title' => 'SEO Artikel Khusus',
                'meta_description' => 'Deskripsi SEO khusus untuk artikel yang sedang diuji.',
                'canonical_url' => 'https://example.com/sumber-utama',
                'seo_robots' => 'noindex, follow',
                'og_title' => 'Judul Berbagi Khusus',
                'og_description' => 'Deskripsi ketika artikel dibagikan ke media sosial.',
                'og_image' => $this->pngUpload(),
                'exclude_from_sitemap' => '1',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $article->refresh();
        $this->assertSame('SEO Artikel Khusus', $article->meta_title);
        $this->assertSame('https://example.com/sumber-utama', $article->canonical_url);
        $this->assertSame('noindex, follow', $article->seo_robots);
        $this->assertTrue($article->exclude_from_sitemap);
        $this->assertNotNull($article->og_image_path);
        Storage::disk('public')->assertExists($article->og_image_path);
    }

    public function test_url_canonical_artikel_menolak_skema_non_http(): void
    {
        $article = $this->makeArticle(['slug' => 'canonical-aman']);

        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'article_category_id' => $article->article_category_id,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'status' => 'published',
                'canonical_url' => 'javascript:alert(1)',
                'seo_robots' => 'index, follow',
            ])
            ->assertSessionHasErrors('canonical_url');
    }

    public function test_detail_artikel_merender_metadata_dan_schema_seo_per_artikel(): void
    {
        $article = $this->makeArticle([
            'slug' => 'metadata-artikel',
            'meta_title' => 'SEO Artikel Khusus',
            'meta_description' => 'Deskripsi hasil pencarian khusus.',
            'seo_robots' => 'index, follow',
            'og_title' => 'Judul Open Graph Khusus',
            'og_description' => 'Deskripsi Open Graph khusus.',
            'cover_alt' => 'Ilustrasi artikel khusus',
        ]);

        $content = $this->get(route('artikel.show', $article->slug))->assertOk()->getContent();

        $this->assertStringContainsString('<title>SEO Artikel Khusus</title>', $content);
        $this->assertStringContainsString('<meta name="description" content="Deskripsi hasil pencarian khusus.">', $content);
        $this->assertStringContainsString('content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1"', $content);
        $this->assertStringContainsString('<meta property="og:title" content="Judul Open Graph Khusus">', $content);
        $this->assertStringContainsString('<meta property="og:image:alt" content="Ilustrasi artikel khusus">', $content);
        $this->assertStringContainsString('"@type": "BlogPosting"', $content);
        $this->assertStringContainsString('"@type": "BreadcrumbList"', $content);

        preg_match('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $content, $matches);
        $schema = json_decode(trim($matches[1] ?? ''), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('https://schema.org', $schema['@context']);
        $this->assertContains('BlogPosting', array_column($schema['@graph'], '@type'));
    }

    public function test_sitemap_hanya_memuat_artikel_indexable_dengan_self_canonical(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('articles/terlihat.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));
        $visible = $this->makeArticle([
            'title' => 'Artikel & Terlihat',
            'slug' => 'artikel-terlihat',
            'cover_image' => 'articles/terlihat.png',
        ]);
        $this->makeArticle(['slug' => 'artikel-noindex', 'seo_robots' => 'noindex, follow']);
        $this->makeArticle(['slug' => 'artikel-dikecualikan', 'exclude_from_sitemap' => true]);
        $this->makeArticle(['slug' => 'artikel-canonical-lain', 'canonical_url' => 'https://example.com/asli']);

        $content = $this->get(route('sitemap'))->assertOk()->getContent();

        $this->assertStringContainsString(route('artikel.show', $visible->slug), $content);
        $this->assertStringContainsString('Artikel &amp; Terlihat', $content);
        $this->assertStringNotContainsString('artikel-noindex', $content);
        $this->assertStringNotContainsString('artikel-dikecualikan', $content);
        $this->assertStringNotContainsString('artikel-canonical-lain', $content);
        $this->assertStringNotContainsString('<changefreq>', $content);
        $this->assertStringNotContainsString('<priority>', $content);
        $this->assertNotFalse(simplexml_load_string($content));
    }

    public function test_hasil_pencarian_artikel_diberi_noindex(): void
    {
        $content = $this->get(route('artikel.index', ['q' => 'halal']))->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $content);
    }
}
