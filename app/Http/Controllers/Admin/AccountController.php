<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.account.edit', ['admin' => $request->user('admin')]);
    }

    public function update(Request $request)
    {
        /** @var Admin $admin */
        $admin = $request->user('admin');
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'current_email' => ['required', 'email'],
            'current_password' => ['required', 'string'],
            'email' => ['required', 'email:rfc', 'max:160', 'ends_with:@gmail.com', Rule::unique('admins', 'email')->ignore($admin->id)],
            'password' => ['nullable', 'confirmed', Password::min(10)->mixedCase()->numbers()],
        ], [
            'current_email.required' => 'Email akun lama wajib diisi.',
            'current_password.required' => 'Password akun lama wajib diisi.',
            'email.ends_with' => 'Email admin wajib menggunakan domain @gmail.com.',
            'email.unique' => 'Email tersebut sudah digunakan akun admin lain.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 10 karakter.',
        ]);

        $oldEmailValid = hash_equals(strtolower($admin->email), strtolower($data['current_email']));
        if (! $oldEmailValid || ! Hash::check($data['current_password'], $admin->password)) {
            throw ValidationException::withMessages([
                'current_credentials' => 'Email atau password akun lama tidak cocok. Perubahan dibatalkan.',
            ]);
        }

        $emailChanged = ! hash_equals(strtolower($admin->email), $data['email']);
        $passwordChanged = filled($data['password'] ?? null);
        if (! $emailChanged && ! $passwordChanged) {
            throw ValidationException::withMessages(['email' => 'Tidak ada perubahan email atau password untuk disimpan.']);
        }

        $admin->email = $data['email'];
        if ($passwordChanged) {
            $admin->password = $data['password'];
        }
        $admin->setRememberToken(Str::random(60));
        $admin->save();

        if (config('session.driver') === 'database') {
            DB::table((string) config('session.table', 'sessions'))
                ->where('user_id', $admin->getAuthIdentifier())
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        $request->session()->regenerate();

        return redirect()->route('admin.account.edit')->with('ok', 'Kredensial akun admin berhasil diperbarui.');
    }
}
