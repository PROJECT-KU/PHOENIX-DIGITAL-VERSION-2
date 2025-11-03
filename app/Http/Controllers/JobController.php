<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $jobs =  Job::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title']);

        return $this->ok($jobs, 'Jobs retrieved successfully', ['count' => $jobs->count()]);
    }
}
