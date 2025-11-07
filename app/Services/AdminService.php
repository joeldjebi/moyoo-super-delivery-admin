<?php

namespace App\Services;

use App\Models\PlatformAdmin;
use App\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Générer un mot de passe aléatoire de 15 caractères alphanumériques
     */
    private function generatePassword(int $length = 15): string
    {
        // Caractères alphanumériques (lettres majuscules, minuscules et chiffres)
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }

    /**
     * Créer un nouvel admin
     * @return array ['admin' => PlatformAdmin, 'plainPassword' => string]
     */
    public function create(array $data, PlatformAdmin $creator): array
    {
        return DB::transaction(function () use ($data, $creator) {
            // Générer un mot de passe aléatoire de 15 caractères alphanumériques
            $plainPassword = $this->generatePassword(15);

            // Créer l'admin
            $admin = PlatformAdmin::create([
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'password' => Hash::make($plainPassword),
                'status' => $data['status'] ?? 'active',
                'created_by' => $creator->id,
            ]);

            // Attribuer les rôles
            if (isset($data['roles']) && is_array($data['roles'])) {
                $admin->roles()->sync($data['roles']);
            }

            // Logger l'action
            $this->activityLogService->log(
                $creator,
                'created',
                PlatformAdmin::class,
                $admin->id,
                null,
                $admin->toArray(),
                "Création de l'admin {$admin->username}"
            );

            return [
                'admin' => $admin->fresh(['roles']),
                'plainPassword' => $plainPassword
            ];
        });
    }

    /**
     * Mettre à jour un admin
     */
    public function update(PlatformAdmin $admin, array $data, PlatformAdmin $updater): PlatformAdmin
    {
        // Protection du premier super admin : personne ne peut le modifier
        if ($admin->isFirstSuperAdmin()) {
            throw new \Exception('Le premier super administrateur ne peut pas être modifié.');
        }

        return DB::transaction(function () use ($admin, $data, $updater) {
            $oldValues = $admin->toArray();

            // Mettre à jour les champs
            $updateData = [
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'status' => $data['status'] ?? $admin->status,
            ];

            // Mettre à jour le mot de passe si fourni
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $admin->update($updateData);

            // Mettre à jour les rôles
            if (isset($data['roles']) && is_array($data['roles'])) {
                $admin->roles()->sync($data['roles']);
            }

            // Logger l'action
            $this->activityLogService->log(
                $updater,
                'updated',
                PlatformAdmin::class,
                $admin->id,
                $oldValues,
                $admin->fresh()->toArray(),
                "Modification de l'admin {$admin->username}"
            );

            return $admin->fresh(['roles']);
        });
    }

    /**
     * Supprimer un admin
     */
    public function delete(PlatformAdmin $admin, PlatformAdmin $deleter): bool
    {
        // Protection du premier super admin : personne ne peut le supprimer
        if ($admin->isFirstSuperAdmin()) {
            throw new \Exception('Le premier super administrateur ne peut pas être supprimé.');
        }

        // Vérifications de sécurité
        if ($admin->id === $deleter->id) {
            throw new \Exception('Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Seul le premier super admin peut supprimer d'autres super admins
        if ($admin->isSuperAdmin()) {
            $firstSuperAdmin = PlatformAdmin::getFirstSuperAdmin();

            if (!$firstSuperAdmin || $deleter->id !== $firstSuperAdmin->id) {
                throw new \Exception('Seul le premier super administrateur peut supprimer d\'autres super administrateurs.');
            }

            // Vérifier que le super admin à supprimer a été créé par le premier super admin
            if ($admin->created_by !== $firstSuperAdmin->id) {
                throw new \Exception('Vous ne pouvez supprimer que les super administrateurs que vous avez créés.');
            }
        }

        return DB::transaction(function () use ($admin, $deleter) {
            $adminData = $admin->toArray();

            // Logger avant suppression
            $this->activityLogService->log(
                $deleter,
                'deleted',
                PlatformAdmin::class,
                $admin->id,
                $adminData,
                null,
                "Suppression de l'admin {$admin->username}"
            );

            // Soft delete
            return $admin->delete();
        });
    }

    /**
     * Activer/Désactiver un admin
     */
    public function toggleStatus(PlatformAdmin $admin, PlatformAdmin $changer): PlatformAdmin
    {
        // Protection du premier super admin : personne ne peut le désactiver
        if ($admin->isFirstSuperAdmin()) {
            throw new \Exception('Le premier super administrateur ne peut pas être désactivé.');
        }

        // Vérifications de sécurité
        if ($admin->isSuperAdmin() && $admin->status === 'active') {
            // Vérifier qu'il n'est pas le dernier super admin actif
            $activeSuperAdminCount = PlatformAdmin::whereHas('roles', function ($query) {
                $query->where('slug', 'super-admin');
            })->where('status', 'active')->count();

            if ($activeSuperAdminCount <= 1) {
                throw new \Exception('Impossible de désactiver le dernier super administrateur actif.');
            }
        }

        return DB::transaction(function () use ($admin, $changer) {
            $oldStatus = $admin->status;
            $newStatus = $admin->status === 'active' ? 'inactive' : 'active';

            $admin->update(['status' => $newStatus]);

            // Logger l'action
            $this->activityLogService->log(
                $changer,
                'status_changed',
                PlatformAdmin::class,
                $admin->id,
                ['status' => $oldStatus],
                ['status' => $newStatus],
                "Changement de statut de l'admin {$admin->username} : {$oldStatus} → {$newStatus}"
            );

            return $admin->fresh();
        });
    }
}

