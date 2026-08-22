@extends('app::layouts.app')

@section('title', trim($user->getFullName()) !== '' ? $user->getFullName() : $user->getDisplayName())

@section('content')
@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $displayName = trim($user->getFullName()) !== '' ? $user->getFullName() : $user->getDisplayName();
    $coverUrl = $user->getCoverUrl();
    $avatarUrl = $user->getAvatarUrl();
    $locationLabel = collect([
        trim((string) $user->city),
        trim((string) $user->district),
        'Nepal',
    ])->filter()->implode(', ');
    $rawPhone = trim((string) $user->phone);
    $phoneLabel = $rawPhone !== ''
        ? (\Illuminate\Support\Str::startsWith($rawPhone, '+') ? $rawPhone : '+977 '.ltrim($rawPhone, '0'))
        : null;
    $memberSince = $user->created_at?->format('M Y') ?? 'New member';
    $profileListings = collect($listings ?? $user->listings ?? []);
    $activeListings = $profileListings
        ->filter(fn ($listing): bool => method_exists($listing, 'statusValue') ? $listing->statusValue() === 'active' : (string) $listing->status === 'active')
        ->values();
    $soldListings = $profileListings
        ->filter(fn ($listing): bool => method_exists($listing, 'statusValue') ? $listing->statusValue() === 'sold' : (string) $listing->status === 'sold')
        ->values();
    $activeTab = in_array(request('tab', 'active'), ['active', 'sold', 'reviews'], true) ? request('tab', 'active') : 'active';
    $listingTotal = (int) ($listingCount ?? $listingsCount ?? $user->listings_count ?? $profileListings->count());
    $savedTotal = (int) ($savedCount ?? $savedListingsCount ?? $user->saved_listings_count ?? 0);
    $socialLinks = collect($socialLinks ?? $user->socialLinks ?? []);
    $socialLinkMap = $socialLinks->keyBy('platform');
    $socialPlatforms = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
    ];
    $tabBaseRoute = static fn (string $tab): string => route('profile.show', [
        'username' => $user->username,
        'tab' => $tab,
    ]);
    $fieldClass = 'h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 shadow-sm transition focus:border-[#d92200] focus:outline-none focus:ring-2 focus:ring-[#ffd9d5]';
    $textareaClass = 'min-h-24 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-[#d92200] focus:outline-none focus:ring-2 focus:ring-[#ffd9d5]';
@endphp

<div class="mx-auto max-w-[1320px] px-4 py-6 md:py-8">
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="relative h-[280px] overflow-hidden bg-gray-200">
            <img
                data-profile-cover-image
                @if ($coverUrl) src="{{ $coverUrl }}" @endif
                alt="{{ $displayName }} cover photo"
                class="h-full w-full object-cover {{ $coverUrl ? '' : 'hidden' }}"
            >

            @if ($isOwner)
                <form method="POST" action="{{ route('profile.cover.upload') }}" enctype="multipart/form-data" class="absolute bottom-4 right-4" data-profile-upload-form data-profile-upload-kind="cover">
                    @csrf
                    <input id="profile-cover-upload" type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="hidden" data-profile-upload-input>
                    <label for="profile-cover-upload" class="inline-flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-800 transition hover:border-gray-300 hover:bg-gray-50 hover:text-red-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 8h3l1.6-2.4A1.5 1.5 0 0 1 9.85 5h4.3a1.5 1.5 0 0 1 1.25.6L17 8h3a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/>
                            <circle cx="12" cy="13.5" r="3.2" stroke-width="1.8"/>
                        </svg>
                        <span>Edit Cover</span>
                    </label>
                </form>
            @endif
        </div>

        <div class="relative px-4 pb-6 sm:px-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    @if ($isOwner)
                        <form method="POST" action="{{ route('profile.avatar.upload') }}" enctype="multipart/form-data" class="-mt-16 h-[120px] w-[120px] shrink-0" data-profile-upload-form data-profile-upload-kind="avatar">
                            @csrf
                            <input id="profile-avatar-upload" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden" data-profile-upload-input>
                            <label for="profile-avatar-upload" class="group relative block h-[120px] w-[120px] cursor-pointer overflow-hidden rounded-full border-4 border-white bg-gray-100 ring-1 ring-gray-200">
                                <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="h-full w-full object-cover" data-profile-avatar-image>
                                <span class="absolute inset-0 grid place-items-center bg-black/45 text-white opacity-0 transition group-hover:opacity-100">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 8h3l1.6-2.4A1.5 1.5 0 0 1 9.85 5h4.3a1.5 1.5 0 0 1 1.25.6L17 8h3a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/>
                                        <circle cx="12" cy="13.5" r="3.2" stroke-width="1.8"/>
                                    </svg>
                                </span>
                            </label>
                        </form>
                    @else
                        <div class="-mt-16 h-[120px] w-[120px] shrink-0 overflow-hidden rounded-full border-4 border-white bg-gray-100 ring-1 ring-gray-200">
                            <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="h-full w-full object-cover">
                        </div>
                    @endif

                    <div class="min-w-0 sm:pb-2">
                        <h1 class="break-words text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">{{ $displayName }}</h1>

                        <div class="mt-3 flex flex-col gap-2 text-sm font-medium text-slate-600 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-4">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-[#d92200]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"/>
                                    <circle cx="12" cy="10" r="2.3" stroke-width="1.8"/>
                                </svg>
                                {{ $locationLabel }}
                            </span>

                            @if ($phoneLabel)
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-[#d92200]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.6 3.8 9 3.25l2.1 4.9-1.55 1.1a11 11 0 0 0 5.2 5.2l1.1-1.55 4.9 2.1-.55 2.4a2 2 0 0 1-2 1.6A15.2 15.2 0 0 1 5 5.8a2 2 0 0 1 1.6-2z"/>
                                    </svg>
                                    {{ $phoneLabel }}
                                </span>
                            @endif
                        </div>

                        @if ($user->bio)
                            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">{{ $user->bio }}</p>
                        @endif

                        @if ($socialLinks->isNotEmpty())
                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                @foreach ($socialLinks as $socialLink)
                                    <a href="{{ $socialLink->getProfileUrl() }}" target="_blank" rel="noopener noreferrer" class="grid h-9 w-9 place-items-center rounded-full border border-gray-200 bg-white transition hover:border-gray-300 hover:bg-gray-50" aria-label="{{ ucfirst($socialLink->platform) }}">
                                        {!! $socialLink->getPlatformIcon() !!}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @if ($isOwner)
                    <button type="button" onclick="document.getElementById('profile-edit-modal').showModal()" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.86 4.49 2.65 2.65M4 20h4.4L19.1 9.3a1.87 1.87 0 0 0 0-2.64L17.34 4.9a1.87 1.87 0 0 0-2.64 0L4 15.6V20z"/>
                        </svg>
                        <span>Edit Profile</span>
                    </button>
                @endif
            </div>
        </div>
    </section>

    <section class="mt-4 grid overflow-hidden rounded-lg border border-gray-200 bg-white sm:grid-cols-3">
        <div class="px-5 py-4 text-center">
            <p class="text-sm font-semibold text-slate-500">Listings({{ number_format($listingTotal) }})</p>
            <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($listingTotal) }}</p>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 text-center sm:border-l sm:border-t-0">
            <p class="text-sm font-semibold text-slate-500">Saved({{ number_format($savedTotal) }})</p>
            <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($savedTotal) }}</p>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 text-center sm:border-l sm:border-t-0">
            <p class="text-sm font-semibold text-slate-500">Since {{ $user->created_at?->format('Y') ?? now()->format('Y') }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-950">{{ $memberSince }}</p>
        </div>
    </section>

    <section class="mt-4 rounded-lg border border-gray-200 bg-white">
        <nav class="flex overflow-x-auto border-b border-gray-200 px-3" aria-label="Profile sections">
            <a href="{{ $tabBaseRoute('active') }}" class="whitespace-nowrap border-b-2 px-4 py-4 text-sm font-semibold transition {{ $activeTab === 'active' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900' }}">
                Active Listings
            </a>
            <a href="{{ $tabBaseRoute('sold') }}" class="whitespace-nowrap border-b-2 px-4 py-4 text-sm font-semibold transition {{ $activeTab === 'sold' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900' }}">
                Sold
            </a>
            <a href="{{ $tabBaseRoute('reviews') }}" class="whitespace-nowrap border-b-2 px-4 py-4 text-sm font-semibold transition {{ $activeTab === 'reviews' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900' }}">
                Reviews
            </a>
        </nav>

        <div class="p-4 sm:p-5">
            @if (in_array($activeTab, ['active', 'sold'], true))
                @php
                    $visibleListings = $activeTab === 'active' ? $activeListings : $soldListings;
                    $emptyMessage = $activeTab === 'active' ? 'No active listings yet' : 'No sold items yet';
                @endphp

                @if ($visibleListings->isEmpty())
                    <div class="grid min-h-48 place-items-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm font-medium text-slate-500">
                        {{ $emptyMessage }}
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        @foreach ($visibleListings as $listing)
                            @include('listing::partials.card', ['listing' => $listing])
                        @endforeach
                    </div>
                @endif
            @else
                <div class="grid min-h-48 place-items-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm font-medium text-slate-500">
                    No reviews yet
                </div>
            @endif
        </div>
    </section>
</div>

@if ($isOwner)
    @include('user::profile.edit-modal')
@endif
@endsection
