<?php

namespace App\Livewire\Pages\Admin\Karyawan;

use App\Models\EmployeeDetail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class KaryawanForm extends Component
{
    public ?User $userModel;

    public $isEdit = false;

    // Data User
    public $name = '';

    public $email = '';

    public $password = ''; // Hanya diisi saat create atau ganti password

    public $role_id = '';

    // Status akun: active / blokir (blokir dipicu 3x gagal login; buka blokir = set active).
    public $status = 'active';

    // Data Detail Karyawan
    public $jabatan = '';

    // NIK otomatis (read-only di form; dibuat saat karyawan pertama disimpan).
    public $nik = '';

    // Tanggal bergabung — dasar perhitungan masa kerja.
    public $tanggal_bergabung = '';

    // Data pribadi (paritas dengan halaman Profil karyawan).
    public $tanggal_lahir = '';

    public $phone = '';

    public $alamat = '';

    public $nama_bank = '';

    public $nomor_rekening = '';

    // Atasan langsung (opsional). Menentukan hierarki untuk pemberian task.
    public $atasan_id = '';

    // Tarif bonus per karyawan (untuk perhitungan gaji dari presensi)
    public $tarif_presensi_offline = 0;

    public $tarif_presensi_online = 0;

    public $tarif_lembur_per_jam = 0;

    public function mount($user = null)
    {
        if ($user) {
            $this->userModel = $user;

            // Load data user
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role_id = $user->role_id;
            $this->status = $user->status ?? 'active';

            // Load data detail (gunakan optional jika detail belum ada)
            $this->jabatan = $user->detail?->jabatan;
            $this->atasan_id = $user->detail?->atasan_id ?? '';

            $this->nik = $user->detail?->nik ?? '';
            $this->tanggal_bergabung = $user->detail?->tanggal_bergabung?->format('Y-m-d') ?? '';
            $this->tanggal_lahir = $user->detail?->tanggal_lahir?->format('Y-m-d') ?? '';
            $this->phone = $user->detail?->phone ?? '';
            $this->alamat = $user->detail?->alamat ?? '';
            $this->nama_bank = $user->detail?->nama_bank ?? '';
            $this->nomor_rekening = $user->detail?->nomor_rekening ?? '';

            $this->tarif_presensi_offline = (int) ($user->detail?->tarif_presensi_offline ?? 0);
            $this->tarif_presensi_online = (int) ($user->detail?->tarif_presensi_online ?? 0);
            $this->tarif_lembur_per_jam = (int) ($user->detail?->tarif_lembur_per_jam ?? 0);

            $this->isEdit = true;
        }
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
            'jabatan' => 'required',
            'atasan_id' => 'nullable|exists:users,id',
            'tarif_presensi_offline' => 'nullable|numeric|min:0',
            'tarif_presensi_online' => 'nullable|numeric|min:0',
            'tarif_lembur_per_jam' => 'nullable|numeric|min:0',
            // Data pribadi opsional (paritas dgn Profil) — tak memaksa alur lama.
            'tanggal_bergabung' => 'nullable|date',
            'tanggal_lahir' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
            'nama_bank' => 'nullable|string|max:100',
            'nomor_rekening' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role_id' => $this->role_id,
            ]);

            EmployeeDetail::create([
                'user_id' => $user->id,
                // NIK dibuat otomatis saat karyawan pertama disimpan.
                'nik' => EmployeeDetail::generateNik(),
                'jabatan' => $this->jabatan,
                'atasan_id' => $this->atasan_id ?: null,
                'tarif_presensi_offline' => (int) $this->tarif_presensi_offline,
                'tarif_presensi_online' => (int) $this->tarif_presensi_online,
                'tarif_lembur_per_jam' => (int) $this->tarif_lembur_per_jam,
                'tanggal_bergabung' => $this->tanggal_bergabung ?: null,
                'tanggal_lahir' => $this->tanggal_lahir ?: null,
                'phone' => $this->phone ?: null,
                'alamat' => $this->alamat ?: null,
                'nama_bank' => $this->nama_bank ?: null,
                'nomor_rekening' => $this->nomor_rekening ?: null,
            ]);
        });

        $this->reset();

        session()->flash('success', 'Berhasil menambah data karyawan');

        return redirect()->route('admin.karyawan.index');
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->userModel->id)],
            'password' => 'nullable|min:6',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,blokir',
            'jabatan' => 'required',
            'atasan_id' => ['nullable', 'exists:users,id', Rule::notIn([$this->userModel->id])],
            'tarif_presensi_offline' => 'nullable|numeric|min:0',
            'tarif_presensi_online' => 'nullable|numeric|min:0',
            'tarif_lembur_per_jam' => 'nullable|numeric|min:0',
            'tanggal_bergabung' => 'nullable|date',
            'tanggal_lahir' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
            'nama_bank' => 'nullable|string|max:100',
            'nomor_rekening' => 'nullable|string|max:50',
        ], [
            'atasan_id.not_in' => 'Karyawan tidak boleh menjadi atasan bagi dirinya sendiri.',
        ]);

        DB::transaction(function () {
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'role_id' => $this->role_id,
                'status' => $this->status,
            ];

            // Membuka blokir (set active) juga mereset penghitung gagal login.
            if ($this->status === 'active') {
                $userData['failed_login_attempts'] = 0;
            }

            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }

            $this->userModel->update($userData);

            // Update atau Create Detail (updateOrCreate untuk jaga-jaga jika data lama kosong)
            EmployeeDetail::updateOrCreate(
                ['user_id' => $this->userModel->id],
                [
                    // NIK dibuat bila belum ada (mis. detail lama tanpa NIK).
                    'nik' => $this->userModel->detail?->nik ?: EmployeeDetail::generateNik(),
                    'jabatan' => $this->jabatan,
                    'atasan_id' => $this->atasan_id ?: null,
                    'tarif_presensi_offline' => (int) $this->tarif_presensi_offline,
                    'tarif_presensi_online' => (int) $this->tarif_presensi_online,
                    'tarif_lembur_per_jam' => (int) $this->tarif_lembur_per_jam,
                    'tanggal_bergabung' => $this->tanggal_bergabung ?: null,
                    'tanggal_lahir' => $this->tanggal_lahir ?: null,
                    'phone' => $this->phone ?: null,
                    'alamat' => $this->alamat ?: null,
                    'nama_bank' => $this->nama_bank ?: null,
                    'nomor_rekening' => $this->nomor_rekening ?: null,
                ]
            );
        });

        $this->reset();

        session()->flash('success', 'Berhasil mengubah data karyawan');

        return redirect()->route('admin.karyawan.index');
    }

    public function render()
    {
        // Kandidat atasan: semua user kecuali diri sendiri (saat edit).
        $atasanOptions = User::query()
            ->when($this->isEdit && $this->userModel, fn ($q) => $q->where('id', '!=', $this->userModel->id))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.pages.admin.karyawan.karyawan-form', [
            'roles' => Role::all(),
            'atasanOptions' => $atasanOptions,
        ]);
    }
}
