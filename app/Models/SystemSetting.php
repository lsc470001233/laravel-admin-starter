<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'label',
    ];

    public static function getGroup(string $group): array
    {
        return Cache::remember("system_settings:{$group}", 300, function () use ($group) {
            return static::query()
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public static function setGroup(string $group, array $data, array $labels = []): void
    {
        foreach ($data as $key => $value) {
            static::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'value' => $value,
                    'label' => $labels[$key] ?? null,
                ]
            );
        }
        Cache::forget("system_settings:{$group}");
    }
}
