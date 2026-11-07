@extends('layouts.admin')

@section('title', 'Accounts Management - ' . config('app.name', 'KUPKOP'))
@section('header', 'Accounts')

@section('content')
<div class="admin-dashboard-container">
    {{-- Page Header --}}
    <div class="posts-header-block">
        <h1 class="posts-heading">Accounts</h1>
        <p class="posts-subheading">Block users who break the rules, or remove their account.</p>
    </div>

    {{-- Top Tabs with Badge Counts --}}
    <div class="posts-tabs-row admin-accounts-tabs-row">
        <a
            href="#users"
            class="tab-pill {{ $tab !== 'archive' && $tab !== 'blocked' ? 'active' : '' }}"
            id="tab-btn-users"
            onclick="switchAccountsTab('users', event)"
        >
            <x-lucide-users style="width: 16px; height: 16px; stroke-width: 2.2;" />
            <span>User accounts</span>
            <span class="tab-badge" id="user-count-badge">{{ max(0, $userCount - $blockedCount) }}</span>
        </a>
        <a
            href="#blocked"
            class="tab-pill {{ $tab === 'blocked' ? 'active' : '' }}"
            id="tab-btn-blocked"
            onclick="switchAccountsTab('blocked', event)"
        >
            <x-lucide-user-x style="width: 16px; height: 16px; stroke-width: 2.2;" />
            <span>Blocked Accounts</span>
            <span class="tab-badge" id="blocked-count-badge">{{ $blockedCount }}</span>
        </a>
        <a
            href="{{ route('admin.accounts', array_merge(request()->only(['search']), ['tab' => 'archive'])) }}#archive"
            class="tab-pill {{ $tab === 'archive' ? 'active' : '' }}"
            id="tab-btn-archive"
            onclick="switchAccountsTab('archive', event)"
        >
            <x-lucide-archive style="width: 16px; height: 16px; stroke-width: 2.2;" />
            <span>Archive</span>
            <span class="tab-badge" id="archive-count-badge">{{ $archiveCount }}</span>
        </a>
    </div>

    {{-- Filters & Search (above table) --}}
    <form method="GET" action="{{ route('admin.accounts') }}" class="posts-filters-form" id="admin-accounts-filter-form" onsubmit="event.preventDefault(); applyAccountFilters();">
        <input type="hidden" name="tab" id="accounts-tab-input" value="{{ $tab }}">

        {{-- Search Input (Full width, live filter across all 3 tabs) --}}
        <div class="filter-search-box" style="flex: 1; max-width: 100%;">
            <span class="filter-search-icon">
                <x-lucide-search style="width: 17px; height: 17px;" />
            </span>
            <input
                type="text"
                name="search"
                placeholder="Search by name or email"
                value="{{ request('search') }}"
                autocomplete="off"
                id="accounts-search-input"
            >
        </div>
    </form>

    {{-- Bulk Actions Toolbar for Admin Accounts --}}
    <div id="admin-accounts-batch-bar" class="batch-actions-bar" style="flex-shrink: 0;">
        <div class="batch-bar-info">
            <span class="batch-bar-count-badge" id="admin-accounts-selected-count">0</span>
            <span>account(s) selected</span>
        </div>
        <div class="batch-bar-actions" id="admin-accounts-batch-actions">
            <button type="button" class="batch-btn batch-btn-restore" id="btn-batch-restore" onclick="executeAdminAccountsBulk('restore')" style="{{ $tab === 'archive' ? 'display: inline-flex;' : 'display: none;' }}">
                <x-lucide-rotate-ccw style="width: 14px; height: 14px; stroke-width: 2.2;" />
                <span>Restore Selected</span>
            </button>
            <button type="button" class="batch-btn batch-btn-delete" id="btn-batch-delete" onclick="executeAdminAccountsBulk('destroy')" style="{{ $tab === 'archive' ? 'display: inline-flex;' : 'display: none;' }}">
                <x-lucide-trash-2 style="width: 14px; height: 14px; stroke-width: 2.2;" />
                <span>Delete Permanently</span>
            </button>
            <button type="button" class="batch-btn batch-btn-verify" id="btn-batch-unblock" onclick="executeAdminAccountsBulk('unblock')" style="{{ $tab === 'blocked' ? 'display: inline-flex;' : 'display: none;' }}">
                <x-lucide-unlock style="width: 14px; height: 14px; stroke-width: 2.2;" />
                <span>Unblock Selected</span>
            </button>
            <button type="button" class="batch-btn batch-btn-block" id="btn-batch-block" onclick="executeAdminAccountsBulk('block')" style="{{ $tab === 'users' ? 'display: inline-flex;' : 'display: none;' }}">
                <x-lucide-user-x style="width: 14px; height: 14px; stroke-width: 2.2;" />
                <span>Block Selected</span>
            </button>
            <button type="button" class="batch-btn batch-btn-archive" id="btn-batch-archive" onclick="executeAdminAccountsBulk('archive')" style="{{ $tab !== 'archive' ? 'display: inline-flex;' : 'display: none;' }}">
                <x-lucide-archive style="width: 14px; height: 14px; stroke-width: 2.2;" />
                <span>Move to Archive</span>
            </button>
            <button type="button" class="batch-btn batch-btn-cancel" onclick="clearAdminAccountsSelection()">
                <span>Cancel</span>
            </button>
        </div>
    </div>

    {{-- Accounts Table Card --}}
    <div class="posts-table-card">
        {{-- Empty States for Each Tab --}}
        <div id="accounts-empty-users" class="accounts-empty-box" style="display: none; padding: 3.5rem 1.5rem; text-align: center; color: #71655B;">
            <div style="width: 52px; height: 52px; background: #FAF5EE; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; color: #4A2E2B;">
                <x-lucide-users style="width: 26px; height: 26px;" />
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #2D1810; margin: 0 0 0.35rem 0;">No user accounts yet.</h3>
            <p style="font-size: 0.95rem; color: #71655B; margin: 0;">Active accounts will appear here.</p>
        </div>

        <div id="accounts-empty-blocked" class="accounts-empty-box" style="display: none; padding: 3.5rem 1.5rem; text-align: center; color: #71655B;">
            <div style="width: 52px; height: 52px; background: #FEF2F2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; color: #DC2626;">
                <x-lucide-user-x style="width: 26px; height: 26px;" />
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #2D1810; margin: 0 0 0.35rem 0;">No blocked accounts.</h3>
            <p style="font-size: 0.95rem; color: #71655B; margin: 0;">Blocked user accounts will appear here.</p>
        </div>

        <div id="accounts-empty-archive" class="accounts-empty-box" style="display: none; padding: 3.5rem 1.5rem; text-align: center; color: #71655B;">
            <div style="width: 52px; height: 52px; background: #FAF5EE; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; color: #4A2E2B;">
                <x-lucide-archive style="width: 26px; height: 26px;" />
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #2D1810; margin: 0 0 0.35rem 0;">The archive is empty.</h3>
            <p style="font-size: 0.95rem; color: #71655B; margin: 0;">Removed accounts will appear here.</p>
        </div>

        <div id="accounts-table-wrapper" class="admin-table-scroll-wrapper" style="overflow-x: auto;">
            <table class="posts-table">
                <thead>
                    <tr>
                        <th style="width: 44px; text-align: center;">
                            <input type="checkbox" id="admin-accounts-select-all" class="batch-checkbox" onchange="toggleAllAdminAccounts(this.checked)" title="Select all accounts">
                        </th>
                        <th style="min-width: 260px;">User</th>
                        <th style="min-width: 160px;">Joined</th>
                        <th style="min-width: 100px;">Posts</th>
                        @if($tab === 'blocked')
                            <th style="min-width: 120px;">Account Role</th>
                            <th style="min-width: 160px;">Date Blocked</th>
                            <th style="min-width: 220px;">Reason for Block</th>
                        @else
                            <th style="min-width: 140px;">Status</th>
                        @endif
                        <th style="min-width: 200px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @php
                            $isBlocked = ($user->status === 'blocked' || $user->is_blocked);
                            $userStatusCategory = ($tab === 'archive') ? 'archive' : ($isBlocked ? 'blocked' : 'active');
                            $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
                            $avatarColors = [
                                'A' => ['#FEE2E2', '#DC2626'], 'B' => ['#DBEAFE', '#1D4ED8'],
                                'C' => ['#D1FAE5', '#065F46'], 'D' => ['#FEF3C7', '#D97706'],
                                'E' => ['#EDE9FE', '#6D28D9'], 'F' => ['#FFEDD5', '#C2410C'],
                                'G' => ['#DCFCE7', '#15803D'], 'H' => ['#E0F2FE', '#0284C7'],
                                'I' => ['#FCE7F3', '#BE185D'], 'J' => ['#FEF08A', '#92400E'],
                                'K' => ['#D1FAE5', '#065F46'], 'L' => ['#FFEDD5', '#C2410C'],
                                'M' => ['#FEE2E2', '#DC2626'], 'N' => ['#DBEAFE', '#1D4ED8'],
                                'O' => ['#FEF3C7', '#D97706'], 'P' => ['#EDE9FE', '#6D28D9'],
                                'Q' => ['#DCFCE7', '#15803D'], 'R' => ['#E0F2FE', '#0284C7'],
                                'S' => ['#FCE7F3', '#BE185D'], 'T' => ['#FEF08A', '#92400E'],
                                'U' => ['#D1FAE5', '#065F46'], 'V' => ['#DBEAFE', '#1D4ED8'],
                                'W' => ['#FFEDD5', '#C2410C'], 'X' => ['#EDE9FE', '#6D28D9'],
                                'Y' => ['#DCFCE7', '#15803D'], 'Z' => ['#E0F2FE', '#0284C7'],
                            ];
                            $color = $avatarColors[$initial] ?? ['#F3F4F6', '#6B7280'];
                        @endphp
                        <tr id="user-row-{{ $user->id }}"
                            class="admin-account-row"
                            data-account-status="{{ $userStatusCategory }}"
                            data-name="{{ strtolower($user->name) }}"
                            data-email="{{ strtolower($user->email) }}">
                            <td style="text-align: center;">
                                <input type="checkbox" class="batch-checkbox admin-account-item-cb" value="{{ $user->id }}" data-name="{{ addslashes($user->name) }}" data-status="{{ $userStatusCategory }}" onchange="updateAdminAccountsBatchState()" aria-label="Select {{ $user->name }}">
                            </td>
                            {{-- User Column --}}
                            <td>
                                <div class="pet-cell">
                                    <div class="user-avatar-circle" style="background-color: {{ $color[0] }}; color: {{ $color[1] }};">
                                        {{ $initial }}
                                    </div>
                                    <div>
                                        <div class="pet-name">{{ $user->name }}</div>
                                        <div class="pet-id">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Joined Column --}}
                            <td>
                                <span class="details-sub">
                                    {{ $user->created_at->format('M j, Y') }}
                                </span>
                            </td>

                            {{-- Posts Column --}}
                            <td>
                                <span class="posted-by-text" style="font-weight: 700;">
                                    {{ $user->active_posts_count }}
                                </span>
                            </td>

                            {{-- Status Badge Column (or blocked-tab columns) --}}
                            @if($tab === 'blocked')
                                <td>
                                    <span class="details-sub">{{ $user->is_admin ? 'Admin' : 'User' }}</span>
                                </td>
                                <td>
                                    <span class="details-sub">{{ $user->blocked_at ? $user->blocked_at->format('M j, Y') : '—' }}</span>
                                </td>
                                <td>
                                    <span class="details-sub" style="font-size: 0.88rem; color: #71655B;">{{ $user->block_reason ?: '—' }}</span>
                                </td>
                            @else
                                <td>
                                    @if($tab === 'archive')
                                        <span class="badge-archived">Archived</span>
                                    @elseif($isBlocked)
                                        <span class="badge-blocked">Blocked</span>
                                    @else
                                        <span class="badge-available">Active</span>
                                    @endif
                                </td>
                            @endif

                            {{-- Actions Column --}}
                            <td>
                                <div class="table-actions-cell">
                                    @if($tab === 'archive')
                                        {{-- Archive tab: Restore + Delete permanently --}}
                                        <button
                                            type="button"
                                            class="btn-action-restore"
                                            onclick="openRestoreModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        >
                                            Restore
                                        </button>
                                        <button
                                            type="button"
                                            class="btn-action-delete"
                                            onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        >
                                            Delete permanently
                                        </button>
                                    @elseif($isBlocked)
                                        {{-- Blocked accounts tab: Unblock + Remove --}}
                                        <button
                                            type="button"
                                            class="btn-action-view"
                                            style="background: #FEF08A; color: #92400E; border: 1px solid #FDE047;"
                                            onclick="openUnblockModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        >
                                            Unblock Account
                                        </button>
                                        <button
                                            type="button"
                                            class="btn-action-remove"
                                            onclick="openRemoveModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        >
                                            Remove
                                        </button>
                                    @else
                                        {{-- User accounts tab: Block + Remove --}}
                                        <button
                                            type="button"
                                            class="btn-action-view"
                                            onclick="openBlockModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        >
                                            Block
                                        </button>
                                        <button
                                            type="button"
                                            class="btn-action-remove"
                                            onclick="openRemoveModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        >
                                            Remove
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Mobile Cards for Accounts (phone only) --}}
            <div class="admin-mobile-cards" aria-label="Admin account cards">
                @foreach($users as $user)
                    @php
                        $isBlocked = ($user->status === 'blocked' || $user->is_blocked);
                        $userStatusCategory = ($tab === 'archive') ? 'archive' : ($isBlocked ? 'blocked' : 'active');
                        $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
                        $avatarColors = [
                            'A' => ['#FEE2E2', '#DC2626'], 'B' => ['#DBEAFE', '#1D4ED8'],
                            'C' => ['#D1FAE5', '#065F46'], 'D' => ['#FEF3C7', '#D97706'],
                            'E' => ['#EDE9FE', '#6D28D9'], 'F' => ['#FFEDD5', '#C2410C'],
                            'G' => ['#DCFCE7', '#15803D'], 'H' => ['#E0F2FE', '#0284C7'],
                            'I' => ['#FCE7F3', '#BE185D'], 'J' => ['#FEF08A', '#92400E'],
                            'K' => ['#D1FAE5', '#065F46'], 'L' => ['#FFEDD5', '#C2410C'],
                            'M' => ['#FEE2E2', '#DC2626'], 'N' => ['#DBEAFE', '#1D4ED8'],
                            'O' => ['#FEF3C7', '#D97706'], 'P' => ['#EDE9FE', '#6D28D9'],
                            'Q' => ['#DCFCE7', '#15803D'], 'R' => ['#E0F2FE', '#0284C7'],
                            'S' => ['#FCE7F3', '#BE185D'], 'T' => ['#FEF08A', '#92400E'],
                            'U' => ['#D1FAE5', '#065F46'], 'V' => ['#DBEAFE', '#1D4ED8'],
                            'W' => ['#FFEDD5', '#C2410C'], 'X' => ['#EDE9FE', '#6D28D9'],
                            'Y' => ['#DCFCE7', '#15803D'], 'Z' => ['#E0F2FE', '#0284C7'],
                        ];
                        $color = $avatarColors[$initial] ?? ['#F3F4F6', '#6B7280'];
                    @endphp
                    <div class="admin-user-card"
                        id="admin-user-card-{{ $user->id }}"
                        data-account-status="{{ $userStatusCategory }}"
                        data-name="{{ strtolower($user->name) }}"
                        data-email="{{ strtolower($user->email) }}">
                        <div class="admin-user-card-top">
                            <div style="display: flex; align-items: center; margin-right: 0.5rem;">
                                <input type="checkbox" class="batch-checkbox admin-account-item-cb" value="{{ $user->id }}" data-name="{{ addslashes($user->name) }}" data-status="{{ $userStatusCategory }}" onchange="updateAdminAccountsBatchState()" aria-label="Select {{ $user->name }}">
                            </div>
                            <div class="admin-user-avatar" style="background-color: {{ $color[0] }}; color: {{ $color[1] }};">
                                {{ $initial }}
                            </div>
                            <div class="admin-user-info">
                                <div class="admin-user-name">{{ $user->name }}</div>
                                <div class="admin-user-email">{{ $user->email }}</div>
                            </div>
                            @if($tab === 'archive')
                                <span class="badge-archived" style="flex-shrink:0;">Archived</span>
                            @elseif($isBlocked)
                                <span class="badge-blocked" style="flex-shrink:0;">Blocked</span>
                            @else
                                <span class="badge-available" style="flex-shrink:0;">Active</span>
                            @endif
                        </div>

                        <div class="admin-user-meta">
                            <span>Joined: <strong>{{ $user->created_at->format('M j, Y') }}</strong></span>
                            <span>•</span>
                            <span>Posts: <strong>{{ $user->active_posts_count }}</strong></span>
                        </div>

                        <div class="admin-user-actions">
                            @if($tab === 'archive')
                                <button
                                    type="button"
                                    class="btn-action-restore"
                                    onclick="openRestoreModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                >
                                    Restore
                                </button>
                                <button
                                    type="button"
                                    class="btn-action-delete"
                                    onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                >
                                    Delete permanently
                                </button>
                            @elseif($isBlocked)
                                <button
                                    type="button"
                                    class="btn-action-view"
                                    style="background: #FEF08A; color: #92400E; border: 1px solid #FDE047;"
                                    onclick="openUnblockModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                >
                                    Unblock Account
                                </button>
                                <button
                                    type="button"
                                    class="btn-action-remove"
                                    onclick="openRemoveModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                >
                                    Remove
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="btn-action-view"
                                    onclick="openBlockModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                >
                                    Block
                                </button>
                                <button
                                    type="button"
                                    class="btn-action-remove"
                                    onclick="openRemoveModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                >
                                    Remove
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- =====================================================================
     1. BLOCK CONFIRMATION MODAL
     ===================================================================== --}}
<div
    id="block-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="block-modal-title"
    style="display: none; z-index: 1060;"
    onclick="if(event.target.id === 'block-modal') closeBlockModal()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 460px; background: #ffffff; border-radius: 1.5rem; padding: 2rem; text-align: left; border: 1px solid #F3E8D8; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22);">
        <h3 id="block-modal-title" style="font-size: 1.35rem; font-weight: 800; color: #2D1810; margin: 0 0 0.5rem 0;">
            Block this user?
        </h3>
        <p style="font-size: 0.98rem; color: #71655B; margin: 0 0 1.25rem 0; line-height: 1.5; font-weight: 500;">
            They can't log in or post until you unblock them. A notification email will be sent to the user.
        </p>
        <form id="block-form" method="POST" action="" style="margin: 0;">
            @csrf
            @method('PATCH')
            {{-- Reason for action --}}
            <div style="margin-bottom: 1.25rem;">
                <label for="block-reason" style="display: block; font-size: 0.9rem; font-weight: 700; color: #4A2E2B; margin-bottom: 0.4rem;">
                    Reason for blocking <span style="color: #71655B; font-weight: 500;">(optional)</span>
                </label>
                <textarea
                    id="block-reason"
                    name="reason"
                    rows="3"
                    maxlength="500"
                    placeholder="e.g. Violation of community rules, inappropriate content..."
                    style="width: 100%; border: 1.5px solid #E5E7EB; border-radius: 0.75rem; padding: 0.65rem 0.85rem; font-size: 0.92rem; color: #2D1810; resize: vertical; font-family: inherit; background: #FAF9F6; box-sizing: border-box; outline: none;"
                    onfocus="this.style.borderColor='#4A2E2B'"
                    onblur="this.style.borderColor='#E5E7EB'"
                ></textarea>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button
                    type="button"
                    onclick="closeBlockModal()"
                    style="padding: 0.65rem 1.35rem; border: 1px solid #E5E7EB; background: #FAF9F6; color: #2D1810; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer;"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    style="padding: 0.65rem 1.35rem; border: none; background: #FEE2E2; color: #991B1B; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer;"
                >
                    Block user
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
     2. UNBLOCK CONFIRMATION MODAL
     ===================================================================== --}}
<div
    id="unblock-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="unblock-modal-title"
    style="display: none; z-index: 1060;"
    onclick="if(event.target.id === 'unblock-modal') closeUnblockModal()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 440px; background: #ffffff; border-radius: 1.5rem; padding: 2rem; text-align: left; border: 1px solid #F3E8D8; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22);">
        <h3 id="unblock-modal-title" style="font-size: 1.35rem; font-weight: 800; color: #2D1810; margin: 0 0 0.85rem 0;">
            Unblock this user?
        </h3>
        <p style="font-size: 0.98rem; color: #71655B; margin: 0 0 1.75rem 0; line-height: 1.5; font-weight: 500;">
            Are you sure you want to restore access for <strong id="unblock-user-name">this user</strong>? They will be able to log in and post again.
        </p>
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
            <button
                type="button"
                onclick="closeUnblockModal()"
                style="padding: 0.65rem 1.35rem; border: 1px solid #E5E7EB; background: #FAF9F6; color: #2D1810; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer;"
            >
                Cancel
            </button>
            <form id="unblock-form" method="POST" action="" style="display: inline; margin: 0;">
                @csrf
                @method('PATCH')
                <button
                    type="submit"
                    id="unblock-submit-btn"
                    style="padding: 0.65rem 1.35rem; border: none; background: #DCFCE7; color: #166534; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer;"
                >
                    Restore access
                </button>
            </form>
        </div>
    </div>
</div>

{{-- =====================================================================
     3. REMOVE (ARCHIVE) CONFIRMATION MODAL
     ===================================================================== --}}
<div
    id="remove-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="remove-modal-title"
    style="display: none; z-index: 1060;"
    onclick="if(event.target.id === 'remove-modal') closeRemoveModal()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 460px; background: #ffffff; border-radius: 1.5rem; padding: 2rem; text-align: left; border: 1px solid #F3E8D8; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22);">
        <h3 id="remove-modal-title" style="font-size: 1.35rem; font-weight: 800; color: #2D1810; margin: 0 0 0.5rem 0;">
            Remove this account?
        </h3>
        <p style="font-size: 0.98rem; color: #71655B; margin: 0 0 1.25rem 0; line-height: 1.5; font-weight: 500;">
            The account moves to the archive. You can restore it later. A notification email will be sent to the user.
        </p>
        <form id="remove-form" method="POST" action="" style="margin: 0;">
            @csrf
            @method('PATCH')
            {{-- Reason for action --}}
            <div style="margin-bottom: 1.25rem;">
                <label for="remove-reason" style="display: block; font-size: 0.9rem; font-weight: 700; color: #4A2E2B; margin-bottom: 0.4rem;">
                    Reason for removal <span style="color: #71655B; font-weight: 500;">(optional)</span>
                </label>
                <textarea
                    id="remove-reason"
                    name="reason"
                    rows="3"
                    maxlength="500"
                    placeholder="e.g. Repeated policy violations, fraudulent activity..."
                    style="width: 100%; border: 1.5px solid #E5E7EB; border-radius: 0.75rem; padding: 0.65rem 0.85rem; font-size: 0.92rem; color: #2D1810; resize: vertical; font-family: inherit; background: #FAF9F6; box-sizing: border-box; outline: none;"
                    onfocus="this.style.borderColor='#4A2E2B'"
                    onblur="this.style.borderColor='#E5E7EB'"
                ></textarea>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button
                    type="button"
                    onclick="closeRemoveModal()"
                    style="padding: 0.65rem 1.35rem; border: 1px solid #E5E7EB; background: #FAF9F6; color: #2D1810; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer;"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    style="padding: 0.65rem 1.35rem; border: none; background: #FEE2E2; color: #991B1B; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer;"
                >
                    Remove account
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
     4. RESTORE CONFIRMATION MODAL
     ===================================================================== --}}
<div
    id="acc-restore-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="acc-restore-modal-title"
    style="display: none; z-index: 1060;"
    onclick="if(event.target.id === 'acc-restore-modal') closeRestoreModal()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 440px; background: #ffffff; border-radius: 1.5rem; padding: 2rem; text-align: left; border: 1px solid #F3E8D8; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22);">
        <h3 id="acc-restore-modal-title" style="font-size: 1.35rem; font-weight: 800; color: #2D1810; margin: 0 0 0.85rem 0;">
            Restore this account?
        </h3>
        <p style="font-size: 0.98rem; color: #71655B; margin: 0 0 1.75rem 0; line-height: 1.5; font-weight: 500;">
            <strong id="restore-user-name">This user</strong> will be restored to active status and can log in again.
        </p>
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
            <button
                type="button"
                onclick="closeRestoreModal()"
                style="padding: 0.65rem 1.35rem; border: 1px solid #E5E7EB; background: #FAF9F6; color: #2D1810; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer;"
            >
                Cancel
            </button>
            <form id="restore-form" method="POST" action="" style="display: inline; margin: 0;">
                @csrf
                @method('PATCH')
                <button
                    type="submit"
                    style="padding: 0.65rem 1.35rem; border: none; background: #FEF08A; color: #4A2E2B; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer;"
                >
                    Yes, Restore
                </button>
            </form>
        </div>
    </div>
</div>

{{-- =====================================================================
     5. DELETE PERMANENTLY CONFIRMATION MODAL
     ===================================================================== --}}
<div
    id="delete-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="delete-modal-title"
    style="display: none; z-index: 1060;"
    onclick="if(event.target.id === 'delete-modal') closeDeleteModal()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 440px; background: #ffffff; border-radius: 1.5rem; padding: 2rem; text-align: left; border: 1px solid #FEE2E2; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22);">
        <h3 id="delete-modal-title" style="font-size: 1.35rem; font-weight: 800; color: #2D1810; margin: 0 0 0.85rem 0;">
            Delete permanently?
        </h3>
        <p style="font-size: 0.98rem; color: #71655B; margin: 0 0 1.75rem 0; line-height: 1.5; font-weight: 500;">
            <strong id="delete-user-name">This user</strong>'s account and all associated data will be permanently erased. <strong>This cannot be undone.</strong>
        </p>
        <form id="delete-form" method="POST" action="" style="margin: 0;">
            @csrf
            @method('DELETE')
            {{-- Reason for deletion --}}
            <div style="margin-bottom: 1.25rem;">
                <label for="delete-reason" style="display: block; font-size: 0.9rem; font-weight: 700; color: #4A2E2B; margin-bottom: 0.4rem;">
                    Reason for deletion <span style="color: #71655B; font-weight: 500;">(optional)</span>
                </label>
                <textarea
                    id="delete-reason"
                    name="reason"
                    rows="3"
                    maxlength="500"
                    placeholder="e.g. Permanent ban due to severe violations..."
                    style="width: 100%; border: 1.5px solid #E5E7EB; border-radius: 0.75rem; padding: 0.65rem 0.85rem; font-size: 0.92rem; color: #2D1810; resize: vertical; font-family: inherit; background: #FAF9F6; box-sizing: border-box; outline: none;"
                    onfocus="this.style.borderColor='#4A2E2B'"
                    onblur="this.style.borderColor='#E5E7EB'"
                ></textarea>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button
                    type="button"
                    onclick="closeDeleteModal()"
                    style="padding: 0.65rem 1.35rem; border: 1px solid #E5E7EB; background: #FAF9F6; color: #2D1810; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer;"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    style="padding: 0.65rem 1.35rem; border: none; background: #FEE2E2; color: #991B1B; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer;"
                >
                    Yes, Delete permanently
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // -----------------------------------------------------------------------
    // Accounts Management — Modal Controllers
    // -----------------------------------------------------------------------

    let currentUnblockUserId = null;

    // Block
    function openBlockModal(userId, userName) {
        document.getElementById('block-form').action = '/admin/accounts/' + userId + '/block';
        document.getElementById('block-modal').style.display = 'flex';
        document.body.classList.add('modal-open');
    }
    function closeBlockModal() {
        document.getElementById('block-modal').style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    // Unblock
    function openUnblockModal(userId, userName) {
        currentUnblockUserId = userId;
        document.getElementById('unblock-user-name').textContent = userName;
        document.getElementById('unblock-form').action = '/admin/accounts/' + userId + '/unblock';
        document.getElementById('unblock-modal').style.display = 'flex';
        document.body.classList.add('modal-open');
    }
    function closeUnblockModal() {
        document.getElementById('unblock-modal').style.display = 'none';
        document.body.classList.remove('modal-open');
        currentUnblockUserId = null;
    }

    // Remove (archive)
    function openRemoveModal(userId, userName) {
        document.getElementById('remove-form').action = '/admin/accounts/' + userId + '/archive';
        document.getElementById('remove-modal').style.display = 'flex';
        document.body.classList.add('modal-open');
    }
    function closeRemoveModal() {
        document.getElementById('remove-modal').style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    // Restore
    function openRestoreModal(userId, userName) {
        document.getElementById('restore-user-name').textContent = userName;
        document.getElementById('restore-form').action = '/admin/accounts/' + userId + '/restore';
        document.getElementById('acc-restore-modal').style.display = 'flex';
        document.body.classList.add('modal-open');
    }
    function closeRestoreModal() {
        document.getElementById('acc-restore-modal').style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    // Delete permanently
    function openDeleteModal(userId, userName) {
        document.getElementById('delete-user-name').textContent = userName;
        document.getElementById('delete-form').action = '/admin/accounts/' + userId;
        document.getElementById('delete-modal').style.display = 'flex';
        document.body.classList.add('modal-open');
    }
    function closeDeleteModal() {
        document.getElementById('delete-modal').style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    // Close any open modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBlockModal();
            closeUnblockModal();
            closeRemoveModal();
            closeRestoreModal();
            closeDeleteModal();
        }
    });

    // Dynamic unblock submission without full page reload
    (function() {
        const unblockForm = document.getElementById('unblock-form');
        if (!unblockForm) return;

        unblockForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('unblock-submit-btn');
            if (btn) btn.disabled = true;

            const action = unblockForm.action;
            const formData = new FormData(unblockForm);

            fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (btn) btn.disabled = false;
                if (data.success) {
                    const removedId = currentUnblockUserId;
                    closeUnblockModal();

                    // If a row for this user exists on the page, animate and remove it
                    if (removedId) {
                        const row = document.getElementById('user-row-' + removedId);
                        if (row) {
                            row.style.transition = 'all 0.3s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(20px)';
                            setTimeout(() => {
                                row.remove();
                                const tbody = document.querySelector('.posts-table tbody');
                                if (tbody && tbody.children.length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                    }

                    // Dynamically update tab badge counts
                    if (data.blocked_count !== undefined) {
                        const b = document.getElementById('blocked-count-badge');
                        if (b) b.textContent = data.blocked_count;
                    }
                    if (data.user_count !== undefined) {
                        const u = document.getElementById('user-count-badge');
                        if (u) u.textContent = data.user_count;
                    }
                    if (data.archive_count !== undefined) {
                        const a = document.getElementById('archive-count-badge');
                        if (a) a.textContent = data.archive_count;
                    }
                } else {
                    unblockForm.submit();
                }
            })
            .catch(() => {
                if (btn) btn.disabled = false;
                unblockForm.submit();
            });
        });
    })();

    // Tab switching and filtering
    let currentTab = '{{ $tab === "archive" ? "archive" : "users" }}';

    function switchAccountsTab(tab, event) {
        if (event) event.preventDefault();

        const isServerArchive = {{ $tab === 'archive' ? 'true' : 'false' }};
        if (isServerArchive && tab !== 'archive') {
            sessionStorage.setItem('adminAccountsTab', tab);
            window.location.href = "{{ route('admin.accounts') }}#" + tab;
            return;
        }

        if (!isServerArchive && tab === 'archive') {
            sessionStorage.setItem('adminAccountsTab', 'archive');
            window.location.href = "{{ route('admin.accounts', ['tab' => 'archive']) }}#archive";
            return;
        }

        currentTab = tab;
        sessionStorage.setItem('adminAccountsTab', tab);
        try {
            history.replaceState(null, '', '#' + tab);
        } catch(e) {}

        // Update active classes on tab pills
        const pills = document.querySelectorAll('.admin-accounts-tabs-row .tab-pill');
        pills.forEach(p => p.classList.remove('active'));
        const activeBtn = document.getElementById('tab-btn-' + tab);
        if (activeBtn) activeBtn.classList.add('active');

        applyAccountFilters();
    }

    function applyAccountFilters() {
        const searchInput = document.getElementById('accounts-search-input');
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();

        const rows = document.querySelectorAll('.admin-account-row');
        const cards = document.querySelectorAll('.admin-user-card');

        let visibleCount = 0;

        rows.forEach(row => {
            const status = row.getAttribute('data-account-status');
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';

            const matchesTab = (currentTab === 'archive' && status === 'archive') ||
                               (currentTab === 'blocked' && status === 'blocked') ||
                               (currentTab === 'users' && status === 'active');

            const matchesSearch = !query || name.includes(query) || email.includes(query);

            if (matchesTab && matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        cards.forEach(card => {
            const status = card.getAttribute('data-account-status');
            const name = card.getAttribute('data-name') || '';
            const email = card.getAttribute('data-email') || '';

            const matchesTab = (currentTab === 'archive' && status === 'archive') ||
                               (currentTab === 'blocked' && status === 'blocked') ||
                               (currentTab === 'users' && status === 'active');

            const matchesSearch = !query || name.includes(query) || email.includes(query);

            if (matchesTab && matchesSearch) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        // Manage empty states
        const emptyUsers = document.getElementById('accounts-empty-users');
        const emptyBlocked = document.getElementById('accounts-empty-blocked');
        const emptyArchive = document.getElementById('accounts-empty-archive');
        const tableWrapper = document.getElementById('accounts-table-wrapper');

        if (emptyUsers) emptyUsers.style.display = 'none';
        if (emptyBlocked) emptyBlocked.style.display = 'none';
        if (emptyArchive) emptyArchive.style.display = 'none';

        if (visibleCount === 0) {
            if (tableWrapper) tableWrapper.style.display = 'none';
            if (currentTab === 'users' && emptyUsers) emptyUsers.style.display = 'block';
            if (currentTab === 'blocked' && emptyBlocked) emptyBlocked.style.display = 'block';
            if (currentTab === 'archive' && emptyArchive) emptyArchive.style.display = 'block';
        } else {
            if (tableWrapper) tableWrapper.style.display = '';
        }

        updateAccountsBatchButtonVisibility();
        updateAdminAccountsBatchState();
    }

    function updateAccountsBatchButtonVisibility() {
        const btnRestore = document.getElementById('btn-batch-restore');
        const btnDelete = document.getElementById('btn-batch-delete');
        const btnUnblock = document.getElementById('btn-batch-unblock');
        const btnBlock = document.getElementById('btn-batch-block');
        const btnArchive = document.getElementById('btn-batch-archive');

        if (btnRestore) btnRestore.style.display = currentTab === 'archive' ? 'inline-flex' : 'none';
        if (btnDelete) btnDelete.style.display = currentTab === 'archive' ? 'inline-flex' : 'none';
        if (btnUnblock) btnUnblock.style.display = currentTab === 'blocked' ? 'inline-flex' : 'none';
        if (btnBlock) btnBlock.style.display = currentTab === 'users' ? 'inline-flex' : 'none';
        if (btnArchive) btnArchive.style.display = currentTab !== 'archive' ? 'inline-flex' : 'none';
    }

    // -------------------------------------------------------------------------
    // Batch Selection: Admin Accounts
    // -------------------------------------------------------------------------
    function toggleAllAdminAccounts(checked) {
        const itemCbs = document.querySelectorAll('.admin-account-item-cb');
        itemCbs.forEach(cb => {
            const row = cb.closest('tr');
            const card = cb.closest('.admin-user-card');
            const isVisible = (!row || row.style.display !== 'none') && (!card || card.style.display !== 'none');
            if (isVisible) {
                cb.checked = checked;
            } else if (!checked) {
                cb.checked = false;
            }
        });
        const master = document.getElementById('admin-accounts-select-all');
        if (master) master.checked = checked;
        updateAdminAccountsBatchState();
    }

    function updateAdminAccountsBatchState() {
        const allVisibleCbs = Array.from(document.querySelectorAll('.admin-account-item-cb')).filter(cb => {
            const row = cb.closest('tr');
            const card = cb.closest('.admin-user-card');
            return (!row || row.style.display !== 'none') && (!card || card.style.display !== 'none');
        });
        const checkedCbs = allVisibleCbs.filter(cb => cb.checked);
        const count = checkedCbs.length;

        const master = document.getElementById('admin-accounts-select-all');
        const isAll = allVisibleCbs.length > 0 && checkedCbs.length === allVisibleCbs.length;
        const isIndet = checkedCbs.length > 0 && !isAll;

        if (master) {
            master.checked = isAll;
            master.indeterminate = isIndet;
        }

        // Highlight selected rows & cards
        document.querySelectorAll('.admin-account-item-cb').forEach(cb => {
            const row = document.getElementById('user-row-' + cb.value);
            const card = document.getElementById('admin-user-card-' + cb.value);
            if (row) row.classList.toggle('is-row-selected', cb.checked);
            if (card) card.classList.toggle('is-card-selected', cb.checked);
        });

        const bar = document.getElementById('admin-accounts-batch-bar');
        const countBadge = document.getElementById('admin-accounts-selected-count');
        if (bar) {
            bar.style.display = count > 0 ? 'flex' : 'none';
        }
        if (countBadge) {
            countBadge.textContent = count;
        }
    }

    function clearAdminAccountsSelection() {
        toggleAllAdminAccounts(false);
    }

    async function executeAdminAccountsBulk(action) {
        const checkedCbs = Array.from(document.querySelectorAll('.admin-account-item-cb:checked')).filter(cb => {
            const row = cb.closest('tr');
            const card = cb.closest('.admin-user-card');
            return (!row || row.style.display !== 'none') && (!card || card.style.display !== 'none');
        });
        const uniqueIds = Array.from(new Set(checkedCbs.map(cb => cb.value)));
        if (uniqueIds.length === 0) return;

        const count = uniqueIds.length;
        let actionLabel = 'process';
        if (action === 'block') actionLabel = 'block';
        if (action === 'unblock') actionLabel = 'unblock';
        if (action === 'archive') actionLabel = 'move to archive';
        if (action === 'restore') actionLabel = 'restore';
        if (action === 'destroy') actionLabel = 'permanently delete';

        let reason = 'Bulk action performed by administrator.';
        if (action === 'block' || action === 'archive' || action === 'destroy') {
            const promptReason = prompt(`Are you sure you want to ${actionLabel} ${count} selected account(s)?\n\n(Optional) Reason for ${actionLabel}:`, reason);
            if (promptReason === null) return; // user cancelled
            if (promptReason.trim()) reason = promptReason.trim();
        } else {
            const confirmMsg = `Are you sure you want to ${actionLabel} ${count} selected account(s)?`;
            if (!confirm(confirmMsg)) return;
        }

        showAdminAccountsBatchLoader(`${capitalize(actionLabel)}ing ${count} accounts...`);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            for (let i = 0; i < uniqueIds.length; i++) {
                const id = uniqueIds[i];
                let url = `/admin/accounts/${id}`;
                let method = 'PATCH';

                if (action === 'block') url = `/admin/accounts/${id}/block`;
                else if (action === 'unblock') url = `/admin/accounts/${id}/unblock`;
                else if (action === 'archive') url = `/admin/accounts/${id}/archive`;
                else if (action === 'restore') url = `/admin/accounts/${id}/restore`;
                else if (action === 'destroy') {
                    url = `/admin/accounts/${id}`;
                    method = 'DELETE';
                }

                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ _method: method, reason: reason })
                });
            }
            window.location.reload();
        } catch (err) {
            alert('An error occurred during bulk operation. Refreshing...');
            window.location.reload();
        }
    }

    function showAdminAccountsBatchLoader(text) {
        let overlay = document.getElementById('admin-batch-loader');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'admin-batch-loader';
            overlay.style.cssText = 'position: fixed; inset: 0; background: rgba(45, 24, 16, 0.7); backdrop-filter: blur(3px); z-index: 99999; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; font-family: inherit;';
            overlay.innerHTML = `
                <div style="background: #2D1810; padding: 1.75rem 2.5rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 20px 50px rgba(0,0,0,0.5); text-align: center;">
                    <div style="width: 38px; height: 38px; border: 3px solid rgba(255,255,255,0.25); border-top-color: #FEF08A; border-radius: 50%; animation: batchSpin 0.8s linear infinite; margin: 0 auto 1rem auto;"></div>
                    <div id="admin-batch-loader-text" style="font-size: 1rem; font-weight: 700; color: #FAF9F6;">Processing...</div>
                </div>
                <style>@keyframes batchSpin { to { transform: rotate(360deg); } }</style>
            `;
            document.body.appendChild(overlay);
        }
        const textEl = document.getElementById('admin-batch-loader-text');
        if (textEl) textEl.textContent = text;
        overlay.style.display = 'flex';
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const hash = window.location.hash.replace('#', '');
        const savedTab = sessionStorage.getItem('adminAccountsTab');
        const isServerArchive = {{ $tab === 'archive' ? 'true' : 'false' }};

        let initialTab = 'users';
        if (isServerArchive) {
            initialTab = 'archive';
        } else if (urlParams.get('status') === 'blocked' || hash === 'blocked' || savedTab === 'blocked') {
            initialTab = 'blocked';
        } else if (hash === 'users' || savedTab === 'users') {
            initialTab = 'users';
        }

        switchAccountsTab(initialTab);

        const searchInput = document.getElementById('accounts-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', applyAccountFilters);
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') e.preventDefault();
            });
        }
    });
</script>
@endpush
