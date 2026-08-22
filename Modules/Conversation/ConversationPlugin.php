<?php

namespace Modules\Conversation;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class ConversationPlugin implements Plugin
{
    public function getId(): string
    {
        return 'conversation';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: module_path('Conversation', 'Filament/Admin/Resources'),
            for: 'Modules\\Conversation\\Filament\\Admin\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
