@extends('layouts.app')

@php
    $menuItems = $menuData['items'] ?? [];
    $pageTitle = $progressDrillData['title'] ?? 'Progress Drill';
    $pageSubtitle = $progressDrillData['subtitle'] ?? 'Statistic Overview';

    $fiscalYears = $progressDrillData['fiscalYears'] ?? [2026];
    $selectedFY = $progressDrillData['selectedFY'] ?? 2026;
    $periods = $progressDrillData['periods'] ?? ['1st Half', '2nd Half'];
    $selectedPeriod = $progressDrillData['selectedPeriod'] ?? '1st Half';
    $periodData = $progressDrillData['periodData'] ?? [];

    $companies = $progressDrillData['companies'] ?? [];
    $selectedCompany = $progressDrillData['selectedCompany'] ?? ($companies[0] ?? '');
    $buCodes = $progressDrillData['buCodes'] ?? [];
    $selectedBuCode = $progressDrillData['selectedBuCode'] ?? ($buCodes[0] ?? '');

    $drillHistory = $progressDrillData['drillHistory'] ?? [];

    $currentPeriod = $periodData[$selectedPeriod] ?? [];
    $planValue = (int) ($currentPeriod['target'] ?? 0);
    $actualValue = (int) ($currentPeriod['actual'] ?? 0);
    $ratioValue = (int) ($currentPeriod['percentage'] ?? 0);

    $videos = $educationData['videos'] ?? [];
    $totalVideos = count($videos);
    $watchedCount = count(array_filter($videos, fn($v) => $v['watched'] ?? false));
    $allWatched = $totalVideos > 0 && $watchedCount >= $totalVideos;
@endphp

@section('title', $pageTitle)

@section('left_sidebar')
    <div class="d-flex flex-column h-100">
        <div class="brand-lockup">
            <img src="{{ asset('Red_DENSO_Hires.png') }}" alt="DENSO - Crafting the Core" class="brand-lockup__logo">
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
                <img src="{{ asset('icon_denso/icon_profile.png') }}" alt="Profile" class="topbar-profile__avatar-img">
            </div>
        </div>
    </x-ui.header>
@endsection

@section('content')

    {{-- Row 1: Heading + Period/FY filters --}}
    <section class="content-span-12 fade-in-up">
        <div class="pd-topbar panel-card">
            <h2 class="pd-topbar__title">{{ $pageSubtitle }}</h2>

            <div class="pd-topbar__filters">
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
            </div>
        </div>
    </section>

    {{-- Row 2: Filter column + Charts column --}}
    <section class="content-span-3 fade-in-up delay-1">
        <div class="pd-filter-col panel-card h-100">
            {{-- Company filter --}}
            <div class="pd-filter-group">
                <p class="pd-filter-group__label">COMPANY</p>
                <div class="pd-filter-group__list" id="companyList">
                    @foreach ($companies as $company)
                        <button
                            class="pd-filter-btn {{ $company === $selectedCompany ? 'pd-filter-btn--active' : '' }}"
                            data-filter-type="company"
                            data-value="{{ $company }}"
                        >{{ $company }}</button>
                    @endforeach
                </div>
            </div>

            {{-- BU Code filter --}}
            <div class="pd-filter-group mt-4">
                <p class="pd-filter-group__label">BU. CODE</p>
                <div class="pd-filter-group__list" id="buCodeList">
                    @foreach ($buCodes as $buCode)
                        <button
                            class="pd-filter-btn {{ $buCode === $selectedBuCode ? 'pd-filter-btn--active' : '' }}"
                            data-filter-type="buCode"
                            data-value="{{ $buCode }}"
                        >{{ $buCode }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="content-span-9 fade-in-up delay-1">
        <div class="panel-card h-100">
            {{-- Charts column heading --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <p class="page-header__eyebrow mb-1">Statistics</p>
                    <h2 class="section-title mb-0">PROGRESS DRILL</h2>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="legend-dot-row"><span class="legend-dot" style="background: var(--color-accent);"></span>ACTUAL</span>
                    <span class="legend-dot-row"><span class="legend-dot" style="background: var(--color-brand);"></span>TARGET</span>
                </div>
            </div>

            {{-- Stats row: numbers + chart --}}
            <div class="pd-stats-row">
                {{-- Number column --}}
                <div class="pd-numbers-col">
                    <x-metric-card
                        label="Plan Register"
                        :value="$planValue"
                        note="PERSON"
                        value-id="metric-plan"
                    />
                    <x-metric-card
                        label="Actual"
                        :value="$actualValue"
                        note="PERSON"
                        value-id="metric-actual"
                    />
                    <x-metric-card
                        label="Attend Ratio"
                        :value="$ratioValue"
                        suffix="%"
                        note="PERSON"
                        value-id="metric-ratio"
                    />
                </div>

                {{-- Chart column --}}
                <div class="pd-chart-col">
                    <canvas id="progress-drill-chart" aria-label="Progress Drill chart" role="img"></canvas>
                </div>
            </div>
        </div>
    </section>

    {{-- Row 3: Drill History table --}}
    <section class="content-span-12 fade-in-up delay-2">
        <div class="pa-section panel-card">
            <div class="pa-section__header pa-section__header--compact">
                <div>
                    <h2 class="pa-section__title">Drill History
                    </h2>
                </div>
                <button class="pa-export-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 2V5h2v6h1.17L12 13.17 9.83 11H11zm-6 7h14v2H5v-2z"/></svg>
                    Export Report
                </button>
            </div>

            <div class="pa-table-wrap">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th>NPK</th>
                            <th>NAMA</th>
                            <th>PLANT</th>
                            <th>PC NAME</th>
                            <th>DRILL TYPE</th>
                            <th>CATEGORY</th>
                            <th>DATE</th>
                            <th>RESPONSE TIME</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drillHistory as $row)
                            <tr>
                                <td>{{ $row['npk'] ?? '—' }}</td>
                                <td>{{ $row['name'] ?? '—' }}</td>
                                <td>{{ $row['plant'] ?? '—' }}</td>
                                <td>{{ $row['pcName'] ?? '—' }}</td>
                                <td>{{ $row['drillType'] ?? '—' }}</td>
                                <td>{{ $row['category'] ?? '—' }}</td>
                                <td>{{ $row['date'] ?? '—' }}</td>
                                <td>{{ $row['responseTime'] ?? '—' }}</td>
                                <td>
                                    <span class="pa-status-badge pa-status-badge--{{ strtolower($row['status'] ?? 'unknown') }}">
                                        {{ $row['status'] ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="pa-table__empty">No drill history available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pa-pagination">
                <button class="pa-pagination__btn pa-pagination__btn--active">First</button>
                <button class="pa-pagination__btn pa-pagination__btn--active">Previous</button>
                <button class="pa-pagination__btn pa-pagination__btn--page pa-pagination__btn--current">1</button>
                <button class="pa-pagination__btn pa-pagination__btn--page">2</button>
                <button class="pa-pagination__btn pa-pagination__btn--page">3</button>
                <button class="pa-pagination__btn pa-pagination__btn--page">4</button>
                <button class="pa-pagination__btn pa-pagination__btn--page">5</button>
                <button class="pa-pagination__btn pa-pagination__btn--active">Next</button>
                <button class="pa-pagination__btn pa-pagination__btn--active">Last</button>
                <span class="pa-pagination__info">| View : 1–10 of 100</span>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const periodData = @json($periodData);
            const periodSelect = document.getElementById('periodSelect');
            const planMetric = document.getElementById('metric-plan');
            const actualMetric = document.getElementById('metric-actual');
            const ratioMetric = document.getElementById('metric-ratio');
            const chartEl = document.getElementById('progress-drill-chart');

            if (!periodSelect || !chartEl) return;

            const getPeriodRecord = (period) => {
                return periodData[period] || { target: 0, actual: 0, percentage: 0, stats: [] };
            };

            const initialRecord = getPeriodRecord(periodSelect.value);
            const initialStats = Array.isArray(initialRecord.stats) ? initialRecord.stats : [];

            const chart = new Chart(chartEl, {
                type: 'bar',
                data: {
                    labels: initialStats.map((item) => item.label || '-'),
                    datasets: [
                        {
                            label: 'ACTUAL',
                            data: initialStats.map((item) => Number(item.actual || 0)),
                            backgroundColor: '#52c6cd',
                            borderRadius: 8,
                            borderSkipped: false,
                            barPercentage: 0.75,
                            categoryPercentage: 0.62,
                        },
                        {
                            label: 'TARGET',
                            data: initialStats.map((item) => Number(item.target || 0)),
                            backgroundColor: '#d62839',
                            borderRadius: 8,
                            borderSkipped: false,
                            barPercentage: 0.75,
                            categoryPercentage: 0.62,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 650, easing: 'easeOutQuart' },
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f2328',
                            titleFont: { family: 'DENSOSans', weight: '700' },
                            bodyFont: { family: 'DENSOSans' },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#1f2328', font: { family: 'DENSOSans', weight: '700' } },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(31, 35, 40, 0.1)' },
                            ticks: { color: '#6a727c', precision: 0, font: { family: 'DENSOSans' } },
                        },
                    },
                },
            });

            const renderPeriod = (period) => {
                const record = getPeriodRecord(period);
                const stats = Array.isArray(record.stats) ? record.stats : [];

                if (planMetric) planMetric.textContent = Number(record.target || 0).toLocaleString();
                if (actualMetric) actualMetric.textContent = Number(record.actual || 0).toLocaleString();
                if (ratioMetric) ratioMetric.textContent = `${Number(record.percentage || 0)}%`;

                chart.data.labels = stats.map((item) => item.label || '-');
                chart.data.datasets[0].data = stats.map((item) => Number(item.actual || 0));
                chart.data.datasets[1].data = stats.map((item) => Number(item.target || 0));
                chart.update();
            };

            renderPeriod(periodSelect.value);
            periodSelect.addEventListener('change', (e) => renderPeriod(e.target.value));

            // Filter button toggle behaviour
            document.querySelectorAll('.pd-filter-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const type = btn.dataset.filterType;
                    document.querySelectorAll(`.pd-filter-btn[data-filter-type="${type}"]`).forEach((b) => {
                        b.classList.remove('pd-filter-btn--active');
                    });
                    btn.classList.add('pd-filter-btn--active');
                });
            });
        })();
    </script>
@endpush
