<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_shell_publik_dan_drawer_mobile_memakai_viewport_penuh(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('content="width=device-width, initial-scale=1, viewport-fit=cover"', false)
            ->assertSee('<template x-teleport="body">', false)
            ->assertSee('class="site-drawer-layer fixed inset-0', false)
            ->assertSee('class="site-drawer-panel absolute', false);

        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('.site-drawer-layer {', $css);
        $this->assertStringContainsString('height: 100dvh;', $css);
    }

    public function test_login_dan_panel_admin_memakai_viewport_responsif_yang_sama(): void
    {
        $viewport = 'content="width=device-width, initial-scale=1, viewport-fit=cover"';

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee($viewport, false);

        $admin = Admin::create([
            'name' => 'Admin Responsive',
            'email' => 'responsive@uji.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($viewport, false);
    }
}
