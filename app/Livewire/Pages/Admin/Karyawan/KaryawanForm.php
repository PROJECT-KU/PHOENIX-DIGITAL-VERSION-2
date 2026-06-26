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

    // Data Detail Karyawan
    public $jabatan = '';

    public $nama_bank = '';

    public $nomor_rekening = '';

    public $phone = '';

    public $alamat = '';

    public function mount($user = null)
    {
        if ($user) {
            $this->userModel = $user;

            // Load data user
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role_id = $user->role_id;

            // Load data detail (gunakan optional jika detail belum ada)
            $this->jabatan = $user->detail?->jabatan;
            $this->nama_bank = $user->detail?->nama_bank;
            $this->nomor_rekening = $user->detail?->nomor_rekening;
            $this->phone = $user->detail?->phone;
            $this->alamat = $user->detail?->alamat;

            $this->isEdit = true;
        }
    }

    public function store()
    {
        $cleanRekening = str_replace('-', '', $this->nomor_rekening);
        $this->nomor_rekening = $cleanRekening;

        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
            'jabatan' => 'required',
            'nama_bank' => 'nullable|string',
            'nomor_rekening' => 'nullable|numeric',
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
                'nama_bank' => $this->nama_bank,
                'nomor_rekening' => $this->nomor_rekening,
                'phone' => $this->phone,
                'alamat' => $this->alamat,
            ]);
        });

        $this->reset();

        session()->flash('success', 'Berhasil menambah data karyawan');

        return redirect()->route('admin.karyawan.index');
    }

    public function update()
    {
        if ($this->nomor_rekening) {
            $this->nomor_rekening = str_replace('-', '', $this->nomor_rekening);
        }

        $this->validate([
            'name' => 'required|min:3',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->userModel->id)],
            'password' => 'nullable|min:6',
            'role_id' => 'required|exists:roles,id',
            'jabatan' => 'required',
            'nama_bank' => 'nullable|string',
            'nomor_rekening' => 'nullable|numeric',
        ]);

        DB::transaction(function () {
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'role_id' => $this->role_id,
            ];

            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }

            $this->userModel->update($userData);

            // Update atau Create Detail (updateOrCreate untuk jaga-jaga jika data lama kosong)
            EmployeeDetail::updateOrCreate(
                ['user_id' => $this->userModel->id],
                [
                    'jabatan' => $this->jabatan,
                    'nama_bank' => $this->nama_bank,
                    'nomor_rekening' => $this->nomor_rekening,
                    'phone' => $this->phone,
                    'alamat' => $this->alamat,
                ]
            );
        });

        $this->reset();

        session()->flash('success', 'Berhasil mengubah data karyawan');

        return redirect()->route('admin.karyawan.index');
    }

    public function render()
    {
        return view('livewire.pages.admin.karyawan.karyawan-form', [
            'roles' => Role::all(),
        ]);
    }
}
