@extends('layouts.app')

@php
    $videos = $educationData['videos'] ?? [];
    $pageTitle = $educationData['title'] ?? 'Education';
    $pageSubtitle = $educationData['subtitle'] ?? '';
    $progressLabel = $educationData['progressLabel'] ?? 'Education Material';
    $progressNote = $educationData['progressNote'] ?? '';
    $searchPlaceholder = $educationData['searchPlaceholder'] ?? 'Search education ...';

    $totalVideos = count($videos);
    $watchedCount = count(array_filter($videos, fn($v) => $v['watched'] ?? false));

    $currentVideoId = (int) request('video', $videos[0]['id'] ?? 1);
    $currentIndex = 0;
    foreach ($videos as $i => $v) {
        if ($v['id'] === $currentVideoId) {
            $currentIndex = $i;
            break;
        }
    }
    $currentVideo = $videos[$currentIndex] ?? null;
    $prevVideo = $currentIndex > 0 ? $videos[$currentIndex - 1] : null;
    $nextVideo = $currentIndex < count($videos) - 1 ? $videos[$currentIndex + 1] : null;

    $menuItems = $menuData['items'] ?? [];
@endphp

@section('title', $pageTitle)

@section('left_sidebar')
    <div class="d-flex flex-column h-100">
        <div class="brand-lockup">
            <img src="{{ asset('storage/Red_DENSO_Hires.png') }}" alt="DENSO - Crafting the Core" class="brand-lockup__logo">
        </div>

        <div class="mt-4">
            <x-sidebar-nav :items="$menuItems" :activeUrl="url()->current()" />
        </div>

        <div class="edu-nav mt-auto">
            @if ($prevVideo)
                <a href="{{ url('/education') }}?video={{ $prevVideo['id'] }}" class="app-btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Previous Video
                </a>
            @else
                <button class="app-btn-secondary" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Previous Video
                </button>
            @endif

            @if ($nextVideo)
                <a href="{{ url('/education') }}?video={{ $nextVideo['id'] }}" class="app-btn-primary mt-2">
                    Next Video
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <button class="app-btn-primary mt-2" disabled>
                    Next Video
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            @endif
        </div>
    </div>
@endsection

@section('topbar')
    <x-ui.header eyebrow="Control Center" :title="$pageTitle" :subtitle="$pageSubtitle">
        <div>
            <div>{{ now()->translatedFormat('D, d M Y') }}</div>
        </div>
    </x-ui.header>
@endsection

@section('content')
    @if ($currentVideo)
        <section class="content-span-12 fade-in-up">
            <div class="edu-video-panel panel-card">
                <div class="edu-video-panel__embed">
                    <iframe
                        src="{{ $currentVideo['embedUrl'] }}"
                        title="{{ $currentVideo['title'] }}"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </section>
    @endif
@endsection

@section('right_sidebar')
    <div class="d-flex flex-column h-100 gap-3">
        <div class="edu-search">
            <svg class="edu-search__icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
            <input class="edu-search__input" type="text" placeholder="{{ $searchPlaceholder }}" id="eduSearchInput">
        </div>

        <div class="edu-list-header">
            <p class="edu-list-header__title">{{ $progressLabel }}</p>
            <p class="edu-list-header__note">{{ $progressNote }}</p>
        </div>

        <div class="edu-video-list" id="eduVideoList">
            @foreach ($videos as $video)
                <a
                    href="{{ url('/education') }}?video={{ $video['id'] }}"
                    class="edu-video-item {{ $video['id'] === $currentVideoId ? 'edu-video-item--active' : '' }}"
                    data-title="{{ strtolower($video['title']) }}"
                >
                    <span class="edu-video-item__dot {{ ($video['watched'] ?? false) ? 'edu-video-item__dot--watched' : '' }}"></span>
                    <span class="edu-video-item__title">{{ $video['title'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="edu-progress-badge mt-auto">
            <div class="edu-progress-badge__score">
                <span class="edu-progress-badge__num">{{ $watchedCount }}</span>
                <span class="edu-progress-badge__denom">/{{ $totalVideos }}</span>
            </div>
            <div>
                <p class="edu-progress-badge__label">{{ $progressLabel }}</p>
                <p class="edu-progress-badge__cta">Education Progress</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('eduSearchInput').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.edu-video-item').forEach(function (item) {
            const title = item.dataset.title || '';
            item.style.display = title.includes(query) ? '' : 'none';
        });
    });
</script>
@endpush
