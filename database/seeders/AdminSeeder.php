<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Support\AdminSecurity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

        try {
            Validator::make(
                ['email' => $email, 'password' => $password],
                ['email' => ['required', 'email:rfc'], 'password' => ['required', AdminSecurity::passwordRule()]],
            )->validate();
        } catch (ValidationException $e) {
            throw new \LogicException('Credential AdminSeeder tidak memenuhi kebijakan keamanan.', previous: $e);
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
