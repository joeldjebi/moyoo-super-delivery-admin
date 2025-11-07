<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminRoleController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
        $this->middleware('permission:admins.update')->only(['assignRoles', 'removeRole', 'assignPermissions', 'removePermission']);
    }

    /**
     * Assign roles to an admin
     */
    public function assignRoles(Request $request, string $id)
    {
        $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        try {
            $admin = PlatformAdmin::findOrFail($id);
            $currentUser = Auth::guard('platform_admin')->user();

            // Vérifier qu'on ne retire pas son propre rôle de super admin
            if ($admin->id === $currentUser->id && $admin->isSuperAdmin()) {
                $requestedRoles = Role::whereIn('id', $request->roles)->pluck('slug')->toArray();
                if (!in_array('super-admin', $requestedRoles)) {
                    throw new \Exception('Vous ne pouvez pas retirer votre propre rôle de super administrateur.');
                }
            }

            DB::transaction(function () use ($admin, $request, $currentUser) {
                $oldRoles = $admin->roles->pluck('id')->toArray();
                $admin->roles()->sync($request->roles);

                // Logger l'action
                $this->activityLogService->log(
                    $currentUser,
                    'roles_assigned',
                    PlatformAdmin::class,
                    $admin->id,
                    ['roles' => $oldRoles],
                    ['roles' => $request->roles],
                    "Attribution de rôles à l'admin {$admin->username}"
                );
            });

            return redirect()
                ->route('platform-admin.admin-users.edit', $id)
                ->with('success', "Les rôles ont été attribués avec succès.");
        } catch (\Exception $e) {
            return back()
                ->with('error', "Erreur lors de l'attribution des rôles : {$e->getMessage()}");
        }
    }

    /**
     * Remove a role from an admin
     */
    public function removeRole(Request $request, string $id, string $roleId)
    {
        try {
            $admin = PlatformAdmin::findOrFail($id);
            $role = Role::findOrFail($roleId);
            $currentUser = Auth::guard('platform_admin')->user();

            // Vérifier qu'on ne retire pas son propre rôle de super admin
            if ($admin->id === $currentUser->id && $role->slug === 'super-admin') {
                throw new \Exception('Vous ne pouvez pas retirer votre propre rôle de super administrateur.');
            }

            DB::transaction(function () use ($admin, $role, $currentUser) {
                $admin->roles()->detach($role->id);

                // Logger l'action
                $this->activityLogService->log(
                    $currentUser,
                    'role_removed',
                    PlatformAdmin::class,
                    $admin->id,
                    ['role' => $role->toArray()],
                    null,
                    "Retrait du rôle {$role->name} de l'admin {$admin->username}"
                );
            });

            return redirect()
                ->route('platform-admin.admin-users.edit', $id)
                ->with('success', "Le rôle {$role->name} a été retiré avec succès.");
        } catch (\Exception $e) {
            return back()
                ->with('error', "Erreur lors du retrait du rôle : {$e->getMessage()}");
        }
    }

    /**
     * Assign direct permissions to an admin
     */
    public function assignPermissions(Request $request, string $id)
    {
        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        try {
            $admin = PlatformAdmin::findOrFail($id);
            $currentUser = Auth::guard('platform_admin')->user();

            DB::transaction(function () use ($admin, $request, $currentUser) {
                $oldPermissions = $admin->permissions->pluck('id')->toArray();
                $admin->permissions()->sync($request->permissions);

                // Logger l'action
                $this->activityLogService->log(
                    $currentUser,
                    'permissions_assigned',
                    PlatformAdmin::class,
                    $admin->id,
                    ['permissions' => $oldPermissions],
                    ['permissions' => $request->permissions],
                    "Attribution de permissions directes à l'admin {$admin->username}"
                );
            });

            return redirect()
                ->route('platform-admin.admin-users.edit', $id)
                ->with('success', "Les permissions ont été attribuées avec succès.");
        } catch (\Exception $e) {
            return back()
                ->with('error', "Erreur lors de l'attribution des permissions : {$e->getMessage()}");
        }
    }

    /**
     * Remove a direct permission from an admin
     */
    public function removePermission(Request $request, string $id, string $permissionId)
    {
        try {
            $admin = PlatformAdmin::findOrFail($id);
            $permission = Permission::findOrFail($permissionId);
            $currentUser = Auth::guard('platform_admin')->user();

            DB::transaction(function () use ($admin, $permission, $currentUser) {
                $admin->permissions()->detach($permission->id);

                // Logger l'action
                $this->activityLogService->log(
                    $currentUser,
                    'permission_removed',
                    PlatformAdmin::class,
                    $admin->id,
                    ['permission' => $permission->toArray()],
                    null,
                    "Retrait de la permission {$permission->name} de l'admin {$admin->username}"
                );
            });

            return redirect()
                ->route('platform-admin.admin-users.edit', $id)
                ->with('success', "La permission {$permission->name} a été retirée avec succès.");
        } catch (\Exception $e) {
            return back()
                ->with('error', "Erreur lors du retrait de la permission : {$e->getMessage()}");
        }
    }
}
