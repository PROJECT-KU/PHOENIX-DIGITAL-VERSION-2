<?php

namespace App\Support\Asthana;

use App\Mail\TandaTerimaSitusMail;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\User;
use App\Notifications\KabarSitusAsthana;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Kabar untuk pesan & lamaran yang masuk dari situs Asthana.
 *
 * TIDAK PERNAH melempar galat. Pemanggilnya ada di dalam blok try yang,
 * bila gagal, menghapus berkas lamaran yang baru tersimpan — notifikasi yang
 * gagal tidak boleh menghanguskan lamaran yang sudah masuk.
 */
class KabarSitus
{
    public static function pesanMasuk(Message $pesan): void
    {
        self::beriTahuAdmin('view_message', new KabarSitusAsthana(
            judul: 'Pesan baru dari situs Asthana ✉️',
            isi: "{$pesan->name}: ".Str::limit(trim((string) $pesan->message), 90),
            url: self::rute('admin.message.detail', $pesan),
            ikon: 'bi-envelope',
            warna: 'primary',
        ));

        self::kirimTandaTerima($pesan->email, new TandaTerimaSitusMail('pesan', $pesan->name));
    }

    public static function lamaranMasuk(JobApplication $lamaran): void
    {
        // Lamaran umum (talent pool) tidak terikat ke lowongan: posisi null.
        $posisi = $lamaran->job?->title;
        $nomor = str_pad((string) $lamaran->id, 6, '0', STR_PAD_LEFT);

        self::beriTahuAdmin('view_pelamar', new KabarSitusAsthana(
            judul: 'Lamaran baru 📄',
            isi: $posisi ? "{$lamaran->name} melamar {$posisi}." : "{$lamaran->name} mengirim lamaran umum.",
            url: self::rute('admin.pelamar.detail', $lamaran->id),
            ikon: 'bi-person-badge',
            warna: 'success',
        ));

        self::kirimTandaTerima($lamaran->email, new TandaTerimaSitusMail('lamaran', $lamaran->name, $posisi, $nomor));
    }

    private static function beriTahuAdmin(string $izin, KabarSitusAsthana $kabar): void
    {
        try {
            // Lewat hasPermission(), bukan whereHas('role.permissions'): izin
            // juga bisa datang per orang (permission_user).
            $penerima = User::query()->with('role.permissions', 'izinTambahan')->get()
                ->filter(fn (User $u) => $u->hasPermission($izin));

            if ($penerima->isNotEmpty()) {
                Notification::send($penerima, $kabar);
            }
        } catch (\Throwable $e) {
            Log::error('Gagal memberi tahu admin soal kiriman situs Asthana: '.$e->getMessage());
        }
    }

    private static function kirimTandaTerima(?string $email, TandaTerimaSitusMail $surel): void
    {
        if (! config('asthana.balasan_otomatis') || ! $email) {
            return;
        }

        try {
            Mail::to($email)->send($surel);
        } catch (\Throwable $e) {
            // Alamat penerima sengaja tidak dicatat: data pribadi.
            Log::error('Gagal mengirim tanda terima situs Asthana: '.$e->getMessage());
        }
    }

    private static function rute(string $nama, mixed $param): string
    {
        try {
            return route($nama, $param);
        } catch (\Throwable) {
            return url('/admin');
        }
    }
}
