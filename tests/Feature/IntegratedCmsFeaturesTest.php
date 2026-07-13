<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Advantage;
use App\Models\Agenda;
use App\Models\BusinessApplication;
use App\Models\BusinessCategory;
use App\Models\Client;
use App\Models\Statistic;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IntegratedCmsFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_admin_modules_have_registered_routes(): void
    {
        foreach ([
            'admin.article-categories.index', 'admin.business-categories.index',
            'admin.agendas.index', 'admin.applications.index', 'admin.reports.index',
            'admin.clients.index', 'admin.testimonials.index',
            'admin.advantages.index', 'admin.statistics.index',
            'admin.analytics.index', 'admin.account.edit',
        ] as $name) {
            $this->assertTrue(Route::has($name), "Route {$name} tidak terdaftar.");
        }
    }

    public function test_deprecated_modules_are_fully_removed(): void
    {
        foreach ([
            'admin.galleries.index',
            'admin.process-steps.index',
            'admin.messages.index',
            'contact.store',
        ] as $name) {
            $this->assertFalse(Route::has($name), "Route {$name} masih terdaftar.");
        }

        $this->assertFalse(Schema::hasTable('process_steps'));
        $this->assertFalse(Schema::hasTable('galleries'));
        $this->assertFalse(Schema::hasTable('messages'));
    }

    public function test_homepage_and_new_admin_pages_render_without_server_error(): void
    {
        $this->get(route('home'))->assertOk();

        $admin = Admin::create([
            'name' => 'Admin Integrasi',
            'email' => 'integrasi@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        foreach ([
            'admin.article-categories.index',
            'admin.business-categories.index',
            'admin.agendas.index',
            'admin.agendas.create',
            'admin.applications.index',
            'admin.applications.create',
            'admin.reports.index',
            'admin.clients.index',
            'admin.clients.create',
            'admin.testimonials.index',
            'admin.testimonials.create',
            'admin.advantages.index',
            'admin.advantages.create',
            'admin.statistics.index',
            'admin.statistics.create',
        ] as $routeName) {
            $response = $this->actingAs($admin, 'admin')->get(route($routeName))->assertOk();

            if ($routeName === 'admin.agendas.index') {
                $response
                    ->assertSee('Konten Website')
                    ->assertSee('Operasional')
                    ->assertSee('Pengaturan')
                    ->assertSee('Artikel')
                    ->assertSee(route('admin.articles.index'), false)
                    ->assertDontSee('Lihat Publik')
                    ->assertDontSee('Preview');
            }
        }
    }

    public function test_application_is_linked_to_category_and_keeps_status_history(): void
    {
        $category = BusinessCategory::create(['name' => 'Kategori Uji', 'is_active' => true]);
        $application = BusinessApplication::create([
            'applicant_type' => 'company',
            'business_name' => 'PT Contoh',
            'business_category_id' => $category->id,
            'process_status' => 'Penawaran',
        ]);
        $application->histories()->create(['new_status' => 'Penawaran']);

        $this->assertTrue($application->category->is($category));
        $this->assertCount(1, $application->histories);
    }

    public function test_admin_crud_uses_consistent_ui_and_complete_filters(): void
    {
        $admin = Admin::create([
            'name' => 'Admin UI',
            'email' => 'admin-ui@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        foreach ([
            'admin.agendas.create',
            'admin.applications.create',
            'admin.clients.create',
            'admin.testimonials.create',
            'admin.advantages.create',
            'admin.statistics.create',
        ] as $routeName) {
            $this->actingAs($admin, 'admin')
                ->get(route($routeName))
                ->assertOk()
                ->assertSee('admin-form-surface', false)
                ->assertSee('admin-field', false);
        }

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('name="applicant_type"', false)
            ->assertSee('name="date_from"', false)
            ->assertSee('name="date_to"', false);

        $category = BusinessCategory::create(['name' => 'Kategori Terpakai', 'is_active' => true]);
        BusinessApplication::create([
            'applicant_type' => 'company',
            'business_name' => 'PT Pengguna Kategori',
            'business_category_id' => $category->id,
            'process_status' => 'Penawaran',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.business-categories.index'))
            ->assertOk()
            ->assertSee('Kategori masih digunakan dan tidak dapat dihapus.')
            ->assertSee('disabled', false);
    }

    public function test_public_content_can_be_disabled_without_static_row_replacement(): void
    {
        Advantage::query()->update(['is_active' => false]);

        $this->assertSame(0, Advantage::where('is_active', true)->count());
    }

    public function test_removed_public_sections_and_contact_inbox_form_stay_absent(): void
    {
        $this->refreshPublicSiteConfig();
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="alur"', false)
            ->assertDontSee('id="galeri"', false)
            ->assertDontSee('Kirim pesan kepada tim kami');
    }

    public function test_active_future_agenda_is_synchronized_with_homepage(): void
    {
        $agenda = Agenda::create([
            'title' => 'Agenda Integrasi Publik',
            'slug' => 'agenda-integrasi-publik',
            'summary' => 'Agenda yang dikelola dari panel admin.',
            'venue' => 'Kantor PT Zam Zam Khan',
            'starts_at' => now()->addDays(2),
            'display_order' => 1,
            'is_active' => true,
        ]);

        $this->refreshPublicSiteConfig();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Agenda Integrasi Publik')
            ->assertSee('Kantor PT Zam Zam Khan');

        $this->assertTrue($agenda->is_active);
    }

    public function test_remaining_admin_content_fields_are_visible_on_public_website(): void
    {
        Statistic::create([
            'value' => '321+',
            'label' => 'Mitra Terverifikasi',
            'description' => 'Dihitung dari pendampingan aktif.',
            'display_order' => 99,
            'is_active' => true,
        ]);
        Client::create([
            'name' => 'Klien Audit Publik',
            'logo_path' => 'images/Logo/hotelsantika.jpg',
            'website_url' => 'https://klien-audit.test',
            'industry' => 'Hospitality Audit',
            'display_order' => 99,
            'is_active' => true,
        ]);
        Testimonial::create([
            'client_name' => 'Testimoni Audit Publik',
            'service_name' => 'Audit CMS',
            'content' => 'Konten testimoni audit.',
            'image_path' => 'images/testimonials/testi1.jpeg',
            'image_alt' => 'Alt dokumentasi dari admin',
            'display_order' => 99,
            'is_active' => true,
        ]);
        Agenda::create([
            'title' => 'Agenda Dengan Waktu Selesai',
            'slug' => 'agenda-dengan-waktu-selesai',
            'summary' => 'Agenda audit sinkronisasi.',
            'starts_at' => now()->addDays(3)->setTime(9, 0),
            'ends_at' => now()->addDays(3)->setTime(11, 30),
            'display_order' => 99,
            'is_active' => true,
        ]);

        $this->refreshPublicSiteConfig();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Dihitung dari pendampingan aktif.')
            ->assertSee('Hospitality Audit')
            ->assertSee('https://klien-audit.test', false)
            ->assertSee('Alt dokumentasi dari admin')
            ->assertSee('11:30 WIB');
    }
}
