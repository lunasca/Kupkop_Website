<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\AdoptionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display the "My Posts" dashboard: listed pets & incoming adoption requests.
     */
    public function index(): View
    {
        $userId = Auth::id();

        // If user is logged in, show their posts; otherwise show all posted pets for demo/guest convenience
        $myPets = $userId 
            ? Pet::where('user_id', $userId)->latest()->get()
            : Pet::latest()->get();

        // Fetch incoming adoption requests for those pets
        $petIds = $myPets->pluck('id');
        $incomingRequests = AdoptionRequest::with('pet')
            ->whereIn('pet_id', $petIds)
            ->latest()
            ->get();

        return view('pages.my-posts', compact('myPets', 'incomingRequests'));
    }

    /**
     * Store a newly created pet post in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'species'               => ['required', 'string', 'in:Dog,Cat,Other'],
            'breed'                 => ['nullable', 'string', 'max:255'],
            'age'                   => ['required', 'string', 'max:100'],
            'gender'                => ['required', 'string', 'in:Male,Female'],
            'vaccination_status'    => ['nullable', 'string', 'max:255'],
            'deworming_status'      => ['nullable', 'string', 'max:255'],
            'is_spayed_neutered'    => ['nullable', 'boolean'],
            'special_health_notes'  => ['nullable', 'string'],
            'description'           => ['required', 'string'],
            'pet_image'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
            'action'                => ['nullable', 'string'], // Post or Draft
        ]);

        $imagePath = null;
        if ($request->hasFile('pet_image')) {
            $imagePath = $request->file('pet_image')->store('pets', 'public');
        }

        $status = ($request->input('action') === 'draft') ? 'Draft' : 'Available';

        $rescuerId = '#RES-' . str_pad((string) rand(100, 999), 4, '0', STR_PAD_LEFT);
        $postedBy = Auth::check() 
            ? Auth::user()->name 
            : 'Barangay Animal Care Volunteer';

        $pet = Pet::create([
            'user_id'              => Auth::id(),
            'name'                 => $validated['name'],
            'species'              => $validated['species'],
            'breed'                => $validated['breed'] ?? ($validated['species'] === 'Cat' ? 'Puspin' : 'Aspin'),
            'age'                  => $validated['age'],
            'gender'               => $validated['gender'],
            'vaccination_status'   => $validated['vaccination_status'] ?? 'Not vaccinated yet',
            'deworming_status'     => $validated['deworming_status'] ?? 'Not dewormed',
            'is_spayed_neutered'   => $request->boolean('is_spayed_neutered'),
            'special_health_notes' => $validated['special_health_notes'],
            'description'          => $validated['description'],
            'image_path'           => $imagePath,
            'status'               => $status,
            'location'             => 'Brgy. San Antonio, Pasig City',
            'rescuer_id'           => $rescuerId,
            'posted_by'            => $postedBy,
        ]);

        $successMsg = ($status === 'Draft') 
            ? 'Pet post saved as Draft!' 
            : '🐾 ' . $pet->name . ' was successfully posted for adoption in your barangay!';

        return redirect()->route('home')->with('success', $successMsg);
    }

    /**
     * Mark a pet as Adopted.
     */
    public function markAsAdopted(Pet $pet): RedirectResponse
    {
        $pet->update(['status' => 'Adopted']);

        return back()->with('success', $pet->name . ' has been marked as Adopted! 🎉');
    }

    /**
     * Update adoption application status (Accept / Decline).
     */
    public function updateRequestStatus(AdoptionRequest $requestRecord, Request $request): RedirectResponse
    {
        $newStatus = $request->input('status', 'Accepted');
        $requestRecord->update(['status' => $newStatus]);

        return back()->with('success', 'Adoption request for ' . ($requestRecord->pet->name ?? 'pet') . ' was ' . strtolower($newStatus) . '.');
    }
}
