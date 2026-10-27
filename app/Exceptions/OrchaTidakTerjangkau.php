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
}
