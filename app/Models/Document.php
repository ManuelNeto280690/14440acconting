<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
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
        'client_id',
        'invoice_id',
        'name',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'type',
        'status',
        'processed_at',
        'ocr_text',
        'ai_analysis',
        'metadata',
        'tags',
        'description',
        'uploaded_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'processed_at' => 'datetime',
        'ai_analysis' => 'array',
        'metadata' => 'array',
        'tags' => 'array',
        'file_size' => 'integer',
    ];

    /**
     * Document types
     */
    const TYPE_INVOICE = 'invoice';
    const TYPE_RECEIPT = 'receipt';
    const TYPE_CONTRACT = 'contract';
    const TYPE_STATEMENT = 'statement';
    const TYPE_OTHER = 'other';

    /**
     * Document statuses
     */
    const STATUS_UPLOADED = 'uploaded';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';

    /**
     * Get the client that owns the document.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the invoice that the document belongs to.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the invoices that were generated from this document.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope a query to only include processed documents.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', self::STATUS_PROCESSED);
    }

    /**
     * Scope a query to only include documents of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include documents with OCR text.
     */
    public function scopeWithOcr($query)
    {
        return $query->whereNotNull('ocr_text');
    }

    /**
     * Get the document's file URL.
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return Storage::url($this->file_path);
        }
        return null;
    }

    /**
     * Get the document's file size in human readable format.
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the document has been processed.
     */
    public function isProcessed()
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    /**
     * Check if the document is currently being processed.
     */
    public function isProcessing()
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if the document processing failed.
     */
    public function hasFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark the document as processed.
     */
    public function markAsProcessed($ocrText = null, $aiAnalysis = null)
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_at' => now(),
            'ocr_text' => $ocrText,
            'ai_analysis' => $aiAnalysis,
        ]);
    }

    /**
     * Mark the document as failed.
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }

    /**
     * Get available document types.
     */
    public static function getTypes()
    {
        return [
            self::TYPE_INVOICE => 'Invoice',
            self::TYPE_RECEIPT => 'Receipt',
            self::TYPE_CONTRACT => 'Contract',
            self::TYPE_STATEMENT => 'Statement',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get available document statuses.
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_UPLOADED => 'Uploaded',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_PROCESSED => 'Processed',
            self::STATUS_FAILED => 'Failed',
        ];
    }
}
