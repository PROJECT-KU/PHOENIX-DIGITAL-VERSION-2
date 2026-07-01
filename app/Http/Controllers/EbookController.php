<?php

namespace App\Http\Controllers;

use App\Models\Ebook;

class EbookController extends Controller
{
    // Unduh file ebook untuk ADMIN (route ini diproteksi auth + permission di web.php)
    public function download(Ebook $ebook)
    {
        $path = storage_path('app/ebooks/' . $ebook->file);
        abort_unless($ebook->file && is_file($path), 404);

        return response()->download($path, $ebook->judul . '.pdf');
    }
}
