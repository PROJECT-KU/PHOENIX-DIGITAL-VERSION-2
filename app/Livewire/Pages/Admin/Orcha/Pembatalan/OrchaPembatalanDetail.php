<?php

namespace App\Livewire\Pages\Admin\Orcha\Pembatalan;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Satu pengajuan pembatalan, lengkap dengan dasar perhitungannya.
 *
 * Menindaklanjuti pembatalan berarti menjawab tiga hal sekaligus: siapa yang
 * mengajukan, berapa yang sudah ia bayar, dan berapa yang harus dikirim balik.
 * Sebelum halaman ini ketiganya tersebar di tiga tempat — daftar pembatalan,
 * halaman pesanan, dan daftar bukti bayar — dan yang paling sering terlewat
 * adalah bukti yang masih menunggu, padahal justru itu yang mengubah angkanya.
 */
class OrchaPembatalanDetail extends Component
{
    use MemanggilOrcha;

    public int $pembatalanId;

    public string $statusBaru = '';

    public string $catatanAdmin = '';

    /**
     * Potongan yang ditetapkan admin, bertitik seperti "500.000".
     *
     * Terisi dari usulan kebijakan saat halaman dibuka, lalu boleh diubah.
     * Sistem tidak tahu segalanya: ada tiket masuk yang sudah dibayarkan ke
     * pihak ketiga dan tidak bisa ditarik, ada pula kelonggaran yang memang
     * layak diberikan. Yang memutuskan tetap manusia — sistem hanya menghemat
     * langkah pertamanya.
     */
    public string $potongan = '';

    /** Data pengajuan yang sedang dibuka; diisi ulang tiap kali digambar. */
    public array $data = [];

    public function mount(int $id): void
    {
        $this->pembatalanId = $id;
    }

    public function simpan(): void
    {
        $this->kirimPerubahan(
            "/pembatalan/{$this->pembatalanId}/status",
            [
                'status' => $this->statusBaru,
                'catatan_admin' => $this->catatanAdmin,
                'potongan_ditetapkan' => $this->angka($this->potongan),
            ],
            'Tindak lanjut pembatalan tersimpan di Orcha.'
        );
    }

    /** Rupiah dibaca dan ditulis bertitik, seperti isian denda di sewa. */
    public function updatedPotongan(): void
    {
        $this->potongan = number_format($this->angka($this->potongan), 0, ',', '.');
    }

    /** "1.500.000" -> 1500000. Kosong berarti nol, bukan null. */
    private function angka($nilai): int
    {
        return (int) preg_replace('/\D/', '', (string) $nilai);
    }

    /**
     * Berapa yang kembali menurut angka yang sedang tertulis di layar.
     *
     * Dihitung di sini, bukan menunggu jawaban server, supaya admin melihat
     * akibat ketikannya seketika — angka pengembalian yang baru diketahui
     * setelah disimpan membuat orang menyimpan dua kali untuk memastikan.
     */
    public function kembaliSekarang(): int
    {
        $dibayar = (int) ($this->data['perkiraan']['dibayar'] ?? 0);

        return max(0, $dibayar - min($this->angka($this->potongan), $dibayar));
    }

    /** Angka di layar belum sama dengan yang tersimpan di Orcha. */
    public function belumTersimpan(): bool
    {
        $tersimpan = $this->data['potongan_ditetapkan'] ?? null;
        $usulan = $this->data['perkiraan']['usulan'] ?? null;

        return $this->angka($this->potongan) !== (int) ($tersimpan ?? $usulan ?? 0);
    }

    /**
     * Pesan WhatsApp berisi hasil perhitungan pengembalian.
     *
     * Inilah kalimat yang selama ini diketik ulang tiap kali. Angkanya diambil
     * dari perkiraan yang sama dengan yang tampil di layar, jadi yang dibaca
     * admin dan yang dibaca pelanggan tidak mungkin berbeda.
     *
     * Emojinya ditulis sebagai penanda; peramban yang merakitnya — lihat
     * catatan di partial salin-wa.
     */
    public function pesanWa(): string
    {
        $p = $this->data['perkiraan'] ?? null;
        $nama = $this->data['nama_pemohon'] ?? 'Kak';
        $kode = $this->data['kode_pendaftaran'] ?? '-';
        $rek = $this->data['rekening'] ?? [];

        $pesan = "Halo {$nama} [[E:1F44B]]\n\n"
            ."Pengajuan pembatalan Anda untuk pesanan *{$kode}* sudah kami periksa.\n\n";

        if ($p) {
            $pesan .= "[[E:1F4C4]] Sudah dibayar: *{$p['dibayar_teks']}*\n"
                ."[[E:2702]] Potongan ({$p['persen']}%): *{$p['potongan_teks']}*\n"
                ."[[E:1F4B0]] Dikembalikan: *{$p['kembali_teks']}*\n\n"
                ."Dasarnya: {$p['batas']}, sesuai Kebijakan Pembatalan & Pengembalian Dana.\n\n";
        }

        if (($rek['bank'] ?? null) && ($rek['nomor'] ?? null)) {
            $pesan .= "Dana dikirim ke {$rek['bank']} {$rek['nomor']} a.n. {$rek['atas_nama']}.\n"
                ."Mohon dikonfirmasi bila rekeningnya sudah benar.\n\n";
        }

        return $pesan.'Terima kasih [[E:1F64F]]';
    }

    public function tautanWa(): ?string
    {
        $tautan = \App\Support\TautanWa::kirim(
            $this->data['whatsapp'] ?? null,
            preg_replace('/\[\[E:[0-9A-F]+\]\] ?/', '', $this->pesanWa()),
        );

        return $tautan ?: null;
    }

    public function render()
    {
        $this->data = $this->muat("/pembatalan/{$this->pembatalanId}")['data'] ?? [];

        // Isian hanya diisikan sekali, saat halaman pertama dibuka. Menimpanya
        // tiap gambar ulang akan membuang ketikan admin yang belum disimpan.
        if ($this->statusBaru === '' && $this->data !== []) {
            $this->statusBaru = $this->data['status'] ?? 'diajukan';
            $this->catatanAdmin = (string) ($this->data['catatan_admin'] ?? '');

            // Terisi dari yang pernah ditetapkan; bila belum pernah, dari usulan
            // kebijakan. Admin melanjutkan, bukan menaksir dari nol.
            $awal = $this->data['potongan_ditetapkan']
                ?? ($this->data['perkiraan']['usulan'] ?? 0);

            $this->potongan = number_format((int) $awal, 0, ',', '.');
        }

        return view('livewire.pages.admin.orcha.pembatalan.detail', [
            'pembatalan' => $this->data,
            'pilihanStatus' => $this->rujukan('status_pembatalan'),
        ])->layout('livewire.layout.templateindex');
    }
}
