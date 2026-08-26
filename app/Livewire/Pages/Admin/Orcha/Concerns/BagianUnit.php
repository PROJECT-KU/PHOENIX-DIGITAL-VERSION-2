<?php

namespace App\Livewire\Pages\Admin\Orcha\Concerns;

/**
 * Bagian pemeriksaan yang berlaku untuk satu unit.
 *
 * Aturannya ditulis SEKALI di sini karena tiga layar memakainya dan ketiganya
 * harus sepakat: ceklis di formulir armada, lembar serah terima, dan tabel
 * hasil pemeriksaan di detail sewa. Tiga salinan sejajar untuk hal yang sama
 * lambat laun berbeda sendiri — dan bedanya baru ketahuan saat penyewa
 * membantah adanya kerusakan.
 *
 * Dua hal yang digabung:
 *
 *   1. Yang BERLAKU untuk jenis unit ini. Bus tidak punya ban serep
 *      sebagaimana mobil, dan ceklis yang memuat bagian tak berlaku hanya akan
 *      diisi "Baik" tanpa pernah benar-benar diperiksa.
 *
 *   2. Yang SUDAH tercatat pada unit atau lembar ini, walaupun bagiannya
 *      belakangan dinonaktifkan atau dicabut dari jenis ini. Tanpa itu, hasil
 *      pemeriksaan yang sudah dicatat lenyap dari layar — dan itulah
 *      satu-satunya bukti ketika penyewa membantah.
 */
trait BagianUnit
{
    /**
     * @param  array<int, array<string, mixed>>  $tercatat  Peta kondisi yang sudah tersimpan
     * @return array<string, string>
     */
    protected function bagianUntukUnit(?string $jenis, array ...$tercatat): array
    {
        $semua = $this->rujukan('pemeriksaan_kendaraan');
        $perJenis = $this->rujukan('pemeriksaan_per_jenis');

        /*
         | Jatuh ke daftar penuh bila Orcha belum mengirim pemilahannya —
         | misalnya selama jeda antara lemon dan Orcha ter-deploy. Ceklis yang
         | terlalu panjang masih bisa dikerjakan; ceklis kosong tidak.
         */
        $berlaku = $perJenis[$jenis] ?? $semua;

        foreach ($tercatat as $peta) {
            foreach (array_keys($peta) as $kunci) {
                if (! isset($berlaku[$kunci])) {
                    $berlaku[$kunci] = $semua[$kunci] ?? $kunci;
                }
            }
        }

        return $berlaku;
    }
}
