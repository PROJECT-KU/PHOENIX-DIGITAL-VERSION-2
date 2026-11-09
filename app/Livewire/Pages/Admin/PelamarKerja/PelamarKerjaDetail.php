<?php

namespace App\Livewire\Pages\Admin\PelamarKerja;

use App\Mail\StatusLamaranMail;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PelamarKerjaDetail extends Component
{
    public $pelamar;

    /** Tahap yang dipilih di formulir (belum tentu sudah disimpan). */
    public string $statusBaru = 'baru';

    /** Pesan untuk pelamar, ikut di surel pemberitahuan (mis. jadwal wawancara). */
    public string $pesanPelamar = '';

    public bool $kirimEmail = true;

    public string $catatanInternal = '';

    public function mount($id)
    {
        $this->pelamar = JobApplication::with('job')->findOrFail($id);
        $this->statusBaru = $this->pelamar->status ?: 'baru';
        $this->catatanInternal = (string) $this->pelamar->catatan_internal;
    }

    public function simpanStatus(): void
    {
        $this->validate([
            'statusBaru' => ['required', Rule::in(array_keys(JobApplication::STATUS))],
            'pesanPelamar' => ['nullable', 'string', 'max:2000'],
        ], [
            'pesanPelamar.max' => 'Pesan maksimal 2000 karakter.',
        ]);

        if ($this->statusBaru === $this->pelamar->status) {
            $this->dispatch('swal-error', message: 'Tahapnya sama dengan sebelumnya — pilih tahap lain.');

            return;
        }

        $this->pelamar->update(['status' => $this->statusBaru, 'status_diubah_at' => now()]);

        $pesan = 'Tahap seleksi diperbarui.';
        if ($this->kirimEmail && StatusLamaranMail::adaTemplat($this->statusBaru)) {
            try {
                Mail::to($this->pelamar->email)->send(new StatusLamaranMail($this->pelamar->fresh('job'), trim($this->pesanPelamar) ?: null));
                $pesan .= ' Surel pemberitahuan terkirim ke pelamar.';
            } catch (\Throwable $e) {
                // Tahap tetap tersimpan; admin diberi tahu supaya bisa menghubungi manual.
                Log::error('Surel status lamaran gagal', ['lamaran' => $this->pelamar->id, 'galat' => $e->getMessage()]);
                $pesan .= ' Tetapi surel GAGAL terkirim — hubungi pelamar secara manual.';
            }
        }

        $this->pesanPelamar = '';
        $this->pelamar->refresh();
        $this->dispatch('swal-success', message: $pesan);
    }

    public function simpanCatatan(): void
    {
        $this->validate(['catatanInternal' => ['nullable', 'string', 'max:5000']]);
        $this->pelamar->update(['catatan_internal' => trim($this->catatanInternal) ?: null]);
        $this->dispatch('swal-success', message: 'Catatan internal disimpan.');
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.pelamar-kerja.pelamar-kerja-detail');
    }
}
