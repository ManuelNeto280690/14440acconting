<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Subscription extends Model
{
    use HasFactory, SoftDeletes, CentralConnection;

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
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'amount',
        'currency',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'canceled_at',
        'stripe_subscription_id',
        'stripe_customer_id',
        'stripe_status',
        'quantity',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'amount' => 'decimal:2',
        'quantity' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }

    /**
     * Get the tenant that owns the subscription.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Get the plan that the subscription belongs to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include canceled subscriptions.
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    /**
     * Scope a query to only include expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now());
    }

    /**
     * Scope a query to only include subscriptions ending soon.
     */
    public function scopeEndingSoon($query, $days = 7)
    {
        return $query->where('ends_at', '<=', now()->addDays($days))
                    ->where('ends_at', '>', now());
    }

    /**
     * Scope a query to only include trial subscriptions.
     */
    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now());
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Check if subscription is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled' || $this->canceled_at !== null;
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    /**
     * Check if subscription is on trial.
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription is ending soon.
     */
    public function isEndingSoon($days = 7): bool
    {
        if ($this->ends_at === null) {
            return false;
        }

        return $this->ends_at->isBetween(now(), now()->addDays($days));
    }

    /**
     * Get days remaining until expiration.
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if ($this->ends_at === null) {
            return null;
        }

        return max(0, now()->diffInDays($this->ends_at, false));
    }

    /**
     * Get trial days remaining.
     */
    public function getTrialDaysRemainingAttribute(): ?int
    {
        if ($this->trial_ends_at === null) {
            return null;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'active' => 'Ativa',
            'canceled' => 'Cancelada',
            'expired' => 'Expirada',
            'pending' => 'Pendente',
            'suspended' => 'Suspensa',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(): bool
    {
        $this->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);

        return true;
    }

    /**
     * Reactivate the subscription.
     */
    public function reactivate(): bool
    {
        $this->update([
            'status' => 'active',
            'canceled_at' => null,
        ]);

        return true;
    }

    /**
     * Extend the subscription.
     */
    public function extend($days): bool
    {
        $endsAt = $this->ends_at ?? now();
        
        $this->update([
            'ends_at' => Carbon::parse($endsAt)->addDays($days),
        ]);

        return true;
    }
}
