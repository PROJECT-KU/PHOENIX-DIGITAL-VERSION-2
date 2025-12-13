<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.authentication')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('admin.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="shadow-lg rounded-3 overflow-hidden" style="width: 32rem">
    <div class="mb-1 bg-dark py-4 d-flex flex-column align-items-center justify-content-center">
        <div class="w-50 overflow-hidden">
            <img src="{{ asset('assets/img/logophoenix.png') }}" alt="" class="h-100 w-100">
        </div>
    </div>
    <div class="p-5">
        <h2>Daftar Akun</h2>
        <form wire:submit="register" class="my-5">
            <!-- Name -->
            <div class="mb-3">
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input wire:model="name" id="name" placeholder="nama kamu" class="block mt-1 w-full"
                    type="text" name="name" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mb-3">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input wire:model="email" placeholder="contoh@email.com" id="email" class="block mt-1 w-full"
                    type="email" name="email" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mb-3">
                <x-input-label for="password" :value="__('Password')" />
                <div class="form-group position-relative has-icon-right" x-data="{ show: false }">
                    <x-text-input wire:model="password" id="password" placeholder="******"
                        x-bind:type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" />
                    <div class="form-control-icon">
                        <i :class="show ? 'bi bi-eye' : 'bi bi-eye-slash'" @click="show = !show">
                        </i>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mb-3">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <div class="form-group position-relative has-icon-right" x-data="{ show: false }">
                    <x-text-input wire:model="password_confirmation" id="password_confirmation" placeholder="******"
                        x-bind:type="show ? 'text' : 'password'" name="password_confirmation" required
                        autocomplete="new-password" />
                    <div class="form-control-icon">
                        <i :class="show ? 'bi bi-eye' : 'bi bi-eye-slash'" @click="show = !show">
                        </i>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="d-flex align-items-center gap-3 justify-content-end mt-4">
                <a class="text-muted" href="{{ route('login') }}" wire:navigate>
                    {{ __('Sudah Mendaftar?') }}
                </a>

                <button class="btn btn-primary" type="submit">Daftar</button>
            </div>
        </form>
    </div>
</div>
