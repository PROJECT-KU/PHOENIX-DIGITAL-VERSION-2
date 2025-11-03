<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $max = (int) (env('MAX_UPLOAD_MB', 2));
        $maxKB = $max * 1024;

        return [
            'job_id'       => ['required', 'integer', 'exists:tbl_jobs,id'],
            'name'         => ['required', 'string', 'max:120'],
            'email'        => ['required', 'email:rfc,dns', 'max:150'],
            'phone'        => ['required', 'string', 'max:30', 'regex:/^[\d\s\+\-\(\)]+$/'],
            'cv'           => ['required', 'file', 'mimetypes:application/pdf', 'max:' . $maxKB],
            'cover_letter' => ['required', 'file', 'mimetypes:application/pdf', 'max:' . $maxKB],

            // Optional CAPTCHA
            'cf_turnstile_response' => ['nullable', 'string'],
            'g_recaptcha_response'  => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        $max = (int) (env('MAX_UPLOAD_MB', 2));
        $maxKB = $max * 1024;

        return [
            'job_id.required' => 'Please select a job position',
            'job_id.exists'   => 'Selected job position is not valid',

            'name.required'   => 'Full name is required',
            'name.max'        => 'Name must not exceed 120 characters',

            'email.required'  => 'Email address is required',
            'email.email'     => 'Please provide a valid email address',

            'phone.required'  => 'Phone number is required',
            'phone.regex'     => 'Phone number format is invalid',

            'cv.required'     => 'CV file is required',
            'cv.mimetypes'    => 'CV must be a PDF file',
            'cv.max'          => "CV file size must not exceed {$maxKB}MB",

            'cover_letter.required'  => 'Cover letter is required',
            'cover_letter.mimetypes' => 'Cover letter must be a PDF file',
            'cover_letter.max'       => "Cover letter file size must not exceed {$maxKB}MB",
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'job_id'       => 'job position',
            'cv'           => 'CV',
            'cover_letter' => 'cover letter',
        ];
    }
}
