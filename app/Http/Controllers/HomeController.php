<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home page with dynamic pet listings from the database.
     */
    public function index(Request $request): View
    {
        $query = Pet::query();

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

        // 5. Status filter (default: Available only — excludes Pending, Adopted, Draft)
        if ($request->filled('status')) {
            if ($request->status === 'all') {
                // Show everything except Drafts
                $query->where('status', '!=', 'Draft');
            } elseif ($request->status === 'available') {
                // Strictly Available — exclude Pending and everything else
                $query->where('status', 'Available');
            } else {
                $query->where('status', ucfirst(strtolower($request->status)));
            }
        } else {
            // Default: only Available pets
            $query->where('status', 'Available');
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

        return view('pages.home', compact('pets'));
    }

    /**
     * Categorize free-text pet age into standardized filter categories.
     *
     * @param string|null $ageStr
     * @param string $filter 'puppy_kitten', 'young', or 'adult'
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

        // 1. Direct keyword shortcuts
        if (in_array($raw, ['puppy', 'kitten', 'pup', 'kit', 'baby'])) {
            return $filter === 'puppy_kitten';
        }
        if (in_array($raw, ['adult', 'senior', 'mature', 'elderly', 'old'])) {
            return $filter === 'adult';
        }
        if (in_array($raw, ['young', 'adolescent', 'juvenile', 'teen'])) {
            return $filter === 'young';
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
        if (str_contains($raw, 'senior') || str_contains($raw, 'adult')) {
            return $filter === 'adult';
        }
        if (str_contains($raw, 'young')) {
            return $filter === 'young';
        }

        return false;
    }

    private static function classifyMonths(float $months, string $filter): bool
    {
        if ($filter === 'puppy_kitten') {
            return $months < 12.0;
        }
        if ($filter === 'young') {
            return $months >= 12.0 && $months <= 24.0;
        }
        if ($filter === 'adult') {
            return $months >= 24.0;
        }
        return false;
    }

    private static function classifyYears(float $years, string $filter): bool
    {
        if ($filter === 'puppy_kitten') {
            return $years < 1.0;
        }
        if ($filter === 'young') {
            return $years >= 1.0 && $years <= 2.0;
        }
        if ($filter === 'adult') {
            return $years >= 2.0;
        }
        return false;
    }
}
