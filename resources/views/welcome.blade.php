@extends('layouts.auth')

@php
    $brand = $drillData['brand'] ?? [];
    $dashboard = $drillData['dashboard'] ?? [];
    $authUsers = $drillData['auth']['users'] ?? [];
    $testAccount = $authUsers[0] ?? null;
    $periodData = $dashboard['periodData'] ?? [];
    $periodOptions = $dashboard['periodOptions'] ?? array_keys($periodData);
    $selectedPeriod = $dashboard['selectedPeriod'] ?? ($periodOptions[0] ?? null);
    $currentPeriod = $periodData[$selectedPeriod] ?? [];
    $targetValue = (int) ($currentPeriod['target'] ?? $dashboard['target'] ?? 0);
    $actualValue = (int) ($currentPeriod['actual'] ?? $dashboard['actual'] ?? 0);
    $percentageValue = (int) ($currentPeriod['percentage'] ?? $dashboard['percentage'] ?? 0);
@endphp

@section('title', $dashboard['title'] ?? 'Drill Simulation')

@section('left_sidebar')
    <div class="d-flex flex-column h-100">
        <div>
            <div class="brand-lockup fade-in-up">
                <img src="{{ asset('storage/Red_DENSO_Hires.png') }}" alt="DENSO - Crafting the Core" class="brand-lockup__logo">
            </div>

            <div class="sidebar-section fade-in-up delay-1">
                <h3 class="section-title">Sign In</h3>
                <p class="section-subtitle">Access the drill dashboard and simulation tools.</p>
            </div>

            <form class="auth-box fade-in-up delay-2 mt-4" action="{{ url('/login') }}" method="post">
                @csrf
                @if ($errors->has('auth'))
                    <div class="alert alert-danger py-2 mb-3" role="alert">{{ $errors->first('auth') }}</div>
                @endif
                <div class="mb-3">
                    <input type="text" name="username" class="form-control form-control-lg" placeholder="Username" aria-label="Username" value="{{ old('username') }}" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" aria-label="Password" required>
                </div>
                <button type="submit" class="app-btn-primary">Sign In</button>
                <a href="#" class="d-inline-block mt-3 text-support text-decoration-none">Forgot Password?</a>
                @if ($testAccount)
                    <div class="small text-support mt-3">
                        Test login: {{ $testAccount['username'] ?? 'demo.user' }} / {{ $testAccount['password'] ?? 'demo123' }}
                    </div>
                @endif
            </form>
        </div>

        <div class="mt-auto text-support fw-semibold">Information System Division</div>
    </div>
@endsection

@section('topbar')
    <x-ui.header eyebrow="Dashboard" :title="$dashboard['title'] ?? 'Drill Simulation'" :subtitle="$dashboard['subtitle'] ?? 'Self-Service Cyber Attack'">
        <div>
            <div>{{ now()->translatedFormat('D, d M Y') }}</div>
            <div class="text-support">Standard desktop shell</div>
        </div>
    </x-ui.header>
@endsection

@section('content')
    <section class="content-span-4 fade-in-up delay-1">
        <x-metric-card
            label="Target"
            :value="$targetValue"
            value-id="metric-target"
        />
    </section>

    <section class="content-span-4 fade-in-up delay-2">
        <x-metric-card
            label="Actual"
            :value="$actualValue"
            value-id="metric-actual"
        />
    </section>

    <section class="content-span-4 fade-in-up delay-3">
        <x-metric-card
            label="Percentage"
            :value="$percentageValue"
            suffix="%"
            value-id="metric-percentage"
        />
    </section>

    <section class="content-span-12 fade-in-up delay-2">
        <div class="panel-card chart-panel">
            <div class="d-flex align-items-start justify-content-between gap-4">
                <div>
                    <p class="page-header__eyebrow mb-2">Statistics</p>
                    <h2 class="section-title">Drill Statistic</h2>
                    <p class="section-subtitle">Target vs Actual by division</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-support fw-semibold" for="period-select">Drill Period</label>
                    <select id="period-select" class="form-select">
                        @foreach ($periodOptions as $option)
                            <option value="{{ $option }}" @selected($option === $selectedPeriod)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="legend-row">
                <span><span class="legend-dot" style="background: var(--color-brand);"></span>TARGET</span>
                <span><span class="legend-dot" style="background: var(--color-accent);"></span>ACTUAL</span>
            </div>

            <div class="chart-panel__canvas">
                <canvas id="drill-chart" aria-label="Drill period statistics" role="img"></canvas>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const periodData = @json($periodData);
            const fallbackPeriod = @json($selectedPeriod);
            const periodSelect = document.getElementById('period-select');
            const targetMetric = document.getElementById('metric-target');
            const actualMetric = document.getElementById('metric-actual');
            const percentageMetric = document.getElementById('metric-percentage');
            const chartEl = document.getElementById('drill-chart');

            if (!periodSelect || !chartEl) {
                return;
            }

            const getPeriodRecord = (period) => {
                const key = period in periodData ? period : fallbackPeriod;
                return periodData[key] || { target: 0, actual: 0, percentage: 0, stats: [] };
            };

            const firstRecord = getPeriodRecord(periodSelect.value || fallbackPeriod);
            const firstStats = Array.isArray(firstRecord.stats) ? firstRecord.stats : [];

            const chart = new Chart(chartEl, {
                type: 'bar',
                data: {
                    labels: firstStats.map((item) => item.label || '-'),
                    datasets: [
                        {
                            label: 'TARGET',
                            data: firstStats.map((item) => Number(item.target || 0)),
                            backgroundColor: '#d62839',
                            borderRadius: 8,
                            borderSkipped: false,
                            barPercentage: 0.75,
                            categoryPercentage: 0.62
                        },
                        {
                            label: 'ACTUAL',
                            data: firstStats.map((item) => Number(item.actual || 0)),
                            backgroundColor: '#52c6cd',
                            borderRadius: 8,
                            borderSkipped: false,
                            barPercentage: 0.75,
                            categoryPercentage: 0.62
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 650,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1f2328',
                            titleFont: { family: 'Plus Jakarta Sans', weight: '700' },
                            bodyFont: { family: 'Plus Jakarta Sans' }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#1f2328',
                                font: { family: 'Plus Jakarta Sans', weight: '700' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(31, 35, 40, 0.1)'
                            },
                            ticks: {
                                color: '#6a727c',
                                precision: 0,
                                font: { family: 'Plus Jakarta Sans' }
                            }
                        }
                    }
                }
            });

            const renderPeriod = (period) => {
                const record = getPeriodRecord(period);
                const stats = Array.isArray(record.stats) ? record.stats : [];

                targetMetric.textContent = Number(record.target || 0).toLocaleString();
                actualMetric.textContent = Number(record.actual || 0).toLocaleString();
                percentageMetric.textContent = `${Number(record.percentage || 0)}%`;

                chart.data.labels = stats.map((item) => item.label || '-');
                chart.data.datasets[0].data = stats.map((item) => Number(item.target || 0));
                chart.data.datasets[1].data = stats.map((item) => Number(item.actual || 0));
                chart.update();
            };

            renderPeriod(periodSelect.value || fallbackPeriod);
            periodSelect.addEventListener('change', (event) => {
                renderPeriod(event.target.value);
            });
        })();
    </script>
@endpush
