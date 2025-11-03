<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    public function store(StoreJobApplicationRequest $request)
    {
        try {
            // Validasi sudah dilakukan di FormRequest
            $cv = $request->file('cv');
            $cl = $request->file('cover_letter');

            // Generate unique subdirectory
            $subdir = now()->format('Y/m') . '/' . Str::uuid()->toString();

            // Atomic transaction
            $app = DB::transaction(function () use ($request, $cv, $cl, $subdir) {
                $cvOriginalName = pathinfo($cv->getClientOriginalName(), PATHINFO_FILENAME);
                $clOriginalName = pathinfo($cl->getClientOriginalName(), PATHINFO_FILENAME);
                $cvFileName = Str::slug($cvOriginalName) . '_' . Str::random(8) . '.pdf';
                $clFileName = Str::slug($clOriginalName) . '_' . Str::random(8) . '.pdf';

                $cvPath = $cv->storeAs("applications/{$subdir}", $cvFileName, 'public');
                $clPath = $cl->storeAs("applications/{$subdir}", $clFileName, 'public');

                // Create application record
                return JobApplication::create([
                    'job_id'            => $request->input('job_id'),
                    'name'              => $request->input('name'),
                    'email'             => $request->input('email'),
                    'phone'             => $request->input('phone'),
                    'cv_path'           => $cvPath,
                    'cover_letter_path' => $clPath,
                    'ip_address'        => $request->ip(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'data'    => [
                    'id'                 => $app->id,
                    'application_number' => str_pad($app->id, 6, '0', STR_PAD_LEFT),
                    'submitted_at'       => $app->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Job Application Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->except(['cv', 'cover_letter']), // jangan log file
            ]);

            // Cleanup uploaded files jika ada error
            if (isset($cvPath) && Storage::disk('public')->exists($cvPath)) {
                Storage::disk('public')->delete($cvPath);
            }
            if (isset($clPath) && Storage::disk('public')->exists($clPath)) {
                Storage::disk('public')->delete($clPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application. Please try again.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
