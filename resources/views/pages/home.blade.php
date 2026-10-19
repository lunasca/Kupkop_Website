@extends('layouts.app')

@section('title', 'Kupkop - Adopt a rescued dog or cat in your barangay')

@section('content')
{{-- 1. Hero Section --}}
<section class="hero-section">
    <div class="site-container hero-content">
        {{-- Badge above title --}}
        <div class="hero-badge">
            <span class="badge-dot">🐾</span>
            COMMUNITY ANIMAL RESCUE &amp; CARE
        </div>

        {{-- Main Heading --}}
        <h1 class="hero-title">
            Adopt a rescued dog or cat in your barangay.
        </h1>

        {{-- Subtitle --}}
        <p class="hero-subtitle">
            Welcome to Kupkop! A simple student project portal connecting kind neighbors with rescued aspins, puspins, and community pets ready for loving permanent homes.
        </p>

        {{-- 2. Search & Filter Bar --}}
        <div class="search-filter-card">
            <form action="{{ route('home') }}" method="GET" class="filter-form">
                {{-- Search Input with Search Icon --}}
                <div class="filter-group search-input-group">
                    <span class="filter-icon">🔍</span>
                    <input 
                        type="text" 
                        name="keyword" 
                        value="{{ request('keyword') }}" 
                        placeholder="Search by name, barangay, or keywords..."
                        class="filter-input"
                    >
                </div>

                {{-- Species Dropdown --}}
                <div class="filter-group">
                    <select name="species" class="filter-select">
                        <option value="">Species: All</option>
                        <option value="dog" {{ request('species') == 'dog' ? 'selected' : '' }}>Dog</option>
                        <option value="cat" {{ request('species') == 'cat' ? 'selected' : '' }}>Cat</option>
                    </select>
                </div>

                {{-- Breed Dropdown --}}
                <div class="filter-group">
                    <select name="breed" class="filter-select">
                        <option value="">Breed: Any</option>
                        <option value="aspin" {{ request('breed') == 'aspin' ? 'selected' : '' }}>Aspin</option>
                        <option value="puspin" {{ request('breed') == 'puspin' ? 'selected' : '' }}>Puspin</option>
                        <option value="mix" {{ request('breed') == 'mix' ? 'selected' : '' }}>Mix Breed</option>
                    </select>
                </div>

                {{-- Age Dropdown --}}
                <div class="filter-group">
                    <select name="age" class="filter-select">
                        <option value="">Age: Any</option>
                        <option value="puppy_kitten" {{ request('age') == 'puppy_kitten' ? 'selected' : '' }}>Puppy / Kitten (&lt; 1 yr)</option>
                        <option value="young" {{ request('age') == 'young' ? 'selected' : '' }}>Young (1 - 2 yrs)</option>
                        <option value="adult" {{ request('age') == 'adult' ? 'selected' : '' }}>Adult (2+ yrs)</option>
                    </select>
                </div>

                {{-- Gender Dropdown --}}
                <div class="filter-group">
                    <select name="gender" class="filter-select">
                        <option value="">Gender: All</option>
                        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                {{-- Status Dropdown --}}
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="available" selected>Status: Available</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                    </select>
                </div>

                {{-- Filter Action Button --}}
                <div class="filter-action">
                    <button type="submit" class="btn-filter-yellow">
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- 3. Recently Listed Pets Section --}}
<section class="pets-section" id="pets">
    <div class="site-container">
        {{-- Section Header with Pet Count --}}
        <div class="section-header">
            <h2 class="section-title">
                Recently Listed Pets <span class="pets-count-badge">(4 Available)</span>
            </h2>
        </div>

        {{-- 4-Column Grid for Pet Cards --}}
        <div class="pets-grid">
            {{-- Pet 1: Bantay --}}
            <x-pet-card
                name="Bantay"
                species="Dog"
                breed="Aspin"
                age="5 months"
                gender="Male"
                description="Very playful brown puppy rescued near basketball court. Vaccinated with 1st shot."
                status="Available"
                image="https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=700&q=80"
            />

            {{-- Pet 2: Miming --}}
            <x-pet-card
                name="Miming"
                species="Cat"
                breed="Calico Puspin"
                age="1.5 yrs"
                gender="Female"
                description="Gentle, quiet indoor cat. Litter box trained and very sweet with children."
                status="Available"
                image="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=700&q=80"
            />

            {{-- Pet 3: Brownie --}}
            <x-pet-card
                name="Brownie"
                species="Dog"
                breed="Golden Aspin"
                age="2 yrs"
                gender="Male"
                description="Friendly community dog that loves belly rubs and outdoor walks. Neutered."
                status="Available"
                image="https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=700&q=80"
            />

            {{-- Pet 4: Oreo --}}
            <x-pet-card
                name="Oreo"
                species="Cat"
                breed="Tuxedo Puspin"
                age="8 months"
                gender="Male"
                description="Curious tuxedo kitten with shiny black coat. Very energetic and healthy."
                status="Pending"
                image="https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=700&q=80"
            />
        </div>
    </div>
</section>
@endsection
