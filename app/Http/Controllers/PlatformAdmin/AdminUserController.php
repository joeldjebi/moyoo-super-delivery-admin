<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Models\PlatformAdmin;
use App\Models\Role;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('admins.read')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les administrateurs.');
        }

        $query = PlatformAdmin::with(['roles', 'creator']);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Filtre par rôle
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $admins = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $roles = Role::orderBy('name')->get();

        $data = [
            'menu' => 'admin-users',
            'admins' => $admins,
            'roles' => $roles,
        ];

        return view('platform-admin.admin-users.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('platform-admin.admin-users.create', [
            'menu' => 'admin-users',
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRequest $request)
    {
        try {
            $result = $this->adminService->create($request->validated(), Auth::guard('platform_admin')->user());
            $admin = $result['admin'];
            $plainPassword = $result['plainPassword'];

            // Envoyer l'email avec les identifiants
            if ($admin->email) {
                $emailMessage = $this->generateAdminAccessEmailMessage($admin, $plainPassword);
                $emailResult = $this->sendAdminAccessEmail($admin, $emailMessage);

                if (!$emailResult['success']) {
                    Log::warning("Échec de l'envoi d'email à l'admin {$admin->username}: " . ($emailResult['error'] ?? 'Erreur inconnue'));
                }
            }

            $message = "L'admin {$admin->username} a été créé avec succès.";
            if ($admin->email && isset($emailResult) && $emailResult['success']) {
                $message .= " Un email avec les identifiants a été envoyé.";
            } elseif ($admin->email) {
                $message .= " Attention : l'envoi de l'email a échoué.";
            }

            return redirect()
                ->route('platform-admin.admin-users.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', "Erreur lors de la création : {$e->getMessage()}");
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $currentAdmin = Auth::guard('platform_admin')->user();
        if (!$currentAdmin || !$currentAdmin->hasPermission('admins.read')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les administrateurs.');
        }

        $admin = PlatformAdmin::with(['roles', 'permissions', 'creator', 'activityLogs' => function ($query) {
            $query->latest()->limit(20);
        }])->findOrFail($id);

        return view('platform-admin.admin-users.show', [
            'menu' => 'admin-users',
            'admin' => $admin,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $admin = PlatformAdmin::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return view('platform-admin.admin-users.edit', [
            'menu' => 'admin-users',
            'admin' => $admin,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, string $id)
    {
        try {
            $admin = PlatformAdmin::findOrFail($id);
            $updatedAdmin = $this->adminService->update($admin, $request->validated(), Auth::guard('platform_admin')->user());

            return redirect()
                ->route('platform-admin.admin-users.index')
                ->with('success', "L'admin {$updatedAdmin->username} a été modifié avec succès.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', "Erreur lors de la modification : {$e->getMessage()}");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $currentAdmin = Auth::guard('platform_admin')->user();
        if (!$currentAdmin || !$currentAdmin->hasPermission('admins.delete')) {
            abort(403, 'Vous n\'avez pas la permission de supprimer les administrateurs.');
        }

        try {
            $admin = PlatformAdmin::findOrFail($id);
            $this->adminService->delete($admin, $currentAdmin);

            return redirect()
                ->route('platform-admin.admin-users.index')
                ->with('success', "L'admin {$admin->username} a été supprimé avec succès.");
        } catch (\Exception $e) {
            return back()
                ->with('error', "Erreur lors de la suppression : {$e->getMessage()}");
        }
    }

    /**
     * Toggle admin status
     */
    public function toggleStatus(string $id)
    {
        try {
            $admin = PlatformAdmin::findOrFail($id);
            $updatedAdmin = $this->adminService->toggleStatus($admin, Auth::guard('platform_admin')->user());

            $status = $updatedAdmin->status === 'active' ? 'activé' : 'désactivé';

            return redirect()
                ->route('platform-admin.admin-users.index')
                ->with('success', "L'admin {$updatedAdmin->username} a été {$status} avec succès.");
        } catch (\Exception $e) {
            return back()
                ->with('error', "Erreur lors du changement de statut : {$e->getMessage()}");
        }
    }

    /**
     * Générer le message d'accès pour l'email
     */
    private function generateAdminAccessEmailMessage(PlatformAdmin $admin, string $password)
    {
        $appUrl = env('PLATFORM_ADMIN_URL', url('/platform-admin/login'));
        $creator = Auth::guard('platform_admin')->user();
        $platformName = 'MOYOO Platform Admin';

        $fullName = trim(($admin->first_name ?? '') . ' ' . ($admin->last_name ?? ''));
        $greetingName = $fullName ?: $admin->username;

        // Version texte
        $textPart = "Bonjour {$greetingName},\n\n";
        $textPart .= "Votre compte administrateur MOYOO Platform a été créé avec succès !\n\n";
        $textPart .= "Vos identifiants de connexion :\n";
        $textPart .= "Nom d'utilisateur : {$admin->username}\n";
        $textPart .= "Mot de passe : {$password}\n\n";
        $textPart .= "Accédez à la plateforme d'administration :\n";
        $textPart .= "{$appUrl}\n\n";
        $textPart .= "Vous pouvez maintenant vous connecter à la plateforme d'administration MOYOO.\n\n";
        $textPart .= "Cordialement,\nL'équipe {$platformName}";

        // Version HTML
        $htmlPart = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #696cff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
                .credentials { background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #696cff; }
                .credential-item { margin: 10px 0; }
                .credential-label { font-weight: bold; color: #555; }
                .credential-value { color: #333; font-size: 16px; }
                .app-link { background-color: #696cff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #777; font-size: 12px; }
                .warning { background-color: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Bienvenue sur MOYOO Platform Admin !</h1>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>{$greetingName}</strong>,</p>

                    <p>Votre compte administrateur MOYOO Platform a été créé avec succès !</p>

                    <div class='credentials'>
                        <h3 style='margin-top: 0; color: #696cff;'>Vos identifiants de connexion :</h3>
                        <div class='credential-item'>
                            <span class='credential-label'>👤 Nom d'utilisateur :</span>
                            <span class='credential-value'><strong>{$admin->username}</strong></span>
                        </div>
                        <div class='credential-item'>
                            <span class='credential-label'>🔑 Mot de passe :</span>
                            <span class='credential-value'><strong>{$password}</strong></span>
                        </div>
                    </div>

                    <div class='warning'>
                        <strong>⚠️ Important :</strong> Pour des raisons de sécurité, veuillez changer votre mot de passe après votre première connexion.
                    </div>

                    <p style='text-align: center;'>
                        <a href='{$appUrl}' class='app-link' target='_blank'>🔐 Accéder à la plateforme d'administration</a>
                    </p>

                    <p>Vous pouvez maintenant vous connecter à la plateforme d'administration MOYOO avec vos identifiants.</p>

                    <p>Cordialement,<br>L'équipe <strong>{$platformName}</strong></p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>";

        return [
            'text' => $textPart,
            'html' => $htmlPart
        ];
    }

    /**
     * Envoyer un email avec les accès de l'admin via Mailjet
     */
    private function sendAdminAccessEmail(PlatformAdmin $admin, array $emailMessage)
    {
        $apiKeyPublic = config('mailjet.api_key_public');
        $apiKeyPrivate = config('mailjet.api_key_private');
        $senderEmail = config('mailjet.default_from.email');
        $senderName = config('mailjet.default_from.name');
        $apiUrl = config('mailjet.api_url');

        if (!$apiKeyPublic || !$apiKeyPrivate || !$senderEmail) {
            Log::error('Configuration Mailjet manquante pour l\'envoi d\'email à l\'admin');
            return [
                'success' => false,
                'error' => 'Configuration Mailjet manquante'
            ];
        }

        $subject = 'Bienvenue sur MOYOO Platform Admin - Vos identifiants de connexion';
        $toName = ($admin->first_name && $admin->last_name)
            ? $admin->first_name . ' ' . $admin->last_name
            : $admin->username;
        $toEmail = $admin->email;

        $data = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $senderEmail,
                        'Name' => $senderName
                    ],
                    'To' => [
                        [
                            'Email' => $toEmail,
                            'Name' => $toName
                        ]
                    ],
                    'Subject' => $subject,
                    'TextPart' => $emailMessage['text'],
                    'HTMLPart' => $emailMessage['html']
                ]
            ]
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($apiKeyPublic . ':' . $apiKeyPrivate)
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            Log::error('Erreur cURL Mailjet pour admin: ' . $error);
            return [
                'success' => false,
                'error' => $error
            ];
        }

        if ($httpCode !== 200) {
            Log::error('Erreur Mailjet pour admin - Code HTTP: ' . $httpCode . ' - Réponse: ' . $response);
            return [
                'success' => false,
                'error' => 'Erreur HTTP ' . $httpCode,
                'response' => $response
            ];
        }

        $responseData = json_decode($response, true);

        return [
            'success' => true,
            'response' => $responseData
        ];
    }
}
