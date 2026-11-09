<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Lowongan;

class JobController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $jobs = Lowongan::query()
            // terbuka() = is_active 'active' (bukan true: kolomnya ENUM, dan
            // true kebetulan cocok lewat nomor urut enum) DAN batas lamaran
            // belum lewat.
            ->terbuka()
            // Yang segera ditutup lebih dulu; tanpa batas waktu di belakang.
            ->orderByRaw('batas_lamaran IS NULL')
            ->orderBy('batas_lamaran')
            ->orderBy('title')
            ->get()
            ->map(fn (Lowongan $l) => $l->untukSitus());

        return $this->ok($jobs, 'Jobs retrieved successfully', ['count' => $jobs->count()]);
    }

    /**
     * Get job detail by slug
     */
    public function show($slug)
    {
        $job = Lowongan::where('slug', $slug)->terbuka()->first();

        if (! $job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or no longer available',
            ], 404);
        }

        return $this->ok($job->untukSitus(lengkap: true), 'Job detail retrieved successfully');
    }
}
