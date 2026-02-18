<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PageStatus;
use App\Helpers\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'published_at',
        'author_id',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post): void {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /** @return Attribute<string, never> */
    protected function safeContent(): Attribute
    {
        return Attribute::get(fn (): string => HtmlSanitizer::sanitize($this->content));
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @param Builder<self> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PageStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}