<?php

namespace App\Livewire\Pages\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $profile_photo;
    public $current_profile_photo;

    // ubah password
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';
    //file upload foto
    public $photo;

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->current_profile_photo = $user->profile_photo;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->dispatch('profile-updated');
    }

    public function updatePassword()
    {
        $this->validate();

        Auth::user()->update([
            'password' => Hash::make($this->password)
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->dispatch('password-updated');
    }

    public function updatePhoto()
    {
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
            'profile_photo' => $path
        ]);

        $this->current_profile_photo = $path;
        $this->reset('photo');

        $this->dispatch('photo-updated');
    }

    public function removePhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $user->update([
            'profile_photo' => null
        ]);

        $this->current_profile_photo = null;

        session()->flash('photo-success', 'Foto profil berhasil dihapus.');
        $this->dispatch('photo-updated');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.profile');
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
