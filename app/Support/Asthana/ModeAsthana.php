<?php

namespace App\Support\Asthana;

use App\Models\JobApplication;
use App\Models\Lowongan;
use App\Models\Message;
use App\Models\User;

/**
 * "Mode Asthana" di lemon — padanan mode Orcha.
 *
 * Situs perusahaan, karyawan, lowongan, pelamar, dan pesan masuk dipindah ke
 * bilah samping tersendiri. Seperti Orcha, penandanya NAMA RUTE (bukan sesi),
 * supaya admin bisa membuka tab lemon dan tab Asthana bersamaan.
 *
 * Gerbangnya izin 'akses_asthana' (lihat AksesAsthanaSeeder); izin per layar
 * (view_lowongan, view_pelamar, …) tetap berlaku di dalamnya.
 */
class ModeAsthana
{
    /** Awalan nama rute yang termasuk mode Asthana. */
    public const RUTE = [
        'admin.asthana.*',
        'admin.karyawan.*',
        'admin.lowongan.*',
        'admin.pelamar.*',
        'admin.message.*',
    ];

    /** Izin yang dipindah ke kelompok 'asthana' di layar Role → Permission. */
    public const IZIN = [
        'akses_asthana',
        'kelola_situs_asthana',
        'view_karyawan', 'create_karyawan', 'edit_karyawan', 'delete_karyawan',
        'view_lowongan', 'create_lowongan', 'edit_lowongan', 'delete_lowongan',
        'view_pelamar', 'delete_pelamar',
        'view_message', 'delete_message',
    ];

    public static function aktif(): bool
    {
        return request()->routeIs(...self::RUTE);
    }

    /**
     * Angka "perlu ditindak" untuk bilah samping & dasbor — hanya yang boleh
     * dilihat pengguna ini.
     *
     * @return array{pelamar_baru:int, pesan_belum_dibaca:int, lowongan_lewat_batas:int}
     */
    public static function perlu(?User $u): array
    {
        $boleh = fn (string $izin) => $u && $u->hasPermission($izin);

        return [
            'pelamar_baru' => $boleh('view_pelamar') ? JobApplication::where('status', 'baru')->count() : 0,
            'pesan_belum_dibaca' => $boleh('view_message') ? Message::unread()->count() : 0,
            'lowongan_lewat_batas' => $boleh('view_lowongan')
                ? Lowongan::active()->whereNotNull('batas_lamaran')->whereDate('batas_lamaran', '<', now()->toDateString())->count()
                : 0,
        ];
    }
}
