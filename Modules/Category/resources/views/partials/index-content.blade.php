@php
    $categoryCount = $categories->count();
@endphp

<div class="mp-page">
    <section class="mp-section">
        <div class="mp-section-head">
            <div>
                <h1 class="mp-section-title">All categories</h1>
                <p class="text-sm text-[#65676b] mt-1">Browse marketplace categories and find what you need.</p>
            </div>
            <p class="text-sm font-medium text-[#65676b]">{{ number_format($categoryCount, 0, '.', ',') }} categories</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($categories as $category)
            @php
                $childNames = $category->children
                    ->take(3)
                    ->pluck('name')
                    ->filter()
                    ->implode(' · ');
                $extraChildCount = max($category->children->count() - 3, 0);
                $iconUrl = $category->iconUrl();
                $iconLabel = strtoupper(\Illuminate\Support\Str::substr($category->name, 0, 1));
            @endphp
            <a
                href="{{ route('listings.index', ['category' => $category->id]) }}"
                class="category-index-card group flex h-full flex-col"
            >
                <div class="flex items-start justify-between gap-3">
                    <span class="category-index-icon">
                        @if($iconUrl)
                        <img src="{{ $iconUrl }}" alt="{{ $category->name }}" class="h-8 w-8 object-contain">
                        @else
                        <span class="text-xl font-semibold">{{ $iconLabel }}</span>
                        @endif
                    </span>
                    <span class="mp-badge">
                        {{ number_format($category->children->count(), 0, '.', ',') }} sub
                    </span>
                </div>

                <div class="mt-4 space-y-2">
                    <h2 class="text-lg font-bold text-[#050505] group-hover:text-[#1877f2] transition">
                        {{ $category->name }}
                    </h2>
                    <p class="text-sm leading-5 text-[#65676b]">
                        {{ $childNames !== '' ? $childNames : 'Browse listings in this category.' }}
                        @if($extraChildCount > 0)
                        <span class="font-semibold text-[#050505]">+{{ $extraChildCount }} more</span>
                        @endif
                    </p>
                </div>

                <div class="mt-auto pt-4">
                    <span class="text-sm font-semibold text-[#1877f2]">Explore category →</span>
                </div>
            </a>
            @endforeach
        </div>
    </section>
</div>
