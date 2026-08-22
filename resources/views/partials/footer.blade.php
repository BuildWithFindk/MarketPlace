<div class="footer-social">
    <div class="flex items-center gap-3">
        @php
            $socials = [
                'instagram' => ['url' => config('social.instagram'), 'icon' => asset('assets/icons/social/instagram.svg'), 'label' => 'Instagram'],
                'facebook' => ['url' => config('social.facebook'), 'icon' => asset('assets/icons/social/facebook.svg'), 'label' => 'Facebook'],
                'youtube' => ['url' => config('social.youtube'), 'icon' => asset('assets/icons/social/youtube.svg'), 'label' => 'YouTube'],
                'tiktok' => ['url' => config('social.tiktok'), 'icon' => asset('assets/icons/social/tiktok.svg'), 'label' => 'TikTok'],
                'discord' => ['url' => config('social.discord'), 'icon' => asset('assets/icons/social/discord.svg'), 'label' => 'Discord'],
                'linkedin' => ['url' => config('social.linkedin'), 'icon' => asset('assets/icons/social/linkedin.svg'), 'label' => 'LinkedIn'],
            ];
        @endphp

        @foreach($socials as $key => $item)
            @if(!empty($item['url']))
                <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $item['label'] }}" class="inline-flex items-center justify-center w-8 h-8 text-slate-700 hover:opacity-80 transition">
                    {{-- Inline the SVG for better styling; load via asset() and include raw SVG content --}}
                    @php
                        $svgPath = public_path('assets/icons/social/' . $key . '.svg');
                        $svg = file_exists($svgPath) ? file_get_contents($svgPath) : null;
                    @endphp
                    @if($svg)
                        {!! str_replace('<svg', '<svg class="w-5 h-5"', $svg) !!}
                    @else
                        <img src="{{ $item['icon'] }}" alt="{{ $item['label'] }}" class="w-5 h-5" />
                    @endif
                </a>
            @endif
        @endforeach
    </div>
</div>
