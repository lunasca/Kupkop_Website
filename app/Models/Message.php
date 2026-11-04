<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'pet_id',
        'subject',
        'message',
        'is_read',
        // Edit tracking
        'original_message',
        'edited_at',
        // Per-message delete flags
        'deleted_by_sender',
        'deleted_by_receiver',
        'deleted_for_everyone',
        // Per-conversation soft-delete timestamps
        'conv_deleted_by_sender_at',
        'conv_deleted_by_receiver_at',
    ];

    protected $casts = [
        'is_read'                    => 'boolean',
        'deleted_by_sender'          => 'boolean',
        'deleted_by_receiver'        => 'boolean',
        'deleted_for_everyone'       => 'boolean',
        'edited_at'                  => 'datetime',
        'conv_deleted_by_sender_at'  => 'datetime',
        'conv_deleted_by_receiver_at'=> 'datetime',
    ];

    /**
     * Scope: filter messages visible to a given user.
     * A message is hidden from a user if they deleted it "for me", or if it was deleted for everyone.
     * Note: "deleted for everyone" still shows a placeholder — filtering for that is done in formatting.
     */
    public function scopeVisibleTo($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            // If user is the sender: exclude messages they deleted for themselves
            $q->where(function ($inner) use ($userId) {
                $inner->where('sender_id', $userId)
                      ->where('deleted_by_sender', false);
            })
            // If user is the receiver: exclude messages they deleted for themselves
            ->orWhere(function ($inner) use ($userId) {
                $inner->where('receiver_id', $userId)
                      ->where('deleted_by_receiver', false);
            });
        });
    }

    /**
     * Format a timestamp into human-readable representation using Philippine Standard Time (Asia/Manila, UTC+8):
     * - 'Today at 10:30 AM'
     * - 'Yesterday at 4:15 PM'
     * - 'Oct 22, 2:50 PM'
     */
    public static function formatHumanTimestamp($dateTime): string
    {
        if (!$dateTime) {
            return '';
        }

        // Ensure timestamp is evaluated in Philippine Standard Time (PST, UTC+8)
        $dt = $dateTime instanceof \Carbon\Carbon
            ? $dateTime->copy()->setTimezone('Asia/Manila')
            : \Carbon\Carbon::parse($dateTime)->setTimezone('Asia/Manila');

        $now = \Carbon\Carbon::now('Asia/Manila');

        if ($dt->isSameDay($now)) {
            return 'Today at ' . $dt->format('g:i A');
        }

        if ($dt->isSameDay($now->copy()->subDay())) {
            return 'Yesterday at ' . $dt->format('g:i A');
        }

        if ($dt->year === $now->year) {
            return $dt->format('M j, g:i A');
        }

        return $dt->format('M j, Y, g:i A');
    }

    public function getFormattedTimeAttribute(): string
    {
        return self::formatHumanTimestamp($this->created_at);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}
