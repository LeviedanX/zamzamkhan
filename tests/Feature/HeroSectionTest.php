<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\HeroSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSectionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Hero',
            'email' => 'hero@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    public function test_admin_bisa_mengelola_elemen_hero_utama_dari_editor_tunggal(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.hero.edit'))
            ->assertOk()
            ->assertSee('Hero Utama')
            ->assertSee('Tambah Chip')
            ->assertDontSee('Teks tombol utama')
            ->assertDontSee('Aktifkan Hero');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.hero.update'), [
                'title' => 'Judul Hero Baru',
                'subtitle' => 'Subjudul hero baru.',
                'secondary_button_text' => 'CTA Kedua',
                'badge_text' => 'Badge Hero Baru',
                'trust_text' => 'Trust line baru.',
                'service_chips' => "Chip Satu\n\n Chip Dua \nChip Tiga",
                'portrait_alt' => 'Alt figur baru',
                'portrait_role' => 'Komisaris',
                'portrait_name' => 'Nama Figur Baru',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hero_sections', [
            'title' => 'Judul Hero Baru',
            'badge_text' => 'Badge Hero Baru',
            'trust_text' => 'Trust line baru.',
            'service_chips' => "Chip Satu\nChip Dua\nChip Tiga",
            'portrait_alt' => 'Alt figur baru',
            'portrait_role' => 'Komisaris',
            'portrait_name' => 'Nama Figur Baru',
            'is_active' => true,
        ]);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('CTA Kedua')
            ->assertSee('href="#layanan"', false);

        $hero = HeroSection::where('title', 'Judul Hero Baru')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.hero.edit'))
            ->assertOk()
            ->assertSee('Judul Hero Baru')
            ->assertSee('Chip Dua');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.hero.update'), [
                'title' => 'Judul Hero Diperbarui',
                'subtitle' => 'Subjudul diperbarui.',
                'secondary_button_text' => 'Detail',
                'badge_text' => 'Badge Baru',
                'trust_text' => 'Trust Baru',
                'service_chips' => "Chip A\nChip B",
                'portrait_alt' => 'Alt baru',
                'portrait_role' => 'Direktur',
                'portrait_name' => 'Nama Baru',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hero_sections', [
            'id' => $hero->id,
            'title' => 'Judul Hero Diperbarui',
            'service_chips' => "Chip A\nChip B",
        ]);
    }

    /**
     * "Tujuan tombol" (secondary_button_url) dihapus dari admin: tidak berguna
     * selain anchor tetap #layanan, jadi tombol hero selalu di-hardcode ke situ.
     */
    public function test_tombol_hero_selalu_mengarah_ke_layanan_tanpa_field_tujuan_tombol(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.hero.edit'))
            ->assertOk()
            ->assertDontSee('name="secondary_button_url"', false)
            ->assertDontSee('Tujuan tombol');

        // Bahkan bila dikirim paksa, tidak lagi tersimpan/berefek.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.hero.update'), [
                'title' => 'Judul Hero',
                'secondary_button_text' => 'Lihat Layanan',
                'secondary_button_url' => 'https://situs-luar.test',
            ])
            ->assertRedirect();

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="#layanan"', false)
            ->assertDontSee('https://situs-luar.test');
    }
}
