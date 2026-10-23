@props([
    'title',
    'value',
    'color' => 'default',
    'highlighted' => false,
    'badge' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'stat-card ' . ($highlighted ? 'stat-card-highlighted' : 'stat-card-white')]) }}>
    <div class="stat-card-inner">
        <div class="stat-info">
            <span class="stat-title">{{ $title }}</span>
            <div class="stat-value-row">
                <span class="stat-value">{{ $value }}</span>
                @if($badge)
                    {!! $badge !!}
                @endif
            </div>
            @if($subtitle)
                <span class="stat-subtitle-text">{{ $subtitle }}</span>
            @endif
        </div>
        @if(isset($icon))
            <div class="stat-icon-box {{ $highlighted ? 'stat-icon-highlighted' : 'stat-icon-' . $color }}">
                {{ $icon }}
            </div>
        @endif
    </div>
</div>
