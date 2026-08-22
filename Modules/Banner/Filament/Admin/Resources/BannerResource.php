<?php

namespace Modules\Banner\Filament\Admin\Resources;

use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\Banner\App\Models\Banner;
use Modules\Banner\Filament\Admin\Resources\BannerResource\Pages;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;
use Modules\Site\App\Support\LocalMedia;
use UnitEnum;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Banners';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            FileUpload::make('image_path')
                ->label('Image')
                ->disk('public')
                ->directory('banners')
                ->visibility('public')
                ->image()
                ->imageEditor()
                ->maxSize(2048)
                ->required()
                ->columnSpanFull(),
            TextInput::make('title')
                ->maxLength(255),
            Select::make('position')
                ->options(static::positionOptions())
                ->required()
                ->rules(fn (Get $get, ?Banner $record): array => static::activePositionRules($get, $record)),
            Select::make('link_type')
                ->options(static::linkTypeOptions())
                ->default('none')
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set): void {
                    $set('link_value', null);
                }),
            Select::make('link_value')
                ->label(fn (Get $get): string => match ($get('link_type')) {
                    'category' => 'Category',
                    'listing' => 'Listing',
                    default => 'Link Value',
                })
                ->options(fn (Get $get): array => static::linkOptions($get))
                ->searchable()
                ->preload()
                ->required(fn (Get $get): bool => in_array($get('link_type'), ['category', 'listing'], true))
                ->visible(fn (Get $get): bool => in_array($get('link_type'), ['category', 'listing'], true))
                ->dehydrated(fn (Get $get): bool => in_array($get('link_type'), ['category', 'listing'], true)),
            TextInput::make('link_value')
                ->label('External URL')
                ->url()
                ->maxLength(2048)
                ->required(fn (Get $get): bool => $get('link_type') === 'external')
                ->visible(fn (Get $get): bool => $get('link_type') === 'external')
                ->dehydrated(fn (Get $get): bool => $get('link_type') === 'external'),
            Toggle::make('is_active')
                ->default(true),
            DateTimePicker::make('starts_at')
                ->nullable(),
            DateTimePicker::make('ends_at')
                ->nullable()
                ->afterOrEqual('starts_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk(LocalMedia::disk())
                    ->visibility('public')
                    ->imageHeight(56)
                    ->circular(false),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Untitled')
                    ->limit(40),
                TextColumn::make('position')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (int|string|null $state): string => filled($state) ? 'Slot '.$state : '-'),
                TextColumn::make('link_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::linkTypeOptions()[$state] ?? ucfirst($state)),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->disabled(fn (Banner $record): bool => ! $record->is_active && static::activeBannerExistsForPosition((int) $record->position, $record))
                    ->sortable(),
                TextColumn::make('click_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('view_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime('M j, Y H:i')
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
                SelectFilter::make('link_type')
                    ->options(static::linkTypeOptions()),
            ])
            ->defaultSort('position')
            ->recordActions(ResourceTableActions::editDelete())
            ->toolbarActions(ResourceTableActions::bulkDelete());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }

    private static function positionOptions(): array
    {
        return [
            1 => 'Slot 1 (Top Left)',
            2 => 'Slot 2 (Top Center)',
            3 => 'Slot 3 (Top Right)',
            4 => 'Slot 4 (Bottom Left)',
            5 => 'Slot 5 (Bottom Center)',
            6 => 'Slot 6 (Bottom Right)',
        ];
    }

    private static function linkTypeOptions(): array
    {
        return [
            'category' => 'Category',
            'listing' => 'Listing',
            'external' => 'External',
            'none' => 'None',
        ];
    }

    private static function linkOptions(Get $get): array
    {
        return match ($get('link_type')) {
            'category' => Category::activeIdNameOptions(),
            'listing' => Listing::query()
                ->active()
                ->orderBy('title')
                ->pluck('title', 'id')
                ->all(),
            default => [],
        };
    }

    private static function activePositionRules(Get $get, ?Banner $record): array
    {
        if (! (bool) $get('is_active')) {
            return [];
        }

        $rule = Rule::unique('banners', 'position')
            ->where(fn ($query) => $query->where('is_active', true));

        if ($record) {
            $rule->ignore($record->getKey());
        }

        return [$rule];
    }

    private static function activeBannerExistsForPosition(int $position, Banner $record): bool
    {
        return Banner::query()
            ->where('position', $position)
            ->where('is_active', true)
            ->whereKeyNot($record)
            ->exists();
    }
}
