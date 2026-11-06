<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data migration: rewrite relative image paths (e.g. "pets/abc.jpg") stored in
 * the pets.image_path column to full public Supabase CDN URLs.
 *
 * Background:
 * - Previously, uploadPetImageToSupabase() returned a relative "pets/<filename>" path
 *   as a fallback when cloud upload credentials were unavailable.
 * - On Vercel/serverless production there is no writable local disk, so these paths
 *   can never resolve to a real file — causing all affected cards to show the same
 *   fallback Unsplash placeholder image.
 * - This migration converts every such relative-path record to its canonical
 *   full Supabase CDN URL so they resolve correctly in all environments.
 *
 * Safe to run on both local (SQLite) and production (PostgreSQL) databases.
 * The `down()` method is a no-op because path rewrites are non-destructive.
 */
return new class extends Migration
{
    /**
     * The Supabase CDN base URL for the pets storage bucket.
     */
    private string $supabaseBase = 'https://hlaoqkozskcyskkhubtw.supabase.co/storage/v1/object/public/pets';

    public function up(): void
    {
        // Fetch all pets that have an image_path stored (non-null)
        $pets = DB::table('pets')
            ->whereNotNull('image_path')
            ->select('id', 'image_path')
            ->get();

        foreach ($pets as $pet) {
            $imagePath = $pet->image_path;

            // Try JSON-decode (multi-image format)
            $decoded = json_decode($imagePath, true);

            if (is_array($decoded)) {
                $changed = false;
                $newPaths = array_map(function (string $path) use (&$changed): string {
                    $rewritten = $this->rewritePath($path);
                    if ($rewritten !== $path) {
                        $changed = true;
                    }
                    return $rewritten;
                }, $decoded);

                if ($changed) {
                    DB::table('pets')->where('id', $pet->id)->update([
                        'image_path' => json_encode(array_values($newPaths)),
                    ]);
                }
            } else {
                // Legacy single-path string
                $rewritten = $this->rewritePath($imagePath);
                if ($rewritten !== $imagePath) {
                    DB::table('pets')->where('id', $pet->id)->update([
                        'image_path' => $rewritten,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally left blank — URL rewriting is a one-way data normalisation.
        // Rolling back would restore broken relative paths; leave as-is on rollback.
    }

    /**
     * Rewrite a single image path to a full Supabase CDN URL if it is a relative path.
     * Full http(s) URLs are returned unchanged.
     */
    private function rewritePath(string $path): string
    {
        $path = trim($path);

        // Already a full URL — no rewrite needed
        if (str_starts_with($path, 'https://') || str_starts_with($path, 'http://')) {
            return $path;
        }

        // Relative path: "pets/abc.jpg" or "abc.jpg" → full Supabase CDN URL
        $filename = basename($path);
        return rtrim($this->supabaseBase, '/') . '/' . $filename;
    }
};
