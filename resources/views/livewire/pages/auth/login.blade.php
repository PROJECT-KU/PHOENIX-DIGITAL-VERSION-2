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

        try {
            $this->form->authenticate();

            Session::regenerate();

            $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ambil pesan asli (bisa "kredensial salah" atau "akun diblokir").
            $pesan = collect($e->errors())->flatten()->first();
            if (! $pesan || str_contains($pesan, 'match our records') || str_contains($pesan, 'credentials')) {
                $pesan = 'Email atau kata sandi salah. Silakan periksa kembali.';
            }

            // Akun terblokir -> Swal khusus; selain itu -> Swal login gagal biasa.
            if (str_contains(strtolower($pesan), 'diblokir')) {
                $this->dispatch('account-blocked', message: $pesan);
            } else {
                $this->dispatch('login-failed', message: $pesan);
            }

            // Tetap lempar agar input ter-highlight.
            throw $e;
        }
    }
}; ?>

<div class="lemon-auth">
    @include('livewire.pages.auth.partials.auth-styles')

    <div class="lemon-card">
        {{-- Brand --}}
        <div class="text-center mb-4">
            @include('livewire.pages.auth.partials.lemon-logo')
            <h1 class="lemon-brand">lemon</h1>
            <p class="lemon-by">by acm</p>
            <p class="lemon-sub">Masuk untuk melanjutkan ke dashboard</p>
        </div>

        <form wire:submit="login"
            x-data
            x-init="
                const savedEmail = localStorage.getItem('lemon_email');
                if (savedEmail) { $wire.set('form.email', savedEmail); $wire.set('form.remember', true); }
            "
            x-on:submit="
                if ($wire.get('form.remember')) { localStorage.setItem('lemon_email', $wire.get('form.email') || ''); }
                else { localStorage.removeItem('lemon_email'); }
            ">
            {{-- Email --}}
            <div class="mb-3">
                <label class="lf-label" for="email">Email</label>
                <div class="lf-field">
                    <i class="bi bi-envelope lead-ico"></i>
                    <input wire:model="form.email" id="email" type="email" name="email" class="lf-input"
                        placeholder="contoh@email.com" required autofocus autocomplete="username">
                </div>
                <x-input-error :messages="$errors->get('form.email')" class="lf-error" />
            </div>

            {{-- Password --}}
            <div class="mb-3" x-data="{ show: false }">
                <label class="lf-label" for="password">Kata Sandi</label>
                <div class="lf-field">
                    <i class="bi bi-lock lead-ico"></i>
                    <input wire:model="form.password" id="password" name="password" class="lf-input"
                        x-bind:type="show ? 'text' : 'password'" placeholder="••••••••" required autocomplete="current-password">
                    <i class="lf-eye bi" :class="show ? 'bi-eye' : 'bi-eye-slash'" @click="show = !show"></i>
                </div>
                <x-input-error :messages="$errors->get('form.password')" class="lf-error" />
            </div>

            {{-- Remember + Forgot --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <label class="lf-check">
                    <input wire:model="form.remember" type="checkbox" name="remember">
                    <span>Ingat saya</span>
                </label>
                @if (Route::has('password.request'))
                <a class="lf-forgot" href="{{ route('password.request') }}" wire:navigate>Lupa sandi?</a>
                @endif
            </div>

            {{-- Tombol --}}
            <button type="submit" class="lf-btn" wire:loading.attr="disabled" wire:target="login">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>
    </div>

    <script>
        (function () {
            function fireGlossy(icon, title, text) {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    icon: icon, title: title, text: text,
                    background: 'rgba(255, 255, 255, 0.9)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', title: 'fw-bold' },
                    buttonsStyling: false,
                    confirmButtonText: 'Mengerti'
                });
            }
            window.__lemonFireGlossy = fireGlossy;

            // Error login -> Swal glossy jelas (daftar segera bila Livewire siap / mis. via wire:navigate)
            function registerLoginError() {
                if (window.__lemonLoginBound) return;
                window.__lemonLoginBound = true;
                Livewire.on('login-failed', (event) => {
                    const msg = (event && (event.message ?? (Array.isArray(event) ? event[0]?.message : null))) || 'Login gagal. Coba lagi.';
                    fireGlossy('error', 'Login Gagal', msg);
                });
                Livewire.on('account-blocked', (event) => {
                    const msg = (event && (event.message ?? (Array.isArray(event) ? event[0]?.message : null))) || 'Akun Anda diblokir. Hubungi admin.';
                    fireGlossy('error', 'Akun Diblokir', msg);
                });
            }
            if (window.Livewire) { registerLoginError(); }
            else { document.addEventListener('livewire:init', registerLoginError); }

            // Notifikasi berbasis session — tunggu Swal siap (jalan di full-load & SPA navigate)
            function whenSwalReady(cb) {
                if (typeof Swal !== 'undefined') { cb(); }
                else { setTimeout(() => whenSwalReady(cb), 60); }
            }
            whenSwalReady(() => {
                @if (session('idle_timeout'))
                fireGlossy('warning', 'Sesi Berakhir', @json(session('idle_timeout')));
                @endif
                @if (session('password_updated'))
                fireGlossy('success', 'Berhasil', @json(session('password_updated')));
                @endif
                @if (session('status'))
                fireGlossy('success', 'Informasi', @json(session('status')));
                @endif
            });
        })();
    </script>
</div>
