<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.authentication')] #[Title('Buat Kata Sandi Baru · lemon')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => [
                'required', 'string', 'confirmed',
                function ($attribute, $value, $fail) {
                    if (strlen($value) < 8) {
                        $fail('Kata sandi minimal 8 karakter.');
                    } elseif (! preg_match('/[A-Z]/', $value)) {
                        $fail('Kata sandi harus mengandung minimal 1 huruf besar.');
                    } elseif (! preg_match('/[^A-Za-z]/', $value)) {
                        $fail('Kata sandi harus mengandung minimal 1 angka atau karakter spesial.');
                    }
                },
            ],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));
            $this->dispatch('reset-fail', message: __($status));

            return;
        }

        // Sukses -> tampilkan Swal glossy di halaman ini, lalu arahkan ke login (via JS).
        $this->dispatch('reset-success', message: 'Kata sandi berhasil diubah. Silakan login dengan kata sandi baru Anda.');
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
            <p class="lemon-sub">Buat kata sandi baru</p>
        </div>

        <form wire:submit="resetPassword">
            {{-- Email (readonly, dari tautan) --}}
            <div class="mb-3">
                <label class="lf-label" for="email">Email</label>
                <div class="lf-field">
                    <i class="bi bi-envelope lead-ico"></i>
                    <input wire:model="email" id="email" type="email" name="email" class="lf-input"
                        autocomplete="username" readonly>
                </div>
                <x-input-error :messages="$errors->get('email')" class="lf-error" />
            </div>

            {{-- Password baru --}}
            <div class="mb-3" x-data="{ show: false }">
                <label class="lf-label" for="password">Kata Sandi Baru</label>
                <div class="lf-field">
                    <i class="bi bi-lock lead-ico"></i>
                    <input wire:model="password" id="password" name="password" class="lf-input"
                        x-bind:type="show ? 'text' : 'password'" placeholder="••••••••" required autocomplete="new-password">
                    <i class="lf-eye bi" :class="show ? 'bi-eye' : 'bi-eye-slash'" @click="show = !show"></i>
                </div>
                <x-input-error :messages="$errors->get('password')" class="lf-error" />
                <small class="lf-hint">Min. 8 karakter, ada huruf besar, dan angka/karakter spesial.</small>
            </div>

            {{-- Konfirmasi Password --}}
            <div class="mb-4" x-data="{ show: false }">
                <label class="lf-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                <div class="lf-field">
                    <i class="bi bi-lock-fill lead-ico"></i>
                    <input wire:model="password_confirmation" id="password_confirmation" name="password_confirmation" class="lf-input"
                        x-bind:type="show ? 'text' : 'password'" placeholder="••••••••" required autocomplete="new-password">
                    <i class="lf-eye bi" :class="show ? 'bi-eye' : 'bi-eye-slash'" @click="show = !show"></i>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="lf-error" />
            </div>

            <button type="submit" class="lf-btn" wire:loading.attr="disabled" wire:target="resetPassword">
                <i class="bi bi-check2-circle me-1"></i> Simpan Kata Sandi
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
            const LOGIN_URL = @json(route('login'));

            function register() {
                if (window.__lemonResetBound) return;
                window.__lemonResetBound = true;
                Livewire.on('reset-fail', (event) => {
                    const msg = (event && (event.message ?? (Array.isArray(event) ? event[0]?.message : null))) || 'Tautan tidak valid atau kedaluwarsa.';
                    fireGlossy('error', 'Gagal', msg);
                });

                Livewire.on('reset-success', (event) => {
                    const msg = (event && (event.message ?? (Array.isArray(event) ? event[0]?.message : null))) || 'Kata sandi berhasil diubah.';
                    if (typeof Swal === 'undefined') { window.location.href = LOGIN_URL; return; }
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: msg,
                        background: 'rgba(255, 255, 255, 0.9)',
                        backdrop: 'rgba(139, 92, 246, 0.15)',
                        customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', title: 'fw-bold' },
                        buttonsStyling: false,
                        confirmButtonText: 'Ke Halaman Login',
                        allowOutsideClick: false,
                        timer: 4500,
                        timerProgressBar: true
                    }).then(() => { window.location.href = LOGIN_URL; });
                });
            }
            if (window.Livewire) { register(); }
            else { document.addEventListener('livewire:init', register); }
        })();
    </script>
</div>
