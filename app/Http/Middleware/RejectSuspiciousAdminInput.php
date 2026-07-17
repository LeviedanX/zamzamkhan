<?php

namespace App\Http\Middleware;

use App\Support\SuspiciousContent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RejectSuspiciousAdminInput
{
    /** @var list<string> */
    private const EXCLUDED_KEYS = [
        '_token',
        '_method',
        'password',
        'password_confirmation',
        'current_password',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $input = $request->except(self::EXCLUDED_KEYS);

        // Frasa pencarian Shortcut tidak dipublikasikan dan perlu dapat memuat
        // spam/judol yang hendak dibersihkan. Teks pengganti tetap diperiksa.
        if ($request->routeIs('admin.shortcuts.preview')) {
            unset($input['search_text']);
        }

        $blocked = $this->findBlockedValue(
            $input,
            (bool) config('admin.block_gambling_content', true),
        );

        if ($blocked) {
            throw ValidationException::withMessages([
                $blocked['key'] => $blocked['reason'] === 'gambling-spam'
                    ? 'Konten terindikasi spam perjudian/judol dan ditolak.'
                    : 'Konten memuat pola sisipan aktif yang berbahaya dan ditolak.',
            ]);
        }

        return $next($request);
    }

    /** @return array{key:string, reason:string}|null */
    private function findBlockedValue(array $input, bool $blockGambling, string $prefix = ''): ?array
    {
        foreach ($input as $key => $value) {
            $field = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                if ($found = $this->findBlockedValue($value, $blockGambling, $field)) {
                    return $found;
                }

                continue;
            }

            if (! is_string($value) || $value === '') {
                continue;
            }

            if ($reason = SuspiciousContent::reason($value, $blockGambling)) {
                return ['key' => $field, 'reason' => $reason];
            }
        }

        return null;
    }
}
