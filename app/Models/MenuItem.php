<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MenuLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

final class MenuItem extends Model
{
    protected $fillable = [
        'location',
        'parent_id',
        'label',
        'url',
        'route_name',
        'route_params',
        'icon',
        'target',
        'is_active',
        'is_highlighted',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'location' => MenuLocation::class,
            'route_params' => 'array',
            'is_active' => 'boolean',
            'is_highlighted' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return HasMany<self, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /** @return BelongsTo<self, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param Builder<self> $query */
    public function scopeTopLevel(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /** @param Builder<self> $query */
    public function scopeForLocation(Builder $query, MenuLocation $location): void
    {
        $query->where('location', $location);
    }

    /** @return Attribute<string, never> */
    protected function resolvedUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->route_name) {
                try {
                    return route($this->route_name, $this->route_params ?? []);
                } catch (\Throwable) {
                    return '#';
                }
            }

            return $this->url ?? '#';
        });
    }

    /** @return Collection<int, self> */
    public static function getMenuForLocation(MenuLocation $location): Collection
    {
        return Cache::remember(
            "menu_{$location->value}",
            now()->addMinutes(60),
            fn (): Collection => self::query()
                ->forLocation($location)
                ->active()
                ->topLevel()
                ->with(['children' => fn (HasMany $q): HasMany => $q->active()->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get(),
        );
    }

    public static function clearMenuCache(): void
    {
        foreach (MenuLocation::cases() as $location) {
            Cache::forget("menu_{$location->value}");
        }
    }

    protected static function booted(): void
    {
        $clearCache = fn (): null => self::clearMenuCache();
        static::saved($clearCache);
        static::deleted($clearCache);
    }
}