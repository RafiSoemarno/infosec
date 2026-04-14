@extends('layouts.app')

@php
    $menuItems = $menuData['items'] ?? [];

    $ss    = $selfService;
    $sh1   = $ss['first_half']  ?? [];
    $sh2   = $ss['second_half'] ?? [];
    $sched = $scheduleDrill;

    $durationOptions = [5, 10, 15, 20, 30, 45, 60];

    $companyOptions = ['DNIA', 'DMIA', 'DSIA', 'HDI'];
    $plantOptions   = ['Bekasi', 'Sunter', 'Fajar', 'DMIA2', 'DMIA1', 'SUNTER', 'BEKASI'];

    // Sort drills: upcoming first (asc), past last (desc)
    $now      = \Carbon\Carbon::now();
    $upcoming = array_values(array_filter($drills, fn($d) => \Carbon\Carbon::parse($d['date'] . ' ' . $d['time'])->gte($now)));
    $past     = array_values(array_filter($drills, fn($d) => \Carbon\Carbon::parse($d['date'] . ' ' . $d['time'])->lt($now)));
    usort($upcoming, fn($a, $b) => strcmp($a['date'] . $a['time'], $b['date'] . $b['time']));
    usort($past,     fn($a, $b) => strcmp($b['date'] . $b['time'], $a['date'] . $a['time']));
    $sortedDrills = array_merge($upcoming, $past);

    $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y') : '—';
@endphp

@section('title', 'Drill Simulation — Admin')

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

    {{-- ── Flash messages ─────────────────────────────────────────── --}}
    @if (session('success_self_service') || session('success_schedule'))
        <section class="content-span-12">
            <div class="da-alert da-alert--success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ session('success_self_service') ?? session('success_schedule') }}
            </div>
        </section>
    @endif
    @if ($errors->any())
        <section class="content-span-12">
            <div class="da-alert da-alert--error">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
                {{ $errors->first() }}
            </div>
        </section>
    @endif

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ROW 1 — Self-Service Windows                                   --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <section class="content-span-12 fade-in-up">
        <form method="POST" action="{{ url('/admin/drill/self-service') }}" id="selfServiceForm">
            @csrf
            <div class="da-branded-card">

                {{-- Card header row: title + save icon --}}
                <div class="da-card-topbar">
                    <span class="da-card-topbar__title">SELF SERVICE</span>
                    <button type="submit" class="da-icon-circle-btn" title="Save self-service settings">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>

                {{-- All fields in one row --}}
                <div class="da-schedule-fields-row">

                    <div class="da-dt-group">
                        <span class="da-dt-label">Start Date :</span>
                        <div class="da-dt-pill">
                            <input type="date" id="startDate" class="da-date-input" value="">
                            <svg class="da-cal-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                        </div>
                    </div>

                    <div class="da-dt-group">
                        <span class="da-dt-label">Start Time :</span>
                        <div class="da-dropdown-pill da-dropdown-pill--time">
                            <input type="time" id="startTime" class="da-pill-select" value="">
                        </div>
                    </div>

                    <div class="da-dt-group">
                        <span class="da-dt-label">End Date :</span>
                        <div class="da-dt-pill">
                            <input type="date" id="endDate" class="da-date-input" value="">
                            <svg class="da-cal-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                        </div>
                    </div>

                    <div class="da-dt-group">
                        <span class="da-dt-label">End Time :</span>
                        <div class="da-dropdown-pill da-dropdown-pill--time">
                            <input type="time" id="endTime" class="da-pill-select" value="">
                        </div>
                    </div>

                    <div class="da-dt-group">
                        <span class="da-dt-label">Duration :</span>
                        <div class="da-dropdown-pill">
                            <select id="duration" class="da-pill-select">
                                @foreach ($durationOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt }} Minutes</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="da-dt-group">
                        <span class="da-dt-label">Period :</span>
                        <div class="da-dropdown-pill">
                            <select id="halfSelector" name="half" class="da-pill-select" onchange="switchHalf(this.value)">
                                <option value="first_half">1st Half</option>
                                <option value="second_half">2nd Half</option>
                            </select>
                        </div>
                    </div>

                    <div class="da-dt-group">
                        <span class="da-dt-label">Target :</span>
                        <div class="da-dt-pill">
                            <input type="number" id="target" class="da-date-input" placeholder="total users..." min="0"
                                   value="{{ $sh1['target'] ?? '' }}">
                        </div>
                    </div>

                </div>

                {{-- Hidden fields for form submission --}}
                <input type="hidden" name="first_half_start_date" id="hf_start_date" value="">
                <input type="hidden" name="first_half_end_date" id="hf_end_date" value="">
                <input type="hidden" name="first_half_start_time" id="hf_start_time" value="">
                <input type="hidden" name="first_half_end_time" id="hf_end_time" value="">
                <input type="hidden" name="first_half_duration" id="hf_duration" value="">
                <input type="hidden" name="first_half_target" id="hf_target" value="">

                <input type="hidden" name="second_half_start_date" id="sh_start_date" value="">
                <input type="hidden" name="second_half_end_date" id="sh_end_date" value="">
                <input type="hidden" name="second_half_start_time" id="sh_start_time" value="">
                <input type="hidden" name="second_half_end_time" id="sh_end_time" value="">
                <input type="hidden" name="second_half_duration" id="sh_duration" value="">
                <input type="hidden" name="second_half_target" id="sh_target" value="">

            </div>
        </form>
    </section>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ROW 2 — Schedule Drill (factory-wide)                         --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <section class="content-span-12 fade-in-up">
        <form method="POST" action="{{ url('/admin/drill/schedule') }}">
            @csrf
            <div class="da-branded-card">

                <div class="da-card-topbar">
                    <span class="da-card-topbar__title">SCHEDULE DRILL</span>
                    <button type="submit" class="da-icon-circle-btn" title="Save schedule drill">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>

                <div class="da-schedule-fields-row">

                    <div class="da-dt-group">
                        <span class="da-dt-label">Company :</span>
                        <div class="da-dropdown-pill">
                            <select name="company" class="da-pill-select">
                                @foreach ($companyOptions as $co)
                                    <option value="{{ $co }}" {{ ($sched['company'] ?? '') === $co ? 'selected' : '' }}>{{ $co }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="da-dt-group">
                        <span class="da-dt-label">Plant :</span>
                        <div class="da-dropdown-pill">
                            <select name="plant" class="da-pill-select">
                                @foreach ($plantOptions as $pl)
                                    <option value="{{ $pl }}" {{ ($sched['plant'] ?? '') === $pl ? 'selected' : '' }}>{{ $pl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="da-dt-group">
                        <span class="da-dt-label">Duration :</span>
                        <div class="da-dropdown-pill">
                            <select name="duration" class="da-pill-select">
                                @foreach ($durationOptions as $opt)
                                    <option value="{{ $opt }}" {{ ($sched['duration'] ?? 30) == $opt ? 'selected' : '' }}>{{ $opt }} Minutes</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="da-dt-group">
                        <span class="da-dt-label">Date :</span>
                        <div class="da-dt-pill">
                            <input type="date" name="date" class="da-date-input"
                                   value="{{ $sched['date'] ?? '' }}">
                            <svg class="da-cal-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                        </div>
                    </div>

                    <div class="da-dt-group">
                        <span class="da-dt-label">Time :</span>
                        <div class="da-dropdown-pill da-dropdown-pill--time">
                            <input type="time" name="time" class="da-pill-select"
                                   value="{{ $sched['time'] ?? '' }}">
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </section>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ROW 3 — Drill list                                            --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <section class="content-span-12 fade-in-up">
        <div class="da-plain-card">

            <div class="da-table-wrap">
                <table class="da-table">
                    <thead>
                        <tr>
                            <th>COMPANY</th>
                            <th>PLANT</th>
                            <th>DATE</th>
                            <th>TIME</th>
                            <th>DURATION</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sortedDrills as $drill)
                            @php
                                $drillDt = \Carbon\Carbon::parse($drill['date'] . ' ' . $drill['time']);
                                $isPast  = $drillDt->lt($now);
                                $drillId = $drill['id'];
                            @endphp
                            <tr id="row-{{ $drillId }}" class="{{ $isPast ? 'da-table__row--past' : '' }}">
                                {{-- View cells --}}
                                <td class="da-cell-view">{{ $drill['company'] }}</td>
                                <td class="da-cell-view"><span class="da-plant-badge">{{ $drill['plant'] }}</span></td>
                                <td class="da-cell-view">{{ $fmtDate($drill['date']) }}</td>
                                <td class="da-cell-view">{{ $drill['time'] }}</td>
                                <td class="da-cell-view">{{ $drill['duration'] }} Minutes</td>

                                {{-- Edit cells (hidden) --}}
                                <td class="da-cell-edit" style="display:none;"><input type="text"  class="da-input da-input--sm" value="{{ $drill['company'] }}"></td>
                                <td class="da-cell-edit" style="display:none;"><input type="text"  class="da-input da-input--sm" value="{{ $drill['plant'] }}"></td>
                                <td class="da-cell-edit" style="display:none;"><input type="date"  class="da-input da-input--sm" value="{{ $drill['date'] }}"></td>
                                <td class="da-cell-edit" style="display:none;"><input type="time"  class="da-input da-input--sm" value="{{ $drill['time'] }}"></td>
                                <td class="da-cell-edit" style="display:none;">
                                    <select class="da-input da-input--sm">
                                        @foreach ($durationOptions as $opt)
                                            <option value="{{ $opt }}" {{ $drill['duration'] == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- Actions --}}
                                <td class="da-cell-actions">
                                    @if (!$isPast)
                                        {{-- Hidden submit form for edit --}}
                                        <form id="editForm-{{ $drillId }}"
                                              method="POST"
                                              action="{{ url('/admin/drill/drills/' . $drillId) }}"
                                              style="display:none;">
                                            @csrf
                                            <input type="hidden" name="company"  class="ef-company"  value="{{ $drill['company'] }}">
                                            <input type="hidden" name="plant"    class="ef-plant"    value="{{ $drill['plant'] }}">
                                            <input type="hidden" name="date"     class="ef-date"     value="{{ $drill['date'] }}">
                                            <input type="hidden" name="time"     class="ef-time"     value="{{ $drill['time'] }}">
                                            <input type="hidden" name="duration" class="ef-duration" value="{{ $drill['duration'] }}">
                                        </form>

                                        <button type="button" class="da-tbl-btn da-tbl-btn--edit"
                                                id="editBtn-{{ $drillId }}"
                                                title="Edit" onclick="toggleEdit({{ $drillId }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-.914.513l-3.414.853.853-3.414a2 2 0 01.513-.914z"/></svg>
                                        </button>

                                        <button type="button" class="da-tbl-btn da-tbl-btn--save"
                                                id="saveBtn-{{ $drillId }}"
                                                title="Save" style="display:none;"
                                                onclick="submitEdit({{ $drillId }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </button>

                                        <form method="POST"
                                              action="{{ url('/admin/drill/drills/' . $drillId . '/delete') }}"
                                              onsubmit="return confirm('Delete this drill entry?')"
                                              style="display:inline;">
                                            @csrf
                                            <button type="submit" class="da-tbl-btn da-tbl-btn--delete" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="da-past-label">Past</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="da-table__empty">No drills scheduled yet. Use the Schedule Drill form above to add one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
<script>
// Self-service data management
var selfServiceData = {
    first_half: {
        start_date: "{{ $sh1['start_date'] ?? '' }}",
        end_date: "{{ $sh1['end_date'] ?? '' }}",
        start_time: "{{ $sh1['start_time'] ?? '' }}",
        end_time: "{{ $sh1['end_time'] ?? '' }}",
        duration: "{{ $sh1['duration'] ?? 10 }}",
        target: "{{ $sh1['target'] ?? '' }}"
    },
    second_half: {
        start_date: "{{ $sh2['start_date'] ?? '' }}",
        end_date: "{{ $sh2['end_date'] ?? '' }}",
        start_time: "{{ $sh2['start_time'] ?? '' }}",
        end_time: "{{ $sh2['end_time'] ?? '' }}",
        duration: "{{ $sh2['duration'] ?? 10 }}",
        target: "{{ $sh2['target'] ?? '' }}"
    }
};

function switchHalf(half) {
    var data = selfServiceData[half];
    document.getElementById('startDate').value = data.start_date;
    document.getElementById('endDate').value = data.end_date;
    document.getElementById('startTime').value = data.start_time;
    document.getElementById('endTime').value = data.end_time;
    document.getElementById('duration').value = data.duration;
    document.getElementById('target').value = data.target;
}

function prepareSubmit(event) {
    event.preventDefault();

    var half = document.getElementById('halfSelector').value;
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;
    var startTime = document.getElementById('startTime').value;
    var endTime = document.getElementById('endTime').value;
    var duration = document.getElementById('duration').value;
    var target = document.getElementById('target').value;

    // Clear all hidden fields
    document.getElementById('hf_start_date').value = '';
    document.getElementById('hf_end_date').value = '';
    document.getElementById('hf_start_time').value = '';
    document.getElementById('hf_end_time').value = '';
    document.getElementById('hf_duration').value = '';
    document.getElementById('hf_target').value = '';
    document.getElementById('sh_start_date').value = '';
    document.getElementById('sh_end_date').value = '';
    document.getElementById('sh_start_time').value = '';
    document.getElementById('sh_end_time').value = '';
    document.getElementById('sh_duration').value = '';
    document.getElementById('sh_target').value = '';

    // Fill only the selected half's fields
    if (half === 'first_half') {
        document.getElementById('hf_start_date').value = startDate;
        document.getElementById('hf_end_date').value = endDate;
        document.getElementById('hf_start_time').value = startTime;
        document.getElementById('hf_end_time').value = endTime;
        document.getElementById('hf_duration').value = duration;
        document.getElementById('hf_target').value = target;
    } else {
        document.getElementById('sh_start_date').value = startDate;
        document.getElementById('sh_end_date').value = endDate;
        document.getElementById('sh_start_time').value = startTime;
        document.getElementById('sh_end_time').value = endTime;
        document.getElementById('sh_duration').value = duration;
        document.getElementById('sh_target').value = target;
    }

    // Submit the form
    document.getElementById('selfServiceForm').submit();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('selfServiceForm');
    form.addEventListener('submit', prepareSubmit);
    switchHalf('first_half');
});

function toggleEdit(id) {
    var row     = document.getElementById('row-' + id);
    var views   = row.querySelectorAll('.da-cell-view');
    var edits   = row.querySelectorAll('.da-cell-edit');
    var editBtn = document.getElementById('editBtn-' + id);
    var saveBtn = document.getElementById('saveBtn-' + id);
    var editing = edits[0].style.display !== 'none';

    if (editing) {
        edits.forEach(function(el){ el.style.display = 'none'; });
        views.forEach(function(el){ el.style.display = ''; });
        editBtn.style.display = '';
        saveBtn.style.display = 'none';
    } else {
        views.forEach(function(el){ el.style.display = 'none'; });
        edits.forEach(function(el){ el.style.display = ''; });
        editBtn.style.display = 'none';
        saveBtn.style.display = '';
    }
}

function submitEdit(id) {
    var row    = document.getElementById('row-' + id);
    var form   = document.getElementById('editForm-' + id);
    var inputs = row.querySelectorAll('.da-cell-edit input, .da-cell-edit select');
    var fields = ['company', 'plant', 'date', 'time', 'duration'];
    inputs.forEach(function(input, i) {
        var h = form.querySelector('.ef-' + fields[i]);
        if (h) h.value = input.value;
    });
    form.submit();
}
</script>
@endpush
