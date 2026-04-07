@props([
    'items' => [],
    'activeUrl' => null,
])

<nav class="sidebar-nav">
    @foreach ($items as $item)
        @php
            $isActive = $activeUrl && rtrim($item['url'] ?? '#', '/') === rtrim($activeUrl, '/');
        @endphp
        <a
            href="{{ $item['url'] ?? '#' }}"
            class="sidebar-nav__item {{ $isActive ? 'sidebar-nav__item--active' : '' }}"
        >
            <span class="sidebar-nav__symbol">{{ $item['symbol'] ?? '' }}</span>
            <span class="sidebar-nav__label">{{ $item['title'] ?? '' }}</span>
        </a>
    @endforeach
</nav>
