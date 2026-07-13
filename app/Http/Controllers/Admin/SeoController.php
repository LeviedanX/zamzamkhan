<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Support\PublicMedia;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function edit()
    {
        $seo = SeoSetting::firstOrNew(['page_key' => 'home']);

        return view('admin.seo.edit', compact('seo'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'meta_title' => ['required', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:180'],
            'og_description' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:255'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_og_image' => ['nullable', 'boolean'],
        ]);
        unset($data['og_image'], $data['remove_og_image']);

        $seo = SeoSetting::firstOrNew(['page_key' => 'home']);
        $seo->page_key = 'home';
        $oldImage = $seo->og_image_path;
        $newImage = null;
        if ($request->boolean('remove_og_image')) {
            $data['og_image_path'] = null;
        } elseif ($request->hasFile('og_image')) {
            $newImage = PublicMedia::store($request->file('og_image'), 'seo');
            $data['og_image_path'] = $newImage;
        }
        try {
            $seo->fill($data)->save();
        } catch (\Throwable $e) {
            PublicMedia::delete($newImage);
            throw $e;
        }
        if (($newImage || $request->boolean('remove_og_image')) && $oldImage !== $newImage) {
            PublicMedia::delete($oldImage);
        }

        return back()->with('ok', 'Pengaturan SEO disimpan.');
    }
}
