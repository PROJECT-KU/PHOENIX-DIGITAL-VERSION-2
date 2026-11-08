<?php

namespace App\Livewire\Pages\Admin\Asthana;

use App\Models\Asthana\Berita;
use App\Models\Asthana\Portofolio;
use App\Support\Asthana\GambarSitus;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Tulis / ubah portofolio atau berita situs Asthana.
 */
#[Layout('livewire.layout.templateindex')]
class AsthanaTulisanForm extends Component
{
    use WithFileUploads;

    private const BATAS_GALERI = 12;

    public string $jenis = 'portofolio';

    public ?int $tulisanId = null;

    public string $judul = '';

    public string $slug = '';

    public string $kategori = '';

    public string $ringkasan = '';

    public string $isi = '';

    public $sampul;

    public ?string $sampulLama = null;

    // Portofolio
    public string $klien = '';

    public $tahun = '';

    public string $tautan = '';

    public bool $unggulan = false;

    public bool $terbit = false;

    /** @var array<int, string> jalur gambar galeri yang sudah tersimpan */
    public array $galeri = [];

    /** @var array<int, mixed> unggahan galeri baru */
    public array $galeriBaru = [];

    /** Jalur galeri yang dibuang admin; berkasnya dihapus saat disimpan. */
    public array $galeriDibuang = [];

    // Berita: 'draf' | 'terbit' | 'jadwal'
    public string $keadaanBerita = 'draf';

    public string $jadwal = '';

    public function mount(string $jenis = 'portofolio', $id = null): void
    {
        abort_unless(in_array($jenis, ['portofolio', 'berita'], true), 404);
        $this->jenis = $jenis;
        $this->kategori = $jenis === 'portofolio' ? 'teknologi' : '';

        if (! $id) {
            return;
        }

        $t = $this->kelas()::findOrFail($id);
        $this->tulisanId = $t->id;
        $this->judul = $t->judul;
        $this->slug = $t->slug;
        $this->kategori = (string) ($t->kategori ?? '');
        $this->ringkasan = (string) ($t->ringkasan ?? '');
        $this->isi = (string) ($t->isi ?? '');
        $this->sampulLama = $t->sampul;

        if ($jenis === 'portofolio') {
            $this->klien = (string) ($t->klien ?? '');
            $this->tahun = $t->tahun ?? '';
            $this->tautan = (string) ($t->tautan ?? '');
            $this->unggulan = (bool) $t->unggulan;
            $this->terbit = (bool) $t->terbit;
            $this->galeri = array_values($t->galeri ?? []);
        } else {
            $keadaan = $t->keadaan();
            $this->keadaanBerita = $keadaan === 'terjadwal' ? 'jadwal' : $keadaan;
            $this->jadwal = $t->terbit_at ? $t->terbit_at->format('Y-m-d\TH:i') : '';
        }
    }

    private function kelas(): string
    {
        return $this->jenis === 'berita' ? Berita::class : Portofolio::class;
    }

    /** Slug mengikuti judul hanya untuk tulisan baru; alamat yang beredar dipertahankan. */
    public function updatedJudul(): void
    {
        if (! $this->tulisanId) {
            $this->slug = Str::slug($this->judul);
        }
    }

    protected function rules(): array
    {
        $tabel = $this->jenis === 'berita' ? 'asthana_berita' : 'asthana_portofolio';
        $aturan = [
            'judul' => 'required|string|max:191',
            'slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', "unique:{$tabel},slug".($this->tulisanId ? ",{$this->tulisanId}" : '')],
            'ringkasan' => 'nullable|string|max:300',
            'isi' => 'nullable|string',
            'sampul' => 'nullable|image|max:6144',
        ];

        if ($this->jenis === 'portofolio') {
            return $aturan + [
                'kategori' => 'required|in:'.implode(',', array_keys(Portofolio::KATEGORI)),
                'klien' => 'nullable|string|max:191',
                'tahun' => 'nullable|integer|min:1990|max:'.(now()->year + 1),
                'tautan' => 'nullable|url|max:255',
                'galeriBaru' => 'array|max:'.self::BATAS_GALERI,
                'galeriBaru.*' => 'image|max:6144',
            ];
        }

        return $aturan + [
            'kategori' => 'nullable|string|max:60',
            'keadaanBerita' => 'required|in:draf,terbit,jadwal',
            'jadwal' => 'required_if:keadaanBerita,jadwal|nullable|date',
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'Wajib diisi.',
            'slug.regex' => 'Hanya huruf kecil, angka, dan tanda hubung.',
            'slug.unique' => 'Alamat ini sudah dipakai tulisan lain.',
            'url' => 'Tulis alamat lengkap, diawali https://',
            'image' => 'Berkasnya harus gambar.',
            'sampul.max' => 'Gambar sampul maksimal 6 MB.',
            'galeriBaru.*.image' => 'Semua berkas galeri harus gambar.',
            'galeriBaru.*.max' => 'Tiap gambar galeri maksimal 6 MB.',
            'galeriBaru.max' => 'Galeri maksimal '.self::BATAS_GALERI.' gambar.',
            'tahun.integer' => 'Tahun berupa angka, mis. 2025.',
            'jadwal.required_if' => 'Pilih tanggal & jam tayang.',
        ];
    }

    public function buangGaleri(int $i): void
    {
        if (isset($this->galeri[$i])) {
            $this->galeriDibuang[] = $this->galeri[$i];
            unset($this->galeri[$i]);
            $this->galeri = array_values($this->galeri);
        }
    }

    public function buangGaleriBaru(int $i): void
    {
        unset($this->galeriBaru[$i]);
        $this->galeriBaru = array_values($this->galeriBaru);
    }

    public function simpan(): void
    {
        $this->isi = trim($this->isi);
        $this->validate();

        if ($this->jenis === 'portofolio' && count($this->galeri) + count($this->galeriBaru) > self::BATAS_GALERI) {
            $this->addError('galeriBaru', 'Galeri maksimal '.self::BATAS_GALERI.' gambar. Buang beberapa dulu.');

            return;
        }

        // Ringkasan kosong diambil dari kalimat awal isinya — kartu di situs
        // tidak boleh tampil tanpa teks.
        $ringkasan = trim($this->ringkasan) !== ''
            ? trim($this->ringkasan)
            : Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($this->isi)))), 180);

        $data = [
            'judul' => trim($this->judul),
            'slug' => $this->slug,
            'kategori' => $this->kategori !== '' ? trim($this->kategori) : null,
            'ringkasan' => $ringkasan !== '' ? $ringkasan : null,
            'isi' => $this->isi !== '' ? $this->isi : null,
        ];

        $sampulBaru = $this->sampul ? GambarSitus::simpan($this->sampul, $this->jenis, 1600) : null;
        if ($sampulBaru) {
            $data['sampul'] = $sampulBaru;
        }

        if ($this->jenis === 'portofolio') {
            $galeriBaru = array_map(fn ($b) => GambarSitus::simpan($b, 'portofolio/galeri', 1600), $this->galeriBaru);
            $data += [
                'klien' => $this->klien !== '' ? trim($this->klien) : null,
                'tahun' => $this->tahun !== '' ? (int) $this->tahun : null,
                'tautan' => $this->tautan !== '' ? $this->tautan : null,
                'unggulan' => $this->unggulan,
                'terbit' => $this->terbit,
                'galeri' => array_values(array_merge($this->galeri, $galeriBaru)),
            ];
        } else {
            // Berita yang sudah terbit dan disunting ulang mempertahankan
            // tanggal terbitnya — kalau tidak, ia melompat ke puncak daftar.
            $terbitLama = $this->tulisanId ? Berita::find($this->tulisanId)?->terbit_at : null;
            $data['terbit_at'] = match ($this->keadaanBerita) {
                'terbit' => ($terbitLama && $terbitLama->isPast()) ? $terbitLama : now(),
                'jadwal' => Carbon::parse($this->jadwal),
                default => null,
            };
        }

        $kelas = $this->kelas();

        if ($this->tulisanId) {
            $t = $kelas::findOrFail($this->tulisanId);
            $t->update($data);
        } else {
            if ($this->jenis === 'portofolio') {
                $data['urutan'] = 0;
            }
            $t = $kelas::create($data);
        }

        // Berkas lama dihapus SETELAH barisnya tersimpan.
        if ($sampulBaru && $this->sampulLama !== $sampulBaru) {
            $kelas::hapusGambar($this->sampulLama);
        }
        foreach ($this->galeriDibuang as $jalur) {
            $kelas::hapusGambar($jalur);
        }

        session()->flash('success', $this->pesanSimpan($t));
        $this->redirectRoute('admin.asthana.'.$this->jenis, navigate: true);
    }

    private function pesanSimpan($t): string
    {
        if ($this->jenis === 'portofolio') {
            return $t->terbit ? 'Portofolio disimpan dan tampil di situs.' : 'Portofolio disimpan sebagai draf.';
        }

        return match ($t->keadaan()) {
            'terbit' => 'Berita diterbitkan.',
            'terjadwal' => 'Berita dijadwalkan tayang '.$t->terbit_at->locale('id')->translatedFormat('d F Y, H:i').'.',
            default => 'Berita disimpan sebagai draf.',
        };
    }

    public function render()
    {
        return view('livewire.pages.admin.asthana.tulisan-form', [
            'judulJenis' => $this->jenis === 'berita' ? 'Berita' : 'Portofolio',
            'urlSampulLama' => $this->kelas()::urlGambar($this->sampulLama),
            'urlGaleri' => array_map(fn ($j) => Portofolio::urlGambar($j), $this->galeri),
            'situs' => config('asthana.situs_url'),
        ]);
    }
}
