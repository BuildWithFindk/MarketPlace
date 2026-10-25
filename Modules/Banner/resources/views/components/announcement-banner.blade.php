@props([
    'position' => 'top',
])

@php
    use Modules\Banner\App\Models\Announcement;

    $announcement = Announcement::currentFor($position);
@endphp

@if ($announcement)
    <div
        id="announcement-banner-{{ $announcement->id }}"
        class="announcement-banner"
        data-announcement-id="{{ $announcement->id }}"
        style="
            @if ($announcement->type === 'text')
                --announcement-background: {{ $announcement->background_color }};
                --announcement-text: {{ $announcement->text_color }};
            @elseif ($announcement->type === 'background_image')
                --announcement-text: {{ $announcement->text_color }};
                background-image: url('{{ $announcement->image_url }}');
            @endif
        "
    >

        @if ($announcement->type === 'text')
            <div class="announcement-banner__content">
                {{ $announcement->message }}
            </div>
        @elseif ($announcement->type === 'background_image')
            <div class="announcement-banner__content">
                {{ $announcement->message }}
            </div>
        @elseif ($announcement->type === 'banner_image')
            <img
                src="{{ $announcement->image_url }}"
                alt=""
                class="announcement-banner__image"
            >
        @endif

        <button
            type="button"
            class="announcement-banner__close"
            aria-label="Close announcement"
        >
            ×
        </button>

    </div>
@endif