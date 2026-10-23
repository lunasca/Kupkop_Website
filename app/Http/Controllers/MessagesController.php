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
    public function index(Request $request): View
    {
        $authId = Auth::id();

        // 1. Fetch all messages involving the authenticated user in chronological order
        $allMessages = Message::with(['sender', 'receiver', 'pet'])
            ->where(function ($q) use ($authId) {
                $q->where('sender_id', $authId)
                  ->orWhere('receiver_id', $authId);
            })
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 2. Group messages by conversation partner
        $threadsByPartner = [];
        $palette = ['#4A2E2B', '#854D0E', '#B45309', '#047857', '#0284C7', '#7C3AED', '#C2410C', '#0D9488'];

        foreach ($allMessages as $msg) {
            $isSender = ($msg->sender_id === $authId);
            $partner = $isSender ? $msg->receiver : $msg->sender;

            if (!$partner) {
                continue;
            }

            $partnerId = $partner->id;

            if (!isset($threadsByPartner[$partnerId])) {
                $pName = $partner->name ?? 'User';
                $hash = crc32($pName);
                $color = $palette[abs($hash) % count($palette)];
                $initials = mb_strtoupper(mb_substr($pName, 0, 1));

                $threadsByPartner[$partnerId] = [
                    'id' => $partnerId,
                    'partner_id' => $partnerId,
                    'name' => $pName,
                    'initials' => $initials,
                    'color' => $color,
                    'subject' => $msg->subject ?? ($msg->pet ? "Inquiry: {$msg->pet->name}" : 'Adoption Inquiry'),
                    'pet_id' => $msg->pet_id,
                    'pet_name' => $msg->pet?->name,
                    'status' => 'Inquiry',
                    'time' => Message::formatHumanTimestamp($msg->created_at),
                    'lastMsg' => $msg->message,
                    'unread' => 0,
                    'messages' => [],
                    'updated_at' => $msg->created_at,
                ];
            }

            // Count unread incoming messages
            if (!$isSender && !$msg->is_read) {
                $threadsByPartner[$partnerId]['unread']++;
            }

            // Update latest message info for the conversation thread in the sidebar
            $threadsByPartner[$partnerId]['lastMsg'] = $msg->message;
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
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender' => $msg->sender->name ?? 'User',
                'initials' => mb_strtoupper(mb_substr($msg->sender->name ?? 'U', 0, 1)),
                'color' => ($msg->sender_id === $authId) ? '#4A2E2B' : $threadsByPartner[$partnerId]['color'],
                'text' => $msg->message,
                'time' => Message::formatHumanTimestamp($msg->created_at),
                'mine' => ($msg->sender_id === $authId),
                'is_read' => (bool) $msg->is_read,
                'created_at' => $msg->created_at?->toIso8601String(),
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
                    'id' => $targetPartnerId,
                    'partner_id' => $targetPartnerId,
                    'name' => $targetUser->name,
                    'initials' => $initials,
                    'color' => $color,
                    'subject' => $subject,
                    'pet_id' => $targetPetId ?: null,
                    'pet_name' => $targetPetName ?: null,
                    'status' => 'Inquiry',
                    'time' => 'New',
                    'lastMsg' => 'Start conversation...',
                    'unread' => 0,
                    'messages' => [],
                    'updated_at' => now(),
                    'is_new' => true,
                ];
            }
        }

        // Sort threads by most recent first
        uasort($threadsByPartner, function ($a, $b) {
            return ($b['updated_at'] <=> $a['updated_at']);
        });

        $conversations = array_values($threadsByPartner);

        return view('pages.messages', [
            'conversations' => $conversations,
            'activePartnerId' => $targetPartnerId,
            'targetPet' => $targetPetName,
            'targetPetId' => $targetPetId,
            'targetRecipient' => $targetRecipientName ?: ($targetUser?->name ?? ''),
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
                'id'         => $message->id,
                'sender_id'  => $message->sender_id,
                'sender'     => $message->sender->name,
                'initials'   => mb_strtoupper(mb_substr($message->sender->name, 0, 1)),
                'color'      => '#4A2E2B',
                'text'       => $message->message,
                'time'       => Message::formatHumanTimestamp($message->created_at),
                'mine'       => true,
                'is_read'    => false,
                'created_at' => $message->created_at->toIso8601String(),
            ]
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
            'success' => true,
            'unread_total' => $remainingUnread,
        ]);
    }

    /**
     * Poll / fetch latest messages for a conversation thread in chronological order (oldest to newest).
     */
    public function pollMessages($partnerId, Request $request): JsonResponse
    {
        $authId = Auth::id();

        $messages = Message::with(['sender'])
            ->where(function ($q) use ($authId, $partnerId) {
                $q->where(function ($inner) use ($authId, $partnerId) {
                    $inner->where('sender_id', $authId)->where('receiver_id', $partnerId);
                })->orWhere(function ($inner) use ($authId, $partnerId) {
                    $inner->where('sender_id', $partnerId)->where('receiver_id', $authId);
                });
            })
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $formatted = $messages->map(function ($m) use ($authId) {
            return [
                'id'         => $m->id,
                'sender_id'  => $m->sender_id,
                'sender'     => $m->sender->name ?? 'User',
                'initials'   => mb_strtoupper(mb_substr($m->sender->name ?? 'U', 0, 1)),
                'text'       => $m->message,
                'time'       => Message::formatHumanTimestamp($m->created_at),
                'mine'       => ($m->sender_id === $authId),
                'is_read'    => (bool) $m->is_read,
                'created_at' => $m->created_at?->toIso8601String(),
            ];
        });

        $unreadCount = Message::where('receiver_id', $authId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'messages' => $formatted,
            'unread_total' => $unreadCount,
        ]);
    }
}
