@extends('layouts.app')

@section('title', 'Kupkop - Adopt a rescued dog or cat in your barangay')

@section('content')
{{-- 1. Hero Section --}}
<section class="hero-section">
    <div class="site-container hero-content">
        {{-- Badge above title --}}
        <div class="hero-badge">
            <span class="badge-dot">🐾</span>
            COMMUNITY ANIMAL RESCUE &amp; CARE
        </div>

        {{-- Main Heading --}}
        <h1 class="hero-title">
            Adopt a rescued dog or cat in your barangay.
        </h1>

        {{-- Subtitle --}}
        <p class="hero-subtitle">
            Welcome to Kupkop! A simple student project portal connecting kind neighbors with rescued aspins, puspins, and community pets ready for loving permanent homes.
        </p>

        {{-- 2. Search & Filter Bar --}}
        <div class="search-filter-card">
            <form action="{{ route('home') }}" method="GET" class="filter-form">
                {{-- Search Input with Search Icon --}}
                <div class="filter-group search-input-group">
                    <span class="filter-icon">🔍</span>
                    <input 
                        type="text" 
                        name="keyword" 
                        value="{{ request('keyword') }}" 
                        placeholder="Search by name, barangay, or keywords..."
                        class="filter-input"
                    >
                </div>

                {{-- Species Dropdown --}}
                <div class="filter-group">
                    <select name="species" class="filter-select">
                        <option value="">Species: All</option>
                        <option value="dog" {{ request('species') == 'dog' ? 'selected' : '' }}>Dog</option>
                        <option value="cat" {{ request('species') == 'cat' ? 'selected' : '' }}>Cat</option>
                    </select>
                </div>

                {{-- Breed Dropdown --}}
                <div class="filter-group">
                    <select name="breed" class="filter-select">
                        <option value="">Breed: Any</option>
                        <option value="aspin" {{ request('breed') == 'aspin' ? 'selected' : '' }}>Aspin</option>
                        <option value="puspin" {{ request('breed') == 'puspin' ? 'selected' : '' }}>Puspin</option>
                        <option value="mix" {{ request('breed') == 'mix' ? 'selected' : '' }}>Mix Breed</option>
                    </select>
                </div>

                {{-- Age Dropdown --}}
                <div class="filter-group">
                    <select name="age" class="filter-select">
                        <option value="">Age: Any</option>
                        <option value="puppy_kitten" {{ request('age') == 'puppy_kitten' ? 'selected' : '' }}>Puppy / Kitten (&lt; 1 yr)</option>
                        <option value="young" {{ request('age') == 'young' ? 'selected' : '' }}>Young (1 - 2 yrs)</option>
                        <option value="adult" {{ request('age') == 'adult' ? 'selected' : '' }}>Adult (2+ yrs)</option>
                    </select>
                </div>

                {{-- Gender Dropdown --}}
                <div class="filter-group">
                    <select name="gender" class="filter-select">
                        <option value="">Gender: All</option>
                        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                {{-- Status Dropdown --}}
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="available" selected>Status: Available</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                    </select>
                </div>

                {{-- Filter Action Button --}}
                <div class="filter-action">
                    <button type="submit" class="btn-filter-yellow">
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- 3. Recently Listed Pets Section --}}
<section class="pets-section" id="pets">
    <div class="site-container">
        {{-- Section Header with Pet Count --}}
        <div class="section-header">
            <h2 class="section-title">
                Recently Listed Pets <span class="pets-count-badge">({{ count($pets ?? []) }} Available)</span>
            </h2>
        </div>

        {{-- 4-Column Grid for Pet Cards --}}
        <div class="pets-grid">
            @forelse($pets as $pet)
                <x-pet-card :pet="$pet" />
            @empty
                <p class="text-muted">No pets currently listed.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- 4. Interactive Pet Details Modal Overlay --}}
<div 
    id="pet-details-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="modal-pet-name"
    style="display: none;"
    onclick="handleBackdropClick(event)"
>
    <div class="modal-dialog" onclick="event.stopPropagation()">
        {{-- Close Button (Top Right) --}}
        <button 
            type="button" 
            class="modal-close-btn" 
            onclick="closePetModal()" 
            aria-label="Close modal"
        >
            &times;
        </button>

        {{-- Modal Header Bar --}}
        <div class="modal-header">
            <div class="modal-header-info">
                <h2 id="modal-pet-name" class="modal-pet-name">Pet Name</h2>
                <p id="modal-pet-location" class="modal-pet-location">
                    📍 Brgy. San Antonio, Pasig City
                </p>
            </div>
            <div class="modal-header-status">
                <span id="modal-status-badge" class="badge-modal-status">
                    Status: Available
                </span>
            </div>
        </div>

        {{-- Two-Column Grid Layout inside Modal --}}
        <div class="modal-body-grid">
            {{-- LEFT COLUMN: Photo, Badges, Thumbnails, Barangay Policy --}}
            <div class="modal-col-left">
                {{-- Main Pet Photo Card with Overlays --}}
                <div class="modal-image-wrapper">
                    <img id="modal-pet-image" src="" alt="Pet Photo" class="modal-main-image">
                    
                    {{-- Status Tag on top-left of image --}}
                    <span id="modal-image-badge" class="modal-image-tag">
                        Available for Adoption
                    </span>

                    {{-- Footer Info Bar on Image --}}
                    <div class="modal-image-footer-bar">
                        <span id="modal-rescuer-id" class="image-bar-id">
                            Rescuer Verification ID: #RES-0492
                        </span>
                        <span class="image-bar-loc">
                            Brgy. San Antonio
                        </span>
                    </div>
                </div>

                {{-- Thumbnail Gallery --}}
                <div class="modal-gallery-row">
                    <div class="gallery-thumb active" onclick="swapMainImage(0)">
                        <img id="modal-thumb-0" src="" alt="Thumbnail 1">
                    </div>
                    <div class="gallery-thumb" onclick="swapMainImage(1)">
                        <img id="modal-thumb-1" src="" alt="Thumbnail 2">
                    </div>
                    <div class="gallery-thumb" onclick="swapMainImage(2)">
                        <img id="modal-thumb-2" src="" alt="Thumbnail 3">
                    </div>
                    <button type="button" class="gallery-more-btn" onclick="alert('Additional community rescue photos will be available in future releases.')">
                        +3 more
                    </button>
                </div>

                {{-- Barangay Adoption Policy Box (Pastel Yellow Container) --}}
                <div class="policy-callout-box">
                    <div class="policy-header">
                        <span class="policy-icon">📋</span>
                        <h4>Barangay Adoption Policy &amp; Requirements:</h4>
                    </div>
                    <p class="policy-text">
                        Adopters must be 18+ and residents of the barangay or nearby areas. Valid government ID and proof of residence will be verified during animal turnover.
                    </p>
                </div>
            </div>

            {{-- RIGHT COLUMN: Details List, Rescue Story, Action Buttons --}}
            <div class="modal-col-right">
                {{-- Pet Details Table/List (Light Cream Box) --}}
                <div class="pet-spec-card">
                    <div class="spec-row">
                        <span class="spec-label">Species:</span>
                        <span id="modal-spec-species" class="spec-val">Dog (Canine)</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Breed:</span>
                        <span id="modal-spec-breed" class="spec-val">Philippine Aspin (Mixed Breed)</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Age:</span>
                        <span id="modal-spec-age" class="spec-val">5 Months Old (Puppy)</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Gender:</span>
                        <span id="modal-spec-gender" class="spec-val">Male (Intact)</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Health / Medical Condition:</span>
                        <span id="modal-spec-health" class="spec-val">Vaccinated (1st 5-in-1 shot), Dewormed, Healthy &amp; active</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Rescued Date:</span>
                        <span id="modal-spec-date" class="spec-val">October 14, 2024</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Posted By:</span>
                        <span id="modal-spec-posted" class="spec-val">Kagawad Elena Santos (Barangay Animal Welfare Committee)</span>
                    </div>
                </div>

                {{-- Rescue Story & Personality Section --}}
                <div class="rescue-story-section">
                    <h4 class="story-title">RESCUE STORY &amp; PERSONALITY:</h4>
                    <div class="story-box">
                        <p id="modal-spec-story" class="story-text">
                            Bantay was rescued wandering near the barangay covered basketball court during a rainstorm. He is energetic, very loving, accustomed to children, and already responds well to gentle commands. He is looking for a forever family with a secure yard or indoor accommodations.
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="modal-actions-area">
                    <div class="modal-buttons-row">
                        <button 
                            type="button" 
                            class="btn-request-adopt"
                            onclick="handleAdoptAction()"
                        >
                            🐾 Request Adoption
                        </button>
                        <button 
                            type="button" 
                            class="btn-contact-poster"
                            onclick="handleContactAction()"
                        >
                            💬 Contact Poster
                        </button>
                    </div>
                    <p class="modal-action-note">
                        🔒 Submits your verified barangay user profile to the post author.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pass Pet Data cleanly into JavaScript --}}
<script>
    const kupkopPetsData = @json($pets ?? []);
    let activePetRecord = null;
    let activeGallery = [];

    function openPetModal(petId) {
        const pet = kupkopPetsData.find(p => p.id === petId);
        if (!pet) return;

        activePetRecord = pet;

        // Populate Header
        document.getElementById('modal-pet-name').textContent = pet.name;
        document.getElementById('modal-pet-location').textContent = '📍 ' + (pet.location || 'Brgy. San Antonio, Pasig City');
        
        // Status badge
        const statusBadge = document.getElementById('modal-status-badge');
        statusBadge.textContent = 'Status: ' + pet.status;
        if (pet.status.toLowerCase() === 'pending') {
            statusBadge.className = 'badge-modal-status status-pending';
        } else {
            statusBadge.className = 'badge-modal-status status-available';
        }

        // Left Column: Images & Gallery
        document.getElementById('modal-pet-image').src = pet.image;
        document.getElementById('modal-pet-image').alt = pet.name;
        document.getElementById('modal-image-badge').textContent = (pet.status.toLowerCase() === 'available') 
            ? 'Available for Adoption' 
            : 'Adoption Pending';
        document.getElementById('modal-rescuer-id').textContent = 'Rescuer Verification ID: ' + (pet.rescuer_id || '#RES-0492');

        // Setup gallery images
        activeGallery = [pet.image, ...(pet.gallery || [])];
        for (let i = 0; i < 3; i++) {
            const thumbImg = document.getElementById('modal-thumb-' + i);
            if (thumbImg) {
                thumbImg.src = activeGallery[i] || pet.image;
                thumbImg.parentElement.classList.toggle('active', i === 0);
            }
        }

        // Right Column: Specifications
        document.getElementById('modal-spec-species').textContent = pet.species_label || (pet.species + ' (' + (pet.species === 'Cat' ? 'Feline' : 'Canine') + ')');
        document.getElementById('modal-spec-breed').textContent = pet.breed_full || pet.breed;
        document.getElementById('modal-spec-age').textContent = pet.age_full || pet.age;
        document.getElementById('modal-spec-gender').textContent = pet.gender_full || pet.gender;
        document.getElementById('modal-spec-health').textContent = pet.health_condition || 'Vaccinated, Dewormed, Healthy & active';
        document.getElementById('modal-spec-date').textContent = pet.rescue_date || 'October 14, 2024';
        document.getElementById('modal-spec-posted').textContent = pet.posted_by || 'Barangay Animal Welfare Committee';
        document.getElementById('modal-spec-story').textContent = pet.story || pet.description;

        // Show Modal
        const modal = document.getElementById('pet-details-modal');
        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
    }

    function swapMainImage(index) {
        if (!activeGallery || !activeGallery[index]) return;
        document.getElementById('modal-pet-image').src = activeGallery[index];
        
        // Highlight active thumbnail
        for (let i = 0; i < 3; i++) {
            const thumb = document.getElementById('modal-thumb-' + i);
            if (thumb && thumb.parentElement) {
                thumb.parentElement.classList.toggle('active', i === index);
            }
        }
    }

    function closePetModal() {
        const modal = document.getElementById('pet-details-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }

    function handleBackdropClick(event) {
        if (event.target.id === 'pet-details-modal') {
            closePetModal();
        }
    }

    // Keyboard navigation (Esc to close)
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePetModal();
        }
    });

    function handleAdoptAction() {
        if (!activePetRecord) return;
        alert('Adoption application for ' + activePetRecord.name + ' has been initiated! You can fill out the barangay verification form next.');
    }

    function handleContactAction() {
        if (!activePetRecord) return;
        alert('Opening chat inquiry with ' + (activePetRecord.posted_by || 'the post author') + ' regarding ' + activePetRecord.name + '...');
    }
</script>
@endsection
