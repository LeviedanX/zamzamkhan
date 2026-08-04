<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminSeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_menyimpan_admin_hanya_di_tabel_users(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertFalse(Schema::hasTable('admins'));
        $this->assertTrue(Schema::hasColumns('users', [
            'is_admin',
            'is_active',
            'auth_version',
            'last_login_at',
        ]));
    }

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

        $admin = User::where('is_admin', true)->firstOrFail();
        $this->assertSame(1, $admin->id);
        $this->assertSame('secure-admin@example.test', $admin->email);
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('Password-Acak-123!', $admin->password));
    }

    public function test_seeding_ulang_tidak_mereset_password_admin_existing(): void
    {
        $admin = User::create([
            'is_admin' => true,
            'name' => 'Admin Lama',
            'email' => 'secure-admin@example.test',
            'password' => 'Password-Lama-456!',
            'is_active' => true,
        ]);

        config()->set('admin.seed.email', $admin->email);
        config()->set('admin.seed.password', 'Password-Baru-Tidak-Dipakai-789!');
        config()->set('admin.seed.name', 'Admin Diperbarui');

        $this->seed(AdminSeeder::class);

        $admin->refresh();
        $this->assertSame('Admin Diperbarui', $admin->name);
        $this->assertTrue(Hash::check('Password-Lama-456!', $admin->password));
        $this->assertFalse(Hash::check('Password-Baru-Tidak-Dipakai-789!', $admin->password));
    }

    public function test_seeding_ulang_tidak_mengembalikan_email_yang_sudah_diubah_dari_panel(): void
    {
        $admin = User::create([
            'is_admin' => true,
            'name' => 'Admin Lama',
            'email' => 'email-panel@gmail.com',
            'password' => 'Password-Lama-456!',
            'is_active' => true,
        ]);

        config()->set('admin.seed.email', 'email-env-lama@gmail.com');
        config()->set('admin.seed.password', 'Password-Env-789!');
        config()->set('admin.seed.name', 'Admin Diperbarui');

        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertSame('email-panel@gmail.com', $admin->fresh()->email);
        $this->assertTrue(Hash::check('Password-Lama-456!', $admin->fresh()->password));
    }
}
