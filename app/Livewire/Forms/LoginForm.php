<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    /** Jumlah percobaan gagal sebelum akun diblokir. */
    public const MAX_ATTEMPTS = 3;

    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Autentikasi dengan blokir akun setelah 3x gagal (persisten di DB).
     * Buka blokir hanya bisa oleh admin lewat fitur karyawan (status active).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $user = User::where('email', $this->email)->first();

        // Akun sudah diblokir -> tolak, apa pun kata sandinya.
        if ($user && $user->isBlocked()) {
            throw ValidationException::withMessages([
                'form.email' => 'Akun Anda diblokir karena '.self::MAX_ATTEMPTS.'x gagal login. Hubungi admin untuk membuka blokir.',
            ]);
        }

        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            // Hitung kegagalan pada akun yang emailnya benar-benar ada.
            if ($user) {
                $user->increment('failed_login_attempts');

                if ($user->failed_login_attempts >= self::MAX_ATTEMPTS) {
                    $user->update(['status' => 'blokir']);

                    throw ValidationException::withMessages([
                        'form.email' => 'Akun Anda diblokir karena '.self::MAX_ATTEMPTS.'x gagal login. Hubungi admin untuk membuka blokir.',
                    ]);
                }

                $sisa = self::MAX_ATTEMPTS - $user->failed_login_attempts;

                throw ValidationException::withMessages([
                    'form.email' => "Email atau kata sandi salah. Sisa {$sisa} percobaan sebelum akun diblokir.",
                ]);
            }

            throw ValidationException::withMessages([
                'form.email' => 'Email atau kata sandi salah.',
            ]);
        }

        // Sukses -> reset penghitung kegagalan.
        if ($user && $user->failed_login_attempts > 0) {
            $user->update(['failed_login_attempts' => 0]);
        }
    }
}
