@extends('layouts.app')

@php
    $menuItems = $menuData['items'] ?? [];
    $pageTitle = $myResultData['title'] ?? 'Performance Analytics';
    $pageSubtitle = $myResultData['subtitle'] ?? 'View your detailed drill history.';

    $fiscalYears = $myResultData['fiscalYears'] ?? [2026];
    $selectedFY = $myResultData['selectedFY'] ?? 2026;
    $periods = $myResultData['periods'] ?? ['1st Half', '2nd Half'];
    $selectedPeriod = $myResultData['selectedPeriod'] ?? '1st Half';

    $attendance = $myResultData['attendance'] ?? ['completed' => 0, 'total' => 0];
    $drillsCompleted = $myResultData['drillsCompleted'] ?? 0;
    $status = $myResultData['status'] ?? 'Not Yet';

    $device = $myResultData['device'] ?? [];
    $drillHistory = $myResultData['drillHistory'] ?? [];

    $attendancePct = $attendance['total'] > 0
        ? round(($attendance['completed'] / $attendance['total']) * 100)
        : 0;

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

    {{-- Section 1: Performance Analytics --}}
    <section class="content-span-12 fade-in-up">
        <div class="pa-section panel-card">
            <div class="pa-section__header">
                <div>
                    <h2 class="pa-section__title">{{ $pageTitle }}</h2>
                    <p class="pa-section__note">{{ $pageSubtitle }}
                    </p>
                </div>
                <div class="pa-section__controls">
                    <button class="pa-export-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 2V5h2v6h1.17L12 13.17 9.83 11H11zm-6 7h14v2H5v-2z"/></svg>
                        Export Report
                    </button>

                    <div class="pa-period-selectors">
                        <div class="pa-selector">
                            <span class="pa-selector__label">FY :</span>
                            <div class="pa-selector__dropdown">
                                <select id="fySelect" class="pa-selector__select">
                                    @foreach ($fiscalYears as $fy)
                                        <option value="{{ $fy }}" {{ $fy == $selectedFY ? 'selected' : '' }}>{{ $fy }}</option>
                                    @endforeach
                                </select>
                                <svg class="pa-selector__caret" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>

                        <div class="pa-selector">
                            <span class="pa-selector__label">PERIOD :</span>
                            <div class="pa-selector__dropdown">
                                <select id="periodSelect" class="pa-selector__select">
                                    @foreach ($periods as $period)
                                        <option value="{{ $period }}" {{ $period === $selectedPeriod ? 'selected' : '' }}>{{ $period }}</option>
                                    @endforeach
                                </select>
                                <svg class="pa-selector__caret" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pa-stats-row">
                {{-- Attendance --}}
                <div class="pa-stat-card">
                    <div class="pa-stat-card__donut-wrap">
                        <svg class="pa-stat-card__donut" viewBox="0 0 44 44">
                            <circle class="pa-stat-card__donut-track" cx="22" cy="22" r="18" fill="none" stroke-width="4"/>
                            <circle class="pa-stat-card__donut-fill" cx="22" cy="22" r="18" fill="none" stroke-width="4"
                                stroke-dasharray="{{ round($attendancePct * 1.131) }} 113.1"
                                stroke-dashoffset="28.3"
                            />
                            <text x="22" y="26" class="pa-stat-card__donut-label" text-anchor="middle">{{ $attendancePct }}%</text>
                        </svg>
                    </div>
                    <div class="pa-stat-card__body">
                        <p class="pa-stat-card__value">{{ $attendance['completed'] }} / {{ $attendance['total'] }}</p>
                        <p class="pa-stat-card__caption">ATTENDANCE</p>
                    </div>
                    <button class="pa-stat-card__chevron" aria-label="View details">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                {{-- Drills Completed --}}
                <div class="pa-stat-card">
                    <div class="pa-stat-card__icon pa-stat-card__icon--check">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="pa-stat-card__body">
                        <p class="pa-stat-card__value pa-stat-card__value--large">{{ $drillsCompleted }}</p>
                        <p class="pa-stat-card__caption">Drills Completed</p>
                    </div>
                    <button class="pa-stat-card__chevron" aria-label="View details">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                {{-- Status --}}
                <div class="pa-stat-card">
                    <div class="pa-stat-card__icon pa-stat-card__icon--chart">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><path stroke-linecap="round" d="M3 17.5A3.5 3.5 0 006.5 21M3 14v.01"/></svg>
                    </div>
                    <div class="pa-stat-card__body">
                        <span class="pa-status-badge pa-status-badge--{{ strtolower(str_replace(' ', '-', $status)) }}">{{ $status }}</span>
                        <p class="pa-stat-card__caption">Status</p>
                        <p class="pa-stat-card__note">Get Info from Drill History Table</p>
                    </div>
                    <button class="pa-stat-card__chevron" aria-label="View details">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 2: Device Information --}}
    <section class="content-span-12 fade-in-up delay-1">
        <div class="pa-section panel-card">
            <div class="pa-section__header pa-section__header--compact">
                <h2 class="pa-section__title">Device Information</h2>
            </div>

            <div class="pa-device-grid">
                <div class="pa-device-card">
                    <div class="pa-device-card__label">Computer Name</div>
                    <div class="pa-device-card__value">{{ $device['computerName'] ?? '—' }}</div>
                </div>
                <div class="pa-device-card">
                    <div class="pa-device-card__label">IP Address</div>
                    <div class="pa-device-card__value">{{ $device['ipAddress'] ?? '—' }}</div>
                </div>
                <div class="pa-device-card">
                    <div class="pa-device-card__label">Plant</div>
                    <div class="pa-device-card__value">{{ $device['plant'] ?? '—' }}</div>
                </div>
                <div class="pa-device-card">
                    <div class="pa-device-card__label">Location</div>
                    <div class="pa-device-card__value">{{ $device['location'] ?? '—' }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 3: Drill History --}}
    <section class="content-span-12 fade-in-up delay-2">
        <div class="pa-section panel-card">
            <div class="pa-section__header pa-section__header--compact">
                <div>
                    <h2 class="pa-section__title">Drill History
                    </h2>
                </div>
                <a href="#" class="pa-view-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                    View All
                </a>
            </div>

            <div class="pa-table-wrap">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th>DRILL NAME</th>
                            <th>CATEGORY</th>
                            <th>DATE</th>
                            <th>TIME</th>
                            <th>RESPONSE TIME</th>
                            <th>SCORE</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drillHistory as $row)
                            <tr>
                                <td>{{ $row['drillName'] ?? '—' }}</td>
                                <td>{{ $row['category'] ?? '—' }}</td>
                                <td>{{ $row['date'] ?? '—' }}</td>
                                <td>{{ $row['time'] ?? '—' }}</td>
                                <td>{{ $row['responseTime'] ?? '—' }}</td>
                                <td>{{ $row['score'] ?? '—' }}</td>
                                <td>
                                    <span class="pa-status-badge pa-status-badge--{{ strtolower($row['status'] ?? 'unknown') }}">
                                        {{ $row['status'] ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="pa-table__empty">No drill history available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pa-pagination">
                <button class="pa-pagination__btn pa-pagination__btn--active">Previous</button>
                <button class="pa-pagination__btn pa-pagination__btn--page pa-pagination__btn--current">1</button>
                <button class="pa-pagination__btn pa-pagination__btn--active">Next</button>
            </div>
        </div>
    </section>

@endsection
