<?php

namespace App\Rules;

use App\Support\SafeUrl;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GoogleMapsUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if (! SafeUrl::googleMaps($value)) {
            $fail('Kolom :attribute harus berupa URL Google Maps yang valid.');
        }
    }
}
