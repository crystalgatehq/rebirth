<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
class CommunicationCategory extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'communication_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'communication_type_id',
        'name',
        'slug',
        'icon',
        'color',
        'description',
        'template',
        'parent_id',
        'lft',
        'rgt',
        'depth',
        'metadata',
        'configuration',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'template' => 'array',
        'metadata' => 'array',
        'configuration' => 'array',
        'status' => 'integer',
        'lft' => 'integer',
        'rgt' => 'integer',
        'depth' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'icon' => 'folder',
        'color' => '#6b7280',
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * Get the communication type that owns the category.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(CommunicationType::class, 'communication_type_id');
    }

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CommunicationCategory::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(CommunicationCategory::class, 'parent_id');
    }

    /**
     * Get the communications for the category.
     */
    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class, 'category_id');
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Check if the category is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the category has children.
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get the template configuration.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getTemplate(string $key = null, $default = null)
    {
        $template = $this->template ?? [];
        
        if (is_null($key)) {
            return $template;
        }

        return $template[$key] ?? $default;
    }
}
