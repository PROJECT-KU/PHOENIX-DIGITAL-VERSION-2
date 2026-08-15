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

    /** Satu baris satu destinasi — lebih enak diketik daripada larik dinamis. */
    public string $destinasiTeks = '';

    public string $fasilitasTeks = '';

    public string $itineraryTeks = '';

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
            return;
        }

        $this->paketId = $paket;
        $this->ubah = true;

        $isi = $this->muat("/paket-wisata/{$paket}")['data'] ?? [];

        if ($isi === []) {
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
        $this->destinasiTeks = implode("\n", $isi['destinasi'] ?? []);
        $this->fasilitasTeks = implode("\n", $isi['fasilitas'] ?? []);
        $this->itineraryTeks = (string) ($isi['itinerary_teks'] ?? '');
        $this->gambarLama = $isi['sampul'] ?? null;
    }

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
            'destinasi' => $this->keBaris($this->destinasiTeks),
            'fasilitas' => $this->keBaris($this->fasilitasTeks),
            'itinerary_teks' => $this->itineraryTeks,
        ];

        $berhasil = $this->ubah
            ? $this->kirimData("/paket-wisata/{$this->paketId}", $data, 'Paket wisata diperbarui.', $this->gambar)
            : $this->kirimData('/paket-wisata', $data, 'Paket wisata ditambahkan.', $this->gambar);

        if ($berhasil) {
            $this->redirectRoute('admin.orcha.paket', navigate: true);
        }
    }

    /** Buang baris kosong dan spasi berlebih. */
    private function keBaris(string $teks): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $teks))
            ->map(fn ($baris) => trim($baris))
            ->filter()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.orcha-paket-form', [
            'pilihanKategori' => $this->rujukan('kategori_paket'),
        ])->layout('livewire.layout.templateindex');
    }
}
