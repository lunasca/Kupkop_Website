<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Unified status field: 'active' | 'blocked' (replaces boolean is_blocked)
            $table->string('status')->default('active')->after('is_blocked');
            // Soft-delete for accounts (null = active, timestamp = archived)
            $table->timestamp('archived_at')->nullable()->default(null)->after('status');
        });

        // Sync status column from existing is_blocked booleans
        \DB::table('users')->where('is_blocked', true)->update(['status' => 'blocked']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'archived_at']);
        });
    }
};
