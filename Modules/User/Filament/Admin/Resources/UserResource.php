<?php

namespace Modules\User\Filament\Admin\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Support\Filament\ResourceTableColumns;
use Modules\User\App\Models\User;
use Modules\User\App\Support\Filament\UserFormFields;
use Modules\User\Filament\Admin\Resources\UserResource\Pages;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Management';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            UserFormFields::name(),
            TextInput::make('first_name')->maxLength(255),
            TextInput::make('last_name')->maxLength(255),
            UserFormFields::email(),
            TextInput::make('phone')->maxLength(255),
            TextInput::make('country')->maxLength(255),
            TextInput::make('district')->maxLength(255),
            TextInput::make('city')->maxLength(255),
            Select::make('status')->options(static::statusOptions())->required(),
            UserFormFields::password(fn ($livewire) => $livewire instanceof Pages\CreateUser),
            UserFormFields::roles(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('listings'))
            ->columns([
                ResourceTableColumns::id(),
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->getStateUsing(fn (User $record): string => $record->getAvatarUrl()),
                TextColumn::make('full_name')
                    ->label('Name')
                    ->getStateUsing(fn (User $record): string => $record->getFullName() ?: $record->name ?: $record->email)
                    ->searchable(['first_name', 'last_name', 'name', 'email'])
                    ->sortable(false),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('phone')->searchable()->sortable()->default('-'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state, User $record): string => match ((string) ($record->status?->value ?? $record->status ?? '')) {
                        'active' => 'Active',
                        'banned' => 'Banned',
                        'suspended' => 'Suspended',
                        default => 'Unknown',
                    })
                    ->color(fn (User $record): string => match ((string) ($record->status?->value ?? $record->status ?? '')) {
                        'active' => 'success',
                        'banned' => 'danger',
                        'suspended' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->since()
                    ->sortable(),
                TextColumn::make('listings_count')
                    ->label('Listings')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('user_status')
                    ->label('Status')
                    ->form([
                        Select::make('status_filter')
                            ->label('Status')
                            ->options(static::statusOptions()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $selectedStatus = $data['status_filter'] ?? null;

                        if (filled($selectedStatus)) {
                            $query->where('status', $selectedStatus);
                        }

                        return $query;
                    }),
                Filter::make('created_at')
                    ->label('Date joined')
                    ->form([
                        DatePicker::make('created_from')->label('From'),
                        DatePicker::make('created_until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['created_from'] ?? null)) {
                            $query->whereDate('created_at', '>=', $data['created_from']);
                        }

                        if (filled($data['created_until'] ?? null)) {
                            $query->whereDate('created_at', '<=', $data['created_until']);
                        }

                        return $query;
                    }),
            ])
            ->actions([
                Action::make('viewProfile')
                    ->label('View Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (User $record): string => $record->getProfileUrl()),
                Action::make('banUser')
                    ->label('Ban User')
                    ->icon('heroicon-o-no-symbol')
                    ->visible(fn (User $record): bool => (int) $record->getKey() !== (int) Auth::id() && (string) ($record->status?->value ?? $record->status ?? '') === 'active')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => "Ban {$record->username}? They will not be able to login.")
                    ->action(function (User $record): void {
                        $record->update(['status' => 'banned']);
                    })
                    ->successNotificationTitle('User banned.'),
                Action::make('suspendUser')
                    ->label('Suspend User')
                    ->icon('heroicon-o-pause-circle')
                    ->visible(fn (User $record): bool => (int) $record->getKey() !== (int) Auth::id() && (string) ($record->status?->value ?? $record->status ?? '') === 'active')
                    ->form([
                        DateTimePicker::make('suspended_until')->label('Suspend until')->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->forceFill([
                            'status' => 'suspended',
                            'account_expires_at' => $data['suspended_until'],
                        ])->save();
                    })
                    ->successNotificationTitle('User suspended.'),
                Action::make('restoreAccess')
                    ->label('Restore Access')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (User $record): bool => (int) $record->getKey() !== (int) Auth::id() && in_array((string) ($record->status?->value ?? $record->status ?? ''), ['banned', 'suspended'], true))
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => "Restore access for {$record->username}?")
                    ->action(function (User $record): void {
                        $record->forceFill([
                            'status' => 'active',
                            'account_expires_at' => null,
                        ])->save();
                    })
                    ->successNotificationTitle('Access restored.'),
                Action::make('editUser')
                    ->label('Edit User')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        TextInput::make('first_name')->maxLength(255),
                        TextInput::make('last_name')->maxLength(255),
                        TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                        TextInput::make('phone')->maxLength(255),
                        TextInput::make('country')->maxLength(255),
                        TextInput::make('district')->maxLength(255),
                        TextInput::make('city')->maxLength(255),
                        Select::make('status')->options(static::statusOptions())->required(),
                    ])
                    ->fillForm(fn (User $record): array => [
                        'first_name' => $record->first_name,
                        'last_name' => $record->last_name,
                        'email' => $record->email,
                        'phone' => $record->phone,
                        'country' => $record->country,
                        'district' => $record->district,
                        'city' => $record->city,
                        'status' => (string) ($record->status?->value ?? $record->status ?? 'active'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->forceFill([
                            'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
                            'first_name' => $data['first_name'] ?? null,
                            'last_name' => $data['last_name'] ?? null,
                            'email' => $data['email'] ?? $record->email,
                            'phone' => $data['phone'] ?? null,
                            'country' => $data['country'] ?? null,
                            'district' => $data['district'] ?? null,
                            'city' => $data['city'] ?? null,
                            'status' => $data['status'] ?? 'active',
                        ])->save();
                    })
                    ->successNotificationTitle('User updated.'),
            ])
            ->bulkActions([
                BulkAction::make('bulkBan')
                    ->label('Ban selected')
                    ->icon('heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->modalHeading('Ban selected users?')
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            if ((int) $record->getKey() !== (int) Auth::id() && (string) ($record->status?->value ?? $record->status ?? '') === 'active') {
                                $record->update(['status' => 'banned']);
                            }
                        }
                    })
                    ->successNotificationTitle('Selected users banned.'),
                BulkAction::make('bulkRestore')
                    ->label('Restore selected')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalHeading('Restore selected users?')
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            if ((int) $record->getKey() !== (int) Auth::id() && in_array((string) ($record->status?->value ?? $record->status ?? ''), ['banned', 'suspended'], true)) {
                                $record->forceFill([
                                    'status' => 'active',
                                    'account_expires_at' => null,
                                ])->save();
                            }
                        }
                    })
                    ->successNotificationTitle('Selected users restored.'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'activities' => Pages\ListUserActivities::route('/{record}/activities'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    protected static function statusOptions(): array
    {
        return [
            'active' => 'Active',
            'banned' => 'Banned',
            'suspended' => 'Suspended',
        ];
    }
}
