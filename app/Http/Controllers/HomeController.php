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
            }
        }

        // 4. Gender filter
        if ($request->filled('gender') && in_array(strtolower($request->gender), ['male', 'female'])) {
            $query->where('gender', ucfirst(strtolower($request->gender)));
        }

        // 5. Status filter (default: Available, or 'all', or specific status)
        if ($request->filled('status')) {
            if ($request->status !== 'all') {
                $query->where('status', ucfirst(strtolower($request->status)));
            }
        } else {
            // Default to Available pets
            $query->where('status', 'Available');
        }

        // Fetch pets ordered with newest first
        $pets = $query->latest()->get();

        return view('pages.home', compact('pets'));
    }
}
