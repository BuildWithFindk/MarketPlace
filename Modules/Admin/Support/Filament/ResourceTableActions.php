<?php

namespace Modules\Admin\Support\Filament;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

final class ResourceTableActions
{
    public static function editDelete(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    public static function editActivityDelete(string $resourceClass, array $afterActivity = []): array
    {
        return [
            EditAction::make(),
            self::activities($resourceClass),
            ...$afterActivity,
            DeleteAction::make(),
        ];
    }

    public static function bulkDelete(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function activities(string $resourceClass): Action
    {
        return Action::make('activities')
            ->icon('heroicon-o-clock')
            ->url(fn ($record): string => $resourceClass::getUrl('activities', ['record' => $record]));
    }
}
