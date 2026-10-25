<?php

namespace Modules\Banner\Filament\Admin\Resources\AnnouncementResource\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Announcement')
                    ->schema([

                        TextInput::make('title')
                            ->label('Internal Name')
                            ->required()
                            ->maxLength(255)
                            ->helperText(
                                'Used only to identify this announcement in the admin panel.'
                            ),

                        Select::make('type')
                            ->label('Banner Type')
                            ->options([
                                'text' => 'Text Banner',
                                'background_image' => 'Background Image',
                                'banner_image' => 'Pre-designed Banner',
                            ])
                            ->default('text')
                            ->required()
                            ->live(),

                        Textarea::make('message')
                            ->label('Banner Text')
                            ->rows(3)
                            ->maxLength(500)
                            ->required(fn (Get $get): bool =>
                                $get('type') === 'text'
                            )
                            ->visible(fn (Get $get): bool =>
                                in_array(
                                    $get('type'),
                                    ['text', 'background_image'],
                                    true
                                )
                            )
                            ->helperText(
                                'Text displayed inside the banner.'
                            ),

                        ColorPicker::make('background_color')
                            ->label('Background Color')
                            ->default('#111827')
                            ->visible(fn (Get $get): bool =>
                                $get('type') === 'text'
                            ),

                        ColorPicker::make('text_color')
                            ->label('Text Color')
                            ->default('#FFFFFF')
                            ->visible(fn (Get $get): bool =>
                                in_array(
                                    $get('type'),
                                    ['text', 'background_image'],
                                    true
                                )
                            ),

                    ])
                    ->columns(2),

                Section::make('Background Image')
                    ->schema([

                        FileUpload::make('background_image')
                            ->label('Background Image')
                            ->image()
                            ->disk('public')
                            ->directory('announcements/backgrounds')
                            ->visibility('public')
                            ->imageAspectRatio('6:1')
                            ->automaticallyOpenImageEditorForAspectRatio()
                            ->maxSize(5120)
                            ->required(fn (Get $get): bool =>
                                $get('type') === 'background_image'
                            )
                            ->visible(fn (Get $get): bool =>
                                $get('type') === 'background_image'
                            )
                            ->helperText(
                                'Banner ratio: 6:1. Recommended output: 1800 × 300 px.'
                            ),

                    ])
                    ->visible(fn (Get $get): bool =>
                        $get('type') === 'background_image'
                    ),

                Section::make('Pre-designed Banner')
                    ->schema([

                        FileUpload::make('banner_image')
                            ->label('Complete Banner Image')
                            ->image()
                            ->disk('public')
                            ->directory('announcements/banners')
                            ->visibility('public')
                            ->imageAspectRatio('6:1')
                            ->automaticallyOpenImageEditorForAspectRatio()
                            ->maxSize(5120)
                            ->required(fn (Get $get): bool =>
                                $get('type') === 'banner_image'
                            )
                            ->visible(fn (Get $get): bool =>
                                $get('type') === 'banner_image'
                            )
                            ->helperText(
                                'Upload the complete banner design. Recommended output: 1800 × 300 px.'
                            ),

                    ])
                    ->visible(fn (Get $get): bool =>
                        $get('type') === 'banner_image'
                    ),

                Section::make('Display Settings')
                    ->schema([

                        Select::make('position')
                            ->label('Position')
                            ->options([
                                'top' => 'Top',
                                'bottom' => 'Bottom',
                                'homepage' => 'Homepage',
                                'listing' => 'Listing Pages',
                            ])
                            ->default('top')
                            ->required(),

                        TextInput::make('priority')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->helperText(
                                'Higher priority appears first when multiple banners are active.'
                            ),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(false)
                            ->helperText(
                                'Only active banners within their schedule are shown.'
                            ),

                        DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->seconds(false)
                            ->native(false),

                        DateTimePicker::make('ends_at')
                            ->label('Ends At')
                            ->seconds(false)
                            ->native(false)
                            ->after('starts_at'),

                    ])
                    ->columns(2),

            ]);
    }
}