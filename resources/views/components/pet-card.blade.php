@props([
    'name',
    'species' => 'Dog',
    'breed',
    'age',
    'gender',
    'description',
    'status' => 'Available',
    'image' => null,
])

@php
    $isAvailable = strtolower($status) === 'available';
    $isCat = strtolower($species) === 'cat';
@endphp

<div class="pet-card">
    <div class="pet-image-container">
        @if($image)
            <img src="{{ $image }}" alt="{{ $name }}" class="pet-image" loading="lazy">
        @else
            <div class="pet-image-placeholder">
                <span>{{ $isCat ? '🐱' : '🐶' }}</span>
            </div>
        @endif

        {{-- Status badge overlaid on top right --}}
        <span class="pet-status-badge {{ $isAvailable ? 'badge-available' : 'badge-pending' }}">
            {{ $status }}
        </span>
    </div>

    <div class="pet-card-body">
        <div class="pet-header">
            <h3 class="pet-name">{{ $name }}</h3>
            <span class="pet-type-pill" title="{{ $species }}">
                {{ $isCat ? '🐱 Cat' : '🐶 Dog' }}
            </span>
        </div>

        <p class="pet-details">
            {{ $breed }} • {{ $age }} • {{ $gender }}
        </p>

        <p class="pet-description">
            {{ $description }}
        </p>

        <div class="pet-card-footer">
            <a href="{{ route('home') }}#pet-{{ Str::slug($name) }}" class="btn-pet-details">
                View Details
            </a>
        </div>
    </div>
</div>
