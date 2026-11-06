<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        // Live badge counts (consolidated into a single aggregation query)
        $counts = Pet::selectRaw("
            COUNT(CASE WHEN archived_at IS NULL THEN 1 END) as all_count,
            COUNT(CASE WHEN archived_at IS NOT NULL THEN 1 END) as archive_count
        ")->first();

        $allCount = (int) ($counts->all_count ?? 0);
        $archiveCount = (int) ($counts->archive_count ?? 0);

        // Base query depending on active tab with pruned columns
        $petColumns = [
            'id', 'user_id', 'name', 'species', 'breed', 'age', 'gender',
            'status', 'location', 'rescuer_id', 'posted_by', 'is_verified',
            'image_path', 'created_at', 'archived_at'
        ];

        $query = Pet::select($petColumns)->with(['user:id,name,email,is_verified,status']);
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

        // Order newest first with pagination to prevent unbounded memory growth
        $pets = $query->latest()->paginate(25)->withQueryString();

        return view('pages.admin.posts', compact('pets', 'tab', 'allCount', 'archiveCount'));
    }

    /**
     * Mark a post as verified.
     */
    public function verify(Request $request, Pet $pet): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        $pet->update(['is_verified' => true]);
        Cache::forget('home_status_counts');

        return back()->with('success', $pet->name . ' has been marked as verified.');
    }

    /**
     * Mark a post as unverified.
     */
    public function unverify(Request $request, Pet $pet): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        $pet->update(['is_verified' => false]);
        Cache::forget('home_status_counts');

        return back()->with('success', $pet->name . ' is now marked as unverified.');
    }

    /**
     * Soft-delete a post by stamping the archived_at timestamp.
     */
    public function archive(Request $request, Pet $pet): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        $pet->update(['archived_at' => now()]);
        Cache::forget('home_status_counts');

        return back()->with('success', $pet->name . ' has been moved to the archive.');
    }

    /**
     * Restore an archived post back to the All posts tab.
     */
    public function restore(Request $request, Pet $pet): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        $pet->update(['archived_at' => null]);
        Cache::forget('home_status_counts');

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
        Cache::forget('home_status_counts');

        return back()->with('success', $petName . ' has been permanently deleted.');
    }
}
