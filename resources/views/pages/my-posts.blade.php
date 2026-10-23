@extends('layouts.app')

@section('title', 'My Listed Animals & Adoption Requests - ' . config('app.name', 'Kupkop'))

@section('content')
<div class="site-container my-posts-container">
    {{-- Header Banner with Action Button --}}
    <div class="my-posts-header">
        <div class="header-titles">
            <h1 class="page-title">My Listed Animals &amp; Adoption Requests</h1>
            <p class="page-subtitle">Manage the pets you have posted and review neighbor adoption applications.</p>
        </div>
        <div class="header-cta" style="display: flex; align-items: center; gap: 0.75rem;">
            @auth
                @if(isset($draftPets) && $draftPets->count() > 0)
                    <button type="button" class="btn-drafts-top" onclick="openDraftsModal()" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.15rem; background: #FAF5EE; color: #4A2E2B; border: 1.5px solid #D5C2B1; border-radius: 0.65rem; font-weight: 600; font-size: 0.92rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#F3E8D8'; this.style.borderColor='#C4B19F';" onmouseout="this.style.background='#FAF5EE'; this.style.borderColor='#D5C2B1';">
                        <x-lucide-file-text style="width: 17px; height: 17px; stroke-width: 2; color: #D97706;" />
                        <span>Drafts</span>
                        <span class="drafts-count-badge" style="background: #D97706; color: #ffffff; font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 999px; font-weight: 700;">{{ $draftPets->count() }}</span>
                    </button>
                @endif
                <button type="button" class="btn-create-post" onclick="openCreatePostModal()">
                    <x-lucide-plus style="width: 16px; height: 16px; stroke-width: 2.4;" />
                    <span>Create Post</span>
                </button>
            @endauth
        </div>
    </div>

    @guest
    {{-- =====================================================================
         GUEST PROMPT — Shown inside the page layout when not logged in
    ====================================================================== --}}
    <div class="dashboard-section">
        <div class="table-card guest-inline-prompt">
            <div class="guest-inline-icon">
                <x-lucide-paw-print style="width: 48px; height: 48px; stroke-width: 1.5; color: #8C756B;" />
            </div>
            <h3 class="guest-inline-title">Log in or sign up to create and view your posts</h3>
            <p class="guest-inline-body">Once logged in, you can post rescued animals for adoption and manage incoming requests from your neighbors.</p>
            <div class="guest-inline-actions">
                <button type="button" class="btn-gate-login" onclick="openLoginModal()">Log In</button>
                <button type="button" class="btn-gate-signup" onclick="openRegisterModal()">Sign Up</button>
            </div>
        </div>
    </div>
    @else

    {{-- ================================================================
         TAB INTERFACE — Tab 1: My Listed Pets | Tab 2: My Applications
    ================================================================ --}}
    <div class="mp-tabs-wrapper">
        <div class="mp-tabs-bar" role="tablist" aria-label="My Posts Sections">
            <button class="mp-tab-btn mp-tab-active" id="tab-btn-listed" onclick="switchTab('listed')" type="button" role="tab" aria-selected="true" aria-controls="tab-panel-listed">
                <x-lucide-paw-print style="width: 16px; height: 16px; stroke-width: 2;" />
                <span>My Listed Pets</span>
                <span class="mp-tab-count">{{ $myPets->count() }}</span>
            </button>
            <button class="mp-tab-btn" id="tab-btn-applications" onclick="switchTab('applications')" type="button" role="tab" aria-selected="false" aria-controls="tab-panel-applications">
                <x-lucide-heart-handshake style="width: 16px; height: 16px; stroke-width: 2;" />
                <span>My Applications</span>
                <span class="mp-tab-count">{{ $myApplications->count() }}</span>
            </button>
        </div>

        {{-- TAB 1: My Listed Pets (Active Posts + Incoming Adoption Requests) --}}
        <div id="tab-panel-listed" class="mp-tab-panel" role="tabpanel" aria-labelledby="tab-btn-listed">
            {{-- SECTION 1: My Active Animal Posts Table --}}
            <div class="dashboard-section">
                <div class="section-title-bar">
                    <h2>My Active Animal Posts ({{ $myPets->count() }})</h2>
                </div>

                <div class="table-card">
                    @if($myPets->count() > 0)
                        <div class="table-responsive">
                            <table class="kupkop-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Photo</th>
                                        <th>Animal Name &amp; ID</th>
                                        <th>Details</th>
                                        <th>Status</th>
                                        <th style="text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myPets as $pet)
                                        <tr>
                                            <td>
                                                <div class="table-pet-thumb">
                                                    <img src="{{ $pet->image_url }}" alt="{{ $pet->name }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="pet-name-cell">
                                                    <span class="pet-table-name">{{ $pet->name }}</span>
                                                    <span class="pet-table-id">{{ $pet->rescuer_id ?? ('#PET-' . $pet->id) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="pet-desc-cell">
                                                    <strong>{{ $pet->species }} • {{ $pet->breed }}</strong><br>
                                                    <span class="text-muted">{{ $pet->age }} • {{ $pet->gender }}</span><br>
                                                    <span class="pet-table-location" style="font-size: 0.76rem; color: #8C756B; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.2rem;">
                                                        <x-lucide-map-pin style="width: 12px; height: 12px; stroke-width: 2;" />
                                                        {{ $pet->location ?? 'Brgy. 176-F' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $stLower = strtolower($pet->status);
                                                    $stClass = match($stLower) {
                                                        'available' => 'status-pill-available',
                                                        'pending'   => 'status-pill-pending',
                                                        'draft'     => 'status-pill-draft',
                                                        default     => 'status-pill-adopted',
                                                    };
                                                @endphp
                                                <span class="status-pill {{ $stClass }}">
                                                    {{ $pet->status }}
                                                </span>
                                            </td>
                                            <td style="text-align: right;">
                                                <div class="table-actions-inline">
                                                    <button 
                                                        type="button" 
                                                        class="btn-table-edit"
                                                        onclick="openEditPostModal({{ json_encode($pet) }})"
                                                    >
                                                        <x-lucide-pencil style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                        <span>Edit Post</span>
                                                    </button>

                                                    @if(strtolower($pet->status) !== 'adopted')
                                                        <button
                                                            type="button"
                                                            class="btn-table-adopt"
                                                            onclick="confirmMarkAdopted({{ $pet->id }}, '{{ addslashes($pet->name) }}')"
                                                        >
                                                            <x-lucide-check-circle-2 style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                            <span>Mark as Adopted</span>
                                                        </button>
                                                        <form id="adopt-form-{{ $pet->id }}" method="POST" action="{{ route('posts.adopt', $pet->id) }}" class="inline-form" style="display: none;">
                                                            @csrf
                                                            @method('PATCH')
                                                        </form>
                                                    @endif

                                                    <button 
                                                        type="button" 
                                                        class="btn-table-delete"
                                                        onclick="confirmDeletePost({{ $pet->id }}, '{{ addslashes($pet->name) }}', false)"
                                                        title="Delete Post"
                                                    >
                                                        <x-lucide-trash-2 style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                        <span>Delete</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state-card">
                            <div class="empty-icon">
                                <x-lucide-paw-print style="width: 44px; height: 44px; stroke-width: 1.5; color: #8C756B; margin-bottom: 0.35rem;" />
                            </div>
                            <h3>No animals posted yet</h3>
                            <p class="text-muted">You haven't listed any pets for adoption yet. Click '+' to get started.</p>
                            <button type="button" class="btn-create-post-icon" onclick="openCreatePostModal()" title="Create Post" aria-label="Create Post">
                                <x-lucide-plus style="width: 20px; height: 20px; stroke-width: 2.4;" />
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- SECTION 2: Incoming Adoption Requests Table --}}
            <div class="dashboard-section mt-5">
                <div class="section-title-bar">
                    <h2>Incoming Adoption Requests ({{ $incomingRequests->count() }})</h2>
                </div>

                <div class="table-card">
                    @if($incomingRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="kupkop-table">
                                <thead>
                                    <tr>
                                        <th>Applicant Name</th>
                                        <th>Animal Requested</th>
                                        <th>Date Submitted</th>
                                        <th>Status</th>
                                        <th style="text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incomingRequests as $req)
                                        <tr>
                                            <td>
                                                <strong>{{ $req->applicant_name }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge-requested-pet">
                                                    {{ $req->pet->name ?? 'Pet' }}
                                                </span>
                                            </td>
                                            <td class="text-muted">
                                                {{ $req->created_at->format('M d, Y') }}
                                            </td>
                                            <td>
                                                @php
                                                    $reqStatus = strtolower($req->status);
                                                    $badgeClass = $reqStatus === 'accepted' 
                                                        ? 'status-pill-available' 
                                                        : ($reqStatus === 'declined' ? 'status-pill-declined' : 'status-pill-pending');
                                                @endphp
                                                <span class="status-pill {{ $badgeClass }}">
                                                    {{ $req->status }}
                                                </span>
                                            </td>
                                            <td style="text-align: right;">
                                                <div class="table-actions-inline">
                                                    @if($req->status === 'Pending')
                                                        <form method="POST" action="{{ route('requests.status', $req->id) }}" class="inline-form">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="Accepted">
                                                            <button type="submit" class="btn-table-accept">
                                                                <x-lucide-check style="width: 13px; height: 13px; stroke-width: 2.2; margin-right: 0.2rem;" />
                                                                <span>Accept</span>
                                                            </button>
                                                        </form>

                                                        <button 
                                                            type="button" 
                                                            class="btn-table-decline"
                                                            onclick="confirmDeclineRequest({{ $req->id }}, '{{ addslashes($req->applicant_name) }}', '{{ addslashes($req->pet->name ?? 'Pet') }}')"
                                                            title="Decline Adoption Request"
                                                        >
                                                            <x-lucide-x style="width: 13px; height: 13px; stroke-width: 2.2; margin-right: 0.2rem;" />
                                                            <span>Decline</span>
                                                        </button>
                                                    @endif

                                                    <button 
                                                        type="button" 
                                                        class="btn-table-message"
                                                        onclick="window.location.href = '{{ route('messages') }}?recipient={{ urlencode($req->applicant_name) }}&recipient_id={{ $req->user_id }}&pet={{ urlencode($req->pet->name ?? 'Pet') }}&pet_id={{ $req->pet_id }}'"
                                                    >
                                                        <x-lucide-message-square style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                        <span>Message</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state-card">
                            <div class="empty-icon">
                                <x-lucide-inbox style="width: 44px; height: 44px; stroke-width: 1.5; color: #8C756B; margin-bottom: 0.35rem;" />
                            </div>
                            <h3>No incoming adoption requests at the moment</h3>
                            <p class="text-muted">When interested neighbors submit adoption applications for your posted pets, they will appear right here for your review.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- TAB 2: My Applications (Submitted Adoption Requests) --}}
        <div id="tab-panel-applications" class="mp-tab-panel" role="tabpanel" aria-labelledby="tab-btn-applications" style="display: none;">
            <div class="dashboard-section">
                <div class="section-title-bar">
                    <h2>My Submitted Adoption Applications ({{ $myApplications->count() }})</h2>
                </div>

                <div class="table-card">
                    @if($myApplications->count() > 0)
                        <div class="table-responsive">
                            <table class="kupkop-table">
                                <thead>
                                    <tr>
                                        <th>Animal Requested</th>
                                        <th>Rescuer / Posted By</th>
                                        <th>Date Submitted</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th style="text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myApplications as $app)
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    @if($app->pet && $app->pet->image_path)
                                                        <img src="{{ $app->pet->image_url }}" alt="{{ $app->pet->name }}" style="width: 38px; height: 38px; border-radius: 0.375rem; object-fit: cover;" />
                                                    @endif
                                                    <div>
                                                        <strong>{{ $app->pet->name ?? 'Pet' }}</strong>
                                                        <div style="font-size: 0.78rem; color: #8C756B;">{{ $app->pet->species ?? '' }} • {{ $app->pet->breed ?? '' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span>{{ $app->pet->posted_by ?? 'Rescuer' }}</span>
                                            </td>
                                            <td class="text-muted">
                                                {{ $app->created_at->format('M d, Y') }}
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $app->barangay }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $appStatus = strtolower($app->status);
                                                    $badgeClass = $appStatus === 'accepted' 
                                                        ? 'status-pill-available' 
                                                        : ($appStatus === 'declined' ? 'status-pill-declined' : ($appStatus === 'cancelled' ? 'status-pill-draft' : 'status-pill-pending'));
                                                @endphp
                                                <span class="status-pill {{ $badgeClass }}">
                                                    {{ $app->status }}
                                                </span>
                                            </td>
                                            <td style="text-align: right;">
                                                <div class="table-actions-inline">
                                                    @if($app->pet && $app->pet->user_id)
                                                        <button 
                                                            type="button" 
                                                            class="btn-table-message"
                                                            onclick="window.location.href = '{{ route('messages') }}?recipient={{ urlencode($app->pet->posted_by ?? 'Rescuer') }}&recipient_id={{ $app->pet->user_id }}&pet={{ urlencode($app->pet->name ?? 'Pet') }}&pet_id={{ $app->pet_id }}'"
                                                        >
                                                            <x-lucide-message-square style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                            <span>Message Rescuer</span>
                                                        </button>
                                                    @endif
                                                    @if($app->status === 'Pending')
                                                        <button 
                                                            type="button" 
                                                            class="btn-table-decline"
                                                            onclick="confirmCancelRequest({{ $app->id }}, '{{ addslashes($app->pet->name ?? 'this pet') }}')"
                                                            title="Cancel Adoption Request"
                                                        >
                                                            <x-lucide-x style="width: 13px; height: 13px; stroke-width: 2.2; margin-right: 0.2rem;" />
                                                            <span>Cancel</span>
                                                        </button>
                                                    @elseif(in_array($app->status, ['Declined', 'Cancelled']))
                                                        <button 
                                                            type="button" 
                                                            class="btn-table-delete"
                                                            onclick="confirmDeleteApplication({{ $app->id }}, '{{ addslashes($app->pet->name ?? 'this pet') }}')"
                                                            title="Delete Application Record"
                                                        >
                                                            <x-lucide-trash-2 style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.2rem;" />
                                                            <span>Delete</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state-card">
                            <div class="empty-icon">
                                <x-lucide-heart-handshake style="width: 44px; height: 44px; stroke-width: 1.5; color: #8C756B; margin-bottom: 0.35rem;" />
                            </div>
                            <h3>You haven't submitted any adoption requests yet</h3>
                            <p class="text-muted">When you find a rescue animal you'd like to bring home, click "Meet [name]" on their details card to apply.</p>
                            <a href="{{ route('home') }}" class="btn-filter-yellow">
                                <x-lucide-paw-print style="width: 16px; height: 16px; stroke-width: 2;" />
                                <span>Browse Available Pets</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endguest
</div>

{{-- ==========================================================================
     CREATE POST MODAL POPUP (Auth only — modals & scripts not needed for guests)
   ========================================================================== --}}
@auth
<div 
    id="create-post-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="create-modal-title"
    style="display: none;"
    onclick="handleCreateBackdropClick(event)"
>
    <div class="modal-dialog create-post-dialog" onclick="event.stopPropagation()">
        {{-- Close Button --}}
        <button 
            type="button" 
            class="modal-close-btn" 
            onclick="confirmCloseCreatePost()" 
            aria-label="Close modal"
        >
            <x-lucide-x class="modal-close-icon" />
        </button>

        {{-- Modal Header --}}
        <div class="create-modal-header">
            <h2 id="create-modal-title" class="create-modal-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <x-lucide-paw-print style="width: 22px; height: 22px; stroke-width: 2; color: #4A2E2B;" />
                <span>Post a Rescued Animal for Adoption</span>
            </h2>
            <p class="create-modal-subtitle">
                Fill out the animal profile below. Specify the zone or area within the barangay so neighbors can find them easily.
            </p>
        </div>

        {{-- Create Post Form --}}
        <form 
            action="{{ route('posts.store') }}" 
            method="POST" 
            enctype="multipart/form-data" 
            class="create-post-form"
        >
            @csrf

            {{-- 1. Photo Upload Zone --}}
            <div class="form-section">
                <label class="form-label font-bold">Animal Photo</label>
                <div class="upload-dropzone" onclick="document.getElementById('pet_image').click()">
                    <div class="upload-icon">
                        <x-lucide-camera style="width: 44px; height: 44px; stroke-width: 1.5; color: #8C756B; margin-bottom: 0.35rem;" />
                    </div>
                    <p class="upload-title">Click to upload photo or drag &amp; drop</p>
                    <p class="upload-hint">PNG, JPG, JPEG, or WEBP up to 5MB</p>
                    <input 
                        type="file" 
                        id="pet_image" 
                        name="pet_image" 
                        accept="image/png, image/jpeg, image/jpg, image/webp" 
                        style="display: none;"
                        onchange="previewPetImage(event)"
                    >
                    <div id="image-preview-box" class="image-preview-container" style="display: none;">
                        <img id="image-preview" src="" alt="Preview">
                        <span class="preview-filename" id="preview-filename"></span>
                    </div>
                </div>
                @error('pet_image')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- 2. Basic Information --}}
            <div class="form-section">
                <label class="form-label font-bold">2. Basic Information</label>
                
                <div class="form-grid-2">
                    {{-- Animal Name --}}
                    <div class="form-group">
                        <label for="name" class="form-label">Animal Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            required 
                            placeholder="e.g. Bantay, Miming, Whitey" 
                            class="form-control"
                            value="{{ old('name') }}"
                        >
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Species --}}
                    <div class="form-group">
                        <label for="species" class="form-label">Species *</label>
                        <select id="species" name="species" required class="form-control">
                            <option value="Dog" {{ old('species') == 'Dog' ? 'selected' : '' }}>Dog</option>
                            <option value="Cat" {{ old('species') == 'Cat' ? 'selected' : '' }}>Cat</option>
                            <option value="Other" {{ old('species') == 'Other' ? 'selected' : '' }}>Other Animal</option>
                        </select>
                        @error('species')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-grid-2 mt-3">
                    {{-- Breed / Mix --}}
                    <div class="form-group">
                        <label for="breed" class="form-label">Breed / Mix</label>
                        <input 
                            type="text" 
                            id="breed" 
                            name="breed" 
                            placeholder="e.g. Philippine Aspin, Calico Puspin" 
                            class="form-control"
                            value="{{ old('breed') }}"
                        >
                    </div>

                    {{-- Age --}}
                    <div class="form-group">
                        <label for="age" class="form-label">Age *</label>
                        <input 
                            type="text" 
                            id="age" 
                            name="age" 
                            required 
                            placeholder="e.g. 5 months, 1.5 yrs, Adult" 
                            class="form-control"
                            value="{{ old('age') }}"
                        >
                        @error('age')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Gender Radio Buttons --}}
                <div class="form-group mt-3">
                    <label class="form-label">Gender *</label>
                    <div class="gender-radio-group">
                        <label class="radio-card">
                            <input type="radio" name="gender" value="Male" {{ old('gender', 'Male') === 'Male' ? 'checked' : '' }}>
                            <span>Male</span>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="gender" value="Female" {{ old('gender') === 'Female' ? 'checked' : '' }}>
                            <span>Female</span>
                        </label>
                    </div>
                    @error('gender')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Location / Area in Barangay --}}
                <div class="form-group mt-3">
                    <label for="location" class="form-label" style="display: flex; align-items: center; gap: 0.35rem;">
                        <x-lucide-map-pin style="width: 14px; height: 14px; stroke-width: 2;" />
                        <span>Location / Area in Barangay *</span>
                    </label>
                    <input 
                        type="text" 
                        id="location" 
                        name="location" 
                        required 
                        class="form-control" 
                        placeholder="e.g., Package 6, Brgy. 176-F"
                        value="{{ old('location') }}"
                    >
                    <span class="form-hint" style="font-size: 0.74rem; color: #8C756B; margin-top: 0.25rem; display: block;">
                        Specify the zone, phase, package, or area within the barangay.
                    </span>
                    @error('location')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- 3. Health & Medical Status --}}
            <div class="form-section">
                <label class="form-label font-bold">3. Health &amp; Medical Status</label>
                
                <div class="form-grid-2">
                    {{-- Vaccination Status --}}
                    <div class="form-group">
                        <label for="vaccination_status" class="form-label">Vaccination Status</label>
                        <input 
                            type="text" 
                            id="vaccination_status" 
                            name="vaccination_status" 
                            placeholder="e.g. 1st shot 5-in-1, Anti-Rabies, None" 
                            class="form-control"
                            value="{{ old('vaccination_status') }}"
                        >
                    </div>

                    {{-- Deworming Status --}}
                    <div class="form-group">
                        <label for="deworming_status" class="form-label">Deworming Status</label>
                        <input 
                            type="text" 
                            id="deworming_status" 
                            name="deworming_status" 
                            placeholder="e.g. Dewormed 2x, Not yet" 
                            class="form-control"
                            value="{{ old('deworming_status') }}"
                        >
                    </div>
                </div>

                {{-- Spayed / Neutered Checkbox --}}
                <div class="form-group-checkbox mt-3">
                    <label class="checkbox-label" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <input type="checkbox" name="is_spayed_neutered" value="1" {{ old('is_spayed_neutered') ? 'checked' : '' }}>
                        <x-lucide-scissors style="width: 15px; height: 15px; stroke-width: 2;" />
                        <span>Animal is Spayed / Neutered (Kapon)</span>
                    </label>
                </div>

                {{-- Special Health Notes --}}
                <div class="form-group mt-3">
                    <label for="special_health_notes" class="form-label">Special Health Notes</label>
                    <input 
                        type="text" 
                        id="special_health_notes" 
                        name="special_health_notes" 
                        placeholder="e.g. Blind in right eye, recovering from mange, limping left paw" 
                        class="form-control"
                        value="{{ old('special_health_notes') }}"
                    >
                </div>
            </div>

            {{-- 4. Rescue Story & Description --}}
            <div class="form-section">
                <label for="description" class="form-label font-bold">4. Rescue Story &amp; Description *</label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="4" 
                    required 
                    placeholder="Tell your neighbors how this animal was rescued, their personality, habits, and what kind of home would be best for them..." 
                    class="form-control textarea-field"
                >{{ old('description') }}</textarea>
                @error('description')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Form Footer Actions --}}
            <div class="create-modal-footer">
                <button type="button" class="btn-draft" onclick="saveCreatePostAsDraft()">
                    <x-lucide-file-text style="width: 16px; height: 16px; stroke-width: 2;" />
                    <span>Save as Draft</span>
                </button>
                <button type="submit" class="btn-submit-post">
                    <x-lucide-plus style="width: 16px; height: 16px; stroke-width: 2.4;" />
                    <span>Post for Adoption</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     CONFIRM CLOSE CREATE POST MODAL DIALOG
   ========================================================================== --}}
<div 
    id="confirm-close-create-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="confirm-close-title"
    style="display: none; z-index: 1060;"
    onclick="hideConfirmCloseCreate()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 440px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; box-shadow: 0 25px 60px rgba(0,0,0,0.35); text-align: center; border: 1px solid #EADDCF;">
        <div style="width: 56px; height: 56px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
            <x-lucide-help-circle style="width: 28px; height: 28px; stroke-width: 2; color: #D97706;" />
        </div>
        <h3 id="confirm-close-title" style="font-size: 1.25rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.5rem 0;">
            Save your post before leaving?
        </h3>
        <p style="font-size: 0.92rem; color: #52473D; margin: 0 0 1.5rem 0; line-height: 1.5;">
            You have unsaved changes in your animal post. You can save it as a draft to finish later, or discard it completely.
        </p>
        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
            <button 
                type="button" 
                onclick="saveCreatePostAsDraft()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1.25rem; border: none; background: #FEF08A; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.92rem; font-weight: 700; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#FDE047'"
                onmouseout="this.style.background='#FEF08A'"
            >
                <x-lucide-file-text style="width: 17px; height: 17px; stroke-width: 2; color: #D97706;" />
                <span>Save as Draft</span>
            </button>
            <button 
                type="button" 
                onclick="discardCreatePost()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1.25rem; border: 1px solid #FECACA; background: #FEF2F2; color: #DC2626; border-radius: 0.6rem; font-size: 0.92rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#FEE2E2'"
                onmouseout="this.style.background='#FEF2F2'"
            >
                <x-lucide-trash-2 style="width: 17px; height: 17px; stroke-width: 2;" />
                <span>Discard Changes</span>
            </button>
            <button 
                type="button" 
                onclick="hideConfirmCloseCreate()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; padding: 0.55rem 1.25rem; border: none; background: transparent; color: #8C756B; border-radius: 0.6rem; font-size: 0.88rem; font-weight: 500; cursor: pointer; transition: color 0.15s;"
                onmouseover="this.style.color='#4A2E2B'"
                onmouseout="this.style.color='#8C756B'"
            >
                <span>Continue Editing</span>
            </button>
        </div>
    </div>
</div>

{{-- ==========================================================================
     SAVED DRAFTS MODAL POPUP
   ========================================================================== --}}
<div 
    id="drafts-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="drafts-modal-title"
    style="display: none; z-index: 1050;"
    onclick="if(event.target.id === 'drafts-modal') closeDraftsModal()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 680px; max-height: 85vh; background: #ffffff; border-radius: 1.25rem; padding: 2rem; box-shadow: 0 25px 60px rgba(0,0,0,0.3); border: 1px solid #EADDCF; display: flex; flex-direction: column;">
        {{-- Close Button --}}
        <button 
            type="button" 
            class="modal-close-btn" 
            onclick="closeDraftsModal()" 
            aria-label="Close modal"
        >
            <x-lucide-x class="modal-close-icon" />
        </button>

        {{-- Header --}}
        <div class="create-modal-header" style="margin-bottom: 1.25rem; border-bottom: 1px solid #EADDCF; padding-bottom: 1rem;">
            <h2 id="drafts-modal-title" class="create-modal-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <x-lucide-file-text style="width: 22px; height: 22px; stroke-width: 2; color: #D97706;" />
                <span>Saved Drafts ({{ isset($draftPets) ? $draftPets->count() : 0 }})</span>
            </h2>
            <p class="create-modal-subtitle" style="margin-top: 0.25rem;">
                Unpublished animal profiles you have saved. Resume editing to complete and publish them for adoption, or delete them anytime.
            </p>
        </div>

        {{-- Drafts List Container --}}
        <div style="overflow-y: auto; flex: 1; padding-right: 0.25rem;">
            @if(isset($draftPets) && $draftPets->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    @foreach($draftPets as $draft)
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.15rem; background: #FAF5EE; border: 1px solid #EADDCF; border-radius: 0.85rem;">
                            <div style="display: flex; align-items: center; gap: 1rem; min-width: 0;">
                                <div style="width: 58px; height: 58px; border-radius: 0.6rem; overflow: hidden; background: #EAE0D5; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 1px solid #D5C2B1;">
                                    @if(!empty($draft->image_path))
                                        <img src="{{ $draft->image_url }}" alt="{{ $draft->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <x-lucide-paw-print style="width: 26px; height: 26px; stroke-width: 1.6; color: #8C756B; opacity: 0.7;" />
                                    @endif
                                </div>
                                <div style="min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #4A2E2B; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $draft->name ?: 'Untitled Draft' }}
                                        </h4>
                                        <span class="status-pill status-pill-draft" style="font-size: 0.7rem; padding: 0.15rem 0.55rem;">Draft</span>
                                    </div>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: #78350F;">
                                        {{ $draft->species }} • {{ $draft->breed ?: 'Mixed Breed' }} • {{ $draft->age ?: 'Age unlisted' }}
                                    </p>
                                    <p style="margin: 0.2rem 0 0 0; font-size: 0.75rem; color: #8C756B; display: flex; align-items: center; gap: 0.25rem;">
                                        <x-lucide-clock style="width: 12px; height: 12px; stroke-width: 2;" />
                                        <span>Last saved: {{ $draft->updated_at ? $draft->updated_at->diffForHumans() : 'Recently' }}</span>
                                    </p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                                <button 
                                    type="button" 
                                    class="btn-table-edit"
                                    onclick="resumeDraft({{ json_encode($draft) }})"
                                    style="padding: 0.5rem 0.9rem; font-size: 0.82rem;"
                                >
                                    <x-lucide-pencil style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                    <span>Resume</span>
                                </button>
                                <button 
                                    type="button" 
                                    onclick="confirmDeleteDraft({{ $draft->id }}, '{{ addslashes($draft->name ?: 'Untitled Draft') }}')"
                                    style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.5rem 0.75rem; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 0.5rem; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                                    onmouseover="this.style.background='#FEE2E2'"
                                    onmouseout="this.style.background='#FEF2F2'"
                                >
                                    <x-lucide-trash-2 style="width: 13px; height: 13px; stroke-width: 2;" />
                                    <span>Delete</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 2.5rem 1rem;">
                    <x-lucide-file-text style="width: 40px; height: 40px; stroke-width: 1.5; color: #8C756B; opacity: 0.6; margin-bottom: 0.5rem;" />
                    <p style="font-weight: 600; color: #4A2E2B; margin: 0 0 0.25rem 0;">No drafts saved</p>
                    <p style="font-size: 0.85rem; color: #8C756B; margin: 0;">When you save posts in progress, they will be kept here.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ==========================================================================
     CONFIRM MARK AS ADOPTED MODAL DIALOG
   ========================================================================== --}}
<div
    id="confirm-adopt-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-adopt-title"
    style="display: none; z-index: 1070;"
    onclick="if(event.target.id === 'confirm-adopt-modal') hideConfirmAdopt()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 420px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; box-shadow: 0 25px 60px rgba(0,0,0,0.35); text-align: center; border: 1px solid #EADDCF;">
        <div style="width: 56px; height: 56px; background: #D1FAE5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
            <x-lucide-check-circle-2 style="width: 28px; height: 28px; stroke-width: 2; color: #059669;" />
        </div>

        <h3 id="confirm-adopt-title" style="font-size: 1.25rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.5rem 0;">
            Mark as Adopted?
        </h3>

        <p id="confirm-adopt-desc" style="font-size: 0.92rem; color: #52473D; margin: 0 0 1.5rem 0; line-height: 1.5;">
            Are you sure you want to mark <strong id="adopt-pet-name" style="color: #4A2E2B;"></strong> as adopted? This will update the pet's status and close new adoption applications.
        </p>

        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button
                type="button"
                onclick="hideConfirmAdopt()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#F3E8D8'"
                onmouseout="this.style.background='#FAF5EE'"
            >
                <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Cancel</span>
            </button>
            <button
                type="button"
                onclick="submitMarkAdopted()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: none; background: #059669; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#047857'"
                onmouseout="this.style.background='#059669'"
            >
                <x-lucide-check-circle-2 style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Yes, Mark as Adopted</span>
            </button>
        </div>
    </div>
</div>

{{-- ==========================================================================
     CONFIRM DELETE POST / DRAFT MODAL DIALOG
   ========================================================================== --}}
<div 
    id="confirm-delete-post-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="confirm-delete-post-title"
    style="display: none; z-index: 1070;"
    onclick="if(event.target.id === 'confirm-delete-post-modal') hideConfirmDeletePost()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 420px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; box-shadow: 0 25px 60px rgba(0,0,0,0.35); text-align: center; border: 1px solid #EADDCF;">
        <div style="width: 56px; height: 56px; background: #FEE2E2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
            <x-lucide-trash-2 style="width: 28px; height: 28px; stroke-width: 2; color: #DC2626;" />
        </div>

        <h3 id="confirm-delete-post-title" style="font-size: 1.25rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.5rem 0;">
            Delete Post?
        </h3>

        <p id="confirm-delete-post-desc" style="font-size: 0.92rem; color: #52473D; margin: 0 0 1.5rem 0; line-height: 1.5;">
            Are you sure you want to delete <strong id="delete-post-pet-name" style="color: #4A2E2B;"></strong>? This action cannot be undone.
        </p>

        {{-- Hidden Form for submission --}}
        <form id="delete-post-form" method="POST" action="" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button 
                type="button" 
                onclick="hideConfirmDeletePost()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#F3E8D8'"
                onmouseout="this.style.background='#FAF5EE'"
            >
                <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Cancel</span>
            </button>
            <button 
                type="button" 
                onclick="submitDeletePost()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: none; background: #DC2626; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#B91C1C'"
                onmouseout="this.style.background='#DC2626'"
            >
                <x-lucide-trash-2 style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Yes, Delete</span>
            </button>
        </div>
    </div>
</div>

{{-- ==========================================================================
     CONFIRM CANCEL ADOPTION REQUEST MODAL DIALOG
   ========================================================================== --}}
<div 
    id="confirm-cancel-request-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="confirm-cancel-request-title"
    style="display: none; z-index: 1070;"
    onclick="if(event.target.id === 'confirm-cancel-request-modal') hideConfirmCancelRequest()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 420px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; box-shadow: 0 25px 60px rgba(0,0,0,0.35); text-align: center; border: 1px solid #EADDCF;">
        <div style="width: 56px; height: 56px; background: #FEE2E2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
            <x-lucide-alert-triangle style="width: 28px; height: 28px; stroke-width: 2; color: #DC2626;" />
        </div>

        <h3 id="confirm-cancel-request-title" style="font-size: 1.25rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.5rem 0;">
            Cancel Adoption Request?
        </h3>

        <p id="confirm-cancel-request-desc" style="font-size: 0.92rem; color: #52473D; margin: 0 0 1.5rem 0; line-height: 1.5;">
            Are you sure you want to cancel your adoption request for <strong id="cancel-request-pet-name" style="color: #4A2E2B;"></strong>? Your application will be completely removed and the rescuer will be notified.
        </p>

        {{-- Hidden Form for submission --}}
        <form id="cancel-request-form" method="POST" action="" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button 
                type="button" 
                onclick="hideConfirmCancelRequest()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#F3E8D8'"
                onmouseout="this.style.background='#FAF5EE'"
            >
                <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Keep Request</span>
            </button>
            <button 
                type="button" 
                onclick="submitCancelRequest()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: none; background: #DC2626; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#B91C1C'"
                onmouseout="this.style.background='#DC2626'"
            >
                <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Yes, Cancel Request</span>
            </button>
        </div>
    </div>
</div>

{{-- ==========================================================================
     CONFIRM DECLINE ADOPTION REQUEST MODAL DIALOG
   ========================================================================== --}}
<div 
    id="confirm-decline-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="confirm-decline-title"
    style="display: none; z-index: 1070;"
    onclick="if(event.target.id === 'confirm-decline-modal') hideConfirmDeclineRequest()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 440px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; box-shadow: 0 25px 60px rgba(0,0,0,0.35); text-align: center; border: 1px solid #EADDCF;">
        <div style="width: 56px; height: 56px; background: #FEE2E2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
            <x-lucide-x-circle style="width: 28px; height: 28px; stroke-width: 2; color: #DC2626;" />
        </div>

        <h3 id="confirm-decline-title" style="font-size: 1.25rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.5rem 0;">
            Decline Adoption Request?
        </h3>

        <p id="confirm-decline-desc" style="font-size: 0.92rem; color: #52473D; margin: 0 0 1.5rem 0; line-height: 1.55;">
            Are you sure you want to decline the adoption application from <strong id="decline-applicant-name" style="color: #4A2E2B;"></strong> for <strong id="decline-pet-name" style="color: #4A2E2B;"></strong>?
        </p>

        {{-- Hidden Form for submission --}}
        <form id="decline-request-form" method="POST" action="" style="display: none;">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="Declined">
        </form>

        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button 
                type="button" 
                onclick="hideConfirmDeclineRequest()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#F3E8D8'"
                onmouseout="this.style.background='#FAF5EE'"
            >
                <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Keep Request</span>
            </button>
            <button 
                type="button" 
                onclick="submitDeclineRequest()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: none; background: #DC2626; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#B91C1C'"
                onmouseout="this.style.background='#DC2626'"
            >
                <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Yes, Decline</span>
            </button>
        </div>
    </div>
</div>

{{-- ==========================================================================
     CONFIRM DELETE ADOPTION APPLICATION MODAL DIALOG
   ========================================================================== --}}
<div 
    id="confirm-delete-application-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="confirm-delete-app-title"
    style="display: none; z-index: 1070;"
    onclick="if(event.target.id === 'confirm-delete-application-modal') hideConfirmDeleteApplication()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 420px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; box-shadow: 0 25px 60px rgba(0,0,0,0.35); text-align: center; border: 1px solid #EADDCF;">
        <div style="width: 56px; height: 56px; background: #FEE2E2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
            <x-lucide-trash-2 style="width: 28px; height: 28px; stroke-width: 2; color: #DC2626;" />
        </div>

        <h3 id="confirm-delete-app-title" style="font-size: 1.25rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.5rem 0;">
            Delete Adoption Application?
        </h3>

        <p id="confirm-delete-app-desc" style="font-size: 0.92rem; color: #52473D; margin: 0 0 1.5rem 0; line-height: 1.5;">
            Are you sure you want to permanently delete your application for <strong id="delete-app-pet-name" style="color: #4A2E2B;"></strong>? This record will be permanently removed from your list.
        </p>

        {{-- Hidden Form for submission --}}
        <form id="delete-application-form" method="POST" action="" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button 
                type="button" 
                onclick="hideConfirmDeleteApplication()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#F3E8D8'"
                onmouseout="this.style.background='#FAF5EE'"
            >
                <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Cancel</span>
            </button>
            <button 
                type="button" 
                onclick="submitDeleteApplication()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: none; background: #DC2626; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#B91C1C'"
                onmouseout="this.style.background='#DC2626'"
            >
                <x-lucide-trash-2 style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Yes, Delete</span>
            </button>
        </div>
    </div>
</div>

{{-- ==========================================================================
     EDIT POST MODAL POPUP
   ========================================================================== --}}
<div 
    id="edit-post-modal" 
    class="modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="edit-modal-title"
    style="display: none;"
    onclick="if(event.target.id === 'edit-post-modal') closeEditPostModal()"
>
    <div class="modal-dialog create-post-dialog" onclick="event.stopPropagation()">
        <button 
            type="button" 
            class="modal-close-btn" 
            onclick="closeEditPostModal()" 
            aria-label="Close modal"
        >
            <x-lucide-x class="modal-close-icon" />
        </button>

        <div class="create-modal-header">
            <h2 id="edit-modal-title" class="create-modal-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <x-lucide-pencil style="width: 22px; height: 22px; stroke-width: 2; color: #4A2E2B;" />
                <span>Edit Rescued Animal Profile</span>
            </h2>
            <p class="create-modal-subtitle">
                Update the information, health status, location, or adoption availability of your posted pet.
            </p>
        </div>

        <form 
            id="edit-post-form"
            method="POST" 
            enctype="multipart/form-data" 
            class="create-post-form"
        >
            @csrf
            @method('PUT')

            {{-- 1. Photo Upload Zone --}}
            <div class="form-section">
                <label class="form-label font-bold">Animal Photo</label>
                <div class="upload-dropzone" onclick="document.getElementById('edit_pet_image').click()">
                    <div class="upload-icon">
                        <x-lucide-camera style="width: 36px; height: 36px; stroke-width: 1.6; color: #8C756B; margin-bottom: 0.35rem;" />
                    </div>
                    <p class="upload-title">Click to change pet photo (Optional)</p>
                    <input 
                        type="file" 
                        id="edit_pet_image" 
                        name="pet_image" 
                        accept="image/png, image/jpeg, image/jpg, image/webp" 
                        style="display: none;"
                        onchange="previewEditImage(event)"
                    >
                    <div id="edit-image-preview-box" class="image-preview-container" style="display: none;">
                        <img id="edit-image-preview" src="" alt="Preview">
                    </div>
                </div>
            </div>

            {{-- 2. Basic Information --}}
            <div class="form-section">
                <label class="form-label font-bold">2. Basic Information</label>
                
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="edit_name" class="form-label">Animal Name *</label>
                        <input type="text" id="edit_name" name="name" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_species" class="form-label">Species *</label>
                        <select id="edit_species" name="species" required class="form-control">
                            <option value="Dog">Dog</option>
                            <option value="Cat">Cat</option>
                            <option value="Other">Other Animal</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2 mt-3">
                    <div class="form-group">
                        <label for="edit_breed" class="form-label">Breed / Mix</label>
                        <input type="text" id="edit_breed" name="breed" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_age" class="form-label">Age *</label>
                        <input type="text" id="edit_age" name="age" required class="form-control">
                    </div>
                </div>

                <div class="form-grid-2 mt-3">
                    <div class="form-group">
                        <label for="edit_gender" class="form-label">Gender *</label>
                        <select id="edit_gender" name="gender" required class="form-control">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_status" class="form-label">Status *</label>
                        <select id="edit_status" name="status" required class="form-control">
                            <option value="Available">Available</option>
                            <option value="Draft">Draft</option>
                            <option value="Adopted">Adopted</option>
                        </select>
                    </div>
                </div>

                {{-- Location / Area in Barangay --}}
                <div class="form-group mt-3">
                    <label for="edit_location" class="form-label" style="display: flex; align-items: center; gap: 0.35rem;">
                        <x-lucide-map-pin style="width: 14px; height: 14px; stroke-width: 2;" />
                        <span>Location / Area in Barangay *</span>
                    </label>
                    <input type="text" id="edit_location" name="location" required class="form-control" placeholder="e.g., Package 6, Brgy. 176-F">
                    <span class="form-hint" style="font-size: 0.74rem; color: #8C756B; margin-top: 0.25rem; display: block;">
                        Specify the zone, phase, package, or area within the barangay.
                    </span>
                </div>
            </div>

            {{-- 3. Health & Medical Status --}}
            <div class="form-section">
                <label class="form-label font-bold">3. Health &amp; Medical Status</label>
                
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="edit_vaccination_status" class="form-label">Vaccination Status</label>
                        <input type="text" id="edit_vaccination_status" name="vaccination_status" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_deworming_status" class="form-label">Deworming Status</label>
                        <input type="text" id="edit_deworming_status" name="deworming_status" class="form-control">
                    </div>
                </div>

                <div class="form-group-checkbox mt-3">
                    <label class="checkbox-label" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <input type="checkbox" id="edit_is_spayed_neutered" name="is_spayed_neutered" value="1">
                        <x-lucide-scissors style="width: 15px; height: 15px; stroke-width: 2;" />
                        <span>Animal is Spayed / Neutered (Kapon)</span>
                    </label>
                </div>

                <div class="form-group mt-3">
                    <label for="edit_special_health_notes" class="form-label">Special Health Notes</label>
                    <input type="text" id="edit_special_health_notes" name="special_health_notes" class="form-control">
                </div>
            </div>

            {{-- 4. Rescue Story & Description --}}
            <div class="form-section">
                <label for="edit_description" class="form-label font-bold">4. Rescue Story &amp; Description *</label>
                <textarea id="edit_description" name="description" rows="4" required class="form-control textarea-field"></textarea>
            </div>

            {{-- Form Footer Actions --}}
            <div class="create-modal-footer" style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
                <button 
                    type="button" 
                    id="edit-delete-post-btn"
                    onclick="handleEditModalDeletePost()"
                    style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.65rem 1rem; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 0.5rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; margin-right: auto;"
                    onmouseover="this.style.background='#FEE2E2'"
                    onmouseout="this.style.background='#FEF2F2'"
                >
                    <x-lucide-trash-2 style="width: 15px; height: 15px; stroke-width: 2;" />
                    <span id="edit-delete-post-text">Delete Post</span>
                </button>
                <button type="button" class="btn-draft" onclick="closeEditPostModal()" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                    <span>Cancel</span>
                </button>
                <button type="submit" class="btn-submit-post" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <x-lucide-check-circle-2 style="width: 15px; height: 15px; stroke-width: 2;" />
                    <span>Update Post</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Scripts for Modals & Tabs --}}
<script>
    // Tab Switching Functionality
    function switchTab(tabName) {
        const listedBtn = document.getElementById('tab-btn-listed');
        const applicationsBtn = document.getElementById('tab-btn-applications');
        const listedPanel = document.getElementById('tab-panel-listed');
        const applicationsPanel = document.getElementById('tab-panel-applications');

        if (tabName === 'listed') {
            listedBtn.classList.add('mp-tab-active');
            listedBtn.setAttribute('aria-selected', 'true');
            applicationsBtn.classList.remove('mp-tab-active');
            applicationsBtn.setAttribute('aria-selected', 'false');

            listedPanel.style.display = 'block';
            applicationsPanel.style.display = 'none';
        } else if (tabName === 'applications') {
            applicationsBtn.classList.add('mp-tab-active');
            applicationsBtn.setAttribute('aria-selected', 'true');
            listedBtn.classList.remove('mp-tab-active');
            listedBtn.setAttribute('aria-selected', 'false');

            applicationsPanel.style.display = 'block';
            listedPanel.style.display = 'none';
        }
    }

    // Modal Display Functions
    function openCreatePostModal() {
        const modal = document.getElementById('create-post-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
        }
    }

    function closeCreatePostModal() {
        const modal = document.getElementById('create-post-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }

    function handleCreateBackdropClick(event) {
        if (event.target.id === 'create-post-modal') {
            confirmCloseCreatePost();
        }
    }

    // Image Previews
    function previewPetImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('image-preview');
                const box = document.getElementById('image-preview-box');
                const filename = document.getElementById('preview-filename');
                if (preview) preview.src = e.target.result;
                if (filename) filename.textContent = file.name;
                if (box) box.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        }
    }

    function previewEditImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('edit-image-preview');
                const box = document.getElementById('edit-image-preview-box');
                if (preview) preview.src = e.target.result;
                if (box) box.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    // Close Confirmation
    function createPostHasInput() {
        const imgInput = document.getElementById('pet_image');
        const hasImage = imgInput && imgInput.files && imgInput.files.length > 0;
        const fields = ['name', 'age', 'location', 'breed', 'vaccination_status', 'deworming_status', 'special_health_notes', 'description'];
        const hasText = fields.some(function(id) {
            const el = document.getElementById(id);
            return el && el.value.trim() !== '';
        });
        const spayedEl = document.querySelector('#create-post-modal input[name="is_spayed_neutered"]');
        const hasSpayed = spayedEl && spayedEl.checked;
        return hasImage || hasText || hasSpayed;
    }

    function confirmCloseCreatePost() {
        if (!createPostHasInput()) {
            discardCreatePost();
            return;
        }
        showConfirmCloseCreate();
    }

    function showConfirmCloseCreate() {
        const dialog = document.getElementById('confirm-close-create-modal');
        if (dialog) dialog.style.display = 'flex';
    }

    function hideConfirmCloseCreate() {
        const dialog = document.getElementById('confirm-close-create-modal');
        if (dialog) dialog.style.display = 'none';
    }

    function discardCreatePost() {
        hideConfirmCloseCreate();
        const form = document.querySelector('#create-post-modal .create-post-form');
        if (form) form.reset();
        const previewBox = document.getElementById('image-preview-box');
        if (previewBox) previewBox.style.display = 'none';
        closeCreatePostModal();
    }

    function saveCreatePostAsDraft() {
        hideConfirmCloseCreate();
        const form = document.querySelector('#create-post-modal .create-post-form');
        if (form) {
            form.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));
            let draftInput = form.querySelector('input[name="action"][type="hidden"]');
            if (!draftInput) {
                draftInput = document.createElement('input');
                draftInput.type = 'hidden';
                draftInput.name = 'action';
                form.appendChild(draftInput);
            }
            draftInput.value = 'draft';
            if (window.showKupLoader) window.showKupLoader('Saving draft...');
            form.submit();
        }
    }

    // Edit Post Modal Functions
    let currentEditingPet = null;

    function openEditPostModal(pet) {
        currentEditingPet = pet;
        document.getElementById('edit-post-form').action = '/posts/' + pet.id;

        document.getElementById('edit_name').value = pet.name || '';
        document.getElementById('edit_species').value = pet.species || 'Dog';
        document.getElementById('edit_breed').value = pet.breed || '';
        document.getElementById('edit_age').value = pet.age || '';
        document.getElementById('edit_location').value = pet.location || '';
        document.getElementById('edit_gender').value = pet.gender || 'Male';
        document.getElementById('edit_status').value = pet.status || 'Available';
        document.getElementById('edit_vaccination_status').value = pet.vaccination_status || '';
        document.getElementById('edit_deworming_status').value = pet.deworming_status || '';
        document.getElementById('edit_special_health_notes').value = pet.special_health_notes || '';
        document.getElementById('edit_description').value = pet.description || '';
        document.getElementById('edit_is_spayed_neutered').checked = Boolean(pet.is_spayed_neutered);

        const deletePostBtn = document.getElementById('edit-delete-post-btn');
        const deletePostText = document.getElementById('edit-delete-post-text');
        if (deletePostBtn) {
            deletePostBtn.onclick = function() {
                closeEditPostModal();
                confirmDeletePost(pet.id, pet.name || 'Pet');
            };
        }

        const previewBox = document.getElementById('edit-image-preview-box');
        const previewImg = document.getElementById('edit-image-preview');
        if (pet.image_path) {
            previewImg.src = pet.image_url;
            previewBox.style.display = 'block';
        } else {
            previewBox.style.display = 'none';
        }

        const modal = document.getElementById('edit-post-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
        }
    }

    function closeEditPostModal() {
        const modal = document.getElementById('edit-post-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
        currentEditingPet = null;
    }

    function handleEditModalDeletePost() {
        if (!currentEditingPet) return;
        closeEditPostModal();
        confirmDeletePost(currentEditingPet.id, currentEditingPet.name || 'Pet');
    }

    // Drafts Modal Functions
    function openDraftsModal() {
        const modal = document.getElementById('drafts-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
        }
    }

    function closeDraftsModal() {
        const modal = document.getElementById('drafts-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }

    function resumeDraft(draftPet) {
        closeDraftsModal();
        openEditPostModal(draftPet);
    }

    // Mark as Adopted Confirmation Functions
    let pendingAdoptPetId = null;

    function confirmMarkAdopted(petId, petName) {
        pendingAdoptPetId = petId;
        const nameEl = document.getElementById('adopt-pet-name');
        if (nameEl) nameEl.textContent = '"' + petName + '"';
        const modal = document.getElementById('confirm-adopt-modal');
        if (modal) modal.style.display = 'flex';
    }

    function hideConfirmAdopt() {
        const modal = document.getElementById('confirm-adopt-modal');
        if (modal) modal.style.display = 'none';
        pendingAdoptPetId = null;
    }

    function submitMarkAdopted() {
        if (!pendingAdoptPetId) return;
        const form = document.getElementById('adopt-form-' + pendingAdoptPetId);
        if (form) {
            if (window.showKupLoader) window.showKupLoader('Marking as adopted...');
            form.submit();
        }
    }

    // Delete Post & Draft Confirmation Functions
    function confirmDeletePost(postId, postName, isDraft = false) {
        const form = document.getElementById('delete-post-form');
        if (form) form.action = '/posts/' + postId;
        const titleEl = document.getElementById('confirm-delete-post-title');
        if (titleEl) titleEl.textContent = isDraft ? 'Delete Draft?' : 'Delete Pet Post?';
        const nameEl = document.getElementById('delete-post-pet-name');
        if (nameEl) nameEl.textContent = '"' + postName + '"';
        const descEl = document.getElementById('confirm-delete-post-desc');
        if (descEl) {
            descEl.innerHTML = isDraft 
                ? 'Are you sure you want to delete <strong style="color: #4A2E2B;">"' + postName + '"</strong>? This draft will be permanently removed.'
                : 'Are you sure you want to delete <strong style="color: #4A2E2B;">"' + postName + '"</strong>? This post and any adoption requests for this pet will be permanently removed.';
        }
        const modal = document.getElementById('confirm-delete-post-modal');
        if (modal) modal.style.display = 'flex';
    }

    function hideConfirmDeletePost() {
        const modal = document.getElementById('confirm-delete-post-modal');
        if (modal) modal.style.display = 'none';
    }

    function submitDeletePost() {
        const form = document.getElementById('delete-post-form');
        if (form) {
            if (window.showKupLoader) window.showKupLoader('Deleting post...');
            form.submit();
        }
    }

    function confirmDeleteDraft(draftId, draftName) {
        confirmDeletePost(draftId, draftName, true);
    }

    function hideConfirmDeleteDraft() {
        hideConfirmDeletePost();
    }

    function submitDeleteDraft() {
        submitDeletePost();
    }

    // Cancel Adoption Request Confirmation Functions
    function confirmCancelRequest(requestId, petName) {
        const form = document.getElementById('cancel-request-form');
        if (form) form.action = '/requests/' + requestId + '/cancel';
        const nameEl = document.getElementById('cancel-request-pet-name');
        if (nameEl) nameEl.textContent = '"' + petName + '"';
        const modal = document.getElementById('confirm-cancel-request-modal');
        if (modal) modal.style.display = 'flex';
    }

    function hideConfirmCancelRequest() {
        const modal = document.getElementById('confirm-cancel-request-modal');
        if (modal) modal.style.display = 'none';
    }

    function submitCancelRequest() {
        const form = document.getElementById('cancel-request-form');
        if (form) {
            if (window.showKupLoader) window.showKupLoader('Cancelling request...');
            form.submit();
        }
    }

    // Decline Adoption Request Confirmation Functions
    function confirmDeclineRequest(requestId, applicantName, petName) {
        const form = document.getElementById('decline-request-form');
        if (form) form.action = '/requests/' + requestId + '/status';
        const applicantEl = document.getElementById('decline-applicant-name');
        if (applicantEl) applicantEl.textContent = '"' + applicantName + '"';
        const petEl = document.getElementById('decline-pet-name');
        if (petEl) petEl.textContent = '"' + petName + '"';
        const modal = document.getElementById('confirm-decline-modal');
        if (modal) modal.style.display = 'flex';
    }

    function hideConfirmDeclineRequest() {
        const modal = document.getElementById('confirm-decline-modal');
        if (modal) modal.style.display = 'none';
    }

    function submitDeclineRequest() {
        const form = document.getElementById('decline-request-form');
        if (form) {
            if (window.showKupLoader) window.showKupLoader('Declining request...');
            form.submit();
        }
    }

    // Delete Adoption Application Confirmation Functions
    function confirmDeleteApplication(appId, petName) {
        const form = document.getElementById('delete-application-form');
        if (form) form.action = '/requests/' + appId;
        const nameEl = document.getElementById('delete-app-pet-name');
        if (nameEl) nameEl.textContent = '"' + petName + '"';
        const modal = document.getElementById('confirm-delete-application-modal');
        if (modal) modal.style.display = 'flex';
    }

    function hideConfirmDeleteApplication() {
        const modal = document.getElementById('confirm-delete-application-modal');
        if (modal) modal.style.display = 'none';
    }

    function submitDeleteApplication() {
        const form = document.getElementById('delete-application-form');
        if (form) {
            if (window.showKupLoader) window.showKupLoader('Deleting application...');
            form.submit();
        }
    }

    // Auto-open modal if validation errors exist
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openCreatePostModal();
        });
    @endif

    // Escape key handling
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const confirmAdoptModal = document.getElementById('confirm-adopt-modal');
            const confirmDeletePostModal = document.getElementById('confirm-delete-post-modal');
            const confirmCancelModal = document.getElementById('confirm-cancel-request-modal');
            const confirmDeclineModal = document.getElementById('confirm-decline-modal');
            const confirmDeleteAppModal = document.getElementById('confirm-delete-application-modal');
            const confirmModal = document.getElementById('confirm-close-create-modal');
            const createModal = document.getElementById('create-post-modal');
            const editModal = document.getElementById('edit-post-modal');
            const draftsModal = document.getElementById('drafts-modal');

            if (confirmAdoptModal && confirmAdoptModal.style.display === 'flex') {
                hideConfirmAdopt();
            } else if (confirmDeletePostModal && confirmDeletePostModal.style.display === 'flex') {
                hideConfirmDeletePost();
            } else if (confirmCancelModal && confirmCancelModal.style.display === 'flex') {
                hideConfirmCancelRequest();
            } else if (confirmDeclineModal && confirmDeclineModal.style.display === 'flex') {
                hideConfirmDeclineRequest();
            } else if (confirmDeleteAppModal && confirmDeleteAppModal.style.display === 'flex') {
                hideConfirmDeleteApplication();
            } else if (confirmModal && confirmModal.style.display === 'flex') {
                hideConfirmCloseCreate();
            } else if (draftsModal && draftsModal.style.display === 'flex') {
                closeDraftsModal();
            } else if (createModal && createModal.style.display === 'flex') {
                confirmCloseCreatePost();
            } else if (editModal && editModal.style.display === 'flex') {
                closeEditPostModal();
            }
        }
    });
</script>
@endauth
@endsection
