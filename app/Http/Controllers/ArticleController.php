<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $categorySlug = trim((string) $request->query('kategori', ''));

        $activeCategory = $categorySlug
            ? ArticleCategory::where('slug', $categorySlug)->first()
            : null;

        $articles = Article::query()
            ->published()
            ->with('category')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('excerpt', 'like', "%{$q}%")
                        ->orWhere('content', 'like', "%{$q}%");
                });
            })
            ->when($activeCategory, fn ($query) => $query->where('article_category_id', $activeCategory->id))
            ->latestPublished()
            ->paginate(9)
            ->withQueryString();

        $categories = ArticleCategory::orderBy('id')->get();

        return view('articles.index', [
            'articles' => $articles,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'q' => $q,
        ]);
    }

    public function show(string $slug)
    {
        $article = Article::published()->with('category')->where('slug', $slug)->firstOrFail();

        $related = Article::published()
            ->with('category')
            ->where('id', '!=', $article->id)
            ->when($article->article_category_id, fn ($q) => $q->where('article_category_id', $article->article_category_id))
            ->latestPublished()
            ->take(3)
            ->get();

        // Fallback: lengkapi dengan artikel terbaru bila kategori kurang dari 3.
        if ($related->count() < 3) {
            $fill = Article::published()
                ->with('category')
                ->where('id', '!=', $article->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->latestPublished()
                ->take(3 - $related->count())
                ->get();

            $related = $related->concat($fill);
        }

        return view('articles.show', [
            'article' => $article,
            'related' => $related,
        ]);
    }
}
