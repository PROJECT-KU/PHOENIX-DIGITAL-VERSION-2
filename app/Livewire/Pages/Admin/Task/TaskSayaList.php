<?php

namespace App\Livewire\Pages\Admin\Task;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskCategory;
use App\Models\TaskCategoryLabel;
use App\Models\TaskComment;
use App\Models\TaskCommentRead;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskReopened;
use App\Support\PeriodeGaji;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class TaskSayaList extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    // Modal diskusi grup (komentar dipusatkan di level folder/grup).
    public bool $showGroupChat = false;

    public $activeTaskId = null;

    public $newComment = '';

    public $commentFile;

    // Filter periode (pola sama seperti Pengeluaran/Spending). Ikut alamat
    // halaman juga: tanpa itu, tautan ke "task September" mendarat di periode
    // berjalan milik pembacanya, bukan periode yang dimaksud pengirimnya.
    #[Url(as: 'bln')]
    public $bulan = '';

    #[Url(as: 'thn')]
    public $tahun = '';

    // Mode periode (sama seperti Cashflow): 'kalender' (1 s/d akhir bulan) atau
    // 'siklus20' = siklus gaji 21–20 (mis. Juli = 21 Jun s/d 20 Jul), mengikuti
    // PeriodeGaji. Nilai 'siklus20' dipertahankan apa adanya agar state/URL lama
    // tidak rusak, walau maknanya kini siklus gaji.
    #[Url(as: 'mode', except: 'kalender')]
    public $modePeriode = 'kalender';

    // ===== Pencarian & saringan daftar =====
    //
    // Semuanya ikut ke ALAMAT HALAMAN. Tanpa itu, hasil saringan tidak bisa
    // dikirim ke orang lain ("coba lihat yang telat ini") dan hilang tiap kali
    // halaman dimuat ulang atau ditinggal ke layar lain. `except` menjaga
    // alamatnya tetap pendek: nilai bawaan tidak ditulis.

    /** Kata kunci nama/uraian task. */
    #[Url(as: 'q', except: '')]
    public string $cari = '';

    /** '' | belum | dikerjakan | selesai | telat — 'telat' bukan nilai kolom. */
    #[Url(as: 'status', except: '')]
    public string $saringStatus = '';

    /** user_id penerima tertentu. */
    #[Url(as: 'orang', except: '')]
    public string $saringOrang = '';

    /** id kategori. */
    #[Url(as: 'kategori', except: '')]
    public string $saringKategori = '';

    /**
     * 'semua' | 'saya' (ditugaskan ke saya) | 'dari-saya' (saya yang memberi).
     * Bagi atasan keduanya bercampur di satu daftar padahal sifatnya berbeda:
     * yang satu harus dikerjakan, yang satu harus ditagih.
     */
    #[Url(as: 'arah', except: 'semua')]
    public string $saringArah = 'semua';

    /** 'terbaru' | 'tenggat' | 'nama' | 'status'. */
    #[Url(as: 'urut', except: 'terbaru')]
    public string $urut = 'terbaru';

    #[Url(as: 'urutan', except: 'desc')]
    public string $arahUrut = 'desc';

    /** Halaman tabel. Dihitung atas GRUP, bukan baris. */
    #[Url(as: 'hal', except: 1)]
    public int $halaman = 1;

    /**
     * Kolom tabel yang DISEMBUNYIKAN. Pilihan pembaca, bukan lebar layar —
     * yang lebar layar sudah diurus CSS. Ikut alamat halaman supaya susunannya
     * bertahan saat kembali dari detail.
     *
     * @var array<int, string>
     */
    #[Url(as: 'tutup', except: [])]
    public array $kolomSembunyi = [];

    public const KOLOM_BISA_DITUTUP = [
        'penerima' => 'Penerima',
        'pemberi' => 'Pemberi',
        'tenggat' => 'Tenggat',
    ];

    public function alihkanKolom(string $kolom): void
    {
        if (! array_key_exists($kolom, self::KOLOM_BISA_DITUTUP)) {
            return;
        }

        $this->kolomSembunyi = in_array($kolom, $this->kolomSembunyi, true)
            ? array_values(array_diff($this->kolomSembunyi, [$kolom]))
            : [...$this->kolomSembunyi, $kolom];
    }

    /**
     * Grup task yang dicentang untuk aksi massal.
     *
     * Berisi group_id, bukan task id: satu baris tabel adalah satu PEKERJAAN,
     * dan mencentangnya berarti memilih seluruh penerimanya.
     *
     * @var array<int, string>
     */
    public array $terpilih = [];

    /**
     * Bersihkan pilihan tiap kali daftarnya berubah.
     *
     * Tanpa ini, grup yang tercentang lalu tersaring keluar tetap ikut
     * terkena aksi massal — pengguna menekan "Tandai Selesai" untuk tiga baris
     * yang terlihat, dan yang berubah tujuh.
     */
    public function bersihkanPilihan(): void
    {
        $this->terpilih = [];
    }

    /**
     * group_id yang tampil di halaman tabel saat ini.
     *
     * Disimpan saat render supaya "pilih semua" tidak perlu menjalankan
     * kuerinya lagi hanya untuk tahu baris mana yang sedang terlihat.
     *
     * @var array<int, string>
     */
    public array $gidHalaman = [];

    /**
     * Centang / lepas SEMUA baris di halaman ini.
     *
     * Sengaja halaman ini saja, bukan seluruh hasil saringan: mencentang 300
     * baris yang tidak terlihat lalu menekan Hapus bukan sesuatu yang orang
     * maksudkan, dan tidak ada layar yang menunjukkan apa saja yang ikut.
     */
    public function alihkanSemuaHalaman(): void
    {
        $semuaTercentang = ! empty($this->gidHalaman)
            && empty(array_diff($this->gidHalaman, $this->terpilih));

        $this->terpilih = $semuaTercentang
            ? array_values(array_diff($this->terpilih, $this->gidHalaman))
            : array_values(array_unique([...$this->terpilih, ...$this->gidHalaman]));
    }

    public const PER_HALAMAN = 15;

    /** Saringan apa pun berubah -> kembali ke halaman pertama. */
    public function updated($nama): void
    {
        if (in_array($nama, ['cari', 'saringStatus', 'saringOrang', 'saringKategori', 'saringArah', 'bulan', 'tahun', 'modePeriode'], true)) {
            $this->halaman = 1;
            $this->bersihkanPilihan();
        }
    }

    public function urutkan(string $kolom): void
    {
        $sah = ['terbaru', 'tenggat', 'nama', 'status', 'penerima', 'pemberi'];
        if (! in_array($kolom, $sah, true)) {
            return;
        }

        // Menekan kolom yang sama membalik arahnya; kolom lain mulai dari
        // arah yang paling sering dicari untuk kolom itu.
        if ($this->urut === $kolom) {
            $this->arahUrut = $this->arahUrut === 'asc' ? 'desc' : 'asc';
        } else {
            $this->urut = $kolom;
            // Nama orang & nama task dicari menaik (A→Z); tenggat menaik (yang
            // paling dekat dulu); sisanya menurun.
            $this->arahUrut = in_array($kolom, ['nama', 'tenggat', 'penerima', 'pemberi'], true) ? 'asc' : 'desc';
        }

        $this->halaman = 1;
    }

    public function keHalaman(int $ke): void
    {
        $this->halaman = max(1, $ke);
        $this->bersihkanPilihan();
    }

    /**
     * Tandai selesai seluruh task TERPILIH yang memang milik saya.
     *
     * Kelayakan tiap task diperiksa ULANG di sini, bukan dipercayakan pada
     * daftar centang: centangnya datang dari peramban, dan satu-satunya yang
     * tahu apakah sebuah task boleh ditutup adalah server.
     */
    public function selesaikanTerpilih(): void
    {
        if (empty($this->terpilih)) {
            return;
        }

        $kandidat = Task::visibleTo()
            ->whereIn('group_id', $this->terpilih)
            ->where('user_id', auth()->id())
            ->where('progress', '!=', 'selesai')
            ->get();

        $berhasil = 0;
        $dilewati = 0;

        foreach ($kandidat as $t) {
            // Task terkunci (sudah selesai atau lewat tenggat & beku) tidak
            // boleh ditutup diam-diam lewat aksi massal.
            if ($t->isLocked()) {
                $dilewati++;

                continue;
            }

            $lamaProgres = $t->progress;
            $t->update(['progress' => 'selesai', 'completed_at' => now()]);
            $t->catat('status', $lamaProgres, 'selesai', 'Lewat aksi massal');
            $berhasil++;
        }

        $this->bersihkanPilihan();
        $this->dispatch('sidebar-badge-updated');

        $pesan = $berhasil > 0
            ? $berhasil.' task ditandai selesai'.($dilewati > 0 ? ', '.$dilewati.' dilewati karena terkunci' : '.')
            : 'Tidak ada task yang bisa ditandai selesai dari pilihan itu.';

        $this->dispatch($berhasil > 0 ? 'swal-success' : 'swal-error', message: $pesan);
    }

    /**
     * Hapus seluruh grup task TERPILIH yang boleh saya kelola.
     *
     * Sama seperti di atas: yang menentukan boleh atau tidak adalah
     * manageableGiverIds() di server, bukan tombol yang tampil di layar.
     */
    public function hapusTerpilih(): void
    {
        if (empty($this->terpilih) || ! $this->bolehBeriTask()) {
            return;
        }

        $dihapus = 0;

        foreach ($this->terpilih as $groupId) {
            $anggota = Task::visibleTo()->where('group_id', $groupId)->get();

            if ($anggota->isEmpty() || $anggota->contains(fn ($t) => ! $this->bolehKelolaTask($t))) {
                continue;
            }

            $this->deleteGroup($groupId);
            $dihapus++;
        }

        $this->bersihkanPilihan();

        $this->dispatch(
            $dihapus > 0 ? 'swal-success' : 'swal-error',
            message: $dihapus > 0
                ? $dihapus.' task grup dihapus.'
                : 'Tidak ada task yang boleh Anda hapus dari pilihan itu.',
        );
    }

    // ===== Checklist di dalam satu task =====
    public string $checklistBaru = '';

    /**
     * Siapa yang boleh menyunting checklist sebuah task: PENERIMANYA, dan
     * pemberi yang boleh mengelolanya. Bukan siapa pun yang bisa melihat —
     * task bawahan orang lain terlihat oleh atasan rantai di atasnya, dan
     * mereka tidak semestinya ikut mencentang pekerjaan orang.
     */
    protected function bolehSuntingChecklist(Task $task): bool
    {
        return $task->user_id === auth()->id()
            || ($task->assigned_by && in_array($task->assigned_by, $this->manageableGiverIds(), true));
    }

    public function tambahChecklist(): void
    {
        $teks = trim($this->checklistBaru);

        if ($teks === '' || ! $this->activeTaskId) {
            return;
        }

        $task = Task::visibleTo()->findOrFail($this->activeTaskId);
        if (! $this->bolehSuntingChecklist($task)) {
            return;
        }

        $task->checklists()->create([
            'teks' => mb_substr($teks, 0, 190),
            'urutan' => (int) $task->checklists()->max('urutan') + 1,
        ]);

        $this->checklistBaru = '';
    }

    public function alihkanChecklist(string $id): void
    {
        $item = \App\Models\TaskChecklist::with('task')->findOrFail($id);

        if (! $item->task || ! $this->bolehSuntingChecklist($item->task)) {
            return;
        }

        $item->update(['selesai' => ! $item->selesai]);
    }

    /**
     * Geser satu langkah naik atau turun satu posisi.
     *
     * Tombol naik/turun, bukan seret-lepas: seret-lepas butuh pustaka baru
     * dan di layar sentuh justru paling sulit dipakai — padahal sebagian
     * besar karyawan membuka layar ini dari ponsel.
     */
    public function geserChecklist(string $id, string $arah): void
    {
        $item = \App\Models\TaskChecklist::with('task')->findOrFail($id);

        if (! $item->task || ! $this->bolehSuntingChecklist($item->task) || ! in_array($arah, ['naik', 'turun'], true)) {
            return;
        }

        // Nomor urut dirapikan dulu (0, 1, 2, …): langkah lama bisa saja
        // bernomor sama, dan menukar dua nomor yang sama tidak menggeser apa pun.
        $daftar = $item->task->checklists()->get()->values();
        foreach ($daftar as $i => $l) {
            if ($l->urutan !== $i) {
                $l->update(['urutan' => $i]);
            }
        }

        $posisi = $daftar->search(fn ($l) => $l->id === $item->id);
        $tujuan = $arah === 'naik' ? $posisi - 1 : $posisi + 1;

        if ($posisi === false || $tujuan < 0 || $tujuan >= $daftar->count()) {
            return;
        }

        $tetangga = $daftar[$tujuan];
        $tetangga->update(['urutan' => $posisi]);
        $item->update(['urutan' => $tujuan]);
    }

    public function hapusChecklist(string $id): void
    {
        $item = \App\Models\TaskChecklist::with('task')->findOrFail($id);

        if ($item->task && $this->bolehSuntingChecklist($item->task)) {
            $item->delete();
        }
    }

    // ===== Berkas HASIL dari penerima =====
    public $hasilFiles = [];

    /**
     * Unggah berkas hasil kerja ke task-nya sendiri.
     *
     * Sebelumnya satu-satunya cara melampirkan hasil adalah lewat komentar,
     * sehingga berkas hasil bercampur dengan percakapan dan tidak ada tempat
     * yang jelas untuk "ini hasilnya".
     */
    public function updatedHasilFiles(): void
    {
        if (! $this->activeTaskId) {
            return;
        }

        $task = Task::visibleTo()->findOrFail($this->activeTaskId);

        // Hanya PENERIMA yang mengunggah hasil. Pemberi punya jalurnya sendiri
        // (lampiran perintah, lewat jendela edit).
        if ($task->user_id !== auth()->id()) {
            $this->hasilFiles = [];

            return;
        }

        $this->validate(['hasilFiles.*' => 'file|max:5120']);

        foreach ((array) $this->hasilFiles as $berkas) {
            $path = $berkas->store('task-hasil', 'public');
            $task->attachments()->create([
                'uploaded_by' => auth()->id(),
                'path' => $path,
                'name' => $berkas->getClientOriginalName(),
                'jenis' => 'hasil',
            ]);
        }

        $task->catat('hasil', null, null, count((array) $this->hasilFiles).' berkas hasil diunggah');
        $this->hasilFiles = [];
        $this->dispatch('swal-success', message: 'Berkas hasil diunggah.');
    }

    /**
     * Unduh rekap task yang SEDANG TERLIHAT sebagai Excel.
     *
     * Mengikuti saringan yang aktif, bukan seluruh tabel: yang ingin dibawa ke
     * rapat adalah daftar yang barusan disusun di layar. Tanpa nilai rupiah —
     * berkas ini bisa berpindah tangan, dan besaran bonus bukan urusan
     * penerimanya.
     */
    public function unduhExcel()
    {
        $tasks = $this->kueriDaftar()->get();

        $nama = 'task-'.($this->bulan ? str_pad((string) $this->bulan, 2, '0', STR_PAD_LEFT).'-' : '')
            .($this->tahun ?: now()->year).'-'.now()->format('Hi').'.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TaskExport($tasks), $nama);
    }

    /** Apakah ada saringan selain periode yang sedang aktif. */
    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->saringStatus !== '' || $this->saringOrang !== ''
            || $this->saringKategori !== '' || $this->saringArah !== 'semua';
    }

    public function kosongkanSaringan(): void
    {
        $this->cari = '';
        $this->saringStatus = '';
        $this->saringOrang = '';
        $this->saringKategori = '';
        $this->saringArah = 'semua';
        $this->halaman = 1;
    }

    // ===== Cara pandang task (murni tampilan — data & filter tidak berubah) =====
    // 'daftar'    : tampilan asli (folder + kartu), tetap default agar kebiasaan
    //               karyawan yang sudah ada tidak berubah.
    // 'scrum'     : papan Kanban 3 kolom (Belum / Dikerjakan / Selesai).
    // 'aktivitas' : grafik kontribusi ala GitHub + linimasa.
    #[Url(as: 'tampilan', except: 'daftar')]
    public string $tampilan = 'daftar';

    /** Ganti cara pandang. Hanya mengubah tampilan, tidak menyentuh data/filter. */
    public function gantiTampilan(string $mode): void
    {
        $this->tampilan = in_array($mode, ['daftar', 'scrum', 'aktivitas'], true)
            ? $mode
            : 'daftar';
    }

    // ===== Modal beri/edit task ke bawahan (khusus atasan ber-izin assign_task) =====
    public bool $showTaskModal = false;

    public $editingTaskId = null;

    // Grup yang sedang diedit (semua sub-task berbagi group_id).
    public $editingGroupId = null;

    // Multi-penerima: 1 task bisa di-assign ke banyak orang (fan-out per orang).
    public $t_user_ids = [];

    public $t_nama = '';

    public $t_deskripsi = '';

    public $t_category_id = '';

    public $t_label_id = '';

    // Untuk tambah kategori/label baru dari popup picker.
    public $newCategoryName = '';

    public $newLabelName = '';

    public $t_bobot = 'sedang';

    /** 'tidak' | 'mingguan' | 'bulanan' — lihat SalinTaskBerulang. */
    public $t_ulang = 'tidak';

    /**
     * Langkah AWAL yang ikut dibuat bersama task-nya.
     *
     * Disiapkan pemberi, lalu tiap penerima mendapat salinannya sendiri —
     * satu orang mencentang langkahnya tidak boleh ikut mencentang milik
     * orang lain.
     *
     * @var array<int, string>
     */
    public array $t_langkah = [];

    /** Kotak isian untuk menambah satu langkah ke daftar di atas. */
    public string $t_langkah_baru = '';

    /**
     * Langkah yang SUDAH ada pada grup yang sedang diedit — ditampilkan
     * sebagai keterangan, bukan sebagai daftar yang bisa disunting.
     *
     * Menghapusnya dari sini berarti menghapusnya dari semua penerima,
     * termasuk yang sudah mencentangnya — dan catatan bahwa seseorang sudah
     * mengerjakan langkah itu ikut hilang bersamanya. Penghapusan tetap ada,
     * tetapi di jendela detail, oleh orang yang mengerjakannya.
     *
     * @var array<int, string>
     */
    public array $t_langkah_ada = [];

    public function tambahLangkahBaru(): void
    {
        $teks = trim($this->t_langkah_baru);

        if ($teks === '') {
            return;
        }

        $this->t_langkah[] = mb_substr($teks, 0, 190);
        $this->t_langkah_baru = '';
    }

    public function hapusLangkahBaru(int $indeks): void
    {
        unset($this->t_langkah[$indeks]);
        $this->t_langkah = array_values($this->t_langkah);
    }

    /**
     * Pasang langkah awal ke sebuah task.
     *
     * Hanya MENAMBAH: langkah yang teksnya sudah ada dilewati, supaya
     * menyimpan ulang jendela edit tidak menggandakan daftar orang lain.
     */
    protected function pasangLangkah(Task $task): void
    {
        if (empty($this->t_langkah)) {
            return;
        }

        $sudahAda = $task->checklists()->pluck('teks')->map(fn ($t) => mb_strtolower($t))->all();
        $urutan = (int) $task->checklists()->max('urutan');

        foreach ($this->t_langkah as $teks) {
            if (in_array(mb_strtolower($teks), $sudahAda, true)) {
                continue;
            }

            $task->checklists()->create(['teks' => $teks, 'urutan' => ++$urutan]);
            $sudahAda[] = mb_strtolower($teks);
        }
    }

    public $t_deadline_mulai = '';

    public $t_deadline_selesai = '';

    // Lampiran task: $newFiles = staging tiap pilih, digabung ke $t_files agar menumpuk.
    public $t_files = [];

    public $newFiles = [];

    // ===== Modal "Buka Kembali" (revisi task terkunci) — pemberi task saja =====
    public bool $showReopenModal = false;

    public $reopenTaskId = null;

    public $reopen_alasan = '';

    public $reopen_label_id = '';

    public $reopen_deadline = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('view_task'), 403);

        // Default ke PERIODE GAJI berjalan, bukan bulan kalender. Pada tanggal
        // 21–akhir bulan keduanya berbeda: mis. 21 Jul sudah masuk periode gaji
        // Agustus (21 Jul–20 Agu). Task difiling per periode gaji (periode_bulan
        // lewat PeriodeGaji::dariTanggal), jadi filter defaultnya harus sama —
        // kalau pakai now()->month, task berdeadline 21 Jul "hilang" karena
        // layar default menampilkan Juli.
        $periodeKini = PeriodeGaji::dariTanggal(now());
        $this->bulan = $periodeKini['bulan'];
        $this->tahun = $periodeKini['tahun'];

        // Buka langsung dari klik notifikasi bell (?open_task=ID).
        // Task GRUP -> langsung ke Diskusi Grup (kolom komentar). Task solo -> detail
        // (yang komentarnya inline). Filter tetap periode berjalan.
        $id = request('open_task');
        if ($id && ($task = Task::visibleTo()->whereKey($id)->first())) {
            $isGroup = Task::where('group_id', $task->group_id)->count() > 1;
            if ($isGroup) {
                $this->openGroupChat($task->group_id);
            } else {
                $this->openTask($id);
            }
        }
    }

    public function resetFilter(): void
    {
        $this->bulan = '';
        $this->tahun = '';
        $this->modePeriode = 'kalender';
    }

    /**
     * Apakah filter memakai siklus gaji 21–20 (butuh bulan terpilih). Sama seperti Cashflow.
     */
    protected function usesSiklus(): bool
    {
        return $this->modePeriode === 'siklus20' && ! empty($this->bulan);
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    /**
     * Rentang siklus gaji untuk bulan/tahun terpilih — SATU sumber dengan fitur
     * Gaji & Cashflow, yaitu PeriodeGaji (setelan `payroll_cutoff_day`, default 20).
     * Mis. Juli = 21 Jun s/d 20 Jul.
     *
     * Sebelumnya di sini dipatok "tgl 20 s/d 19 bulan berikutnya" — itu meleset
     * dari periode gaji dan bikin task yang tampil di filter tidak sama dengan
     * task yang dihitung bonusnya. Kolom periode_bulan/periode_tahun sendiri
     * memang sudah memakai PeriodeGaji, jadi ini menyelaraskan filternya.
     *
     * Dikembalikan sebagai [mulai, akhirEksklusif] agar kontrak lama
     * (>= mulai, < akhirEksklusif) di render() tetap berlaku apa adanya.
     */
    protected function siklusRange(): array
    {
        $tahun = (int) ($this->tahun ?: now()->year);
        $bulan = (int) $this->bulan;

        return [
            PeriodeGaji::mulai($bulan, $tahun),
            PeriodeGaji::akhir($bulan, $tahun)->addDay()->startOfDay(),
        ];
    }

    protected function daftarBulan(): array
    {
        return [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    }

    protected function daftarTahun(): array
    {
        $y = (int) now()->year;

        return range($y, $y - 5);
    }

    public function openTask($id): void
    {
        // visibleTo memastikan hanya task miliknya (kecuali admin).
        $task = Task::visibleTo()->findOrFail($id);
        $this->activeTaskId = $task->id;
        $this->reset(['newComment', 'commentFile']);
        $this->showModal = true;
        $this->showGroupChat = false;

        // Hanya task SOLO yang menampilkan komentar inline di detail -> tandai dibaca.
        // Task GRUP: komentar ada di "Diskusi Grup", jadi membuka sub-card (untuk
        // progres) TIDAK menandai diskusi dibaca — hanya openGroupChat yang menandai.
        $isSolo = Task::where('group_id', $task->group_id)->count() <= 1;
        if ($isSolo) {
            $this->markGroupSeen($task);
        }
    }

    /** Buka diskusi grup (komentar dipusatkan di folder). */
    public function openGroupChat($groupId): void
    {
        // Anchor: sub-task milik saya di grup ini, atau sub-task pertama yang terlihat.
        $task = Task::visibleTo()->where('group_id', $groupId)->where('user_id', auth()->id())->first()
            ?? Task::visibleTo()->where('group_id', $groupId)->firstOrFail();

        $this->activeTaskId = $task->id;
        $this->reset(['newComment', 'commentFile']);
        $this->showGroupChat = true;
        $this->showModal = false;

        $this->markGroupSeen($task);
    }

    /** Tandai diskusi grup sudah dilihat: status baca + bersihkan notifikasi grup. */
    protected function markGroupSeen(Task $task): void
    {
        TaskCommentRead::updateOrCreate(
            ['group_id' => $task->group_id, 'user_id' => auth()->id()],
            ['task_id' => $task->id, 'last_read_at' => now()]
        );

        $groupTaskIds = Task::where('group_id', $task->group_id)->pluck('id')->all();
        auth()->user()->unreadNotifications()
            ->whereIn('data->task_id', $groupTaskIds)
            ->update(['read_at' => now()]);

        // Beri tahu komponen lonceng agar hitung ulang real-time (tanpa refresh).
        $this->dispatch('notifs-read');
    }

    public function mulaiKerjakan($id): void
    {
        $task = Task::visibleTo()->findOrFail($id);
        // Hanya pemilik task yang boleh mengubah progres (bukan atasan pemberi).
        if ($task->user_id !== auth()->id() || $task->isLocked()) {
            return;
        }
        $lama = $task->progress;
        $task->update(['progress' => 'dikerjakan']);
        $task->catat('status', $lama, 'dikerjakan');
        // Badge "Task Saya" di sidebar ikut dihitung ulang — angkanya berubah
        // begitu ada yang selesai atau dimulai.
        $this->dispatch('sidebar-badge-updated');
    }

    public function tandaiSelesai($id): void
    {
        $task = Task::visibleTo()->findOrFail($id);
        // Hanya pemilik task, harus "dikerjakan" dulu — tak boleh langsung selesai.
        if ($task->user_id !== auth()->id() || $task->isLocked() || $task->progress !== 'dikerjakan') {
            return;
        }
        // Bonus ikut periode gaji saat task BENAR-BENAR selesai, bukan deadline-nya.
        // Mis. deadline 10 Jun tapi selesai 30 Jun -> masuk gaji Juli (21 Jun–20 Jul),
        // sebab gaji Juni sudah dibayar 20 Jun & bonusnya akan terkunci di 0.
        $selesaiPada = now();
        $periode = PeriodeGaji::dariTanggal($selesaiPada);

        $lama = $task->progress;
        $task->update([
            'progress' => 'selesai',
            'completed_at' => $selesaiPada,
            'periode_bulan' => $periode['bulan'],
            'periode_tahun' => $periode['tahun'],
        ]);
        $task->catat('status', $lama, 'selesai');
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: 'Task ditandai selesai.');
    }

    public function addComment(): void
    {
        $this->validate([
            'newComment' => 'nullable|string|max:2000',
            'commentFile' => 'nullable|file|max:5120',
        ]);

        if (blank($this->newComment) && ! $this->commentFile) {
            return;
        }

        $task = Task::visibleTo()->findOrFail($this->activeTaskId);

        $path = null;
        $name = null;
        if ($this->commentFile) {
            $path = $this->commentFile->store('task_files', 'public');
            $name = $this->commentFile->getClientOriginalName();
        }

        $body = $this->newComment ?: null;

        TaskComment::create([
            'task_id' => $task->id,
            'group_id' => $task->group_id,
            'user_id' => auth()->id(),
            'body' => $body,
            'file_path' => $path,
            'file_name' => $name,
        ]);

        $this->notifyComment($task, $body);

        $this->reset(['newComment', 'commentFile']);
    }

    /** Pin/lepas-pin komentar (grup) — bisa banyak pin agar tak tenggelam. */
    public function togglePin($commentId): void
    {
        $task = Task::visibleTo()->findOrFail($this->activeTaskId);

        $c = TaskComment::where('group_id', $task->group_id)->find($commentId);
        if ($c) {
            $c->update(['pinned_at' => $c->pinned_at ? null : now()]);
        }
    }

    /**
     * Kirim notifikasi komentar ke seluruh pihak yang terlibat, mengikuti rantai
     * hierarki, kecuali penulis komentar:
     *  - Penerima task (user_id) + pemberi (assigned_by) + SEMUA atasan pemberi
     *    (rantai ke atas) → tautan ke halaman "Task Saya".
     *  - Admin ber-izin manage_task → tautan ke "Penyelesaian Task".
     * Kedua himpunan dibuat saling lepas (admin tidak dobel).
     */
    protected function notifyComment(Task $task, ?string $body = null): void
    {
        app(\App\Actions\Task\NotifyTaskCommentAction::class)->execute($task, auth()->user(), $body);
    }

    // ===== Beri task ke bawahan (atasan ber-izin assign_task) =====

    /** Task hanya bisa diberikan ke user yang ada di downline si atasan. */
    protected function assignableIds(): array
    {
        $u = auth()->user();

        // Pemegang manage_task tidak selalu punya bawahan di struktur, tetapi
        // ia tetap perlu bisa menugaskan — daftarnya jadi seluruh karyawan
        // selain dirinya sendiri.
        if ($u->hasPermission('manage_task') && empty($u->bawahanIds())) {
            return User::whereKeyNot($u->id)->whereHas('detail')->pluck('id')->all();
        }

        return $u->bawahanIds();
    }

    /**
     * ID pemberi task yang boleh SAYA kelola (edit/hapus/reopen): diri saya
     * sendiri + seluruh bawahan saya. Jadi atasan bisa mengelola task yang
     * diberikan oleh siapa pun di bawahnya. Task admin (assigned_by NULL) tak masuk.
     *
     * @return array<int>
     */
    protected function manageableGiverIds(): array
    {
        $u = auth()->user();

        return array_merge([$u->id], $u->bawahanIds());
    }

    /**
     * Boleh mengelola (edit / hapus / buka kembali) sebuah task?
     *
     * DUA jalur, bukan satu:
     *
     *  1. PEMEGANG manage_task — izin yang sama yang membuka layar
     *     Penyelesaian Task. Ia pengurus task perusahaan, jadi task apa pun
     *     yang terlihat olehnya boleh ia kelola.
     *  2. PEMBERINYA, atau atasan dari pemberinya.
     *
     * Sebelumnya hanya jalur kedua yang ada, dan syaratnya `assigned_by` ada
     * di daftar. Padahal task yang dibuat dari layar Penyelesaian Task
     * ber-assigned_by NULL — dan NULL tidak pernah cocok dengan whereIn().
     * Akibatnya seluruh task semacam itu tidak bisa dihapus SIAPA PUN,
     * termasuk administrator, sementara tombolnya memang tidak pernah muncul.
     */
    public function bolehKelolaTask(?Task $task): bool
    {
        if (! $task) {
            return false;
        }

        if (auth()->user()?->hasPermission('manage_task')) {
            return true;
        }

        return $task->assigned_by && in_array($task->assigned_by, $this->manageableGiverIds(), true);
    }

    /**
     * Boleh memberi task baru?
     *
     * Pemegang manage_task juga boleh, walau tidak punya bawahan seorang pun —
     * canAssignTask() mensyaratkan bawahan, dan itu benar untuk atasan biasa
     * tetapi menutup administrator yang memang tidak ada di dalam struktur.
     */
    public function bolehBeriTask(): bool
    {
        $u = auth()->user();

        return (bool) ($u?->canAssignTask() || $u?->hasPermission('manage_task'));
    }

    /** Ambil task yang boleh saya kelola atau 403/404. */
    protected function findManageableTask($id): Task
    {
        $task = Task::visibleTo()->findOrFail($id);

        abort_unless($this->bolehKelolaTask($task), 403);

        return $task;
    }

    protected function ensureCanAssign(): void
    {
        abort_unless($this->bolehBeriTask(), 403);
    }

    public function openCreateTask(): void
    {
        $this->ensureCanAssign();

        $this->reset(['editingTaskId', 'editingGroupId', 't_user_ids', 't_nama', 't_deskripsi', 't_files', 'newFiles',
            't_category_id', 't_label_id', 'newCategoryName', 'newLabelName', 't_langkah', 't_langkah_baru', 't_langkah_ada']);
        $this->t_bobot = 'sedang';
        $this->t_ulang = 'tidak';
        $this->t_langkah = [];
        $this->t_langkah_baru = '';
        $this->t_langkah_ada = [];
        $this->t_deadline_mulai = now()->toDateString();
        $this->t_deadline_selesai = now()->addDays(7)->toDateString();
        $this->showTaskModal = true;
    }

    public function openEditTask($id): void
    {
        $this->ensureCanAssign();

        // Boleh mengedit task yang diberikan oleh diri sendiri ATAU bawahannya.
        $task = $this->findManageableTask($id);

        // Edit seluruh GRUP: muat semua sub-task (semua penerima) dalam grup ini.
        $group = Task::where('group_id', $task->group_id)->get();

        $this->editingTaskId = $task->id;
        $this->editingGroupId = $task->group_id;
        $this->t_user_ids = $group->pluck('user_id')->map(fn ($v) => (string) $v)->values()->all();
        $this->t_nama = $task->nama;
        $this->t_deskripsi = $task->deskripsi;
        $this->t_category_id = $task->task_category_id ?? '';
        $this->t_label_id = $task->task_category_label_id ?? '';
        $this->t_bobot = $task->bobot;
        $this->t_ulang = $task->ulang ?? 'tidak';
        $this->t_langkah = [];
        $this->t_langkah_baru = '';
        // Yang sudah ada ditampilkan sebagai keterangan saja — lihat catatan
        // di $t_langkah_ada.
        $this->t_langkah_ada = $task->checklists()->orderBy('urutan')->pluck('teks')->all();
        $this->t_deadline_mulai = $task->deadline_mulai?->toDateString();
        $this->t_deadline_selesai = $task->deadline_selesai?->toDateString();
        $this->t_files = [];
        $this->newFiles = [];
        $this->showTaskModal = true;
    }

    // Ganti kategori -> reset label (daftar label milik kategori lain).
    public function updatedTCategoryId(): void
    {
        $this->t_label_id = '';
    }

    // ===== Kelola Kategori & Label dari popup picker (tambah/hapus) =====
    public function addCategory(): void
    {
        $this->ensureCanAssign();

        $this->validate(
            ['newCategoryName' => 'required|string|max:100|unique:task_categories,nama'],
            [],
            ['newCategoryName' => 'nama kategori']
        );

        $cat = TaskCategory::create(['nama' => trim($this->newCategoryName)]);
        $this->t_category_id = $cat->id;
        $this->t_label_id = '';
        $this->newCategoryName = '';
    }

    public function deleteCategory($id): void
    {
        $this->ensureCanAssign();

        // Label ikut terhapus (cascade); task lama jadi tanpa kategori (nullOnDelete).
        TaskCategory::whereKey($id)->delete();

        if ((string) $this->t_category_id === (string) $id) {
            $this->t_category_id = '';
            $this->t_label_id = '';
        }
    }

    public function addLabel(): void
    {
        $this->ensureCanAssign();

        if (blank($this->t_category_id)) {
            return;
        }

        $this->validate(
            ['newLabelName' => ['required', 'string', 'max:100',
                Rule::unique('task_category_labels', 'nama')->where('task_category_id', $this->t_category_id)]],
            [],
            ['newLabelName' => 'nama label']
        );

        $label = TaskCategoryLabel::create([
            'task_category_id' => $this->t_category_id,
            'nama' => trim($this->newLabelName),
        ]);
        $this->t_label_id = $label->id;
        $this->newLabelName = '';
    }

    public function deleteLabel($id): void
    {
        $this->ensureCanAssign();

        TaskCategoryLabel::whereKey($id)->delete();

        if ((string) $this->t_label_id === (string) $id) {
            $this->t_label_id = '';
        }
    }

    // Tiap file dipilih, gabungkan ke daftar terkumpul agar bisa menumpuk.
    public function updatedNewFiles(): void
    {
        $this->validate(
            ['newFiles.*' => 'file|max:2048'],
            ['newFiles.*.max' => 'Ukuran file maksimal 2 MB (batas server).', 'newFiles.*.file' => 'Berkas tidak valid.']
        );

        foreach ((array) $this->newFiles as $f) {
            if ($f) {
                $this->t_files[] = $f;
            }
        }
        $this->newFiles = [];
    }

    public function removeNewFile($index): void
    {
        if (isset($this->t_files[$index])) {
            unset($this->t_files[$index]);
            $this->t_files = array_values($this->t_files);
        }
    }

    public function removeAttachment($id): void
    {
        $this->ensureCanAssign();

        // Jebakan yang sama dengan hapus task: task dari layar Penyelesaian
        // Task ber-assigned_by NULL, dan NULL tidak pernah cocok dengan
        // whereIn — lampirannya lalu tidak bisa dihapus siapa pun.
        $att = TaskAttachment::with('task')->find($id);

        if (! $att || ! $att->task || ! $this->bolehKelolaTask($att->task)) {
            return;
        }

        // Lampiran disalin ke tiap sub-task grup -> hapus semua salinannya sekaligus.
        $groupTaskIds = Task::where('group_id', $att->task->group_id)->pluck('id');
        TaskAttachment::whereIn('task_id', $groupTaskIds)->where('path', $att->path)->delete();

        if ($att->path && \Illuminate\Support\Facades\Storage::disk('public')->exists($att->path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($att->path);
        }
    }

    public function saveTask(): void
    {
        $this->ensureCanAssign();

        $this->validate([
            't_user_ids' => ['required', 'array', 'min:1'],
            't_user_ids.*' => ['exists:users,id', function ($attr, $value, $fail) {
                if (! in_array((int) $value, $this->assignableIds(), true)) {
                    $fail('Anda hanya bisa memberi task kepada bawahan Anda.');
                }
            }],
            't_nama' => 'required|string|max:200',
            't_bobot' => 'required|in:ringan,sedang,berat',
            't_ulang' => 'required|in:tidak,mingguan,bulanan',
            't_deadline_mulai' => 'required|date',
            't_deadline_selesai' => 'required|date|after_or_equal:t_deadline_mulai',
            't_files.*' => 'nullable|file|max:2048',
        ], [
            't_files.*.max' => 'Ukuran file maksimal 2 MB (batas server).',
        ], [
            't_user_ids' => 'penerima',
            't_nama' => 'nama task',
            't_deadline_selesai' => 'deadline selesai',
        ]);

        $akhir = Carbon::parse($this->t_deadline_selesai);
        $categoryId = $this->t_category_id ?: null;
        $labelId = ($categoryId && $this->t_label_id) ? $this->t_label_id : null;

        // Task baru belum selesai -> periode dipatok dari deadline sbg perkiraan,
        // nanti ditandai ulang dari tanggal selesai saat tandaiSelesai().
        $periodeTask = PeriodeGaji::dariTanggal($akhir);

        // Field bersama untuk semua sub-task dalam grup.
        $shared = [
            'periode_bulan' => $periodeTask['bulan'],
            'periode_tahun' => $periodeTask['tahun'],
            'nama' => $this->t_nama,
            'deskripsi' => $this->t_deskripsi,
            'task_category_id' => $categoryId,
            'task_category_label_id' => $labelId,
            'bobot' => $this->t_bobot,
            'ulang' => $this->t_ulang,
            'deadline_mulai' => $this->t_deadline_mulai,
            'deadline_selesai' => $this->t_deadline_selesai,
        ];

        $userIds = array_values(array_unique(array_map('intval', $this->t_user_ids)));

        // Simpan file sekali; di-link ke tiap sub-task saat fan-out.
        $storedFiles = [];
        foreach ((array) $this->t_files as $file) {
            if ($file) {
                $storedFiles[] = ['path' => $file->store('task_files', 'public'), 'name' => $file->getClientOriginalName()];
            }
        }

        DB::transaction(function () use ($shared, $userIds, $storedFiles) {
            if ($this->editingTaskId) {
                $this->updateGroup($shared, $userIds, $storedFiles);
            } else {
                $this->createGroup($shared, $userIds, $storedFiles);
            }
        });

        $this->reset('t_files');
        $this->showTaskModal = false;
    }

    /** Buat grup baru: satu sub-task per penerima, berbagi group_id. */
    private function createGroup(array $shared, array $userIds, array $storedFiles): void
    {
        $groupId = (string) Str::uuid();

        foreach ($userIds as $uid) {
            $task = Task::create($shared + [
                'group_id' => $groupId,
                'user_id' => $uid,
                'assigned_by' => auth()->id(),
                'created_by' => auth()->id(),
            ]);
            $this->attachFilesTo($task, $storedFiles);
            $this->pasangLangkah($task);
            $task->catat('dibuat');
            $task->karyawan?->notify(new TaskAssigned($task));
            $task->update(['assigned_notified_at' => now()]);
        }

        $this->dispatch('swal-success', message: 'Task dibuat & dikirim ke '.count($userIds).' penerima.');
    }

    /** Update grup: ubah field bersama, tambah penerima baru, hapus yang dibuang. */
    private function updateGroup(array $shared, array $userIds, array $storedFiles): void
    {
        // visibleTo() + bolehKelolaTask(), BUKAN whereIn('assigned_by', …):
        // task yang dibuat dari layar Penyelesaian Task ber-assigned_by NULL,
        // dan NULL tidak pernah cocok dengan whereIn — grup semacam itu dulu
        // gagal disimpan tanpa satu pun pesan.
        $existing = Task::visibleTo()->where('group_id', $this->editingGroupId)->get();

        if ($existing->isEmpty() || $existing->contains(fn ($t) => ! $this->bolehKelolaTask($t))) {
            return;
        }

        $byUser = $existing->keyBy('user_id');

        foreach ($userIds as $uid) {
            if ($t = $byUser->get($uid)) {
                // Tenggat lama dibaca SEBELUM update, dan dicatat per sub-task:
                // satu grup menyebar ke banyak orang, dan tiap orang punya
                // riwayatnya sendiri.
                $tenggatLama = $t->deadline_selesai?->locale('id')->translatedFormat('d M Y');

                $t->update($shared); // progres & completed_at tidak diubah

                $tenggatBaru = $t->fresh()->deadline_selesai?->locale('id')->translatedFormat('d M Y');
                if ($tenggatLama !== $tenggatBaru) {
                    $t->catat('tenggat', $tenggatLama, $tenggatBaru);
                }
            } else {
                $t = Task::create($shared + [
                    'group_id' => $this->editingGroupId,
                    'user_id' => $uid,
                    'assigned_by' => auth()->id(),
                ]);
                $t->karyawan?->notify(new TaskAssigned($t));
                $t->update(['assigned_notified_at' => now()]);
            }
            $this->attachFilesTo($t, $storedFiles);
            $this->pasangLangkah($t);
        }

        // Penerima yang dihapus dari daftar -> sub-task-nya dihapus.
        foreach ($existing as $t) {
            if (! in_array((int) $t->user_id, $userIds, true)) {
                $t->delete();
            }
        }

        $this->dispatch('swal-success', message: 'Task grup berhasil diperbarui.');
    }

    /** @param  array<array{path:string,name:string}>  $storedFiles */
    private function attachFilesTo(Task $task, array $storedFiles): void
    {
        foreach ($storedFiles as $f) {
            TaskAttachment::create([
                'task_id' => $task->id,
                'uploaded_by' => auth()->id(),
                'path' => $f['path'],
                'name' => $f['name'],
            ]);
        }
    }

    public function deleteTask($id): void
    {
        $task = Task::visibleTo()->find($id);

        if (! $this->bolehKelolaTask($task)) {
            // Berkata jujur saat tidak ada yang terhapus. Versi lama selalu
            // menjawab "Task dihapus." walau kuerinya menghapus nol baris —
            // dan tidak ada cara bagi siapa pun untuk menyadarinya.
            $this->dispatch('swal-error', message: 'Anda tidak berhak menghapus task ini.');

            return;
        }

        $task->delete();
        $this->dispatch('swal-success', message: 'Task dihapus.');
    }

    /** Hapus seluruh grup (semua sub-task/penerima) sekaligus. */
    public function deleteGroup($groupId): void
    {
        $anggota = Task::visibleTo()->where('group_id', $groupId)->get();

        // Satu anggota saja yang tidak boleh dikelola sudah cukup untuk
        // menolak: menghapus separuh grup meninggalkan pekerjaan yang
        // penerimanya tinggal sebagian, dan tidak ada layar yang menjelaskan
        // kenapa.
        if ($anggota->isEmpty() || $anggota->contains(fn ($t) => ! $this->bolehKelolaTask($t))) {
            $this->dispatch('swal-error', message: 'Anda tidak berhak menghapus task ini.');

            return;
        }

        Task::where('group_id', $groupId)->delete();
        $this->dispatch('swal-success', message: 'Seluruh grup task dihapus.');
    }

    // ===== Buka Kembali (revisi task terkunci) — hanya untuk task yang IA berikan =====
    public function openReopen($id): void
    {
        $this->ensureCanAssign();

        $task = $this->findManageableTask($id);
        $this->reopenTaskId = $task->id;
        $this->reopen_alasan = '';
        $this->reopen_label_id = $task->task_category_label_id ?? '';
        // Default deadline baru: 7 hari dari hari ini (revisi biasanya butuh waktu baru).
        $this->reopen_deadline = now()->addDays(7)->toDateString();
        $this->showReopenModal = true;
        // Tutup modal detail bila terbuka agar tidak bertumpuk.
        $this->showModal = false;
    }

    public function bukaKembali(): void
    {
        $this->ensureCanAssign();

        $this->validate([
            'reopen_alasan' => 'required|string|max:2000',
            'reopen_deadline' => 'required|date',
        ], [], [
            'reopen_alasan' => 'alasan revisi',
            'reopen_deadline' => 'deadline baru',
        ]);

        $task = $this->findManageableTask($this->reopenTaskId);
        $deadline = Carbon::parse($this->reopen_deadline);

        // Label baru hanya diterima bila memang milik kategori task ini.
        $labelId = null;
        if ($this->reopen_label_id) {
            $labelId = TaskCategoryLabel::where('id', $this->reopen_label_id)
                ->where('task_category_id', $task->task_category_id)
                ->value('id');
        }

        // Dibuka lagi -> completed_at dihapus, jadi periode kembali dipatok dari
        // deadline baru; akan ditandai ulang dari tanggal selesai putaran ini.
        $periodeReopen = PeriodeGaji::dariTanggal($deadline);

        // Dibaca SEBELUM update: sesudahnya sudah tertimpa, dan riwayatnya
        // akan mencatat "dari X ke X".
        $tenggatLama = $task->deadline_selesai?->locale('id')->translatedFormat('d M Y');

        $task->update([
            'progress' => 'dikerjakan',
            'completed_at' => null,
            'deadline_selesai' => $this->reopen_deadline,
            'periode_bulan' => $periodeReopen['bulan'],
            'periode_tahun' => $periodeReopen['tahun'],
            'task_category_label_id' => $labelId,
            // Buka lagi peluang notifikasi deadline/overdue untuk putaran revisi ini.
            'deadline_notified_at' => null,
            'overdue_notified_at' => null,
        ]);

        $task->catat(
            'dibuka-kembali',
            $tenggatLama,
            $deadline->locale('id')->translatedFormat('d M Y'),
            $this->reopen_alasan,
        );

        // Catat alasan sebagai komentar bertipe "revisi" (ditandai badge di thread grup).
        TaskComment::create([
            'task_id' => $task->id,
            'group_id' => $task->group_id,
            'user_id' => auth()->id(),
            'body' => $this->reopen_alasan,
            'type' => 'revisi',
        ]);
        // Notifikasi revisi khusus ke bawahan pemilik task.
        $task->karyawan?->notify(new TaskReopened($task, auth()->user()->name, $this->reopen_alasan));

        $this->showReopenModal = false;
        $this->reset(['reopenTaskId', 'reopen_alasan', 'reopen_label_id', 'reopen_deadline']);
        $this->dispatch('swal-success', message: 'Task dibuka kembali untuk revisi.');
    }

    #[Layout('livewire.layout.templateindex')]
    /**
     * Data untuk tampilan "Aktivitas" (ala GitHub) — HANYA dihitung saat mode itu
     * dipilih, jadi tampilan lain tetap seringan sebelumnya.
     *
     * Memakai Task::visibleTo() yang SAMA dengan render(), jadi aturan siapa boleh
     * melihat task siapa tetap persis seperti semula — tidak ada yang dilonggarkan.
     *
     * Grafik memakai satu TAHUN penuh (seperti GitHub yang per tahun). Tahun
     * diambil dari filter tahun yang sudah ada; kalau kosong, tahun berjalan.
     *
     * @return array{tahun:int,perHari:array<string,int>,total:int,streak:int,
     *               terbaik:array{tanggal:?string,jumlah:int},rataMingguan:float,
     *               linimasa:\Illuminate\Support\Collection}
     */
    protected function dataAktivitas(): array
    {
        $tahun = (int) ($this->tahun ?: now()->year);
        $awal = Carbon::create($tahun, 1, 1)->startOfDay();
        $akhir = Carbon::create($tahun, 12, 31)->endOfDay();

        $selesai = Task::visibleTo()
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$awal, $akhir])
            // Saringan yang sedang aktif ikut berlaku di sini. Status sengaja
            // DILEWATI: grafik ini memang hanya tentang yang sudah selesai,
            // jadi menyaring "belum dikerjakan" akan selalu mengosongkannya.
            ->tap(fn ($q) => $this->saring($q, denganStatus: false))
            ->with(['karyawan', 'category', 'label'])
            ->orderByDesc('completed_at')
            ->get();

        // Jumlah task selesai per tanggal → sumber warna kotak heatmap.
        $perHari = $selesai
            ->groupBy(fn (Task $t) => $t->completed_at->toDateString())
            ->map->count()
            ->all();

        // Hari paling produktif.
        $terbaikTgl = null;
        $terbaikJml = 0;
        foreach ($perHari as $tgl => $jml) {
            if ($jml > $terbaikJml) {
                $terbaikJml = $jml;
                $terbaikTgl = $tgl;
            }
        }

        // Streak = rentetan hari berturut-turut ada task selesai (terpanjang di tahun ini).
        $streak = 0;
        $jalan = 0;
        $kursor = $awal->copy();
        $batas = $akhir->copy()->min(now()->endOfDay());
        while ($kursor->lte($batas)) {
            if (($perHari[$kursor->toDateString()] ?? 0) > 0) {
                $jalan++;
                $streak = max($streak, $jalan);
            } else {
                $jalan = 0;
            }
            $kursor->addDay();
        }

        // Rata-rata per minggu (dibagi minggu yang sudah berjalan di tahun itu).
        $hariBerjalan = $tahun === (int) now()->year
            ? max(1, $awal->diffInDays(now()) + 1)
            : 365;
        $rataMingguan = round($selesai->count() / max(1, $hariBerjalan / 7), 1);

        return [
            'tahun' => $tahun,
            'perHari' => $perHari,
            'total' => $selesai->count(),
            'streak' => $streak,
            'terbaik' => ['tanggal' => $terbaikTgl, 'jumlah' => $terbaikJml],
            'rataMingguan' => $rataMingguan,
            'linimasa' => $selesai->take(12),
        ];
    }

    /**
     * Saringan yang TIDAK menyangkut periode: kata kunci, status, penerima,
     * kategori, dan hubungan.
     *
     * Ditulis sekali dan dipakai bersama oleh tabel/papan DAN grafik
     * aktivitas. Sebelumnya aktivitas hanya menghormati tahun: menyaring
     * "Penerima: Aulia" lalu berpindah ke tab Aktivitas menampilkan aktivitas
     * SEMUA orang, tanpa satu pun tanda bahwa saringannya dibuang — angkanya
     * benar untuk pertanyaan yang tidak diajukan siapa pun.
     */
    protected function saring($query, bool $denganStatus = true)
    {
        return $query
            ->when($this->cari !== '', function ($q) {
                $kata = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($this->cari)).'%';
                $q->where(fn ($qq) => $qq->where('nama', 'like', $kata)->orWhere('deskripsi', 'like', $kata));
            })
            ->when($denganStatus && $this->saringStatus !== '', function ($q) {
                // 'telat' bukan nilai kolom progress — ia turunan dari tenggat
                // yang lewat sementara pekerjaannya belum selesai.
                if ($this->saringStatus === 'telat') {
                    $q->where('progress', '!=', 'selesai')->whereDate('deadline_selesai', '<', today());
                } else {
                    $q->where('progress', $this->saringStatus);
                }
            })
            ->when($this->saringOrang !== '', fn ($q) => $q->where('user_id', $this->saringOrang))
            ->when($this->saringKategori !== '', fn ($q) => $q->where('task_category_id', $this->saringKategori))
            ->when($this->saringArah === 'saya', fn ($q) => $q->where('user_id', auth()->id()))
            ->when($this->saringArah === 'dari-saya', fn ($q) => $q->where('assigned_by', auth()->id()));
    }

    /**
     * Susunan kartu ringkasan: mana yang menempati slot utama kedua, kartu
     * kecil apa saja, dan selebar apa masing-masing.
     *
     * Ditulis di sini, bukan sebagai rantai @if di Blade: dengan rantai, satu
     * keadaan yang tidak terpikir — administrator tanpa task sendiri —
     * membuat kartu "Selesai" hilang sekaligus meninggalkan lubang di ujung
     * barisnya. Sebagai daftar, tiap kartu pasti terpakai tepat sekali dan
     * lebarnya dihitung dari jumlahnya.
     *
     * @return array{utama_kedua: string, kecil: array<int, string>, lebar_kecil: string}
     */
    public static function susunanKartu(bool $adaPoin, bool $adaBonus): array
    {
        // Slot utama kedua diisi kartu yang paling berarti bagi pembacanya:
        // poin bagi yang punya task sendiri, bonus bagi administrator yang
        // tidak punya, selesai bagi yang tidak punya keduanya.
        $utamaKedua = $adaPoin ? 'poin' : ($adaBonus ? 'bonus' : 'selesai');

        $kecil = ['hari-ini', 'lewat'];
        if ($utamaKedua !== 'selesai') {
            $kecil[] = 'selesai';
        }
        if ($adaBonus && $utamaKedua !== 'bonus') {
            $kecil[] = 'bonus';
        }

        return [
            'utama_kedua' => $utamaKedua,
            'kecil' => $kecil,
            // Barisnya harus selalu penuh — deretan kartu dengan lubang di
            // ujung adalah hal pertama yang membuat halaman terbaca tidak rapi.
            'lebar_kecil' => [1 => 'k-12', 2 => 'k-6', 3 => 'k-4', 4 => 'k-3'][count($kecil)] ?? 'k-3',
        ];
    }

    /**
     * Poin task MILIK SAYA pada periode yang sedang ditampilkan.
     *
     * Poin — bukan rupiah. Besaran rupiahnya bergantung pada pool anggaran
     * periode itu dan hanya boleh dilihat pemegang view_all_gajikaryawan.
     *
     * Persentasenya sengaja memakai konstanta yang SAMA dengan perhitungan
     * uangnya (BonusTaskPeriodeAction::STATUS_PERSEN), jadi poin di layar ini
     * tidak akan pernah bercerita hal yang berbeda dengan slip gajinya.
     *
     * @return array{didapat: float, maksimum: int, persen: ?int, selesai: int, total: int}
     */
    protected function poinTaskSaya($tasks): array
    {
        $milikSaya = $tasks->where('user_id', auth()->id());

        $maks = 0;
        $didapat = 0.0;
        $selesai = 0;

        foreach ($milikSaya as $t) {
            $poin = $t->bobotPoin();
            $maks += $poin;

            $status = $t->bonusStatus();
            if ($status === 'selesai' || $t->progress === 'selesai') {
                $selesai++;
            }

            $didapat += $poin * (\App\Actions\Gaji\BonusTaskPeriodeAction::STATUS_PERSEN[$status] ?? 0);
        }

        return [
            'didapat' => round($didapat, 1),
            'maksimum' => $maks,
            'persen' => $maks > 0 ? (int) round($didapat / $maks * 100) : null,
            'selesai' => $selesai,
            'total' => $milikSaya->count(),
        ];
    }

    /**
     * Kueri daftar task: periode + saringan + urutan yang sedang aktif.
     *
     * Dipakai bersama oleh layar dan oleh unduhan Excel, supaya berkas yang
     * diunduh berisi PERSIS apa yang barusan disusun di layar — bukan hasil
     * kueri kedua yang kebetulan mirip.
     */
    protected function kueriDaftar()
    {
        return Task::visibleTo()
            ->with(['groupComments', 'category', 'label', 'pemberi', 'pembuat', 'karyawan'])
            // Jumlah lampiran ikut dihitung di kueri yang sama — tanpa ini tiap
            // baris tabel akan memicu kuerinya sendiri saat menampilkan angkanya.
            ->withCount([
                'attachments',
                // Kemajuan langkah dihitung di kueri yang SAMA — tanpa ini tiap
                // baris tabel akan memicu dua kueri sendiri saat menampilkannya.
                'checklists',
                'checklists as checklists_selesai_count' => fn ($q) => $q->where('selesai', true),
                // Berkas HASIL dihitung terpisah: lampiran perintah disalin ke
                // tiap penerima, jadi jumlah lampiran biasa sama di semua baris
                // dan tidak memberi tahu siapa yang sudah mengunggah hasilnya.
                'attachments as hasil_count' => fn ($q) => $q->where('jenis', 'hasil'),
            ])
            ->when($this->usesSiklus(), function ($q) {
                // Siklus gaji 21–20: filter berdasarkan tanggal deadline_selesai.
                [$mulai, $akhir] = $this->siklusRange();
                $q->whereDate('deadline_selesai', '>=', $mulai->toDateString())
                    ->whereDate('deadline_selesai', '<', $akhir->toDateString());
            }, function ($q) {
                // Kalender: berdasarkan bulan/tahun periode (dari deadline_selesai).
                $q->when($this->bulan, fn ($qq) => $qq->where('periode_bulan', $this->bulan))
                    ->when($this->tahun, fn ($qq) => $qq->where('periode_tahun', $this->tahun));
            })
            // Saringan daftar — SATU tempat, dipakai juga oleh tampilan
            // Aktivitas. Lihat catatan di saring().
            ->tap(fn ($q) => $this->saring($q))
            // Urutan bawaan: YANG TERBARU DI ATAS. Urutan lama mendahulukan yang
            // jatuh tempo hari ini lalu progresnya, sehingga task yang baru saja
            // diberikan bisa mendarat di tengah daftar dan tidak terlihat sudah
            // masuk. Yang mendesak tidak hilang, hanya pindah cara tandanya:
            // pita warna di tepi baris dan kartu ringkasan di atas.
            ->when($this->urut === 'tenggat', fn ($q) => $q->orderBy('deadline_selesai', $this->arahUrut))
            ->when($this->urut === 'nama', fn ($q) => $q->orderBy('nama', $this->arahUrut))
            ->when($this->urut === 'status', fn ($q) => $q->orderByRaw("FIELD(progress,'belum','dikerjakan','selesai') ".($this->arahUrut === 'asc' ? 'asc' : 'desc')))
            // Penerima & pemberi adalah RELASI, jadi tidak bisa orderBy kolom
            // biasa. Subkueri nama berjalan di MySQL maupun SQLite, sehingga
            // pengujian tetap menguji hal yang sama dengan yang dijalankan.
            ->when($this->urut === 'penerima', fn ($q) => $q->orderBy(
                User::select('name')->whereColumn('users.id', 'tasks.user_id'), $this->arahUrut
            ))
            ->when($this->urut === 'pemberi', fn ($q) => $q->orderBy(
                User::select('name')->whereColumn('users.id', 'tasks.assigned_by'), $this->arahUrut
            ))
            ->orderBy('created_at', $this->urut === 'terbaru' ? $this->arahUrut : 'desc');
    }

    public function render()
    {
        $tasks = $this->kueriDaftar()->get();

        $activeTask = $this->activeTaskId
            ? Task::visibleTo()
                ->with(['groupComments.user', 'attachments', 'pemberi', 'karyawan', 'checklists', 'riwayats.pelaku'])
                ->find($this->activeTaskId)
            : null;

        // Task solo (grup beranggota 1) menampilkan diskusi inline; grup di folder.
        $activeIsSolo = $activeTask
            ? Task::where('group_id', $activeTask->group_id)->count() <= 1
            : true;

        // Nama depan tiap penerima grup untuk fitur @mention di chat grup.
        $chatMembers = $activeTask
            ? User::whereIn('id', Task::where('group_id', $activeTask->group_id)->pluck('user_id')->all())
                ->pluck('name')
                ->map(fn ($n) => Str::of($n)->trim()->explode(' ')->first())
                ->filter()->unique()->values()->all()
            : [];

        // Data untuk modal beri task (hanya relevan bila boleh assign).
        $canAssign = $this->bolehBeriTask();
        $bawahan = $canAssign
            ? User::whereIn('id', $this->assignableIds())->orderBy('name')->get(['id', 'name'])
            : collect();
        $categories = $canAssign ? TaskCategory::orderBy('nama')->get() : collect();
        $categoryLabels = ($canAssign && $this->t_category_id)
            ? TaskCategoryLabel::where('task_category_id', $this->t_category_id)->orderBy('nama')->get()
            : collect();
        $editAttachments = $this->editingGroupId
            ? TaskAttachment::whereIn('task_id', Task::where('group_id', $this->editingGroupId)->pluck('id'))
                ->latest()->get()->unique('path')->values()
            : collect();

        // Sama: jendela "Buka Kembali" dulu tidak menemukan apa pun untuk task
        // ber-assigned_by NULL, sehingga terbuka dalam keadaan kosong.
        $reopenTask = $this->reopenTaskId
            ? Task::visibleTo()->with('category.labels')->find($this->reopenTaskId)
            : null;

        if ($reopenTask && ! $this->bolehKelolaTask($reopenTask)) {
            $reopenTask = null;
        }

        // Pemberi task yang boleh saya kelola (untuk memunculkan tombol di kartu/modal).
        $manageGiverIds = $this->manageableGiverIds();

        // Status baca komentar per user PER GROUP (untuk badge "komentar baru" kartu).
        $reads = TaskCommentRead::where('user_id', auth()->id())
            ->whereIn('group_id', $tasks->pluck('group_id'))
            ->pluck('last_read_at', 'group_id')
            ->all();

        // Rentang siklus dikirim dari sini supaya view TIDAK menghitung ulang
        // sendiri — kalau view punya rumus sendiri, chip di layar bisa beda dgn
        // data yang benar-benar difilter. 'siklusAkhir' sudah inklusif (siap tampil).
        $siklusMulai = $siklusAkhir = null;
        if ($this->usesSiklus()) {
            [$sm, $saEks] = $this->siklusRange();
            $siklusMulai = $sm;
            $siklusAkhir = $saEks->copy()->subDay();
        }

        // ===== Halaman tabel, dihitung atas GRUP =====
        // Dipenggal per grup, bukan per baris: memenggal per baris bisa
        // memotong satu task grup di tengah, sehingga sebagian penerimanya
        // pindah ke halaman berikutnya tanpa induknya.
        $semuaGrup = $tasks->groupBy('group_id')->toBase();
        $totalGrup = $semuaGrup->count();
        $totalHalaman = max(1, (int) ceil($totalGrup / self::PER_HALAMAN));
        $halamanKini = min(max(1, $this->halaman), $totalHalaman);
        $grupHalaman = $semuaGrup->slice(($halamanKini - 1) * self::PER_HALAMAN, self::PER_HALAMAN);
        $this->gidHalaman = array_map('strval', array_keys($grupHalaman->all()));

        // ===== Poin task SAYA =====
        // Poin, bukan rupiah. Nilai rupiahnya urusan penggajian dan hanya
        // boleh dilihat pemegang view_all_gajikaryawan — lihat $bonusRupiah.
        $poin = $this->poinTaskSaya($tasks);

        // ===== Nilai rupiah bonus (ADMIN SAJA) =====
        // Dijaga izin view_all_gajikaryawan, izin yang sama yang memisahkan
        // "boleh melihat gaji orang lain" dari "boleh melihat gaji sendiri".
        // Karyawan TIDAK BOLEH melihat angka ini.
        $bonusRupiah = null;
        if (auth()->user()?->hasPermission('view_all_gajikaryawan') && $this->bulan && $this->tahun) {
            $d = (new \App\Actions\Gaji\BonusTaskPeriodeAction)->distribusi((int) $this->bulan, (int) $this->tahun);
            $bonusRupiah = [
                'pool' => $d['pool'],
                'terpakai' => $d['terpakai'],
                'sisa' => $d['sisa'],
            ];
        }

        // Pilihan saringan diambil dari task yang TERLIHAT, bukan seluruh tabel:
        // daftar nama yang memuat orang tanpa satu pun task hanya menambah
        // pilihan yang selalu menghasilkan layar kosong.
        $daftarOrang = $tasks->map(fn ($t) => ['id' => $t->user_id, 'nama' => $t->karyawan?->name])
            ->filter(fn ($o) => $o['id'] && $o['nama'])->unique('id')->sortBy('nama')->values();
        $daftarKategoriSaring = $tasks->map(fn ($t) => $t->category)
            ->filter()->unique('id')->sortBy('nama')->values();

        return view('livewire.pages.admin.task.task-saya-list', [
            // Dikirim sebagai data, bukan dibaca lewat $this di Blade: view yang
            // memanggil metode komponennya sendiri hanya bisa dirender oleh
            // Livewire, dan itu membuatnya mustahil diuji terpisah.
            'adaSaringan' => $this->adaSaringan(),
            'grupHalaman' => $grupHalaman,
            'gidHalaman' => $this->gidHalaman,
            'halamanKini' => $halamanKini,
            'totalHalaman' => $totalHalaman,
            'totalGrup' => $totalGrup,
            'poin' => $poin,
            'bonusRupiah' => $bonusRupiah,
            'daftarOrang' => $daftarOrang,
            'daftarKategoriSaring' => $daftarKategoriSaring,
            // Data aktivitas hanya dihitung bila mode itu yang dipilih.
            'aktivitas' => $this->tampilan === 'aktivitas' ? $this->dataAktivitas() : null,
            'siklusMulai' => $siklusMulai,
            'siklusAkhir' => $siklusAkhir,
            'tasks' => $tasks,
            'activeTask' => $activeTask,
            'activeIsSolo' => $activeIsSolo,
            'chatMembers' => $chatMembers,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
            'canAssign' => $canAssign,
            'bawahan' => $bawahan,
            'categories' => $categories,
            'categoryLabels' => $categoryLabels,
            'editAttachments' => $editAttachments,
            'reopenTask' => $reopenTask,
            'reads' => $reads,
            'manageGiverIds' => $manageGiverIds,
            // Pemegang manage_task boleh mengelola task apa pun yang terlihat
            // olehnya. Dikirim sebagai bendera terpisah supaya tampilan bisa
            // memakai ATURAN YANG SAMA dengan server tanpa memanggil metode
            // komponen dari dalam Blade.
            'bolehKelolaSemua' => (bool) auth()->user()?->hasPermission('manage_task'),
        ]);
    }
}
