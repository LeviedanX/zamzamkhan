<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

final class AdminSecurity
{
    public const SESSION_AUTH_VERSION = 'admin_auth_version';

    public const SESSION_STARTED_AT = 'admin_session_started_at';

    public const SESSION_LAST_ACTIVITY_AT = 'admin_session_last_activity_at';

    public static function passwordRule(): Password
    {
        return Password::min(14)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();
    }
}
