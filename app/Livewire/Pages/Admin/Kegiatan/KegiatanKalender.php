<?php

namespace App\Livewire\Pages\Admin\Kegiatan;

use App\Mail\UndanganKegiatanMail;
use App\Models\Kegiatan;
use App\Models\User;
use App\Support\KabarKegiatan;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Kalender kegiatan satu bulan penuh.
 *
 * Sengaja satu komponen, bukan trio List/Create/Edit: pada kalender, menambah
 * kegiatan hampir selalu berawal dari "klik tanggal ini". Melempar orang ke
 * halaman lain memutus konteks tanggal yang baru saja ia pilih, jadi formnya
 * tinggal di modal pada halaman yang sama.
 */
class KegiatanKalender extends Component
{
    /**
     * Berapa balok kegiatan yang muat dalam satu sel sebelum sisanya diringkas.
     *
     * Tiga, bukan sebanyak-banyaknya: sel yang meninggi mengikuti hari tersibuk
     * membuat seluruh kisi ikut meninggi, dan satu bulan tidak lagi terlihat
     * sekaligus — padahal itulah gunanya tampilan bulanan.
     */
    public const LAJUR_MAKS = 3;

    /** Bulan & tahun yang sedang ditampilkan. */
    public int $bulan;

    public int $tahun;

    /** Saring menurut jenis kegiatan; kosong = semua. */
    public string $saringJenis = '';

    /** Tampilkan hanya kegiatan yang saya ikuti / saya buat. */
    public bool $hanyaSaya = false;

    /** Tanggal yang sedang dibuka daftarnya (Y-m-d), kosong = belum ada. */
    public string $tanggalTerpilih = '';

    // ===== Form =====
    public bool $formTampil = false;

    public ?string $formId = null;

    public string $judul = '';

    public string $deskripsi = '';

    public string $jenis = 'rapat';

    public string $lokasi = '';

    public string $tanggalMulai = '';

    public string $jamMulai = '09:00';

    public string $tanggalSelesai = '';

    public string $jamSelesai = '';

    public bool $seharian = false;

    /** @var array<int> */
    public array $peserta = [];

    protected $queryString = [
        'bulan' => ['except' => ''],
        'tahun' => ['except' => ''],
        'saringJenis' => ['except' => ''],
        'hanyaSaya' => ['except' => false],
        // Ikut di URL supaya satu hari tertentu bisa dikirim sebagai tautan.
        'tanggalTerpilih' => ['except' => ''],
    ];

    public function mount(): void
    {
        $kini = now();
        $this->bulan = (int) $kini->month;
        $this->tahun = (int) $kini->year;
    }

    // ===== Navigasi bulan =====

    private function acuan(): Carbon
    {
        return Carbon::create($this->tahun, $this->bulan, 1)->startOfDay();
    }

    public function bulanSebelumnya(): void
    {
        $t = $this->acuan()->subMonthNoOverflow();
        $this->bulan = (int) $t->month;
        $this->tahun = (int) $t->year;
        $this->tanggalTerpilih = '';
    }

    public function bulanBerikutnya(): void
    {
        $t = $this->acuan()->addMonthNoOverflow();
        $this->bulan = (int) $t->month;
        $this->tahun = (int) $t->year;
        $this->tanggalTerpilih = '';
    }

    public function keHariIni(): void
    {
        $kini = now();
        $this->bulan = (int) $kini->month;
        $this->tahun = (int) $kini->year;
        $this->tanggalTerpilih = $kini->toDateString();
    }

    public function pilihTanggal(string $tanggal): void
    {
        $this->tanggalTerpilih = $this->tanggalTerpilih === $tanggal ? '' : $tanggal;
    }

    // ===== Izin =====

    private function boleh(string $izin): bool
    {
        return (bool) auth()->user()?->hasPermission($izin);
    }

    public function getBolehTambahProperty(): bool
    {
        return $this->boleh('create_kegiatan');
    }

    public function getBolehUbahProperty(): bool
    {
        return $this->boleh('edit_kegiatan');
    }

    public function getBolehHapusProperty(): bool
    {
        return $this->boleh('delete_kegiatan');
    }

    /**
     * Agenda Orcha hanya untuk yang memang berwenang melihatnya.
     *
     * Izin yang sama dengan seluruh halaman /admin/orcha/*. Tanpa pagar ini,
     * karyawan yang cuma boleh melihat jadwal rapat akan ikut tahu siapa
     * memesan apa, berapa orang, dan dijemput di mana — data pelanggan yang
     * tidak ada urusannya dengan pekerjaannya.
     */
    public function getBolehLihatOrchaProperty(): bool
    {
        return $this->boleh('akses_orcha');
    }

    // ===== Form =====

    public function buatBaru(?string $tanggal = null): void
    {
        if (! $this->bolehTambah) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menambah kegiatan.');

            return;
        }

        $this->bersihkanForm();

        // Tanggal awal mengikuti tanggal yang diklik; kalau tidak ada, hari ini
        // bila bulannya sedang tampil, selain itu tanggal 1 bulan tersebut —
        // supaya tanggal di form tidak pernah melompat keluar dari yang dilihat.
        $this->tanggalMulai = $tanggal
            ?: ($this->tanggalTerpilih ?: $this->tanggalBawaan());

        $this->formTampil = true;
    }

    private function tanggalBawaan(): string
    {
        $kini = now();

        return ((int) $kini->month === $this->bulan && (int) $kini->year === $this->tahun)
            ? $kini->toDateString()
            : $this->acuan()->toDateString();
    }

    public function sunting(string $id): void
    {
        if (! $this->bolehUbah) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah kegiatan.');

            return;
        }

        $k = Kegiatan::with('peserta')->findOrFail($id);

        $this->formId = $k->id;
        $this->judul = $k->judul;
        $this->deskripsi = (string) $k->deskripsi;
        $this->jenis = $k->jenis;
        $this->lokasi = (string) $k->lokasi;
        $this->seharian = (bool) $k->seharian;
        $this->tanggalMulai = $k->mulai->toDateString();
        $this->jamMulai = $k->mulai->format('H:i');
        $this->tanggalSelesai = $k->selesai?->toDateString() ?? '';
        $this->jamSelesai = $k->selesai?->format('H:i') ?? '';
        $this->peserta = $k->peserta->pluck('id')->all();

        $this->resetValidation();
        $this->formTampil = true;
    }

    public function tutupForm(): void
    {
        $this->formTampil = false;
        $this->bersihkanForm();
    }

    private function bersihkanForm(): void
    {
        $this->formId = null;
        $this->judul = '';
        $this->deskripsi = '';
        $this->jenis = 'rapat';
        $this->lokasi = '';
        $this->tanggalMulai = '';
        $this->jamMulai = '09:00';
        $this->tanggalSelesai = '';
        $this->jamSelesai = '';
        $this->seharian = false;
        $this->peserta = [];
        $this->resetValidation();
    }

    protected function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'jenis' => ['required', 'in:'.implode(',', array_keys(Kegiatan::JENIS))],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'tanggalMulai' => ['required', 'date'],
            'jamMulai' => [$this->seharian ? 'nullable' : 'required', 'date_format:H:i'],
            'tanggalSelesai' => ['nullable', 'date'],
            'jamSelesai' => ['nullable', 'date_format:H:i'],
            'peserta' => ['array'],
            'peserta.*' => ['integer', 'exists:users,id'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'judul' => 'judul kegiatan',
            'tanggalMulai' => 'tanggal mulai',
            'jamMulai' => 'jam mulai',
            'tanggalSelesai' => 'tanggal selesai',
            'jamSelesai' => 'jam selesai',
        ];
    }

    public function simpan(): void
    {
        $ubah = (bool) $this->formId;
        $izin = $ubah ? 'edit_kegiatan' : 'create_kegiatan';

        if (! $this->boleh($izin)) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menyimpan kegiatan.');

            return;
        }

        $this->validate();

        $mulai = $this->seharian
            ? Carbon::parse($this->tanggalMulai)->startOfDay()
            : Carbon::parse($this->tanggalMulai.' '.$this->jamMulai);

        $selesai = $this->hitungSelesai($mulai);

        if ($selesai && $selesai->lessThanOrEqualTo($mulai)) {
            $this->addError('jamSelesai', 'Waktu selesai harus setelah waktu mulai.');

            return;
        }

        $data = [
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi ?: null,
            'jenis' => $this->jenis,
            'lokasi' => $this->lokasi ?: null,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'seharian' => $this->seharian,
        ];

        $pesertaBaru = array_values(array_unique(array_map('intval', $this->peserta)));

        if ($this->formId) {
            $k = Kegiatan::findOrFail($this->formId);

            // Direkam SEBELUM update: sesudahnya, model sudah memuat nilai baru
            // dan tidak ada lagi yang bisa dibandingkan.
            $sebelum = $k->only(KabarKegiatan::RINCIAN_PENTING);
            $pesertaLama = $k->peserta()->pluck('users.id')->map('intval')->all();

            $k->update($data);
        } else {
            $k = Kegiatan::create($data + ['dibuat_oleh' => auth()->id()]);
            $sebelum = null;
            $pesertaLama = [];
        }

        $k->peserta()->sync($pesertaBaru);

        $dikirimi = $this->kabari($k, $pesertaLama, $pesertaBaru, $sebelum);

        // Kalender melompat ke bulan kegiatannya. Tanpa ini, menyimpan kegiatan
        // bulan depan terlihat seperti gagal menyimpan: tidak muncul di mana pun.
        $this->bulan = (int) $mulai->month;
        $this->tahun = (int) $mulai->year;
        $this->tanggalTerpilih = $mulai->toDateString();

        $this->formTampil = false;
        $this->bersihkanForm();

        $pesan = $ubah ? 'Kegiatan berhasil diperbarui.' : 'Kegiatan berhasil ditambahkan.';

        // Jumlah surel yang benar-benar keluar ikut dikatakan. Tanpa ini,
        // "berhasil ditambahkan" terucap sama saja entah tujuh peserta
        // dikabari atau tidak seorang pun — dan kegagalan kirim hanya
        // ketahuan berhari-hari kemudian, saat rapatnya sepi.
        if ($dikirimi > 0) {
            $pesan .= ' Undangan dikirim ke '.$dikirimi.' peserta.';
        }

        $this->dispatch('swal-success', message: $pesan);
    }

    /**
     * Kabari peserta sesuai apa yang benar-benar berubah bagi masing-masing.
     *
     * Dipisah dari simpan() karena aturannya punya alasannya sendiri dan akan
     * terus tumbuh; menyelipkannya di tengah alur penyimpanan membuat keduanya
     * sulit dibaca sekaligus sulit diuji.
     *
     * @param  array<int>  $lama
     * @param  array<int>  $baru
     * @param  array<string, mixed>|null  $sebelum  null bila kegiatannya baru
     * @return int jumlah alamat yang benar-benar dikirimi
     */
    private function kabari(Kegiatan $k, array $lama, array $baru, ?array $sebelum): int
    {
        $pelaku = auth()->user();

        $masuk = array_diff($baru, $lama);
        $keluar = array_diff($lama, $baru);
        $tetap = array_intersect($baru, $lama);

        $dikirimi = 0;

        // Peserta baru selalu diundang, apa pun yang berubah pada kegiatannya.
        if ($masuk) {
            $dikirimi += KabarKegiatan::kirim($k, $masuk, UndanganKegiatanMail::UNDANGAN, $pelaku);
        }

        if ($keluar) {
            $dikirimi += KabarKegiatan::kirim($k, $keluar, UndanganKegiatanMail::DIKELUARKAN, $pelaku);
        }

        // Peserta lama hanya dikabari bila yang berubah menyangkut kehadirannya.
        if ($tetap && $sebelum !== null && KabarKegiatan::perluDikabarkan($k, $sebelum)) {
            $dikirimi += KabarKegiatan::kirim($k, $tetap, UndanganKegiatanMail::PERUBAHAN, $pelaku);
        }

        return $dikirimi;
    }

    private function hitungSelesai(Carbon $mulai): ?Carbon
    {
        if ($this->seharian) {
            return $this->tanggalSelesai
                ? Carbon::parse($this->tanggalSelesai)->endOfDay()
                : null;
        }

        if (! $this->jamSelesai) {
            return null;
        }

        // Tanggal selesai boleh kosong untuk kegiatan yang berakhir di hari yang
        // sama — kasus paling sering, dan mengisinya dua kali hanya menambah kerja.
        $tanggal = $this->tanggalSelesai ?: $this->tanggalMulai;

        return Carbon::parse($tanggal.' '.$this->jamSelesai);
    }

    public function hapus(string $id): void
    {
        if (! $this->bolehHapus) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus kegiatan.');

            return;
        }

        $k = Kegiatan::findOrFail($id);

        // Dikumpulkan sebelum dihapus: setelah delete, baris pivotnya ikut
        // hilang dan tidak ada lagi yang bisa dikabari.
        $peserta = $k->peserta()->pluck('users.id')->all();

        $k->delete();

        // $k masih utuh di memori, jadi suratnya tetap bisa menyebut rincian
        // kegiatan yang baru saja dihapus.
        KabarKegiatan::kirim($k, $peserta, UndanganKegiatanMail::PEMBATALAN, auth()->user());

        $this->dispatch('swal-success', message: 'Kegiatan berhasil dihapus.');
    }

    // ===== Menyusun balok kegiatan =====

    /**
     * Kegiatan yang berlangsung pada satu tanggal — bukan yang MULAI hari itu.
     *
     * @param  \Illuminate\Support\Collection<int, Kegiatan>  $kegiatan
     * @return \Illuminate\Support\Collection<int, Kegiatan>
     */
    private function padaTanggal($kegiatan, string $tanggal)
    {
        $hari = Carbon::parse($tanggal);

        return $kegiatan
            ->filter(fn (Kegiatan $k) => $k->mulai->lte($hari->copy()->endOfDay())
                && $k->akhirEfektif()->gte($hari->copy()->startOfDay()))
            ->sortBy(fn (Kegiatan $k) => $k->mulai->timestamp)
            ->values();
    }

    /**
     * Menyusun kegiatan satu minggu menjadi balok-balok yang membentang.
     *
     * Kegiatan 8–9 September harus tampil sebagai SATU balok utuh melintasi dua
     * kolom, bukan dua potongan terpisah — mata membaca satu batang panjang
     * sebagai satu urusan, dan dua kotak kecil sebagai dua urusan.
     *
     * Baloknya ditumpuk dalam "lajur": balok yang tanggalnya beririsan tidak
     * boleh berbagi lajur, karena akan saling menimpa. Yang mulai lebih dulu
     * dan yang paling panjang mendapat lajur teratas, supaya batang terpanjang
     * tidak terpotong-potong di bawah batang pendek.
     *
     * @param  \Illuminate\Support\Collection<int, Kegiatan>  $kegiatan
     * @return array{balok: array<int, array<string, mixed>>, lebih: array<int, int>, tepi: array<int, ?array{warna: string, lembut: string}>}
     */
    private function susunBalok($kegiatan, Carbon $awalMinggu): array
    {
        // Kolom dicari lewat peta tanggal, bukan hitung selisih hari: peta tidak
        // bisa meleset karena pergantian zona waktu atau pembulatan pecahan hari.
        $kolomHari = [];
        for ($i = 0; $i < 7; $i++) {
            $kolomHari[$awalMinggu->copy()->addDays($i)->toDateString()] = $i;
        }

        $awalHari = $awalMinggu->copy()->startOfDay();
        $akhirHari = $awalMinggu->copy()->addDays(6)->endOfDay();

        $dalamMinggu = $kegiatan
            ->filter(fn (Kegiatan $k) => $k->mulai->lte($akhirHari) && $k->akhirEfektif()->gte($awalHari))
            ->sortBy([
                fn (Kegiatan $a, Kegiatan $b) => $a->mulai->timestamp <=> $b->mulai->timestamp,
                // Yang lebih panjang lebih dulu bila mulainya sama.
                fn (Kegiatan $a, Kegiatan $b) => $b->akhirEfektif()->timestamp <=> $a->akhirEfektif()->timestamp,
            ])
            ->values();

        $lajur = [];      // lajur => daftar [kolomAwal, kolomAkhir] yang sudah terpakai
        $balok = [];
        $lebih = array_fill(0, 7, 0);

        foreach ($dalamMinggu as $k) {
            $mulaiKolom = $kolomHari[$k->mulai->toDateString()] ?? 0;
            $akhirKolom = $kolomHari[$k->akhirEfektif()->toDateString()] ?? 6;

            $indeks = $this->lajurKosong($lajur, $mulaiKolom, $akhirKolom);
            $lajur[$indeks][] = [$mulaiKolom, $akhirKolom];

            if ($indeks >= self::LAJUR_MAKS) {
                // Tidak muat: dihitung sebagai "+N lagi" pada setiap hari yang dilewatinya.
                for ($c = $mulaiKolom; $c <= $akhirKolom; $c++) {
                    $lebih[$c]++;
                }

                continue;
            }

            $balok[] = [
                'kegiatan' => $k,
                'kolom' => $mulaiKolom,
                'rentang' => $akhirKolom - $mulaiKolom + 1,
                'lajur' => $indeks,
                // Ujung yang terpotong batas minggu dibuat rata, bukan membulat:
                // ujung membulat berarti "selesai di sini", dan itu bohong.
                'sambungKiri' => $k->mulai->lt($awalHari),
                'sambungKanan' => $k->akhirEfektif()->gt($akhirHari),
            ];
        }

        return ['balok' => $balok, 'lebih' => $lebih, 'tepi' => $this->tepiHari($balok)];
    }

    /**
     * Warna bingkai tiap sel hari.
     *
     * Batang saja belum cukup membuat 8-9 September terbaca sebagai SATU blok:
     * di antara keduanya masih ada dinding sel yang memisahkan. Dengan bingkai
     * selnya ikut berwarna, kedua hari itu terlihat menyatu, dan hari yang
     * tidak dipakai kegiatan apa pun tetap bersih.
     *
     * Bila satu hari dipakai beberapa kegiatan, yang menang adalah lajur
     * teratas — kegiatan yang paling dulu mulai dan paling panjang. Mencampur
     * warna hanya menghasilkan warna yang bukan milik siapa-siapa.
     *
     * @param  array<int, array<string, mixed>>  $balok
     * @return array<int, ?array{warna: string, lembut: string}>
     */
    private function tepiHari(array $balok): array
    {
        $tepi = array_fill(0, 7, null);
        $lajurTerpilih = array_fill(0, 7, PHP_INT_MAX);

        foreach ($balok as $b) {
            for ($c = $b['kolom']; $c < $b['kolom'] + $b['rentang']; $c++) {
                if ($b['lajur'] >= $lajurTerpilih[$c]) {
                    continue;
                }

                $lajurTerpilih[$c] = $b['lajur'];
                $tepi[$c] = [
                    'warna' => $b['kegiatan']->warnaBalok(),
                    'lembut' => $b['kegiatan']->pucatBalok(),
                ];
            }
        }

        return $tepi;
    }

    /**
     * Lajur pertama yang kolomnya belum terpakai pada rentang ini.
     *
     * @param  array<int, array<int, array{0: int, 1: int}>>  $lajur
     */
    private function lajurKosong(array $lajur, int $mulai, int $akhir): int
    {
        $indeks = 0;

        while (true) {
            $bentrok = false;

            foreach ($lajur[$indeks] ?? [] as [$a, $b]) {
                if ($mulai <= $b && $akhir >= $a) {
                    $bentrok = true;
                    break;
                }
            }

            if (! $bentrok) {
                return $indeks;
            }

            $indeks++;
        }
    }

    // ===== Tampilan =====

    #[Layout('livewire.layout.templateindex')]
    /**
     * Agenda Orcha, diubah jadi objek Kegiatan yang TIDAK disimpan.
     *
     * Dibuat sebagai Kegiatan, bukan larik biasa, karena seluruh mesin
     * kalender di bawah — susunBalok(), tepiHari(), padaTanggal() — bekerja
     * dengan objek itu dan memanggil mulai, akhirEfektif(), warnaBalok().
     * Merakitnya sebagai bentuk lain berarti menulis ulang semuanya dalam dua
     * versi sejajar yang suatu saat akan berbeda jawabannya.
     *
     * @return \Illuminate\Support\Collection<int, Kegiatan>
     */
    private function agendaOrcha(Carbon $awal, Carbon $akhir, ?string &$galat): \Illuminate\Support\Collection
    {
        $baris = app(\App\Services\OrchaClient::class)
            ->kalender($awal->toDateString(), $akhir->toDateString(), $galat);

        return collect($baris)
            ->map(function (array $b) {
                $jenis = match ($b['sumber'] ?? '') {
                    'penyewaan' => 'orcha_sewa',
                    // RAB yang sudah disetujui: tanggalnya terpakai, tetapi
                    // pendaftarannya belum ada. Dibedakan warnanya supaya
                    // "sudah deal" dan "sudah bayar" tidak terbaca sama.
                    'rab' => 'orcha_deal',
                    // Jadwal yang sedang dijual: belum deal, belum ada yang
                    // bayar. Rencana, bukan komitmen.
                    //
                    // 'rab_jual' ikut ke sini, BUKAN ke orcha_deal: RAB open
                    // trip pelanggannya "Umum" dan tidak ada yang
                    // berkomitmen — yang disetujui cuma hitungan harganya.
                    'katalog', 'rab_jual' => 'orcha_jual',
                    default => 'orcha_trip',
                };

                $k = new Kegiatan([
                    'judul' => $this->judulOrcha($b),
                    'deskripsi' => $this->rincianOrcha($b),
                    'jenis' => $jenis,
                    // ?? bukan ?: — kuncinya bisa TIDAK ADA, bukan sekadar
                    // kosong, kalau suatu saat Orcha menambah sumber baru yang
                    // tidak punya titik jemput. ?: pada kunci yang hilang
                    // melempar peringatan dan menjatuhkan seluruh kalender.
                    'lokasi' => $b['titik_jemput'] ?? null,
                    'mulai' => Carbon::parse($b['mulai'])->startOfDay(),
                    'selesai' => isset($b['selesai']) && $b['selesai']
                        ? Carbon::parse($b['selesai'])->endOfDay()
                        : null,
                    'seharian' => true,
                ]);

                /*
                 | id diisi sendiri walau barisnya tidak pernah disimpan.
                 |
                 | Dua tempat membutuhkannya: wire:key tiap balok, dan nada()
                 | yang memilih variasi warna dari crc32 id. Dengan id null,
                 | seluruh baris Orcha berbagi satu kunci — Livewire lalu
                 | menggambar ulang balok yang salah — dan semuanya bernada
                 | sama persis sehingga dua trip bersebelahan terbaca sebagai
                 | satu batang panjang.
                 |
                 | Berawalan 'orcha-' supaya tidak pernah tertukar dengan UUID
                 | milik baris sungguhan di tabel kegiatan.
                 */
                $k->id = 'orcha-'.$b['sumber'].'-'.$b['id'];
                $k->exists = false;

                // Relasi diisi kosong, bukan dibiarkan dimuat sendiri: tanpa
                // ini setiap pembacaan $k->peserta menembak satu kueri ke
                // kegiatan_peserta dengan kunci yang tidak ada.
                $k->setRelation('peserta', collect());

                // Dipakai tampilan untuk menautkan ke halaman aslinya di lemon
                // dan untuk menyembunyikan tombol Ubah/Hapus.
                $k->orchaTautan = $this->tautanOrcha($b);

                return $k;
            })
            ->filter(fn (Kegiatan $k) => ! $this->saringJenis || $k->jenis === $this->saringJenis)
            ->values();
    }

    private function judulOrcha(array $b): string
    {
        $judul = $b['judul'] ?: 'Agenda Orcha';

        /*
         | Jadwal jualan dibedakan kalimatnya.
         |
         | Angkanya di sini bukan rombongan yang berangkat, melainkan yang
         | mendaftar tetapi belum bayar. "— 1 orang" akan terbaca sebagai
         | trip sehari dengan satu peserta, padahal artinya tanggal itu masih
         | kosong dan ada satu orang yang menunggu dibayar.
         */
        /*
         | Rencana harga open trip: angkanya TARGET, bukan peserta.
         |
         | Dikirim terpisah sebagai target_peserta justru supaya tidak pernah
         | tertulis "13 orang" di tanggal yang pendaftarnya nol.
         */
        if (($b['sumber'] ?? '') === 'rab_jual') {
            return $judul.' — rencana '.($b['target_peserta'] ?? 0).' orang, belum ada tanggal dijual';
        }

        if (($b['sumber'] ?? '') === 'katalog') {
            return $judul.' — '.(empty($b['jumlah_peserta'])
                ? 'belum ada pendaftar'
                : $b['jumlah_peserta'].' belum bayar');
        }

        // Jumlah peserta ikut di JUDUL, bukan hanya di rinciannya: balok di
        // kisi cuma menampilkan judul, dan "berapa orang" justru pertanyaan
        // pertama saat melihat ada rombongan berangkat hari itu.
        if (! empty($b['jumlah_peserta'])) {
            $judul .= ' — '.$b['jumlah_peserta'].' orang';
        }

        return $judul;
    }

    private function rincianOrcha(array $b): string
    {
        $bagian = array_filter([
            $b['kode'] ?? null,
            // RAB yang melebarkan rentang tanggalnya — tanpa ini admin melihat
            // pita 12-13 Des pada keberangkatan yang di borang paket tertulis
            // 13 Des saja, dan tidak punya cara tahu dari mana malam itu
            // datang.
            ! empty($b['rab']) ? 'Rentang dari '.$b['rab'] : null,
            isset($b['pemesan']) ? 'Pemesan: '.$b['pemesan'] : null,
            ! empty($b['tujuan']) ? 'Tujuan: '.$b['tujuan'] : null,
            ! empty($b['titik_jemput']) ? 'Jemput: '.$b['titik_jemput'] : null,
            $b['status_label'] ?? null,
        ]);

        return implode(' · ', $bagian);
    }

    private function tautanOrcha(array $b): ?string
    {
        return match ($b['sumber'] ?? '') {
            'pendaftaran' => route('admin.orcha.pendaftaran.detail', $b['id']),
            // Yang dibuka penyusunnya, bukan borang kepala: yang ingin
            // dilihat dari kalender adalah rencana perjalanannya.
            // Keduanya baris RAB, jadi yang dibuka penyusunnya — yang ingin
            // dilihat dari kalender rencana perjalanannya.
            'rab', 'rab_jual' => route('admin.orcha.rab.susun', $b['id']),
            /*
             | Dibuka borang PAKETNYA, dan lewat paket_id — bukan $b['id'],
             | yang pada sumber ini adalah id keberangkatan. Menautkannya
             | dengan id yang salah akan membuka paket milik rombongan lain,
             | diam-diam dan tanpa galat.
             |
             | Tanpa paket_id (Orcha versi lama) jatuh ke daftar paket, bukan
             | ke tautan yang salah.
             */
            'katalog' => empty($b['paket_id'])
                ? route('admin.orcha.paket')
                : route('admin.orcha.paket.ubah', $b['paket_id']),
            default => route('admin.orcha.penyewaan.detail', $b['id']),
        };
    }

    public function render()
    {
        $awalBulan = $this->acuan();
        $akhirBulan = $awalBulan->copy()->endOfMonth();

        // Grid selalu mulai Senin dan berakhir Minggu supaya kolomnya rata.
        $awalGrid = $awalBulan->copy()->startOfWeek(Carbon::MONDAY);
        $akhirGrid = $akhirBulan->copy()->endOfWeek(Carbon::SUNDAY);

        $idSaya = auth()->id();

        $kegiatan = Kegiatan::with('peserta:id,name')
            ->dalamRentang($awalGrid, $akhirGrid)
            ->when($this->saringJenis, fn ($q) => $q->where('jenis', $this->saringJenis))
            ->when($this->hanyaSaya, fn ($q) => $q->milik($idSaya))
            ->get();

        /*
         | Agenda Orcha ditumpangkan, bukan disalin ke tabel ini.
         |
         | Menyalinnya butuh penjadwal yang berjalan berkala — dan penjadwal
         | Phoenix mati karena proc_open dimatikan hosting, jadi salinannya
         | tidak akan pernah diperbarui. Lebih dari itu, pendaftaran yang
         | dibatalkan di Orcha akan meninggalkan baris basi di kalender yang
         | tidak ada yang menghapusnya.
         |
         | Diambil sesudah kegiatan lemon, dan digabung ke koleksi yang sama,
         | supaya seluruh logika balok lintas-hari di bawah berlaku apa adanya.
         */
        $galatOrcha = null;

        if ($this->bolehLihatOrcha && ! $this->hanyaSaya) {
            $kegiatan = $kegiatan->concat(
                $this->agendaOrcha($awalGrid, $akhirGrid, $galatOrcha)
            );
        }

        // Hari libur & peringatan nasional. Diambil sekali untuk seluruh kisi,
        // bukan per sel: 42 pemanggilan untuk satu bulan yang sama itu sia-sia.
        $penanda = \App\Support\HariLibur::untukRentang($awalGrid, $akhirGrid);

        $minggu = [];
        $hari = $awalGrid->copy();

        while ($hari->lessThanOrEqualTo($akhirGrid)) {
            $awalMinggu = $hari->copy();
            $baris = [];

            for ($i = 0; $i < 7; $i++) {
                $tanggal = $hari->toDateString();

                $baris[] = [
                    'tanggal' => $tanggal,
                    'angka' => (int) $hari->day,
                    'bulanIni' => (int) $hari->month === $this->bulan,
                    'hariIni' => $hari->isToday(),
                    'akhirPekan' => $hari->isWeekend(),
                    'penanda' => $penanda[$tanggal] ?? null,
                ];
                $hari->addDay();
            }

            $minggu[] = ['hari' => $baris] + $this->susunBalok($kegiatan, $awalMinggu);
        }

        return view('livewire.pages.admin.kegiatan.kegiatan-kalender', [
            'minggu' => $minggu,
            'namaBulan' => $awalBulan->locale('id')->translatedFormat('F Y'),
            'jumlahBulanIni' => $kegiatan->filter(
                fn (Kegiatan $k) => (int) $k->mulai->month === $this->bulan && (int) $k->mulai->year === $this->tahun
            )->count(),
            'berikutnya' => Kegiatan::with('peserta:id,name')
                ->where('mulai', '>=', now())
                ->orderBy('mulai')
                ->limit(5)
                ->get(),
            'daftarKegiatanTerpilih' => $this->tanggalTerpilih
                ? $this->padaTanggal($kegiatan, $this->tanggalTerpilih)
                : collect(),
            'semuaKaryawan' => User::orderBy('name')->get(['id', 'name']),
            // Dipakai kepala halaman untuk berterus terang bahwa libur yang
            // tanggalnya berpindah belum diisi untuk tahun ini.
            'bergerakTerisi' => \App\Support\HariLibur::bergerakTerisi($this->tahun),
            // Kosong berarti aman. Diisi berarti Orcha tidak terjangkau, dan
            // kalender yang diam soal itu akan terbaca sebagai bulan tanpa
            // keberangkatan — kabar yang jauh lebih berbahaya daripada
            // pemberitahuan galat.
            'galatOrcha' => $galatOrcha,
        ]);
    }
}
