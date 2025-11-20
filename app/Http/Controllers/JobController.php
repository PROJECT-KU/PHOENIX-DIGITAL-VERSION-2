<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Lowongan;

class JobController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $jobs = Lowongan::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title']);

        return $this->ok($jobs, 'Jobs retrieved successfully', ['count' => $jobs->count()]);
    }
}
