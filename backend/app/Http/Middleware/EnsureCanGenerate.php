<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanGenerate
{
    /**
     * Roles that are allowed to generate/write data.
     * 'viewer' role is restricted to read-only operations.
     */
    private const ALLOWED_ROLES = ['super_admin', 'admin', 'analyst'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, self::ALLOWED_ROLES, true)) {
            return response()->json([
                'message' => 'Forbidden. Your role does not have permission to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
