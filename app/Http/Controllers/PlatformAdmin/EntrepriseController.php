<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\Marchand;
use App\Models\Boutique;
use App\Models\Colis;
use App\Models\HistoriqueLivraison;
use App\Models\PackageColis;
use App\Models\Ramassage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EntrepriseController extends Controller
{
    /**
     * Vérifier la permission pour accéder aux entreprises
     */
    private function checkPermission(string $permission = 'entreprises.read'): void
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission($permission)) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder à cette ressource.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Entreprises';
        $data['menu'] = 'entreprises';

        // Récupérer les entreprises avec leur abonnement actuel
        $query = DB::table('entreprises')
            ->leftJoin('subscription_plans', function($join) {
                $join->on('entreprises.id', '=', 'subscription_plans.entreprise_id')
                     ->where('subscription_plans.is_active', '=', true);
            })
            ->leftJoin('pricing_plans', 'subscription_plans.pricing_plan_id', '=', 'pricing_plans.id')
            ->select(
                'entreprises.*',
                'subscription_plans.id as subscription_id',
                'subscription_plans.name as subscription_name',
                'subscription_plans.price as subscription_price',
                'subscription_plans.currency as subscription_currency',
                'subscription_plans.started_at as subscription_started_at',
                'subscription_plans.expires_at as subscription_expires_at',
                'subscription_plans.is_active as subscription_is_active',
                'pricing_plans.name as pricing_plan_name'
            )
            ->whereNull('entreprises.deleted_at');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('entreprises.name', 'like', "%{$search}%")
                  ->orWhere('entreprises.email', 'like', "%{$search}%")
                  ->orWhere('entreprises.mobile', 'like', "%{$search}%");
            });
        }

        // Filtre par statut (1 = actif, autre = inactif)
        if ($request->has('statut') && $request->statut !== '') {
            $query->where('entreprises.statut', $request->statut);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['entreprises'] = $query->orderBy('entreprises.created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Calculer les statistiques sur les entreprises
        $data['stats'] = [
            'total_entreprises' => DB::table('entreprises')->whereNull('deleted_at')->count(),
            'entreprises_actives' => DB::table('entreprises')->where('statut', 1)->whereNull('deleted_at')->count(),
            'entreprises_inactives' => DB::table('entreprises')->where('statut', 0)->whereNull('deleted_at')->count(),
            'avec_abonnement' => DB::table('entreprises')
                ->join('subscription_plans', 'entreprises.id', '=', 'subscription_plans.entreprise_id')
                ->where('subscription_plans.is_active', true)
                ->whereNull('entreprises.deleted_at')
                ->distinct()
                ->count('entreprises.id'),
            'sans_abonnement' => DB::table('entreprises')
                ->leftJoin('subscription_plans', function($join) {
                    $join->on('entreprises.id', '=', 'subscription_plans.entreprise_id')
                         ->where('subscription_plans.is_active', true);
                })
                ->whereNull('entreprises.deleted_at')
                ->whereNull('subscription_plans.id')
                ->count(),
            'abonnements_expires' => DB::table('subscription_plans')
                ->where('is_active', true)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count(),
            'total_abonnements' => DB::table('subscription_plans')
                ->where('is_active', true)
                ->count(),
        ];

        return view('platform-admin.entreprises.index', $data);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Voir une entreprise';
        $data['menu'] = 'entreprises';

        // Récupérer l'entreprise avec les relations
        $entreprise = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer le propriétaire (created_by)
        $proprietaire = null;
        if ($entreprise->created_by) {
            $proprietaire = DB::table('users')
                ->where('id', $entreprise->created_by)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer la commune
        $commune = null;
        if ($entreprise->commune_id) {
            $commune = DB::table('communes')
                ->where('id', $entreprise->commune_id)
                ->whereNull('deleted_at')
                ->first();
        }

        $data['entreprise'] = $entreprise;
        $data['proprietaire'] = $proprietaire;
        $data['commune'] = $commune;

        return view('platform-admin.entreprises.show', $data);
    }

    /**
     * Activer ou désactiver une entreprise
     */
    public function toggleStatus(string $id)
    {
        $this->checkPermission('entreprises.toggle_status');
        $data['title'] = 'Activer ou désactiver une entreprise';
        $data['menu'] = 'entreprises';
        $entreprise = Entreprise::find($id);

        // Inverser le statut : 1 devient 0, 0 devient 1
        $newStatus = $entreprise->statut == 1 ? 0 : 1;
        $statusLabel = $newStatus == 1 ? 'activée' : 'désactivée';

        $entreprise->statut = $newStatus;
        $entreprise->save();

        Log::info('Statut entreprise modifié par super admin', [
            'admin_id' => auth()->guard('platform_admin')->id(),
            'entreprise_id' => $id,
            'ancien_statut' => $entreprise->statut,
            'nouveau_statut' => $newStatus
        ]);

        return redirect()->route('platform-admin.entreprises.index')
            ->with('success', "Entreprise {$statusLabel} avec succès.");
    }

    /**
     * Afficher les marchands et boutiques de l'entreprise
     */
    public function marchands(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Marchands & Boutiques';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer les marchands avec leurs boutiques
        $query = Marchand::with(['commune', 'boutiques'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['marchands'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.marchands', $data);
    }

    /**
     * Afficher tous les utilisateurs d'une entreprise
     */
    public function users(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Utilisateurs de l\'entreprise';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer les utilisateurs de l'entreprise
        $query = DB::table('users')
            ->leftJoin('entreprises', 'users.entreprise_id', '=', 'entreprises.id')
            ->select(
                'users.*',
                'entreprises.name as entreprise_name',
                'entreprises.statut as entreprise_statut'
            )
            ->where('users.entreprise_id', $id)
            ->whereNull('users.deleted_at');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.first_name', 'like', "%{$search}%")
                  ->orWhere('users.last_name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.mobile', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status) {
            $query->where('users.status', $request->status);
        }

        // Filtre par type d'utilisateur
        if ($request->has('user_type') && $request->user_type) {
            $query->where('users.user_type', $request->user_type);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['users'] = $query->orderBy('users.created_at', 'desc')->paginate($perPage)->appends(request()->query());

        return view('platform-admin.entreprises.users', $data);
    }

    /**
     * Afficher la liste des boutiques d'une entreprise
     */
    public function boutiques(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Boutiques de l\'entreprise';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer les boutiques avec leurs relations
        $query = Boutique::with(['marchand'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('libelle', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('adresse', 'like', "%{$search}%")
                  ->orWhereHas('marchand', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filtre par marchand
        if ($request->has('marchand_id') && $request->marchand_id) {
            $query->where('marchand_id', $request->marchand_id);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['boutiques'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des marchands pour le filtre
        $data['marchands'] = Marchand::where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('platform-admin.entreprises.boutiques', $data);
    }

    /**
     * Afficher les détails d'une boutique
     */
    public function showBoutique(string $id, string $boutique_id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Détails de la boutique';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer la boutique avec ses relations
        $data['boutique'] = Boutique::with(['marchand', 'entreprise'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($boutique_id);

        // Récupérer la commune si disponible (via le marchand)
        $data['commune'] = null;
        if ($data['boutique']->marchand && $data['boutique']->marchand->commune_id) {
            $data['commune'] = DB::table('communes')
                ->where('id', $data['boutique']->marchand->commune_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer l'utilisateur qui a créé la boutique
        $data['createur'] = null;
        if ($data['boutique']->created_by) {
            $data['createur'] = DB::table('users')
                ->where('id', $data['boutique']->created_by)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer les IDs des package_colis liés à cette boutique
        $packageColisIds = PackageColis::where('boutique_id', $boutique_id)
            ->pluck('id')
            ->toArray();

        // Calculer les statistiques des colis
        // Si aucun package_colis n'est lié à cette boutique, retourner 0 pour toutes les stats
        if (empty($packageColisIds)) {
            $colisStats = [
                'total' => 0,
                'termine' => 0,
                'en_cours' => 0,
                'annule' => 0,
                'en_attente' => 0,
            ];
        } else {
            $colisQuery = Colis::where('entreprise_id', $id)
                ->whereIn('package_colis_id', $packageColisIds)
                ->whereNull('deleted_at');

            $colisStats = [
                'total' => $colisQuery->count(),
                'termine' => (clone $colisQuery)->where('status', 2)->count(), // status = 2: livré
                'en_cours' => (clone $colisQuery)->where('status', 1)->count(), // status = 1: en cours
                'annule' => (clone $colisQuery)->whereIn('status', [3, 4, 5])->count(), // status = 3,4,5: annulés
                'en_attente' => (clone $colisQuery)->where('status', 0)->count(), // status = 0: en attente
            ];
        }

        // Calculer les statistiques des ramassages (table ramassages via boutique_id)
        $ramassagesQuery = Ramassage::where('entreprise_id', $id)
            ->where('boutique_id', $boutique_id);

        $ramassagesStats = [
            'total' => $ramassagesQuery->count(),
            'termine' => (clone $ramassagesQuery)->where('statut', 'termine')->count(),
            'en_cours' => (clone $ramassagesQuery)->where('statut', 'en_cours')->count(),
            'annule' => (clone $ramassagesQuery)->where('statut', 'annule')->count(),
            'planifie' => (clone $ramassagesQuery)->where('statut', 'planifie')->count(),
        ];

        // Calculer les statistiques des livraisons (historique_livraisons via package_colis)
        // Si aucun package_colis n'est lié à cette boutique, retourner 0 pour toutes les stats
        if (empty($packageColisIds)) {
            $livraisonsStats = [
                'total' => 0,
                'termine' => 0,
                'en_cours' => 0,
                'annule' => 0,
                'echoue' => 0,
            ];
        } else {
            $livraisonsQuery = HistoriqueLivraison::where('entreprise_id', $id)
                ->whereIn('package_colis_id', $packageColisIds)
                ->whereNull('deleted_at');

            $livraisonsStats = [
                'total' => $livraisonsQuery->count(),
                'termine' => (clone $livraisonsQuery)->where('status', 'termine')->count(),
                'en_cours' => (clone $livraisonsQuery)->where('status', 'en_cours')->count(),
                'annule' => (clone $livraisonsQuery)->where('status', 'annule')->count(),
                'echoue' => (clone $livraisonsQuery)->where('status', 'echoue')->count(),
            ];
        }

        $data['stats'] = [
            'colis' => $colisStats,
            'ramassages' => $ramassagesStats,
            'livraisons' => $livraisonsStats,
        ];

        return view('platform-admin.entreprises.boutique.show', $data);
    }

    /**
     * Afficher les colis d'une boutique
     */
    public function boutiqueColis(string $id, string $boutique_id, Request $request)
    {
        $data['title'] = 'Colis de la boutique';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['boutique'] = Boutique::with(['marchand', 'entreprise'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($boutique_id);

        // Récupérer les IDs des package_colis liés à cette boutique
        $packageColisIds = PackageColis::where('boutique_id', $boutique_id)
            ->pluck('id')
            ->toArray();

        // Récupérer les colis de la boutique
        $query = Colis::where('entreprise_id', $id)
            ->whereNull('deleted_at');

        if (!empty($packageColisIds)) {
            $query->whereIn('package_colis_id', $packageColisIds);
        } else {
            // Si aucun package_colis, retourner une requête vide
            $query->whereRaw('1 = 0');
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('nom_client', 'like', "%{$search}%")
                  ->orWhere('telephone_client', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['colis'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.boutique.colis', $data);
    }

    /**
     * Afficher les ramassages d'une boutique
     */
    public function boutiqueRamassages(string $id, string $boutique_id, Request $request)
    {
        $data['title'] = 'Ramassages de la boutique';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['boutique'] = Boutique::with(['marchand', 'entreprise'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($boutique_id);

        // Récupérer les ramassages de la boutique
        $query = Ramassage::with(['boutique', 'livreur'])
            ->where('entreprise_id', $id)
            ->where('boutique_id', $boutique_id);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code_ramassage', 'like', "%{$search}%")
                  ->orWhere('contact_ramassage', 'like', "%{$search}%")
                  ->orWhere('adresse_ramassage', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->has('statut') && $request->statut !== '') {
            $query->where('statut', $request->statut);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['ramassages'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.boutique.ramassages', $data);
    }

    /**
     * Afficher les livraisons d'une boutique
     */
    public function boutiqueLivraisons(string $id, string $boutique_id, Request $request)
    {
        $data['title'] = 'Livraisons de la boutique';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['boutique'] = Boutique::with(['marchand', 'entreprise'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($boutique_id);

        // Récupérer les IDs des package_colis liés à cette boutique
        $packageColisIds = PackageColis::where('boutique_id', $boutique_id)
            ->pluck('id')
            ->toArray();

        // Récupérer les livraisons de la boutique
        $query = HistoriqueLivraison::with(['colis'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at');

        if (!empty($packageColisIds)) {
            $query->whereIn('package_colis_id', $packageColisIds);
        } else {
            // Si aucun package_colis, retourner une requête vide
            $query->whereRaw('1 = 0');
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('status', 'like', "%{$search}%")
                  ->orWhere('note_livraison', 'like', "%{$search}%")
                  ->orWhere('code_validation_utilise', 'like', "%{$search}%")
                  ->orWhere('motif_annulation', 'like', "%{$search}%")
                  ->orWhereHas('colis', function($q) use ($search) {
                      $q->where('code', 'like', "%{$search}%")
                        ->orWhere('nom_client', 'like', "%{$search}%")
                        ->orWhere('telephone_client', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['livraisons'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.boutique.livraisons', $data);
    }

    /**
     * Afficher les détails d'un colis d'une boutique
     */
    public function showBoutiqueColis(string $id, string $boutique_id, string $colis_id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Détails du colis';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['boutique'] = Boutique::with(['marchand', 'entreprise'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($boutique_id);

        // Récupérer les IDs des package_colis liés à cette boutique
        $packageColisIds = PackageColis::where('boutique_id', $boutique_id)
            ->pluck('id')
            ->toArray();

        // Récupérer le colis avec ses relations
        $data['colis'] = Colis::with(['entreprise', 'packageColis'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($colis_id);

        // Vérifier que le colis appartient bien à la boutique
        if (!empty($packageColisIds) && !in_array($data['colis']->package_colis_id, $packageColisIds)) {
            abort(403, 'Ce colis n\'appartient pas à cette boutique.');
        }

        // Récupérer la commune
        $data['commune'] = null;
        if ($data['colis']->commune_id) {
            $data['commune'] = DB::table('communes')
                ->where('id', $data['colis']->commune_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer la zone
        $data['zone'] = null;
        if ($data['colis']->zone_id) {
            $data['zone'] = DB::table('zones')
                ->where('id', $data['colis']->zone_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer le livreur
        $data['livreur'] = null;
        if ($data['colis']->livreur_id) {
            $data['livreur'] = DB::table('livreurs')
                ->where('id', $data['colis']->livreur_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer l'utilisateur qui a créé le colis
        $data['createur'] = null;
        if ($data['colis']->created_by) {
            $data['createur'] = DB::table('users')
                ->where('id', $data['colis']->created_by)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer l'engin si disponible
        $data['engin'] = null;
        if ($data['colis']->engin_id) {
            $data['engin'] = DB::table('engins')
                ->where('id', $data['colis']->engin_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer le poids si disponible
        $data['poids'] = null;
        if ($data['colis']->poids_id) {
            $data['poids'] = DB::table('poids')
                ->where('id', $data['colis']->poids_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer le mode de livraison si disponible
        $data['mode_livraison'] = null;
        if ($data['colis']->mode_livraison_id) {
            $data['mode_livraison'] = DB::table('mode_livraisons')
                ->where('id', $data['colis']->mode_livraison_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer le temp si disponible
        $data['temp'] = null;
        if ($data['colis']->temp_id) {
            $data['temp'] = DB::table('temps')
                ->where('id', $data['colis']->temp_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer les données de livraison depuis historique_livraisons
        $data['livraison'] = HistoriqueLivraison::where('colis_id', $colis_id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('platform-admin.entreprises.boutique.colis.show', $data);
    }

    /**
     * Afficher les détails d'un ramassage d'une boutique
     */
    public function showBoutiqueRamassage(string $id, string $boutique_id, string $ramassage_id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Détails du ramassage';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['boutique'] = Boutique::with(['marchand', 'entreprise'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($boutique_id);

        // Récupérer le ramassage avec ses relations
        $data['ramassage'] = Ramassage::with(['boutique', 'livreur', 'planifications'])
            ->where('entreprise_id', $id)
            ->where('boutique_id', $boutique_id)
            ->findOrFail($ramassage_id);

        // Récupérer le livreur si disponible
        $data['livreur'] = null;
        if ($data['ramassage']->livreur_id) {
            $data['livreur'] = DB::table('livreurs')
                ->where('id', $data['ramassage']->livreur_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer l'utilisateur qui a annulé si disponible
        $data['annuleur'] = null;
        if ($data['ramassage']->annule_par) {
            $data['annuleur'] = DB::table('users')
                ->where('id', $data['ramassage']->annule_par)
                ->whereNull('deleted_at')
                ->first();
        }

        return view('platform-admin.entreprises.boutique.ramassage.show', $data);
    }

    /**
     * Afficher les détails d'une livraison d'une boutique
     */
    public function showBoutiqueLivraison(string $id, string $boutique_id, string $livraison_id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Détails de la livraison';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['boutique'] = Boutique::with(['marchand', 'entreprise'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($boutique_id);

        // Récupérer les IDs des package_colis liés à cette boutique
        $packageColisIds = PackageColis::where('boutique_id', $boutique_id)
            ->pluck('id')
            ->toArray();

        // Récupérer la livraison avec ses relations
        $data['livraison'] = HistoriqueLivraison::with(['colis', 'entreprise'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($livraison_id);

        // Vérifier que la livraison appartient bien à la boutique
        if (!empty($packageColisIds) && !in_array($data['livraison']->package_colis_id, $packageColisIds)) {
            abort(403, 'Cette livraison n\'appartient pas à cette boutique.');
        }

        // Récupérer le livreur si disponible
        $data['livreur'] = null;
        if ($data['livraison']->livreur_id) {
            $data['livreur'] = DB::table('livreurs')
                ->where('id', $data['livraison']->livreur_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer l'utilisateur qui a créé la livraison
        $data['createur'] = null;
        if ($data['livraison']->created_by) {
            $data['createur'] = DB::table('users')
                ->where('id', $data['livraison']->created_by)
                ->whereNull('deleted_at')
                ->first();
        }

        // Parser les photos de preuve
        $photoProofPath = $data['livraison']->photo_proof_path;
        $data['photos'] = [];
        if ($photoProofPath) {
            $photoBaseUrl = env('PHOTO_PROOF_BASE_URL', 'http://192.168.1.8:8000/');

            // Si c'est un JSON
            if (is_string($photoProofPath) && (str_starts_with($photoProofPath, '[') || str_starts_with($photoProofPath, '{'))) {
                $decoded = json_decode($photoProofPath, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $photo) {
                        if (is_string($photo)) {
                            $photoPath = strpos($photo, 'storage/') === 0 ? substr($photo, 8) : $photo;
                            $data['photos'][] = rtrim($photoBaseUrl, '/') . '/storage/' . $photoPath;
                        }
                    }
                }
            } else {
                // Si c'est une chaîne séparée par des virgules ou un seul chemin
                $photos = is_string($photoProofPath) ? explode(',', $photoProofPath) : [$photoProofPath];
                foreach ($photos as $photo) {
                    $photo = trim($photo);
                    if (!empty($photo)) {
                        $photoPath = strpos($photo, 'storage/') === 0 ? substr($photo, 8) : $photo;
                        $data['photos'][] = rtrim($photoBaseUrl, '/') . '/storage/' . $photoPath;
                    }
                }
            }
        }

        return view('platform-admin.entreprises.boutique.livraison.show', $data);
    }

    /**
     * Afficher les détails d'un marchand avec ses statistiques et boutiques
     */
    public function showMarchand(string $id, string $marchand_id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Détails du marchand';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['marchand'] = Marchand::with(['commune', 'boutiques'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($marchand_id);

        // Récupérer les boutiques du marchand avec pagination
        $data['boutiques'] = Boutique::where('marchand_id', $marchand_id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Récupérer les IDs des boutiques du marchand
        $boutiqueIds = Boutique::where('marchand_id', $marchand_id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        // Récupérer les IDs des package_colis liés aux boutiques
        $packageColisIds = PackageColis::whereIn('boutique_id', $boutiqueIds)
            ->pluck('id')
            ->toArray();

        // Calculer les statistiques des colis
        $colisQuery = Colis::where('entreprise_id', $id)
            ->whereNull('deleted_at');

        if (!empty($packageColisIds)) {
            $colisQuery->whereIn('package_colis_id', $packageColisIds);
        }

        $colisStats = [
            'total' => $colisQuery->count(),
            'termine' => (clone $colisQuery)->where('status', 2)->count(), // status = 2: livré
            'en_cours' => (clone $colisQuery)->where('status', 1)->count(), // status = 1: en cours
            'annule' => (clone $colisQuery)->whereIn('status', [3, 4, 5])->count(), // status = 3,4,5: annulés
            'en_attente' => (clone $colisQuery)->where('status', 0)->count(), // status = 0: en attente
        ];

        // Calculer les statistiques des ramassages (table ramassages)
        // La table ramassages a directement marchand_id et entreprise_id
        $ramassagesQuery = Ramassage::where('entreprise_id', $id)
            ->where('marchand_id', $marchand_id);

        $ramassagesStats = [
            'total' => $ramassagesQuery->count(),
            'termine' => (clone $ramassagesQuery)->where('statut', 'termine')->count(),
            'en_cours' => (clone $ramassagesQuery)->where('statut', 'en_cours')->count(),
            'annule' => (clone $ramassagesQuery)->where('statut', 'annule')->count(),
            'planifie' => (clone $ramassagesQuery)->where('statut', 'planifie')->count(),
        ];

        $data['stats'] = [
            'colis' => $colisStats,
            'ramassages' => $ramassagesStats,
        ];

        return view('platform-admin.entreprises.marchand.show', $data);
    }

    /**
     * Afficher les colis d'un marchand
     */
    public function marchandColis(string $id, string $marchand_id, Request $request)
    {
        $data['title'] = 'Colis du marchand';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['marchand'] = Marchand::with(['commune'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($marchand_id);

        // Récupérer les IDs des boutiques du marchand
        $boutiqueIds = Boutique::where('marchand_id', $marchand_id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        // Récupérer les IDs des package_colis liés aux boutiques
        $packageColisIds = PackageColis::whereIn('boutique_id', $boutiqueIds)
            ->pluck('id')
            ->toArray();

        // Récupérer les colis du marchand
        $query = Colis::where('entreprise_id', $id)
            ->whereNull('deleted_at');

        if (!empty($packageColisIds)) {
            $query->whereIn('package_colis_id', $packageColisIds);
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('nom_client', 'like', "%{$search}%")
                  ->orWhere('telephone_client', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['colis'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.marchand.colis', $data);
    }

    /**
     * Afficher l'historique des ramassages d'un marchand
     */
    public function marchandRamassages(string $id, string $marchand_id, Request $request)
    {
        $data['title'] = 'Historique Ramassages du marchand';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['marchand'] = Marchand::with(['commune'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($marchand_id);

        // Récupérer les ramassages du marchand
        $query = Ramassage::with(['boutique', 'livreur'])
            ->where('entreprise_id', $id)
            ->where('marchand_id', $marchand_id);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code_ramassage', 'like', "%{$search}%")
                  ->orWhere('contact_ramassage', 'like', "%{$search}%")
                  ->orWhere('adresse_ramassage', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->has('statut') && $request->statut !== '') {
            $query->where('statut', $request->statut);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['ramassages'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.marchand.ramassages', $data);
    }

    /**
     * Afficher les détails d'un ramassage
     */
    public function showRamassage(string $id, string $marchand_id, string $ramassage_id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Détails du ramassage';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['marchand'] = Marchand::with(['commune'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($marchand_id);

        // Récupérer le ramassage avec ses relations
        $data['ramassage'] = Ramassage::with(['boutique', 'livreur', 'planifications'])
            ->where('entreprise_id', $id)
            ->where('marchand_id', $marchand_id)
            ->findOrFail($ramassage_id);

        // Récupérer le livreur si disponible
        $data['livreur'] = null;
        if ($data['ramassage']->livreur_id) {
            $data['livreur'] = DB::table('livreurs')
                ->where('id', $data['ramassage']->livreur_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer l'utilisateur qui a annulé si disponible
        $data['annuleur'] = null;
        if ($data['ramassage']->annule_par) {
            $data['annuleur'] = DB::table('users')
                ->where('id', $data['ramassage']->annule_par)
                ->whereNull('deleted_at')
                ->first();
        }

        return view('platform-admin.entreprises.marchand.ramassage.show', $data);
    }

    /**
     * Afficher l'historique des livraisons d'un marchand
     */
    public function marchandLivraisons(string $id, string $marchand_id, Request $request)
    {
        $data['title'] = 'Historique Livraisons du marchand';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['marchand'] = Marchand::with(['commune'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($marchand_id);

        // Récupérer les IDs des boutiques du marchand
        $boutiqueIds = Boutique::where('marchand_id', $marchand_id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        // Récupérer les IDs des package_colis liés aux boutiques
        $packageColisIds = PackageColis::whereIn('boutique_id', $boutiqueIds)
            ->pluck('id')
            ->toArray();

        // Récupérer l'historique des livraisons du marchand
        // La table historique_livraisons a entreprise_id et package_colis_id
        $query = HistoriqueLivraison::with(['colis'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at');

        if (!empty($packageColisIds)) {
            $query->whereIn('package_colis_id', $packageColisIds);
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('status', 'like', "%{$search}%")
                  ->orWhere('note_livraison', 'like', "%{$search}%")
                  ->orWhere('code_validation_utilise', 'like', "%{$search}%")
                  ->orWhere('motif_annulation', 'like', "%{$search}%")
                  ->orWhereHas('colis', function($q) use ($search) {
                      $q->where('code', 'like', "%{$search}%")
                        ->orWhere('nom_client', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['livraisons'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.marchand.livraisons', $data);
    }

    /**
     * Afficher les détails d'une livraison
     */
    public function showLivraison(string $id, string $marchand_id, string $livraison_id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Détails de la livraison';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);
        $data['marchand'] = Marchand::with(['commune'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($marchand_id);

        // Récupérer les IDs des boutiques du marchand
        $boutiqueIds = Boutique::where('marchand_id', $marchand_id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        // Récupérer les IDs des package_colis liés aux boutiques
        $packageColisIds = PackageColis::whereIn('boutique_id', $boutiqueIds)
            ->pluck('id')
            ->toArray();

        // Récupérer la livraison avec ses relations
        $data['livraison'] = HistoriqueLivraison::with(['colis', 'entreprise'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->findOrFail($livraison_id);

        // Vérifier que la livraison appartient bien au marchand
        if (!empty($packageColisIds) && !in_array($data['livraison']->package_colis_id, $packageColisIds)) {
            abort(403, 'Cette livraison n\'appartient pas à ce marchand.');
        }

        // Récupérer le livreur si disponible
        $data['livreur'] = null;
        if ($data['livraison']->livreur_id) {
            $data['livreur'] = DB::table('livreurs')
                ->where('id', $data['livraison']->livreur_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer l'utilisateur qui a créé la livraison
        $data['createur'] = null;
        if ($data['livraison']->created_by) {
            $data['createur'] = DB::table('users')
                ->where('id', $data['livraison']->created_by)
                ->whereNull('deleted_at')
                ->first();
        }

        // Parser les photos de preuve (peut être JSON, array séparé par virgule, ou une seule chaîne)
        $data['photos'] = [];
        if ($data['livraison']->photo_proof_path) {
            $photoPath = $data['livraison']->photo_proof_path;

            // Essayer de parser comme JSON
            $decoded = json_decode($photoPath, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['photos'] = $decoded;
            } else {
                // Essayer de séparer par virgule
                $photos = explode(',', $photoPath);
                $data['photos'] = array_filter(array_map('trim', $photos));

                // Si une seule photo, créer un array avec un seul élément
                if (count($data['photos']) === 0) {
                    $data['photos'] = [$photoPath];
                }
            }
        }

        // URL de base pour les photos (utiliser /storage/ pour accéder via le lien symbolique)
        $baseUrl = env('PHOTO_PROOF_BASE_URL', 'http://192.168.1.8:8000/');
        $data['photo_base_url'] = rtrim($baseUrl, '/') . '/storage/';

        return view('platform-admin.entreprises.marchand.livraison.show', $data);
    }

    /**
     * Afficher l'historique des ramassages
     */
    public function ramassages(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Historique Ramassages';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer les ramassages de l'entreprise
        $query = Ramassage::with(['boutique', 'livreur', 'marchand'])
            ->where('entreprise_id', $id);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code_ramassage', 'like', "%{$search}%")
                  ->orWhere('contact_ramassage', 'like', "%{$search}%")
                  ->orWhere('adresse_ramassage', 'like', "%{$search}%")
                  ->orWhereHas('boutique', function($q) use ($search) {
                      $q->where('libelle', 'like', "%{$search}%");
                  })
                  ->orWhereHas('marchand', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($request->has('statut') && $request->statut !== '') {
            $query->where('statut', $request->statut);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['ramassages'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.ramassages', $data);
    }

    /**
     * Afficher les détails d'un ramassage de l'entreprise
     */
    public function showRamassageEntreprise(string $id, string $ramassage_id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Détails du ramassage';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer le ramassage avec ses relations
        $data['ramassage'] = Ramassage::with(['boutique', 'livreur', 'planifications', 'marchand'])
            ->where('entreprise_id', $id)
            ->findOrFail($ramassage_id);

        // Récupérer le livreur si disponible
        $data['livreur'] = null;
        if ($data['ramassage']->livreur_id) {
            $data['livreur'] = DB::table('livreurs')
                ->where('id', $data['ramassage']->livreur_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer l'utilisateur qui a annulé si disponible
        $data['annuleur'] = null;
        if ($data['ramassage']->annule_par) {
            $data['annuleur'] = DB::table('users')
                ->where('id', $data['ramassage']->annule_par)
                ->whereNull('deleted_at')
                ->first();
        }

        // Utiliser la vue de la boutique si le ramassage a une boutique, sinon créer une vue générique
        if ($data['ramassage']->boutique_id) {
            $data['boutique'] = Boutique::with(['marchand', 'entreprise'])
                ->where('entreprise_id', $id)
                ->whereNull('deleted_at')
                ->find($data['ramassage']->boutique_id);

            return view('platform-admin.entreprises.boutique.ramassage.show', $data);
        }

        // Vue générique pour les ramassages sans boutique
        return view('platform-admin.entreprises.ramassage.show', $data);
    }

    /**
     * Afficher l'historique des livraisons
     */
    public function livraisons(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Historique Livraisons';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer l'historique des livraisons de l'entreprise
        $query = HistoriqueLivraison::with(['colis.packageColis.boutique'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('status', 'like', "%{$search}%")
                  ->orWhere('note_livraison', 'like', "%{$search}%")
                  ->orWhere('code_validation_utilise', 'like', "%{$search}%")
                  ->orWhere('motif_annulation', 'like', "%{$search}%")
                  ->orWhereHas('colis', function($q) use ($search) {
                      $q->where('code', 'like', "%{$search}%")
                        ->orWhere('nom_client', 'like', "%{$search}%")
                        ->orWhere('telephone_client', 'like', "%{$search}%");
                  })
                  ->orWhereHas('colis.packageColis.boutique', function($q) use ($search) {
                      $q->where('libelle', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['livraisons'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.livraisons', $data);
    }

    /**
     * Afficher les colis de l'entreprise
     */
    public function colis(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Colis';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer les colis de l'entreprise
        $query = Colis::with(['packageColis.boutique'])
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('nom_client', 'like', "%{$search}%")
                  ->orWhere('telephone_client', 'like', "%{$search}%")
                  ->orWhere('adresse_client', 'like', "%{$search}%")
                  ->orWhereHas('packageColis.boutique', function($q) use ($search) {
                      $q->where('libelle', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['colis'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.colis', $data);
    }

    /**
     * Afficher les livreurs de l'entreprise
     */
    public function livreurs(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Livreurs';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer les livreurs de l'entreprise
        $query = DB::table('livreurs')
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('adresse', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['livreurs'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('platform-admin.entreprises.livreurs', $data);
    }

    /**
     * Afficher les détails d'un livreur
     */
    public function showLivreur(string $id, string $livreur_id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Détails du livreur';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer le livreur
        $data['livreur'] = DB::table('livreurs')
            ->where('id', $livreur_id)
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$data['livreur']) {
            abort(404);
        }

        // Récupérer l'engin si disponible
        $data['engin'] = null;
        if ($data['livreur']->engin_id) {
            $data['engin'] = DB::table('engins')
                ->where('id', $data['livreur']->engin_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer la zone d'activité si disponible
        $data['zone_activite'] = null;
        if ($data['livreur']->zone_activite_id) {
            $data['zone_activite'] = DB::table('zone_activites')
                ->where('id', $data['livreur']->zone_activite_id)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer l'utilisateur qui a créé le livreur
        $data['createur'] = null;
        if ($data['livreur']->created_by) {
            $data['createur'] = DB::table('users')
                ->where('id', $data['livreur']->created_by)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer l'utilisateur qui a modifié le livreur
        $data['modificateur'] = null;
        if ($data['livreur']->updated_by) {
            $data['modificateur'] = DB::table('users')
                ->where('id', $data['livreur']->updated_by)
                ->whereNull('deleted_at')
                ->first();
        }

        return view('platform-admin.entreprises.livreur.show', $data);
    }

    /**
     * Afficher les tarifs de livraison de l'entreprise
     */
    public function tarifsLivraison(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Tarifs de livraison';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer les tarifs de livraison avec les relations
        $query = DB::table('tarif_livraisons')
            ->leftJoin('communes as commune_depart', 'tarif_livraisons.commune_depart_id', '=', 'commune_depart.id')
            ->leftJoin('communes as commune_arrivee', 'tarif_livraisons.commune_id', '=', 'commune_arrivee.id')
            ->leftJoin('type_engins', 'tarif_livraisons.type_engin_id', '=', 'type_engins.id')
            ->leftJoin('mode_livraisons', 'tarif_livraisons.mode_livraison_id', '=', 'mode_livraisons.id')
            ->leftJoin('poids', 'tarif_livraisons.poids_id', '=', 'poids.id')
            ->leftJoin('temps', 'tarif_livraisons.temp_id', '=', 'temps.id')
            ->where('tarif_livraisons.entreprise_id', $id)
            ->whereNull('tarif_livraisons.deleted_at')
            ->select(
                'tarif_livraisons.*',
                'commune_depart.libelle as commune_depart_nom',
                'commune_arrivee.libelle as commune_arrivee_nom',
                'type_engins.libelle as type_engin_nom',
                'mode_livraisons.libelle as mode_livraison_nom',
                'poids.libelle as poids_nom',
                'temps.libelle as temps_nom'
            );

        // Recherche textuelle
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('commune_depart.libelle', 'like', "%{$search}%")
                  ->orWhere('commune_arrivee.libelle', 'like', "%{$search}%")
                  ->orWhere('type_engins.libelle', 'like', "%{$search}%")
                  ->orWhere('mode_livraisons.libelle', 'like', "%{$search}%")
                  ->orWhere('poids.libelle', 'like', "%{$search}%")
                  ->orWhere('temps.libelle', 'like', "%{$search}%");
            });
        }

        // Filtres - chaque filtre fonctionne individuellement
        if ($request->filled('commune_depart_id')) {
            $query->where('tarif_livraisons.commune_depart_id', (int)$request->commune_depart_id);
        }

        if ($request->filled('commune_arrivee_id')) {
            $query->where('tarif_livraisons.commune_id', (int)$request->commune_arrivee_id);
        }

        if ($request->filled('type_engin_id')) {
            $query->where('tarif_livraisons.type_engin_id', (int)$request->type_engin_id);
        }

        if ($request->filled('mode_livraison_id')) {
            $query->where('tarif_livraisons.mode_livraison_id', (int)$request->mode_livraison_id);
        }

        if ($request->filled('poids_id')) {
            $query->where('tarif_livraisons.poids_id', (int)$request->poids_id);
        }

        if ($request->filled('temps_id')) {
            $query->where('tarif_livraisons.temp_id', (int)$request->temps_id);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $data['tarifs'] = $query->paginate($perPage)->appends($request->query());

        // Récupérer les listes pour les filtres
        $data['communes'] = DB::table('communes')
            ->whereNull('deleted_at')
            ->orderBy('libelle')
            ->pluck('libelle', 'id');

        $data['type_engins'] = DB::table('type_engins')
            ->whereNull('deleted_at')
            ->orderBy('libelle')
            ->pluck('libelle', 'id');

        $data['mode_livraisons'] = DB::table('mode_livraisons')
            ->whereNull('deleted_at')
            ->orderBy('libelle')
            ->pluck('libelle', 'id');

        $data['poids'] = DB::table('poids')
            ->whereNull('deleted_at')
            ->orderBy('libelle')
            ->pluck('libelle', 'id');

        $data['temps'] = DB::table('temps')
            ->whereNull('deleted_at')
            ->orderBy('libelle')
            ->pluck('libelle', 'id');

        return view('platform-admin.entreprises.tarifs-livraison', $data);
    }

    /**
     * Afficher la configuration de l'entreprise
     */
    public function config(string $id)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Configuration';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // TODO: Récupérer les configurations
        // commune_zone, conditionnement_colis, delais, engins, tarif_livraisons,
        // mode_livraisons, package_colis, planification_ramassage, role_permissions,
        // temps, type_colis, type_engins, zones, zone_activites

        return view('platform-admin.entreprises.config', $data);
    }

}
