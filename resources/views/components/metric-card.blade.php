@props([
    'label',
    'value',
    'note' => null,
    'valueId' => null,
    'suffix' => null,
])

<div {{ $attributes->class('metric-card panel-card') }}>
    <div class="metric-card__label">{{ $label }}</div>
    <p @if ($valueId) id="{{ $valueId }}" @endif class="metric-card__value">{{ $value }}{{ $suffix }}</p>
    @if ($note)
        <div class="metric-card__note">{{ $note }}</div>
    @endif
</div>
