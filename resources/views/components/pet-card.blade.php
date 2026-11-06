@props([
    'pet',
])

@php
    $statusLower = strtolower($pet->status ?? 'available');
    $isAvailable = $statusLower === 'available';
    $isPending   = $statusLower === 'pending';
    $isAdopted   = $statusLower === 'adopted';
    $isCat       = strtolower($pet->species ?? 'dog') === 'cat';
    $isVerified  = (bool) (($pet->is_verified ?? false) || ($pet->user?->is_verified ?? false));

    // Cycle through cheerful pastel tint backgrounds matching the mockup reference design:
    // 1: warm peach/sand, 2: soft periwinkle/lavender, 3: soft mint/sage, 4: soft apricot/melon
    $bgPastels = ['#FDE7D3', '#E0E7FF', '#DCFCE7', '#FDE8D0'];
    $pastelBg = $bgPastels[$pet->id % 4] ?? '#FDE7D3';
@endphp

<div class="pet-card" id="pet-card-{{ $pet->id }}">
    <div class="pet-image-container" style="background-color: {{ $pastelBg }}; cursor: pointer;" onclick="openPetModal({{ $pet->id }})" role="button" tabindex="0" title="Click to view {{ $pet->name }} details">
        @php
            $imageUrls = $pet->image_urls;
            $photoCount = count($imageUrls);
        @endphp
        @if(!empty($pet->image_path) && $photoCount > 0)
            <div class="pet-card-gallery-wrap" id="card-gallery-{{ $pet->id }}">
                @foreach($imageUrls as $imgIndex => $imgUrl)
                    <img src="{{ $imgUrl }}" 
                         alt="{{ $pet->name }} - Photo {{ $imgIndex + 1 }}" 
                         class="pet-image pet-card-slide pet-card-slide-{{ $pet->id }}" 
                         loading="lazy" 
                         data-index="{{ $imgIndex }}"
                         style="{{ $imgIndex === 0 ? '' : 'display: none;' }}"
                         onerror="this.onerror=null; this.src='{{ $isCat ? 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80' : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80' }}';">
                @endforeach
            </div>
        @else
            <div class="pet-image-placeholder" style="background-color: {{ $pastelBg }};">
                <x-lucide-paw-print style="width: 52px; height: 52px; stroke-width: 1.5; color: #4A2E2B; opacity: 0.35;" />
            </div>
        @endif

        {{-- Verified badge overlaid on top left if pet post or poster account is verified --}}
        @if($isVerified)
            <div class="pet-verified-badge" title="Verified">
                <span class="verified-check" aria-hidden="true">✓</span>
                <span class="verified-text">Verified</span>
            </div>
        @endif

        {{-- Status badge overlaid on top right --}}
        <div class="pet-status-pill status-{{ $statusLower }}">
            <span class="status-dot {{ $isAvailable ? 'dot-available' : ($isPending ? 'dot-pending' : 'dot-adopted') }}"></span>
            <span class="status-text">{{ ucfirst($pet->status ?? 'Available') }}</span>
        </div>

        {{-- Multi-photo indicator badge (+N extra photos) & Carousel pagination dots --}}
        @if($photoCount > 1)
            <div class="pet-photos-badge" title="{{ $photoCount }} photos available">
                <x-lucide-camera style="width: 12px; height: 12px; stroke-width: 2.2;" />
                <span>+{{ $photoCount - 1 }}</span>
            </div>
            <div class="pet-card-carousel-dots" onclick="event.stopPropagation();" aria-label="Photo pagination">
                @foreach($imageUrls as $dotIndex => $dotUrl)
                    <button type="button" 
                            class="card-dot-indicator card-dot-{{ $pet->id }} {{ $dotIndex === 0 ? 'active' : '' }}" 
                            onclick="switchCardPhoto(event, {{ $pet->id }}, {{ $dotIndex }})"
                            aria-label="View photo {{ $dotIndex + 1 }}"></button>
                @endforeach
            </div>
        @endif
    </div>

    <div class="pet-card-body">
        <div class="pet-header">
            <h3 class="pet-name">{{ $pet->name }}</h3>
            <span class="pet-species-pill">{{ ucfirst($pet->species ?? ($isCat ? 'Cat' : 'Dog')) }}</span>
        </div>

        <div class="pet-details-pills">
            <span class="pet-detail-pill">{{ !empty($pet->breed) ? $pet->breed : 'Mixed Breed' }}</span>
            <span class="pet-detail-pill">{{ !empty($pet->age) ? $pet->age : 'Young' }}</span>
            <span class="pet-detail-pill">{{ !empty($pet->gender) ? ucfirst($pet->gender) : 'Unknown' }}</span>
        </div>

        <div class="pet-meta-row">
            <p class="pet-location">
                <x-lucide-map-pin class="location-pin-icon" />
                <span>{{ !empty($pet->location) ? $pet->location : 'Brgy. 176-F' }}</span>
            </p>
            @php
                $posterName = $pet->posted_by ?? $pet->user?->name;
            @endphp
            @if(!empty($posterName))
                <p class="pet-poster-tag">
                    <span class="poster-label">By</span>
                    <span class="poster-name">{{ $posterName }}</span>
                </p>
            @endif
        </div>

        <p class="pet-description">
            {{ !empty($pet->description) ? $pet->description : 'Loving rescued pet looking for a home.' }}
        </p>

        <div class="pet-card-footer">
            <button 
                type="button" 
                class="btn-pet-meet"
                onclick="openPetModal({{ $pet->id }})"
                aria-haspopup="dialog"
                aria-controls="pet-details-modal"
            >
                Meet {{ $pet->name }}
            </button>
        </div>
    </div>
</div>
