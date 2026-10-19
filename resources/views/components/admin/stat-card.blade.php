@props(['title', 'value', 'icon' => '📈', 'color' => 'primary'])

<div class="stat-card stat-card-{{ $color }}">
    <div class="stat-icon">{{ $icon }}</div>
    <div class="stat-info">
        <span class="stat-title">{{ $title }}</span>
        <span class="stat-value">{{ $value }}</span>
    </div>
</div>
