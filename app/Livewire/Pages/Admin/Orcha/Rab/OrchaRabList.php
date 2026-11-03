<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Daftar RAB & itinerary private trip.
 *
 * Membuat dan mengubah kepala RAB punya halamannya sendiri
 * (OrchaRabForm): borang lima belas isian yang terbuka di bawah daftar
 * mendorong tabelnya keluar layar, dan admin kehilangan tempat ia tadi
 * berada begitu borangnya ditutup.
 */
class OrchaRabList extends Component
{
    use MemanggilOrcha;

    /*
     | $filterStatus sudah milik trait MemanggilOrcha — tidak dideklarasikan
     | ulang di sini supaya atribut #[Url]-nya tidak tertimpa diam-diam.
     */
    #[Url(as: 'provinsi', except: '')]
    public string $filterProvinsi = '';

    #[Url(as: 'jenis', except: '')]
    public string $filterJenis = '';

    /** Hanya yang lewat masa berlaku dan masih menunggu jawaban. */
    #[Url(as: 'basi', except: false)]
    public bool $filterKedaluwarsa = false;

    #[Url(as: 'urut', except: '')]
    public string $urut = '';

    #[Url(as: 'baris', except: 10)]
    public int $barisHalaman = 10;

    /** Panel salin penawaran; berisi id RAB yang sedang disalin. */
    public ?int $salinDari = null;

    public array $salinIsian = [];

    /** Riwayat versi penawaran yang sedang dibuka. */
    public ?int $versiUntuk = null;

    public array $versi = [];

    public array $versiMeta = [];

    public function hapus(int $id): void
    {
        $this->hapusData("/rab/{$id}", 'RAB dihapus.');
    }

    public function updatedFilterProvinsi(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterJenis(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterKedaluwarsa(): void
    {
        $this->halaman = 1;
    }

    public function updatedUrut(): void
    {
        $this->halaman = 1;
    }

    public function updatedBarisHalaman(): void
    {
        $this->halaman = 1;
    }

    /** Dibatasi ke daftar yang dikenal: angkanya datang dari URL. */
    protected function perHalaman(): int
    {
        return in_array($this->barisHalaman, [10, 25, 50, 100], true) ? $this->barisHalaman : 10;
    }

    public function bersihkanSaringan(): void
    {
        $this->cari = '';
        $this->filterStatus = '';
        $this->filterProvinsi = '';
        $this->filterJenis = '';
        $this->filterKedaluwarsa = false;
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->jumlahSaringLanjut() > 0;
    }

    /**
     * Berapa saringan di balik tombol "Saringan lainnya" yang sedang aktif.
     *
     * Kotak cari TIDAK dihitung: ia tetap terlihat, jadi tidak ada yang
     * perlu diberitahukan tentangnya. Yang dihitung hanya yang tersembunyi —
     * saringan yang bekerja tanpa terlihat membuat daftar yang "kurang"
     * terbaca sebagai data hilang, dan angka di tombol itulah yang
     * menjelaskan ke mana sisanya pergi.
     *
     * Urutan tidak dihitung: ia menata, bukan membuang baris.
     */
    public function jumlahSaringLanjut(): int
    {
        return count(array_filter([
            $this->filterStatus, $this->filterProvinsi, $this->filterJenis,
            $this->filterKedaluwarsa ? '1' : '',
        ], fn ($n) => (string) $n !== ''));
    }

    /**
     * Ubin ringkasan: memasang saringannya, menekan lagi melepasnya.
     *
     * Keputusannya dirakit DI SINI, bukan di dalam atribut wire:. Isi
     * atribut bukan kode PHP — ekspresi bersarang di sana sampai ke
     * peramban apa adanya dan kliknya mati tanpa satu pun pesan.
     * Dijaga WireAtributMentahTest.
     */
    public function pasangUbin(string $kunci): void
    {
        $this->halaman = 1;

        if ($kunci === 'kedaluwarsa') {
            $this->filterKedaluwarsa = ! $this->filterKedaluwarsa;

            return;
        }

        $this->filterStatus = $this->filterStatus === $kunci ? '' : $kunci;
    }

    /** Ubin yang sedang memasang saringan — dipakai menandainya. */
    public function ubinNyala(string $kunci): bool
    {
        return $kunci === 'kedaluwarsa'
            ? $this->filterKedaluwarsa
            : $this->filterStatus === $kunci;
    }

    /**
     * Warna untung menurut tipisnya margin.
     *
     * Sebelumnya kolom ini selalu hijau — penawaran yang merugi pun
     * tergambar meyakinkan. Ia satu-satunya angka di layar yang bisa
     * mencegah penawaran merugi terkirim, dan warnanya tidak pernah
     * berubah.
     */
    public function rupaUntung(?float $persen): string
    {
        if ($persen === null) {
            return 'netral';
        }

        return match (true) {
            $persen < 0 => 'rugi',
            $persen < 10 => 'tipis',
            default => 'sehat',
        };
    }

    /**
     * Tautan WhatsApp berisi pesan yang sudah terketik.
     *
     * Nomornya tersimpan dan PDF-nya sudah ada, tetapi mengirimnya tetap
     * manual: unduh, buka WA, cari nomor, ketik pesan — di SETIAP
     * penawaran. Nomornya dinormalkan ke format internasional karena
     * wa.me menolak "0812...".
     */
    public function tautanWa(array $baris): ?string
    {
        $angka = preg_replace('/\D/', '', (string) ($baris['whatsapp'] ?? ''));

        if (strlen($angka) < 8) {
            return null;
        }

        if (str_starts_with($angka, '0')) {
            $angka = '62'.ltrim($angka, '0');
        }

        $pesan = 'Halo'.($baris['nama_pelanggan'] ? ' '.$baris['nama_pelanggan'] : '')
            .', berikut penawaran '.($baris['judul'] ?: 'perjalanan').' ('.$baris['kode'].'). '
            .'Harga '.$baris['harga_per_orang_teks'].' per orang untuk '.$baris['jumlah_peserta'].' peserta. '
            .'Berkas rinciannya menyusul ya.';

        return 'https://wa.me/'.$angka.'?text='.rawurlencode($pesan);
    }

    public function bukaSalin(int $id, array $baris): void
    {
        $this->salinDari = $id;
        $this->salinIsian = [
            'nama_pelanggan' => '',
            'whatsapp' => '',
            'tanggal_mulai' => '',
        ];
        $this->resetValidation('salinIsian.nama_pelanggan');
    }

    public function tutupSalin(): void
    {
        $this->salinDari = null;
        $this->salinIsian = [];
    }

    /** Menyalin penawaran; yang dikosongkan ikut nilai aslinya. */
    public function salin(): void
    {
        if ($this->salinDari === null) {
            return;
        }

        try {
            $balasan = $this->orcha()->kirim("/rab/{$this->salinDari}/salin", array_filter([
                'nama_pelanggan' => trim((string) ($this->salinIsian['nama_pelanggan'] ?? '')) ?: null,
                'whatsapp' => trim((string) ($this->salinIsian['whatsapp'] ?? '')) ?: null,
                'tanggal_mulai' => ($this->salinIsian['tanggal_mulai'] ?? '') ?: null,
            ], fn ($n) => $n !== null));

            $this->tutupSalin();
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Penawaran disalin.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->addError('salinIsian.nama_pelanggan', $e->getMessage());
        }
    }

    /** Versi yang pernah dikirim ke pelanggan. */
    public function bukaVersi(int $id): void
    {
        try {
            $balasan = $this->orcha()->ambil("/rab/{$id}/versi");

            $this->versiUntuk = $id;
            $this->versi = $balasan['data'] ?? [];
            $this->versiMeta = $balasan['meta'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function tutupVersi(): void
    {
        $this->versiUntuk = null;
        $this->versi = [];
        $this->versiMeta = [];
    }

    public function render()
    {
        $hasil = $this->muat('/rab', $this->parameterDaftar([
            'provinsi' => $this->filterProvinsi,
            'jenis' => $this->filterJenis,
            'kedaluwarsa' => $this->filterKedaluwarsa ? 1 : '',
            'urut' => $this->urut,
        ]));

        return view('livewire.pages.admin.orcha.rab.daftar', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            // Dihitung Orcha dari SELURUH tabel, bukan halaman ini.
            'ringkas' => $hasil['meta']['ringkas'] ?? [],
            'status' => $hasil['meta']['kosakata']['status'] ?? [],
            // Dipakai saringan DAN formulir tambah, jadi selalu dimuat —
            // tetapi lewat simpanan, bukan satu panggilan tiap gambar ulang.
            'provinsi' => $this->muatKatalog()['provinsi'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }

    /** Provinsi & destinasi; disimpan sebentar karena jarang berubah. */
    private function muatKatalog(): array
    {
        $simpan = cache()->get('orcha.rab.katalog');

        if (is_array($simpan) && $simpan !== []) {
            return $simpan;
        }

        try {
            $data = $this->orcha()->ambil('/rab/katalog')['data'] ?? [];
        } catch (OrchaTidakTerjangkau) {
            return [];
        }

        if ($data !== []) {
            cache()->put('orcha.rab.katalog', $data, now()->addMinutes(10));
        }

        return $data;
    }
}
