@extends('layouts.app')

@php
    $menuItems = array_values(array_filter($menuData['items'] ?? [], fn($item) => strtolower($item['title'] ?? '') !== 'dashboard'));
    $menuTitle = $menuData['title'] ?? 'Main Menu';
    $welcomeTitle = $menuData['welcomeTitle'] ?? 'Welcome Drill Simulation';
    $welcomeSubtitle = $menuData['welcomeSubtitle'] ?? 'Self-Service Cyber Attack';
    $showDate = now()->translatedFormat('D, d M Y');
@endphp

@section('title', $menuTitle)

@section('left_sidebar')
    <div class="d-flex flex-column h-100">
        <div class="brand-lockup">
            <img src="{{ asset('storage/Red_DENSO_Hires.png') }}" alt="DENSO - Crafting the Core" class="brand-lockup__logo">
        </div>

        <div class="mt-4 stack-lg">
            <x-profile-card :user="$user" />
        </div>

        <form class="mt-auto" method="post" action="{{ url('/logout') }}">
            @csrf
            <button type="submit" class="app-btn-primary">Sign Out</button>
        </form>
    </div>
@endsection

@section('topbar')
    <x-ui.header eyebrow="Control Center" :title="$welcomeTitle" :subtitle="$welcomeSubtitle">
        <div class="topbar-actions">
            <span class="topbar-actions__date">{{ $showDate }}</span>
            <x-notification-bell :count="$notificationCount ?? 0" />
        </div>
    </x-ui.header>
@endsection

@section('content')
    <section class="content-span-12">
        <div class="panel-card panel-card--muted p-4">
            <p class="page-header__eyebrow mb-2">Navigation</p>
            <h2 class="section-title">{{ $menuTitle }}</h2>
            <p class="section-subtitle">Reusable menu modules aligned to the shared desktop layout and color system.</p>
        </div>
    </section>

    @forelse ($menuItems as $item)
        <section class="content-span-12 fade-in-up">
            <x-menu-card
                :title="$item['title'] ?? 'Menu Item'"
                :subtitle="$item['subtitle'] ?? 'Quote for this theme'"
                :url="$item['url'] ?? '#'"
                :symbol="$item['symbol'] ?? 'ITEM'"
            />
        </section>
    @empty
        <section class="content-span-12 fade-in-up">
            <x-menu-card title="Education" subtitle="Quote for this theme" url="#" symbol="EDU" />
        </section>
        <section class="content-span-12 fade-in-up delay-1">
            <x-menu-card title="Drill Simulation" subtitle="Quote for this theme" url="#" symbol="DRL" />
        </section>
        <section class="content-span-12 fade-in-up delay-2">
            <x-menu-card title="My Result" subtitle="Quote for this theme" url="#" symbol="RES" />
        </section>
    @endforelse
@endsection
