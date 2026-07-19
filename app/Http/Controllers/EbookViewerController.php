<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use Illuminate\Http\Request;

class EbookViewerController extends Controller
{
    // Halaman viewer view-only (PDF.js) — link pendek /e/{token}
    public function show(string $token)
    {
        $ebook = Ebook::where('share_token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        return view('ebook.viewer', ['ebook' => $ebook]);
    }

    // Streaming byte PDF — hanya boleh diakses oleh viewer (butuh header khusus),
    // sehingga URL tidak bisa dibuka/diunduh langsung dari browser.
    public function raw(Request $request, string $token)
    {
        abort_unless($request->header('X-Ebook-View') === '1', 403);

        $ebook = Ebook::where('share_token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        $path = storage_path('app/ebooks/' . $ebook->file);
        abort_unless($ebook->file && is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="view.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
