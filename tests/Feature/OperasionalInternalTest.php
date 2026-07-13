<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BusinessApplication;
use App\Models\BusinessCategory;
use App\Models\ReportExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperasionalInternalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Internal',
            'email' => 'internal@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function application(array $override = []): BusinessApplication
    {
        return BusinessApplication::create(array_merge([
            'applicant_type' => 'company',
            'business_name' => 'PT Uji Coba',
            'brand_name' => 'Merek Uji',
            'process_status' => 'Penawaran',
            'submitted_at' => now(),
        ], $override));
    }

    /** @return array<string, mixed> */
    private function payload(array $override = []): array
    {
        return array_merge([
            'applicant_type' => 'company',
            'business_name' => 'PT Uji Coba',
            'brand_name' => 'Merek Uji',
            'process_status' => 'Penawaran',
        ], $override);
    }

    // ---------------------------------------------------------------------
    // Export CSV
    // ---------------------------------------------------------------------

    public function test_csv_terbaca_excel_sebagai_tabel_bukan_satu_kolom(): void
    {
        $kategori = BusinessCategory::create(['name' => 'Kategori Uji Jasa', 'is_active' => true]);
        $this->application(['business_category_id' => $kategori->id]);

        $csv = $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.reports.csv'))
            ->assertOk()
            ->streamedContent();

        // BOM UTF-8 supaya huruf beraksen tidak rusak di Excel.
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);

        // Direktif sep= memaksa Excel memakai koma apa pun locale Windows-nya.
        $this->assertStringContainsString("sep=,\r\n", $csv);

        $baris = preg_split('/\r\n|\n/', trim(substr($csv, 3)));

        // Baris 0 = sep=, baris 1 = header, baris 2 = data.
        $this->assertSame('sep=,', $baris[0]);
        $this->assertSame(
            ['Merek', 'Perusahaan', 'Pemilik', 'No. Daftar', 'Jenis Pemohon', 'Kategori Bisnis', 'Status', 'Tanggal Masuk', 'Tanggal Sertifikat'],
            str_getcsv($baris[1])
        );

        // Data terpecah ke kolom-kolom, bukan menumpuk di satu sel.
        $data = str_getcsv($baris[2]);
        $this->assertCount(9, $data);
        $this->assertSame('Merek Uji', $data[0]);
        $this->assertSame('PT Uji Coba', $data[1]);
        $this->assertSame('Kategori Uji Jasa', $data[5]);

        // Tidak boleh ada titik koma sebagai pemisah lagi.
        $this->assertStringNotContainsString('Merek;Perusahaan', $csv);
    }

    public function test_export_excel_menghasilkan_xlsx_bergaya_tabel(): void
    {
        $kategori = BusinessCategory::create(['name' => 'Kategori Uji Jasa', 'is_active' => true]);
        $this->application(['business_category_id' => $kategori->id, 'business_name' => 'PT Nama Perusahaan Yang Panjang Sekali']);
        $this->application(['brand_name' => 'Merek Kedua']);

        $response = $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.reports.excel'))
            ->assertOk();

        $berkas = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($berkas, $response->streamedContent());

        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($berkas) === true, 'Berkas harus berupa XLSX (arsip zip) yang valid.');

        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $styles = $zip->getFromName('xl/styles.xml');
        $zip->close();
        @unlink($berkas);

        // Lebar kolom ditulis eksplisit dan mengikuti isi terpanjang (autofit).
        preg_match_all('/<col min="(\d+)"[^>]*width="([\d.]+)"/', $sheet, $kolom);
        $this->assertCount(9, $kolom[1], 'Setiap kolom harus punya lebar sendiri.');

        $lebar = array_combine($kolom[1], array_map('floatval', $kolom[2]));
        $this->assertGreaterThan(
            $lebar[1],
            $lebar[2],
            'Kolom Perusahaan berisi teks panjang, jadi harus lebih lebar dari kolom Merek.'
        );

        // Desain tabel: header beku, filter per kolom, judul dibentang, header berwarna.
        $this->assertStringContainsString('state="frozen"', $sheet);
        $this->assertStringContainsString('<autoFilter', $sheet);
        $this->assertStringContainsString('<mergeCell ref="A1:I1"/>', $sheet);
        $this->assertStringContainsString('861D1D', $styles, 'Header tabel harus berlatar maroon.');
        $this->assertStringContainsString('F7F1F1', $styles, 'Baris genap harus berwarna belang.');
        $this->assertStringContainsString('<border>', $styles, 'Sel harus punya garis tabel.');
    }

    public function test_riwayat_export_mencatat_format_xlsx(): void
    {
        $this->application();

        $this->actingAs($this->admin(), 'admin')->post(route('admin.reports.excel'))->assertOk();

        $this->assertDatabaseHas('report_exports', ['format' => 'xlsx', 'report_type' => 'applications']);
    }

    public function test_export_hanya_menerima_post_dan_path_selalu_unik(): void
    {
        $admin = $this->admin();
        $this->application();
        $this->travelTo(now()->startOfSecond());

        $this->actingAs($admin, 'admin')
            ->get(route('admin.reports.excel'))
            ->assertStatus(405);

        $this->actingAs($admin, 'admin')->post(route('admin.reports.excel'))->assertOk();
        $this->actingAs($admin, 'admin')->post(route('admin.reports.excel'))->assertOk();

        $paths = ReportExport::pluck('file_path');
        $this->assertCount(2, $paths);
        $this->assertCount(2, $paths->unique());
        $this->assertTrue($paths->every(fn (string $path) => Storage::disk('local')->exists($path)));

        $this->travelBack();
    }

    public function test_form_export_memakai_post_dan_csrf(): void
    {
        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.reports.index'))
            ->assertOk();

        $response->assertSee('method="POST"', false);
        $response->assertSee('data-download-form', false);
        $response->assertSee('name="_token"', false);
    }

    // ---------------------------------------------------------------------
    // Riwayat Export
    // ---------------------------------------------------------------------

    public function test_hapus_satu_riwayat_export_ikut_menghapus_berkasnya(): void
    {
        $admin = $this->admin();
        $this->application();

        $this->actingAs($admin, 'admin')->post(route('admin.reports.excel'))->assertOk();

        $riwayat = ReportExport::firstOrFail();
        $this->assertTrue(Storage::disk('local')->exists($riwayat->file_path));

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.reports.history.destroy', $riwayat))
            ->assertRedirect()
            ->assertSessionHas('ok');

        $this->assertDatabaseMissing('report_exports', ['id' => $riwayat->id]);
        $this->assertFalse(
            Storage::disk('local')->exists($riwayat->file_path),
            'Berkas export harus ikut terhapus agar storage tidak menumpuk.'
        );
    }

    public function test_hapus_semua_riwayat_export(): void
    {
        $admin = $this->admin();
        $this->application();

        $this->actingAs($admin, 'admin')->post(route('admin.reports.excel'))->assertOk();
        $this->actingAs($admin, 'admin')->post(route('admin.reports.csv'))->assertOk();

        $berkas = ReportExport::pluck('file_path');
        $this->assertCount(2, $berkas);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.reports.history.clear'))
            ->assertRedirect()
            ->assertSessionHas('ok');

        $this->assertSame(0, ReportExport::count());

        foreach ($berkas as $path) {
            $this->assertFalse(Storage::disk('local')->exists($path));
        }
    }

    public function test_tombol_hapus_riwayat_muncul_hanya_bila_ada_riwayat(): void
    {
        $admin = $this->admin();
        $this->application();

        // Belum ada export → tombol tidak ditampilkan.
        $this->actingAs($admin, 'admin')
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertDontSee('Hapus Riwayat');

        $this->actingAs($admin, 'admin')->post(route('admin.reports.excel'))->assertOk();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Hapus Riwayat');
    }

    // ---------------------------------------------------------------------
    // Kategori Bisnis
    // ---------------------------------------------------------------------

    public function test_kategori_nonaktif_tidak_hilang_diam_diam_dari_pengajuan_lama(): void
    {
        $admin = $this->admin();
        $kategori = BusinessCategory::create(['name' => 'Kategori Uji Hotel', 'is_active' => true]);
        $pengajuan = $this->application(['business_category_id' => $kategori->id]);

        // Admin menonaktifkan kategori yang sedang dipakai.
        $kategori->update(['is_active' => false]);

        // Form edit tetap menawarkan kategori itu, ditandai nonaktif.
        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.edit', $pengajuan))
            ->assertOk()
            ->assertSee('Kategori Uji Hotel (nonaktif)');

        // Menyimpan ulang tidak boleh membuat kategorinya jadi kosong.
        $this->actingAs($admin, 'admin')
            ->put(route('admin.applications.update', $pengajuan), $this->payload([
                'business_category_id' => $kategori->id,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame($kategori->id, $pengajuan->fresh()->business_category_id);
    }

    public function test_kategori_nonaktif_baru_tetap_ditolak_untuk_pengajuan_lain(): void
    {
        $admin = $this->admin();
        $nonaktif = BusinessCategory::create(['name' => 'Kategori Uji Arsip', 'is_active' => false]);
        $pengajuan = $this->application();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.applications.update', $pengajuan), $this->payload([
                'business_category_id' => $nonaktif->id,
            ]))
            ->assertSessionHasErrors('business_category_id');

        $this->assertNull($pengajuan->fresh()->business_category_id);
    }

    public function test_hapus_kategori_terpakai_memberi_pesan_bukan_halaman_error(): void
    {
        $admin = $this->admin();
        $kategori = BusinessCategory::create(['name' => 'Kategori Uji Jasa', 'is_active' => true]);
        $this->application(['business_category_id' => $kategori->id]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.business-categories.destroy', $kategori))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('business_categories', ['id' => $kategori->id]);
    }

    public function test_kategori_tanpa_pengajuan_bisa_dihapus(): void
    {
        $kategori = BusinessCategory::create(['name' => 'Kategori Uji Kosong', 'is_active' => true]);

        $this->actingAs($this->admin(), 'admin')
            ->delete(route('admin.business-categories.destroy', $kategori))
            ->assertRedirect()
            ->assertSessionHas('ok');

        $this->assertDatabaseMissing('business_categories', ['id' => $kategori->id]);
    }

    public function test_nama_kategori_tidak_boleh_duplikat(): void
    {
        $admin = $this->admin();
        BusinessCategory::create(['name' => 'Kategori Uji Jasa', 'is_active' => true]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.business-categories.store'), ['name' => 'Kategori Uji Jasa'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, BusinessCategory::where('name', 'Kategori Uji Jasa')->count());
    }
}
