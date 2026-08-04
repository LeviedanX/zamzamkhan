<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\AdminSecurity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RotateAdminCredentials extends Command
{
    protected $signature = 'admin:rotate-credentials {--email= : Email Gmail admin yang akan digunakan} {--name= : Nama admin}';

    protected $description = 'Rotasi email/password admin secara interaktif tanpa menampilkan password di terminal.';

    public function handle(): int
    {
        $adminUsers = User::query()->where('is_admin', true);
        $existing = (clone $adminUsers)
            ->when($this->option('email'), fn ($query, $email) => $query->where('email', Str::lower(trim($email))))
            ->first();
        if (! $existing && (clone $adminUsers)->count() === 1) {
            $existing = (clone $adminUsers)->first();
        }

        $email = Str::lower(trim((string) ($this->option('email') ?: $this->ask('Email admin', $existing?->email))));
        $name = trim((string) ($this->option('name') ?: $this->ask('Nama admin', $existing?->name ?: 'Administrator PT Zam Zam Khan')));
        $password = (string) $this->secret('Password baru');
        $confirmation = (string) $this->secret('Ulangi password baru');

        $validator = Validator::make(compact('email', 'name', 'password', 'confirmation'), [
            'email' => ['required', 'email:rfc', 'max:160', 'ends_with:@gmail.com', 'unique:users,email,'.($existing?->id ?? 'NULL')],
            'name' => ['required', 'string', 'max:120'],
            'password' => ['required', AdminSecurity::passwordRule(), 'same:confirmation'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($existing, $email, $name, $password): void {
            $admin = $existing ?? new User;
            if (! $existing) {
                if (User::query()->whereKey(1)->exists()) {
                    throw new \LogicException('ID 1 sudah digunakan akun lain. Admin pertama tidak dapat dibuat dengan ID 1.');
                }

                $admin->id = 1;
            }
            $admin->fill([
                'email' => $email,
                'name' => $name,
                'password' => $password,
                'is_admin' => true,
                'is_active' => true,
                'auth_version' => $existing ? max(1, (int) $admin->auth_version) + 1 : 1,
            ]);
            $admin->setRememberToken(Str::random(60));
            $admin->save();

            if (config('session.driver') === 'database') {
                DB::table((string) config('session.table', 'sessions'))->where('user_id', $admin->id)->delete();
            }
        });

        $this->info('Credential admin berhasil dirotasi. Semua sesi database akun tersebut telah dicabut.');

        return self::SUCCESS;
    }
}
