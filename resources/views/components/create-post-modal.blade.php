@auth
{{-- ==========================================================================
     CREATE POST MODAL POPUP — Post a Rescued Animal for Adoption
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
                            <img id="image-preview" src="" alt="Photo Preview">
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
                    <span id="create-name-counter" class="char-counter" style="display: none;"></span>
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
                <button 
                    type="submit" 
                    class="btn-submit-post is-disabled" 
                    id="create-post-submit-btn" 
                    disabled 
                    style="display: none;"
                    title="Post for Adoption / Save Changes"
                >
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

<script>
    // Create Post Modal Controls
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

    if (typeof window.openCreatePostModal !== 'function') {
        window.openCreatePostModal = function() {
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

                if (typeof window.updateCreateSubmitButtonState === 'function') {
                    window.updateCreateSubmitButtonState();
                }
            }
        };
    }

    if (typeof window.closeCreatePostModal !== 'function') {
        window.closeCreatePostModal = function() {
            const modal = document.getElementById('create-post-modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
            createPostIsTouched = false;
            if (typeof window.updateCreateSubmitButtonState === 'function') {
                window.updateCreateSubmitButtonState();
            }
        };
    }

    if (typeof window.handleCreateBackdropClick !== 'function') {
        window.handleCreateBackdropClick = function(event) {
            if (event.target.id === 'create-post-modal') {
                confirmCloseCreatePost();
            }
        };
    }

    if (typeof window.triggerCreateImageUpload !== 'function') {
        window.triggerCreateImageUpload = function(event) {
            if (event && event.target && event.target.closest('.btn-clear-photos')) return;
            const input = document.getElementById('pet_images');
            if (input) input.click();
        };
    }

    if (typeof window.previewPetImages !== 'function') {
        window.previewPetImages = function(event) {
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

            // Very first image is automatically treated as main thumbnail photo by default
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
        };
    }

    if (typeof window.clearCreateImages !== 'function') {
        window.clearCreateImages = function(event) {
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
        };
    }

    // Dynamic Visibility / Activation of Submit Button
    if (typeof window.updateCreateSubmitButtonState !== 'function') {
        window.updateCreateSubmitButtonState = function() {
            const submitBtn = document.getElementById('create-post-submit-btn');
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
        };
    }

    if (typeof window.createPostHasInput !== 'function') {
        window.createPostHasInput = function() {
            return checkCreatePostIsDirty();
        };
    }

    if (typeof window.confirmCloseCreatePost !== 'function') {
        window.confirmCloseCreatePost = function() {
            if (!createPostHasInput()) {
                discardCreatePost();
                return;
            }
            showConfirmCloseCreate();
        };
    }

    if (typeof window.showConfirmCloseCreate !== 'function') {
        window.showConfirmCloseCreate = function() {
            const dialog = document.getElementById('confirm-close-create-modal');
            if (dialog) dialog.style.display = 'flex';
        };
    }

    if (typeof window.hideConfirmCloseCreate !== 'function') {
        window.hideConfirmCloseCreate = function() {
            const dialog = document.getElementById('confirm-close-create-modal');
            if (dialog) dialog.style.display = 'none';
        };
    }

    if (typeof window.discardCreatePost !== 'function') {
        window.discardCreatePost = function() {
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
            if (typeof window.updateCreateSubmitButtonState === 'function') {
                window.updateCreateSubmitButtonState();
            }
        };
    }

    if (typeof window.saveCreatePostAsDraft !== 'function') {
        window.saveCreatePostAsDraft = function() {
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
        };
    }

    // Initialize character counters, form listeners & escape handling
    document.addEventListener('DOMContentLoaded', function() {
        const setupModalCharCounter = function(inputId, counterId, max) {
            const input = document.getElementById(inputId);
            const counter = document.getElementById(counterId);
            if (!input || !counter) return;

            const update = function() {
                const len = input.value.length;
                counter.textContent = len + '/' + max;
                if (len >= max) {
                    counter.classList.add('at-limit');
                    counter.classList.remove('near-limit');
                } else if (len >= max * 0.9) {
                    counter.classList.add('near-limit');
                    counter.classList.remove('at-limit');
                } else {
                    counter.classList.remove('near-limit', 'at-limit');
                }
            };

            input.addEventListener('input', update);
            input.addEventListener('change', update);
            update();
        };

        setupModalCharCounter('name', 'create-name-counter', 30);
        setupModalCharCounter('description', 'create-description-counter', 300);

        // Conditional submit button activation listener
        const createPostForm = document.querySelector('#create-post-modal .create-post-form');
        if (createPostForm) {
            const handleFormInteraction = function(e) {
                if (createPostIsProgrammaticReset) return;
                createPostIsTouched = true;
                updateCreateSubmitButtonState();
            };
            createPostForm.addEventListener('input', handleFormInteraction);
            createPostForm.addEventListener('change', handleFormInteraction);
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

        @if($errors->hasAny(['name', 'species', 'breed', 'age', 'gender', 'location', 'vaccination_status', 'deworming_status', 'special_health_notes', 'description', 'pet_images', 'pet_images.*', 'pet_image']))
            openCreatePostModal();
        @endif
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const createModal = document.getElementById('create-post-modal');
            const confirmCloseModal = document.getElementById('confirm-close-create-modal');
            if (confirmCloseModal && confirmCloseModal.style.display === 'flex') {
                hideConfirmCloseCreate();
                return;
            }
            if (createModal && createModal.style.display === 'flex') {
                confirmCloseCreatePost();
            }
        }
    });
</script>
@endauth
