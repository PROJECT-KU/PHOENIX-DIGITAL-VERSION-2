<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerMessage extends Model
{
    protected static function booted()
    {
        static::creating(function ($message) {
            // Generate ticket unik: format TKT-XXXX-XXXX
            $message->ticket = 'TKT-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        });
    }

    protected $fillable = [
        'ticket',
        'status',
        'priority',
        'name',
        'email',
        'no_telp',
        'message',
        'ip_address',
        'user_agent',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
