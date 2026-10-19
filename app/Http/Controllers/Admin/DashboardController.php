<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin Dashboard.
     */
    public function index(): View
    {
        // Starter metrics for quick dashboard overview
        $stats = [
            'total_animals'    => 0,
            'pending_requests' => 0,
            'approved_requests'=> 0,
            'total_users'      => 0,
        ];

        return view('pages.admin.dashboard', compact('stats'));
    }
}
