<?php

namespace App\Services;

use App\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class RoleService
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Créer un nouveau rôle
     */
    public function create(array $data, $creator): Role
    {
        return DB::transaction(function () use ($data, $creator) {
            $role = Role::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'is_system_role' => $data['is_system_role'] ?? false,
            ]);

            // Attribuer les permissions
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $role->permissions()->sync($data['permissions']);
            }

            // Logger l'action
            $this->activityLogService->log(
                $creator,
                'created',
                Role::class,
                $role->id,
                null,
                $role->toArray(),
                "Création du rôle {$role->name}"
            );

            return $role->fresh(['permissions']);
        });
    }

    /**
     * Mettre à jour un rôle
     */
    public function update(Role $role, array $data, $updater): Role
    {
        // Vérifier si le rôle système peut être modifié
        if ($role->isSystemRole() && isset($data['is_system_role']) && !$data['is_system_role']) {
            throw new \Exception('Impossible de modifier le statut système d\'un rôle système.');
        }

        return DB::transaction(function () use ($role, $data, $updater) {
            $oldValues = $role->toArray();

            $updateData = [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
            ];

            // Ne pas modifier is_system_role pour les rôles système
            if (!$role->isSystemRole() && isset($data['is_system_role'])) {
                $updateData['is_system_role'] = $data['is_system_role'];
            }

            $role->update($updateData);

            // Mettre à jour les permissions
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $role->permissions()->sync($data['permissions']);
            }

            // Logger l'action
            $this->activityLogService->log(
                $updater,
                'updated',
                Role::class,
                $role->id,
                $oldValues,
                $role->fresh()->toArray(),
                "Modification du rôle {$role->name}"
            );

            return $role->fresh(['permissions']);
        });
    }

    /**
     * Supprimer un rôle
     */
    public function delete(Role $role, $deleter): bool
    {
        if ($role->isSystemRole()) {
            throw new \Exception('Impossible de supprimer un rôle système.');
        }

        return DB::transaction(function () use ($role, $deleter) {
            $roleData = $role->toArray();

            // Logger avant suppression
            $this->activityLogService->log(
                $deleter,
                'deleted',
                Role::class,
                $role->id,
                $roleData,
                null,
                "Suppression du rôle {$role->name}"
            );

            // Soft delete
            return $role->delete();
        });
    }

    /**
     * Attribuer des permissions à un rôle
     */
    public function assignPermissions(Role $role, array $permissionIds, $assigner): Role
    {
        return DB::transaction(function () use ($role, $permissionIds, $assigner) {
            $oldPermissions = $role->permissions->pluck('id')->toArray();
            $role->permissions()->sync($permissionIds);

            // Logger l'action
            $this->activityLogService->log(
                $assigner,
                'permissions_assigned',
                Role::class,
                $role->id,
                ['permissions' => $oldPermissions],
                ['permissions' => $permissionIds],
                "Attribution de permissions au rôle {$role->name}"
            );

            return $role->fresh(['permissions']);
        });
    }
}

