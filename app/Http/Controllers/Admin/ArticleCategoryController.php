<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleCategoryController extends Controller
{
    public function index() { return view('admin.categories.index', ['items' => ArticleCategory::withCount('articles')->orderBy('name')->get(), 'type' => 'article']); }
    public function store(Request $request) { $data=$request->validate(['name'=>['required','string','max:120','unique:article_categories,name']]); ArticleCategory::create(['name'=>$data['name'],'slug'=>$this->slug($data['name'])]); return back()->with('ok','Kategori artikel ditambahkan.'); }
    public function update(Request $request, ArticleCategory $articleCategory) { $data=$request->validate(['name'=>['required','string','max:120','unique:article_categories,name,'.$articleCategory->id]]); $articleCategory->update(['name'=>$data['name'],'slug'=>$this->slug($data['name'],$articleCategory->id)]); return back()->with('ok','Kategori artikel diperbarui.'); }
    public function destroy(ArticleCategory $articleCategory) { abort_if($articleCategory->articles()->exists(), 422, 'Kategori masih digunakan artikel.'); $articleCategory->delete(); return back()->with('ok','Kategori artikel dihapus.'); }
    private function slug(string $name, ?int $ignore=null): string { $base=Str::slug($name) ?: 'kategori'; $slug=$base; $i=2; while (ArticleCategory::where('slug',$slug)->when($ignore,fn($q)=>$q->whereKeyNot($ignore))->exists()) $slug=$base.'-'.$i++; return $slug; }
}
