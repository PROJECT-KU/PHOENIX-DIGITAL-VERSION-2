<?php

namespace App\Livewire\Pages\Admin\Orcha\Pendaftaran;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Riwayat kesehatan seluruh peserta satu pendaftaran.
 *
 * Halaman tersendiri, bukan popup. Isinya panjang — satu kartu per peserta,
 * masing-masing dengan riwayat penyakit, alergi, obat rutin, dan kontak
 * darurat — dan rombongan dua belas orang tidak muat di jendela yang
 * separuhnya sudah terpakai bingkai.
 *
 * Datanya sensitif: hanya diambil saat halaman ini dibuka, dan setiap
 * pembukaannya tercatat di sisi Orcha beserta akun yang membukanya.
 */
class OrchaRiwayatKesehatan extends Component
{
    use MemanggilOrcha;

    public int $pendaftaranId;

    public array $data = [];

    public array $riwayat = [];

    public function mount(int $pendaftaran): void
    {
        // Penjagaan sebenarnya ada di sini, bukan di tombol yang disembunyikan.
        abort_unless(auth()->user()->hasPermission('view_orcha_kesehatan'), 403);

        $this->pendaftaranId = $pendaftaran;
        $this->data = $this->muat("/pendaftaran/{$pendaftaran}")['data'] ?? [];

        if ($this->galat === '') {
            $this->riwayat = $this->muat("/pendaftaran/{$pendaftaran}/riwayat-kesehatan")['data'] ?? [];
        }
    }

    /**
     * Jumlah peserta menurut tingkat perhatiannya.
     *
     * Tiga tingkat, bukan dua: "tinggi" menuntut kesiapan sebelum berangkat,
     * "sedang" cukup diingat di lapangan. Kalau semuanya ditandai merah,
     * penandanya berhenti berarti.
     *
     * @return array{tinggi: int, sedang: int, aman: int}
     */
    public function hitungTingkat(): array
    {
        $hitung = ['tinggi' => 0, 'sedang' => 0, 'aman' => 0];

        foreach ($this->riwayat as $satu) {
            // Peserta yang sudah diganti tidak ikut dihitung — ia arsip, bukan
            // orang yang akan berangkat.
            if (($satu['peserta_aktif'] ?? true) === false) {
                continue;
            }

            $tingkat = $satu['tingkat_perhatian']
                ?? (($satu['ada_catatan_khusus'] ?? false) ? 'tinggi' : 'aman');

            $hitung[$tingkat] = ($hitung[$tingkat] ?? 0) + 1;
        }

        return $hitung;
    }

    /**
     * Memisahkan riwayat yang berlaku dari yang tinggal arsip.
     *
     * Keduanya sempat berjajar di satu daftar, dibedakan hanya oleh sebaris
     * penanda di atas kartunya. Kartunya sendiri tetap selengkap milik peserta
     * yang berangkat — alergi, obat rutin, kontak darurat — dan tim lapangan
     * yang membacanya sambil berjalan tidak punya cara membedakan siapa yang
     * benar-benar ada di kendaraan.
     *
     * @return array{aktif: array<int, array<string, mixed>>, arsip: array<int, array<string, mixed>>}
     */
    public function pilah(): array
    {
        $kelompok = collect($this->riwayat)
            ->groupBy(fn ($satu) => ($satu['peserta_aktif'] ?? true) === false ? 'arsip' : 'aktif');

        return [
            'aktif' => $kelompok->get('aktif', collect())->values()->all(),
            'arsip' => $kelompok->get('arsip', collect())->values()->all(),
        ];
    }

    /**
     * Siapa menggantikan siapa, dalam huruf kecil, untuk dicocokkan dengan nama
     * pada riwayat kesehatan.
     *
     * Kartu arsip yang cuma berkata "sudah digantikan" memaksa admin membuka
     * halaman detail untuk tahu digantikan oleh siapa — dua halaman untuk satu
     * pertanyaan yang jawabannya sudah ada di tangan.
     *
     * @return array<string, string>
     */
    public function penggantiPer(): array
    {
        return collect($this->data['riwayat_penggantian'] ?? [])
            ->filter(fn ($satu) => filled($satu['dari'] ?? null) && filled($satu['ke'] ?? null))
            ->mapWithKeys(fn ($satu) => [mb_strtolower(trim($satu['dari'])) => trim($satu['ke'])])
            ->all();
    }

    /**
     * Peserta pengganti yang riwayat kesehatannya belum masuk.
     *
     * Ini akibat langsung penggantian, dan yang paling mudah terlewat: orang
     * lamanya sudah mengisi, jadi rombongan terlihat lengkap sampai ada yang
     * menghitung ulang. Padahal yang berangkat orang baru, dan riwayat
     * kesehatannya belum ada sama sekali.
     *
     * @return array<int, string>
     */
    public function penggantiBelumIsi(): array
    {
        /*
         | Dicocokkan dengan daftar "belum isi" milik Orcha, bukan dengan nama
         | pada riwayat kesehatan.
         |
         | Percobaan pertama membandingkan nama pengganti langsung ke nama di
         | riwayat kesehatan, dan meleset: admin mengetik "wildan" di daftar
         | peserta, sedangkan yang bersangkutan mengisi formulir dengan nama
         | lengkapnya, "Wildan Prasetyo". Keduanya orang yang sama, tetapi
         | perbandingan persis menyatakan ia belum mengisi — lalu halaman ini
         | menyuruh admin menagih orang yang sudah menurut.
         |
         | Orcha sudah menghitungnya sekali lewat peserta_belum_isi. Memakai
         | angka itu berarti kedua tempat selalu sepakat; membuat aturan
         | pencocokan kedua di sini berarti mengundang keduanya berbeda pendapat
         | tanpa ada yang tahu mana yang benar.
         */
        $belumIsi = collect($this->data['peserta_belum_isi'] ?? [])
            ->map(fn ($nama) => mb_strtolower(trim((string) $nama)))
            ->all();

        if ($belumIsi === []) {
            return [];
        }

        $sudahDigantiLagi = $this->penggantiPer();

        return collect($this->data['riwayat_penggantian'] ?? [])
            ->pluck('ke')
            ->filter()
            ->map(fn ($nama) => trim($nama))
            // Nama yang belakangan ikut diganti lagi tidak dihitung: yang
            // ditunggu riwayatnya cuma orang yang benar-benar jadi berangkat.
            ->reject(fn ($nama) => array_key_exists(mb_strtolower($nama), $sudahDigantiLagi))
            ->filter(fn ($nama) => in_array(mb_strtolower($nama), $belumIsi, true))
            ->unique()
            ->values()
            ->all();
    }

    public function render()
    {
        $pilah = $this->pilah();

        return view('livewire.pages.admin.orcha.pendaftaran.kesehatan', [
            'pendaftaran' => $this->data,
            'tingkat' => $this->hitungTingkat(),
            'aktif' => $pilah['aktif'],
            'arsip' => $pilah['arsip'],
            'penggantiPer' => $this->penggantiPer(),
            'penggantiBelumIsi' => $this->penggantiBelumIsi(),
        ])->layout('livewire.layout.templateindex');
    }
}
