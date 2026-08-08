<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banners extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'banners';

    protected $fillable = [
        'judul',
        'gambar',
        'deskripsi',
        'status',
        'mulai_tayang',
        'selesai_tayang',
    ];

    protected $casts = [
        'mulai_tayang' => 'datetime',
        'selesai_tayang' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Banner yang BOLEH tampil ke pengunjung saat ini.
     *
     * SATU pintu untuk seluruh tampilan publik (beranda, kontak, meta SEO).
     * Sebelumnya tiap tempat menulis where('status','active') sendiri-sendiri;
     * begitu penjadwalan ditambahkan, satu tempat yang terlewat = banner
     * kedaluwarsa masih nongol di halaman itu.
     *
     * Aturannya:
     *   status = 'active'  DAN  sudah waktunya  DAN  belum lewat waktunya
     *
     * Tanggal NULL berarti "tanpa batas" — banner tanpa jadwal berperilaku
     * persis seperti sebelum fitur ini ada.
     *
     * Penyaringan dilakukan SAAT QUERY, bukan lewat cron yang membalik kolom
     * status. Dua alasan:
     *  1. Cron di hosting ini pernah mati diam-diam. Kalau jadwal bergantung
     *     pada cron, banner kedaluwarsa akan terus tayang tanpa ada yang tahu.
     *  2. Kolom `status` adalah saklar MANUAL milik admin. Kalau cron ikut
     *     menulisinya, "dimatikan admin" dan "jadwalnya habis" jadi tak bisa
     *     dibedakan, dan pilihan admin bisa tertimpa diam-diam.
     */
    public function scopeTayang(Builder $query): Builder
    {
        $now = now();

        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('mulai_tayang')->orWhere('mulai_tayang', '<=', $now))
            ->where(fn ($q) => $q->whereNull('selesai_tayang')->orWhere('selesai_tayang', '>=', $now));
    }

    /** Sedang tayang di publik sekarang? (versi per-baris dari scopeTayang) */
    public function sedangTayang(): bool
    {
        return $this->status === 'active'
            && (is_null($this->mulai_tayang) || $this->mulai_tayang <= now())
            && (is_null($this->selesai_tayang) || $this->selesai_tayang >= now());
    }

    /** Jadwalnya sudah lewat. */
    public function sudahBerakhir(): bool
    {
        return $this->selesai_tayang !== null && $this->selesai_tayang < now();
    }

    /** Menunggu waktu mulai. */
    public function menungguJadwal(): bool
    {
        return $this->mulai_tayang !== null && $this->mulai_tayang > now();
    }

    /**
     * Keterangan singkat keadaan banner untuk daftar admin.
     *
     * @return array{0: string, 1: string} [label, warna bootstrap]
     */
    public function keadaanTayang(): array
    {
        if ($this->status !== 'active') {
            return ['Non-Aktif', 'secondary'];
        }

        if ($this->menungguJadwal()) {
            return ['Terjadwal', 'info'];
        }

        if ($this->sudahBerakhir()) {
            return ['Berakhir', 'danger'];
        }

        return ['Tayang', 'success'];
    }

    /** Rentang jadwal untuk ditampilkan, mis. "12 Agt 2026 – 20 Agt 2026". */
    public function jadwalLabel(): string
    {
        if (! $this->mulai_tayang && ! $this->selesai_tayang) {
            return 'Tanpa batas waktu';
        }

        // locale('id') dipaksa karena APP_LOCALE=en (lihat CLAUDE.md).
        $mulai = $this->mulai_tayang
            ? $this->mulai_tayang->locale('id')->translatedFormat('d M Y, H:i')
            : 'kapan pun';
        $selesai = $this->selesai_tayang
            ? $this->selesai_tayang->locale('id')->translatedFormat('d M Y, H:i')
            : 'seterusnya';

        return $mulai.' – '.$selesai;
    }
}
