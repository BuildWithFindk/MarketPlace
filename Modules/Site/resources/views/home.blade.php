@extends('app::layouts.app')
@section('content')
@php
    $menuCategories = $categories->take(8);
    $demoEnabled = (bool) config('demo.enabled');
    $prepareDemoRoute = $demoEnabled ? route('demo.prepare') : null;
    $prepareDemoRedirect = request()->fullUrl();
    $hasDemoSession = (bool) session('is_demo_session') || filled(session('demo_uuid'));
    $demoLandingMode = $demoEnabled && !auth()->check() && !$hasDemoSession;
    $demoTurnstileProtectionEnabled = (bool) config('demo.turnstile.enabled', false);
    $demoTurnstileSiteKey = trim((string) config('demo.turnstile.site_key', ''));
    $prepareDemoTurnstileRequired = $demoLandingMode && $demoTurnstileProtectionEnabled;
    $prepareDemoTurnstileRenderable = $prepareDemoTurnstileRequired && $demoTurnstileSiteKey !== '';
    $demoTtlMinutes = (int) config('demo.ttl_minutes', 360);
    $demoTtlHours = intdiv($demoTtlMinutes, 60);
    $demoTtlRemainderMinutes = $demoTtlMinutes % 60;
    $demoTtlLabelParts = [];

    if ($demoTtlHours > 0) {
        $demoTtlLabelParts[] = $demoTtlHours.' '.\Illuminate\Support\Str::plural('hour', $demoTtlHours);
    }

    if ($demoTtlRemainderMinutes > 0) {
        $demoTtlLabelParts[] = $demoTtlRemainderMinutes.' '.\Illuminate\Support\Str::plural('minute', $demoTtlRemainderMinutes);
    }

    $demoTtlLabel = $demoTtlLabelParts !== [] ? implode(' ', $demoTtlLabelParts) : '0 minutes';
    $homeSlides = collect($generalSettings['home_slides'] ?? [])
        ->filter(fn ($slide): bool => is_array($slide))
        ->map(function (array $slide): array {
            $badge = trim((string) ($slide['badge'] ?? ''));
            $title = trim((string) ($slide['title'] ?? ''));
            $subtitle = trim((string) ($slide['subtitle'] ?? ''));
            $primaryButtonText = trim((string) ($slide['primary_button_text'] ?? ''));
            $secondaryButtonText = trim((string) ($slide['secondary_button_text'] ?? ''));
            $imagePath = trim((string) ($slide['image_path'] ?? ''));

            return [
                'badge' => $badge !== '' ? $badge : 'HajurKoAfno Marketplace',
                'title' => $title !== '' ? $title : 'Sell faster with a cleaner local marketplace.',
                'subtitle' => $subtitle !== '' ? $subtitle : 'Buy and sell everything in your area',
                'primary_button_text' => $primaryButtonText !== '' ? $primaryButtonText : 'Browse Listings',
                'secondary_button_text' => $secondaryButtonText !== '' ? $secondaryButtonText : 'Post Listing',
                'image_url' => \Modules\Site\App\Support\LocalMedia::url($imagePath),
            ];
        })
        ->values();

    if ($homeSlides->isEmpty()) {
        $homeSlides = collect([
            [
                'badge' => 'HajurKoAfno Marketplace',
                'title' => 'Sell faster with a cleaner local marketplace.',
                'subtitle' => 'Buy and sell everything in your area',
                'primary_button_text' => 'Browse Listings',
                'secondary_button_text' => 'Post Listing',
                'image_url' => null,
            ],
        ]);
    }
@endphp

@if($demoLandingMode && $prepareDemoRoute)
<div class="min-h-screen flex items-center justify-center px-5 py-10">
    <form method="POST" action="{{ $prepareDemoRoute }}" data-demo-prepare-form data-turnstile-required="{{ $prepareDemoTurnstileRequired ? '1' : '0' }}" class="w-full max-w-xl rounded-md border border-[#dddfe2] bg-white p-6 md:p-8 shadow-sm">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ $prepareDemoRedirect }}">
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-[#050505]">Prepare Demo</h1>
        <p class="mt-4 text-sm md:text-base leading-7 text-[#65676b]">
            Launch a private seeded marketplace for this browser. Listings, favorites, inbox data, and admin access are prepared automatically.
        </p>
        <p class="mt-3 text-sm text-[#65676b]">
            This demo is deleted automatically after {{ $demoTtlLabel }}.
        </p>
        @if($prepareDemoTurnstileRenderable)
        <div class="mt-6 space-y-2">
            <div class="cf-turnstile" data-sitekey="{{ $demoTurnstileSiteKey }}"></div>
            <p class="text-xs text-slate-500">Complete the security check before starting your private demo.</p>
        </div>
        @elseif($prepareDemoTurnstileRequired)
        <p class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium leading-6 text-amber-700">
            Security check is enabled but the widget is not configured. Contact the administrator.
        </p>
        @endif
        <p data-demo-prepare-status data-turnstile-message="Please complete the security verification first." data-loading-message="Preparing your private demo. This can take longer because a dedicated seeded environment is being provisioned for your browser." aria-live="polite" class="mt-4 hidden rounded-md border border-primary-soft-border bg-primary-soft px-4 py-3 text-sm font-medium leading-6 text-primary">
            Preparing your private demo. This can take longer because a dedicated seeded environment is being provisioned for your browser.
        </p>
        <x-button variant="primary" type="submit" data-demo-prepare-button :disabled="$prepareDemoTurnstileRequired" class="mt-6 min-h-11 w-full items-center justify-center px-6 py-3 text-base font-semibold disabled:cursor-not-allowed">
            <span data-demo-prepare-idle>Prepare Demo</span>
            <span data-demo-prepare-loading class="hidden items-center gap-2">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
                </svg>
                Preparing Demo...
            </span>
        </x-button>
    </form>
</div>
@else
<section data-home-hero>
    <div class="mx-auto max-w-7xl px-4 py-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <aside class="hidden lg:block lg:col-span-1">
                <nav class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-900">Categories</h2>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach($menuCategories as $category)
                        @php
                            $categoryIconUrl = $category->iconUrl();
                            $fallbackLabel = strtoupper(\Illuminate\Support\Str::substr($category->name, 0, 1));
                        @endphp
                        <li>
                            <a
                                href="{{ route('listings.index', ['category' => $category->id]) }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-red-600"
                            >
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100">
                                    @if($categoryIconUrl)
                                        <img src="{{ $categoryIconUrl }}" alt="" class="h-5 w-5 object-contain">
                                    @else
                                        <span class="text-xs font-semibold text-gray-600">{{ $fallbackLabel }}</span>
                                    @endif
                                </span>
                                <span class="font-medium">{{ $category->name }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('categories.index') }}" class="block border-t border-gray-100 px-4 py-3 text-xs font-semibold text-red-600 transition-colors hover:bg-red-50">
                        See all categories
                    </a>
                </nav>
            </aside>

            <div class="lg:col-span-3 xl:col-span-2">
                <div
                    data-home-slider
                    data-home-hero-copy
                    class="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-sm"
                >
                    <div class="relative min-h-[280px] md:min-h-[320px]">
                        @foreach($homeSlides as $index => $slide)
                        <div
                            data-home-slide
                            @class(['absolute inset-0 transition-opacity duration-300', 'hidden' => $index !== 0])
                            aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                        >
                            <div class="flex h-full flex-col justify-between p-6 md:p-8">
                                <div class="grid flex-1 grid-cols-1 items-center gap-6 md:grid-cols-5">
                                    <div class="space-y-4 md:col-span-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $slide['badge'] }}</p>
                                        <h1 class="max-w-lg text-2xl font-bold leading-tight text-white md:text-3xl lg:text-4xl">{{ $slide['title'] }}</h1>
                                        <p class="max-w-md text-sm leading-relaxed text-slate-300 md:text-base">{{ $slide['subtitle'] }}</p>
                                        <div class="flex flex-wrap gap-3 pt-1">
                                            <a href="{{ route('listings.index') }}" class="inline-flex items-center rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-slate-900 transition-colors hover:bg-slate-100">
                                                {{ $slide['primary_button_text'] }}
                                            </a>
                                            @auth
                                            <a href="{{ route('panel.listings.create') }}" class="inline-flex items-center rounded-lg border border-white/25 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-white/15">
                                                {{ $slide['secondary_button_text'] }}
                                            </a>
                                            @else
                                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg border border-white/25 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-white/15">
                                                {{ $slide['secondary_button_text'] }}
                                            </a>
                                            @endauth
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-center md:col-span-2">
                                        <div
                                            data-home-slide-visual
                                            @if($index === 0) data-home-hero-visual @endif
                                            @class(['relative w-full max-w-[220px]', 'hidden' => $index !== 0])
                                            aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                                        >
                                            @if($slide['image_url'])
                                            <img
                                                src="{{ $slide['image_url'] }}"
                                                alt=""
                                                class="mx-auto h-44 w-auto object-contain md:h-52"
                                            >
                                            @else
                                            <div class="relative mx-auto flex h-44 w-44 items-center justify-center rounded-2xl border border-white/10 bg-gradient-to-br from-slate-700/80 to-slate-800/80 md:h-52 md:w-52">
                                                <div class="absolute inset-3 rounded-xl border border-white/10 bg-slate-900/60"></div>
                                                <svg class="relative h-20 w-20 text-slate-300 md:h-24 md:w-24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 7h4l2-3h4l2 3h4v11H4V7z"/>
                                                    <circle cx="12" cy="13" r="3.5" stroke-width="1.2"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9 7l1.5-2h3L15 7"/>
                                                </svg>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if($homeSlides->count() > 1)
                    <div class="relative flex items-center gap-2 border-t border-white/10 px-6 py-4 md:px-8">
                        <button
                            type="button"
                            data-home-slide-prev
                            class="grid h-8 w-8 place-items-center rounded-lg border border-white/20 text-white transition-colors hover:border-white/40 hover:bg-white/10"
                            aria-label="Previous slide"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        @foreach($homeSlides as $index => $slide)
                        <button
                            type="button"
                            data-home-slide-dot="{{ $index }}"
                            @class([
                                'h-2 rounded-full transition-all',
                                'w-7 bg-white' => $index === 0,
                                'w-2.5 bg-white/40 hover:bg-white/60' => $index !== 0,
                            ])
                            aria-label="Slide {{ $index + 1 }}"
                        ></button>
                        @endforeach
                        <button
                            type="button"
                            data-home-slide-next
                            class="grid h-8 w-8 place-items-center rounded-lg border border-white/20 text-white transition-colors hover:border-white/40 hover:bg-white/10"
                            aria-label="Next slide"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <aside class="hidden flex-col gap-4 xl:col-span-1 xl:flex">
                <a
                    href="{{ route('listings.index') }}"
                    class="group flex flex-1 flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-md"
                >
                    <div>
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-amber-700">Limited time</span>
                        <h3 class="mt-3 text-lg font-bold text-gray-900">Flash Sales</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-gray-600">Discover featured deals across electronics, fashion, and more on HajurKoAfno.</p>
                    </div>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-amber-600 transition-colors group-hover:text-amber-700">
                        Shop deals
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>

                <a
                    href="{{ auth()->check() ? route('panel.listings.create') : route('register') }}"
                    class="group flex flex-1 flex-col justify-between rounded-xl border border-gray-200 bg-red-600 p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:border-red-700 hover:shadow-md"
                >
                    <div>
                        <span class="inline-flex items-center rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-red-700">Start selling</span>
                        <h3 class="mt-3 text-lg font-bold text-white">Sell on HajurKoAfno</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-red-50">List your items in minutes and reach buyers across Nepal.</p>
                    </div>
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-white transition-colors group-hover:text-red-50">
                        Post a listing
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            </aside>
        </div>
    </div>
</section>

<div class="mp-page">
    @include('banner::grid')

    <section class="mp-section mt-10" data-home-section>
        <div class="mp-section-head">
            <h2 class="mp-section-title text-lg font-semibold text-gray-900">Browse by category</h2>
            <a href="{{ route('categories.index') }}" class="mp-section-link text-sm font-medium text-red-600 hover:text-red-700">See all</a>
        </div>
        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6 lg:grid-cols-8">
            @foreach($menuCategories as $category)
            @php
                $categoryIconUrl = $category->iconUrl();
                $fallbackLabel = strtoupper(\Illuminate\Support\Str::substr($category->name, 0, 1));
            @endphp
            <a
                href="{{ route('listings.index', ['category' => $category->id]) }}"
                class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-200 bg-white p-2.5 text-center transition-[border-color,box-shadow] hover:border-gray-300 hover:shadow-sm"
                data-home-category-card
            >
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100">
                    @if($categoryIconUrl)
                        <img src="{{ $categoryIconUrl }}" alt="{{ $category->name }}" class="h-7 w-7 object-contain">
                    @else
                        <span class="text-sm font-semibold text-gray-600">{{ $fallbackLabel }}</span>
                    @endif
                </span>
                <span class="text-[11px] font-semibold leading-tight text-gray-900">{{ $category->name }}</span>
            </a>
            @endforeach
        </div>
    </section>

    <section class="mp-section mt-10" data-home-section>
        <div class="mp-section-head">
            <h2 class="mp-section-title text-lg font-semibold text-gray-900">Today's picks</h2>
            <a href="{{ route('listings.index') }}" class="mp-section-link text-sm font-medium text-red-600 hover:text-red-700">See all listings</a>
        </div>
        @if($recentListings->isNotEmpty())
        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach($recentListings as $listing)
            @include('listing::partials.card', ['listing' => $listing])
            @endforeach
        </div>
        @else
        <div class="rounded-lg border border-dashed border-gray-200 bg-white px-6 py-16 text-center">
            <p class="text-base font-medium text-gray-900">No listings yet — be the first to post!</p>
            <a
                href="{{ route('listings.create') }}"
                class="mt-4 inline-flex min-h-10 items-center justify-center rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700"
            >
                Post a listing
            </a>
        </div>
        @endif
    </section>

    <section class="mp-cta-bar mt-10" data-home-section>
        <div>
            <p class="mp-cta-title">{{ __('messages.sell_something') }}</p>
            <p class="mp-cta-text">Create a free listing and reach buyers in your area.</p>
        </div>
        @auth
        <a href="{{ route('panel.listings.create') }}" class="btn-primary inline-flex min-h-9 items-center px-4 text-sm font-semibold">
            Post listing
        </a>
        @else
        <a href="{{ route('register') }}" class="btn-primary inline-flex min-h-9 items-center px-4 text-sm font-semibold">
            Start free
        </a>
        @endauth
    </section>
</div>
@endif
@if($prepareDemoTurnstileRenderable)
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
@endsection
