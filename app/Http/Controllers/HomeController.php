<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home page with dynamic pet listings from the database.
     */
    public function index(Request $request): View
    {
        // Public feed: all non-archived, non-draft pets from active user accounts.
        // Verification (is_verified) is an admin-review marker only and does NOT gate
        // public visibility — posts appear on the feed as soon as they are published.
        $query = Pet::select([
                'id',
                'user_id',
                'name',
                'species',
                'breed',
                'age',
                'gender',
                'vaccination_status',
                'deworming_status',
                'is_spayed_neutered',
                'special_health_notes',
                'description',
                'image_path',
                'status',
                'location',
                'rescuer_id',
                'posted_by',
                'is_verified',
                'created_at',
                'archived_at',
            ])
            ->with(['user' => function ($q) {
                $q->select('id', 'name', 'is_verified', 'status', 'archived_at');
            }])
            ->whereNull('archived_at')
            ->whereNotIn('status', ['Draft', 'Adopted'])
            ->whereHas('user', function ($q) {
                $q->where('status', 'active')
                  ->whereNull('archived_at');
            });

        // 1. Keyword search (Name, breed, description, barangay)
        if ($request->filled('keyword')) {
            $keyword = '%' . $request->keyword . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', $keyword)
                  ->orWhere('breed', 'like', $keyword)
                  ->orWhere('description', 'like', $keyword)
                  ->orWhere('location', 'like', $keyword);
            });
        }

        // 2. Species filter
        if ($request->filled('species') && in_array(strtolower($request->species), ['dog', 'cat'])) {
            $query->where('species', ucfirst(strtolower($request->species)));
        }

        // 3. Breed filter
        if ($request->filled('breed')) {
            $breed = strtolower($request->breed);
            if ($breed === 'aspin') {
                $query->where('breed', 'like', '%Aspin%');
            } elseif ($breed === 'puspin') {
                $query->where('breed', 'like', '%Puspin%');
            } elseif ($breed === 'mix') {
                $query->where(function($q) {
                    $q->where('breed', 'like', '%mix%')->orWhere('breed', 'like', '%mixed%');
                });
            } else {
                $query->where('breed', 'like', '%' . $request->breed . '%');
            }
        }

        // 4. Gender filter
        if ($request->filled('gender') && in_array(strtolower($request->gender), ['male', 'female'])) {
            $query->where('gender', ucfirst(strtolower($request->gender)));
        }

        // Count for status tabs: All, Available, Pending, Verified (safely cached with short TTL)
        $statusCounts = Cache::remember('home_status_counts', 60, function () {
            $countBase = Pet::query()
                ->whereNull('archived_at')
                ->whereHas('user', function ($q) {
                    $q->where('status', 'active')
                      ->whereNull('archived_at');
                })
                ->whereNotIn('status', ['Draft', 'Adopted']);

            $rawCounts = (clone $countBase)
                ->selectRaw("
                    COUNT(*) as all_count,
                    COUNT(CASE WHEN status = 'Available' THEN 1 END) as available_count,
                    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_count
                ")
                ->first();

            $verifiedCount = (clone $countBase)->where(function ($q) {
                $q->whereRaw('is_verified IS TRUE')
                  ->orWhereHas('user', function ($uq) {
                      $uq->whereRaw('is_verified IS TRUE');
                  });
            })->count();

            return [
                'all'       => (int) ($rawCounts->all_count ?? 0),
                'available' => (int) ($rawCounts->available_count ?? 0),
                'pending'   => (int) ($rawCounts->pending_count ?? 0),
                'verified'  => (int) $verifiedCount,
            ];
        });

        // 5. Status filter (default: all)
        $selectedStatus = strtolower($request->get('status', 'all'));
        if ($selectedStatus === 'available') {
            $query->where('status', 'Available');
        } elseif ($selectedStatus === 'pending') {
            $query->where('status', 'Pending');
        } elseif ($selectedStatus === 'verified') {
            $query->where(function ($q) {
                $q->whereRaw('is_verified IS TRUE')
                  ->orWhereHas('user', function ($uq) {
                      $uq->whereRaw('is_verified IS TRUE');
                  });
            });
        }

        // Fetch pets ordered with newest first
        $pets = $query->latest()->get();

        // 4b. Age filter (accurately parses human age descriptions like "5 months", "1.5 yrs", "22 years old", "Adult")
        if ($request->filled('age')) {
            $ageFilter = strtolower($request->age);
            $pets = $pets->filter(function (Pet $pet) use ($ageFilter) {
                return self::matchesAgeFilter($pet->age, $ageFilter);
            })->values();
        }

        // 6. Admin summary stats (only queried when admin user is authenticated to save query overhead for guests/users)
        $adminStats = [
            'total_listed' => $statusCounts['all'],
            'available'    => $statusCounts['available'],
            'pending'      => $statusCounts['pending'],
        ];

        if (auth()->check() && auth()->user()->is_admin) {
            $adminCounts = Pet::whereNull('archived_at')
                ->where('status', '!=', 'Draft')
                ->selectRaw("
                    COUNT(*) as total_listed,
                    COUNT(CASE WHEN status = 'Available' THEN 1 END) as available,
                    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending
                ")
                ->first();

            if ($adminCounts) {
                $adminStats = [
                    'total_listed' => (int) ($adminCounts->total_listed ?? 0),
                    'available'    => (int) ($adminCounts->available ?? 0),
                    'pending'      => (int) ($adminCounts->pending ?? 0),
                ];
            }
        }

        // Pre-fetch pending adoption requests for the authenticated user to eliminate query overhead in blade view
        $myPendingPetIds = [];
        if (auth()->check()) {
            $myPendingPetIds = \App\Models\AdoptionRequest::where('user_id', auth()->id())
                ->where('status', 'Pending')
                ->pluck('pet_id')
                ->all();
        }

        return view('pages.home', compact('pets', 'statusCounts', 'adminStats', 'myPendingPetIds'));
    }

    /**
     * Categorize free-text pet age into standardized filter categories:
     * - 'puppy_kitten': Puppy / Kitten (0 - 6 months)
     * - 'junior' (or 'young'): Junior (7 - 12 months)
     * - 'adult': Adult (1 - 7 years)
     * - 'senior': Senior (8+ years, up to 20 years)
     *
     * @param string|null $ageStr
     * @param string $filter 'puppy_kitten', 'junior', 'young', 'adult', or 'senior'
     * @return bool
     */
    public static function matchesAgeFilter(?string $ageStr, string $filter): bool
    {
        if (!$ageStr) {
            return false;
        }

        $raw = strtolower(trim($ageStr));
        if ($raw === '') {
            return false;
        }

        $filter = strtolower($filter);

        // 1. Direct keyword shortcuts
        if (in_array($raw, ['puppy', 'kitten', 'pup', 'kit', 'baby'])) {
            return $filter === 'puppy_kitten';
        }
        if (in_array($raw, ['junior', 'young', 'adolescent', 'juvenile', 'teen'])) {
            return $filter === 'junior' || $filter === 'young';
        }
        if (in_array($raw, ['adult', 'mature'])) {
            return $filter === 'adult';
        }
        if (in_array($raw, ['senior', 'elderly', 'old', 'geriatric'])) {
            return $filter === 'senior';
        }

        // 2. Numerical value with units
        // Weeks: e.g. "8 weeks", "4 wks", "12wk"
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:weeks?|wks?|wk)\b/i', $raw, $m)) {
            $months = ((float) $m[1]) / 4.3;
            return self::classifyMonths($months, $filter);
        }

        // Days: e.g. "45 days"
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:days?|d)\b/i', $raw, $m)) {
            $months = ((float) $m[1]) / 30.0;
            return self::classifyMonths($months, $filter);
        }

        // Months: e.g. "5 months", "11 mos", "6 mo", "3mo"
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:months?|mos?|mo)\b/i', $raw, $m)) {
            $months = (float) $m[1];
            return self::classifyMonths($months, $filter);
        }

        // Years: e.g. "1 year old", "1.5 yrs", "22 years old", "3yo", "4 yr", "2 yrs"
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:years?|yrs?|yr|y\.?o\.?|yo)\b/i', $raw, $m)) {
            $years = (float) $m[1];
            return self::classifyYears($years, $filter);
        }

        // Plain number: e.g. "2" or "3" (standard assumption in pet profiles is years)
        if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*$/', $raw, $m)) {
            $val = (float) $m[1];
            return self::classifyYears($val, $filter);
        }

        // 3. Fallback word search if combined with text (e.g. "Adult (approx 3 yrs)")
        if (str_contains($raw, 'puppy') || str_contains($raw, 'kitten')) {
            return $filter === 'puppy_kitten';
        }
        if (str_contains($raw, 'junior')) {
            return $filter === 'junior' || $filter === 'young';
        }
        if (str_contains($raw, 'senior') || str_contains($raw, 'elderly')) {
            return $filter === 'senior';
        }
        if (str_contains($raw, 'adult')) {
            return $filter === 'adult';
        }
        if (str_contains($raw, 'young')) {
            return $filter === 'junior' || $filter === 'young';
        }

        return false;
    }

    private static function classifyMonths(float $months, string $filter): bool
    {
        // Unrealistic age over 240 months (20 years) or non-positive
        if ($months <= 0 || $months > 240.0) {
            return false;
        }

        // Puppy / Kitten: 0 - 6 months (≤ 6)
        if ($filter === 'puppy_kitten') {
            return $months <= 6.0;
        }

        // Junior: 7 - 12 months (> 6 and <= 12)
        if ($filter === 'junior' || $filter === 'young') {
            return $months > 6.0 && $months <= 12.0;
        }

        // Adult: > 12 months up to 7 years (> 12 to 84 months)
        if ($filter === 'adult') {
            return $months > 12.0 && $months <= 84.0;
        }

        // Senior: 8+ years (> 84 to 240 months)
        if ($filter === 'senior') {
            return $months > 84.0 && $months <= 240.0;
        }

        return false;
    }

    private static function classifyYears(float $years, string $filter): bool
    {
        // Unrealistic age over 20 years or non-positive
        if ($years <= 0 || $years > 20.0) {
            return false;
        }

        // Puppy / Kitten: 0 - 6 months (≤ 0.5 years)
        if ($filter === 'puppy_kitten') {
            return $years <= 0.5;
        }

        // Junior: 7 - 11 months (> 0.5 and < 1.0 years)
        if ($filter === 'junior' || $filter === 'young') {
            return $years > 0.5 && $years < 1.0;
        }

        // Adult: 1 - 7 years (inclusive)
        if ($filter === 'adult') {
            return $years >= 1.0 && $years <= 7.0;
        }

        // Senior: 8+ years (capped at realistic 20 years)
        if ($filter === 'senior') {
            return $years >= 8.0 && $years <= 20.0;
        }

        return false;
    }
}
