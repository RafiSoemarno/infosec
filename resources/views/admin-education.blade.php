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

    {{-- Video creation panel --}}
    <section class="content-span-12 fade-in-up">
        <div class="panel-card edu-admin-create-panel">
            <form method="POST" action="{{ url('/education/materials') }}" id="saveVideoForm">
                @csrf

                {{-- Title input --}}
                <input
                    type="text"
                    name="title"
                    id="videoTitle"
                    class="edu-admin-title-input"
                    placeholder="Create Title...."
                    maxlength="255"
                    value="{{ old('title') }}"
                    autocomplete="off"
                >

                {{-- Video preview area --}}
                <div class="edu-admin-video-preview" id="videoPreviewArea">
                    <div class="edu-admin-video-preview__placeholder" id="previewPlaceholder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                            <rect x="2" y="3" width="20" height="18" rx="3" stroke-width="1.2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9l5 3-5 3V9z"/>
                        </svg>
                        <p>Video preview will appear here</p>
                        <p class="edu-admin-video-preview__hint">Paste a SharePoint or embed link below</p>
                    </div>
                    <div class="edu-admin-video-preview__frame" id="previewFrame" style="display:none">
                        <iframe
                            id="previewIframe"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            style="position:absolute;inset:0;width:100%;height:100%;"
                        ></iframe>
                    </div>
                </div>

                {{-- Link input --}}
                <input
                    type="url"
                    name="video_link"
                    id="videoLink"
                    class="edu-admin-link-input"
                    placeholder="Link Video...."
                    value="{{ old('video_link') }}"
                    autocomplete="off"
                >

                {{-- Save button --}}
                <div class="edu-admin-create-footer">
                    <button type="submit" class="app-btn-primary edu-admin-save-btn" id="saveBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 3H7a2 2 0 00-2 2v14l7-3 7 3V5a2 2 0 00-2-2z"/></svg>
                        Save Video
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('right_sidebar')
    <div class="d-flex flex-column h-100 gap-3">
        {{-- Search --}}
        <div class="edu-search">
            <svg class="edu-search__icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
            <input class="edu-search__input" type="text" placeholder="Search education..." id="adminSearchInput">
        </div>

        {{-- List header --}}
        <div class="edu-list-header">
            <p class="edu-list-header__title">Education Material</p>
            <p class="edu-list-header__note">Track All Ongoing education material</p>
        </div>

        {{-- Material list --}}
        <div class="edu-video-list edu-admin-material-list" id="adminMaterialList">
            @forelse ($materials as $material)
                <div
                    class="edu-admin-material-item"
                    data-title="{{ strtolower($material['title']) }}"
                    data-id="{{ $material['id'] }}"
                >
                    <span class="edu-video-item__dot {{ ($material['status'] ?? 'draft') === 'published' ? 'edu-video-item__dot--watched' : 'edu-admin-dot--draft' }}"></span>
                    <div class="edu-admin-material-item__body">
                        <span class="edu-video-item__title">{{ $material['title'] }}</span>
                        <span class="edu-admin-material-item__meta">
                            Video
                            &bull;
                            <span class="edu-admin-status-badge edu-admin-status-badge--{{ $material['status'] ?? 'draft' }}">
                                {{ ucfirst($material['status'] ?? 'draft') }}
                            </span>
                        </span>
                    </div>
                    <div class="edu-admin-material-item__del">
                        <button type="button" class="edu-admin-edit-btn" title="Edit"
                            data-id="{{ $material['id'] }}"
                            data-title="{{ addslashes($material['title']) }}"
                            data-link="{{ addslashes($material['embedUrl'] ?? '') }}"
                            onclick="openEditModal(this.dataset.id, this.dataset.title, this.dataset.link)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="{{ url('/education/materials/' . $material['id']) }}"
                              onsubmit="return confirm('Delete &quot;{{ addslashes($material['title']) }}&quot;?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="edu-admin-del-btn" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="edu-admin-empty">
                    <p>No materials saved yet.</p>
                </div>
            @endforelse
        </div>

        {{-- Upload (Publish) button --}}
        <button
            type="button"
            class="app-btn-primary edu-admin-publish-btn"
            id="publishBtn"
            onclick="publishSelected()"
            disabled
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Upload Video
        </button>

        {{-- Publish form (hidden) --}}
        <form method="POST" id="publishForm" style="display:none">
            @csrf
            @method('PUT')
        </form>

        {{-- Summary card --}}
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

{{-- Edit modal --}}
<div id="editModal" class="edu-admin-modal-overlay" style="display:none" onclick="closeEditModal(event)">
    <div class="edu-admin-modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <p class="edu-admin-modal__title" id="editModalTitle">Edit Material</p>
        <form method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="edu-admin-modal__field">
                <label class="edu-admin-modal__label" for="editTitleInput">Title</label>
                <input class="edu-admin-modal__input" type="text" id="editTitleInput" name="title" required maxlength="255">
            </div>
            <div class="edu-admin-modal__field">
                <label class="edu-admin-modal__label" for="editLinkInput">Video Link</label>
                <input class="edu-admin-modal__input" type="url" id="editLinkInput" name="video_link">
            </div>
            <div class="edu-admin-modal__actions">
                <button type="button" class="app-btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="app-btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // ── Video link preview ──────────────────────────────────────
    var linkInput       = document.getElementById('videoLink');
    var previewIframe   = document.getElementById('previewIframe');
    var previewPlaceholder = document.getElementById('previewPlaceholder');
    var previewFrame    = document.getElementById('previewFrame');

    function toEmbedUrl(raw) {
        if (!raw) return '';
        var trimmed = raw.trim();

        // SharePoint embed: already contains ":x:/r" or "embed" path — serve as-is
        if (trimmed.includes('sharepoint.com') || trimmed.includes('sharepointonline.com')) {
            // Convert sharing links to embed links if needed
            if (!trimmed.includes('embed') && trimmed.includes('?')) {
                return trimmed.replace('?', '?action=embedview&');
            }
            return trimmed;
        }

        // YouTube watch URL → embed
        var ytMatch = trimmed.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{11})/);
        if (ytMatch) {
            return 'https://www.youtube.com/embed/' + ytMatch[1];
        }

        // Already an embed URL or direct iframe src
        return trimmed;
    }

    function updatePreview() {
        var url = toEmbedUrl(linkInput.value);
        if (url) {
            previewIframe.src = url;
            previewPlaceholder.style.display = 'none';
            previewFrame.style.display = 'block';
        } else {
            previewIframe.src = '';
            previewPlaceholder.style.display = '';
            previewFrame.style.display = 'none';
        }
    }

    linkInput.addEventListener('input', updatePreview);

    // ── Sidebar: item selection for publish ────────────────────
    var selectedId = null;
    var publishBtn = document.getElementById('publishBtn');

    document.querySelectorAll('.edu-admin-material-item').forEach(function (item) {
        item.addEventListener('click', function (e) {
            // Ignore clicks on edit/delete buttons
            if (e.target.closest('.edu-admin-material-item__del')) return;

            document.querySelectorAll('.edu-admin-material-item').forEach(function (el) {
                el.classList.remove('edu-admin-material-item--selected');
            });

            item.classList.add('edu-admin-material-item--selected');
            selectedId = item.dataset.id;

            // Only enable publish for draft items
            var dot = item.querySelector('.edu-admin-dot--draft');
            publishBtn.disabled = !dot;
        });
    });

    window.publishSelected = function () {
        if (!selectedId) return;
        var form = document.getElementById('publishForm');
        form.action = '/education/materials/' + selectedId + '/publish';
        form.submit();
    };

    // ── Search ──────────────────────────────────────────────────
    document.getElementById('adminSearchInput').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('.edu-admin-material-item').forEach(function (item) {
            item.style.display = (item.dataset.title || '').includes(q) ? '' : 'none';
        });
    });

    // ── Edit modal ──────────────────────────────────────────────
    window.openEditModal = function (id, title, link) {
        document.getElementById('editForm').action = '/education/materials/' + id;
        document.getElementById('editTitleInput').value = title;
        document.getElementById('editLinkInput').value = link || '';
        document.getElementById('editModal').style.display = 'flex';
        document.getElementById('editTitleInput').focus();
    };

    window.closeEditModal = function (event) {
        if (!event || event.target === document.getElementById('editModal')) {
            document.getElementById('editModal').style.display = 'none';
        }
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeEditModal();
    });
})();
</script>
@endpush
