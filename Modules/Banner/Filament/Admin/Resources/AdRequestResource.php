<?php

namespace Modules\Banner\Filament\Admin\Resources;

use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Banner\App\Models\AdRequest;
use Modules\Banner\App\Models\Banner;
use Modules\Banner\Filament\Admin\Resources\AdRequestResource\Pages;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;
use UnitEnum;

class AdRequestResource extends Resource
{
    protected static ?string $model = AdRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Ad Requests';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('ad_title')->disabled(),
            TextInput::make('desired_link_type')->disabled(),
            TextInput::make('desired_link_value')->disabled(),
            TextInput::make('contact_phone')->disabled(),
            TextInput::make('contact_email')->disabled(),
            Textarea::make('message')->disabled()->columnSpanFull(),
            TextInput::make('status')->disabled(),
            Textarea::make('admin_notes')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->getStateUsing(fn (AdRequest $record) => $record->getImageUrl()),
                TextColumn::make('ad_title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('desired_link_type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (AdRequest $record): string => $record->statusBadgeColor())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (AdRequest $record): bool => $record->status === 'pending')
                    ->form([
                        Select::make('position')
                            ->options([
                                1 => 'Slot 1 (Top Left)',
                                2 => 'Slot 2 (Top Center)',
                                3 => 'Slot 3 (Top Right)',
                                4 => 'Slot 4 (Bottom Left)',
                                5 => 'Slot 5 (Bottom Center)',
                                6 => 'Slot 6 (Bottom Right)',
                            ])
                            ->required(),
                        Select::make('link_type')
                            ->options([
                                'category' => 'Category',
                                'listing' => 'Listing',
                                'external' => 'External',
                                'none' => 'None',
                            ])
                            ->default(fn (AdRequest $record) => $record->desired_link_type)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('link_value', null);
                            }),
                        Select::make('link_value')
                            ->label(fn (Get $get): string => match ($get('link_type')) {
                                'category' => 'Category',
                                'listing' => 'Listing',
                                default => 'Link Value',
                            })
                            ->options(fn (Get $get): array => match ($get('link_type')) {
                                'category' => Category::activeIdNameOptions(),
                                'listing' => Listing::query()->active()->orderBy('title')->pluck('title', 'id')->all(),
                                default => [],
                            })
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => in_array($get('link_type'), ['category', 'listing'], true))
                            ->visible(fn (Get $get): bool => in_array($get('link_type'), ['category', 'listing'], true))
                            ->default(fn (AdRequest $record) => in_array($record->desired_link_type, ['category', 'listing'], true) ? $record->desired_link_value : null)
                            ->dehydrated(fn (Get $get): bool => in_array($get('link_type'), ['category', 'listing'], true)),
                        TextInput::make('link_value')
                            ->label('External URL')
                            ->url()
                            ->maxLength(2048)
                            ->required(fn (Get $get): bool => $get('link_type') === 'external')
                            ->visible(fn (Get $get): bool => $get('link_type') === 'external')
                            ->default(fn (AdRequest $record) => $record->desired_link_type === 'external' ? $record->desired_link_value : null)
                            ->dehydrated(fn (Get $get): bool => $get('link_type') === 'external'),
                        Toggle::make('is_active')
                            ->default(true),
                        DateTimePicker::make('ends_at')
                            ->nullable(),
                    ])
                    ->action(function (array $data, AdRequest $record): void {
                        $banner = Banner::create([
                            'title' => $record->ad_title,
                            'image_path' => $record->image_path,
                            'position' => $data['position'],
                            'link_type' => $data['link_type'],
                            'link_value' => $data['link_value'] ?? null,
                            'is_active' => $data['is_active'],
                            'ends_at' => $data['ends_at'],
                        ]);

                        $record->update([
                            'status' => 'approved',
                            'banner_id' => $banner->id,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Ad request approved and banner created')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (AdRequest $record): bool => $record->status === 'pending')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Rejection Reason')
                            ->required(),
                    ])
                    ->action(function (array $data, AdRequest $record): void {
                        $record->update([
                            'status' => 'rejected',
                            'admin_notes' => $data['admin_notes'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Ad request rejected')
                            ->success()
                            ->send();
                    }),

                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdRequests::route('/'),
        ];
    }
}
