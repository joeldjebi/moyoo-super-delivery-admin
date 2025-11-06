<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier que l'utilisateur est authentifié avec le guard platform_admin
        if (!Auth::guard('platform_admin')->check()) {
            return redirect()->route('platform-admin.login')
                ->with('error', 'Vous devez être connecté en tant que super administrateur pour accéder à cette page.');
        }

        $admin = Auth::guard('platform_admin')->user();

        // Vérifier que le compte est actif
        if (!$admin->isActive()) {
            Auth::guard('platform_admin')->logout();
            return redirect()->route('platform-admin.login')
                ->with('error', 'Votre compte est inactif. Veuillez contacter le support.');
        }

        // Vérifier que le compte n'est pas bloqué
        if ($admin->isLocked()) {
            Auth::guard('platform_admin')->logout();
            return redirect()->route('platform-admin.login')
                ->with('error', 'Votre compte est temporairement bloqué. Veuillez réessayer plus tard.');
        }

        return $next($request);
    }
}
