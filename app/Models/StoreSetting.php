<?php

namespace App\Models;

use Database\Factories\StoreSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value'])]
class StoreSetting extends Model
{
    /** @use HasFactory<StoreSettingFactory> */
    use HasFactory;

    /** @param array<string, string|null> $settings */
    public static function putMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    /** @param array<int, string> $keys
     * @return array<string, string|null>
     */
    public static function values(array $keys): array
    {
        $stored = static::query()->whereIn('key', $keys)->pluck('value', 'key');

        return collect($keys)->mapWithKeys(fn (string $key): array => [$key => $stored->get($key)])->all();
    }
}
