<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationReceipt extends Model
{
    use HasFactory, SoftDeletes;

    // Status Constants
    public const STATUS_DRAFT = 1;
    public const STATUS_PENDING = 2;
    public const STATUS_PROCESSING = 3;
    public const STATUS_SENT = 4;
    public const STATUS_DELIVERED = 5;
    public const STATUS_FAILED = 6;
    public const STATUS_UNDELIVERED = 7;
    public const STATUS_PARTIALLY_SENT = 8;
    public const STATUS_CANCELLED = 9;

    // Provider Constants
    public const PROVIDER_AFRICASTALKING = 1;
    public const PROVIDER_TWILIO = 2;
    public const PROVIDER_NEXMO = 3;
    public const PROVIDER_WHATSAPP = 4;
    public const PROVIDER_EMAIL = 5;
    public const PROVIDER_SYSTEM = 6;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'campaign_id',
        'communication_type_id',
        'communication_category_id',
        'communication_id',
        'contact_group_id',
        'contact_group_subscriber_id',
        'recipient_telephone',
        'recipient_name',
        'template_content',
        'template_identifier',
        'template_variables',
        'sender_id',
        'sender_name',
        'provider',
        'provider_message_id',
        'provider_response',
        'retry_count',
        'error_message',
        'delivery_errors',
        'processing_started_at',
        'processing_completed_at',
        'sent_at',
        'delivered_at',
        'failed_at',
        'cost',
        'currency',
        'metadata',
        'created_by',
        'updated_by',
        'team_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'template_variables' => 'array',
        'provider_response' => 'array',
        'delivery_errors' => 'array',
        'metadata' => 'array',
        'processing_started_at' => 'datetime',
        'processing_completed_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'cost' => 'decimal:4',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'provider' => self::PROVIDER_AFRICASTALKING,
    ];

    /**
     * Get the communication that owns the receipt.
     */
    public function communication(): BelongsTo
    {
        return $this->belongsTo(Communication::class);
    }

    /**
     * Get the contact group that owns the receipt.
     */
    public function contactGroup(): BelongsTo
    {
        return $this->belongsTo(ContactGroup::class);
    }

    /**
     * Get the communication category that owns the receipt.
     */
    public function communicationCategory(): BelongsTo
    {
        return $this->belongsTo(CommunicationCategory::class);
    }

    /**
     * Get the campaign that owns the receipt.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the creator that owns the receipt.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the updater that owns the receipt.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the team that owns the receipt.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope a query to only include pending receipts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include processing receipts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope a query to only include sent receipts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Scope a query to only include delivered receipts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    /**
     * Scope a query to only include failed receipts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope a query to only include undelivered receipts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUndelivered($query)
    {
        return $query->where('status', self::STATUS_UNDELIVERED);
    }

    /**
     * Scope a query to only include for provider receipts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $providerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProvider($query, $providerId)
    {
        return $query->where('provider', $providerId);
    }

    /**
     * Check if the receipt is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the receipt is processing.
     *
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if the receipt is sent.
     *
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * Check if the receipt is delivered.
     *
     * @return bool
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if the receipt is failed.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark the receipt as processing.
     *
     * @return bool
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => self::STATUS_PROCESSING,
            'processing_started_at' => now(),
        ]);
    }

    /**
     * Mark the receipt as sent.
     *
     * @param string $providerMessageId
     * @param int $provider
     * @param array $providerResponse
     * @return bool
     */
    public function markAsSent(string $providerMessageId, ?int $provider = null, ?array $providerResponse = null): bool
    {
        return $this->update([
            'status' => self::STATUS_SENT,
            'provider_message_id' => $providerMessageId,
            'provider' => $provider ?? $this->provider,
            'provider_response' => $providerResponse,
            'sent_at' => now(),
            'processing_completed_at' => now(),
        ]);
    }

    /**
     * Mark the receipt as delivered.
     *
     * @return bool
     */
    public function markAsDelivered(): bool
    {
        return $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark the receipt as failed.
     *
     * @param string $errorMessage
     * @param array $errors
     * @return bool
     */
    public function markAsFailed(string $errorMessage, ?array $errors = null): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'delivery_errors' => $errors,
            'failed_at' => now(),
            'processing_completed_at' => now(),
        ]);
    }

    /**
     * Increment the retry count.
     *
     * @return int
     */
    public function incrementRetryCount(): int
    {
        $this->increment('retry_count');
        return $this->retry_count;
    }
}