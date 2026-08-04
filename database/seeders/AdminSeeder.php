<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AdminSecurity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Kredensial diambil dari .env — TIDAK di-hardcode untuk produksi.
        // Password otomatis di-hash oleh cast 'hashed' pada model User.
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

        // Seeder hanya membuat akun pertama. Menjalankannya kembali tidak boleh
        // mengembalikan email/password yang sudah diubah melalui panel admin.
        $admin = User::query()->where('is_admin', true)->orderBy('id')->first();
        if (! $admin) {
            $admin = User::firstOrNew(['email' => mb_strtolower($email)]);
        }

        if (! $admin->exists) {
            if (User::query()->whereKey(1)->exists()) {
                throw new \LogicException('ID 1 sudah digunakan akun lain. Admin pertama tidak dapat dibuat dengan ID 1.');
            }

            $admin->id = 1;
            $admin->email = mb_strtolower($email);
            $admin->password = $password;
        }

        $admin->name = (string) config('admin.seed.name');
        $admin->is_admin = true;
        $admin->is_active = true;

        $admin->save();
    }
}
