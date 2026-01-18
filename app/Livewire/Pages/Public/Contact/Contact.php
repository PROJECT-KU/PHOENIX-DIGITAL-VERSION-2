<?php

namespace App\Livewire\Pages\Public\Contact;

use App\Models\CustomerMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Contact extends Component
{
    public $name;

    public $email;

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
            'message' => 'required|string|max:2000',
        ]);

        CustomerMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'message' => $this->message,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        RateLimiter::hit($key);

        $this->reset(['name', 'email', 'message']);
        session()->flash('success', 'Terima kasih! Pesan Anda telah kami terima.');
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.contact.contact');
    }
}
