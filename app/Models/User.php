<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Accessor: $user->online
     */
    public function getOnlineAttribute()
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(1));
    }

    /**
     * Fallback: Cek dari tabel sessions
     */
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
        return $this->last_seen_at;
    }
}
