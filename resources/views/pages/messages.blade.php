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
            <div class="msg-search-wrap">
                <x-lucide-search class="msg-search-icon" />
                <input
                    type="text"
                    id="convSearch"
                    class="msg-search-input"
                    placeholder="Search conversations..."
                    oninput="filterConversations(this.value)"
                    autocomplete="off"
                />
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
        </li>
    `).join('');
}

// ── Render Chat History ────────────────────────────────────
function renderChat(conv) {
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

        return `
            <div class="msg-bubble-row ${m.mine ? 'mine' : 'theirs'}">
                ${!m.mine ? `<div class="msg-bubble-avatar" style="background:${m.color || conv.color}">${m.initials || conv.initials}</div>` : ''}
                <div class="msg-bubble-wrap">
                    <div class="msg-bubble-meta">
                        ${m.mine ? `
                            ${statusIndicator}
                            <span class="msg-bubble-time">${escapeHTML(displayTime)} •</span>
                            <span class="msg-bubble-sender">You</span>
                        ` : `
                            <span class="msg-bubble-sender">${escapeHTML(m.sender || conv.name)}</span>
                            <span class="msg-bubble-time">• ${escapeHTML(displayTime)}</span>
                        `}
                    </div>
                    <div class="msg-bubble ${m.mine ? 'msg-bubble-sent' : 'msg-bubble-received'}">
                        ${escapeHTML(m.text)}
                    </div>
                </div>
                ${m.mine ? `<div class="msg-bubble-avatar" style="background:#4A2E2B">${ME.initials}</div>` : ''}
            </div>
        `;
    }).join('');

    scrollToBottom();
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
        sender:     ME.name,
        initials:   ME.initials,
        color:      '#4A2E2B',
        text:       text,
        time:       nowTime,
        mine:       true,
        is_read:    false,
        created_at: new Date().toISOString()
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

        // Check if messages changed or read receipts updated
        let changed = (newLen !== prevLen);
        if (!changed && newLen > 0) {
            // Check if any message's read status updated
            for (let idx = 0; idx < newLen; idx++) {
                if (Boolean(conv.messages[idx]?.is_read) !== Boolean(data.messages[idx]?.is_read)) {
                    changed = true;
                    break;
                }
            }
        }

        if (changed) {
            conv.messages = data.messages;
            if (newLen > 0) {
                // In ascending chronological order, latest message is at index newLen - 1
                const latest = data.messages[newLen - 1];
                conv.lastMsg = latest.text;
                conv.time = latest.time;
            }

            renderChat(conv);
            scrollToBottom(true);
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
    // If no specific thread selected via query param, auto-select the first conversation
    if (activeId === null && conversations.length > 0) {
        activeId = conversations[0].id;
    }

    renderSidebar();
    updateUnreadBadges();

    if (activeId !== null) {
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

    // Live sync polling every 3.5 seconds
    setInterval(pollThread, 3500);
});
</script>
@endpush
@endauth
