<?php

namespace Tests\Feature;

use App\Models\Admin;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_gagal_bila_credential_tidak_disediakan(): void
    {
        config()->set('admin.seed.email');
        config()->set('admin.seed.password');

        $this->expectException(\LogicException::class);
        $this->seed(AdminSeeder::class);
    }

    public function test_seeder_membuat_admin_baru_tanpa_credential_fallback(): void
    {
        config()->set('admin.seed.email', 'secure-admin@example.test');
        config()->set('admin.seed.password', 'Password-Acak-123!');
        config()->set('admin.seed.name', 'Admin Aman');

        $this->seed(AdminSeeder::class);

        $admin = Admin::firstOrFail();
        $this->assertSame('secure-admin@example.test', $admin->email);
        $this->assertTrue(Hash::check('Password-Acak-123!', $admin->password));
    }

    public function test_seeding_ulang_tidak_mereset_password_admin_existing(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Lama',
            'email' => 'secure-admin@example.test',
            'password' => 'Password-Lama-456!',
            'is_active' => true,
        ]);

        config()->set('admin.seed.email', $admin->email);
        config()->set('admin.seed.password', 'Password-Baru-Yang-Tidak-Boleh-Dipakai!');
        config()->set('admin.seed.name', 'Admin Diperbarui');

        $this->seed(AdminSeeder::class);

        $admin->refresh();
        $this->assertSame('Admin Diperbarui', $admin->name);
        $this->assertTrue(Hash::check('Password-Lama-456!', $admin->password));
        $this->assertFalse(Hash::check('Password-Baru-Yang-Tidak-Boleh-Dipakai!', $admin->password));
    }
}
