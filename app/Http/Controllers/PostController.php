<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\AdoptionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display the "My Posts" dashboard: listed pets & incoming adoption requests.
     * Guests see a clean auth-prompt placeholder instead.
     */
    public function index(): View
    {
        // Guests see the placeholder — no DB queries needed
        if (!Auth::check()) {
            return view('pages.my-posts', [
                'myPets' => collect(), 
                'incomingRequests' => collect(),
                'myApplications' => collect(),
                'archivedPets' => collect()
            ]);
        }

        // Administrators are restricted from regular user My Posts page
        if (Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard')
                ->with('warning', 'Access to My Posts is restricted for Admin accounts. Please use the Admin Panel.');
        }

        $userId = Auth::id();

        // Stamp the "last viewed" time so the navbar badge clears after visiting
        Auth::user()->update(['requests_last_viewed_at' => now()]);

        $petColumns = [
            'id', 'user_id', 'name', 'species', 'breed', 'age', 'gender',
            'vaccination_status', 'deworming_status', 'is_spayed_neutered',
            'special_health_notes', 'description', 'image_path', 'status',
            'location', 'rescuer_id', 'posted_by', 'is_verified', 'created_at', 'archived_at'
        ];

        // Only show published/active posts under "My Active Animal Posts" (exclude Drafts & Archived)
        $myPets = Pet::select($petColumns)
            ->with(['user:id,name,is_verified,status'])
            ->where('user_id', $userId)
            ->where('status', '!=', 'Draft')
            ->whereNull('archived_at')
            ->latest()
            ->get();

        // Draft posts kept separately for the dedicated Drafts section
        $draftPets = Pet::select($petColumns)
            ->with(['user:id,name,is_verified,status'])
            ->where('user_id', $userId)
            ->where('status', 'Draft')
            ->whereNull('archived_at')
            ->latest()
            ->get();

        // Fetch incoming adoption requests for active pets (show ONLY Pending)
        $petIds = $myPets->pluck('id');
        $incomingRequests = AdoptionRequest::select([
                'id', 'pet_id', 'user_id', 'applicant_name', 'contact_number',
                'email', 'barangay', 'message', 'status', 'created_at'
            ])
            ->with([
                'pet:id,name,species,breed,age,gender,image_path,user_id,rescuer_id,posted_by',
                'pet.user:id,name,is_verified',
                'user:id,name,email'
            ])
            ->whereIn('pet_id', $petIds)
            ->where('status', 'Pending')
            ->latest()
            ->get();

        // Fetch submitted adoption applications made by this user
        $myApplications = AdoptionRequest::select([
                'id', 'pet_id', 'user_id', 'applicant_name', 'contact_number',
                'email', 'barangay', 'message', 'status', 'created_at'
            ])
            ->with([
                'pet:id,name,species,breed,age,gender,image_path,user_id,rescuer_id,posted_by',
                'pet.user:id,name,is_verified',
                'user:id,name,email'
            ])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        // Archived animal posts
        $archivedPets = Pet::select($petColumns)
            ->with(['user:id,name,is_verified,status'])
            ->where('user_id', $userId)
            ->whereNotNull('archived_at')
            ->latest('archived_at')
            ->get();

        return view('pages.my-posts', compact('myPets', 'draftPets', 'incomingRequests', 'myApplications', 'archivedPets'));
    }

    /**
     * Store a newly created pet post in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'You must be logged in to post an animal for adoption.');
        }

        if (Auth::user()->status === 'blocked' || Auth::user()->is_blocked || Auth::user()->archived_at !== null) {
            abort(403, 'Your account has been blocked or removed.');
        }

        $isDraft = ($request->input('action') === 'draft');

        $rules = [
            'name'                 => [$isDraft ? 'nullable' : 'required', 'string', 'max:30'],
            'species'              => [$isDraft ? 'nullable' : 'required', 'string', 'in:Dog,Cat,Other'],
            'breed'                => ['nullable', 'string', 'max:255'],
            'age'                  => [
                $isDraft ? 'nullable' : 'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($isDraft) {
                    if (!$isDraft && !empty($value) && !\App\Models\Pet::isRealisticAge($value)) {
                        $fail('Please enter a realistic age for the pet (maximum 20 years).');
                    }
                },
            ],
            'gender'               => [$isDraft ? 'nullable' : 'required', 'string', 'in:Male,Female'],
            'location'             => [$isDraft ? 'nullable' : 'required', 'string', 'max:255'],
            'vaccination_status'   => ['nullable', 'string', 'max:255'],
            'deworming_status'     => ['nullable', 'string', 'max:255'],
            'is_spayed_neutered'   => ['nullable', 'boolean'],
            'special_health_notes' => ['nullable', 'string'],
            'description'          => [$isDraft ? 'nullable' : 'required', 'string', 'max:300'],
            'pet_images'           => 'nullable|array|max:5',
            'pet_images.*'         => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'photos'               => 'nullable|array|max:5',
            'photos.*'             => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'images'               => 'nullable|array|max:5',
            'images.*'             => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'pet_image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'action'               => ['nullable', 'string'], // Post or Draft
        ];

        $messages = [
            'name.max'            => 'The animal name cannot exceed 30 characters.',
            'description.max'     => 'The description cannot exceed 300 characters.',
            'pet_images.required' => 'Please upload at least one main photo of the pet.',
            'pet_images.max'      => 'You may upload a maximum of 5 photos per post.',
            'pet_images.*.image'  => 'Each file must be a valid image.',
            'pet_images.*.mimes'  => 'Only PNG, JPG, JPEG, and WEBP images are accepted.',
            'pet_images.*.max'    => 'Each image must not exceed 5MB.',
        ];

        $validated = $request->validate($rules, $messages);

        if (!$isDraft) {
            $hasAnyImage = $request->hasFile('pet_images') 
                || $request->hasFile('photos') 
                || $request->hasFile('images') 
                || $request->hasFile('pet_image');
            if (!$hasAnyImage) {
                return back()->withErrors(['pet_images' => 'Please upload at least one main photo of the pet.'])->withInput();
            }
        }

        $imagePath = null;
        $uploadedFiles = [];
        foreach (['pet_images', 'photos', 'images', 'pet_image'] as $inputKey) {
            if ($request->hasFile($inputKey)) {
                $fileOrFiles = $request->file($inputKey);
                if (is_array($fileOrFiles)) {
                    foreach ($fileOrFiles as $f) {
                        if ($f && $f->isValid()) {
                            $uploadedFiles[] = $f;
                        }
                    }
                } elseif ($fileOrFiles && $fileOrFiles->isValid()) {
                    $uploadedFiles[] = $fileOrFiles;
                }
            }
        }

        if (!empty($uploadedFiles)) {
            $paths = [];
            foreach (array_slice($uploadedFiles, 0, 5) as $file) {
                $paths[] = $this->uploadPetImageToSupabase($file);
            }
            if (!empty($paths)) {
                $imagePath = json_encode(array_values($paths));
            }
        }

        $status = $isDraft ? 'Draft' : 'Available';

        $rescuerId = '#RES-' . str_pad((string) rand(100, 999), 4, '0', STR_PAD_LEFT);
        $postedBy = Auth::user()->name;
        $species = $validated['species'] ?? 'Dog';

        $pet = Pet::create([
            'user_id'              => Auth::id(),
            'name'                 => !empty($validated['name']) ? $validated['name'] : 'Untitled Draft',
            'species'              => $species,
            'breed'                => $validated['breed'] ?? ($species === 'Cat' ? 'Puspin' : 'Aspin'),
            'age'                  => !empty($validated['age']) ? $validated['age'] : 'Age not specified',
            'gender'               => $validated['gender'] ?? 'Male',
            'location'             => !empty($validated['location']) ? $validated['location'] : 'Brgy. 176-F, Caloocan City',
            'vaccination_status'   => $validated['vaccination_status'] ?? 'Not vaccinated yet',
            'deworming_status'     => $validated['deworming_status'] ?? 'Not dewormed',
            'is_spayed_neutered'   => $request->boolean('is_spayed_neutered'),
            'special_health_notes' => $validated['special_health_notes'] ?? null,
            'description'          => !empty($validated['description']) ? $validated['description'] : 'Draft pet post saved on ' . now()->format('M d, Y'),
            'image_path'           => $imagePath,
            'status'               => $status,
            'rescuer_id'           => $rescuerId,
            'posted_by'            => $postedBy,
            // New posts always start unverified so they enter the admin review queue.
            // Drafts are also unverified until the admin approves them once published.
            'is_verified'          => false,
        ]);

        Cache::forget('home_status_counts');

        if ($isDraft) {
            return redirect()->route('my-posts')->with('success', 'Pet post saved as Draft!');
        }

        return redirect()->route('home')->with('success', $pet->name . ' was successfully posted for adoption in your barangay!');
    }

    /**
     * Update the specified pet post in storage.
     */
    public function update(Request $request, Pet $pet): RedirectResponse
    {
        // Siguraduhing ang may-ari lamang ng post ang pwedeng mag-edit
        if ($pet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:30'],
            'species'              => ['required', 'string', 'in:Dog,Cat,Other'],
            'breed'                => ['nullable', 'string', 'max:255'],
            'age'                  => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !\App\Models\Pet::isRealisticAge($value)) {
                        $fail('Please enter a realistic age for the pet (maximum 20 years).');
                    }
                },
            ],
            'gender'               => ['required', 'string', 'in:Male,Female'],
            'location'             => ['required', 'string', 'max:255'],
            'vaccination_status'   => ['nullable', 'string', 'max:255'],
            'deworming_status'     => ['nullable', 'string', 'max:255'],
            'is_spayed_neutered'   => ['nullable', 'boolean'],
            'special_health_notes' => ['nullable', 'string'],
            'description'          => ['required', 'string', 'max:300'],
            'pet_images'           => ['nullable', 'array', 'max:5'],
            'pet_images.*'         => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'photos'               => ['nullable', 'array', 'max:5'],
            'photos.*'             => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'images'               => ['nullable', 'array', 'max:5'],
            'images.*'             => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'pet_image'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'status'               => ['required', 'string', 'in:Available,Draft,Adopted'],
        ], [
            'name.max'             => 'The animal name cannot exceed 30 characters.',
            'description.max'      => 'The description cannot exceed 300 characters.',
            'pet_images.max'       => 'You may upload a maximum of 5 photos per post.',
        ]);

        $uploadedUpdateFiles = [];
        foreach (['pet_images', 'photos', 'images', 'pet_image'] as $inputKey) {
            if ($request->hasFile($inputKey)) {
                $fileOrFiles = $request->file($inputKey);
                if (is_array($fileOrFiles)) {
                    foreach ($fileOrFiles as $f) {
                        if ($f && $f->isValid()) {
                            $uploadedUpdateFiles[] = $f;
                        }
                    }
                } elseif ($fileOrFiles && $fileOrFiles->isValid()) {
                    $uploadedUpdateFiles[] = $fileOrFiles;
                }
            }
        }

        if (!empty($uploadedUpdateFiles)) {
            $paths = [];
            foreach (array_slice($uploadedUpdateFiles, 0, 5) as $file) {
                $paths[] = $this->uploadPetImageToSupabase($file);
            }
            if (!empty($paths)) {
                $validated['image_path'] = json_encode(array_values($paths));
            }
        }

        $validated['is_spayed_neutered'] = $request->boolean('is_spayed_neutered');

        // When a draft is published (status changed from Draft to Available),
        // reset to unverified so the admin can review and approve it before
        // it appears on the public home feed.
        if ($pet->status === 'Draft' && isset($validated['status']) && $validated['status'] !== 'Draft') {
            $validated['is_verified'] = false;
        }

        $pet->update($validated);

        Cache::forget('home_status_counts');

        return back()->with('success', $pet->name . '\'s details have been updated successfully!');
    }

    /**
     * Toggle a pet's status between Adopted and Available.
     *
     * - If the pet is currently Adopted → revert it to Available.
     * - Otherwise → mark it as Adopted.
     *
     * Only the post owner may call this action.
     */
    public function markAsAdopted(Pet $pet): RedirectResponse
    {
        if ($pet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($pet->status === 'Adopted') {
            // Revert: make the post active again so it reappears on the feed
            $pet->update(['status' => 'Available']);
            Cache::forget('home_status_counts');

            return back()->with('success', $pet->name . ' has been marked as Available again.');
        }

        // Forward: mark the pet as adopted
        $pet->update(['status' => 'Adopted']);
        Cache::forget('home_status_counts');

        return back()->with('success', $pet->name . ' has been marked as Adopted!');
    }

    /**
     * Archive an active pet post (soft delete).
     */
    public function archive(Pet $pet): RedirectResponse
    {
        if ($pet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $pet->update(['archived_at' => now()]);
        Cache::forget('home_status_counts');

        return back()->with('success', 'Post "' . $pet->name . '" has been archived and moved to Archived Posts.');
    }

    /**
     * Restore an archived pet post back to active.
     */
    public function restore(Pet $pet): RedirectResponse
    {
        if ($pet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $pet->update([
            'archived_at' => null,
            'status' => in_array($pet->status, ['Available', 'Pending', 'Adopted']) ? $pet->status : 'Available',
        ]);
        Cache::forget('home_status_counts');

        return back()->with('success', 'Post "' . $pet->name . '" has been restored to active posts.');
    }

    /**
     * Delete a pet post permanently or saved draft.
     */
    public function destroy(Pet $pet): RedirectResponse
    {
        if ($pet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $isDraft = (strtolower($pet->status) === 'draft');
        $isArchived = ($pet->archived_at !== null);
        $petName = $pet->name ?: ($isDraft ? 'Draft' : 'Post');

        // Delete associated pet image file(s) if stored on disk
        if ($pet->image_path) {
            $decoded = json_decode($pet->image_path, true);
            $pathsToDelete = is_array($decoded) ? $decoded : [$pet->image_path];
            $cloudDisk = config('filesystems.disks.supabase') ? 'supabase' : 's3';
            foreach ($pathsToDelete as $path) {
                if ($path) {
                    $filename = basename($path);
                    try {
                        if (Storage::disk($cloudDisk)->exists($filename)) {
                            Storage::disk($cloudDisk)->delete($filename);
                        }
                    } catch (\Throwable $e) {
                        // ignore cloud error
                    }
                    try {
                        if (Storage::disk('public')->exists('pets/' . $filename)) {
                            Storage::disk('public')->delete('pets/' . $filename);
                        }
                    } catch (\Throwable $e) {
                        // ignore local error
                    }
                }
            }
        }

        $pet->delete();
        Cache::forget('home_status_counts');

        $msg = $isDraft 
            ? 'Draft "' . $petName . '" has been deleted.' 
            : ($isArchived 
                ? 'Post "' . $petName . '" has been permanently deleted.' 
                : 'Post "' . $petName . '" has been deleted successfully.');

        return back()->with('success', $msg);
    }

    /**
     * Store a newly submitted adoption application.
     */
    public function storeAdoptionRequest(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to submit an adoption request.');
        }

        if (Auth::user()->status === 'blocked' || Auth::user()->is_blocked || Auth::user()->archived_at !== null) {
            abort(403, 'Your account has been blocked or removed.');
        }

        $validated = $request->validate([
            'pet_id'         => ['required', 'exists:pets,id'],
            'applicant_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'address'        => ['nullable', 'string', 'max:255'],
            'barangay'       => ['nullable', 'string', 'max:255'],
            'message'        => ['nullable', 'string', 'max:2000'],
        ]);

        $rawAddress = trim($request->input('address', $request->input('barangay', '')));
        if (empty($rawAddress)) {
            return back()->withErrors(['address' => 'Please provide your Package, Block, or Lot address within Brgy. 176-F.']);
        }

        // Format location ensuring Brgy. 176-F context is preserved
        $location = str_contains(strtolower($rawAddress), '176-f')
            ? $rawAddress
            : ($rawAddress . ', Brgy. 176-F');

        $pet = Pet::findOrFail($validated['pet_id']);

        // Prevent adopting own pet
        if ($pet->user_id === Auth::id()) {
            return back()->with('error', 'You cannot submit an adoption request for your own posted pet.');
        }

        // Check for existing pending request by this user first (before status check)
        $existing = AdoptionRequest::where('pet_id', $pet->id)
            ->where('user_id', Auth::id())
            ->where('status', 'Pending')
            ->first();

        if ($existing) {
            return back()->with('info', 'You already have an active pending adoption request for ' . $pet->name . '. The rescuer will review your application soon!');
        }

        // Prevent adopting non-available pets (after duplicate check so returning users get the right message)
        if (!in_array(strtolower($pet->status), ['available', 'pending'])) {
            return back()->with('error', 'This pet is currently ' . strtolower($pet->status) . ' and not open for new adoption applications.');
        }

        // Only allow new requests if pet is Available
        if (strtolower($pet->status) === 'pending') {
            return back()->with('error', 'This pet currently has a pending adoption request from another applicant and is not accepting new applications.');
        }

        $adoptionRequest = AdoptionRequest::create([
            'pet_id'         => $pet->id,
            'user_id'        => Auth::id(),
            'applicant_name' => $validated['applicant_name'],
            'contact_number' => $validated['contact_number'],
            'email'          => $validated['email'] ?? Auth::user()->email,
            'barangay'       => $location,
            'message'        => $validated['message'] ?? null,
            'status'         => 'Pending',
        ]);

        // Auto-set pet status to Pending so it disappears from the Available feed
        $pet->update(['status' => 'Pending']);

        // Send an introductory message to the rescuer in Messages
        if ($pet->user_id) {
            $note = !empty($validated['message']) ? "\n\nNote: \"" . trim($validated['message']) . '"' : '';
            $introText = "Hello {$pet->posted_by}! I have submitted an official adoption request for {$pet->name}. Contact: {$validated['contact_number']} ({$location}).{$note}";

            \App\Models\Message::create([
                'sender_id'   => Auth::id(),
                'receiver_id' => $pet->user_id,
                'pet_id'      => $pet->id,
                'subject'     => 'Adoption Request: ' . $pet->name,
                'message'     => $introText,
                'is_read'     => false,
            ]);
        }

        return redirect()->route('my-posts')->with('success', '🐾 Your adoption request for ' . $pet->name . ' has been submitted successfully! You can track your application status under My Posts.');
    }

    /**
     * Update adoption application status (Accept / Decline).
     * - Accepted  → set pet status to Adopted, send acceptance system message
     * - Declined  → revert pet status to Available (if no other pending), send decline system message
     */
    public function updateRequestStatus(AdoptionRequest $requestRecord, Request $request): RedirectResponse
    {
        $newStatus = $request->input('status', 'Accepted');
        $requestRecord->update(['status' => $newStatus]);

        $pet        = $requestRecord->pet;
        $petName    = $pet->name ?? 'the pet';
        $rescuerName = Auth::user()->name;
        $applicantId = $requestRecord->user_id;

        if ($pet) {
            if ($newStatus === 'Accepted') {
                $pet->update(['status' => 'Adopted']);
            } elseif ($newStatus === 'Declined') {
                // Only revert to Available if no other pending request exists for this pet
                $stillPending = AdoptionRequest::where('pet_id', $pet->id)
                    ->where('id', '!=', $requestRecord->id)
                    ->where('status', 'Pending')
                    ->exists();
                if (!$stillPending) {
                    $pet->update(['status' => 'Available']);
                }
            }

            // Send automated system message to the applicant's chat thread
            if ($applicantId) {
                $messageText = $newStatus === 'Accepted'
                    ? $rescuerName . ' has accepted your adoption request for ' . $petName . '.'
                    : $rescuerName . ' has declined your adoption request for ' . $petName . '.';

                $subject = $newStatus === 'Accepted'
                    ? 'Adoption Request Accepted: ' . $petName
                    : 'Adoption Request Declined: ' . $petName;

                \App\Models\Message::create([
                    'sender_id'   => Auth::id(),
                    'receiver_id' => $applicantId,
                    'pet_id'      => $pet->id,
                    'subject'     => $subject,
                    'message'     => $messageText,
                    'is_read'     => false,
                ]);
            }
        }

        return back()->with('success', 'Adoption request for ' . $petName . ' was ' . strtolower($newStatus) . '.');
    }

    /**
     * Cancel an adoption request submitted by the currently logged-in user.
     * Deletes the application record, sends an automated notification message,
     * and reverts the pet status back to Available (if no other pending request exists).
     */
    public function cancelAdoptionRequest(AdoptionRequest $requestRecord): RedirectResponse
    {
        // Only the applicant can cancel their own request
        if ($requestRecord->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow cancellation while still Pending (or legacy Cancelled)
        if (!in_array($requestRecord->status, ['Pending', 'Cancelled'])) {
            return back()->with('info', 'This request has already been ' . strtolower($requestRecord->status) . ' and cannot be cancelled.');
        }

        $pet = $requestRecord->pet;
        $petName = $pet ? $pet->name : 'the pet';
        $userName = Auth::user()->name;

        // Revert pet to Available if no other pending requests remain
        if ($pet) {
            $stillPending = AdoptionRequest::where('pet_id', $pet->id)
                ->where('id', '!=', $requestRecord->id)
                ->where('status', 'Pending')
                ->exists();
            if (!$stillPending) {
                $pet->update(['status' => 'Available']);
            }

            // Send automated system message to rescuer in their chat thread
            if ($pet->user_id) {
                \App\Models\Message::create([
                    'sender_id'   => Auth::id(),
                    'receiver_id' => $pet->user_id,
                    'pet_id'      => $pet->id,
                    'subject'     => 'Adoption Request Cancelled: ' . $pet->name,
                    'message'     => $userName . ' cancelled their adoption request for ' . $pet->name . '.',
                    'is_read'     => false,
                ]);
            }
        }

        // Completely delete the application record so it no longer shows up under My Applications
        $requestRecord->delete();

        return back()->with('success', 'Your adoption request for ' . $petName . ' has been cancelled and removed.');
    }

    /**
     * Permanently delete an adoption application record submitted by the user.
     * Allowed ONLY for Declined or Cancelled applications.
     */
    public function destroyApplication(AdoptionRequest $requestRecord): RedirectResponse
    {
        // Only the applicant can delete their own application
        if ($requestRecord->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Only allowed for Declined or Cancelled applications
        if (!in_array($requestRecord->status, ['Declined', 'Cancelled'])) {
            return back()->with('error', 'Only declined or cancelled applications can be deleted.');
        }

        $pet = $requestRecord->pet;
        $petName = $pet ? $pet->name : 'the pet';

        $requestRecord->delete();

        return back()->with('success', 'Adoption application for ' . $petName . ' has been permanently deleted.');
    }

    /**
     * Upload an uploaded pet image directly to Supabase Storage Bucket,
     * applying client-safe image optimization (compression and resize),
     * and returning the full public Supabase CDN URL for database storage.
     */
    protected function uploadPetImageToSupabase(\Illuminate\Http\UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }
        $filename = Str::random(40) . '.' . $extension;
        $tempPath = $file->getRealPath();

        // Performance Optimization: Image compression and max-dimension resizing before cloud upload
        if (extension_loaded('gd') && in_array($extension, ['jpg', 'png', 'webp'])) {
            try {
                $imageInfo = @getimagesize($tempPath);
                if ($imageInfo) {
                    $origWidth = $imageInfo[0];
                    $origHeight = $imageInfo[1];
                    $mime = $imageInfo['mime'];
                    $maxDimension = 1280;

                    if ($origWidth > $maxDimension || $origHeight > $maxDimension || filesize($tempPath) > 400 * 1024) {
                        $scale = min($maxDimension / max($origWidth, 1), $maxDimension / max($origHeight, 1), 1.0);
                        $targetWidth = (int) round($origWidth * $scale);
                        $targetHeight = (int) round($origHeight * $scale);

                        $srcImg = match ($mime) {
                            'image/jpeg' => @imagecreatefromjpeg($tempPath),
                            'image/png'  => @imagecreatefrompng($tempPath),
                            'image/webp' => @imagecreatefromwebp($tempPath),
                            default      => null,
                        };

                        if ($srcImg) {
                            $dstImg = imagecreatetruecolor($targetWidth, $targetHeight);
                            if ($mime === 'image/png') {
                                imagealphablending($dstImg, false);
                                imagesavealpha($dstImg, true);
                            }
                            imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);

                            $compressedPath = tempnam(sys_get_temp_dir(), 'kupkop_opt_');
                            if ($mime === 'image/png') {
                                imagepng($dstImg, $compressedPath, 7);
                            } else {
                                imagejpeg($dstImg, $compressedPath, 82);
                            }

                            imagedestroy($srcImg);
                            imagedestroy($dstImg);

                            $tempPath = $compressedPath;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Optimization failsafe: proceed with original file if GD processing hits any issue
            }
        }

        // Supabase Storage Bucket details
        $bucket = env('SUPABASE_STORAGE_BUCKET', env('AWS_BUCKET', 'pets'));
        $projectRef = env('SUPABASE_PROJECT_REF', 'hlaoqkozskcyskkhubtw');
        $publicCdnUrl = rtrim(env('SUPABASE_STORAGE_URL', env('AWS_URL', "https://{$projectRef}.supabase.co/storage/v1/object/public/{$bucket}")), '/') . '/' . $filename;

        // 1. Attempt upload using configured Supabase / S3 disk driver
        $cloudDisk = config('filesystems.disks.supabase') ? 'supabase' : 's3';
        $cloudKey = config("filesystems.disks.{$cloudDisk}.key");
        $cloudSecret = config("filesystems.disks.{$cloudDisk}.secret");

        if (!empty($cloudKey) && !empty($cloudSecret)) {
            try {
                $contents = file_get_contents($tempPath);
                Storage::disk($cloudDisk)->put($filename, $contents, 'public');
                return $publicCdnUrl;
            } catch (\Throwable $e) {
                // S3 driver upload threw exception, try REST fallback below
            }
        }

        // 2. Direct Supabase Storage REST API (with API Key if present)
        $supabaseApiKey = env('SUPABASE_KEY', env('SUPABASE_SERVICE_ROLE_KEY', env('SUPABASE_ANON_KEY')));
        if (!empty($supabaseApiKey)) {
            try {
                $contents = file_get_contents($tempPath);
                $mimeType = $file->getMimeType() ?: 'image/jpeg';
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $supabaseApiKey,
                    'apikey'        => $supabaseApiKey,
                    'Content-Type'  => $mimeType,
                ])->withBody($contents, $mimeType)
                  ->post("https://{$projectRef}.supabase.co/storage/v1/object/{$bucket}/{$filename}");

                if ($response->successful()) {
                    return $publicCdnUrl;
                }
            } catch (\Throwable $e) {
                // REST API threw exception
            }
        }

        // 3. No cloud credentials available — local dev fallback.
        //    Save the file to public/storage/pets/ so it's accessible via /storage/pets/<filename>.
        //    Store only the relative path so formatPetImageUrl() can resolve it correctly.
        try {
            $contents = file_get_contents($tempPath);
            Storage::disk('public')->put('pets/' . $filename, $contents);
        } catch (\Throwable $e) {
            // Ignore storage failure — file may already be accessible from the temp upload path
        }

        // Return a relative path that formatPetImageUrl() resolves to /storage/pets/<filename>
        return 'pets/' . $filename;
    }
}