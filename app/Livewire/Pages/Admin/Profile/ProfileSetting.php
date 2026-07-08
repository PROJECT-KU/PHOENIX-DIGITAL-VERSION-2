<?php

namespace App\Livewire\Pages\Admin\Profile;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileSetting extends Component
{
    use WithFileUploads;

    public string $activeTab = 'tab-profile';

    public $name;

    public $email;

    public $profile_photo;

    public $current_profile_photo;

    // Data karyawan (self-service)
    public string $nama_bank = 'Bank Mandiri';

    public $nomor_rekening = '';

    public $tanggal_lahir = '';

    public $phone = '';

    public $alamat = '';

    // ubah password
    public $current_password = '';

    public $password = '';

    public $password_confirmation = '';

    // file upload foto
    public $photo;

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->current_profile_photo = $user->profile_photo;

        $d = $user->detail;
        $this->nomor_rekening = $d?->nomor_rekening ?? '';
        $this->tanggal_lahir = $d?->tanggal_lahir?->format('Y-m-d') ?? '';
        $this->phone = $d?->phone ?? '';
        $this->alamat = $d?->alamat ?? '';
    }

    public function updateDataKaryawan()
    {
        $this->validate([
            'nomor_rekening' => 'required|numeric',
            'tanggal_lahir' => 'required|date|before:today',
            'phone' => 'required|string|max:20',
            'alamat' => 'required|string|max:500',
        ], [
            'nomor_rekening.required' => 'No. Rekening harus diisi.',
            'nomor_rekening.numeric' => 'No. Rekening hanya boleh angka.',
            'tanggal_lahir.required' => 'Tanggal lahir harus diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir tidak valid.',
            'phone.required' => 'No. HP harus diisi.',
            'alamat.required' => 'Alamat harus diisi.',
        ]);

        try {
            $user = Auth::user();
            \App\Models\EmployeeDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'jabatan' => $user->detail?->jabatan ?: '-',
                    'nama_bank' => 'Bank Mandiri',
                    'nomor_rekening' => $this->nomor_rekening,
                    'tanggal_lahir' => $this->tanggal_lahir,
                    'phone' => $this->phone,
                    'alamat' => $this->alamat,
                ]
            );

            $this->dispatch('swal-success', message: 'Data karyawan berhasil disimpan.');
        } catch (Exception $e) {
            $this->dispatch('swal-error', message: 'Data karyawan gagal disimpan.');
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updateProfile()
    {
        // Validasi di luar try agar pesan error tampil di tiap field (bukan tertangkap catch)
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.Auth::id(),
        ]);

        try {
            $user = Auth::user();
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            $this->dispatch('swal-success', message: 'Data profil berhasil diperbarui.');
        } catch (Exception $e) {
            $this->dispatch('swal-error', message: 'Data profil gagal diperbarui.');
        }
    }

    public function updatePassword()
    {
        // Validasi di luar try agar alasan error (mis. password tidak sama / terlalu pendek)
        // tampil di field, tidak tertangkap catch (Exception) yang juga menangkap ValidationException.
        $this->validate();

        try {
            Auth::user()->update([
                'password' => Hash::make($this->password),
            ]);

            $this->reset(['current_password', 'password', 'password_confirmation']);

            // Demi keamanan: logout & paksa login ulang dengan password baru
            Auth::guard('web')->logout();
            session()->invalidate();
            session()->regenerateToken();

            session()->flash('password_updated', 'Password berhasil diperbarui. Silakan login kembali dengan password baru Anda.');

            return redirect()->route('login');
        } catch (Exception $e) {
            $this->dispatch('swal-error', message: 'Password gagal diperbarui.');
        }
    }

    public function updatePhoto()
    {
        $this->validate([
            'photo' => 'required|image|max:2048',
        ]);

        try {
            $user = Auth::user();

            // Hapus foto lama dari storage jika ada
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Simpan file baru di storage/app/public/user_photo_profile
            $path = $this->photo->store('user_photo_profile', 'public');

            // Update database dengan path relatif
            $user->update([
                'profile_photo' => $path,
            ]);

            $this->current_profile_photo = $path;
            $this->reset('photo');

            $this->dispatch('swal-success', message: 'Foto profil berhasil diperbarui.');
        } catch (Exception $e) {
            $this->dispatch('swal-error', message: 'Foto profil gagal diperbarui.');
        }
    }

    public function removePhoto()
    {
        try {
            $user = Auth::user();

            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $user->update([
                'profile_photo' => null,
            ]);

            $this->current_profile_photo = null;

            $this->dispatch('swal-success', message: 'Foto profil berhasil dihapus.');
        } catch (Exception $e) {
            $this->dispatch('swal-error', message: 'Foto profil gagal dihapus.');
        }
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.profile.profile-setting');
    }

    protected function rules()
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'different:current_password', Password::defaults()],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function messages()
    {
        return [
            'current_password.required' => 'Password saat ini harus diisi.',
            'current_password.current_password' => 'Password saat ini salah.',

            'password.required' => 'Password baru harus diisi.',
            'password.min' => 'Password baru minimal :min karakter.',
            'password.different' => 'Password baru tidak boleh sama dengan password saat ini.',

            'password_confirmation.required' => 'Ulangi password baru harus diisi.',
            'password_confirmation.same' => 'Ulangi password tidak sama dengan password baru.',

            'photo.required' => 'Silakan pilih foto terlebih dahulu.',
            'photo.image' => 'File harus berupa gambar (JPG/PNG).',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
