<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'permissions',
        'last_login_at',
        'avatar',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'password' => 'hashed',
            'permissions' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'preferences' => 'array',
        ];
    }

    /**
     * Get the roles assigned to the user
     */
    public function roles(): BelongsToMany
    {
        // Only available in central database context
        if (tenant()) {
            throw new \Exception('Roles relationship is not available in tenant context. Use the role attribute instead.');
        }
        
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        // If we're in a tenant context, use the role field instead of relationships
        if (tenant()) {
            return $this->role === $roleName;
        }
        
        // For central database users, use the roles relationship
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        // If we're in a tenant context, use the role field instead of relationships
        if (tenant()) {
            return in_array($this->role, $roles);
        }
        
        // For central database users, use the roles relationship
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Assign a role to the user
     */
    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }
    }


    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get admin users
     */
    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['super_admin', 'admin']);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager']);
    }

    /**
     * Check if user has admin privileges
     */
    public function hasAdminPrivileges(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user has management privileges
     */
    public function hasManagementPrivileges(): bool
    {
        return $this->isManager();
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return !empty(array_intersect($permissions, $this->permissions ?? []));
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return empty(array_diff($permissions, $this->permissions ?? []));
    }

    /**
     * Grant a permission to the user
     */
    public function grantPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * Grant multiple permissions to the user
     */
    public function grantPermissions(array $permissions): void
    {
        $currentPermissions = $this->permissions ?? [];
        $newPermissions = array_unique(array_merge($currentPermissions, $permissions));
        
        $this->permissions = $newPermissions;
        $this->save();
    }

    /**
     * Revoke a permission from the user
     */
    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        
        $this->permissions = array_values($permissions);
        $this->save();
    }

    /**
     * Revoke multiple permissions from the user
     */
    public function revokePermissions(array $permissions): void
    {
        $currentPermissions = $this->permissions ?? [];
        $newPermissions = array_diff($currentPermissions, $permissions);
        
        $this->permissions = array_values($newPermissions);
        $this->save();
    }

    /**
     * Sync user permissions (replace all permissions)
     */
    public function syncPermissions(array $permissions): void
    {
        $this->permissions = array_unique($permissions);
        $this->save();
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->last_login_at = now();
        $this->save();
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the documents uploaded by this user.
     */
    public function uploadedDocuments()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * Get the clients managed by this user (if applicable).
     */
    public function managedClients()
    {
        return $this->hasMany(Client::class, 'manager_id');
    }

    /**
     * Get all available permissions for the system
     */
    public static function getAvailablePermissions(): array
    {
        return [
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.manage_roles',
            'users.manage_permissions',
            'users.activate_deactivate',
            'users.impersonate',

            // Tenant Management
            'tenants.view',
            'tenants.create',
            'tenants.edit',
            'tenants.delete',
            'tenants.manage_subscriptions',
            'tenants.manage_settings',
            'tenants.view_analytics',

            // Plan Management
            'plans.view',
            'plans.create',
            'plans.edit',
            'plans.delete',
            'plans.manage_features',
            'plans.manage_pricing',

            // Billing Management
            'billing.view',
            'billing.create',
            'billing.edit',
            'billing.process_payments',
            'billing.manage_subscriptions',
            'billing.view_reports',

            // System Management
            'system.view_logs',
            'system.manage_settings',
            'system.manage_integrations',
            'system.backup_restore',
            'system.maintenance_mode',
            'system.view_analytics',

            // Integration Management
            'integrations.view',
            'integrations.create',
            'integrations.edit',
            'integrations.delete',
            'integrations.test',
            'integrations.manage_webhooks',

            // Document Management
            'documents.view',
            'documents.upload',
            'documents.download',
            'documents.delete',
            'documents.process',
            'documents.manage_categories',

            // Invoice Management
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'invoices.send',
            'invoices.process_payments',

            // Client Management
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.manage_contacts',

            // Reports
            'reports.view',
            'reports.export',
            'reports.create_custom',
        ];
    }
}
