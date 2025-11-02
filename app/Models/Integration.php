<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Integration extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

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
        'service_name',
        'description',
        'api_key',
        'api_secret',
        'webhook_url',
        'webhook_secret',
        'settings',
        'is_active',
        'last_sync_at',
        'sync_status',
        'error_message',
        'retry_count',
        'max_retries',
        'rate_limit_per_minute',
        'timeout_seconds',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'retry_count' => 'integer',
        'max_retries' => 'integer',
        'rate_limit_per_minute' => 'integer',
        'timeout_seconds' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'api_key',
        'api_secret',
        'webhook_secret',
    ];

    /**
     * Service types
     */
    const SERVICE_N8N = 'n8n';
    const SERVICE_QUICKBOOKS = 'quickbooks';
    const SERVICE_STRIPE = 'stripe';
    const SERVICE_PAYPAL = 'paypal';
    const SERVICE_SLACK = 'slack';
    const SERVICE_DISCORD = 'discord';
    const SERVICE_TELEGRAM = 'telegram';
    const SERVICE_WHATSAPP = 'whatsapp';
    const SERVICE_EMAIL = 'email';
    const SERVICE_SMS = 'sms';
    const SERVICE_CUSTOM = 'custom';

    /**
     * Sync statuses
     */
    const SYNC_STATUS_IDLE = 'idle';
    const SYNC_STATUS_SYNCING = 'syncing';
    const SYNC_STATUS_SUCCESS = 'success';
    const SYNC_STATUS_FAILED = 'failed';
    const SYNC_STATUS_PARTIAL = 'partial';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($integration) {
            if (empty($integration->max_retries)) {
                $integration->max_retries = 3;
            }
            if (empty($integration->rate_limit_per_minute)) {
                $integration->rate_limit_per_minute = 60;
            }
            if (empty($integration->timeout_seconds)) {
                $integration->timeout_seconds = 30;
            }
        });
    }

    /**
     * Scope to get active integrations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get integrations by service.
     */
    public function scopeByService($query, $service)
    {
        return $query->where('service_name', $service);
    }

    /**
     * Scope to get integrations with sync errors.
     */
    public function scopeWithErrors($query)
    {
        return $query->where('sync_status', self::SYNC_STATUS_FAILED);
    }

    /**
     * Scope to get integrations that need retry.
     */
    public function scopeNeedsRetry($query)
    {
        return $query->where('sync_status', self::SYNC_STATUS_FAILED)
                    ->where('retry_count', '<', 'max_retries');
    }

    /**
     * Get the encrypted API key.
     */
    public function getApiKeyAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set the encrypted API key.
     */
    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the encrypted API secret.
     */
    public function getApiSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set the encrypted API secret.
     */
    public function setApiSecretAttribute($value)
    {
        $this->attributes['api_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the encrypted webhook secret.
     */
    public function getWebhookSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set the encrypted webhook secret.
     */
    public function setWebhookSecretAttribute($value)
    {
        $this->attributes['webhook_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Check if integration is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if integration is syncing.
     */
    public function isSyncing(): bool
    {
        return $this->sync_status === self::SYNC_STATUS_SYNCING;
    }

    /**
     * Check if last sync was successful.
     */
    public function lastSyncSuccessful(): bool
    {
        return $this->sync_status === self::SYNC_STATUS_SUCCESS;
    }

    /**
     * Check if integration has errors.
     */
    public function hasErrors(): bool
    {
        return $this->sync_status === self::SYNC_STATUS_FAILED;
    }

    /**
     * Check if integration can retry.
     */
    public function canRetry(): bool
    {
        return $this->retry_count < $this->max_retries;
    }

    /**
     * Mark sync as started.
     */
    public function markSyncStarted(): void
    {
        $this->update([
            'sync_status' => self::SYNC_STATUS_SYNCING,
            'error_message' => null,
        ]);
    }

    /**
     * Mark sync as successful.
     */
    public function markSyncSuccessful(): void
    {
        $this->update([
            'sync_status' => self::SYNC_STATUS_SUCCESS,
            'last_sync_at' => now(),
            'error_message' => null,
            'retry_count' => 0,
        ]);
    }

    /**
     * Mark sync as failed.
     */
    public function markSyncFailed(string $errorMessage = null): void
    {
        $this->update([
            'sync_status' => self::SYNC_STATUS_FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Reset retry count.
     */
    public function resetRetryCount(): void
    {
        $this->update(['retry_count' => 0]);
    }

    /**
     * Test the integration connection.
     */
    public function testConnection(): bool
    {
        // This would be implemented based on the specific service
        // For now, return true if all required fields are present
        return !empty($this->api_key) && !empty($this->webhook_url);
    }

    /**
     * Get integration configuration for external services.
     */
    public function getConfig(): array
    {
        return [
            'service_name' => $this->service_name,
            'api_key' => $this->api_key,
            'api_secret' => $this->api_secret,
            'webhook_url' => $this->webhook_url,
            'webhook_secret' => $this->webhook_secret,
            'settings' => $this->settings ?? [],
            'rate_limit_per_minute' => $this->rate_limit_per_minute,
            'timeout_seconds' => $this->timeout_seconds,
        ];
    }

    /**
     * Get all available service types.
     */
    public static function getServiceTypes(): array
    {
        return [
            self::SERVICE_N8N => 'n8n Automation',
            self::SERVICE_QUICKBOOKS => 'QuickBooks',
            self::SERVICE_STRIPE => 'Stripe',
            self::SERVICE_PAYPAL => 'PayPal',
            self::SERVICE_SLACK => 'Slack',
            self::SERVICE_DISCORD => 'Discord',
            self::SERVICE_TELEGRAM => 'Telegram',
            self::SERVICE_WHATSAPP => 'WhatsApp',
            self::SERVICE_EMAIL => 'Email',
            self::SERVICE_SMS => 'SMS',
            self::SERVICE_CUSTOM => 'Custom Integration',
        ];
    }

    /**
     * Get all available sync statuses.
     */
    public static function getSyncStatuses(): array
    {
        return [
            self::SYNC_STATUS_IDLE => 'Idle',
            self::SYNC_STATUS_SYNCING => 'Syncing',
            self::SYNC_STATUS_SUCCESS => 'Success',
            self::SYNC_STATUS_FAILED => 'Failed',
            self::SYNC_STATUS_PARTIAL => 'Partial',
        ];
    }

    /**
     * Get the status badge color for UI.
     */
    public function getStatusBadgeColor(): string
    {
        return match ($this->sync_status) {
            self::SYNC_STATUS_SUCCESS => 'green',
            self::SYNC_STATUS_SYNCING => 'blue',
            self::SYNC_STATUS_FAILED => 'red',
            self::SYNC_STATUS_PARTIAL => 'yellow',
            default => 'gray',
        };
    }
}
