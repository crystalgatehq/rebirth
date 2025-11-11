<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Communication extends Model
{
    use HasFactory, SoftDeletes;

    // Delivery type constants (using integers as per migration)
    const DELIVERY_TYPE_IMMEDIATE = 1;
    const DELIVERY_TYPE_SCHEDULED = 2;
    const DELIVERY_TYPE_RECURRING = 3;
    
    // Delivery type labels
    public static $deliveryTypeLabels = [
        self::DELIVERY_TYPE_IMMEDIATE => 'Immediate',
        self::DELIVERY_TYPE_SCHEDULED => 'Scheduled',
        self::DELIVERY_TYPE_RECURRING => 'Recurring',
    ];

    // Status constants (using integers as per migration)
    const STATUS_DRAFT = 1;
    const STATUS_PENDING_APPROVAL = 2;
    const STATUS_APPROVED = 3;
    const STATUS_PROCESSING = 4;
    const STATUS_SENT = 5;
    const STATUS_PARTIALLY_SENT = 6;
    const STATUS_FAILED = 7;
    const STATUS_CANCELLED = 8;
    
    // Status labels
    public static $statusLabels = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PENDING_APPROVAL => 'Pending Approval',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_PROCESSING => 'Processing',
        self::STATUS_SENT => 'Sent',
        self::STATUS_PARTIALLY_SENT => 'Partially Sent',
        self::STATUS_FAILED => 'Failed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];
    
    // Final statuses (no further processing needed)
    public static $finalStatuses = [
        self::STATUS_SENT,
        self::STATUS_CANCELLED,
        self::STATUS_FAILED,
    ];

    // Source constants
    const SOURCE_MANUAL = 'MANUAL';
    const SOURCE_API = 'API';
    const SOURCE_SYSTEM = 'SYSTEM';
    const SOURCE_IMPORT = 'IMPORT';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'communication_type_id',
        'communication_category_id',
        'campaign_id',
        'created_by',
        'team_id',
        'variant_name',
        'variant_metrics',
        'subject',
        'content',
        'variables',
        'attachments',
        'delivery_type',
        'scheduled_for',
        'recurrence',
        'status',
        'total_recipients',
        'successful_deliveries',
        'failed_deliveries',
        'delivery_errors',
        'slug',
        'approved_at',
        'approved_by',
        'last_processed_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'recurrence' => 'array',
        'variables' => 'array',
        'attachments' => 'array',
        'delivery_errors' => 'array',
        'metadata' => 'array',
        'variant_metrics' => 'array',
        'scheduled_for' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'approved_at' => 'datetime',
        'last_processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'total_recipients' => 'integer',
        'successful_deliveries' => 'integer',
        'failed_deliveries' => 'integer',
    ];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'scheduled_for',
        'start_at',
        'end_at',
        'approved_at',
        'last_processed_at',
        'completed_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'delivery_type' => self::DELIVERY_TYPE_IMMEDIATE,
        'total_recipients' => 0,
        'successful_deliveries' => 0,
        'failed_deliveries' => 0,
    ];

    /**
     * The recurrence frequencies.
     *
     * @var array<string, string>
     */
    public static $recurrenceFrequencies = [
        'daily',
        'weekly',
        'monthly',
        'quarterly',
        'yearly',
        'custom'
    ];

    /**
     * Get the validation rules for the communication.
     *
     * @return array
     */
    public static function rules()
    {
        return [
            'recurrence' => ['required', 'array', new \App\Rules\ValidRecurrence],
            'recurrence.frequency' => 'required_if:delivery_type,RECURRING|nullable|in:' . implode(',', self::$recurrenceFrequencies),
            'recurrence.days' => 'required_if:recurrence.frequency,weekly|array',
            'recurrence.days.*' => 'integer|min:0|max:6', // 0-6 for days of week
            'recurrence.end_type' => 'required_if:delivery_type,RECURRING|in:never,after_occurrences,end_date',
            'recurrence.end_value' => 'nullable|required_if:recurrence.end_type,after_occurrences,end_date',
        ];
    }

    /**
     * The "saving" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($communication) {
            // Validate recurrence if this is a recurring communication
            if ($communication->delivery_type === self::DELIVERY_TYPE_RECURRING) {
                $recurrence = $communication->recurrence;
                
                if (!is_array($recurrence)) {
                    throw new \InvalidArgumentException('Recurrence must be an array');
                }
                
                $frequency = $recurrence['frequency'] ?? null;
                
                if (!in_array($frequency, self::$recurrenceFrequencies)) {
                    throw new \InvalidArgumentException('Invalid recurrence frequency');
                }
                
                // Validate days for weekly frequency
                if ($frequency === 'weekly') {
                    if (empty($recurrence['days']) || !is_array($recurrence['days'])) {
                        throw new \InvalidArgumentException('Weekly frequency requires days array');
                    }
                    
                    foreach ($recurrence['days'] as $day) {
                        if (!is_numeric($day) || $day < 0 || $day > 6) {
                            throw new \InvalidArgumentException('Invalid day value in recurrence days array');
                        }
                    }
                }
            }
            
            // Set completed_at if communication is in a final state
            if (in_array($communication->status, self::$finalStatuses) && !$communication->completed_at) {
                $communication->completed_at = now();
            }
            
            // Update last_processed_at when status changes to PROCESSING
            if ($communication->isDirty('status') && $communication->status === self::STATUS_PROCESSING) {
                $communication->last_processed_at = now();
            }
        });
        
        // When a communication is being processed, update the last_processed_at timestamp
        static::updating(function ($communication) {
            if ($communication->isDirty('status') && $communication->status === self::STATUS_PROCESSING) {
                $communication->last_processed_at = now();
            }
        });
    }


    /**
     * Get the delivery logs for the communication.
     */
    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(CommunicationDeliveryLog::class);
    }
    
    /**
     * Get the communication type this communication belongs to.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(CommunicationType::class, 'communication_type_id');
    }
    
    /**
     * Get the communication category this communication belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CommunicationCategory::class, 'communication_category_id');
    }
    
    /**
     * Get the campaign this communication belongs to.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
    
    /**
     * Get the team this communication belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
    
    /**
     * Get all receipts for this communication.
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(CommunicationReceipt::class);
    }

    // Scopes
    
    /**
     * Scope a query to only include draft communications.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }
    
    
    /**
     * Scope a query to only include processing communications.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }
    
    /**
     * Scope a query to only include sent communications.
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }
    
    /**
     * Scope a query to only include failed communications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }
    
    /**
     * Scope a query to only include cancelled communications.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }
    
    /**
     * Scope a query to only include active communications (not in final state).
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', self::$finalStatuses);
    }
    
    /**
     * Scope a query to only include communications that need processing.
     */
    public function scopeNeedsProcessing($query)
    {
        return $query->whereIn('status', [self::STATUS_APPROVED, self::STATUS_PARTIALLY_SENT])
                    ->where(function($q) {
                        $q->whereNull('last_processed_at')
                          ->orWhere('last_processed_at', '<', now()->subHour());
                    });
    }
    
    /**
     * Scope a query to only include communications that are scheduled for sending.
     */
    public function scopeScheduled($query)
    {
        return $query->where('delivery_type', self::DELIVERY_TYPE_SCHEDULED)
                    ->where('scheduled_for', '<=', now())
                    ->whereIn('status', [self::STATUS_APPROVED, self::STATUS_PARTIALLY_SENT]);
    }
    
    /**
     * Scope a query to only include communications that are due for recurring sending.
     */
    public function scopeDueForRecurring($query)
    {
        return $query->where('delivery_type', self::DELIVERY_TYPE_RECURRING)
                    ->whereIn('status', [self::STATUS_APPROVED, self::STATUS_PARTIALLY_SENT])
                    ->where(function($q) {
                        $q->whereNull('last_processed_at')
                          ->orWhere('last_processed_at', '<', now()->subDay());
                    });
    }
    
    // Status check helpers
    
    /**
     * Check if the communication is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
    
    /**
     * Check if the communication is pending approval.
     */
    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }
    
    /**
     * Check if the communication is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
    
    /**
     * Check if the communication is being processed.
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }
    
    /**
     * Check if the communication has been sent.
     */
    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }
    
    /**
     * Check if the communication is partially sent.
     */
    public function isPartiallySent(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_SENT;
    }
    
    /**
     * Check if the communication has failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
    
    /**
     * Check if the communication has been cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
    
    /**
     * Check if the communication is scheduled.
     * 
     * @return bool
     */
    public function isScheduled(): bool
    {
        return $this->delivery_type === self::DELIVERY_TYPE_SCHEDULED && 
               $this->scheduled_for && 
               $this->scheduled_for->isFuture();
    }
    
    /**
     * Check if the communication is recurring.
     * 
     * @return bool
     */
    public function isRecurring(): bool
    {
        return $this->delivery_type === self::DELIVERY_TYPE_RECURRING && 
               !empty($this->recurrence);
    }
    
    /**
     * Check if the communication is in a final state (no further processing needed).
     * 
     * @return bool
     */
    public function isFinalState(): bool
    {
        return in_array($this->status, self::$finalStatuses);
    }
    
    /**
     * Check if the communication is immediate.
     */
    public function isImmediate(): bool
    {
        return $this->delivery_type === self::DELIVERY_TYPE_IMMEDIATE;
    }
    
    /**
     * Check if the communication can be processed.
     */
    public function canBeProcessed(): bool
    {
        if ($this->isFinalState()) {
            return false;
        }
        
        if ($this->isScheduled() && $this->scheduled_for->isFuture()) {
            return false;
        }
        
        // If it was processed recently, don't process again too soon
        if ($this->last_processed_at && $this->last_processed_at->gt(now()->subMinutes(5))) {
            return false;
        }
        
        return true;
    }
    
    // Timestamp helpers
    
    /**
     * Mark the communication as processed.
     */
    public function markAsProcessed(): self
    {
        $this->last_processed_at = now();
        $this->save();
        return $this;
    }
    
    /**
     * Mark the communication as completed.
     */
    public function markAsCompleted(string $status = self::STATUS_SENT): self
    {
        $this->status = $status;
        $this->completed_at = now();
        $this->save();
        return $this;
    }
    
    /**
     * Get the human-readable status.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? 'Unknown';
    }
    
    /**
     * Get the human-readable delivery type.
     */
    public function getDeliveryTypeLabelAttribute(): string
    {
        return self::$deliveryTypeLabels[$this->delivery_type] ?? 'Unknown';
    }
    
    /**
     * Get the time since last processing attempt.
     */
    public function getTimeSinceLastProcessedAttribute(): ?string
    {
        return $this->last_processed_at ? $this->last_processed_at->diffForHumans() : null;
    }
    
    /**
     * Get the delivery success rate.
     */
    public function getDeliverySuccessRateAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0.0;
        }
        
        return round(($this->successful_deliveries / $this->total_recipients) * 100, 2);
    }
    
    /**
     * Get the delivery failure rate.
     */
    public function getDeliveryFailureRateAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0.0;
        }
        
        return round(($this->failed_deliveries / $this->total_recipients) * 100, 2);
    }

    /**
     * Scope a query to only include pending approval communications.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    /**
     * Scope a query to only include approved communications.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Check if the communication can be sent.
     *
     * @return bool
     */
    public function canBeSent(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_DRAFT]) 
            && !$this->trashed();
    }
}