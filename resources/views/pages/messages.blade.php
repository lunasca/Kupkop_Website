@extends('layouts.app')

@section('title', 'Messages - ' . config('app.name', 'Kupkop'))

@section('content')
<div class="messages-page">

    {{-- ======================================================
         LEFT SIDEBAR — Conversations List
    ====================================================== --}}
    <aside class="msg-sidebar" id="msgSidebar">
        {{-- Sidebar Header --}}
        <div class="msg-sidebar-header">
            <h2 class="msg-sidebar-title">
                <x-lucide-message-square class="msg-title-icon" />
                Messages
            </h2>
            @auth
                <span class="msg-total-badge" id="totalBadge" style="display: none;">0</span>
            @endauth
        </div>

        {{-- Search Bar --}}
        @auth
            <div class="msg-search-wrap" style="position: relative;">
                <x-lucide-search class="msg-search-icon" />
                <input
                    type="text"
                    id="convSearch"
                    class="msg-search-input"
                    placeholder="Search conversations..."
                    oninput="handleConvSearchInput(this.value)"
                    onfocus="handleConvSearchInput(this.value)"
                    autocomplete="off"
                />
                {{-- Floating Dropdown Suggestions --}}
                <div id="msgSearchDropdown" class="msg-search-dropdown" style="display: none;"></div>
            </div>

            {{-- Conversation Items --}}
            <ul class="msg-conv-list" id="convList" role="listbox">
                <!-- Injected by JS -->
            </ul>
        @else
            {{-- Guest: empty sidebar state --}}
            <div class="msg-guest-sidebar-hint">
                <x-lucide-lock class="msg-guest-lock-icon" />
                <p>Sign in to see your conversations</p>
            </div>
        @endauth
    </aside>

    {{-- ======================================================
         RIGHT PANEL — Chat Area
    ====================================================== --}}
    <section class="msg-chat-area" id="msgChatArea">

        @guest
        {{-- =====================================================================
             GUEST PROMPT — Shown inside the chat panel when not logged in
        ====================================================================== --}}
        <div class="msg-guest-prompt">
            <div class="msg-guest-prompt-icon">
                <x-lucide-message-square style="width: 44px; height: 44px; stroke-width: 1.5; color: #8C756B;" />
            </div>
            <h3 class="msg-guest-prompt-title">Log in or sign up to send and view messages</h3>
            <p class="msg-guest-prompt-body">
                Once logged in, you can message pet rescuers and coordinate adoptions directly in Brgy.&nbsp;176&#8209;F.
            </p>
            <div class="msg-guest-prompt-actions">
                <button type="button" class="btn-gate-login" onclick="openLoginModal()">Log In</button>
                <button type="button" class="btn-gate-signup" onclick="openRegisterModal()">Sign Up</button>
            </div>
        </div>
        @else

        {{-- Empty state — visible when no conversation selected --}}
        <div class="msg-empty-state" id="msgEmptyState">
            <div class="msg-empty-icon">
                <x-lucide-message-square style="width: 48px; height: 48px; stroke-width: 1.5; color: #8C756B; opacity: 0.6;" />
            </div>
            <h3>Your Messages</h3>
            <p>Select a conversation from the left to start chatting.</p>
        </div>

        {{-- Chat Panel — hidden until a conversation is selected --}}
        <div class="msg-chat-panel" id="msgChatPanel" style="display:none;">

            {{-- Chat Header --}}
            <div class="msg-chat-header">
                {{-- Mobile back button --}}
                <button class="msg-back-btn" id="msgBackBtn" onclick="deselectConversation()" aria-label="Back">
                    <x-lucide-arrow-left class="msg-back-icon" />
                </button>

                <div class="msg-chat-header-avatar" id="chatHeaderAvatar"></div>

                <div class="msg-chat-header-info">
                    <div class="msg-chat-header-name" id="chatHeaderName"></div>
                    <div class="msg-chat-header-sub" id="chatHeaderSub"></div>
                </div>

                <div class="msg-chat-header-badge" id="chatHeaderBadge"></div>

                {{-- Conversation Options Menu --}}
                <div class="msg-chat-header-actions" id="chatHeaderActions">
                    <div class="msg-header-dropdown-wrap" id="headerDropdownWrap">
                        <button type="button" class="msg-header-action-btn" id="btnConvMenu" onclick="toggleConvMenu(event)" aria-label="Conversation options" title="Conversation options">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="1.5"></circle>
                                <circle cx="12" cy="5" r="1.5"></circle>
                                <circle cx="12" cy="19" r="1.5"></circle>
                            </svg>
                        </button>
                        <div class="msg-header-dropdown-menu" id="convDropdownMenu" style="display: none;">
                            <button type="button" class="msg-dropdown-item text-danger" onclick="openDeleteConversationModal()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                                <span>Delete Conversation</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chat History --}}
            <div class="msg-chat-history" id="chatHistory">
                <!-- Messages injected by JS -->
            </div>

            {{-- Input Footer --}}
            <div class="msg-chat-footer">
                <div class="msg-input-wrap">
                    <textarea
                        id="msgInput"
                        class="msg-input"
                        placeholder="Type your reply here..."
                        rows="1"
                        oninput="autoResizeTextarea(this)"
                        onkeydown="handleInputKeydown(event)"
                    ></textarea>
                    <button class="msg-send-btn" onclick="sendMessage()" aria-label="Send Message">
                        <x-lucide-send class="msg-send-icon" />
                        <span>Send</span>
                    </button>
                </div>
                <p class="msg-footer-hint">Press <kbd>Ctrl</kbd>+<kbd>Enter</kbd> to send</p>
            </div>
        </div>

        {{-- ======================================================
             MODALS — Delete Message & Delete Conversation
        ====================================================== --}}
        {{-- Delete Message Confirmation Modal --}}
        <div class="modal-backdrop" id="deleteMessageModal" style="display: none;" onclick="closeModalOnBackdrop(event, 'deleteMessageModal')">
            <div class="modal-dialog msg-action-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="delMsgModalTitle">
                <div class="msg-modal-header">
                    <div class="msg-modal-icon-wrap danger">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <h3 class="msg-modal-title" id="delMsgModalTitle">Delete Message</h3>
                        <p class="msg-modal-desc">Choose how you want to delete this message.</p>
                    </div>
                    <button type="button" class="msg-modal-close" onclick="closeDeleteMessageModal()" aria-label="Close">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="msg-modal-body">
                    <p class="msg-preview-quote" id="delMsgPreview"></p>
                </div>
                <div class="msg-modal-footer">
                    <button type="button" class="btn-msg-modal-cancel" onclick="closeDeleteMessageModal()">Cancel</button>
                    <button type="button" class="btn-msg-modal-secondary" onclick="confirmDeleteMessage('for_me')">Delete for Me</button>
                    <button type="button" class="btn-msg-modal-danger" onclick="confirmDeleteMessage('for_everyone')">Delete for Everyone</button>
                </div>
            </div>
        </div>

        {{-- Delete Conversation Confirmation Modal --}}
        <div class="modal-backdrop" id="deleteConversationModal" style="display: none;" onclick="closeModalOnBackdrop(event, 'deleteConversationModal')">
            <div class="modal-dialog msg-action-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="delConvModalTitle">
                <div class="msg-modal-header">
                    <div class="msg-modal-icon-wrap danger">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <h3 class="msg-modal-title" id="delConvModalTitle">Delete Conversation</h3>
                        <p class="msg-modal-desc">This action will hide the conversation thread.</p>
                    </div>
                    <button type="button" class="msg-modal-close" onclick="closeDeleteConversationModal()" aria-label="Close">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="msg-modal-body">
                    <p id="delConvMessage" style="margin: 0; line-height: 1.5; color: #4A2E2B;">Are you sure you want to delete this conversation? This will hide/remove all messages in this thread.</p>
                </div>
                <div class="msg-modal-footer">
                    <button type="button" class="btn-msg-modal-cancel" onclick="closeDeleteConversationModal()">Cancel</button>
                    <button type="button" class="btn-msg-modal-danger" id="btnConfirmDelConv" onclick="confirmDeleteConversation()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        <span>Delete Conversation</span>
                    </button>
                </div>
            </div>
        </div>
        @endauth
    </section>

</div>
@endsection

@auth
@push('scripts')
<script>
/* =========================================================
   KUPKOP MESSAGES — Database-backed State & Real-time Logic
   ========================================================= */

// ── State ─────────────────────────────────────────────────
const ME = {
    id: {{ Auth::id() }},
    name: @json(Auth::user()->name),
    initials: @json(mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)))
};

let conversations = @json($conversations ?? []);
let activeId = @json($activePartnerId ?? null);
const targetPet = @json($targetPet ?? '');
const targetRecipient = @json($targetRecipient ?? '');
const csrfToken = '{{ csrf_token() }}';

let editingMessageId = null;
let deletingMessageId = null;
let targetDeleteConvId = null;

// ── Helpers ────────────────────────────────────────────────
function formatHumanTime(dateInput) {
    if (!dateInput) return '';
    const date = (dateInput instanceof Date) ? dateInput : new Date(dateInput);
    if (isNaN(date.getTime())) return String(dateInput);

    // Format strictly in Philippine Standard Time (PST / Asia/Manila, UTC+8)
    const tz = 'Asia/Manila';
    const now = new Date();

    const pstDateStr = date.toLocaleDateString('en-CA', { timeZone: tz });
    const pstNowStr = now.toLocaleDateString('en-CA', { timeZone: tz });

    const yesterday = new Date(now.getTime() - 86400000);
    const pstYesterdayStr = yesterday.toLocaleDateString('en-CA', { timeZone: tz });

    const timeStr = date.toLocaleTimeString('en-US', {
        timeZone: tz,
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });

    if (pstDateStr === pstNowStr) {
        return `Today at ${timeStr}`;
    }
    if (pstDateStr === pstYesterdayStr) {
        return `Yesterday at ${timeStr}`;
    }

    const monthStr = date.toLocaleDateString('en-US', { timeZone: tz, month: 'short' });
    const dayStr = date.toLocaleDateString('en-US', { timeZone: tz, day: 'numeric' });
    const yearStr = date.toLocaleDateString('en-US', { timeZone: tz, year: 'numeric' });
    const nowYearStr = now.toLocaleDateString('en-US', { timeZone: tz, year: 'numeric' });

    if (yearStr === nowYearStr) {
        return `${monthStr} ${dayStr}, ${timeStr}`;
    }
    return `${monthStr} ${dayStr}, ${yearStr}, ${timeStr}`;
}

function timeNow() {
    return formatHumanTime(new Date());
}

function escapeHTML(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Move the given conversation to the top of the list
function sortToTop(id) {
    const idx = conversations.findIndex(c => c.id === id);
    if (idx > 0) {
        const [conv] = conversations.splice(idx, 1);
        conversations.unshift(conv);
    }
}

// Update unread notification badges (navbar and sidebar)
function updateUnreadBadges(count) {
    const totalUnread = typeof count === 'number' 
        ? count 
        : conversations.reduce((sum, c) => sum + (c.unread || 0), 0);

    const sideBadge = document.getElementById('totalBadge');
    if (sideBadge) {
        if (totalUnread > 0) {
            sideBadge.textContent = totalUnread > 99 ? '99+' : totalUnread;
            sideBadge.style.display = 'inline-flex';
        } else {
            sideBadge.textContent = '0';
            sideBadge.style.display = 'none';
        }
    }

    const navBadge = document.getElementById('navMsgBadge');
    if (navBadge) {
        if (totalUnread > 0) {
            navBadge.textContent = totalUnread > 99 ? '99+' : totalUnread;
            navBadge.style.display = 'flex';
            navBadge.setAttribute('data-count', totalUnread);
        } else {
            navBadge.textContent = '0';
            navBadge.style.display = 'none';
            navBadge.setAttribute('data-count', '0');
        }
    }
}

// ── Render Sidebar ─────────────────────────────────────────
function renderSidebar(filter = '') {
    const list = document.getElementById('convList');
    if (!list) return;
    const prevScroll = list.scrollTop;

    const lc = filter.toLowerCase();
    const filtered = conversations.filter(c =>
        (c.name || '').toLowerCase().includes(lc) || (c.subject || '').toLowerCase().includes(lc)
    );

    updateUnreadBadges();

    if (conversations.length === 0) {
        list.innerHTML = `
            <li class="msg-conv-empty">
                <div style="font-size:2rem;margin-bottom:0.5rem;">💬</div>
                <div style="font-weight:600;color:#6B4C48;margin-bottom:0.3rem;">No messages yet</div>
                <div style="font-size:0.78rem;color:#9CA3AF;">When you contact a pet rescuer or someone inquires about your pets, your chats will appear here.</div>
            </li>`;
        return;
    }

    if (filtered.length === 0) {
        list.innerHTML = `<li class="msg-conv-empty">No conversations match your search.</li>`;
        return;
    }

    list.innerHTML = filtered.map(c => `
        <li class="msg-conv-item ${c.id === activeId ? 'active' : ''} ${c.unread > 0 ? 'has-unread' : ''}"
            role="option"
            aria-selected="${c.id === activeId}"
            onclick="selectConversation(${c.id})">
            <div class="msg-conv-avatar" style="background:${c.color}">${c.initials}</div>
            <div class="msg-conv-info">
                <div class="msg-conv-meta">
                    <span class="msg-conv-name">${escapeHTML(c.name)}</span>
                    <span class="msg-conv-time">${escapeHTML(c.time || '')}</span>
                </div>
                <div class="msg-conv-subject">${escapeHTML(c.subject || 'Adoption Inquiry')}</div>
                <div class="msg-conv-preview">${escapeHTML(c.lastMsg || 'No messages yet')}</div>
            </div>
            ${c.unread > 0 ? `<span class="msg-unread-badge">${c.unread}</span>` : ''}
            <button type="button" class="msg-conv-delete-btn" onclick="openDeleteConversationModal(${c.id}, event)" title="Delete conversation" aria-label="Delete conversation">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
        </li>
    `).join('');

    list.scrollTop = prevScroll;
}

// ── Render Chat History ────────────────────────────────────
function renderChat(conv, autoScroll = true) {
    const history = document.getElementById('chatHistory');
    if (!history) return;

    // Header
    const avatarEl = document.getElementById('chatHeaderAvatar');
    if (avatarEl) {
        avatarEl.style.background = conv.color;
        avatarEl.textContent = conv.initials;
    }
    const nameEl = document.getElementById('chatHeaderName');
    if (nameEl) nameEl.textContent = conv.name;

    const subEl = document.getElementById('chatHeaderSub');
    if (subEl) subEl.textContent = `Topic: ${conv.subject || 'Adoption Inquiry'}`;

    const badge = document.getElementById('chatHeaderBadge');
    if (badge) {
        if (conv.pet_name) {
            badge.textContent = '🐾 ' + conv.pet_name;
            badge.className = 'msg-chat-header-badge status-inquiry';
            badge.style.display = 'inline-block';
        } else if (conv.status) {
            badge.textContent = conv.status;
            badge.className = 'msg-chat-header-badge status-inquiry';
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    // Messages
    if (!conv.messages || conv.messages.length === 0) {
        history.innerHTML = `
            <div style="text-align:center; padding: 3rem 1.5rem; color: #6B4C48;">
                <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">🐾</div>
                <div style="font-weight: 700; font-size: 1.05rem; margin-bottom: 0.35rem;">Start your conversation with ${escapeHTML(conv.name)}</div>
                <div style="font-size: 0.85rem; color: #88706B; max-width: 380px; margin: 0 auto; line-height: 1.4;">
                    ${conv.pet_name ? `Ask about <strong>${escapeHTML(conv.pet_name)}</strong>, adoption requirements, or schedule a visit.` : 'Send a friendly greeting to start discussing pet adoptions.'}
                </div>
            </div>
        `;
        return;
    }

    history.innerHTML = conv.messages.map((m) => {
        let statusIndicator = '';
        if (m.mine) {
            if (m.is_read) {
                statusIndicator = `
                    <span class="msg-status-indicator is-read" title="Seen by recipient">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 7 17l-5-5"/><path d="m22 10-7.5 7.5L13 16"/></svg>
                        <span>Read</span>
                    </span>
                `;
            } else {
                statusIndicator = `
                    <span class="msg-status-indicator is-sent" title="Saved & delivered">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Sent</span>
                    </span>
                `;
            }
        }

        const displayTime = m.time || (m.created_at ? formatHumanTime(m.created_at) : '');
        const editedTag = m.edited_at ? `<span class="msg-edited-label" title="Edited at ${escapeHTML(formatHumanTime(m.edited_at))}">(edited)</span>` : '';

        // If this message is currently being edited
        const isEditing = (editingMessageId === m.id);

        let bubbleHtml = '';
        if (m.deleted_for_everyone) {
            bubbleHtml = `
                <div class="msg-bubble msg-bubble-deleted">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    <span>This message was deleted</span>
                </div>
            `;
        } else if (isEditing) {
            bubbleHtml = `
                <div class="msg-inline-edit" id="inlineEdit-${m.id}">
                    <textarea
                        id="editInput-${m.id}"
                        class="msg-inline-edit-input"
                        rows="1"
                        placeholder="Edit message..."
                        oninput="autoResizeInlineEdit(this)"
                        onkeydown="handleEditKeydown(event, ${m.id})"
                    >${escapeHTML(m.text)}</textarea>
                    <div class="msg-inline-edit-actions">
                        <button type="button" class="btn-edit-cancel" onclick="cancelEditMessage(${m.id})">Cancel</button>
                        <button type="button" class="btn-edit-save" onclick="saveEditMessage(${m.id})">Save</button>
                    </div>
                </div>
            `;
        } else {
            const actionsHtml = (m.mine && !m.deleted_for_everyone) ? `
                <div class="msg-bubble-actions">
                    <button type="button" class="msg-action-btn" onclick="startEditMessage(${m.id})" title="Edit message" aria-label="Edit message">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </button>
                    <button type="button" class="msg-action-btn text-danger" onclick="openDeleteMessageModal(${m.id})" title="Delete message" aria-label="Delete message">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
            ` : '';

            bubbleHtml = `
                <div class="msg-bubble-content-wrap">
                    <div class="msg-bubble ${m.mine ? 'msg-bubble-sent' : 'msg-bubble-received'}">
                        ${escapeHTML(m.text)}
                    </div>
                    ${actionsHtml}
                </div>
            `;
        }

        return `
            <div class="msg-bubble-row ${m.mine ? 'mine' : 'theirs'}" id="msgRow-${m.id}">
                ${!m.mine ? `<div class="msg-bubble-avatar" style="background:${m.color || conv.color}">${m.initials || conv.initials}</div>` : ''}
                <div class="msg-bubble-wrap">
                    <div class="msg-bubble-meta">
                        ${m.mine ? `
                            ${statusIndicator}
                            <span class="msg-bubble-time">${escapeHTML(displayTime)}${editedTag} •</span>
                            <span class="msg-bubble-sender">You</span>
                        ` : `
                            <span class="msg-bubble-sender">${escapeHTML(m.sender || conv.name)}</span>
                            <span class="msg-bubble-time">• ${escapeHTML(displayTime)}${editedTag}</span>
                        `}
                    </div>
                    ${bubbleHtml}
                </div>
                ${m.mine ? `<div class="msg-bubble-avatar" style="background:#4A2E2B">${ME.initials}</div>` : ''}
            </div>
        `;
    }).join('');

    if (autoScroll) {
        scrollToBottom();
    }
}

function scrollToBottom(smooth = false) {
    const h = document.getElementById('chatHistory');
    if (!h) return;
    requestAnimationFrame(() => {
        if (smooth) {
            h.scrollTo({ top: h.scrollHeight, behavior: 'smooth' });
        } else {
            h.scrollTop = h.scrollHeight;
        }
    });
}

// ── Select Conversation ────────────────────────────────────
async function selectConversation(id) {
    activeId = id;
    editingMessageId = null;
    const conv = conversations.find(c => c.id === id);
    if (!conv) return;

    // Immediately clear unread locally
    conv.unread = 0;
    updateUnreadBadges();

    // Mark as read in DB
    try {
        fetch(`/messages/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            if (data && typeof data.unread_total !== 'undefined') {
                updateUnreadBadges(data.unread_total);
            }
        }).catch(() => {});
    } catch(e) {}

    // Show panel
    const emptyState = document.getElementById('msgEmptyState');
    if (emptyState) emptyState.style.display = 'none';

    const panel = document.getElementById('msgChatPanel');
    if (panel) panel.style.display = 'flex';

    // On mobile: hide sidebar, show chat
    const sidebar = document.getElementById('msgSidebar');
    if (sidebar) sidebar.classList.add('mobile-hidden');

    const chatArea = document.getElementById('msgChatArea');
    if (chatArea) chatArea.classList.add('mobile-active');

    renderChat(conv);

    const searchInput = document.getElementById('convSearch');
    renderSidebar(searchInput ? searchInput.value : '');

    const input = document.getElementById('msgInput');
    if (input) input.focus();
}

function deselectConversation() {
    activeId = null;
    editingMessageId = null;
    const sidebar = document.getElementById('msgSidebar');
    if (sidebar) sidebar.classList.remove('mobile-hidden');

    const chatArea = document.getElementById('msgChatArea');
    if (chatArea) chatArea.classList.remove('mobile-active');

    const emptyState = document.getElementById('msgEmptyState');
    if (emptyState) emptyState.style.display = 'flex';

    const panel = document.getElementById('msgChatPanel');
    if (panel) panel.style.display = 'none';

    renderSidebar();
}

// ── Send Message ───────────────────────────────────────────
let isSending = false;
async function sendMessage() {
    if (isSending) return;
    const input = document.getElementById('msgInput');
    if (!input) return;
    const text  = input.value.trim();
    if (!text || activeId === null) return;

    const conv = conversations.find(c => c.id === activeId);
    if (!conv) return;

    isSending = true;
    const nowTime = timeNow();
    const tempMsg = {
        sender:               ME.name,
        initials:             ME.initials,
        color:                '#4A2E2B',
        text:                 text,
        time:                 nowTime,
        mine:                 true,
        is_read:              false,
        created_at:           new Date().toISOString(),
        deleted_for_everyone: false,
        edited_at:            null
    };

    if (!conv.messages) conv.messages = [];
    // Place at the bottom because messages are in chronological ascending order
    conv.messages.push(tempMsg);
    conv.lastMsg = text;
    conv.time    = nowTime;
    delete conv.is_new;

    // Bubble this conversation to the top of the sidebar
    sortToTop(activeId);
    renderChat(conv);
    scrollToBottom(true);

    const searchInput = document.getElementById('convSearch');
    renderSidebar(searchInput ? searchInput.value : '');

    input.value = '';
    input.style.height = 'auto';

    try {
        const response = await fetch("{{ route('messages.send') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: activeId,
                message: text,
                pet_id: conv.pet_id || null,
                subject: conv.subject || null
            })
        });

        const data = await response.json();
        if (data && data.success && data.message) {
            // Replace temporary message at the bottom with persisted message
            conv.messages[conv.messages.length - 1] = data.message;
            renderChat(conv);
            scrollToBottom(true);
        } else if (data && data.error) {
            alert(data.error);
        }
    } catch (err) {
        console.error('Failed to send message:', err);
        alert('Could not deliver message. Please check your connection and try again.');
    } finally {
        isSending = false;
    }
}

function handleInputKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

function autoResizeTextarea(el) {
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 140) + 'px';
}

function filterConversations(val) {
    renderSidebar(val);
}

// ── Search Suggestions Dropdown ────────────────────────────
function handleConvSearchInput(val) {
    renderSidebar(val);
    renderSearchDropdown(val);
}

function renderSearchDropdown(val) {
    const dropdown = document.getElementById('msgSearchDropdown');
    if (!dropdown) return;

    const term = (val || '').trim().toLowerCase();
    if (!term) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        return;
    }

    const matches = [];
    conversations.forEach(c => {
        const nameMatch = (c.name || '').toLowerCase().includes(term);
        const subjectMatch = (c.subject || '').toLowerCase().includes(term);
        const lastMsgMatch = (c.lastMsg || '').toLowerCase().includes(term);

        // Check if any specific message in conversation matches
        let matchingSnippet = '';
        if (c.messages && c.messages.length > 0) {
            for (let i = c.messages.length - 1; i >= 0; i--) {
                const text = c.messages[i].text || '';
                if (text.toLowerCase().includes(term)) {
                    matchingSnippet = text;
                    break;
                }
            }
        }
        if (!matchingSnippet && lastMsgMatch) {
            matchingSnippet = c.lastMsg;
        }

        if (nameMatch || subjectMatch || lastMsgMatch || matchingSnippet) {
            matches.push({
                conv: c,
                snippet: matchingSnippet || c.lastMsg || c.subject || 'Conversation'
            });
        }
    });

    if (matches.length === 0) {
        dropdown.innerHTML = `
            <div class="msg-dropdown-empty">
                <span>No matching conversations found</span>
            </div>
        `;
        dropdown.style.display = 'block';
        return;
    }

    dropdown.innerHTML = matches.slice(0, 6).map(item => {
        const c = item.conv;
        return `
            <div class="msg-dropdown-item" onclick="selectFromDropdown(${c.id})">
                <div class="msg-dropdown-avatar" style="background:${c.color}">${c.initials}</div>
                <div class="msg-dropdown-text">
                    <div class="msg-dropdown-name">${escapeHTML(c.name)}</div>
                    <div class="msg-dropdown-sub">${escapeHTML(c.subject || 'Adoption Inquiry')}</div>
                    <div class="msg-dropdown-snippet">${escapeHTML(item.snippet)}</div>
                </div>
                ${c.unread > 0 ? `<span class="msg-unread-badge" style="font-size:0.7rem;padding:2px 6px;">${c.unread}</span>` : ''}
            </div>
        `;
    }).join('');

    dropdown.style.display = 'block';
}

function selectFromDropdown(id) {
    const dropdown = document.getElementById('msgSearchDropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
    }
    const searchInput = document.getElementById('convSearch');
    if (searchInput) {
        searchInput.value = '';
    }
    renderSidebar('');
    selectConversation(id);
}

// Close search dropdown when clicking outside
document.addEventListener('click', function(e) {
    const wrap = document.querySelector('.msg-search-wrap');
    const dropdown = document.getElementById('msgSearchDropdown');
    if (dropdown && wrap && !wrap.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// ── Message Edit Logic ─────────────────────────────────────
function autoResizeInlineEdit(el) {
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
}

function startEditMessage(id) {
    editingMessageId = id;
    const conv = conversations.find(c => c.id === activeId);
    if (!conv) return;
    renderChat(conv, false);

    const textarea = document.getElementById(`editInput-${id}`);
    if (textarea) {
        autoResizeInlineEdit(textarea);
        textarea.focus();
        const len = textarea.value.length;
        textarea.setSelectionRange(len, len);
    }
}

function cancelEditMessage(id) {
    editingMessageId = null;
    const conv = conversations.find(c => c.id === activeId);
    if (conv) renderChat(conv);
}

function handleEditKeydown(e, id) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        saveEditMessage(id);
    } else if (e.key === 'Escape') {
        e.preventDefault();
        cancelEditMessage(id);
    }
}

async function saveEditMessage(id) {
    const input = document.getElementById(`editInput-${id}`);
    if (!input) return;
    const newText = input.value.trim();
    if (!newText) {
        alert('Message content cannot be empty.');
        return;
    }

    const conv = conversations.find(c => c.id === activeId);
    if (!conv) return;
    const msg = (conv.messages || []).find(m => m.id === id);
    if (!msg) return;

    if (newText === msg.text) {
        cancelEditMessage(id);
        return;
    }

    try {
        const res = await fetch(`/messages/${id}/edit`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: newText })
        });

        const data = await res.json();
        if (data && data.success) {
            msg.text = data.text;
            msg.edited_at = data.edited_at;

            // Update conversation last message preview if this was the latest message
            const lastM = conv.messages[conv.messages.length - 1];
            if (lastM && lastM.id === id) {
                conv.lastMsg = data.text;
                renderSidebar(document.getElementById('convSearch')?.value || '');
            }

            editingMessageId = null;
            renderChat(conv);
        } else if (data && data.error) {
            alert(data.error);
        }
    } catch (err) {
        console.error('Failed to edit message:', err);
        alert('Could not update message. Please check your connection and try again.');
    }
}

// ── Message Delete Logic ───────────────────────────────────
function openDeleteMessageModal(id) {
    const conv = conversations.find(c => c.id === activeId);
    if (!conv) return;
    const msg = (conv.messages || []).find(m => m.id === id);
    if (!msg) return;

    deletingMessageId = id;
    const previewEl = document.getElementById('delMsgPreview');
    if (previewEl) {
        previewEl.textContent = msg.text;
    }

    const modal = document.getElementById('deleteMessageModal');
    if (modal) modal.style.display = 'flex';
}

function closeDeleteMessageModal() {
    deletingMessageId = null;
    const modal = document.getElementById('deleteMessageModal');
    if (modal) modal.style.display = 'none';
}

async function confirmDeleteMessage(mode) {
    if (!deletingMessageId) return;
    const id = deletingMessageId;
    const conv = conversations.find(c => c.id === activeId);
    if (!conv) return;

    try {
        const res = await fetch(`/messages/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ mode: mode })
        });

        const data = await res.json();
        if (data && data.success) {
            closeDeleteMessageModal();

            if (mode === 'for_everyone') {
                const msg = (conv.messages || []).find(m => m.id === id);
                if (msg) {
                    msg.deleted_for_everyone = true;
                    msg.text = 'This message was deleted';
                }
            } else {
                // Delete for Me: remove from conv.messages
                conv.messages = (conv.messages || []).filter(m => m.id !== id);
            }

            // Update conversation last message preview
            if (conv.messages.length > 0) {
                const last = conv.messages[conv.messages.length - 1];
                conv.lastMsg = last.deleted_for_everyone ? 'This message was deleted' : last.text;
                conv.time = last.time || (last.created_at ? formatHumanTime(last.created_at) : '');
            } else {
                conv.lastMsg = 'No messages yet';
            }

            renderChat(conv);
            renderSidebar(document.getElementById('convSearch')?.value || '');
        } else if (data && data.error) {
            alert(data.error);
        }
    } catch (err) {
        console.error('Failed to delete message:', err);
        alert('Could not delete message. Please try again.');
    }
}

// ── Conversation Options & Delete Logic ─────────────────────
function toggleConvMenu(e) {
    e.stopPropagation();
    const menu = document.getElementById('convDropdownMenu');
    if (!menu) return;
    menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'flex' : 'none';
}

function openDeleteConversationModal(partnerId = null, event = null) {
    if (event) event.stopPropagation();

    // Close header dropdown if open
    const menu = document.getElementById('convDropdownMenu');
    if (menu) menu.style.display = 'none';

    targetDeleteConvId = partnerId !== null ? partnerId : activeId;
    if (!targetDeleteConvId) return;

    const conv = conversations.find(c => c.id === targetDeleteConvId);
    const convName = conv ? conv.name : 'this user';

    const msgEl = document.getElementById('delConvMessage');
    if (msgEl) {
        msgEl.innerHTML = `Are you sure you want to delete this conversation with <strong>${escapeHTML(convName)}</strong>? This will hide/remove all messages in this thread.`;
    }

    const modal = document.getElementById('deleteConversationModal');
    if (modal) modal.style.display = 'flex';
}

function closeDeleteConversationModal() {
    targetDeleteConvId = null;
    const modal = document.getElementById('deleteConversationModal');
    if (modal) modal.style.display = 'none';
}

async function confirmDeleteConversation() {
    if (!targetDeleteConvId) return;
    const partnerId = targetDeleteConvId;

    try {
        const res = await fetch(`/messages/${partnerId}/conversation`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const data = await res.json();
        if (data && data.success) {
            closeDeleteConversationModal();

            // Remove from local conversations list
            const idx = conversations.findIndex(c => c.id === partnerId);
            if (idx !== -1) {
                conversations.splice(idx, 1);
            }

            // If the deleted conversation was currently active, switch or deselect
            if (activeId === partnerId) {
                if (conversations.length > 0) {
                    selectConversation(conversations[0].id);
                } else {
                    deselectConversation();
                }
            } else {
                renderSidebar(document.getElementById('convSearch')?.value || '');
            }

            updateUnreadBadges();
        } else if (data && data.error) {
            alert(data.error);
        }
    } catch (err) {
        console.error('Failed to delete conversation:', err);
        alert('Could not delete conversation. Please try again.');
    }
}

function closeModalOnBackdrop(e, modalId) {
    if (e.target.id === modalId) {
        if (modalId === 'deleteMessageModal') closeDeleteMessageModal();
        if (modalId === 'deleteConversationModal') closeDeleteConversationModal();
    }
}

// ── Global Event Listeners ─────────────────────────────────
document.addEventListener('click', (e) => {
    const wrap = document.getElementById('headerDropdownWrap');
    const menu = document.getElementById('convDropdownMenu');
    if (menu && menu.style.display !== 'none') {
        if (!wrap || !wrap.contains(e.target)) {
            menu.style.display = 'none';
        }
    }
});

// ── Real-time Thread Polling ───────────────────────────────
async function pollThread() {
    if (!activeId) return;
    try {
        const res = await fetch(`/messages/${activeId}/poll`, {
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();
        const conv = conversations.find(c => c.id === activeId);
        if (!conv) return;

        const prevLen = (conv.messages || []).length;
        const newLen = (data.messages || []).length;

        // Check if messages changed, edited, deleted, or read receipts updated
        let changed = (newLen !== prevLen);
        if (!changed && newLen > 0) {
            for (let idx = 0; idx < newLen; idx++) {
                if (
                    Boolean(conv.messages[idx]?.is_read) !== Boolean(data.messages[idx]?.is_read) ||
                    conv.messages[idx]?.text !== data.messages[idx]?.text ||
                    Boolean(conv.messages[idx]?.deleted_for_everyone) !== Boolean(data.messages[idx]?.deleted_for_everyone) ||
                    conv.messages[idx]?.edited_at !== data.messages[idx]?.edited_at
                ) {
                    changed = true;
                    break;
                }
            }
        }

        if (changed) {
            // If user is currently editing, don't overwrite if they are on an active input
            const activeEdit = editingMessageId;
            conv.messages = data.messages;
            if (newLen > 0) {
                // In ascending chronological order, latest message is at index newLen - 1
                const latest = data.messages[newLen - 1];
                conv.lastMsg = latest.deleted_for_everyone ? 'This message was deleted' : latest.text;
                conv.time = latest.time;
            }

            const historyEl = document.getElementById('chatHistory');
            const wasNearBottom = historyEl ? (historyEl.scrollHeight - historyEl.scrollTop - historyEl.clientHeight < 120) : true;
            const prevScrollTop = historyEl ? historyEl.scrollTop : 0;

            if (!activeEdit) {
                renderChat(conv, false);
                if (wasNearBottom) {
                    scrollToBottom(true);
                } else if (historyEl) {
                    historyEl.scrollTop = prevScrollTop;
                }
            }
            sortToTop(activeId);
            renderSidebar(document.getElementById('convSearch')?.value || '');

            // Mark received as read since current user is actively chatting
            fetch(`/messages/${activeId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).catch(() => {});
        }

        if (typeof data.unread_total !== 'undefined') {
            updateUnreadBadges(data.unread_total);
        }
    } catch (e) {
        // Silently skip background poll network errors
    }
}

// ── Init ───────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Track whether a specific partner was set via URL query param
    const hadExplicitPartner = activeId !== null;

    // On mobile, show the conversation list first unless a specific recipient was requested
    const isMobile = window.matchMedia('(max-width: 768px)').matches;

    // On desktop, auto-select the first conversation if none was explicitly requested
    if (!isMobile && activeId === null && conversations.length > 0) {
        activeId = conversations[0].id;
    }

    renderSidebar();
    updateUnreadBadges();

    if (activeId !== null && (!isMobile || hadExplicitPartner)) {
        selectConversation(activeId);

        // Pre-fill courteous inquiry if opening with a pet context and no messages sent yet
        const input = document.getElementById('msgInput');
        const activeConv = conversations.find(c => c.id === activeId);
        if (input && activeConv && (!activeConv.messages || activeConv.messages.length === 0)) {
            const petContext = targetPet || activeConv.pet_name;
            if (petContext) {
                input.value = `Hi ${activeConv.name}, I saw ${petContext} on Kupkop and would love to ask a few questions about adopting!`;
                autoResizeTextarea(input);
                input.focus();
            }
        }
    }

    // Live sync polling every 8 seconds (optimized for serverless DB)
    setInterval(pollThread, 8000);

    // Expose hook so the global layout poller (app.blade.php, every 8 s) can
    // feed unread counts into the messages page without losing the user's scroll position.
    window.kupGlobalUnreadUpdate = function (total, unreadsBySender = {}) {
        updateUnreadBadges(total);
        if (unreadsBySender && typeof unreadsBySender === 'object') {
            let sidebarNeedsUpdate = false;
            conversations.forEach(c => {
                if (c.id !== activeId) {
                    const newUnread = unreadsBySender[c.id] || 0;
                    if (c.unread !== newUnread) {
                        c.unread = newUnread;
                        sidebarNeedsUpdate = true;
                    }
                }
            });
            if (sidebarNeedsUpdate) {
                renderSidebar(document.getElementById('convSearch')?.value || '');
            }
        }
    };
});
</script>
@endpush
@endauth
