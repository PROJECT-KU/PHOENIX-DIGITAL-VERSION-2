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
     * Dibangun lewat App\Support\TautanWa — penolong yang sudah dipakai alur
     * order, yang memaksa endpoint api.whatsapp.com dan bukan wa.me.
     *
     * Pesannya sengaja TANPA emoji di sini. Skrip di halaman menyusun ulang
     * tautannya dengan emoji yang dirakit di peramban saat tombolnya ditekan;
     * yang ini jaring pengaman bila skripnya gagal — dan kalimat utuh tanpa
     * emoji jauh lebih baik daripada tanda tanya.
     */
    public function tautanWa(array $baris): ?string
    {
        $tautan = \App\Support\TautanWa::kirim(
            data_get($baris, 'pesanan.whatsapp'),
            $this->pesanWaPolos($baris),
        );

        return $tautan ?: null;
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

        // Emoji ditulis sebagai TITIK KODE, bukan huruf emojinya langsung.
        //
        // Ini pola yang sudah terbukti jalan di halaman detail order: emojinya
        // dirakit di peramban dengan String.fromCodePoint, jadi tidak pernah
        // ikut melewati respons server. Komentar di sana menyebutkan alasan
        // yang sama — "agar tidak rusak (?) karena encoding".
        //
        // Empat putaran percobaan membuktikan hal itu memang terjadi di sini:
        // sisi kami bersih di tiap pemeriksaan, tapi yang sampai ke WhatsApp
        // tetap tanda tanya. Jadi jalannya dihindari, bukan diperbaiki.
        //
        // Penanda diganti emoji oleh skrip di partial salin-wa. Bila skripnya
        // tidak jalan, penandanya dibuang begitu saja dan pesannya tetap utuh
        // — lihat tanpaPenanda().
        $pakaiEmoji = (bool) config('orcha.emoji_wa', true);
        $e = fn (string $titikKode) => $pakaiEmoji ? "[[E:{$titikKode}]] " : '';

        $pembuka = 'Halo '.$nama.($pakaiEmoji ? ' [[E:1F44B]]' : '')."\n\n"
            ."Kabar dari *Orcha Journey* soal pembayaran Anda:\n"
            .$e('1F4C4')."Kode pesanan: *{$kode}*\n"
            .($paket ? $e('1F4CD')."Pesanan: {$paket}\n" : '')
            .$e('1F4B0')."Nominal: *{$nominal}*\n\n";

        $isi = match ($baris['status'] ?? 'menunggu') {
            'diterima' => $e('2705')."Pembayaran Anda *sudah kami terima* dan tercatat.\n\n"
                .(($tagihan['lunas'] ?? false)
                    ? $e('1F389')."Pesanan Anda sudah *LUNAS*. Tidak ada sisa yang perlu dibayar lagi.\n"
                    : (isset($tagihan['sisa_teks'])
                        ? $e('1F4CC')."Sisa yang perlu dilunasi: *{$tagihan['sisa_teks']}*\n"
                        : '')),

            'ditolak' => $e('2757')."Maaf, bukti yang Anda kirim *belum bisa kami cocokkan* dengan mutasi rekening.\n\n"
                .(($baris['catatan_admin'] ?? null) ? $e('1F4DD')."Catatan tim kami: {$baris['catatan_admin']}\n\n" : "\n")
                ."Mohon kirim ulang buktinya ya. Kalau transfernya sudah benar-benar keluar, "
                ."balas pesan ini — uang yang sudah berpindah tidak hilang.\n",

            default => $e('23F3')."Bukti Anda *sedang kami periksa*. Kami kabari lagi setelah "
                ."dicocokkan dengan mutasi rekening.\n",
        };

        return $pembuka.$isi."\nTerima kasih".($pakaiEmoji ? ' [[E:1F64F]]' : '.');
    }

    /**
     * Pesan tanpa penanda emoji, untuk tautan yang dibangun server.
     *
     * Dipakai sebagai jaring pengaman: bila skrip perakit emoji tidak sempat
     * jalan, tautannya tetap membawa kalimat yang utuh — hanya tanpa emoji,
     * bukan penuh "[[E:1F44B]]" yang justru lebih memalukan daripada tanda
     * tanya.
     */
    public function pesanWaPolos(array $baris): string
    {
        $polos = preg_replace('/\[\[E:[0-9A-F]+\]\] ?/', '', $this->pesanWa($baris));

        return trim(preg_replace('/[ \t]+$/m', '', $polos));
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
