<?php

namespace App\Livewire\Pages\Admin\Orcha;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Tambah / ubah paket wisata Orcha dari lemon.
 *
 * Datanya tidak disimpan di sini — dikirim ke server Orcha lewat API, termasuk
 * fotonya. Paket yang sudah punya pendaftar ditolak Orcha bila dihapus.
 *
 * Tiga isian terberat (destinasi, fasilitas, itinerary) sengaja dibuat berupa
 * pilihan dan baris yang bisa ditambah, bukan kotak teks bebas: admin mengisi
 * paket berkali-kali dengan isi yang mirip, dan mengetik ulang itu sumber salah
 * ketik.
 */
class OrchaPaketForm extends Component
{
    use MemanggilOrcha, WithFileUploads;

    public ?int $paketId = null;

    public bool $ubah = false;

    public string $nama = '';

    public string $kategori = 'open_trip';

    public string $durasi = '';

    public string $tanggalBerangkat = '';

    public string $tanggalPulang = '';

    public string $titikJemput = '';

    public int $minimalPeserta = 6;

    public string $catatanPromo = '';

    public $harga = 0;

    public $hargaAsli = 0;

    public $diskonPersen = 0;

    public bool $pilihanTerbaik = false;

    /** @var array<int, string> */
    public array $destinasi = [];

    /** @var array<int, string> */
    public array $fasilitas = [];

    /** Isian tambahan untuk yang belum ada di daftar pilihan. */
    public string $destinasiBaru = '';

    public string $fasilitasBaru = '';

    /**
     * Itinerary sebagai baris yang bisa ditambah:
     * [['nama' => 'Day 1', 'agenda' => [['jam' => '07.00', 'kegiatan' => '...']]]]
     *
     * @var array<int, array{nama: string, agenda: array<int, array{jam: string, kegiatan: string}>}>
     */
    public array $hari = [];

    public $gambar;

    public ?string $gambarLama = null;

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string',
            'durasi' => 'nullable|string|max:60',
            'tanggalBerangkat' => 'nullable|date',
            'tanggalPulang' => 'nullable|date|after_or_equal:tanggalBerangkat',
            'titikJemput' => 'nullable|string|max:191',
            'minimalPeserta' => 'required|integer|min:1|max:200',
            'catatanPromo' => 'nullable|string|max:191',
            'harga' => 'required|numeric|min:0',
            'hargaAsli' => 'nullable|numeric|min:0',
            'diskonPersen' => 'nullable|numeric|min:0|max:100',
            'gambar' => 'nullable|image|max:4096',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'nama paket',
            'minimalPeserta' => 'minimal peserta',
            'tanggalBerangkat' => 'tanggal berangkat',
            'tanggalPulang' => 'tanggal pulang',
            'hargaAsli' => 'harga asli',
            'diskonPersen' => 'diskon',
        ];
    }

    public function mount(?int $paket = null): void
    {
        if (! $paket) {
            $this->hari = [$this->hariKosong(1)];

            return;
        }

        $this->paketId = $paket;
        $this->ubah = true;

        $isi = $this->muat("/paket-wisata/{$paket}")['data'] ?? [];

        if ($isi === []) {
            $this->hari = [$this->hariKosong(1)];

            return;
        }

        $this->nama = $isi['nama'] ?? '';
        $this->kategori = $isi['kategori'] ?? 'open_trip';
        $this->durasi = (string) ($isi['durasi'] ?? '');
        $this->tanggalBerangkat = (string) ($isi['tanggal_berangkat'] ?? '');
        $this->tanggalPulang = (string) ($isi['tanggal_pulang'] ?? '');
        $this->titikJemput = (string) ($isi['titik_jemput'] ?? '');
        $this->minimalPeserta = (int) ($isi['minimal_peserta'] ?? 6);
        $this->catatanPromo = (string) ($isi['catatan_promo'] ?? '');
        $this->harga = $isi['harga'] ?? 0;
        $this->hargaAsli = $isi['harga_asli'] ?? 0;
        $this->diskonPersen = $isi['diskon_persen'] ?? 0;
        $this->pilihanTerbaik = (bool) ($isi['pilihan_terbaik'] ?? false);
        $this->destinasi = array_values($isi['destinasi'] ?? []);
        $this->fasilitas = array_values($isi['fasilitas'] ?? []);
        $this->gambarLama = $isi['sampul'] ?? null;

        $this->hari = $this->dariItinerary($isi['itinerary'] ?? []);
    }

    /* ------------------------- DESTINASI & FASILITAS ------------------------- */

    /** Klik pada saran: masuk kalau belum ada, keluar kalau sudah. */
    public function jungkitDestinasi(string $nama): void
    {
        $this->destinasi = in_array($nama, $this->destinasi, true)
            ? array_values(array_diff($this->destinasi, [$nama]))
            : [...$this->destinasi, $nama];
    }

    public function jungkitFasilitas(string $nama): void
    {
        $this->fasilitas = in_array($nama, $this->fasilitas, true)
            ? array_values(array_diff($this->fasilitas, [$nama]))
            : [...$this->fasilitas, $nama];
    }

    public function tambahDestinasi(): void
    {
        $nama = trim($this->destinasiBaru);

        if ($nama !== '' && ! in_array($nama, $this->destinasi, true)) {
            $this->destinasi[] = $nama;
        }

        $this->destinasiBaru = '';
    }

    public function tambahFasilitas(): void
    {
        $nama = trim($this->fasilitasBaru);

        if ($nama !== '' && ! in_array($nama, $this->fasilitas, true)) {
            $this->fasilitas[] = $nama;
        }

        $this->fasilitasBaru = '';
    }

    public function buangDestinasi(int $urutan): void
    {
        unset($this->destinasi[$urutan]);
        $this->destinasi = array_values($this->destinasi);
    }

    public function buangFasilitas(int $urutan): void
    {
        unset($this->fasilitas[$urutan]);
        $this->fasilitas = array_values($this->fasilitas);
    }

    /* ------------------------------ ITINERARY ------------------------------ */

    public function tambahHari(): void
    {
        $this->hari[] = $this->hariKosong(count($this->hari) + 1);
    }

    public function buangHari(int $urutan): void
    {
        unset($this->hari[$urutan]);
        $this->hari = array_values($this->hari);

        if ($this->hari === []) {
            $this->hari = [$this->hariKosong(1)];
        }
    }

    public function tambahAgenda(int $urutanHari): void
    {
        $this->hari[$urutanHari]['agenda'][] = ['jam' => '', 'kegiatan' => ''];
    }

    public function buangAgenda(int $urutanHari, int $urutanAgenda): void
    {
        unset($this->hari[$urutanHari]['agenda'][$urutanAgenda]);
        $this->hari[$urutanHari]['agenda'] = array_values($this->hari[$urutanHari]['agenda']);

        if ($this->hari[$urutanHari]['agenda'] === []) {
            $this->hari[$urutanHari]['agenda'] = [['jam' => '', 'kegiatan' => '']];
        }
    }

    private function hariKosong(int $nomor): array
    {
        return ['nama' => "Day {$nomor}", 'agenda' => [['jam' => '', 'kegiatan' => '']]];
    }

    /** Bentuk dari API ('hari' + 'agenda') jadi bentuk isian di layar. */
    private function dariItinerary(array $itinerary): array
    {
        $hasil = [];

        foreach ($itinerary as $nomor => $satuHari) {
            $agenda = [];

            foreach ($satuHari['agenda'] ?? [] as $baris) {
                $agenda[] = ['jam' => $baris['jam'] ?? '', 'kegiatan' => $baris['kegiatan'] ?? ''];
            }

            $hasil[] = [
                'nama' => $satuHari['hari'] ?? ('Day '.($nomor + 1)),
                'agenda' => $agenda ?: [['jam' => '', 'kegiatan' => '']],
            ];
        }

        return $hasil ?: [$this->hariKosong(1)];
    }

    /**
     * Kembali ke format teks yang dipahami Orcha. Hari tanpa agenda terisi
     * dibuang supaya baris kosong tidak ikut tersimpan.
     */
    private function keTeksItinerary(): string
    {
        $baris = [];

        foreach ($this->hari as $satuHari) {
            $agenda = collect($satuHari['agenda'] ?? [])
                ->filter(fn ($item) => trim($item['kegiatan'] ?? '') !== '');

            if ($agenda->isEmpty()) {
                continue;
            }

            $baris[] = trim($satuHari['nama'] ?? '') ?: 'Hari '.(count($baris) + 1);

            foreach ($agenda as $item) {
                $baris[] = trim($item['jam'] ?? '').' | '.trim($item['kegiatan']);
            }

            $baris[] = '';
        }

        return trim(implode("\n", $baris));
    }

    /* -------------------------------- SIMPAN -------------------------------- */

    public function simpan(): void
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'kategori' => $this->kategori,
            'durasi' => $this->durasi,
            'tanggal_berangkat' => $this->tanggalBerangkat,
            'tanggal_pulang' => $this->tanggalPulang,
            'titik_jemput' => $this->titikJemput,
            'minimal_peserta' => $this->minimalPeserta,
            'catatan_promo' => $this->catatanPromo,
            'harga' => $this->harga,
            'harga_asli' => $this->hargaAsli ?: $this->harga,
            'diskon_persen' => $this->diskonPersen ?: 0,
            'pilihan_terbaik' => $this->pilihanTerbaik,
            'destinasi' => array_values($this->destinasi),
            'fasilitas' => array_values($this->fasilitas),
            'itinerary_teks' => $this->keTeksItinerary(),
        ];

        $berhasil = $this->ubah
            ? $this->kirimData("/paket-wisata/{$this->paketId}", $data, 'Paket wisata diperbarui.', $this->gambar)
            : $this->kirimData('/paket-wisata', $data, 'Paket wisata ditambahkan.', $this->gambar);

        if ($berhasil) {
            $this->redirectRoute('admin.orcha.paket', navigate: true);
        }
    }

    public function render()
    {
        // Saran destinasi diambil dari daftar destinasi populer yang sudah ada
        // di Orcha — jadi penamaannya seragam antar paket.
        $saranDestinasi = collect($this->muat('/destinasi')['data'] ?? [])
            ->pluck('nama')
            ->filter()
            ->values()
            ->all();

        return view('livewire.pages.admin.orcha.orcha-paket-form', [
            'pilihanKategori' => $this->rujukan('kategori_paket'),
            'saranDestinasi' => $saranDestinasi,
            'saranFasilitas' => $this->rujukan('fasilitas_umum'),
        ])->layout('livewire.layout.templateindex');
    }
}
