<?php

namespace App\Mail;

use App\Models\Kegiatan;
use Illuminate\Mail\Mailable;

/**
 * Kabar kepada peserta sebuah kegiatan.
 *
 * Satu surat dengan tiga rupa, bukan tiga kelas: isinya sama persis — kegiatan
 * apa, kapan, di mana — hanya kalimat pembukanya yang berbeda. Memecahnya jadi
 * tiga berkas berarti tiga tempat yang harus diubah setiap kali rincian
 * kegiatan bertambah.
 *
 * Dikirim sinkron seperti ModulAdminDijedaMail: antrean di server ini tidak
 * bisa diandalkan karena proc_open dimatikan hosting. Kegagalan kirim TIDAK
 * boleh membatalkan penyimpanan kegiatannya — itu diurus pemanggilnya.
 *
 * Selalu membawa versi TEKS di samping HTML-nya; surel yang hanya berisi HTML
 * adalah penanda spam yang paling sering dipakai penyaring.
 */
class UndanganKegiatanMail extends Mailable
{
    public const UNDANGAN = 'undangan';

    public const PERUBAHAN = 'perubahan';

    public const PEMBATALAN = 'pembatalan';

    public const DIKELUARKAN = 'dikeluarkan';

    public function __construct(
        public Kegiatan $kegiatan,
        public string $rupa = self::UNDANGAN,
        public string $olehSiapa = 'Admin',
    ) {}

    public function build()
    {
        $k = $this->kegiatan;

        $judul = match ($this->rupa) {
            self::PERUBAHAN => 'Perubahan jadwal: '.$k->judul,
            self::PEMBATALAN => 'Dibatalkan: '.$k->judul,
            self::DIKELUARKAN => 'Anda tidak lagi terdaftar: '.$k->judul,
            default => 'Undangan: '.$k->judul,
        };

        $isi = [
            'judul' => $judul,
            'rupa' => $this->rupa,
            'kegiatan' => $k,
            'namaJenis' => $k->label(),
            'tanggal' => $k->mulai->locale('id')->translatedFormat('l, d F Y'),
            'waktu' => $k->rentangWaktu(),
            'olehSiapa' => $this->olehSiapa,
        ];

        // Kabar internal tetap atas nama perusahaan — panel lemon bernaung di
        // sana, dan karyawan mengenalnya. Dipatok tegas, bukan mengandalkan
        // bawaan, supaya tidak ikut berubah bila pengirim bawaan diganti.
        $dari = config('mail.from.address');

        return $this->subject($judul.' — lemon by ACM')
            ->from($dari, config('mail.from.name'))
            ->replyTo($dari, config('mail.from.name'))
            ->text('emails.undangan-kegiatan-teks', $isi)
            ->view('emails.undangan-kegiatan', $isi);
    }
}
