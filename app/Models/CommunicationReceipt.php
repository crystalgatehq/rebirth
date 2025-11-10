<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationReceipt extends Model
{
    use HasFactory, SoftDeletes;

    // Status Constants
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_SENT = 'SENT';
    public const STATUS_DELIVERED = 'DELIVERED';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_UNDELIVERED = 'UNDELIVERED';
    public const STATUS_PARTIALLY_SENT = 'PARTIALLY_SENT';

    protected $fillable = [
        'communication_id',
        'contact_group_id',
        'communication_category_id',
        'campaign_id',
        'phone_number',
        'recipient_name',
        'message',
        'template_identifier',
        'template_variables',
        'sender_id',
        'sender_name',
        'status',
        'provider_name',
        'provider_message_id',
        'provider_response',
        'retry_count',
        'error_message',
        'delivery_errors',
        'scheduled_at',
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

    protected $casts = [
        'template_variables' => 'array',
        'provider_response' => 'array',
        'delivery_errors' => 'array',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'processing_completed_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'cost' => 'decimal:4',
    ];

    // Relationships
    public function communication()
    {
        return $this->belongsTo(Communication::class);
    }

    public function contactGroup()
    {
        return $this->belongsTo(ContactGroup::class);
    }

    public function communicationCategory()
    {
        return $this->belongsTo(CommunicationCategory::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeUndelivered($query)
    {
        return $query->where('status', self::STATUS_UNDELIVERED);
    }

    public function scopeForProvider($query, $providerName)
    {
        return $query->where('provider_name', $providerName);
    }

    // Status Helpers
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => self::STATUS_PROCESSING,
            'processing_started_at' => now(),
        ]);
    }

    public function markAsSent(string $providerMessageId, ?string $providerName = null, ?array $providerResponse = null): bool
    {
        return $this->update([
            'status' => self::STATUS_SENT,
            'provider_message_id' => $providerMessageId,
            'provider_name' => $providerName ?? $this->provider_name,
            'provider_response' => $providerResponse,
            'sent_at' => now(),
            'processing_completed_at' => now(),
        ]);
    }

    public function markAsDelivered(): bool
    {
        return $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    }

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

    public function incrementRetryCount(): int
    {
        $this->increment('retry_count');
        return $this->retry_count;
    }
}