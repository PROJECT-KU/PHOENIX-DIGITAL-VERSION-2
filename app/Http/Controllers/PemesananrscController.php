<?php

namespace App\Http\Controllers;

use App\Models\PemesananRsc;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PemesananrscController extends Controller
{
    public function previewInvoice(Request $request)
    {
        $selectedBatches = $request->input('batches', []);

        if (empty($selectedBatches)) {
            return 'Silakan pilih batch terlebih dahulu.';
        }

        // Sumber tunggal item invoice (sama dgn unduh invoice) — termasuk daftar
        // akun untuk batch metode "per_akun".
        $invoiceItems = PemesananRsc::invoiceItemsFor($selectedBatches);

        $data = [
            'invoiceNumber' => 'INV-PREVIEW',
            'date' => now()->translatedFormat('d F Y'),
            'items' => $invoiceItems,
            'grandTotal' => $invoiceItems->sum('total_harga'),
        ];

        $pdf = Pdf::loadView('exports.invoice-pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('preview_invoice.pdf');
    }
}
