<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const STATUS_MEMBER_ACTIVE = 'active';
    public const STATUS_MEMBER_NONACTIVE = 'non-active';

    protected $fillable = [
        'nama',
        'email',
        'no_hp',
        'status_member'
    ];

    protected $cast = [
        'status_member' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'status_member' => self::STATUS_MEMBER_NONACTIVE
    ];

    /**
     * Format nomor HP dengan format Indonesia
     */
    public function getNoHpFormattedAttribute(): ?string
    {
        if (!$this->no_hp) return null;

        $phone = preg_replace('/[^0-9]/', '', $this->no_hp);

        // Convert 08xx to +628xx
        if (substr($phone, 0, 1) === '0') {
            $phone = '+62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '+62' . $phone;
        } else {
            $phone = '+' . $phone;
        }

        return $phone;
    }
    public function setNoHpAttribute($value): void
    {
        if ($value) {
            // Remove non-numeric characters
            $phone = preg_replace('/[^0-9]/', '', $value);

            // Convert various formats to standard Indonesian format
            if (substr($phone, 0, 3) === '620') {
                $phone = '0' . substr($phone, 3);
            } elseif (substr($phone, 0, 2) === '62') {
                $phone = '0' . substr($phone, 2);
            } elseif (substr($phone, 0, 1) !== '0') {
                $phone = '0' . $phone;
            }

            $this->attributes['no_hp'] = $phone;
        } else {
            $this->attributes['no_hp'] = null;
        }
    }

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->timezone('Asia/Jakarta');
    }
    /**
     * Scope untuk searching berdasarkan nama atau email
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('nama', 'LIKE', '%' . $term . '%')
                ->orWhere('email', 'LIKE', '%' . $term . '%');
        });
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status_member', $status);
    }

    /**
     * Check apakah customer adalah member active atau tidak aktif (non member)
     */
    public function isMember(): bool
    {
        return $this->status_member === self::STATUS_MEMBER_ACTIVE;
    }
    public function isNonMember(): bool
    {
        return $this->status_member === self::STATUS_MEMBER_NONACTIVE;
    }
}
