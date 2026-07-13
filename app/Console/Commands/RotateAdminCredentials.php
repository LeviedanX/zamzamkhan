<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RotateAdminCredentials extends Command
{
    protected $signature = 'admin:rotate-credentials {--email= : Email Gmail admin yang akan digunakan} {--name= : Nama admin}';

    protected $description = 'Rotasi email/password admin secara interaktif tanpa menampilkan password di terminal.';

    public function handle(): int
    {
        $existing = Admin::query()->when($this->option('email'), fn ($query, $email) => $query->where('email', Str::lower(trim($email))))->first();
        if (! $existing && Admin::count() === 1) {
            $existing = Admin::first();
        }

        $email = Str::lower(trim((string) ($this->option('email') ?: $this->ask('Email admin', $existing?->email))));
        $name = trim((string) ($this->option('name') ?: $this->ask('Nama admin', $existing?->name ?: 'Administrator PT Zam Zam Khan')));
        $password = (string) $this->secret('Password baru');
        $confirmation = (string) $this->secret('Ulangi password baru');

        $validator = Validator::make(compact('email', 'name', 'password', 'confirmation'), [
            'email' => ['required', 'email:rfc', 'max:160', 'ends_with:@gmail.com', 'unique:admins,email,'.($existing?->id ?? 'NULL')],
            'name' => ['required', 'string', 'max:120'],
            'password' => ['required', Password::min(10)->mixedCase()->numbers(), 'same:confirmation'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($existing, $email, $name, $password): void {
            $admin = $existing ?? new Admin;
            $admin->fill([
                'email' => $email,
                'name' => $name,
                'password' => $password,
                'is_active' => true,
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
