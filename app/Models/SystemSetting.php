<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember("system_setting:{$key}", 300, function () use ($key) {
            return static::where('key', $key)->value('value');
        });

        if ($value === null) {
            return $default;
        }

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    public static function put(string $key, mixed $value): void
    {
        $stored = is_scalar($value) ? (string) $value : json_encode($value);
        static::updateOrCreate(['key' => $key], ['value' => $stored]);
        Cache::forget("system_setting:{$key}");
    }
}
