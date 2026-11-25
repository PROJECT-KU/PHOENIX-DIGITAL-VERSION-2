<?php

namespace App\Livewire\Pages\Admin\Orcha\Penyewaan;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Mencatat penyewaan yang disepakati lewat telepon atau WhatsApp.
 *
 * Sebelum halaman ini ada, penyewaan HANYA bisa lahir dari borang publik —
 * sehingga admin yang menerima pesanan lewat telepon terpaksa mengisikan
 * borang pelanggan atas namanya. Akibatnya pesanan tercatat dengan alamat IP
 * kantor, kena pembatas laju pengunjung umum, dan pelanggan menerima surel
 * pemesanan yang tidak pernah ia minta.
 *
 * Halaman tersendiri, bukan jendela di atas daftar: isiannya enam belas, dan
 * aturan tiap unit berbeda — ada yang tidak punya tarif per jam, ada yang
 * wajib bersopir. Semuanya perlu ruang untuk dibaca, bukan digulung di kotak
 * setinggi layar.
 *
 * Yang TIDAK dihitung di sini: harga, modal, dan tenggat pengembalian.
 * Ketiganya dihitung Orcha dari rumus unit yang sama dengan borang publik.
 * Menghitungnya ulang di lemon berarti dua rumus sejajar yang suatu saat
 * berbeda jawabannya — dan yang pertama ketahuan biasanya keuntungan yang
 * tidak pernah cocok.
 */
class OrchaSewaBaru extends Component
{
    use MemanggilOrcha;

    /**
     * Tanggal yang diklik di Kalender Kegiatan, lewat ?tanggal=YYYY-MM-DD.
     *
     * Berbeda dari pendaftaran rombongan: sewa TIDAK terikat jadwal
     * keberangkatan mana pun, jadi tanggalnya bisa langsung dipakai apa
     * adanya tanpa harus mencocokkannya dengan apa pun.
     */
    #[Url(as: 'tanggal', except: '')]
    public string $tanggalMulai = '';

    public string $unitId = '';

    public string $transmisi = '';

    public string $satuan = 'hari';

    public $durasi = 1;

    public string $jamMulai = '08:00';

    public string $denganSopir = 'ya';

    public bool $luarKota = false;

    public string $lokasiAntar = '';

    public string $lokasiKembali = '';

    public string $tujuan = '';

    public string $nama = '';

    public string $whatsapp = '';

    public string $email = '';

    public string $catatan = '';

    public string $status = 'baru';

    /** Terisi berarti layarnya sudah berpindah ke kabar berhasil. */
    public array $hasil = [];

    public function mount(): void
    {
        if ($this->tanggalMulai === '') {
            $this->tanggalMulai = now()->toDateString();
        }
    }

    /**
     * Seluruh unit yang bisa dipilih, apa adanya dari Orcha.
     *
     * Diambil lewat daftar armada, bukan lewat /rujukan: aturan tiap unit —
     * transmisi yang tersedia, boleh-tidaknya lepas kunci, satuan mana yang
     * bertarif — hanya ada di sana, dan tanpanya borang ini cuma bisa
     * menawarkan semua pilihan lalu dibantah Orcha setelah Simpan ditekan.
     *
     * @return array<int, array<string, mixed>>
     */
    public function daftarUnit(): array
    {
        return cache()->remember('orcha.unit-sewa', now()->addMinutes(5), function () {
            return rescue(
                fn () => $this->orcha()->ambil('/kendaraan', ['per_halaman' => 100])['data'] ?? [],
                null,
                false
            );
        }) ?? [];
    }

    /** @return array<string, mixed>|null */
    public function unitTerpilih(): ?array
    {
        if ($this->unitId === '') {
            return null;
        }

        foreach ($this->daftarUnit() as $unit) {
            if ((string) $unit['id'] === $this->unitId) {
                return $unit;
            }
        }

        return null;
    }

    /**
     * Pilihan yang tertinggal dari unit sebelumnya DIBUANG.
     *
     * Tanpa ini, berpindah dari Avanza (Manual & Matic, bertarif per jam) ke
     * bus (Manual saja, tanpa tarif per jam) meninggalkan "Matic" dan "jam"
     * yang masih terpilih — dan Orcha menolaknya setelah Simpan ditekan,
     * dengan galat yang tidak ada hubungannya dengan apa yang baru saja
     * dilakukan admin.
     */
    public function updatedUnitId(): void
    {
        $unit = $this->unitTerpilih();

        if (! $unit) {
            return;
        }

        $transmisi = $unit['transmisi_tersedia'] ?? [];
        if (! in_array($this->transmisi, $transmisi, true)) {
            $this->transmisi = $transmisi[0] ?? '';
        }

        if (! in_array($this->satuan, $this->satuanTersedia(), true)) {
            $this->satuan = $this->satuanTersedia()[0] ?? 'hari';
        }

        // Unit yang tidak boleh lepas kunci dipaksa bersopir di layar juga,
        // bukan cuma ditolak di server: pilihan yang pasti ditolak tidak
        // pantas ditawarkan.
        if (! ($unit['lepas_kunci'] ?? true)) {
            $this->denganSopir = 'ya';
        }
    }

    /**
     * Satuan yang benar-benar bertarif untuk unit ini.
     *
     * @return array<int, string>
     */
    public function satuanTersedia(): array
    {
        $unit = $this->unitTerpilih();

        if (! $unit) {
            return ['hari'];
        }

        return collect($unit['tarif'] ?? [])
            ->filter(fn ($nilai) => filled($nilai))
            ->keys()
            ->all() ?: ['hari'];
    }

    public function simpan(): void
    {
        $this->galat = '';

        $this->validate([
            'unitId' => 'required',
            'transmisi' => 'required',
            'satuan' => 'required',
            'durasi' => 'required|integer|min:1|max:30',
            'tanggalMulai' => 'required|date',
            'jamMulai' => 'required|date_format:H:i',
            'lokasiAntar' => 'required|string|min:4|max:191',
            'nama' => 'required|string|min:3|max:120',
            'whatsapp' => 'required|string|max:25',
            'email' => 'nullable|email|max:150',
            'catatan' => 'nullable|string|max:1000',
        ], [], [
            'unitId' => 'unit',
            'tanggalMulai' => 'tanggal mulai',
            'jamMulai' => 'jam mulai',
            'lokasiAntar' => 'lokasi antar',
        ]);

        try {
            $balasan = $this->orcha()->kirim('/penyewaan', [
                'car_id' => (int) $this->unitId,
                'nama' => $this->nama,
                'whatsapp' => $this->whatsapp,
                'email' => $this->email ?: null,
                'transmisi' => $this->transmisi,
                'satuan' => $this->satuan,
                'durasi' => (int) $this->durasi,
                'tanggal_mulai' => $this->tanggalMulai,
                'jam_mulai' => $this->jamMulai,
                'dengan_sopir' => $this->denganSopir === 'ya',
                'luar_kota' => $this->luarKota,
                'lokasi_antar' => $this->lokasiAntar,
                'lokasi_kembali' => $this->lokasiKembali ?: null,
                'tujuan' => $this->tujuan ?: null,
                'catatan' => $this->catatan ?: null,
                'status' => $this->status,
            ]);
        } catch (OrchaTidakTerjangkau $e) {
            // Pesannya ditampilkan apa adanya: Orcha kerap sudah menjelaskan
            // sendiri apa yang salah — "Unit ini tidak disewakan dengan satuan
            // jam" jauh lebih berguna daripada "gagal menyimpan".
            $this->galat = $e->getMessage();

            return;
        }

        $this->hasil = $balasan['data'] ?? [];
    }

    /** Mencatat satu lagi tanpa mengetik ulang keterangan unitnya. */
    public function lagi(): void
    {
        $this->hasil = [];
        $this->reset(['nama', 'whatsapp', 'email', 'catatan', 'tujuan', 'lokasiKembali']);
        $this->status = 'baru';
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.penyewaan.sewa-baru', [
            'daftarUnit' => $this->daftarUnit(),
            'unit' => $this->unitTerpilih(),
            'satuanPilihan' => $this->satuanTersedia(),
            'daftarStatus' => $this->rujukan('status_penyewaan'),
        ])->layout('livewire.layout.templateindex');
    }
}
