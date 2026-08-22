@php
    $activeMenu = $activeMenu ?? '';
    $activeFavoritesTab = $activeFavoritesTab ?? '';
    $primaryItems = [
        [
            'label' => 'Sell',
            'route' => route('panel.listings.create'),
            'key' => 'create',
        ],
        [
            'label' => 'My Listings',
            'route' => route('panel.listings.index'),
            'key' => 'listings',
        ],
        [
            'label' => 'Videos',
            'route' => route('panel.videos.index'),
            'key' => 'videos',
        ],
        [
            'label' => 'Inbox',
            'route' => route('panel.inbox.index'),
            'key' => 'inbox',
        ],
        [
            'label' => 'My Profile',
            'route' => route('panel.profile.edit'),
            'key' => 'profile',
        ],
    ];
    $favoriteItems = [
        [
            'label' => 'Saved Listings',
            'route' => route('favorites.index', ['tab' => 'listings']),
            'key' => 'listings',
        ],
        [
            'label' => 'Saved Searches',
            'route' => route('favorites.index', ['tab' => 'searches']),
            'key' => 'searches',
        ],
        [
            'label' => 'Saved Sellers',
            'route' => route('favorites.index', ['tab' => 'sellers']),
            'key' => 'sellers',
        ],
    ];
    $favoritesActive = $activeMenu === 'favorites' || $activeFavoritesTab !== '';
@endphp

<aside class="panel-side-nav border border-[#dddfe2] bg-white p-2 shadow-sm">
    <nav class="space-y-1">
        @foreach ($primaryItems as $item)
            <a
                href="{{ $item['route'] }}"
                data-level="primary"
                @class([
                    'flex items-center gap-2 rounded-md px-3 py-2.5 text-sm font-semibold transition',
                    'bg-[#e7f3ff] text-[#1877f2]' => $activeMenu === $item['key'],
                    'text-[#050505] hover:bg-[#f0f2f5]' => $activeMenu !== $item['key'],
                ])
            >
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        <div class="mt-2 border-t border-[#dddfe2] pt-2">
            <a
                href="{{ route('favorites.index', ['tab' => 'listings']) }}"
                data-level="primary"
                @class([
                    'flex items-center gap-2 rounded-md px-3 py-2.5 text-sm font-semibold transition',
                    'bg-[#e7f3ff] text-[#1877f2]' => $favoritesActive,
                    'text-[#050505] hover:bg-[#f0f2f5]' => ! $favoritesActive,
                ])
            >
                <span>Favorites</span>
            </a>

            <div class="mt-1 space-y-0.5 pl-2">
                @foreach ($favoriteItems as $item)
                    <a
                        href="{{ $item['route'] }}"
                        data-level="secondary"
                        @class([
                            'flex items-center justify-between gap-2 rounded-md px-3 py-2 text-sm transition',
                            'bg-[#f0f2f5] text-[#1877f2] font-semibold' => $activeFavoritesTab === $item['key'],
                            'text-[#65676b] hover:bg-[#f0f2f5] hover:text-[#050505]' => $activeFavoritesTab !== $item['key'],
                        ])
                    >
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </nav>
</aside>
