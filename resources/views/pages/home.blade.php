@extends('layouts.app')

@section('title', 'Kupkop - Adopt a rescued dog or cat in Brgy. 176-F')

@section('content')
{{-- 1. Hero Section --}}
<section class="hero-section">
    <div class="site-container hero-content">
        {{-- Welcome Greeting Row with Shelter Summary Statistics Counters --}}
        <div class="hero-welcome-row">
            <div class="hero-auth-greeting">
                @auth
                    <h1 class="page-title hero-greeting-title">Welcome, {{ Auth::user()->name }}!</h1>
                    <p class="page-subtitle hero-greeting-sub">Meet rescued aspins, puspins, and pets ready for adoption in Brgy.&nbsp;176&#8209;F.</p>
                @else
                    <h1 class="page-title hero-greeting-title">Adopt a rescued dog or cat in <span class="text-nowrap">Brgy.&nbsp;176&#8209;F.</span></h1>
                    <p class="page-subtitle hero-greeting-sub">Connecting rescued pets with loving homes in Brgy.&nbsp;176&#8209;F.</p>
                @endauth
            </div>
            @auth
                @if(Auth::user()->is_admin)
                    <div class="hero-admin-stats-widget" role="region" aria-label="Shelter Summary Stats">
                        <div class="admin-stat-card">
                            <span class="admin-stat-number">{{ $adminStats['total_listed'] ?? $statusCounts['all'] ?? 0 }}</span>
                            <span class="admin-stat-label">Pets listed</span>
                        </div>
                        <div class="admin-stat-card">
                            <span class="admin-stat-number">{{ $adminStats['available'] ?? $statusCounts['available'] ?? 0 }}</span>
                            <span class="admin-stat-label">Available</span>
                        </div>
                        <div class="admin-stat-card">
                            <span class="admin-stat-number">{{ $adminStats['pending'] ?? $statusCounts['pending'] ?? 0 }}</span>
                            <span class="admin-stat-label">Pending</span>
                        </div>
                    </div>
                @else
                    <button 
                        type="button" 
                        class="btn-hero-create-post" 
                        onclick="openCreatePostModal()" 
                        title="Create Post" 
                        aria-label="Create Post"
                    >
                        <x-lucide-plus class="hero-create-icon" />
                    </button>
                @endif
            @endauth
        </div>

        {{-- 2. Search & Filter Bar --}}
        <div class="search-filter-card">
            <form action="{{ route('home') }}" method="GET" class="filter-form" id="filter-form">
                <input type="hidden" name="status" id="filter-status-input" value="{{ request('status', 'all') }}">

                {{-- Search Input with Search Icon --}}
                <div class="filter-group search-input-group">
                    <x-lucide-search class="filter-icon" />
                    <input 
                        type="text" 
                        name="keyword" 
                        id="home-search-input"
                        value="{{ request('keyword') }}" 
                        placeholder="Search by name or breed"
                        autocomplete="off"
                        class="filter-input"
                    >
                </div>

                {{-- Species Dropdown --}}
                <div class="filter-group custom-select-group" data-filter="species">
                    <input type="hidden" name="species" value="{{ request('species') }}">
                    <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select-label">
                            {{ request('species') ? ucfirst(request('species')) : 'All species' }}
                        </span>
                        <x-lucide-chevron-down class="custom-select-arrow" />
                    </button>
                    <ul class="custom-select-dropdown" role="listbox">
                        <li class="custom-select-option {{ !request('species') ? 'selected' : '' }}" data-value="" data-label="All species">
                            <x-lucide-check class="option-check-icon" />
                            <span>All species</span>
                        </li>
                        <li class="custom-select-option {{ request('species') == 'dog' ? 'selected' : '' }}" data-value="dog" data-label="Dog">
                            <x-lucide-check class="option-check-icon" />
                            <span>Dog</span>
                        </li>
                        <li class="custom-select-option {{ request('species') == 'cat' ? 'selected' : '' }}" data-value="cat" data-label="Cat">
                            <x-lucide-check class="option-check-icon" />
                            <span>Cat</span>
                        </li>
                    </ul>
                </div>

                {{-- Breed Dropdown --}}
                <div class="filter-group custom-select-group" data-filter="breed">
                    <input type="hidden" name="breed" value="{{ request('breed') }}">
                    <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select-label">
                            @if(request('breed') == 'aspin') Aspin
                            @elseif(request('breed') == 'puspin') Puspin
                            @elseif(request('breed') == 'mix') Mix Breed
                            @elseif(request('breed')) {{ ucfirst(request('breed')) }}
                            @else Any breed
                            @endif
                        </span>
                        <x-lucide-chevron-down class="custom-select-arrow" />
                    </button>
                    <ul class="custom-select-dropdown" role="listbox">
                        <li class="custom-select-option {{ !request('breed') ? 'selected' : '' }}" data-value="" data-label="Any breed">
                            <x-lucide-check class="option-check-icon" />
                            <span>Any breed</span>
                        </li>
                        <li class="custom-select-option {{ request('breed') == 'aspin' ? 'selected' : '' }}" data-value="aspin" data-label="Aspin">
                            <x-lucide-check class="option-check-icon" />
                            <span>Aspin</span>
                        </li>
                        <li class="custom-select-option {{ request('breed') == 'puspin' ? 'selected' : '' }}" data-value="puspin" data-label="Puspin">
                            <x-lucide-check class="option-check-icon" />
                            <span>Puspin</span>
                        </li>
                        <li class="custom-select-option {{ request('breed') == 'mix' ? 'selected' : '' }}" data-value="mix" data-label="Mix Breed">
                            <x-lucide-check class="option-check-icon" />
                            <span>Mix Breed</span>
                        </li>
                    </ul>
                </div>

                {{-- Age Dropdown --}}
                <div class="filter-group custom-select-group" data-filter="age">
                    <input type="hidden" name="age" value="{{ request('age') }}">
                    <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select-label">
                            @if(request('age') == 'puppy_kitten') Puppy / Kitten (0 - 6 months)
                            @elseif(request('age') == 'junior' || request('age') == 'young') Junior (7 - 12 months)
                            @elseif(request('age') == 'adult') Adult (1 - 7 years)
                            @elseif(request('age') == 'senior') Senior (8+ years)
                            @else Any age
                            @endif
                        </span>
                        <x-lucide-chevron-down class="custom-select-arrow" />
                    </button>
                    <ul class="custom-select-dropdown" role="listbox">
                        <li class="custom-select-option {{ !request('age') ? 'selected' : '' }}" data-value="" data-label="Any age">
                            <x-lucide-check class="option-check-icon" />
                            <span>Any age</span>
                        </li>
                        <li class="custom-select-option {{ request('age') == 'puppy_kitten' ? 'selected' : '' }}" data-value="puppy_kitten" data-label="Puppy / Kitten (0 - 6 months)">
                            <x-lucide-check class="option-check-icon" />
                            <span>Puppy / Kitten (0 - 6 months)</span>
                        </li>
                        <li class="custom-select-option {{ (request('age') == 'junior' || request('age') == 'young') ? 'selected' : '' }}" data-value="junior" data-label="Junior (7 - 12 months)">
                            <x-lucide-check class="option-check-icon" />
                            <span>Junior (7 - 12 months)</span>
                        </li>
                        <li class="custom-select-option {{ request('age') == 'adult' ? 'selected' : '' }}" data-value="adult" data-label="Adult (1 - 7 years)">
                            <x-lucide-check class="option-check-icon" />
                            <span>Adult (1 - 7 years)</span>
                        </li>
                        <li class="custom-select-option {{ request('age') == 'senior' ? 'selected' : '' }}" data-value="senior" data-label="Senior (8+ years)">
                            <x-lucide-check class="option-check-icon" />
                            <span>Senior (8+ years)</span>
                        </li>
                    </ul>
                </div>

                {{-- Gender Dropdown --}}
                <div class="filter-group custom-select-group" data-filter="gender">
                    <input type="hidden" name="gender" value="{{ request('gender') }}">
                    <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select-label">
                            {{ request('gender') ? ucfirst(request('gender')) : 'All genders' }}
                        </span>
                        <x-lucide-chevron-down class="custom-select-arrow" />
                    </button>
                    <ul class="custom-select-dropdown" role="listbox">
                        <li class="custom-select-option {{ !request('gender') ? 'selected' : '' }}" data-value="" data-label="All genders">
                            <x-lucide-check class="option-check-icon" />
                            <span>All genders</span>
                        </li>
                        <li class="custom-select-option {{ request('gender') == 'male' ? 'selected' : '' }}" data-value="male" data-label="Male">
                            <x-lucide-check class="option-check-icon" />
                            <span>Male</span>
                        </li>
                        <li class="custom-select-option {{ request('gender') == 'female' ? 'selected' : '' }}" data-value="female" data-label="Female">
                            <x-lucide-check class="option-check-icon" />
                            <span>Female</span>
                        </li>
                    </ul>
                </div>

                {{-- Reset Button --}}
                <div class="filter-action">
                    <a href="{{ route('home') }}" class="btn-filter-reset-link" title="Reset all filters">
                        <x-lucide-rotate-ccw style="width: 14px; height: 14px;" />
                        <span>Reset</span>
                    </a>
                </div>

                {{-- Mobile: Filter toggle button (phone only, hidden on tablet+) --}}
                @php
                    $activeFilterCount = collect([request('species'), request('breed'), request('age'), request('gender')])->filter()->count();
                @endphp
                <button type="button" class="mobile-filter-btn" id="mobileFilterBtn" onclick="openFilterSheet()" aria-label="Open filters">
                    <x-lucide-sliders-horizontal style="width: 16px; height: 16px; stroke-width: 2.2;" />
                    @if($activeFilterCount > 0)
                        <span class="mobile-filter-badge" id="filterBadgeCount">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            </form>
        </div>

        {{-- Mobile Filter Bottom Sheet (phone only) --}}
        <div class="filter-sheet-backdrop" id="filterSheetBackdrop" onclick="closeFilterSheet()"></div>
        <div class="filter-bottom-sheet" id="filterBottomSheet" role="dialog" aria-modal="true" aria-label="Filter pets">
            <div class="filter-sheet-handle"></div>
            <div class="filter-sheet-header">
                <span class="filter-sheet-title-text">Filters</span>
                <button type="button" class="filter-sheet-reset-link" onclick="resetFilterSheet()">Reset</button>
            </div>
            <form action="{{ route('home') }}" method="GET" id="mobile-filter-form">
                <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                @if(request('keyword'))
                    <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                @endif
                {{-- Hidden inputs that get updated by chip selections --}}
                <input type="hidden" name="species" id="sheet-species-val" value="{{ request('species', '') }}">
                <input type="hidden" name="age" id="sheet-age-val" value="{{ request('age', '') }}">
                <input type="hidden" name="gender" id="sheet-gender-val" value="{{ request('gender', '') }}">
                <input type="hidden" name="breed" id="sheet-breed-val" value="{{ request('breed', '') }}">

                {{-- SPECIES chips --}}
                <div class="filter-sheet-chip-group">
                    <div class="filter-sheet-chip-label">SPECIES</div>
                    <div class="filter-sheet-chips">
                        <button type="button" class="filter-chip {{ !request('species') ? 'active' : '' }}" data-filter="species" data-value="">All</button>
                        <button type="button" class="filter-chip {{ request('species') == 'dog' ? 'active' : '' }}" data-filter="species" data-value="dog">Dog</button>
                        <button type="button" class="filter-chip {{ request('species') == 'cat' ? 'active' : '' }}" data-filter="species" data-value="cat">Cat</button>
                    </div>
                </div>

                {{-- AGE chips --}}
                <div class="filter-sheet-chip-group">
                    <div class="filter-sheet-chip-label">AGE</div>
                    <div class="filter-sheet-chips">
                        <button type="button" class="filter-chip {{ !request('age') ? 'active' : '' }}" data-filter="age" data-value="">Any</button>
                        <button type="button" class="filter-chip {{ request('age') == 'puppy_kitten' ? 'active' : '' }}" data-filter="age" data-value="puppy_kitten">Under 1 yr</button>
                        <button type="button" class="filter-chip {{ (request('age') == 'junior' || request('age') == 'young') ? 'active' : '' }}" data-filter="age" data-value="junior">1–3 yrs</button>
                        <button type="button" class="filter-chip {{ request('age') == 'adult' ? 'active' : '' }}" data-filter="age" data-value="adult">4+ yrs</button>
                    </div>
                </div>

                {{-- GENDER chips --}}
                <div class="filter-sheet-chip-group">
                    <div class="filter-sheet-chip-label">GENDER</div>
                    <div class="filter-sheet-chips">
                        <button type="button" class="filter-chip {{ !request('gender') ? 'active' : '' }}" data-filter="gender" data-value="">All</button>
                        <button type="button" class="filter-chip {{ request('gender') == 'male' ? 'active' : '' }}" data-filter="gender" data-value="male">Male</button>
                        <button type="button" class="filter-chip {{ request('gender') == 'female' ? 'active' : '' }}" data-filter="gender" data-value="female">Female</button>
                    </div>
                </div>

                {{-- BREED dropdown (styled select) --}}
                <div class="filter-sheet-chip-group">
                    <div class="filter-sheet-chip-label">BREED</div>
                    <select name="breed" id="sheet-breed" class="filter-sheet-select filter-sheet-breed-select">
                        <option value="">Any breed</option>
                        <option value="aspin" {{ request('breed') == 'aspin' ? 'selected' : '' }}>Aspin</option>
                        <option value="puspin" {{ request('breed') == 'puspin' ? 'selected' : '' }}>Puspin</option>
                        <option value="mix" {{ request('breed') == 'mix' ? 'selected' : '' }}>Mix Breed</option>
                    </select>
                </div>

                <button type="submit" class="filter-sheet-apply" id="filterSheetApplyBtn">
                    Show {{ $pets->count() }} pets
                </button>
            </form>
        </div>

        {{-- Status Filter Pills & Count --}}
        <div class="status-pills-row">
            <div class="status-pills-group" role="tablist" aria-label="Status filter">
                @php $currStatus = strtolower(request('status', 'all')); @endphp
                <button type="button" class="home-status-pill {{ $currStatus === 'all' ? 'active' : '' }}" onclick="setHomeStatus('all')">
                    <span>All</span>
                    <span class="home-status-badge">{{ $statusCounts['all'] ?? $pets->count() }}</span>
                </button>
                <button type="button" class="home-status-pill {{ $currStatus === 'available' ? 'active' : '' }}" onclick="setHomeStatus('available')">
                    <span>Available</span>
                    <span class="home-status-badge">{{ $statusCounts['available'] ?? 0 }}</span>
                </button>
                <button type="button" class="home-status-pill {{ $currStatus === 'pending' ? 'active' : '' }}" onclick="setHomeStatus('pending')">
                    <span>Pending</span>
                    <span class="home-status-badge">{{ $statusCounts['pending'] ?? 0 }}</span>
                </button>
                <button type="button" class="home-status-pill {{ $currStatus === 'verified' ? 'active' : '' }}" onclick="setHomeStatus('verified')">
                    <span>Verified</span>
                    <span class="home-status-badge">{{ $statusCounts['verified'] ?? 0 }}</span>
                </button>
            </div>
            <div class="status-pills-meta">
                Showing {{ $pets->count() }} pet{{ $pets->count() == 1 ? '' : 's' }}
            </div>
        </div>
    </div>
</section>

{{-- 3. Pets Feed Section (Dynamic Feed from Database) --}}
<section class="pets-section" id="pets">
    <div class="site-container">
        <div class="pets-grid">
            @forelse($pets as $pet)
                <x-pet-card :pet="$pet" />
            @empty
                <div class="empty-feed-card">
                    <span class="empty-icon">
                        <x-lucide-search-x class="empty-lucide-icon" />
                    </span>
                    <h3>No pets found matching your criteria</h3>
                    <p class="text-muted">Try clearing the search filters or check back soon for newly rescued barangay animals.</p>
                    <a href="{{ route('home') }}" class="btn-filter-yellow mt-2">
                        <x-lucide-rotate-ccw class="btn-reset-icon" />
                        <span>Clear Filters</span>
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- 4. Interactive Pet Details Modal Overlay (Styled exactly like Create Post Modal) --}}
<div 
    id="pet-details-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="modal-pet-name"
    style="display: none;"
    onclick="handleBackdropClick(event)"
>
    <div class="modal-dialog pet-details-dialog" onclick="event.stopPropagation()">
        {{-- Close Button (Pinned at top right corner) --}}
        <button 
            type="button" 
            class="modal-close-btn" 
            onclick="closePetModal()" 
            aria-label="Close modal"
        >
            <x-lucide-x class="modal-close-icon" />
        </button>

        {{-- Hidden element for safe backwards compatibility --}}
        <span id="modal-image-badge" style="display: none;"></span>

        {{-- Modal Header --}}
        <div class="pet-details-header">
            <div class="pet-header-left">
                <div class="pet-header-title-row">
                    <h2 id="modal-pet-name" class="pet-modal-title">Pet Name</h2>
                    <span id="modal-status-badge" class="badge-modal-status status-available">Available</span>
                    <span id="modal-verified-badge" class="poster-verified-badge" style="display: none; padding: 0.2rem 0.65rem; font-size: 0.78rem;">
                        <span class="verified-check-circle" aria-hidden="true">✓</span>
                        <span>Verified</span>
                    </span>
                </div>
                <p class="pet-modal-subtitle">
                    <x-lucide-map-pin style="width: 14px; height: 14px; stroke-width: 2.2; color: #D97706; flex-shrink: 0;" />
                    <span id="modal-pet-location">Brgy. 176-F, Caloocan City</span>
                </p>
            </div>
        </div>

        {{-- Pet Details Body (Wide 2-Column Balanced Layout) --}}
        <div class="pet-details-content pet-details-grid-layout">
            {{-- Left Column: Larger Clickable Photo & Rescuer Card --}}
            <div class="pet-details-col-left">
                <div class="form-section pet-photo-section">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <label class="form-label font-bold" style="margin-bottom: 0;">1. Animal Photo</label>
                        <span id="modal-photo-counter" class="modal-photo-counter-badge" style="display: none;">1 / 1</span>
                    </div>
                    <div 
                        class="pet-photo-frame" 
                        style="position: relative;"
                        onclick="openFullscreenImage()" 
                        title="Click to view full screen" 
                        role="button" 
                        tabindex="0"
                        onkeydown="if(event.key==='Enter'||event.key===' '){openFullscreenImage();event.preventDefault();}"
                    >
                        <img id="modal-pet-image" src="" alt="Pet Photo" class="pet-frame-image" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80';">
                        
                        {{-- Cycle Arrows (visible when pet has multiple photos) --}}
                        <button 
                            type="button" 
                            id="modal-gallery-prev" 
                            class="modal-gallery-arrow-btn prev" 
                            onclick="event.stopPropagation(); cycleModalGallery(-1);" 
                            style="display: none;"
                            aria-label="Previous photo"
                            title="Previous photo"
                        >
                            &#10094;
                        </button>
                        <button 
                            type="button" 
                            id="modal-gallery-next" 
                            class="modal-gallery-arrow-btn next" 
                            onclick="event.stopPropagation(); cycleModalGallery(1);" 
                            style="display: none;"
                            aria-label="Next photo"
                            title="Next photo"
                        >
                            &#10095;
                        </button>

                        <div class="photo-expand-badge">
                            <x-lucide-maximize-2 class="expand-badge-icon" />
                            <span>View Full Screen</span>
                        </div>
                    </div>

                    {{-- Dynamic Thumbnail Strip for Multi-Image Gallery --}}
                    <div id="modal-gallery-thumbnails" class="modal-gallery-thumbnails-strip" style="display: none;"></div>
                    
                    <div class="pet-rescuer-card">
                        <div class="rescuer-icon-badge">
                            <x-lucide-user style="width: 18px; height: 18px; stroke-width: 2.2; color: #4A2E2B;" />
                        </div>
                        <div class="rescuer-text-group">
                            <span class="rescuer-sublabel">Rescued &amp; Posted By</span>
                            <div style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                                <strong id="modal-spec-posted" class="rescuer-name">Rescuer Name</strong>
                                <span id="modal-rescuer-verified" class="poster-verified-badge" style="display: none; padding: 0.12rem 0.55rem; font-size: 0.74rem;">
                                    <span class="verified-check-circle" aria-hidden="true">✓</span>
                                    <span>Verified</span>
                                </span>
                            </div>
                        </div>
                        <span class="rescuer-tag" id="modal-rescuer-id">#RES-0492</span>
                    </div>
                </div>
            </div>

            {{-- Right Column: Basic Info, Health, and Story --}}
            <div class="pet-details-col-right">
                {{-- 2. Basic Information --}}
                <div class="form-section pet-info-section">
                    <label class="form-label font-bold">2. Basic Information</label>
                    
                    <div class="pet-info-grid">
                        <div class="info-cell">
                            <span class="info-cell-label">Species</span>
                            <span id="modal-spec-species" class="info-cell-val">Dog</span>
                        </div>
                        <div class="info-cell">
                            <span class="info-cell-label">Breed / Mix</span>
                            <span id="modal-spec-breed" class="info-cell-val">Aspin</span>
                        </div>
                        <div class="info-cell">
                            <span class="info-cell-label">Age</span>
                            <span id="modal-spec-age" class="info-cell-val">Young</span>
                        </div>
                        <div class="info-cell">
                            <span class="info-cell-label">Gender</span>
                            <span id="modal-spec-gender" class="info-cell-val">Male</span>
                        </div>
                        <div class="info-cell info-cell-date">
                            <span class="info-cell-label">Date Posted</span>
                            <span id="modal-spec-date" class="info-cell-val">October 20, 2026</span>
                        </div>
                    </div>
                </div>

                {{-- 3. Health & Medical Status --}}
                <div class="form-section pet-health-section">
                    <label class="form-label font-bold">3. Health &amp; Medical Status</label>
                    <div class="health-status-box">
                        <x-lucide-activity style="width: 15px; height: 15px; stroke-width: 2.2; color: #059669; flex-shrink: 0;" />
                        <p id="modal-spec-health" class="health-status-text">
                            Vaccinated &amp; Dewormed
                        </p>
                    </div>
                </div>

                {{-- 4. Rescue Story & Description --}}
                <div class="form-section pet-story-section">
                    <label class="form-label font-bold">4. Rescue Story &amp; Description</label>
                    <div class="story-display-box">
                        <p id="modal-spec-story" class="story-display-text">
                            Rescued and looking for a loving permanent home.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer: Full Width Actions & Own Post Notice --}}
        <div class="pet-modal-footer">
            {{-- Message shown when viewing own post --}}
            <div class="modal-own-post-notice" id="modal-own-post-notice" style="display: none;">
                <x-lucide-info style="width: 16px; height: 16px; stroke-width: 2; color: #D97706; flex-shrink: 0;" />
                <span>This is your own pet post. Manage it under <a href="{{ route('my-posts') }}" style="color: #4A2E2B; text-decoration: underline; font-weight: 700;">My Posts</a>.</span>
            </div>

            {{-- Action Buttons --}}
            <div class="pet-modal-actions" id="modal-actions-area">
                @auth
                    <button 
                        type="button" 
                        class="btn-draft btn-contact-poster"
                        onclick="handleContactAction()"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;"
                    >
                        <x-lucide-message-square style="width: 16px; height: 16px; stroke-width: 2;" />
                        <span>Contact Poster</span>
                    </button>
                    <button 
                        type="button" 
                        class="btn-submit-post btn-request-adopt"
                        onclick="handleAdoptAction()"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;"
                    >
                        <x-lucide-heart-handshake style="width: 16px; height: 16px; stroke-width: 2;" />
                        <span>Request Adoption</span>
                    </button>
                @else
                    <div class="modal-guest-auth-prompt">
                        <span class="guest-prompt-text" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <x-lucide-paw-print style="width: 16px; height: 16px; stroke-width: 2; color: #8C756B;" />
                            <span>Want to adopt or contact the rescuer?</span>
                        </span>
                        <button 
                            type="button" 
                            class="btn-submit-post btn-login-to-adopt" 
                            onclick="closePetModal(); openLoginModal();"
                            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;"
                        >
                            <x-lucide-log-in style="width: 16px; height: 16px; stroke-width: 2;" />
                            <span>Log In to Adopt</span>
                        </button>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>

{{-- 4b. Fullscreen Pet Image Carousel Viewer Modal --}}
<div 
    id="pet-fullscreen-image-modal" 
    class="fullscreen-carousel-backdrop" 
    style="display: none;" 
    role="dialog" 
    aria-modal="true" 
    aria-label="Full screen pet image carousel"
    onclick="handleFullscreenBackdropClick(event)"
>
    {{-- Header Bar (Pet Name, Counter, Close Button) --}}
    <div class="fullscreen-carousel-header" onclick="event.stopPropagation()">
        <div class="fs-carousel-meta-info">
            <h3 id="fs-carousel-pet-name" class="fs-carousel-title">Pet Name</h3>
            <span id="fs-carousel-counter" class="fs-carousel-counter-badge">1 / 1</span>
        </div>
        <button 
            type="button" 
            class="fullscreen-close-btn" 
            onclick="closeFullscreenImage()" 
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
            id="fs-carousel-prev-btn" 
            class="fs-carousel-nav-btn fs-prev-btn" 
            onclick="cycleFullscreenCarousel(-1)" 
            aria-label="Previous photo"
            title="Previous photo (Left arrow key)"
        >
            &#10094;
        </button>

        {{-- Centered Image Stage --}}
        <div class="fs-carousel-slide-box">
            <img id="pet-fullscreen-img" src="" alt="Full screen pet photo" class="fullscreen-img fs-carousel-img" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=1200&q=80';">
        </div>

        {{-- Next Arrow Button --}}
        <button 
            type="button" 
            id="fs-carousel-next-btn" 
            class="fs-carousel-nav-btn fs-next-btn" 
            onclick="cycleFullscreenCarousel(1)" 
            aria-label="Next photo"
            title="Next photo (Right arrow key)"
        >
            &#10095;
        </button>
    </div>

    {{-- Bottom Thumbnail Indicators Strip & Keyboard Hints --}}
    <div class="fullscreen-carousel-footer" onclick="event.stopPropagation()">
        <div id="fs-carousel-thumbnails-strip" class="fs-carousel-thumbnails-strip"></div>
        <div class="fullscreen-image-bar" style="justify-content: center;">
            <span id="pet-fullscreen-caption" class="fullscreen-caption" style="display: none;"></span>
            <span class="fullscreen-hint">Use <strong>&larr; &rarr;</strong> arrow keys to browse &bull; Press <strong>ESC</strong> to exit</span>
        </div>
    </div>
</div>

{{-- 5. Adoption Request Application Modal --}}
@auth
<div 
    id="adoption-request-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="adopt-modal-title"
    style="display: none; z-index: 1060;" 
    onclick="handleAdoptBackdropClick(event)"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 600px; background: #ffffff; border-radius: 1.5rem; padding: 2.25rem 2rem; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22); border: 1px solid #F3E8D8; position: relative;">
        {{-- Close Button (Pinned at top right) --}}
        <button 
            type="button" 
            class="modal-close-btn" 
            onclick="closeAdoptionRequestModal()" 
            aria-label="Close modal"
            style="position: sticky; top: 1rem; align-self: flex-end; margin-left: auto; margin-right: -0.75rem; margin-bottom: -2.25rem; z-index: 100; background: #FAF5EE; border: 1px solid rgba(74, 46, 43, 0.12); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #4A2E2B; transition: all 0.15s ease; box-shadow: 0 2px 6px rgba(74, 46, 43, 0.08);"
            onmouseover="this.style.background='#FDE047'"
            onmouseout="this.style.background='#FAF5EE'"
        >
            <x-lucide-x style="width: 18px; height: 18px; stroke-width: 2.2;" />
        </button>

        {{-- Header --}}
        <div style="margin-bottom: 1.25rem; padding-right: 2.5rem;">
            <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: #FEF08A; color: #4A2E2B; font-size: 0.76rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; margin-bottom: 0.5rem;">
                <x-lucide-heart-handshake style="width: 14px; height: 14px; stroke-width: 2.4;" />
                <span>Adoption Application</span>
            </div>
            <h2 id="adopt-modal-title" style="font-size: 1.45rem; font-weight: 800; color: #4A2E2B; margin: 0 0 0.35rem 0; line-height: 1.25;">
                <span id="adopt-modal-title-text">Request Adoption</span>
            </h2>
            <p style="font-size: 0.88rem; color: #52473D; margin: 0; line-height: 1.45;">
                Submit your adoption request directly to the barangay pet rescuer.
            </p>
        </div>

        {{-- Pet Summary Card --}}
        <div style="display: flex; align-items: center; gap: 1rem; background: #FAF5EE; border: 1px solid #EADDCF; border-radius: 1rem; padding: 0.9rem 1.15rem; margin-bottom: 1.25rem;">
            <img id="adopt-summary-img" src="" alt="Pet" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80';" style="width: 68px; height: 68px; border-radius: 0.75rem; object-fit: cover; border: 1px solid #D5C2B1; flex-shrink: 0;" />
            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <h3 id="adopt-summary-name" style="font-size: 1.15rem; font-weight: 800; color: #4A2E2B; margin: 0;"></h3>
                    <span id="adopt-summary-status" class="badge-modal-status status-available" style="font-size: 0.72rem; padding: 0.2rem 0.55rem; border-radius: 9999px;">Available</span>
                </div>
                <div id="adopt-summary-meta" style="font-size: 0.82rem; color: #78350F; margin-top: 0.25rem; font-weight: 500;"></div>
                <div style="font-size: 0.78rem; color: #8C756B; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <span>Rescuer: <strong id="adopt-summary-rescuer" style="color: #4A2E2B;"></strong></span>
                    <span>•</span>
                    <x-lucide-map-pin style="width: 13px; height: 13px; stroke-width: 2; color: #D97706; flex-shrink: 0;" />
                    <span id="adopt-summary-location"></span>
                </div>
            </div>
        </div>

        {{-- Static Barangay 176-F Context Notice --}}
        <div style="display: flex; align-items: center; gap: 0.55rem; background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 0.85rem; padding: 0.7rem 1rem; margin-bottom: 1.25rem; font-size: 0.82rem; color: #854D0E; line-height: 1.45;">
            <x-lucide-map-pin style="width: 17px; height: 17px; stroke-width: 2.2; color: #D97706; flex-shrink: 0;" />
            <span><strong>Brgy. 176-F Exclusive:</strong> This pet adoption service is exclusively for residents of <strong>Brgy. 176-F, Caloocan City</strong>.</span>
        </div>

        {{-- Application Form --}}
        <form method="POST" action="{{ route('adoption-requests.store') }}" id="adoption-request-form">
            @csrf
            <input type="hidden" name="pet_id" id="adopt-input-pet-id" value="" />

            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label for="adopt-input-applicant" class="form-label" style="font-weight: 700; font-size: 0.86rem; color: #4A2E2B; margin-bottom: 0.4rem; display: block;">
                        Applicant Name <span style="color: #DC2626;">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="applicant_name" 
                        id="adopt-input-applicant" 
                        class="form-control" 
                        style="padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1.5px solid #E5E7EB; font-size: 0.92rem; background: #FAF9F6; width: 100%; box-sizing: border-box;"
                        value="{{ Auth::user()->name }}" 
                        required 
                    />
                </div>

                <div class="form-group">
                    <label for="adopt-input-contact" class="form-label" style="font-weight: 700; font-size: 0.86rem; color: #4A2E2B; margin-bottom: 0.4rem; display: block;">
                        Contact Number <span style="color: #DC2626;">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="contact_number" 
                        id="adopt-input-contact" 
                        class="form-control" 
                        style="padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1.5px solid #E5E7EB; font-size: 0.92rem; background: #FAF9F6; width: 100%; box-sizing: border-box;"
                        placeholder="e.g. 0917-123-4567" 
                        required 
                    />
                </div>
            </div>

            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label for="adopt-input-email" class="form-label" style="font-weight: 700; font-size: 0.86rem; color: #4A2E2B; margin-bottom: 0.4rem; display: block;">
                        Email Address <span style="color: #DC2626;">*</span>
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="adopt-input-email" 
                        class="form-control" 
                        style="padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1.5px solid #E5E7EB; font-size: 0.92rem; background: #FAF9F6; width: 100%; box-sizing: border-box;"
                        value="{{ Auth::user()->email }}" 
                        required 
                    />
                </div>

                <div class="form-group">
                    <label for="adopt-input-address" class="form-label" style="display: flex; align-items: center; justify-content: space-between; font-weight: 700; font-size: 0.86rem; color: #4A2E2B; margin-bottom: 0.4rem;">
                        <span>Package / Block / Lot / Address <span style="color: #DC2626;">*</span></span>
                    </label>
                    <input 
                        type="text" 
                        name="address" 
                        id="adopt-input-address" 
                        class="form-control" 
                        style="padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1.5px solid #E5E7EB; font-size: 0.92rem; background: #FAF9F6; width: 100%; box-sizing: border-box;"
                        placeholder="e.g. Package 6, Block 12, Lot 4" 
                        required 
                    />
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="adopt-input-message" class="form-label" style="font-weight: 700; font-size: 0.86rem; color: #4A2E2B; margin-bottom: 0.4rem; display: block;">
                    Message to Rescuer / Note <span style="font-size: 0.76rem; color: #8C756B; font-weight: normal;">(Optional)</span>
                </label>
                <textarea 
                    name="message" 
                    id="adopt-input-message" 
                    class="form-control" 
                    rows="3" 
                    placeholder="Tell the rescuer about your living situation, pet experience, and readiness to provide a loving home..."
                    style="padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1.5px solid #E5E7EB; font-size: 0.92rem; background: #FAF9F6; resize: vertical; width: 100%; box-sizing: border-box;"
                ></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; border-top: 1px solid #F3E8D8; padding-top: 1.25rem;">
                <button 
                    type="button" 
                    onclick="closeAdoptionRequestModal()"
                    style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.75rem 1.35rem; background: #FAF5EE; color: #4A2E2B; border: none; border-radius: 0.75rem; font-size: 0.92rem; font-weight: 600; cursor: pointer; transition: background 0.15s ease;"
                    onmouseover="this.style.background='#F3E8D8'"
                    onmouseout="this.style.background='#FAF5EE'"
                >
                    <x-lucide-x style="width: 16px; height: 16px; stroke-width: 2;" />
                    <span>Cancel</span>
                </button>
                <button 
                    type="submit" 
                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.65rem; background: #FEF08A; color: #4A2E2B; border: none; border-radius: 0.75rem; font-size: 0.92rem; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px rgba(234, 179, 8, 0.25); transition: all 0.15s ease;"
                    onmouseover="this.style.background='#FDE047'; this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.background='#FEF08A'; this.style.transform='translateY(0)'"
                >
                    <x-lucide-heart-handshake style="width: 17px; height: 17px; stroke-width: 2.2;" />
                    <span>Submit Adoption Request</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endauth

{{-- Floating Create Post Button (Regular users only, Home page only) --}}
@auth
    @if(!Auth::user()->is_admin)
        <button 
            type="button" 
            class="floating-create-post-btn" 
            onclick="openCreatePostModal()" 
            title="Create Post" 
            aria-label="Create post"
            id="floatingCreatePostBtn"
        >
            <x-lucide-plus class="floating-create-post-icon" />
        </button>
    @endif
@endauth

{{-- 6. Create Post Modal (Post a Rescued Animal for Adoption) --}}
<x-create-post-modal />

{{-- Pass Dynamic Pet Data into JavaScript --}}
@php
    // Use controller-passed pending request IDs or fallback query for user lookup
    $myPendingPetIds = $myPendingPetIds ?? (Auth::check()
        ? \App\Models\AdoptionRequest::where('user_id', Auth::id())
            ->where('status', 'Pending')
            ->pluck('pet_id')
            ->all()
        : []);

    $petsJson = $pets->map(function($p) use ($myPendingPetIds) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'species' => $p->species,
            'species_label' => $p->species . ' (' . (strtolower($p->species) === 'cat' ? 'Feline' : 'Canine') . ')',
            'breed' => $p->breed ?? 'Mixed Breed',
            'breed_full' => $p->breed ? ($p->breed . ' (Barangay Rescue)') : 'Philippine Native / Mixed Breed',
            'age' => $p->age,
            'age_full' => $p->age,
            'gender' => $p->gender,
            'gender_full' => $p->gender . ($p->is_spayed_neutered ? ' (Spayed/Neutered)' : ' (Intact)'),
            'status' => $p->status,
            'location' => $p->location ?? 'Brgy. 176-F, Caloocan City',
            'rescuer_id' => $p->rescuer_id ?? '#RES-0492',
            'posted_by' => $p->posted_by ?? 'Barangay Animal Welfare Committee',
            'date_posted' => $p->created_at ? $p->created_at->format('F d, Y') : 'Recent',
            'health_condition' => trim(($p->vaccination_status ? $p->vaccination_status . ', ' : '') . ($p->deworming_status ? $p->deworming_status . ', ' : '') . ($p->special_health_notes ?? 'Healthy & Active'), ', '),
            'description' => $p->description,
            'story' => $p->description,
            'image' => $p->image_url,
            'images' => $p->image_urls,
            'user_id' => $p->user_id,
            'is_own_post' => Auth::check() && ($p->user_id === Auth::id()),
            'has_pending_request' => in_array($p->id, $myPendingPetIds),
            'is_verified' => (bool) (($p->is_verified ?? false) || ($p->user?->is_verified ?? false)),
        ];
    });
@endphp

<script>
    const currentUserId = {{ Auth::check() ? Auth::id() : 'null' }};
    const kupkopPetsData = @json($petsJson);
    let activePetRecord = null;
    let activeGallery = [];
    let activeGalleryIndex = 0;

    window.switchCardPhoto = function(event, petId, targetIndex) {
        if (event) event.stopPropagation();
        const slides = document.querySelectorAll('.pet-card-slide-' + petId);
        slides.forEach(function(slide, idx) {
            slide.style.display = (idx === targetIndex) ? 'block' : 'none';
        });
        const dots = document.querySelectorAll('.card-dot-' + petId);
        dots.forEach(function(dot, idx) {
            if (idx === targetIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    };

    function openPetModal(petId) {
        const pet = kupkopPetsData.find(p => p.id === petId);
        if (!pet) return;

        activePetRecord = pet;
        activeGallery = (pet.images && pet.images.length > 0) ? pet.images : [pet.image];
        activeGalleryIndex = 0;

        // Populate Header
        document.getElementById('modal-pet-name').textContent = pet.name;
        document.getElementById('modal-pet-location').textContent = pet.location || 'Brgy. 176-F, Caloocan City';
        
        // Status badge
        const statusBadge = document.getElementById('modal-status-badge');
        if (statusBadge) {
            statusBadge.textContent = pet.status;
            if (pet.status.toLowerCase() === 'pending') {
                statusBadge.className = 'badge-modal-status status-pending';
            } else if (pet.status.toLowerCase() === 'adopted') {
                statusBadge.className = 'badge-modal-status status-adopted';
            } else {
                statusBadge.className = 'badge-modal-status status-available';
            }
        }

        // Verified badges
        const modalVerifiedBadge = document.getElementById('modal-verified-badge');
        if (modalVerifiedBadge) {
            modalVerifiedBadge.style.display = pet.is_verified ? 'inline-flex' : 'none';
        }
        const modalRescuerVerified = document.getElementById('modal-rescuer-verified');
        if (modalRescuerVerified) {
            modalRescuerVerified.style.display = pet.is_verified ? 'inline-flex' : 'none';
        }

        // Left Column: Image Gallery & Rescuer
        renderModalGallery();
        const rescuerIdEl = document.getElementById('modal-rescuer-id');
        if (rescuerIdEl) {
            rescuerIdEl.textContent = pet.rescuer_id || '#RES-0492';
        }

        // Right Column: Specifications
        const specSpecies = document.getElementById('modal-spec-species');
        if (specSpecies) specSpecies.textContent = pet.species;
        
        const specBreed = document.getElementById('modal-spec-breed');
        if (specBreed) specBreed.textContent = pet.breed;

        const specAge = document.getElementById('modal-spec-age');
        if (specAge) specAge.textContent = pet.age;

        const specGender = document.getElementById('modal-spec-gender');
        if (specGender) specGender.textContent = pet.gender;

        const specHealth = document.getElementById('modal-spec-health');
        if (specHealth) specHealth.textContent = pet.health_condition || 'Vaccinated & Dewormed';

        const specDate = document.getElementById('modal-spec-date');
        if (specDate) specDate.textContent = pet.date_posted || 'Recent';

        const specPosted = document.getElementById('modal-spec-posted');
        if (specPosted) specPosted.textContent = pet.posted_by;

        const specStory = document.getElementById('modal-spec-story');
        if (specStory) specStory.textContent = pet.story;

        // Toggle Action Buttons vs Own Post Notice
        const actionsArea = document.getElementById('modal-actions-area');
        const ownPostNotice = document.getElementById('modal-own-post-notice');
        const isOwn = pet.is_own_post || (currentUserId !== null && pet.user_id === currentUserId);

        if (isOwn) {
            if (actionsArea) actionsArea.style.display = 'none';
            if (ownPostNotice) ownPostNotice.style.display = 'block';
        } else {
            if (actionsArea) actionsArea.style.display = 'block';
            if (ownPostNotice) ownPostNotice.style.display = 'none';

            // Disable the "Request Adoption" button if user already has a pending request
            const adoptBtn = actionsArea ? actionsArea.querySelector('.btn-request-adopt') : null;
            if (adoptBtn) {
                const isPending = pet.status && pet.status.toLowerCase() === 'pending';
                const hasPending = pet.has_pending_request === true;
                if (hasPending || isPending) {
                    adoptBtn.disabled = true;
                    adoptBtn.style.opacity = '0.5';
                    adoptBtn.style.cursor = 'not-allowed';
                    adoptBtn.title = hasPending
                        ? 'You already have a pending request for this pet.'
                        : 'This pet already has a pending adoption request.';
                } else {
                    adoptBtn.disabled = false;
                    adoptBtn.style.opacity = '';
                    adoptBtn.style.cursor = '';
                    adoptBtn.title = '';
                }
            }
        }

        // Show Modal
        const modal = document.getElementById('pet-details-modal');
        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
    }

    function renderModalGallery() {
        if (!activeGallery || activeGallery.length === 0) return;
        const fallbackUrl = (activePetRecord && (activePetRecord.species || '').toLowerCase() === 'cat')
            ? 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80'
            : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80';

        const mainImg = document.getElementById('modal-pet-image');
        if (mainImg) {
            mainImg.onerror = function() {
                this.onerror = null;
                this.src = fallbackUrl;
            };
            mainImg.src = activeGallery[activeGalleryIndex] || fallbackUrl;
            mainImg.alt = activePetRecord ? activePetRecord.name : 'Pet Photo';
        }

        const counter = document.getElementById('modal-photo-counter');
        const prevBtn = document.getElementById('modal-gallery-prev');
        const nextBtn = document.getElementById('modal-gallery-next');
        const thumbsContainer = document.getElementById('modal-gallery-thumbnails');

        const total = activeGallery.length;
        if (counter) {
            if (total > 1) {
                counter.textContent = (activeGalleryIndex + 1) + ' / ' + total;
                counter.style.display = 'inline-block';
            } else {
                counter.style.display = 'none';
            }
        }

        if (prevBtn) prevBtn.style.display = total > 1 ? 'flex' : 'none';
        if (nextBtn) nextBtn.style.display = total > 1 ? 'flex' : 'none';

        if (thumbsContainer) {
            if (total > 1) {
                thumbsContainer.innerHTML = '';
                activeGallery.forEach(function(url, idx) {
                    const thumb = document.createElement('button');
                    thumb.type = 'button';
                    thumb.className = 'modal-gallery-thumb' + (idx === activeGalleryIndex ? ' active' : '');
                    thumb.setAttribute('aria-label', 'View photo ' + (idx + 1));
                    thumb.onclick = function() {
                        swapModalGalleryImage(idx);
                    };

                    const img = document.createElement('img');
                    img.onerror = function() {
                        this.onerror = null;
                        this.src = fallbackUrl;
                    };
                    img.src = url;
                    img.alt = 'Thumbnail ' + (idx + 1);
                    thumb.appendChild(img);
                    thumbsContainer.appendChild(thumb);
                });
                thumbsContainer.style.display = 'flex';
            } else {
                thumbsContainer.style.display = 'none';
                thumbsContainer.innerHTML = '';
            }
        }
    }

    function swapModalGalleryImage(index) {
        if (!activeGallery || !activeGallery[index]) return;
        activeGalleryIndex = index;
        renderModalGallery();
    }

    function cycleModalGallery(direction) {
        if (!activeGallery || activeGallery.length <= 1) return;
        activeGalleryIndex = (activeGalleryIndex + direction + activeGallery.length) % activeGallery.length;
        renderModalGallery();
    }

    function swapMainImage(index) {
        swapModalGalleryImage(index);
    }

    function closePetModal() {
        closeFullscreenImage();
        const modal = document.getElementById('pet-details-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }

    function openFullscreenImage(optionalIndex = null) {
        if (!activeGallery || activeGallery.length === 0) return;
        if (optionalIndex !== null && typeof optionalIndex === 'number' && activeGallery[optionalIndex]) {
            activeGalleryIndex = optionalIndex;
        }

        const fsModal = document.getElementById('pet-fullscreen-image-modal');
        const petNameEl = document.getElementById('modal-pet-name');
        const fsPetName = document.getElementById('fs-carousel-pet-name');
        if (fsPetName) {
            fsPetName.textContent = activePetRecord ? activePetRecord.name : (petNameEl ? petNameEl.textContent : 'Pet Photo');
        }

        renderFullscreenCarouselSlide();

        if (fsModal) {
            fsModal.style.display = 'flex';
        }
    }

    function renderFullscreenCarouselSlide() {
        if (!activeGallery || activeGallery.length === 0) return;
        const currentSrc = activeGallery[activeGalleryIndex];
        const fsImg = document.getElementById('pet-fullscreen-img');
        const fsCounter = document.getElementById('fs-carousel-counter');
        const prevBtn = document.getElementById('fs-carousel-prev-btn');
        const nextBtn = document.getElementById('fs-carousel-next-btn');
        const thumbsStrip = document.getElementById('fs-carousel-thumbnails-strip');
        const total = activeGallery.length;

        if (fsImg && currentSrc) {
            fsImg.classList.remove('fade-in');
            // Trigger browser reflow to restart fade animation
            void fsImg.offsetWidth;
            const fallbackFs = (activePetRecord && (activePetRecord.species || '').toLowerCase() === 'cat')
                ? 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=1200&q=80'
                : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=1200&q=80';
            fsImg.onerror = function() {
                this.onerror = null;
                this.src = fallbackFs;
            };
            fsImg.src = currentSrc;
            fsImg.alt = (activePetRecord ? activePetRecord.name : 'Pet') + ' - Photo ' + (activeGalleryIndex + 1);
            fsImg.classList.add('fade-in');
        }

        if (fsCounter) {
            if (total > 1) {
                fsCounter.textContent = (activeGalleryIndex + 1) + ' / ' + total;
                fsCounter.style.display = 'inline-block';
            } else {
                fsCounter.textContent = '1 / 1';
                fsCounter.style.display = 'inline-block';
            }
        }

        if (prevBtn) prevBtn.style.display = total > 1 ? 'flex' : 'none';
        if (nextBtn) nextBtn.style.display = total > 1 ? 'flex' : 'none';

        if (thumbsStrip) {
            if (total > 1) {
                thumbsStrip.innerHTML = '';
                activeGallery.forEach(function(url, idx) {
                    const thumbBtn = document.createElement('button');
                    thumbBtn.type = 'button';
                    thumbBtn.className = 'fs-thumb-pill' + (idx === activeGalleryIndex ? ' is-active' : '');
                    thumbBtn.setAttribute('aria-label', 'View photo ' + (idx + 1));
                    thumbBtn.onclick = function(e) {
                        e.stopPropagation();
                        setFullscreenCarouselSlide(idx);
                    };

                    const img = document.createElement('img');
                    img.onerror = function() {
                        this.onerror = null;
                        this.src = fallbackUrl;
                    };
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

        // Keep Pet Details modal gallery synchronized in background
        renderModalGallery();
    }

    function cycleFullscreenCarousel(direction) {
        if (!activeGallery || activeGallery.length <= 1) return;
        activeGalleryIndex = (activeGalleryIndex + direction + activeGallery.length) % activeGallery.length;
        renderFullscreenCarouselSlide();
    }

    function setFullscreenCarouselSlide(index) {
        if (!activeGallery || !activeGallery[index]) return;
        activeGalleryIndex = index;
        renderFullscreenCarouselSlide();
    }

    function closeFullscreenImage() {
        const fsModal = document.getElementById('pet-fullscreen-image-modal');
        if (fsModal) {
            fsModal.style.display = 'none';
        }
    }

    function handleFullscreenBackdropClick(event) {
        if (event.target.id === 'pet-fullscreen-image-modal') {
            closeFullscreenImage();
        }
    }

    function handleBackdropClick(event) {
        if (event.target.id === 'pet-details-modal') {
            closePetModal();
        }
    }

    // Touch swipe navigation for mobile
    let fsTouchStartX = 0;
    let fsTouchEndX = 0;
    const fsCarouselModal = document.getElementById('pet-fullscreen-image-modal');
    if (fsCarouselModal) {
        fsCarouselModal.addEventListener('touchstart', function(e) {
            fsTouchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        fsCarouselModal.addEventListener('touchend', function(e) {
            fsTouchEndX = e.changedTouches[0].screenX;
            const diff = fsTouchStartX - fsTouchEndX;
            if (Math.abs(diff) > 40) {
                if (diff > 0) {
                    cycleFullscreenCarousel(1); // Swipe left -> Next
                } else {
                    cycleFullscreenCarousel(-1); // Swipe right -> Prev
                }
            }
        }, { passive: true });
    }

    document.addEventListener('keydown', function(event) {
        const fsModal = document.getElementById('pet-fullscreen-image-modal');
        const isFsOpen = fsModal && fsModal.style.display !== 'none';

        if (isFsOpen) {
            if (event.key === 'ArrowLeft') {
                cycleFullscreenCarousel(-1);
                event.preventDefault();
                return;
            }
            if (event.key === 'ArrowRight') {
                cycleFullscreenCarousel(1);
                event.preventDefault();
                return;
            }
            if (event.key === 'Escape') {
                closeFullscreenImage();
                event.preventDefault();
                return;
            }
        }

        if (event.key === 'Escape') {
            closePetModal();
            closeAdoptionRequestModal();
        }
    });

    function handleAdoptAction() {
        if (!activePetRecord) return;
        if (!currentUserId) {
            closePetModal();
            openLoginModal();
            return;
        }

        if (activePetRecord.is_own_post || (currentUserId !== null && activePetRecord.user_id === currentUserId)) {
            alert('This is your own post. You can manage it in My Posts.');
            return;
        }

        if (activePetRecord.has_pending_request) {
            alert('You already have a pending adoption request for ' + activePetRecord.name + '. Check "My Posts" to track your application.');
            return;
        }

        if (activePetRecord.status && activePetRecord.status.toLowerCase() !== 'available') {
            alert('This pet is currently ' + activePetRecord.status.toLowerCase() + ' and is not open for new adoption requests.');
            return;
        }

        closePetModal();
        openAdoptionRequestModal(activePetRecord);
    }

    function openAdoptionRequestModal(pet) {
        const modal = document.getElementById('adoption-request-modal');
        if (!modal) return;

        const inputPetId = document.getElementById('adopt-input-pet-id');
        if (inputPetId) inputPetId.value = pet.id;

        const titleTextEl = document.getElementById('adopt-modal-title-text');
        if (titleTextEl) {
            titleTextEl.textContent = 'Apply to Adopt ' + pet.name;
        } else {
            const titleEl = document.getElementById('adopt-modal-title');
            if (titleEl) titleEl.textContent = 'Apply to Adopt ' + pet.name;
        }

        const imgEl = document.getElementById('adopt-summary-img');
        if (imgEl) {
            imgEl.src = pet.image;
            imgEl.alt = pet.name;
        }

        const nameEl = document.getElementById('adopt-summary-name');
        if (nameEl) nameEl.textContent = pet.name;

        const metaEl = document.getElementById('adopt-summary-meta');
        if (metaEl) metaEl.textContent = (pet.breed || 'Mixed Breed') + ' • ' + pet.age + ' • ' + pet.gender;

        const rescuerEl = document.getElementById('adopt-summary-rescuer');
        if (rescuerEl) rescuerEl.textContent = pet.posted_by || 'Rescuer';

        const locationEl = document.getElementById('adopt-summary-location');
        if (locationEl) locationEl.textContent = pet.location || 'Brgy. 176-F';

        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
    }

    function closeAdoptionRequestModal() {
        const modal = document.getElementById('adoption-request-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }

    function handleAdoptBackdropClick(event) {
        if (event.target.id === 'adoption-request-modal') {
            closeAdoptionRequestModal();
        }
    }

    function handleContactAction() {
        if (!activePetRecord) return;
        if (!currentUserId) {
            closePetModal();
            openLoginModal();
            return;
        }

        if (activePetRecord.is_own_post || (currentUserId !== null && activePetRecord.user_id === currentUserId)) {
            alert('This is your own post. You can manage it in My Posts.');
            return;
        }

        const params = new URLSearchParams({
            recipient: activePetRecord.posted_by || 'Poster',
            recipient_id: activePetRecord.user_id || '',
            pet: activePetRecord.name || 'Pet',
            pet_id: activePetRecord.id || '',
            rescuer_id: activePetRecord.rescuer_id || ''
        });

        window.location.href = "{{ route('messages') }}?" + params.toString();
    }

    // Scroll pet cards container to top
    function scrollPetGridToTop() {
        const petContainer = document.querySelector('.pets-section .site-container') || document.querySelector('.pets-section') || document.getElementById('pets');
        if (petContainer) {
            petContainer.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function setHomeStatus(status) {
        scrollPetGridToTop();
        const input = document.getElementById('filter-status-input');
        const form = document.getElementById('filter-form');
        if (input && form) {
            input.value = status;
            if (window.showKupLoader) {
                window.showKupLoader('Loading pets...');
            }
            form.submit();
        } else {
            const url = new URL(window.location.href);
            url.searchParams.set('status', status);
            window.location.href = url.toString();
        }
    }

    // Custom Styled Dropdowns for Search & Filter Bar
    document.addEventListener('DOMContentLoaded', function () {
        const customGroups = document.querySelectorAll('.custom-select-group');
        const filterForm = document.getElementById('filter-form');

        // Close all dropdowns
        function closeAllDropdowns() {
            customGroups.forEach(group => {
                group.classList.remove('open');
                const trigger = group.querySelector('.custom-select-trigger');
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            });
        }

        customGroups.forEach(group => {
            const trigger = group.querySelector('.custom-select-trigger');
            const hiddenInput = group.querySelector('input[type="hidden"]');
            const options = group.querySelectorAll('.custom-select-option');
            const labelSpan = group.querySelector('.custom-select-label');

            if (!trigger) return;

            // Toggle dropdown open/close on trigger click
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = group.classList.contains('open');
                closeAllDropdowns();
                if (!isOpen) {
                    group.classList.add('open');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });

            // Option selection
            const prefix = group.getAttribute('data-prefix') || '';
            options.forEach(option => {
                option.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const value = this.getAttribute('data-value');
                    const label = this.getAttribute('data-label') || (this.querySelector('span') ? this.querySelector('span').textContent.trim() : this.textContent.trim());

                    // Update UI state
                    options.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    labelSpan.textContent = prefix + label;
                    hiddenInput.value = value;

                    closeAllDropdowns();

                    // Auto-submit form immediately upon option change
                    if (filterForm) {
                        if (window.showKupLoader) {
                            window.showKupLoader('Filtering pets...');
                        }
                        filterForm.submit();
                    }
                });
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function () {
            closeAllDropdowns();
        });

        // Close on escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAllDropdowns();
            }
        });

        // Live on-screen filtering for Home feed search (no dropdown, no Enter key)
        const homeSearchInput = document.getElementById('home-search-input');
        if (homeSearchInput) {
            let searchDebounceTimer;
            const petCards = Array.from(document.querySelectorAll('.pets-grid .pet-card'));
            const emptyNotice = document.querySelector('.pets-grid .empty-feed-card');
            const metaCountEl = document.querySelector('.status-pills-meta');

            homeSearchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Do not trigger form submit on Enter
                }
            });

            homeSearchInput.addEventListener('input', function() {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(function() {
                    const query = homeSearchInput.value.trim().toLowerCase();
                    let visibleCount = 0;

                    petCards.forEach(function(card) {
                        const name = (card.querySelector('.pet-name')?.textContent || '').toLowerCase();
                        const species = (card.querySelector('.pet-species-pill')?.textContent || '').toLowerCase();
                        const pills = (card.querySelector('.pet-details-pills')?.textContent || '').toLowerCase();
                        const desc = (card.querySelector('.pet-description')?.textContent || '').toLowerCase();
                        const poster = (card.querySelector('.pet-poster-tag')?.textContent || '').toLowerCase();
                        const loc = (card.querySelector('.pet-location')?.textContent || '').toLowerCase();

                        const matches = !query ||
                            name.includes(query) ||
                            species.includes(query) ||
                            pills.includes(query) ||
                            desc.includes(query) ||
                            poster.includes(query) ||
                            loc.includes(query);

                        if (matches) {
                            card.style.display = '';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Manage dynamic empty state if none match
                    let liveEmptyCard = document.getElementById('home-live-empty-card');
                    if (visibleCount === 0 && petCards.length > 0) {
                        if (!liveEmptyCard) {
                            liveEmptyCard = document.createElement('div');
                            liveEmptyCard.id = 'home-live-empty-card';
                            liveEmptyCard.className = 'empty-feed-card';
                            liveEmptyCard.innerHTML = `
                                <span class="empty-icon">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="m8 8 6 6"/><path d="m14 8-6 6"/></svg>
                                </span>
                                <h3>No pets match "${escapeHTML(query)}"</h3>
                                <p class="text-muted">Try a different name, breed, or clear your search.</p>
                            `;
                            const grid = document.querySelector('.pets-grid');
                            if (grid) grid.appendChild(liveEmptyCard);
                        } else {
                            liveEmptyCard.style.display = '';
                            liveEmptyCard.querySelector('h3').textContent = `No pets match "${query}"`;
                        }
                    } else if (liveEmptyCard) {
                        liveEmptyCard.style.display = 'none';
                    }

                    if (metaCountEl) {
                        metaCountEl.textContent = `Showing ${visibleCount} pet${visibleCount === 1 ? '' : 's'}`;
                    }
                    scrollPetGridToTop();
                }, 250);
            });
        }
    });

    // ── Mobile Filter Bottom Sheet ──────────────────────────────────────
    function openFilterSheet() {
        const sheet = document.getElementById('filterBottomSheet');
        const backdrop = document.getElementById('filterSheetBackdrop');
        if (sheet) sheet.classList.add('open');
        if (backdrop) backdrop.classList.add('active');
        document.body.classList.add('sheet-open');
        document.body.style.overflow = 'hidden';
    }
    function closeFilterSheet() {
        const sheet = document.getElementById('filterBottomSheet');
        const backdrop = document.getElementById('filterSheetBackdrop');
        if (sheet) sheet.classList.remove('open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.classList.remove('sheet-open');
        document.body.style.overflow = '';
    }
    function resetFilterSheet() {
        // Reset hidden inputs
        ['sheet-species-val', 'sheet-age-val', 'sheet-gender-val'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        // Reset breed select
        const breedSelect = document.getElementById('sheet-breed');
        if (breedSelect) breedSelect.selectedIndex = 0;
        // Reset all chip active states
        document.querySelectorAll('#filterBottomSheet .filter-chip').forEach(chip => {
            const filter = chip.dataset.filter;
            chip.classList.toggle('active', chip.dataset.value === '');
        });
    }
    // Close sheet on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeFilterSheet();
    });
    // Chip button interactions for filter bottom sheet
    document.querySelectorAll('#filterBottomSheet .filter-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            const filter = this.dataset.filter;
            const value = this.dataset.value;
            // Deactivate siblings
            document.querySelectorAll('#filterBottomSheet .filter-chip[data-filter="' + filter + '"]').forEach(c => c.classList.remove('active'));
            // Activate this chip
            this.classList.add('active');
            // Update hidden input
            const hiddenInput = document.getElementById('sheet-' + filter + '-val');
            if (hiddenInput) hiddenInput.value = value;
        });
    });
</script>
@endsection
