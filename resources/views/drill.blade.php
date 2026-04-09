@extends('layouts.app')

@php
    $menuItems = $menuData['items'] ?? [];
    $drills = $drillSimData['drills'] ?? [];
    $pageTitle = $drillSimData['title'] ?? 'Drill Simulation';
    $pageSubtitle = $drillSimData['subtitle'] ?? 'Self-Service Cyber Attack';

    $videos = $educationData['videos'] ?? [];
    $totalVideos = count($videos);
    $watchedCount = count(array_filter($videos, fn($v) => $v['watched'] ?? false));
    $allWatched = $totalVideos > 0 && $watchedCount >= $totalVideos;

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

        <div class="mt-auto">
            <div class="drill-edu-progress panel-card p-3">
                <p class="sidebar-label mb-2">Education Progress</p>
                <div class="d-flex align-items-center gap-2">
                    <div class="drill-edu-progress__bar-wrap">
                        <div class="drill-edu-progress__bar" style="width: {{ $totalVideos > 0 ? round(($watchedCount / $totalVideos) * 100) : 0 }}%"></div>
                    </div>
                    <span class="drill-edu-progress__count">{{ $watchedCount }}/{{ $totalVideos }}</span>
                </div>
                @if (!$allWatched)
                    <p class="drill-edu-progress__hint mt-2">Watch all videos to unlock Run Drill</p>
                @else
                    <p class="drill-edu-progress__hint drill-edu-progress__hint--done mt-2">All videos watched — drills unlocked!</p>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('topbar')
    @php $authUser = session('auth_user'); @endphp
    <x-ui.header eyebrow="Control Center" :title="$pageTitle" :subtitle="$pageSubtitle">
        <div class="topbar-profile">
            <div class="topbar-profile__text">
                <span class="topbar-profile__name">{{ $authUser['name'] ?? '' }}</span>
                <span class="topbar-profile__id">{{ $authUser['employeeId'] ?? '' }}</span>
            </div>
            <div class="topbar-profile__avatar">
                {{ strtoupper(substr($authUser['name'] ?? 'U', 0, 1)) }}
            </div>
        </div>
    </x-ui.header>
@endsection

@section('content')
    @foreach ($drills as $drill)
        @php
            $drillId = $drill['id'] ?? 0;
            $isCompleted = $drill['completed'] ?? false;
            $isComingSoon = $drill['comingSoon'] ?? false;
            $periodStart = $drill['periodStart'] ?? '';
            $periodEnd = $drill['periodEnd'] ?? '';
            $durationLabel = $drill['duration'] ?? '';
            $computerName = session('auth_user')['employeeId'] ?? 'N/A';
            $notifyNote = $drill['notifyNote'] ?? '';
        @endphp
        <section class="content-span-12 fade-in-up">
            <div class="drill-card panel-card">
                <div class="drill-card__body">
                    <div class="drill-card__info">
                        <h2 class="drill-card__title">{{ $drill['title'] ?? 'Drill' }}</h2>
                        <p class="drill-card__desc">{{ $drill['description'] ?? '' }}</p>

                        @if ($notifyNote)
                            <p class="drill-card__notify">{{ $notifyNote }}</p>
                        @endif

                        <div class="drill-card__actions">
                            @if ($isComingSoon)
                                <button class="drill-action-btn drill-action-btn--coming" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    Coming Soon
                                </button>
                            {{-- @elseif ($isCompleted && $allWatched)
                                <button class="drill-action-btn drill-action-btn--completed" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Complete
                                </button> --}}
                            @elseif ($allWatched)
                                <form method="POST" action="{{ url('/drill/complete') }}">
                                    @csrf
                                    <input type="hidden" name="drill_id" value="{{ $drillId }}">
                                    <button type="submit" class="drill-action-btn drill-action-btn--run">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        Run Drill
                                    </button>
                                </form>
                            @else
                                <a href="{{ url('/education') }}" class="drill-action-btn drill-action-btn--watch">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    Watch Video
                                </a>
                            @endif

                            <div class="drill-card__meta">
                                <span class="drill-card__meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                                    {{ $durationLabel }}
                                </span>
                                <span class="drill-card__meta-item">
                                    @if ($isComingSoon)
                                        Due date : {{ $periodStart }}
                                    @else
                                        Period : {{ $periodStart }} - {{ $periodEnd }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="drill-card__badge {{ $isComingSoon ? 'drill-card__badge--empty' : '' }}">
                        @if ($isComingSoon)
                            <span style="font-size: 3.5rem; line-height: 1; display: block; text-align: center;">???</span>
                        @else
                            <div class="drill-card__badge-label">Computer Name</div>
                            <div class="drill-card__badge-value">{{ $computerName }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endforeach

@endsection
