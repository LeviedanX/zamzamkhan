<?php

namespace App\Http\Requests\Admin;

use App\Rules\GoogleMapsUrl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route sudah dilindungi middleware auth:admin.
        return true;
    }

    public function rules(): array
    {
        return [
            // Identitas
            'company_name' => ['required', 'string', 'max:180'],
            'tagline' => ['nullable', 'string', 'max:255'],

            // Tentang perusahaan
            'company_description' => ['nullable', 'string', 'max:5000'],
            'vision' => ['nullable', 'string', 'max:2000'],
            'mission' => ['nullable', 'string', 'max:3000'],

            // Kontak & operasional
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'address' => ['nullable', 'string', 'max:500'],
            'operating_hours' => ['nullable', 'string', 'max:255'],

            // Lokasi & sosial media
            'maps_url' => ['nullable', 'url:http,https', new GoogleMapsUrl, 'max:500'],
            'maps_embed_url' => ['nullable', 'url:http,https', new GoogleMapsUrl, 'max:500'],
            'social_links' => ['nullable', 'array', 'max:8'],
            'social_links.*.label' => ['nullable', 'string', 'max:40'],
            'social_links.*.url' => ['nullable', 'url:http,https', 'max:255'],

            // Media
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name' => 'nama perusahaan',
            'company_description' => 'deskripsi perusahaan',
            'operating_hours' => 'jam operasional',
            'maps_url' => 'URL Google Maps',
            'maps_embed_url' => 'URL embed Google Maps',
        ];
    }
}
