<?php

namespace Modules\Banner;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class BannerPlugin implements Plugin
{
    public function getId(): string
    {
        return 'banner';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: module_path('Banner', 'Filament/Admin/Resources'),
            for: 'Modules\\Banner\\Filament\\Admin\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
