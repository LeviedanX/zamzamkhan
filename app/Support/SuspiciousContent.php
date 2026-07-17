<?php

namespace App\Support;

final class SuspiciousContent
{
    private const INJECTION_PATTERN = <<<'REGEX'
~(?:<\s*/?\s*(?:script|iframe|object|embed|meta|base|link|form|svg|math)\b|on[a-z]{3,}\s*=|javascript\s*:|data\s*:\s*text/html|<\?php|\{\!\!|srcdoc\s*=|__halt_compiler|&#0*60;?\s*script)~iu
REGEX;

    private const GAMBLING_PATTERN = <<<'REGEX'
~(?:\bjudi[\W_]*online\b|\bjudol\b|\bslot[\W_]*(?:gacor|online)\b|\btogel\b|\bcasino[\W_]*online\b|\bmaxwin\b|\bscatter[\W_]*(?:hitam|mahjong)\b)~iu
REGEX;

    private const GAMBLING_COMPACT_PATTERN = <<<'REGEX'
~(?:judionline|judol|slotgacor|slotonline|togel|casinoonline|maxwin|scatterhitam|scattermahjong)~iu
REGEX;

    public static function reason(string $value, bool $blockGambling = true): ?string
    {
        $normalized = self::normalize($value);

        if (str_contains($normalized, "\0") || preg_match(self::INJECTION_PATTERN, $normalized) === 1) {
            return 'injection';
        }

        if ($blockGambling) {
            $leet = strtr(mb_strtolower($normalized, 'UTF-8'), [
                '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a',
                '5' => 's', '7' => 't', '@' => 'a', '$' => 's',
            ]);
            $compact = preg_replace('/[^\p{L}\p{N}]+/u', '', $leet) ?? $leet;

            if (preg_match(self::GAMBLING_PATTERN, $leet) === 1
                || preg_match(self::GAMBLING_COMPACT_PATTERN, $compact) === 1) {
                return 'gambling-spam';
            }
        }

        return null;
    }

    private static function normalize(string $value): string
    {
        $value = preg_replace('/[\x{200B}-\x{200D}\x{2060}\x{FEFF}]/u', '', $value) ?? $value;

        for ($i = 0; $i < 2; $i++) {
            $decoded = rawurldecode($value);
            if ($decoded === $value) {
                break;
            }
            $value = $decoded;
        }

        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
