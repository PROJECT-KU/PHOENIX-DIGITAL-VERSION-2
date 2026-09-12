<?php

namespace App\Livewire\Pages\Public\Contact;

use App\Models\Banners;
use App\Models\CustomerMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Contact extends Component
{
    public const WA = '6289505967995';

    public const EMAIL = 'halo@phoenixdigitalwarehouse.com';

    public $name;

    public $email;

    public $no_telp;

    public $message;

    // Honeypot field
    public $website_url;

    public function save(Request $request)
    {
        if (! empty($this->website_url)) {
            session()->flash('success', 'Pesan terkirim!');

            return;
        }

        $key = 'contact-form:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('rate_limit', "Terlalu banyak percobaan. Silakan tunggu $seconds detik lagi.");

            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'no_telp' => ['required', 'string', 'regex:/^\+[1-9]\d{6,14}$/'],
            'message' => 'required|string|max:2000',
        ]);

        CustomerMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'no_telp' => $this->no_telp,
            'message' => $this->message,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        RateLimiter::hit($key);

        // Catatan: field dikosongkan via JS (lihat handler 'contact-success' di blade)
        // untuk menghindari re-render yang mengganggu widget intl-tel-input.
        $this->dispatch('contact-success', message: 'Terima kasih! Pesan Anda telah kami terima.');
    }

    /** Tautan WhatsApp admin dengan pesan pembuka yang sudah terisi. */
    public static function wa(string $pesan): string
    {
        return 'https://wa.me/'.self::WA.'?text='.rawurlencode($pesan);
    }

    /**
     * Kanal kontak. Disusun di sini, bukan di view, supaya nomor/alamatnya
     * tidak tercecer di markup dan bisa diuji. Tiap kanal punya warnanya
     * sendiri (--c di view), bahasa visual yang sama dengan Shop & Bundling.
     */
    public static function kanal(): array
    {
        return [
            ['ikon' => 'bi-whatsapp', 'warna' => '#16a34a', 'label' => 'WhatsApp', 'nilai' => '0895-0596-7995',
                'href' => self::wa('Halo Phoenix Digital, saya ingin bertanya.'), 'baru' => true],
            ['ikon' => 'bi-envelope-fill', 'warna' => '#2563eb', 'label' => 'Email', 'nilai' => self::EMAIL,
                'href' => 'mailto:'.self::EMAIL, 'baru' => false],
            ['ikon' => 'bi-geo-alt-fill', 'warna' => '#db2777', 'label' => 'Alamat',
                'nilai' => 'Jl. Durmo, Ngemplak, Mlati, Sleman, Yogyakarta', 'href' => null, 'baru' => false],
            ['ikon' => 'bi-clock-fill', 'warna' => '#d97706', 'label' => 'Jam Operasional',
                'nilai' => 'Setiap hari · 08.00–21.00 WIB', 'href' => null, 'baru' => false],
        ];
    }

    /** Media sosial resmi. */
    public static function sosial(): array
    {
        return [
            ['ikon' => 'bi-facebook', 'warna' => '#1877f2', 'label' => 'Facebook', 'href' => 'https://web.facebook.com/profile.php?id=61586376808425'],
            ['ikon' => 'bi-instagram', 'warna' => '#d62976', 'label' => 'Instagram', 'href' => 'https://www.instagram.com/phoenixdigital.id/'],
            ['ikon' => 'bi-tiktok', 'warna' => '#111827', 'label' => 'TikTok', 'href' => 'https://www.tiktok.com/@phoenix_digitalwarehouse'],
        ];
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        $banners = Banners::tayang()->get();

        return view('livewire.pages.public.contact.contact', [
            'banners' => $banners,
            'kanal' => self::kanal(),
            'sosial' => self::sosial(),
        ]);
    }
}
