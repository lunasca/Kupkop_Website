<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountRestrictionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly User $user,
        public readonly string $action,   // 'blocked' | 'archived' | 'deleted'
        public readonly string $reason,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->action) {
            'archived' => 'Notice: Your Kupkop Account Has Been Suspended',
            'deleted'  => 'Notice: Your Kupkop Account Has Been Removed',
            default    => 'Notice Regarding Your Kupkop Account Status',
        };

        return new Envelope(subject: $subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.account-restriction',
            with: [
                'user'   => $this->user,
                'action' => $this->action,
                'reason' => $this->reason,
            ],
        );
    }
}
