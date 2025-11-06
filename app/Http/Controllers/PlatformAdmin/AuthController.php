<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Models\PlatformAdmin;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLogin()
    {
        // Si déjà connecté, rediriger vers le dashboard
        if (Auth::guard('platform_admin')->check()) {
            return redirect()->route('platform-admin.dashboard');
        }

        return view('platform-admin.auth.login');
    }

    /**
     * Traiter la connexion
     */
    public function login(Request $request)
    {
        // Log du début de la tentative de connexion
        Log::info('Tentative de connexion super admin', [
            'username' => $request->username,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Limitation du taux de tentatives (5 max par IP)
        $key = 'platform-admin-login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            Log::warning('Tentative de connexion super admin bloquée - Trop de tentatives', [
                'username' => $request->username,
                'ip' => $request->ip(),
                'seconds_remaining' => $seconds
            ]);
            throw ValidationException::withMessages([
                'username' => [trans('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        // Validation
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:8',
        ], [
            'username.required' => 'Le nom d\'utilisateur est requis.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        // Récupérer l'admin par username
        $admin = PlatformAdmin::where('username', $request->username)->first();

        // Vérifier si l'admin existe
        if (!$admin) {
            RateLimiter::hit($key, 300); // 5 minutes
            Log::warning('Tentative de connexion super admin échouée - Utilisateur inexistant', [
                'username' => $request->username,
                'ip' => $request->ip()
            ]);
            throw ValidationException::withMessages([
                'username' => ['Identifiants incorrects.'],
            ]);
        }

        // Vérifier si le compte est actif
        if (!$admin->isActive()) {
            RateLimiter::hit($key, 300);
            Log::warning('Tentative de connexion super admin échouée - Compte inactif', [
                'username' => $request->username,
                'status' => $admin->status,
                'ip' => $request->ip()
            ]);
            throw ValidationException::withMessages([
                'username' => ['Ce compte est inactif.'],
            ]);
        }

        // Vérifier si le compte est bloqué
        if ($admin->isLocked()) {
            RateLimiter::hit($key, 300);
            Log::warning('Tentative de connexion super admin échouée - Compte bloqué', [
                'username' => $request->username,
                'locked_until' => $admin->locked_until,
                'ip' => $request->ip()
            ]);
            throw ValidationException::withMessages([
                'username' => ['Ce compte est temporairement bloqué. Veuillez réessayer plus tard.'],
            ]);
        }

        // Vérifier le mot de passe manuellement
        // Laravel's Hash::check() va comparer le mot de passe en clair avec le hash stocké
        if (!Hash::check($request->password, $admin->password)) {
            // Échec de l'authentification
            $admin->incrementFailedLoginAttempts();
            RateLimiter::hit($key, 300);

            Log::warning('Tentative de connexion super admin échouée - Mot de passe incorrect', [
                'username' => $request->username,
                'ip' => $request->ip(),
                'failed_attempts' => $admin->fresh()->failed_login_attempts
            ]);

            throw ValidationException::withMessages([
                'username' => ['Identifiants incorrects.'],
            ]);
        }

        // Authentification réussie - connecter l'utilisateur manuellement
        Auth::guard('platform_admin')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();
        RateLimiter::clear($key);

        // Enregistrer les informations de connexion
        $admin->recordLogin($request->ip());

        // Log de connexion réussie
        Log::info('Connexion super admin réussie', [
            'admin_id' => $admin->id,
            'username' => $admin->username,
            'ip' => $request->ip()
        ]);

        return redirect()->intended(route('platform-admin.dashboard'))
            ->with('success', 'Connexion réussie. Bienvenue !');

    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();

        if ($admin) {
            Log::info('Déconnexion super admin', [
                'admin_id' => $admin->id,
                'username' => $admin->username,
                'ip' => $request->ip()
            ]);
        }

        Auth::guard('platform_admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform-admin.login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }
}
