@php
    $banners = \Modules\Banner\App\Models\Banner
        ::liveForHomepage()
        ->get();
@endphp

@if($banners->isNotEmpty())
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 
            gap-4 my-8">
    @foreach($banners as $banner)
        <a href="{{ route('banners.redirect', $banner) }}"
           class="block rounded-lg overflow-hidden 
                  aspect-video border border-gray-200 
                  bg-gray-50 hover:border-gray-300 
                  transition-colors">
            <img src="{{ $banner->getImageUrl() }}"
                 alt="{{ $banner->title }}"
                 class="w-full h-full object-cover">
        </a>
    @endforeach
</div>
@endif
