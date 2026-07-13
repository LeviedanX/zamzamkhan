<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\HeroSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroMediaUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Media',
            'email' => 'media@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    /**
     * PNG asli 1x1. Dipakai menggantikan UploadedFile::fake()->image()
     * karena ekstensi GD tidak tersedia di environment ini.
     */
    private function pngUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'zzk').'.png';
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    /** @return array<string, string> Field teks wajib agar validasi lolos. */
    private function basePayload(): array
    {
        return [
            'title' => 'Konsultan Halal dan Legalitas Usaha di Malang',
            'subtitle' => 'Subjudul hero.',
            'secondary_button_text' => 'Lihat Layanan',
            'badge_text' => 'Badge',
            'trust_text' => 'Trust line.',
            'service_chips' => "Chip A\nChip B",
            'portrait_alt' => 'Alt figur',
            'portrait_role' => 'Direktur',
            'portrait_name' => 'Nama Figur',
        ];
    }

    public function test_admin_bisa_upload_gambar_latar_dan_figur_lalu_tampil_di_homepage(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.hero.update'), $this->basePayload() + [
                'image' => $this->pngUpload('latar.png'),
                'portrait' => $this->pngUpload('figur.png'),
            ])
            ->assertRedirect();

        $hero = HeroSection::where('is_active', true)->latest('updated_at')->firstOrFail();

        $this->assertStringStartsWith('hero/', $hero->image_path);
        $this->assertStringStartsWith('hero/', $hero->portrait_path);
        Storage::disk('public')->assertExists($hero->image_path);
        Storage::disk('public')->assertExists($hero->portrait_path);

        // Keduanya harus benar-benar dirender homepage lewat URL /storage/...
        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('storage/'.$hero->image_path, false)
            ->assertSee('storage/'.$hero->portrait_path, false);
    }

    public function test_upload_baru_mengganti_file_lama(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->put(route('admin.hero.update'), $this->basePayload() + [
            'image' => $this->pngUpload('latar-lama.png'),
            'portrait' => $this->pngUpload('figur-lama.png'),
        ]);

        $lama = HeroSection::where('is_active', true)->latest('updated_at')->firstOrFail();

        $this->actingAs($admin, 'admin')->put(route('admin.hero.update'), $this->basePayload() + [
            'image' => $this->pngUpload('latar-baru.png'),
            'portrait' => $this->pngUpload('figur-baru.png'),
        ]);

        $baru = $lama->fresh();

        $this->assertNotSame($lama->image_path, $baru->image_path);
        $this->assertNotSame($lama->portrait_path, $baru->portrait_path);
        Storage::disk('public')->assertMissing($lama->image_path);
        Storage::disk('public')->assertMissing($lama->portrait_path);
        Storage::disk('public')->assertExists($baru->image_path);
        Storage::disk('public')->assertExists($baru->portrait_path);
    }

    public function test_centang_hapus_mengembalikan_ke_visual_bawaan(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->put(route('admin.hero.update'), $this->basePayload() + [
            'image' => $this->pngUpload('latar.png'),
            'portrait' => $this->pngUpload('figur.png'),
        ]);

        $hero = HeroSection::where('is_active', true)->latest('updated_at')->firstOrFail();
        $imagePath = $hero->image_path;
        $portraitPath = $hero->portrait_path;

        $this->actingAs($admin, 'admin')
            ->put(route('admin.hero.update'), $this->basePayload() + [
                'remove_image' => '1',
                'remove_portrait' => '1',
            ])
            ->assertRedirect();

        $hero->refresh();

        $this->assertNull($hero->image_path);
        $this->assertNull($hero->portrait_path);
        Storage::disk('public')->assertMissing($imagePath);
        Storage::disk('public')->assertMissing($portraitPath);

        // Homepage tetap aman: figur jatuh ke gambar bawaan, bukan broken image.
        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('images/buzamzami.png', false);
    }
}
