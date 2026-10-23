<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearTestData extends Command
{
    protected $signature   = 'db:clear-test-data';
    protected $description = 'Clear all test data while preserving the admin user account.';

    public function handle(): int
    {
        // ── 1. Identify the real admin account to preserve ────────────────
        // Priority: adoptme@gmail.com first, then any is_admin account
        $admin = DB::table('users')->where('email', 'adoptme@gmail.com')->first()
               ?? DB::table('users')->where('is_admin', 1)->orderBy('id')->first();

        if (!$admin) {
            $this->error('No admin user found! Aborting to prevent accidental data loss.');
            return self::FAILURE;
        }

        $this->info("Admin account preserved: {$admin->name} ({$admin->email})");
        $this->newLine();

        // ── 2. Disable FK constraints (SQLite) ────────────────────────────
        DB::statement('PRAGMA foreign_keys = OFF');

        // ── 3. Clear tables in dependency-safe order ──────────────────────
        $steps = [
            ['table' => 'messages',          'label' => 'Messages'],
            ['table' => 'adoption_requests',  'label' => 'Adoption requests'],
            ['table' => 'pets',               'label' => 'Pets / Posts'],
        ];

        foreach ($steps as $step) {
            $count = DB::table($step['table'])->count();
            DB::table($step['table'])->delete();
            $this->line("  ✓ {$step['label']}: cleared {$count} record(s).");
        }

        // ── 4. Delete all non-admin users ─────────────────────────────────
        $nonAdminCount = DB::table('users')->where('id', '!=', $admin->id)->count();
        DB::table('users')->where('id', '!=', $admin->id)->delete();
        $this->line("  ✓ Non-admin users: deleted {$nonAdminCount} account(s).");

        // ── 5. Re-enable FK constraints ───────────────────────────────────
        DB::statement('PRAGMA foreign_keys = ON');

        // ── 6. Summary ────────────────────────────────────────────────────
        $this->newLine();
        $remaining = DB::table('users')->count();
        $this->info("Done! Database is clean.");
        $this->line("  Remaining users : {$remaining} (admin only)");
        $this->line("  Remaining pets  : " . DB::table('pets')->count());

        return self::SUCCESS;
    }
}
