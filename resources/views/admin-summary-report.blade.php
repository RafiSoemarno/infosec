@extends('layouts.app')

@php
    $menuItems      = $menuData['items'] ?? [];
    $periods        = $summaryData['periods'] ?? ['1st Half', '2nd Half'];
    $selectedPeriod = $summaryData['selectedPeriod'] ?? '1st Half';
    $drillDates     = $summaryData['drillDates'] ?? [];
    $selectedDate   = $summaryData['selectedDate'] ?? '';
    $availableTimes = $summaryData['availableTimes'] ?? [];
    $selectedTime   = $summaryData['selectedTime'] ?? '';
    $allRows        = $summaryData['rows'] ?? [];

    $rows = $allRows[$selectedPeriod] ?? [];

    $totalPc   = array_sum(array_column($rows, 'noPc'));
    $totalOk   = array_sum(array_column($rows, 'noPcOk'));
    $totalNg   = array_sum(array_column($rows, 'noPcNg'));
@endphp

@section('title', 'Summary Report Drill')

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
    @php $authUser = session('auth_user'); @endphp
    <x-ui.header eyebrow="Drill" title="Self-Service Cyber Attack" subtitle="">
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

<section class="content-span-12 fade-in-up">
    <div class="sr-card">

        {{-- ── Header ───────────────────────────────────────────────────── --}}
        <div class="sr-header">
            <h2 class="sr-title">Summary Report Drill</h2>
        </div>

        {{-- ── Filters row ──────────────────────────────────────────────── --}}
        <div class="sr-filters" id="srFilters">

            {{-- PERIOD --}}
            <div class="sr-filter-group">
                <div class="sr-filter-label">PERIOD</div>
                <div class="sr-period-dropdown" id="periodDropdown">
                    <button class="sr-period-btn" id="periodBtn" type="button" onclick="togglePeriodDropdown()">
                        <span id="periodBtnText">{{ $selectedPeriod }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="sr-period-menu" id="periodMenu" style="display:none;">
                        @foreach ($periods as $period)
                            <div class="sr-period-option {{ $period === $selectedPeriod ? 'sr-period-option--active' : '' }}"
                                 onclick="selectPeriod('{{ $period }}')">
                                {{ $period }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- DATE --}}
            <div class="sr-filter-group">
                <div class="sr-filter-label">DATE</div>
                <div class="sr-date-picker">
                    <span class="sr-date-display" id="srDateDisplay">
                        {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('n/j/Y') : '—' }}
                    </span>
                    <input type="date" id="srDateInput" class="sr-date-hidden"
                           value="{{ $selectedDate }}" onchange="onDateChange(this.value)">
                    <button type="button" class="sr-cal-btn" onclick="document.getElementById('srDateInput').showPicker ? document.getElementById('srDateInput').showPicker() : document.getElementById('srDateInput').click()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </button>
                </div>
            </div>

            {{-- TIME --}}
            <div class="sr-filter-group">
                <div class="sr-filter-label">TIME</div>
                <div class="sr-time-dropdown" id="timeDropdown">
                    <button class="sr-time-btn" id="timeBtn" type="button" onclick="toggleTimeDropdown()">
                        <span id="timeBtnText">{{ $selectedTime }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="sr-time-menu" id="timeMenu" style="display:none;">
                        @foreach ($availableTimes as $t)
                            <div class="sr-time-option {{ $t === $selectedTime ? 'sr-time-option--active' : '' }}"
                                 data-time="{{ $t }}" onclick="selectTime('{{ $t }}')">
                                @if ($t === $selectedTime)
                                    <svg class="sr-time-check" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @endif
                                {{ $t }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Export Button --}}
            <div class="sr-filter-group sr-filter-group--export">
                <button class="sr-export-btn" type="button" onclick="exportReport()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 2V5h2v6h1.17L12 13.17 9.83 11H11zm-6 7h14v2H5v-2z"/></svg>
                    Export Report
                </button>
            </div>

        </div>

        {{-- ── Table ────────────────────────────────────────────────────── --}}
        <div class="sr-table-wrap">
            <table class="sr-table" id="srTable">
                <thead>
                    <tr>
                        <th>COMPANY</th>
                        <th>DIVISION</th>
                        <th>No. PC</th>
                        <th class="sr-th--ok">No. PC [OK]</th>
                        <th class="sr-th--ng">No. PC [NG]</th>
                        <th>RESPONSIBLE PERSON</th>
                        <th class="sr-th--ng">REMARK PC [NG]</th>
                    </tr>
                </thead>
                <tbody id="srTableBody">
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row['company'] }}</td>
                            <td>{{ $row['division'] }}</td>
                            <td>{{ $row['noPc'] }}</td>
                            <td class="sr-td--ok">{{ $row['noPcOk'] }}</td>
                            <td class="sr-td--ng">{{ $row['noPcNg'] ?: '' }}</td>
                            <td>{{ $row['responsiblePerson'] }}</td>
                            <td class="sr-td--remark">
                                @foreach ($row['remarkPcNg'] as $remark)
                                    <span class="sr-remark">{{ $remark }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="sr-table__empty">No data available for this period.</td>
                        </tr>
                    @endforelse
                    @if (count($rows) > 0)
                        <tr class="sr-tr--total">
                            <td colspan="2" class="sr-td--total-label">TOTAL</td>
                            <td>{{ $totalPc }}</td>
                            <td class="sr-td--ok">{{ $totalOk }}</td>
                            <td class="sr-td--ng">{{ $totalNg ?: '' }}</td>
                            <td colspan="2"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- ── Pagination ───────────────────────────────────────────────── --}}
        <div class="sr-pagination">
            <button class="sr-page-btn" id="prevBtn" onclick="changePage(-1)" disabled>Previous</button>
            <button class="sr-page-btn sr-page-btn--current" id="pageNum">1</button>
            <button class="sr-page-btn" id="nextBtn" onclick="changePage(1)">Next</button>
        </div>

    </div>
</section>

@endsection

@push('scripts')
<script>
// All rows data per period (passed from PHP)
var allRowsData = @json($summaryData['rows'] ?? []);
var currentPeriod = '{{ $selectedPeriod }}';
var currentDate = '{{ $selectedDate }}';
var currentTime = '{{ $selectedTime }}';
var currentPage = 1;
var rowsPerPage = 10;

// ── Period dropdown ───────────────────────────────────────────────────────
function togglePeriodDropdown() {
    var menu = document.getElementById('periodMenu');
    var timeMenu = document.getElementById('timeMenu');
    timeMenu.style.display = 'none';
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function selectPeriod(period) {
    currentPeriod = period;
    document.getElementById('periodBtnText').textContent = period;
    document.getElementById('periodMenu').style.display = 'none';

    // Update active state
    document.querySelectorAll('.sr-period-option').forEach(function(el) {
        el.classList.toggle('sr-period-option--active', el.textContent.trim() === period);
    });

    currentPage = 1;
    renderTable();
}

// ── Date picker ───────────────────────────────────────────────────────────
function onDateChange(val) {
    currentDate = val;
    if (val) {
        var d = new Date(val + 'T00:00:00');
        var formatted = (d.getMonth()+1) + '/' + d.getDate() + '/' + d.getFullYear();
        document.getElementById('srDateDisplay').textContent = formatted;
    }
    currentPage = 1;
    renderTable();
}

// ── Time dropdown ─────────────────────────────────────────────────────────
function toggleTimeDropdown() {
    var menu = document.getElementById('timeMenu');
    var periodMenu = document.getElementById('periodMenu');
    periodMenu.style.display = 'none';
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function selectTime(time) {
    currentTime = time;
    document.getElementById('timeBtnText').textContent = time;
    document.getElementById('timeMenu').style.display = 'none';

    // Update active state & checkmarks
    document.querySelectorAll('.sr-time-option').forEach(function(el) {
        var isActive = el.dataset.time === time;
        el.classList.toggle('sr-time-option--active', isActive);
        var check = el.querySelector('.sr-time-check');
        if (isActive && !check) {
            var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('class', 'sr-time-check');
            svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svg.setAttribute('width', '14');
            svg.setAttribute('height', '14');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('viewBox', '0 0 24 24');
            svg.setAttribute('stroke', 'currentColor');
            svg.setAttribute('stroke-width', '2.5');
            var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('d', 'M5 13l4 4L19 7');
            svg.appendChild(path);
            el.insertBefore(svg, el.firstChild);
        } else if (!isActive && check) {
            check.remove();
        }
    });

    currentPage = 1;
    renderTable();
}

// ── Table rendering ───────────────────────────────────────────────────────
function renderTable() {
    var rows = allRowsData[currentPeriod] || [];
    var tbody = document.getElementById('srTableBody');

    // Calculate totals
    var totalPc = 0, totalOk = 0, totalNg = 0;
    rows.forEach(function(r) { totalPc += r.noPc; totalOk += r.noPcOk; totalNg += r.noPcNg; });

    // Pagination
    var start = (currentPage - 1) * rowsPerPage;
    var paged = rows.slice(start, start + rowsPerPage);
    var totalPages = Math.max(1, Math.ceil(rows.length / rowsPerPage));

    document.getElementById('pageNum').textContent = currentPage;
    document.getElementById('prevBtn').disabled = currentPage <= 1;
    document.getElementById('nextBtn').disabled = currentPage >= totalPages;

    var html = '';
    paged.forEach(function(row) {
        var remarks = (row.remarkPcNg || []).map(function(r) {
            return '<span class="sr-remark">' + escHtml(r) + '</span>';
        }).join('');
        html += '<tr>'
            + '<td>' + escHtml(row.company) + '</td>'
            + '<td>' + escHtml(row.division) + '</td>'
            + '<td>' + row.noPc + '</td>'
            + '<td class="sr-td--ok">' + row.noPcOk + '</td>'
            + '<td class="sr-td--ng">' + (row.noPcNg || '') + '</td>'
            + '<td>' + escHtml(row.responsiblePerson) + '</td>'
            + '<td class="sr-td--remark">' + remarks + '</td>'
            + '</tr>';
    });

    if (rows.length === 0) {
        html = '<tr><td colspan="7" class="sr-table__empty">No data available for this period.</td></tr>';
    } else {
        html += '<tr class="sr-tr--total">'
            + '<td colspan="2" class="sr-td--total-label">TOTAL</td>'
            + '<td>' + totalPc + '</td>'
            + '<td class="sr-td--ok">' + totalOk + '</td>'
            + '<td class="sr-td--ng">' + (totalNg || '') + '</td>'
            + '<td colspan="2"></td>'
            + '</tr>';
    }

    tbody.innerHTML = html;
}

function changePage(dir) {
    var rows = allRowsData[currentPeriod] || [];
    var totalPages = Math.max(1, Math.ceil(rows.length / rowsPerPage));
    currentPage = Math.min(Math.max(1, currentPage + dir), totalPages);
    renderTable();
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Export ────────────────────────────────────────────────────────────────
function exportReport() {
    var rows = allRowsData[currentPeriod] || [];
    var lines = [['COMPANY','DIVISION','No. PC','No. PC [OK]','No. PC [NG]','RESPONSIBLE PERSON','REMARK PC [NG]']];
    var totalPc = 0, totalOk = 0, totalNg = 0;
    rows.forEach(function(r) {
        lines.push([r.company, r.division, r.noPc, r.noPcOk, r.noPcNg, r.responsiblePerson, (r.remarkPcNg||[]).join('; ')]);
        totalPc += r.noPc; totalOk += r.noPcOk; totalNg += r.noPcNg;
    });
    lines.push(['TOTAL','', totalPc, totalOk, totalNg, '', '']);
    var csv = lines.map(function(row) {
        return row.map(function(v) { return '"' + String(v).replace(/"/g,'""') + '"'; }).join(',');
    }).join('\r\n');
    var blob = new Blob([csv], { type: 'text/csv' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'summary-report-' + currentPeriod.replace(/\s+/g,'-') + '-' + currentDate + '.csv';
    a.click();
}

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('#periodDropdown')) {
        document.getElementById('periodMenu').style.display = 'none';
    }
    if (!e.target.closest('#timeDropdown')) {
        document.getElementById('timeMenu').style.display = 'none';
    }
});
</script>
@endpush
