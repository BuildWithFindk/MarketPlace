<img
    src="{{ asset('images/logo.png') }}"
    alt="{{ config('app.name', 'HajurKoAfno') }}"
    {{ $attributes->except(['src', 'alt'])->merge(['class' => 'object-contain']) }}
>
