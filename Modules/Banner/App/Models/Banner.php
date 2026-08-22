<?php

namespace Modules\Banner\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

class Banner extends Model
{
    private const MIN_POSITION = 1;

    private const MAX_POSITION = 6;

    protected $fillable = [
        'title',
        'image_path',
        'position',
        'link_type',
        'link_value',
        'is_active',
        'starts_at',
        'ends_at',
        'click_count',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'click_count' => 'integer',
            'view_count' => 'integer',
        ];
    }

    protected function position(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): int => $this->validatedPosition($value),
        );
    }

    public function getDestinationUrl(): string
    {
        return match ($this->link_type) {
            'category' => $this->routeUrl('categories.show'),
            'listing' => $this->routeUrl('listings.show'),
            'external' => $this->link_value ?? '#',
            default => '#',
        };
    }

    public function getImageUrl(): string
    {
        if (! $this->image_path) {
            return asset('images/banner-placeholder.png');
        }

        if (! \Storage::disk('public')->exists($this->image_path)) {
            return asset('images/banner-placeholder.png');
        }

        return \Storage::disk('public')->url($this->image_path);
    }

    public function isLive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function scopeLiveForHomepage(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('position');
    }

    public function trackClick(): void
    {
        $this->increment('click_count');
    }

    public function trackView(): void
    {
        $this->increment('view_count');
    }

    private function routeUrl(string $routeName): string
    {
        if (! filled($this->link_value) || ! Route::has($routeName)) {
            return '#';
        }

        return route($routeName, $this->link_value);
    }

    private function validatedPosition(mixed $value): int
    {
        $position = filter_var($value, FILTER_VALIDATE_INT);

        if ($position === false || $position < self::MIN_POSITION || $position > self::MAX_POSITION) {
            throw new InvalidArgumentException('Banner position must be between 1 and 6.');
        }

        return $position;
    }
}
