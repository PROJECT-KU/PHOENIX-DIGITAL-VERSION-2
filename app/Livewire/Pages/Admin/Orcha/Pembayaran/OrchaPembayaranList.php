<?php

namespace App\Livewire\Pages\Admin\Orcha\Pembayaran;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Bukti transfer yang dikirim pelanggan lewat formulir di website.
 *
 * Menggantikan kebiasaan mengumpulkan bukti di percakapan WhatsApp: satu open
 * trip berisi enam peserta yang masing-masing membayar dua kali, dan pada H-5
 * pertanyaan "siapa yang belum lunas" harus bisa dijawab dalam hitungan menit.
 */
class OrchaPembayaranList extends Component
{
    use MemanggilOrcha;

    public ?int $sedangDicek = null;

    public string $statusBaru = '';

    public string $catatanAdmin = '';

    public function buka(array $baris): void
    {
        $this->sedangDicek = (int) $baris['id'];
        $this->statusBaru = $baris['status'] ?? 'menunggu';
        $this->catatanAdmin = (string) ($baris['catatan_admin'] ?? '');
    }

    public function tutup(): void
    {
        $this->reset(['sedangDicek', 'statusBaru', 'catatanAdmin']);
    }

    public function simpan(): void
    {
        if (! $this->sedangDicek) {
            return;
        }

        $this->kirimPerubahan(
            "/pembayaran/{$this->sedangDicek}/status",
            ['status' => $this->statusBaru, 'catatan_admin' => $this->catatanAdmin],
            'Status pembayaran diperbarui di Orcha.'
        );

        $this->tutup();
    }

    /**
     * Tautan WhatsApp berisi kabar status pembayaran, siap kirim.
     *
     * Surat sudah dikirim otomatis oleh Orcha setiap status berubah, tapi tidak
     * semua pelanggan membuka kotak suratnya — sebagian tidak pernah. WhatsApp
     * yang dibaca, dan nomornya memang sudah ada di formulir.
     *
     * Pesannya dibuka lebih dulu di WhatsApp, bukan langsung terkirim: admin
     * masih bisa menambah kalimat, dan yang menekan kirim tetap manusia.
     *
     * Emoji ditulis apa adanya di sini lalu disandikan dengan rawurlencode.
     * urlencode biasa mengubah spasi jadi "+", dan WhatsApp menampilkan tanda
     * plus itu apa adanya — kalimatnya jadi penuh "+" alih-alih spasi.
     */
    public function tautanWa(array $baris): ?string
    {
        $nomor = preg_replace('/\D/', '', (string) data_get($baris, 'pesanan.whatsapp'));

        if (blank($nomor)) {
            return null;
        }

        $nomor = preg_replace('/^0/', '62', $nomor);

        return 'https://wa.me/'.$nomor.'?text='.rawurlencode($this->pesanWa($baris));
    }

    /**
     * Isi pesannya, berbeda menurut status buktinya.
     *
     * Tiga status, tiga maksud yang berbeda: yang menunggu perlu ditenangkan,
     * yang diterima perlu tahu sisanya, yang ditolak perlu tahu apa yang harus
     * diperbaiki. Kalimatnya disamakan dengan surat yang dikirim Orcha supaya
     * pelanggan tidak menerima dua kabar yang berbeda bunyinya.
     */
    public function pesanWa(array $baris): string
    {
        $nama = data_get($baris, 'pesanan.nama') ?: 'Kak';
        $kode = $baris['kode'] ?? '-';
        $nominal = $baris['nominal_formatted'] ?? '-';
        $paket = data_get($baris, 'pesanan.keterangan');
        $tagihan = data_get($baris, 'pesanan.tagihan') ?: [];

        $pembuka = "Halo {$nama} 👋\n\n"
            ."Kabar dari *Orcha Journey* soal pembayaran Anda:\n"
            ."🧾 Kode pesanan: *{$kode}*\n"
            .($paket ? "📍 Pesanan: {$paket}\n" : '')
            ."💰 Nominal: *{$nominal}*\n\n";

        $isi = match ($baris['status'] ?? 'menunggu') {
            'diterima' => "✅ Pembayaran Anda *sudah kami terima* dan tercatat.\n\n"
                .(($tagihan['lunas'] ?? false)
                    ? "🎉 Pesanan Anda sudah *LUNAS*. Tidak ada sisa yang perlu dibayar lagi.\n"
                    : (isset($tagihan['sisa_teks'])
                        ? "📌 Sisa yang perlu dilunasi: *{$tagihan['sisa_teks']}*\n"
                        : '')),

            'ditolak' => "⚠️ Maaf, bukti yang Anda kirim *belum bisa kami cocokkan* dengan mutasi rekening.\n\n"
                .(($baris['catatan_admin'] ?? null) ? "📝 Catatan tim kami: {$baris['catatan_admin']}\n\n" : "\n")
                ."Mohon kirim ulang buktinya ya. Kalau transfernya sudah benar-benar keluar, "
                ."balas pesan ini — uang yang sudah berpindah tidak hilang. 🙏\n",

            default => "⏳ Bukti Anda *sedang kami periksa*. Kami kabari lagi setelah "
                ."dicocokkan dengan mutasi rekening.\n",
        };

        return $pembuka.$isi."\nTerima kasih 🙏";
    }

    public function render()
    {
        $hasil = $this->muat('/pembayaran', $this->parameterDaftar());

        return view('livewire.pages.admin.orcha.pembayaran.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanStatus' => $this->rujukan('status_pembayaran'),
        ])->layout('livewire.layout.templateindex');
    }
}
