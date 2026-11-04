<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display the Posts Management table (All posts or Archive).
     */
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        // Tab selection: 'all' or 'archive' (defaults to 'all')
        $tab = $request->input('tab', 'all');
        if (!in_array($tab, ['all', 'archive'])) {
            $tab = 'all';
        }

        // Live badge counts
        $allCount = Pet::whereNull('archived_at')->count();
        $archiveCount = Pet::whereNotNull('archived_at')->count();

        // Base query depending on active tab
        $query = Pet::with('user');
        if ($tab === 'archive') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        // 1. Search Input: Filter by pet name, pet ID, or owner name
        if ($request->filled('search')) {
            $search = trim($request->search);
            $term = '%' . $search . '%';
            $numericId = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($term, $numericId) {
                $q->where('name', 'like', $term)
                  ->orWhere('posted_by', 'like', $term)
                  ->orWhere('location', 'like', $term)
                  ->orWhere('breed', 'like', $term)
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->where('name', 'like', $term);
                  });

                if (!empty($numericId)) {
                    $q->orWhere('id', (int) $numericId)
                      ->orWhere('rescuer_id', 'like', '%' . $numericId . '%');
                }
            });
        }

        // 2. Species Filter: Dropdown with options All, Dog, Cat
        if ($request->filled('species') && strtolower($request->species) !== 'all') {
            $species = strtolower($request->species);
            if (in_array($species, ['dog', 'cat'])) {
                $query->whereRaw('LOWER(TRIM(species)) = ?', [$species]);
            }
        }

        // 3. Verification Filter: Dropdown with options All, Verified, Unverified
        if ($request->filled('verification') && strtolower($request->verification) !== 'all') {
            $verification = strtolower($request->verification);
            if ($verification === 'verified') {
                $query->where('is_verified', true);
            } elseif ($verification === 'unverified') {
                $query->where('is_verified', false);
            }
        }

        // Order newest first
        $pets = $query->latest()->get();

        return view('pages.admin.posts', compact('pets', 'tab', 'allCount', 'archiveCount'));
    }

    /**
     * Mark a post as verified.
     */
    public function verify(Request $request, Pet $pet): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        $pet->update(['is_verified' => true]);

        return back()->with('success', $pet->name . ' has been marked as verified.');
    }

    /**
     * Mark a post as unverified.
     */
    public function unverify(Request $request, Pet $pet): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        $pet->update(['is_verified' => false]);

        return back()->with('success', $pet->name . ' is now marked as unverified.');
    }

    /**
     * Soft-delete a post by stamping the archived_at timestamp.
     */
    public function archive(Request $request, Pet $pet): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        $pet->update(['archived_at' => now()]);

        return back()->with('success', $pet->name . ' has been moved to the archive.');
    }

    /**
     * Restore an archived post back to the All posts tab.
     */
    public function restore(Request $request, Pet $pet): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        $pet->update(['archived_at' => null]);

        return back()->with('success', $pet->name . ' has been restored to active posts.');
    }

    /**
     * Permanently delete a post from the database.
     */
    public function destroy(Request $request, Pet $pet): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        $petName = $pet->name;
        $pet->delete();

        return back()->with('success', $petName . ' has been permanently deleted.');
    }
}
