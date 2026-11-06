<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessagesController extends Controller
{
    /**
     * Display the Messages tab UI with database-backed threads.
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        // Administrators are restricted from regular user Messages page
        if (Auth::user()?->is_admin) {
            return redirect()->route('admin.dashboard')
                ->with('warning', 'Access to Messages is restricted for Admin accounts. Please use the Admin Panel.');
        }

        $authId = Auth::id();

        // 1. Fetch all messages involving the authenticated user in chronological order.
        //    Exclude messages the current user has "deleted for me" using the visibleTo scope.
        $allMessages = Message::select([
                'id', 'sender_id', 'receiver_id', 'pet_id', 'subject', 'message',
                'is_read', 'created_at', 'conv_deleted_by_sender_at',
                'conv_deleted_by_receiver_at', 'deleted_by_sender', 'deleted_by_receiver',
                'deleted_for_everyone', 'edited_at'
            ])
            ->with([
                'sender:id,name',
                'receiver:id,name',
                'pet:id,name,species,image_path'
            ])
            ->where(function ($q) use ($authId) {
                $q->where('sender_id', $authId)
                  ->orWhere('receiver_id', $authId);
            })
            ->visibleTo($authId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 2. Group messages by conversation partner
        $threadsByPartner = [];
        $palette = ['#4A2E2B', '#854D0E', '#B45309', '#047857', '#0284C7', '#7C3AED', '#C2410C', '#0D9488'];

        // Track which conversations the user has soft-deleted (deleted conversation)
        // A conversation is hidden if ALL messages in it have a conv_deleted_by_* timestamp
        // We track by partner: the latest conv_deleted_by_* timestamp for the user
        $convDeletedAt = [];

        foreach ($allMessages as $msg) {
            $isSender = ($msg->sender_id === $authId);
            $partner = $isSender ? $msg->receiver : $msg->sender;

            if (!$partner) {
                continue;
            }

            $partnerId = $partner->id;

            // Check if this message's conversation was deleted by the current user
            $deletedAt = $isSender
                ? $msg->conv_deleted_by_sender_at
                : $msg->conv_deleted_by_receiver_at;

            // Track the most recent deletion timestamp for this partner's conversation
            if ($deletedAt) {
                if (!isset($convDeletedAt[$partnerId]) || $deletedAt > $convDeletedAt[$partnerId]) {
                    $convDeletedAt[$partnerId] = $deletedAt;
                }
            }

            // Skip messages older than the conversation deletion timestamp for this user
            if (isset($convDeletedAt[$partnerId]) && $msg->created_at <= $convDeletedAt[$partnerId]) {
                continue;
            }

            if (!isset($threadsByPartner[$partnerId])) {
                $pName = $partner->name ?? 'User';
                $hash = crc32($pName);
                $color = $palette[abs($hash) % count($palette)];
                $initials = mb_strtoupper(mb_substr($pName, 0, 1));

                $threadsByPartner[$partnerId] = [
                    'id'         => $partnerId,
                    'partner_id' => $partnerId,
                    'name'       => $pName,
                    'initials'   => $initials,
                    'color'      => $color,
                    'subject'    => $msg->subject ?? ($msg->pet ? "Inquiry: {$msg->pet->name}" : 'Adoption Inquiry'),
                    'pet_id'     => $msg->pet_id,
                    'pet_name'   => $msg->pet?->name,
                    'status'     => 'Inquiry',
                    'time'       => Message::formatHumanTimestamp($msg->created_at),
                    'lastMsg'    => $msg->message,
                    'unread'     => 0,
                    'messages'   => [],
                    'updated_at' => $msg->created_at,
                ];
            }

            // Count unread incoming messages
            if (!$isSender && !$msg->is_read) {
                $threadsByPartner[$partnerId]['unread']++;
            }

            // Update latest message info for the conversation thread in the sidebar
            $displayText = $msg->deleted_for_everyone ? 'This message was deleted' : $msg->message;
            $threadsByPartner[$partnerId]['lastMsg'] = $displayText;
            $threadsByPartner[$partnerId]['time'] = Message::formatHumanTimestamp($msg->created_at);
            $threadsByPartner[$partnerId]['updated_at'] = $msg->created_at;
            if ($msg->subject) {
                $threadsByPartner[$partnerId]['subject'] = $msg->subject;
            }
            if ($msg->pet_id) {
                $threadsByPartner[$partnerId]['pet_id'] = $msg->pet_id;
                $threadsByPartner[$partnerId]['pet_name'] = $msg->pet?->name;
            }

            // Messages are ordered ascending (oldest at top, most recent at bottom)
            $threadsByPartner[$partnerId]['messages'][] = [
                'id'                  => $msg->id,
                'sender_id'           => $msg->sender_id,
                'sender'              => $msg->sender->name ?? 'User',
                'initials'            => mb_strtoupper(mb_substr($msg->sender->name ?? 'U', 0, 1)),
                'color'               => ($msg->sender_id === $authId) ? '#4A2E2B' : $threadsByPartner[$partnerId]['color'],
                'text'                => $msg->message,
                'time'                => Message::formatHumanTimestamp($msg->created_at),
                'mine'                => ($msg->sender_id === $authId),
                'is_read'             => (bool) $msg->is_read,
                'created_at'          => $msg->created_at?->toIso8601String(),
                'deleted_for_everyone'=> (bool) $msg->deleted_for_everyone,
                'edited_at'           => $msg->edited_at?->toIso8601String(),
            ];
        }

        // 3. Handle query params from "Contact Poster" or applicant messaging
        $targetPartnerId = null;
        $targetRecipientName = trim($request->query('recipient', ''));
        $targetRecipientId = $request->query('recipient_id');
        $targetPetId = $request->query('pet_id');
        $targetPetName = trim($request->query('pet', ''));

        $targetUser = null;
        if ($targetRecipientId && is_numeric($targetRecipientId)) {
            $targetUser = User::find($targetRecipientId);
        }

        if (!$targetUser && $targetPetId && is_numeric($targetPetId)) {
            $pet = Pet::find($targetPetId);
            if ($pet && $pet->user_id) {
                $targetUser = User::find($pet->user_id);
            }
        }

        if (!$targetUser && $targetRecipientName) {
            $targetUser = User::where('name', $targetRecipientName)->first();
        }

        if ($targetUser && $targetUser->id !== $authId) {
            $targetPartnerId = $targetUser->id;

            // Proactively mark unread messages from this partner as read
            Message::where('sender_id', $targetPartnerId)
                ->where('receiver_id', $authId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            if (isset($threadsByPartner[$targetPartnerId])) {
                $threadsByPartner[$targetPartnerId]['unread'] = 0;
            }

            // If a thread does not yet exist in database with this partner, create an active draft thread
            if (!isset($threadsByPartner[$targetPartnerId])) {
                $hash = crc32($targetUser->name);
                $color = $palette[abs($hash) % count($palette)];
                $initials = mb_strtoupper(mb_substr($targetUser->name, 0, 1));
                $subject = $targetPetName ? "Inquiry: {$targetPetName}" : 'Adoption Inquiry';

                $threadsByPartner[$targetPartnerId] = [
                    'id'         => $targetPartnerId,
                    'partner_id' => $targetPartnerId,
                    'name'       => $targetUser->name,
                    'initials'   => $initials,
                    'color'      => $color,
                    'subject'    => $subject,
                    'pet_id'     => $targetPetId ?: null,
                    'pet_name'   => $targetPetName ?: null,
                    'status'     => 'Inquiry',
                    'time'       => 'New',
                    'lastMsg'    => 'Start conversation...',
                    'unread'     => 0,
                    'messages'   => [],
                    'updated_at' => now(),
                    'is_new'     => true,
                ];
            }
        }

        // Sort threads by most recent first
        uasort($threadsByPartner, function ($a, $b) {
            return ($b['updated_at'] <=> $a['updated_at']);
        });

        $conversations = array_values($threadsByPartner);

        return view('pages.messages', [
            'conversations'  => $conversations,
            'activePartnerId'=> $targetPartnerId,
            'targetPet'      => $targetPetName,
            'targetPetId'    => $targetPetId,
            'targetRecipient'=> $targetRecipientName ?: ($targetUser?->name ?? ''),
        ]);
    }

    /**
     * Store and dispatch a new message to the database.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'message'     => ['required', 'string', 'max:3000'],
            'pet_id'      => ['nullable', 'exists:pets,id'],
            'subject'     => ['nullable', 'string', 'max:255'],
        ]);

        $authId = Auth::id();

        if (Auth::user()->status === 'blocked' || Auth::user()->is_blocked || Auth::user()->archived_at !== null) {
            return response()->json(['error' => 'Your account has been blocked or removed.'], 403);
        }

        $receiverId = (int) $request->receiver_id;

        if ($authId === $receiverId) {
            return response()->json(['error' => 'You cannot send a message to yourself.'], 422);
        }

        $message = Message::create([
            'sender_id'   => $authId,
            'receiver_id' => $receiverId,
            'pet_id'      => $request->pet_id ?: null,
            'subject'     => $request->subject ?: 'Inquiry',
            'message'     => trim($request->message),
            'is_read'     => false,
        ]);

        $message->load(['sender', 'receiver', 'pet']);

        return response()->json([
            'success' => true,
            'message' => [
                'id'                   => $message->id,
                'sender_id'            => $message->sender_id,
                'sender'               => $message->sender->name,
                'initials'             => mb_strtoupper(mb_substr($message->sender->name, 0, 1)),
                'color'                => '#4A2E2B',
                'text'                 => $message->message,
                'time'                 => Message::formatHumanTimestamp($message->created_at),
                'mine'                 => true,
                'is_read'              => false,
                'created_at'           => $message->created_at->toIso8601String(),
                'deleted_for_everyone' => false,
                'edited_at'            => null,
            ]
        ]);
    }

    /**
     * Return the total count of unread messages for the authenticated user.
     * Lightweight endpoint used by global background polling (no message content returned).
     */
    public function unreadCount(): JsonResponse
    {
        $authId = Auth::id();

        $count = Message::where('receiver_id', $authId)
            ->where('is_read', false)
            ->count();

        $unreadsBySender = Message::where('receiver_id', $authId)
            ->where('is_read', false)
            ->selectRaw('sender_id, count(*) as count')
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id')
            ->toArray();

        return response()->json([
            'unread_total'      => $count,
            'unreads_by_sender' => $unreadsBySender,
        ]);
    }

    /**
     * Mark all unread messages from a partner as read.
     */
    public function markAsRead($partnerId): JsonResponse
    {
        $authId = Auth::id();

        Message::where('sender_id', $partnerId)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $remainingUnread = Message::where('receiver_id', $authId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success'      => true,
            'unread_total' => $remainingUnread,
        ]);
    }

    /**
     * Poll / fetch latest messages for a conversation thread in chronological order (oldest to newest).
     * Respects per-message visibility (deleted for me / deleted for everyone) and conversation deletion.
     */
    public function pollMessages($partnerId, Request $request): JsonResponse
    {
        $authId = Auth::id();

        // Find the most recent "delete conversation" timestamp for this user/partner combo,
        // so we can exclude messages older than that timestamp
        $convDeletedAt = Message::where(function ($q) use ($authId, $partnerId) {
                $q->where('sender_id', $authId)->where('receiver_id', $partnerId);
            })->orWhere(function ($q) use ($authId, $partnerId) {
                $q->where('sender_id', $partnerId)->where('receiver_id', $authId);
            })
            ->where(function ($q) use ($authId) {
                $q->where(function ($inner) use ($authId) {
                    $inner->where('sender_id', $authId)->whereNotNull('conv_deleted_by_sender_at');
                })->orWhere(function ($inner) use ($authId) {
                    $inner->where('receiver_id', $authId)->whereNotNull('conv_deleted_by_receiver_at');
                });
            })
            ->selectRaw('MAX(CASE WHEN sender_id = ? THEN conv_deleted_by_sender_at ELSE conv_deleted_by_receiver_at END) as deleted_at', [$authId])
            ->value('deleted_at');

        $query = Message::select([
                'id', 'sender_id', 'receiver_id', 'message', 'is_read', 'created_at',
                'deleted_for_everyone', 'edited_at'
            ])
            ->with(['sender:id,name'])
            ->where(function ($q) use ($authId, $partnerId) {
                $q->where(function ($inner) use ($authId, $partnerId) {
                    $inner->where('sender_id', $authId)->where('receiver_id', $partnerId);
                })->orWhere(function ($inner) use ($authId, $partnerId) {
                    $inner->where('sender_id', $partnerId)->where('receiver_id', $authId);
                });
            })
            ->visibleTo($authId);

        // Filter out messages older than conversation deletion timestamp
        if ($convDeletedAt) {
            $query->where('created_at', '>', $convDeletedAt);
        }

        $messages = $query->orderBy('created_at', 'asc')->orderBy('id', 'asc')->get();

        $formatted = $messages->map(function ($m) use ($authId) {
            return [
                'id'                   => $m->id,
                'sender_id'            => $m->sender_id,
                'sender'               => $m->sender->name ?? 'User',
                'initials'             => mb_strtoupper(mb_substr($m->sender->name ?? 'U', 0, 1)),
                'text'                 => $m->message,
                'time'                 => Message::formatHumanTimestamp($m->created_at),
                'mine'                 => ($m->sender_id === $authId),
                'is_read'              => (bool) $m->is_read,
                'created_at'           => $m->created_at?->toIso8601String(),
                'deleted_for_everyone' => (bool) $m->deleted_for_everyone,
                'edited_at'            => $m->edited_at?->toIso8601String(),
            ];
        });

        $unreadCount = Message::where('receiver_id', $authId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'messages'     => $formatted,
            'unread_total' => $unreadCount,
        ]);
    }

    /**
     * Edit a message the authenticated user sent.
     * Saves the original content and marks the edited_at timestamp.
     */
    public function editMessage(Request $request, Message $message): JsonResponse
    {
        $authId = Auth::id();

        // Only the sender can edit their own messages
        if ($message->sender_id !== $authId) {
            return response()->json(['error' => 'You can only edit your own messages.'], 403);
        }

        // Cannot edit a message that was deleted for everyone
        if ($message->deleted_for_everyone) {
            return response()->json(['error' => 'Cannot edit a deleted message.'], 422);
        }

        $request->validate([
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $newText = trim($request->message);

        // Preserve the original message text only on the first edit
        if (is_null($message->original_message)) {
            $message->original_message = $message->message;
        }

        $message->message   = $newText;
        $message->edited_at = now();
        $message->save();

        return response()->json([
            'success'  => true,
            'id'       => $message->id,
            'text'     => $message->message,
            'edited_at'=> $message->edited_at->toIso8601String(),
            'time'     => Message::formatHumanTimestamp($message->created_at),
        ]);
    }

    /**
     * Delete a single message.
     * Supports two modes:
     *  - "for_me"      → soft-hides from the requesting user's view only
     *  - "for_everyone"→ replaces message content with a deleted placeholder for all parties
     */
    public function deleteMessage(Request $request, Message $message): JsonResponse
    {
        $authId = Auth::id();

        // Only the sender can delete their own messages
        if ($message->sender_id !== $authId) {
            return response()->json(['error' => 'You can only delete your own messages.'], 403);
        }

        $request->validate([
            'mode' => ['required', 'in:for_me,for_everyone'],
        ]);

        $mode = $request->mode;

        if ($mode === 'for_everyone') {
            // Replaces message for both parties — preserve original for audit trail
            if (is_null($message->original_message)) {
                $message->original_message = $message->message;
            }
            $message->deleted_for_everyone = true;
            $message->save();

            return response()->json([
                'success'              => true,
                'mode'                 => 'for_everyone',
                'id'                   => $message->id,
                'deleted_for_everyone' => true,
            ]);
        }

        // mode === 'for_me' — hide from sender's view
        $message->deleted_by_sender = true;
        $message->save();

        return response()->json([
            'success' => true,
            'mode'    => 'for_me',
            'id'      => $message->id,
        ]);
    }

    /**
     * Soft-delete an entire conversation from the authenticated user's perspective.
     * All existing messages between auth user and partner get a deletion timestamp set.
     * Any new messages sent after this timestamp will still appear normally.
     */
    public function deleteConversation(Request $request, $partnerId): JsonResponse
    {
        $authId = Auth::id();
        $partnerId = (int) $partnerId;

        if ($authId === $partnerId) {
            return response()->json(['error' => 'Invalid conversation.'], 422);
        }

        $now = now();

        // Set conv_deleted_by_sender_at on messages where auth user is the sender
        Message::where('sender_id', $authId)
            ->where('receiver_id', $partnerId)
            ->whereNull('conv_deleted_by_sender_at')
            ->update(['conv_deleted_by_sender_at' => $now]);

        // Set conv_deleted_by_receiver_at on messages where auth user is the receiver
        Message::where('sender_id', $partnerId)
            ->where('receiver_id', $authId)
            ->whereNull('conv_deleted_by_receiver_at')
            ->update(['conv_deleted_by_receiver_at' => $now]);

        return response()->json([
            'success'    => true,
            'partner_id' => $partnerId,
        ]);
    }
}
