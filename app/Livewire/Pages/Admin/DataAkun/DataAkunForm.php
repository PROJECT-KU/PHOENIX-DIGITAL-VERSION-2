<?php

namespace App\Livewire\Pages\Admin\DataAkun;

use App\Models\DataAkun;
use App\Models\Product;
use App\Models\User;
use Livewire\Component;

class DataAkunForm extends Component
{
    public ?DataAkun $dataAkun = null;

    public $nama_akun = '';

    public $username_akun = '';

    public $password_akun = '';

    public $link_login_akun = '';

    public $pj_akun = '';

    public $harga_satuan = '';

    public $deskripsi = '';

    public $status = '';

    public $mode = 'create';

    public function mount($dataAkun = null)
    {
        if ($dataAkun) {
            $this->dataAkun = $dataAkun;
            $this->nama_akun = $this->dataAkun->nama_akun;
            $this->username_akun = $this->dataAkun->username_akun;
            $this->password_akun = $this->dataAkun->password_akun;
            $this->link_login_akun = $this->dataAkun->link_login_akun;
            $this->pj_akun = $this->dataAkun->pj_akun;
            $this->harga_satuan = $this->dataAkun->harga_satuan;
            $this->deskripsi = $this->dataAkun->deskripsi;
            $this->status = $dataAkun->status;
            $this->mode = 'edit';
        }
    }

    /** Banyaknya slot nama akun per produk ("Nama 1".."Nama N"), private & sharing sama. */
    private const SLOT_PER_PRODUK = 10;

    /**
     * Peta slot nama akun => id produk induknya.
     * Tiap produk dapat SLOT_PER_PRODUK slot ("Nama 1".."Nama 10").
     *
     * Dulu produk private dibatasi 1 slot. Itu menyulitkan kenyataan: satu camp
     * bisa memakai beberapa akun private berbeda (mis. 2 login Scopus), tapi
     * hanya ada satu slot sehingga akun yang sama terpaksa dipilih dua kali —
     * datanya jadi tidak mencerminkan akun yang benar-benar dipakai.
     *
     * Nama slot memang DIBANGKITKAN dari produk, jadi induknya sudah pasti —
     * tidak perlu ditebak dari teks nama (rapuh: spasi/typo/huruf besar).
     * Petanya dipakai mengisi product_id otomatis saat slot dipilih.
     *
     * @return array<string,string>  "Grammarly 1" => <uuid produk Grammarly>
     */
    private function slotMap(): array
    {
        $map = [];
        foreach (Product::orderBy('nama_akun')->get(['id', 'nama_akun', 'tipe_akun']) as $p) {
            $base = trim((string) $p->nama_akun);
            if ($base === '') {
                continue;
            }
            for ($n = 1; $n <= self::SLOT_PER_PRODUK; $n++) {
                $map[$base.' '.$n] = (string) $p->id;
            }
        }

        return $map;
    }

    /**
     * Semua slot nama akun dari produk (hanya namanya).
     */
    private function slotNames(): array
    {
        return array_keys($this->slotMap());
    }

    /**
     * Id produk induk dari slot nama yang sedang dipilih.
     *
     * Null bila namanya bukan slot bawaan (mis. data lama yang diketik manual) —
     * aman: akun tanpa tautan dianggap bukan-private, modalnya tidak dicatat dan
     * penjualan RSC-nya tidak diakui ke produk mana pun.
     */
    private function productIdDariSlot(): ?string
    {
        return $this->slotMap()[trim((string) $this->nama_akun)] ?? null;
    }

    /**
     * Slot nama yang dipilih menunjuk produk PRIVATE?
     *
     * Dipakai form (bukan cuma validasi) untuk melonggarkan username/password:
     * akun private sering baru dibuat/dibeli belakangan, jadi kredensialnya
     * belum ada saat data akun didaftarkan. Akun sharing kredensialnya sudah
     * pasti ada karena memang dipakai bersama, jadi tetap wajib.
     *
     * Slot yang belum tertaut produk → dianggap bukan private (tetap wajib).
     */
    public function slotPrivate(): bool
    {
        $pid = $this->productIdDariSlot();

        return $pid !== null
            && Product::where('id', $pid)->value('tipe_akun') === 'private';
    }

    /**
     * Segarkan baris modal cash flow batch RSC yang memakai akun ini —
     * baik sebagai akun UTAMA maupun akun TAMBAHAN.
     *
     * Kenapa perlu: modal RSC dihitung dari tautan produk akun (private/sharing)
     * dan harga katalognya. Baris cash flow-nya hanya ditulis saat batch RSC
     * disimpan. Jadi kalau tautan produk diisi/diubah SETELAH batch dibuat,
     * baris modalnya akan tertinggal (usang) sampai batch itu disimpan ulang.
     * Dipanggil di sini supaya angkanya langsung benar tanpa admin sadar.
     *
     * Aman & idempoten: action-nya self-guard (bukan private / bukan 'baru' /
     * harga katalog tak ada → barisnya dihapus).
     */
    private function segarkanModalRscTerkait(?string $akunId): void
    {
        if (! $akunId) {
            return;
        }

        // Batch yang memakai akun ini sebagai akun TAMBAHAN.
        $batchTambahan = \App\Models\RscBatchAkun::where('akun_id', $akunId)
            ->get(['nama_camp', 'batch_camp']);

        $reps = \App\Models\PemesananRsc::where(function ($q) use ($akunId, $batchTambahan) {
            $q->where('akun', $akunId); // sebagai akun UTAMA
            foreach ($batchTambahan as $b) {
                $q->orWhere(fn ($x) => $x->where('nama_camp', $b->nama_camp)->where('batch_camp', $b->batch_camp));
            }
        })
            ->orderBy('created_at')->orderBy('id')
            ->get()
            ->groupBy(fn ($r) => $r->nama_camp.'|'.$r->batch_camp)
            ->map(fn ($grp) => $grp->first());

        $action = app(\App\Actions\Finance\SyncRscPrivateCostAction::class);
        foreach ($reps as $rep) {
            $action->execute($rep);
        }
    }

    /**
     * Slot yang boleh dipilih: kecuali yang sedang dipakai Data Akun berstatus AKTIF.
     * Saat edit, nama record ini sendiri tetap boleh dipilih.
     */
    public function availableNames(): array
    {
        $used = DataAkun::where('status', 'active')->pluck('nama_akun')->all();
        $current = $this->mode === 'edit' ? $this->nama_akun : null;

        return array_values(array_filter(
            $this->slotNames(),
            fn ($nm) => ! in_array($nm, $used, true) || $nm === $current
        ));
    }

    public function save()
    {
        // Akun PRIVATE: username & password TIDAK wajib (kredensialnya sering
        // baru ada setelah akunnya dibeli). Akun SHARING: tetap wajib.
        $private = $this->slotPrivate();

        if ($private) {
            // Kosongkan string kosong jadi null dulu — aturan 'nullable' hanya
            // melewati validasi berikutnya bila nilainya benar-benar null,
            // sedangkan input kosong dari form berupa string '' (bukan null).
            if (trim((string) $this->username_akun) === '') {
                $this->username_akun = null;
            }
            if (trim((string) $this->password_akun) === '') {
                $this->password_akun = null;
            }
        }

        $this->validate([
            'nama_akun' => 'required|min:3',
            'username_akun' => $private ? 'nullable' : 'required',
            'password_akun' => $private ? 'nullable|min:6' : 'required|min:6',
            'link_login_akun' => 'required|nullable|url',
            'pj_akun' => 'required',
            'harga_satuan' => 'required',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:active,non-active',
        ]);

        // Cegah pilih nama yang sedang AKTIF dipakai record lain
        $dup = DataAkun::where('nama_akun', $this->nama_akun)->where('status', 'active');
        if ($this->mode === 'edit' && $this->dataAkun) {
            $dup->where('id', '!=', $this->dataAkun->id);
        }
        if ($dup->exists()) {
            $this->addError('nama_akun', 'Nama akun ini sedang AKTIF dipakai. Nonaktifkan yang lama dulu, atau pilih nama lain.');

            return;
        }

        if ($this->mode === 'create') {
            $this->createDataAkun();
        } else {
            $this->updateDataAkun();
        }
    }

    private function createDataAkun()
    {
        try {
            DataAkun::create([
                'nama_akun' => $this->nama_akun,
                // Tautan ke produk induk diisi OTOMATIS dari slot yang dipilih —
                // menentukan private/sharing & harga modal tanpa input tambahan.
                'product_id' => $this->productIdDariSlot(),
                // Kolom username/password NOT NULL di DB, sedangkan akun private
                // boleh dikosongkan → simpan string kosong, bukan null.
                'username_akun' => $this->username_akun ?? '',
                'password_akun' => $this->password_akun ?? '',
                'link_login_akun' => $this->link_login_akun,
                'pj_akun' => $this->pj_akun,
                'harga_satuan' => $this->harga_satuan,
                'deskripsi' => $this->deskripsi,
                'status' => $this->status,
            ]);

            session()->flash('successCreated', 'Data Akun berhasil ditambahkan!');
            $this->dispatch('DataAkun-created');
            $this->resetForm();

            return redirect()->route('admin.DataAkun.index');
        } catch (\Exception $e) {
            session()->flash('errorCreated', 'Gagal menambahkan Data Akun: ' . $e->getMessage());
            $this->dispatch('failed-create-data-DataAkun');
        }
    }

    private function updateDataAkun()
    {
        try {
            $this->dataAkun->update([
                'nama_akun' => $this->nama_akun,
                // Tautan ke produk induk diisi OTOMATIS dari slot yang dipilih —
                // menentukan private/sharing & harga modal tanpa input tambahan.
                'product_id' => $this->productIdDariSlot(),
                // Kolom username/password NOT NULL di DB, sedangkan akun private
                // boleh dikosongkan → simpan string kosong, bukan null.
                'username_akun' => $this->username_akun ?? '',
                'password_akun' => $this->password_akun ?? '',
                'link_login_akun' => $this->link_login_akun,
                'pj_akun' => $this->pj_akun,
                'harga_satuan' => $this->harga_satuan,
                'deskripsi' => $this->deskripsi,
                'status' => $this->status,
            ]);

            // Tautan produk akun ini menentukan modal batch RSC yang memakainya.
            // Kalau tautannya baru diisi/diubah sekarang, batch RSC lama tidak
            // tahu dan baris modalnya jadi usang — jadi disegarkan di sini.
            $this->segarkanModalRscTerkait($this->dataAkun->id);

            session()->flash('successUpdated', 'Perubahan Data Akun berhasil disimpan!');
            $this->dispatch('DataAkun-updated');
            $this->resetForm();

            return redirect()->route('admin.DataAkun.index');
        } catch (\Exception $e) {
            session()->flash('errorUpdated', 'Gagal mengupdate Data Akun: ' . $e->getMessage());
            $this->dispatch('failed-update-data-DataAkun');
        }
    }

    private function resetForm()
    {
        $this->nama_akun = '';
        $this->username_akun = '';
        $this->password_akun = '';
        $this->link_login_akun = '';
        $this->pj_akun = '';
        $this->harga_satuan = '';
        $this->deskripsi = '';
        $this->status = '';
    }

    public function render()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('livewire.pages.admin.data-akun.DataAkun-form', [
            'dataAkun' => $this->dataAkun,
            'users' => $users,
            'availableNames' => $this->availableNames(),
            // Kredensial wajib hanya untuk akun SHARING. Dikirim dari sini supaya
            // penanda * di form tidak pernah beda dengan aturan validasi di save().
            'wajibKredensial' => ! $this->slotPrivate(),
        ]);
    }
}
