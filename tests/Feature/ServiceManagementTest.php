<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Layanan',
            'email' => 'layanan@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    public function test_admin_bisa_mengelola_semua_field_layanan(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertDontSee('Preview')
            ->assertSee('Tambah Layanan');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.create'))
            ->assertOk()
            ->assertSee('Manfaat utama')
            ->assertSee('Alur singkat')
            ->assertSee('Kebutuhan awal')
            ->assertDontSee('Preview');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.services.store'), [
                'title' => 'Layanan Uji',
                'icon' => 'nib',
                'summary' => 'Ringkasan card layanan.',
                'description' => 'Deskripsi detail layanan.',
                'benefits' => "Manfaat satu\n\n Manfaat dua ",
                'suitable_for' => 'UMKM yang membutuhkan legalitas.',
                'workflow_steps' => "Tahap satu\n\nTahap dua",
                'whatsapp_message' => 'Halo, saya ingin konsultasi layanan uji.',
                'display_order' => 3,
                'is_active' => '1',
                'is_featured' => '1',
            ])
            ->assertRedirect(route('admin.services.index'));

        $service = Service::where('title', 'Layanan Uji')->firstOrFail();

        $this->assertSame("Manfaat satu\nManfaat dua", $service->benefits);
        $this->assertSame("Tahap satu\nTahap dua", $service->workflow_steps);
        $this->assertTrue($service->is_active);
        $this->assertTrue($service->is_featured);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.services.update', $service), [
                'title' => 'Layanan Uji Update',
                'icon' => 'bpom',
                'summary' => 'Ringkasan update.',
                'description' => 'Deskripsi update.',
                'benefits' => 'Manfaat final',
                'suitable_for' => 'Produsen produk.',
                'workflow_steps' => 'Tahap final',
                'whatsapp_message' => 'Pesan WA final.',
                'display_order' => 4,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'title' => 'Layanan Uji Update',
            'icon' => 'bpom',
            'benefits' => 'Manfaat final',
            'workflow_steps' => 'Tahap final',
            'is_featured' => false,
        ]);
    }

    public function test_detail_layanan_yang_dikosongkan_admin_tidak_diisi_teks_hardcoded(): void
    {
        // Layanan memakai ikon 'halal', ikon yang dulu punya salinan deskripsi, "cocok
        // untuk", dan alur hardcoded di partial. Setelah field dikosongkan admin, teks
        // lama itu tidak boleh muncul kembali (aturan fallback CLAUDE.md §11.3).
        Service::create([
            'title' => 'Layanan Tanpa Detail',
            'slug' => 'layanan-tanpa-detail',
            'icon' => 'halal',
            'summary' => 'Ringkasan singkat.',
            'description' => null,
            'suitable_for' => null,
            'workflow_steps' => null,
            'is_active' => true,
        ]);

        $this->refreshPublicSiteConfig();

        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('Layanan Tanpa Detail', $content);
        $this->assertStringNotContainsString('SIHALAL', $content);
        $this->assertStringNotContainsString('Pendamping PPH', $content);
        $this->assertStringNotContainsString('Komite Fatwa Produk Halal', $content);
        $this->assertStringNotContainsString('bahan yang jelas status kehalalannya', $content);
    }
}
