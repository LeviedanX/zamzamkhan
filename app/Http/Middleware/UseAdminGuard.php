<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UseAdminGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        // DatabaseSessionHandler mengambil user_id dari guard default saat sesi ditulis.
        Auth::shouldUse('admin');

        return $next($request);
    }
}
