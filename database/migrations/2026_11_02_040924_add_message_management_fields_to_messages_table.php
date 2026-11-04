<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds per-message edit/delete fields and per-conversation soft-delete timestamps.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // ── Per-message edit tracking ──────────────────────────────────────
            // Stores the original message content before an edit
            $table->text('original_message')->nullable()->after('message');
            // Timestamp of when the message was last edited (null = never edited)
            $table->timestamp('edited_at')->nullable()->after('original_message');

            // ── Per-message delete flags ───────────────────────────────────────
            // "Delete for Me" — only hides the message from the sender's view
            $table->boolean('deleted_by_sender')->default(false)->after('edited_at');
            // "Delete for Me" — only hides the message from the receiver's view
            $table->boolean('deleted_by_receiver')->default(false)->after('deleted_by_sender');
            // "Delete for Everyone" — replaces content with placeholder for both parties
            $table->boolean('deleted_for_everyone')->default(false)->after('deleted_by_receiver');

            // ── Per-conversation soft-delete (hides entire thread for one party) ─
            // When set, this conversation is hidden from the sender's sidebar
            $table->timestamp('conv_deleted_by_sender_at')->nullable()->after('deleted_for_everyone');
            // When set, this conversation is hidden from the receiver's sidebar
            $table->timestamp('conv_deleted_by_receiver_at')->nullable()->after('conv_deleted_by_sender_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn([
                'original_message',
                'edited_at',
                'deleted_by_sender',
                'deleted_by_receiver',
                'deleted_for_everyone',
                'conv_deleted_by_sender_at',
                'conv_deleted_by_receiver_at',
            ]);
        });
    }
};
