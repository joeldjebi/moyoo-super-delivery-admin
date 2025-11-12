<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('platform_admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }
            return redirect()->route('platform-admin.login');
        }

        $admin = Auth::guard('platform_admin')->user();

        if (!$admin->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès refusé. Seuls les super administrateurs peuvent accéder à cette ressource.'], 403);
            }
            abort(403, 'Accès refusé. Seuls les super administrateurs peuvent accéder à cette ressource.');
        }

        return $next($request);
    }
}

