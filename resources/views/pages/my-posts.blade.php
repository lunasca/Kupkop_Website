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
        <div class="header-cta">
            <button type="button" class="btn-create-post" onclick="openCreatePostModal()">
                <span>+</span> Create New Post
            </button>
        </div>
    </div>

    {{-- SECTION 1: My Active Animal Posts Table --}}
    <div class="dashboard-section">
        <div class="section-title-bar">
            <h2>🐾 My Active Animal Posts ({{ $myPets->count() }})</h2>
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
                                            <span class="text-muted">{{ $pet->age }} • {{ $pet->gender }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $stClass = strtolower($pet->status) === 'available' 
                                                ? 'status-pill-available' 
                                                : (strtolower($pet->status) === 'pending' ? 'status-pill-pending' : 'status-pill-adopted');
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
                                                onclick="alert('Editing {{ $pet->name }} is ready for your next feature expansion!')"
                                            >
                                                Edit Post
                                            </button>

                                            @if(strtolower($pet->status) !== 'adopted')
                                                <form method="POST" action="{{ route('posts.adopt', $pet->id) }}" class="inline-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn-table-adopt" onclick="return confirm('Mark {{ $pet->name }} as adopted?')">
                                                        Mark as Adopted
                                                    </button>
                                                </form>
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
                    <span class="empty-icon">🐶</span>
                    <h3>No animals posted yet</h3>
                    <p class="text-muted">You haven't listed any pets for adoption yet. Click '+ Create New Post' to get started.</p>
                    <button type="button" class="btn-create-post mt-3" onclick="openCreatePostModal()">
                        <span>+</span> Create New Post
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- SECTION 2: Incoming Adoption Requests Table --}}
    <div class="dashboard-section mt-5">
        <div class="section-title-bar">
            <h2>📋 Incoming Adoption Requests ({{ $incomingRequests->count() }})</h2>
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
                                <th>Contact</th>
                                <th>Barangay</th>
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
                                            🐾 {{ $req->pet->name ?? 'Pet' }}
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        {{ $req->created_at->format('M d, Y') }}
                                    </td>
                                    <td>
                                        <span>{{ $req->contact_number }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $req->barangay }}</span>
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
                                                    <button type="submit" class="btn-table-accept">Accept</button>
                                                </form>

                                                <form method="POST" action="{{ route('requests.status', $req->id) }}" class="inline-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="Declined">
                                                    <button type="submit" class="btn-table-decline">Decline</button>
                                                </form>
                                            @endif

                                            <button 
                                                type="button" 
                                                class="btn-table-message"
                                                onclick="alert('Messaging with {{ $req->applicant_name }} will open in your next Messages module update!')"
                                            >
                                                Message
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
                    <span class="empty-icon">📬</span>
                    <h3>No incoming adoption requests at the moment</h3>
                    <p class="text-muted">When interested neighbors submit adoption applications for your posted pets, they will appear right here for your review.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ==========================================================================
     CREATE POST MODAL POPUP
   ========================================================================== --}}
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
            onclick="closeCreatePostModal()" 
            aria-label="Close modal"
        >
            &times;
        </button>

        {{-- Modal Header --}}
        <div class="create-modal-header">
            <h2 id="create-modal-title" class="create-modal-title">
                Post a Rescued Animal for Adoption
            </h2>
            <p class="create-modal-subtitle">
                Fill out the animal profile below. Once submitted, your post will be visible to neighbors in your barangay.
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
                <label class="form-label font-bold">1. Animal Photo</label>
                <div class="upload-dropzone" onclick="document.getElementById('pet_image').click()">
                    <span class="upload-icon">📷</span>
                    <p class="upload-title">Click to upload pet photo</p>
                    <p class="upload-hint">Select JPEG or PNG photo (Max 5MB)</p>
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
                            <span>♂️ Male</span>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="gender" value="Female" {{ old('gender') === 'Female' ? 'checked' : '' }}>
                            <span>♀️ Female</span>
                        </label>
                    </div>
                    @error('gender')
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
                            placeholder="e.g. Dewormed 2x, Pending" 
                            class="form-control"
                            value="{{ old('deworming_status') }}"
                        >
                    </div>
                </div>

                {{-- Spayed / Neutered Checkbox --}}
                <div class="form-group-checkbox mt-3">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_spayed_neutered" value="1" {{ old('is_spayed_neutered') ? 'checked' : '' }}>
                        <span>✂️ Animal is Spayed / Neutered (Kapon)</span>
                    </label>
                </div>

                {{-- Special Health Notes --}}
                <div class="form-group mt-3">
                    <label for="special_health_notes" class="form-label">Special Health Notes</label>
                    <input 
                        type="text" 
                        id="special_health_notes" 
                        name="special_health_notes" 
                        placeholder="e.g. Sensitive to dairy, fully healed leg injury, very energetic" 
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
                    placeholder="Where was this animal found? What is their personality like with kids, other pets, or strangers? What kind of home are they looking for?"
                    class="form-control textarea-field"
                >{{ old('description') }}</textarea>
                @error('description')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Form Footer Actions --}}
            <div class="create-modal-footer">
                <button type="submit" name="action" value="draft" class="btn-draft">
                    Save Draft
                </button>
                <button type="submit" name="action" value="publish" class="btn-submit-post">
                    🐾 Post Animal for Adoption
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Scripts for My Posts Modal --}}
<script>
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
            closeCreatePostModal();
        }
    }

    function previewPetImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('preview-filename').textContent = input.files[0].name;
                document.getElementById('image-preview-box').style.display = 'flex';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Auto-open modal if validation errors exist
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openCreatePostModal();
        });
    @endif
</script>
@endsection
