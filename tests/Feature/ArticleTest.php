<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->get(route('admin.articles.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.articles.create'))->assertRedirect(route('admin.login'));
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
}
