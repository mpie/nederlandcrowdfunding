<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PageStatus;
use App\Helpers\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

final class Page extends Model
{
    protected $fillable = [
        'parent_id',
        'title',
        'slug',
        'content',
        'blocks',
        'seo_title',
        'seo_description',
        'status',
        'sort_order',
        'published_at',
        'created_by',
        'updated_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'blocks' => 'array',
        ];
    }

    /** @return Attribute<string, never> */
    protected function safeContent(): Attribute
    {
        return Attribute::get(fn (): string => HtmlSanitizer::sanitize($this->content));
    }

    public function isHomePage(): bool
    {
        return $this->slug === 'home';
    }

    public function block(string $key, mixed $default = null): mixed
    {
        return data_get($this->blocks, $key, $default);
    }

    protected static function booted(): void
    {
        static::creating(function (Page $page): void {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    /** @return BelongsTo<self, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<self, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** @param Builder<self> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PageStatus::Published);
    }

    public function getFullSlugAttribute(): string
    {
        $slugs = collect([$this->slug]);
        $parent = $this->parent;

        while ($parent !== null) {
            $slugs->prepend($parent->slug);
            $parent = $parent->parent;
        }

        return $slugs->implode('/');
    }

    public static function findByFullSlug(string $fullSlug): ?self
    {
        $segments = explode('/', trim($fullSlug, '/'));
        $page = null;

        foreach ($segments as $slug) {
            $query = self::where('slug', $slug);

            if ($page === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $page->id);
            }

            $page = $query->first();

            if ($page === null) {
                return null;
            }
        }

        return $page;
    }
}