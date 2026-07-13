<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSiteSettingRequest;
use App\Models\SiteSetting;
use App\Support\PublicMedia;

class SiteSettingController extends Controller
{
    public function edit()
    {
        $setting = SiteSetting::firstOrNew([]);

        return view('admin.settings.edit', compact('setting'));
    }

    public function update(UpdateSiteSettingRequest $request)
    {
        $data = $request->validated();
        unset($data['logo'], $data['remove_logo']);

        $data['mission'] = $this->normalizeLines($data['mission'] ?? null);
        $data['company_description'] = $this->normalizeParagraphs($data['company_description'] ?? null);
        $data['social_links'] = $this->normalizeSocialLinks($data['social_links'] ?? []);

        foreach (['tagline', 'vision', 'phone', 'whatsapp', 'email', 'address', 'operating_hours', 'maps_url', 'maps_embed_url'] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = blank($data[$key]) ? null : trim((string) $data[$key]);
            }
        }

        $setting = SiteSetting::firstOrNew([]);
        $oldLogo = $setting->logo_path;
        $newLogo = null;
        if ($request->boolean('remove_logo')) {
            $data['logo_path'] = null;
        } elseif ($request->hasFile('logo')) {
            $newLogo = PublicMedia::store($request->file('logo'), 'branding');
            $data['logo_path'] = $newLogo;
        }

        try {
            $setting->fill($data)->save();
        } catch (\Throwable $e) {
            PublicMedia::delete($newLogo);
            throw $e;
        }

        if (($newLogo || $request->boolean('remove_logo')) && $oldLogo !== $newLogo) {
            PublicMedia::delete($oldLogo);
        }

        return back()->with('ok', 'Pengaturan situs disimpan.');
    }

    private function normalizeLines(?string $value): ?string
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        return $lines->isEmpty() ? null : $lines->implode("\n");
    }

    private function normalizeParagraphs(?string $value): ?string
    {
        $paragraphs = collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        return $paragraphs->isEmpty() ? null : $paragraphs->implode("\n");
    }

    private function normalizeSocialLinks(array $links): ?array
    {
        $items = collect($links)
            ->map(fn ($item) => [
                'label' => trim((string) ($item['label'] ?? '')),
                'url' => trim((string) ($item['url'] ?? '')),
            ])
            ->filter(fn ($item) => filled($item['label']) && filled($item['url']))
            ->values();

        return $items->isEmpty() ? null : $items->all();
    }
}
