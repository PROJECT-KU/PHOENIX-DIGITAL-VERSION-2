<?php

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.authentication')] #[Title('Lupa Kata Sandi · lemon')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        // Cek dulu apakah email terdaftar di sistem.
        if (! User::where('email', $this->email)->exists()) {
            $this->addError('email', 'Email tidak terdaftar di sistem.');
            $this->dispatch('reset-error', message: 'Email tidak terdaftar di sistem. Periksa kembali alamat email Anda.');

            return;
        }

        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            $this->dispatch('reset-error', message: __($status));

            return;
        }

        $this->reset('email');
        session()->flash('status', __($status));
        $this->dispatch('reset-sent', message: 'Tautan reset kata sandi telah dikirim. Silakan cek kotak masuk email Anda (termasuk folder Spam).');
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
            <p class="lemon-sub">Reset kata sandi Anda</p>
        </div>

        <p class="text-center mb-4" style="font-size:.9rem; color:#6b7280;">
            Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.
        </p>

        <form wire:submit="sendPasswordResetLink">
            {{-- Email --}}
            <div class="mb-4">
                <label class="lf-label" for="email">Email</label>
                <div class="lf-field">
                    <i class="bi bi-envelope lead-ico"></i>
                    <input wire:model="email" id="email" type="email" name="email" class="lf-input"
                        placeholder="contoh@email.com" required autofocus autocomplete="username">
                </div>
                <x-input-error :messages="$errors->get('email')" class="lf-error" />
            </div>

            {{-- Tombol --}}
            <button type="submit" class="lf-btn" wire:loading.attr="disabled" wire:target="sendPasswordResetLink">
                <i class="bi bi-send me-1"></i> Kirim Tautan Reset
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="lf-back" wire:navigate><i class="bi bi-arrow-left"></i> Kembali ke Login</a>
        </div>
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

            function register() {
                if (window.__lemonForgotBound) return;
                window.__lemonForgotBound = true;
                Livewire.on('reset-sent', (event) => {
                    const msg = (event && (event.message ?? (Array.isArray(event) ? event[0]?.message : null))) || 'Tautan reset telah dikirim.';
                    fireGlossy('success', 'Email Terkirim', msg);
                });
                Livewire.on('reset-error', (event) => {
                    const msg = (event && (event.message ?? (Array.isArray(event) ? event[0]?.message : null))) || 'Gagal mengirim tautan reset.';
                    fireGlossy('error', 'Gagal', msg);
                });
            }
            // Daftarkan segera bila Livewire sudah siap (mis. tiba via wire:navigate),
            // atau tunggu livewire:init pada load pertama.
            if (window.Livewire) { register(); }
            else { document.addEventListener('livewire:init', register); }
        })();
    </script>
</div>
