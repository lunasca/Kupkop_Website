<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add database indexes to frequently filtered and joined columns.
     */
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->index('status', 'idx_pets_status');
            $table->index('species', 'idx_pets_species');
            $table->index('archived_at', 'idx_pets_archived_at');
            $table->index('created_at', 'idx_pets_created_at');
            $table->index(['archived_at', 'status'], 'idx_pets_archived_status');
            $table->index(['user_id', 'archived_at'], 'idx_pets_user_archived');
        });

        Schema::table('adoption_requests', function (Blueprint $table) {
            $table->index('status', 'idx_adoption_requests_status');
            $table->index(['user_id', 'status'], 'idx_adoption_requests_user_status');
            $table->index(['pet_id', 'status'], 'idx_adoption_requests_pet_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropIndex('idx_pets_status');
            $table->dropIndex('idx_pets_species');
            $table->dropIndex('idx_pets_archived_at');
            $table->dropIndex('idx_pets_created_at');
            $table->dropIndex('idx_pets_archived_status');
            $table->dropIndex('idx_pets_user_archived');
        });

        Schema::table('adoption_requests', function (Blueprint $table) {
            $table->dropIndex('idx_adoption_requests_status');
            $table->dropIndex('idx_adoption_requests_user_status');
            $table->dropIndex('idx_adoption_requests_pet_status');
        });
    }
};
