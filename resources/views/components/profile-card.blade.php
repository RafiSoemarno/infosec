@props(['user' => []])

@php
    $userName = (string) ($user['name'] ?? 'Guest User');
    $initial = strtoupper(substr($userName, 0, 1));
@endphp

<div {{ $attributes->class('profile-card panel-card') }}>
    <div class="profile-card__avatar">
        <img src="{{ asset('icon_denso/icon_profile.png') }}" alt="{{ $initial }}" class="profile-card__avatar-img">
    </div>
    <h2 class="profile-card__name">{{ $userName }}</h2>
    <p class="profile-card__meta">{{ $user['employeeId'] ?? '-' }}</p>

    <div class="sidebar-section">
        <p class="sidebar-label">Company</p>
        <p class="sidebar-value">{{ $user['company'] ?? '-' }}</p>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-label">Business Unit</p>
        <p class="sidebar-value">{{ $user['businessUnit'] ?? '-' }}</p>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-label">E-mail</p>
        <p class="sidebar-value">{{ $user['email'] ?? '-' }}</p>
    </div>
</div>
