<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Invoice extends Model
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
        'document_id',
        'invoice_number',
        'status',
        'issue_date',
        'due_date',
        'paid_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'notes',
        'terms',
        'payment_method',
        'payment_reference',
        'quickbooks_id',
        'metadata',
        'items',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'metadata' => 'array',
        'items' => 'array',
    ];

    /**
     * Invoice statuses
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Payment methods
     */
    const PAYMENT_CASH = 'cash';
    const PAYMENT_CHECK = 'check';
    const PAYMENT_CREDIT_CARD = 'credit_card';
    const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_PAYPAL = 'paypal';
    const PAYMENT_OTHER = 'other';

    /**
     * Get the client that owns the invoice.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the document that originated this invoice.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the documents for the invoice.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Scope a query to only include paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope a query to only include pending invoices.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
                    ->orWhere(function ($q) {
                        $q->where('due_date', '<', now())
                          ->whereIn('status', [self::STATUS_PENDING, self::STATUS_SENT]);
                    });
    }

    /**
     * Scope a query to only include invoices due soon.
     */
    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
                    ->where('due_date', '>=', now())
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_SENT]);
    }

    /**
     * Scope a query for invoices in a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    /**
     * Check if the invoice is paid.
     */
    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if the invoice is overdue.
     */
    public function isOverdue()
    {
        return $this->due_date < now() && !$this->isPaid();
    }

    /**
     * Check if the invoice is due soon.
     */
    public function isDueSoon($days = 7)
    {
        return $this->due_date <= now()->addDays($days) 
               && $this->due_date >= now() 
               && !$this->isPaid();
    }

    /**
     * Get the number of days until due date.
     */
    public function getDaysUntilDueAttribute()
    {
        if ($this->isPaid()) {
            return null;
        }
        
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get the invoice age in days.
     */
    public function getAgeInDaysAttribute()
    {
        return $this->issue_date->diffInDays(now());
    }

    /**
     * Mark the invoice as paid.
     */
    public function markAsPaid($paymentMethod = null, $paymentReference = null, $paidDate = null)
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_date' => $paidDate ?: now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);
    }

    /**
     * Mark the invoice as sent.
     */
    public function markAsSent()
    {
        $this->update([
            'status' => self::STATUS_SENT,
        ]);
    }

    /**
     * Mark the invoice as overdue.
     */
    public function markAsOverdue()
    {
        $this->update([
            'status' => self::STATUS_OVERDUE,
        ]);
    }

    /**
     * Calculate totals based on items.
     */
    public function calculateTotals()
    {
        if (!$this->items || !is_array($this->items)) {
            return;
        }

        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
        }

        $discountAmount = $this->discount_amount ?? 0;
        $taxAmount = ($subtotal - $discountAmount) * (($this->tax_rate ?? 0) / 100);
        $total = $subtotal - $discountAmount + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
        ]);
    }

    /**
     * Generate next invoice number.
     */
    public static function generateInvoiceNumber($prefix = 'INV')
    {
        $year = date('Y');
        $month = date('m');
        
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}-{$year}{$month}%")
                          ->orderBy('invoice_number', 'desc')
                          ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $nextNumber);
    }

    /**
     * Get available invoice statuses.
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_PAID => 'Paid',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get available payment methods.
     */
    public static function getPaymentMethods()
    {
        return [
            self::PAYMENT_CASH => 'Cash',
            self::PAYMENT_CHECK => 'Check',
            self::PAYMENT_CREDIT_CARD => 'Credit Card',
            self::PAYMENT_BANK_TRANSFER => 'Bank Transfer',
            self::PAYMENT_PAYPAL => 'PayPal',
            self::PAYMENT_OTHER => 'Other',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }
}
