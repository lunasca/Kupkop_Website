<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add block_reason and blocked_at timestamp to track admin action details.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Admin-provided reason when blocking or archiving an account
            $table->string('block_reason')->nullable()->default(null)->after('archived_at');
            // Timestamp of when the restriction was applied
            $table->timestamp('blocked_at')->nullable()->default(null)->after('block_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['block_reason', 'blocked_at']);
        });
    }
};
