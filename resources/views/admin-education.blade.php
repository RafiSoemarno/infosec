@extends('layouts.app')

@php
    $menuItems = $menuData['items'] ?? [];
@endphp

@section('title', 'Education — Admin')

@section('left_sidebar')
    <div class="d-flex flex-column h-100">
        <div class="brand-lockup">
            <img src="{{ asset('storage/Red_DENSO_Hires.png') }}" alt="DENSO - Crafting the Core" class="brand-lockup__logo">
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

                <input
                    type="text"
                    name="title"
                    class="edu-admin-title-input"
                    placeholder="Create Title ..."
                    value="{{ old('title') }}"
                    required
                    autocomplete="off"
                >

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
                        name="file"
                        id="fileInput"
                        class="edu-admin-dropzone__input"
                        accept=".mp4,.webm,.ogv,.mov,.avi,.pdf,.ppt,.pptx,.doc,.docx"
                        required
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
                                Upload files
                            </label>
                        </div>

                        <p class="edu-admin-dropzone__formats">Supported: MP4, WebM, MOV, AVI, PDF, PPT, PPTX, DOC, DOCX &mdash; max {{ 500 }} MB</p>
                    </div>

                    {{-- Preview state (shown after file selected) --}}
                    <div class="edu-admin-dropzone__preview" id="filePreview" style="display:none;">
                        <div class="edu-admin-file-preview">
                            <div class="edu-admin-file-preview__icon" id="previewIcon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="edu-admin-file-preview__info">
                                <p class="edu-admin-file-preview__name" id="previewName"></p>
                                <p class="edu-admin-file-preview__size" id="previewSize"></p>
                            </div>
                            <button type="button" class="edu-admin-file-preview__remove" onclick="clearFile()" title="Remove file">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="edu-admin-upload-footer">
                    <button type="submit" class="app-btn-primary edu-admin-submit-btn" id="submitBtn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Upload Material
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
    // ── File input handling ──────────────────────────────────────
    const fileInput   = document.getElementById('fileInput');
    const dropzone    = document.getElementById('dropzone');
    const dzContent   = document.getElementById('dropzoneContent');
    const filePreview = document.getElementById('filePreview');
    const previewName = document.getElementById('previewName');
    const previewSize = document.getElementById('previewSize');
    const submitBtn   = document.getElementById('submitBtn');

    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            showPreview(this.files[0]);
        }
    });

    function showPreview(file) {
        previewName.textContent = file.name;
        previewSize.textContent = formatBytes(file.size);
        dzContent.style.display = 'none';
        filePreview.style.display = 'flex';
        submitBtn.disabled = false;
    }

    function clearFile() {
        fileInput.value = '';
        dzContent.style.display = '';
        filePreview.style.display = 'none';
        submitBtn.disabled = true;
    }

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

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
        const files = e.dataTransfer.files;
        if (files && files[0]) {
            // Assign to actual input via DataTransfer
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            fileInput.files = dt.files;
            showPreview(files[0]);
        }
    }

    // ── Right-sidebar search ─────────────────────────────────────
    document.getElementById('adminSearchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.edu-admin-material-item').forEach(function (item) {
            item.style.display = (item.dataset.title || '').includes(q) ? '' : 'none';
        });
    });

    // ── Upload progress UX ───────────────────────────────────────
    document.getElementById('uploadForm').addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Uploading…';
    });
</script>
@endpush
