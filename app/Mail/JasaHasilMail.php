<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderUpload;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Beri tahu customer bahwa HASIL pengecekan plagiasi sudah siap, beserta
 * link untuk membukanya & mengunduh. Dikirim saat admin menyimpan hasil.
 * Branding Phoenix (mailer 'phoenix' = halo@phoenixdigital.id), seragam
 * dengan email pesanan customer lain.
 */
class JasaHasilMail extends Mailable
{
    public function __construct(
        public Order $order,
        public OrderUpload $upload,
    ) {
        $this->mailer = 'phoenix';
    }

    public function envelope(): Envelope
    {
        $from = config('mail.mailers.phoenix.username', 'halo@phoenixdigital.id');

        // Parafrase menghasilkan dokumen kerja (DOCX), bukan sekadar laporan
        // pengecekan — subjeknya menyesuaikan agar tidak membingungkan customer.
        $judul = $this->upload->hasil_docx_path
            ? 'Dokumen Hasil Sudah Siap'
            : 'Hasil Pengecekan Sudah Siap';

        return new Envelope(
            from: new Address($from, 'Phoenix Digital'),
            subject: $judul.' — '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.jasa-hasil',
            with: [
                'order' => $this->order,
                'upload' => $this->upload,
                'link' => url('/cek/'.$this->order->share_token),
            ],
        );
    }
}
