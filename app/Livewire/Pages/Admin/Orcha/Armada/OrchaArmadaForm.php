<?php

namespace App\Livewire\Pages\Admin\Orcha\Armada;

use App\Exceptions\OrchaTidakTerjangkau;
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

    public string $jenis = 'mobil';

    public string $nopol = '';

    public int $kapasitas = 7;

    /**
     * Kapasitas sudah diubah admin sendiri.
     *
     * Sekali diubah, saran dari katalog tidak menimpanya lagi. Tanpa penanda ini,
     * admin yang mengoreksi jumlah kursi lalu mengganti nama unit akan kehilangan
     * koreksinya tanpa tahu kenapa.
     */
    public bool $kapasitasDiubahManual = false;

    /** Unit yang menjadi sumber angka kapasitas, untuk keterangan di layar. */
    public string $kursiOtomatisDari = '';

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
     * Merek di luar katalog mengembalikan daftar kosong — dan itu benar: yang
     * tersisa untuk unit semacam itu memang hanya menulis sendiri.
     *
     * @return list<string>
     */
    public function modelPilihan(): array
    {
        return $this->katalog()[$this->merek] ?? [];
    }

    /**
     * Sumber angka untuk mengisi kapasitas otomatis.
     *
     * @return array<string, array<string, int>>
     */
    public function kapasitasKatalog(): array
    {
        return $this->rujukan('kapasitas_kendaraan');
    }

    /**
     * Kursi yang disarankan untuk merek + nama unit yang sedang dipilih.
     */
    public function kursiDisarankan(): ?int
    {
        $kursi = $this->kapasitasKatalog()[trim($this->merek)][trim($this->nama)] ?? null;

        return is_numeric($kursi) && (int) $kursi > 0 ? (int) $kursi : null;
    }

    /**
     * Memilih nama unit mengisi kapasitas — tetap bisa diubah admin.
     *
     * Semi otomatis, bukan otomatis: unit yang sama bisa dipasangi kursi berbeda
     * (Gran Max niaga vs minibus, HiAce yang kursinya dicabut), jadi angkanya
     * saran dan bukan keputusan. Yang sudah diubah admin tidak ditimpa lagi.
     *
     * Model yang kursinya belum dipastikan tidak mengubah apa pun: lebih baik
     * isiannya dibiarkan daripada diisi angka yang belum tentu benar, karena
     * angka yang sudah tertulis cenderung tidak diperiksa lagi.
     */
    public function updatedNama(): void
    {
        $this->nama = trim($this->nama);
        $this->isiKursiOtomatis();
    }

    private function isiKursiOtomatis(): void
    {
        $this->kursiOtomatisDari = '';

        if ($this->kapasitasDiubahManual) {
            return;
        }

        $kursi = $this->kursiDisarankan();

        if ($kursi === null) {
            return;
        }

        $this->kapasitas = $kursi;
        $this->kursiOtomatisDari = trim($this->merek.' '.$this->nama);
    }

    /**
     * Admin mengubah kapasitas sendiri: saran tidak boleh menimpanya lagi.
     */
    public function updatedKapasitas(): void
    {
        $this->kapasitasDiubahManual = true;
        $this->kursiOtomatisDari = '';
    }

    /**
     * @return list<array{id: int, merek: string, model: string|null}>
     */
    public function katalogKustom(): array
    {
        return $this->rujukan('katalog_kustom');
    }

    /**
     * Mendaftarkan merek atau nama unit yang ditulis admin sendiri.
     *
     * Sebelum ini nilai manual hanya bertahan bila unitnya ikut tersimpan, jadi
     * merek yang sama harus ditulis ulang untuk setiap unit sejenis — dan salah
     * tulis tidak bisa dibetulkan.
     *
     * Gagal mendaftar TIDAK membatalkan pemakaiannya untuk unit ini: nilainya
     * tetap dipakai, hanya belum masuk daftar. Menghalangi admin menyimpan unit
     * karena katalognya gagal diperbarui adalah menukar masalah kecil dengan
     * masalah besar.
     */
    public function tambahKatalog(string $nilai, bool $untukUnit = false): void
    {
        $nilai = trim(preg_replace('/\s+/', ' ', $nilai));

        if ($nilai === '') {
            return;
        }

        if ($untukUnit) {
            $this->nama = $nilai;
        } else {
            $this->merek = $nilai;
            // Merek berganti, jadi nama unit sebelumnya tidak lagi berlaku.
            $this->nama = '';
        }

        // Nama unit tanpa merek tidak bisa didaftarkan: entri modelnya harus
        // menempel pada mereknya.
        if ($untukUnit && trim($this->merek) === '') {
            return;
        }

        $this->simpanKatalog($untukUnit
            ? ['merek' => trim($this->merek), 'model' => $nilai]
            : ['merek' => $nilai]);

        if ($untukUnit) {
            $this->isiKursiOtomatis();
        }
    }

    public function hapusKatalog(int $id): void
    {
        try {
            $this->orcha()->hapus("/katalog-kendaraan/{$id}");
            $this->segarkanKatalog();
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    private function simpanKatalog(array $data): void
    {
        try {
            $this->orcha()->kirim('/katalog-kendaraan', $data);
            $this->segarkanKatalog();
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Membuang simpanan rujukan lalu mengirim daftar terbaru ke peramban.
     *
     * Rujukan disimpan 10 menit. Tanpa dibuang, merek yang baru didaftarkan tidak
     * akan muncul di daftar pilihan sampai simpanannya kedaluwarsa — terlihat
     * seperti penambahannya gagal padahal tersimpan.
     */
    private function segarkanKatalog(): void
    {
        cache()->forget('orcha.rujukan');

        $this->dispatch('orcha-katalog-segar',
            katalog: $this->katalog(),
            kustom: $this->katalogKustom());
    }

    /**
     * Mengganti merek mengosongkan nama unit.
     *
     * Model melekat pada mereknya. Tanpa pengosongan ini, memilih Toyota lalu
     * "Avanza" lalu berpindah ke Suzuki akan menyimpan "Suzuki Avanza" — unit
     * yang tidak pernah ada.
     */
    public function updatedMerek(): void
    {
        $this->merek = trim($this->merek);
        $this->nama = '';
        // Unitnya berganti, jadi koreksi kursi untuk unit sebelumnya tidak lagi
        // berlaku dan saran boleh mengisi lagi.
        $this->kapasitasDiubahManual = false;
        $this->kursiOtomatisDari = '';
        $this->resetValidation(['merek', 'nama']);
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
        $this->jenis = $isi['jenis'] ?? 'mobil';
        $this->nopol = (string) ($isi['nopol'] ?? '');
        $this->kapasitas = (int) ($isi['kapasitas'] ?? 7);
        // Unit yang sudah ada memakai kapasitas tersimpannya. Menimpanya dengan
        // saran katalog akan mengubah data yang benar tanpa diminta.
        $this->kapasitasDiubahManual = true;
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
            'katalogKustom' => $this->katalogKustom(),
            'kursiDisarankan' => $this->kursiDisarankan(),
            'modelPilihan' => $this->modelPilihan(),
        ])->layout('livewire.layout.templateindex');
    }
}
