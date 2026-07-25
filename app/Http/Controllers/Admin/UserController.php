<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a paginated listing of users with search and filtering.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $role = $request->query('role');

        $users = User::with('roles')
            ->search($search)
            ->filterByStatus($status)
            ->filterByRole($role)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = Role::pluck('name', 'name');

        return view('admin.users.index', compact('users', 'roles', 'search', 'status', 'role'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' was created successfully.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $user->load('roles');
        $roles = Role::all();
        $userRoleNames = $user->roles->pluck('name')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRoleNames'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', Password::defaults()],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        // Safeguard: Do not allow removing Super Admin role from the last remaining Super Admin
        if ($user->isSuperAdmin() && !in_array('Super Admin', $validated['roles'] ?? [])) {
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= 1) {
                return back()->withInput()->withErrors([
                    'roles' => 'Cannot remove Super Admin role from the system\'s only Super Admin.',
                ]);
            }
        }

        // Safeguard: Prevent deactivating the last remaining Super Admin
        if ($user->isSuperAdmin() && $validated['status'] === 'inactive') {
            $activeSuperAdminCount = User::role('Super Admin')->where('status', 'active')->count();
            if ($activeSuperAdminCount <= 1) {
                return back()->withInput()->withErrors([
                    'status' => 'Cannot deactivate the system\'s only active Super Admin.',
                ]);
            }
        }

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' was updated successfully.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Safeguard 1: Self-deletion prevention
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account while logged in.');
        }

        // Safeguard 2: Prevent deleting the last Super Admin
        if ($user->isSuperAdmin()) {
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'Cannot delete the system\'s only Super Admin account.');
            }
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' was deleted successfully.");
    }
}
