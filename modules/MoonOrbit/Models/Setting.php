<?php

declare(strict_types=1);

namespace Modules\MoonOrbit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $key
 * @property string|null $value
 */
final class Setting extends Model
{
    public const CACHE_KEY = 'moon-orbit.settings';

    public $timestamps = false;

    protected $table = 'settings';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    /**
     * @return array<string, string|null>
     */
    public static function values(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            static fn (): array => self::query()->pluck('value', 'key')->all(),
        );
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::values()[$key] ?? $default;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
