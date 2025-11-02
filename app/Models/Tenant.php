<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Tenant extends BaseTenant implements TenantWithDatabase
{
    use SoftDeletes, HasDatabase;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'email',
        'domain',
        'database',
        'company_name',
        'tax_id',
        'address',
        'phone',
        'website',
        'status',
        'activated_at',
        'suspended_at',
        'settings',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'settings' => 'array',
        'data' => 'array',
    ];

    /**
     * Tenant statuses
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_PENDING = 'pending';

    /**
     * Get the domains for the tenant.
     */
    public function domains()
    {
        return $this->hasMany(config('tenancy.domain_model'));
    }

    /**
     * Get the subscription for the tenant (most recent active).
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_id')->latest();
    }

    /**
     * Get all subscriptions for the tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id');
    }

    /**
     * Get the active subscription for the tenant.
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_id')->where('status', 'active')->latest();
    }

    /**
     * Scope a query to only include active tenants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include suspended tenants.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    /**
     * Scope a query to only include pending tenants.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Check if tenant is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get the is_active attribute.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if tenant is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Check if tenant is pending activation.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Activate the tenant.
     */
    public function activate(): bool
    {
        return $this->update([
            'status' => self::STATUS_ACTIVE,
            'activated_at' => now(),
            'suspended_at' => null,
        ]);
    }

    /**
     * Suspend the tenant.
     */
    public function suspend($reason = null): bool
    {
        $settings = $this->settings ?? [];
        if ($reason) {
            $settings['suspension_reason'] = $reason;
        }

        return $this->update([
            'status' => self::STATUS_SUSPENDED,
            'suspended_at' => now(),
            'settings' => $settings,
        ]);
    }

    /**
     * Get available tenant statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_INACTIVE => 'Inativo',
            self::STATUS_SUSPENDED => 'Suspenso',
            self::STATUS_PENDING => 'Pendente',
        ];
    }

    /**
     * Get the tenant's full address.
     */
    public function getFullAddressAttribute(): ?string
    {
        return $this->address;
    }

    /**
     * Check if tenant has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Get the current plan through active subscription.
     */
    public function getCurrentPlan()
    {
        $subscription = $this->activeSubscription()->with('plan')->first();
        return $subscription ? $subscription->plan : null;
    }

    /**
     * The attributes that are mass assignable.
     * 
     * Note: We don't use $fillable because this model uses VirtualColumn trait
     * which automatically handles attribute assignment based on getCustomColumns()
     */
    // protected $fillable = []; // Removido - não usar com VirtualColumn

    /**
     * Get the custom columns that exist as physical columns in the database.
     * All other attributes will be stored in the 'data' JSON column.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'domain',
             'database', // Adicionar o campo database
            'company_name',
            'tax_id',
            'address',
            'phone',
            'website',
            'status',
            'activated_at',
            'suspended_at',
            'settings',
            'created_at', 
            'updated_at',
            'deleted_at', // Para SoftDeletes
            'data',
        ];
    }
}