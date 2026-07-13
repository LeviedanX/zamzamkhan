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

        try {
            Article::create($data);
        } catch (\Throwable $e) {
            PublicMedia::delete($data['cover_image'] ?? null);
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
        $newCover = null;
        if ($request->boolean('remove_cover_image')) {
            $data['cover_image'] = null;
        } elseif ($request->hasFile('cover_image')) {
            $newCover = PublicMedia::store($request->file('cover_image'), 'articles');
            $data['cover_image'] = $newCover;
        }

        try {
            $article->update($data);
        } catch (\Throwable $e) {
            PublicMedia::delete($newCover);
            throw $e;
        }

        if (($newCover || $request->boolean('remove_cover_image')) && $oldCover !== $newCover) {
            PublicMedia::delete($oldCover);
        }

        return redirect()->route('admin.articles.index')->with('ok', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        $cover = $article->cover_image;
        $article->delete();
        PublicMedia::delete($cover);

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
        ], [], [
            'article_category_id' => 'kategori',
        ]);

        unset($data['cover_image'], $data['remove_cover_image']);

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
