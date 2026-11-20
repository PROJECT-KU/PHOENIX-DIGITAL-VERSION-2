<?php

namespace App\Livewire\Pages\Admin\Surat;

use App\Models\Surat\Surat;
use App\Models\Surat\SuratDisposisi;
use App\Models\Surat\SuratLampiran;
use App\Models\User;
use App\Notifications\DisposisiBaru;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Satu surat, lengkap: rinciannya, pindaiannya, dan disposisinya.
 *
 * Halaman ini yang membuat arsip bukan cuma lemari — di sinilah surat masuk
 * diberi tugas ke orang, dan tugas itu ditutup.
 */
#[Layout('livewire.layout.templateindex')]
#[Title('Detail Surat')]
class SuratDetail extends Component
{
    use WithFileUploads;

    /** Satu tempat untuk aturan berkas, supaya kotak dan antrean tidak pernah berbeda. */
    private const ATURAN_BERKAS = 'file|max:20480|mimes:pdf,jpg,jpeg,png,webp,doc,docx';

    private const BATAS_ANTREAN = 20;

    public int $suratId;

    /* ---- Borang disposisi baru ---- */
    public string $disKepadaUser = '';

    public string $disKepadaNama = '';

    public string $disInstruksi = '';

    public string $disBatas = '';

    /**
     * Antrean pindaian yang sudah dipilih tetapi belum diunggah.
     *
     * Terpisah dari kotak berkasnya ($unggahan) karena <input type="file">
     * selalu MENGGANTI isinya — memilih lembar kedua membuang lembar
     * pertama tanpa pesan apa pun.
     */
    public array $berkasBaru = [];

    /** Kotak berkasnya; isinya langsung dipindahkan ke antrean lalu dikosongkan. */
    public array $unggahan = [];

    public function mount(int $surat): void
    {
        $this->suratId = $surat;

        // Memastikan ada; findOrFail di mount supaya alamat yang salah
        // jadi 404 biasa, bukan halaman setengah jadi.
        $this->surat();
    }

    /**
     * Suratnya, dengan penjaga kerahasiaan ikut melekat.
     *
     * SEMUA jalan masuk ke baris ini lewat sini — membuka halamannya,
     * mengubah status, menambah disposisi, mengunggah pindaian, menghapus.
     * Kalau penjagaannya hanya dipasang di mount(), permintaan Livewire
     * berikutnya tidak melewatinya lagi.
     */
    private function surat(): Surat
    {
        $surat = Surat::with(['klasifikasi', 'pencatat', 'lampiran.pengunggah', 'disposisi.penerima', 'disposisi.pembuat'])
            ->findOrFail($this->suratId);

        abort_unless($surat->bolehDilihat(auth()->user()), 403,
            'Surat ini bersifat rahasia. Mintalah izin "Lihat Surat Rahasia" kepada admin.');

        return $surat;
    }

    /* ----------------------------- STATUS ----------------------------- */

    public function ubahStatus(string $status): void
    {
        abort_unless(auth()->user()?->hasPermission('edit_surat'), 403);

        if (! in_array($status, Surat::STATUS_MANUAL, true)) {
            return;
        }

        $surat = $this->surat();
        $surat->status = $status;
        $surat->save();

        $this->dispatch('swal-success', message: 'Status surat diubah jadi "'.Surat::STATUS[$status].'".');
    }

    /* ----------------------------- DISPOSISI ----------------------------- */

    public function tambahDisposisi(): void
    {
        // Lewat penjaga kerahasiaan dulu: surat rahasia tidak boleh
        // disentuh dari jalur tulis mana pun.
        $this->surat();

        abort_unless(auth()->user()?->hasPermission('create_surat'), 403);

        $this->validate([
            'disInstruksi' => 'required|string|max:2000',
            'disKepadaUser' => 'nullable|exists:users,id',
            'disKepadaNama' => 'nullable|string|max:180',
            'disBatas' => 'nullable|date',
        ], [
            'disInstruksi.required' => 'Tulis dulu apa yang harus dikerjakan.',
        ], [
            'disInstruksi' => 'instruksi',
            'disKepadaNama' => 'nama penerima',
            'disBatas' => 'tenggat',
        ]);

        /*
         | Harus ada penerimanya — akun lemon ATAU nama yang ditulis tangan.
         |
         | Divalidasi di sini, bukan lewat 'required' pada salah satunya:
         | keduanya sah sendiri-sendiri, dan menuntut akun lemon berarti
         | disposisi ke orang yang tidak punya akun tidak tercatat sama sekali.
         */
        if ($this->disKepadaUser === '' && trim($this->disKepadaNama) === '') {
            $this->addError('disKepadaNama', 'Pilih akun penerimanya, atau tulis namanya.');

            return;
        }

        $disposisi = SuratDisposisi::create([
            'surat_id' => $this->suratId,
            'kepada_user_id' => $this->disKepadaUser ?: null,
            'kepada_nama' => trim($this->disKepadaNama) ?: null,
            'instruksi' => trim($this->disInstruksi),
            'batas' => $this->disBatas ?: null,
            'status' => 'menunggu',
        ]);

        /*
         | Beri tahu orangnya — kalau ia punya akun.
         |
         | Disposisi yang tidak terbaca sama saja dengan disposisi yang tidak
         | pernah dibuat. Yang ditulis sebagai NAMA (tanpa akun) tetap sah dan
         | tetap tercatat; menagihnya memang masih tugas manusia, dan angkanya
         | sudah tampil di dasbor.
         |
         | Tidak mengirim ke diri sendiri: orang yang baru saja mengetik
         | perintahnya tidak perlu diberi tahu bahwa ia mengetiknya.
         */
        if ($disposisi->kepada_user_id && (int) $disposisi->kepada_user_id !== (int) auth()->id()) {
            $disposisi->penerima?->notify(new DisposisiBaru($disposisi));
            $disposisi->forceFill(['diberitahu_pada' => now()])->saveQuietly();
        }

        /*
         | Surat yang baru didisposisi otomatis jadi 'diproses'.
         |
         | Memberi tugas ADALAH menindak suratnya; membiarkannya tetap
         | "belum ditindak" membuat angka di dasbor menghitung pekerjaan yang
         | sudah berjalan, dan angka yang salah lama-lama berhenti dibaca.
         | Status lanjut lainnya (selesai, diarsipkan) tidak diturunkan
         | kembali — itu keputusan yang sudah diambil orang.
         */
        $surat = $this->surat();

        if ($surat->status === 'baru') {
            $surat->status = 'diproses';
            $surat->save();
        }

        $this->reset(['disKepadaUser', 'disKepadaNama', 'disInstruksi', 'disBatas']);
        $this->dispatch('swal-success', message: 'Disposisi ditambahkan.');
    }

    public function ubahStatusDisposisi(int $id, string $status): void
    {
        // Lewat penjaga kerahasiaan dulu: surat rahasia tidak boleh
        // disentuh dari jalur tulis mana pun.
        $this->surat();

        abort_unless(auth()->user()?->hasPermission('edit_surat'), 403);

        if (! array_key_exists($status, SuratDisposisi::STATUS)) {
            return;
        }

        $d = SuratDisposisi::where('surat_id', $this->suratId)->findOrFail($id);
        $d->status = $status;
        $d->selesai_pada = $status === 'selesai' ? now() : null;
        $d->save();

        $this->dispatch('swal-success', message: 'Disposisi ditandai "'.SuratDisposisi::STATUS[$status].'".');
    }

    public function hapusDisposisi(int $id): void
    {
        // Lewat penjaga kerahasiaan dulu: surat rahasia tidak boleh
        // disentuh dari jalur tulis mana pun.
        $this->surat();

        abort_unless(auth()->user()?->hasPermission('delete_surat'), 403);

        SuratDisposisi::where('surat_id', $this->suratId)->findOrFail($id)->delete();

        $this->dispatch('swal-success', message: 'Disposisi dihapus.');
    }

    /* ----------------------------- LAMPIRAN ----------------------------- */

    /** Pesan berkas ditulis sekali, dipakai kotak maupun tombol simpan. */
    private function pesanBerkas(string $medan): array
    {
        return [
            $medan.'.max' => 'Berkas pindaian maksimal 20 MB per lembar.',
            $medan.'.mimes' => 'Berkas pindaian harus PDF, gambar (JPG/PNG/WEBP), atau Word.',
        ];
    }

    /**
     * Pindahkan berkas yang baru dipilih ke antrean — ditambahkan, bukan
     * menggantikan. Surat berlembar-lembar dipindai dan dipilih beberapa
     * kali, dan pilihan kedua tidak boleh membuang pilihan pertama.
     */
    public function updatedUnggahan(): void
    {
        $this->validate(['unggahan.*' => 'nullable|'.self::ATURAN_BERKAS], $this->pesanBerkas('unggahan.*'));

        foreach ($this->unggahan as $berkas) {
            if (! $berkas) {
                continue;
            }

            if (count($this->berkasBaru) >= self::BATAS_ANTREAN) {
                $this->addError('unggahan', 'Sekali unggah maksimal '.self::BATAS_ANTREAN
                    .' berkas. Simpan dulu yang ini, sisanya bisa ditambahkan setelahnya.');
                break;
            }

            $this->berkasBaru[] = $berkas;
        }

        $this->unggahan = [];
    }

    /** Keluarkan satu berkas dari antrean sebelum diunggah. */
    public function hapusAntrean(int $urutan): void
    {
        unset($this->berkasBaru[$urutan]);

        // Nomornya disusun ulang: celah di tengah larik membuat tombol
        // hapus berikutnya menunjuk berkas yang salah.
        $this->berkasBaru = array_values($this->berkasBaru);
    }

    public function unggahBerkas(): void
    {
        // Lewat penjaga kerahasiaan dulu: surat rahasia tidak boleh
        // disentuh dari jalur tulis mana pun.
        $this->surat();

        abort_unless(auth()->user()?->hasPermission('edit_surat'), 403);

        $this->validate(
            ['berkasBaru.*' => 'required|'.self::ATURAN_BERKAS],
            $this->pesanBerkas('berkasBaru.*'),
        );

        foreach ($this->berkasBaru as $berkas) {
            if (! $berkas) {
                continue;
            }

            SuratLampiran::create([
                'surat_id' => $this->suratId,
                'nama' => $berkas->getClientOriginalName(),
                'jalur' => $berkas->store('surat', 'local'),
                'ukuran' => $berkas->getSize(),
                'jenis' => $berkas->getMimeType(),
                'diunggah_oleh' => auth()->id(),
            ]);
        }

        $this->berkasBaru = [];
        $this->unggahan = [];
        $this->dispatch('swal-success', message: 'Pindaian ditambahkan.');
    }

    public function hapusLampiran(int $id): void
    {
        // Lewat penjaga kerahasiaan dulu: surat rahasia tidak boleh
        // disentuh dari jalur tulis mana pun.
        $this->surat();

        abort_unless(auth()->user()?->hasPermission('delete_surat'), 403);

        $l = SuratLampiran::where('surat_id', $this->suratId)->findOrFail($id);

        /*
         | Berkasnya ikut dihapus dari disk, dan barisnya dihapus walau
         | berkasnya sudah tidak ada.
         |
         | Baris yang tertinggal tanpa berkas adalah tautan yang selalu 404 —
         | admin akan menekannya berkali-kali dan menyimpulkan sistemnya rusak.
         */
        rescue(fn () => Storage::disk('local')->delete($l->jalur), null, false);

        $l->delete();

        $this->dispatch('swal-success', message: 'Pindaian dihapus.');
    }

    /* ----------------------------- HAPUS SURAT ----------------------------- */

    public function hapusSurat()
    {
        abort_unless(auth()->user()?->hasPermission('delete_surat'), 403);

        $surat = $this->surat();
        $nomor = $surat->nomor_agenda;

        /*
         | Hapus LEMBUT (softDeletes), dan pindaiannya TIDAK dibuang.
         |
         | Arsip surat adalah catatan resmi; "hapus" di sini hampir selalu
         | berarti salah catat, bukan surat yang tidak pernah ada. Nomor
         | agendanya pun tetap dipegang baris terhapus supaya tidak pernah
         | dipakai dua kali (lihat Surat::nomorAgendaBaru).
         */
        $surat->delete();

        session()->flash('success', 'Surat '.$nomor.' dihapus dari daftar.');

        return $this->redirect(route('admin.surat.index'), navigate: true);
    }

    public function render()
    {
        $u = auth()->user();
        $surat = $this->surat();

        return view('livewire.pages.admin.surat.detail', [
            'surat' => $surat,
            /*
             | Riwayat dibatasi 60 baris terakhir, TERBARU DI ATAS.
             |
             | Diurutkan dengan id, bukan created_at: beberapa jejak lahir
             | dalam detik yang sama (ubah data lalu ubah status), dan
             | riwayat yang terbaca terbalik lebih buruk daripada tidak ada.
             */
            'jejak' => $surat->jejak()->with('pelaku')->orderByDesc('id')->limit(60)->get(),
            'tanggapan' => $surat->tanggapan()->terlihatOleh($u)
                ->orderBy('tanggal_surat')->orderBy('id')->get(),
            'bolehUbah' => (bool) $u?->hasPermission('edit_surat'),
            'bolehCatat' => (bool) $u?->hasPermission('create_surat'),
            'bolehHapus' => (bool) $u?->hasPermission('delete_surat'),
            /*
             | Daftar akun untuk kotak "disposisi kepada".
             |
             | Disaring ke pemegang 'view_surat': mendisposisi surat kepada
             | orang yang tidak bisa membuka arsipnya menghasilkan tugas yang
             | tidak akan pernah dilihat penerimanya.
             */
            'daftarUser' => User::whereHas('role.permissions', fn ($q) => $q->where('name', 'view_surat'))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
