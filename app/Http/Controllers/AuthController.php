<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showLogin()
    {
        $data['title'] = 'Connexion';
        $data['menu'] = 'login';

        return view('auth.login', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function loginUser(Request $request)
    {
        // Log du début de la tentative de connexion
        Log::info('Tentative de connexion', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'remember' => $request->boolean('remember')
        ]);

        // Limitation du taux de tentatives
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            Log::warning('Tentative de connexion bloquée - Trop de tentatives', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'seconds_remaining' => $seconds
            ]);
            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        // Log de la validation réussie
        Log::info('Validation des données de connexion réussie', [
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            $request->session()->regenerate();
            RateLimiter::clear($key);

            // Redirection si les informations d'entreprise doivent être mises à jour
            $entreprise = Entreprise::getEntrepriseByUser($user->id);
            if ($entreprise && (int)($entreprise->not_update) === 0) {
                return redirect()->intended(route('entreprise.index'));
            }

            return redirect()->intended(route('dashboard'));
        }

        // Incrémenter le compteur de tentatives
        RateLimiter::hit($key, 300); // 5 minutes

        // Message d'erreur générique pour éviter la fuite d'informations
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
