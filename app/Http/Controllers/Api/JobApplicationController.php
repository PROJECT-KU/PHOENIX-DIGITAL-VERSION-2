<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\JobApplication;
use App\Support\Asthana\KabarSitus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobApplicationController extends Controller
{
    public function store(StoreJobApplicationRequest $request)
    {
        try {
            // Validasi sudah dilakukan di FormRequest
            $cv = $request->file('cv');
            $cl = $request->file('cover_letter');
            $lampiran = $request->file('lampiran');

            // Generate unique subdirectory
            $subdir = now()->format('Y/m').'/'.Str::uuid()->toString();

            // Diisi DI DALAM transaksi, tapi harus terbaca di catch untuk
            // dibersihkan — jadi dideklarasikan di sini dan diikat by-reference.
            $cvPath = null;
            $clPath = null;
            $lampiranPath = null;

            // Atomic transaction
            $app = DB::transaction(function () use ($request, $cv, $cl, $lampiran, $subdir, &$cvPath, &$clPath, &$lampiranPath) {
                $cvOriginalName = pathinfo($cv->getClientOriginalName(), PATHINFO_FILENAME);
                $cvFileName = Str::slug($cvOriginalName).'_'.Str::random(8).'.pdf';

                // Disk PRIVAT: CV & surat lamaran memuat data pribadi pelamar.
                $cvPath = $cv->storeAs("applications/{$subdir}", $cvFileName, 'local');

                $clOriginalName = pathinfo($cl->getClientOriginalName(), PATHINFO_FILENAME);
                $clFileName = Str::slug($clOriginalName).'_'.Str::random(8).'.pdf';
                $clPath = $cl->storeAs("applications/{$subdir}", $clFileName, 'local');

                // Berkas pendukung opsional.
                if ($lampiran) {
                    $lampiranNama = pathinfo($lampiran->getClientOriginalName(), PATHINFO_FILENAME);
                    $lampiranPath = $lampiran->storeAs(
                        "applications/{$subdir}",
                        'pendukung-'.Str::slug($lampiranNama).'_'.Str::random(8).'.pdf',
                        'local'
                    );
                }

                // Create application record
                return JobApplication::create([
                    'job_id' => $request->input('job_id') ?: null,
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone'),
                    'cv_path' => $cvPath,
                    'cover_letter_path' => $clPath,
                    'lampiran_path' => $lampiranPath,
                    'portofolio_url' => $request->input('portofolio_url') ?: null,
                    'sumber_info' => $request->input('sumber_info') ?: null,
                    'ip_address' => $request->ip(),
                ]);
            });

            // Lonceng/push admin + surel tanda terima ke pelamar. Keduanya
            // dibungkus sendiri: kegagalannya tidak boleh membatalkan lamaran
            // yang sudah tersimpan.
            KabarSitus::lamaranMasuk($app);

            return response()->json([
                'success' => true,
                'message' => 'Lamaran berhasil dikirim',
                'data' => [
                    'id' => $app->id,
                    'application_number' => str_pad($app->id, 6, '0', STR_PAD_LEFT),
                    'submitted_at' => $app->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Job Application Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->except(['cv', 'cover_letter', 'lampiran']), // jangan log file
            ]);

            // Bersihkan berkas yang sempat tersimpan. Disk 'local', SAMA dengan
            // tempat storeAs() menaruhnya — dulu yang dibersihkan disk 'public',
            // dan $cvPath tidak pernah terisi di luar closure, sehingga PDF
            // lamaran yang gagal tertinggal selamanya.
            foreach ([$cvPath ?? null, $clPath ?? null, $lampiranPath ?? null] as $jalur) {
                if ($jalur && Storage::disk('local')->exists($jalur)) {
                    Storage::disk('local')->delete($jalur);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Lamaran gagal dikirim. Silakan coba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
