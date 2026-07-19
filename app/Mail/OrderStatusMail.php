<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderStatusMail extends Mailable
{
    public function __construct(
        public Order $order,
        public string $type // paid | cancelled | reminder
    ) {
        // Email PUBLIC dikirim via akun halo@phoenixdigital.id (mailer 'phoenix').
        $this->mailer = 'phoenix';
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'paid' => 'Pembayaran Berhasil — '.$this->order->order_number,
            'cancelled' => 'Pesanan Kedaluwarsa — '.$this->order->order_number,
            'reminder' => 'Segera Selesaikan Pembayaran — '.$this->order->order_number,
        ];

        $from = config('mail.mailers.phoenix.username', 'halo@phoenixdigital.id');

        return new Envelope(
            from: new Address($from, 'Phoenix Digital'),
            subject: $subjects[$this->type] ?? ('Pesanan '.$this->order->order_number),
        );
    }

    public function content(): Content
    {
        $map = [
            'paid' => [
                'icon' => '✓', 'badgeBg' => '#ecfdf5', 'badgeColor' => '#10b981',
                'heading' => 'Pembayaran Berhasil!',
                'intro' => 'Terima kasih! Pembayaran Anda sudah kami terima dan pesanan sedang diproses. Detail akun akan dikirim via WhatsApp/email secepatnya pada jam operasional.',
                'ctaText' => 'Lacak Pesanan',
            ],
            'cancelled' => [
                'icon' => '⌛', 'badgeBg' => '#fef2f2', 'badgeColor' => '#ef4444',
                'heading' => 'Pesanan Kedaluwarsa',
                'intro' => 'Waktu pembayaran telah habis sehingga pesanan ini dibatalkan. Tidak masalah — Anda bisa memesan kembali kapan saja.',
                'ctaText' => 'Belanja Lagi',
            ],
            'reminder' => [
                'icon' => '⏰', 'badgeBg' => '#fff7e6', 'badgeColor' => '#f0531e',
                'heading' => 'Segera Selesaikan Pembayaran',
                'intro' => 'Pesanan Anda masih menunggu pembayaran. Selesaikan sebelum waktunya habis agar pesanan tidak dibatalkan otomatis.',
                'ctaText' => 'Bayar Sekarang',
            ],
        ];
        $d = $map[$this->type] ?? $map['paid'];

        $ctaUrl = match ($this->type) {
            'reminder' => route('payment', $this->order),
            'cancelled' => route('shop.index'),
            // Dulu mengarah ke struk, tapi struk kini hanya untuk pesanan SELESAI —
            // pesanan yang baru dibayar akan kena 404. Halaman lacak tetap berguna:
            // pembeli melihat statusnya, dan tombol struk muncul di sana saat selesai.
            default => route('track-order'),
        };

        return new Content(
            view: 'emails.order-status',
            with: array_merge($d, ['order' => $this->order, 'ctaUrl' => $ctaUrl]),
        );
    }
}
