<?php

namespace Modules\Conversation\Filament\Admin\Resources;

use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\HtmlString;
use Modules\Conversation\App\Models\Conversation;
use Modules\Conversation\App\Models\ConversationMessage;
use Modules\Conversation\Filament\Admin\Resources\ConversationResource\Pages;
use Modules\User\App\Models\User;
use UnitEnum;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?string $navigationLabel = 'Conversations';

    public static function getNavigationBadge(): ?string
    {
        return (string) Conversation::query()->count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'buyer',
                'seller',
                'listing',
                'messages' => fn (Relation $relation): mixed => $relation
                    ->getQuery()
                    ->with('sender')
                    ->oldest(),
            ])
            ->withCount('messages');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('buyer.name')
                    ->label('Buyer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unknown buyer'),
                TextColumn::make('seller.name')
                    ->label('Seller')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unknown seller'),
                TextColumn::make('listing.title')
                    ->label('Listing')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->placeholder('Listing removed'),
                TextColumn::make('messages_count')
                    ->label('Messages')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_message_at')
                    ->label('Last Message')
                    ->since()
                    ->sortable()
                    ->placeholder('No messages'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('M j, Y')
                    ->sortable(),
            ])
            ->filters([
                static::createdAtFilter(),
                SelectFilter::make('listing_id')
                    ->label('Listing')
                    ->relationship('listing', 'title')
                    ->searchable(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('View Thread')
                    ->modalHeading(fn (Conversation $record): string => 'Conversation #'.$record->getKey())
                    ->modalWidth('5xl')
                    ->schema([])
                    ->modalContent(fn (Conversation $record): HtmlString => static::conversationThread($record)),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
        ];
    }

    private static function createdAtFilter(): Filter
    {
        return Filter::make('created_at')
            ->label('Created Date')
            ->schema([
                DatePicker::make('from')
                    ->label('From'),
                DatePicker::make('until')
                    ->label('Until'),
            ])
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when($data['from'] ?? null, fn (Builder $query, mixed $date): Builder => $query->whereDate('created_at', '>=', $date))
                ->when($data['until'] ?? null, fn (Builder $query, mixed $date): Builder => $query->whereDate('created_at', '<=', $date)));
    }

    private static function conversationThread(Conversation $conversation): HtmlString
    {
        $conversation->loadMissing([
            'buyer',
            'seller',
            'listing',
            'messages.sender',
        ]);

        return new HtmlString(
            '<div class="space-y-6">'.
            static::listingSummary($conversation).
            '<div class="grid gap-4 md:grid-cols-2">'.
            static::participantSummary('Buyer', $conversation->buyer).
            static::participantSummary('Seller', $conversation->seller).
            '</div>'.
            static::messageList($conversation).
            '</div>'
        );
    }

    private static function listingSummary(Conversation $conversation): string
    {
        $listing = $conversation->listing;
        $title = e($listing?->title ?? 'Listing removed');

        if (! $listing) {
            return '<section class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"><p class="text-sm font-medium text-gray-950 dark:text-white">'.$title.'</p></section>';
        }

        $url = e(route('listings.show', $listing));

        return '<section class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">'.
            '<p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Listing</p>'.
            '<a href="'.$url.'" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">'.$title.'</a>'.
            '</section>';
    }

    private static function participantSummary(string $label, ?User $user): string
    {
        $name = e($user?->name ?: 'Unknown user');
        $email = e($user?->email ?: 'No email');

        return '<section class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">'.
            '<p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">'.e($label).'</p>'.
            '<p class="mt-1 text-sm font-medium text-gray-950 dark:text-white">'.$name.'</p>'.
            '<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">'.$email.'</p>'.
            '</section>';
    }

    private static function messageList(Conversation $conversation): string
    {
        $messages = $conversation->messages
            ->sortBy('created_at')
            ->values();

        if ($messages->isEmpty()) {
            return '<section class="space-y-3"><h3 class="text-sm font-medium text-gray-950 dark:text-white">Messages</h3><p class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">No messages yet.</p></section>';
        }

        return '<section class="space-y-3">'.
            '<h3 class="text-sm font-medium text-gray-950 dark:text-white">Messages</h3>'.
            $messages
                ->map(fn (ConversationMessage $message): string => static::messageItem($message))
                ->implode('').
            '</section>';
    }

    private static function messageItem(ConversationMessage $message): string
    {
        $senderName = e($message->sender?->name ?: 'Unknown sender');
        $sentAt = e($message->created_at?->format('M j, Y g:i A') ?? 'No timestamp');
        $body = e((string) $message->body);

        return '<article class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">'.
            '<div class="flex flex-wrap items-center gap-x-3 gap-y-1">'.
            '<p class="text-sm font-medium text-gray-950 dark:text-white">'.$senderName.'</p>'.
            '<time class="text-xs text-gray-500 dark:text-gray-400">'.$sentAt.'</time>'.
            '</div>'.
            '<p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-gray-700 dark:text-gray-300">'.$body.'</p>'.
            '</article>';
    }
}
