<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ebook extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'share_token',
        'judul',
        'deskripsi',
        'file',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($ebook) {
            if (empty($ebook->share_token)) {
                $ebook->share_token = self::generateToken();
            }
        });
    }

    public static function generateToken(): string
    {
        do {
            $token = Str::random(10);
        } while (self::where('share_token', $token)->exists());

        return $token;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function orderItems(): BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class, 'order_item_ebook')->withTimestamps();
    }

    // URL viewer view-only (link pendek, tanpa expose file/uuid)
    public function getViewUrl(): ?string
    {
        return $this->share_token ? url('/e/' . $this->share_token) : null;
    }

    // URL unduh untuk ADMIN (terproteksi auth), bukan untuk pelanggan
    public function getAdminDownloadUrl(): ?string
    {
        return route('admin.ebook.download', $this);
    }
}
