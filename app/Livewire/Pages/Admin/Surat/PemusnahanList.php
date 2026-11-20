<?php

namespace App\Livewire\Pages\Admin\Surat;

use App\Models\Surat\Surat;
use App\Models\Surat\SuratPemusnahan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Penyusutan arsip: dari "masa simpan habis" ke berita acara pemusnahan.
 *
 * Inilah yang menutup lingkaran retensi. Tanpa layar ini, tanda "boleh
 * dimusnahkan" hanya tumbuh — daftarnya makin panjang tiap bulan sampai tidak
 * ada lagi yang membacanya, dan peringatan yang tidak pernah bisa ditutup
 * sama saja dengan tidak ada peringatan.
 *
 * YANG DIMUSNAHKAN ADALAH KERTASNYA, BUKAN CATATANNYA.
 *
 * Suratnya tetap ada di buku agenda, berganti status jadi 'dimusnahkan' dan
 * menunjuk ke berita acaranya. Justru itulah gunanya: pertanyaan "ke mana
 * surat nomor 42?" harus punya jawaban bertahun-tahun kemudian, dan
 * jawabannya adalah nomor berita acara beserta tanggalnya.
 */
#[Layout('livewire.layout.templateindex')]
#[Title('Penyusutan Arsip')]
class PemusnahanList extends Component
{
    use WithPagination;

    /** @var array<int> id surat yang dicentang untuk dimusnahkan */
    public array $terpilih = [];

    public string $tanggal = '';

    public string $penanggungJawab = '';

    public string $catatan = '';

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
        $this->penanggungJawab = (string) (auth()->user()?->name ?? '');
    }

    protected function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'penanggungJawab' => 'nullable|string|max:180',
            'catatan' => 'nullable|string|max:2000',
        ];
    }

    protected function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal pemusnahan wajib diisi.',
        ];
    }

    /**
     * Surat yang masa simpannya habis dan BELUM masuk berita acara mana pun.
     *
     * Yang berstatus 'diarsipkan' ikut dihitung di sini — berbeda dari angka
     * "perlu diputuskan" di dasbor. Di dasbor, surat yang sudah diarsipkan
     * memang sedang menunggu di lemari sampai masa simpannya lewat, jadi
     * menandainya terus membuat angkanya tidak pernah bisa dinolkan. Di
     * layar INI, justru surat itulah calon utamanya.
     */
    private function calon()
    {
        return Surat::terlihatOleh(auth()->user())
            ->whereNull('pemusnahan_id')
            ->where('status', '!=', 'dimusnahkan')
            ->whereNotNull('musnah_pada')
            ->whereDate('musnah_pada', '<=', now()->toDateString())
            ->with('klasifikasi')
            ->orderBy('musnah_pada')
            ->orderBy('id');
    }

    public function pilihSemua(): void
    {
        $this->terpilih = $this->calon()->pluck('id')->map(fn ($i) => (int) $i)->all();
    }

    public function kosongkanPilihan(): void
    {
        $this->terpilih = [];
    }

    public function buatBeritaAcara(): void
    {
        abort_unless(auth()->user()?->hasPermission('kelola_pemusnahan_surat'), 403);

        $this->validate();

        /*
         | Daftarnya dibaca ULANG dari basis data, bukan dipercaya dari layar.
         |
         | Centang datang dari peramban, dan yang dimusnahkan harus benar-benar
         | surat yang memang sudah habis masa simpannya dan boleh dilihat orang
         | ini. Tanpa pembacaan ulang, satu id yang disisipkan dari luar bisa
         | menyeret surat yang masih berlaku — dan status 'dimusnahkan' adalah
         | pernyataan tentang dunia nyata, bukan sekadar nilai kolom.
         */
        $pilihan = array_map('intval', $this->terpilih);

        $surat = $this->calon()->whereIn('id', $pilihan)->get();

        if ($surat->isEmpty()) {
            $this->addError('terpilih', 'Pilih dulu surat mana saja yang akan dimusnahkan.');

            return;
        }

        $ba = DB::transaction(function () use ($surat) {
            $ba = SuratPemusnahan::create([
                'tanggal' => $this->tanggal,
                'penanggung_jawab' => trim($this->penanggungJawab) ?: null,
                'catatan' => trim($this->catatan) ?: null,
            ]);

            foreach ($surat as $s) {
                $s->forceFill([
                    'pemusnahan_id' => $ba->id,
                    'status' => 'dimusnahkan',
                ])->saveQuietly();

                $s->catatJejak('dimusnahkan', 'Berkas kertasnya dimusnahkan lewat berita acara '.$ba->nomor.'.');
            }

            return $ba;
        });

        $this->terpilih = [];
        $this->catatan = '';
        $this->resetPage();

        $this->dispatch('swal-success', message: 'Berita acara '.$ba->nomor.' dibuat untuk '
            .$surat->count().' surat. Cetak lembarnya lalu simpan bersama arsip.');
    }

    public function render()
    {
        $u = auth()->user();

        return view('livewire.pages.admin.surat.pemusnahan', [
            'calon' => $this->calon()->paginate(25),
            'jumlahCalon' => $this->calon()->count(),
            'beritaAcara' => SuratPemusnahan::withCount('surat')
                ->with('pembuat')->orderByDesc('id')->limit(12)->get(),
            'boleh' => (bool) $u?->hasPermission('kelola_pemusnahan_surat'),
        ]);
    }
}
