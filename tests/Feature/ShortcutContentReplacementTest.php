<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ContentReplacementRun;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ShortcutContentReplacementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'is_admin' => true,
            'name' => 'Admin Shortcut',
            'email' => 'shortcut@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    public function test_shortcut_routes_menu_and_access_are_integrated(): void
    {
        foreach (['admin.shortcuts.index', 'admin.shortcuts.preview', 'admin.shortcuts.apply', 'admin.shortcuts.undo'] as $name) {
            $this->assertTrue(Route::has($name));
        }

        $this->get(route('admin.shortcuts.index'))->assertNotFound();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.shortcuts.index'))
            ->assertOk()
            ->assertSeeText('Shortcut')
            ->assertSeeText('Visi & Misi')
            ->assertSeeText('Layanan')
            ->assertSeeText('Artikel')
            ->assertSee('href="'.route('admin.shortcuts.index').'"', false);
    }

    public function test_preview_does_not_mutate_and_apply_replaces_one_hundred_articles(): void
    {
        foreach (range(1, 100) as $number) {
            Article::create([
                'title' => "Artikel {$number}",
                'slug' => "artikel-{$number}-uu-lama",
                'content' => 'Dasar hukum yang digunakan adalah UU Lama dan tetap berlaku.',
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        $this->preview([
            'cluster' => 'articles',
            'search_text' => 'UU Lama',
            'replacement_text' => 'UU Baru',
        ])->assertRedirect(route('admin.shortcuts.index'));

        $this->assertSame(100, Article::where('content', 'like', '%UU Lama%')->count());
        $state = session('shortcut_preview');
        $this->assertSame(100, $state['result']['affected_records']);
        $this->assertSame(100, $state['result']['affected_fields']);
        $this->assertSame(100, $state['result']['occurrence_count']);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.shortcuts.index'))
            ->assertOk()
            ->assertSeeText('Dampak Perubahan')
            ->assertSeeText('Terapkan 100 Penggantian');

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.shortcuts.apply'), ['preview_token' => $state['token']])
            ->assertRedirect(route('admin.shortcuts.index'))
            ->assertSessionHas('ok');

        $this->assertSame(0, Article::where('content', 'like', '%UU Lama%')->count());
        $this->assertSame(100, Article::where('content', 'like', '%UU Baru%')->count());
        $this->assertSame(100, Article::where('slug', 'like', '%uu-lama')->count(), 'Slug teknis tidak boleh ikut diganti.');

        $run = ContentReplacementRun::firstOrFail();
        $this->assertSame(100, $run->changes()->count());
        $this->assertSame($this->admin->id, $run->created_by);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.shortcuts.index'))
            ->assertOk()
            ->assertSeeText('Riwayat Perubahan')
            ->assertSeeText('100 kemunculan');
    }

    public function test_cluster_scope_isolated_and_case_insensitive_mode_works(): void
    {
        $service = Service::create([
            'title' => 'Layanan Berbahaya',
            'slug' => 'layanan-berbahaya',
            'description' => 'BERBAHAYA bila tanpa pendampingan.',
            'display_order' => 1,
            'is_active' => true,
        ]);
        $article = Article::create([
            'title' => 'Artikel Berbahaya',
            'slug' => 'artikel-berbahaya',
            'content' => 'Berbahaya bila diabaikan.',
            'status' => 'draft',
        ]);

        $this->preview([
            'cluster' => 'services',
            'search_text' => 'berbahaya',
            'replacement_text' => 'menyenangkan',
            'case_sensitive' => '0',
        ]);
        $this->applyPreview();

        $service->refresh();
        $article->refresh();
        $this->assertSame('Layanan menyenangkan', $service->title);
        $this->assertSame('menyenangkan bila tanpa pendampingan.', $service->description);
        $this->assertSame('Artikel Berbahaya', $article->title);
        $this->assertSame('layanan-berbahaya', $service->slug);
    }

    public function test_stale_preview_is_rejected_without_partial_write(): void
    {
        $article = Article::create([
            'title' => 'Aturan Lama',
            'slug' => 'aturan-lama',
            'content' => 'Mengacu pada UU Lama.',
            'status' => 'draft',
        ]);

        $this->preview([
            'cluster' => 'articles',
            'search_text' => 'UU Lama',
            'replacement_text' => 'UU Baru',
        ]);
        $state = session('shortcut_preview');

        $article->update(['content' => 'Konten diedit manual dan masih menyebut UU Lama.']);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.shortcuts.apply'), ['preview_token' => $state['token']])
            ->assertRedirect(route('admin.shortcuts.index'))
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'Konten berubah'));

        $this->assertSame('Konten diedit manual dan masih menyebut UU Lama.', $article->fresh()->content);
        $this->assertDatabaseCount('content_replacement_runs', 0);
    }

    public function test_undo_restores_unchanged_fields_but_preserves_newer_edits(): void
    {
        $first = Article::create([
            'title' => 'Dokumen Satu', 'slug' => 'dokumen-satu',
            'content' => 'Rujukan UU Lama.', 'status' => 'draft',
        ]);
        $second = Article::create([
            'title' => 'Dokumen Dua', 'slug' => 'dokumen-dua',
            'content' => 'Rujukan UU Lama.', 'status' => 'draft',
        ]);

        $this->preview([
            'cluster' => 'articles',
            'search_text' => 'UU Lama',
            'replacement_text' => 'UU Baru',
        ]);
        $this->applyPreview();
        $run = ContentReplacementRun::firstOrFail();

        $second->update(['content' => 'Perubahan manual terbaru.']);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.shortcuts.undo', $run))
            ->assertRedirect(route('admin.shortcuts.index'))
            ->assertSessionHas('error', fn ($message) => str_contains($message, '1 kolom dilewati'));

        $this->assertSame('Rujukan UU Lama.', $first->fresh()->content);
        $this->assertSame('Perubahan manual terbaru.', $second->fresh()->content);
        $this->assertSame('partially_reverted', $run->fresh()->status);
        $this->assertSame(1, $run->changes()->whereNotNull('reverted_at')->count());

        $second->update(['content' => 'Rujukan UU Baru.']);
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.shortcuts.undo', $run))
            ->assertSessionHas('ok');

        $this->assertSame('Rujukan UU Lama.', $second->fresh()->content);
        $this->assertSame('reverted', $run->fresh()->status);
    }

    public function test_judol_can_be_searched_for_cleanup_but_cannot_be_published_as_replacement(): void
    {
        Article::create([
            'title' => 'Konten terinfeksi',
            'slug' => 'konten-terinfeksi',
            'content' => 'Sisipan slot gacor harus dibersihkan.',
            'status' => 'draft',
        ]);

        $this->preview([
            'cluster' => 'articles',
            'search_text' => 'slot gacor',
            'replacement_text' => 'konten aman',
        ])->assertSessionHasNoErrors();

        $this->preview([
            'cluster' => 'articles',
            'search_text' => 'konten aman',
            'replacement_text' => 'slot gacor',
        ])->assertSessionHasErrors('replacement_text');
    }

    public function test_invalid_token_short_phrase_and_html_are_rejected(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.shortcuts.apply'), ['preview_token' => str_repeat('x', 64)])
            ->assertSessionHas('error');

        $this->preview([
            'cluster' => 'all',
            'search_text' => 'a',
            'replacement_text' => 'b',
        ])->assertSessionHasErrors('search_text');

        $this->preview([
            'cluster' => 'articles',
            'search_text' => 'teks lama',
            'replacement_text' => '<script>alert(1)</script>',
        ])->assertSessionHasErrors('replacement_text');
    }

    private function preview(array $overrides)
    {
        return $this->actingAs($this->admin, 'admin')->post(route('admin.shortcuts.preview'), array_merge([
            'cluster' => 'articles',
            'search_text' => 'UU Lama',
            'replacement_text' => 'UU Baru',
            'case_sensitive' => '1',
        ], $overrides));
    }

    private function applyPreview(): void
    {
        $state = session('shortcut_preview');
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.shortcuts.apply'), ['preview_token' => $state['token']])
            ->assertRedirect(route('admin.shortcuts.index'))
            ->assertSessionHas('ok');
    }
}
