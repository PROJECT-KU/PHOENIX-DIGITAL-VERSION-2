<?php

namespace App\Livewire\Pages\Admin\Surat;

use App\Models\Surat\Surat;
use App\Models\Surat\SuratKlasifikasi;
use App\Models\Surat\SuratLampiran;
use App\Support\Surat\NomorSuratKeluar;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Catat / ubah satu surat.
 *
 * Satu komponen untuk dua keadaan lewat $isEdit, mengikuti pola FeatureForm
 * di repo ini.
 *
 * YANG WAJIB HANYA TIGA
 *
 * Arah, perihal, dan tanggal surat. Sisanya boleh menyusul. Itu keputusan
 * sadar, bukan kelalaian: surat yang datang pagi harus bisa masuk buku agenda
 * dalam setengah menit, dan borang yang menuntut sembilan kolom terisi akan
 * membuat staf menunda pencatatan sampai "nanti kalau sempat" — lalu suratnya
 * tidak pernah tercatat sama sekali. Yang tidak tercatat tidak bisa dicari,
 * dan itu kerugian yang jauh lebih besar daripada kolom yang kosong.
 */
#[Layout('livewire.layout.templateindex')]
#[Title('Catat Surat')]
class SuratForm extends Component
{
    use WithFileUploads;

    /** Satu tempat untuk aturan berkas, supaya kotak dan antrean tidak pernah berbeda. */
    private const ATURAN_BERKAS = 'file|max:20480|mimes:pdf,jpg,jpeg,png,webp,doc,docx';

    /**
     * Batas antrean sekali catat.
     *
     * Bukan batas arsip: pindaian boleh ditambah terus dari halaman detail
     * sesudah suratnya tersimpan. Batas ini menjaga satu permintaan simpan
     * tidak membawa ratusan berkas sekaligus di hosting bersama.
     */
    private const BATAS_ANTREAN = 20;

    public ?int $suratId = null;

    public bool $isEdit = false;

    /** @var array<string, mixed> */
    public array $f = [
        'arah' => 'masuk',
        'nomor_surat' => '',
        'tanggal_surat' => '',
        'tanggal_catat' => '',
        'klasifikasi_id' => '',
        'sifat' => 'biasa',
        'perihal' => '',
        'dari' => '',
        'kepada' => '',
        'status' => 'baru',
        'lokasi_fisik' => '',
        'ringkasan' => '',
    ];

    /**
     * Antrean pindaian yang sudah dipilih tetapi belum tersimpan.
     *
     * TERPISAH dari kotak berkasnya ($unggahan) dengan sengaja. Kotak
     * <input type="file"> selalu MENGGANTI isinya: memilih satu berkas lagi
     * membuang semua yang dipilih sebelumnya — tanpa pesan apa pun. Surat
     * yang dipindai lembar demi lembar justru selalu dipilih beberapa kali,
     * jadi antreannya disimpan sendiri dan kotaknya hanya jadi pintu masuk.
     */
    public array $berkas = [];

    /** Kotak berkasnya; isinya langsung dipindahkan ke antrean lalu dikosongkan. */
    public array $unggahan = [];

    /*
     | Nomor surat keluar dibuatkan sistem, tetapi tetap boleh diketik sendiri.
     |
     | Menyalakan centang ini membuat kotak nomornya ikut berubah sendiri
     | ketika tanggal atau klasifikasinya diganti; mematikannya menyerahkan
     | kotak itu sepenuhnya kepada admin. Harus ada dua-duanya: ada surat yang
     | nomornya sudah terlanjur dikeluarkan di kertas, dan arsip wajib
     | mengikuti kertasnya — bukan sebaliknya.
     |
     | Saat MENGUBAH surat lama, centangnya mati sejak awal. Nomor yang sudah
     | beredar di luar kantor tidak boleh berganti hanya karena seseorang
     | membuka layar ubah untuk membetulkan perihalnya.
     */
    public bool $nomorOtomatis = true;

    public function mount(?int $surat = null, ?string $arah = null): void
    {
        if ($surat) {
            $this->isEdit = true;
            $this->suratId = $surat;
            $m = Surat::findOrFail($surat);

            // Layar ubah adalah jalan masuk kedua ke isi surat; tanpa
            // penjaga di sini, surat rahasia tetap terbaca lengkap lewat
            // borangnya walau halaman detailnya sudah ditutup.
            abort_unless($m->bolehDilihat(auth()->user()), 403,
                'Surat ini bersifat rahasia. Mintalah izin "Lihat Surat Rahasia" kepada admin.');

            $this->f = [
                'arah' => $m->arah,
                'nomor_surat' => (string) $m->nomor_surat,
                'tanggal_surat' => $m->tanggal_surat?->toDateString() ?? '',
                'tanggal_catat' => $m->tanggal_catat?->toDateString() ?? '',
                'klasifikasi_id' => (string) ($m->klasifikasi_id ?? ''),
                'sifat' => $m->sifat,
                'perihal' => (string) $m->perihal,
                'dari' => (string) $m->dari,
                'kepada' => (string) $m->kepada,
                'status' => $m->status,
                'lokasi_fisik' => (string) $m->lokasi_fisik,
                'ringkasan' => (string) $m->ringkasan,
            ];

            $this->nomorOtomatis = false;

            return;
        }

        // Surat baru: arah datang dari tombol yang ditekan, dan hari ini
        // diisikan lebih dulu karena itulah jawaban yang benar hampir selalu.
        $this->f['arah'] = in_array($arah, ['masuk', 'keluar'], true) ? $arah : 'masuk';
        $this->f['tanggal_surat'] = now()->toDateString();
        $this->f['tanggal_catat'] = now()->toDateString();

        $this->susunNomor();
    }

    /*
     | Tanda tangan tanpa tipe pada $kunci disengaja.
     |
     | Livewire memanggil kait ini juga untuk pembaruan yang bukan per-kunci,
     | dan $kunci bisa datang null; menuliskan `string $kunci` membuat
     | seluruh layar jatuh pada ketikan pertama di kotak mana pun.
     */
    public function updatedF($nilai = null, $kunci = null): void
    {
        if (in_array($kunci, ['arah', 'tanggal_surat', 'klasifikasi_id'], true)) {
            $this->susunNomor();
        }
    }

    public function updatedNomorOtomatis(): void
    {
        $this->susunNomor();
    }

    /**
     * Isikan usulan nomor ke kotaknya — hanya bila admin memintanya.
     *
     * Surat MASUK tidak pernah diberi nomor usulan: nomornya ditulis oleh
     * pengirimnya. Kalau arahnya berpindah ke masuk sementara centang
     * otomatis masih menyala, nomor yang terlanjur dirakit justru dihapus,
     * supaya nomor milik kita tidak tercatat sebagai nomor surat orang lain.
     */
    public function susunNomor(): void
    {
        if (! $this->nomorOtomatis) {
            return;
        }

        if ($this->f['arah'] !== 'keluar') {
            $this->f['nomor_surat'] = '';

            return;
        }

        $this->f['nomor_surat'] = $this->usulanNomor()['nomor'];
    }

    /** Usulan nomor saat ini, dipakai kotak isian maupun keterangannya. */
    public function usulanNomor(): array
    {
        return NomorSuratKeluar::usulkan(
            $this->f['tanggal_surat'] ?: null,
            $this->f['klasifikasi_id'] ?: null,
            $this->suratId,
        );
    }

    /** Tombol "Hitung ulang" saat nomornya sedang diketik manual. */
    public function pakaiNomorUsulan(): void
    {
        if ($this->f['arah'] !== 'keluar') {
            return;
        }

        $this->f['nomor_surat'] = $this->usulanNomor()['nomor'];
        $this->resetValidation('f.nomor_surat');
    }

    protected function rules(): array
    {
        return [
            'f.arah' => 'required|in:masuk,keluar',
            'f.perihal' => 'required|string|max:255',
            'f.tanggal_surat' => 'required|date',
            'f.tanggal_catat' => 'nullable|date',
            'f.nomor_surat' => 'nullable|string|max:120',
            'f.klasifikasi_id' => 'nullable|exists:surat_klasifikasi,id',
            'f.sifat' => 'required|in:'.implode(',', array_keys(Surat::SIFAT)),
            'f.status' => 'required|in:'.implode(',', array_keys(Surat::STATUS)),
            'f.dari' => 'nullable|string|max:180',
            'f.kepada' => 'nullable|string|max:180',
            'f.lokasi_fisik' => 'nullable|string|max:180',
            'f.ringkasan' => 'nullable|string|max:5000',
            /*
             | 20 MB per berkas dan hanya PDF/gambar/Word.
             |
             | Pindaian surat satu lembar yang wajar berada di bawah 2 MB;
             | batas ini longgar supaya pindaian berwarna beresolusi tinggi
             | tetap lolos, tanpa membuka pintu untuk berkas yang bukan surat.
             */
            'berkas.*' => 'nullable|'.self::ATURAN_BERKAS,
        ];
    }

    protected function messages(): array
    {
        return [
            'f.perihal.required' => 'Perihal surat wajib diisi — ini yang dicari orang nanti.',
            'f.tanggal_surat.required' => 'Tanggal surat wajib diisi.',
            'f.tanggal_surat.date' => 'Tanggal surat belum berupa tanggal yang sah.',
            'berkas.*.max' => 'Berkas pindaian maksimal 20 MB per lembar.',
            'berkas.*.mimes' => 'Berkas pindaian harus PDF, gambar (JPG/PNG/WEBP), atau Word.',
            'unggahan.*.max' => 'Berkas pindaian maksimal 20 MB per lembar.',
            'unggahan.*.mimes' => 'Berkas pindaian harus PDF, gambar (JPG/PNG/WEBP), atau Word.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'f.arah' => 'jenis surat',
            'f.perihal' => 'perihal',
            'f.tanggal_surat' => 'tanggal surat',
            'f.tanggal_catat' => 'tanggal diterima/dikirim',
            'f.nomor_surat' => 'nomor surat',
            'unggahan.*' => 'berkas pindaian',
            'f.klasifikasi_id' => 'klasifikasi',
            'f.lokasi_fisik' => 'tempat berkas',
        ];
    }

    /** Label kolom pihak ikut arah suratnya, supaya tidak perlu ditebak. */
    public function labelPihak(): string
    {
        return $this->f['arah'] === 'keluar' ? 'Kepada' : 'Dari';
    }

    /**
     * Pindahkan berkas yang baru dipilih ke antrean.
     *
     * Ditambahkan, bukan menggantikan: admin yang memindai sepuluh lembar
     * akan memilihnya beberapa kali, dan pilihan kedua tidak boleh membuang
     * pilihan pertama.
     */
    public function updatedUnggahan(): void
    {
        $this->validate(['unggahan.*' => 'nullable|'.self::ATURAN_BERKAS], $this->messages());

        foreach ($this->unggahan as $berkas) {
            if (! $berkas) {
                continue;
            }

            if (count($this->berkas) >= self::BATAS_ANTREAN) {
                $this->addError('unggahan', 'Sekali catat maksimal '.self::BATAS_ANTREAN
                    .' berkas. Simpan dulu suratnya, sisanya bisa ditambahkan dari halaman suratnya.');
                break;
            }

            $this->berkas[] = $berkas;
        }

        $this->unggahan = [];
    }

    /** Keluarkan satu berkas dari antrean sebelum suratnya disimpan. */
    public function hapusAntrean(int $urutan): void
    {
        unset($this->berkas[$urutan]);

        // Disusun ulang nomornya: celah di tengah larik membuat tombol
        // hapus berikutnya menunjuk berkas yang salah.
        $this->berkas = array_values($this->berkas);
    }

    public function save()
    {
        $this->validate();

        /*
         | Nomor dirakit ULANG tepat sebelum disimpan, bukan dipakai apa
         | adanya dari layar.
         |
         | Borang bisa dibiarkan terbuka setengah jam, dan dalam waktu itu
         | admin lain sudah mengeluarkan dua surat. Nomor yang dirakit saat
         | layar dibuka akan kembar dengan punya mereka — dan surat kembar
         | nomor baru ketahuan berbulan-bulan kemudian, saat seseorang
         | mencarinya dan menemukan dua.
         */
        if ($this->nomorOtomatis) {
            $this->susunNomor();
        }

        $nomor = trim((string) $this->f['nomor_surat']);

        if ($this->f['arah'] === 'keluar' && $nomor !== ''
            && NomorSuratKeluar::sudahDipakai($nomor, $this->suratId)) {
            $this->addError('f.nomor_surat',
                'Nomor '.$nomor.' sudah dipakai surat keluar lain. Ganti nomornya, '
                .'atau centang "Buatkan nomornya otomatis" supaya dihitungkan.');

            return null;
        }

        $data = [
            'arah' => $this->f['arah'],
            'nomor_surat' => $nomor ?: null,
            'tanggal_surat' => $this->f['tanggal_surat'],
            'tanggal_catat' => $this->f['tanggal_catat'] ?: null,
            'klasifikasi_id' => $this->f['klasifikasi_id'] ?: null,
            'sifat' => $this->f['sifat'],
            'perihal' => trim((string) $this->f['perihal']),
            'dari' => trim((string) $this->f['dari']) ?: null,
            'kepada' => trim((string) $this->f['kepada']) ?: null,
            'status' => $this->f['status'],
            'lokasi_fisik' => trim((string) $this->f['lokasi_fisik']) ?: null,
            'ringkasan' => trim((string) $this->f['ringkasan']) ?: null,
        ];

        $surat = $this->isEdit ? Surat::findOrFail($this->suratId) : new Surat;

        abort_unless(! $this->isEdit || $surat->bolehDilihat(auth()->user()), 403,
            'Surat ini bersifat rahasia. Mintalah izin "Lihat Surat Rahasia" kepada admin.');
        $surat->fill($data);

        /*
         | Tanggal boleh musnah dihitung ulang setiap kali disimpan.
         |
         | Dihitung DI SINI, bukan saat dibaca: retensi di master klasifikasi
         | bisa diubah kemudian, dan arsip yang sudah berjalan tidak boleh
         | berubah masa simpannya diam-diam karena orang lain menyunting
         | master data. Kalau retensinya belum ditentukan, nilainya tetap null
         | — "belum ditentukan" tidak pernah jadi tanggal karangan.
         */
        $surat->save();
        $surat->load('klasifikasi');
        $musnah = $surat->hitungMusnah();

        if ($surat->musnah_pada?->toDateString() !== $musnah) {
            $surat->musnah_pada = $musnah;
            $surat->save();
        }

        $this->simpanBerkas($surat);

        session()->flash('success', $this->isEdit
            ? 'Surat '.$surat->nomor_agenda.' diperbarui.'
            : 'Surat tercatat dengan nomor agenda '.$surat->nomor_agenda.'.');

        return $this->redirect(route('admin.surat.detail', $surat), navigate: true);
    }

    /**
     * Simpan pindaian ke disk PRIVAT.
     *
     * Bukan disk publik: pindaian surat memuat nama, alamat, dan nomor
     * telepon orang, dan disk publik berarti siapa pun yang tahu URL-nya bisa
     * mengunduhnya selamanya tanpa login — nama berkas yang acak tidak
     * menolong begitu URL-nya tersalin sekali.
     */
    private function simpanBerkas(Surat $surat): void
    {
        foreach ($this->berkas as $berkas) {
            if (! $berkas) {
                continue;
            }

            $jalur = $berkas->store('surat', 'local');

            SuratLampiran::create([
                'surat_id' => $surat->id,
                'nama' => $berkas->getClientOriginalName(),
                'jalur' => $jalur,
                'ukuran' => $berkas->getSize(),
                'jenis' => $berkas->getMimeType(),
                'diunggah_oleh' => auth()->id(),
            ]);
        }

        $this->berkas = [];
        $this->unggahan = [];
    }

    public function render()
    {
        return view('livewire.pages.admin.surat.borang', [
            'daftarKlasifikasi' => SuratKlasifikasi::aktif()->tersusun()->get(),
            'nomorNanti' => $this->isEdit
                ? null
                : Surat::nomorAgendaBaru((string) $this->f['arah']),
            'potonganNomor' => $this->f['arah'] === 'keluar'
                ? NomorSuratKeluar::penjelasan(trim((string) $this->f['nomor_surat'])
                    ?: $this->usulanNomor()['nomor'])
                : [],
        ]);
    }
}
