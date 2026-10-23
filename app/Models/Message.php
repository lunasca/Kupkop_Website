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
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

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
