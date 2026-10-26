<?php

namespace App\Livewire\Pages\Admin\Orcha\Vendor\Concerns;

use App\Exceptions\OrchaTidakTerjangkau;

/**
 * Formulir vendor yang dipakai dua layar: daftar dan rincian.
 *
 * Ditarik ke sini, bukan disalin, karena menyalinnya berarti dua formulir
 * yang menyimpan ke tempat yang sama tetapi bisa berbeda aturannya — dan
 * yang berbeda baru ketahuan saat salah satunya sudah menyimpan nilai yang
 * tidak diterima yang lain.
 */
trait MenyuntingVendor
{
    public bool $tambah = false;

    public ?int $sunting = null;

    public array $isian = [];

    /** Sakelar dipegang properti tersendiri — lihat OrchaRujukanList::$aktif. */
    public bool $aktif = true;

    /**
     * Jenis vendor yang paling sering dipakai.
     *
     * Ditawarkan sebagai pilihan cepat, bukan dikunci: vendor nyata tidak
     * selalu muat di lima kotak, dan menolak yang keenam hanya membuat admin
     * mengetiknya di kolom lain yang bukan tempatnya.
     */
    public const JENIS = ['Armada', 'Akomodasi', 'Konsumsi', 'Tiket', 'Pemandu'];

    /**
     * Daftar jenisnya lewat metode, bukan tetapan yang dibaca langsung.
     *
     * PHP melarang membaca tetapan sebuah trait lewat nama traitnya
     * (MenyuntingVendor::JENIS) — yang diizinkan hanya lewat kelas yang
     * memakainya. Di dalam trait, self:: sudah menunjuk kelas itu.
     */
    public function pilihanJenis(): array
    {
        return self::JENIS;
    }

    protected function kosongkanVendor(): void
    {
        $this->isian = ['nama' => '', 'kontak' => '', 'jenis' => '', 'provinsi' => '', 'catatan' => ''];
        $this->aktif = true;
    }

    protected function isiDari(array $baris): void
    {
        $this->isian = [
            'nama' => (string) $baris['nama'],
            'kontak' => (string) ($baris['kontak'] ?? ''),
            'jenis' => (string) ($baris['jenis'] ?? ''),
            'provinsi' => (string) ($baris['provinsi'] ?? ''),
            'catatan' => (string) ($baris['catatan'] ?? ''),
        ];
        $this->aktif = (bool) $baris['aktif'];
    }

    public function bukaTambah(): void
    {
        $this->sunting = null;
        $this->tambah = true;
        $this->kosongkanVendor();
        $this->resetValidation();
    }

    public function bukaSunting(int $id, array $baris): void
    {
        $this->tambah = false;
        $this->sunting = $id;
        $this->resetValidation();
        $this->isiDari($baris);
    }

    public function tutup(): void
    {
        $this->sunting = null;
        $this->tambah = false;
        $this->kosongkanVendor();
    }

    public function pakaiJenis(string $jenis): void
    {
        // Menekan jenis yang sedang terpilih melepaskannya — tanpa itu
        // jenis yang salah tekan tidak punya cara dibatalkan.
        $this->isian['jenis'] = trim((string) ($this->isian['jenis'] ?? '')) === $jenis ? '' : $jenis;
    }

    /** @return array<string, mixed> */
    protected function periksaVendor(): array
    {
        $this->validate([
            'isian.nama' => 'required|string|min:2|max:120',
            'isian.kontak' => 'nullable|string|max:120',
            'isian.jenis' => 'nullable|string|max:30',
            'isian.provinsi' => 'nullable|string|max:80',
            'isian.catatan' => 'nullable|string|max:300',
        ], [
            'isian.nama.required' => 'Isi nama vendornya.',
        ], [
            'isian.nama' => 'nama', 'isian.kontak' => 'kontak',
        ]);

        return [
            'nama' => trim($this->isian['nama']),
            // Nomor TIDAK dipaksa berformat angka: "0812xxx (Pak Budi)"
            // justru bentuk yang paling berguna saat menelepon.
            'kontak' => trim($this->isian['kontak']) ?: null,
            'jenis' => trim($this->isian['jenis']) ?: null,
            // Kosong berarti BERLAKU DI MANA SAJA, bukan belum diisi.
            'provinsi' => trim($this->isian['provinsi']) ?: null,
            'catatan' => trim($this->isian['catatan']) ?: null,
            'aktif' => $this->aktif,
        ];
    }

    /** Nomor yang bisa ditelepon, diambil dari kontak yang ditulis bebas. */
    public function nomorDari(?string $kontak): ?string
    {
        if (! preg_match('/\d[\d\s().+-]{6,}/', (string) $kontak, $cocok)) {
            return null;
        }

        $angka = preg_replace('/\D/', '', $cocok[0]);

        return strlen($angka) >= 7 ? $angka : null;
    }

    /** Bentuk 62… untuk WhatsApp; 0 di depan DIGANTI, bukan ditempeli. */
    public function nomorWa(?string $kontak): ?string
    {
        $angka = $this->nomorDari($kontak);

        if ($angka === null) {
            return null;
        }

        return str_starts_with($angka, '0') ? '62'.substr($angka, 1) : $angka;
    }

    /**
     * Nomor yang panjangnya di luar wajar.
     *
     * Bukan penolakan — kontaknya memang teks bebas, dan nomor kantor
     * berbeda panjang dari nomor seluler. Tetapi salah ketik satu digit
     * tetap tersimpan dengan yakin, dan tombol WhatsApp-nya mengarah ke
     * nomor orang lain tanpa satu pun tanda. Jadi ditandai, bukan ditolak.
     */
    public function nomorJanggal(?string $kontak): bool
    {
        $angka = $this->nomorDari($kontak);

        if ($angka === null) {
            return false;
        }

        $panjang = strlen($angka);

        return $panjang < 9 || $panjang > 15;
    }

    /** Mematikan vendor: barisnya dibaca ulang dari Orcha, bukan dari markah. */
    public function matikan(int $id): void
    {
        try {
            $v = $this->orcha()->ambil("/vendor/{$id}/rincian")['data']['vendor'] ?? null;

            if ($v === null) {
                $this->dispatch('toast-error', message: 'Vendornya tidak ditemukan.');

                return;
            }

            $this->orcha()->ubah("/vendor/{$id}", [
                'nama' => $v['nama'],
                'kontak' => $v['kontak'],
                'jenis' => $v['jenis'],
                'provinsi' => $v['provinsi'] ?? null,
                'catatan' => $v['catatan'],
                'aktif' => false,
            ]);

            cache()->forget('orcha.rab.katalog');
            $this->dispatch('order-updated', message: "{$v['nama']} dimatikan. Kontaknya tetap terbaca di RAB yang sudah memakainya.");
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }
}
