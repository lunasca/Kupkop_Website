@extends('layouts.app')

@section('title', 'Kupkop - Adopt a rescued dog or cat in Brgy. 176-F')

@section('content')
{{-- 1. Hero Section --}}
<section class="hero-section">
    <div class="site-container hero-content">
        @auth
            {{-- Auth greeting: above the card, left-aligned --}}
            <div class="hero-auth-greeting">
                <h1 class="hero-greeting-title">Welcome, {{ Auth::user()->name }}!</h1>
                <p class="hero-greeting-sub">Meet rescued aspins, puspins, and pets ready for adoption in Brgy.&nbsp;176&#8209;F.</p>
            </div>
        @else
            {{-- Guest greeting: same layout above the card, left-aligned --}}
            <div class="hero-auth-greeting">
                <h1 class="hero-greeting-title">Adopt a rescued dog or cat in <span class="text-nowrap">Brgy.&nbsp;176&#8209;F.</span></h1>
                <p class="hero-greeting-sub">Connecting rescued pets with loving homes in Brgy.&nbsp;176&#8209;F.</p>
            </div>
        @endauth

        {{-- 2. Search & Filter Bar --}}
        <div class="search-filter-card">
            <form action="{{ route('home') }}" method="GET" class="filter-form" id="filter-form">
                {{-- Search Input with Search Icon --}}
                <div class="filter-group search-input-group">
                    <x-lucide-search class="filter-icon" />
                    <input 
                        type="text" 
                        name="keyword" 
                        value="{{ request('keyword') }}" 
                        placeholder="Search by name or breed"
                        autocomplete="off"
                        class="filter-input"
                    >
                </div>

                {{-- Species Dropdown --}}
                <div class="filter-group custom-select-group" data-filter="species" data-prefix="Species: ">
                    <input type="hidden" name="species" value="{{ request('species') }}">
                    <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select-label">
                            Species: {{ request('species') ? ucfirst(request('species')) : 'All' }}
                        </span>
                        <x-lucide-chevron-down class="custom-select-arrow" />
                    </button>
                    <ul class="custom-select-dropdown" role="listbox">
                        <li class="custom-select-option {{ !request('species') ? 'selected' : '' }}" data-value="" data-label="All">
                            <x-lucide-check class="option-check-icon" />
                            <span>All</span>
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
                <div class="filter-group custom-select-group" data-filter="breed" data-prefix="Breed: ">
                    <input type="hidden" name="breed" value="{{ request('breed') }}">
                    <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select-label">
                            @if(request('breed') == 'aspin') Breed: Aspin
                            @elseif(request('breed') == 'puspin') Breed: Puspin
                            @elseif(request('breed') == 'mix') Breed: Mix Breed
                            @else Breed: Any
                            @endif
                        </span>
                        <x-lucide-chevron-down class="custom-select-arrow" />
                    </button>
                    <ul class="custom-select-dropdown" role="listbox">
                        <li class="custom-select-option {{ !request('breed') ? 'selected' : '' }}" data-value="" data-label="Any">
                            <x-lucide-check class="option-check-icon" />
                            <span>Any</span>
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
                <div class="filter-group custom-select-group" data-filter="age" data-prefix="Age: ">
                    <input type="hidden" name="age" value="{{ request('age') }}">
                    <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select-label">
                            @if(request('age') == 'puppy_kitten') Age: Puppy / Kitten (&lt; 1 yr)
                            @elseif(request('age') == 'young') Age: Young (1 - 2 yrs)
                            @elseif(request('age') == 'adult') Age: Adult (2+ yrs)
                            @else Age: Any
                            @endif
                        </span>
                        <x-lucide-chevron-down class="custom-select-arrow" />
                    </button>
                    <ul class="custom-select-dropdown" role="listbox">
                        <li class="custom-select-option {{ !request('age') ? 'selected' : '' }}" data-value="" data-label="Any">
                            <x-lucide-check class="option-check-icon" />
                            <span>Any</span>
                        </li>
                        <li class="custom-select-option {{ request('age') == 'puppy_kitten' ? 'selected' : '' }}" data-value="puppy_kitten" data-label="Puppy / Kitten (&lt; 1 yr)">
                            <x-lucide-check class="option-check-icon" />
                            <span>Puppy / Kitten (&lt; 1 yr)</span>
                        </li>
                        <li class="custom-select-option {{ request('age') == 'young' ? 'selected' : '' }}" data-value="young" data-label="Young (1 - 2 yrs)">
                            <x-lucide-check class="option-check-icon" />
                            <span>Young (1 - 2 yrs)</span>
                        </li>
                        <li class="custom-select-option {{ request('age') == 'adult' ? 'selected' : '' }}" data-value="adult" data-label="Adult (2+ yrs)">
                            <x-lucide-check class="option-check-icon" />
                            <span>Adult (2+ yrs)</span>
                        </li>
                    </ul>
                </div>

                {{-- Gender Dropdown --}}
                <div class="filter-group custom-select-group" data-filter="gender" data-prefix="Gender: ">
                    <input type="hidden" name="gender" value="{{ request('gender') }}">
                    <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select-label">
                            Gender: {{ request('gender') ? ucfirst(request('gender')) : 'All' }}
                        </span>
                        <x-lucide-chevron-down class="custom-select-arrow" />
                    </button>
                    <ul class="custom-select-dropdown" role="listbox">
                        <li class="custom-select-option {{ !request('gender') ? 'selected' : '' }}" data-value="" data-label="All">
                            <x-lucide-check class="option-check-icon" />
                            <span>All</span>
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

                {{-- Status Dropdown --}}
                <div class="filter-group custom-select-group" data-filter="status" data-prefix="Status: ">
                    <input type="hidden" name="status" value="{{ request('status', 'available') }}">
                    <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select-label">
                            @if(request('status') == 'pending') Status: Pending
                            @elseif(request('status') == 'all') Status: All
                            @else Status: Available
                            @endif
                        </span>
                        <x-lucide-chevron-down class="custom-select-arrow" />
                    </button>
                    <ul class="custom-select-dropdown" role="listbox">
                        <li class="custom-select-option {{ request('status', 'available') == 'available' ? 'selected' : '' }}" data-value="available" data-label="Available">
                            <x-lucide-check class="option-check-icon" />
                            <span>Available</span>
                        </li>
                        <li class="custom-select-option {{ request('status') == 'pending' ? 'selected' : '' }}" data-value="pending" data-label="Pending">
                            <x-lucide-check class="option-check-icon" />
                            <span>Pending</span>
                        </li>
                        <li class="custom-select-option {{ request('status') == 'all' ? 'selected' : '' }}" data-value="all" data-label="All Statuses">
                            <x-lucide-check class="option-check-icon" />
                            <span>All Statuses</span>
                        </li>
                    </ul>
                </div>

                {{-- Reset Button if any filter is active --}}
                @if(request()->hasAny(['keyword', 'species', 'breed', 'age', 'gender']) || (request('status') && request('status') !== 'available'))
                    <div class="filter-action">
                        <a href="{{ route('home') }}" class="btn-filter-reset" title="Reset all filters" aria-label="Reset all filters">
                            <x-lucide-rotate-ccw class="btn-reset-icon" />
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</section>

{{-- 3. Recently Listed Pets Section (Dynamic Feed from Database) --}}
<section class="pets-section" id="pets">
    <div class="site-container">
        {{-- White Card Circling Recently Listed Pets --}}
        <div class="pets-container-card">
            {{-- Section Header with Pet Count --}}
            <div class="section-header">
                <h2 class="section-title">
                    Recently Listed Pets <span class="pets-count-badge">({{ $pets->count() }} Available)</span>
                </h2>
            </div>

            {{-- Dedicated Scroll Area for Pet Cards --}}
            <div class="pets-scroll-area">
                {{-- 4-Column Grid for Pet Cards --}}
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
                    <label class="form-label font-bold">1. Animal Photo</label>
                    <div 
                        class="pet-photo-frame" 
                        onclick="openFullscreenImage()" 
                        title="Click to view full screen" 
                        role="button" 
                        tabindex="0"
                        onkeydown="if(event.key==='Enter'||event.key===' '){openFullscreenImage();event.preventDefault();}"
                    >
                        <img id="modal-pet-image" src="" alt="Pet Photo" class="pet-frame-image">
                        <div class="photo-expand-badge">
                            <x-lucide-maximize-2 class="expand-badge-icon" />
                            <span>View Full Screen</span>
                        </div>
                    </div>
                    
                    <div class="pet-rescuer-card">
                        <div class="rescuer-icon-badge">
                            <x-lucide-user style="width: 15px; height: 15px; stroke-width: 2.2; color: #4A2E2B;" />
                        </div>
                        <div class="rescuer-text-group">
                            <span class="rescuer-sublabel">Rescued &amp; Posted By</span>
                            <strong id="modal-spec-posted" class="rescuer-name">Rescuer Name</strong>
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

{{-- 4b. Fullscreen Pet Image Viewer Modal --}}
<div 
    id="pet-fullscreen-image-modal" 
    class="fullscreen-image-backdrop" 
    style="display: none;" 
    role="dialog" 
    aria-modal="true" 
    aria-label="Full screen pet image"
    onclick="closeFullscreenImage()"
>
    {{-- Pinned Close Button --}}
    <button 
        type="button" 
        class="fullscreen-close-btn" 
        onclick="event.stopPropagation(); closeFullscreenImage();" 
        aria-label="Close full screen view"
    >
        <x-lucide-x class="fullscreen-close-icon" />
    </button>

    {{-- Image Container --}}
    <div class="fullscreen-image-container" onclick="event.stopPropagation()">
        <img id="pet-fullscreen-img" src="" alt="Full screen pet photo" class="fullscreen-img">
        <div class="fullscreen-image-bar">
            <span id="pet-fullscreen-caption" class="fullscreen-caption"></span>
            <span class="fullscreen-hint">Click anywhere or press ESC to exit</span>
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
            <img id="adopt-summary-img" src="" alt="Pet" style="width: 68px; height: 68px; border-radius: 0.75rem; object-fit: cover; border: 1px solid #D5C2B1; flex-shrink: 0;" />
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

{{-- Pass Dynamic Pet Data into JavaScript --}}
@php
    // Pre-load current user's pending request pet IDs for efficient lookup
    $myPendingPetIds = [];
    if (Auth::check()) {
        $myPendingPetIds = \App\Models\AdoptionRequest::where('user_id', Auth::id())
            ->where('status', 'Pending')
            ->pluck('pet_id')
            ->all();
    }

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
            'user_id' => $p->user_id,
            'is_own_post' => Auth::check() && ($p->user_id === Auth::id()),
            'has_pending_request' => in_array($p->id, $myPendingPetIds),
        ];
    });
@endphp

<script>
    const currentUserId = {{ Auth::check() ? Auth::id() : 'null' }};
    const kupkopPetsData = @json($petsJson);
    let activePetRecord = null;
    let activeGallery = [];

    function openPetModal(petId) {
        const pet = kupkopPetsData.find(p => p.id === petId);
        if (!pet) return;

        activePetRecord = pet;

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

        // Left Column: Image & Rescuer
        document.getElementById('modal-pet-image').src = pet.image;
        document.getElementById('modal-pet-image').alt = pet.name;
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

    function swapMainImage(index) {
        if (!activeGallery || !activeGallery[index]) return;
        document.getElementById('modal-pet-image').src = activeGallery[index];
        for (let i = 0; i < 3; i++) {
            const thumb = document.getElementById('modal-thumb-' + i);
            if (thumb && thumb.parentElement) {
                thumb.parentElement.classList.toggle('active', i === index);
            }
        }
    }

    function closePetModal() {
        closeFullscreenImage();
        const modal = document.getElementById('pet-details-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }

    function openFullscreenImage() {
        const mainImg = document.getElementById('modal-pet-image');
        if (!mainImg || !mainImg.src) return;
        const fsModal = document.getElementById('pet-fullscreen-image-modal');
        const fsImg = document.getElementById('pet-fullscreen-img');
        const fsCaption = document.getElementById('pet-fullscreen-caption');
        const petNameEl = document.getElementById('modal-pet-name');
        
        if (fsImg) {
            fsImg.src = mainImg.src;
            fsImg.alt = (petNameEl ? petNameEl.textContent : 'Pet') + ' - Fullscreen';
        }
        if (fsCaption && petNameEl) {
            fsCaption.textContent = petNameEl.textContent;
        }
        if (fsModal) {
            fsModal.style.display = 'flex';
        }
    }

    function closeFullscreenImage() {
        const fsModal = document.getElementById('pet-fullscreen-image-modal');
        if (fsModal) {
            fsModal.style.display = 'none';
        }
    }

    function handleBackdropClick(event) {
        if (event.target.id === 'pet-details-modal') {
            closePetModal();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const fsModal = document.getElementById('pet-fullscreen-image-modal');
            if (fsModal && fsModal.style.display !== 'none') {
                closeFullscreenImage();
                return;
            }
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
    });
</script>
@endsection
