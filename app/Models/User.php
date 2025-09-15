<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'profile_photo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // relation role user
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    public function hasRole(string $role): bool
    {
        return $this->role->name === $role;
    }
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role->name, $roles);
    }
    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo && file_exists(public_path($this->profile_photo))) {
            return asset($this->profile_photo);
        }

        // Default avatar using initials
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    public function isOnline()
    {
        $lastActivity = DB::table('sessions')
            ->where('user_id', $this->id)
            ->orderBy('last_activity', 'desc')
            ->value('last_activity');

        if (!$lastActivity) {
            return false;
        }

        return Carbon::createFromTimestamp($lastActivity)
            ->gt(Carbon::now()->subMinutes(1));
    }

    public function lastSeen(): ?Carbon
    {
        $lastActivity = DB::table('sessions')
            ->where('user_id', $this->id)
            ->orderBy('last_activity', 'desc')
            ->value('last_activity');

        return $lastActivity
            ? Carbon::createFromTimestamp($lastActivity)
            : null;
    }
}
