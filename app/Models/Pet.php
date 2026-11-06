<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'species',
        'breed',
        'age',
        'gender',
        'vaccination_status',
        'deworming_status',
        'is_spayed_neutered',
        'special_health_notes',
        'description',
        'image_path',
        'status',
        'is_verified',
        'archived_at',
        'location',
        'rescuer_id',
        'posted_by',
    ];

    protected $casts = [
        'is_spayed_neutered' => 'boolean',
        'is_verified'        => 'boolean',
        'archived_at'        => 'datetime',
    ];

    /**
     * Virtual attributes appended to array / JSON representations.
     */
    protected $appends = [
        'image_url',
        'image_urls',
        'image_paths',
        'formatted_id',
    ];

    /**
     * The rescuer/user who posted this pet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Adoption applications submitted for this pet.
     */
    public function adoptionRequests(): HasMany
    {
        return $this->hasMany(AdoptionRequest::class);
    }

    /**
     * Helper accessor for pet code / ID (e.g., #RES-0764).
     */
    public function getFormattedIdAttribute(): string
    {
        if (!empty($this->rescuer_id) && str_starts_with($this->rescuer_id, '#RES-')) {
            return $this->rescuer_id;
        }

        return '#RES-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Helper to format pet names to Title Case:
     * Capitalizes each word while preserving intentional mixed-casing (e.g., VisiblePet).
     */
    public static function formatPetName(?string $name): ?string
    {
        if ($name === null || trim($name) === '') {
            return $name;
        }

        $trimmed = trim($name);

        // If entirely uppercase or entirely lowercase, convert cleanly to title case
        if (mb_strtoupper($trimmed) === $trimmed || mb_strtolower($trimmed) === $trimmed) {
            return mb_convert_case($trimmed, MB_CASE_TITLE, 'UTF-8');
        }

        // If mixed case (e.g., VisiblePet, McDonald), capitalize word starts without lowercasing existing capitals
        return ucwords($trimmed);
    }

    /**
     * Helper to format text into sentence case:
     * Capitalizes first letter of text and subsequent sentences after '.', '!', '?', or newlines.
     */
    public static function formatSentenceCase(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        $text = trim($text);

        // Capitalize the first character of the text (supports multibyte)
        $firstChar = mb_substr($text, 0, 1);
        $remainder = mb_substr($text, 1);
        $text = mb_strtoupper($firstChar) . $remainder;

        // Capitalize the first letter after sentence terminators (. ! ? \n) followed by whitespace
        return preg_replace_callback('/([.!?\n]\s*)([a-z])/u', function ($matches) {
            return $matches[1] . mb_strtoupper($matches[2]);
        }, $text);
    }

    /**
     * Interact with the pet's name.
     * Automatically format animal name to Title Case / Capitalized Words.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? self::formatPetName($value) : $value,
            set: fn (?string $value) => $value ? self::formatPetName($value) : $value,
        );
    }

    /**
     * Interact with the pet's description.
     * Automatically format description to Sentence Case.
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? self::formatSentenceCase($value) : $value,
            set: fn (?string $value) => $value ? self::formatSentenceCase(trim($value)) : $value,
        );
    }

    /**
     * Parse human-readable age into years as a float.
     * Returns null if unparseable or non-positive.
     */
    public static function parseAgeInYears(?string $ageStr): ?float
    {
        if (!$ageStr) {
            return null;
        }

        $raw = strtolower(trim($ageStr));
        if ($raw === '') {
            return null;
        }

        // Weeks: e.g. "8 weeks", "12 wks"
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:weeks?|wks?|wk)\b/i', $raw, $m)) {
            $years = ((float) $m[1]) / 52.0;
            return $years > 0 ? $years : null;
        }

        // Days: e.g. "45 days"
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:days?|d)\b/i', $raw, $m)) {
            $years = ((float) $m[1]) / 365.0;
            return $years > 0 ? $years : null;
        }

        // Months: e.g. "5 months", "11 mos"
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:months?|mos?|mo)\b/i', $raw, $m)) {
            $years = ((float) $m[1]) / 12.0;
            return $years > 0 ? $years : null;
        }

        // Years: e.g. "1 year old", "1.5 yrs", "2 years"
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:years?|yrs?|yr|y\.?o\.?|yo)\b/i', $raw, $m)) {
            $years = (float) $m[1];
            return $years > 0 ? $years : null;
        }

        // Plain number (assume years): e.g. "2"
        if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*$/', $raw, $m)) {
            $years = (float) $m[1];
            return $years > 0 ? $years : null;
        }

        return null;
    }

    /**
     * Check if an age string is realistic (maximum 20 years / 240 months, and > 0).
     */
    public static function isRealisticAge(?string $ageStr): bool
    {
        if (!$ageStr) {
            return false;
        }

        $raw = strtolower(trim($ageStr));

        // Direct standard keywords
        if (in_array($raw, ['puppy', 'kitten', 'pup', 'kit', 'baby', 'young', 'junior', 'adolescent', 'juvenile', 'adult', 'mature', 'senior'])) {
            return true;
        }

        $years = self::parseAgeInYears($ageStr);
        if ($years === null) {
            return false;
        }

        // Must be greater than 0 and at most 20 years (240 months)
        return $years > 0 && $years <= 20.0;
    }

    /**
     * Scope for active (non-archived) pets.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Scope for archived pets.
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Resolve a clean, environment-safe image URL for a pet photo.
     * Supports full Supabase CDN URLs, external image URLs, and relative local storage paths.
     */
    public static function formatPetImageUrl(?string $path, ?string $species = 'Dog'): string
    {
        $fallback = strtolower($species ?? 'dog') === 'cat'
            ? 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80'
            : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80';

        if (empty($path)) {
            return $fallback;
        }

        // Full Supabase CDN or Web URL
        if (str_starts_with($path, 'https://') || str_starts_with($path, 'http://')) {
            // Rewrite legacy hardcoded localhost / 127.0.0.1 URLs to avoid broken images across environments
            if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?/storage/(.*)$#i', $path, $matches)) {
                $filename = basename($matches[3]);
                if (file_exists(public_path('storage/pets/' . $filename))) {
                    return '/storage/pets/' . $filename;
                }
                return 'https://hlaoqkozskcyskkhubtw.supabase.co/storage/v1/object/public/pets/' . $filename;
            }
            return $path;
        }

        // Relative path (e.g. "pets/abc.jpg" or "abc.jpg")
        $filename = basename($path);

        // Build the canonical Supabase CDN URL for this file
        $bucket = env('SUPABASE_STORAGE_BUCKET', env('AWS_BUCKET', 'pets'));
        $projectRef = env('SUPABASE_PROJECT_REF', 'hlaoqkozskcyskkhubtw');
        $baseUrl = rtrim(env('SUPABASE_STORAGE_URL', env('AWS_URL', "https://{$projectRef}.supabase.co/storage/v1/object/public/{$bucket}")), '/');
        $supabaseUrl = $baseUrl . '/' . $filename;

        // On local dev: prefer the locally-stored copy for faster display (avoids CDN round-trip).
        // This check is skipped in production (Vercel/serverless) where there is no local disk.
        if (app()->environment('local') &&
            (file_exists(public_path('storage/pets/' . $filename)) || file_exists(storage_path('app/public/pets/' . $filename)))) {
            return '/storage/pets/' . $filename;
        }

        // Always return the full Supabase CDN URL in all other environments (staging, production).
        return $supabaseUrl;
    }

    /**
     * Helper accessor for pet image URL (supports local uploaded images or full web URLs).
     * If JSON array is present, uses the first image as the primary cover.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return self::formatPetImageUrl(null, $this->species);
        }

        // Handle JSON multi-image array: use first image as primary
        $decoded = json_decode($this->image_path, true);
        $firstPath = (is_array($decoded) && !empty($decoded)) ? $decoded[0] : $this->image_path;

        return self::formatPetImageUrl($firstPath, $this->species);
    }

    /**
     * Return all uploaded image paths as an array (supports both legacy single-path and new JSON-encoded multi-image).
     * Index 0 is always the primary/main photo.
     */
    public function getImagePathsAttribute(): array
    {
        if (empty($this->image_path)) {
            return [];
        }

        // Try JSON decode first (multi-image format)
        $decoded = json_decode($this->image_path, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Legacy: single image path string
        return [$this->image_path];
    }

    /**
     * Return all uploaded image URLs as an array, suitable for gallery/slideshow use.
     * Handles both legacy single-path and multi-image JSON-encoded paths.
     */
    public function getImageUrlsAttribute(): array
    {
        $paths = $this->image_paths;
        if (empty($paths)) {
            return [self::formatPetImageUrl(null, $this->species)];
        }

        return array_map(fn (string $path) => self::formatPetImageUrl($path, $this->species), $paths);
    }
}
