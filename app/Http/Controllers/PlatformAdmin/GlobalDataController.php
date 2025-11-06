<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\Boutique;
use App\Models\Colis;
use App\Models\HistoriqueLivraison;
use App\Models\Ramassage;
use App\Models\Livreur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalDataController extends Controller
{
    /**
     * Afficher toutes les livraisons (toutes entreprises)
     */
    public function livraisons(Request $request)
    {
        $data['title'] = 'Toutes les livraisons';
        $data['menu'] = 'global-data-livraisons';

        // Récupérer toutes les livraisons avec leurs relations
        $query = HistoriqueLivraison::with(['colis.packageColis.boutique', 'entreprise'])
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
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['livraisons'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = Entreprise::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('platform-admin.global-data.livraisons', $data);
    }

    /**
     * Afficher tous les colis (toutes entreprises)
     */
    public function colis(Request $request)
    {
        $data['title'] = 'Tous les colis';
        $data['menu'] = 'global-data-colis';

        // Récupérer tous les colis avec leurs relations
        $query = Colis::with(['packageColis.boutique', 'entreprise'])
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
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['colis'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = Entreprise::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('platform-admin.global-data.colis', $data);
    }

    /**
     * Afficher tous les ramassages (toutes entreprises)
     */
    public function ramassages(Request $request)
    {
        $data['title'] = 'Tous les ramassages';
        $data['menu'] = 'global-data-ramassages';

        // Récupérer tous les ramassages avec leurs relations
        $query = Ramassage::with(['boutique', 'livreur', 'marchand', 'entreprise']);

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

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['ramassages'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = Entreprise::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('platform-admin.global-data.ramassages', $data);
    }

    /**
     * Afficher tous les livreurs (toutes entreprises)
     */
    public function livreurs(Request $request)
    {
        $data['title'] = 'Tous les livreurs';
        $data['menu'] = 'global-data-livreurs';

        // Récupérer tous les livreurs avec leurs relations
        $query = Livreur::with(['entreprise'])
            ->whereNull('deleted_at');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('adresse', 'like', "%{$search}%")
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
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['livreurs'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = Entreprise::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('platform-admin.global-data.livreurs', $data);
    }

    /**
     * Afficher toutes les boutiques (toutes entreprises)
     */
    public function boutiques(Request $request)
    {
        $data['title'] = 'Toutes les boutiques';
        $data['menu'] = 'global-data-boutiques';

        // Récupérer toutes les boutiques avec leurs relations
        $query = Boutique::with(['marchand', 'entreprise'])
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
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['boutiques'] = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = Entreprise::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('platform-admin.global-data.boutiques', $data);
    }
}