<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'default_value',
        'current_value',
        'data_type',
        'group',
        'sort_order',
        'is_public',
        'options',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'uuid' => 'string',
        'is_public' => 'boolean',
        'options' => 'json',
        'sort_order' => 'integer',
        'status' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'is_active',
    ];

    /**
     * Get the setting value by item name.
     *
     * @param string $item
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $item, $default = null)
    {
        return Cache::rememberForever("setting_{$item}", function () use ($item, $default) {
            $setting = static::where('slug', $item)->first();
            
            if (!$setting) {
                return $default;
            }

            return $setting->current_value ?? $setting->default_value;
        });
    }

    /**
     * Set a setting value.
     *
     * @param string $item
     * @param mixed $value
     * @return Setting
     */
    public static function setValue(string $item, $value): Setting
    {
        $setting = static::firstOrNew(['slug' => $item]);
        $setting->current_value = $value;
        $setting->save();

        Cache::forget("setting_{$item}");
        
        return $setting;
    }

    /**
     * Get all settings as a key-value array.
     *
     * @param string|null $group
     * @return array
     */
    public static function getAllSettings(string $group = null): array
    {
        $query = $group ? static::where('group', $group) : static::query();
        
        return $query->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->slug => $setting->current_value ?? $setting->default_value];
            })
            ->toArray();
    }

    /**
     * Check if a setting exists.
     *
     * @param string $item
     * @return bool
     */
    public static function has(string $item): bool
    {
        return static::where('slug', $item)->exists();
    }

    /**
     * Get the data type of a setting's value.
     *
     * @param mixed $value
     * @return string
     */
    public static function getDataType($value): string
    {
        $type = gettype($value);
        
        return in_array($type, ['string', 'integer', 'boolean', 'double', 'array', 'object']) 
            ? $type 
            : 'string';
    }

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::saved(function ($setting) {
            Cache::forget("setting_{$setting->slug}");
        });

        static::deleted(function ($setting) {
            Cache::forget("setting_{$setting->slug}");
        });
    }
}
