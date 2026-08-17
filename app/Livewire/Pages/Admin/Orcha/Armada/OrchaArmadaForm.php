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

    /** Tipe/varian unit: "E", "G", "GR Sport". */
    public string $varian = '';

    /** Tahun perakitan. Tidak bisa disimpulkan dari model — selalu diketik. */
    public $tahun = '';

    /** Isi silinder dalam cc. */
    public $cc = '';

    /**
     * Jenis dan cc sudah diubah admin sendiri.
     *
     * Sama seperti kapasitas: sekali dikoreksi, saran tidak menimpanya lagi
     * selama mereknya belum berganti.
     */
    public bool $jenisDiubahManual = false;

    public bool $ccDiubahManual = false;

    /** Unit sumber angka cc, untuk keterangan di layar. */
    public string $ccOtomatisDari = '';

    /**
     * Unit boleh disewa lepas kunci (tanpa sopir).
     *
     * HiAce dan bus tidak dilepas tanpa sopir, dan kolom ini juga menentukan
     * hitungan kursi penumpang: unit yang selalu dengan sopir kehilangan satu
     * kursi untuk sopirnya.
     */
    public bool $lepasKunci = true;

    public bool $lepasKunciDiubahManual = false;

    /**
     * Tarif sewa sudah termasuk sopir.
     *
     * Untuk HiAce dan bus yang tarifnya memang dihitung bersama sopirnya —
     * "2.500.000 per hari, sudah termasuk sopir". Sebelumnya satu-satunya cara
     * menyatakannya adalah mengosongkan tarif sopir, dan itu bermakna ganda:
     * bisa "sudah termasuk", bisa "belum diisi".
     */
    public bool $termasukSopir = false;

    public array $transmisi = ['Manual'];

    public $tarifHari = 0;

    public $tarifJam = '';

    public $tarif12Jam = '';

    public $tarifSopir = '';

    /**
     * Tarif harian untuk perjalanan luar kota.
     *
     * Hanya harian: sewa luar kota tidak dijual per jam atau paket 12 jam —
     * perjalanan ke Bromo tidak selesai dalam dua belas jam. Kosong berarti
     * tarifnya sama dengan dalam kota.
     */
    public $tarifLuarKota = '';

    /** Bentuk bertitik yang tampil di layar. */
    public string $tarifHariTeks = '';

    public string $tarifJamTeks = '';

    public string $tarif12JamTeks = '';

    public string $tarifSopirTeks = '';

    public string $tarifLuarKotaTeks = '';

    public bool $tersedia = true;

    /**
     * BBM, tol, dan parkir — masing-masing berdiri sendiri.
     *
     * Ada unit yang BBM-nya ditanggung pemilik tetapi tolnya tidak, dan parkir
     * hampir selalu urusan tersendiri. Satu penanda gabungan memaksa ketiganya
     * diputuskan bersama, sehingga keadaan yang sebenarnya tidak bisa dinyatakan.
     *
     * Disimpan sebagai array berkunci pos supaya penambahan pos berikutnya cukup
     * mengubah config di Orcha, bukan menambah properti dan cabang baru di sini.
     *
     * @var array<string, bool>
     */
    public array $termasukPos = [];

    /** @var array<string, int|string> nominal murni per pos */
    public array $biayaPos = [];

    /** @var array<string, string> bentuk bertitik yang tampil di layar */
    public array $biayaPosTeks = [];

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
            'varian' => 'nullable|string|max:60',
            // Batas ke belakang longgar (Kijang Kapsul 1997 masih disewakan),
            // ke depan hanya satu tahun: unit tahun 2035 pasti salah ketik, dan
            // salah ketik tahun tidak pernah kelihatan salah.
            'tahun' => 'nullable|integer|min:1980|max:'.(date('Y') + 1),
            'cc' => 'nullable|integer|min:500|max:20000',
            // Bentuknya diperiksa di sini juga, bukan hanya di Orcha: pesan galat
            // yang muncul di sebelah isiannya lebih berguna daripada galat yang
            // datang setelah permintaan bolak-balik.
            'nopol' => ['nullable', 'string', 'max:20', function ($atribut, $nilai, $gagal) {
                $telanjang = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $nilai));

                if ($telanjang !== '' && ! preg_match('/^[A-Z]{1,2}\d{1,5}[A-Z]{0,3}$/', $telanjang)) {
                    $gagal('Nomor polisi belum benar. Contoh: AB 4169 TE.');
                }
            }],
            'kapasitas' => 'required|integer|min:1|max:80',
            'transmisi' => 'required|array|min:1',
            'tarifHari' => 'required|numeric|min:0',
            'tarifJam' => 'nullable|numeric|min:0',
            'tarif12Jam' => 'nullable|numeric|min:0',
            'tarifSopir' => 'nullable|numeric|min:0',
            'tarifLuarKota' => 'nullable|numeric|min:0',
            // Unit yang selalu dengan sopir harus menyatakan salah satu: tarifnya
            // sudah termasuk sopir, atau berapa tambahannya. Tanpa keduanya,
            // halaman publik menampilkan unit yang pasti bersopir tanpa
            // keterangan biaya sopirnya sama sekali.
            'termasukSopir' => ['boolean', function ($atribut, $nilai, $gagal) {
                if (! $this->lepasKunci && ! $nilai && ! $this->tarifSopir) {
                    $gagal('Unit yang selalu dengan sopir harus menyebut tarif sopirnya, '
                        .'atau ditandai tarifnya sudah termasuk sopir.');
                }
            }],
            'biayaPos.*' => 'nullable|numeric|min:0|max:100000000',
            'gambar' => 'nullable|image|max:4096',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'nama unit',
            'merek' => 'merek',
            'varian' => 'tipe',
            'nopol' => 'nomor polisi',
            'tahun' => 'tahun',
            'cc' => 'isi silinder',
            'transmisi' => 'transmisi',
            'tarifHari' => 'tarif per hari',
            'tarifJam' => 'tarif per jam',
            'tarif12Jam' => 'tarif paket 12 jam',
            'tarifSopir' => 'tarif sopir',
            'tarifLuarKota' => 'tarif luar kota',
            'termasukSopir' => 'keterangan sopir',
            'biayaPos.bbm' => 'biaya BBM',
            'biayaPos.tol' => 'biaya tol',
            'biayaPos.parkir' => 'biaya parkir',
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

    /**
     * @return array<string, string> kunci pos => label
     */
    public function posOperasional(): array
    {
        return $this->rujukan('pos_operasional');
    }

    /**
     * Jumlah biaya harian dari pos yang termasuk saja.
     *
     * Ditampilkan di formulir supaya admin melihat angka yang benar-benar
     * ditambahkan ke perkiraan harga, bukan menjumlahkan tiga isian di kepala.
     */
    public function totalPos(): int
    {
        $total = 0;

        foreach (array_keys($this->posOperasional()) as $pos) {
            if ($this->termasukPos[$pos] ?? false) {
                $total += (int) ($this->biayaPos[$pos] ?? 0);
            }
        }

        return $total;
    }

    /**
     * Isian rupiah tiap pos dibakukan tampilannya.
     *
     * Satu hook untuk seluruh array — Livewire menyebut nama medan bertitik
     * ("biayaPosTeks.bbm"), jadi posnya diambil dari situ.
     */
    public function updatedBiayaPosTeks(string $nilai, string $pos): void
    {
        $this->biayaPos[$pos] = $this->angkaDari($nilai);
        $this->biayaPosTeks[$pos] = $this->keRupiah($this->biayaPos[$pos]);
    }

    /**
     * Mematikan sebuah pos mengosongkan nominalnya.
     *
     * Angka yang tertinggal pada pos yang ditanggung penyewa adalah biaya
     * siluman: ia ikut terpakai begitu penandanya dinyalakan lagi, dan pemiliknya
     * tidak ingat pernah mengisinya.
     */
    public function updatedTermasukPos($nilai, string $pos): void
    {
        if (! $nilai) {
            $this->biayaPos[$pos] = '';
            $this->biayaPosTeks[$pos] = '';
        }
    }

    public function updatedTarifLuarKotaTeks(): void
    {
        $this->tarifLuarKota = $this->angkaDari($this->tarifLuarKotaTeks);
        $this->tarifLuarKotaTeks = $this->keRupiah($this->tarifLuarKota);
    }

    public function updatedTarifSopirTeks(): void
    {
        $this->tarifSopir = $this->angkaDari($this->tarifSopirTeks) ?: '';
        $this->tarifSopirTeks = $this->keRupiah($this->tarifSopir);
        $this->tarifLuarKotaTeks = $this->keRupiah($this->tarifLuarKota);
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
     * SELURUH peta tipe: merek => model => daftar tipe.
     *
     * Dikirim utuh ke peramban, bukan hanya tipe untuk unit yang sedang dipilih.
     *
     * Alasannya: Livewire TIDAK menjalankan ulang <script> inline saat me-render
     * ulang komponennya. Nilai yang hanya berisi pilihan saat ini akan membeku
     * pada keadaan pemuatan pertama — yaitu kosong, karena belum ada merek yang
     * dipilih. Gejalanya tepat seperti yang dilaporkan: daftar tipe kosong
     * setelah memilih merek dan unit, lalu tiba-tiba terisi begitu ada tipe
     * ditambahkan, karena penambahan itu memancarkan peristiwa yang menyegarkan
     * datanya.
     *
     * Dengan peta utuh, pencariannya dilakukan saat tombolnya diklik — sama
     * seperti daftar model yang memang sudah bekerja benar sejak awal.
     *
     * @return array<string, array<string, list<string>>>
     */
    public function varianSemua(): array
    {
        return $this->rujukan('varian_per_model');
    }

    /**
     * Tipe untuk merek + model yang sedang dipilih.
     *
     * Dipakai sisi server: menentukan keadaan nonaktif tombolnya dan diuji
     * tersendiri. Peramban memakai varianSemua() — lihat keterangan di atas.
     *
     * @return list<string>
     */
    public function varianPilihan(): array
    {
        return $this->varianSemua()[trim($this->merek)][trim($this->nama)] ?? [];
    }

    public function jenisDisarankan(): ?string
    {
        return $this->rujukan('jenis_per_model')[trim($this->merek)][trim($this->nama)] ?? null;
    }

    public function lepasKunciDisarankan(): ?bool
    {
        $nilai = $this->rujukan('lepas_kunci_per_model')[trim($this->merek)][trim($this->nama)] ?? null;

        return $nilai === null ? null : (bool) $nilai;
    }

    /**
     * Kursi total, termasuk kursi sopir.
     *
     * Isian Kapasitas sudah berisi kursi penumpang, jadi yang ditambahkan di sini
     * kursi sopirnya — supaya spesifikasi pabriknya tetap bisa disebut di layar
     * ("14 penumpang dari 15 kursi") tanpa admin perlu menghitung sendiri.
     */
    public function kursiTotal(): int
    {
        return $this->lepasKunci ? $this->kapasitas : $this->kapasitas + 1;
    }

    public function ccDisarankan(): ?int
    {
        $cc = $this->rujukan('cc_per_model')[trim($this->merek)][trim($this->nama)] ?? null;

        return is_numeric($cc) && (int) $cc > 0 ? (int) $cc : null;
    }

    /**
     * Kursi total menurut katalog — termasuk kursi sopir, sesuai spesifikasi pabrik.
     */
    public function kursiTotalKatalog(): ?int
    {
        $kursi = $this->kapasitasKatalog()[trim($this->merek)][trim($this->nama)] ?? null;

        return is_numeric($kursi) && (int) $kursi > 0 ? (int) $kursi : null;
    }

    /**
     * Kapasitas yang disarankan, yaitu kursi PENUMPANG.
     *
     * Unit yang selalu dengan sopir sudah dikurangi satu di sini, karena isian
     * Kapasitas menyimpan angka yang dipakai menjawab "muat berapa orang?" —
     * angka itulah yang tertulis di penawaran dan dijanjikan ke pelanggan.
     * Menyimpan kursi total lalu menguranginya belakangan berarti angka yang
     * paling sering dibaca justru yang harus dihitung ulang tiap kali.
     */
    public function kursiDisarankan(): ?int
    {
        $total = $this->kursiTotalKatalog();

        if ($total === null) {
            return null;
        }

        return $this->lepasKunci ? $total : max(1, $total - 1);
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
    /**
     * Nomor polisi dirapikan begitu admin keluar dari isiannya.
     *
     * Kapitalnya sudah terlihat sejak diketik lewat text-transform, tetapi itu
     * hanya tampilan — nilainya tetap apa adanya. Penormalan di sini yang membuat
     * yang TERLIHAT sama dengan yang TERSIMPAN, sehingga admin tidak menekan
     * simpan sambil menyangka nopolnya sudah rapi padahal belum.
     *
     * Aturan bakunya tetap ada di Orcha (App\Support\SewaKendaraan\NomorPolisi);
     * yang di sini hanya menyamakan tampilannya lebih awal.
     */
    public function updatedNopol(): void
    {
        $telanjang = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->nopol));

        if ($telanjang === '') {
            $this->nopol = '';

            return;
        }

        $this->nopol = preg_match('/^([A-Z]{1,2})(\d{1,5})([A-Z]{0,3})$/', $telanjang, $bagian)
            ? trim($bagian[1].' '.$bagian[2].' '.$bagian[3])
            // Bentuk tak terduga tidak dibuang isinya — hanya dikapitalkan.
            : trim(preg_replace('/\s+/', ' ', strtoupper($this->nopol)));
    }

    public function updatedNama(): void
    {
        $this->nama = trim($this->nama);

        // Tipe melekat pada modelnya: tipe "Veloz" tidak berlaku untuk Ertiga.
        $this->varian = '';

        $this->isiOtomatis();
    }

    /**
     * Mengisi kapasitas, jenis, dan cc dari katalog.
     *
     * Berlaku di TAMBAH maupun UBAH: yang membedakan hanya siapa yang menang saat
     * halaman dibuka. Di halaman ubah, nilai tersimpan unitnya dipakai apa adanya
     * dan tidak ditimpa; begitu admin mengganti modelnya, saran berlaku seperti
     * biasa — karena unit yang dimaksud sudah bukan unit yang sama.
     *
     * Tiap isian punya penandanya sendiri, jadi mengoreksi kapasitas tidak ikut
     * membekukan cc dan sebaliknya.
     */
    private function isiOtomatis(): void
    {
        $sumber = trim($this->merek.' '.$this->nama);

        $this->kursiOtomatisDari = '';
        $this->ccOtomatisDari = '';

        if (! $this->jenisDiubahManual && ($jenis = $this->jenisDisarankan()) !== null) {
            $this->jenis = $jenis;
        }

        // Lepas kunci diputuskan LEBIH DAHULU daripada kapasitas: jumlah kursi
        // penumpang bergantung padanya. Urutan sebaliknya mengisi 15 lalu baru
        // tahu unitnya selalu dengan sopir, dan angkanya tertinggal salah.
        if (! $this->lepasKunciDiubahManual && ($lepas = $this->lepasKunciDisarankan()) !== null) {
            $this->lepasKunci = $lepas;
        }

        if (! $this->kapasitasDiubahManual && ($kursi = $this->kursiDisarankan()) !== null) {
            $this->kapasitas = $kursi;
            $this->kursiOtomatisDari = $sumber;
        }

        if (! $this->ccDiubahManual && ($cc = $this->ccDisarankan()) !== null) {
            $this->cc = $cc;
            $this->ccOtomatisDari = $sumber;
        }
    }

    /**
     * Admin mengubah isiannya sendiri: saran tidak boleh menimpanya lagi.
     */
    public function updatedKapasitas(): void
    {
        $this->kapasitasDiubahManual = true;
        $this->kursiOtomatisDari = '';
    }

    public function updatedJenis(): void
    {
        $this->jenisDiubahManual = true;
    }

    public function updatedCc(): void
    {
        $this->ccDiubahManual = true;
        $this->ccOtomatisDari = '';
    }

    /**
     * Menggeser sakelar lepas kunci menghitung ulang kapasitasnya.
     *
     * Menandai unit "selalu dengan sopir" tanpa mengubah kapasitasnya akan
     * meninggalkan angka yang menjanjikan satu kursi lebih daripada yang ada —
     * persis kesalahan yang sakelar ini ada untuk mencegah.
     */
    /**
     * Menandai tarif sudah termasuk sopir mengosongkan tarif sopirnya.
     *
     * Angka yang tertinggal di sana ikut ditagihkan begitu penandanya dimatikan,
     * dan pemiliknya tidak ingat pernah mengisinya.
     */
    public function updatedTermasukSopir(): void
    {
        if ($this->termasukSopir) {
            $this->tarifSopir = '';
            $this->tarifSopirTeks = '';
        }

        $this->resetValidation('termasukSopir');
    }

    public function updatedLepasKunci(): void
    {
        $this->lepasKunciDiubahManual = true;
        $this->resetValidation('termasukSopir');

        if ($this->kapasitasDiubahManual) {
            return;
        }

        if (($kursi = $this->kursiDisarankan()) !== null) {
            $this->kapasitas = $kursi;
            $this->kursiOtomatisDari = trim($this->merek.' '.$this->nama);

            return;
        }

        // Tanpa angka katalog, kapasitas yang sudah tertulis tetap disesuaikan
        // arah perubahannya: satu kursi dilepas untuk sopir, atau dikembalikan.
        $this->kapasitas = $this->lepasKunci
            ? $this->kapasitas + 1
            : max(1, $this->kapasitas - 1);
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
    public function tambahKatalog(string $nilai, string $untuk = 'merek'): void
    {
        $nilai = trim(preg_replace('/\s+/', ' ', $nilai));

        if ($nilai === '') {
            return;
        }

        // Nilainya dipakai lebih dahulu, apa pun hasil pendaftarannya: admin
        // sudah menyatakan maksudnya, dan kegagalan mendaftar tidak boleh
        // menghalanginya menyimpan unit ini.
        match ($untuk) {
            'unit' => $this->nama = $nilai,
            'varian' => $this->varian = $nilai,
            default => [$this->merek = $nilai, $this->nama = ''],
        };

        // Entri model harus menempel pada mereknya, dan entri tipe pada modelnya.
        // Tanpa itu barisnya jadi yatim dan tidak pernah terbaca sebagai pilihan.
        if ($untuk !== 'merek' && trim($this->merek) === '') {
            return;
        }

        if ($untuk === 'varian' && trim($this->nama) === '') {
            return;
        }

        $this->simpanKatalog(match ($untuk) {
            'unit' => ['merek' => trim($this->merek), 'model' => $nilai],
            'varian' => ['merek' => trim($this->merek), 'model' => trim($this->nama), 'varian' => $nilai],
            default => ['merek' => $nilai],
        });

        if ($untuk === 'unit') {
            $this->isiOtomatis();
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
            kustom: $this->katalogKustom(),
            varian: $this->varianSemua());
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
        $this->varian = '';
        // Unitnya berganti, jadi koreksi untuk unit sebelumnya tidak lagi berlaku
        // dan saran boleh mengisi lagi.
        $this->kapasitasDiubahManual = false;
        $this->jenisDiubahManual = false;
        $this->ccDiubahManual = false;
        $this->lepasKunciDiubahManual = false;
        $this->kursiOtomatisDari = '';
        $this->ccOtomatisDari = '';
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
        $this->varian = (string) ($isi['varian'] ?? '');
        $this->tahun = $isi['tahun'] ?? '';
        $this->cc = $isi['cc'] ?? '';
        $this->lepasKunci = (bool) ($isi['lepas_kunci'] ?? true);
        $this->termasukSopir = (bool) ($isi['termasuk_sopir'] ?? false);
        $this->transmisi = $isi['transmisi_tersedia'] ?: ['Manual'];
        $this->tarifHari = $isi['tarif']['hari'] ?? 0;
        $this->tarifJam = $isi['tarif']['jam'] ?? '';
        $this->tarif12Jam = $isi['tarif']['12jam'] ?? '';
        $this->tarifSopir = $isi['tarif']['sopir_per_hari'] ?? '';
        $this->tarifLuarKota = $isi['tarif']['luar_kota'] ?? '';
        $this->tersedia = (bool) ($isi['tersedia'] ?? true);
        foreach ($isi['operasional'] ?? [] as $pos => $rinci) {
            $this->termasukPos[$pos] = (bool) ($rinci['termasuk'] ?? false);
            $this->biayaPos[$pos] = ($rinci['biaya'] ?? 0) ?: '';
            $this->biayaPosTeks[$pos] = $this->keRupiah($this->biayaPos[$pos]);
        }
        $this->tarifHariTeks = $this->keRupiah($this->tarifHari);
        $this->tarifJamTeks = $this->keRupiah($this->tarifJam);
        $this->tarif12JamTeks = $this->keRupiah($this->tarif12Jam);
        $this->tarifSopirTeks = $this->keRupiah($this->tarifSopir);
        $this->tarifLuarKotaTeks = $this->keRupiah($this->tarifLuarKota);
        $this->gambarLama = $isi['gambar'] ?? null;
    }

    public function simpan(): void
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'merek' => $this->merek,
            'varian' => $this->varian ?: null,
            'tahun' => $this->tahun !== '' ? (int) $this->tahun : null,
            'cc' => $this->cc !== '' ? (int) $this->cc : null,
            'jenis' => $this->jenis,
            'nopol' => $this->nopol,
            'kapasitas' => $this->kapasitas,
            'lepas_kunci' => $this->lepasKunci,
            'transmisi_tersedia' => array_values($this->transmisi),
            'tarif_hari' => $this->tarifHari,
            'tarif_jam' => $this->tarifJam ?: null,
            'tarif_12jam' => $this->tarif12Jam ?: null,
            'tarif_sopir' => $this->termasukSopir ? null : ($this->tarifSopir ?: null),
            'tarif_luar_kota' => $this->tarifLuarKota ?: null,
            'termasuk_sopir' => $this->termasukSopir,
            'tersedia' => $this->tersedia,
            ...$this->muatanPos(),
        ];

        // Tujuan diteruskan supaya pemberitahuan sukses tampil utuh dulu di
        // halaman ini, baru berpindah ke daftar setelah popupnya menutup.
        $this->ubah
            ? $this->kirimData("/kendaraan/{$this->kendaraanId}", $data, 'Kendaraan diperbarui.', $this->gambar, route('admin.orcha.armada'))
            : $this->kirimData('/kendaraan', $data, 'Kendaraan ditambahkan.', $this->gambar, route('admin.orcha.armada'));
    }

    /**
     * Penanda dan biaya tiap pos untuk dikirim ke Orcha.
     *
     * @return array<string, bool|int|null>
     */
    private function muatanPos(): array
    {
        $muatan = [];

        foreach (array_keys($this->posOperasional()) as $pos) {
            $termasuk = (bool) ($this->termasukPos[$pos] ?? false);

            $muatan["termasuk_{$pos}"] = $termasuk;
            $muatan["biaya_{$pos}"] = $termasuk ? (($this->biayaPos[$pos] ?? null) ?: null) : null;
        }

        return $muatan;
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
            'ccDisarankan' => $this->ccDisarankan(),
            'varianPilihan' => $this->varianPilihan(),
            'varianSemua' => $this->varianSemua(),
            'kursiTotal' => $this->kursiTotal(),
            'posOperasional' => $this->posOperasional(),
            'totalPos' => $this->totalPos(),
            'modelPilihan' => $this->modelPilihan(),
        ])->layout('livewire.layout.templateindex');
    }
}
