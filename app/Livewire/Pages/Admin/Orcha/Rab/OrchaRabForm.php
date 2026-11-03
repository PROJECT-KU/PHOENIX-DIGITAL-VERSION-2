<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Buat dan ubah kepala RAB — halaman tersendiri, bukan panel yang menumpang.
 *
 * Dulu keduanya menumpang di layar lain: membuat di panel yang terbuka di
 * bawah daftar, mengubah di panel "Data perjalanan" di dalam penyusun. Dua
 * tempat untuk isian yang sama berarti dua borang yang harus dijaga sama,
 * dan keduanya sudah mulai berselisih — panel ubah punya Email, Berlaku
 * sampai, dan dua kotak catatan yang tidak ada di panel buat.
 *
 * Satu halaman, dua jalur: /rab/tambah dan /rab/{id}/ubah.
 */
class OrchaRabForm extends Component
{
    use MemanggilOrcha;

    public ?int $rabId = null;

    public bool $ubah = false;

    public array $isian = [];

    /** Kode RAB yang sedang diubah, untuk disebut di kepala halaman. */
    public string $kode = '';

    /**
     * Hari terjauh yang sudah terpakai itinerary.
     *
     * Memperpendek perjalanan tidak boleh membuang hari yang masih berisi
     * kegiatan: admin kehilangan susunan hari keempat hanya karena salah
     * ketik 3, dan tidak ada satu pun tanda bahwa ada yang hilang.
     */
    public int $hariTerpakai = 0;

    /**
     * Ke mana admin dikembalikan sesudah menyimpan.
     *
     * Dibawa di alamat, bukan ditebak: yang menekan "Data perjalanan" dari
     * penyusun sedang di tengah menyusun dan harus kembali ke sana, sedangkan
     * yang menekan "Ubah" dari daftar sedang membereskan daftarnya.
     */
    #[Url(as: 'dari', except: '')]
    public string $dari = '';

    private function kosongkan(): void
    {
        $this->isian = [
            'judul' => '',
            'nama_pelanggan' => '',
            'whatsapp' => '',
            'email' => '',
            'provinsi' => '',
            'daerah' => '',
            'tanggal_mulai' => '',
            // Diturunkan dari tanggal mulai + jumlah hari; tidak dikirim ke
            // Orcha, karena di sana pun ia diturunkan, bukan disimpan.
            'tanggal_pulang' => '',
            'jumlah_hari' => 3,
            'jumlah_malam' => 2,
            'jumlah_peserta' => 10,
            /*
             | Private trip sebagai bawaan: jumlah orangnya sudah pasti di
             | kesepakatan, jadi kursi kosong bukan risiko kantor. Open trip
             | dipilih sendiri, karena ia menuntut keputusan lain — jumlah
             | paling rendah yang masih mau diberangkatkan.
             */
            'jenis' => 'private',
            'peserta_minimum' => 5,
            'harga_dasar' => 'minimum',
            'berlaku_sampai' => '',
            'catatan_penawaran' => '',
            'catatan' => '',
        ];
    }

    public function mount(?int $rab = null): void
    {
        $this->kosongkan();

        if (! $rab) {
            return;
        }

        $this->rabId = $rab;
        $this->ubah = true;

        try {
            $data = $this->orcha()->ambil("/rab/{$rab}")['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        if ($data === []) {
            return;
        }

        $this->kode = (string) ($data['kode'] ?? '');
        $this->hariTerpakai = (int) collect($data['itinerary'] ?? [])->max('hari_ke');

        // Hanya kunci yang memang milik borang ini yang disalin: jawaban RAB
        // membawa puluhan kunci hitungan, dan menyalinnya bulat-bulat berarti
        // mengirimkannya kembali sebagai isian saat disimpan.
        foreach (array_keys($this->isian) as $kunci) {
            if (array_key_exists($kunci, $data) && $data[$kunci] !== null) {
                $this->isian[$kunci] = is_string($this->isian[$kunci])
                    ? (string) $data[$kunci]
                    : $data[$kunci];
            }
        }

        // Dua yang tidak ikut pola di atas: keduanya angka, dan nol bukan
        // kosong.
        $this->isian['jumlah_hari'] = (int) ($data['jumlah_hari'] ?? 1);
        $this->isian['jumlah_malam'] = (int) ($data['jumlah_malam'] ?? 0);
        $this->isian['jumlah_peserta'] = (int) ($data['jumlah_peserta'] ?? 1);
        $this->isian['jenis'] = (string) ($data['jenis'] ?? 'private');
        $this->isian['peserta_minimum'] = $data['peserta_minimum'] ?? null;
    }

    /** Open trip menuntut isian yang tidak dipakai private trip. */
    public function openTrip(): bool
    {
        return ($this->isian['jenis'] ?? 'private') === 'open';
    }

    /**
     * Judul yang dirakit dari isian lain: "Private Trip Yogyakarta 3H2M".
     *
     * Judul adalah satu-satunya isian wajib yang jawabannya sudah diketahui
     * dari kotak-kotak di sebelahnya. Mengetiknya ulang bukan keputusan, cuma
     * penyalinan — dan penyalinan yang dilakukan berpuluh kali sehari adalah
     * tempat salah ketik lahir.
     *
     * Tetap bisa ditulis sendiri; yang dihapus kewajibannya, bukan pilihannya.
     */
    public function judulOtomatis(): string
    {
        $awalan = $this->openTrip() ? 'Open Trip ' : 'Private Trip ';
        $tujuan = trim((string) ($this->isian['daerah'] ?? '')) ?: trim((string) ($this->isian['provinsi'] ?? ''));
        $hari = max(1, (int) ($this->isian['jumlah_hari'] ?? 1));
        $malam = max(0, (int) ($this->isian['jumlah_malam'] ?? 0));

        return trim($awalan.$tujuan.' '.$hari.'H'.$malam.'M');
    }

    /**
     * Malam mengikuti hari: 3 hari hampir selalu 2 malam. Admin yang ingin
     * lain tinggal mengubahnya, tetapi tidak perlu mengingat untuk mengubah.
     *
     * Tanggal pulang ikut bergeser supaya ketiga angka itu tidak pernah
     * saling bertentangan di layar.
     */
    public function updatedIsianJumlahHari($hari): void
    {
        $this->isian['jumlah_malam'] = max(0, (int) $hari - 1);
        $this->selaraskanTanggalPulang();
    }

    /**
     * Tanggal pulang diisi → jumlah hari & malam dihitung dari rentangnya.
     *
     * Yang disepakati dengan pelanggan tanggalnya ("berangkat 10, pulang
     * 12"), bukan jumlah harinya. Menyuruh admin menerjemahkannya sendiri
     * adalah hitungan kepala yang mudah meleset satu — dan yang meleset
     * satu hari menggeser seluruh modal akomodasi.
     */
    public function updatedIsianTanggalPulang(): void
    {
        $this->hitungDariTanggal();
    }

    public function updatedIsianTanggalMulai(): void
    {
        $this->hitungDariTanggal();
    }

    private function hitungDariTanggal(): void
    {
        $mulai = trim((string) ($this->isian['tanggal_mulai'] ?? ''));
        $pulang = trim((string) ($this->isian['tanggal_pulang'] ?? ''));

        if ($mulai === '' || $pulang === '') {
            return;
        }

        try {
            $a = \Carbon\Carbon::parse($mulai)->startOfDay();
            $b = \Carbon\Carbon::parse($pulang)->startOfDay();
        } catch (\Throwable) {
            return;
        }

        // Pulang sebelum berangkat tidak dihitung — angkanya akan negatif,
        // dan menolaknya diam-diam lebih baik daripada menulis "0 hari".
        if ($b->lessThan($a)) {
            return;
        }

        // Hari keberangkatan ikut terhitung: 10 → 12 adalah 3 hari 2 malam.
        $this->isian['jumlah_hari'] = $a->diffInDays($b) + 1;
        $this->isian['jumlah_malam'] = $a->diffInDays($b);
    }

    /** Menggeser tanggal pulang mengikuti jumlah hari yang baru. */
    private function selaraskanTanggalPulang(): void
    {
        $mulai = trim((string) ($this->isian['tanggal_mulai'] ?? ''));

        if ($mulai === '') {
            return;
        }

        try {
            $this->isian['tanggal_pulang'] = \Carbon\Carbon::parse($mulai)
                ->addDays(max(1, (int) $this->isian['jumlah_hari']) - 1)
                ->toDateString();
        } catch (\Throwable) {
            // Tanggal yang belum selesai diketik bukan galat.
        }
    }

    /** Ke mana tombol Batal dan simpan yang berhasil mengembalikan admin. */
    public function tujuanKembali(): string
    {
        if ($this->ubah && $this->dari !== 'daftar' && $this->rabId) {
            return route('admin.orcha.rab.susun', $this->rabId);
        }

        return route('admin.orcha.rab');
    }

    public function simpan(): void
    {
        // Judul kosong berarti "pakai yang dirakit", bukan "belum diisi".
        $this->isian['judul'] = trim((string) $this->isian['judul']) ?: $this->judulOtomatis();

        $this->validate([
            'isian.judul' => 'required|string|min:3|max:150',
            'isian.nama_pelanggan' => 'required|string|min:2|max:120',
            'isian.whatsapp' => 'nullable|string|max:32',
            'isian.email' => 'nullable|email|max:150',
            'isian.provinsi' => 'required|string|max:80',
            'isian.daerah' => 'nullable|string|max:80',
            'isian.tanggal_mulai' => 'nullable|date',
            'isian.berlaku_sampai' => 'nullable|date',
            'isian.jumlah_hari' => 'required|integer|min:1|max:30',
            'isian.jumlah_malam' => 'required|integer|min:0|max:30|lte:isian.jumlah_hari',
            'isian.jumlah_peserta' => 'required|integer|min:1|max:500',
            // Peserta minimum hanya berarti pada open trip, dan tidak boleh
            // melebihi targetnya sendiri.
            'isian.peserta_minimum' => $this->openTrip()
                ? 'required|integer|min:1|max:500|lte:isian.jumlah_peserta'
                : 'nullable',
        ], [
            'isian.jumlah_malam.lte' => 'Jumlah malam tidak boleh melebihi jumlah hari.',
            'isian.peserta_minimum.lte' => 'Peserta minimum tidak boleh lebih banyak daripada targetnya.',
        ], [
            'isian.judul' => 'judul', 'isian.nama_pelanggan' => 'nama pelanggan',
            'isian.email' => 'email', 'isian.provinsi' => 'provinsi',
            'isian.jumlah_hari' => 'jumlah hari', 'isian.jumlah_peserta' => 'jumlah peserta',
            'isian.peserta_minimum' => 'peserta minimum',
        ]);

        /*
         | Memperpendek perjalanan ditolak bila masih ada kegiatan di hari yang
         | akan hilang. Membuangnya diam-diam berarti admin kehilangan susunan
         | hari keempat hanya karena salah ketik 3.
         */
        if ($this->hariTerpakai > (int) $this->isian['jumlah_hari']) {
            $this->addError('isian.jumlah_hari',
                "Masih ada kegiatan di hari ke-{$this->hariTerpakai}. Pindahkan atau hapus dulu.");

            return;
        }

        // Private trip tidak membawa isian open trip sama sekali: kolom yang
        // terisi tapi tak berarti akan dibaca sebagai keadaan nyata kelak.
        if (! $this->openTrip()) {
            $this->isian['peserta_minimum'] = null;
            $this->isian['harga_dasar'] = 'peserta';
        }

        /*
         | tanggal_pulang TIDAK ikut dikirim.
         |
         | Di Orcha ia diturunkan dari tanggal mulai + jumlah hari, bukan
         | disimpan. Mengirimnya berarti menitipkan angka kedua untuk hal
         | yang sama — dan yang kedua itulah yang akan berselisih begitu
         | jumlah harinya diubah lewat jalur lain.
         */
        $data = array_map(fn ($v) => is_string($v) ? (trim($v) ?: null) : $v,
            collect($this->isian)->except('tanggal_pulang')->all());

        try {
            if ($this->ubah) {
                $this->orcha()->ubah("/rab/{$this->rabId}", $data);
                \App\Support\HitunganOrcha::lupakanSemua();

                $this->dispatch('orcha-sukses-pindah',
                    message: 'Data perjalanan disimpan.',
                    url: $this->tujuanKembali());

                return;
            }

            // Titik awal yang masuk akal; diatur ulang di halaman penyusun.
            $baru = $this->orcha()->kirim('/rab', $data + [
                'margin_jenis' => 'persen',
                'margin_nilai' => 20,
            ])['data'] ?? null;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        $this->dispatch('orcha-sukses-pindah',
            message: 'RAB '.($baru['kode'] ?? '').' dibuat. Sekarang pilih destinasinya.',
            url: route('admin.orcha.rab.susun', $baru['id']));
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.rab.borang', [
            'provinsi' => $this->muatKatalog()['provinsi'] ?? [],
            'judulRakitan' => $this->judulOtomatis(),
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
