<?php

namespace App\Livewire\Layout;

use App\Models\CustomerMessage;
use App\Models\Order;
use App\Models\OrderUpload;
use App\Models\ProductReview;
use App\Models\Testimoni;
use Livewire\Component;

/**
 * Poller ringan untuk badge judul tab + popup notifikasi OS (seperti WhatsApp).
 *
 * SENGAJA dipisah dari sidebar: sidebar menentukan menu aktif via
 * request()->routeIs(), dan bila sidebar ikut di-poll, setiap poll me-render
 * ulang sidebar sehingga kelas .active/.open hilang (dropdown menutup sendiri).
 * Komponen ini hanya merender <span> hitungan tak terlihat, jadi poll-nya tidak
 * pernah mengganggu tampilan menu.
 *
 * wire:poll.keep-alive di blade menjaga hitungan tetap segar walau admin sedang
 * di aplikasi/tab lain, sehingga popup tetap muncul saat ada hal baru.
 */
class NotifPoller extends Component
{
    public function render()
    {
        $login = auth()->check();
        $u = $login ? auth()->user() : null;

        $pesananTokoPaid = $login && $u->hasPermission('view_pemesanantoko')
            ? Order::paid()->count() : 0;

        $testimoniBaru = $login && $u->hasPermission('view_testimoni')
            ? Testimoni::menunggu()->count() : 0;

        $ulasanBaru = $login && $u->hasPermission('view_productreview')
            ? ProductReview::where('status', 'pending')->count() : 0;

        $helpdeskBaru = $login && $u->hasPermission('view_customer_message')
            ? CustomerMessage::unread()->count() : 0;

        // Pengecekan plagiasi/AI yang menunggu diproses (mis. customer paket 5x
        // mengunggah file ke-2). Naiknya angka ini memicu popup + suara "lemon"
        // di JS notif-poller, jadi admin tahu ada pengecekan baru real-time.
        $pengecekanBaru = $login && $u->hasPermission('view_pemesanantoko')
            ? OrderUpload::where('status', 'menunggu')->count() : 0;

        return view('livewire.layout.notif-poller', [
            'pesananTokoPaid' => $pesananTokoPaid,
            'testimoniBaru' => $testimoniBaru,
            'ulasanBaru' => $ulasanBaru,
            'helpdeskBaru' => $helpdeskBaru,
            'pengecekanBaru' => $pengecekanBaru,
            // Badge judul tab "(N) lemon" = jumlah hal baru yg perlu ditindaklanjuti.
            'titleBadge' => $pesananTokoPaid + $testimoniBaru + $ulasanBaru + $helpdeskBaru,
        ]);
    }
}
