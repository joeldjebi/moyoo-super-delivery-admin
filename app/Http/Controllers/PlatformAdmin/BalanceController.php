<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\BalanceMarchand;
use App\Models\Entreprise;
use App\Models\HistoriqueBalance;
use App\Models\Reversement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    /**
     * Vérifier la permission pour accéder aux balances
     */
    private function checkPermission(string $permission = 'entreprises.read'): void
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission($permission)) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder à cette ressource.');
        }
    }

    /**
     * Afficher les balances des marchands d'une entreprise
     */
    public function balances(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Balances des marchands';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer les balances avec les relations
        $query = BalanceMarchand::with(['marchand', 'boutique'])
            ->where('entreprise_id', $id);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('marchand', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('boutique', function($q) use ($search) {
                    $q->where('libelle', 'like', "%{$search}%");
                });
            });
        }

        // Filtre par marchand
        if ($request->has('marchand_id') && $request->marchand_id) {
            $query->where('marchand_id', $request->marchand_id);
        }

        // Filtre par boutique
        if ($request->has('boutique_id') && $request->boutique_id) {
            $query->where('boutique_id', $request->boutique_id);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['balances'] = $query->orderBy('derniere_mise_a_jour', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des marchands pour le filtre
        $data['marchands'] = DB::table('marchands')
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Récupérer la liste des boutiques pour le filtre
        $data['boutiques'] = DB::table('boutiques')
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('libelle')
            ->get();

        return view('platform-admin.entreprises.balances', $data);
    }

    /**
     * Afficher l'historique de balance d'une entreprise
     */
    public function historiqueBalance(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Historique de balance';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer l'historique avec les relations
        $query = HistoriqueBalance::with(['balanceMarchand.marchand', 'balanceMarchand.boutique', 'createdBy'])
            ->where('entreprise_id', $id);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('balanceMarchand.marchand', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('balanceMarchand.boutique', function($q) use ($search) {
                      $q->where('libelle', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par type d'opération
        if ($request->has('type_operation') && $request->type_operation !== '') {
            $query->where('type_operation', $request->type_operation);
        }

        // Filtre par marchand
        if ($request->has('marchand_id') && $request->marchand_id) {
            $query->whereHas('balanceMarchand', function($q) use ($request) {
                $q->where('marchand_id', $request->marchand_id);
            });
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['historiques'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des marchands pour le filtre
        $data['marchands'] = DB::table('marchands')
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('platform-admin.entreprises.historique-balance', $data);
    }

    /**
     * Afficher l'historique de reversement d'une entreprise
     */
    public function historiqueReversement(string $id, Request $request)
    {
        $this->checkPermission('entreprises.read');
        $data['title'] = 'Historique de reversement';
        $data['menu'] = 'entreprises';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($id);

        // Récupérer les reversements avec les relations
        $query = Reversement::with(['marchand', 'boutique', 'createdBy', 'validatedBy'])
            ->where('entreprise_id', $id);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_reversement', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('marchand', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('boutique', function($q) use ($search) {
                      $q->where('libelle', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($request->has('statut') && $request->statut !== '') {
            $query->where('statut', $request->statut);
        }

        // Filtre par mode de reversement
        if ($request->has('mode_reversement') && $request->mode_reversement !== '') {
            $query->where('mode_reversement', $request->mode_reversement);
        }

        // Filtre par marchand
        if ($request->has('marchand_id') && $request->marchand_id) {
            $query->where('marchand_id', $request->marchand_id);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['reversements'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des marchands pour le filtre
        $data['marchands'] = DB::table('marchands')
            ->where('entreprise_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('platform-admin.entreprises.historique-reversement', $data);
    }

    /**
     * Afficher toutes les balances des marchands (toutes entreprises)
     */
    public function globalBalances(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('global_data.balances')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les balances globales.');
        }
        $data['title'] = 'Toutes les balances des marchands';
        $data['menu'] = 'global-data-balances';

        // Récupérer toutes les balances avec les relations
        $query = BalanceMarchand::with(['marchand', 'boutique', 'entreprise']);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('marchand', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('boutique', function($q) use ($search) {
                    $q->where('libelle', 'like', "%{$search}%");
                })
                ->orWhereHas('entreprise', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filtre par entreprise
        if ($request->has('entreprise_id') && $request->entreprise_id) {
            $query->where('entreprise_id', $request->entreprise_id);
        }

        // Filtre par marchand
        if ($request->has('marchand_id') && $request->marchand_id) {
            $query->where('marchand_id', $request->marchand_id);
        }

        // Filtre par boutique
        if ($request->has('boutique_id') && $request->boutique_id) {
            $query->where('boutique_id', $request->boutique_id);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['balances'] = $query->orderBy('derniere_mise_a_jour', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = Entreprise::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Récupérer la liste des marchands pour le filtre
        $data['marchands'] = DB::table('marchands')
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Récupérer la liste des boutiques pour le filtre
        $data['boutiques'] = DB::table('boutiques')
            ->whereNull('deleted_at')
            ->orderBy('libelle')
            ->get();

        return view('platform-admin.global-data.balances', $data);
    }

    /**
     * Afficher tout l'historique de balance (toutes entreprises)
     */
    public function globalHistoriqueBalance(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('global_data.historique_balance')) {
            abort(403, 'Vous n\'avez pas la permission de consulter l\'historique de balance global.');
        }
        $data['title'] = 'Tout l\'historique de balance';
        $data['menu'] = 'global-data-historique-balance';

        // Récupérer tout l'historique avec les relations
        $query = HistoriqueBalance::with(['balanceMarchand.marchand', 'balanceMarchand.boutique', 'balanceMarchand.entreprise', 'createdBy']);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('balanceMarchand.marchand', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('balanceMarchand.boutique', function($q) use ($search) {
                      $q->where('libelle', 'like', "%{$search}%");
                  })
                  ->orWhereHas('balanceMarchand.entreprise', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par entreprise
        if ($request->has('entreprise_id') && $request->entreprise_id) {
            $query->where('entreprise_id', $request->entreprise_id);
        }

        // Filtre par type d'opération
        if ($request->has('type_operation') && $request->type_operation !== '') {
            $query->where('type_operation', $request->type_operation);
        }

        // Filtre par marchand
        if ($request->has('marchand_id') && $request->marchand_id) {
            $query->whereHas('balanceMarchand', function($q) use ($request) {
                $q->where('marchand_id', $request->marchand_id);
            });
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['historiques'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = Entreprise::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Récupérer la liste des marchands pour le filtre
        $data['marchands'] = DB::table('marchands')
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('platform-admin.global-data.historique-balance', $data);
    }

    /**
     * Afficher tout l'historique de reversement (toutes entreprises)
     */
    public function globalHistoriqueReversement(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('global_data.historique_reversement')) {
            abort(403, 'Vous n\'avez pas la permission de consulter l\'historique de reversement global.');
        }
        $data['title'] = 'Tout l\'historique de reversement';
        $data['menu'] = 'global-data-historique-reversement';

        // Récupérer tous les reversements avec les relations
        $query = Reversement::with(['marchand', 'boutique', 'entreprise', 'createdBy', 'validatedBy']);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_reversement', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('marchand', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('boutique', function($q) use ($search) {
                      $q->where('libelle', 'like', "%{$search}%");
                  })
                  ->orWhereHas('entreprise', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par entreprise
        if ($request->has('entreprise_id') && $request->entreprise_id) {
            $query->where('entreprise_id', $request->entreprise_id);
        }

        // Filtre par statut
        if ($request->has('statut') && $request->statut !== '') {
            $query->where('statut', $request->statut);
        }

        // Filtre par mode de reversement
        if ($request->has('mode_reversement') && $request->mode_reversement !== '') {
            $query->where('mode_reversement', $request->mode_reversement);
        }

        // Filtre par marchand
        if ($request->has('marchand_id') && $request->marchand_id) {
            $query->where('marchand_id', $request->marchand_id);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['reversements'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = Entreprise::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Récupérer la liste des marchands pour le filtre
        $data['marchands'] = DB::table('marchands')
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('platform-admin.global-data.historique-reversement', $data);
    }
}

