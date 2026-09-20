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
            $message->ticket = 'TKT-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
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

    /** Tiket yang masih perlu ditangani (bukan selesai/ditutup). */
    public function scopeBerjalan($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed']);
    }

    public function belumDibaca(): bool
    {
        return $this->read_at === null;
    }

    public function selesai(): bool
    {
        return in_array($this->status, ['resolved', 'closed'], true);
    }

    /** [label, kelas lencana, warna] untuk tiap status. */
    public function tampilanStatus(): array
    {
        return match ($this->status) {
            'open' => ['Terbuka', 'is-kuning', '#d97706'],
            'pending' => ['Tertunda', 'is-abu', '#64748b'],
            'in_progress' => ['Diproses', 'is-biru', '#2563eb'],
            'resolved' => ['Selesai', 'is-hijau', '#16a34a'],
            'closed' => ['Ditutup', 'is-abu', '#64748b'],
            default => [ucfirst((string) $this->status), 'is-abu', '#64748b'],
        };
    }

    /** [label, kelas, warna] untuk tiap prioritas. */
    public function tampilanPrioritas(): array
    {
        return match ($this->priority) {
            'urgent' => ['Mendesak', 'is-mendesak', '#dc2626'],
            'high' => ['Tinggi', 'is-tinggi', '#ea580c'],
            'medium' => ['Sedang', 'is-sedang', '#d97706'],
            default => ['Rendah', 'is-rendah', '#64748b'],
        };
    }

    /**
     * Berapa lama tiket ini menunggu dijawab (jam).
     *
     * Dihitung sampai DIBACA; sesudah dibaca, umurnya berhenti — angka yang
     * terus tumbuh untuk tiket yang sudah ditangani cuma jadi kecemasan palsu.
     */
    public function menungguJam(): int
    {
        $sampai = $this->read_at ?: now();

        return (int) $this->created_at?->diffInHours($sampai);
    }

    /** Tautan WhatsApp untuk membalas pengirim (kosong bila nomornya tidak ada). */
    public function tautanWa(?string $pesan = null): ?string
    {
        $nomor = preg_replace('/\D+/', '', (string) $this->no_telp);
        if ($nomor === '' || $nomor === null) {
            return null;
        }

        $nomor = str_starts_with($nomor, '0') ? '62'.substr($nomor, 1) : $nomor;

        return 'https://wa.me/'.$nomor.($pesan ? '?text='.rawurlencode($pesan) : '');
    }

    /** Tautan surel balasan, lengkap dengan nomor tiket di perihalnya. */
    public function tautanEmail(): ?string
    {
        if (blank($this->email)) {
            return null;
        }

        return 'mailto:'.$this->email.'?subject='.rawurlencode('Balasan '.$this->ticket.' — Phoenix Digital');
    }
}
