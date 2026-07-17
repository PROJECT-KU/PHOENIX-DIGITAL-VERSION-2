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

    #[Validate('required|string')]
    public string $nik = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Autentikasi via NOMOR INDUK KARYAWAN + kata sandi, dengan blokir akun
     * setelah 3x gagal (persisten di DB). Buka blokir hanya oleh admin lewat
     * fitur karyawan (status active).
     *
     * NIK tersimpan di employee_details; setelah user ditemukan, autentikasi
     * tetap memakai email+password miliknya (kredensial di tabel users tidak
     * berubah) sehingga sesi & "ingat saya" bekerja seperti biasa.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $nik = strtoupper(trim($this->nik));

        $user = User::whereHas('detail', fn ($q) => $q->where('nik', $nik))->first();

        // Akun sudah diblokir -> tolak, apa pun kata sandinya.
        if ($user && $user->isBlocked()) {
            throw ValidationException::withMessages([
                'form.nik' => 'Akun Anda diblokir karena '.self::MAX_ATTEMPTS.'x gagal login. Hubungi admin untuk membuka blokir.',
            ]);
        }

        $berhasil = $user
            && Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember);

        if (! $berhasil) {
            // Hitung kegagalan hanya pada NIK yang benar-benar ada.
            if ($user) {
                $user->increment('failed_login_attempts');

                if ($user->failed_login_attempts >= self::MAX_ATTEMPTS) {
                    $user->update(['status' => 'blokir']);

                    throw ValidationException::withMessages([
                        'form.nik' => 'Akun Anda diblokir karena '.self::MAX_ATTEMPTS.'x gagal login. Hubungi admin untuk membuka blokir.',
                    ]);
                }

                $sisa = self::MAX_ATTEMPTS - $user->failed_login_attempts;

                throw ValidationException::withMessages([
                    'form.nik' => "NIK atau kata sandi salah. Sisa {$sisa} percobaan sebelum akun diblokir.",
                ]);
            }

            throw ValidationException::withMessages([
                'form.nik' => 'NIK atau kata sandi salah.',
            ]);
        }

        // Sukses -> reset penghitung kegagalan.
        if ($user && $user->failed_login_attempts > 0) {
            $user->update(['failed_login_attempts' => 0]);
        }
    }
}
