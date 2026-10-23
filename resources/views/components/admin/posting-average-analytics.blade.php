@props(['postingAnalytics'])

@php
    $period       = $postingAnalytics['period'];
    $isCalendar   = ($period === 'calendar');
    $calGrid      = $postingAnalytics['calendar_grid'] ?? null;
@endphp

<div class="analytics-card average-posts-card" id="posting-analytics-card">
    {{-- Card Header: Title & Subtitle on Left, Filter Tabs on Right --}}
    <div class="analytics-card-header minimal-card-header">
        <div class="analytics-title-group">
            <h2 class="minimal-card-title">Average posts</h2>
            @if($isCalendar && $calGrid)
                <p class="minimal-card-subtitle">Posts per day in {{ $calGrid['month_label'] }}</p>
            @else
                <p class="minimal-card-subtitle">{{ $postingAnalytics['avg_posts_per_day'] }} posts per day on average</p>
            @endif
        </div>

        {{-- Filter Tabs (Pill Container) --}}
        <div class="filter-pills-container" role="tablist" aria-label="Time Filter Buttons">
            <button
                type="button"
                class="filter-pill-btn {{ $period === '7d' ? 'active' : '' }}"
                onclick="window.switchPostingPeriod('7d')"
                data-period="7d"
            >7 days</button>
            <button
                type="button"
                class="filter-pill-btn {{ $period === '1m' ? 'active' : '' }}"
                onclick="window.switchPostingPeriod('1m')"
                data-period="1m"
            >1 month</button>
            <button
                type="button"
                class="filter-pill-btn {{ $period === '1y' ? 'active' : '' }}"
                onclick="window.switchPostingPeriod('1y')"
                data-period="1y"
            >1 year</button>
            <button
                type="button"
                class="filter-pill-btn {{ $isCalendar ? 'active' : '' }}"
                onclick="window.switchPostingPeriod('calendar')"
                data-period="calendar"
                id="btn-filter-custom"
            >Calendar</button>
        </div>
    </div>

    {{-- CALENDAR VIEW (when Calendar tab is active) --}}
    @if($isCalendar && $calGrid)
        <div class="analytics-card-body cal-view-body">
            {{-- Month navigation --}}
            <div class="cal-month-nav">
                @php
                    $calCarbon = \Carbon\Carbon::parse($calGrid['cal_month'] . '-01');
                    $prevMonth = $calCarbon->copy()->subMonth()->format('Y-m');
                    $nextMonth = $calCarbon->copy()->addMonth()->format('Y-m');
                    $isFuture  = $calCarbon->copy()->addMonth()->startOfMonth()->gt(\Carbon\Carbon::now()->endOfMonth());
                @endphp
                <button class="cal-nav-btn" onclick="window.switchCalMonth('{{ $prevMonth }}')">
                    <x-lucide-chevron-left style="width:16px;height:16px;" />
                </button>
                <span class="cal-month-title">{{ $calGrid['month_label'] }}</span>
                <button class="cal-nav-btn{{ $isFuture ? ' cal-nav-disabled' : '' }}"
                    {{ $isFuture ? 'disabled' : '' }}
                    onclick="window.switchCalMonth('{{ $nextMonth }}')">
                    <x-lucide-chevron-right style="width:16px;height:16px;" />
                </button>
            </div>

            {{-- Day-of-week headers: Su Mo Tu We Th Fr Sa --}}
            <div class="cal-grid-wrap">
                <div class="cal-dow-row">
                    @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $dow)
                        <div class="cal-dow-cell">{{ $dow }}</div>
                    @endforeach
                </div>

                {{-- Calendar weeks --}}
                @foreach($calGrid['weeks'] as $week)
                    <div class="cal-week-row">
                        @foreach($week as $day)
                            @if($day === null)
                                <div class="cal-day-cell cal-day-empty"></div>
                            @else
                                <div class="cal-day-cell cal-level-{{ $day['level'] }}"
                                     title="{{ $day['date'] }}: {{ $day['count'] }} post{{ $day['count'] == 1 ? '' : 's' }}">
                                    <span class="cal-day-num">{{ $day['day'] }}</span>
                                    @if($day['count'] > 0)
                                        <span class="cal-day-count">{{ $day['count'] }}</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>

            <p class="cal-legend-note">{{ $calGrid['month_label'] }} · darker means more posts</p>
        </div>

    {{-- BAR CHART VIEW (default) --}}
    @else
        <div class="analytics-card-body minimal-chart-body">
            @php
                $points     = $postingAnalytics['chart_points'];
                $pointCount = count($points);
                $chartMax   = max(1, $postingAnalytics['chart_max']);
                $avgVal     = (float) $postingAnalytics['avg_posts_per_day'];
                $svgWidth   = 680;
                $svgHeight  = 250;
                $padLeft    = 20;
                $padRight   = 75;
                $padTop     = 38;
                $padBottom  = 35;
                $plotWidth  = $svgWidth - $padLeft - $padRight;
                $plotHeight = $svgHeight - $padTop - $padBottom;
                $colSlot    = $pointCount > 0 ? $plotWidth / $pointCount : $plotWidth;
                $barWidth   = max(12, min(54, $colSlot * 0.65));
                $avgY       = $plotHeight > 0 && $chartMax > 0
                    ? $padTop + $plotHeight - (($avgVal / $chartMax) * $plotHeight)
                    : $padTop + $plotHeight;
                $avgY       = max($padTop + 10, min($padTop + $plotHeight - 10, $avgY));
            @endphp

            <div class="minimal-bar-chart-wrap">
                <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}"
                     class="minimal-bars-svg"
                     preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="barGradOrange" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#E87948" />
                            <stop offset="100%" stop-color="#DE6B35" />
                        </linearGradient>
                    </defs>

                    {{-- Baseline --}}
                    <line x1="{{ $padLeft }}" y1="{{ $padTop + $plotHeight }}"
                          x2="{{ $svgWidth - 10 }}" y2="{{ $padTop + $plotHeight }}"
                          stroke="#F3F4F6" stroke-width="1.5" />

                    {{-- Average dashed line + pill --}}
                    <line x1="{{ $padLeft }}" y1="{{ $avgY }}"
                          x2="{{ $svgWidth - 65 }}" y2="{{ $avgY }}"
                          stroke="#9CA3AF" stroke-width="1.5" stroke-dasharray="4 4" />
                    <rect x="{{ $svgWidth - 60 }}" y="{{ $avgY - 11 }}"
                          width="54" height="22" rx="11" fill="#9CA3AF" />
                    <text x="{{ $svgWidth - 33 }}" y="{{ $avgY + 4 }}"
                          text-anchor="middle" font-size="11" font-weight="700"
                          fill="#FFFFFF" font-family="'Outfit', sans-serif">avg {{ $avgVal }}</text>

                    {{-- Chart Columns --}}
                    @foreach($points as $idx => $pt)
                        @php
                            $colCenterX = $padLeft + ($idx * $colSlot) + ($colSlot / 2);
                            $postVal    = $pt['total_posts'];
                            $isMax      = ($postVal > 0 && $postVal >= $chartMax);
                            $barFill    = $isMax ? '#4A2E2B' : '#E87948';
                            $postH      = $chartMax > 0 ? ($postVal / $chartMax) * $plotHeight : 0;
                            $effectiveH = $postVal > 0 ? max(16, $postH) : 0;
                            $barY       = $padTop + $plotHeight - $effectiveH;
                            $barX       = $colCenterX - ($barWidth / 2);
                            $showLabel  = true;
                            if ($pointCount > 15 && ($idx % 3 !== 0 && $idx !== $pointCount - 1)) {
                                $showLabel = false;
                            }
                        @endphp
                        <g class="minimal-col-group" tabindex="0"
                           data-date="{{ $pt['label'] }}" data-count="{{ $postVal }}"
                           onmouseenter="window.showChartTooltip(event, this)"
                           onmouseleave="window.hideChartTooltip()">
                            <rect x="{{ $colCenterX - ($colSlot / 2) }}" y="{{ $padTop }}"
                                  width="{{ $colSlot }}" height="{{ $plotHeight }}"
                                  class="col-hit-area" />
                            @if($postVal > 0)
                                <rect x="{{ $barX }}" y="{{ $barY }}"
                                      width="{{ $barWidth }}" height="{{ $effectiveH }}"
                                      rx="8" ry="8" fill="{{ $barFill }}"
                                      class="minimal-bar-rect" />
                                <text x="{{ $colCenterX }}" y="{{ $barY - 7 }}"
                                      font-size="13" font-weight="800" fill="#4A2E2B"
                                      font-family="'Outfit', sans-serif"
                                      text-anchor="middle">{{ $postVal }}</text>
                            @else
                                <rect x="{{ $colCenterX - 14 }}" y="{{ $padTop + $plotHeight - 4 }}"
                                      width="28" height="4" rx="2" fill="#E5E7EB" />
                            @endif
                            @if($showLabel)
                                <text x="{{ $colCenterX }}" y="{{ $svgHeight - 10 }}"
                                      class="minimal-x-axis-label"
                                      text-anchor="middle">{{ $pt['short_label'] }}</text>
                            @endif
                        </g>
                    @endforeach
                </svg>

                {{-- Floating Tooltip --}}
                <div id="chart-floating-tooltip" class="chart-floating-tooltip" style="display: none;">
                    <div class="tt-date-text" id="tt-date"></div>
                    <div class="tt-count-text"><strong id="tt-count">0</strong> posts</div>
                </div>
            </div>
        </div>
    @endif
</div>
