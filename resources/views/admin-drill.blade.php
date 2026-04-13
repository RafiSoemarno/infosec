@extends('layouts.app')

@php
    $menuItems = $menuData['items'] ?? [];

    $ss       = $selfService;
    $sh1      = $ss['first_half']  ?? [];
    $sh2      = $ss['second_half'] ?? [];
    $sched    = $scheduleDrill;

    // Duration options
    $durationOptions = [5, 10, 15, 20, 30, 45, 60];

    // Company options
    $companyOptions = [
        'PT. Denso Indonesia',
        'PT. Denso Manufacturing Indonesia',
        'PT. Denso Sales Indonesia',
        'PT. Hamaden Indonesia',
    ];

    // Plant options
    $plantOptions = ['Bekasi', 'Sunter', 'Fajar', 'DMIA2', 'DMIA1', 'SUNTER', 'BEKASI'];

    // Sort drills: upcoming first, past last
    $now    = \Carbon\Carbon::now();
    $upcoming = array_filter($drills, fn($d) => \Carbon\Carbon::parse($d['date'] . ' ' . $d['time'])->gte($now));
    $past     = array_filter($drills, fn($d) => \Carbon\Carbon::parse($d['date'] . ' ' . $d['time'])->lt($now));

    // Sort upcoming ascending, past descending
    usort($upcoming, fn($a, $b) => strcmp($a['date'] . $a['time'], $b['date'] . $b['time']));
    usort($past,     fn($a, $b) => strcmp($b['date'] . $b['time'], $a['date'] . $a['time']));

    $sortedDrills = array_merge(array_values($upcoming), array_values($past));

    $formatDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y') : '—';
    $formatTime = fn($t) => $t ? \Carbon\Carbon::createFromFormat('H:i', substr($t, 0, 5))->format('h:i A') : '—';
@endphp

@section('title', 'Drill Simulation — Admin')

@section('left_sidebar')
    <div class="d-flex flex-column h-100">
        <div class="brand-lockup">
            <img src="{{ asset('storage/Red_DENSO_Hires.png') }}" alt="DENSO - Crafting the Core" class="brand-lockup__logo">
        </div>

        <div class="mt-4">
            <x-sidebar-nav :items="$menuItems" :activeUrl="url()->current()" />
        </div>
    </div>
@endsection

@section('topbar')
    @php $authUser = session('auth_user'); @endphp
    <x-ui.header eyebrow="Admin" title="Drill Simulation" subtitle="Schedule and manage drills for all factories">
        <div class="topbar-profile">
            <div class="topbar-profile__text">
                <span class="topbar-profile__name">{{ $authUser['name'] ?? '' }}</span>
                <span class="topbar-profile__id">{{ $authUser['employeeId'] ?? '' }}</span>
            </div>
            <div class="topbar-profile__avatar">
                <img src="{{ asset('storage/icon_denso/icon_profile.png') }}" alt="Profile" class="topbar-profile__avatar-img">
            </div>
        </div>
    </x-ui.header>
@endsection

@section('content')

    {{-- ── Flash messages ──────────────────────────────────────── --}}
    @if (session('success_self_service'))
        <section class="content-span-12">
            <div class="da-alert da-alert--success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ session('success_self_service') }}
            </div>
        </section>
    @endif

    @if (session('success_schedule'))
        <section class="content-span-12">
            <div class="da-alert da-alert--success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ session('success_schedule') }}
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

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- ROW 1 — Self-Service Window                               --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <section class="content-span-12 fade-in-up">
        <form method="POST" action="{{ url('/admin/drill/self-service') }}">
            @csrf
            <div class="da-section-card">

                {{-- Section header + save --}}
                <div class="da-section-header">
                    <div class="da-section-header__left">
                        <span class="da-section-badge">SELF SERVICE</span>
                        <p class="da-section-desc">Active time window for self-directed drills</p>
                    </div>
                    <button type="submit" class="da-save-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Save
                    </button>
                </div>

                {{-- 1st Half & 2nd Half side by side --}}
                <div class="da-halves-grid">

                    {{-- ── 1st Half ────────────────────────────── --}}
                    <div class="da-half-panel da-half-panel--active">
                        <div class="da-half-header">
                            <span class="da-half-title">SELF SERVICE 1<sup>st</sup> Half</span>
                            <svg class="da-half-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                        </div>

                        <div class="da-fields-row">
                            <div class="da-field-group">
                                <label class="da-field-label">Start Date</label>
                                <input type="date" name="first_half_start_date" class="da-input"
                                       value="{{ $sh1['start_date'] ?? '' }}">
                            </div>
                            <div class="da-field-group">
                                <label class="da-field-label">Start Time</label>
                                <input type="time" name="first_half_start_time" class="da-input"
                                       value="{{ $sh1['start_time'] ?? '' }}">
                            </div>
                        </div>

                        <div class="da-fields-row">
                            <div class="da-field-group">
                                <label class="da-field-label">End Date</label>
                                <input type="date" name="first_half_end_date" class="da-input"
                                       value="{{ $sh1['end_date'] ?? '' }}">
                            </div>
                            <div class="da-field-group">
                                <label class="da-field-label">End Time</label>
                                <input type="time" name="first_half_end_time" class="da-input"
                                       value="{{ $sh1['end_time'] ?? '' }}">
                            </div>
                        </div>

                        <div class="da-field-group">
                            <label class="da-field-label">Duration (minutes)</label>
                            <select name="first_half_duration" class="da-select">
                                @foreach ($durationOptions as $opt)
                                    <option value="{{ $opt }}" {{ ($sh1['duration'] ?? 10) == $opt ? 'selected' : '' }}>
                                        {{ $opt }} Minutes
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- ── 2nd Half ────────────────────────────── --}}
                    <div class="da-half-panel">
                        <div class="da-half-header">
                            <span class="da-half-title">SELF SERVICE 2<sup>nd</sup> Half</span>
                            <svg class="da-half-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                        </div>

                        <div class="da-fields-row">
                            <div class="da-field-group">
                                <label class="da-field-label">Start Date</label>
                                <input type="date" name="second_half_start_date" class="da-input"
                                       value="{{ $sh2['start_date'] ?? '' }}">
                            </div>
                            <div class="da-field-group">
                                <label class="da-field-label">Start Time</label>
                                <input type="time" name="second_half_start_time" class="da-input"
                                       value="{{ $sh2['start_time'] ?? '' }}">
                            </div>
                        </div>

                        <div class="da-fields-row">
                            <div class="da-field-group">
                                <label class="da-field-label">End Date</label>
                                <input type="date" name="second_half_end_date" class="da-input"
                                       value="{{ $sh2['end_date'] ?? '' }}">
                            </div>
                            <div class="da-field-group">
                                <label class="da-field-label">End Time</label>
                                <input type="time" name="second_half_end_time" class="da-input"
                                       value="{{ $sh2['end_time'] ?? '' }}">
                            </div>
                        </div>

                        <div class="da-field-group">
                            <label class="da-field-label">Duration (minutes)</label>
                            <select name="second_half_duration" class="da-select">
                                @foreach ($durationOptions as $opt)
                                    <option value="{{ $opt }}" {{ ($sh2['duration'] ?? 10) == $opt ? 'selected' : '' }}>
                                        {{ $opt }} Minutes
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>{{-- .da-halves-grid --}}
            </div>{{-- .da-section-card --}}
        </form>
    </section>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- ROW 2 — Schedule Drill (factory-wide)                     --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <section class="content-span-12 fade-in-up">
        <form method="POST" action="{{ url('/admin/drill/schedule') }}">
            @csrf
            <div class="da-section-card da-section-card--brand">

                <div class="da-section-header">
                    <div class="da-section-header__left">
                        <span class="da-section-badge da-section-badge--white">SCHEDULE DRILL</span>
                        <p class="da-section-desc da-section-desc--light">Set the factory-wide drill schedule</p>
                    </div>
                    <button type="submit" class="da-save-btn da-save-btn--light">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Save
                    </button>
                </div>

                <div class="da-fields-row da-fields-row--wrap">
                    <div class="da-field-group">
                        <label class="da-field-label da-field-label--light">Company</label>
                        <select name="company" class="da-select da-select--brand">
                            @foreach ($companyOptions as $co)
                                @php $coShort = preg_replace('/PT\.\s*/', '', $co); $coShort = preg_replace('/\s+/', '', $coShort); $coShort = strtoupper(substr($coShort, 0, 4)); @endphp
                                <option value="{{ $coShort }}" {{ ($sched['company'] ?? '') === $coShort ? 'selected' : '' }}>
                                    {{ $coShort }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="da-field-group">
                        <label class="da-field-label da-field-label--light">Plant</label>
                        <select name="plant" class="da-select da-select--brand">
                            @foreach ($plantOptions as $pl)
                                <option value="{{ $pl }}" {{ ($sched['plant'] ?? '') === $pl ? 'selected' : '' }}>
                                    {{ $pl }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="da-field-group">
                        <label class="da-field-label da-field-label--light">Duration</label>
                        <select name="duration" class="da-select da-select--brand">
                            @foreach ($durationOptions as $opt)
                                <option value="{{ $opt }}" {{ ($sched['duration'] ?? 30) == $opt ? 'selected' : '' }}>
                                    {{ $opt }} Minutes
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="da-field-group">
                        <label class="da-field-label da-field-label--light">Date</label>
                        <input type="date" name="date" class="da-input da-input--brand"
                               value="{{ $sched['date'] ?? '' }}">
                    </div>

                    <div class="da-field-group">
                        <label class="da-field-label da-field-label--light">Time</label>
                        <input type="time" name="time" class="da-input da-input--brand"
                               value="{{ $sched['time'] ?? '' }}">
                    </div>
                </div>
            </div>
        </form>
    </section>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- ROW 3 — Drill list + Add form                             --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <section class="content-span-12 fade-in-up">
        <div class="da-section-card">

            <div class="da-section-header">
                <div class="da-section-header__left">
                    <span class="da-section-badge">ALL DRILLS</span>
                    <p class="da-section-desc">Past and scheduled drill entries — edit or delete upcoming ones</p>
                </div>
                <button class="da-save-btn" id="toggleAddDrill" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Drill
                </button>
            </div>

            {{-- ── Add drill form (hidden by default) ─────────── --}}
            <div class="da-add-form" id="addDrillForm" style="display:none;">
                <form method="POST" action="{{ url('/admin/drill/drills') }}">
                    @csrf
                    <div class="da-add-form__grid">
                        <div class="da-field-group">
                            <label class="da-field-label">Company</label>
                            <input type="text" name="company" class="da-input" placeholder="e.g. PT. Denso Indonesia"
                                   value="{{ old('company') }}">
                        </div>
                        <div class="da-field-group">
                            <label class="da-field-label">Plant</label>
                            <input type="text" name="plant" class="da-input" placeholder="e.g. BEKASI"
                                   value="{{ old('plant') }}">
                        </div>
                        <div class="da-field-group">
                            <label class="da-field-label">Date</label>
                            <input type="date" name="date" class="da-input" value="{{ old('date') }}">
                        </div>
                        <div class="da-field-group">
                            <label class="da-field-label">Time</label>
                            <input type="time" name="time" class="da-input" value="{{ old('time') }}">
                        </div>
                        <div class="da-field-group">
                            <label class="da-field-label">Duration (min)</label>
                            <select name="duration" class="da-select">
                                @foreach ($durationOptions as $opt)
                                    <option value="{{ $opt }}" {{ old('duration', 30) == $opt ? 'selected' : '' }}>
                                        {{ $opt }} min
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="da-field-group da-field-group--action">
                            <button type="submit" class="da-save-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Add
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ── Drill table ─────────────────────────────────── --}}
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
                                $drillDt   = \Carbon\Carbon::parse($drill['date'] . ' ' . $drill['time']);
                                $isPast    = $drillDt->lt($now);
                                $drillId   = $drill['id'];
                            @endphp
                            <tr class="{{ $isPast ? 'da-table__row--past' : '' }}" id="row-{{ $drillId }}">
                                {{-- View mode --}}
                                <td class="da-cell-view">{{ $drill['company'] }}</td>
                                <td class="da-cell-view">
                                    <span class="da-plant-badge">{{ $drill['plant'] }}</span>
                                </td>
                                <td class="da-cell-view">{{ $formatDate($drill['date']) }}</td>
                                <td class="da-cell-view">{{ $drill['time'] }}</td>
                                <td class="da-cell-view">{{ $drill['duration'] }} Minutes</td>

                                {{-- Edit mode (hidden) --}}
                                <td class="da-cell-edit" style="display:none;">
                                    <input type="text"  class="da-input da-input--sm" name="company"  value="{{ $drill['company'] }}">
                                </td>
                                <td class="da-cell-edit" style="display:none;">
                                    <input type="text"  class="da-input da-input--sm" name="plant"    value="{{ $drill['plant'] }}">
                                </td>
                                <td class="da-cell-edit" style="display:none;">
                                    <input type="date"  class="da-input da-input--sm" name="date"     value="{{ $drill['date'] }}">
                                </td>
                                <td class="da-cell-edit" style="display:none;">
                                    <input type="time"  class="da-input da-input--sm" name="time"     value="{{ $drill['time'] }}">
                                </td>
                                <td class="da-cell-edit" style="display:none;">
                                    <select class="da-select da-select--sm" name="duration">
                                        @foreach ($durationOptions as $opt)
                                            <option value="{{ $opt }}" {{ $drill['duration'] == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- Actions --}}
                                <td class="da-cell-actions">
                                    @if (!$isPast)
                                        {{-- Edit / Save toggle --}}
                                        <button type="button"
                                                class="da-icon-btn da-icon-btn--edit"
                                                title="Edit"
                                                onclick="toggleEdit({{ $drillId }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-.914.513l-3.414.853.853-3.414a2 2 0 01.513-.914z"/></svg>
                                        </button>

                                        {{-- Inline save form --}}
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

                                        <button type="button"
                                                class="da-icon-btn da-icon-btn--save"
                                                id="saveBtn-{{ $drillId }}"
                                                title="Save"
                                                style="display:none;"
                                                onclick="submitEdit({{ $drillId }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </button>

                                        {{-- Delete --}}
                                        <form method="POST"
                                              action="{{ url('/admin/drill/drills/' . $drillId . '/delete') }}"
                                              onsubmit="return confirm('Delete this drill entry?')"
                                              style="display:inline;">
                                            @csrf
                                            <button type="submit" class="da-icon-btn da-icon-btn--delete" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="da-past-label">Past</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="da-table__empty">No drills scheduled yet.</td>
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
// Toggle add-drill form visibility
document.getElementById('toggleAddDrill').addEventListener('click', function () {
    var form = document.getElementById('addDrillForm');
    var open = form.style.display !== 'none';
    form.style.display = open ? 'none' : 'block';
    this.textContent = open ? '+ Add Drill' : '× Cancel';
});

// Toggle inline row editing
function toggleEdit(id) {
    var row     = document.getElementById('row-' + id);
    var views   = row.querySelectorAll('.da-cell-view');
    var edits   = row.querySelectorAll('.da-cell-edit');
    var editBtn = row.querySelector('.da-icon-btn--edit');
    var saveBtn = document.getElementById('saveBtn-' + id);

    var editing = edits[0].style.display !== 'none';

    if (editing) {
        // Cancel: hide edit cells, show view cells
        edits.forEach(function (el) { el.style.display = 'none'; });
        views.forEach(function (el) { el.style.display = ''; });
        editBtn.style.display = '';
        saveBtn.style.display = 'none';
    } else {
        // Enter edit mode
        views.forEach(function (el) { el.style.display = 'none'; });
        edits.forEach(function (el) { el.style.display = ''; });
        editBtn.style.display = 'none';
        saveBtn.style.display = '';
    }
}

// Collect values from inline edit cells and submit hidden form
function submitEdit(id) {
    var row  = document.getElementById('row-' + id);
    var form = document.getElementById('editForm-' + id);

    var inputs = row.querySelectorAll('.da-cell-edit input, .da-cell-edit select');
    var fields  = ['company', 'plant', 'date', 'time', 'duration'];

    inputs.forEach(function (input, i) {
        var hidden = form.querySelector('.ef-' + fields[i]);
        if (hidden) hidden.value = input.value;
    });

    form.submit();
}
</script>
@endpush
