<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use Illuminate\Http\Request;

class BusinessCategoryController extends Controller
{
    public function index() { return view('admin.categories.index', ['items' => BusinessCategory::withCount('applications')->orderBy('name')->get(), 'type' => 'business']); }
    public function store(Request $request) { BusinessCategory::create($request->validate(['name' => ['required','string','max:255','unique:business_categories,name']]) + ['is_active' => true]); return back()->with('ok','Kategori bisnis ditambahkan.'); }
    public function update(Request $request, BusinessCategory $businessCategory) { $data=$request->validate(['name'=>['required','string','max:255','unique:business_categories,name,'.$businessCategory->id]]); $businessCategory->update($data + ['is_active'=>$request->boolean('is_active')]); return back()->with('ok','Kategori bisnis diperbarui.'); }
    public function destroy(BusinessCategory $businessCategory)
    {
        // Dulu memakai abort_if(422): admin dilempar ke halaman error mentah.
        // Kembalikan sebagai pesan biasa supaya tetap berada di halaman kategori.
        if ($businessCategory->applications()->exists()) {
            return back()->with('error', 'Kategori "'.$businessCategory->name.'" masih dipakai data pengajuan dan tidak bisa dihapus. Pindahkan pengajuannya dulu, atau nonaktifkan kategori ini.');
        }

        $businessCategory->delete();

        return back()->with('ok', 'Kategori bisnis dihapus.');
    }
}
