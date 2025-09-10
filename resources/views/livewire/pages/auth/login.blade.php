<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.authentication')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="bg-white shadow-lg rounded-3 overflow-hidden" style="width: 32rem;">
    <div class="mb-1 bg-dark py-4 d-flex flex-column align-items-center justify-content-center">
        <div class="w-50 overflow-hidden">
            <img src="{{ asset('global/assets/img/logophoenix.png') }}" alt="" class="h-100 w-100">
        </div>
    </div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="p-5">
        <h3>Login Admin</h3>
        <form wire:submit="login" class="my-3">
            <!-- Email Address -->
            <div class="mb-3">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input wire:model="form.email" id="email" placeholder="contoh@email.com" type="email"
                    name="email" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mb-3">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input wire:model="form.password" id="password" placeholder="******" type="password"
                    name="password" required autocomplete="current-password" />

                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="mb-3 form-check" for="remember">
                <input wire:model="form.remember" type="checkbox" name="remember" class="form-check-input"
                    id="remember">
                <label class="form-check-label" for="exampleCheck1">remember me</label>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 mt-4">
                @if (Route::has('password.request'))
                    <a class="text-muted" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>
</div>
