<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris linimasa tiket helpdesk.
 *
 * Barisnya sengaja tidak pernah diubah atau dihapus: gunanya justru sebagai
 * bukti apa yang terjadi pada sebuah tiket, termasuk isi balasan yang dikirim
 * ke pelanggan.
 */
class CustomerMessageLog extends Model
{
    protected $fillable = [
        'customer_message_id',
        'user_id',
        'nama_pelaku',
        'jenis',
        'kanal',
        'isi',
    ];

    public function pesan()
    {
        return $this->belongsTo(CustomerMessage::class, 'customer_message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Nama pelaku yang dibekukan saat kejadian; akun boleh hilang belakangan. */
    public function pelaku(): string
    {
        return $this->nama_pelaku ?: ($this->user?->name ?: 'Sistem');
    }

    /** [ikon, warna, judul] untuk tiap jenis kejadian. */
    public function tampilan(): array
    {
        return match ($this->jenis) {
            'balasan' => ['bi-send-fill', '#16a34a', 'Dibalas lewat '.(config('helpdesk.kanal')[$this->kanal] ?? 'kanal lain')],
            'catatan' => ['bi-sticky-fill', '#d97706', 'Catatan internal'],
            'status' => ['bi-arrow-repeat', '#2563eb', 'Status diubah'],
            'prioritas' => ['bi-flag-fill', '#ea580c', 'Prioritas diubah'],
            'kategori' => ['bi-tag-fill', '#7c3aed', 'Topik diubah'],
            'tugas' => ['bi-person-check-fill', '#0891b2', 'Penugasan diubah'],
            'dibaca' => ['bi-envelope-open-fill', '#64748b', 'Pesan dibaca'],
            'spam' => ['bi-shield-exclamation', '#dc2626', 'Ditandai spam'],
            'arsip' => ['bi-archive-fill', '#64748b', 'Diarsipkan'],
            'pulih' => ['bi-arrow-counterclockwise', '#16a34a', 'Dikembalikan dari arsip'],
            default => ['bi-dot', '#64748b', ucfirst((string) $this->jenis)],
        };
    }
}
