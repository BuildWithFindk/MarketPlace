<div class="flex min-h-[620px] flex-col bg-white" data-inbox-thread-panel data-selected-conversation-id="{{ $selectedConversation?->id ?? '' }}">
    @if($selectedConversation)
    @php
        $activeListing = $selectedConversation->listing;
        $activePartner = (int) $selectedConversation->buyer_id === (int) auth()->id()
            ? $selectedConversation->seller
            : $selectedConversation->buyer;
        $activePriceLabel = $activeListing && !is_null($activeListing->price)
            ? number_format((float) $activeListing->price, 0).' '.($activeListing->currency ?? 'TL')
            : null;
    @endphp
    <div class="flex min-h-[80px] items-center gap-4 border-b border-gray-200 bg-white px-6">
        <div class="grid h-12 w-12 place-items-center rounded-full bg-slate-600 text-lg font-semibold text-white">
            {{ strtoupper(substr((string) ($activePartner?->name ?? 'K'), 0, 1)) }}
        </div>
        <div class="min-w-0">
            <p class="truncate text-lg font-semibold text-gray-900">{{ $activePartner?->name ?? 'User' }}</p>
            <p class="truncate text-sm text-gray-500">{{ $activeListing?->title ?? 'Listing removed' }}</p>
        </div>
        @if($activePriceLabel)
        <div class="ml-auto whitespace-nowrap text-sm font-medium text-gray-500">{{ $activePriceLabel }}</div>
        @endif
    </div>

    <div class="max-h-[390px] flex-1 overflow-y-auto bg-white px-6 py-6" data-inbox-thread>
        @forelse($selectedConversation->messages as $message)
        @php $isMine = (int) $message->sender_id === (int) auth()->id(); @endphp
        <div class="mb-4 flex {{ $isMine ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
            <div class="max-w-[80%]">
                <div class="px-4 py-2 text-base {{ $isMine ? 'bg-red-600 text-white' : 'border border-gray-200 bg-white text-gray-900' }}" style="{{ $isMine ? 'border-radius: 12px 2px 12px 12px;' : 'border-radius: 2px 12px 12px 12px;' }}">
                    {{ $message->body }}
                </div>
                <p class="mt-1 text-left text-xs text-gray-400">
                    {{ $message->created_at?->format('H:i') }}
                </p>
            </div>
        </div>
        @empty
        <div class="h-full grid place-items-center text-slate-500 text-center px-8" data-inbox-empty>
            <div>
                <p class="font-semibold text-slate-700">No messages yet.</p>
                <p class="mt-1 text-sm">Use a quick reply or send the first message below.</p>
            </div>
        </div>
        @endforelse
    </div>

    <div class="border-t border-gray-200 bg-white px-4 py-3">
        <div class="flex items-center gap-2 overflow-x-auto pb-2">
            @foreach($quickMessages as $quickMessage)
            <form method="POST" action="{{ route('conversations.messages.send', $selectedConversation) }}" class="shrink-0" data-inbox-send-form>
                @csrf
                <input type="hidden" name="message_filter" value="{{ $messageFilter }}">
                <input type="hidden" name="message" value="{{ $quickMessage }}">
                <button type="submit" class="inline-flex h-11 items-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    {{ $quickMessage }}
                </button>
            </form>
            @endforeach
        </div>
        <form method="POST" action="{{ route('conversations.messages.send', $selectedConversation) }}" class="flex items-center gap-2 border-t border-slate-200 pt-3 mt-1" data-inbox-send-form>
            @csrf
            <input type="hidden" name="message_filter" value="{{ $messageFilter }}">
            <input type="text" name="message" value="{{ old('message') }}" placeholder="Write a message" maxlength="2000" class="h-12 flex-1 rounded-lg border border-gray-300 bg-white px-5 text-sm focus:border-red-400 focus:outline-none" required data-inbox-message-input>
            <button type="submit" class="grid h-12 w-12 place-items-center rounded-lg bg-red-600 text-white transition hover:bg-red-700" aria-label="Send" data-inbox-send-button>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h13m0 0l-5-5m5 5l-5 5"/>
                </svg>
            </button>
        </form>
        <p class="text-xs text-rose-600 mt-2 px-2 hidden" data-inbox-send-error></p>
    </div>
    @else
    <div class="h-full min-h-[620px] grid place-items-center px-8 text-center text-slate-500">
        <div>
            <p class="text-2xl font-semibold text-slate-700">Choose a conversation to start messaging.</p>
            <p class="mt-2 text-sm">Start a new chat from a listing detail page or continue an existing thread here.</p>
        </div>
    </div>
    @endif
</div>
