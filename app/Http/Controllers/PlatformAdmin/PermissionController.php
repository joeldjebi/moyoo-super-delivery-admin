<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('permissions.read')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les permissions.');
        }

        $permissions = Permission::orderBy('resource')->orderBy('action')->get()->groupBy('resource');

        return view('platform-admin.permissions.index', [
            'menu' => 'permissions',
            'permissions' => $permissions,
        ]);
    }
}
