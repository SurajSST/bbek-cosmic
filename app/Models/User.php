<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Scope query to search users by name or email.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Scope query to filter users by status.
     */
    public function scopeFilterByStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status) || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Scope query to filter users by role name.
     */
    public function scopeFilterByRole(Builder $query, ?string $role): Builder
    {
        if (empty($role) || $role === 'all') {
            return $query;
        }

        return $query->whereHas('roles', function (Builder $q) use ($role) {
            $q->where('name', $role);
        });
    }

    /**
     * Get the default landing route based on user's active permissions.
     */
    public function getHomeRoute(): string
    {
        if ($this->can('dashboard.view')) {
            return route('admin.dashboard');
        }

        if ($this->can('sales-orders.view')) {
            return route('admin.sales-orders.index');
        }

        if ($this->can('users.view')) {
            return route('admin.users.index');
        }

        if ($this->can('roles.view')) {
            return route('admin.roles.index');
        }

        if ($this->can('permissions.view')) {
            return route('admin.permissions.index');
        }

        return route('login');
    }
}
