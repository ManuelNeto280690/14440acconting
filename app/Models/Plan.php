<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Plan extends Model
{
    use HasFactory, SoftDeletes, HasUuids, CentralConnection;              

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
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'billing_cycle_days',
        'features',
        'limits',
        'is_active',
        'is_popular',
        'stripe_price_id',
        'trial_days',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'price' => 'decimal:2',
        'trial_days' => 'integer',
        'billing_cycle_days' => 'integer',
        'sort_order' => 'integer',~
        'id' => 'string',
    ];

  


    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = $plan->generateUniqueSlug($plan->name);
            }
        });

        static::updating(function ($plan) {
            if ($plan->isDirty('name') && empty($plan->slug)) {
                $plan->slug = $plan->generateUniqueSlug($plan->name);
            }
        });
    }

    /**
     * Generate a unique slug for the plan.
     */
    public function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the subscriptions for the plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /**
     * Get active subscriptions for the plan.
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->where('status', 'active');
    }

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include popular plans.
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Get the billing cycle label.
     */
    public function getBillingCycleLabelAttribute()
    {
        $cycles = [
            'monthly' => 'Mensal',
            'yearly' => 'Anual',
            'quarterly' => 'Trimestral',
            'weekly' => 'Semanal',
        ];

        return $cycles[$this->billing_cycle] ?? ucfirst($this->billing_cycle);
    }

    /**
     * Check if plan has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get a specific limit value.
     */
    public function getLimit(string $key, $default = null)
    {
        return $this->limits[$key] ?? $default;
    }

    /**
     * Check if plan is free.
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Get total active subscriptions count.
     */
    public function getActiveSubscriptionsCountAttribute()
    {
        return $this->activeSubscriptions()->count();
    }

    /**
     * Get total revenue from this plan.
     */
    public function getTotalRevenueAttribute()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->sum('amount');
    }
}
