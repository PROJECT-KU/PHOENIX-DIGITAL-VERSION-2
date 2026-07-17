<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;

/**
 * Kirim pengingat Nomor Induk Karyawan ke email karyawan (fitur "Lupa NIK").
 * Dikirim sinkron (bukan queue) karena aksinya jarang & butuh langsung sampai.
 */
class NikReminderMail extends Mailable
{
    public function __construct(public User $user, public string $nik) {}

    public function build()
    {
        return $this->subject('Nomor Induk Karyawan Anda — lemon by ACM')
            ->view('emails.nik-reminder', [
                'user' => $this->user,
                'nik' => $this->nik,
            ]);
    }
}
