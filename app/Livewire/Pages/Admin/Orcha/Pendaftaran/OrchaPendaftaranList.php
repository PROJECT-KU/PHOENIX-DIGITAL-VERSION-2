<?php

namespace App\Livewire\Pages\Admin\Orcha\Pendaftaran;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

class OrchaPendaftaranList extends Component
{
    use MemanggilOrcha;

    /**
     * Saringan paket.
     *
     * Ada karena manifes tour leader dibentuk dari daftar yang sedang tampil,
     * dan satu keberangkatan hampir selalu berarti satu paket. Sebelum ini
     * admin harus mengetik nama paketnya di kotak cari lalu berharap tidak ada
     * paket lain yang namanya mirip — dan manifes yang kelebihan satu rombongan
     * baru ketahuan di lapangan.
     */
    public string $filterPaket = '';

    /**
     * Saringan per ROMBONGAN, satu tingkat lebih halus daripada paket.
     *
     * Sejak satu paket boleh dijual untuk beberapa tanggal, "satu
     * keberangkatan = satu paket" tidak lagi benar. Manifes tour leader
     * dibentuk dari daftar yang sedang tampil — saringan paket membuatnya
     * memuat tiga rombongan sekaligus, dan kelebihannya baru ketahuan di
     * parkiran.
     */
    #[\Livewire\Attributes\Url(as: 'rombongan', except: '')]
    public string $filterKeberangkatan = '';

    public function updatedFilterPaket(): void
    {
        // Rombongan milik paket sebelumnya dibuang: menyaring "paket A +
        // rombongan milik paket B" memulangkan daftar kosong yang terbaca
        // sebagai data yang hilang.
        $this->filterKeberangkatan = '';
        $this->halaman = 1;
    }

    public function updatedFilterKeberangkatan(): void
    {
        $this->halaman = 1;
    }

    /**
     * Rombongan yang bisa disaring untuk paket yang sedang dipilih.
     *
     * Kosong saat belum ada paket terpilih: menawarkan seluruh rombongan
     * dari semua paket sekaligus menghasilkan daftar panjang yang tanggalnya
     * tidak bisa dibedakan satu sama lain.
     *
     * @return array<int, array<string, mixed>>
     */
    public function keberangkatanPaket(): array
    {
        if ($this->filterPaket === '') {
            return [];
        }

        $paket = collect($this->rujukan('paket_wisata'))
            ->firstWhere('id', (int) $this->filterPaket);

        return $paket['keberangkatan'] ?? [];
    }

    /**
     * Saringan "perlu ditagih": sudah bayar DP, tanggalnya sudah dekat.
     *
     * Ini daftar yang perlu dikejar orang. Pengingat otomatis sudah mengurus
     * yang bergerak setelah membaca suratnya; yang tersisa di sini justru yang
     * TIDAK bergerak — dan itu cuma bisa diselesaikan lewat telepon.
     *
     * Sebelum ada saringan ini, satu-satunya cara menemukannya adalah membuka
     * pendaftaran satu per satu dan menghitung tanggalnya di kepala.
     * Pekerjaannya cukup melelahkan sehingga tidak pernah benar-benar
     * dikerjakan, dan uangnya menguap tanpa ada yang menyadarinya.
     */
    public bool $perluDitagih = false;

    public function updatedPerluDitagih(): void
    {
        $this->halaman = 1;
    }

    /**
     * Tab yang sedang dibuka.
     *
     * Status dan "perlu ditagih" tetap jadi keadaan sebenarnya di balik
     * layar — tab hanya cara memilihnya. Disimpan terpisah supaya tab yang
     * sedang menyala tetap terlihat menyala walau keadaannya sama
     * ("Semua" dan status kosong adalah hal yang sama bagi peladen, tetapi
     * bukan bagi mata).
     */
    public string $tabAktif = 'semua';

    /**
     | Tab yang tersedia: label, ikon, warna.
     |
     | Dikelompokkan dua: yang pertama CARA MELIHAT daftar, yang kedua
     | DAFTAR PEKERJAAN. Keduanya tidak boleh berjajar begitu saja —
     | "Perlu ditagih" yang duduk di antara status tenggelam jadi salah satu
     | pilihan menonton, padahal ia satu-satunya yang menuntut tindakan hari
     | ini.
     */
    public const TAB = [
        'semua' => ['Semua', 'bi-grid-fill', '#7c3aed'],
        'baru' => ['Baru masuk', 'bi-bell-fill', '#16a34a'],
        'dihubungi' => ['Sudah dihubungi', 'bi-chat-dots-fill', '#0284c7'],
        'dp_masuk' => ['Baru bayar DP', 'bi-cash-coin', '#d97706'],
        'lunas' => ['Sudah lunas', 'bi-check-circle-fill', '#4f46e5'],
        'batal' => ['Batal', 'bi-x-circle-fill', '#e11d48'],
        'perlu_ditagih' => ['Perlu ditelepon', 'bi-telephone-outbound-fill', '#ea580c'],
    ];

    public const GRUP_TAB = [
        ['Keadaan pendaftaran', 'Sudah sampai mana orangnya',
            ['semua', 'baru', 'dihubungi', 'dp_masuk', 'lunas', 'batal']],
        /*
         | Keterangannya SENGAJA pendek.
         |
         | .pt-grup-ket milik partial bersama memotongnya dengan elipsis bila
         | kepanjangan, dan kolom kedua ini hanya 3fr. Kalimat yang terpotong
         | jadi "Yang uangnya bisa …" tidak memberi tahu apa pun — lebih buruk
         | daripada tidak ada keterangan sama sekali.
         */
        ['Perlu dikerjakan hari ini', 'Jangan didiamkan', ['perlu_ditagih']],
    ];

    /**
     * Pindah tab.
     *
     * Menerjemahkan satu pilihan jadi dua keadaan di balik layar. Admin
     * tidak perlu tahu bahwa "Perlu ditelepon" sebenarnya saringan lain
     * jenisnya daripada "Sudah lunas".
     */
    public function setTab(string $tab): void
    {
        $this->tabAktif = array_key_exists($tab, self::TAB) ? $tab : 'semua';

        $this->perluDitagih = $this->tabAktif === 'perlu_ditagih';
        $this->filterStatus = in_array($this->tabAktif, ['semua', 'perlu_ditagih'], true)
            ? ''
            : $this->tabAktif;

        $this->halaman = 1;
    }

    /**
     * Saringan paket ikut dikosongkan.
     *
     * Menimpa milik trait: halaman ini punya saringan ketiga, dan tombol yang
     * menyisakan satu saringan hidup justru membingungkan — daftarnya tetap
     * terpotong padahal admin merasa sudah membersihkan semuanya.
     */
    public function bersihkanSaringan(): void
    {
        $this->reset(['cari', 'filterStatus', 'filterPaket', 'filterKeberangkatan', 'perluDitagih']);
        // Tab ikut pulang ke "Semua": tombolnya menjanjikan membersihkan
        // semuanya, dan tab yang tetap menyala membuat daftarnya terlihat
        // masih tersaring padahal tidak.
        $this->tabAktif = 'semua';
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterStatus !== '' || $this->filterPaket !== ''
            || $this->filterKeberangkatan !== '' || $this->perluDitagih;
    }

    /** Saringan yang sedang berlaku — dipakai daftar maupun tautan manifes. */
    public function saringanTampil(): array
    {
        return array_filter([
            'cari' => $this->cari,
            'status' => $this->filterStatus,
            'paket_id' => $this->filterPaket,
            // Ikut ke tautan manifes: lembar yang dicetak harus memuat
            // rombongan yang sama dengan yang sedang dilihat di layar.
            'keberangkatan_id' => $this->filterKeberangkatan,
            // Dikirim hanya saat menyala. Mengirim 0 membuat Orcha membaca
            // parameternya ada, dan daftar biasa ikut tersaring.
            'perlu_ditagih' => $this->perluDitagih ? 1 : '',
        ], fn ($nilai) => $nilai !== '' && $nilai !== null);
    }

    public function ubahStatus(int $id, string $status): void
    {
        $this->kirimPerubahan("/pendaftaran/{$id}/status", ['status' => $status], 'Status pendaftaran diperbarui di Orcha.');
    }

    public function render()
    {
        $hasil = $this->muat('/pendaftaran', $this->parameterDaftar(array_filter([
            'paket_id' => $this->filterPaket,
            'keberangkatan_id' => $this->filterKeberangkatan,
            'perlu_ditagih' => $this->perluDitagih ? 1 : null,
        ], fn ($nilai) => $nilai !== null && $nilai !== '')));

        $meta = $hasil['meta'] ?? [];

        return view('livewire.pages.admin.orcha.pendaftaran.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $meta,
            'pilihanStatus' => $this->rujukan('status_pendaftaran'),
            'pilihanPaket' => $this->rujukan('paket_wisata'),
            'tabs' => self::TAB,
            'grupTab' => self::GRUP_TAB,
            /*
             | Angka di tab. Dibaca dengan ?? : Orcha yang belum dideploy
             | tidak mengirim kunci ini sama sekali, dan tab tanpa angka
             | masih berguna — sedangkan halaman yang mati karenanya tidak.
             */
            'jumlahTab' => $meta['jumlah_tab'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
