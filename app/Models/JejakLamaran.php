<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris riwayat sebuah lamaran: perubahan tahap, catatan tim, atau hasil
 * pengiriman surel.
 *
 * Nama penulis DISALIN ke kolomnya sendiri, tidak cuma disimpan sebagai
 * user_id. Akun bisa dihapus atau namanya berubah, sementara riwayat seleksi
 * justru harus terbaca apa adanya bertahun-tahun kemudian — "diubah oleh
 * (akun terhapus)" tidak menjawab siapa pun.
 */
class JejakLamaran extends Model
{
    protected $table = 'tbl_jejak_lamaran';

    protected $fillable = [
        'job_application_id', 'user_id', 'nama_penulis',
        'jenis', 'dari_status', 'ke_status', 'isi',
    ];

    public const JENIS = ['tahap', 'catatan', 'surel'];

    public function lamaran(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Catat sesuatu yang terjadi pada sebuah lamaran.
     *
     * Penulisnya diambil dari pengguna yang sedang masuk, bukan dititipkan
     * pemanggil: jejak yang penulisnya bisa ditentukan dari luar bukan jejak.
     */
    public static function catat(JobApplication $lamaran, string $jenis, array $data = []): self
    {
        $pengguna = auth()->user();

        return self::create(array_merge([
            'job_application_id' => $lamaran->id,
            'user_id' => $pengguna?->id,
            'nama_penulis' => $pengguna?->name,
            'jenis' => in_array($jenis, self::JENIS, true) ? $jenis : 'catatan',
        ], $data));
    }

    public function getPenulisLabelAttribute(): string
    {
        if ($this->nama_penulis) {
            return $this->nama_penulis;
        }

        if ($this->penulis?->name) {
            return $this->penulis->name;
        }

        /*
         | Dua sebab penulisnya kosong, dan keduanya BUKAN hal yang sama.
         |
         | Baris 'surel' lahir sesudah jawaban dikirim ke peramban, saat tidak
         | ada lagi pengguna yang sedang masuk — itu memang dikerjakan sistem.
         | Baris lain yang kosong adalah catatan pindahan dari sebelum riwayat
         | ini ada. Menyeragamkan keduanya menjadi "sistem" akan mengaku
         | tahu sesuatu yang tidak pernah tercatat.
         */
        return $this->jenis === 'surel' ? 'Dikirim otomatis' : 'Sebelum riwayat dicatat';
    }

    public function getWaktuLabelAttribute(): string
    {
        return $this->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm');
    }
}
