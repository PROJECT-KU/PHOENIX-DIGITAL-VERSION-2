<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Lampiran tiket helpdesk.
 *
 * Berkasnya di disk PRIVAT ('local') dan hanya bisa diambil lewat route
 * ber-izin — lampiran pelanggan sering berisi tangkapan layar mutasi bank.
 */
class CustomerMessageAttachment extends Model
{
    protected $fillable = [
        'customer_message_id',
        'sumber',
        'nama_asli',
        'path',
        'mime',
        'ukuran',
        'user_id',
    ];

    public function pesan()
    {
        return $this->belongsTo(CustomerMessage::class, 'customer_message_id');
    }

    public function dariAdmin(): bool
    {
        return $this->sumber === 'admin';
    }

    public function ukuranTerbaca(): string
    {
        $b = (int) $this->ukuran;

        return $b >= 1048576
            ? round($b / 1048576, 1).' MB'
            : max(1, (int) round($b / 1024)).' KB';
    }

    /** Hapus berkas fisiknya; dipanggil saat tiket dihapus permanen. */
    public function hapusBerkas(): void
    {
        rescue(fn () => Storage::disk('local')->delete($this->path), null, false);
    }
}
