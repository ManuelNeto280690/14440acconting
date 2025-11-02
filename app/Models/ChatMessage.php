<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
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
        'user_id',
        'client_id',
        'message',
        'type',
        'direction',
        'status',
        'metadata',
        'response_to',
        'processed_at',
        'ai_confidence',
        'intent',
        'entities',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'entities' => 'array',
        'processed_at' => 'datetime',
        'ai_confidence' => 'float',
    ];

    /**
     * Message types
     */
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_DOCUMENT = 'document';
    const TYPE_AUDIO = 'audio';
    const TYPE_SYSTEM = 'system';
    const TYPE_AI_CONVERSATION = 'ai_conversation';

    /**
     * Message directions
     */
    const DIRECTION_INBOUND = 'inbound';
    const DIRECTION_OUTBOUND = 'outbound';

    /**
     * Message statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';

    /**
     * Get the user that sent this message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client associated with this message.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the message this is a response to.
     */
    public function responseTo()
    {
        return $this->belongsTo(ChatMessage::class, 'response_to');
    }

    /**
     * Get the responses to this message.
     */
    public function responses()
    {
        return $this->hasMany(ChatMessage::class, 'response_to');
    }

    /**
     * Scope to get messages by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get messages by direction.
     */
    public function scopeByDirection($query, $direction)
    {
        return $query->where('direction', $direction);
    }

    /**
     * Scope to get inbound messages.
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', self::DIRECTION_INBOUND);
    }

    /**
     * Scope to get outbound messages.
     */
    public function scopeOutbound($query)
    {
        return $query->where('direction', self::DIRECTION_OUTBOUND);
    }

    /**
     * Scope to get processed messages.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', self::STATUS_PROCESSED);
    }

    /**
     * Scope to get pending messages.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get messages for a specific client.
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope to get conversation thread.
     */
    public function scopeConversationThread($query, $messageId)
    {
        return $query->where(function ($q) use ($messageId) {
            $q->where('id', $messageId)
              ->orWhere('response_to', $messageId)
              ->orWhereHas('responseTo', function ($subQ) use ($messageId) {
                  $subQ->where('id', $messageId);
              });
        });
    }

    /**
     * Check if message is inbound.
     */
    public function isInbound(): bool
    {
        return $this->direction === self::DIRECTION_INBOUND;
    }

    /**
     * Check if message is outbound.
     */
    public function isOutbound(): bool
    {
        return $this->direction === self::DIRECTION_OUTBOUND;
    }

    /**
     * Check if message is processed.
     */
    public function isProcessed(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    /**
     * Check if message is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Mark message as processed.
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark message as failed.
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    /**
     * Get all available message types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_TEXT => 'Text',
            self::TYPE_IMAGE => 'Image',
            self::TYPE_DOCUMENT => 'Document',
            self::TYPE_AUDIO => 'Audio',
            self::TYPE_SYSTEM => 'System',
            self::TYPE_AI_CONVERSATION => 'AI Conversation',
        ];
    }

    /**
     * Get all available message directions.
     */
    public static function getDirections(): array
    {
        return [
            self::DIRECTION_INBOUND => 'Inbound',
            self::DIRECTION_OUTBOUND => 'Outbound',
        ];
    }

    /**
     * Get all available message statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_PROCESSED => 'Processed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_READ => 'Read',
        ];
    }
}
