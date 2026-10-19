@props([
    'pet',
])

@php
    $isAvailable = strtolower($pet->status ?? 'available') === 'available';
    $isPending = strtolower($pet->status ?? '') === 'pending';
    $isAdopted = strtolower($pet->status ?? '') === 'adopted';
    $isCat = strtolower($pet->species ?? 'dog') === 'cat';
@endphp

<div class="pet-card" id="pet-card-{{ $pet->id }}">
    <div class="pet-image-container">
        @if(!empty($pet->image_path))
            <img src="{{ $pet->image_url }}" alt="{{ $pet->name }}" class="pet-image" loading="lazy">
        @else
            <div class="pet-image-placeholder">
                <span>{{ $isCat ? '🐱' : '🐶' }}</span>
            </div>
        @endif

        {{-- Status badge overlaid on top right --}}
        <span class="pet-status-badge {{ $isAvailable ? 'badge-available' : ($isPending ? 'badge-pending' : 'badge-adopted') }}">
            {{ $pet->status }}
        </span>
    </div>

    <div class="pet-card-body">
        <div class="pet-header">
            <h3 class="pet-name">{{ $pet->name }}</h3>
            <span class="pet-type-pill" title="{{ $pet->species }}">
                {{ $isCat ? '🐱 Cat' : '🐶 Dog' }}
            </span>
        </div>

        <p class="pet-details">
            {{ $pet->breed }} • {{ $pet->age }} • {{ $pet->gender }}
        </p>

        <p class="pet-description">
            {{ Str::limit($pet->description, 95) }}
        </p>

        <div class="pet-card-footer">
            <button 
                type="button" 
                class="btn-pet-details"
                onclick="openPetModal({{ $pet->id }})"
                aria-haspopup="dialog"
                aria-controls="pet-details-modal"
            >
                View Details
            </button>
        </div>
    </div>
</div>
