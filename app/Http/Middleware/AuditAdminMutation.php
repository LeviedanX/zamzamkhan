<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditAdminMutation
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodSafe() && $request->user('admin')) {
            Log::channel('security')->notice('admin_mutation', [
                'admin_id' => $request->user('admin')->getAuthIdentifier(),
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'status' => $response->getStatusCode(),
                'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
            ]);
        }

        return $response;
    }
}
