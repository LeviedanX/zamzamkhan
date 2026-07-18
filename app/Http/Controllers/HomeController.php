<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\SiteCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        try {
            $articles = Cache::remember(
                SiteCache::HOME_ARTICLES,
                now()->addMinutes(5),
                fn () => Schema::hasTable('articles')
                    ? Article::published()
                        ->with('category:id,name,slug')
                        ->latestPublished()
                        ->limit(3)
                        ->get()
                        ->map(fn (Article $article) => [
                            'id' => $article->getKey(),
                            'slug' => $article->slug,
                            'title' => $article->title,
                            'excerpt' => $article->excerpt,
                            'published_date' => $article->publishedDate(),
                            'cover_image' => $article->cover_image,
                            'cover_alt' => $article->cover_alt,
                            'category_name' => $article->category?->name,
                        ])
                        ->all()
                    : [],
            );
            $latestArticles = collect($articles);
        } catch (\Throwable) {
            // Homepage harus tetap tersedia ketika database/cache belum siap.
            $latestArticles = collect();
        }

        return view('home', compact('latestArticles'));
    }
}
