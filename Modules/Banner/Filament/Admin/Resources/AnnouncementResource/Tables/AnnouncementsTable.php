<?php

namespace Modules\Banner\Filament\Admin\Resources\AnnouncementResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('title')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'text' => 'Text',
                            'background_image' => 'Background Image',
                            'banner_image' => 'Pre-designed',
                            default => ucfirst($state),
                        }
                    ),

                TextColumn::make('position')
                    ->label('Position')
                    ->badge(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('priority')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'background_image' => 'Background Image',
                        'banner_image' => 'Pre-designed Banner',
                    ]),

                SelectFilter::make('position')
                    ->options([
                        'top' => 'Top',
                        'bottom' => 'Bottom',
                        'homepage' => 'Homepage',
                        'listing' => 'Listing Pages',
                    ]),
            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}