@props(['postAnalytics'])

<div class="analytics-card dogs-cats-side-card">
    {{-- Top Section: "Dogs vs. cats" --}}
    <div class="species-top-section">
        <div class="species-header-group">
            <h2 class="minimal-card-title">Dogs vs. cats</h2>
            <p class="minimal-card-subtitle">Share of all posts</p>
        </div>

        {{-- Centered Donut Chart with Center Text & Legend Below (Matching Screenshot) --}}
        @php
            $radius = 52;
            $circumference = 2 * M_PI * $radius; // ~326.7
            $total = $postAnalytics['total'];
            $dogPct = $postAnalytics['dog_percent'];
            $catPct = $postAnalytics['cat_percent'];
            $otherPct = $postAnalytics['other_percent'];

            $dogDash = ($dogPct / 100) * $circumference;
            $catDash = ($catPct / 100) * $circumference;
            $otherDash = ($otherPct / 100) * $circumference;

            // In reference screenshot, Cats (orange) is on the left, Dogs (dark brown) on the right, Others (gray) after
            $catOffset = 0;
            $dogOffset = -$catDash;
            $otherOffset = -($catDash + $dogDash);

            // By status calculations
            $availCount = $postAnalytics['status_counts']['available'] ?? 0;
            $adoptedCount = $postAnalytics['status_counts']['adopted'] ?? 0;
            $pendingCount = $postAnalytics['status_counts']['pending'] ?? 0;
            $statusTotal = max(1, $availCount + $adoptedCount + $pendingCount);

            $availWidth = round(($availCount / $statusTotal) * 100, 1);
            $adoptedWidth = round(($adoptedCount / $statusTotal) * 100, 1);
            $pendingWidth = round(($pendingCount / $statusTotal) * 100, 1);
        @endphp

        <div class="donut-centered-container">
            <div class="donut-svg-wrapper">
                <svg viewBox="0 0 140 140" class="donut-svg" role="img" aria-label="Dogs vs cats donut breakdown">
                    {{-- Base Track --}}
                    <circle
                        cx="70" cy="70" r="{{ $radius }}"
                        class="donut-track-base"
                    />

                    @if($total > 0)
                        {{-- Cats (Orange: #E87948) --}}
                        @if($catPct > 0)
                        <circle
                            cx="70" cy="70" r="{{ $radius }}"
                            class="donut-segment-circle seg-cats"
                            stroke-dasharray="{{ $catDash }} {{ $circumference }}"
                            stroke-dashoffset="{{ $catOffset }}"
                        />
                        @endif

                        {{-- Dogs (Dark Brown: #4A2E2B) --}}
                        @if($dogPct > 0)
                        <circle
                            cx="70" cy="70" r="{{ $radius }}"
                            class="donut-segment-circle seg-dogs"
                            stroke-dasharray="{{ $dogDash }} {{ $circumference }}"
                            stroke-dashoffset="{{ $dogOffset }}"
                        />
                        @endif

                        {{-- Others (Neutral Slate/Warm Gray: #9CA3AF) --}}
                        @if($otherPct > 0)
                        <circle
                            cx="70" cy="70" r="{{ $radius }}"
                            class="donut-segment-circle seg-other"
                            stroke-dasharray="{{ $otherDash }} {{ $circumference }}"
                            stroke-dashoffset="{{ $otherOffset }}"
                        />
                        @endif
                    @endif
                </svg>

                {{-- Center Text inside Donut --}}
                <div class="donut-center-overlay">
                    <span class="donut-center-number">{{ $total }}</span>
                    <span class="donut-center-label">posts</span>
                </div>
            </div>

            {{-- Centered Legend Below Donut --}}
            <div class="donut-bottom-legend">
                <div class="legend-chip">
                    <span class="legend-dot dot-dog"></span>
                    <span class="legend-name">Dogs · {{ $postAnalytics['dogs'] }}</span>
                </div>
                <div class="legend-chip">
                    <span class="legend-dot dot-cat"></span>
                    <span class="legend-name">Cats · {{ $postAnalytics['cats'] }}</span>
                </div>
                <div class="legend-chip">
                    <span class="legend-dot dot-other"></span>
                    <span class="legend-name">Others · {{ $postAnalytics['other'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Section: "By status" (Matching Screenshot) --}}
    <div class="species-bottom-section">
        <h3 class="by-status-heading">By status</h3>

        {{-- Horizontal Multi-Segment Progress Bar --}}
        <div class="status-multi-bar">
            @if($availCount > 0)
                <div class="multi-bar-seg seg-available" style="width: {{ $availWidth }}%;" title="Available: {{ $availCount }}"></div>
            @endif
            @if($adoptedCount > 0)
                <div class="multi-bar-seg seg-adopted" style="width: {{ $adoptedWidth }}%;" title="Adopted: {{ $adoptedCount }}"></div>
            @endif
            @if($pendingCount > 0)
                <div class="multi-bar-seg seg-pending" style="width: {{ $pendingWidth }}%;" title="Pending: {{ $pendingCount }}"></div>
            @endif
        </div>

        {{-- Clean Status Count Rows --}}
        <div class="status-rows-list">
            {{-- Available Row --}}
            <div class="status-data-row">
                <div class="status-row-label">
                    <span class="status-sq-bullet sq-available"></span>
                    <span class="status-title-text">Available</span>
                </div>
                <span class="status-count-value">{{ $availCount }}</span>
            </div>

            {{-- Adopted Row --}}
            <div class="status-data-row">
                <div class="status-row-label">
                    <span class="status-sq-bullet sq-adopted"></span>
                    <span class="status-title-text">Adopted</span>
                </div>
                <span class="status-count-value">{{ $adoptedCount }}</span>
            </div>

            {{-- Pending Row --}}
            <div class="status-data-row">
                <div class="status-row-label">
                    <span class="status-sq-bullet sq-pending"></span>
                    <span class="status-title-text">Pending</span>
                </div>
                <span class="status-count-value">{{ $pendingCount }}</span>
            </div>
        </div>
    </div>
</div>
