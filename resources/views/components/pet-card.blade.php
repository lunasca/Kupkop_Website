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
                <x-lucide-paw-print style="width: 44px; height: 44px; stroke-width: 1.5; color: #8C756B; opacity: 0.6;" />
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
            <span class="pet-type-pill">{{ ucfirst($pet->species ?? ($isCat ? 'Cat' : 'Dog')) }}</span>
        </div>

        <div class="pet-details-pills">
            <span class="pet-detail-pill">{{ $pet->breed }}</span>
            <span class="pet-detail-pill">{{ $pet->age }}</span>
            <span class="pet-detail-pill">{{ $pet->gender }}</span>
        </div>

        @if(!empty($pet->location))
            <p class="pet-location" style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.8rem; color: #8C756B; margin: 0.4rem 0 0.5rem 0;">
                <x-lucide-map-pin style="width: 13px; height: 13px; stroke-width: 2; flex-shrink: 0; color: #D97706;" />
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $pet->location }}</span>
            </p>
        @endif

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
                Meet {{ $pet->name }}
            </button>
        </div>
    </div>
</div>
