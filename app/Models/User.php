<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_blocked',
        'is_verified',
        'status',
        'archived_at',
        'block_reason',
        'blocked_at',
        'requests_last_viewed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'        => 'datetime',
            'password'                 => 'hashed',
            'is_admin'                 => 'boolean',
            'is_blocked'               => 'boolean',
            'is_verified'              => 'boolean',
            'archived_at'              => 'datetime',
            'blocked_at'               => 'datetime',
            'requests_last_viewed_at'  => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty('status') && !$user->isDirty('is_blocked')) {
                $user->is_blocked = in_array($user->status, ['blocked', 'removed']);
            } elseif ($user->isDirty('is_blocked') && !$user->isDirty('status')) {
                $user->status = $user->is_blocked ? 'blocked' : 'active';
            }
        });
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }
}
