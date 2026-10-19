<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'location',
        'rescuer_id',
        'posted_by',
    ];

    protected $casts = [
        'is_spayed_neutered' => 'boolean',
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
     * Helper accessor for pet image URL (supports local uploaded images or full web URLs).
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return strtolower($this->species) === 'cat'
                ? 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80'
                : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80';
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return asset('storage/' . $this->image_path);
    }
}
