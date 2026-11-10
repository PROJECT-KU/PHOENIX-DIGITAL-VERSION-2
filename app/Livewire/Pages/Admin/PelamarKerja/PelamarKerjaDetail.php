<?php

namespace App\Livewire\Pages\Admin\PelamarKerja;

use App\Mail\StatusLamaranMail;
use App\Models\JejakLamaran;
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

    /**
     * Catatan BARU yang sedang ditulis.
     *
     * Dulu satu kotak berisi seluruh catatan tim, dan menyimpannya menimpa
     * tulisan orang sebelumnya tanpa jejak. Sekarang tiap catatan jadi
     * barisnya sendiri, bertanda penulis dan waktu.
     */
    public string $catatanBaru = '';

    /**
     * Berkas yang sedang dibaca: 'cv', 'surat', atau 'lampiran'.
     *
     * Satu jendela, tiga berkas. Dulu ketiganya kartu bertumpuk masing-masing
     * setinggi A4 — membaca CV lalu surat lamarannya berarti menggulung tiga
     * layar penuh, dan berkas pendukung yang kosong tetap memakan satu kartu.
     */
    public string $berkasAktif = 'cv';

    public function bukaBerkas(string $kunci): void
    {
        $this->berkasAktif = in_array($kunci, ['cv', 'surat', 'lampiran'], true) ? $kunci : 'cv';
    }

    public function mount($id)
    {
        $this->pelamar = JobApplication::with('job')->findOrFail($id);
        $this->statusBaru = $this->pelamar->status ?: 'baru';

        foreach (['cv' => 'cv_path', 'surat' => 'cover_letter_path', 'lampiran' => 'lampiran_path'] as $kunci => $kolom) {
            if ($this->pelamar->{$kolom}) {
                $this->berkasAktif = $kunci;
                break;
            }
        }
    }

    /**
     * Boleh memutuskan, bukan sekadar membaca.
     *
     * Dipakai layar DAN kedua aksi di bawah. Menyembunyikan tombolnya saja
     * bukan penjagaan: aksi Livewire bisa dipanggil langsung dari peramban,
     * dan yang dipicu di sini keluar sampai ke kotak surat orang lain.
     */
    public function getBisaUbahProperty(): bool
    {
        return (bool) auth()->user()?->hasPermission('edit_pelamar');
    }

    private function wajibBolehUbah(): void
    {
        abort_unless($this->bisaUbah, 403, 'Anda tidak berhak mengubah data pelamar.');
    }

    public function simpanStatus(): void
    {
        $this->wajibBolehUbah();

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

        $dari = $this->pelamar->status;
        $pesan = trim($this->pesanPelamar) ?: null;

        $this->pelamar->update(['status' => $this->statusBaru, 'status_diubah_at' => now()]);

        JejakLamaran::catat($this->pelamar, 'tahap', [
            'dari_status' => $dari,
            'ke_status' => $this->statusBaru,
            'isi' => $pesan,
        ]);

        $kabar = 'Tahap seleksi diperbarui.';

        if ($this->kirimEmail && StatusLamaranMail::adaTemplat($this->statusBaru)) {
            $this->kirimSurel($pesan);
            $kabar .= ' Surel pemberitahuan sedang dikirim — hasilnya muncul di Riwayat.';
        }

        $this->pesanPelamar = '';
        $this->pelamar->refresh();
        $this->dispatch('swal-success', message: $kabar);
    }

    /**
     * Surel dikirim SESUDAH jawaban sampai ke peramban.
     *
     * Dulu dikirim di tengah aksi: admin menatap tombol "Menyimpan…" selama
     * SMTP bekerja, dan di hosting yang lambat itu terasa seperti layar
     * menggantung. Dipakai afterResponse, bukan antrean, karena pekerja
     * antrean tidak berjalan di hosting ini — lamaran yang diantrekan tidak
     * akan pernah terkirim, dan tidak ada yang memberi tahu.
     *
     * Hasilnya dicatat ke jejak, bukan ke pemberitahuan sekejap: pada saat
     * surelnya selesai dikirim, jawaban untuk admin sudah telanjur pergi.
     */
    private function kirimSurel(?string $pesan): void
    {
        $lamaran = $this->pelamar->fresh('job');

        dispatch(function () use ($lamaran, $pesan) {
            try {
                Mail::to($lamaran->email)->send(new StatusLamaranMail($lamaran, $pesan));

                JejakLamaran::create([
                    'job_application_id' => $lamaran->id,
                    'jenis' => 'surel',
                    'ke_status' => $lamaran->status,
                    'isi' => 'Surel pemberitahuan terkirim ke '.$lamaran->email.'.',
                ]);
            } catch (\Throwable $e) {
                Log::error('Surel status lamaran gagal', ['lamaran' => $lamaran->id, 'galat' => $e->getMessage()]);

                JejakLamaran::create([
                    'job_application_id' => $lamaran->id,
                    'jenis' => 'surel',
                    'ke_status' => $lamaran->status,
                    'isi' => 'Surel GAGAL terkirim ke '.$lamaran->email.' — hubungi pelamar secara manual.',
                ]);
            }
        })->afterResponse();
    }

    public function simpanCatatan(): void
    {
        $this->wajibBolehUbah();

        $this->validate(
            ['catatanBaru' => ['required', 'string', 'max:5000']],
            [
                'catatanBaru.required' => 'Tulis dulu catatannya.',
                'catatanBaru.max' => 'Catatan maksimal 5000 karakter.',
            ]
        );

        JejakLamaran::catat($this->pelamar, 'catatan', ['isi' => trim($this->catatanBaru)]);

        $this->catatanBaru = '';
        $this->dispatch('swal-success', message: 'Catatan disimpan.');
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.pelamar-kerja.pelamar-kerja-detail', [
            'jejak' => $this->pelamar->jejak()->get(),
            // Lamaran lain dari orang yang sama — dibatasi lima: yang perlu
            // diketahui adalah "pernah melamar sebelumnya", bukan daftar
            // lengkapnya.
            'lamaranLain' => $this->pelamar->lamaranLain()->limit(5)->get(),
        ]);
    }
}
