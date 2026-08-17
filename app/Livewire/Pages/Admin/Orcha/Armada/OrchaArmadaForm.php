<?php

namespace App\Livewire\Pages\Admin\Orcha\Armada;

use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Tambah / ubah unit armada Orcha dari lemon.
 *
 * Tarif per jam dan per 12 jam boleh kosong — bus memang tidak dilepas per jam,
 * dan Orcha memperlakukan kosong sebagai "tidak dijual per satuan itu".
 */
class OrchaArmadaForm extends Component
{
    use IsianRupiah, MemanggilOrcha, WithFileUploads;

    public ?int $kendaraanId = null;

    public bool $ubah = false;

    public string $nama = '';

    public string $merek = '';

    /**
     * Nilai yang menandai "isi manual" pada dropdown merek dan nama unit.
     *
     * Bukan string kosong, karena kosong sudah berarti "belum memilih" — dua
     * keadaan yang harus bisa dibedakan.
     */
    public const MANUAL = '__manual__';

    /**
     * Isi dropdown merek dan nama unit.
     *
     * $merek dan $nama tetap satu-satunya nilai yang dikirim ke Orcha dan
     * divalidasi; keempat properti di bawah ini hanya keadaan tampilan, dan
     * diringkas menjadi keduanya di serapPilihan() tepat sebelum disimpan.
     * Menyimpan nilai akhirnya di satu tempat mencegah keadaan yang saling
     * bertentangan — dropdown menunjuk Toyota sementara yang tersimpan Honda.
     */
    public string $merekPilihan = '';

    public string $merekManual = '';

    public string $namaPilihan = '';

    public string $namaManual = '';

    public string $jenis = 'mobil';

    public string $nopol = '';

    public int $kapasitas = 7;

    public array $transmisi = ['Manual'];

    public $tarifHari = 0;

    public $tarifJam = '';

    public $tarif12Jam = '';

    public $tarifSopir = '';

    /** Bentuk bertitik yang tampil di layar. */
    public string $tarifHariTeks = '';

    public string $tarifJamTeks = '';

    public string $tarif12JamTeks = '';

    public string $tarifSopirTeks = '';

    public bool $tersedia = true;

    public $gambar;

    public ?string $gambarLama = null;

    /**
     * Kondisi fisik dan jadwal pakai unit, hanya untuk dibaca.
     *
     * Ditampilkan saat mengubah unit karena keduanya menentukan apakah
     * perubahan yang sedang dikerjakan aman: menonaktifkan unit yang sedang
     * disewa, atau menaikkan tarif unit yang kacanya masih retak, adalah dua
     * hal yang lebih baik disadari sebelum tombol simpan ditekan.
     *
     * Tidak bisa disunting di sini — kondisi hanya berubah lewat serah terima.
     */
    public ?array $kondisi = null;

    public array $jadwal = [];

    /** @var array<string, string> bagian => kondisi, yang sedang tampil di isian */
    public array $kondisiIsian = [];

    public string $kondisiCatatan = '';

    /** Berapa kali unit ini pernah disewa — untuk kartu ringkasan. */
    public int $jumlahPenyewaan = 0;

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'merek' => 'required|string|max:100',
            'jenis' => 'required|string',
            'nopol' => 'nullable|string|max:20',
            'kapasitas' => 'required|integer|min:1|max:80',
            'transmisi' => 'required|array|min:1',
            'tarifHari' => 'required|numeric|min:0',
            'tarifJam' => 'nullable|numeric|min:0',
            'tarif12Jam' => 'nullable|numeric|min:0',
            'tarifSopir' => 'nullable|numeric|min:0',
            'gambar' => 'nullable|image|max:4096',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'nama unit',
            'merek' => 'merek',
            'transmisi' => 'transmisi',
            'tarifHari' => 'tarif per hari',
            'tarifJam' => 'tarif per jam',
            'tarif12Jam' => 'tarif paket 12 jam',
            'tarifSopir' => 'tarif sopir',
        ];
    }

    public function updatedTarifHariTeks(): void
    {
        $this->tarifHari = $this->angkaDari($this->tarifHariTeks);
        $this->tarifHariTeks = $this->keRupiah($this->tarifHari);
    }

    public function updatedTarifJamTeks(): void
    {
        $this->tarifJam = $this->angkaDari($this->tarifJamTeks) ?: '';
        $this->tarifJamTeks = $this->keRupiah($this->tarifJam);
    }

    public function updatedTarif12JamTeks(): void
    {
        $this->tarif12Jam = $this->angkaDari($this->tarif12JamTeks) ?: '';
        $this->tarif12JamTeks = $this->keRupiah($this->tarif12Jam);
    }

    public function updatedTarifSopirTeks(): void
    {
        $this->tarifSopir = $this->angkaDari($this->tarifSopirTeks) ?: '';
        $this->tarifSopirTeks = $this->keRupiah($this->tarifSopir);
    }

    /**
     * @return array<string, list<string>> merek => daftar model
     */
    public function katalog(): array
    {
        return $this->rujukan('katalog_kendaraan');
    }

    /**
     * Model yang tersedia untuk merek yang sedang dipilih.
     *
     * @return list<string>
     */
    public function modelPilihan(): array
    {
        if ($this->merekPilihan === '' || $this->merekPilihan === self::MANUAL) {
            return [];
        }

        return $this->katalog()[$this->merekPilihan] ?? [];
    }

    /**
     * Mengganti merek mengosongkan pilihan nama unit.
     *
     * Model melekat pada mereknya. Tanpa pengosongan ini, memilih Toyota lalu
     * "Avanza" lalu berpindah ke Suzuki akan menyimpan "Suzuki Avanza" — unit
     * yang tidak pernah ada.
     */
    public function updatedMerekPilihan(): void
    {
        $this->namaPilihan = $this->merekPilihan === self::MANUAL ? self::MANUAL : '';
        $this->namaManual = '';
        $this->nama = '';
        $this->resetValidation(['merek', 'nama']);
    }

    /**
     * Meringkas pilihan dropdown menjadi nilai yang benar-benar disimpan.
     */
    private function serapPilihan(): void
    {
        $this->merek = $this->merekPilihan === self::MANUAL
            ? trim($this->merekManual)
            : trim($this->merekPilihan);

        $this->nama = $this->namaPilihan === self::MANUAL
            ? trim($this->namaManual)
            : trim($this->namaPilihan);
    }

    /**
     * Menyiapkan dropdown dari nilai yang sudah tersimpan.
     *
     * Merek atau model yang tidak ada di katalog TIDAK dibuang: dropdown-nya
     * dialihkan ke "isi manual" dengan nilai lamanya utuh di kotak teks. Unit
     * yang sudah benar tidak boleh berubah hanya karena katalognya belum
     * memuat namanya.
     */
    private function pasangPilihan(string $merek, string $nama): void
    {
        $katalog = $this->katalog();

        $merekDikenal = $merek !== '' && array_key_exists($merek, $katalog);
        $this->merekPilihan = $merek === '' ? '' : ($merekDikenal ? $merek : self::MANUAL);
        $this->merekManual = $merekDikenal ? '' : $merek;

        $daftarModel = $merekDikenal ? $katalog[$merek] : [];
        $namaDikenal = $nama !== '' && in_array($nama, $daftarModel, true);
        $this->namaPilihan = $nama === '' ? '' : ($namaDikenal ? $nama : self::MANUAL);
        $this->namaManual = $namaDikenal ? '' : $nama;
    }

    public function mount(?int $kendaraan = null): void
    {
        if (! $kendaraan) {
            return;
        }

        $this->kendaraanId = $kendaraan;
        $this->ubah = true;

        $isi = $this->muat("/kendaraan/{$kendaraan}")['data'] ?? [];

        if ($isi === []) {
            return;
        }

        $this->kondisi = $isi['kondisi'] ?? null;
        $this->jadwal = $isi['jadwal'] ?? [];
        $this->kondisiIsian = (array) ($isi['kondisi_terkini'] ?? []);
        $this->jumlahPenyewaan = (int) ($isi['jumlah_penyewaan'] ?? 0);
        $this->kondisiCatatan = (string) ($isi['kondisi']['catatan'] ?? '');

        $this->nama = $isi['nama'] ?? '';
        $this->merek = $isi['merek'] ?? '';
        $this->pasangPilihan($this->merek, $this->nama);
        $this->jenis = $isi['jenis'] ?? 'mobil';
        $this->nopol = (string) ($isi['nopol'] ?? '');
        $this->kapasitas = (int) ($isi['kapasitas'] ?? 7);
        $this->transmisi = $isi['transmisi_tersedia'] ?: ['Manual'];
        $this->tarifHari = $isi['tarif']['hari'] ?? 0;
        $this->tarifJam = $isi['tarif']['jam'] ?? '';
        $this->tarif12Jam = $isi['tarif']['12jam'] ?? '';
        $this->tarifSopir = $isi['tarif']['sopir_per_hari'] ?? '';
        $this->tersedia = (bool) ($isi['tersedia'] ?? true);
        $this->tarifHariTeks = $this->keRupiah($this->tarifHari);
        $this->tarifJamTeks = $this->keRupiah($this->tarifJam);
        $this->tarif12JamTeks = $this->keRupiah($this->tarif12Jam);
        $this->tarifSopirTeks = $this->keRupiah($this->tarifSopir);
        $this->gambarLama = $isi['gambar'] ?? null;
    }

    public function simpan(): void
    {
        $this->serapPilihan();
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'merek' => $this->merek,
            'jenis' => $this->jenis,
            'nopol' => $this->nopol,
            'kapasitas' => $this->kapasitas,
            'transmisi_tersedia' => array_values($this->transmisi),
            'tarif_hari' => $this->tarifHari,
            'tarif_jam' => $this->tarifJam ?: null,
            'tarif_12jam' => $this->tarif12Jam ?: null,
            'tarif_sopir' => $this->tarifSopir ?: null,
            'tersedia' => $this->tersedia,
        ];

        // Tujuan diteruskan supaya pemberitahuan sukses tampil utuh dulu di
        // halaman ini, baru berpindah ke daftar setelah popupnya menutup.
        $this->ubah
            ? $this->kirimData("/kendaraan/{$this->kendaraanId}", $data, 'Kendaraan diperbarui.', $this->gambar, route('admin.orcha.armada'))
            : $this->kirimData('/kendaraan', $data, 'Kendaraan ditambahkan.', $this->gambar, route('admin.orcha.armada'));
    }

    /**
     * Mencatat kondisi unit sesudah diperbaiki.
     *
     * Dikirim ke jalur tersendiri, bukan ikut tombol simpan utama: yang ini
     * mengubah keadaan fisik unit, sedangkan yang itu mengubah keterangan dan
     * tarifnya. Menggabungkan keduanya berarti mengubah tarif tanpa sengaja
     * ikut menyatakan unitnya sudah diperbaiki.
     *
     * Jejak kerusakan sebelumnya tidak hilang: denda dan rinciannya melekat
     * pada penyewaannya masing-masing, bukan pada unitnya.
     */
    public function simpanKondisi(): void
    {
        if (! $this->ubah) {
            return;
        }

        $berhasil = $this->kirimData(
            "/kendaraan/{$this->kendaraanId}/kondisi",
            ['kondisi' => $this->kondisiIsian, 'catatan' => $this->kondisiCatatan ?: null],
            'Kondisi unit tersimpan di Orcha.',
        );

        if ($berhasil) {
            $isi = $this->muat("/kendaraan/{$this->kendaraanId}")['data'] ?? [];
            $this->kondisi = $isi['kondisi'] ?? null;
            $this->kondisiIsian = (array) ($isi['kondisi_terkini'] ?? []);
        }
    }

    /**
     * Bagian yang sedang ditandai rusak atau hilang pada isian.
     *
     * Dibaca dari isian, bukan dari data tersimpan, supaya peringatan "siap
     * disewakan padahal rusak" ikut berubah begitu admin mengganti pilihannya —
     * tanpa menunggu disimpan lebih dulu.
     *
     * @return array<int, string>
     */
    public function bagianBermasalah(): array
    {
        $label = $this->rujukan('pemeriksaan_kendaraan');

        return collect($this->kondisiIsian)
            ->filter(fn ($nilai) => in_array($nilai, ['rusak', 'hilang'], true))
            ->map(fn ($nilai, $bagian) => $label[$bagian] ?? $bagian)
            ->values()
            ->all();
    }

    /** Semua bagian ditandai baik — jalan pintas sesudah perbaikan menyeluruh. */
    public function semuaBaik(): void
    {
        foreach (array_keys($this->rujukan('pemeriksaan_kendaraan')) as $bagian) {
            $this->kondisiIsian[$bagian] = 'baik';
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.armada.form', [
            'daftarBagian' => $this->rujukan('pemeriksaan_kendaraan'),
            'daftarKondisi' => $this->rujukan('kondisi_pemeriksaan'),
            'pilihanJenis' => $this->rujukan('jenis_kendaraan'),
            'katalog' => $this->katalog(),
            'modelPilihan' => $this->modelPilihan(),
        ])->layout('livewire.layout.templateindex');
    }
}
