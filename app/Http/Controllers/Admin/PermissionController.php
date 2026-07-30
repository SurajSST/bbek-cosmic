<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    /**
     * Protected core system permissions that cannot be deleted.
     */
    protected array $protectedPermissions = [
        'dashboard.view',
        'users.view', 'users.create', 'users.edit', 'users.delete',
        'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
    ];

    /**
     * Display a listing of permissions grouped by module with search and module filtering.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $activeModule = $request->query('module', 'all');

        $query = Permission::with(['roles'])
            ->withCount('roles');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $allPermissions = $query->get();

        // Build modules dictionary
        $groupedPermissions = [];
        $modulesList = [];

        foreach ($allPermissions as $permission) {
            $parts = explode('.', $permission->name);
            $moduleName = count($parts) > 1 ? ucfirst($parts[0]) : 'General';
            
            $modulesList[$moduleName] = ($modulesList[$moduleName] ?? 0) + 1;

            if ($activeModule === 'all' || strtolower($activeModule) === strtolower($moduleName)) {
                $groupedPermissions[$moduleName][] = $permission;
            }
        }

        ksort($groupedPermissions);
        ksort($modulesList);

        // Dynamically extract all active modules for the new permission dropdown
        $availableModules = Permission::all()
            ->map(function ($p) {
                $parts = explode('.', $p->name);
                return count($parts) > 1 ? strtolower($parts[0]) : 'general';
            })
            ->merge(['dashboard', 'users', 'roles', 'permissions', 'sales-orders', 'bills', 'upload-sos'])
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return view('admin.permissions.index', compact(
            'groupedPermissions',
            'modulesList',
            'search',
            'activeModule',
            'availableModules'
        ));
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module' => ['required', 'string', 'max:50', 'alpha_dash'],
            'action' => ['required', 'string', 'max:50', 'alpha_dash'],
        ]);

        $permissionName = strtolower($validated['module']) . '.' . strtolower($validated['action']);

        if (Permission::where('name', $permissionName)->where('guard_name', 'web')->exists()) {
            return back()->withInput()->withErrors([
                'action' => "Permission '{$permissionName}' already exists.",
            ]);
        }

        $permission = Permission::create([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);

        // Automatically assign new permission to Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
        }

        // Reset Spatie cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        ActivityLog::record('created_permission', "Created Permission '{$permission->name}'", $permission);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission '{$permission->name}' created and assigned to Super Admin.");
    }

    /**
     * Delete the specified permission with safety checks.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        if (in_array($permission->name, $this->protectedPermissions)) {
            return back()->with('error', "Core system permission '{$permission->name}' cannot be deleted.");
        }

        $permissionName = $permission->name;
        $permission->delete();

        // Reset Spatie cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        ActivityLog::record('deleted_permission', "Deleted Permission '{$permissionName}'");

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission '{$permissionName}' deleted successfully.");
    }
}
