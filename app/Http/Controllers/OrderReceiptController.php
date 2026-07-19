<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class OrderReceiptController extends Controller
{
    // Struk/receipt berbasis token pendek (/s/{token}) — tanpa expose UUID order.
    public function show(string $token)
    {
        Carbon::setLocale('id');

        $order = Order::where('share_token', $token)
            ->with(['customer', 'items.product', 'items.ebooks'])
            ->firstOrFail();

        // Struk hanya untuk pesanan SELESAI. Penjaga ditaruh di sini — bukan cuma
        // menyembunyikan tombolnya — karena token-nya bisa dibuka langsung lewat
        // URL /s/{token}. Tanpa ini, pesanan yang DIBATALKAN pun menyajikan struk
        // bertuliskan "LUNAS" (17 pesanan cancelled sudah punya token).
        abort_unless($order->status === 'completed', 404);

        $pdf = Pdf::loadView('exports.order-receipt-pdf', [
            'order' => $order,
        ]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Struk-' . $order->order_number . '.pdf');
    }
}
