<?php

namespace Modules\Banner\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'type',
        'message',
        'background_color',
        'text_color',
        'background_image',
        'banner_image',
        'position',
        'priority',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeForPosition(
        Builder $query,
        string $position
    ): Builder {
        return $query->where('position', $position);
    }

    public static function currentFor(string $position): ?self
    {
        return static::query()
            ->active()
            ->forPosition($position)
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->first();
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = match ($this->type) {
            'background_image' => $this->background_image,
            'banner_image' => $this->banner_image,
            default => null,
        };

        return $path
            ? asset('storage/' . ltrim($path, '/'))
            : null;
    }
}