<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $guarded = ['id'];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    /**
     * Set (upsert) a setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Get all settings as a flat key→value array.
     */
    public static function all_map(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }

    /**
     * No-op kept for backwards compatibility — caching removed.
     */
    public static function clearAllCache(): void
    {
        // Cache removed — site_settings is a tiny table, no cache needed.
    }
}

