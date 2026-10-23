@extends('layouts.admin')

@section('title', 'Admin Dashboard - ' . config('app.name', 'KUPKOP'))
@section('header', 'Dashboard')

@section('content')
<div class="admin-dashboard-container">
    {{-- Minimal Clean Page Header --}}
    <div class="dashboard-header-block">
        <h1 class="dashboard-heading">Dashboard</h1>
        <p class="dashboard-subheading">Overview of pets listed in Brgy. 171.</p>
    </div>

    {{-- TOP ROW: 4 Stat Cards Matching Reference Screenshot --}}
    <div class="admin-stats-row">
        {{-- Card 1: "Total posts" (Highlighted with soft yellow background) --}}
        <div class="stat-card-custom card-highlight-yellow">
            <div class="card-head-row">
                <span class="card-title-text">Total posts</span>
                <span class="card-head-icon icon-yellow-trans">
                    <x-lucide-paw-print style="width: 15px; height: 15px;" />
                </span>
            </div>
            <div class="stat-big-num">{{ $postAnalytics['total'] }}</div>
            <div class="stat-footer-pill pill-dark-brown">
                +{{ $stats['posts_this_week'] ?? 0 }} this week
            </div>
        </div>

        {{-- Card 2: "Dogs" (Clean white card) --}}
        <div class="stat-card-custom card-white">
            <div class="card-head-row">
                <span class="card-title-text">Dogs</span>
                <span class="card-head-icon icon-light-muted">
                    <x-lucide-paw-print style="width: 15px; height: 15px;" />
                </span>
            </div>
            <div class="stat-big-num">{{ $postAnalytics['dogs'] }}</div>
            <div class="stat-footer-pill pill-light-neutral">
                {{ $postAnalytics['dog_percent'] }}% of posts
            </div>
        </div>

        {{-- Card 3: "Cats" (Clean white card) --}}
        <div class="stat-card-custom card-white">
            <div class="card-head-row">
                <span class="card-title-text">Cats</span>
                <span class="card-head-icon icon-light-muted">
                    <x-lucide-paw-print style="width: 15px; height: 15px;" />
                </span>
            </div>
            <div class="stat-big-num">{{ $postAnalytics['cats'] }}</div>
            <div class="stat-footer-pill pill-light-neutral">
                {{ $postAnalytics['cat_percent'] }}% of posts
            </div>
        </div>

        {{-- Card 4: "Active accounts" (Clean white card) --}}
        <div class="stat-card-custom card-white">
            <div class="card-head-row">
                <span class="card-title-text">Active accounts</span>
                <span class="card-head-icon icon-light-muted">
                    <x-lucide-users style="width: 15px; height: 15px;" />
                </span>
            </div>
            <div class="stat-big-num">{{ $stats['total_users'] }}</div>
            <div class="stat-footer-pill pill-soft-green">
                +{{ $stats['users_this_month'] ?? 0 }} this month
            </div>
        </div>
    </div>

    {{-- MIDDLE GRID: Left ("Average posts") & Right ("Dogs vs. cats" & "By status") --}}
    <div class="admin-main-grid">
        {{-- Middle Left Card: Average posts --}}
        <div id="posting-analytics-wrapper" class="grid-col-left">
            <x-admin.posting-average-analytics :postingAnalytics="$postingAnalytics" />
        </div>

        {{-- Middle Right Card: Dogs vs. cats & By status --}}
        <div class="grid-col-right">
            <x-admin.species-chart :postAnalytics="$postAnalytics" />
        </div>
    </div>

    {{-- BOTTOM ROW: "Needs your attention" & "Recent activity" --}}
    <div class="admin-bottom-cards-grid">
        {{-- Left: Needs your attention --}}
        <div class="analytics-card attention-card">
            <div class="card-header-simple">
                <h2 class="section-title">Needs your attention</h2>
                <p class="section-subtitle">Things waiting for an admin</p>
            </div>

            <div class="attention-items-list">
                @if($attention['posts_to_verify'] === 0 && $attention['pending_requests'] === 0)
                    {{-- Empty state --}}
                    <div class="attention-empty-state">
                        <div class="attention-empty-icon">
                            <x-lucide-check-circle style="width: 28px; height: 28px;" />
                        </div>
                        <p class="attention-empty-title">All caught up!</p>
                        <p class="attention-empty-sub">No items need your attention right now.</p>
                    </div>
                @else
                    {{-- Posts to verify (only show if > 0) --}}
                    @if($attention['posts_to_verify'] > 0)
                        <div class="attention-row-item">
                            <div class="attention-icon-box box-amber">
                                <x-lucide-shield-check style="width: 17px; height: 17px;" />
                            </div>
                            <div class="attention-item-info">
                                <h4 class="attention-item-title">{{ $attention['posts_to_verify'] }} post{{ $attention['posts_to_verify'] == 1 ? '' : 's' }} to verify</h4>
                                <p class="attention-item-sub">Check the photo and location</p>
                            </div>
                            <a href="{{ route('home') }}" class="btn-attention-action">Review</a>
                        </div>
                    @endif

                    {{-- Adoption requests (only show if > 0) --}}
                    @if($attention['pending_requests'] > 0)
                        <div class="attention-row-item">
                            <div class="attention-icon-box box-yellow">
                                <x-lucide-heart style="width: 17px; height: 17px;" />
                            </div>
                            <div class="attention-item-info">
                                <h4 class="attention-item-title">{{ $attention['pending_requests'] }} adoption request{{ $attention['pending_requests'] == 1 ? '' : 's' }}</h4>
                                <p class="attention-item-sub">Waiting for the owner's reply</p>
                            </div>
                            <a href="{{ route('my-posts') }}" class="btn-attention-action">Open</a>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Right: Recent activity --}}
        <div class="analytics-card activity-card">
            <div class="card-header-simple">
                <h2 class="section-title">Recent activity</h2>
                <p class="section-subtitle">Latest on Kupkop</p>
            </div>

            <div class="activity-timeline-wrap">
                @forelse($recentActivities as $idx => $act)
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <span class="timeline-dot dot-{{ $act['dot'] ?? 'orange' }}"></span>
                            @if(!$loop->last)
                                <span class="timeline-line"></span>
                            @endif
                        </div>
                        <div class="timeline-content">
                            <p class="timeline-text">{{ $act['title'] }}</p>
                            <span class="timeline-time">{{ $act['timestamp'] }}</span>
                        </div>
                    </div>
                @empty
                    {{-- Empty state --}}
                    <div class="activity-empty-state">
                        <div class="activity-empty-icon">
                            <x-lucide-clock style="width: 28px; height: 28px;" />
                        </div>
                        <p class="activity-empty-title">No recent activity</p>
                        <p class="activity-empty-sub">Events will appear here once users start posting or requesting adoptions.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Global filter state switcher for Posting Average Analytics
    window.switchPostingPeriod = function(period) {
        const wrapper = document.getElementById('posting-analytics-wrapper');
        if (!wrapper) return;

        // Visual subtle loading state
        wrapper.style.opacity = '0.55';
        wrapper.style.pointerEvents = 'none';

        const url = new URL(window.location.href);
        url.searchParams.set('period', period);
        url.searchParams.delete('from');
        url.searchParams.delete('to');
        url.searchParams.delete('cal_month');

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.html) {
                wrapper.innerHTML = data.html;
                window.history.pushState(null, '', url.toString());
            }
        })
        .catch(err => {
            console.error('Error updating analytics period:', err);
            window.location.href = url.toString();
        })
        .finally(() => {
            wrapper.style.opacity = '1';
            wrapper.style.pointerEvents = 'auto';
        });
    };

    // Calendar month navigation — switches the calendar view to a specific YYYY-MM
    window.switchCalMonth = function(calMonth) {
        const wrapper = document.getElementById('posting-analytics-wrapper');
        if (!wrapper) return;

        wrapper.style.opacity = '0.55';
        wrapper.style.pointerEvents = 'none';

        const url = new URL(window.location.href);
        url.searchParams.set('period', 'calendar');
        url.searchParams.set('cal_month', calMonth);

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.html) {
                wrapper.innerHTML = data.html;
                window.history.pushState(null, '', url.toString());
            }
        })
        .catch(err => {
            console.error('Error navigating calendar month:', err);
            window.location.href = url.toString();
        })
        .finally(() => {
            wrapper.style.opacity = '1';
            wrapper.style.pointerEvents = 'auto';
        });
    };

    // Toggle Calendar Custom Date Range picker
    window.toggleCustomRangePicker = function() {
        const panel = document.getElementById('custom-range-panel');
        const btn = document.getElementById('btn-filter-custom');
        if (!panel) return;

        const isHidden = panel.style.display === 'none' || !panel.style.display;
        if (isHidden) {
            panel.style.display = 'flex';
            document.querySelectorAll('.filter-pill-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
        } else {
            panel.style.display = 'none';
        }
    };

    // Submit custom range form
    window.submitCustomRange = function(event) {
        event.preventDefault();
        const fromVal = document.getElementById('date-from').value;
        const toVal = document.getElementById('date-to').value;

        if (!fromVal || !toVal) return;

        const wrapper = document.getElementById('posting-analytics-wrapper');
        if (wrapper) {
            wrapper.style.opacity = '0.55';
            wrapper.style.pointerEvents = 'none';
        }

        const url = new URL(window.location.href);
        url.searchParams.set('period', 'custom');
        url.searchParams.set('from', fromVal);
        url.searchParams.set('to', toVal);

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.html) {
                wrapper.innerHTML = data.html;
                window.history.pushState(null, '', url.toString());
            }
        })
        .catch(err => {
            console.error('Error applying custom date range:', err);
            window.location.href = url.toString();
        })
        .finally(() => {
            if (wrapper) {
                wrapper.style.opacity = '1';
                wrapper.style.pointerEvents = 'auto';
            }
        });
    };

    // Chart Floating Tooltip Handlers
    window.showChartTooltip = function(event, element) {
        const tooltip = document.getElementById('chart-floating-tooltip');
        if (!tooltip) return;

        const date = element.getAttribute('data-date') || '';
        const count = element.getAttribute('data-count') || '0';

        document.getElementById('tt-date').textContent = date;
        document.getElementById('tt-count').textContent = count;

        tooltip.style.display = 'block';

        const rect = element.getBoundingClientRect();
        const parentRect = tooltip.parentElement.getBoundingClientRect();

        let left = rect.left - parentRect.left + (rect.width / 2) - 60;
        let top = rect.top - parentRect.top - 55;

        if (left < 10) left = 10;
        if (left + 120 > parentRect.width) left = parentRect.width - 130;
        if (top < 0) top = rect.bottom - parentRect.top + 10;

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    };

    window.hideChartTooltip = function() {
        const tooltip = document.getElementById('chart-floating-tooltip');
        if (tooltip) {
            tooltip.style.display = 'none';
        }
    };
</script>
@endpush
