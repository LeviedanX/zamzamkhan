<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSection;
use App\Support\PublicMedia;
use Illuminate\Http\Request;

class HeroController extends Controller
{
    public function edit()
    {
        $hero = HeroSection::where('is_active', true)->latest('updated_at')->latest('id')->first()
            ?? HeroSection::latest('updated_at')->latest('id')->first()
            ?? new HeroSection;

        return view('admin.hero.edit', compact('hero'));
    }

    public function update(Request $request)
    {
        $hero = HeroSection::where('is_active', true)->latest('updated_at')->latest('id')->first()
            ?? HeroSection::latest('updated_at')->latest('id')->first()
            ?? new HeroSection;

        $this->persist($request, $hero);

        return back()->with('ok', 'Hero utama berhasil diperbarui.');
    }

    private function persist(Request $request, HeroSection $hero): void
    {
        $data = $this->validatedData($request);
        unset($data['image'], $data['portrait'], $data['remove_image'], $data['remove_portrait']);
        // Homepage selalu membutuhkan satu hero aktif; status publikasi bukan kontrol yang relevan.
        $data['is_active'] = true;

        foreach (['secondary_button_text', 'badge_text', 'trust_text', 'portrait_alt', 'portrait_role', 'portrait_name'] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = blank($data[$key]) ? null : trim($data[$key]);
            }
        }

        if (array_key_exists('service_chips', $data)) {
            $chips = collect(preg_split('/\r\n|\r|\n/', (string) $data['service_chips']))
                ->map(fn ($chip) => trim($chip))
                ->filter()
                ->values();

            $data['service_chips'] = $chips->isEmpty() ? null : $chips->implode("\n");
        }

        $oldImage = $hero->image_path;
        $oldPortrait = $hero->portrait_path;
        $newImage = null;
        $newPortrait = null;

        if ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        } elseif ($request->hasFile('image')) {
            $newImage = PublicMedia::store($request->file('image'), 'hero');
            $data['image_path'] = $newImage;
        }

        if ($request->boolean('remove_portrait')) {
            $data['portrait_path'] = null;
        } elseif ($request->hasFile('portrait')) {
            $newPortrait = PublicMedia::store($request->file('portrait'), 'hero');
            $data['portrait_path'] = $newPortrait;
        }

        try {
            $hero->fill($data)->save();
        } catch (\Throwable $e) {
            PublicMedia::delete($newImage);
            PublicMedia::delete($newPortrait);
            throw $e;
        }

        if (($newImage || $request->boolean('remove_image')) && $oldImage !== $newImage) {
            PublicMedia::delete($oldImage);
        }
        if (($newPortrait || $request->boolean('remove_portrait')) && $oldPortrait !== $newPortrait) {
            PublicMedia::delete($oldPortrait);
        }
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:1000'],
            'secondary_button_text' => ['nullable', 'string', 'max:80'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'badge_text' => ['nullable', 'string', 'max:120'],
            'trust_text' => ['nullable', 'string', 'max:160'],
            'service_chips' => ['nullable', 'string', 'max:600'],
            'portrait' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_portrait' => ['nullable', 'boolean'],
            'portrait_alt' => ['nullable', 'string', 'max:180'],
            'portrait_role' => ['nullable', 'string', 'max:80'],
            'portrait_name' => ['nullable', 'string', 'max:120'],
        ]);
    }
}
