<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::guard('platform_admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }
            return redirect()->route('platform-admin.login');
        }

        $admin = Auth::guard('platform_admin')->user();

        // Super admin a tous les rôles
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Vérifier si l'admin a au moins un des rôles requis
        if (!$admin->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès refusé. Rôle requis : ' . implode(', ', $roles)], 403);
            }
            abort(403, 'Accès refusé. Vous n\'avez pas le rôle requis.');
        }

        return $next($request);
    }
}
