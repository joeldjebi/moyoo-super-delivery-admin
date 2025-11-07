<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Afficher le dashboard principal
     */
    public function index()
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('dashboard.read')) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder au dashboard.');
        }
        // Statistiques globales
        $stats = [
            'total_entreprises' => Schema::hasTable('entreprises')
                ? DB::table('entreprises')->whereNull('deleted_at')->count() : 0,
            'entreprises_actives' => Schema::hasTable('entreprises')
                ? DB::table('entreprises')->where('statut', 1)->whereNull('deleted_at')->count() : 0,
            'total_users' => Schema::hasTable('users')
                ? DB::table('users')->whereNull('deleted_at')->count() : 0,
            'total_colis' => Schema::hasTable('colis')
                ? DB::table('colis')->count() : 0,
            'total_livreurs' => Schema::hasTable('livreurs')
                ? DB::table('livreurs')->count() : 0,
            'total_revenus' => Schema::hasTable('subscription_plans')
                ? (DB::table('subscription_plans')->where('is_active', true)->sum('price') ?? 0) : 0,
        ];

        // Statistiques des abonnements
        if (Schema::hasTable('subscription_plans')) {
            $stats['total_abonnements'] = DB::table('subscription_plans')->count();
            $stats['abonnements_actifs'] = DB::table('subscription_plans')->where('is_active', true)->count();
            $stats['abonnements_inactifs'] = DB::table('subscription_plans')->where('is_active', false)->count();
            $stats['abonnements_expires'] = DB::table('subscription_plans')
                ->where('is_active', true)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count();
            $stats['abonnements_non_expires'] = DB::table('subscription_plans')
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('expires_at', '>=', now())
                      ->orWhereNull('expires_at');
                })
                ->count();
            $stats['revenus_totaux'] = DB::table('subscription_plans')
                ->where('is_active', true)
                ->sum('price') ?? 0;
            $stats['entreprises_abonnees'] = DB::table('subscription_plans')
                ->where('is_active', true)
                ->distinct()
                ->count('entreprise_id');
        } else {
            $stats['total_abonnements'] = 0;
            $stats['abonnements_actifs'] = 0;
            $stats['abonnements_inactifs'] = 0;
            $stats['abonnements_expires'] = 0;
            $stats['abonnements_non_expires'] = 0;
            $stats['revenus_totaux'] = 0;
            $stats['entreprises_abonnees'] = 0;
        }

        // Récupérer les dernières entreprises (5 dernières)
        $entreprises = Schema::hasTable('entreprises')
            ? DB::table('entreprises')
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
            : collect();

        // Récupérer les derniers abonnements (5 derniers)
        $abonnements = Schema::hasTable('subscription_plans')
            ? DB::table('subscription_plans')
                ->leftJoin('entreprises', 'subscription_plans.entreprise_id', '=', 'entreprises.id')
                ->leftJoin('pricing_plans', 'subscription_plans.pricing_plan_id', '=', 'pricing_plans.id')
                ->select(
                    'subscription_plans.*',
                    'entreprises.name as entreprise_name',
                    'pricing_plans.name as pricing_plan_name'
                )
                ->whereNull('entreprises.deleted_at')
                ->orderBy('subscription_plans.created_at', 'desc')
                ->limit(5)
                ->get()
            : collect();

        return view('platform-admin.dashboard.index', compact('stats', 'entreprises', 'abonnements'));
    }
}
