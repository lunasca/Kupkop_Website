<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\AdoptionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
                'myApplications' => collect()
            ]);
        }

        $userId = Auth::id();

        // Stamp the "last viewed" time so the navbar badge clears after visiting
        Auth::user()->update(['requests_last_viewed_at' => now()]);

        // Only show published/active posts under "My Active Animal Posts" (exclude Drafts)
        $myPets = Pet::where('user_id', $userId)
            ->where('status', '!=', 'Draft')
            ->latest()
            ->get();

        // Draft posts kept separately for the dedicated Drafts section
        $draftPets = Pet::where('user_id', $userId)
            ->where('status', 'Draft')
            ->latest()
            ->get();

        // Fetch incoming adoption requests for active pets (show ONLY Pending)
        $petIds = $myPets->pluck('id');
        $incomingRequests = AdoptionRequest::with('pet')
            ->whereIn('pet_id', $petIds)
            ->where('status', 'Pending')
            ->latest()
            ->get();

        // Fetch submitted adoption applications made by this user
        $myApplications = AdoptionRequest::with(['pet.user'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return view('pages.my-posts', compact('myPets', 'draftPets', 'incomingRequests', 'myApplications'));
    }

    /**
     * Store a newly created pet post in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'You must be logged in to post an animal for adoption.');
        }

        $isDraft = ($request->input('action') === 'draft');

        $rules = [
            'name'                 => [$isDraft ? 'nullable' : 'required', 'string', 'max:255'],
            'species'              => [$isDraft ? 'nullable' : 'required', 'string', 'in:Dog,Cat,Other'],
            'breed'                => ['nullable', 'string', 'max:255'],
            'age'                  => [$isDraft ? 'nullable' : 'required', 'string', 'max:100'],
            'gender'               => [$isDraft ? 'nullable' : 'required', 'string', 'in:Male,Female'],
            'location'             => [$isDraft ? 'nullable' : 'required', 'string', 'max:255'],
            'vaccination_status'   => ['nullable', 'string', 'max:255'],
            'deworming_status'     => ['nullable', 'string', 'max:255'],
            'is_spayed_neutered'   => ['nullable', 'boolean'],
            'special_health_notes' => ['nullable', 'string'],
            'description'          => [$isDraft ? 'nullable' : 'required', 'string'],
            'pet_image'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
            'action'               => ['nullable', 'string'], // Post or Draft
        ];

        $validated = $request->validate($rules);

        $imagePath = null;
        if ($request->hasFile('pet_image')) {
            $imagePath = $request->file('pet_image')->store('pets', 'public');
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
        ]);

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
            'name'                 => ['required', 'string', 'max:255'],
            'species'              => ['required', 'string', 'in:Dog,Cat,Other'],
            'breed'                => ['nullable', 'string', 'max:255'],
            'age'                  => ['required', 'string', 'max:100'],
            'gender'               => ['required', 'string', 'in:Male,Female'],
            'location'             => ['required', 'string', 'max:255'],
            'vaccination_status'   => ['nullable', 'string', 'max:255'],
            'deworming_status'     => ['nullable', 'string', 'max:255'],
            'is_spayed_neutered'   => ['nullable', 'boolean'],
            'special_health_notes' => ['nullable', 'string'],
            'description'          => ['required', 'string'],
            'pet_image'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'status'               => ['required', 'string', 'in:Available,Draft,Adopted'],
        ]);

        if ($request->hasFile('pet_image')) {
            $validated['image_path'] = $request->file('pet_image')->store('pets', 'public');
        }

        $validated['is_spayed_neutered'] = $request->boolean('is_spayed_neutered');

        $pet->update($validated);

        return back()->with('success', $pet->name . '\'s details have been updated successfully!');
    }

    /**
     * Mark a pet as Adopted.
     */
    public function markAsAdopted(Pet $pet): RedirectResponse
    {
        $pet->update(['status' => 'Adopted']);

        return back()->with('success', $pet->name . ' has been marked as Adopted!');
    }

    /**
     * Delete a pet post or saved draft.
     */
    public function destroy(Pet $pet): RedirectResponse
    {
        if ($pet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $isDraft = (strtolower($pet->status) === 'draft');
        $petName = $pet->name ?: ($isDraft ? 'Draft' : 'Post');

        // Delete associated pet image file if stored locally
        if ($pet->image_path && Storage::disk('public')->exists($pet->image_path)) {
            Storage::disk('public')->delete($pet->image_path);
        }

        $pet->delete();

        $msg = $isDraft 
            ? 'Draft "' . $petName . '" has been deleted.' 
            : 'Post "' . $petName . '" has been deleted successfully.';

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
}