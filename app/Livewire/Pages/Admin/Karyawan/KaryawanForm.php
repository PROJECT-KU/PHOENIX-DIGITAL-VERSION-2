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
                'jabatan' => $this->jabatan,
                'atasan_id' => $this->atasan_id ?: null,
                'tarif_presensi_offline' => (int) $this->tarif_presensi_offline,
                'tarif_presensi_online' => (int) $this->tarif_presensi_online,
                'tarif_lembur_per_jam' => (int) $this->tarif_lembur_per_jam,
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
                    'jabatan' => $this->jabatan,
                    'atasan_id' => $this->atasan_id ?: null,
                    'tarif_presensi_offline' => (int) $this->tarif_presensi_offline,
                    'tarif_presensi_online' => (int) $this->tarif_presensi_online,
                    'tarif_lembur_per_jam' => (int) $this->tarif_lembur_per_jam,
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
