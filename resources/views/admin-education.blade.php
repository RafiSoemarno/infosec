@extends('layouts.app')

@php
    $menuItems = $menuData['items'] ?? [];
@endphp

@section('title', 'Education — Admin')

@section('left_sidebar')
    <div class="d-flex flex-column h-100">
        <div class="brand-lockup">
            <img src="{{ asset('Red_DENSO_Hires.png') }}" alt="DENSO - Crafting the Core" class="brand-lockup__logo">
        </div>

        <div class="mt-4">
            <x-sidebar-nav :items="$menuItems" :activeUrl="url()->current()" />
        </div>

        <div class="edu-nav mt-auto">
            {{-- Prev/Next placeholders to keep layout consistent --}}
            <button class="app-btn-secondary" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Previous Video
            </button>
            <button class="app-btn-primary mt-2" disabled>
                Next Video
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
@endsection

@section('topbar')
    <x-ui.header eyebrow="Control Center" title="Education" subtitle="Manage education materials for all users">
        <div>
            <div>{{ now()->translatedFormat('D, d M Y') }}</div>
        </div>
    </x-ui.header>
@endsection

@section('content')
    {{-- Flash messages --}}
    @if (session('success'))
        <section class="content-span-12">
            <div class="edu-admin-alert edu-admin-alert--success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        </section>
    @endif

    @if ($errors->any())
        <section class="content-span-12">
            <div class="edu-admin-alert edu-admin-alert--error">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
                {{ $errors->first() }}
            </div>
        </section>
    @endif

    {{-- Upload panel --}}
    <section class="content-span-12 fade-in-up">
        <div class="panel-card edu-admin-upload-panel">
            {{-- Title input --}}
            <form method="POST" action="{{ url('/education/materials') }}" enctype="multipart/form-data" id="uploadForm">
                @csrf

                {{-- Drop zone --}}
                <div
                    class="edu-admin-dropzone"
                    id="dropzone"
                    ondragover="handleDragOver(event)"
                    ondragleave="handleDragLeave(event)"
                    ondrop="handleDrop(event)"
                >
                    <input
                        type="file"
                        name="files[]"
                        id="fileInput"
                        class="edu-admin-dropzone__input"
                        accept=".mp4,.webm,.ogv,.mov,.avi,.pdf,.ppt,.pptx,.doc,.docx"
                        multiple
                    >

                    <div class="edu-admin-dropzone__content" id="dropzoneContent">
                        {{-- Cloud upload illustration --}}
                        <div class="edu-admin-dropzone__icon">
                            <svg viewBox="0 0 120 90" fill="none" xmlns="http://www.w3.org/2000/svg" class="edu-admin-cloud-svg">
                                <!-- Cloud body -->
                                <ellipse cx="45" cy="52" rx="32" ry="22" fill="url(#cloudGrad)"/>
                                <ellipse cx="68" cy="50" rx="24" ry="18" fill="url(#cloudGrad2)"/>
                                <ellipse cx="30" cy="58" rx="20" ry="14" fill="url(#cloudGrad3)"/>
                                <!-- Arrow shaft -->
                                <rect x="56" y="38" width="8" height="26" rx="4" fill="url(#arrowGrad)"/>
                                <!-- Arrow head -->
                                <polygon points="60,18 45,40 75,40" fill="url(#arrowGrad)"/>
                                <defs>
                                    <linearGradient id="cloudGrad" x1="13" y1="30" x2="77" y2="74" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="#5ee7ff"/>
                                        <stop offset="100%" stop-color="#2278d9"/>
                                    </linearGradient>
                                    <linearGradient id="cloudGrad2" x1="44" y1="32" x2="92" y2="68" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="#7dd6f8"/>
                                        <stop offset="100%" stop-color="#3a8fe8"/>
                                    </linearGradient>
                                    <linearGradient id="cloudGrad3" x1="10" y1="44" x2="50" y2="72" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="#4ecff5"/>
                                        <stop offset="100%" stop-color="#1a5cb8"/>
                                    </linearGradient>
                                    <linearGradient id="arrowGrad" x1="60" y1="14" x2="60" y2="64" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="#b8e6ff"/>
                                        <stop offset="100%" stop-color="#6ec6f5"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>

                        <p class="edu-admin-dropzone__hint">Drop your content here or</p>

                        <div class="edu-admin-dropzone__actions">
                            <label for="fileInput" class="edu-admin-btn-outline" role="button" tabindex="0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Add files
                            </label>
                        </div>

                        <p class="edu-admin-dropzone__formats">Supported: MP4, WebM, MOV, AVI, PDF, PPT, PPTX, DOC, DOCX &mdash; max {{ 500 }} MB</p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="edu-admin-upload-footer">
                    <button type="submit" class="app-btn-primary edu-admin-submit-btn" id="submitBtn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Save Material
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('right_sidebar')
    <div class="d-flex flex-column h-100 gap-3">
        <div class="edu-search">
            <svg class="edu-search__icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
            <input class="edu-search__input" type="text" placeholder="Search education ..." id="adminSearchInput">
        </div>

        <div class="edu-list-header">
            <p class="edu-list-header__title">Education Material</p>
            <p class="edu-list-header__note">Track All Ongoing education material</p>
        </div>

        <div class="edu-video-list edu-admin-material-list" id="adminMaterialList">
            {{-- Pending (staged) files — added by JS before save --}}
            <div id="pendingList"></div>

            @forelse ($materials as $material)
                <div class="edu-admin-material-item" data-title="{{ strtolower($material['title']) }}">
                    {{-- Dot is always red/active because every stored material is live --}}
                    <span class="edu-video-item__dot edu-video-item__dot--watched"></span>
                    <div class="edu-admin-material-item__body">
                        <span class="edu-video-item__title">{{ $material['title'] }}</span>
                        {{-- original_filename and created_at come directly from the JSON store --}}
                        @if (!empty($material['original_filename']))
                            <span class="edu-admin-material-item__meta">{{ $material['original_filename'] }}</span>
                        @endif
                        @if (!empty($material['created_at']))
                            <span class="edu-admin-material-item__meta">
                                {{ \Carbon\Carbon::parse($material['created_at'])->format('d M Y') }}
                                &bull; by {{ $material['uploaded_by'] ?? 'admin' }}
                            </span>
                        @endif
                    </div>
                    <form method="POST" action="{{ url('/education/materials/' . $material['id']) }}"
                          class="edu-admin-material-item__del"
                          onsubmit="return confirm('Delete &quot;{{ addslashes($material['title']) }}&quot;?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="edu-admin-del-btn" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            @empty
                <div class="edu-admin-empty">
                    <p>No materials uploaded yet.</p>
                </div>
            @endforelse
        </div>

        <div class="edu-progress-badge mt-auto">
            <div class="edu-progress-badge__score">
                <span class="edu-progress-badge__num">{{ count($materials) }}</span>
                <span class="edu-progress-badge__denom">&nbsp;files</span>
            </div>
            <div>
                <p class="edu-progress-badge__label">Education Material</p>
                <p class="edu-progress-badge__cta">Total Uploads</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // ── State ────────────────────────────────────────────────────
    const fileInput   = document.getElementById('fileInput');
    const dropzone    = document.getElementById('dropzone');
    const dzContent   = document.getElementById('dropzoneContent');
    const submitBtn   = document.getElementById('submitBtn');
    const pendingList = document.getElementById('pendingList');

    // Each entry: { file: File, title: string }
    let stagedFiles = [];

    // ── Add files to the staging list ────────────────────────────
    function stageFiles(newFiles) {
        Array.from(newFiles).forEach(function (file) {
            // Default title: filename without extension
            const defaultTitle = file.name.replace(/\.[^.]+$/, '');
            stagedFiles.push({ file: file, title: defaultTitle });
        });
        renderPendingList();
        updateSubmitBtn();
    }

    function removeStagedFile(index) {
        stagedFiles.splice(index, 1);
        renderPendingList();
        updateSubmitBtn();
    }

    function renderPendingList() {
        pendingList.innerHTML = '';
        stagedFiles.forEach(function (entry, i) {
            const item = document.createElement('div');
            item.className = 'edu-admin-material-item edu-admin-pending-item';

            const dot = document.createElement('span');
            dot.className = 'edu-video-item__dot edu-admin-pending-dot';

            const body = document.createElement('div');
            body.className = 'edu-admin-material-item__body';

            const titleInput = document.createElement('input');
            titleInput.type        = 'text';
            titleInput.className   = 'edu-admin-pending-title-input';
            titleInput.placeholder = 'Enter title…';
            titleInput.value       = entry.title;
            titleInput.addEventListener('input', function () {
                stagedFiles[i].title = this.value;
            });

            const meta = document.createElement('span');
            meta.className = 'edu-admin-material-item__meta edu-admin-pending-badge';
            meta.textContent = 'Pending \u2022 ' + formatBytes(entry.file.size);

            body.appendChild(titleInput);
            body.appendChild(meta);

            const btn = document.createElement('button');
            btn.type      = 'button';
            btn.className = 'edu-admin-del-btn';
            btn.title     = 'Remove';
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
            btn.addEventListener('click', function () { removeStagedFile(i); });

            const del = document.createElement('div');
            del.className = 'edu-admin-material-item__del';
            del.appendChild(btn);

            item.appendChild(dot);
            item.appendChild(body);
            item.appendChild(del);
            pendingList.appendChild(item);
        });
    }

    function updateSubmitBtn() {
        submitBtn.disabled = stagedFiles.length === 0;
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    // ── File input change ────────────────────────────────────────
    fileInput.addEventListener('change', function () {
        if (this.files && this.files.length) {
            stageFiles(this.files);
            this.value = ''; // reset so same file can be picked again
        }
    });

    // ── Drag-and-drop ────────────────────────────────────────────
    function handleDragOver(e) {
        e.preventDefault();
        dropzone.classList.add('edu-admin-dropzone--drag');
    }

    function handleDragLeave(e) {
        if (!dropzone.contains(e.relatedTarget)) {
            dropzone.classList.remove('edu-admin-dropzone--drag');
        }
    }

    function handleDrop(e) {
        e.preventDefault();
        dropzone.classList.remove('edu-admin-dropzone--drag');
        if (e.dataTransfer.files && e.dataTransfer.files.length) {
            stageFiles(e.dataTransfer.files);
        }
    }

    // ── Right-sidebar search ─────────────────────────────────────
    document.getElementById('adminSearchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.edu-admin-material-item').forEach(function (item) {
            if (item.classList.contains('edu-admin-pending-item')) return; // always show pending
            item.style.display = (item.dataset.title || '').includes(q) ? '' : 'none';
        });
    });

    // ── Save via fetch (avoids fileInput.files re-assignment issues) ──
    document.getElementById('uploadForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Validate all titles are filled
        const emptyIdx = stagedFiles.findIndex(function (entry) { return entry.title.trim() === ''; });
        if (emptyIdx !== -1) {
            pendingList.querySelectorAll('.edu-admin-pending-title-input')[emptyIdx].focus();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Uploading…';

        const fd = new FormData();
        fd.append('_token', document.querySelector('input[name="_token"]').value);
        stagedFiles.forEach(function (entry) {
            fd.append('titles[]', entry.title.trim());
            fd.append('files[]', entry.file, entry.file.name);
        });

        fetch('{{ url('/education/materials') }}', { method: 'POST', body: fd })
            .then(function (res) {
                // Laravel redirects back — follow the redirect URL
                if (res.redirected) { window.location.href = res.url; return; }
                // Non-redirect (e.g. validation error) — reload to show flash/errors
                window.location.href = '{{ url('/education') }}';
            })
            .catch(function () {
                window.location.href = '{{ url('/education') }}';
            });
    });
</script>
@endpush
