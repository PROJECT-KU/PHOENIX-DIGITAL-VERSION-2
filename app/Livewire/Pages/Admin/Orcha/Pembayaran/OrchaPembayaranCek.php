<?php

namespace App\Livewire\Pages\Admin\Orcha\Pembayaran;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Livewire\Pages\Admin\Orcha\Pembayaran\Concerns\KabarPembayaran;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Lembar cek satu bukti pembayaran.
 *
 * Halaman tersendiri, bukan jendela di atas daftar.
 *
 * Yang dikerjakan di sini adalah memutuskan uang orang lain: nominalnya
 * dicocokkan dengan mutasi rekening, buktinya diperbesar dan dibaca, lalu
 * statusnya ditetapkan — dan penetapan itu mengirim email ke pelanggan yang
 * tidak bisa ditarik kembali. Pekerjaan sepanjang itu pantas mendapat
 * halamannya sendiri, dengan alamat yang bisa disalin dan dibagikan ke admin
 * lain.
 *
 * Ia juga dibuka lewat alamat biasa, jadi tombolnya di daftar cukup berupa
 * tautan: tidak ada yang perlu berhasil lebih dulu sebelum halamannya terbuka.
 */
class OrchaPembayaranCek extends Component
{
    use KabarPembayaran;
    use MemanggilOrcha;
    use WithFileUploads;

    public int $pembayaranId;

    public array $bukti = [];

    public string $statusBaru = '';

    public string $catatanAdmin = '';

    /**
     * Bukti susulan, atau pengganti bukti yang sudah ada.
     *
     * Sebelum ini satu-satunya jalur yang menerima berkas adalah pencatatan
     * pembayaran BARU. Admin yang lupa melampirkan buktinya tinggal punya dua
     * pilihan, dan dua-duanya buruk: mencatat ulang — yang menghitung uangnya
     * dua kali sehingga tagihannya salah — atau membiarkannya tanpa gambar,
     * sehingga tidak ada yang bisa ditelusuri kalau suatu saat dipersoalkan.
     */
    public $buktiBaru;

    public function mount(int $pembayaran): void
    {
        $this->pembayaranId = $pembayaran;

        $baris = $this->muat("/pembayaran/{$pembayaran}")['data'] ?? [];

        if ($baris === []) {
            $this->galat = $this->galat ?: 'Bukti pembayaran itu tidak ditemukan di Orcha.';

            return;
        }

        $this->bukti = $baris;
        $this->statusBaru = $baris['status'] ?? 'menunggu';
        $this->catatanAdmin = (string) ($baris['catatan_admin'] ?? '');
    }

    public function simpan(): void
    {
        $this->kirimPerubahan(
            "/pembayaran/{$this->pembayaranId}/status",
            ['status' => $this->statusBaru, 'catatan_admin' => $this->catatanAdmin],
            'Status pembayaran diperbarui di Orcha.'
        );

        // Data di layar disegarkan supaya lencana status dan pesan WhatsApp-nya
        // mengikuti yang BARU tersimpan. Tanpa ini admin membaca kabar lama dan
        // mengirimkannya ke pelanggan.
        $this->bukti = $this->muat("/pembayaran/{$this->pembayaranId}")['data'] ?? $this->bukti;

    }

    /**
     * Melampirkan bukti susulan, atau mengganti yang sudah ada.
     *
     * Terpisah dari simpan(): menyimpan status mengirim email ke pelanggan
     * yang tidak bisa ditarik kembali, sedangkan melampirkan bukti tidak
     * mengabari siapa pun. Menggabungkannya berarti admin yang cuma ingin
     * menyusulkan gambar ikut mengirim surat.
     */
    public function unggahBukti(): void
    {
        $this->validate([
            'buktiBaru' => 'required|image|max:4096',
        ], [], ['buktiBaru' => 'bukti transfer']);

        try {
            $hasil = $this->orcha()->unggah(
                "/pembayaran/{$this->pembayaranId}/bukti",
                'bukti',
                $this->buktiBaru,
            );

            $this->buktiBaru = null;

            // Buktinya disegarkan dari jawaban Orcha, bukan ditebak dari
            // berkas yang baru diunggah: alamat gambarnya dirakit di sana, dan
            // menebaknya di sini berarti tautan yang salah begitu jalurnya
            // berubah.
            $this->bukti = $hasil['data'] ?? $this->bukti;

            $this->dispatch('order-updated',
                message: $hasil['pesan'] ?? 'Bukti transfer tersimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->buktiBaru = null;
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /** Kembali ke daftar. Dulu menutup jendela; sekarang berpindah halaman. */
    public function tutup(): void
    {
        $this->redirectRoute('admin.orcha.pembayaran', navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.pembayaran.cek', [
            'pilihanStatus' => $this->rujukan('status_pembayaran'),
        ])->layout('livewire.layout.templateindex');
    }
}
