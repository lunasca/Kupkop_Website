@extends('layouts.app')

@section('title', 'My Listed Animals & Adoption Requests - ' . config('app.name', 'Kupkop'))

@section('content')
<div class="site-container my-posts-container">
    {{-- Header Banner with Action Button --}}
    <div class="my-posts-header">
        <div class="header-titles">
            <h1 class="page-title my-posts-mobile-title">My Posts</h1>
            <p class="page-subtitle">Manage your pets and review adoption requests.</p>
        </div>
        <div class="header-cta" style="display: flex; align-items: center; gap: 0.75rem;">
            @auth
                @if(isset($draftPets) && $draftPets->count() > 0)
                    <button type="button" class="btn-drafts-top" onclick="openDraftsModal()" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 1.15rem; background: #FAF5EE; color: #4A2E2B; border: 1.5px solid #D5C2B1; border-radius: 0.55rem; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#F3E8D8'; this.style.borderColor='#C4B19F';" onmouseout="this.style.background='#FAF5EE'; this.style.borderColor='#D5C2B1';">
                        <x-lucide-file-text style="width: 17px; height: 17px; stroke-width: 2; color: #D97706;" />
                        <span>Drafts</span>
                        <span class="drafts-count-badge" style="background: #D97706; color: #ffffff; font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 999px; font-weight: 700;">{{ $draftPets->count() }}</span>
                    </button>
                @endif
                <button type="button" class="btn-create-post" onclick="openCreatePostModal()">
                    <x-lucide-plus style="width: 16px; height: 16px; stroke-width: 2.4;" />
                    <span>Create Post</span>
                </button>
                <button 
                    type="button" 
                    class="btn-mp-create-mobile" 
                    onclick="openCreatePostModal()" 
                    title="Create Post" 
                    aria-label="Create Post"
                >
                    <x-lucide-plus style="width: 18px; height: 18px; stroke-width: 2.6;" />
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
        <div class="mp-tabs-header-row">
            <div class="mp-tabs-bar" role="tablist" aria-label="My Posts Sections">
                <button class="mp-tab-btn mp-tab-active" id="tab-btn-listed" onclick="switchTab('listed')" type="button" role="tab" aria-selected="true" aria-controls="tab-panel-listed">
                    <x-lucide-paw-print style="width: 16px; height: 16px; stroke-width: 2;" />
                    <span>My Listed Pets</span>
                    <span class="mp-tab-count">{{ $myPets->count() }}</span>
                </button>
                <button class="mp-tab-btn" id="tab-btn-applications" onclick="switchTab('applications')" type="button" role="tab" aria-selected="false" aria-controls="tab-panel-applications">
                    <x-lucide-heart style="width: 16px; height: 16px; stroke-width: 2;" />
                    <span>My Applications</span>
                    <span class="mp-tab-count">{{ $myApplications->count() }}</span>
                </button>
                <button class="mp-tab-btn" id="tab-btn-archived" onclick="switchTab('archived')" type="button" role="tab" aria-selected="false" aria-controls="tab-panel-archived">
                    <x-lucide-archive style="width: 16px; height: 16px; stroke-width: 2;" />
                    <span>Archived Posts</span>
                    <span class="mp-tab-count">{{ $archivedPets->count() }}</span>
                </button>
            </div>
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
                        <div class="table-responsive hide-on-phone">
                            <table class="kupkop-table">
                                <thead>
                                    <tr>
                                        <th style="width: 90px;">Photo</th>
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
                                                <div class="table-pet-thumb" onclick="openFullscreenCarouselForPet({{ json_encode($pet) }})" style="cursor: pointer;" title="Click to view photos in fullscreen carousel">
                                                    <img src="{{ $pet->image_url }}" alt="{{ $pet->name }}" loading="lazy">
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
                                                    <span class="pet-table-location" style="font-size: 0.82rem; color: #8C756B; display: inline-flex; align-items: center; gap: 0.3rem; margin-top: 0.25rem;">
                                                        <x-lucide-map-pin style="width: 14px; height: 14px; stroke-width: 2;" />
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
                                                <span class="status-pill {{ $stClass }}">{{ $pet->status }}</span>
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

                                                    @php
                                                        $isAdopted = strtolower($pet->status) === 'adopted';
                                                    @endphp
                                                    <button
                                                        type="button"
                                                        class="{{ $isAdopted ? 'btn-table-revert' : 'btn-table-adopt' }}"
                                                        onclick="confirmToggleAdopted({{ $pet->id }}, '{{ addslashes($pet->name) }}', {{ $isAdopted ? 'true' : 'false' }})"
                                                    >
                                                        @if($isAdopted)
                                                            <x-lucide-rotate-ccw style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                            <span>Mark as Available</span>
                                                        @else
                                                            <x-lucide-check-circle-2 style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                            <span>Mark as Adopted</span>
                                                        @endif
                                                    </button>
                                                    <form id="adopt-form-{{ $pet->id }}" method="POST" action="{{ route('posts.adopt', $pet->id) }}" class="inline-form" style="display: none;">
                                                        @csrf
                                                        @method('PATCH')
                                                    </form>

                                                    <button
                                                        type="button"
                                                        class="btn-table-archive"
                                                        onclick="confirmArchivePost({{ $pet->id }}, '{{ addslashes($pet->name) }}')"
                                                        title="Archive Post"
                                                    >
                                                        <x-lucide-archive style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                        <span>Archive</span>
                                                    </button>
                                                    <form id="archive-form-{{ $pet->id }}" method="POST" action="{{ route('posts.archive', $pet->id) }}" class="inline-form" style="display: none;">
                                                        @csrf
                                                        @method('PATCH')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards for Active Posts (phone only) --}}
                        <div class="mobile-card-list" aria-label="My active posts">
                            @foreach($myPets as $pet)
                                @php
                                    $stLower = strtolower($pet->status);
                                    $stClass = match($stLower) {
                                        'available' => 'status-pill-available',
                                        'pending'   => 'status-pill-pending',
                                        'draft'     => 'status-pill-draft',
                                        default     => 'status-pill-adopted',
                                    };
                                    $isAdopted = $stLower === 'adopted';
                                @endphp
                                <div class="mobile-pet-card">
                                    <div class="mobile-card-top">
                                        <div class="table-pet-thumb" onclick="openFullscreenCarouselForPet({{ json_encode($pet) }})" style="cursor: pointer; width: 64px; height: 64px; flex-shrink: 0;" title="Click to view photos">
                                            <img src="{{ $pet->image_url }}" alt="{{ $pet->name }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;">
                                        </div>
                                        <div class="mobile-card-info" style="flex: 1; min-width: 0;">
                                            <div class="mobile-card-name" style="font-weight: 700; color: #2D1810; font-size: 0.95rem;">{{ $pet->name }}</div>
                                            <div class="mobile-card-id" style="font-size: 0.75rem; color: #8C756B;">{{ $pet->rescuer_id ?? ('#PET-' . $pet->id) }}</div>
                                            <div class="mobile-card-details" style="font-size: 0.8rem; color: #52473D; margin-top: 0.2rem; line-height: 1.35;">
                                                <strong>{{ $pet->species }} • {{ $pet->breed }}</strong><br>
                                                <span class="text-muted">{{ $pet->age }} • {{ $pet->gender }}</span>
                                            </div>
                                        </div>
                                        <span class="status-pill {{ $stClass }}" style="flex-shrink: 0; align-self: flex-start;">{{ $pet->status }}</span>
                                    </div>
                                    <div class="mobile-card-actions" style="display: flex; flex-direction: row; align-items: center; gap: 0.35rem; padding-top: 0.45rem; border-top: 1px solid #F3E8D8; width: 100%;">
                                        <button 
                                            type="button" 
                                            class="btn-table-edit"
                                            style="flex: 1; justify-content: center; height: 32px; min-width: 0; font-size: 0.78rem; font-weight: 700; padding: 0.25rem 0.45rem; border-radius: 0.5rem;"
                                            onclick="openEditPostModal({{ json_encode($pet) }})"
                                        >
                                            <x-lucide-pencil style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                            <span>Edit</span>
                                        </button>

                                        <button
                                            type="button"
                                            class="{{ $isAdopted ? 'btn-table-revert' : 'btn-table-adopt' }}"
                                            style="flex: 1; justify-content: center; height: 32px; min-width: 0; font-size: 0.78rem; font-weight: 700; padding: 0.25rem 0.45rem; border-radius: 0.5rem;"
                                            onclick="confirmToggleAdopted({{ $pet->id }}, '{{ addslashes($pet->name) }}', {{ $isAdopted ? 'true' : 'false' }})"
                                        >
                                            @if($isAdopted)
                                                <x-lucide-rotate-ccw style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                <span>Available</span>
                                            @else
                                                <x-lucide-check-circle-2 style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                <span>Adopted</span>
                                            @endif
                                        </button>

                                        <button
                                            type="button"
                                            class="btn-archive-icon-only"
                                            style="flex: 0 0 32px; width: 32px; height: 32px; min-width: 32px; max-width: 32px; border-radius: 0.5rem;"
                                            onclick="confirmArchivePost({{ $pet->id }}, '{{ addslashes($pet->name) }}')"
                                            title="Archive Post"
                                            aria-label="Archive Post"
                                        >
                                            <x-lucide-trash-2 style="width: 14px; height: 14px; stroke-width: 2;" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
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
                        <div class="table-responsive hide-on-phone">
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
                                                <span class="status-pill {{ $badgeClass }}">{{ $req->status }}</span>
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
                                                        <x-lucide-message-square style="width: 13px; height: 13px; stroke-width: 2.2; margin-right: 0.2rem;" />
                                                        <span>Message</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards for Incoming Requests (phone only) --}}
                        <div class="mobile-card-list" aria-label="Incoming adoption requests">
                            @foreach($incomingRequests as $req)
                                @php
                                    $reqStatus = strtolower($req->status);
                                    $reqBadge = $reqStatus === 'accepted'
                                        ? 'status-pill-available'
                                        : ($reqStatus === 'declined' ? 'status-pill-declined' : 'status-pill-pending');
                                    $reqInitial = strtoupper(substr($req->applicant_name ?? 'U', 0, 1));
                                @endphp
                                <div class="mobile-req-card">
                                    <div class="mobile-req-top">
                                        <div class="mobile-req-avatar">{{ $reqInitial }}</div>
                                        <div class="mobile-req-info">
                                            <div class="mobile-req-name">{{ $req->applicant_name }}</div>
                                            <div class="mobile-req-pet">wants to adopt <strong>{{ $req->pet->name ?? 'Pet' }}</strong></div>
                                        </div>
                                        <span class="status-pill {{ $reqBadge }}">{{ $req->status }}</span>
                                    </div>
                                    <div class="mobile-req-meta">
                                        <span class="mobile-req-date">{{ $req->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="mobile-req-actions">
                                        @if($req->status === 'Pending')
                                            <form method="POST" action="{{ route('requests.status', $req->id) }}" class="inline-form mobile-req-accept-form">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="Accepted">
                                                <button type="submit" class="btn-table-accept">
                                                    <x-lucide-check style="width: 13px; height: 13px; stroke-width: 2.4;" />
                                                    <span>Accept</span>
                                                </button>
                                            </form>
                                            <button 
                                                type="button" 
                                                class="btn-table-decline"
                                                onclick="confirmDeclineRequest({{ $req->id }}, '{{ addslashes($req->applicant_name) }}', '{{ addslashes($req->pet->name ?? 'Pet') }}')"
                                                title="Decline Adoption Request"
                                            >
                                                <x-lucide-x style="width: 13px; height: 13px; stroke-width: 2.4;" />
                                                <span>Decline</span>
                                            </button>
                                        @endif
                                        <button 
                                            type="button" 
                                            class="btn-table-message"
                                            onclick="window.location.href = '{{ route('messages') }}?recipient={{ urlencode($req->applicant_name) }}&recipient_id={{ $req->user_id }}&pet={{ urlencode($req->pet->name ?? 'Pet') }}&pet_id={{ $req->pet_id }}'"
                                        >
                                            <x-lucide-message-square style="width: 13px; height: 13px; stroke-width: 2.2;" />
                                            <span>Message</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
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
                        <div class="table-responsive hide-on-phone">
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
                                                        <img src="{{ $app->pet->image_url }}" alt="{{ $app->pet->name }}" loading="lazy" style="width: 44px; height: 44px; border-radius: 0.5rem; object-fit: cover; flex-shrink: 0;" />
                                                    @endif
                                                    <div>
                                                        <strong style="font-size: 1rem; color: var(--brand-dark-brown);">{{ $app->pet->name ?? 'Pet' }}</strong>
                                                        <div style="font-size: 0.84rem; color: #8C756B; margin-top: 0.15rem;">{{ $app->pet->species ?? '' }} • {{ $app->pet->breed ?? '' }}</div>
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
                                                <span class="status-pill {{ $badgeClass }}">{{ $app->status }}</span>
                                            </td>
                                            <td style="text-align: right;">
                                                <div class="table-actions-inline">
                                                    @if($app->pet && $app->pet->user_id)
                                                        <button 
                                                            type="button" 
                                                            class="btn-table-message"
                                                            onclick="window.location.href = '{{ route('messages') }}?recipient={{ urlencode($app->pet->posted_by ?? 'Rescuer') }}&recipient_id={{ $app->pet->user_id }}&pet={{ urlencode($app->pet->name ?? 'Pet') }}&pet_id={{ $app->pet_id }}'"
                                                        >
                                                            <x-lucide-message-square style="width: 13px; height: 13px; stroke-width: 2.2; margin-right: 0.2rem;" />
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
                                                            <x-lucide-trash-2 style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
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

                        {{-- Mobile Cards for My Applications (phone only) --}}
                        <div class="mobile-card-list" aria-label="My applications">
                            @foreach($myApplications as $app)
                                @php
                                    $appStatus = strtolower($app->status);
                                    $badgeClass = $appStatus === 'accepted' 
                                        ? 'status-pill-available' 
                                        : ($appStatus === 'declined' ? 'status-pill-declined' : ($appStatus === 'cancelled' ? 'status-pill-draft' : 'status-pill-pending'));
                                @endphp
                                <div class="mobile-app-card">
                                    <div class="mobile-card-top">
                                        @if($app->pet && $app->pet->image_path)
                                            <img src="{{ $app->pet->image_url }}" alt="{{ $app->pet->name }}" loading="lazy" class="mobile-card-thumb" style="width: 56px; height: 56px; border-radius: 0.5rem; object-fit: cover;">
                                        @endif
                                        <div class="mobile-card-info" style="flex: 1; min-width: 0;">
                                            <div class="mobile-card-name" style="font-weight: 700; color: #2D1810; font-size: 0.95rem;">{{ $app->pet->name ?? 'Pet' }}</div>
                                            <div class="mobile-card-details" style="font-size: 0.8rem; color: #52473D; margin-top: 0.2rem;">
                                                <span>{{ $app->pet->species ?? '' }} • {{ $app->pet->breed ?? '' }}</span><br>
                                                <span class="text-muted">Rescuer: {{ $app->pet->posted_by ?? 'Rescuer' }}</span>
                                            </div>
                                        </div>
                                        <span class="status-pill {{ $badgeClass }}" style="flex-shrink: 0; align-self: flex-start;">{{ $app->status }}</span>
                                    </div>
                                    <div style="font-size: 0.78rem; color: #8C756B; display: flex; justify-content: space-between; align-items: center;">
                                        <span>Submitted: {{ $app->created_at->format('M d, Y') }}</span>
                                        <span>{{ $app->barangay }}</span>
                                    </div>
                                    <div class="mobile-card-actions" style="display: flex; flex-direction: row; align-items: center; gap: 0.35rem; padding-top: 0.45rem; border-top: 1px solid #F3E8D8; width: 100%;">
                                        @if($app->pet && $app->pet->user_id)
                                            <button 
                                                type="button" 
                                                class="btn-table-message"
                                                style="flex: 1; min-width: 0; justify-content: center; height: 32px; background: #FDE047; color: #2D1810; border: none; border-radius: 0.5rem; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; cursor: pointer; font-family: inherit; gap: 0.25rem; padding: 0.25rem 0.45rem;"
                                                onclick="window.location.href = '{{ route('messages') }}?recipient={{ urlencode($app->pet->posted_by ?? 'Rescuer') }}&recipient_id={{ $app->pet->user_id }}&pet={{ urlencode($app->pet->name ?? 'Pet') }}&pet_id={{ $app->pet_id }}'"
                                            >
                                                <x-lucide-message-square style="width: 13px; height: 13px; stroke-width: 2.2;" />
                                                <span>Message</span>
                                            </button>
                                        @endif
                                        @if($app->status === 'Pending')
                                            <button 
                                                type="button" 
                                                class="btn-table-decline"
                                                style="flex: 1; min-width: 0; justify-content: center; height: 32px; background: #ffffff; color: #4A2E2B; border: 1.5px solid #D5C2B1; border-radius: 0.5rem; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; cursor: pointer; font-family: inherit; gap: 0.25rem; padding: 0.25rem 0.45rem;"
                                                onclick="confirmCancelRequest({{ $app->id }}, '{{ addslashes($app->pet->name ?? 'this pet') }}')"
                                                title="Cancel Adoption Request"
                                            >
                                                <x-lucide-x style="width: 13px; height: 13px; stroke-width: 2.2;" />
                                                <span>Cancel</span>
                                            </button>
                                        @elseif(in_array($app->status, ['Declined', 'Cancelled']))
                                            <button 
                                                type="button" 
                                                class="btn-table-delete"
                                                style="flex: 1; min-width: 0; justify-content: center; height: 32px; background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; border-radius: 0.5rem; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; cursor: pointer; font-family: inherit; gap: 0.25rem; padding: 0.25rem 0.45rem;"
                                                onclick="confirmDeleteApplication({{ $app->id }}, '{{ addslashes($app->pet->name ?? 'this pet') }}')"
                                                title="Delete Application Record"
                                            >
                                                <x-lucide-trash-2 style="width: 13px; height: 13px; stroke-width: 2;" />
                                                <span>Delete</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
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

        {{-- ================================================================
             TAB 3: Archived Posts
        ================================================================ --}}
        <div id="tab-panel-archived" class="mp-tab-panel" role="tabpanel" aria-labelledby="tab-btn-archived" style="display: none;">
            <div class="dashboard-section">
                <div class="section-title-bar">
                    <h2>Archived Posts ({{ $archivedPets->count() }})</h2>
                </div>
                <div class="table-card">
                    @if($archivedPets->count() > 0)
                        <div class="table-responsive hide-on-phone">
                            <table class="kupkop-table">
                                <thead>
                                    <tr>
                                        <th style="width: 90px;">Photo</th>
                                        <th>Animal Name & ID</th>
                                        <th>Details</th>
                                        <th>Archived On</th>
                                        <th style="text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($archivedPets as $pet)
                                        <tr>
                                            <td>
                                                <div class="table-pet-thumb" onclick="openFullscreenCarouselForPet({{ json_encode($pet) }})" style="cursor: pointer;" title="Click to view photos in fullscreen carousel">
                                                    <img src="{{ $pet->image_url }}" alt="{{ $pet->name }}" loading="lazy">
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
                                                    <span class="text-muted">{{ $pet->age }} • {{ $pet->gender }}</span>
                                                </div>
                                            </td>
                                            <td class="text-muted" style="font-size: 0.85rem;">{{ $pet->archived_at->format('M d, Y') }}</td>
                                            <td style="text-align: right;">
                                                <div class="table-actions-inline">
                                                    {{-- Restore Action --}}
                                                    <form method="POST" action="{{ route('posts.restore', $pet->id) }}" class="inline-form">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn-table-restore" title="Restore Post">
                                                            <x-lucide-rotate-ccw style="width: 13px; height: 13px; stroke-width: 2.2; margin-right: 0.25rem;" />
                                                            <span>Restore</span>
                                                        </button>
                                                    </form>
                                                    {{-- Delete Permanently Action --}}
                                                    <button
                                                        type="button"
                                                        class="btn-table-delete"
                                                        onclick="confirmPermanentDelete({{ $pet->id }}, '{{ addslashes($pet->name) }}')"
                                                        title="Delete Permanently"
                                                    >
                                                        <x-lucide-trash-2 style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                                        <span>Delete Permanently</span>
                                                    </button>
                                                    {{-- Hidden permanent delete form --}}
                                                    <form id="permanent-delete-form-{{ $pet->id }}" method="POST" action="{{ route('posts.destroy', $pet->id) }}" class="inline-form" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards for Archived Posts (phone only) --}}
                        <div class="mobile-card-list" aria-label="Archived posts">
                            @foreach($archivedPets as $pet)
                                <div class="mobile-pet-card">
                                    <div class="mobile-card-top">
                                        <div class="table-pet-thumb" onclick="openFullscreenCarouselForPet({{ json_encode($pet) }})" style="cursor: pointer; width: 64px; height: 64px; flex-shrink: 0;" title="Click to view photos">
                                            <img src="{{ $pet->image_url }}" alt="{{ $pet->name }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;">
                                        </div>
                                        <div class="mobile-card-info" style="flex: 1; min-width: 0;">
                                            <div class="mobile-card-name" style="font-weight: 700; color: #2D1810; font-size: 0.95rem;">{{ $pet->name }}</div>
                                            <div class="mobile-card-id" style="font-size: 0.75rem; color: #8C756B;">{{ $pet->rescuer_id ?? ('#PET-' . $pet->id) }}</div>
                                            <div class="mobile-card-details" style="font-size: 0.8rem; color: #52473D; margin-top: 0.2rem; line-height: 1.35;">
                                                <strong>{{ $pet->species }} • {{ $pet->breed }}</strong><br>
                                                <span class="text-muted">{{ $pet->age }} • {{ $pet->gender }}</span><br>
                                                <span class="text-muted" style="font-size: 0.75rem;">Archived: {{ $pet->archived_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                        <span class="badge-archived" style="flex-shrink: 0; align-self: flex-start; padding: 0.15rem 0.55rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; background: #F3F4F6; color: #4B5563;">Archived</span>
                                    </div>
                                    <div class="mobile-card-actions" style="display: flex; flex-direction: row; align-items: center; gap: 0.35rem; padding-top: 0.45rem; border-top: 1px solid #F3E8D8; width: 100%;">
                                        <form method="POST" action="{{ route('posts.restore', $pet->id) }}" class="inline-form" style="flex: 1; display: contents;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-table-restore" style="flex: 1; min-width: 0; justify-content: center; height: 32px; font-size: 0.78rem; font-weight: 700; padding: 0.25rem 0.45rem; border-radius: 0.5rem;" title="Restore Post">
                                                <x-lucide-rotate-ccw style="width: 13px; height: 13px; stroke-width: 2.2; margin-right: 0.25rem;" />
                                                <span>Restore</span>
                                            </button>
                                        </form>
                                        <button
                                            type="button"
                                            class="btn-table-delete"
                                            style="flex: 1; min-width: 0; justify-content: center; height: 32px; font-size: 0.78rem; font-weight: 700; padding: 0.25rem 0.45rem; border-radius: 0.5rem;"
                                            onclick="confirmPermanentDelete({{ $pet->id }}, '{{ addslashes($pet->name) }}')"
                                            title="Delete Permanently"
                                        >
                                            <x-lucide-trash-2 style="width: 13px; height: 13px; stroke-width: 2; margin-right: 0.25rem;" />
                                            <span>Delete Permanently</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state-card">
                            <div class="empty-icon">
                                <x-lucide-archive style="width: 44px; height: 44px; stroke-width: 1.5; color: #8C756B; margin-bottom: 0.35rem;" />
                            </div>
                            <h3>No archived posts</h3>
                            <p class="text-muted">Posts you archive will appear here. You can restore them or permanently delete them at any time.</p>
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
                <label class="form-label font-bold">Animal Photos <span class="required-asterisk" style="color: #DC2626;">*</span></label>
                <div class="upload-dropzone" id="create-upload-zone" onclick="triggerCreateImageUpload(event)">
                    <div class="upload-icon" id="create-upload-placeholder">
                        <x-lucide-camera style="width: 44px; height: 44px; stroke-width: 1.5; color: #8C756B; margin-bottom: 0.35rem;" />
                    </div>
                    <p class="upload-title" id="create-upload-title">Click to upload photos or drag &amp; drop</p>
                    <p class="upload-hint" id="create-upload-hint">Upload up to 5 photos &bull; PNG, JPG, JPEG, or WEBP up to 5MB each</p>
                    <input 
                        type="file" 
                        id="pet_images" 
                        name="pet_images[]" 
                        accept="image/png, image/jpeg, image/jpg, image/webp" 
                        multiple
                        style="display: none;"
                        onchange="previewPetImages(event)"
                    >
                    {{-- Hidden alias for backwards compatibility --}}
                    <input type="file" id="pet_image" style="display: none;" disabled>

                    {{-- Multi-image preview container (Cleaned up, organized, and uncluttered) --}}
                    <div id="image-preview-box" class="multi-image-preview-container" style="display: none; width: 100%;">
                        <div class="preview-main-frame">
                            <img id="image-preview" src="" alt="Photo Preview" loading="lazy">
                        </div>
                        <div id="preview-thumbnails-grid" class="preview-thumbnails-grid"></div>
                        <div class="preview-actions-bar">
                            <span class="preview-summary-text" id="preview-summary-text">1 photo selected</span>
                            <button type="button" class="btn-clear-photos" onclick="clearCreateImages(event)">
                                <x-lucide-trash-2 style="width: 14px; height: 14px; stroke-width: 2.2;" />
                                <span>Change / Remove Photos</span>
                            </button>
                        </div>
                    </div>
                </div>
                @error('pet_images')
                    <span class="form-error">{{ $message }}</span>
                @enderror
                @error('pet_images.*')
                    <span class="form-error">{{ $message }}</span>
                @enderror
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
                        <div class="form-label-row">
                            <label for="name" class="form-label">Animal Name *</label>
                            <span id="create-name-counter" class="char-counter">0/30</span>
                        </div>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            required 
                            maxlength="30"
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
                        <div class="custom-select-field-wrap">
                            <select id="species" name="species" required class="form-select-styled">
                                <option value="Dog" {{ old('species') == 'Dog' ? 'selected' : '' }}>Dog</option>
                                <option value="Cat" {{ old('species') == 'Cat' ? 'selected' : '' }}>Cat</option>
                                <option value="Other" {{ old('species') == 'Other' ? 'selected' : '' }}>Other Animal</option>
                            </select>
                            <x-lucide-chevron-down class="select-dropdown-arrow" />
                        </div>
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
                        <div class="custom-select-field-wrap">
                            <select 
                                id="age" 
                                name="age" 
                                required 
                                class="form-select-styled"
                            >
                                <option value="" disabled {{ old('age') ? '' : 'selected' }}>Select pet age...</option>
                                <optgroup label="Months (Puppy / Kitten / Junior)">
                                    <option value="1 month" {{ old('age') === '1 month' ? 'selected' : '' }}>1 month</option>
                                    <option value="2 months" {{ old('age') === '2 months' ? 'selected' : '' }}>2 months</option>
                                    <option value="3 months" {{ old('age') === '3 months' ? 'selected' : '' }}>3 months</option>
                                    <option value="4 months" {{ old('age') === '4 months' ? 'selected' : '' }}>4 months</option>
                                    <option value="5 months" {{ old('age') === '5 months' ? 'selected' : '' }}>5 months</option>
                                    <option value="6 months" {{ old('age') === '6 months' ? 'selected' : '' }}>6 months</option>
                                    <option value="7 months" {{ old('age') === '7 months' ? 'selected' : '' }}>7 months</option>
                                    <option value="8 months" {{ old('age') === '8 months' ? 'selected' : '' }}>8 months</option>
                                    <option value="9 months" {{ old('age') === '9 months' ? 'selected' : '' }}>9 months</option>
                                    <option value="10 months" {{ old('age') === '10 months' ? 'selected' : '' }}>10 months</option>
                                    <option value="11 months" {{ old('age') === '11 months' ? 'selected' : '' }}>11 months</option>
                                </optgroup>
                                <optgroup label="Years (Adult / Senior)">
                                    <option value="1 year" {{ old('age') === '1 year' ? 'selected' : '' }}>1 year</option>
                                    <option value="2 years" {{ old('age') === '2 years' ? 'selected' : '' }}>2 years</option>
                                    <option value="3 years" {{ old('age') === '3 years' ? 'selected' : '' }}>3 years</option>
                                    <option value="4 years" {{ old('age') === '4 years' ? 'selected' : '' }}>4 years</option>
                                    <option value="5 years" {{ old('age') === '5 years' ? 'selected' : '' }}>5 years</option>
                                    <option value="6 years" {{ old('age') === '6 years' ? 'selected' : '' }}>6 years</option>
                                    <option value="7 years" {{ old('age') === '7 years' ? 'selected' : '' }}>7 years</option>
                                    <option value="8 years" {{ old('age') === '8 years' ? 'selected' : '' }}>8 years</option>
                                    <option value="9 years" {{ old('age') === '9 years' ? 'selected' : '' }}>9 years</option>
                                    <option value="10 years" {{ old('age') === '10 years' ? 'selected' : '' }}>10 years</option>
                                    <option value="11 years" {{ old('age') === '11 years' ? 'selected' : '' }}>11 years</option>
                                    <option value="12 years" {{ old('age') === '12 years' ? 'selected' : '' }}>12 years</option>
                                    <option value="13 years" {{ old('age') === '13 years' ? 'selected' : '' }}>13 years</option>
                                    <option value="14 years" {{ old('age') === '14 years' ? 'selected' : '' }}>14 years</option>
                                    <option value="15 years" {{ old('age') === '15 years' ? 'selected' : '' }}>15 years</option>
                                    <option value="16 years" {{ old('age') === '16 years' ? 'selected' : '' }}>16 years</option>
                                    <option value="17 years" {{ old('age') === '17 years' ? 'selected' : '' }}>17 years</option>
                                    <option value="18 years" {{ old('age') === '18 years' ? 'selected' : '' }}>18 years</option>
                                    <option value="19 years" {{ old('age') === '19 years' ? 'selected' : '' }}>19 years</option>
                                    <option value="20 years" {{ old('age') === '20 years' ? 'selected' : '' }}>20 years</option>
                                </optgroup>
                            </select>
                            <x-lucide-chevron-down class="select-dropdown-arrow" />
                        </div>
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
                        <div class="custom-select-field-wrap">
                            <select id="vaccination_status" name="vaccination_status" class="form-select-styled">
                                <option value="Not vaccinated yet" {{ old('vaccination_status', 'Not vaccinated yet') === 'Not vaccinated yet' ? 'selected' : '' }}>Not vaccinated yet</option>
                                <option value="Up to date / Fully Vaccinated" {{ old('vaccination_status') === 'Up to date / Fully Vaccinated' ? 'selected' : '' }}>Up to date / Fully Vaccinated</option>
                                <option value="Partially Vaccinated (1st dose)" {{ old('vaccination_status') === 'Partially Vaccinated (1st dose)' ? 'selected' : '' }}>Partially Vaccinated (1st dose)</option>
                                <option value="Anti-Rabies Only" {{ old('vaccination_status') === 'Anti-Rabies Only' ? 'selected' : '' }}>Anti-Rabies Only</option>
                            </select>
                            <x-lucide-chevron-down class="select-dropdown-arrow" />
                        </div>
                    </div>

                    {{-- Deworming Status --}}
                    <div class="form-group">
                        <label for="deworming_status" class="form-label">Deworming Status</label>
                        <div class="custom-select-field-wrap">
                            <select id="deworming_status" name="deworming_status" class="form-select-styled">
                                <option value="Not dewormed" {{ old('deworming_status', 'Not dewormed') === 'Not dewormed' ? 'selected' : '' }}>Not dewormed</option>
                                <option value="Dewormed (Up to date)" {{ old('deworming_status') === 'Dewormed (Up to date)' ? 'selected' : '' }}>Dewormed (Up to date)</option>
                                <option value="Partially Dewormed (1st dose)" {{ old('deworming_status') === 'Partially Dewormed (1st dose)' ? 'selected' : '' }}>Partially Dewormed (1st dose)</option>
                            </select>
                            <x-lucide-chevron-down class="select-dropdown-arrow" />
                        </div>
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
                <div class="form-label-row">
                    <label for="description" class="form-label font-bold" style="margin-bottom: 0;">4. Rescue Story &amp; Description *</label>
                    <span id="create-description-counter" class="char-counter">0/300</span>
                </div>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="4" 
                    required 
                    maxlength="300"
                    placeholder="Tell your neighbors how this animal was rescued, their personality, habits, and what kind of home would be best for them..." 
                    class="form-control textarea-field"
                    style="margin-top: 0.5rem;"
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
                <button type="submit" class="btn-submit-post is-disabled" id="create-post-submit-btn-mp" disabled style="display: none;">
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
                                        <img src="{{ $draft->image_url }}" alt="{{ $draft->name }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
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
     CONFIRM ADOPT / REVERT STATUS MODAL DIALOG
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
        {{-- Icon container — colour changes based on direction --}}
        <div id="adopt-modal-icon-wrap" style="width: 56px; height: 56px; background: #D1FAE5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
            <span id="adopt-modal-icon-adopt" style="display: flex; align-items: center; justify-content: center;">
                <x-lucide-check-circle-2 style="width: 28px; height: 28px; stroke-width: 2; color: #059669;" />
            </span>
            <span id="adopt-modal-icon-revert" style="display: none; align-items: center; justify-content: center;">
                <x-lucide-rotate-ccw style="width: 28px; height: 28px; stroke-width: 2; color: #4A2E2B;" />
            </span>
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
                id="adopt-modal-confirm-btn"
                type="button"
                onclick="submitMarkAdopted()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: none; background: #059669; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#047857'"
                onmouseout="this.style.background='#059669'"
            >
                <span id="adopt-modal-confirm-icon" style="display: flex; align-items: center; gap: 0.35rem;">
                    <x-lucide-check-circle-2 style="width: 15px; height: 15px; stroke-width: 2;" />
                    <span id="adopt-modal-confirm-label">Yes, Mark as Adopted</span>
                </span>
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
     CONFIRM ARCHIVE POST MODAL DIALOG
   ========================================================================== --}}
<div
    id="confirm-archive-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-archive-title"
    style="display: none; z-index: 1070;"
    onclick="if(event.target.id === 'confirm-archive-modal') hideConfirmArchive()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 420px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; box-shadow: 0 25px 60px rgba(0,0,0,0.35); text-align: center; border: 1px solid #EADDCF;">
        <div style="width: 56px; height: 56px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
            <x-lucide-archive style="width: 28px; height: 28px; stroke-width: 2; color: #D97706;" />
        </div>

        <h3 id="confirm-archive-title" style="font-size: 1.25rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.5rem 0;">
            Archive Post?
        </h3>

        <p style="font-size: 0.92rem; color: #52473D; margin: 0 0 1.5rem 0; line-height: 1.5;">
            Are you sure you want to archive <strong id="archive-pet-name" style="color: #4A2E2B;"></strong>? The post will be removed from the public feed. You can restore it anytime from the <em>Archived Posts</em> tab.
        </p>

        {{-- Hidden Form for submission --}}
        <form id="archive-post-form" method="POST" action="" style="display: none;">
            @csrf
            @method('PATCH')
        </form>

        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button
                type="button"
                onclick="hideConfirmArchive()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#F3E8D8'"
                onmouseout="this.style.background='#FAF5EE'"
            >
                <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Cancel</span>
            </button>
            <button
                type="button"
                onclick="submitArchivePost()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: none; background: #D97706; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#B45309'"
                onmouseout="this.style.background='#D97706'"
            >
                <x-lucide-archive style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Yes, Archive</span>
            </button>
        </div>
    </div>
</div>

{{-- ==========================================================================
     CONFIRM PERMANENT DELETE MODAL DIALOG
   ========================================================================== --}}
<div
    id="confirm-permanent-delete-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-perm-delete-title"
    style="display: none; z-index: 1070;"
    onclick="if(event.target.id === 'confirm-permanent-delete-modal') hideConfirmPermanentDelete()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 420px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; box-shadow: 0 25px 60px rgba(0,0,0,0.35); text-align: center; border: 1px solid #EADDCF;">
        <div style="width: 56px; height: 56px; background: #FEE2E2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
            <x-lucide-trash-2 style="width: 28px; height: 28px; stroke-width: 2; color: #DC2626;" />
        </div>

        <h3 id="confirm-perm-delete-title" style="font-size: 1.25rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.5rem 0;">
            Delete Permanently?
        </h3>

        <p style="font-size: 0.92rem; color: #52473D; margin: 0 0 0.4rem 0; line-height: 1.5;">
            You are about to permanently delete <strong id="permanent-delete-pet-name" style="color: #4A2E2B;"></strong>.
        </p>
        <p style="font-size: 0.88rem; color: #DC2626; font-weight: 600; margin: 0 0 1.5rem 0;">
            ⚠ This action cannot be undone.
        </p>

        {{-- Hidden Form for submission — action set dynamically via JS --}}
        <form id="permanent-delete-form" method="POST" action="" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button
                type="button"
                onclick="hideConfirmPermanentDelete()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#F3E8D8'"
                onmouseout="this.style.background='#FAF5EE'"
            >
                <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Cancel</span>
            </button>
            <button
                type="button"
                onclick="submitPermanentDelete()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.15rem; border: none; background: #DC2626; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#B91C1C'"
                onmouseout="this.style.background='#DC2626'"
            >
                <x-lucide-trash-2 style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Yes, Delete Forever</span>
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
                <label class="form-label font-bold">Animal Photos <span style="font-weight: 500; font-size: 0.85rem; color: #8C756B;">(Optional, up to 5 photos)</span></label>
                <div class="upload-dropzone" onclick="document.getElementById('edit_pet_image').click()">
                    <div class="upload-icon">
                        <x-lucide-camera style="width: 36px; height: 36px; stroke-width: 1.6; color: #8C756B; margin-bottom: 0.35rem;" />
                    </div>
                    <p class="upload-title">Click to change pet photos (Optional)</p>
                    <p class="upload-hint">Upload up to 5 photos &bull; PNG, JPG, JPEG, or WEBP up to 5MB each</p>
                    <input 
                        type="file" 
                        id="edit_pet_image" 
                        name="pet_images[]" 
                        accept="image/png, image/jpeg, image/jpg, image/webp" 
                        multiple
                        style="display: none;"
                        onchange="previewEditImage(event)"
                    >
                    <div id="edit-image-preview-box" class="multi-image-preview-container" style="display: none; width: 100%;">
                        <div class="preview-main-frame">
                            <img id="edit-image-preview" src="" alt="Photo Preview" loading="lazy">
                        </div>
                        <div id="edit-preview-thumbnails-grid" class="preview-thumbnails-grid"></div>
                        <div class="preview-actions-bar">
                            <span class="preview-summary-text" id="edit-preview-summary">1 photo selected</span>
                            <button type="button" class="btn-clear-photos" onclick="clearEditImages(event)">
                                <x-lucide-trash-2 style="width: 14px; height: 14px; stroke-width: 2.2;" />
                                <span>Change / Remove New Photos</span>
                            </button>
                        </div>
                    </div>
                </div>
                @error('pet_images')
                    <span class="form-error">{{ $message }}</span>
                @enderror
                @error('pet_images.*')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- 2. Basic Information --}}
            <div class="form-section">
                <label class="form-label font-bold">2. Basic Information</label>
                
                <div class="form-grid-2">
                    <div class="form-group">
                        <div class="form-label-row">
                            <label for="edit_name" class="form-label">Animal Name *</label>
                            <span id="edit-name-counter" class="char-counter">0/30</span>
                        </div>
                        <input type="text" id="edit_name" name="name" required maxlength="30" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_species" class="form-label">Species *</label>
                        <div class="custom-select-field-wrap">
                            <select id="edit_species" name="species" required class="form-select-styled">
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                                <option value="Other">Other Animal</option>
                            </select>
                            <x-lucide-chevron-down class="select-dropdown-arrow" />
                        </div>
                    </div>
                </div>

                <div class="form-grid-2 mt-3">
                    <div class="form-group">
                        <label for="edit_breed" class="form-label">Breed / Mix</label>
                        <input type="text" id="edit_breed" name="breed" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_age" class="form-label">Age *</label>
                        <div class="custom-select-field-wrap">
                            <select id="edit_age" name="age" required class="form-select-styled">
                                <option value="" disabled selected>Select pet age...</option>
                                <optgroup label="Months (Puppy / Kitten / Junior)">
                                    <option value="1 month">1 month</option>
                                    <option value="2 months">2 months</option>
                                    <option value="3 months">3 months</option>
                                    <option value="4 months">4 months</option>
                                    <option value="5 months">5 months</option>
                                    <option value="6 months">6 months</option>
                                    <option value="7 months">7 months</option>
                                    <option value="8 months">8 months</option>
                                    <option value="9 months">9 months</option>
                                    <option value="10 months">10 months</option>
                                    <option value="11 months">11 months</option>
                                </optgroup>
                                <optgroup label="Years (Adult / Senior)">
                                    <option value="1 year">1 year</option>
                                    <option value="2 years">2 years</option>
                                    <option value="3 years">3 years</option>
                                    <option value="4 years">4 years</option>
                                    <option value="5 years">5 years</option>
                                    <option value="6 years">6 years</option>
                                    <option value="7 years">7 years</option>
                                    <option value="8 years">8 years</option>
                                    <option value="9 years">9 years</option>
                                    <option value="10 years">10 years</option>
                                    <option value="11 years">11 years</option>
                                    <option value="12 years">12 years</option>
                                    <option value="13 years">13 years</option>
                                    <option value="14 years">14 years</option>
                                    <option value="15 years">15 years</option>
                                    <option value="16 years">16 years</option>
                                    <option value="17 years">17 years</option>
                                    <option value="18 years">18 years</option>
                                    <option value="19 years">19 years</option>
                                    <option value="20 years">20 years</option>
                                </optgroup>
                            </select>
                            <x-lucide-chevron-down class="select-dropdown-arrow" />
                        </div>
                    </div>
                </div>

                <div class="form-grid-2 mt-3">
                    <div class="form-group">
                        <label for="edit_gender" class="form-label">Gender *</label>
                        <div class="custom-select-field-wrap">
                            <select id="edit_gender" name="gender" required class="form-select-styled">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <x-lucide-chevron-down class="select-dropdown-arrow" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_status" class="form-label">Status *</label>
                        <div class="custom-select-field-wrap">
                            <select id="edit_status" name="status" required class="form-select-styled">
                                <option value="Available">Available</option>
                                <option value="Draft">Draft</option>
                                <option value="Adopted">Adopted</option>
                            </select>
                            <x-lucide-chevron-down class="select-dropdown-arrow" />
                        </div>
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
                        <div class="custom-select-field-wrap">
                            <select id="edit_vaccination_status" name="vaccination_status" class="form-select-styled">
                                <option value="Not vaccinated yet">Not vaccinated yet</option>
                                <option value="Up to date / Fully Vaccinated">Up to date / Fully Vaccinated</option>
                                <option value="Partially Vaccinated (1st dose)">Partially Vaccinated (1st dose)</option>
                                <option value="Anti-Rabies Only">Anti-Rabies Only</option>
                            </select>
                            <x-lucide-chevron-down class="select-dropdown-arrow" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_deworming_status" class="form-label">Deworming Status</label>
                        <div class="custom-select-field-wrap">
                            <select id="edit_deworming_status" name="deworming_status" class="form-select-styled">
                                <option value="Not dewormed">Not dewormed</option>
                                <option value="Dewormed (Up to date)">Dewormed (Up to date)</option>
                                <option value="Partially Dewormed (1st dose)">Partially Dewormed (1st dose)</option>
                            </select>
                            <x-lucide-chevron-down class="select-dropdown-arrow" />
                        </div>
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
                <div class="form-label-row">
                    <label for="edit_description" class="form-label font-bold" style="margin-bottom: 0;">4. Rescue Story &amp; Description *</label>
                    <span id="edit-description-counter" class="char-counter">0/300</span>
                </div>
                <textarea id="edit_description" name="description" rows="4" required maxlength="300" class="form-control textarea-field" style="margin-top: 0.5rem;"></textarea>
            </div>

            {{-- Form Footer Actions --}}
            <div class="create-modal-footer" style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
                <button 
                    type="button" 
                    id="edit-delete-post-btn"
                    onclick="handleEditModalDeletePost()"
                    style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.65rem 1rem; background: #FFF7ED; color: #C2410C; border: 1px solid #FED7AA; border-radius: 0.5rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; margin-right: auto;"
                    onmouseover="this.style.background='#FFEDD5'"
                    onmouseout="this.style.background='#FFF7ED'"
                >
                    <x-lucide-archive style="width: 15px; height: 15px; stroke-width: 2;" />
                    <span id="edit-delete-post-text">Archive Post</span>
                </button>
                <button type="button" class="btn-draft" onclick="closeEditPostModal()" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                    <span>Cancel</span>
                </button>
                <button type="submit" class="btn-submit-post is-disabled" id="edit-post-submit-btn" disabled style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <x-lucide-check-circle-2 style="width: 15px; height: 15px; stroke-width: 2;" />
                    <span>Save Changes</span>
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
        const archivedBtn = document.getElementById('tab-btn-archived');
        const listedPanel = document.getElementById('tab-panel-listed');
        const applicationsPanel = document.getElementById('tab-panel-applications');
        const archivedPanel = document.getElementById('tab-panel-archived');

        // Reset all tabs
        [listedBtn, applicationsBtn, archivedBtn].forEach(function(b) {
            if (b) { b.classList.remove('mp-tab-active'); b.setAttribute('aria-selected', 'false'); }
        });
        [listedPanel, applicationsPanel, archivedPanel].forEach(function(p) {
            if (p) p.style.display = 'none';
        });

        if (tabName === 'listed') {
            if (listedBtn) { listedBtn.classList.add('mp-tab-active'); listedBtn.setAttribute('aria-selected', 'true'); }
            if (listedPanel) listedPanel.style.display = 'block';
        } else if (tabName === 'applications') {
            if (applicationsBtn) { applicationsBtn.classList.add('mp-tab-active'); applicationsBtn.setAttribute('aria-selected', 'true'); }
            if (applicationsPanel) applicationsPanel.style.display = 'block';
        } else if (tabName === 'archived') {
            if (archivedBtn) { archivedBtn.classList.add('mp-tab-active'); archivedBtn.setAttribute('aria-selected', 'true'); }
            if (archivedPanel) archivedPanel.style.display = 'block';
        }
    }

    // Archive Post Confirmation Functions
    function confirmArchivePost(petId, petName) {
        const form = document.getElementById('archive-post-form');
        if (form) form.action = '/posts/' + petId + '/archive';
        const nameEl = document.getElementById('archive-pet-name');
        if (nameEl) nameEl.textContent = '"' + petName + '"';
        const modal = document.getElementById('confirm-archive-modal');
        if (modal) modal.style.display = 'flex';
    }

    function hideConfirmArchive() {
        const modal = document.getElementById('confirm-archive-modal');
        if (modal) modal.style.display = 'none';
    }

    function submitArchivePost() {
        const form = document.getElementById('archive-post-form');
        if (form) {
            if (window.showKupLoader) window.showKupLoader('Archiving post...');
            form.submit();
        }
    }

    // Permanent Delete Confirmation Functions (for archived posts)
    function confirmPermanentDelete(petId, petName) {
        const form = document.getElementById('permanent-delete-form');
        if (form) form.action = '/posts/' + petId;
        const nameEl = document.getElementById('permanent-delete-pet-name');
        if (nameEl) nameEl.textContent = '"' + petName + '"';
        const modal = document.getElementById('confirm-permanent-delete-modal');
        if (modal) modal.style.display = 'flex';
    }

    function hideConfirmPermanentDelete() {
        const modal = document.getElementById('confirm-permanent-delete-modal');
        if (modal) modal.style.display = 'none';
    }

    function submitPermanentDelete() {
        const form = document.getElementById('permanent-delete-form');
        if (form) {
            if (window.showKupLoader) window.showKupLoader('Deleting post permanently...');
            form.submit();
        }
    }

    // Modal Display Functions & Dirty Form Tracking for Create Post
    const CREATE_POST_DEFAULTS = {
        hasImages: false,
        name: '',
        species: 'Dog',
        breed: '',
        age: '',
        gender: 'Male',
        location: '',
        vaccination_status: 'Not vaccinated yet',
        deworming_status: 'Not dewormed',
        is_spayed_neutered: false,
        special_health_notes: '',
        description: ''
    };

    let createPostInitialSnapshot = null;
    let createPostIsTouched = false;
    let createPostIsProgrammaticReset = false;

    function getCreatePostFormState() {
        const modal = document.getElementById('create-post-modal');
        if (!modal) return null;

        const fileInput = modal.querySelector('#pet_images') || modal.querySelector('#pet_image');
        const hasImages = Boolean(fileInput && fileInput.files && fileInput.files.length > 0);
        const genderChecked = modal.querySelector('input[name="gender"]:checked');
        const spayedCheckbox = modal.querySelector('input[name="is_spayed_neutered"]');

        return {
            hasImages: hasImages,
            name: (modal.querySelector('#name')?.value || '').trim(),
            species: modal.querySelector('#species')?.value || 'Dog',
            breed: (modal.querySelector('#breed')?.value || '').trim(),
            age: modal.querySelector('#age')?.value || '',
            gender: genderChecked ? genderChecked.value : 'Male',
            location: (modal.querySelector('#location')?.value || '').trim(),
            vaccination_status: modal.querySelector('#vaccination_status')?.value || 'Not vaccinated yet',
            deworming_status: modal.querySelector('#deworming_status')?.value || 'Not dewormed',
            is_spayed_neutered: Boolean(spayedCheckbox && spayedCheckbox.checked),
            special_health_notes: (modal.querySelector('#special_health_notes')?.value || '').trim(),
            description: (modal.querySelector('#description')?.value || '').trim()
        };
    }

    function checkCreatePostIsDirty() {
        const current = getCreatePostFormState();
        if (!current) return false;

        const baseline = createPostInitialSnapshot || CREATE_POST_DEFAULTS;

        return current.hasImages !== baseline.hasImages ||
            current.name !== baseline.name ||
            current.species !== baseline.species ||
            current.breed !== baseline.breed ||
            current.age !== baseline.age ||
            current.gender !== baseline.gender ||
            current.location !== baseline.location ||
            current.vaccination_status !== baseline.vaccination_status ||
            current.deworming_status !== baseline.deworming_status ||
            current.is_spayed_neutered !== baseline.is_spayed_neutered ||
            current.special_health_notes !== baseline.special_health_notes ||
            current.description !== baseline.description;
    }

    function openCreatePostModal() {
        const modal = document.getElementById('create-post-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');

            const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
            if (!hasErrors) {
                createPostIsTouched = false;
                createPostInitialSnapshot = Object.assign({}, CREATE_POST_DEFAULTS);
            } else {
                createPostIsTouched = checkCreatePostIsDirty();
            }

            updateCreateSubmitButtonState();
        }
    }

    // Auto-open Create Post modal when navigated here with ?create=1 (e.g., from the navbar "+" button)
    (function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('create') === '1') {
            // Remove the query param from the URL without a reload so the browser history stays clean
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
            // Open the modal after the page has fully rendered
            document.addEventListener('DOMContentLoaded', function() {
                openCreatePostModal();
            });
            // Fallback: also try immediately in case DOMContentLoaded already fired
            if (document.readyState !== 'loading') {
                openCreatePostModal();
            }
        }
    })();

    function closeCreatePostModal() {
        const modal = document.getElementById('create-post-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
        createPostIsTouched = false;
        updateCreateSubmitButtonState();
    }

    function handleCreateBackdropClick(event) {
        if (event.target.id === 'create-post-modal') {
            confirmCloseCreatePost();
        }
    }

    // Image Previews (Supports up to 5 photos with primary thumbnail & +N indicator)
    function triggerCreateImageUpload(event) {
        if (event.target.closest('.btn-clear-photos')) return;
        const input = document.getElementById('pet_images');
        if (input) input.click();
    }

    function previewPetImages(event) {
        const input = event.target;
        if (!input.files || input.files.length === 0) return;

        let files = Array.from(input.files);
        if (files.length > 5) {
            alert('You may only upload up to 5 photos. The first 5 photos have been selected.');
            files = files.slice(0, 5);
        }

        const placeholder = document.getElementById('create-upload-placeholder');
        const title = document.getElementById('create-upload-title');
        const hint = document.getElementById('create-upload-hint');
        const previewBox = document.getElementById('image-preview-box');
        const mainImg = document.getElementById('image-preview');
        const thumbsGrid = document.getElementById('preview-thumbnails-grid');
        const summaryText = document.getElementById('preview-summary-text');

        if (placeholder) placeholder.style.display = 'none';
        if (title) title.style.display = 'none';
        if (hint) hint.style.display = 'none';
        if (previewBox) previewBox.style.display = 'block';

        // Automatically treat the very first image as main thumbnail photo
        const reader0 = new FileReader();
        reader0.onload = function(e) {
            if (mainImg) mainImg.src = e.target.result;
        };
        reader0.readAsDataURL(files[0]);

        if (thumbsGrid) thumbsGrid.innerHTML = '';

        if (summaryText) {
            summaryText.textContent = files.length === 1 
                ? '1 photo selected' 
                : files.length + ' photos selected';
        }

        // Render clean, structured thumbnail cards without explicit "Main" badges
        files.forEach(function(file, index) {
            const r = new FileReader();
            r.onload = function(e) {
                const thumbItem = document.createElement('div');
                thumbItem.className = 'preview-thumb-card' + (index === 0 ? ' is-active' : '');
                thumbItem.title = 'Click to preview photo ' + (index + 1);

                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Photo ' + (index + 1);
                thumbItem.appendChild(img);

                thumbItem.onclick = function(ev) {
                    ev.stopPropagation();
                    if (mainImg) mainImg.src = e.target.result;
                    if (thumbsGrid) {
                        thumbsGrid.querySelectorAll('.preview-thumb-card').forEach(function(c) {
                            c.classList.remove('is-active');
                        });
                    }
                    thumbItem.classList.add('is-active');
                };

                if (thumbsGrid) thumbsGrid.appendChild(thumbItem);
            };
            r.readAsDataURL(file);
        });

        createPostIsTouched = true;
        updateCreateSubmitButtonState();
    }

    function clearCreateImages(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        const input = document.getElementById('pet_images');
        if (input) input.value = '';

        const placeholder = document.getElementById('create-upload-placeholder');
        const title = document.getElementById('create-upload-title');
        const hint = document.getElementById('create-upload-hint');
        const previewBox = document.getElementById('image-preview-box');
        const mainImg = document.getElementById('image-preview');
        const thumbsGrid = document.getElementById('preview-thumbnails-grid');

        if (placeholder) placeholder.style.display = '';
        if (title) title.style.display = '';
        if (hint) hint.style.display = '';
        if (previewBox) previewBox.style.display = 'none';
        if (mainImg) mainImg.src = '';
        if (thumbsGrid) thumbsGrid.innerHTML = '';

        updateCreateSubmitButtonState();
    }

    // Dynamic submit button activation for Create Post
    function updateCreateSubmitButtonState() {
        const submitBtn = document.getElementById('create-post-submit-btn-mp') || document.getElementById('create-post-submit-btn');
        if (!submitBtn) return;
        const isDirty = checkCreatePostIsDirty();
        const shouldActivate = createPostIsTouched && isDirty;
        submitBtn.disabled = !shouldActivate;
        if (shouldActivate) {
            submitBtn.classList.remove('is-disabled');
            submitBtn.style.display = '';
        } else {
            submitBtn.classList.add('is-disabled');
            submitBtn.style.display = 'none';
        }
    }

    // Backwards compatibility alias
    function previewPetImage(event) {
        previewPetImages(event);
    }

    function previewEditImage(event) {
        const input = event.target;
        if (!input.files || input.files.length === 0) return;
        let files = Array.from(input.files);
        if (files.length > 5) {
            alert('You may only upload up to 5 photos. The first 5 photos have been selected.');
            files = files.slice(0, 5);
        }

        const previewBox = document.getElementById('edit-image-preview-box');
        const previewImg = document.getElementById('edit-image-preview');
        const thumbsGrid = document.getElementById('edit-preview-thumbnails-grid');
        const summary = document.getElementById('edit-preview-summary');

        if (previewBox) previewBox.style.display = 'block';

        // Automatically treat the very first new image as main thumbnail photo
        const reader0 = new FileReader();
        reader0.onload = function(e) {
            if (previewImg) previewImg.src = e.target.result;
        };
        reader0.readAsDataURL(files[0]);

        if (thumbsGrid) thumbsGrid.innerHTML = '';
        if (summary) {
            summary.textContent = files.length === 1 
                ? '1 new photo selected' 
                : files.length + ' new photos selected';
        }

        files.forEach(function(file, index) {
            const r = new FileReader();
            r.onload = function(e) {
                const thumbItem = document.createElement('div');
                thumbItem.className = 'preview-thumb-card' + (index === 0 ? ' is-active' : '');
                thumbItem.title = 'Click to preview photo ' + (index + 1);

                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'New photo ' + (index + 1);
                thumbItem.appendChild(img);

                thumbItem.onclick = function(ev) {
                    ev.stopPropagation();
                    if (previewImg) previewImg.src = e.target.result;
                    if (thumbsGrid) {
                        thumbsGrid.querySelectorAll('.preview-thumb-card').forEach(function(c) {
                            c.classList.remove('is-active');
                        });
                    }
                    thumbItem.classList.add('is-active');
                };

                if (thumbsGrid) thumbsGrid.appendChild(thumbItem);
            };
            r.readAsDataURL(file);
        });

        checkEditPostChanges();
    }

    function clearEditImages(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        const input = document.getElementById('edit_pet_image');
        if (input) input.value = '';

        // Restore original pet photo preview
        if (currentEditingPet) {
            renderEditPetExistingPhotos(currentEditingPet);
        }
        checkEditPostChanges();
    }

    // Close Confirmation
    function createPostHasInput() {
        return checkCreatePostIsDirty();
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
        createPostIsProgrammaticReset = true;
        const form = document.querySelector('#create-post-modal .create-post-form');
        if (form) form.reset();
        clearCreateImages();
        closeCreatePostModal();
        const nameInput = document.getElementById('name');
        if (nameInput) nameInput.dispatchEvent(new Event('input'));
        const descInput = document.getElementById('description');
        if (descInput) descInput.dispatchEvent(new Event('input'));
        createPostIsTouched = false;
        createPostInitialSnapshot = Object.assign({}, CREATE_POST_DEFAULTS);
        createPostIsProgrammaticReset = false;
        updateCreateSubmitButtonState();
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
    let editFormInitialSnapshot = null;

    function renderEditPetExistingPhotos(pet) {
        const previewBox = document.getElementById('edit-image-preview-box');
        const previewImg = document.getElementById('edit-image-preview');
        const thumbsGrid = document.getElementById('edit-preview-thumbnails-grid');
        const summary = document.getElementById('edit-preview-summary');

        let urls = [];
        if (pet.image_urls && Array.isArray(pet.image_urls) && pet.image_urls.length > 0) {
            urls = pet.image_urls;
        } else if (pet.image_url) {
            urls = [pet.image_url];
        }

        if (urls.length > 0) {
            if (previewImg) previewImg.src = urls[0];
            if (thumbsGrid) {
                thumbsGrid.innerHTML = '';
                if (urls.length > 1) {
                    urls.forEach(function(url, idx) {
                        const thumbItem = document.createElement('div');
                        thumbItem.className = 'preview-thumb-card' + (idx === 0 ? ' is-active' : '');
                        thumbItem.title = 'Click to preview photo ' + (idx + 1);

                        const img = document.createElement('img');
                        img.src = url;
                        img.alt = 'Existing photo ' + (idx + 1);
                        thumbItem.appendChild(img);

                        thumbItem.onclick = function(ev) {
                            ev.stopPropagation();
                            if (previewImg) previewImg.src = url;
                            thumbsGrid.querySelectorAll('.preview-thumb-card').forEach(function(c) {
                                c.classList.remove('is-active');
                            });
                            thumbItem.classList.add('is-active');
                        };

                        thumbsGrid.appendChild(thumbItem);
                    });
                    thumbsGrid.style.display = 'flex';
                } else {
                    thumbsGrid.style.display = 'none';
                }
            }
            if (summary) {
                summary.textContent = urls.length === 1 
                    ? '1 existing photo' 
                    : urls.length + ' existing photos';
            }
            if (previewBox) previewBox.style.display = 'block';
        } else {
            if (previewBox) previewBox.style.display = 'none';
            if (thumbsGrid) thumbsGrid.innerHTML = '';
        }
    }

    function checkEditPostChanges() {
        const submitBtn = document.getElementById('edit-post-submit-btn');
        if (!submitBtn || !editFormInitialSnapshot) return;

        const fileInput = document.getElementById('edit_pet_image');
        const hasNewImage = Boolean(fileInput && fileInput.files && fileInput.files.length > 0);

        const hasChanged = hasNewImage ||
            (document.getElementById('edit_name')?.value || '').trim() !== editFormInitialSnapshot.name ||
            (document.getElementById('edit_species')?.value || 'Dog') !== editFormInitialSnapshot.species ||
            (document.getElementById('edit_breed')?.value || '').trim() !== editFormInitialSnapshot.breed ||
            (document.getElementById('edit_age')?.value || '') !== editFormInitialSnapshot.age ||
            (document.getElementById('edit_gender')?.value || 'Male') !== editFormInitialSnapshot.gender ||
            (document.getElementById('edit_status')?.value || 'Available') !== editFormInitialSnapshot.status ||
            (document.getElementById('edit_location')?.value || '').trim() !== editFormInitialSnapshot.location ||
            (document.getElementById('edit_vaccination_status')?.value || '') !== editFormInitialSnapshot.vaccination_status ||
            (document.getElementById('edit_deworming_status')?.value || '') !== editFormInitialSnapshot.deworming_status ||
            (document.getElementById('edit_special_health_notes')?.value || '').trim() !== editFormInitialSnapshot.special_health_notes ||
            (document.getElementById('edit_description')?.value || '').trim() !== editFormInitialSnapshot.description ||
            Boolean(document.getElementById('edit_is_spayed_neutered')?.checked) !== editFormInitialSnapshot.is_spayed_neutered;

        submitBtn.disabled = !hasChanged;
        if (hasChanged) {
            submitBtn.classList.remove('is-disabled');
        } else {
            submitBtn.classList.add('is-disabled');
        }
    }

    function openEditPostModal(pet) {
        currentEditingPet = pet;
        document.getElementById('edit-post-form').action = '/posts/' + pet.id;

        document.getElementById('edit_name').value = pet.name || '';
        document.getElementById('edit_species').value = pet.species || 'Dog';
        document.getElementById('edit_breed').value = pet.breed || '';

        // Set age on the dropdown; fallback: add a temporary option if not in the list
        const editAgeSelect = document.getElementById('edit_age');
        if (editAgeSelect) {
            const ageVal = pet.age || '';
            let ageMatched = false;
            for (let i = 0; i < editAgeSelect.options.length; i++) {
                if (editAgeSelect.options[i].value === ageVal) {
                    editAgeSelect.selectedIndex = i;
                    ageMatched = true;
                    break;
                }
            }
            if (!ageMatched && ageVal) {
                // Legacy / custom age string: add a temporary option so the form still submits the value
                const tmpOpt = document.createElement('option');
                tmpOpt.value = ageVal;
                tmpOpt.textContent = ageVal + ' (current)';
                tmpOpt.selected = true;
                editAgeSelect.insertBefore(tmpOpt, editAgeSelect.options[0]);
            }
        }

        document.getElementById('edit_location').value = pet.location || '';
        document.getElementById('edit_gender').value = pet.gender || 'Male';
        document.getElementById('edit_status').value = pet.status || 'Available';

        // Set vaccination_status on dropdown
        const vacSelect = document.getElementById('edit_vaccination_status');
        if (vacSelect) {
            const targetVal = pet.vaccination_status || 'Not vaccinated yet';
            let matched = false;
            for (let i = 0; i < vacSelect.options.length; i++) {
                if (vacSelect.options[i].value.toLowerCase() === targetVal.toLowerCase()) {
                    vacSelect.selectedIndex = i;
                    matched = true;
                    break;
                }
            }
            if (!matched && targetVal) {
                const opt = document.createElement('option');
                opt.value = targetVal;
                opt.textContent = targetVal;
                opt.selected = true;
                vacSelect.appendChild(opt);
            }
        }

        // Set deworming_status on dropdown
        const dewSelect = document.getElementById('edit_deworming_status');
        if (dewSelect) {
            const targetVal = pet.deworming_status || 'Not dewormed';
            let matched = false;
            for (let i = 0; i < dewSelect.options.length; i++) {
                if (dewSelect.options[i].value.toLowerCase() === targetVal.toLowerCase()) {
                    dewSelect.selectedIndex = i;
                    matched = true;
                    break;
                }
            }
            if (!matched && targetVal) {
                const opt = document.createElement('option');
                opt.value = targetVal;
                opt.textContent = targetVal;
                opt.selected = true;
                dewSelect.appendChild(opt);
            }
        }

        document.getElementById('edit_special_health_notes').value = pet.special_health_notes || '';
        document.getElementById('edit_description').value = pet.description || '';
        document.getElementById('edit_is_spayed_neutered').checked = Boolean(pet.is_spayed_neutered);

        // Trigger input event to refresh live character counters
        const editNameEl = document.getElementById('edit_name');
        if (editNameEl) editNameEl.dispatchEvent(new Event('input'));
        const editDescEl = document.getElementById('edit_description');
        if (editDescEl) editDescEl.dispatchEvent(new Event('input'));

        const deletePostBtn = document.getElementById('edit-delete-post-btn');
        const deletePostText = document.getElementById('edit-delete-post-text');
        if (deletePostBtn) {
            deletePostBtn.onclick = function() {
                closeEditPostModal();
                confirmDeletePost(pet.id, pet.name || 'Pet');
            };
        }

        const fileInput = document.getElementById('edit_pet_image');
        if (fileInput) fileInput.value = '';

        renderEditPetExistingPhotos(pet);

        // Snapshot initial state to conditionally activate Save Changes button
        editFormInitialSnapshot = {
            name: (pet.name || '').trim(),
            species: pet.species || 'Dog',
            breed: (pet.breed || '').trim(),
            age: pet.age || '',
            gender: pet.gender || 'Male',
            status: pet.status || 'Available',
            location: (pet.location || '').trim(),
            vaccination_status: pet.vaccination_status || '',
            deworming_status: pet.deworming_status || '',
            special_health_notes: (pet.special_health_notes || '').trim(),
            description: (pet.description || '').trim(),
            is_spayed_neutered: Boolean(pet.is_spayed_neutered)
        };

        checkEditPostChanges();

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
        // Active posts are archived (soft-delete) rather than hard-deleted via the UI
        confirmArchivePost(currentEditingPet.id, currentEditingPet.name || 'Pet');
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

    // Mark as Adopted / Revert to Available — Toggle Confirmation Functions
    let pendingAdoptPetId = null;
    let pendingAdoptIsRevert = false;

    function confirmToggleAdopted(petId, petName, isCurrentlyAdopted) {
        pendingAdoptPetId   = petId;
        pendingAdoptIsRevert = isCurrentlyAdopted;

        // Update pet name in both directions of the description
        const nameEl = document.getElementById('adopt-pet-name');
        if (nameEl) nameEl.textContent = '"' + petName + '"';

        const titleEl   = document.getElementById('confirm-adopt-title');
        const descEl    = document.getElementById('confirm-adopt-desc');
        const labelEl   = document.getElementById('adopt-modal-confirm-label');
        const confirmBtn = document.getElementById('adopt-modal-confirm-btn');
        const iconWrap  = document.getElementById('adopt-modal-icon-wrap');
        const iconAdopt  = document.getElementById('adopt-modal-icon-adopt');
        const iconRevert = document.getElementById('adopt-modal-icon-revert');

        if (isCurrentlyAdopted) {
            // Revert direction: Adopted → Available
            if (titleEl)  titleEl.textContent = 'Mark as Available?';
            if (descEl)   descEl.innerHTML    = 'Are you sure you want to revert <strong style="color: #4A2E2B;">"' + petName + '"</strong> back to <strong style="color: #4A2E2B;">Available</strong>? The post will reappear on the public feed.';
            if (labelEl)  labelEl.textContent = 'Yes, Mark as Available';
            if (confirmBtn) {
                confirmBtn.style.background = '#4A2E2B';
                confirmBtn.onmouseover = function() { this.style.background = '#382220'; };
                confirmBtn.onmouseout  = function() { this.style.background = '#4A2E2B'; };
            }
            if (iconWrap)  iconWrap.style.background = '#EADDCF';
            if (iconAdopt)  iconAdopt.style.display  = 'none';
            if (iconRevert) iconRevert.style.display = 'flex';
        } else {
            // Forward direction: → Adopted
            if (titleEl)  titleEl.textContent = 'Mark as Adopted?';
            if (descEl)   descEl.innerHTML    = 'Are you sure you want to mark <strong style="color: #4A2E2B;">"' + petName + '"</strong> as adopted? This will update the pet\'s status and close new adoption applications.';
            if (labelEl)  labelEl.textContent = 'Yes, Mark as Adopted';
            if (confirmBtn) {
                confirmBtn.style.background = '#059669';
                confirmBtn.onmouseover = function() { this.style.background = '#047857'; };
                confirmBtn.onmouseout  = function() { this.style.background = '#059669'; };
            }
            if (iconWrap)  iconWrap.style.background = '#D1FAE5';
            if (iconAdopt)  iconAdopt.style.display  = 'flex';
            if (iconRevert) iconRevert.style.display = 'none';
        }

        const modal = document.getElementById('confirm-adopt-modal');
        if (modal) modal.style.display = 'flex';
    }

    function hideConfirmAdopt() {
        const modal = document.getElementById('confirm-adopt-modal');
        if (modal) modal.style.display = 'none';
        pendingAdoptPetId   = null;
        pendingAdoptIsRevert = false;
    }

    function submitMarkAdopted() {
        if (!pendingAdoptPetId) return;
        const form = document.getElementById('adopt-form-' + pendingAdoptPetId);
        if (form) {
            const loaderMsg = pendingAdoptIsRevert ? 'Reverting status...' : 'Marking as adopted...';
            if (window.showKupLoader) window.showKupLoader(loaderMsg);
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

    // Live Character Counter Function
    function setupCharCounter(inputId, counterId, maxLen) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);
        if (!input || !counter) return;

        function update() {
            const len = (input.value || '').length;
            counter.textContent = len + '/' + maxLen;
            if (len >= maxLen) {
                counter.classList.add('at-limit');
                counter.classList.remove('near-limit');
            } else if (len >= Math.floor(maxLen * 0.85)) {
                counter.classList.add('near-limit');
                counter.classList.remove('at-limit');
            } else {
                counter.classList.remove('near-limit', 'at-limit');
            }
        }

        input.addEventListener('input', update);
        input.addEventListener('change', update);
        update();
    }

    // Fullscreen Photo Carousel Viewer for My Posts
    let activeCarouselPet = null;
    let activeCarouselGallery = [];
    let activeCarouselIndex = 0;

    function openFullscreenCarouselForPet(pet, startIndex = 0) {
        if (!pet) return;
        activeCarouselPet = pet;
        activeCarouselGallery = (pet.image_urls && pet.image_urls.length > 0)
            ? pet.image_urls
            : (pet.image_url ? [pet.image_url] : []);
        activeCarouselIndex = (startIndex >= 0 && startIndex < activeCarouselGallery.length) ? startIndex : 0;

        if (activeCarouselGallery.length === 0) return;

        const fsModal = document.getElementById('pet-fullscreen-image-modal');
        const fsPetName = document.getElementById('fs-carousel-pet-name');
        if (fsPetName) {
            fsPetName.textContent = pet.name || 'Pet Photo';
        }

        renderFullscreenCarouselSlide();

        if (fsModal) {
            fsModal.style.display = 'flex';
        }
    }

    function renderFullscreenCarouselSlide() {
        if (!activeCarouselGallery || activeCarouselGallery.length === 0) return;
        const currentSrc = activeCarouselGallery[activeCarouselIndex];
        const fsImg = document.getElementById('pet-fullscreen-img');
        const fsCounter = document.getElementById('fs-carousel-counter');
        const prevBtn = document.getElementById('fs-carousel-prev-btn');
        const nextBtn = document.getElementById('fs-carousel-next-btn');
        const thumbsStrip = document.getElementById('fs-carousel-thumbnails-strip');
        const total = activeCarouselGallery.length;

        if (fsImg && currentSrc) {
            fsImg.classList.remove('fade-in');
            void fsImg.offsetWidth;
            fsImg.src = currentSrc;
            fsImg.alt = (activeCarouselPet ? activeCarouselPet.name : 'Pet') + ' - Photo ' + (activeCarouselIndex + 1);
            fsImg.classList.add('fade-in');
        }

        if (fsCounter) {
            fsCounter.textContent = (activeCarouselIndex + 1) + ' / ' + total;
            fsCounter.style.display = total > 1 ? 'inline-block' : 'inline-block';
        }

        if (prevBtn) prevBtn.style.display = total > 1 ? 'flex' : 'none';
        if (nextBtn) nextBtn.style.display = total > 1 ? 'flex' : 'none';

        if (thumbsStrip) {
            if (total > 1) {
                thumbsStrip.innerHTML = '';
                activeCarouselGallery.forEach(function(url, idx) {
                    const thumbBtn = document.createElement('button');
                    thumbBtn.type = 'button';
                    thumbBtn.className = 'fs-thumb-pill' + (idx === activeCarouselIndex ? ' is-active' : '');
                    thumbBtn.setAttribute('aria-label', 'View photo ' + (idx + 1));
                    thumbBtn.onclick = function(e) {
                        e.stopPropagation();
                        setFullscreenCarouselSlide(idx);
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
    }

    function cycleFullscreenCarousel(direction) {
        if (!activeCarouselGallery || activeCarouselGallery.length <= 1) return;
        activeCarouselIndex = (activeCarouselIndex + direction + activeCarouselGallery.length) % activeCarouselGallery.length;
        renderFullscreenCarouselSlide();
    }

    function setFullscreenCarouselSlide(index) {
        if (!activeCarouselGallery || !activeCarouselGallery[index]) return;
        activeCarouselIndex = index;
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

    document.addEventListener('DOMContentLoaded', function() {
        setupCharCounter('name', 'create-name-counter', 30);
        setupCharCounter('description', 'create-description-counter', 300);
        setupCharCounter('edit_name', 'edit-name-counter', 30);
        setupCharCounter('edit_description', 'edit-description-counter', 300);

        // Conditional submit button activation listener for Create Post
        const createPostForm = document.querySelector('#create-post-modal .create-post-form');
        if (createPostForm) {
            const handleCreatePostInteraction = function(e) {
                if (createPostIsProgrammaticReset) return;
                createPostIsTouched = true;
                updateCreateSubmitButtonState();
            };
            createPostForm.addEventListener('input', handleCreatePostInteraction);
            createPostForm.addEventListener('change', handleCreatePostInteraction);
        }

        // Drag & drop support for upload dropzone
        const dropZone = document.getElementById('create-upload-zone');
        if (dropZone) {
            ['dragenter', 'dragover'].forEach(function(evt) {
                dropZone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.add('is-dragover');
                });
            });
            ['dragleave', 'drop'].forEach(function(evt) {
                dropZone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.remove('is-dragover');
                });
            });
            dropZone.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length > 0) {
                    const input = document.getElementById('pet_images');
                    if (input) {
                        input.files = dt.files;
                        createPostIsTouched = true;
                        previewPetImages({ target: input });
                    }
                }
            });
        }

        updateCreateSubmitButtonState();

        // Conditional submit button activation listener for Edit Post
        const editPostForm = document.getElementById('edit-post-form');
        if (editPostForm) {
            editPostForm.addEventListener('input', checkEditPostChanges);
            editPostForm.addEventListener('change', checkEditPostChanges);
        }
    });

    // Auto-open modal if validation errors exist
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openCreatePostModal();
        });
    @endif

    // Escape key & arrow keys handling
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
            const confirmAdoptModal = document.getElementById('confirm-adopt-modal');
            const confirmDeletePostModal = document.getElementById('confirm-delete-post-modal');
            const confirmCancelModal = document.getElementById('confirm-cancel-request-modal');
            const confirmDeclineModal = document.getElementById('confirm-decline-modal');
            const confirmDeleteAppModal = document.getElementById('confirm-delete-application-modal');
            const confirmArchiveModal = document.getElementById('confirm-archive-modal');
            const confirmPermDeleteModal = document.getElementById('confirm-permanent-delete-modal');
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
            } else if (confirmArchiveModal && confirmArchiveModal.style.display === 'flex') {
                hideConfirmArchive();
            } else if (confirmPermDeleteModal && confirmPermDeleteModal.style.display === 'flex') {
                hideConfirmPermanentDelete();
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

{{-- Fullscreen Pet Image Carousel Viewer Modal (My Posts) --}}
<div 
    id="pet-fullscreen-image-modal" 
    class="fullscreen-carousel-backdrop" 
    style="display: none;" 
    role="dialog" 
    aria-modal="true" 
    aria-label="Full screen pet image carousel"
    onclick="handleFullscreenBackdropClick(event)"
>
    {{-- Header Bar --}}
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

        <div class="fs-carousel-slide-box">
            <img id="pet-fullscreen-img" src="" alt="Full screen pet photo" class="fullscreen-img fs-carousel-img" loading="lazy">
        </div>

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
            <span class="fullscreen-hint">Use <strong>&larr; &rarr;</strong> arrow keys to browse &bull; Press <strong>ESC</strong> to exit</span>
        </div>
    </div>
</div>
@endauth
@endsection
