<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Hari libur nasional: tanggal merah yang dihargai seperti akhir pekan.
 *
 * Sabtu & Minggu TIDAK didaftarkan di sini — keduanya terbaca sendiri dari
 * tanggalnya. Yang didaftarkan hanya libur yang tidak bisa dihitung: 17
 * Agustus, Idul Fitri, Nyepi, cuti bersama.
 *
 * Layar yang paling mudah dilupakan di seluruh Orcha: diisi sekali setahun,
 * dan kalau tidak diisi tidak ada apa pun yang rusak — tanggal merahnya
 * hanya dihargai tarif hari kerja, dan selisihnya baru ketahuan saat tagihan
 * vendornya datang. Karena itu layar ini menyebutkan akibatnya, bukan
 * sekadar menampilkan daftar.
 */
class OrchaHariLiburList extends Component
{
    use MemanggilOrcha;

    #[Url(as: 'tahun', except: '')]
    public string $filterTahun = '';

    /** Formulir satu tanggal; null berarti tertutup. */
    public bool $tambah = false;

    public ?int $sunting = null;

    public array $isian = ['tanggal' => '', 'nama' => ''];

    /** Panel tempel banyak tanggal sekaligus. */
    public bool $bukaTempel = false;

    public string $tempel = '';

    /**
     * Libur yang sering dipakai, sebagai pengisi cepat.
     *
     * Tanggalnya TIDAK ditebak: Idul Fitri dan Nyepi bergeser tiap tahun,
     * dan menebakkannya berarti menuliskan tanggal yang salah dengan yakin.
     * Yang ditawarkan cuma namanya, tanggalnya tetap diisi admin.
     */
    public const NAMA_SERING = [
        'Tahun Baru', 'Idul Fitri', 'Cuti bersama', 'HUT RI',
        'Natal', 'Nyepi', 'Waisak', 'Isra Mikraj',
    ];

    public function mount(): void
    {
        $this->kosongkan();
    }

    private function kosongkan(): void
    {
        $this->isian = ['tanggal' => '', 'nama' => ''];
    }

    public function updatedFilterTahun(): void
    {
        $this->halaman = 1;
    }

    public function bukaTambah(): void
    {
        $this->sunting = null;
        $this->tambah = true;
        $this->bukaTempel = false;
        $this->kosongkan();
        $this->resetValidation();
    }

    public function bukaSunting(int $id, array $baris): void
    {
        $this->tambah = false;
        $this->sunting = $id;
        $this->bukaTempel = false;
        $this->isian = ['tanggal' => $baris['tanggal'], 'nama' => $baris['nama']];
        $this->resetValidation();
    }

    public function tutup(): void
    {
        $this->tambah = false;
        $this->sunting = null;
        $this->bukaTempel = false;
        $this->tempel = '';
        $this->kosongkan();
        $this->resetValidation();
    }

    public function pakaiNama(string $nama): void
    {
        $this->isian['nama'] = $nama;
        $this->resetValidation('isian.nama');
    }

    public function simpan(): void
    {
        $this->validate([
            'isian.tanggal' => 'required|date',
            'isian.nama' => 'required|string|min:2|max:120',
        ], [
            'isian.tanggal.required' => 'Pilih tanggalnya dulu.',
            'isian.nama.required' => 'Tulis nama liburnya — "Cuti bersama" saja sudah cukup.',
        ], [
            'isian.tanggal' => 'tanggal', 'isian.nama' => 'nama',
        ]);

        $data = ['tanggal' => $this->isian['tanggal'], 'nama' => trim($this->isian['nama'])];

        try {
            $balasan = $this->sunting
                ? $this->orcha()->ubah("/hari-libur/{$this->sunting}", $data)
                : $this->orcha()->kirim('/hari-libur', $data);

            $this->tutup();
            // Pesan dari Orcha, bukan kalimat sendiri: yang menghitung
            // berapa harga terpengaruh hanya peladen, dan dua kalimat untuk
            // satu kejadian akan berselisih begitu salah satunya diubah.
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Tanggal disimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function hapus(int $id): void
    {
        $this->hapusData("/hari-libur/{$id}", 'Hari libur dihapus.');
    }

    /**
     * Menempelkan banyak tanggal sekaligus.
     *
     * Satu baris satu tanggal, dipisah tab/koma/spasi dari namanya:
     * "2027-08-17  HUT RI". Mengisi 16 tanggal merah satu per satu tiap awal
     * tahun adalah pekerjaan yang karena itu tidak dikerjakan.
     */
    public function kirimTempel(): void
    {
        $baris = [];
        $salah = [];

        foreach (preg_split('/\r?\n/', $this->tempel) as $n => $teks) {
            $teks = trim($teks);

            if ($teks === '') {
                continue;
            }

            // Pemisahnya tab, koma, titik koma, atau dua spasi — apa saja
            // yang keluar dari tempelan spreadsheet.
            $bagian = preg_split('/\t|,|;|\s{2,}/', $teks, 2);
            $tanggal = trim($bagian[0] ?? '');
            $nama = trim($bagian[1] ?? '');

            if ($tanggal === '' || $nama === '' || strtotime($tanggal) === false) {
                $salah[] = $n + 1;

                continue;
            }

            $baris[] = ['tanggal' => date('Y-m-d', strtotime($tanggal)), 'nama' => mb_substr($nama, 0, 120)];
        }

        if ($baris === []) {
            $this->addError('tempel', $salah === []
                ? 'Belum ada yang ditempel.'
                : 'Tidak ada baris yang bisa dibaca. Bentuknya: tanggal, lalu namanya — misal "2027-08-17  HUT RI".');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim('/hari-libur/banyak', ['baris' => $baris]);

            $this->tempel = '';
            $this->bukaTempel = false;

            $pesan = $balasan['pesan'] ?? 'Tanggal ditambahkan.';
            // Baris yang tidak terbaca disebut nomornya, bukan ditelan:
            // tempelan yang "berhasil" padahal tiga barisnya hilang adalah
            // tanggal merah yang tidak pernah terdaftar.
            $this->dispatch('order-updated', message: $salah === []
                ? $pesan
                : $pesan.' Baris '.implode(', ', $salah).' tidak terbaca dan dilewati.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function bersihkanSaringan(): void
    {
        $this->cari = '';
        $this->filterTahun = '';
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->filterTahun !== '';
    }

    public function render()
    {
        $balasan = $this->muat('/hari-libur', array_filter([
            'tahun' => $this->filterTahun,
        ], fn ($n) => $n !== ''));

        $daftar = $balasan['data'] ?? [];
        $meta = $balasan['meta'] ?? [];

        return view('livewire.pages.admin.orcha.rab.hari-libur', [
            'daftar' => $daftar,
            'tahunAda' => $meta['tahun'] ?? [],
            'hargaTerpengaruh' => (int) ($meta['harga_terpengaruh'] ?? 0),
            'namaSering' => self::NAMA_SERING,
        ])->layout('livewire.layout.templateindex');
    }
}
