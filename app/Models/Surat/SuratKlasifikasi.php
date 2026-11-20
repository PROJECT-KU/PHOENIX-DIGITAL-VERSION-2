<?php

namespace App\Models\Surat;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Klasifikasi (kualifikasi) surat — master data kode arsip.
 *
 * Satu baris menjawab dua hal sekaligus: surat ini termasuk urusan apa, dan
 * berapa lama ia wajib disimpan. Keduanya disatukan karena di praktik
 * kearsipan memang satu keputusan: kode klasifikasi yang menentukan jadwal
 * retensinya.
 */
class SuratKlasifikasi extends Model
{
    protected $table = 'surat_klasifikasi';

    protected $fillable = ['kode', 'nama', 'keterangan', 'retensi_tahun', 'aktif', 'urutan'];

    protected $casts = [
        'aktif' => 'boolean',
        'retensi_tahun' => 'integer',
        'urutan' => 'integer',
    ];

    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class, 'klasifikasi_id');
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('aktif', true);
    }

    /** Urutan tampil: yang disusun admin lebih dulu, lalu menurut kodenya. */
    public function scopeTersusun(Builder $q): Builder
    {
        return $q->orderBy('urutan')->orderBy('kode');
    }

    /** "110 — Surat Masuk Umum", bentuk yang dipakai di kotak pilih. */
    public function getLabelAttribute(): string
    {
        return $this->kode.' — '.$this->nama;
    }

    /**
     * Masa simpan dalam kalimat biasa.
     *
     * "Belum ditentukan", bukan "0 tahun": nol tahun berarti boleh dimusnahkan
     * hari ini, dan itu kesimpulan yang sangat berbeda dari belum diputuskan.
     */
    public function getRetensiTeksAttribute(): string
    {
        return $this->retensi_tahun === null
            ? 'Belum ditentukan'
            : $this->retensi_tahun.' tahun';
    }
}
