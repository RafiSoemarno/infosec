@props([
    'items' => [],
    'activeUrl' => null,
])

<nav class="sidebar-nav">
    @foreach ($items as $item)
        @php
            $itemUrl = $item['url'] ?? '#';
            $isActive = $activeUrl && url($itemUrl) === rtrim($activeUrl, '/');
        @endphp
        <a
            href="{{ $itemUrl }}"
            class="sidebar-nav__item {{ $isActive ? 'sidebar-nav__item--active' : '' }}"
        >
            <span class="sidebar-nav__symbol">{{ $item['symbol'] ?? '' }}</span>
            <span class="sidebar-nav__label">{{ $item['title'] ?? '' }}</span>
        </a>
    @endforeach
</nav>
