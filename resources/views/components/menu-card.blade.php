@props([
    'title',
    'subtitle' => null,
    'url' => '#',
    'symbol' => 'ITEM',
    'cta' => 'View Details',
])

<article {{ $attributes->class('menu-card panel-card') }}>
    <h3 class="menu-card__title">{{ $title }}</h3>
    @if ($subtitle)
        <p class="menu-card__subtitle">{{ $subtitle }}</p>
    @endif
    <a class="menu-card__link" href="{{ $url }}">{{ $cta }} <span aria-hidden="true">&rarr;</span></a>
    <div class="menu-card__symbol">{{ $symbol }}</div>
</article>
