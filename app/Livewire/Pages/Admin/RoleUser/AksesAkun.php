<?php

namespace App\Livewire\Pages\Admin\RoleUser;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Livewire\Component;

/**
 * Mengatur akses SATU orang: perannya, dan izin tambahan khusus dirinya.
 *
 * Dulu ini jendela sembul di layar Pengaturan Role. Dipindah jadi halaman
 * sendiri karena isinya memang sepanjang halaman — 121 izin berkelompok,
 * kotak cari, dan daftar izin yang sudah datang dari perannya — sementara
 * jendela sembul memaksa semuanya masuk kotak setinggi setengah layar yang
 * digulir sendiri di dalam halaman yang juga digulir.
 */
class AksesAkun extends Component
{
    public int $userId;

    public string $nama = '';

    public string $email = '';

    /** Peran yang sedang dipilih di kotak pilih. */
    public $peranId = null;

    /**
     * Id izin yang diberikan KHUSUS orang ini, di luar perannya.
     *
     * Bertipe array of int sejak dimuat: kotak centang mengirim nilainya
     * sebagai teks, dan membandingkan "110" dengan 110 memakai in_array
     * ketat memulangkan false — centangnya lalu tidak pernah tergambar.
     */
    public array $izinTambahan = [];

    public string $cariIzin = '';

    public function mount(int $user): void
    {
        abort_unless(auth()->user()?->hasPermission('view_roles'), 403);

        $orang = User::with('role', 'izinTambahan')->findOrFail($user);

        $this->userId = $orang->id;
        $this->nama = $orang->name;
        $this->email = $orang->email;
        // Pengguna tanpa peran tidak boleh merobohkan halamannya — sejak izin
        // bisa diberikan per orang, keadaan itu masuk akal.
        $this->peranId = $orang->role?->id;
        $this->izinTambahan = $orang->izinTambahan->pluck('id')->map(fn ($i) => (int) $i)->all();
    }

    public function simpan()
    {
        if (! auth()->user()->hasPermission('edit_roles')) {
            $this->dispatch('swal-alert', [
                'type' => 'error', 'title' => 'Gagal!',
                'message' => 'Anda tidak memiliki izin mengubah akses akun.',
            ]);

            return null;
        }

        $this->validate(
            ['peranId' => 'required|exists:roles,id'],
            ['peranId.required' => 'Peran wajib dipilih.'],
            ['peranId' => 'Peran'],
        );

        $orang = User::findOrFail($this->userId);
        $orang->update(['role_id' => $this->peranId]);

        /*
         | Izin yang SUDAH datang dari perannya tidak ikut disimpan.
         |
         | Mencentangnya tidak salah, tetapi menyimpannya membuat baris
         | pribadi yang tidak mengubah apa pun — lalu saat izin itu dicabut
         | dari perannya kelak, orang ini diam-diam TETAP memilikinya, dan
         | tidak ada yang mengira harus memeriksa daftar pribadinya.
         */
        $dariPeran = $this->idIzinPeran($orang->fresh());

        $pilihan = collect($this->izinTambahan)
            ->map(fn ($i) => (int) $i)
            ->reject(fn ($i) => in_array($i, $dariPeran, true))
            ->unique()
            ->values();

        $orang->izinTambahan()->sync(
            $pilihan->mapWithKeys(fn ($id) => [$id => [
                // Siapa yang memberi — pertanyaan yang selalu muncul
                // belakangan, dan tidak bisa dijawab tabel berisi dua kunci.
                'diberi_oleh' => auth()->user()?->name,
            ]])->all()
        );

        session()->flash('success', 'Akses '.$orang->name.' berhasil disimpan.');

        return $this->redirectRoute('admin.account.role', navigate: true);
    }

    /** Id izin yang didapat dari peran yang SEDANG dipilih, bukan yang tersimpan. */
    public function idIzinPeran(?User $orang = null): array
    {
        $peran = Role::with('permissions')->find($this->peranId);

        return $peran
            ? $peran->permissions->pluck('id')->map(fn ($i) => (int) $i)->all()
            : [];
    }

    public function render()
    {
        $dariPeran = $this->idIzinPeran();

        $semuaIzin = Permission::query()
            ->when(trim($this->cariIzin) !== '', function ($q) {
                $kata = '%'.trim($this->cariIzin).'%';
                // Nama teknis ikut dicari: yang mencari "akses_orcha"
                // biasanya menyalinnya dari dokumentasi atau dari kode.
                $q->where(fn ($w) => $w->where('name', 'like', $kata)
                    ->orWhere('display_name', 'like', $kata)
                    ->orWhere('group', 'like', $kata)
                    ->orWhere('description', 'like', $kata));
            })
            ->orderBy('group')->orderBy('name')->get();

        return view('livewire.pages.admin.role-user.akses-akun', [
            'semuaIzin' => $semuaIzin->groupBy('group'),
            'izinDariPeran' => $dariPeran,
            // Dipakai kartu "yang sudah didapat dari peran": nama manusianya,
            // bukan sekadar berapa banyak.
            'izinPeran' => Permission::whereIn('id', $dariPeran)->orderBy('group')->orderBy('name')->get(),
            'peranTersedia' => Role::orderBy('name')->get(),
            'jumlahPribadi' => count(array_diff(
                array_map('intval', $this->izinTambahan),
                $dariPeran,
            )),
        ])->layout('livewire.layout.templateindex');
    }
}
