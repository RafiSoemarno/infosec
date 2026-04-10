@props([
    'items' => [],
    'activeUrl' => null,
])

@php
$symbolIconMap = [
    'EDU' => 'icon_education.png',
    'DRL' => 'icon_drill_simulation.png',
    'RES' => 'icon_my_result.png',
    'PRG' => 'icon_progress_drill.png',
    'DSH' => 'icon_dashboard.png',
];
@endphp

<nav class="sidebar-nav">
    @foreach ($items as $item)
        @php
            $itemUrl = $item['url'] ?? '#';
            $isActive = $activeUrl && url($itemUrl) === rtrim($activeUrl, '/');
            $symbol = $item['symbol'] ?? '';
            $iconFile = $symbolIconMap[strtoupper($symbol)] ?? null;
        @endphp
        <a
            href="{{ $itemUrl }}"
            class="sidebar-nav__item {{ $isActive ? 'sidebar-nav__item--active' : '' }}"
        >
            <span class="sidebar-nav__symbol">
                @if ($iconFile)
                    <img src="{{ asset('storage/icon_denso/' . $iconFile) }}" alt="{{ $symbol }}" class="sidebar-nav__symbol-img">
                @else
                    {{ $symbol }}
                @endif
            </span>
            <span class="sidebar-nav__label">{{ $item['title'] ?? '' }}</span>
        </a>
    @endforeach
</nav>
