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
            // Batas lamaran yang sudah lewat juga ditolak (sama dengan yang
            // tampil di situs: Lowongan::terbuka()). Kosong = lamaran umum.
            'job_id' => ['nullable', 'integer', function (string $atribut, $nilai, \Closure $gagal) {
                if (! \App\Models\Lowongan::terbuka()->whereKey($nilai)->exists()) {
                    $gagal('Posisi ini sudah tidak dibuka');
                }
            }],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[\d\s\+\-\(\)]+$/'],
            'cv' => ['required', 'file', 'mimetypes:application/pdf', 'max:'.$maxKB],
            'cover_letter' => ['required', 'file', 'mimetypes:application/pdf', 'max:'.$maxKB],
            // Berkas pendukung opsional: portofolio, sertifikat, transkrip, dsb.
            'lampiran' => ['nullable', 'file', 'mimetypes:application/pdf', 'max:'.$maxKB],
            'portofolio_url' => ['nullable', 'url:http,https', 'max:255'],
            'sumber_info' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Models\JobApplication::SUMBER_INFO))],

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
            'job_id.integer' => 'Pilih posisi yang dilamar',

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

            'lampiran.mimetypes' => 'Berkas pendukung harus berformat PDF',
            'lampiran.max' => "Ukuran berkas pendukung maksimal {$max} MB",

            'portofolio_url.url' => 'Tautan portofolio harus diawali http:// atau https://',
            'portofolio_url.max' => 'Tautan portofolio maksimal 255 karakter',
            'sumber_info.in' => 'Pilihan sumber informasi tidak dikenal',
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
