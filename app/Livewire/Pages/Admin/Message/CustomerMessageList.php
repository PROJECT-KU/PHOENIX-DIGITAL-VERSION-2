<?php

namespace App\Livewire\Pages\Admin\Message;

use App\Exports\PesanPelangganExport;
use App\Models\CustomerMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class CustomerMessageList extends Component
{
    use WithPagination;

    /** baru | berjalan | selesai | semua | arsip — tab yang sekaligus kartu hitungan. */
    #[Url(as: 'tab', except: 'baru')]
    public string $tab = 'baru';

    #[Url(as: 'cari', except: '')]
    public string $search = '';

    // ===== Saringan lanjutan =====
    #[Url(as: 'status', except: '')]
    public string $fStatus = '';

    #[Url(as: 'prioritas', except: '')]
    public string $fPrioritas = '';

    #[Url(as: 'topik', except: '')]
    public string $fKategori = '';

    /** '' | 'saya' | id petugas */
    #[Url(as: 'petugas', except: '')]
    public string $fPetugas = '';

    /** '' | 'lewat' (lewat batas waktu membalas) | 'belum' (belum dibalas) */
    #[Url(as: 'batas', except: '')]
    public string $fBatas = '';

    /** '' | 'spam' | 'biasa' — hanya berlaku di tab Arsip. */
    #[Url(as: 'spam', except: '')]
    public string $fSpam = '';

    /** '1' = hanya pengirim yang pernah kirim spam sebelumnya. */
    #[Url(as: 'curiga', except: '')]
    public string $fCuriga = '';

    #[Url(as: 'dari', except: '')]
    public string $fDari = '';

    #[Url(as: 'sampai', except: '')]
    public string $fSampai = '';

    /** baru | lama | prioritas — 'prioritas' menaikkan yang paling mendesak. */
    #[Url(as: 'urut', except: 'baru')]
    public string $urut = 'baru';

    /** kartu | tabel — tabel untuk memindai banyak tiket di layar lebar. */
    #[Url(as: 'tampilan', except: 'kartu')]
    public string $tampilan = 'kartu';

    #[Url(as: 'per', except: 12)]
    public int $perPage = 12;

    /** Id pesan yang dicentang untuk aksi massal. */
    public array $pilih = [];

    public const TAB = ['baru', 'berjalan', 'selesai', 'semua', 'arsip'];

    /**
     * Urutan daftar. Tiga yang pertama muncul di menu; sisanya dipakai kepala
     * kolom tabel (klik sekali naik, klik lagi turun).
     */
    public const URUT = [
        'baru', 'lama', 'prioritas',
        'nama', 'nama-turun',
        'status', 'status-turun',
        'petugas', 'petugas-turun',
        'topik', 'topik-turun',
    ];

    /** Kolom tabel yang bisa diklik untuk mengurutkan. */
    public const KOLOM_URUT = [
        'nama' => 'name',
        'status' => 'status',
        'petugas' => 'assigned_to',
        'topik' => 'kategori',
    ];

    public const TAMPILAN = ['kartu', 'tabel'];

    public const STATUS = [
        'open' => 'Terbuka',
        'pending' => 'Tertunda',
        'in_progress' => 'Diproses',
        'resolved' => 'Selesai',
        'closed' => 'Ditutup',
    ];

    public const PRIORITAS = [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'urgent' => 'Mendesak',
    ];

    /** Saringan yang ikut dibersihkan & bisa dilepas lewat chip. */
    protected const SARINGAN = ['fStatus', 'fPrioritas', 'fKategori', 'fPetugas', 'fBatas', 'fSpam', 'fCuriga', 'fDari', 'fSampai'];

    public function updatingSearch(): void
    {
        $this->pilih = [];
        $this->resetPage();
    }

    public function updated($nama): void
    {
        if (in_array($nama, array_merge(self::SARINGAN, ['urut', 'perPage', 'tampilan']), true)) {
            $this->pilih = [];
            $this->resetPage();
        }

        if ($nama === 'urut' && ! in_array($this->urut, self::URUT, true)) {
            $this->urut = 'baru';
        }

        if ($nama === 'tampilan' && ! in_array($this->tampilan, self::TAMPILAN, true)) {
            $this->tampilan = 'kartu';
        }
    }

    public function setTab(string $t): void
    {
        $this->tab = in_array($t, self::TAB, true) ? $t : 'baru';
        $this->pilih = [];
        $this->resetPage();
    }

    /** Klik kepala kolom tabel: naik dulu, klik lagi jadi turun. */
    public function urutkanKolom(string $kolom): void
    {
        if (! array_key_exists($kolom, self::KOLOM_URUT)) {
            return;
        }

        $this->urut = $this->urut === $kolom ? $kolom.'-turun' : $kolom;
        $this->pilih = [];
        $this->resetPage();
    }

    /** Untuk panah di kepala kolom: '' | 'naik' | 'turun'. */
    public function arahUrut(string $kolom): string
    {
        return match ($this->urut) {
            $kolom => 'naik',
            $kolom.'-turun' => 'turun',
            default => '',
        };
    }

    /** Lompatan dari kartu ringkasan — satu klik, bukan cari-cari di menu Saring. */
    public function sorotLewatBatas(): void
    {
        $this->tab = 'semua';
        $this->reset(['fStatus', 'fPrioritas', 'fKategori', 'fPetugas', 'fSpam', 'fCuriga', 'fDari', 'fSampai', 'pilih']);
        $this->fBatas = 'lewat';
        $this->urut = 'lama';
        $this->resetPage();
    }

    public function sorotBelumDibaca(): void
    {
        $this->tab = 'baru';
        $this->reset(array_merge(['pilih'], self::SARINGAN));
        $this->urut = 'lama';
        $this->resetPage();
    }

    public function sorotPekanIni(): void
    {
        $this->tab = 'semua';
        $this->reset(array_merge(['pilih'], self::SARINGAN));
        $this->fDari = now()->subDays(7)->toDateString();
        $this->resetPage();
    }

    public function sorotTopik(string $kategori): void
    {
        if (! array_key_exists($kategori, config('helpdesk.kategori'))) {
            return;
        }

        $this->tab = 'semua';
        $this->reset(array_merge(['pilih'], self::SARINGAN));
        $this->fKategori = $kategori;
        $this->resetPage();
    }

    public function setTampilan(string $t): void
    {
        $this->tampilan = in_array($t, self::TAMPILAN, true) ? $t : 'kartu';
    }

    public function resetFilters(): void
    {
        $this->reset(array_merge(['search', 'pilih'], self::SARINGAN));
        $this->resetPage();
    }

    public function lepasSaring(string $nama): void
    {
        if (in_array($nama, array_merge(self::SARINGAN, ['search']), true)) {
            $this->$nama = '';
            $this->pilih = [];
            $this->resetPage();
        }
    }

    public function getAdaSaringProperty(): bool
    {
        foreach (self::SARINGAN as $nama) {
            if (filled($this->$nama)) {
                return true;
            }
        }

        return false;
    }

    /** Saringan aktif sebagai chip yang bisa dilepas satu-satu. */
    public function getChipSaringProperty(): array
    {
        $chip = [];
        $tambah = function ($nama, $label) use (&$chip) {
            $chip[] = ['nama' => $nama, 'label' => $label];
        };

        if ($this->search !== '') {
            $tambah('search', 'Cari: "'.$this->search.'"');
        }
        if ($this->fStatus !== '') {
            $tambah('fStatus', self::STATUS[$this->fStatus] ?? $this->fStatus);
        }
        if ($this->fPrioritas !== '') {
            $tambah('fPrioritas', 'Prioritas '.(self::PRIORITAS[$this->fPrioritas] ?? $this->fPrioritas));
        }
        if ($this->fKategori !== '') {
            $tambah('fKategori', 'Topik: '.(config('helpdesk.kategori')[$this->fKategori] ?? $this->fKategori));
        }
        if ($this->fPetugas !== '') {
            $tambah('fPetugas', $this->fPetugas === 'saya'
                ? 'Tiket saya'
                : 'Petugas: '.(CustomerMessage::petugasTersedia()->firstWhere('id', (int) $this->fPetugas)?->name ?? $this->fPetugas));
        }
        if ($this->fBatas !== '') {
            $tambah('fBatas', $this->fBatas === 'lewat' ? 'Lewat batas waktu' : 'Belum dibalas');
        }
        if ($this->fSpam !== '') {
            $tambah('fSpam', $this->fSpam === 'spam' ? 'Hanya spam' : 'Tanpa spam');
        }
        if ($this->fCuriga !== '') {
            $tambah('fCuriga', 'Pengirim pernah spam');
        }
        if ($this->fDari !== '') {
            $tambah('fDari', 'Dari '.$this->fDari);
        }
        if ($this->fSampai !== '') {
            $tambah('fSampai', 'Sampai '.$this->fSampai);
        }

        return $chip;
    }

    // ===== Izin =====

    /** Menangani tiket (status, prioritas, topik, penugasan) butuh izin sendiri. */
    protected function bolehUbah(): bool
    {
        if (auth()->user()?->hasPermission('edit_customer_message')) {
            return true;
        }

        $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menangani pesan pelanggan.');

        return false;
    }

    protected function bolehHapus(): bool
    {
        if (auth()->user()?->hasPermission('delete_customer_message')) {
            return true;
        }

        $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengarsipkan pesan pelanggan.');

        return false;
    }

    // ===== Tindakan satuan =====

    public function updateStatus($id, $value): void
    {
        if (! $this->bolehUbah() || ! array_key_exists($value, self::STATUS)) {
            return;
        }

        $pesan = CustomerMessage::find($id);

        if (! $pesan) {
            return;
        }

        $lama = self::STATUS[$pesan->status] ?? $pesan->status;
        $pesan->update(['status' => $value]);
        $pesan->catat('status', $lama.' → '.self::STATUS[$value]);

        $this->dispatch('swal-success', message: 'Status diperbarui jadi '.self::STATUS[$value].'.');
        $this->dispatch('sidebar-badge-updated');
    }

    public function updatePriority($id, $value): void
    {
        if (! $this->bolehUbah() || ! array_key_exists($value, self::PRIORITAS)) {
            return;
        }

        $pesan = CustomerMessage::find($id);

        if (! $pesan) {
            return;
        }

        $lama = self::PRIORITAS[$pesan->priority] ?? $pesan->priority;
        $pesan->update(['priority' => $value]);
        $pesan->catat('prioritas', $lama.' → '.self::PRIORITAS[$value]);

        $this->dispatch('swal-success', message: 'Prioritas diperbarui jadi '.self::PRIORITAS[$value].'.');
    }

    /** Tandai satu pesan sudah dibaca tanpa membuka halamannya. */
    public function tandaiDibaca($id): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $pesan = CustomerMessage::find($id);

        if ($pesan?->belumDibaca()) {
            $pesan->markAsRead();
            $pesan->catat('dibaca');
        }

        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: 'Ditandai sudah dibaca.');
    }

    /** Pegang tiket ini sendiri — jalan pintas penugasan yang paling sering dipakai. */
    public function ambilTiket($id): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $pesan = CustomerMessage::find($id);

        if (! $pesan) {
            return;
        }

        $pesan->update(['assigned_to' => auth()->id()]);
        $pesan->catat('tugas', 'Dipegang '.auth()->user()?->name);

        $this->dispatch('swal-success', message: 'Tiket '.$pesan->ticket.' sekarang Anda yang pegang.');
    }

    /**
     * Arsipkan (soft delete).
     *
     * Isi pesan pelanggan adalah bukti percakapan, jadi tombolnya memindahkan
     * ke arsip — bukan menghapus. Hapus permanen hanya dari tab Arsip.
     */
    public function delete($id): void
    {
        if (! auth()->user()->hasPermission('delete_customer_message')) {
            $this->dispatch('CustomerMessage-deleteError', message: 'Anda tidak memiliki izin mengarsipkan pesan pelanggan.');

            return;
        }

        $customerMessage = CustomerMessage::find($id);

        if (! $customerMessage) {
            $this->dispatch('CustomerMessage-deleteError', message: 'Data Pesan Pelanggan tidak ditemukan!');

            return;
        }

        // Pesan yang belum dibaca tidak boleh hilang sebelum ada yang melihatnya.
        if ($customerMessage->belumDibaca()) {
            $this->dispatch('CustomerMessage-deleteError', message: 'Pesan belum dibaca dan tidak bisa diarsipkan!');

            return;
        }

        $customerMessage->catat('arsip');
        $customerMessage->delete();

        $this->dispatch('CustomerMessage-deleted', id: $id);
        $this->dispatch('sidebar-badge-updated');
    }

    public function tandaiSpam($id): void
    {
        if (! $this->bolehHapus()) {
            return;
        }

        $pesan = CustomerMessage::find($id);

        if (! $pesan) {
            return;
        }

        $pesan->catat('spam');
        $pesan->update(['is_spam' => true, 'status' => 'closed']);
        $pesan->delete();

        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: 'Tiket '.$pesan->ticket.' ditandai spam dan masuk arsip.');
    }

    public function pulihkan($id): void
    {
        if (! $this->bolehHapus()) {
            return;
        }

        $pesan = CustomerMessage::onlyTrashed()->find($id);

        if (! $pesan) {
            return;
        }

        $pesan->restore();
        $pesan->update(['is_spam' => false]);
        $pesan->catat('pulih');

        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: 'Tiket '.$pesan->ticket.' dikembalikan dari arsip.');
    }

    public function hapusPermanen($id): void
    {
        if (! $this->bolehHapus()) {
            return;
        }

        CustomerMessage::onlyTrashed()->find($id)?->forceDelete();

        $this->dispatch('CustomerMessage-deleted', id: $id);
        $this->dispatch('sidebar-badge-updated');
    }

    // ===== Aksi massal =====

    /** Centang/lepas semua yang tampil di halaman ini. */
    public function pilihHalaman(array $ids): void
    {
        $this->pilih = array_values(array_diff($ids, $this->pilih)) === []
            ? array_values(array_diff($this->pilih, $ids))
            : array_values(array_unique(array_merge($this->pilih, $ids)));
    }

    public function lepasPilih(): void
    {
        $this->pilih = [];
    }

    /** Model terpilih; di tab arsip yang diambil justru yang sudah diarsipkan. */
    protected function terpilih()
    {
        $kueri = $this->tab === 'arsip'
            ? CustomerMessage::onlyTrashed()
            : CustomerMessage::query();

        return $kueri->whereKey($this->pilih)->get();
    }

    public function tandaiDibacaTerpilih(): void
    {
        if (! $this->bolehUbah() || $this->pilih === []) {
            return;
        }

        $jumlah = 0;

        foreach ($this->terpilih() as $pesan) {
            if ($pesan->belumDibaca()) {
                $pesan->markAsRead();
                $pesan->catat('dibaca');
                $jumlah++;
            }
        }

        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' pesan ditandai sudah dibaca.');
    }

    public function statusTerpilih(string $value): void
    {
        if (! $this->bolehUbah() || $this->pilih === [] || ! array_key_exists($value, self::STATUS)) {
            return;
        }

        $jumlah = 0;

        foreach ($this->terpilih() as $pesan) {
            $lama = self::STATUS[$pesan->status] ?? $pesan->status;
            $pesan->update(['status' => $value]);
            $pesan->catat('status', $lama.' → '.self::STATUS[$value]);
            $jumlah++;
        }

        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' pesan diubah jadi '.self::STATUS[$value].'.');
    }

    public function prioritasTerpilih(string $value): void
    {
        if (! $this->bolehUbah() || $this->pilih === [] || ! array_key_exists($value, self::PRIORITAS)) {
            return;
        }

        $jumlah = 0;

        foreach ($this->terpilih() as $pesan) {
            $lama = self::PRIORITAS[$pesan->priority] ?? $pesan->priority;
            $pesan->update(['priority' => $value]);
            $pesan->catat('prioritas', $lama.' → '.self::PRIORITAS[$value]);
            $jumlah++;
        }

        $this->pilih = [];
        $this->dispatch('swal-success', message: $jumlah.' pesan diubah jadi prioritas '.self::PRIORITAS[$value].'.');
    }

    public function kategoriTerpilih(string $value): void
    {
        $daftar = config('helpdesk.kategori');

        if (! $this->bolehUbah() || $this->pilih === [] || ($value !== '' && ! array_key_exists($value, $daftar))) {
            return;
        }

        $jumlah = 0;

        foreach ($this->terpilih() as $pesan) {
            $pesan->update(['kategori' => $value ?: null]);
            $pesan->catat('kategori', $value ? $daftar[$value] : 'Tanpa topik');
            $jumlah++;
        }

        $this->pilih = [];
        $this->dispatch('swal-success', message: $jumlah.' tiket diberi topik '.($value ? $daftar[$value] : 'kosong').'.');
    }

    public function tugaskanTerpilih(string $value): void
    {
        $petugas = $value === '' ? null : CustomerMessage::petugasTersedia()->firstWhere('id', (int) $value);

        if (! $this->bolehUbah() || $this->pilih === [] || ($value !== '' && ! $petugas)) {
            return;
        }

        $jumlah = 0;

        foreach ($this->terpilih() as $pesan) {
            $pesan->update(['assigned_to' => $petugas?->id]);
            $pesan->catat('tugas', $petugas ? 'Dipegang '.$petugas->name : 'Penugasan dilepas');
            \App\Notifications\TiketDitugaskan::kirim($pesan, $petugas);
            $jumlah++;
        }

        $this->pilih = [];
        $this->dispatch('swal-success', message: $petugas
            ? $jumlah.' tiket diserahkan ke '.$petugas->name.'.'
            : $jumlah.' tiket dilepas dari petugasnya.');
    }

    public function spamTerpilih(): void
    {
        if (! $this->bolehHapus() || $this->pilih === []) {
            return;
        }

        $jumlah = 0;

        foreach ($this->terpilih() as $pesan) {
            $pesan->catat('spam');
            $pesan->update(['is_spam' => true, 'status' => 'closed']);
            $pesan->delete();
            $jumlah++;
        }

        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' pesan ditandai spam dan masuk arsip.');
    }

    public function hapusTerpilih(): void
    {
        if (! $this->bolehHapus() || $this->pilih === []) {
            return;
        }

        // Pesan yang belum dibaca tetap dilindungi, sama seperti arsip satuan.
        $jumlah = 0;

        foreach ($this->terpilih() as $pesan) {
            if ($pesan->belumDibaca()) {
                continue;
            }

            $pesan->catat('arsip');
            $pesan->delete();
            $jumlah++;
        }

        $lewat = count($this->pilih) - $jumlah;

        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' pesan dipindahkan ke arsip.'
            .($lewat ? ' '.$lewat.' dilewati karena belum dibaca.' : ''));
    }

    public function pulihkanTerpilih(): void
    {
        if (! $this->bolehHapus() || $this->pilih === []) {
            return;
        }

        $jumlah = 0;

        foreach (CustomerMessage::onlyTrashed()->whereKey($this->pilih)->get() as $pesan) {
            $pesan->restore();
            $pesan->update(['is_spam' => false]);
            $pesan->catat('pulih');
            $jumlah++;
        }

        $this->pilih = [];
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: $jumlah.' tiket dikembalikan dari arsip.');
    }

    public function hapusPermanenTerpilih(): void
    {
        if (! $this->bolehHapus() || $this->pilih === []) {
            return;
        }

        $jumlah = CustomerMessage::onlyTrashed()->whereKey($this->pilih)->get()
            ->each(fn ($pesan) => $pesan->forceDelete())
            ->count();

        $this->pilih = [];
        $this->dispatch('swal-success', message: $jumlah.' tiket dihapus permanen dari arsip.');
    }

    // ===== Unduhan =====

    public function unduhExcel()
    {
        abort_unless(auth()->user()?->hasPermission('view_customer_message'), 403);

        return Excel::download(new PesanPelangganExport($this->dataEkspor()), 'pesan-pelanggan-'.now()->format('Ymd-His').'.xlsx');
    }

    public function unduhPdf()
    {
        abort_unless(auth()->user()?->hasPermission('view_customer_message'), 403);

        $data = $this->dataEkspor();

        $pdf = Pdf::loadView('exports.pesan-pelanggan-pdf', [
            'pesan' => $data,
            'saringan' => $this->pilih
                ? ['hanya '.count($this->pilih).' pesan terpilih']
                : collect($this->chipSaring)->pluck('label')->all(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print ($pdf->output()), 'pesan-pelanggan-'.now()->format('Ymd-His').'.pdf');
    }

    /** Yang DICENTANG bila ada, kalau tidak ikut saringan yang sedang tampil. */
    protected function dataEkspor()
    {
        // Linimasa ikut dimuat supaya ekspor membawa balasan terakhirnya,
        // bukan cuma status akhir yang tidak bisa dipertanggungjawabkan.
        return $this->pilih
            ? $this->terpilih()->load(['petugas', 'logs'])
            : $this->kueri()->with(['petugas', 'logs'])->get();
    }

    /** Kueri daftar — dipakai kartu, ekspor, dan hitungan. */
    protected function kueri()
    {
        // Tab arsip melihat yang sudah diarsipkan (termasuk spam); tab lain
        // hanya melihat tiket hidup dan bukan spam.
        $kueri = $this->tab === 'arsip'
            ? CustomerMessage::onlyTrashed()
            : CustomerMessage::query()->bukanSpam();

        return $kueri
            ->when($this->tab === 'baru', fn ($q) => $q->unread())
            ->when($this->tab === 'berjalan', fn ($q) => $q->berjalan())
            ->when($this->tab === 'selesai', fn ($q) => $q->whereIn('status', ['resolved', 'closed']))
            ->when($this->fStatus !== '', fn ($q) => $q->where('status', $this->fStatus))
            ->when($this->fPrioritas !== '', fn ($q) => $q->where('priority', $this->fPrioritas))
            ->when($this->fKategori !== '', fn ($q) => $q->where('kategori', $this->fKategori))
            ->when($this->fPetugas === 'saya', fn ($q) => $q->milik(auth()->id()))
            ->when($this->fPetugas !== '' && $this->fPetugas !== 'saya', fn ($q) => $q->milik((int) $this->fPetugas))
            ->when($this->fBatas === 'lewat', fn ($q) => $q->lewatBatas())
            ->when($this->fBatas === 'belum', fn ($q) => $q->belumDibalas())
            ->when($this->fSpam === 'spam', fn ($q) => $q->where('is_spam', true))
            ->when($this->fSpam === 'biasa', fn ($q) => $q->where('is_spam', false))
            ->when($this->fCuriga !== '', function ($q) {
                // Kontak yang pernah dipakai mengirim spam. Daftarnya disusun di
                // PHP (jumlah spam selalu kecil) supaya kuerinya sama jalan di
                // MySQL maupun SQLite.
                [$surel, $nomor] = $this->kontakSpam();

                $q->where(function ($sub) use ($surel, $nomor) {
                    $sub->whereIn('email', $surel ?: [''])
                        ->orWhereIn('no_telp', $nomor ?: ['']);
                });
            })
            ->when($this->fDari !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->fDari))
            ->when($this->fSampai !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->fSampai))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                // Nomor ikut dicari lewat bentuk INTI-nya: nomor yang sama bisa
                // tersimpan "0895…" atau "62895…". Hanya bila yang diketik
                // memang berbentuk nomor — kalau tidak, angka nyasar di dalam
                // kode tiket (TKT-5A2B-…) ikut mencocokkan nomor orang lain.
                $inti = preg_match('/^[\d\s().+-]{4,}$/', trim($this->search))
                    ? \App\Models\Customer::normalisasiNoHp($this->search)
                    : '';

                $q->where(function ($sub) use ($term, $inti) {
                    $sub->where('ticket', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('message', 'like', $term);

                    if ($inti !== '') {
                        $sub->orWhere('no_telp', 'like', '%'.$inti.'%');
                    }
                });
            })
            // Mendesak lebih dulu, lalu yang paling lama menunggu.
            // CASE, bukan FIELD(): FIELD() hanya ada di MySQL, sedangkan uji
            // berjalan di SQLite — kueri semacam itu hijau di uji lalu galat
            // di server, atau sebaliknya.
            ->when($this->urut === 'prioritas', fn ($q) => $q->orderByRaw(
                "CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END"
            )->orderBy('created_at'))
            ->when($this->urut === 'lama', fn ($q) => $q->orderBy('created_at'))
            ->when($this->urut === 'baru', fn ($q) => $q->orderByDesc('created_at'))
            // Urutan dari kepala kolom tabel.
            ->when(isset(self::KOLOM_URUT[$this->urut]), fn ($q) => $q
                ->orderBy(self::KOLOM_URUT[$this->urut])->orderByDesc('created_at'))
            ->when(str_ends_with($this->urut, '-turun') && isset(self::KOLOM_URUT[substr($this->urut, 0, -6)]),
                fn ($q) => $q->orderByDesc(self::KOLOM_URUT[substr($this->urut, 0, -6)])->orderByDesc('created_at'));
    }

    /** [surel, nomor] yang pernah dipakai mengirim spam. */
    protected function kontakSpam(): array
    {
        $spam = CustomerMessage::withTrashed()->where('is_spam', true)->get(['email', 'no_telp']);

        return [
            $spam->pluck('email')->filter()->unique()->values()->all(),
            $spam->pluck('no_telp')->filter()->unique()->values()->all(),
        ];
    }

    /**
     * Sebaran topik 30 hari terakhir — menjawab "keluhan terbanyak soal apa".
     * Tanpa ini topik cuma label yang dikumpulkan lalu tidak pernah dibaca.
     */
    protected function sebaranTopik(): array
    {
        $hitung = CustomerMessage::query()->bukanSpam()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('kategori, count(*) as jumlah')
            ->groupBy('kategori')
            ->pluck('jumlah', 'kategori');

        $total = (int) $hitung->sum();
        $daftar = [];

        foreach (config('helpdesk.kategori') as $kunci => $label) {
            $jumlah = (int) ($hitung[$kunci] ?? 0);

            if ($jumlah > 0) {
                $daftar[] = ['kunci' => $kunci, 'label' => $label, 'jumlah' => $jumlah,
                    'persen' => $total ? round($jumlah * 100 / $total) : 0];
            }
        }

        usort($daftar, fn ($a, $b) => $b['jumlah'] <=> $a['jumlah']);

        return [
            'sebaranTopik' => $daftar,
            'tanpaTopik' => (int) ($hitung[null] ?? $hitung[''] ?? 0),
        ];
    }

    /**
     * Ringkasan periode: berapa yang masuk sepekan, rata-rata waktu tanggap,
     * dan berapa yang sudah lewat batas.
     */
    protected function ringkasan(): array
    {
        $dibalas = CustomerMessage::query()->bukanSpam()
            ->whereNotNull('replied_at')
            ->where('replied_at', '>=', now()->subDays(30))
            ->get(['created_at', 'replied_at']);

        // Dirata-rata di PHP: AVG atas selisih waktu ditulis berbeda di MySQL
        // dan SQLite, dan barisnya sedikit (hanya 30 hari terakhir).
        $rata = $dibalas->isEmpty()
            ? null
            : round($dibalas->avg(fn ($p) => abs($p->created_at->diffInMinutes($p->replied_at))) / 60, 1);

        return [
            'masukPekanIni' => CustomerMessage::query()->bukanSpam()->where('created_at', '>=', now()->subDays(7))->count(),
            'rataResponJam' => $rata,
            'lewatBatas' => CustomerMessage::query()->bukanSpam()->lewatBatas()->count(),
        ];
    }

    public function render()
    {
        $messages = $this->kueri()->with('petugas')->withCount('lampiran')->paginate(max(6, min(48, $this->perPage)));

        // Satu kueri untuk hitungan status; sisanya dihitung dari situ.
        $perStatus = CustomerMessage::query()->bukanSpam()
            ->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $selesai = (int) ($perStatus['resolved'] ?? 0) + (int) ($perStatus['closed'] ?? 0);

        $tabCounts = [
            'baru' => CustomerMessage::query()->bukanSpam()->unread()->count(),
            'berjalan' => (int) $perStatus->sum() - $selesai,
            'selesai' => $selesai,
            'semua' => (int) $perStatus->sum(),
            'arsip' => CustomerMessage::onlyTrashed()->count(),
        ];

        return view('livewire.pages.admin.message.customer-message-list', array_merge($this->sebaranTopik(), [
            'messages' => $messages,
            'tabCounts' => $tabCounts,
            'unreadCount' => $tabCounts['baru'],
            // Tiket mendesak yang belum selesai — yang paling pantas dikerjakan dulu.
            'mendesak' => CustomerMessage::query()->bukanSpam()->berjalan()->whereIn('priority', ['urgent', 'high'])->count(),
            // Tiket terlama yang masih menunggu dibaca, untuk kartu ringkasan.
            'tertua' => CustomerMessage::query()->bukanSpam()->unread()->oldest()->first(),
            'daftarPetugas' => CustomerMessage::petugasTersedia(),
            // Dihitung SEKALI di sini, bukan lewat pernahSpam() per kartu:
            // satu halaman berisi 12-48 tiket dan itu jadi 48 kueri.
            'kontakSpam' => $this->kontakSpam(),
        ], $this->ringkasan()))
            ->layout('livewire.layout.templateindex');
    }
}
