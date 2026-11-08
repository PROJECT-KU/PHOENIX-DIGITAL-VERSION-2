<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $max = (int) config('app.max_upload_mb', 2);
        $maxKB = $max * 1024;

        return [
            // Hanya lowongan yang masih dibuka: lowongan yang sudah ditutup tetap
            // ada di tabel, dan tanpa syarat ini lamarannya tetap diterima.
            'job_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('lowongans', 'id')->where('is_active', 'active')],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[\d\s\+\-\(\)]+$/'],
            'cv' => ['required', 'file', 'mimetypes:application/pdf', 'max:'.$maxKB],
            'cover_letter' => ['required', 'file', 'mimetypes:application/pdf', 'max:'.$maxKB],

            // Optional CAPTCHA
            'cf_turnstile_response' => ['nullable', 'string'],
            'g_recaptcha_response' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        $max = (int) config('app.max_upload_mb', 2);

        return [
            // Ditampilkan apa adanya di bawah kolom formulir situs, jadi harus
            // berbahasa Indonesia seperti situsnya.
            'job_id.required' => 'Pilih posisi yang dilamar',
            'job_id.exists' => 'Posisi ini sudah tidak dibuka',

            'name.required' => 'Nama lengkap wajib diisi',
            'name.max' => 'Nama maksimal 120 karakter',

            'email.required' => 'Email wajib diisi',
            'email.email' => 'Alamat email tidak valid',
            'email.max' => 'Email maksimal 150 karakter',

            'phone.required' => 'Nomor telepon wajib diisi',
            'phone.regex' => 'Format nomor telepon tidak valid',
            'phone.max' => 'Nomor telepon maksimal 30 karakter',

            'cv.required' => 'CV wajib diunggah',
            'cv.mimetypes' => 'CV harus berformat PDF',
            // Dulu tertulis {$maxKB}MB → "2048MB". Satuannya MB, angkanya $max.
            'cv.max' => "Ukuran CV maksimal {$max} MB",

            'cover_letter.required' => 'Surat lamaran wajib diunggah',
            'cover_letter.mimetypes' => 'Surat lamaran harus berformat PDF',
            'cover_letter.max' => "Ukuran surat lamaran maksimal {$max} MB",
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'job_id' => 'posisi',
            'cv' => 'CV',
            'cover_letter' => 'surat lamaran',
        ];
    }
}
