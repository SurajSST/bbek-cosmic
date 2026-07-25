<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with permission and user counts.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $roles = Role::with(['permissions'])
            ->withCount(['permissions', 'users'])
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->get();

        $totalPermissionsCount = Permission::count();

        return view('admin.roles.index', compact('roles', 'search', 'totalPermissionsCount'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        $groupedPermissions = $this->getGroupedPermissions();

        return view('admin.roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        // Reset Spatie cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' was created successfully.");
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        $role->load('permissions');
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $groupedPermissions = $this->getGroupedPermissions();

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        // Safeguard: Rename prevention for Super Admin
        if ($role->name === 'Super Admin' && $request->input('name') !== 'Super Admin') {
            return back()->withInput()->withErrors([
                'name' => 'The Super Admin role name cannot be modified.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);

        // If updating Super Admin, ensure all permissions remain synced
        if ($role->name === 'Super Admin') {
            $role->syncPermissions(Permission::all());
        } else {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        // Reset Spatie cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' was updated successfully.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        // Safeguard: Cannot delete Super Admin role
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'The Super Admin role is protected and cannot be deleted.');
        }

        // Prevent deletion if users are assigned to this role
        if ($role->users()->count() > 0) {
            return back()->with('error', "Cannot delete role '{$role->name}' because it is assigned to {$role->users()->count()} user(s).");
        }

        $roleName = $role->name;
        $role->delete();

        // Reset Spatie cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$roleName}' was deleted successfully.");
    }

    /**
     * Group permissions logically by module prefix.
     */
    private function getGroupedPermissions(): array
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = count($parts) > 1 ? ucfirst($parts[0]) : 'General';
            $grouped[$module][] = $permission;
        }

        ksort($grouped);

        return $grouped;
    }
}
