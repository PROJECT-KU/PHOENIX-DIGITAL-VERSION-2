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
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updateProfile()
    {
        try {

            $this->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.Auth::id(),
            ]);

            $user = Auth::user();
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            $this->dispatch('swal-alert', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => 'Data profil berhasil diperbarui.',
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal-alert', [
                'type' => 'error',
                'title' => 'Gagal!',
                'message' => 'Data profil gagal diperbarui.',
            ]);
        }
    }

    public function updatePassword()
    {
        try {

            $this->validate();

            Auth::user()->update([
                'password' => Hash::make($this->password),
            ]);

            $this->reset(['current_password', 'password', 'password_confirmation']);

            $this->dispatch('swal-alert', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => 'Password berhasil diperbarui.',
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal-alert', [
                'type' => 'error',
                'title' => 'Gagal!',
                'message' => 'Password gagal diperbarui.',
            ]);
        }
    }

    public function updatePhoto()
    {
        try {
            $this->validate([
                'photo' => 'required|image|max:2048',
            ]);

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

            session()->flash('success', 'foto profil berhasil diperbarui.');
            $this->dispatch('swal-alert', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => 'Foto Profil berhasil diperbarui.',
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal-alert', [
                'type' => 'error',
                'title' => 'Gagal!',
                'message' => 'Foto Profil gagal diperbarui.'.$e->getMessage(),
            ]);
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

            $this->dispatch('swal-alert', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => 'Foto profil berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            $this->dispatch('swal-alert', [
                'type' => 'error',
                'title' => 'Gagal!',
                'message' => 'Foto profil gagal dihapus.'.$e->getMessage(),
            ]);
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.profile.profile-setting');
    }

    protected function rules()
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', Password::defaults()],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
