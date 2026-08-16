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

        // Memakai api.whatsapp.com/send, bukan wa.me.
        //
        // Keduanya sah, tapi wa.me adalah tautan pendek yang dialihkan dulu
        // sebelum sampai ke aplikasi — dan tiap pengalihan adalah kesempatan
        // query-nya disandikan ulang. Emoji yang berubah jadi tanda tanya di
        // layar pelanggan paling mungkin lahir di situ, karena tautan yang
        // kami hasilkan sendiri sudah terbukti berisi UTF-8 yang sah.
        //
        // api.whatsapp.com adalah alamat resmi yang didokumentasikan WhatsApp,
        // satu lompatan lebih pendek, dan sudah dipakai halaman publik Orcha.
        return 'https://api.whatsapp.com/send?phone='.$nomor
            .'&text='.rawurlencode($this->pesanWa($baris));
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

        // Emoji dipasang lewat penolong ini supaya bisa dimatikan seluruhnya
        // dari .env bila di lapangan ia tetap berubah jadi tanda tanya. Yang
        // menyusun bentuk pesan adalah tebal bawaan WhatsApp dan baris baru,
        // bukan emojinya — jadi tanpa emoji pun kabarnya tetap terbaca.
        //
        // Emoji yang dipakai sengaja yang polos: tanpa penanda ragam (U+FE0F)
        // seperti pada ⚠️, karena penanda itu tidak kelihatan tapi ikut
        // disandikan, dan justru bagian itu yang paling sering rusak di jalan.
        $pakaiEmoji = (bool) config('orcha.emoji_wa', true);
        $e = fn (string $emoji) => $pakaiEmoji ? $emoji.' ' : '';

        $pembuka = 'Halo '.$nama.($pakaiEmoji ? ' 👋' : '')."\n\n"
            ."Kabar dari *Orcha Journey* soal pembayaran Anda:\n"
            .$e('📄')."Kode pesanan: *{$kode}*\n"
            .($paket ? $e('📍')."Pesanan: {$paket}\n" : '')
            .$e('💰')."Nominal: *{$nominal}*\n\n";

        $isi = match ($baris['status'] ?? 'menunggu') {
            'diterima' => $e('✅')."Pembayaran Anda *sudah kami terima* dan tercatat.\n\n"
                .(($tagihan['lunas'] ?? false)
                    ? $e('🎉')."Pesanan Anda sudah *LUNAS*. Tidak ada sisa yang perlu dibayar lagi.\n"
                    : (isset($tagihan['sisa_teks'])
                        ? $e('📌')."Sisa yang perlu dilunasi: *{$tagihan['sisa_teks']}*\n"
                        : '')),

            'ditolak' => $e('❗')."Maaf, bukti yang Anda kirim *belum bisa kami cocokkan* dengan mutasi rekening.\n\n"
                .(($baris['catatan_admin'] ?? null) ? $e('📝')."Catatan tim kami: {$baris['catatan_admin']}\n\n" : "\n")
                ."Mohon kirim ulang buktinya ya. Kalau transfernya sudah benar-benar keluar, "
                ."balas pesan ini — uang yang sudah berpindah tidak hilang.\n",

            default => $e('⏳')."Bukti Anda *sedang kami periksa*. Kami kabari lagi setelah "
                ."dicocokkan dengan mutasi rekening.\n",
        };

        return $pembuka.$isi."\nTerima kasih".($pakaiEmoji ? ' 🙏' : '.');
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
