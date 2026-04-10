@props([
    'title',
    'subtitle' => null,
    'url' => '#',
    'symbol' => 'ITEM',
    'cta' => 'View Details',
])

@php
$symbolIconMap = [
    'EDU' => 'icon_education.png',
    'DRL' => 'icon_drill_simulation.png',
    'RES' => 'icon_my_result.png',
    'PRG' => 'icon_progress_drill.png',
    'DSH' => 'icon_dashboard.png',
];
$iconFile = $symbolIconMap[strtoupper($symbol)] ?? null;
@endphp

<article {{ $attributes->class('menu-card panel-card') }}>
    <h3 class="menu-card__title">{{ $title }}</h3>
    @if ($subtitle)
        <p class="menu-card__subtitle">{{ $subtitle }}</p>
    @endif
    <a class="menu-card__link" href="{{ $url }}">{{ $cta }} <span aria-hidden="true">&rarr;</span></a>
    <div class="menu-card__symbol">
        @if ($iconFile)
            <img src="{{ asset('storage/icon_denso/' . $iconFile) }}" alt="{{ $symbol }}" class="menu-card__symbol-img">
        @else
            {{ $symbol }}
        @endif
    </div>
</article>
