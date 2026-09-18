<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Penyusun RAB: itinerary di kiri, angka di kanan, keduanya bergerak bersama.
 *
 * Setiap klik langsung tersimpan ke Orcha dan layar digambar ulang dari
 * jawabannya — bukan dari hitungan di sini. Hitungan RAB hanya ada di satu
 * tempat (HitungRab di Orcha); menghitung ulang di lemon berarti suatu hari
 * layar dan PDF menyebut harga yang berbeda.
 */
class OrchaRabSusun extends Component
{
    use IsianRupiah;
    use MemanggilOrcha;

    public int $rabId;

    /**
     * Bentuk RAB terakhir dari Orcha — satu-satunya sumber angka di layar.
     *
     * Bukan $rab: nama itu milik parameter rute, dan Livewire mengisi
     * properti bernama sama dengan angka id-nya.
     */
    public array $dataRab = [];

    /** Destinasi, master harga, dan paket untuk provinsi RAB ini. */
    public array $katalog = [];

    /** Salinan itinerary yang bisa disunting; disimpan utuh tiap berubah. */
    public array $itinerary = [];

    public int $hariAktif = 1;

    public string $cariDestinasi = '';

    public string $kegiatanBaru = '';

    public string $marginJenis = 'persen';

    public string $marginNilai = '';

    public int $pembulatan = 1000;

    /** Harga & jumlah per baris biaya, dikunci id barisnya. */
    public array $hargaBaris = [];

    public array $jumlahBaris = [];

    public string $pilihMaster = '';

    public bool $bukaManual = false;

    public array $manual = [];

    public bool $bukaKepala = false;

    public array $kepala = [];

    /** Destinasi di itinerary yang belum punya harga tiket di master. */
    public array $tanpaTiket = [];

    public string $paketId = '';

    public function mount(int $rab): void
    {
        $this->rabId = $rab;
        $this->kosongkanManual();

        try {
            $this->terapkan($this->orcha()->ambil("/rab/{$rab}")['data'] ?? []);
            $this->katalog = $this->orcha()->ambil('/rab/katalog', ['provinsi' => $this->dataRab['provinsi'] ?? ''])['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->galat = $e->getMessage();
        }
    }

    /** Menyalin jawaban Orcha ke seluruh isian layar. */
    private function terapkan(array $data): void
    {
        if ($data === []) {
            return;
        }

        $this->dataRab = $data;
        $this->itinerary = collect($data['itinerary'] ?? [])->map(fn ($k) => [
            'hari_ke' => (int) $k['hari_ke'],
            'jam' => (string) ($k['jam'] ?? ''),
            'nama' => (string) $k['nama'],
            'keterangan' => (string) ($k['keterangan'] ?? ''),
            'destinasi' => (string) ($k['destinasi'] ?? ''),
        ])->values()->all();

        $this->marginJenis = (string) $data['margin_jenis'];
        $this->marginNilai = $data['margin_jenis'] === 'persen'
            ? (string) $data['margin_nilai']
            : $this->keRupiah($data['margin_nilai']);
        $this->pembulatan = (int) $data['pembulatan'];
        $this->hariAktif = min(max(1, $this->hariAktif), max(1, (int) $data['jumlah_hari']));

        $this->hargaBaris = [];
        $this->jumlahBaris = [];
        foreach ($data['ringkasan']['baris'] ?? [] as $b) {
            $this->hargaBaris[$b['id']] = $this->keRupiah($b['harga_satuan']) ?: '0';
            $this->jumlahBaris[$b['id']] = (string) $b['jumlah'];
        }

        $this->kepala = [
            'judul' => (string) $data['judul'],
            'nama_pelanggan' => (string) $data['nama_pelanggan'],
            'whatsapp' => (string) ($data['whatsapp'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'daerah' => (string) ($data['daerah'] ?? ''),
            'tanggal_mulai' => (string) ($data['tanggal_mulai'] ?? ''),
            'jumlah_hari' => (int) $data['jumlah_hari'],
            'jumlah_malam' => (int) $data['jumlah_malam'],
            'jumlah_peserta' => (int) $data['jumlah_peserta'],
            'berlaku_sampai' => (string) ($data['berlaku_sampai'] ?? ''),
            'catatan_penawaran' => (string) ($data['catatan_penawaran'] ?? ''),
            'catatan' => (string) ($data['catatan'] ?? ''),
        ];
    }

    /**
     * Satu pintu untuk setiap perubahan: kirim, lalu gambar ulang dari
     * jawabannya. Gagal berarti layar dimuat ulang dari Orcha, supaya yang
     * terlihat tidak pernah angka yang sebenarnya tidak tersimpan.
     */
    private function kirimKe(string $cara, string $jalur, array $data = [], ?string $pesan = null): ?array
    {
        try {
            $balasan = match ($cara) {
                'ubah' => $this->orcha()->ubah($jalur, $data),
                'kirim' => $this->orcha()->kirim($jalur, $data),
                'hapus' => $this->orcha()->hapus($jalur),
            };
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
            $this->muatUlang();

            return null;
        }

        $this->terapkan($balasan['data'] ?? []);

        if ($pesan) {
            $this->dispatch('order-updated', message: $pesan);
        }

        return $balasan;
    }

    private function muatUlang(): void
    {
        try {
            $this->terapkan($this->orcha()->ambil("/rab/{$this->rabId}")['data'] ?? []);
        } catch (OrchaTidakTerjangkau) {
            // Pesan galatnya sudah tampil lewat toast.
        }
    }

    /* ------------------------------ ITINERARY ------------------------------ */

    public function pilihHari(int $hari): void
    {
        $this->hariAktif = max(1, min($hari, (int) ($this->dataRab['jumlah_hari'] ?? 1)));
    }

    public function tambahDestinasi(string $nama): void
    {
        $this->sisipkan(['hari_ke' => $this->hariAktif, 'jam' => '', 'nama' => $nama, 'keterangan' => '', 'destinasi' => $nama]);
    }

    public function tambahKegiatan(): void
    {
        $nama = trim($this->kegiatanBaru);

        if ($nama === '') {
            return;
        }

        $this->kegiatanBaru = '';
        $this->sisipkan(['hari_ke' => $this->hariAktif, 'jam' => '', 'nama' => mb_substr($nama, 0, 150), 'keterangan' => '', 'destinasi' => '']);
    }

    /** Disisipkan di akhir harinya, bukan di akhir seluruh daftar. */
    private function sisipkan(array $kegiatan): void
    {
        $posisi = count($this->itinerary);
        foreach ($this->itinerary as $i => $k) {
            if ($k['hari_ke'] <= $kegiatan['hari_ke']) {
                $posisi = $i + 1;
            }
        }

        array_splice($this->itinerary, $posisi, 0, [$kegiatan]);
        $this->simpanItinerary();
    }

    public function hapusKegiatan(int $i): void
    {
        unset($this->itinerary[$i]);
        $this->itinerary = array_values($this->itinerary);
        $this->simpanItinerary();
    }

    /** Menggeser kegiatan naik (-1) atau turun (+1) di dalam harinya. */
    public function geser(int $i, int $arah): void
    {
        $j = $i + ($arah < 0 ? -1 : 1);

        if (! isset($this->itinerary[$i], $this->itinerary[$j])
            || $this->itinerary[$i]['hari_ke'] !== $this->itinerary[$j]['hari_ke']) {
            return;
        }

        [$this->itinerary[$i], $this->itinerary[$j]] = [$this->itinerary[$j], $this->itinerary[$i]];
        $this->simpanItinerary();
    }

    public function pindahHari(int $i, int $hari): void
    {
        if (! isset($this->itinerary[$i])) {
            return;
        }

        $kegiatan = $this->itinerary[$i];
        unset($this->itinerary[$i]);
        $this->itinerary = array_values($this->itinerary);
        $kegiatan['hari_ke'] = $hari;
        $this->sisipkan($kegiatan);
    }

    /** Jam, nama, atau keterangan disunting lalu ditinggalkan. */
    public function updatedItinerary(): void
    {
        $this->simpanItinerary();
    }

    private function simpanItinerary(): void
    {
        $data = collect($this->itinerary)
            ->filter(fn ($k) => trim((string) $k['nama']) !== '')
            ->sortBy('hari_ke', SORT_NUMERIC)   // stabil: urutan di dalam hari tetap
            ->map(fn ($k) => [
                'hari_ke' => (int) $k['hari_ke'],
                'jam' => $k['jam'] !== '' ? substr($k['jam'], 0, 5) : null,
                'nama' => trim($k['nama']),
                'keterangan' => trim($k['keterangan']) ?: null,
                'destinasi' => $k['destinasi'] ?: null,
            ])->values()->all();

        $this->kirimKe('ubah', "/rab/{$this->rabId}/itinerary", ['itinerary' => $data]);
    }

    /* -------------------------------- BIAYA -------------------------------- */

    public function tarikBiaya(): void
    {
        $balasan = $this->kirimKe('kirim', "/rab/{$this->rabId}/tarik-biaya", [], null);

        if ($balasan) {
            $this->tanpaTiket = $balasan['tanpa_tiket'] ?? [];
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Biaya ditarik.');
        }
    }

    public function tambahDariMaster(): void
    {
        if ($this->pilihMaster === '') {
            return;
        }

        $this->kirimKe('kirim', "/rab/{$this->rabId}/biaya", ['master_harga_id' => (int) $this->pilihMaster], 'Biaya ditambahkan.');
        $this->pilihMaster = '';
    }

    private function kosongkanManual(): void
    {
        $this->manual = ['kategori' => 'lainnya', 'nama' => '', 'satuan' => 'rombongan', 'harga_satuan' => '', 'kapasitas' => ''];
    }

    public function tambahManual(): void
    {
        $berkapasitas = in_array($this->manual['satuan'], ['unit_hari', 'kamar_malam'], true);

        $this->validate([
            'manual.nama' => 'required|string|max:150',
            'manual.harga_satuan' => 'required',
            'manual.kapasitas' => $berkapasitas ? 'required|integer|min:1' : 'nullable',
        ], [], ['manual.nama' => 'nama biaya', 'manual.harga_satuan' => 'harga', 'manual.kapasitas' => 'kapasitas']);

        $hasil = $this->kirimKe('kirim', "/rab/{$this->rabId}/biaya", [
            'kategori' => $this->manual['kategori'],
            'nama' => trim($this->manual['nama']),
            'satuan' => $this->manual['satuan'],
            'harga_satuan' => $this->angkaDari($this->manual['harga_satuan']),
            'kapasitas' => $berkapasitas ? (int) $this->manual['kapasitas'] : null,
        ], 'Biaya ditambahkan.');

        if ($hasil) {
            $this->kosongkanManual();
            $this->bukaManual = false;
        }
    }

    public function updatedManualHargaSatuan(): void
    {
        $this->manual['harga_satuan'] = $this->keRupiah($this->angkaDari($this->manual['harga_satuan']));
    }

    public function updatedHargaBaris($nilai, $id): void
    {
        $this->kirimKe('ubah', "/rab/{$this->rabId}/biaya/{$id}", ['harga_satuan' => $this->angkaDari((string) $nilai)]);
    }

    public function updatedJumlahBaris($nilai, $id): void
    {
        $this->kirimKe('ubah', "/rab/{$this->rabId}/biaya/{$id}", ['jumlah' => max(1, min(100, (int) $nilai))]);
    }

    public function pakaiHargaMaster(int $id): void
    {
        $this->kirimKe('ubah', "/rab/{$this->rabId}/biaya/{$id}", ['pakai_harga_master' => true], 'Harga diperbarui ke harga master.');
    }

    public function hapusBiaya(int $id): void
    {
        $this->kirimKe('hapus', "/rab/{$this->rabId}/biaya/{$id}", [], 'Biaya dihapus.');
    }

    /* -------------------------------- MARGIN ------------------------------- */

    public function updatedMarginJenis(): void
    {
        /*
         | Angka lama tidak dibawa pindah jenis: "20" persen yang berubah jadi
         | Rp 20 per orang bukan yang dimaksud siapa pun.
         |
         | Belum disimpan di sini. Menyimpan jenis baru dengan nilai kosong
         | membuat harga sesaat jatuh ke modal — dan PDF yang diunduh pada
         | saat itu menawarkan perjalanan tanpa untung. Disimpan begitu
         | angkanya diisi.
         */
        $this->marginNilai = '';
    }

    public function updatedMarginNilai(): void
    {
        $this->simpanMargin();
    }

    public function updatedPembulatan(): void
    {
        $this->simpanMargin();
    }

    private function simpanMargin(): void
    {
        $nilai = $this->marginJenis === 'persen'
            ? (int) round((float) str_replace(',', '.', $this->marginNilai))
            : $this->angkaDari($this->marginNilai);

        $this->kirimKe('ubah', "/rab/{$this->rabId}", [
            'margin_jenis' => $this->marginJenis,
            'margin_nilai' => $nilai,
            'pembulatan' => $this->pembulatan,
        ]);
    }

    /* ------------------------------ KEPALA RAB ----------------------------- */

    public function simpanKepala(): void
    {
        $this->validate([
            'kepala.judul' => 'required|string|min:3|max:150',
            'kepala.nama_pelanggan' => 'required|string|min:2|max:120',
            'kepala.email' => 'nullable|email|max:150',
            'kepala.tanggal_mulai' => 'nullable|date',
            'kepala.jumlah_hari' => 'required|integer|min:1|max:30',
            'kepala.jumlah_malam' => 'required|integer|min:0|max:30|lte:kepala.jumlah_hari',
            'kepala.jumlah_peserta' => 'required|integer|min:1|max:500',
            'kepala.berlaku_sampai' => 'nullable|date',
        ], [
            'kepala.jumlah_malam.lte' => 'Jumlah malam tidak boleh melebihi jumlah hari.',
        ], [
            'kepala.judul' => 'judul', 'kepala.nama_pelanggan' => 'nama pelanggan', 'kepala.email' => 'email',
            'kepala.jumlah_hari' => 'jumlah hari', 'kepala.jumlah_peserta' => 'jumlah peserta',
        ]);

        /*
         | Memperpendek perjalanan ditolak bila masih ada kegiatan di hari yang
         | akan hilang. Membuangnya diam-diam berarti admin kehilangan susunan
         | hari keempat hanya karena salah ketik 3.
         */
        $hariTerjauh = (int) collect($this->itinerary)->max('hari_ke');
        if ($hariTerjauh > (int) $this->kepala['jumlah_hari']) {
            $this->addError('kepala.jumlah_hari', "Masih ada kegiatan di hari ke-{$hariTerjauh}. Pindahkan atau hapus dulu.");

            return;
        }

        $data = collect($this->kepala)->map(fn ($v) => is_string($v) ? (trim($v) ?: null) : $v)->all();

        if ($this->kirimKe('ubah', "/rab/{$this->rabId}", $data, 'Data perjalanan disimpan.')) {
            $this->bukaKepala = false;
        }
    }

    public function ubahStatus(string $status): void
    {
        $this->kirimKe('ubah', "/rab/{$this->rabId}", ['status' => $status], 'Status diubah.');
    }

    public function jadikanPendaftaran(): void
    {
        $this->validate(['paketId' => 'required'], ['paketId.required' => 'Pilih paket induknya dulu.']);

        try {
            $baru = $this->orcha()->kirim("/rab/{$this->rabId}/jadikan-pendaftaran", [
                'travel_package_id' => (int) $this->paketId,
            ])['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        \App\Support\HitunganOrcha::lupakanSemua();
        $this->dispatch('orcha-sukses-pindah',
            message: 'Pendaftaran '.($baru['kode'] ?? '').' dibuat dari RAB ini.',
            url: route('admin.orcha.pendaftaran.detail', $baru['id']));
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.rab.susun', ['rab' => $this->dataRab])
            ->layout('livewire.layout.templateindex');
    }
}
