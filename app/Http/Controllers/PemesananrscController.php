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

        $conditions = collect($selectedBatches)->map(function ($item) {
            [$nama, $batch] = explode('|', $item);

            return ['nama_camp' => $nama, 'batch_camp' => $batch];
        });

        $invoiceItems = PemesananRsc::query()
            ->where(function ($query) use ($conditions) {
                foreach ($conditions as $condition) {
                    $query->orWhere(function ($q) use ($condition) {
                        $q->where('nama_camp', $condition['nama_camp'])
                            ->where('batch_camp', $condition['batch_camp']);
                    });
                }
            })
            ->selectRaw('
                nama_camp, 
                batch_camp, 
                MIN(tanggal_mulai_camp) as periode_mulai, 
                MAX(tanggal_akhir_camp) as periode_akhir,
                COUNT(id) as total_peserta, 
                SUM(total) as total_harga,
                MAX(harga_satuan) as harga_satuan
            ')
            ->groupBy('nama_camp', 'batch_camp')
            ->orderBy('nama_camp')
            ->orderBy('batch_camp')
            ->get();

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
