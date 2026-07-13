<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Kredensial diambil dari .env — TIDAK di-hardcode untuk produksi.
        // Password otomatis di-hash oleh cast 'hashed' pada model Admin.
        $email = trim((string) config('admin.seed.email'));
        $password = (string) config('admin.seed.password');

        if ($email === '' || $password === '') {
            throw new \LogicException('ADMIN_EMAIL dan ADMIN_PASSWORD wajib diisi sebelum menjalankan AdminSeeder.');
        }

        $admin = Admin::firstOrNew(['email' => mb_strtolower($email)]);
        $admin->name = (string) config('admin.seed.name');
        $admin->is_active = true;

        // Seeding ulang tidak boleh mereset password akun yang sudah ada.
        if (! $admin->exists) {
            $admin->password = $password;
        }

        $admin->save();
    }
}
