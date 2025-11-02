<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class TenantUser extends Model
{
    use HasFactory, SoftDeletes, CentralConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'role',
        'permissions',
        'is_active',
        'invited_at',
        'joined_at',
        'last_activity_at',
        'invitation_token',
        'invitation_expires_at',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'invited_at' => 'datetime',
        'joined_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
    ];

    /**
     * Tenant user roles
     */
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_ACCOUNTANT = 'accountant';
    const ROLE_USER = 'user';
    const ROLE_VIEWER = 'viewer';

    /**
     * Get the tenant that owns this user relationship.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user in this tenant relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active tenant users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tenant users by role.
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get owners.
     */
    public function scopeOwners($query)
    {
        return $query->where('role', self::ROLE_OWNER);
    }

    /**
     * Scope to get admins and owners.
     */
    public function scopeAdmins($query)
    {
        return $query->whereIn('role', [self::ROLE_OWNER, self::ROLE_ADMIN]);
    }

    /**
     * Scope to get pending invitations.
     */
    public function scopePendingInvitations($query)
    {
        return $query->whereNull('joined_at')
                    ->whereNotNull('invitation_token')
                    ->where('invitation_expires_at', '>', now());
    }

    /**
     * Scope to get expired invitations.
     */
    public function scopeExpiredInvitations($query)
    {
        return $query->whereNull('joined_at')
                    ->whereNotNull('invitation_token')
                    ->where('invitation_expires_at', '<=', now());
    }

    /**
     * Check if user is owner.
     */
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Check if user is admin or owner.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN]);
    }

    /**
     * Check if user is manager or higher.
     */
    public function isManager(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Check if user has joined the tenant.
     */
    public function hasJoined(): bool
    {
        return !is_null($this->joined_at);
    }

    /**
     * Check if invitation is pending.
     */
    public function isPendingInvitation(): bool
    {
        return is_null($this->joined_at) 
               && !is_null($this->invitation_token) 
               && $this->invitation_expires_at > now();
    }

    /**
     * Check if invitation has expired.
     */
    public function isInvitationExpired(): bool
    {
        return is_null($this->joined_at) 
               && !is_null($this->invitation_token) 
               && $this->invitation_expires_at <= now();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return !empty(array_intersect($permissions, $this->permissions ?? []));
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return empty(array_diff($permissions, $this->permissions ?? []));
    }

    /**
     * Grant a permission to the tenant user.
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
     * Revoke a permission from the tenant user.
     */
    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        
        $this->permissions = array_values($permissions);
        $this->save();
    }

    /**
     * Sync tenant user permissions.
     */
    public function syncPermissions(array $permissions): void
    {
        $this->permissions = array_unique($permissions);
        $this->save();
    }

    /**
     * Mark invitation as accepted.
     */
    public function acceptInvitation(): void
    {
        $this->update([
            'joined_at' => now(),
            'invitation_token' => null,
            'invitation_expires_at' => null,
        ]);
    }

    /**
     * Update last activity timestamp.
     */
    public function updateLastActivity(): void
    {
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Generate invitation token.
     */
    public function generateInvitationToken(): string
    {
        $token = bin2hex(random_bytes(32));
        
        $this->update([
            'invitation_token' => $token,
            'invitation_expires_at' => now()->addDays(7),
            'invited_at' => now(),
        ]);

        return $token;
    }

    /**
     * Get all available roles.
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_OWNER => 'Owner',
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_ACCOUNTANT => 'Accountant',
            self::ROLE_USER => 'User',
            self::ROLE_VIEWER => 'Viewer',
        ];
    }

    /**
     * Get role hierarchy (higher number = more permissions).
     */
    public static function getRoleHierarchy(): array
    {
        return [
            self::ROLE_VIEWER => 1,
            self::ROLE_USER => 2,
            self::ROLE_ACCOUNTANT => 3,
            self::ROLE_MANAGER => 4,
            self::ROLE_ADMIN => 5,
            self::ROLE_OWNER => 6,
        ];
    }

    /**
     * Check if current role is higher than given role.
     */
    public function hasHigherRoleThan(string $role): bool
    {
        $hierarchy = self::getRoleHierarchy();
        
        return ($hierarchy[$this->role] ?? 0) > ($hierarchy[$role] ?? 0);
    }

    /**
     * Get default permissions for a role.
     */
    public static function getDefaultPermissionsForRole(string $role): array
    {
        return match ($role) {
            self::ROLE_OWNER => [
                'tenant.manage',
                'users.manage',
                'billing.manage',
                'integrations.manage',
                'documents.manage',
                'invoices.manage',
                'clients.manage',
                'reports.view',
                'settings.manage',
            ],
            self::ROLE_ADMIN => [
                'users.manage',
                'integrations.manage',
                'documents.manage',
                'invoices.manage',
                'clients.manage',
                'reports.view',
                'settings.view',
            ],
            self::ROLE_MANAGER => [
                'documents.manage',
                'invoices.manage',
                'clients.manage',
                'reports.view',
            ],
            self::ROLE_ACCOUNTANT => [
                'invoices.manage',
                'clients.view',
                'reports.view',
                'documents.view',
            ],
            self::ROLE_USER => [
                'documents.upload',
                'invoices.view',
                'clients.view',
            ],
            self::ROLE_VIEWER => [
                'documents.view',
                'invoices.view',
                'clients.view',
            ],
            default => [],
        };
    }
}
