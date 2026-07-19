<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    /** Jumlah percobaan gagal sebelum akun diblokir. */
    public const MAX_ATTEMPTS = 3;

    /** Satu kalimat blokir, dipakai di semua cabang agar seragam. */
    private const PESAN_DIBLOKIR = 'Akun Anda diblokir karena '.self::MAX_ATTEMPTS.'x gagal login. Hubungi admin untuk membuka blokir.';

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
        /*
         * Pembatasan per ALAMAT IP, di luar penghitung per akun.
         *
         * Tanpa ini penyerang bisa mencoba tanpa henti: menebak NIK yang ada
         * (pesan galatnya berbeda), lalu mengunci akun siapa pun cukup dengan
         * 3x salah. Dengan jeda ini, satu IP hanya dapat 10 percobaan/menit
         * sehingga penguncian massal tak lagi praktis.
         */
        $kunciIp = 'login-ip:'.request()->ip();

        if (RateLimiter::tooManyAttempts($kunciIp, 10)) {
            $detik = RateLimiter::availableIn($kunciIp);

            throw ValidationException::withMessages([
                'form.nik' => 'Terlalu banyak percobaan. Coba lagi dalam '.ceil($detik / 60).' menit.',
            ]);
        }

        $nik = strtoupper(trim($this->nik));

        $user = User::whereHas('detail', fn ($q) => $q->where('nik', $nik))->first();

        // Akun sudah diblokir -> tolak, apa pun kata sandinya.
        if ($user && $user->isBlocked()) {
            RateLimiter::hit($kunciIp, 60);

            throw ValidationException::withMessages([
                'form.nik' => self::PESAN_DIBLOKIR,
            ]);
        }

        $berhasil = $user
            && Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember);

        if (! $berhasil) {
            RateLimiter::hit($kunciIp, 60);

            // Hitung kegagalan hanya pada NIK yang benar-benar ada.
            if ($user) {
                $user->increment('failed_login_attempts');

                if ($user->failed_login_attempts >= self::MAX_ATTEMPTS) {
                    $user->update(['status' => 'blokir']);

                    throw ValidationException::withMessages([
                        'form.nik' => self::PESAN_DIBLOKIR,
                    ]);
                }
            }

            /*
             * Pesan SERAGAM, tak peduli NIK-nya ada atau tidak.
             *
             * Dulu pesannya menyebut "sisa N percobaan" hanya bila NIK ada,
             * sehingga penyerang bisa memetakan NIK karyawan yang valid cukup
             * dari beda pesannya. Sisa percobaan sengaja tak lagi diberitahukan
             * — karyawan yang benar-benar lupa sandi tetap terbantu lewat
             * pesan blokir di atas dan bantuan admin.
             */
            throw ValidationException::withMessages([
                'form.nik' => 'NIK atau kata sandi salah.',
            ]);
        }

        // Sukses -> bersihkan penghitung kegagalan (akun & IP).
        RateLimiter::clear($kunciIp);

        if ($user->failed_login_attempts > 0) {
            $user->update(['failed_login_attempts' => 0]);
        }
    }
}
