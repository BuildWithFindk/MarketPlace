<?php

namespace Modules\Conversation\Filament\Admin\Resources\ConversationResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Conversation\Filament\Admin\Resources\ConversationResource;

class ListConversations extends ListRecords
{
    protected static string $resource = ConversationResource::class;
}
