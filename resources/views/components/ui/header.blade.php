@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'meta' => null,
])

<div {{ $attributes->class('page-header') }}>
    <div>
        @if ($eyebrow)
            <p class="page-header__eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="page-header__title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-header__subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if (trim($slot) !== '')
        <div class="page-header__aside">{{ $slot }}</div>
    @elseif ($meta)
        <div class="page-header__meta">{{ $meta }}</div>
    @endif
</div>
