@extends('layouts.admin')

@section('title', 'Admin Dashboard - ' . config('app.name', 'KUPKOP'))
@section('header', 'Overview Dashboard')

@section('content')
<div class="admin-welcome-box">
    <h3>Welcome back, Administrator</h3>
    <p class="text-muted">Here is an overview of the pet shelter and adoption requests.</p>
</div>

{{-- Metric Stat Cards --}}
<div class="grid grid-4 stat-cards-grid">
    <x-admin.stat-card
        title="Total Animals"
        :value="$stats['total_animals']"
        icon="🐾"
        color="primary"
    />

    <x-admin.stat-card
        title="Pending Inquiries"
        :value="$stats['pending_requests']"
        icon="⏳"
        color="warning"
    />

    <x-admin.stat-card
        title="Approved Adoptions"
        :value="$stats['approved_requests']"
        icon="✅"
        color="success"
    />

    <x-admin.stat-card
        title="Registered Users"
        :value="$stats['total_users']"
        icon="👥"
        color="info"
    />
</div>

{{-- Quick Action Cards --}}
<div class="grid grid-2 admin-quick-actions">
    <x-card title="Quick Actions">
        <p class="text-muted mb-3">Common tasks you can perform as an admin:</p>
        <div class="actions-list">
            <a href="#" class="btn btn-primary">+ Add New Animal</a>
            <a href="#" class="btn btn-secondary">Review Pending Requests</a>
        </div>
    </x-card>

    <x-card title="Recent Activity">
        <p class="text-muted">No recent activity logged yet. When adoption requests or new animals are added, they will appear here.</p>
    </x-card>
</div>
@endsection
