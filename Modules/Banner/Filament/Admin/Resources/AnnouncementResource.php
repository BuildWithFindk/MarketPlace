<?php

namespace Modules\Banner\Filament\Admin\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Banner\App\Models\Announcement;
use Modules\Banner\Filament\Admin\Resources\AnnouncementResource\Pages\CreateAnnouncement;
use Modules\Banner\Filament\Admin\Resources\AnnouncementResource\Pages\EditAnnouncement;
use Modules\Banner\Filament\Admin\Resources\AnnouncementResource\Pages\ListAnnouncements;
use Modules\Banner\Filament\Admin\Resources\AnnouncementResource\Schemas\AnnouncementForm;
use Modules\Banner\Filament\Admin\Resources\AnnouncementResource\Tables\AnnouncementsTable;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationLabel = 'Announcements';

    protected static ?string $modelLabel = 'Announcement';

    protected static ?string $pluralModelLabel = 'Announcements';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return AnnouncementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnnouncementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnnouncements::route('/'),
            'create' => CreateAnnouncement::route('/create'),
            'edit' => EditAnnouncement::route('/{record}/edit'),
        ];
    }
}