<?php

namespace App\Livewire\Pages\Admin\Asthana;

use App\Models\Asthana\Angka;
use App\Models\Asthana\Klien;
use App\Models\Asthana\Testimoni;
use App\Models\Asthana\Tim;
use App\Support\Asthana\GambarSitus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Empat daftar pendek situs Asthana: klien, testimoni, tim, dan angka.
 *
 * Isiannya sedikit, jadi tambah/ubahnya lewat jendela di halaman yang sama
 * (pola OrchaEtalaseList). Urutan tampil di situs diatur dengan tombol naik
 * turun — daftarnya pendek, seret-lepas tidak sebanding repotnya.
 */
#[Layout('livewire.layout.templateindex')]
class AsthanaDaftarList extends Component
{
    use WithFileUploads;

    /**
     * judul, ikon, warna, model, kolom gambar (null = tanpa gambar), keterangan.
     */
    public const JENIS = [
        'klien' => ['Klien & Mitra', 'bi-buildings', '#2455e6', Klien::class, 'logo', 'Logo yang berjalan di beranda situs.'],
        'testimoni' => ['Testimoni', 'bi-chat-quote', '#7b5cff', Testimoni::class, 'foto', 'Kutipan klien di beranda situs.'],
        'tim' => ['Tim Manajemen', 'bi-people', '#0ea5c6', Tim::class, 'foto', 'Pimpinan di halaman Tentang Kami.'],
        'angka' => ['Angka Pencapaian', 'bi-bar-chart', '#ff8a3d', Angka::class, null, 'Pita angka di bawah hero beranda.'],
    ];

    public string $jenis = 'klien';

    public bool $formTerbuka = false;

    public ?int $sedangDiubah = null;

    // Isian gabungan; tiap jenis memakai yang relevan saja.
    public string $nama = '';

    public string $jabatan = '';

    public string $perusahaan = '';

    public string $isi = '';

    public int $bintang = 5;

    public string $tautan = '';

    public string $label = '';

    public $nilai = '';

    public string $akhiran = '';

    public string $ikon = '';

    public bool $tampil = true;

    public $gambar;

    public ?string $gambarLama = null;

    public function mount(string $jenis = 'klien'): void
    {
        abort_unless(isset(self::JENIS[$jenis]), 404);
        $this->jenis = $jenis;
    }

    private function model(): string
    {
        return self::JENIS[$this->jenis][3];
    }

    private function kolomGambar(): ?string
    {
        return self::JENIS[$this->jenis][4];
    }

    protected function rules(): array
    {
        $gambar = ['gambar' => 'nullable|image|max:4096'];

        return match ($this->jenis) {
            'klien' => ['nama' => 'required|string|max:191', 'tautan' => 'nullable|url|max:255'] + $gambar,
            'testimoni' => [
                'nama' => 'required|string|max:191',
                'jabatan' => 'nullable|string|max:191',
                'perusahaan' => 'nullable|string|max:191',
                'isi' => 'required|string|max:1000',
                'bintang' => 'required|integer|min:1|max:5',
            ] + $gambar,
            'tim' => [
                'nama' => 'required|string|max:191',
                'jabatan' => 'required|string|max:191',
                'tautan' => 'nullable|url|max:255',
            ] + $gambar,
            'angka' => [
                'label' => 'required|string|max:60',
                'nilai' => 'required|integer|min:0|max:100000000',
                'akhiran' => 'nullable|string|max:10',
                'ikon' => ['nullable', 'string', 'max:40', 'regex:/^bi-[a-z0-9-]+$/'],
            ],
        };
    }

    protected function messages(): array
    {
        return [
            'required' => 'Wajib diisi.',
            'url' => 'Tulis alamat lengkap, diawali https://',
            'image' => 'Berkasnya harus gambar.',
            'gambar.max' => 'Gambar maksimal 4 MB.',
            'nilai.integer' => 'Isi dengan angka bulat, tanpa titik.',
            'ikon.regex' => 'Nama ikon diawali "bi-", mis. bi-trophy.',
        ];
    }

    public function tambah(): void
    {
        $this->kosongkan();
        $this->formTerbuka = true;
    }

    public function ubah(int $id): void
    {
        $this->kosongkan();
        $baris = $this->model()::findOrFail($id);

        $this->sedangDiubah = $baris->id;
        $this->nama = (string) ($baris->nama ?? '');
        $this->jabatan = (string) ($baris->jabatan ?? '');
        $this->perusahaan = (string) ($baris->perusahaan ?? '');
        $this->isi = (string) ($baris->isi ?? '');
        $this->bintang = (int) ($baris->bintang ?? 5);
        $this->tautan = (string) ($this->jenis === 'tim' ? ($baris->linkedin ?? '') : ($baris->tautan ?? ''));
        $this->label = (string) ($baris->label ?? '');
        $this->nilai = $baris->nilai ?? '';
        $this->akhiran = (string) ($baris->akhiran ?? '');
        $this->ikon = (string) ($baris->ikon ?? '');
        $this->tampil = (bool) $baris->tampil;
        $this->gambarLama = $this->kolomGambar() ? $baris->{$this->kolomGambar()} : null;

        $this->formTerbuka = true;
    }

    public function simpan(): void
    {
        $this->validate();

        $data = match ($this->jenis) {
            'klien' => ['nama' => $this->nama, 'tautan' => $this->tautan ?: null],
            'testimoni' => [
                'nama' => $this->nama, 'jabatan' => $this->jabatan ?: null,
                'perusahaan' => $this->perusahaan ?: null, 'isi' => $this->isi, 'bintang' => $this->bintang,
            ],
            'tim' => ['nama' => $this->nama, 'jabatan' => $this->jabatan, 'linkedin' => $this->tautan ?: null],
            'angka' => [
                'label' => $this->label, 'nilai' => (int) $this->nilai,
                'akhiran' => $this->akhiran ?: null, 'ikon' => $this->ikon ?: null,
            ],
        };
        $data['tampil'] = $this->tampil;

        $kolom = $this->kolomGambar();
        if ($kolom && $this->gambar) {
            $data[$kolom] = GambarSitus::simpan($this->gambar, $this->jenis, $this->jenis === 'klien' ? 600 : 900);
        }

        $model = $this->model();

        if ($this->sedangDiubah) {
            $baris = $model::findOrFail($this->sedangDiubah);
            $lama = $kolom ? $baris->{$kolom} : null;
            $baris->update($data);
            // Gambar lama dihapus SETELAH barisnya tersimpan: gagal simpan
            // tidak boleh meninggalkan baris yang menunjuk berkas terhapus.
            if ($kolom && isset($data[$kolom]) && $lama !== $data[$kolom]) {
                $model::hapusGambar($lama);
            }
            $pesan = 'Perubahan disimpan.';
        } else {
            // Butir baru masuk di urutan paling akhir.
            $data['urutan'] = (int) $model::max('urutan') + 1;
            $model::create($data);
            $pesan = 'Ditambahkan. Situs memperlihatkannya pada kunjungan berikutnya.';
        }

        $this->tutup();
        $this->dispatch('swal-success', message: $pesan);
    }

    public function alihTampil(int $id): void
    {
        $baris = $this->model()::findOrFail($id);
        $baris->update(['tampil' => ! $baris->tampil]);
    }

    /** Tukar urutan dengan tetangga di atas (-1) atau di bawah (+1). */
    public function geser(int $id, int $arah): void
    {
        $daftar = $this->model()::query()->orderBy('urutan')->orderBy('id')->get();
        $i = $daftar->search(fn (Model $m) => $m->id === $id);
        $j = $i === false ? false : $i + ($arah < 0 ? -1 : 1);

        if ($i === false || ! isset($daftar[$j])) {
            return;
        }

        // Diberi nomor ulang 1..n dulu: data lama bisa punya urutan kembar
        // (semuanya 0), dan menukar dua angka yang sama tidak menggeser apa-apa.
        DB::transaction(function () use ($daftar, $i, $j) {
            $urut = $daftar->values()->all();
            [$urut[$i], $urut[$j]] = [$urut[$j], $urut[$i]];
            foreach ($urut as $n => $m) {
                if ($m->urutan !== $n + 1) {
                    $m->update(['urutan' => $n + 1]);
                }
            }
        });
    }

    public function hapus(int $id): void
    {
        $baris = $this->model()::findOrFail($id);
        $gambar = $this->kolomGambar() ? $baris->{$this->kolomGambar()} : null;
        $baris->delete();
        $this->model()::hapusGambar($gambar);

        $this->dispatch('swal-success', message: 'Dihapus dari situs.');
    }

    public function tutup(): void
    {
        $this->formTerbuka = false;
        $this->kosongkan();
    }

    private function kosongkan(): void
    {
        $this->reset([
            'sedangDiubah', 'nama', 'jabatan', 'perusahaan', 'isi', 'tautan',
            'label', 'nilai', 'akhiran', 'ikon', 'gambar', 'gambarLama',
        ]);
        $this->bintang = 5;
        $this->tampil = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.pages.admin.asthana.daftar-list', [
            'daftar' => $this->model()::query()->orderBy('urutan')->orderBy('id')->get(),
            'info' => self::JENIS[$this->jenis],
            'jumlahPerJenis' => collect(self::JENIS)->map(fn ($j) => $j[3]::count()),
        ]);
    }
}
