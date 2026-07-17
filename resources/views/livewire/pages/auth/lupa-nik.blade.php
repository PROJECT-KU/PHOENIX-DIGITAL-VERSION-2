<?php

use App\Mail\NikReminderMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.authentication')] class extends Component {
    public string $email = '';

    /**
     * Kirim NIK ke email karyawan.
     *
     * Sesuai permintaan & seragam dengan halaman "Lupa sandi": cek dulu apakah
     * email terdaftar. Bila tidak, beri tahu. Bila ya (dan punya NIK), kirim.
     */
    public function sendNik(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $user = User::with('detail')->where('email', $this->email)->first();

        // 1) Email tidak terdaftar.
        if (! $user) {
            $this->addError('email', 'Email tidak terdaftar di sistem.');
            $this->dispatch('nik-error', message: 'Email tidak terdaftar di sistem. Periksa kembali alamat email Anda.');

            return;
        }

        // 2) Terdaftar tapi akunnya belum punya NIK.
        if (! $user->detail || ! $user->detail->nik) {
            $this->addError('email', 'Akun ini belum memiliki NIK.');
            $this->dispatch('nik-error', message: 'Akun ini terdaftar tetapi belum memiliki Nomor Induk Karyawan. Silakan hubungi admin.');

            return;
        }

        // 3) Kirim NIK ke email.
        try {
            Mail::to($user->email)->send(new NikReminderMail($user, $user->detail->nik));
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('nik-error', message: 'Gagal mengirim email. Silakan coba lagi beberapa saat.');

            return;
        }

        $this->reset('email');
        $this->dispatch('nik-sent', message: 'Nomor Induk Karyawan telah dikirim ke email Anda. Silakan cek kotak masuk (termasuk folder Spam).');
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
            <p class="lemon-sub">Lupa Nomor Induk Karyawan</p>
        </div>

        <p class="text-center mb-4" style="font-size:.9rem; color:#6b7280;">
            Masukkan email akun Anda. Kami akan mengirim Nomor Induk Karyawan (NIK) ke email tersebut.
        </p>

        <form wire:submit="sendNik">
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
            <button type="submit" class="lf-btn" wire:loading.attr="disabled" wire:target="sendNik">
                <i class="bi bi-send me-1"></i> Kirim NIK ke Email
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
                if (window.__lemonLupaNikBound) return;
                window.__lemonLupaNikBound = true;
                Livewire.on('nik-sent', (event) => {
                    const msg = (event && (event.message ?? (Array.isArray(event) ? event[0]?.message : null))) || 'NIK telah dikirim ke email Anda.';
                    fireGlossy('success', 'Email Terkirim', msg);
                });
                Livewire.on('nik-error', (event) => {
                    const msg = (event && (event.message ?? (Array.isArray(event) ? event[0]?.message : null))) || 'Gagal mengirim NIK.';
                    fireGlossy('error', 'Gagal', msg);
                });
            }
            if (window.Livewire) { register(); }
            else { document.addEventListener('livewire:init', register); }
        })();
    </script>
</div>
