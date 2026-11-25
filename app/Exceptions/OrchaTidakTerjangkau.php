<?php

namespace App\Exceptions;

use Exception;

/**
 * Dilempar bila API Orcha tidak bisa dihubungi atau menolak permintaan.
 *
 * Pesannya sengaja sudah berbahasa Indonesia dan layak dibaca admin, jadi
 * halaman cukup menampilkan getMessage() apa adanya.
 */
class OrchaTidakTerjangkau extends Exception
{
    /**
     * Isi balasan penolakan, bila ada.
     *
     * Sebagian penolakan Orcha membawa keterangan yang bisa DITINDAKLANJUTI,
     * bukan sekadar dibaca — "kembaran" menyebut baris mana yang bentrok,
     * dan karena itu layar bisa menawarkan "tetap simpan" alih-alih
     * membiarkan admin menebak jalan keluar. Mencocokkan kalimatnya dengan
     * string akan patah pada perubahan kata pertama.
     *
     * @var array<string, mixed>
     */
    public array $isi = [];

    /** @param  array<string, mixed>  $isi */
    public function dengan(array $isi): static
    {
        $this->isi = $isi;

        return $this;
    }

    /** Penolakan karena sudah ada baris yang dianggap kembarannya. */
    public function kembaran(): ?array
    {
        return $this->isi['kembaran'] ?? null;
    }

    /**
     * Penolakan yang masih bisa dilanjutkan admin dengan sengaja.
     *
     * Dipakai perpindahan rombongan: kuota tujuan yang kurang ditolak lebih
     * dulu, tetapi menjual kursi ke-41 dari bus 40 kursi adalah keputusan yang
     * memang kadang diambil. Dibaca dari penandanya, bukan dari kalimatnya —
     * mencocokkan kata "kursi" akan patah pada hari kalimatnya diperhalus.
     */
    public function butuhPaksa(): bool
    {
        return (bool) ($this->isi['butuh_paksa'] ?? false);
    }

    /**
     * Perpindahan paket yang menghasilkan KELEBIHAN BAYAR, dan nasibnya
     * belum diputuskan.
     *
     * Dibedakan dari butuhPaksa() karena yang diminta bukan penegasan
     * melainkan PILIHAN: uang lebihnya dikembalikan, atau disimpan sebagai
     * saldo untuk trip berikutnya. Orcha membatalkan seluruh perpindahannya
     * sampai pilihan itu dikirim — pendaftaran yang sudah pindah sementara
     * selisihnya menggantung adalah keadaan yang paling mudah terlupakan.
     */
    public function butuhKeputusanKelebihan(): bool
    {
        return (bool) ($this->isi['butuh_keputusan_kelebihan'] ?? false);
    }

    /** Berapa lebihnya, untuk disebutkan kepada admin. */
    public function kelebihan(): int
    {
        return (int) ($this->isi['kelebihan'] ?? 0);
    }
}
