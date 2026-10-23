<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\AdoptionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin Dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        // Live database metrics for admin dashboard overview
        $stats = [
            'total_animals'     => Pet::count(),
            'pending_requests'  => AdoptionRequest::where('status', 'Pending')->count(),
            'approved_requests' => AdoptionRequest::where('status', 'Accepted')->count(),
            'total_users'       => User::count(),
            'posts_this_week'   => Pet::where('created_at', '>=', now()->subDays(7))->count(),
            'users_this_month'  => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        // 1. Total Posts Counter & Cats vs. Dogs Breakdown Analytics
        $postAnalytics = $this->calculatePostSpeciesAnalytics();

        // 2. Posting Average Analytics with Date Filters
        $postingAnalytics = $this->calculatePostingAverageAnalytics($request);

        // 3. Needs your attention metrics
        $attention = $this->getAttentionItems($stats);

        // 4. Recent activity timeline
        $recentActivities = $this->getRecentActivities();

        // AJAX response for seamless dynamic date filter switching without full-page reloads
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $postingAnalytics,
                'html'    => view('components.admin.posting-average-analytics', compact('postingAnalytics'))->render(),
            ]);
        }

        return view('pages.admin.dashboard', compact('stats', 'postAnalytics', 'postingAnalytics', 'attention', 'recentActivities'));
    }

    /**
     * Attention items waiting for admin — real counts only, no fallbacks.
     */
    protected function getAttentionItems(array $stats): array
    {
        $postsToVerify   = Pet::whereIn('status', ['Pending', 'Draft', 'Under Review'])->count();
        $pendingRequests = $stats['pending_requests'];

        return [
            'posts_to_verify'  => $postsToVerify,
            'pending_requests' => $pendingRequests,
        ];
    }

    /**
     * Recent platform activity timeline — real DB events only, no dummy fallbacks.
     * Excludes the admin account from the "created an account" event to avoid noise on a clean DB.
     */
    protected function getRecentActivities(): array
    {
        $activities = [];

        // 1. Recent pets posted
        $recentPets = Pet::with('user')->latest()->take(5)->get();
        foreach ($recentPets as $pet) {
            $poster = $pet->posted_by ?? ($pet->user->name ?? 'A user');
            $activities[] = [
                'dot'       => 'orange',
                'title'     => $pet->name . ' was posted by ' . $poster,
                'timestamp' => $this->formatTimelineTime($pet->created_at),
                'raw_time'  => $pet->created_at,
            ];
        }

        // 2. Recent adoption requests
        $recentRequests = AdoptionRequest::with('pet')->latest()->take(5)->get();
        foreach ($recentRequests as $req) {
            $petName = $req->pet->name ?? 'A rescue pet';
            $activities[] = [
                'dot'       => 'orange',
                'title'     => $petName . ' received an adoption request',
                'timestamp' => $this->formatTimelineTime($req->created_at),
                'raw_time'  => $req->created_at,
            ];
        }

        // 3. Recent non-admin user registrations
        $recentUsers = User::where('is_admin', false)->latest()->take(5)->get();
        foreach ($recentUsers as $user) {
            $activities[] = [
                'dot'       => 'green',
                'title'     => $user->name . ' created an account',
                'timestamp' => $this->formatTimelineTime($user->created_at),
                'raw_time'  => $user->created_at,
            ];
        }

        // 4. Recently adopted or archived pets
        $archivedPets = Pet::whereIn('status', ['Adopted', 'Archived'])->latest('updated_at')->take(5)->get();
        foreach ($archivedPets as $pet) {
            $activities[] = [
                'dot'       => 'red',
                'title'     => $pet->name . ' was moved to archive',
                'timestamp' => $this->formatTimelineTime($pet->updated_at),
                'raw_time'  => $pet->updated_at,
            ];
        }

        // Sort all events newest-first and return up to 6
        usort($activities, fn($a, $b) => ($b['raw_time'] ?? null) <=> ($a['raw_time'] ?? null));

        return array_slice($activities, 0, 6);
    }

    protected function formatTimelineTime(?Carbon $date): string
    {
        if (!$date) return 'Recently';
        if ($date->isToday()) {
            return 'Today, ' . $date->format('g:i A');
        } elseif ($date->isYesterday()) {
            return 'Yesterday, ' . $date->format('g:i A');
        } else {
            return $date->format('M j, g:i A');
        }
    }

    /**
     * Calculate Total Posts and Cats vs. Dogs breakdown metrics.
     */
    protected function calculatePostSpeciesAnalytics(): array
    {
        $total = Pet::count();
        $dogs = Pet::whereRaw('LOWER(TRIM(species)) IN (?, ?)', ['dog', 'dogs'])->count();
        $cats = Pet::whereRaw('LOWER(TRIM(species)) IN (?, ?)', ['cat', 'cats'])->count();
        $other = max(0, $total - ($dogs + $cats));

        $dogPercent = $total > 0 ? round(($dogs / $total) * 100, 1) : 0;
        $catPercent = $total > 0 ? round(($cats / $total) * 100, 1) : 0;
        $otherPercent = $total > 0 ? round(($other / $total) * 100, 1) : 0;

        $statusCounts = [
            'available' => Pet::whereRaw('LOWER(status) = ?', ['available'])->count(),
            'adopted'   => Pet::whereRaw('LOWER(status) = ?', ['adopted'])->count(),
            'pending'   => Pet::whereRaw('LOWER(status) = ?', ['pending'])->count(),
            'archived'  => Pet::whereRaw('LOWER(status) in (?, ?)', ['archived', 'pending'])->count(),
        ];

        return [
            'total'         => $total,
            'dogs'          => $dogs,
            'cats'          => $cats,
            'other'         => $other,
            'dog_percent'   => $dogPercent,
            'cat_percent'   => $catPercent,
            'other_percent' => $otherPercent,
            'status_counts' => $statusCounts,
        ];
    }

    /**
     * Calculate Posting Average Analytics based on selected date filter (7 Days, 1 Month, 1 Year, Custom).
     */
    protected function calculatePostingAverageAnalytics(Request $request): array
    {
        $period = $request->input('period', '7d');
        $fromInput = $request->input('from');
        $toInput = $request->input('to');

        if ($period === '1m') {
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $periodLabel = '1 month';
        } elseif ($period === '1y') {
            $startDate = Carbon::now()->subYear()->addDay()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $periodLabel = '1 year';
        } elseif ($period === 'calendar') {
            // Calendar view: show the current calendar month
            $calMonth = $request->input('cal_month'); // e.g. "2026-10"
            try {
                $calDate  = $calMonth ? Carbon::parse($calMonth . '-01') : Carbon::now()->startOfMonth();
            } catch (\Exception $e) {
                $calDate  = Carbon::now()->startOfMonth();
            }
            $startDate   = $calDate->copy()->startOfMonth()->startOfDay();
            $endDate     = $calDate->copy()->endOfMonth()->endOfDay();
            $periodLabel = 'Calendar';
        } elseif ($period === 'custom' && $fromInput && $toInput) {
            try {
                $startDate = Carbon::parse($fromInput)->startOfDay();
                $endDate = Carbon::parse($toInput)->endOfDay();
                if ($startDate->gt($endDate)) {
                    $temp = $startDate;
                    $startDate = $endDate->copy()->startOfDay();
                    $endDate = $temp->copy()->endOfDay();
                }
                $period = 'custom';
                $periodLabel = 'Calendar (' . $startDate->format('M d, Y') . ' – ' . $endDate->format('M d, Y') . ')';
            } catch (\Exception $e) {
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $period = '7d';
                $periodLabel = '7 days';
            }
        } else {
            $period = '7d';
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $periodLabel = '7 days';
        }

        $daysCount = max(1, (int)$startDate->diffInDays($endDate) + 1);

        // Retrieve pets created in range
        $pets = Pet::whereBetween('created_at', [$startDate, $endDate])
            ->get(['id', 'user_id', 'created_at']);

        $totalPeriodPosts = $pets->count();
        $uniquePosters = $pets->pluck('user_id')->filter()->unique();
        $uniquePostersCount = $uniquePosters->count();

        // Calculate key posting average metrics
        $avgActiveUsersPerDay = round($uniquePostersCount / $daysCount, 2);
        $avgPostsPerUser = $uniquePostersCount > 0 ? round($totalPeriodPosts / $uniquePostersCount, 1) : 0;

        // Contextual user metrics
        $totalRegisteredUsers = User::count();
        $activeUserRatio = $totalRegisteredUsers > 0 ? round(($uniquePostersCount / $totalRegisteredUsers) * 100, 1) : 0;

        // Generate chronological chart time-series data
        $chartPoints = $this->buildChartPoints($pets, $startDate, $endDate, $period, $daysCount);

        // For calendar period, also build a calendar grid
        $calendarGrid = null;
        if ($period === 'calendar') {
            $calendarGrid = $this->buildCalendarGrid($pets, $startDate);
        }

        // Calculate max scale for SVG chart bars
        $maxPointValue = 1;
        foreach ($chartPoints as $pt) {
            if ($pt['active_users'] > $maxPointValue) {
                $maxPointValue = $pt['active_users'];
            }
            if ($pt['total_posts'] > $maxPointValue) {
                $maxPointValue = $pt['total_posts'];
            }
        }

        return [
            'period'               => $period,
            'period_label'         => $periodLabel,
            'start_date'           => $startDate->format('Y-m-d'),
            'end_date'             => $endDate->format('Y-m-d'),
            'cal_month'            => $startDate->format('Y-m'),
            'cal_month_label'      => $startDate->format('F Y'),
            'days_count'           => $daysCount,
            'unique_posters_count' => $uniquePostersCount,
            'total_period_posts'   => $totalPeriodPosts,
            'avg_posts_per_day'    => $daysCount > 0 ? round($totalPeriodPosts / $daysCount, 1) : 0,
            'avg_active_users'     => $avgActiveUsersPerDay,
            'avg_posts_per_user'   => $avgPostsPerUser,
            'active_user_ratio'    => $activeUserRatio,
            'total_users'          => $totalRegisteredUsers,
            'chart_points'         => $chartPoints,
            'chart_max'            => $maxPointValue,
            'calendar_grid'        => $calendarGrid,
        ];
    }

    /**
     * Build chronological chart data points based on date span.
     */
    protected function buildChartPoints($pets, Carbon $startDate, Carbon $endDate, string $period, int $daysCount): array
    {
        $points = [];

        if ($period === '1y' || $daysCount > 90) {
            // Group by Month (up to 12 months)
            $cursor = $startDate->copy()->startOfMonth();
            while ($cursor->lte($endDate)) {
                $monthKey = $cursor->format('Y-m');
                $monthPets = $pets->filter(function ($p) use ($monthKey) {
                    return $p->created_at->format('Y-m') === $monthKey;
                });

                $points[] = [
                    'label'        => $cursor->format('M Y'),
                    'short_label'  => $cursor->format('M'),
                    'active_users' => $monthPets->pluck('user_id')->filter()->unique()->count(),
                    'total_posts'  => $monthPets->count(),
                ];

                $cursor->addMonth();
            }
        } elseif ($daysCount > 35) {
            // Group by 5-day intervals
            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                $bucketEnd = $cursor->copy()->addDays(4)->endOfDay();
                if ($bucketEnd->gt($endDate)) {
                    $bucketEnd = $endDate->copy();
                }

                $bStart = $cursor->copy();
                $bucketPets = $pets->filter(function ($p) use ($bStart, $bucketEnd) {
                    return $p->created_at->between($bStart, $bucketEnd);
                });

                $points[] = [
                    'label'        => $bStart->format('M j') . ' - ' . $bucketEnd->format('M j'),
                    'short_label'  => $bStart->format('M j'),
                    'active_users' => $bucketPets->pluck('user_id')->filter()->unique()->count(),
                    'total_posts'  => $bucketPets->count(),
                ];

                $cursor->addDays(5);
            }
        } else {
            // Daily points for 7d, 1m (30 days), or custom range <= 35 days
            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                $dateKey = $cursor->format('Y-m-d');
                $dayPets = $pets->filter(function ($p) use ($dateKey) {
                    return $p->created_at->format('Y-m-d') === $dateKey;
                });

                $points[] = [
                    'label'        => $cursor->format('D, M j, Y'),
                    'day_name'     => $cursor->format('D'),
                    'short_label'  => $daysCount <= 7 ? $cursor->format('D') : $cursor->format('M j'),
                    'active_users' => $dayPets->pluck('user_id')->filter()->unique()->count(),
                    'total_posts'  => $dayPets->count(),
                ];

                $cursor->addDay();
            }
        }

        return $points;
    }

    /**
     * Build a calendar grid (Sun–Sat weeks) for the given month,
     * with each day's post count.
     */
    protected function buildCalendarGrid($pets, Carbon $startDate): array
    {
        $monthStart   = $startDate->copy()->startOfMonth();
        $monthEnd     = $startDate->copy()->endOfMonth();
        $daysInMonth  = $monthEnd->day;

        // Index posts by date string
        $byDate = [];
        foreach ($pets as $pet) {
            $key = $pet->created_at->format('Y-m-d');
            $byDate[$key] = ($byDate[$key] ?? 0) + 1;
        }

        $maxCount = max(1, count($byDate) > 0 ? max($byDate) : 1);

        // Build flat day list; pad front to start on correct weekday (0=Sun)
        $startDow = (int) $monthStart->copy()->dayOfWeek; // 0=Sun
        $days     = [];

        // Leading empty cells
        for ($i = 0; $i < $startDow; $i++) {
            $days[] = null;
        }

        // Day cells
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date       = $monthStart->copy()->addDays($d - 1);
            $dateStr    = $date->format('Y-m-d');
            $count      = $byDate[$dateStr] ?? 0;
            $days[]     = [
                'day'    => $d,
                'date'   => $dateStr,
                'count'  => $count,
                // intensity 0–4 for colour shading
                'level'  => $count === 0 ? 0 : (int) ceil(($count / $maxCount) * 4),
            ];
        }

        // Trailing empty cells to complete last row
        $trailing = (7 - (count($days) % 7)) % 7;
        for ($i = 0; $i < $trailing; $i++) {
            $days[] = null;
        }

        return [
            'weeks'       => array_chunk($days, 7),
            'month_label' => $monthStart->format('F Y'),
            'max_count'   => $maxCount,
            'cal_month'   => $monthStart->format('Y-m'),
        ];
    }
}
