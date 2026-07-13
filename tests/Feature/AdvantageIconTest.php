<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Advantage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvantageIconTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Keunggulan',
            'email' => 'keunggulan@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function advantage(string $icon = 'clipboard'): Advantage
    {
        return Advantage::create([
            'title' => 'Pendampingan dari Awal',
            'description' => 'Deskripsi keunggulan.',
            'icon' => $icon,
            'display_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_form_memakai_dropdown_bukan_isian_bebas(): void
    {
        $advantage = $this->advantage();

        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.advantages.edit', $advantage))
            ->assertOk()
            ->assertSee('<select name="icon"', false)
            ->assertDontSee('<input name="icon"', false);

        // Semua ikon yang punya SVG harus tersedia sebagai opsi.
        foreach (Advantage::ICONS as $key => $label) {
            $response->assertSee('value="'.$key.'"', false)->assertSee($label);
        }

        // Ikon yang sedang dipakai harus tetap terpilih (tidak diam-diam berubah).
        $response->assertSee('value="clipboard" selected', false);
    }

    public function test_setiap_ikon_bisa_disimpan_dan_dirender_di_homepage(): void
    {
        $admin = $this->admin();
        $advantage = $this->advantage();

        foreach (array_keys(Advantage::ICONS) as $icon) {
            $this->actingAs($admin, 'admin')
                ->put(route('admin.advantages.update', $advantage), [
                    'title' => 'Pendampingan dari Awal',
                    'description' => 'Deskripsi keunggulan.',
                    'icon' => $icon,
                    'display_order' => 1,
                    'is_active' => '1',
                ])
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.advantages.index'));

            $this->assertSame($icon, $advantage->fresh()->icon);

            $this->refreshPublicSiteConfig();
            $this->get(route('home'))->assertOk()->assertSee('Pendampingan dari Awal');
        }
    }

    public function test_kode_ikon_di_luar_daftar_ditolak(): void
    {
        $admin = $this->admin();
        $advantage = $this->advantage('shield');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.advantages.update', $advantage), [
                'title' => 'Pendampingan dari Awal',
                'description' => 'Deskripsi keunggulan.',
                'icon' => 'ikon-ngawur',
                'display_order' => 1,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('icon');

        // Data lama tidak boleh ikut berubah saat validasi gagal.
        $this->assertSame('shield', $advantage->fresh()->icon);
    }

    public function test_ikon_boleh_dikosongkan_dan_memakai_ikon_bawaan(): void
    {
        $admin = $this->admin();
        $advantage = $this->advantage('star');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.advantages.update', $advantage), [
                'title' => 'Pendampingan dari Awal',
                'description' => 'Deskripsi keunggulan.',
                'icon' => '',
                'display_order' => 1,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($advantage->fresh()->icon);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))->assertOk()->assertSee('Pendampingan dari Awal');
    }
}
