@extends('layouts.admin')

@section('title', 'Posts Management - ' . config('app.name', 'KUPKOP'))
@section('header', 'Posts')

@section('content')
<div class="admin-dashboard-container">
    {{-- Page Header --}}
    <div class="posts-header-block">
        <h1 class="posts-heading">Posts</h1>
        <p class="posts-subheading">Check that each listing is legit. Removed posts go to the archive.</p>
    </div>

    {{-- Top Tabs with Badge Counts --}}
    <div class="posts-tabs-row">
        <a href="{{ route('admin.posts', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}" class="tab-pill {{ $tab === 'all' ? 'active' : '' }}">
            <span>All posts</span>
            <span class="tab-badge">{{ $allCount }}</span>
        </a>
        <a href="{{ route('admin.posts', array_merge(request()->except(['tab', 'page']), ['tab' => 'archive'])) }}" class="tab-pill {{ $tab === 'archive' ? 'active' : '' }}">
            <span>Archive</span>
            <span class="tab-badge">{{ $archiveCount }}</span>
        </a>
    </div>

    {{-- Filters & Search (above table) --}}
    <form method="GET" action="{{ route('admin.posts') }}" class="posts-filters-form" id="admin-posts-filter-form">
        <input type="hidden" name="tab" value="{{ $tab }}">

        {{-- Search Input --}}
        <div class="filter-search-box">
            <span class="filter-search-icon">
                <x-lucide-search style="width: 17px; height: 17px;" />
            </span>
            <input
                type="text"
                name="search"
                id="admin-posts-search-input"
                value="{{ request('search') }}"
                placeholder="Search by pet name, ID or owner"
                autocomplete="off"
            >
        </div>

        {{-- Species Filter --}}
        <div class="admin-custom-select-group" data-filter="species" data-prefix="Species: ">
            <input type="hidden" name="species" value="{{ request('species', 'all') }}">
            <button type="button" class="admin-custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                <span class="admin-custom-select-label">
                    Species: {{ request('species') && strtolower(request('species')) !== 'all' ? ucfirst(request('species')) : 'All' }}
                </span>
                <x-lucide-chevron-down class="admin-custom-select-arrow" />
            </button>
            <ul class="admin-custom-select-dropdown" role="listbox">
                @php $currSpecies = strtolower(request('species', 'all')); @endphp
                <li class="admin-custom-select-option {{ $currSpecies === 'all' || !$currSpecies ? 'selected' : '' }}" data-value="all" data-label="All">
                    <x-lucide-check class="admin-option-check-icon" />
                    <span>All</span>
                </li>
                <li class="admin-custom-select-option {{ $currSpecies === 'dog' ? 'selected' : '' }}" data-value="Dog" data-label="Dog">
                    <x-lucide-check class="admin-option-check-icon" />
                    <span>Dog</span>
                </li>
                <li class="admin-custom-select-option {{ $currSpecies === 'cat' ? 'selected' : '' }}" data-value="Cat" data-label="Cat">
                    <x-lucide-check class="admin-option-check-icon" />
                    <span>Cat</span>
                </li>
            </ul>
        </div>

        {{-- Verification Filter --}}
        <div class="admin-custom-select-group" data-filter="verification" data-prefix="Verification: ">
            <input type="hidden" name="verification" value="{{ request('verification', 'all') }}">
            <button type="button" class="admin-custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                <span class="admin-custom-select-label">
                    Verification: {{ request('verification') && strtolower(request('verification')) !== 'all' ? ucfirst(request('verification')) : 'All' }}
                </span>
                <x-lucide-chevron-down class="admin-custom-select-arrow" />
            </button>
            <ul class="admin-custom-select-dropdown" role="listbox">
                @php $currVerif = strtolower(request('verification', 'all')); @endphp
                <li class="admin-custom-select-option {{ $currVerif === 'all' || !$currVerif ? 'selected' : '' }}" data-value="all" data-label="All">
                    <x-lucide-check class="admin-option-check-icon" />
                    <span>All</span>
                </li>
                <li class="admin-custom-select-option {{ $currVerif === 'verified' ? 'selected' : '' }}" data-value="verified" data-label="Verified">
                    <x-lucide-check class="admin-option-check-icon" />
                    <span>Verified</span>
                </li>
                <li class="admin-custom-select-option {{ $currVerif === 'unverified' ? 'selected' : '' }}" data-value="unverified" data-label="Unverified">
                    <x-lucide-check class="admin-option-check-icon" />
                    <span>Unverified</span>
                </li>
            </ul>
        </div>
    </form>

    {{-- Posts Table Card --}}
    <div class="posts-table-card">
        @if($pets->isEmpty())
            <div style="padding: 3rem 1.5rem; text-align: center; color: #71655B;">
                <div style="width: 52px; height: 52px; background: #FAF5EE; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; color: #4A2E2B;">
                    <x-lucide-paw-print style="width: 26px; height: 26px;" />
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #2D1810; margin: 0 0 0.35rem 0;">No posts found</h3>
                <p style="font-size: 0.9rem; color: #71655B; margin: 0;">Try adjusting your filters or search terms.</p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="posts-table">
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">Pet</th>
                            <th style="min-width: 240px;">Details</th>
                            <th style="min-width: 170px;">Posted by</th>
                            <th style="min-width: 180px;">{{ $tab === 'archive' ? 'Status' : 'Verification' }}</th>
                            <th style="min-width: 200px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pets as $pet)
                            @php
                                $speciesLower = strtolower(trim($pet->species));
                                $isDog = str_contains($speciesLower, 'dog');
                                $isCat = str_contains($speciesLower, 'cat');
                                $formattedId = $pet->formatted_id;
                                $statusLower = strtolower(trim($pet->status));
                            @endphp
                            <tr>
                                {{-- Pet: Photo, Name, and ID --}}
                                <td>
                                    <div class="pet-cell">
                                        <div class="pet-avatar-box {{ $isCat ? 'avatar-cat' : 'avatar-dog' }}" style="position: relative;">
                                            @if($pet->image_path)
                                                <img src="{{ $pet->image_url }}" alt="{{ $pet->name }}">
                                                @php $extraCount = count($pet->image_urls) - 1; @endphp
                                                @if($extraCount > 0)
                                                    <span class="pet-photos-badge" title="{{ count($pet->image_urls) }} photos uploaded" style="position: absolute; bottom: 3px; right: 3px; background: rgba(45, 24, 16, 0.88); color: #fff; font-size: 0.65rem; font-weight: 700; padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center; gap: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.35);">
                                                        <x-lucide-camera style="width: 10px; height: 10px; stroke-width: 2.2;" />
                                                        <span>+{{ $extraCount }}</span>
                                                    </span>
                                                @endif
                                            @else
                                                @if($isCat)
                                                    <x-lucide-cat style="width: 24px; height: 24px;" />
                                                @else
                                                    <x-lucide-dog style="width: 24px; height: 24px;" />
                                                @endif
                                            @endif
                                        </div>
                                        <div>
                                            <div class="pet-name">{{ $pet->name }}</div>
                                            <div class="pet-id">{{ $formattedId }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Details: Species, Breed, Age, Gender, and Location --}}
                                <td>
                                    <div class="details-top">
                                        {{ ucfirst($pet->species) }} · {{ $pet->breed ?? 'Unknown' }}
                                    </div>
                                    <div class="details-sub">
                                        {{ $pet->age }} · {{ ucfirst($pet->gender) }} · {{ $pet->location ?? 'Brgy. 176-F' }}
                                    </div>
                                </td>

                                {{-- Posted by --}}
                                <td>
                                    <span class="posted-by-text">
                                        {{ $pet->posted_by ?? ($pet->user->name ?? 'Community Member') }}
                                    </span>
                                </td>

                                {{-- Status / Verification Badges --}}
                                <td>
                                    @if($tab === 'archive')
                                        {{-- Archive tab shows single Archived badge --}}
                                        <div class="status-badges-group">
                                            <span class="badge-archived">Archived</span>
                                        </div>
                                    @else
                                        {{-- All posts tab shows two distinct badges --}}
                                        <div class="status-badges-group">
                                            {{-- 1. Verification Badge --}}
                                            @if($pet->is_verified)
                                                <span class="badge-verified">Verified</span>
                                            @else
                                                <span class="badge-unverified">Unverified</span>
                                            @endif

                                            {{-- 2. Adoption Status Badge --}}
                                            @if($statusLower === 'available')
                                                <span class="badge-available">Available</span>
                                            @elseif($statusLower === 'pending')
                                                <span class="badge-pending">Pending</span>
                                            @elseif($statusLower === 'adopted')
                                                <span class="badge-adopted">Adopted</span>
                                            @else
                                                <span class="badge-available">{{ ucfirst($pet->status) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="table-actions-cell">
                                        @if($tab === 'archive')
                                            {{-- Restore & Delete permanently --}}
                                            <button
                                                type="button"
                                                class="btn-action-restore"
                                                onclick="openRestoreModal({{ $pet->id }}, '{{ addslashes($pet->name) }}')"
                                            >
                                                Restore
                                            </button>
                                            <button
                                                type="button"
                                                class="btn-action-delete"
                                                onclick="openDeletePermanentModal({{ $pet->id }}, '{{ addslashes($pet->name) }}')"
                                            >
                                                Delete permanently
                                            </button>
                                        @else
                                            {{-- View & verify, Remove --}}
                                            <button
                                                type="button"
                                                class="btn-action-view"
                                                onclick="openVerifyModal({{ json_encode([
                                                    'id' => $pet->id,
                                                    'name' => $pet->name,
                                                    'formatted_id' => $formattedId,
                                                    'species' => ucfirst($pet->species),
                                                    'breed' => $pet->breed ?? 'Unknown',
                                                    'age' => $pet->age,
                                                    'gender' => ucfirst($pet->gender),
                                                    'location' => $pet->location ?? 'Brgy. 176-F',
                                                    'posted_by' => $pet->posted_by ?? ($pet->user->name ?? 'Community Member'),
                                                    'is_verified' => (bool)$pet->is_verified,
                                                    'status' => ucfirst($pet->status),
                                                    'image_url' => $pet->image_url,
                                                    'image_urls' => $pet->image_urls,
                                                    'has_image' => !empty($pet->image_path),
                                                    'is_cat' => $isCat,
                                                ]) }})"
                                            >
                                                View & verify
                                            </button>
                                            <button
                                                type="button"
                                                class="btn-action-remove"
                                                onclick="openArchiveModal({{ $pet->id }}, '{{ addslashes($pet->name) }}')"
                                            >
                                                Remove
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- =====================================================================
     1. DETAIL & VERIFY MODAL (Screenshot 2)
     ===================================================================== --}}
<div
    id="verify-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="vm-pet-name"
    style="display: none; z-index: 1050;"
    onclick="if(event.target.id === 'verify-modal') closeVerifyModal()"
>
    <div class="verify-modal-dialog" onclick="event.stopPropagation()">
        {{-- Header: Pet Name, ID, Badges, Close Button --}}
        <div class="verify-modal-header">
            <div>
                <h2 class="verify-modal-name">
                    <span id="vm-name">Pet Name</span>
                    <span class="verify-modal-id" id="vm-id">#RES-0000</span>
                </h2>
                <div class="status-badges-group" style="margin-top: 0.4rem;">
                    <span id="vm-badge-verified" class="badge-unverified">Unverified</span>
                    <span id="vm-badge-status" class="badge-available">Available</span>
                </div>
            </div>
            <button type="button" class="verify-modal-close" onclick="closeVerifyModal()" aria-label="Close modal">
                <x-lucide-x style="width: 16px; height: 16px;" />
            </button>
        </div>

        {{-- Pet Image Box with Fullscreen trigger, Navigation Arrows & Multi-photo counter --}}
        <div 
            class="verify-modal-img-box" 
            id="vm-img-box"
            onclick="openAdminFullscreenImage()"
            title="Click to view full screen"
            role="button"
            tabindex="0"
            onkeydown="if(event.key==='Enter'||event.key===' '){openAdminFullscreenImage();event.preventDefault();}"
            style="position: relative; cursor: pointer;"
        >
            <img id="vm-img" src="" alt="Pet photo" style="display: none;">
            <div id="vm-img-fallback" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
                <x-lucide-dog id="vm-dog-icon" style="width: 80px; height: 80px; color: #D97706;" />
                <x-lucide-cat id="vm-cat-icon" style="width: 80px; height: 80px; color: #0284C7; display: none;" />
            </div>

            {{-- Multi-photo Counter Badge --}}
            <span id="vm-photo-counter" class="vm-photo-counter-badge" style="display: none; position: absolute; top: 0.65rem; left: 0.65rem; background: rgba(45, 24, 16, 0.85); color: #FEF08A; font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 9999px; letter-spacing: 0.2px; border: 1px solid rgba(254, 240, 138, 0.35); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 3;">
                1 / 1
            </span>

            {{-- Previous Arrow Button --}}
            <button 
                type="button" 
                id="vm-gallery-prev" 
                class="modal-gallery-arrow-btn prev" 
                onclick="event.stopPropagation(); cycleAdminGallery(-1);" 
                style="display: none;"
                aria-label="Previous photo"
                title="Previous photo"
            >
                &#10094;
            </button>

            {{-- Next Arrow Button --}}
            <button 
                type="button" 
                id="vm-gallery-next" 
                class="modal-gallery-arrow-btn next" 
                onclick="event.stopPropagation(); cycleAdminGallery(1);" 
                style="display: none;"
                aria-label="Next photo"
                title="Next photo"
            >
                &#10095;
            </button>

            {{-- Hover Expand Badge --}}
            <div class="photo-expand-badge" id="vm-expand-badge" style="display: none;">
                <x-lucide-maximize-2 class="expand-badge-icon" />
                <span>View Full Screen</span>
            </div>
        </div>

        {{-- Dynamic Thumbnail Preview Grid / Strip for Multi-Image Inspection --}}
        <div id="vm-gallery-thumbnails" class="modal-gallery-thumbnails-strip" style="display: none; margin: -0.25rem 0 1rem 0;"></div>

        {{-- Details Grid --}}
        <div class="verify-modal-details-grid">
            <div class="verify-detail-label">Species</div>
            <div class="verify-detail-val" id="vm-species-breed">Dog · Aspin</div>

            <div class="verify-detail-label">Age / sex</div>
            <div class="verify-detail-val" id="vm-age-sex">2 years old · Female</div>

            <div class="verify-detail-label">Location</div>
            <div class="verify-detail-val" id="vm-location">Package 6, Brgy. 176-F</div>

            <div class="verify-detail-label">Posted by</div>
            <div class="verify-detail-val" id="vm-posted-by">Margarette Karylle</div>
        </div>

        {{-- Legit Check Section (Screenshot 2) --}}
        <div class="legit-check-box" id="vm-legit-check-box">
            <h4 class="legit-check-title">Legit check</h4>
            <label class="legit-check-item">
                <input type="checkbox" id="chk-legit-photo" onchange="updateVerifyButtonState()">
                <span>Photo shows a real animal</span>
            </label>
            <label class="legit-check-item">
                <input type="checkbox" id="chk-legit-location" onchange="updateVerifyButtonState()">
                <span>Location is inside the target barangay</span>
            </label>
            <label class="legit-check-item">
                <input type="checkbox" id="chk-legit-owner" onchange="updateVerifyButtonState()">
                <span>Owner account is real</span>
            </label>
        </div>

        {{-- Footer Actions --}}
        <div class="verify-modal-actions">
            <div>
                <button
                    type="button"
                    class="btn-action-remove"
                    id="vm-btn-remove"
                    onclick="triggerArchiveFromModal()"
                >
                    Remove
                </button>
            </div>

            <div>
                {{-- Verify Form --}}
                <form id="vm-verify-form" method="POST" action="" style="display: inline; margin: 0;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" id="btn-mark-verified" class="btn-mark-verified" disabled>
                        Mark as verified
                    </button>
                </form>

                {{-- Unverify Form --}}
                <form id="vm-unverify-form" method="POST" action="" style="display: none; margin: 0;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" id="btn-mark-unverify" class="btn-mark-unverify">
                        Unverify
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================================
     2. ARCHIVE / REMOVE CONFIRMATION MODAL
     ===================================================================== --}}
<div
    id="archive-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="archive-modal-title"
    style="display: none; z-index: 1060;"
    onclick="if(event.target.id === 'archive-modal') closeArchiveModal()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 420px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; text-align: center; border: 1px solid #F3E8D8; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22);">
        <div style="width: 52px; height: 52px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto; color: #D97706;">
            <x-lucide-archive style="width: 26px; height: 26px;" />
        </div>
        <h3 id="archive-modal-title" style="font-size: 1.2rem; font-weight: 800; color: #2D1810; margin: 0 0 0.45rem 0;">
            Remove Post?
        </h3>
        <p style="font-size: 0.92rem; color: #71655B; margin: 0 0 1.5rem 0; line-height: 1.5;">
            Are you sure you want to remove <strong id="archive-pet-name">this pet</strong>? It will be moved to the archive tab and hidden from the public feed.
        </p>
        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button
                type="button"
                onclick="closeArchiveModal()"
                style="padding: 0.65rem 1.25rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer;"
            >
                Cancel
            </button>
            <form id="archive-form" method="POST" action="" style="display: inline; margin: 0;">
                @csrf
                @method('PATCH')
                <button
                    type="submit"
                    style="padding: 0.65rem 1.25rem; border: none; background: #4A2E2B; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 700; cursor: pointer;"
                >
                    Yes, Remove
                </button>
            </form>
        </div>
    </div>
</div>

{{-- =====================================================================
     3. RESTORE CONFIRMATION MODAL
     ===================================================================== --}}
<div
    id="restore-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="restore-modal-title"
    style="display: none; z-index: 1060;"
    onclick="if(event.target.id === 'restore-modal') closeRestoreModal()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 420px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; text-align: center; border: 1px solid #F3E8D8; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22);">
        <div style="width: 52px; height: 52px; background: #FEF9C3; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto; color: #854D0E;">
            <x-lucide-rotate-ccw style="width: 26px; height: 26px;" />
        </div>
        <h3 id="restore-modal-title" style="font-size: 1.2rem; font-weight: 800; color: #2D1810; margin: 0 0 0.45rem 0;">
            Restore Post?
        </h3>
        <p style="font-size: 0.92rem; color: #71655B; margin: 0 0 1.5rem 0; line-height: 1.5;">
            Are you sure you want to restore <strong id="restore-pet-name">this pet</strong> back to active posts?
        </p>
        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button
                type="button"
                onclick="closeRestoreModal()"
                style="padding: 0.65rem 1.25rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer;"
            >
                Cancel
            </button>
            <form id="restore-form" method="POST" action="" style="display: inline; margin: 0;">
                @csrf
                @method('PATCH')
                <button
                    type="submit"
                    style="padding: 0.65rem 1.25rem; border: 1px solid #FCD34D; background: #FEF08A; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 800; cursor: pointer;"
                >
                    Yes, Restore
                </button>
            </form>
        </div>
    </div>
</div>

{{-- =====================================================================
     4. DELETE PERMANENTLY CONFIRMATION MODAL
     ===================================================================== --}}
<div
    id="delete-perm-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="delete-perm-title"
    style="display: none; z-index: 1060;"
    onclick="if(event.target.id === 'delete-perm-modal') closeDeletePermanentModal()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 420px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; text-align: center; border: 1px solid #F3E8D8; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22);">
        <div style="width: 52px; height: 52px; background: #FEE2E2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto; color: #DC2626;">
            <x-lucide-alert-triangle style="width: 26px; height: 26px;" />
        </div>
        <h3 id="delete-perm-title" style="font-size: 1.2rem; font-weight: 800; color: #991B1B; margin: 0 0 0.45rem 0;">
            Delete Permanently?
        </h3>
        <p style="font-size: 0.92rem; color: #71655B; margin: 0 0 1.5rem 0; line-height: 1.5;">
            Warning: This action is irreversible. <strong id="delete-perm-pet-name">this pet</strong> will be permanently deleted from the database.
        </p>
        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button
                type="button"
                onclick="closeDeletePermanentModal()"
                style="padding: 0.65rem 1.25rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer;"
            >
                Cancel
            </button>
            <form id="delete-perm-form" method="POST" action="" style="display: inline; margin: 0;">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    style="padding: 0.65rem 1.25rem; border: none; background: #DC2626; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 700; cursor: pointer;"
                >
                    Yes, Delete Permanently
                </button>
            </form>
        </div>
    </div>
</div>

{{-- =====================================================================
     5. FULLSCREEN PET IMAGE CAROUSEL VIEWER MODAL FOR ADMIN INSPECTION
     ===================================================================== --}}
<div 
    id="admin-fullscreen-image-modal" 
    class="fullscreen-carousel-backdrop" 
    style="display: none;" 
    role="dialog" 
    aria-modal="true" 
    aria-label="Full screen pet image carousel"
    onclick="handleAdminFullscreenBackdropClick(event)"
>
    {{-- Header Bar (Pet Name, Counter, Close Button) --}}
    <div class="fullscreen-carousel-header" onclick="event.stopPropagation()">
        <div class="fs-carousel-meta-info">
            <h3 id="admin-fs-carousel-pet-name" class="fs-carousel-title">Pet Name</h3>
            <span id="admin-fs-carousel-counter" class="fs-carousel-counter-badge">1 / 1</span>
        </div>
        <button 
            type="button" 
            class="fullscreen-close-btn" 
            onclick="closeAdminFullscreenImage()" 
            aria-label="Close full screen view"
            title="Close (Esc)"
        >
            <x-lucide-x class="fullscreen-close-icon" />
        </button>
    </div>

    {{-- Main Carousel Viewport with Smooth Previous/Next Navigation Arrows --}}
    <div class="fullscreen-carousel-viewport" onclick="event.stopPropagation()">
        {{-- Previous Arrow Button --}}
        <button 
            type="button" 
            id="admin-fs-carousel-prev-btn" 
            class="fs-carousel-nav-btn fs-prev-btn" 
            onclick="cycleAdminFullscreenCarousel(-1)" 
            aria-label="Previous photo"
            title="Previous photo (Left arrow key)"
        >
            &#10094;
        </button>

        {{-- Centered Image Stage --}}
        <div class="fs-carousel-slide-box">
            <img id="admin-pet-fullscreen-img" src="" alt="Full screen pet photo" class="fullscreen-img fs-carousel-img">
        </div>

        {{-- Next Arrow Button --}}
        <button 
            type="button" 
            id="admin-fs-carousel-next-btn" 
            class="fs-carousel-nav-btn fs-next-btn" 
            onclick="cycleAdminFullscreenCarousel(1)" 
            aria-label="Next photo"
            title="Next photo (Right arrow key)"
        >
            &#10095;
        </button>
    </div>

    {{-- Bottom Thumbnail Indicators Strip & Keyboard Hints --}}
    <div class="fullscreen-carousel-footer" onclick="event.stopPropagation()">
        <div id="admin-fs-carousel-thumbnails-strip" class="fs-carousel-thumbnails-strip"></div>
        <div class="fullscreen-image-bar" style="justify-content: center;">
            <span class="fullscreen-hint">Use <strong>&larr; &rarr;</strong> arrow keys to browse &bull; Press <strong>ESC</strong> to exit</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentSelectedPet = null;
    let adminGallery = [];
    let adminGalleryIndex = 0;

    // Open Detail & Verify Modal
    window.openVerifyModal = function(pet) {
        currentSelectedPet = pet;

        document.getElementById('vm-name').textContent = pet.name;
        document.getElementById('vm-id').textContent = pet.formatted_id;

        // Verification Badge
        const vBadge = document.getElementById('vm-badge-verified');
        if (pet.is_verified) {
            vBadge.className = 'badge-verified';
            vBadge.textContent = 'Verified';
        } else {
            vBadge.className = 'badge-unverified';
            vBadge.textContent = 'Unverified';
        }

        // Status Badge
        const sBadge = document.getElementById('vm-badge-status');
        sBadge.textContent = pet.status;
        const statusLower = (pet.status || '').toLowerCase();
        if (statusLower === 'available') {
            sBadge.className = 'badge-available';
        } else if (statusLower === 'pending') {
            sBadge.className = 'badge-pending';
        } else if (statusLower === 'adopted') {
            sBadge.className = 'badge-adopted';
        } else {
            sBadge.className = 'badge-available';
        }

        // Gallery initialization
        if (pet.image_urls && Array.isArray(pet.image_urls) && pet.image_urls.length > 0) {
            adminGallery = pet.image_urls;
        } else if (pet.has_image && pet.image_url) {
            adminGallery = [pet.image_url];
        } else {
            adminGallery = [];
        }
        adminGalleryIndex = 0;

        // Fallback animal icons
        const dogIcon = document.getElementById('vm-dog-icon');
        const catIcon = document.getElementById('vm-cat-icon');
        if (pet.is_cat) {
            dogIcon.style.display = 'none';
            catIcon.style.display = 'block';
        } else {
            dogIcon.style.display = 'block';
            catIcon.style.display = 'none';
        }

        renderAdminModalGallery();

        // Details
        document.getElementById('vm-species-breed').textContent = pet.species + ' · ' + pet.breed;
        document.getElementById('vm-age-sex').textContent = pet.age + ' · ' + pet.gender;
        document.getElementById('vm-location').textContent = pet.location;
        document.getElementById('vm-posted-by').textContent = pet.posted_by;

        // Reset Legit Checkboxes
        document.getElementById('chk-legit-photo').checked = false;
        document.getElementById('chk-legit-location').checked = false;
        document.getElementById('chk-legit-owner').checked = false;

        // Action Forms
        const verifyForm = document.getElementById('vm-verify-form');
        const unverifyForm = document.getElementById('vm-unverify-form');
        verifyForm.action = `/admin/posts/${pet.id}/verify`;
        unverifyForm.action = `/admin/posts/${pet.id}/unverify`;

        if (pet.is_verified) {
            // Already verified: show Unverify button, hide verify form
            verifyForm.style.display = 'none';
            unverifyForm.style.display = 'inline';
        } else {
            // Unverified: show verify form (disabled until all 3 checkboxes checked)
            verifyForm.style.display = 'inline';
            unverifyForm.style.display = 'none';
            updateVerifyButtonState();
        }

        // Show Modal
        const modal = document.getElementById('verify-modal');
        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
    };

    function renderAdminModalGallery() {
        const imgEl = document.getElementById('vm-img');
        const fallbackEl = document.getElementById('vm-img-fallback');
        const prevBtn = document.getElementById('vm-gallery-prev');
        const nextBtn = document.getElementById('vm-gallery-next');
        const counterBadge = document.getElementById('vm-photo-counter');
        const expandBadge = document.getElementById('vm-expand-badge');
        const thumbsStrip = document.getElementById('vm-gallery-thumbnails');
        const imgBox = document.getElementById('vm-img-box');

        if (!adminGallery || adminGallery.length === 0) {
            if (imgEl) imgEl.style.display = 'none';
            if (fallbackEl) fallbackEl.style.display = 'flex';
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            if (counterBadge) counterBadge.style.display = 'none';
            if (expandBadge) expandBadge.style.display = 'none';
            if (thumbsStrip) {
                thumbsStrip.style.display = 'none';
                thumbsStrip.innerHTML = '';
            }
            if (imgBox) imgBox.style.cursor = 'default';
            return;
        }

        const currentSrc = adminGallery[adminGalleryIndex] || adminGallery[0];
        if (imgEl) {
            imgEl.src = currentSrc;
            imgEl.style.display = 'block';
        }
        if (fallbackEl) fallbackEl.style.display = 'none';
        if (expandBadge) expandBadge.style.display = 'inline-flex';
        if (imgBox) imgBox.style.cursor = 'pointer';

        const total = adminGallery.length;
        if (total > 1) {
            if (prevBtn) prevBtn.style.display = 'flex';
            if (nextBtn) nextBtn.style.display = 'flex';
            if (counterBadge) {
                counterBadge.textContent = (adminGalleryIndex + 1) + ' / ' + total;
                counterBadge.style.display = 'inline-block';
            }

            if (thumbsStrip) {
                thumbsStrip.innerHTML = '';
                adminGallery.forEach(function(url, idx) {
                    const thumbBtn = document.createElement('button');
                    thumbBtn.type = 'button';
                    thumbBtn.className = 'modal-gallery-thumb' + (idx === adminGalleryIndex ? ' active' : '');
                    thumbBtn.title = 'Photo ' + (idx + 1) + ' (Click to preview)';
                    thumbBtn.onclick = function(e) {
                        e.stopPropagation();
                        adminGalleryIndex = idx;
                        renderAdminModalGallery();
                    };
                    const thumbImg = document.createElement('img');
                    thumbImg.src = url;
                    thumbImg.alt = 'Thumbnail ' + (idx + 1);
                    thumbBtn.appendChild(thumbImg);
                    thumbsStrip.appendChild(thumbBtn);
                });
                thumbsStrip.style.display = 'flex';
            }
        } else {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            if (counterBadge) counterBadge.style.display = 'none';
            if (thumbsStrip) {
                thumbsStrip.style.display = 'none';
                thumbsStrip.innerHTML = '';
            }
        }
    }

    window.cycleAdminGallery = function(direction) {
        if (!adminGallery || adminGallery.length <= 1) return;
        adminGalleryIndex = (adminGalleryIndex + direction + adminGallery.length) % adminGallery.length;
        renderAdminModalGallery();
    };

    // Admin Fullscreen Carousel Functions
    window.openAdminFullscreenImage = function(optionalIndex = null) {
        if (!adminGallery || adminGallery.length === 0) return;
        if (optionalIndex !== null && typeof optionalIndex === 'number' && adminGallery[optionalIndex]) {
            adminGalleryIndex = optionalIndex;
        }

        const fsModal = document.getElementById('admin-fullscreen-image-modal');
        const fsPetName = document.getElementById('admin-fs-carousel-pet-name');
        if (fsPetName && currentSelectedPet) {
            fsPetName.textContent = currentSelectedPet.name;
        }

        renderAdminFullscreenCarouselSlide();

        if (fsModal) {
            fsModal.style.display = 'flex';
        }
    };

    function renderAdminFullscreenCarouselSlide() {
        if (!adminGallery || adminGallery.length === 0) return;
        const currentSrc = adminGallery[adminGalleryIndex];
        const fsImg = document.getElementById('admin-pet-fullscreen-img');
        const fsCounter = document.getElementById('admin-fs-carousel-counter');
        const prevBtn = document.getElementById('admin-fs-carousel-prev-btn');
        const nextBtn = document.getElementById('admin-fs-carousel-next-btn');
        const thumbsStrip = document.getElementById('admin-fs-carousel-thumbnails-strip');
        const total = adminGallery.length;

        if (fsImg && currentSrc) {
            fsImg.classList.remove('fade-in');
            void fsImg.offsetWidth;
            fsImg.src = currentSrc;
            fsImg.alt = (currentSelectedPet ? currentSelectedPet.name : 'Pet') + ' - Photo ' + (adminGalleryIndex + 1);
            fsImg.classList.add('fade-in');
        }

        if (fsCounter) {
            fsCounter.textContent = (adminGalleryIndex + 1) + ' / ' + total;
            fsCounter.style.display = 'inline-block';
        }

        if (prevBtn) prevBtn.style.display = total > 1 ? 'flex' : 'none';
        if (nextBtn) nextBtn.style.display = total > 1 ? 'flex' : 'none';

        if (thumbsStrip) {
            if (total > 1) {
                thumbsStrip.innerHTML = '';
                adminGallery.forEach(function(url, idx) {
                    const thumbBtn = document.createElement('button');
                    thumbBtn.type = 'button';
                    thumbBtn.className = 'fs-thumb-pill' + (idx === adminGalleryIndex ? ' is-active' : '');
                    thumbBtn.setAttribute('aria-label', 'View photo ' + (idx + 1));
                    thumbBtn.onclick = function(e) {
                        e.stopPropagation();
                        adminGalleryIndex = idx;
                        renderAdminFullscreenCarouselSlide();
                        renderAdminModalGallery();
                    };
                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = 'Thumbnail ' + (idx + 1);
                    thumbBtn.appendChild(img);
                    thumbsStrip.appendChild(thumbBtn);
                });
                thumbsStrip.style.display = 'flex';
            } else {
                thumbsStrip.style.display = 'none';
                thumbsStrip.innerHTML = '';
            }
        }

        renderAdminModalGallery();
    }

    window.cycleAdminFullscreenCarousel = function(direction) {
        if (!adminGallery || adminGallery.length <= 1) return;
        adminGalleryIndex = (adminGalleryIndex + direction + adminGallery.length) % adminGallery.length;
        renderAdminFullscreenCarouselSlide();
    };

    window.closeAdminFullscreenImage = function() {
        const fsModal = document.getElementById('admin-fullscreen-image-modal');
        if (fsModal) {
            fsModal.style.display = 'none';
        }
    };

    window.handleAdminFullscreenBackdropClick = function(event) {
        if (event.target.id === 'admin-fullscreen-image-modal') {
            closeAdminFullscreenImage();
        }
    };

    window.closeVerifyModal = function() {
        closeAdminFullscreenImage();
        const modal = document.getElementById('verify-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    };

    // Enables "Mark as verified" button ONLY when all 3 checkboxes are checked
    window.updateVerifyButtonState = function() {
        const c1 = document.getElementById('chk-legit-photo').checked;
        const c2 = document.getElementById('chk-legit-location').checked;
        const c3 = document.getElementById('chk-legit-owner').checked;

        const btn = document.getElementById('btn-mark-verified');
        if (btn) {
            btn.disabled = !(c1 && c2 && c3);
        }
    };

    // Modal Archive action
    window.triggerArchiveFromModal = function() {
        if (!currentSelectedPet) return;
        closeVerifyModal();
        openArchiveModal(currentSelectedPet.id, currentSelectedPet.name);
    };

    // Archive Confirmation Modal
    window.openArchiveModal = function(petId, petName) {
        document.getElementById('archive-pet-name').textContent = petName;
        document.getElementById('archive-form').action = `/admin/posts/${petId}/archive`;
        document.getElementById('archive-modal').style.display = 'flex';
        document.body.classList.add('modal-open');
    };

    window.closeArchiveModal = function() {
        const modal = document.getElementById('archive-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    };

    // Restore Confirmation Modal
    window.openRestoreModal = function(petId, petName) {
        document.getElementById('restore-pet-name').textContent = petName;
        document.getElementById('restore-form').action = `/admin/posts/${petId}/restore`;
        document.getElementById('restore-modal').style.display = 'flex';
        document.body.classList.add('modal-open');
    };

    window.closeRestoreModal = function() {
        const modal = document.getElementById('restore-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    };

    // Delete Permanently Confirmation Modal
    window.openDeletePermanentModal = function(petId, petName) {
        document.getElementById('delete-perm-pet-name').textContent = petName;
        document.getElementById('delete-perm-form').action = `/admin/posts/${petId}`;
        document.getElementById('delete-perm-modal').style.display = 'flex';
        document.body.classList.add('modal-open');
    };

    window.closeDeletePermanentModal = function() {
        const modal = document.getElementById('delete-perm-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    };

    // Close on Escape key & carousel arrow keys
    document.addEventListener('keydown', function(e) {
        const fsModal = document.getElementById('admin-fullscreen-image-modal');
        const isFsOpen = fsModal && fsModal.style.display !== 'none';

        if (isFsOpen) {
            if (e.key === 'ArrowLeft') {
                cycleAdminFullscreenCarousel(-1);
                e.preventDefault();
                return;
            }
            if (e.key === 'ArrowRight') {
                cycleAdminFullscreenCarousel(1);
                e.preventDefault();
                return;
            }
            if (e.key === 'Escape') {
                closeAdminFullscreenImage();
                e.preventDefault();
                return;
            }
        }

        if (e.key === 'Escape') {
            closeVerifyModal();
            closeArchiveModal();
            closeRestoreModal();
            closeDeletePermanentModal();
        }
    });

    // Live on-screen filtering for Admin Posts table (no dropdown, no Enter key)
    (function() {
        const searchInput = document.getElementById('admin-posts-search-input');
        if (!searchInput) return;

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Do not reload or submit
            }
        });

        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                const query = searchInput.value.trim().toLowerCase();
                const table = document.querySelector('.posts-table');
                if (!table) return;

                const rows = Array.from(table.querySelectorAll('tbody tr'));
                let visibleCount = 0;

                rows.forEach(function(row) {
                    // Check if it's our live empty row
                    if (row.id === 'admin-posts-live-empty-row') return;

                    const text = (row.textContent || '').toLowerCase();
                    const matches = !query || text.includes(query);

                    if (matches) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Live empty row handling
                let emptyRow = document.getElementById('admin-posts-live-empty-row');
                const tbody = table.querySelector('tbody');
                if (visibleCount === 0 && rows.length > 0 && tbody) {
                    if (!emptyRow) {
                        emptyRow = document.createElement('tr');
                        emptyRow.id = 'admin-posts-live-empty-row';
                        emptyRow.innerHTML = `
                            <td colspan="5" style="text-align: center; padding: 2.5rem 1rem; color: #71655B;">
                                <p style="font-weight: 700; font-size: 1.05rem; margin-bottom: 0.35rem; color: #2D1810;">No matching posts</p>
                                <p style="font-size: 0.88rem; margin: 0;">No posts match your search query "${searchInput.value.trim()}".</p>
                            </td>
                        `;
                        tbody.appendChild(emptyRow);
                    } else {
                        emptyRow.style.display = '';
                        emptyRow.querySelector('p:last-child').textContent = `No posts match your search query "${searchInput.value.trim()}".`;
                    }
                } else if (emptyRow) {
                    emptyRow.style.display = 'none';
                }
            }, 250);
        });
    })();
</script>
@endpush
