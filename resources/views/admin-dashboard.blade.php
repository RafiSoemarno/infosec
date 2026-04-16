@extends('layouts.app')

@php
    $menuItems      = $menuData['items'] ?? [];
    $authUser       = session('auth_user');

    // ── Period / FY ──────────────────────────────────────────────
    $periodOptions  = $dashData['periodOptions']  ?? ['1st Half', '2nd Half'];
    $selectedPeriod = $dashData['selectedPeriod'] ?? '1st Half';
    $fiscalYears    = $dashData['fiscalYears']     ?? [2026];
    $selectedFY     = $dashData['selectedFY']      ?? 2026;

    // ── Hierarchy data ────────────────────────────────────────────
    $companies         = $dashData['companies']         ?? [];
    $directorates      = $dashData['directorates']      ?? [];
    $divisions         = $dashData['divisions']         ?? [];
    $personsByDivision = $dashData['personsByDivision'] ?? [];

    // ── Chart / KPI data ──────────────────────────────────────────
    $periodData        = $dashData['periodData']       ?? [];
    $drillTypeSummary  = $dashData['drillTypeSummary'] ?? [];

    // ── Table rows ────────────────────────────────────────────────
    $allTableRows = $dashData['tableRows'] ?? [];

    // Default visible stats (all companies, current period)
    $currentStats     = $periodData[$selectedPeriod] ?? ['target' => 0, 'actual' => 0, 'percentage' => 0, 'stats' => []];
    $currentDrillType = $drillTypeSummary[$selectedPeriod] ?? ['selfService' => ['count' => 0], 'scheduled' => ['count' => 0, 'percentage' => 0]];
@endphp

@section('title', 'Drill – Self-Service Cyber Attack')

@section('left_sidebar')
    <div class="d-flex flex-column h-100">
        <div class="brand-lockup">
            <img src="{{ asset('Red_DENSO_Hires.png') }}" alt="DENSO - Crafting the Core" class="brand-lockup__logo">
        </div>
        <div class="mt-4">
            <x-sidebar-nav :items="$menuItems" :activeUrl="url()->current()" />
        </div>
    </div>
@endsection

@section('topbar')
    <x-ui.header eyebrow="Drill" title="Self-Service Cyber Attack" subtitle="">
        {{-- Period selector --}}
        <div class="dsh-topbar-controls">
            <div class="dsh-period-selector">
                <span class="dsh-period-selector__label">PERIOD:</span>
                <div class="dsh-period-dropdown" id="topPeriodDropdown">
                    <button class="dsh-period-btn" id="topPeriodBtn" type="button">
                        <span id="topPeriodText">{{ $selectedPeriod }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dsh-period-menu" id="topPeriodMenu" style="display:none;">
                        @foreach ($periodOptions as $p)
                            <div class="dsh-period-option {{ $p === $selectedPeriod ? 'dsh-period-option--active' : '' }}"
                                 data-period="{{ $p }}">{{ $p }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="dsh-fy-selector">
                <span class="dsh-period-selector__label">FY:</span>
                <div class="dsh-period-dropdown" id="fyDropdown">
                    <button class="dsh-period-btn" id="fyBtn" type="button">
                        <span id="fyText">{{ $selectedFY }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="dsh-period-menu" id="fyMenu" style="display:none;">
                        @foreach ($fiscalYears as $fy)
                            <div class="dsh-period-option {{ $fy == $selectedFY ? 'dsh-period-option--active' : '' }}"
                                 data-fy="{{ $fy }}">{{ $fy }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

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

{{-- ════════════════════════════════════════════════════════════
     SECTION 1 — STATISTIC OVERVIEW
     Filters: Company + Directorate
     ════════════════════════════════════════════════════════════ --}}
<section class="content-span-12 fade-in-up">
    <div class="dsh-overview panel-card">

        {{-- Header row --}}
        <div class="dsh-overview__header">
            <h2 class="dsh-overview__title">Statistic Overview</h2>

            {{-- Chart-layer filters: Company & Directorate --}}
            <div class="dsh-chart-filters" id="chartFilters">
                {{-- Company filter --}}
                <div class="dsh-filter-group">
                    <label class="dsh-filter-label">COMPANY</label>
                    <div class="dsh-multi-select" id="companyFilter">
                        @foreach ($companies as $code => $co)
                            <label class="dsh-check-pill">
                                <input type="checkbox" class="dsh-check-pill__input company-cb" value="{{ $code }}" checked>
                                <span class="dsh-check-pill__label">{{ $code }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Directorate filter (dynamic, dependent on Company) --}}
                <div class="dsh-filter-group">
                    <label class="dsh-filter-label">DIRECTORATE</label>
                    <div class="dsh-multi-select" id="directorateFilter">
                        @foreach ($directorates as $code => $dir)
                            <label class="dsh-check-pill dsh-dir-pill" data-company="{{ $dir['company'] }}">
                                <input type="checkbox" class="dsh-check-pill__input dir-cb" value="{{ $code }}" checked>
                                <span class="dsh-check-pill__label">{{ $dir['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI cards + Progress chart + Drill type --}}
        <div class="dsh-overview__body">

            {{-- KPI cards --}}
            <div class="dsh-kpi-col">
                <div class="dsh-kpi-card dsh-kpi-card--brand">
                    <p class="dsh-kpi-card__label">Plan Register</p>
                    <p class="dsh-kpi-card__value" id="kpiTarget">{{ $currentStats['target'] }}</p>
                </div>
                <div class="dsh-kpi-card">
                    <p class="dsh-kpi-card__label">Actual</p>
                    <p class="dsh-kpi-card__value" id="kpiActual">{{ $currentStats['actual'] }}</p>
                </div>
                <div class="dsh-kpi-card">
                    <p class="dsh-kpi-card__label">Attend Ratio</p>
                    <p class="dsh-kpi-card__value" id="kpiRatio">
                        @php
                            $ratio = $currentStats['target'] > 0
                                ? round(($currentStats['actual'] / $currentStats['target']) * 100)
                                : 0;
                        @endphp
                        {{ $ratio }}%
                    </p>
                </div>
            </div>

            {{-- Progress Drill bar chart --}}
            <div class="dsh-chart-col">
                <p class="dsh-chart-col__heading">PROGRESS DRILL</p>
                <div class="dsh-bar-chart" id="barChart">
                    @foreach ($currentStats['stats'] as $stat)
                        @php
                            $barPct = $stat['target'] > 0 ? round(($stat['actual'] / $stat['target']) * 100) : 0;
                        @endphp
                        <div class="dsh-bar-group" data-company="{{ $stat['label'] }}">
                            <div class="dsh-bar-group__bars">
                                <div class="dsh-bar dsh-bar--target" style="height: 100%;" title="Target: {{ $stat['target'] }}">
                                    <span class="dsh-bar__label">{{ $stat['target'] }}</span>
                                </div>
                                <div class="dsh-bar dsh-bar--actual" style="height: {{ $barPct }}%;" title="Actual: {{ $stat['actual'] }}">
                                    <span class="dsh-bar__label">{{ $stat['actual'] }}</span>
                                </div>
                            </div>
                            <p class="dsh-bar-group__name">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="dsh-chart-legend">
                    <span class="dsh-legend-dot dsh-legend-dot--target"></span><span>TARGET</span>
                    <span class="dsh-legend-dot dsh-legend-dot--actual"></span><span>ACTUAL</span>
                </div>
            </div>

            {{-- Drill type indicators --}}
            <div class="dsh-drilltype-col">
                {{-- Self Service --}}
                <div class="dsh-type-card">
                    <p class="dsh-type-card__label">DRILL TYPE</p>
                    <div class="dsh-type-card__badge dsh-type-card__badge--brand">SELF SERVICE</div>
                    <p class="dsh-type-card__count" id="selfServiceCount">{{ $currentDrillType['selfService']['count'] ?? 0 }}</p>
                </div>
                {{-- Scheduled --}}
                <div class="dsh-type-card">
                    <p class="dsh-type-card__label">SCHEDULED</p>
                    <div class="dsh-type-donut-wrap">
                        <svg class="dsh-type-donut" viewBox="0 0 44 44" id="scheduledDonut">
                            @php
                                $sPct = $currentDrillType['scheduled']['percentage'] ?? 0;
                                $sDash = round($sPct * 1.131);
                            @endphp
                            <circle class="dsh-type-donut__track" cx="22" cy="22" r="18" fill="none" stroke-width="5"/>
                            <circle class="dsh-type-donut__fill" cx="22" cy="22" r="18" fill="none" stroke-width="5"
                                stroke-dasharray="{{ $sDash }} 113.1" stroke-dashoffset="28.3"/>
                            <text x="22" y="26" class="dsh-type-donut__text" text-anchor="middle" id="scheduledPct">{{ $sPct }}%</text>
                        </svg>
                    </div>
                    <p class="dsh-type-card__count" id="scheduledCount">{{ $currentDrillType['scheduled']['count'] ?? 0 }}</p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════
     SECTION 2 — DIVISION / PERSON FILTERS + DATA TABLE
     Filters: Division + Responsible Person (independent of charts)
     ════════════════════════════════════════════════════════════ --}}
<section class="content-span-12 fade-in-up delay-1">
    <div class="dsh-table-section panel-card">

        {{-- Table filters header --}}
        <div class="dsh-table-filters" id="tableFilters">
            <div class="dsh-filter-group">
                <label class="dsh-filter-label">DIVISION</label>
                <div class="dsh-select-wrap">
                    <select class="dsh-select" id="divisionSelect">
                        <option value="">All Divisions</option>
                        @foreach ($divisions as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <svg class="dsh-select-caret" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            <div class="dsh-filter-group">
                <label class="dsh-filter-label">RESPONSIBLE PERSON</label>
                <div class="dsh-select-wrap">
                    <select class="dsh-select" id="personSelect">
                        <option value="">All Persons</option>
                        {{-- Options populated dynamically via JS --}}
                    </select>
                    <svg class="dsh-select-caret" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            <div class="dsh-filter-group dsh-filter-group--right">
                <button class="dsh-export-btn" type="button" id="exportBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 2V5h2v6h1.17L12 13.17 9.83 11H11zm-6 7h14v2H5v-2z"/></svg>
                    Export Report
                </button>
            </div>
        </div>

        {{-- Data table --}}
        <div class="dsh-table-wrap">
            <table class="dsh-table" id="drillTable">
                <thead>
                    <tr>
                        <th>NPK</th>
                        <th>NAME</th>
                        <th>PLANT</th>
                        <th>PC NAME</th>
                        <th>DRILL TYPE</th>
                        <th>CATEGORY</th>
                        <th>DATE</th>
                        <th>RESPONSE TIME</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody id="drillTableBody">
                    {{-- Rendered by JS --}}
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="dsh-pagination">
            <button class="dsh-page-btn" id="tblPrevBtn" disabled>Previous</button>
            <button class="dsh-page-btn dsh-page-btn--current" id="tblPageNum">1</button>
            <button class="dsh-page-btn" id="tblNextBtn">Next</button>
        </div>

    </div>
</section>

@endsection

@push('scripts')
<script>
// ═══════════════════════════════════════════════════════════════════
//  DATA — passed from PHP
// ═══════════════════════════════════════════════════════════════════
var DSH = {
    periodData:        @json($dashData['periodData']),
    drillTypeSummary:  @json($dashData['drillTypeSummary']),
    tableRows:         @json($dashData['tableRows']),
    personsByDivision: @json($dashData['personsByDivision']),
    companies:         @json($dashData['companies']),
    directorates:      @json($dashData['directorates']),

    // State (chart layer — affected by Period, Company, Directorate)
    currentPeriod:     '{{ $selectedPeriod }}',
    selectedCompanies: Object.keys(@json($dashData['companies'])),   // all by default
    selectedDirs:      Object.keys(@json($dashData['directorates'])), // all by default

    // State (table layer — affected by Division, Responsible Person)
    selectedDivision:  '',
    selectedPerson:    '',

    // Pagination
    currentPage: 1,
    rowsPerPage: 10,
};

// ═══════════════════════════════════════════════════════════════════
//  CHART LAYER — Period / Company / Directorate
// ═══════════════════════════════════════════════════════════════════

/**
 * Aggregate period stats for the currently selected companies.
 * Because the JSON stores per-company stats, we sum across selected ones.
 */
function getFilteredStats() {
    var raw = (DSH.periodData[DSH.currentPeriod] || {}).stats || [];

    // Keep only companies that have at least one selected directorate
    // AND are themselves selected.
    var activeCos = DSH.selectedCompanies.filter(function(co) {
        // Does this company have any selected directorate?
        var dirs = (DSH.companies[co] || {}).directorates || [];
        return dirs.some(function(d) { return DSH.selectedDirs.indexOf(d) >= 0; });
    });

    var filtered = raw.filter(function(s) { return activeCos.indexOf(s.label) >= 0; });

    var totalTarget = filtered.reduce(function(a, s) { return a + s.target; }, 0);
    var totalActual = filtered.reduce(function(a, s) { return a + s.actual; }, 0);
    var ratio = totalTarget > 0 ? Math.round((totalActual / totalTarget) * 100) : 0;

    return { target: totalTarget, actual: totalActual, ratio: ratio, stats: filtered };
}

function updateKPIs() {
    var s = getFilteredStats();
    document.getElementById('kpiTarget').textContent = s.target;
    document.getElementById('kpiActual').textContent = s.actual;
    document.getElementById('kpiRatio').textContent  = s.ratio + '%';
}

function updateBarChart() {
    var s   = getFilteredStats();
    var chart = document.getElementById('barChart');
    if (!chart) return;

    // Find the global max target to normalise bar heights
    var maxTarget = Math.max.apply(null, s.stats.map(function(x) { return x.target; }).concat([1]));

    var html = '';
    s.stats.forEach(function(stat) {
        var targetH = Math.round((stat.target / maxTarget) * 100);
        var actualH = stat.target > 0 ? Math.round((stat.actual / stat.target) * 100) : 0;
        actualH = Math.min(actualH, 100);

        html += '<div class="dsh-bar-group" data-company="' + escH(stat.label) + '">'
             +    '<div class="dsh-bar-group__bars">'
             +      '<div class="dsh-bar dsh-bar--target" style="height:' + targetH + '%;" title="Target: ' + stat.target + '">'
             +        '<span class="dsh-bar__label">' + stat.target + '</span>'
             +      '</div>'
             +      '<div class="dsh-bar dsh-bar--actual" style="height:' + actualH + '%;" title="Actual: ' + stat.actual + '">'
             +        '<span class="dsh-bar__label">' + stat.actual + '</span>'
             +      '</div>'
             +    '</div>'
             +    '<p class="dsh-bar-group__name">' + escH(stat.label) + '</p>'
             + '</div>';
    });

    if (s.stats.length === 0) {
        html = '<p class="dsh-no-data">No data for selected filters.</p>';
    }

    chart.innerHTML = html;
}

function updateDrillType() {
    var dt = DSH.drillTypeSummary[DSH.currentPeriod] || {};

    var ssCount = (dt.selfService || {}).count || 0;
    var schCount = (dt.scheduled  || {}).count || 0;
    var schPct   = (dt.scheduled  || {}).percentage || 0;

    var el = document.getElementById('selfServiceCount');
    if (el) el.textContent = ssCount;

    el = document.getElementById('scheduledCount');
    if (el) el.textContent = schCount;

    el = document.getElementById('scheduledPct');
    if (el) el.textContent = schPct + '%';

    // Update donut arc
    var fill = document.querySelector('#scheduledDonut .dsh-type-donut__fill');
    if (fill) {
        var dash = Math.round(schPct * 1.131);
        fill.setAttribute('stroke-dasharray', dash + ' 113.1');
    }
}

function refreshChartLayer() {
    updateKPIs();
    updateBarChart();
    updateDrillType();
}

// ── Period selector ───────────────────────────────────────────────
document.querySelectorAll('#topPeriodMenu .dsh-period-option').forEach(function(opt) {
    opt.addEventListener('click', function() {
        DSH.currentPeriod = this.dataset.period;
        document.getElementById('topPeriodText').textContent = DSH.currentPeriod;
        document.getElementById('topPeriodMenu').style.display = 'none';

        // Sync active state
        document.querySelectorAll('#topPeriodMenu .dsh-period-option').forEach(function(o) {
            o.classList.toggle('dsh-period-option--active', o.dataset.period === DSH.currentPeriod);
        });

        refreshChartLayer();
        // Also re-render table (period changes table data too)
        DSH.currentPage = 1;
        renderTable();
    });
});

document.getElementById('topPeriodBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    var m = document.getElementById('topPeriodMenu');
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
    document.getElementById('fyMenu').style.display = 'none';
});

// ── FY selector (display-only for now) ───────────────────────────
document.getElementById('fyBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    var m = document.getElementById('fyMenu');
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
    document.getElementById('topPeriodMenu').style.display = 'none';
});

document.querySelectorAll('#fyMenu .dsh-period-option').forEach(function(opt) {
    opt.addEventListener('click', function() {
        document.getElementById('fyText').textContent = this.dataset.fy;
        document.getElementById('fyMenu').style.display = 'none';
        document.querySelectorAll('#fyMenu .dsh-period-option').forEach(function(o) {
            o.classList.toggle('dsh-period-option--active', o.dataset.fy === opt.dataset.fy);
        });
    });
});

// ── Company checkboxes → filter Directorate pills → update charts ─
document.querySelectorAll('.company-cb').forEach(function(cb) {
    cb.addEventListener('change', syncCompanyFilter);
});

function syncCompanyFilter() {
    DSH.selectedCompanies = [];
    document.querySelectorAll('.company-cb:checked').forEach(function(cb) {
        DSH.selectedCompanies.push(cb.value);
    });

    // Show/hide directorate pills based on selected companies
    document.querySelectorAll('.dsh-dir-pill').forEach(function(pill) {
        var co = pill.dataset.company;
        var visible = DSH.selectedCompanies.indexOf(co) >= 0;
        pill.style.display = visible ? '' : 'none';
        if (!visible) {
            var input = pill.querySelector('.dir-cb');
            if (input) input.checked = false;
        }
    });

    syncDirFilter();
}

function syncDirFilter() {
    DSH.selectedDirs = [];
    document.querySelectorAll('.dir-cb:checked').forEach(function(cb) {
        DSH.selectedDirs.push(cb.value);
    });
    refreshChartLayer();
}

document.querySelectorAll('.dir-cb').forEach(function(cb) {
    cb.addEventListener('change', syncDirFilter);
});

// ═══════════════════════════════════════════════════════════════════
//  TABLE LAYER — Division / Responsible Person
//  Rule: MUST NOT affect chart layer, and vice versa.
// ═══════════════════════════════════════════════════════════════════

var divisionSelect = document.getElementById('divisionSelect');
var personSelect   = document.getElementById('personSelect');

divisionSelect.addEventListener('change', function() {
    DSH.selectedDivision = this.value;
    DSH.selectedPerson   = '';
    refreshPersonOptions();
    DSH.currentPage = 1;
    renderTable();
});

personSelect.addEventListener('change', function() {
    DSH.selectedPerson = this.value;
    DSH.currentPage    = 1;
    renderTable();
});

function refreshPersonOptions() {
    var div = DSH.selectedDivision;
    var persons = div ? (DSH.personsByDivision[div] || []) : getAllPersons();

    var html = '<option value="">All Persons</option>';
    persons.forEach(function(p) {
        html += '<option value="' + escH(p) + '">' + escH(p) + '</option>';
    });
    personSelect.innerHTML = html;
}

function getAllPersons() {
    var all = [];
    Object.values(DSH.personsByDivision).forEach(function(arr) {
        arr.forEach(function(p) { if (all.indexOf(p) < 0) all.push(p); });
    });
    return all;
}

function getFilteredRows() {
    var rows = (DSH.tableRows[DSH.currentPeriod] || []).slice();

    if (DSH.selectedDivision) {
        rows = rows.filter(function(r) { return r.division === DSH.selectedDivision; });
    }
    if (DSH.selectedPerson) {
        rows = rows.filter(function(r) { return r.responsiblePerson === DSH.selectedPerson; });
    }
    return rows;
}

function renderTable() {
    var rows     = getFilteredRows();
    var start    = (DSH.currentPage - 1) * DSH.rowsPerPage;
    var paged    = rows.slice(start, start + DSH.rowsPerPage);
    var totalPgs = Math.max(1, Math.ceil(rows.length / DSH.rowsPerPage));

    document.getElementById('tblPageNum').textContent = DSH.currentPage;
    document.getElementById('tblPrevBtn').disabled    = DSH.currentPage <= 1;
    document.getElementById('tblNextBtn').disabled    = DSH.currentPage >= totalPgs;

    var statusClass = { 'Passed': 'passed', 'Failed': 'failed', 'Scheduled': 'scheduled' };

    var html = '';
    paged.forEach(function(row) {
        var sc = statusClass[row.status] || 'unknown';
        html += '<tr>'
             + '<td>' + escH(row.npk)               + '</td>'
             + '<td>' + escH(row.name)               + '</td>'
             + '<td>' + escH(row.plant)              + '</td>'
             + '<td>' + escH(row.pcName)             + '</td>'
             + '<td>' + escH(row.drillType)          + '</td>'
             + '<td>' + escH(row.category)           + '</td>'
             + '<td>' + escH(row.date)               + '</td>'
             + '<td>' + escH(row.responseTime)       + '</td>'
             + '<td><span class="dsh-status-badge dsh-status-badge--' + sc + '">' + escH(row.status) + '</span></td>'
             + '</tr>';
    });

    if (paged.length === 0) {
        html = '<tr><td colspan="9" class="dsh-table__empty">No records match the selected filters.</td></tr>';
    }

    document.getElementById('drillTableBody').innerHTML = html;
}

document.getElementById('tblPrevBtn').addEventListener('click', function() {
    if (DSH.currentPage > 1) { DSH.currentPage--; renderTable(); }
});
document.getElementById('tblNextBtn').addEventListener('click', function() {
    var rows = getFilteredRows();
    var totalPgs = Math.max(1, Math.ceil(rows.length / DSH.rowsPerPage));
    if (DSH.currentPage < totalPgs) { DSH.currentPage++; renderTable(); }
});

// ═══════════════════════════════════════════════════════════════════
//  EXPORT
// ═══════════════════════════════════════════════════════════════════
document.getElementById('exportBtn').addEventListener('click', function() {
    var rows = getFilteredRows();
    var headers = ['NPK','NAME','PLANT','PC NAME','DRILL TYPE','CATEGORY','DATE','RESPONSE TIME','STATUS'];
    var lines   = [headers];
    rows.forEach(function(r) {
        lines.push([r.npk, r.name, r.plant, r.pcName, r.drillType, r.category, r.date, r.responseTime, r.status]);
    });
    var csv = lines.map(function(row) {
        return row.map(function(v) { return '"' + String(v).replace(/"/g,'""') + '"'; }).join(',');
    }).join('\r\n');
    var blob = new Blob([csv], { type: 'text/csv' });
    var a    = document.createElement('a');
    a.href     = URL.createObjectURL(blob);
    a.download = 'drill-dashboard-' + DSH.currentPeriod.replace(/\s+/g,'-') + '.csv';
    a.click();
});

// ═══════════════════════════════════════════════════════════════════
//  UTILITIES
// ═══════════════════════════════════════════════════════════════════
function escH(str) {
    return String(str || '')
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    ['topPeriodDropdown','fyDropdown'].forEach(function(id) {
        var wrap = document.getElementById(id);
        if (wrap && !wrap.contains(e.target)) {
            var menu = wrap.querySelector('.dsh-period-menu');
            if (menu) menu.style.display = 'none';
        }
    });
});

// ═══════════════════════════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════════════════════════
refreshPersonOptions();
renderTable();
// Chart is already rendered server-side; JS handles subsequent changes.
</script>
@endpush
