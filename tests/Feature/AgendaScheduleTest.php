<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Agenda;
use App\Support\AgendaPurger;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgendaScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Agenda',
            'email' => 'agenda@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Kelas Sertifikasi Halal',
            'summary' => 'Ringkasan agenda.',
            'venue' => 'Malang',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(3)->addHours(2)->format('Y-m-d H:i:s'),
            'display_order' => 1,
            'is_active' => '1',
        ], $override);
    }

    private function agenda(array $override = []): Agenda
    {
        return Agenda::create(array_merge([
            'title' => 'Kelas Sertifikasi Halal',
            'slug' => 'kelas-sertifikasi-halal',
            'summary' => 'Ringkasan agenda.',
            'venue' => 'Malang',
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addHours(2),
            'display_order' => 1,
            'is_active' => true,
        ], $override));
    }

    public function test_agenda_tidak_bisa_dijadwalkan_ke_masa_lalu(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.agendas.store'), $this->payload([
                'starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ]))
            ->assertSessionHasErrors('starts_at');

        $this->assertDatabaseCount('agendas', 0);
    }

    public function test_waktu_selesai_harus_lebih_besar_dari_waktu_mulai(): void
    {
        $admin = $this->admin();
        $start = now()->addDays(3);

        // Sama persis → ditolak.
        $this->actingAs($admin, 'admin')
            ->post(route('admin.agendas.store'), $this->payload([
                'starts_at' => $start->format('Y-m-d H:i:s'),
                'ends_at' => $start->format('Y-m-d H:i:s'),
            ]))
            ->assertSessionHasErrors('ends_at');

        // Lebih awal dari mulai → ditolak.
        $this->actingAs($admin, 'admin')
            ->post(route('admin.agendas.store'), $this->payload([
                'starts_at' => $start->format('Y-m-d H:i:s'),
                'ends_at' => $start->copy()->subHour()->format('Y-m-d H:i:s'),
            ]))
            ->assertSessionHasErrors('ends_at');

        // Waktu selesai wajib diisi.
        $this->actingAs($admin, 'admin')
            ->post(route('admin.agendas.store'), $this->payload(['ends_at' => '']))
            ->assertSessionHasErrors('ends_at');

        $this->assertDatabaseCount('agendas', 0);

        // Selisih satu menit pun sudah sah.
        $this->actingAs($admin, 'admin')
            ->post(route('admin.agendas.store'), $this->payload([
                'starts_at' => $start->format('Y-m-d H:i:s'),
                'ends_at' => $start->copy()->addMinute()->format('Y-m-d H:i:s'),
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('agendas', 1);
    }

    public function test_agenda_berlangsung_tetap_bisa_disunting_tanpa_menggeser_waktu_mulai(): void
    {
        // Sudah mulai kemarin, selesai besok → sedang berlangsung.
        $agenda = $this->agenda([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.agendas.update', $agenda), $this->payload([
                'title' => 'Judul Diperbarui',
                'starts_at' => $agenda->starts_at->format('Y-m-d H:i:s'),
                'ends_at' => $agenda->ends_at->format('Y-m-d H:i:s'),
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('Judul Diperbarui', $agenda->fresh()->title);
    }

    public function test_agenda_yang_sedang_berlangsung_tetap_tampil_di_homepage(): void
    {
        // Bug lama: agenda lintas hari hilang begitu ganti hari, padahal belum selesai.
        $this->agenda([
            'title' => 'Agenda Sedang Berlangsung',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))->assertOk()->assertSee('Agenda Sedang Berlangsung');
    }

    public function test_agenda_yang_sudah_selesai_tidak_tampil_di_homepage(): void
    {
        // Bug lama: agenda hari ini yang jamnya sudah lewat tetap tampil.
        $this->agenda([
            'title' => 'Agenda Sudah Selesai',
            'starts_at' => now()->subHours(5),
            'ends_at' => now()->subHour(),
        ]);

        $this->refreshPublicSiteConfig();
        $this->get(route('home'))->assertOk()->assertDontSee('Agenda Sudah Selesai');
    }

    public function test_agenda_selesai_dihapus_otomatis_beserta_gambarnya(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('agendas/lama.png', 'x');

        $selesai = $this->agenda([
            'title' => 'Agenda Selesai',
            'slug' => 'agenda-selesai',
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
            'image_path' => 'agendas/lama.png',
            'display_order' => 1,
        ]);

        $aktif = $this->agenda([
            'title' => 'Agenda Mendatang',
            'slug' => 'agenda-mendatang',
            'display_order' => 2,
        ]);

        $this->artisan('agendas:purge')->assertSuccessful();

        $this->assertDatabaseMissing('agendas', ['id' => $selesai->id]);
        $this->assertDatabaseHas('agendas', ['id' => $aktif->id]);
        Storage::disk('public')->assertMissing('agendas/lama.png');

        // Urutan tampil dirapatkan kembali, tidak menyisakan nomor bolong.
        $this->assertSame(1, $aktif->fresh()->display_order);
    }

    /**
     * Situs menerima dan menampilkan jam WIB (label "WIB" di kartu agenda,
     * input datetime-local mengirim jam dinding admin). Kalau aplikasi jatuh ke
     * UTC, agenda yang selesai 14:30 WIB baru dianggap selesai 7 jam kemudian
     * sehingga tidak pernah terhapus tepat waktu.
     */
    public function test_aplikasi_memakai_zona_waktu_wib(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('Asia/Jakarta', now()->timezoneName);
    }

    public function test_agenda_yang_selesai_menurut_jam_dinding_wib_terdeteksi_selesai(): void
    {
        // Persis seperti yang dikirim form: string waktu polos tanpa offset,
        // mengikuti jam dinding admin di Indonesia.
        $wib = new \DateTimeZone('Asia/Jakarta');
        $mulai = (new \DateTime('-30 minutes', $wib))->format('Y-m-d H:i:s');
        $selesai = (new \DateTime('-5 minutes', $wib))->format('Y-m-d H:i:s');

        $agenda = $this->agenda(['starts_at' => $mulai, 'ends_at' => $selesai]);

        $this->assertSame(1, Agenda::finished()->count());
        $this->assertSame(1, AgendaPurger::purgeFinished());
        $this->assertDatabaseMissing('agendas', ['id' => $agenda->id]);
    }

    public function test_agenda_berlangsung_tidak_ikut_terhapus(): void
    {
        $berlangsung = $this->agenda([
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]);

        $this->assertSame(0, AgendaPurger::purgeFinished());
        $this->assertDatabaseHas('agendas', ['id' => $berlangsung->id]);
    }

    public function test_agenda_tidak_dihapus_otomatis_oleh_scheduler(): void
    {
        $commands = collect($this->app->make(Schedule::class)->events())
            ->pluck('command')
            ->filter();

        $this->assertFalse($commands->contains(
            fn (string $command) => str_contains($command, 'agendas:purge')
        ));
    }

    public function test_membuka_daftar_agenda_di_admin_tidak_menghapus_data(): void
    {
        $selesai = $this->agenda([
            'title' => 'Agenda Kedaluwarsa',
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.agendas.index'))
            ->assertOk()
            ->assertSee('Agenda Kedaluwarsa');

        $this->assertDatabaseHas('agendas', ['id' => $selesai->id]);
    }
}
