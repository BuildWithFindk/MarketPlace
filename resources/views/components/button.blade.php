@props(['href' => null, 'variant' => 'primary', 'type' => 'button'])

@php
switch($variant) {
    case 'primary':
        $classBase = 'inline-flex items-center h-9 px-4 btn-primary text-white text-sm font-semibold rounded-md transition';
        break;
    case 'secondary':
        $classBase = 'inline-flex items-center h-9 px-4 btn-secondary text-sm font-semibold rounded-md transition';
        break;
    case 'danger':
        $classBase = 'inline-flex items-center h-9 px-4 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700 transition';
        break;
    case 'outline':
        $classBase = 'inline-flex items-center h-9 px-4 border border-[#dddfe2] text-sm font-semibold rounded-md transition hover:bg-[#f0f2f5]';
        break;
    default:
        $classBase = 'inline-flex items-center h-9 px-4 text-sm font-semibold rounded-md transition';
}
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classBase]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classBase]) }}>
        {{ $slot }}
    </button>
@endif
