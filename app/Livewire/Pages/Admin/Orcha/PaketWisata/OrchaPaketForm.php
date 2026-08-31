<?php

namespace App\Livewire\Pages\Admin\Orcha\PaketWisata;

use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
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
    use IsianRupiah, MemanggilOrcha, WithFileUploads;

    public ?int $paketId = null;

    public bool $ubah = false;

    public string $nama = '';

    public string $kategori = 'open_trip';

    public string $status = 'terbit';

    public string $tayangMulai = '';

    public string $tayangSampai = '';

    public bool $berakhirOtomatis = true;

    public string $durasi = '';

    /**
     * Selama benar, durasi ikut tanggal berangkat/pulang. Begitu admin
     * mengetiknya sendiri, hitungan berhenti mengganggu — sebagian paket
     * memang perlu tulisan khusus, mis. "2 Hari 1 Malam (opsional extend)".
     */
    public bool $durasiOtomatis = true;

    public string $tanggalBerangkat = '';

    public string $tanggalPulang = '';

    public string $titikJemput = '';

    public int $minimalPeserta = 6;

    /**
     * Berapa kursi yang dijual. Kosong berarti BELUM DITETAPKAN, bukan nol.
     *
     * Bedanya penting: paket yang kuotanya kosong berperilaku persis seperti
     * sebelum kolom ini ada — pendaftarannya tidak pernah ditutup sistem.
     * Kalau kosong diperlakukan nol, seluruh paket lama langsung penuh.
     */
    public $kuota = null;

    public string $catatanPromo = '';

    public $harga = 0;

    public $hargaAsli = 0;

    /**
     * Modal per orang: biaya internal yang sudah dihitung untuk satu peserta.
     *
     * Kosong berarti belum dihitung, dan itu keadaan yang sah — private trip
     * sering dibuat paketnya dulu, modalnya menyusul setelah penawaran hotel
     * masuk. Yang tidak boleh adalah menganggapnya nol: laporan keuntungan
     * memisahkan keduanya, dan nol berarti paket ini tidak berbiaya.
     */
    public $hargaModal = null;

    /** Bentuk bertitik yang tampil di layar; angkanya di $harga/$hargaAsli. */
    public string $hargaTeks = '';

    public string $hargaAsliTeks = '';

    public string $hargaModalTeks = '';

    public $diskonPersen = 0;

    /**
     * Selama benar, diskon ikut selisih harga. Berhenti begitu admin
     * mengisinya sendiri — sebagian promo memakai angka bulat yang tidak
     * persis sama dengan hitungan.
     */
    public bool $diskonOtomatis = true;

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

    /** Tautan halaman paket di website, untuk memeriksa hasil hero-nya. */
    public ?string $tautanPublik = null;

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string',
            'status' => 'required|string',
            'tayangMulai' => 'nullable|date',
            'tayangSampai' => 'nullable|date|after_or_equal:tayangMulai',
            'durasi' => 'nullable|string|max:60',
            'tanggalBerangkat' => 'nullable|date',
            'tanggalPulang' => 'nullable|date|after_or_equal:tanggalBerangkat',
            'titikJemput' => 'nullable|string|max:191',
            'minimalPeserta' => 'required|integer|min:1|max:200',
            // Batas bawahnya minimal peserta: kuota yang lebih kecil daripada
            // jumlah minimum keberangkatan berarti tripnya tidak akan pernah
            // bisa berangkat, dan itu pasti salah ketik.
            'kuota' => 'nullable|integer|min:1|max:500|gte:minimalPeserta',
            'catatanPromo' => 'nullable|string|max:191',
            'harga' => 'required|numeric|min:0',
            'hargaAsli' => 'nullable|numeric|min:0',
            'hargaModal' => 'nullable|numeric|min:0',
            'diskonPersen' => 'nullable|numeric|min:0|max:100',
            'gambar' => 'nullable|image|max:4096',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'nama paket',
            'minimalPeserta' => 'minimal peserta',
            'kuota' => 'kuota kursi',
            'tanggalBerangkat' => 'tanggal berangkat',
            'tanggalPulang' => 'tanggal pulang',
            'hargaAsli' => 'harga asli',
            'hargaModal' => 'modal per orang',
            'diskonPersen' => 'diskon',
            'tayangMulai' => 'mulai tayang',
            'tayangSampai' => 'berhenti tayang',
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
        $this->status = $isi['status'] ?? 'terbit';
        $this->tayangMulai = (string) ($isi['tayang_mulai'] ?? '');
        $this->tayangSampai = (string) ($isi['tayang_sampai'] ?? '');
        $this->berakhirOtomatis = (bool) ($isi['berakhir_otomatis'] ?? true);
        $this->durasi = (string) ($isi['durasi'] ?? '');
        $this->tanggalBerangkat = (string) ($isi['tanggal_berangkat'] ?? '');
        $this->tanggalPulang = (string) ($isi['tanggal_pulang'] ?? '');
        $this->titikJemput = (string) ($isi['titik_jemput'] ?? '');
        $this->minimalPeserta = (int) ($isi['minimal_peserta'] ?? 6);
        $this->kuota = $isi['kuota'] ?? null;
        $this->catatanPromo = (string) ($isi['catatan_promo'] ?? '');
        $this->harga = $isi['harga'] ?? 0;
        $this->hargaAsli = $isi['harga_asli'] ?? 0;
        $this->hargaModal = $isi['harga_modal'] ?? null;
        $this->hargaTeks = $this->keRupiah($this->harga);
        $this->hargaAsliTeks = $this->keRupiah($this->hargaAsli);
        $this->hargaModalTeks = $this->hargaModal === null ? '' : $this->keRupiah($this->hargaModal);
        $this->diskonPersen = $isi['diskon_persen'] ?? 0;
        $this->diskonOtomatis = (int) $this->diskonPersen === $this->diskonTerhitung();
        $this->pilihanTerbaik = (bool) ($isi['pilihan_terbaik'] ?? false);
        $this->destinasi = array_values($isi['destinasi'] ?? []);
        $this->fasilitas = array_values($isi['fasilitas'] ?? []);
        $this->gambarLama = $isi['sampul'] ?? null;
        $this->tautanPublik = $isi['tautan_publik'] ?? null;

        $this->hari = $this->dariItinerary($isi['itinerary'] ?? []);
        $this->durasiOtomatis = $this->durasi === '' || $this->durasi === $this->durasiTerhitung();
    }

    /* -------------------------------- DURASI -------------------------------- */

    public function updatedTanggalBerangkat(): void
    {
        $this->hitungDurasiBila();
    }

    public function updatedTanggalPulang(): void
    {
        $this->hitungDurasiBila();
    }

    /** Admin mengetik sendiri: hitungan berhenti menimpa. */
    public function updatedDurasi(): void
    {
        $this->durasiOtomatis = $this->durasi === $this->durasiTerhitung();
    }

    /** Tombol "hitung ulang" — mengembalikan durasi ke hasil hitungan. */
    public function hitungDurasi(): void
    {
        $this->durasiOtomatis = true;
        $this->durasi = $this->durasiTerhitung();
    }

    private function hitungDurasiBila(): void
    {
        if ($this->durasiOtomatis) {
            $this->durasi = $this->durasiTerhitung();
        }
    }

    /**
     * "3 Hari 2 Malam" dari selisih tanggal. Bila tanggalnya belum diisi,
     * jumlah hari di itinerary dipakai sebagai perkiraan.
     */
    public function durasiTerhitung(): string
    {
        $hari = $this->jumlahHari();

        if ($hari < 1) {
            return '';
        }

        $malam = $hari - 1;

        return $malam > 0 ? "{$hari} Hari {$malam} Malam" : '1 Hari';
    }

    private function jumlahHari(): int
    {
        if ($this->tanggalBerangkat !== '' && $this->tanggalPulang !== '') {
            try {
                $berangkat = \Carbon\Carbon::parse($this->tanggalBerangkat)->startOfDay();
                $pulang = \Carbon\Carbon::parse($this->tanggalPulang)->startOfDay();
            } catch (\Throwable) {
                return 0;
            }

            // Tanggal pulang sebelum berangkat berarti isiannya belum benar;
            // validasi yang menegur, bukan hitungan ini.
            return $pulang->lt($berangkat) ? 0 : $berangkat->diffInDays($pulang) + 1;
        }

        // Belum ada tanggal: pakai hari yang sudah ada isinya di itinerary.
        return collect($this->hari)
            ->filter(fn ($satuHari) => collect($satuHari['agenda'] ?? [])
                ->contains(fn ($item) => trim($item['kegiatan'] ?? '') !== ''))
            ->count();
    }

    /* -------------------------------- DISKON -------------------------------- */

    public function updatedHargaTeks(): void
    {
        $this->harga = $this->angkaDari($this->hargaTeks);
        $this->hargaTeks = $this->keRupiah($this->harga);
        $this->hitungDiskonBila();
    }

    public function updatedHargaAsliTeks(): void
    {
        $this->hargaAsli = $this->angkaDari($this->hargaAsliTeks);
        $this->hargaAsliTeks = $this->keRupiah($this->hargaAsli);
        $this->hitungDiskonBila();
    }

    /**
     * Modal dikosongkan tetap null, bukan nol.
     *
     * angkaDari() mengembalikan 0 untuk isian kosong, dan kalau nilai itu
     * dikirim apa adanya, paket yang modalnya sengaja dibiarkan kosong
     * berubah jadi "modal Rp 0" — laporan lalu mengaku untung sebesar seluruh
     * harga jual, yang justru angka paling menyesatkan di halaman ini.
     */
    public function updatedHargaModalTeks(): void
    {
        if (trim($this->hargaModalTeks) === '') {
            $this->hargaModal = null;
            $this->hargaModalTeks = '';

            return;
        }

        $this->hargaModal = $this->angkaDari($this->hargaModalTeks);
        $this->hargaModalTeks = $this->keRupiah($this->hargaModal);
    }

    /**
     * Untung per peserta yang langsung terlihat saat kedua angka diisi.
     *
     * null berarti modalnya belum diisi — bukan nol. Boleh negatif: paket yang
     * dijual di bawah modal memang rugi, dan admin perlu melihatnya sebelum
     * menyimpan, bukan saat menutup buku sebulan kemudian.
     */
    public function marginPerOrang(): ?int
    {
        return $this->hargaModal === null ? null : (int) $this->harga - (int) $this->hargaModal;
    }

    public function marginPersen(): ?float
    {
        $margin = $this->marginPerOrang();

        return $margin === null || (int) $this->harga <= 0
            ? null
            : round($margin / (int) $this->harga * 100, 1);
    }

    public function updatedDiskonPersen(): void
    {
        $this->diskonOtomatis = (int) $this->diskonPersen === $this->diskonTerhitung();
    }

    public function hitungDiskon(): void
    {
        $this->diskonOtomatis = true;
        $this->diskonPersen = $this->diskonTerhitung();
    }

    private function hitungDiskonBila(): void
    {
        if ($this->diskonOtomatis) {
            $this->diskonPersen = $this->diskonTerhitung();
        }
    }

    /**
     * Berapa persen harga turun dari harga sebelum diskon. Dibulatkan ke
     * bawah supaya angka yang dipajang tidak pernah melebih-lebihkan potongan.
     */
    public function diskonTerhitung(): int
    {
        $asli = (float) $this->hargaAsli;
        $jual = (float) $this->harga;

        if ($asli <= 0 || $jual <= 0 || $jual >= $asli) {
            return 0;
        }

        return (int) floor((($asli - $jual) / $asli) * 100);
    }

    /* ------------------------- DESTINASI & FASILITAS ------------------------- */

    /** Klik pada saran: masuk kalau belum ada, keluar kalau sudah. */
    public function jungkit(string $jenis, string $nama): void
    {
        $terpilih = $jenis === 'destinasi' ? $this->destinasi : $this->fasilitas;

        $terpilih = in_array($nama, $terpilih, true)
            ? array_values(array_diff($terpilih, [$nama]))
            : [...$terpilih, $nama];

        $jenis === 'destinasi' ? $this->destinasi = $terpilih : $this->fasilitas = $terpilih;
    }

    /**
     * Isian baru langsung ikut masuk daftar pilihan di Orcha, jadi paket
     * berikutnya dengan isi yang sama tinggal diklik.
     */
    public function tambah(string $jenis): void
    {
        $nama = trim($jenis === 'destinasi' ? $this->destinasiBaru : $this->fasilitasBaru);

        if ($nama === '') {
            return;
        }

        $terpilih = $jenis === 'destinasi' ? $this->destinasi : $this->fasilitas;

        if (! in_array($nama, $terpilih, true)) {
            $terpilih[] = $nama;
            $jenis === 'destinasi' ? $this->destinasi = $terpilih : $this->fasilitas = $terpilih;
        }

        $this->kirimData('/saran', ['jenis' => $jenis, 'nama' => $nama], 'Masuk daftar pilihan.');

        $jenis === 'destinasi' ? $this->destinasiBaru = '' : $this->fasilitasBaru = '';
    }

    /** Keluarkan dari paket ini — daftar pilihannya tidak tersentuh. */
    public function buang(string $jenis, int $urutan): void
    {
        if ($jenis === 'destinasi') {
            unset($this->destinasi[$urutan]);
            $this->destinasi = array_values($this->destinasi);

            return;
        }

        unset($this->fasilitas[$urutan]);
        $this->fasilitas = array_values($this->fasilitas);
    }

    /**
     * Hapus dari daftar pilihan di Orcha.
     *
     * Paket yang sudah tersimpan tidak ikut berubah — yang hilang cuma
     * pilihan cepatnya.
     */
    public function hapusSaran(int $id): void
    {
        $this->hapusData("/saran/{$id}", 'Dihapus dari daftar pilihan.');
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
            'status' => $this->status,
            'tayang_mulai' => $this->tayangMulai,
            'tayang_sampai' => $this->tayangSampai,
            'berakhir_otomatis' => $this->berakhirOtomatis,
            'durasi' => $this->durasi,
            'tanggal_berangkat' => $this->tanggalBerangkat,
            'tanggal_pulang' => $this->tanggalPulang,
            'titik_jemput' => $this->titikJemput,
            'minimal_peserta' => $this->minimalPeserta,
            'kuota' => $this->kuota === '' ? null : $this->kuota,
            'catatan_promo' => $this->catatanPromo,
            'harga' => $this->harga,
            'harga_asli' => $this->hargaAsli ?: $this->harga,
            // Dikirim sebagai teks kosong, bukan dihilangkan: perataan
            // multipart membuang nilai null, dan kunci yang tidak terkirim
            // membuat Orcha mengira modalnya memang tidak disentuh.
            'harga_modal' => $this->hargaModal === null ? '' : $this->hargaModal,
            'diskon_persen' => $this->diskonPersen ?: 0,
            'pilihan_terbaik' => $this->pilihanTerbaik,
            'destinasi' => array_values($this->destinasi),
            'fasilitas' => array_values($this->fasilitas),
            'itinerary_teks' => $this->keTeksItinerary(),
        ];

        // Tujuan diteruskan supaya pemberitahuan sukses tampil utuh dulu di
        // halaman ini, baru berpindah ke daftar setelah popupnya menutup.
        $this->ubah
            ? $this->kirimData("/paket-wisata/{$this->paketId}", $data, 'Paket wisata diperbarui.', $this->gambar, route('admin.orcha.paket'))
            : $this->kirimData('/paket-wisata', $data, 'Paket wisata ditambahkan.', $this->gambar, route('admin.orcha.paket'));
    }

    /** Cerminan aturan tayang di Orcha, supaya akibat pilihan langsung terlihat. */
    public function statusTayang(): string
    {
        if (in_array($this->status, ['draf', 'arsip'], true)) {
            return $this->status;
        }

        $waktu = fn (string $nilai) => $nilai !== '' ? \Carbon\Carbon::parse($nilai) : null;

        if (($mulai = $waktu($this->tayangMulai)) && $mulai->isFuture()) {
            return 'terjadwal';
        }

        if (($sampai = $waktu($this->tayangSampai)) && $sampai->isPast()) {
            return 'berakhir';
        }

        $akhirTrip = $waktu($this->tanggalPulang) ?? $waktu($this->tanggalBerangkat);

        if ($this->berakhirOtomatis && $akhirTrip && $akhirTrip->endOfDay()->isPast()) {
            return 'berakhir';
        }

        return 'tayang';
    }

    public function render()
    {
        // Daftar pilihan hidup di Orcha dan tumbuh sendiri setiap ada isian
        // baru, jadi diambil segar tiap render — bukan dari cache rujukan.
        $saran = $this->muat('/saran')['data'] ?? [];

        return view('livewire.pages.admin.orcha.paket-wisata.form', [
            'pilihanKategori' => $this->rujukan('kategori_paket'),
            'pilihanStatusPaket' => $this->rujukan('status_paket'),
            'statusTayang' => $statusTayang = $this->statusTayang(),
            'statusTayangLabel' => $this->rujukan('status_tayang')[$statusTayang] ?? 'Tayang',
            'saranDestinasi' => $saran['destinasi'] ?? [],
            'saranFasilitas' => $saran['fasilitas'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
