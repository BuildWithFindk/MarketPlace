<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Conversation\App\Models\Conversation;
use Modules\Conversation\App\Models\ConversationMessage;
use Modules\Conversation\Filament\Admin\Resources\ConversationResource;
use Modules\Listing\Models\Listing;
use Modules\User\App\Models\User;
use Tests\TestCase;

class ConversationResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_resource_can_eager_load_messages(): void
    {
        config()->set('activitylog.enabled', false);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $listing = Listing::create([
            'title' => 'Test Listing',
            'slug' => 'test-listing',
            'user_id' => $user->getKey(),
        ]);

        $conversation = Conversation::create([
            'listing_id' => $listing->getKey(),
            'seller_id' => $user->getKey(),
            'buyer_id' => $user->getKey(),
            'last_message_at' => now(),
        ]);

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->getKey(),
            'sender_id' => $user->getKey(),
            'body' => 'Hello there',
        ]);

        $loadedConversation = ConversationResource::getEloquentQuery()
            ->find($conversation->getKey());

        $this->assertNotNull($loadedConversation);
        $this->assertTrue($loadedConversation->relationLoaded('messages'));
        $this->assertSame($message->getKey(), $loadedConversation->messages->first()->getKey());
    }
}
