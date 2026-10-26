<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Daftar RAB & itinerary private trip, sekaligus pintu membuat yang baru.
 *
 * Membuat RAB hanya menanyakan yang dibutuhkan untuk mulai menghitung —
 * siapa, ke mana, berapa hari, berapa orang. Sisanya disusun di halaman
 * penyusun, tempat admin mengklik destinasi dan melihat harganya bergerak.
 */
class OrchaRabList extends Component
{
    use MemanggilOrcha;

    public bool $tambah = false;

    /**
     * Isian yang jarang diubah, disembunyikan sampai diminta.
     *
     * Judul, kota, dan jumlah malam semuanya punya jawaban yang hampir selalu
     * benar — dan tiga kotak yang hampir selalu diisi dengan jawaban yang sama
     * bukan pilihan, melainkan pekerjaan. Yang ingin lain tinggal membukanya.
     */
    public bool $rinci = false;

    public array $isian = [];

    private function kosongkan(): void
    {
        $this->isian = [
            'judul' => '',
            'nama_pelanggan' => '',
            'whatsapp' => '',
            'provinsi' => '',
            'daerah' => '',
            'tanggal_mulai' => '',
            'jumlah_hari' => 3,
            'jumlah_malam' => 2,
            'jumlah_peserta' => 10,
        ];
    }

    public function mount(): void
    {
        $this->kosongkan();
    }

    public function bukaTambah(): void
    {
        $this->tambah = true;
        $this->rinci = false;
        $this->kosongkan();
        $this->resetValidation();
    }

    public function tutup(): void
    {
        $this->tambah = false;
        $this->rinci = false;
        $this->kosongkan();
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
        $tujuan = trim((string) ($this->isian['daerah'] ?? '')) ?: trim((string) ($this->isian['provinsi'] ?? ''));
        $hari = max(1, (int) ($this->isian['jumlah_hari'] ?? 1));
        $malam = max(0, (int) ($this->isian['jumlah_malam'] ?? 0));

        return trim('Private Trip '.$tujuan.' '.$hari.'H'.$malam.'M');
    }

    /**
     * Malam mengikuti hari: 3 hari hampir selalu 2 malam. Admin yang ingin
     * lain tinggal mengubahnya, tetapi tidak perlu mengingat untuk mengubah.
     */
    public function updatedIsianJumlahHari($hari): void
    {
        $this->isian['jumlah_malam'] = max(0, (int) $hari - 1);
    }

    public function simpan(): void
    {
        // Judul kosong berarti "pakai yang dirakit", bukan "belum diisi".
        $this->isian['judul'] = trim((string) $this->isian['judul']) ?: $this->judulOtomatis();

        $this->validate([
            'isian.judul' => 'required|string|min:3|max:150',
            'isian.nama_pelanggan' => 'required|string|min:2|max:120',
            'isian.whatsapp' => 'nullable|string|max:32',
            'isian.provinsi' => 'required|string|max:80',
            'isian.daerah' => 'nullable|string|max:80',
            'isian.tanggal_mulai' => 'nullable|date',
            'isian.jumlah_hari' => 'required|integer|min:1|max:30',
            'isian.jumlah_malam' => 'required|integer|min:0|max:30|lte:isian.jumlah_hari',
            'isian.jumlah_peserta' => 'required|integer|min:1|max:500',
        ], [
            'isian.jumlah_malam.lte' => 'Jumlah malam tidak boleh melebihi jumlah hari.',
        ], [
            'isian.judul' => 'judul', 'isian.nama_pelanggan' => 'nama pelanggan', 'isian.provinsi' => 'provinsi',
            'isian.jumlah_hari' => 'jumlah hari', 'isian.jumlah_peserta' => 'jumlah peserta',
        ]);

        $data = array_map(fn ($v) => is_string($v) ? (trim($v) ?: null) : $v, $this->isian) + [
            // Titik awal yang masuk akal; diatur ulang di halaman penyusun.
            'margin_jenis' => 'persen',
            'margin_nilai' => 20,
        ];

        try {
            $baru = $this->orcha()->kirim('/rab', $data)['data'] ?? null;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        $this->dispatch('orcha-sukses-pindah',
            message: 'RAB '.($baru['kode'] ?? '').' dibuat. Sekarang pilih destinasinya.',
            url: route('admin.orcha.rab.susun', $baru['id']));
    }

    public function hapus(int $id): void
    {
        $this->hapusData("/rab/{$id}", 'RAB dihapus.');
    }

    public function render()
    {
        $hasil = $this->muat('/rab', $this->parameterDaftar());

        return view('livewire.pages.admin.orcha.rab.daftar', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'status' => $hasil['meta']['kosakata']['status'] ?? [],
            'provinsi' => $this->tambah ? ($this->muatKatalog()['provinsi'] ?? []) : [],
        ])->layout('livewire.layout.templateindex');
    }

    private function muatKatalog(): array
    {
        try {
            return $this->orcha()->ambil('/rab/katalog')['data'] ?? [];
        } catch (OrchaTidakTerjangkau) {
            return [];
        }
    }
}
