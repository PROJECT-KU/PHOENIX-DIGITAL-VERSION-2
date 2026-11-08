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
            // 'active', bukan true: kolomnya ENUM('active','non-active').
            // true kebetulan cocok karena MySQL membandingkannya dengan nomor
            // urut enum (1 = 'active') — urutan enum berubah, semua lowongan
            // lenyap dari situs tanpa galat.
            ->active()
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'created_at']);

        return $this->ok($jobs, 'Jobs retrieved successfully', ['count' => $jobs->count()]);
    }

    /**
     * Get job detail by slug
     */
    public function show($slug)
    {
        $job = Lowongan::where('slug', $slug)
            ->where('is_active', 'active')
            ->first();

        if (! $job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or no longer available',
            ], 404);
        }

        return $this->ok([
            'id' => $job->id,
            'title' => $job->title,
            'slug' => $job->slug,
            'requirements' => $job->requirements,
            'descriptions' => $job->descriptions,
            'posted_at' => $job->created_at->format('Y-m-d'),
            'is_active' => $job->is_active,
        ], 'Job detail retrieved successfully');
    }
}
