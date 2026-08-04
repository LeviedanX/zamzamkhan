<?php

namespace Tests\Feature;

use App\Models\BusinessApplication;
use App\Models\BusinessProcessStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BusinessProcessStatusTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'is_admin' => true,
            'name' => 'Admin Status',
            'email' => 'status@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function statusPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Verifikasi Dokumen',
            'type' => 'ongoing',
            'display_order' => 9,
            'is_active' => '1',
            'is_default' => '0',
        ], $overrides);
    }

    private function applicationPayload(string $status, array $overrides = []): array
    {
        return array_merge([
            'applicant_type' => 'company',
            'business_name' => 'PT Status Dinamis',
            'process_status' => $status,
            'submitted_at' => '2026-07-18',
        ], $overrides);
    }

    public function test_menu_status_proses_terpisah_dan_status_bawaan_tersedia(): void
    {
        $admin = $this->admin();
        $this->assertTrue(Route::has('admin.process-statuses.index'));
        $this->assertCount(8, BusinessProcessStatus::all());
        $this->assertSame('Penawaran', BusinessProcessStatus::defaultName());

        $this->actingAs($admin, 'admin')
            ->get(route('admin.process-statuses.index'))
            ->assertOk()
            ->assertSeeText('Status Proses Pengajuan')
            ->assertSeeText('Menu ini terpisah dari Data Pengajuan')
            ->assertSee('value="Sertifikat Terbit"', false);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.process-statuses.store'), [])
            ->assertRedirect(route('admin.process-statuses.index'))
            ->assertSessionHasErrors(['name', 'type', 'display_order']);
    }

    public function test_status_baru_terintegrasi_ke_tambah_pengajuan_riwayat_dan_laporan(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.process-statuses.store'), $this->statusPayload())
            ->assertRedirect(route('admin.process-statuses.index'))
            ->assertSessionHas('ok');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.create'))
            ->assertOk()
            ->assertSeeText('Verifikasi Dokumen');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.applications.store'), $this->applicationPayload('Verifikasi Dokumen'))
            ->assertRedirect(route('admin.applications.index'));

        $application = BusinessApplication::where('process_status', 'Verifikasi Dokumen')->firstOrFail();
        $this->assertDatabaseHas('business_application_status_histories', [
            'business_application_id' => $application->id,
            'new_status' => 'Verifikasi Dokumen',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSeeText('Verifikasi Dokumen');
    }

    public function test_status_terpakai_tidak_dapat_dihapus_atau_diganti_nama_tetapi_dapat_dinonaktifkan(): void
    {
        $admin = $this->admin();
        $status = BusinessProcessStatus::create($this->statusPayload());
        $application = BusinessApplication::create([
            'applicant_type' => 'company',
            'business_name' => 'PT Pemakai Status',
            'process_status' => $status->name,
        ]);
        $application->histories()->create(['new_status' => $status->name]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.process-statuses.destroy', $status))
            ->assertRedirect(route('admin.process-statuses.index'))
            ->assertSessionHas('error');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.process-statuses.update', $status), $this->statusPayload(['name' => 'Nama Baru']))
            ->assertRedirect(route('admin.process-statuses.index'))
            ->assertSessionHas('error');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.process-statuses.update', $status), $this->statusPayload(['is_active' => '0']))
            ->assertRedirect(route('admin.process-statuses.index'))
            ->assertSessionHas('ok');

        $this->assertFalse($status->fresh()->is_active);
        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.create'))
            ->assertOk()
            ->assertDontSeeText('Verifikasi Dokumen');
        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.edit', $application))
            ->assertOk()
            ->assertSeeText('Verifikasi Dokumen (nonaktif)');
    }

    public function test_status_tidak_terpakai_dapat_dihapus_dan_status_default_dilindungi(): void
    {
        $admin = $this->admin();
        $status = BusinessProcessStatus::create($this->statusPayload());

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.process-statuses.destroy', $status))
            ->assertRedirect(route('admin.process-statuses.index'))
            ->assertSessionHas('ok');
        $this->assertDatabaseMissing('business_process_statuses', ['id' => $status->id]);

        $default = BusinessProcessStatus::where('is_default', true)->firstOrFail();
        $this->actingAs($admin, 'admin')
            ->delete(route('admin.process-statuses.destroy', $default))
            ->assertRedirect(route('admin.process-statuses.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('business_process_statuses', ['id' => $default->id]);
    }

    public function test_default_baru_dan_kategori_sertifikat_memengaruhi_form_dan_ringkasan(): void
    {
        $admin = $this->admin();
        $issued = BusinessProcessStatus::create($this->statusPayload([
            'name' => 'Dokumen Final Terbit',
            'type' => 'issued',
            'is_default' => false,
        ]));

        $this->actingAs($admin, 'admin')
            ->post(route('admin.applications.store'), $this->applicationPayload($issued->name))
            ->assertSessionHasErrors('certificate_issued_at');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.applications.store'), $this->applicationPayload($issued->name, [
                'certificate_issued_at' => '2026-07-18',
            ]))
            ->assertRedirect(route('admin.applications.index'));

        $this->actingAs($admin, 'admin')
            ->put(route('admin.process-statuses.update', $issued), $this->statusPayload([
                'name' => $issued->name,
                'type' => 'issued',
                'is_default' => '1',
            ]))
            ->assertSessionHas('ok');

        $this->assertSame($issued->name, BusinessProcessStatus::defaultName());
        $this->assertSame(1, BusinessProcessStatus::where('is_default', true)->count());
        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSeeInOrder(['Sertifikat terbit', '<strong>1</strong>'], false);
    }
}
