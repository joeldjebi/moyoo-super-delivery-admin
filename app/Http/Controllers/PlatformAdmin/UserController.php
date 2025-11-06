<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $data['title'] = 'Utilisateurs';
        $data['menu'] = 'users';
        $query = DB::table('users')
            ->leftJoin('entreprises', 'users.entreprise_id', '=', 'entreprises.id')
            ->select(
                'users.*',
                'entreprises.name as entreprise_name',
                'entreprises.statut as entreprise_statut'
            )
            ->whereNull('users.deleted_at');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.first_name', 'like', "%{$search}%")
                  ->orWhere('users.last_name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.mobile', 'like', "%{$search}%")
                  ->orWhere('entreprises.name', 'like', "%{$search}%");
            });
        }

        // Filtre par entreprise
        if ($request->has('entreprise_id') && $request->entreprise_id) {
            $query->where('users.entreprise_id', $request->entreprise_id);
        }

        // Filtre par statut
        if ($request->has('status') && $request->status) {
            $query->where('users.status', $request->status);
        }

        // Filtre par type d'utilisateur (par défaut: Admin Entreprise uniquement)
        if ($request->has('user_type') && $request->user_type) {
            $query->where('users.user_type', $request->user_type);
        } else {
            // Par défaut, afficher uniquement les Admin Entreprise
            $query->where('users.user_type', 'entreprise_admin');
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['users'] = $query->orderBy('users.created_at', 'desc')->paginate($perPage)->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = DB::table('entreprises')
            ->where('statut', 1)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('platform-admin.users.index', $data);
    }

    // Note: La création d'utilisateurs par le super admin est désactivée pour le moment

    public function show(string $id)
    {
        $data['title'] = 'Voir un utilisateur';
        $data['menu'] = 'users';
        $data['user'] = DB::table('users')
            ->leftJoin('entreprises', 'users.entreprise_id', '=', 'entreprises.id')
            ->select(
                'users.*',
                'entreprises.name as entreprise_name',
                'entreprises.email as entreprise_email',
                'entreprises.mobile as entreprise_mobile',
                'entreprises.statut as entreprise_statut'
            )
            ->where('users.id', $id)
            ->whereNull('users.deleted_at')
            ->first();

        if (!$data['user']) {
            abort(404);
        }

        // Récupérer l'utilisateur créateur
        $data['createur'] = null;
        if (isset($data['user']->created_by) && $data['user']->created_by) {
            $data['createur'] = DB::table('users')
                ->where('id', $data['user']->created_by)
                ->whereNull('deleted_at')
                ->first();
        }

        // Récupérer le plan d'abonnement
        $data['subscription_plan'] = null;
        if (isset($data['user']->subscription_plan_id) && $data['user']->subscription_plan_id) {
            $data['subscription_plan'] = DB::table('subscription_plans')
                ->where('id', $data['user']->subscription_plan_id)
                ->first();
        }

        // Récupérer le plan tarifaire actuel
        $data['pricing_plan'] = null;
        if (isset($data['user']->current_pricing_plan_id) && $data['user']->current_pricing_plan_id) {
            $data['pricing_plan'] = DB::table('pricing_plans')
                ->where('id', $data['user']->current_pricing_plan_id)
                ->first();
        }

        // Parser les permissions JSON
        $data['permissions'] = [];
        if (isset($data['user']->permissions) && $data['user']->permissions) {
            $permissions = json_decode($data['user']->permissions, true);
            if (is_array($permissions)) {
                $data['permissions'] = $permissions;
            }
        }

        return view('platform-admin.users.show', $data);
    }


    public function destroy(string $id)
    {
        // Soft delete
        DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        Log::info('Utilisateur supprimé par super admin', [
            'admin_id' => auth()->guard('platform_admin')->id(),
            'user_id' => $id,
        ]);

        return redirect()->route('platform-admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}
