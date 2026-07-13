<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModuleNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tombol_kiri_mengarah_ke_modul_sebelumnya(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Navigasi',
            'email' => 'navigasi@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.hero.edit'))
            ->assertOk()
            ->assertSee($this->previousLink(route('admin.dashboard')), false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee($this->previousLink(route('admin.hero.edit')), false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee($this->previousLink(route('admin.settings.edit')), false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.advantages.index'))
            ->assertOk()
            ->assertSee($this->previousLink(route('admin.services.index')), false);
    }

    public function test_sidebar_tampil_dari_kiri_dan_memuat_semua_modul_aktif(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Sidebar',
            'email' => 'sidebar@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('x-transition:enter-start="-translate-x-full"', false)
            ->assertSee('<details class="admin-drawer__section admin-nav-group" open>', false)
            ->assertSeeText('Operasional Internal');

        $moduleRoutes = [
            'admin.hero.edit',
            'admin.settings.edit',
            'admin.services.index',
            'admin.articles.index',
            'admin.faqs.index',
            'admin.article-categories.index',
            'admin.advantages.index',
            'admin.statistics.index',
            'admin.clients.index',
            'admin.testimonials.index',
            'admin.agendas.index',
            'admin.business-categories.index',
            'admin.applications.index',
            'admin.reports.index',
            'admin.analytics.index',
            'admin.seo.edit',
            'admin.account.edit',
        ];

        foreach ($moduleRoutes as $routeName) {
            $response->assertSee(
                'href="'.route($routeName).'" class="admin-drawer__link',
                false,
            );
        }

        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('inset: 0 auto 0 0;', $css);
        $this->assertStringContainsString('box-shadow: 28px 0 70px', $css);
    }

    private function previousLink(string $url): string
    {
        return 'href="'.$url.'" class="admin-module-nav__item admin-module-nav__item--prev"';
    }
}
