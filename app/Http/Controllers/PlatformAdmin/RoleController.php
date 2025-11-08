<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('roles.read')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les rôles.');
        }

        $query = Role::withCount('admins');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $roles = $query->orderBy('name')->paginate($perPage);

        return view('platform-admin.roles.index', [
            'menu' => 'roles',
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::orderBy('resource')->orderBy('action')->get()->groupBy('resource');
        return view('platform-admin.roles.create', [
            'menu' => 'roles',
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            $role = $this->roleService->create($request->validated(), Auth::guard('platform_admin')->user());

            return redirect()
                ->route('platform-admin.roles.index')
                ->with('success', "Le rôle {$role->name} a été créé avec succès.");
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
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('roles.read')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les rôles.');
        }

        $role = Role::with(['admins', 'permissions'])->findOrFail($id);

        // Si la relation ne fonctionne pas, utiliser le fallback
        if ($role->permissions->count() === 0) {
            $permissions = $role->getPermissionsWithFallback();
            $role->setRelation('permissions', $permissions);
        }

        return view('platform-admin.roles.show', [
            'menu' => 'roles',
            'role' => $role,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        // Si la relation ne fonctionne pas, utiliser le fallback
        if ($role->permissions->count() === 0) {
            $permissions = $role->getPermissionsWithFallback();
            $role->setRelation('permissions', $permissions);
        }

        $permissions = Permission::orderBy('resource')->orderBy('action')->get()->groupBy('resource');

        return view('platform-admin.roles.edit', [
            'menu' => 'roles',
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id)
    {
        try {
            $role = Role::findOrFail($id);
            $updatedRole = $this->roleService->update($role, $request->validated(), Auth::guard('platform_admin')->user());

            return redirect()
                ->route('platform-admin.roles.index')
                ->with('success', "Le rôle {$updatedRole->name} a été modifié avec succès.");
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
        if (!$currentAdmin || !$currentAdmin->hasPermission('roles.delete')) {
            abort(403, 'Vous n\'avez pas la permission de supprimer les rôles.');
        }

        try {
            $role = Role::findOrFail($id);
            $this->roleService->delete($role, $currentAdmin);

            return redirect()
                ->route('platform-admin.roles.index')
                ->with('success', "Le rôle {$role->name} a été supprimé avec succès.");
        } catch (\Exception $e) {
            return back()
                ->with('error', "Erreur lors de la suppression : {$e->getMessage()}");
        }
    }

    /**
     * Assign permissions to a role
     */
    public function assignPermissions(Request $request, string $id)
    {
        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        try {
            $role = Role::findOrFail($id);
            $updatedRole = $this->roleService->assignPermissions($role, $request->permissions, Auth::guard('platform_admin')->user());

            return redirect()
                ->route('platform-admin.roles.edit', $id)
                ->with('success', "Les permissions ont été attribuées au rôle {$updatedRole->name} avec succès.");
        } catch (\Exception $e) {
            return back()
                ->with('error', "Erreur lors de l'attribution des permissions : {$e->getMessage()}");
        }
    }
}
