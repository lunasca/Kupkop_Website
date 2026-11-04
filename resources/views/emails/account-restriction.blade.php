<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Status Notice - Kupkop</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #FAF5EE; margin: 0; padding: 0; color: #2D1810; }
        .email-wrapper { max-width: 580px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(74,46,43,0.10); border: 1px solid #F3E8D8; }
        .email-header { background: #4A2E2B; padding: 32px 40px; text-align: center; }
        .email-header h1 { color: #ffffff; font-size: 1.6rem; font-weight: 800; margin: 0; letter-spacing: -0.5px; }
        .email-header p { color: #F3C5B0; font-size: 0.9rem; margin: 6px 0 0 0; }
        .email-body { padding: 36px 40px; }
        .status-badge { display: inline-block; padding: 6px 18px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; margin-bottom: 20px; }
        .status-blocked { background: #FEE2E2; color: #991B1B; }
        .status-archived { background: #FEF3C7; color: #92400E; }
        .status-deleted { background: #F3F4F6; color: #374151; }
        .reason-box { background: #FFF7F0; border-left: 4px solid #4A2E2B; border-radius: 0 8px 8px 0; padding: 14px 18px; margin: 20px 0; }
        .reason-box p { margin: 0; font-size: 0.97rem; color: #4A2E2B; font-weight: 600; line-height: 1.5; }
        .support-box { background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px 20px; margin-top: 24px; }
        .support-box p { margin: 0; font-size: 0.92rem; color: #166534; line-height: 1.6; }
        .email-footer { background: #FAF5EE; padding: 20px 40px; text-align: center; border-top: 1px solid #F3E8D8; }
        .email-footer p { margin: 0; font-size: 0.82rem; color: #71655B; }
        h2 { font-size: 1.25rem; font-weight: 700; color: #2D1810; margin: 0 0 12px 0; }
        p { font-size: 0.95rem; line-height: 1.65; color: #4A2E2B; margin: 0 0 12px 0; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <h1>🐾 Kupkop</h1>
            <p>Pet Adoption Platform &mdash; Account Notice</p>
        </div>
        <div class="email-body">
            <p>Hello, <strong>{{ $user->name }}</strong>,</p>

            @if($action === 'blocked')
                <span class="status-badge status-blocked">Account Restricted</span>
                <h2>Your account has been restricted.</h2>
                <p>
                    An administrator has placed a restriction on your Kupkop account. While your account is restricted,
                    you will not be able to log in, post listings, or send messages on the platform.
                </p>
            @elseif($action === 'archived')
                <span class="status-badge status-archived">Account Suspended</span>
                <h2>Your account has been suspended.</h2>
                <p>
                    An administrator has suspended your Kupkop account. Your account has been moved to archive status.
                    While suspended, you will not be able to access the platform.
                </p>
            @else
                <span class="status-badge status-deleted">Account Removed</span>
                <h2>Your account has been removed.</h2>
                <p>
                    Your Kupkop account and all associated data have been permanently removed from our platform
                    by an administrator.
                </p>
            @endif

            <div class="reason-box">
                <p><strong>Reason provided:</strong><br>{{ $reason }}</p>
            </div>

            <p>
                If you believe this action was taken in error or would like to appeal this decision,
                please reach out to our support team.
            </p>

            <div class="support-box">
                <p>
                    📧 <strong>Support Contact:</strong> support@kupkop.local<br>
                    Please include your registered email address and a brief description of your appeal.
                    Our team will review your request within 3&ndash;5 business days.
                </p>
            </div>
        </div>
        <div class="email-footer">
            <p>This is an automated message from the Kupkop platform. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
