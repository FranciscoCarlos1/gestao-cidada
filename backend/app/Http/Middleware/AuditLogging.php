<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogging
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log da requisição (POST, PATCH, DELETE)
        if (in_array($request->method(), ['POST', 'PATCH', 'DELETE', 'PUT'])) {
            \App\Models\AuditLog::log(
                strtolower($request->method()),
                null,
                null,
                null
            );
        }

        return $next($request);
    }
}
