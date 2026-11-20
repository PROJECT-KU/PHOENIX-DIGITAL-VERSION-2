<?php

namespace App\Support\Surat;

use App\Models\Surat\Surat;
use App\Models\Surat\SuratDisposisi;
use App\Models\User;

/**
 * "Mode Arsip Surat" di lemon — padanan mode Orcha dan mode Asthana.
 *
 * Buku agenda surat masuk & keluar punya bilah samping sendiri. Seperti dua
 * mode sebelumnya, penandanya NAMA RUTE (bukan sesi), supaya admin bisa
 * membuka tab lemon dan tab arsip bersamaan tanpa salah satunya tiba-tiba
 * berganti menu di tengah pekerjaan.
 *
 * Gerbangnya izin 'akses_arsip_surat' (lihat AksesArsipSuratSeeder); izin per
 * layar (view_surat, create_surat, …) tetap berlaku di dalamnya.
 */
class ModeSurat
{
    /** Awalan nama rute yang termasuk mode Arsip Surat. */
    public const RUTE = ['admin.surat.*'];

    /** Izin yang dikelompokkan 'arsip_surat' di layar Role → Permission. */
    public const IZIN = [
        'akses_arsip_surat',
        'view_surat', 'create_surat', 'edit_surat', 'delete_surat',
        'kelola_klasifikasi_surat',
    ];

    public static function aktif(): bool
    {
        return request()->routeIs(...self::RUTE);
    }

    /**
     * Angka "perlu ditindak" untuk bilah samping & dasbor — hanya yang boleh
     * dilihat pengguna ini.
     *
     * Ketiganya dipilih karena masing-masing menuntut tindakan yang BERBEDA:
     * surat baru menunggu dibaca, disposisi telat menunggu ditagih, dan arsip
     * yang masa simpannya habis menunggu diputuskan. Digabung jadi satu angka
     * di bilah samping, dirinci di dasbor yang punya ruang untuk kalimat.
     *
     * @return array{surat_baru:int, disposisi_telat:int, retensi_habis:int}
     */
    public static function perlu(?User $u): array
    {
        if (! $u || ! $u->hasPermission('view_surat')) {
            return ['surat_baru' => 0, 'disposisi_telat' => 0, 'retensi_habis' => 0];
        }

        return [
            'surat_baru' => Surat::where('status', 'baru')->count(),
            'disposisi_telat' => SuratDisposisi::belumSelesai()
                ->whereNotNull('batas')
                ->whereDate('batas', '<', now()->toDateString())
                ->count(),
            /*
             | Arsip yang masa simpannya sudah habis.
             |
             | Yang sudah berstatus 'diarsipkan' TIDAK dihitung di sini —
             | justru surat itulah yang memang sedang menunggu di lemari
             | sampai masa simpannya lewat, dan menandainya terus-menerus
             | membuat angka ini tidak pernah bisa dinolkan. Yang dihitung
             | adalah yang masa simpannya habis padahal pekerjaannya belum
             | ditutup: itu keadaan yang memang perlu diperiksa orang.
             */
            'retensi_habis' => Surat::whereNotNull('musnah_pada')
                ->whereDate('musnah_pada', '<=', now()->toDateString())
                ->where('status', '!=', 'diarsipkan')
                ->count(),
        ];
    }
}
