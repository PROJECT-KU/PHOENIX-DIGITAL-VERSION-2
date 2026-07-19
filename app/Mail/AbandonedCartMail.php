<?php

namespace App\Mail;

use App\Models\AbandonedCart;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AbandonedCartMail extends Mailable
{
    public function __construct(public AbandonedCart $cart)
    {
        $this->mailer = 'phoenix';
    }

    public function envelope(): Envelope
    {
        $from = config('mail.mailers.phoenix.username', 'halo@phoenixdigital.id');

        return new Envelope(
            from: new Address($from, 'Phoenix Digital'),
            subject: '🛒 Masih ada produk di keranjang Anda — Phoenix Digital',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.abandoned-cart',
            with: [
                'cart' => $this->cart,
                'shopUrl' => route('shop.index'),
            ],
        );
    }
}
