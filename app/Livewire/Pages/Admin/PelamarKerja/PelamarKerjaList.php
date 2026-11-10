<?php

namespace App\Livewire\Pages\Admin\PelamarKerja;

use App\Models\JejakLamaran;
use App\Models\JobApplication;
use App\Models\Lowongan;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PelamarKerjaList extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $search = '';

    public $filterMonth = '';

    /**
     * Lowongan yang sedang disaring.
     *
     * Terikat URL supaya layar Lowongan bisa menautkannya langsung:
     * /admin/pelamar?filterJob=7. Tanpa itu, angka "7 orang" di daftar
     * lowongan tidak bisa ditindaklanjuti — admin harus membuka layar ini
     * lalu mencari lowongannya lagi di kotak pilih.
     */
    #[Url(as: 'lowongan', except: '')]
    public $filterJob = '';

    public $filterStatus = '';

    /**
     * Pengurutan daftar. 'terbaru' = tanggal melamar, arah turun.
     *
     * Sebelum ini daftarnya SELALU terbaru dulu dan tidak bisa diubah —
     * mencari "siapa saja yang melamar posisi ini dari A" berarti membaca
     * seluruh halaman satu per satu.
     */
    public string $urut = 'terbaru';

    public string $arahUrut = 'desc';

    /** Baris yang dicentang untuk aksi massal. */
    public array $pilihan = [];

    /** Tahap tujuan di aksi massal. */
    public string $tahapMassal = '';

    /**
     * Kueri bersama layar & ekspor: berkas Excel berisi persis yang sedang
     * disaring di layar.
     */
    private function kueri()
    {
        /*
         | Nama kolom SELALU disebut lengkap dengan tabelnya.
         |
         | Pengurutan menurut posisi menyambung tabel lowongans, dan tabel itu
         | punya created_at, title, dan id-nya sendiri. Tanpa awalan tabel,
         | saringan "bulan melamar" diam-diam menyaring tanggal LOWONGANnya,
         | dan MySQL melempar "ambiguous column" untuk sebagian yang lain.
         */
        $t = (new JobApplication)->getTable();

        $query = JobApplication::with('job')->select($t.'.*');

        // Pencarian: nama, email, telepon, nomor lamaran, atau posisi lowongan
        if ($this->search) {
            $query->where(function ($q) use ($t) {
                $q->where($t.'.name', 'like', "%{$this->search}%")
                    ->orWhere($t.'.email', 'like', "%{$this->search}%")
                    ->orWhere($t.'.phone', 'like', "%{$this->search}%")
                    ->orWhere($t.'.nomor_lamaran', 'like', "%{$this->search}%")
                    ->orWhereHas('job', fn ($j) => $j->where('title', 'like', "%{$this->search}%"));
            });
        }

        /*
         | Bulan DAN tahun.
         |
         | Dulu hanya whereMonth, padahal labelnya tertulis "November 2026":
         | begitu ada lamaran lebih dari setahun lalu, memilih November 2026
         | ikut memanggil November 2025 — tanpa satu pun tanda bahwa daftarnya
         | mencampur dua tahun. Nilainya sekarang 'YYYY-MM'.
         */
        if ($this->filterMonth) {
            [$tahun, $bulan] = array_pad(explode('-', $this->filterMonth, 2), 2, null);

            if ($tahun && $bulan) {
                $query->whereYear($t.'.created_at', (int) $tahun)
                    ->whereMonth($t.'.created_at', (int) $bulan);
            }
        }

        // Filter berdasarkan posisi lowongan
        // 'umum' = lamaran umum (talent pool) tanpa posisi.
        if ($this->filterJob === 'umum') {
            $query->whereNull($t.'.job_id');
        } elseif ($this->filterJob) {
            $query->where($t.'.job_id', $this->filterJob);
        }

        if ($this->filterStatus) {
            $query->where($t.'.status', $this->filterStatus);
        }

        return $this->urutkanKueri($query);
    }

    /** Kolom yang boleh diurut => kolom sebenarnya di basis data. */
    private const KOLOM_URUT = [
        'terbaru' => 'created_at',
        'nama' => 'name',
        'tahap' => 'status',
    ];

    private function urutkanKueri($query)
    {
        $t = (new JobApplication)->getTable();
        $kolom = $t.'.'.(self::KOLOM_URUT[$this->urut] ?? 'created_at');
        $arah = $this->arahUrut === 'asc' ? 'asc' : 'desc';

        if ($this->urut === 'posisi') {
            /*
             | Diurut menurut JUDUL lowongannya, bukan job_id.
             |
             | Mengurut job_id menghasilkan urutan yang tampak acak bagi yang
             | membacanya — angkanya tidak kelihatan di layar. Lamaran umum
             | (job_id kosong) selalu ditaruh di akhir, berapa pun arahnya:
             | ia bukan posisi yang paling awal ataupun paling akhir menurut
             | abjad, ia memang tidak punya posisi.
             */
            return $query
                ->leftJoin('lowongans', 'lowongans.id', '=', $t.'.job_id')
                ->orderByRaw($t.'.job_id is null')
                ->orderBy('lowongans.title', $arah)
                ->orderByDesc($t.'.id');
        }

        // Pengurut kedua yang pasti unik: tanpa ini, dua lamaran dengan nama
        // atau tahap yang sama bisa bertukar tempat antar-halaman, dan baris
        // yang sama muncul dua kali di halaman berbeda.
        return $query->orderBy($kolom, $arah)->orderByDesc($t.'.id');
    }

    public function urutkan(string $kolom): void
    {
        if (! array_key_exists($kolom, self::KOLOM_URUT) && $kolom !== 'posisi') {
            return;
        }

        if ($this->urut === $kolom) {
            $this->arahUrut = $this->arahUrut === 'asc' ? 'desc' : 'asc';
        } else {
            $this->urut = $kolom;
            // Nama & posisi dicari menaik (A→Z); tanggal dan tahap menurun.
            $this->arahUrut = in_array($kolom, ['nama', 'posisi'], true) ? 'asc' : 'desc';
        }

        $this->resetPage();
    }

    /**
     * Berapa pelamar di tiap tahap, mengikuti saringan yang SEDANG hidup.
     *
     * Dihitung dari kueri yang sama dengan daftarnya, hanya tanpa batas
     * tahapnya sendiri. Dua kueri yang ditulis terpisah akan berselisih
     * begitu saringannya bertambah, dan angka di tab lalu menjanjikan isi
     * yang tidak ada saat ditekan.
     */
    public function jumlahTahap(): array
    {
        $tahap = $this->filterStatus;
        $this->filterStatus = '';
        $dasar = $this->kueri();
        $this->filterStatus = $tahap;

        // reorder(): menghitung tidak butuh urutan, dan ORDER BY atas kolom
        // tabel sambungan ikut terbawa ke kueri COUNT.
        $dasar = $dasar->reorder();

        $jumlah = ['' => (clone $dasar)->count()];

        foreach (array_keys(JobApplication::STATUS) as $kunci) {
            $jumlah[$kunci] = (clone $dasar)->where((new JobApplication)->getTable().'.status', $kunci)->count();
        }

        return $jumlah;
    }

    public function setTahap(string $tahap): void
    {
        $this->filterStatus = array_key_exists($tahap, JobApplication::STATUS) ? $tahap : '';
        $this->resetPage();
        $this->bersihkanPilihan();
    }

    /**
     * Pelamar yang berkasnya sedang dibaca di jendela sembul; null = tertutup.
     *
     * Supaya CV bisa dibaca TANPA meninggalkan daftarnya. Admin yang sedang
     * menyaring sepuluh pelamar kehilangan posisi gulung dan saringannya tiap
     * kali membuka halaman detail, lalu kembali ke daftar yang sudah
     * ter-reset — dan membaca sepuluh CV berarti sepuluh kali begitu.
     */
    public $pratinjauId = null;

    public string $berkasAktif = 'cv';

    public function bukaPratinjau($id): void
    {
        abort_unless(auth()->user()?->hasPermission('view_pelamar'), 403);

        $pelamar = JobApplication::find($id);

        if (! $pelamar) {
            return;
        }

        $this->pratinjauId = $pelamar->id;

        // Tab awal mengikuti berkas yang MEMANG ADA: membuka pelamar yang
        // CV-nya sudah dipangkas pada tab kosong terbaca sebagai "tidak ada
        // berkasnya sama sekali", padahal surat lamarannya masih ada.
        $this->berkasAktif = 'cv';

        foreach (['cv' => 'cv_path', 'surat' => 'cover_letter_path', 'lampiran' => 'lampiran_path'] as $kunci => $kolom) {
            if ($pelamar->{$kolom}) {
                $this->berkasAktif = $kunci;
                break;
            }
        }
    }

    public function bukaBerkas(string $kunci): void
    {
        $this->berkasAktif = in_array($kunci, ['cv', 'surat', 'lampiran'], true) ? $kunci : 'cv';
    }

    public function tutupPratinjau(): void
    {
        $this->pratinjauId = null;
    }

    public function adaSaringan(): bool
    {
        return trim((string) $this->search) !== ''
            || $this->filterMonth !== ''
            || $this->filterJob !== ''
            || $this->filterStatus !== '';
    }

    public function ekspor()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PelamarKerjaExport($this->kueri()->get()),
            'pelamar-kerja-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $query = $this->kueri();

        $dataPelamar = $query->paginate($this->perPage);

        // Get data untuk filter dropdown (is_active = enum string 'active')
        $jobList = Lowongan::where('is_active', 'active')
            ->orderBy('title')
            ->get();

        // Bulan untuk saringan, 12 bulan terakhir. Nilainya 'YYYY-MM' —
        // labelnya memang menyebut tahun, jadi nilainya harus menyebutnya juga.
        $months = collect();
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $months->push([
                'value' => $date->format('Y-m'),
                'label' => $date->locale('id')->isoFormat('MMMM YYYY'),
            ]);
        }

        return view('livewire.pages.admin.pelamar-kerja.pelamar-kerja-list', [
            'dataPelamar' => $dataPelamar,
            'jobList' => $jobList,
            'months' => $months,
            'jumlahTahap' => $this->jumlahTahap(),
            // Email yang muncul lebih dari sekali DI SELURUH data, bukan cuma
            // di halaman ini: pelamar ganda justru paling sering terpisah
            // beberapa halaman.
            'emailBerulang' => $this->emailBerulang($dataPelamar->pluck('email')->all()),
            'pratinjau' => $this->pratinjauId ? JobApplication::with('job')->find($this->pratinjauId) : null,
            // Dipakai kepala layar: lowongan mana yang sedang dibaca, bila
            // layar ini dibuka lewat tautan dari daftar Lowongan.
            'lowonganDisaring' => $this->filterJob && $this->filterJob !== 'umum'
                ? Lowongan::find($this->filterJob)
                : null,
        ]);
    }

    #[On('confirm-delete')]
    public function delete($id)
    {
        if (! auth()->user()->hasPermission('delete_pelamar')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus data pelamar.');

            return;
        }

        try {
            $application = JobApplication::findOrFail($id);

            $cvPath = $application->cv_path;
            $clPath = $application->cover_letter_path;
            $lampiranPath = $application->lampiran_path;

            // Lamaran baru disimpan di disk PRIVAT 'local'; yang lama mungkin
            // masih di 'public'. Dulu hanya 'public' yang dibersihkan, jadi CV
            // pelamar yang dihapus tetap tertinggal di server.
            foreach (['local', 'public'] as $disk) {
                foreach ([$cvPath, $clPath, $lampiranPath] as $jalur) {
                    if ($jalur && Storage::disk($disk)->exists($jalur)) {
                        Storage::disk($disk)->delete($jalur);
                    }
                }

                // Folder per lamaran: applications/{tahun}/{bulan}/{uuid}.
                // Dijaga ketat supaya dirname() dari jalur aneh ('.' atau
                // 'applications') tidak pernah menghapus folder bersama.
                $folder = dirname($cvPath ?? $clPath ?? '');
                if (preg_match('#^applications/\d{4}/\d{2}/[0-9a-f-]{36}$#', $folder)
                    && Storage::disk($disk)->exists($folder)
                    && Storage::disk($disk)->files($folder) === []) {
                    Storage::disk($disk)->deleteDirectory($folder);
                }
            }

            // Riwayatnya ikut dibuang: tabel jejak tidak memakai kunci asing
            // bercascade, jadi tanpa ini barisnya tertinggal menunjuk lamaran
            // yang sudah tidak ada.
            JejakLamaran::where('job_application_id', $application->id)->delete();

            $application->delete();
            $this->pilihan = array_values(array_diff($this->pilihan, [$id, (string) $id]));

            $this->dispatch('swal-success', message: 'Data pelamar berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('swal-error', message: 'Gagal menghapus data pelamar.');
        }
    }

    /**
     * Email yang punya lebih dari satu lamaran.
     *
     * Dicari sekali untuk seluruh halaman (satu kueri), bukan per baris:
     * sepuluh baris berarti sepuluh kueri, dan daftarnya terasa berat tanpa
     * sebab yang kelihatan.
     */
    private function emailBerulang(array $email): array
    {
        $email = array_values(array_filter(array_unique($email)));

        if ($email === []) {
            return [];
        }

        return JobApplication::query()
            ->selectRaw('email, count(*) as jumlah')
            ->whereIn('email', $email)
            ->groupBy('email')
            ->havingRaw('count(*) > 1')
            ->pluck('jumlah', 'email')
            ->all();
    }

    /** Boleh memutuskan (ubah tahap), bukan sekadar membaca. */
    public function getBisaUbahProperty(): bool
    {
        return (bool) auth()->user()?->hasPermission('edit_pelamar');
    }

    public function getBisaHapusProperty(): bool
    {
        return (bool) auth()->user()?->hasPermission('delete_pelamar');
    }

    /**
     * Ubah tahap beberapa lamaran sekaligus.
     *
     * TIDAK mengirim surel, dan itu disengaja: satu klik yang mengirim
     * belasan surel sekaligus — masing-masing tanpa pesan pribadi — adalah
     * kesalahan yang tidak bisa ditarik kembali. Massal dipakai untuk
     * merapikan tumpukan; pemberitahuan ke pelamar lewat halaman detail,
     * satu per satu, dengan pesannya sendiri.
     */
    public function terapkanTahapMassal(): void
    {
        if (! $this->bisaUbah) {
            $this->dispatch('swal-error', message: 'Anda tidak berhak mengubah tahap pelamar.');

            return;
        }

        if (! array_key_exists($this->tahapMassal, JobApplication::STATUS)) {
            $this->dispatch('swal-error', message: 'Pilih dulu tahap tujuannya.');

            return;
        }

        $terpilih = JobApplication::whereIn('id', $this->pilihan)->get();

        if ($terpilih->isEmpty()) {
            $this->dispatch('swal-error', message: 'Belum ada pelamar yang dicentang.');

            return;
        }

        $berubah = 0;

        foreach ($terpilih as $lamaran) {
            if ($lamaran->status === $this->tahapMassal) {
                continue;
            }

            $dari = $lamaran->status;
            $lamaran->update(['status' => $this->tahapMassal, 'status_diubah_at' => now()]);

            JejakLamaran::catat($lamaran, 'tahap', [
                'dari_status' => $dari,
                'ke_status' => $this->tahapMassal,
                'isi' => 'Diubah bersama '.$terpilih->count().' lamaran sekaligus. Surel tidak dikirim.',
            ]);

            $berubah++;
        }

        $this->bersihkanPilihan();
        $this->tahapMassal = '';

        $this->dispatch('swal-success', message: $berubah === 0
            ? 'Tidak ada yang berubah — semuanya sudah di tahap itu.'
            : $berubah.' lamaran dipindahkan. Surel TIDAK dikirim.');
    }

    public function bersihkanPilihan(): void
    {
        $this->pilihan = [];
    }

    /**
     * Centang semua baris di HALAMAN INI.
     *
     * Sengaja hanya halaman ini, bukan seluruh hasil saringan: "pilih semua"
     * yang diam-diam mencakup 300 baris yang tidak terlihat adalah cara
     * tercepat menghapus data yang tidak diniatkan.
     */
    public function pilihHalaman(): void
    {
        /*
         | Daftar id-nya dihitung ULANG di peladen, tidak dititipkan layar.
         |
         | Menitipkannya lewat atribut wire:click berarti menempelkan JSON
         | berisi tanda kutip ke dalam atribut HTML — markahnya patah pada
         | halaman pertama yang id-nya panjang. Dan daftar yang datang dari
         | peramban bisa berisi id mana pun, bukan hanya yang tampil.
         */
        $id = $this->kueri()->paginate($this->perPage)->pluck('id')
            ->map(fn ($n) => (string) $n)->all();

        $this->pilihan = array_values(array_unique(array_merge($this->pilihan, $id)));
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->bersihkanPilihan();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
        $this->bersihkanPilihan();
    }

    public function updatingFilterJob()
    {
        $this->resetPage();
        $this->bersihkanPilihan();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
        $this->bersihkanPilihan();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterMonth', 'filterJob', 'filterStatus']);
        $this->resetPage();
        $this->bersihkanPilihan();
    }
}
