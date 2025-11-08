<?php

namespace App\Http\Middleware;

use App\Services\ModuleAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    protected ModuleAccessService $moduleAccessService;

    public function __construct(ModuleAccessService $moduleAccessService)
    {
        $this->moduleAccessService = $moduleAccessService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $moduleSlug
     */
    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        // Récupérer l'entreprise_id depuis l'utilisateur connecté ou la session
        $entrepriseId = $this->getEntrepriseId($request);

        if (!$entrepriseId) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Entreprise non identifiée'], 403);
            }
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette ressource.');
        }

        // Vérifier l'accès au module
        if (!$this->moduleAccessService->hasAccess($entrepriseId, $moduleSlug)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Ce module n\'est pas disponible dans votre plan d\'abonnement.',
                    'module' => $moduleSlug
                ], 403);
            }

            // Rediriger vers la page des abonnements avec un message d'erreur
            return redirect()->route('subscriptions.index')
                ->with('error', 'Ce module n\'est pas disponible dans votre plan d\'abonnement. Veuillez mettre à jour votre abonnement pour accéder à cette fonctionnalité.');
        }

        return $next($request);
    }

    /**
     * Récupérer l'entreprise_id depuis l'utilisateur connecté ou la session
     */
    protected function getEntrepriseId(Request $request): ?int
    {
        // Essayer depuis l'utilisateur connecté (guard web)
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if (isset($user->entreprise_id)) {
                return $user->entreprise_id;
            }
        }

        // Essayer depuis la session
        if ($request->session()->has('entreprise_id')) {
            return $request->session()->get('entreprise_id');
        }

        // Essayer depuis le paramètre de la requête (pour les routes avec {entreprise_id})
        if ($request->has('entreprise_id')) {
            return (int) $request->input('entreprise_id');
        }

        // Essayer depuis le paramètre de route
        if ($request->route('entreprise_id')) {
            return (int) $request->route('entreprise_id');
        }

        // Essayer depuis le paramètre de route 'id' si c'est une entreprise
        if ($request->route('id')) {
            $id = (int) $request->route('id');
            // Vérifier si c'est une entreprise (optionnel, peut être amélioré)
            return $id;
        }

        return null;
    }
}
