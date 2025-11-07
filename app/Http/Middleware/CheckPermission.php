<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!Auth::guard('platform_admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }
            return redirect()->route('platform-admin.login');
        }

        $admin = Auth::guard('platform_admin')->user();

        // Super admin a toutes les permissions
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Vérifier si l'admin a au moins une des permissions requises
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($admin->hasPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès refusé. Permission requise : ' . implode(', ', $permissions)], 403);
            }
            abort(403, 'Accès refusé. Vous n\'avez pas la permission requise.');
        }

        return $next($request);
    }
}
