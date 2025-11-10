<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Communication extends Model
{
    use HasFactory, SoftDeletes;

    // Delivery type constants
    const DELIVERY_TYPE_IMMEDIATE = 'IMMEDIATE';
    const DELIVERY_TYPE_SCHEDULED = 'SCHEDULED';
    const DELIVERY_TYPE_RECURRING = 'RECURRING';

    // Status constants
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_SENT = 'SENT';
    const STATUS_PARTIALLY_SENT = 'PARTIALLY_SENT';
    const STATUS_FAILED = 'FAILED';
    const STATUS_CANCELLED = 'CANCELLED';

    // Source constants
    const SOURCE_MANUAL = 'MANUAL';
    const SOURCE_API = 'API';
    const SOURCE_SYSTEM = 'SYSTEM';
    const SOURCE_IMPORT = 'IMPORT';

    protected $fillable = [
        '_uuid',
        'communication_type_id',
        'communication_category_id',
        'campaign_id',
        'created_by',
        'team_id',
        'variant_name',
        'variant_metrics',
        'content',
        'variables',
        'attachments',
        'delivery_type',
        'scheduled_for',
        'recurrence',
        '_status',
        'total_recipients',
        'successful_deliveries',
        'failed_deliveries',
        'delivery_errors',
        '_slug',
        'approved_at',
        'approved_by',
        'sent_at',
        'completed_at',
    ];

    protected $casts = [
        'recurrence' => 'array',
        'variables' => 'array',
        'attachments' => 'array',
        'delivery_errors' => 'array',
        'metadata' => 'array',
        'scheduled_for' => 'datetime',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'variant_metrics' => 'array',
    ];

    public static $recurrenceFrequencies = [
        'daily',
        'weekly',
        'monthly',
        'quarterly',
        'yearly',
        'custom'
    ];

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->_uuid = (string) Str::uuid();
            $model->_slug = (string) Str::slug(Str::random(10) . '-' . $model->subject);
        });
    }

    protected static function booted()
    {
        static::saving(function ($communication) {
            if ($communication->delivery_type === 'RECURRING') {
                $recurrence = $communication->recurrence;
                
                if (!is_array($recurrence)) {
                    throw new \InvalidArgumentException('Recurrence must be an array');
                }
                
                if (!in_array($recurrence['frequency'] ?? null, self::$recurrenceFrequencies)) {
                    throw new \InvalidArgumentException('Invalid recurrence frequency');
                }
                
                // Validate days for weekly frequency
                if (($recurrence['frequency'] ?? null) === 'weekly') {
                    if (empty($recurrence['days']) || !is_array($recurrence['days'])) {
                        throw new \InvalidArgumentException('Weekly frequency requires days array');
                    }
                    
                    foreach ($recurrence['days'] as $day) {
                        if (!is_numeric($day) || $day < 0 || $day > 6) {
                            throw new \InvalidArgumentException('Invalid day value in recurrence days array');
                        }
                    }
                }
                
                // Validate end_type if present
                $validEndTypes = ['never', 'after_occurrences', 'end_date'];
                if (isset($recurrence['end_type']) && !in_array($recurrence['end_type'], $validEndTypes)) {
                    throw new \InvalidArgumentException('Invalid recurrence end type');
                }
                
                // Validate end_value based on end_type
                if (isset($recurrence['end_type'])) {
                    if ($recurrence['end_type'] === 'after_occurrences' && (!isset($recurrence['end_value']) || !is_numeric($recurrence['end_value']) || $recurrence['end_value'] <= 0)) {
                        throw new \InvalidArgumentException('Invalid end value for after_occurrences end type');
                    }
                    
                    if ($recurrence['end_type'] === 'end_date' && (!isset($recurrence['end_value']) || !strtotime($recurrence['end_value']))) {
                        throw new \InvalidArgumentException('Invalid date format for end_date end type');
                    }
                }
            }
        });
    }

    // Relationships
    public function communicationType()
    {
        return $this->belongsTo(CommunicationType::class);
    }

    public function communicationCategory()
    {
        return $this->belongsTo(CommunicationCategory::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}