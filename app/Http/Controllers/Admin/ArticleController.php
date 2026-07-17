<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\PublicMedia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $articles = Article::query()
            ->with('category')
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%{$q}%"))
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.articles.index', compact('articles', 'q'));
    }

    public function create()
    {
        return view('admin.articles.form', [
            'article' => new Article,
            'categories' => ArticleCategory::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        $data['slug'] = Article::uniqueSlug(($data['slug'] ?? '') ?: $data['title']);
        $data = $this->applyPublishedAt($data);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = PublicMedia::store($request->file('cover_image'), 'articles');
        }
        if ($request->hasFile('og_image')) {
            $data['og_image_path'] = PublicMedia::store($request->file('og_image'), 'articles/seo');
        }

        try {
            Article::create($data);
        } catch (\Throwable $e) {
            PublicMedia::delete($data['cover_image'] ?? null);
            PublicMedia::delete($data['og_image_path'] ?? null);
            throw $e;
        }

        return redirect()->route('admin.articles.index')->with('ok', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article $article)
    {
        return view('admin.articles.form', [
            'article' => $article,
            'categories' => ArticleCategory::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $data = $this->validated($request, $article);
        $data['slug'] = Article::uniqueSlug(($data['slug'] ?? '') ?: $data['title'], $article->id);
        $data = $this->applyPublishedAt($data, $article);

        $oldCover = $article->cover_image;
        $oldOgImage = $article->og_image_path;
        $newCover = null;
        $newOgImage = null;
        if ($request->boolean('remove_cover_image')) {
            $data['cover_image'] = null;
        } elseif ($request->hasFile('cover_image')) {
            $newCover = PublicMedia::store($request->file('cover_image'), 'articles');
            $data['cover_image'] = $newCover;
        }
        if ($request->boolean('remove_og_image')) {
            $data['og_image_path'] = null;
        } elseif ($request->hasFile('og_image')) {
            $newOgImage = PublicMedia::store($request->file('og_image'), 'articles/seo');
            $data['og_image_path'] = $newOgImage;
        }

        try {
            $article->update($data);
        } catch (\Throwable $e) {
            PublicMedia::delete($newCover);
            PublicMedia::delete($newOgImage);
            throw $e;
        }

        if (($newCover || $request->boolean('remove_cover_image')) && $oldCover !== $newCover) {
            PublicMedia::delete($oldCover);
        }
        if (($newOgImage || $request->boolean('remove_og_image')) && $oldOgImage !== $newOgImage) {
            PublicMedia::delete($oldOgImage);
        }

        return redirect()->route('admin.articles.index')->with('ok', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        $cover = $article->cover_image;
        $ogImage = $article->og_image_path;
        $article->delete();
        PublicMedia::delete($cover);
        PublicMedia::delete($ogImage);

        return back()->with('ok', 'Artikel berhasil dihapus.');
    }

    private function validated(Request $request, ?Article $article): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'alpha_dash', 'max:200', Rule::unique('articles', 'slug')->ignore($article?->id)],
            'article_category_id' => ['required', Rule::exists('article_categories', 'id')],
            'excerpt' => ['required', 'string', 'max:350'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'cover_alt' => ['nullable', 'string', 'max:180'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:2048'],
            'seo_robots' => ['nullable', Rule::in(['index, follow', 'noindex, follow', 'noindex, nofollow'])],
            'og_title' => ['nullable', 'string', 'max:120'],
            'og_description' => ['nullable', 'string', 'max:200'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_og_image' => ['nullable', 'boolean'],
            'exclude_from_sitemap' => ['nullable', 'boolean'],
        ], [], [
            'article_category_id' => 'kategori',
        ]);

        unset($data['cover_image'], $data['remove_cover_image'], $data['og_image'], $data['remove_og_image']);

        $data['exclude_from_sitemap'] = $request->boolean('exclude_from_sitemap');
        $data['seo_robots'] = $data['seo_robots'] ?? ($article?->seo_robots ?: 'index, follow');

        return $data;
    }

    /** Isi published_at otomatis saat pertama kali dipublikasikan tanpa tanggal. */
    private function applyPublishedAt(array $data, ?Article $article = null): array
    {
        if ($data['status'] === 'published' && empty($data['published_at']) && ! ($article?->published_at)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
