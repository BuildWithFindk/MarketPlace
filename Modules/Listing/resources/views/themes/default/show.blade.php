@extends('app::layouts.app')
@section('content')
@php
    $title = trim((string) ($listing->title ?? ''));
    $displayTitle = ($title !== '' && preg_match('/[\pL\pN]/u', $title)) ? $title : 'Untitled listing';

    $city = trim((string) ($listing->city ?? ''));
    $country = trim((string) ($listing->country ?? ''));
    // Force display country first as Nepal for frontend views
    $location = $city !== '' ? 'Nepal, '.ucwords(strtolower($city)) : 'Nepal';

    $description = trim((string) ($listing->description ?? ''));
    $displayDescription = ($description !== '' && preg_match('/[\pL\pN]/u', $description))
        ? $description
        : 'No description provided.';

    $hasPrice = !is_null($listing->price);
    $priceValue = $hasPrice ? (float) $listing->price : null;
    $currencyRaw = trim((string) ($listing->currency ?? ''));
    $displayCurrency = strtoupper($currencyRaw) === 'TRY' ? 'NPR' : ($currencyRaw !== '' ? $currencyRaw : 'NPR');

    $district = trim((string) ($listing->district ?? ''));
    $locationParts = array_filter([
        $city !== '' ? ucwords(strtolower($city)) : null,
        $district !== '' ? ucwords(strtolower($district)) : null,
        'Nepal',
    ]);
    $displayLocation = implode(', ', $locationParts);

    $galleryImages = collect($gallery ?? [])
        ->filter(fn ($value) => is_array($value) && is_array($value['gallery'] ?? null))
        ->values()
        ->all();
    $initialGalleryImage = $galleryImages[0]['gallery'] ?? null;
    $galleryCount = count($galleryImages);

    $seller = $listing->user;
    $sellerName = $seller ? trim((string) ($seller->getFullName() ?: $seller->getDisplayName())) : 'Marketplace Seller';
    $sellerAvatarUrl = $seller?->getAvatarUrl();
    $sellerProfileUrl = $seller?->getProfileUrl();
    $sellerMemberSince = $seller?->created_at?->format('M Y') ?? 'New member';
    $sellerListingCount = (int) ($seller?->listings_count ?? 0);
    $isOwnListing = auth()->check() && (int) auth()->id() === (int) $listing->user_id;
    $existingConversationId = $existingConversationId ?? ($detailConversation?->getKey());
@endphp
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <nav class="mb-4 flex flex-wrap items-center gap-1 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="font-medium text-gray-700 hover:text-gray-900">Home</a>
            @foreach(($breadcrumbCategories ?? collect()) as $crumb)
                <span class="text-gray-400">/</span>
                <a href="{{ route('listings.index', ['category' => $crumb->id]) }}" class="font-medium text-gray-700 hover:text-gray-900">{{ $crumb->name }}</a>
            @endforeach
            <span class="text-gray-400">/</span>
            <span class="max-w-full truncate text-gray-500">{{ $displayTitle }}</span>
        </nav>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-200 bg-gray-100" data-gallery>
                <div class="relative flex min-h-[18rem] items-center justify-center overflow-hidden sm:min-h-[24rem]">
                    @if($initialGalleryImage)
                        <picture class="block h-full w-full">
                            <source
                                data-gallery-source-mobile
                                media="(max-width: 767px)"
                                srcset="{{ $initialGalleryImage['mobile'] ?? ($initialGalleryImage['fallback'] ?? '') }}"
                            >
                            <source
                                data-gallery-source-desktop
                                media="(min-width: 768px)"
                                srcset="{{ $initialGalleryImage['desktop'] ?? ($initialGalleryImage['fallback'] ?? '') }}"
                            >
                            <img
                                src="{{ $initialGalleryImage['fallback'] ?? ($initialGalleryImage['desktop'] ?? ($initialGalleryImage['mobile'] ?? '')) }}"
                                alt="{{ $displayTitle }}"
                                class="h-72 w-full object-cover sm:h-96"
                                data-gallery-main
                            >
                        </picture>

                        @if($galleryCount > 1)
                            <div class="absolute bottom-3 right-3 rounded-full bg-black/60 px-3 py-1 text-xs font-semibold text-white">
                                <span data-gallery-current>1</span> / {{ $galleryCount }}
                            </div>
                        @endif
                    @else
                        <div class="flex h-72 w-full flex-col items-center justify-center gap-3 bg-gray-200 text-gray-400 sm:h-96">
                            <svg class="h-24 w-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-500">No photos uploaded yet.</p>
                        </div>
                    @endif
                </div>

                @if($galleryCount > 1)
                    <div class="flex gap-3 overflow-x-auto border-t border-gray-200 bg-white p-3" data-gallery-thumbs>
                        @foreach($galleryImages as $index => $image)
                            @php
                                $galleryImage = $image['gallery'] ?? null;
                                $thumbImage = $image['thumb'] ?? $galleryImage;
                            @endphp
                            <button
                                type="button"
                                class="h-20 w-24 shrink-0 overflow-hidden rounded-lg border-2 {{ $index === 0 ? 'border-red-600' : 'border-transparent hover:border-gray-300' }}"
                                data-gallery-thumb
                                data-gallery-index="{{ $index }}"
                                data-gallery-mobile-src="{{ $galleryImage['mobile'] ?? ($galleryImage['fallback'] ?? '') }}"
                                data-gallery-desktop-src="{{ $galleryImage['desktop'] ?? ($galleryImage['fallback'] ?? '') }}"
                                data-gallery-fallback-src="{{ $galleryImage['fallback'] ?? '' }}"
                                aria-label="Show photo {{ $index + 1 }}"
                            >
                                @include('listing::partials.responsive-image', [
                                    'image' => $thumbImage,
                                    'alt' => $displayTitle.' photo '.($index + 1),
                                    'class' => 'h-full w-full object-cover',
                                ])
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $displayTitle }}</h1>
                    <span class="text-3xl font-bold text-gray-900">
                        @if($hasPrice)
                            @if($priceValue > 0)
                                {{ number_format($priceValue, 0) }} {{ $displayCurrency }}
                            @else
                                Free
                            @endif
                        @else
                            Price on request
                        @endif
                    </span>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @auth
                    <form method="POST" action="{{ route('favorites.listings.toggle', $listing) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold transition {{ $isListingFavorited ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            {{ $isListingFavorited ? '♥ Saved' : '♡ Save listing' }}
                        </button>
                    </form>
                    @if($isOwnListing)
                    <a href="{{ route('listings.edit', $listing) }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Edit Listing
                    </a>
                    <button type="button" class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700" data-delete-listing-open>
                        Delete
                    </button>
                    @endif
                    @if($listing->user && (int) $listing->user->id !== (int) auth()->id())
                    <form method="POST" action="{{ route('favorites.sellers.toggle', $listing->user) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold transition {{ $isSellerFavorited ? 'bg-primary-soft text-primary' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            {{ $isSellerFavorited ? 'Seller saved' : 'Save seller' }}
                        </button>
                    </form>
                        @if($existingConversationId)
                        <x-button variant="outline" href="{{ route('panel.inbox.index', ['conversation' => $existingConversationId]) }}" class="rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200">Open chat</x-button>
                        @else
                        <form method="POST" action="{{ route('conversations.start', $listing) }}">
                            @csrf
                            <x-button variant="danger" type="submit">Message seller</x-button>
                        </form>
                        @endif
                    @endif
                    @else
                    <x-button variant="outline" href="{{ route('login') }}" class="rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">Log in to save</x-button>
                    @endauth
                </div>
                <dl class="mt-3 divide-y divide-gray-100 rounded-lg border border-gray-100">
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-gray-500">Location</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $displayLocation !== '' ? $displayLocation : 'Location not specified' }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-gray-500">Views</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ number_format((int) ($listing->view_count ?? 0)) }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-gray-500">Posted</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $listing->created_at?->diffForHumans() ?? 'recently' }}</dd>
                    </div>
                </dl>
                <div class="mt-4 border-t pt-4">
                    <h2 class="font-semibold text-lg mb-2">Description</h2>
                    <p class="text-gray-700">{{ $displayDescription }}</p>
                </div>
                <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            @if($sellerAvatarUrl)
                                <img src="{{ $sellerAvatarUrl }}" alt="{{ $sellerName }}" class="h-16 w-16 rounded-full border-2 border-gray-200 object-cover">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-gray-200 bg-slate-200 text-xl font-bold text-slate-500">
                                    {{ mb_strtoupper(mb_substr($sellerName, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Seller</p>
                                @if($sellerProfileUrl)
                                    <a href="{{ $sellerProfileUrl }}" class="text-lg font-bold text-slate-900 hover:text-primary">{{ $sellerName }}</a>
                                @else
                                    <p class="text-lg font-bold text-slate-900">{{ $sellerName }}</p>
                                @endif
                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-sm text-slate-500">
                                    <span>Member since {{ $sellerMemberSince }}</span>
                                    <span>{{ number_format($sellerListingCount) }} {{ $sellerListingCount === 1 ? 'listing' : 'listings' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 sm:justify-end">
                            @if($seller && ! $isOwnListing)
                                @auth
                                    @if($existingConversationId)
                                        <a href="{{ route('panel.inbox.index', ['conversation' => $existingConversationId]) }}" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-medium text-white transition-colors hover:bg-red-700">Open chat</a>
                                    @else
                                        <form method="POST" action="{{ route('conversations.start', $listing) }}" class="w-full">
                                            @csrf
                                            <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-medium text-white transition-colors hover:bg-red-700">Message seller</button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-medium text-white transition-colors hover:bg-red-700">Log in to contact</a>
                                @endauth
                            @endif
                            @if($listing->contact_phone)
                                <a href="tel:{{ preg_replace('/\s+/', '', (string) $listing->contact_phone) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">Call</a>
                            @endif
                            @if($listing->contact_email)
                                <a href="mailto:{{ $listing->contact_email }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50">Email</a>
                            @endif
                        </div>
                    </div>
                </div>
                @if(($listingVideos ?? collect())->isNotEmpty())
                <div class="mt-6 border-t pt-4">
                    <h2 class="font-semibold text-lg mb-3">Videos</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach($listingVideos as $video)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <video class="w-full rounded-lg bg-black" controls preload="metadata" src="{{ $video->playableUrl() }}"></video>
                            <p class="mt-2 text-sm font-semibold text-slate-800">{{ $video->titleLabel() }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @if(($presentableCustomFields ?? []) !== [])
                <div class="mt-6 border-t pt-4">
                    <h2 class="font-semibold text-lg mb-3">Listing details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($presentableCustomFields as $field)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            <p class="text-xs uppercase tracking-wide text-slate-500">{{ $field['label'] }}</p>
                            <p class="text-sm font-medium text-slate-800 mt-1">{{ $field['value'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="mt-6 rounded-lg border border-gray-200 bg-white p-4">
                    <h2 class="mb-3 text-lg font-semibold text-gray-900">Contact Seller</h2>
                    @if($listing->user)
                    <p class="text-sm text-gray-700"><span class="font-medium">Name:</span> {{ $listing->user->name }}</p>
                    @endif
                    @if($listing->contact_phone)
                    <p class="mt-1 text-sm text-gray-700"><span class="font-medium">Phone:</span> {{ $listing->contact_phone }}</p>
                    @endif
                    @if($listing->contact_email)
                    <p class="mt-1 text-sm text-gray-700"><span class="font-medium">Email:</span> {{ $listing->contact_email }}</p>
                    @endif
                    @if(!$listing->contact_phone && !$listing->contact_email)
                    <p class="text-sm text-gray-700">No contact details provided.</p>
                    @endif
                </div>
                <div class="mt-6">
                    <a href="{{ route('listings.index') }}" class="text-primary hover:underline">← Back to listings</a>
                </div>
            </div>
        </div>
    </div>
</div>

@if($isOwnListing)
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4 py-6" data-delete-listing-modal>
    <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-bold text-slate-950">Delete Listing</h2>
        <p class="mt-2 text-sm text-slate-600">Are you sure? This cannot be undone.</p>
        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-full border border-slate-300 px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-delete-listing-close>
                Cancel
            </button>
            <form method="POST" action="{{ route('listings.destroy', $listing) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-full bg-rose-600 px-5 text-sm font-semibold text-white transition hover:bg-rose-700 sm:w-auto">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endif

<script>
    (() => {
        document.querySelectorAll('[data-gallery]').forEach((galleryRoot) => {
            const mainImage = galleryRoot.querySelector('[data-gallery-main]');
            const mainMobileSource = galleryRoot.querySelector('[data-gallery-source-mobile]');
            const mainDesktopSource = galleryRoot.querySelector('[data-gallery-source-desktop]');
            const currentCounter = galleryRoot.querySelector('[data-gallery-current]');
            const thumbButtons = Array.from(galleryRoot.querySelectorAll('[data-gallery-thumb]'));

            if (! mainImage || thumbButtons.length === 0) {
                return;
            }

            const activate = (index) => {
                const thumb = thumbButtons[index];

                if (! thumb) {
                    return;
                }

                const mobileSrc = thumb.dataset.galleryMobileSrc || '';
                const desktopSrc = thumb.dataset.galleryDesktopSrc || '';
                const fallbackSrc = thumb.dataset.galleryFallbackSrc || desktopSrc || mobileSrc;

                if (mainMobileSource && mobileSrc) {
                    mainMobileSource.srcset = mobileSrc;
                }

                if (mainDesktopSource && desktopSrc) {
                    mainDesktopSource.srcset = desktopSrc;
                }

                if (fallbackSrc) {
                    mainImage.src = fallbackSrc;
                }

                if (currentCounter) {
                    currentCounter.textContent = String(index + 1);
                }

                thumbButtons.forEach((button, buttonIndex) => {
                    const isActive = buttonIndex === index;

                    button.classList.toggle('border-red-600', isActive);
                    button.classList.toggle('border-transparent', ! isActive);
                    button.setAttribute('aria-current', isActive ? 'true' : 'false');
                });
            };

            thumbButtons.forEach((button, index) => {
                button.addEventListener('click', () => activate(index));
            });
        });

        const deleteModal = document.querySelector('[data-delete-listing-modal]');

        if (! deleteModal) {
            return;
        }

        const openDeleteModal = () => {
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
        };

        const closeDeleteModal = () => {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
        };

        document.querySelectorAll('[data-delete-listing-open]').forEach((button) => {
            button.addEventListener('click', openDeleteModal);
        });

        document.querySelectorAll('[data-delete-listing-close]').forEach((button) => {
            button.addEventListener('click', closeDeleteModal);
        });

        deleteModal.addEventListener('click', (event) => {
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });
    })();
</script>
@endsection
