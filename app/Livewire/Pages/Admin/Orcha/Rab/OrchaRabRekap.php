<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Rekap pengeluaran sesudah trip jalan.
 *
 * Menjawab dua hal yang tidak bisa dijawab RAB sendirian: trip ini
 * sebenarnya untung berapa, dan uang mana yang masih menggantung.
 *
 * TIGA angka per baris — RAB, tagihan vendor, yang dibayar — dan angka
 * ketiga itu yang membuat seluruhnya berguna. Tanpa tagihan dicatat
 * terpisah, hotel yang lupa dilunasi terlihat persis seperti hotel yang
 * memberi cashback: keduanya "RAB dikurangi dibayar = 1 juta".
 */
class OrchaRabRekap extends Component
{
    use IsianRupiah;
    use MemanggilOrcha;
    use WithFileUploads;

    public int $rabId;

    /** Tagihan per baris biaya, sebagai teks rupiah. @var array<int, string> */
    public array $tagihan = [];

    /** PIC per baris biaya. @var array<int, string> */
    public array $pic = [];

    public bool $terisi = false;

    /** Baris biaya yang panel "tambah pembayaran"-nya sedang terbuka. */
    public ?int $bayarUntuk = null;

    public array $bayar = ['jenis' => 'pelunasan', 'nominal' => '', 'tanggal' => '', 'catatan' => ''];

    public $bukti;

    /** Pembayaran yang buktinya sedang diganti. */
    public ?int $gantiUntuk = null;

    public $buktiBaru;

    public function mount(int $rab): void
    {
        $this->rabId = $rab;
    }

    /** Tagihan ditampilkan bertitik begitu kotaknya ditinggalkan. */
    public function updatedTagihan($nilai, $kunci): void
    {
        if ($kunci === null) {
            return;
        }

        $this->tagihan[$kunci] = $this->adaAngka($nilai)
            ? number_format($this->angkaDari((string) $nilai), 0, ',', '.')
            : '';
    }

    public function updatedBayarNominal(): void
    {
        $this->bayar['nominal'] = $this->keRupiah($this->angkaDari($this->bayar['nominal']));
    }

    /**
     * Apakah ketikan ini BERISI angka — bukan sekadar bukan-kosong.
     *
     * Pembeda antara "kosongkan rekapnya" dan "tagihannya nol". Nol adalah
     * tagihan yang sah: destinasi yang ternyata gratis. Memakai
     * `(int) $teks` untuk membedakannya mustahil — "Rp " dan "0" sama-sama
     * jadi 0.
     */
    private function adaAngka($teks): bool
    {
        return preg_match('/\d/', (string) $teks) === 1;
    }

    public function simpanTagihan(int $biayaId): void
    {
        $angka = $this->tagihan[$biayaId] ?? '';

        $this->kirim("/rab/{$this->rabId}/rekap/biaya/{$biayaId}", [
            // null mengembalikannya ke "belum direkap"; nol tetap nol.
            'tagihan' => $this->adaAngka($angka) ? $this->angkaDari((string) $angka) : null,
            'pic' => trim((string) ($this->pic[$biayaId] ?? '')) ?: null,
        ]);
    }

    public function bukaBayar(int $biayaId): void
    {
        $this->bayarUntuk = $biayaId;
        $this->gantiUntuk = null;
        $this->bayar = ['jenis' => 'pelunasan', 'nominal' => '', 'tanggal' => '', 'catatan' => ''];
        $this->bukti = null;
        $this->resetValidation();
    }

    public function tutupBayar(): void
    {
        $this->bayarUntuk = null;
        $this->gantiUntuk = null;
        $this->bukti = null;
        $this->buktiBaru = null;
        $this->resetValidation();
    }

    public function simpanBayar(): void
    {
        $this->validate([
            'bayar.jenis' => 'required|string',
            'bayar.nominal' => 'required|string',
            'bayar.tanggal' => 'nullable|date',
            'bayar.catatan' => 'nullable|string|max:300',
            // Opsional: pembayaran yang sudah terjadi tetap harus bisa
            // dicatat walau fotonya masih di ponsel orang lain.
            'bukti' => 'nullable|image|max:4096',
        ], [
            'bayar.nominal.required' => 'Isi nominalnya dulu.',
            'bukti.image' => 'Buktinya harus berupa gambar.',
        ], [
            'bayar.nominal' => 'nominal', 'bukti' => 'bukti transfer',
        ]);

        $nominal = $this->angkaDari($this->bayar['nominal']);

        if ($nominal < 1) {
            $this->addError('bayar.nominal', 'Nominalnya belum terbaca sebagai angka.');

            return;
        }

        $terkirim = $this->kirimBerkas(
            "/rab/{$this->rabId}/rekap/biaya/{$this->bayarUntuk}/bayar",
            [
                'jenis' => $this->bayar['jenis'],
                'nominal' => $nominal,
                'tanggal' => $this->bayar['tanggal'] ?: null,
                'catatan' => trim((string) $this->bayar['catatan']) ?: null,
            ],
            $this->bukti,
        );

        if ($terkirim) {
            $this->tutupBayar();
        }
    }

    public function bukaGanti(int $bayarId): void
    {
        $this->gantiUntuk = $bayarId;
        $this->bayarUntuk = null;
        $this->buktiBaru = null;
        $this->resetValidation();
    }

    public function simpanGantiBukti(): void
    {
        $this->validate(['buktiBaru' => 'required|image|max:4096'], [
            'buktiBaru.required' => 'Pilih berkas buktinya dulu.',
        ], ['buktiBaru' => 'bukti transfer']);

        if ($this->kirimBerkas("/rab/{$this->rabId}/rekap/bayar/{$this->gantiUntuk}/bukti", [], $this->buktiBaru)) {
            $this->tutupBayar();
        }
    }

    public function hapusBayar(int $bayarId): void
    {
        try {
            $balasan = $this->orcha()->hapus("/rab/{$this->rabId}/rekap/bayar/{$bayarId}");
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Pembayaran dihapus.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Menandai trip selesai supaya rekapnya bisa dimulai.
     *
     * Dari layar ini, bukan hanya dari layar RAB: yang membuka rekap dan
     * yang menemukan bahwa statusnya belum selesai adalah orang yang sama,
     * pada detik yang sama.
     */
    public function tandaiSelesai(): void
    {
        $this->kirimPerubahan("/rab/{$this->rabId}", ['status' => 'selesai'],
            'Trip ditandai selesai. Rekap pengeluarannya sudah bisa diisi.');
    }

    /** Satu jalur kirim untuk perubahan tanpa berkas. */
    private function kirim(string $jalur, array $data): void
    {
        try {
            $balasan = $this->orcha()->kirim($jalur, $data);
            $this->terisi = false;
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Tersimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Kirim dengan berkas, dengan nama medan 'bukti'.
     *
     * Bukan 'gambar' seperti layar lain: Orcha memvalidasi medan bernama
     * `bukti`, dan berkas yang sampai dengan nama lain membuat validasinya
     * berkata "bukti harus berupa gambar" untuk berkas yang sebenarnya ada.
     */
    private function kirimBerkas(string $jalur, array $data, $berkas): bool
    {
        try {
            $balasan = $this->orcha()->kirim($jalur, $data, $berkas, [], 'bukti');
            $this->terisi = false;
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Tersimpan.');

            return true;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return false;
        }
    }

    public function render()
    {
        $data = $this->muat("/rab/{$this->rabId}/rekap")['data'] ?? [];
        $baris = $data['baris'] ?? [];

        /*
         | Kotak diisi SEKALI, bukan tiap render.
         |
         | Tiap render menimpanya berarti angka yang baru diketik hilang pada
         | render berikutnya — dan render berikutnya datang dari mengetik di
         | kotak lain. Borang yang menghapus isinya sendiri sambil diisi.
         */
        if (! $this->terisi) {
            foreach ($baris as $b) {
                $this->tagihan[$b['biaya_id']] = $b['tagihan'] === null
                    ? '' : number_format((int) $b['tagihan'], 0, ',', '.');
                $this->pic[$b['biaya_id']] = (string) ($b['pic'] ?? '');
            }
            $this->terisi = true;
        }

        return view('livewire.pages.admin.orcha.rab.rekap', [
            'rekap' => $data,
            'baris' => $baris,
            'rab' => $data['rab'] ?? null,
            'jenisBayar' => $data['jenis_bayar'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
