@php
    $listingImage = $listing->primaryImageData('card');
    $isFavorited = isset($favoriteListingIds)
        ? in_array($listing->id, $favoriteListingIds, true)
        : (auth()->check() && auth()->user()->favoriteListings()->whereKey($listing->getKey())->exists());
    $city = trim((string) ($listing->city ?? ''));
    $priceLabel = number_format((float) ($listing->price ?? 0)).' NPR';
    $cityLabel = $city !== '' ? ucwords(strtolower($city)) : 'Location not specified';
    $postedLabel = $listing->created_at?->diffForHumans() ?? '';
@endphp

<article
    data-listing-card
    class="cursor-pointer overflow-hidden rounded-lg border border-gray-200 bg-white transition-colors hover:border-gray-400"
>
    <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
        <div class="pointer-events-none absolute inset-0 grid place-items-center text-gray-300" aria-hidden="true">
            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.5-4.5a2 2 0 012.8 0L16 16m-1.5-1.5l1.8-1.8a2 2 0 012.8 0L21 14m-7-8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>

        @if($listingImage)
            <a href="{{ route('listings.show', $listing) }}" class="relative block h-full w-full">
                @include('listing::partials.responsive-image', [
                    'image' => $listingImage,
                    'alt' => $listing->title,
                    'class' => 'h-full w-full rounded-t-lg object-cover',
                    'hideOnError' => true,
                ])
            </a>
        @endif

        @if($listing->is_featured)
            <span class="absolute left-2 top-2 rounded border border-gray-300 bg-white px-2 py-0.5 text-[11px] font-medium text-gray-700">
                Featured
            </span>
        @endif

        <div class="absolute right-2 top-2">
            @auth
                <form method="POST" action="{{ route('favorites.listings.toggle', $listing) }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 bg-white p-1.5 text-sm hover:bg-gray-50 {{ $isFavorited ? 'text-red-600' : 'text-gray-500' }}"
                        aria-label="Save listing"
                    >
                        {{ $isFavorited ? '♥' : '♡' }}
                    </button>
                </form>
            @else
                <a
                    href="{{ route('login') }}"
                    class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 bg-white p-1.5 text-sm text-gray-500 hover:bg-gray-50"
                    aria-label="Sign in to save"
                >
                    ♡
                </a>
            @endauth
        </div>
    </div>

    <div class="px-2.5 py-2.5">
        <a href="{{ route('listings.show', $listing) }}" class="block">
            <p class="text-base font-semibold leading-tight text-gray-900">{{ $priceLabel }}</p>
            <h3 class="mt-1 line-clamp-1 truncate text-sm leading-snug text-gray-700">{{ $listing->title }}</h3>
        </a>

        <p class="mt-1 truncate text-xs text-gray-400">
            {{ $cityLabel }}
            @if($postedLabel !== '')
                <span class="text-gray-400"> · {{ $postedLabel }}</span>
            @endif
        </p>
    </div>
</article>
