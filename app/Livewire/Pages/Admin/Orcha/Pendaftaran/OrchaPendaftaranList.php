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
        /*
         | Centangan ikut dikosongkan.
         |
         | Pilihan yang bertahan melewati pembersihan saringan adalah pilihan
         | atas baris yang tidak lagi terlihat — dan tombol "Kirim ke 7 orang"
         | yang muncul di atas daftar berisi 200 baris tidak bisa diperiksa
         | siapa pun sebelum ditekan.
         */
        $this->dipilih = [];
        $this->bukaPesanMassal = false;
        $this->bukaGabung = false;
        $this->keadaanMassal = '';
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
            /*
             | Urutan ikut ke berkas yang diunduh.
             |
             | Lembar yang urutannya berbeda dari layar memaksa yang
             | membacanya mencari ulang baris yang baru saja ia lihat — dan
             | pada manifes tour leader, urutan itu yang dipakai memanggil
             | nama di parkiran.
             */
            'urut' => $this->urut === 'terbaru' ? '' : $this->urut,
        ], fn ($nilai) => $nilai !== '' && $nilai !== null);
    }

    /*
     |--------------------------------------------------------------------------
     | Urutan daftar
     |--------------------------------------------------------------------------
     |
     | Sebelum ini urutannya terkunci: terbaru mendaftar lebih dulu, kecuali
     | tab "Perlu ditelepon" yang memakai tanggal berangkat. Pertanyaan harian
     | yang tidak bisa dijawab daftar semacam itu: siapa yang berangkatnya
     | paling dekat tetapi uangnya paling sedikit masuk.
     |
     | Sisa tagihan TIDAK ada di daftar pilihan, dan itu bukan kelupaan.
     | Totalnya dihitung Orcha di PHP karena ikut memperhitungkan campuran tipe
     | kamar dan potongan rujukan; mengurutnya di basis data menuntut hitungan
     | itu ditulis ulang sebagai SQL, dan dua hitungan untuk satu angka uang
     | cepat atau lambat berbeda jawabannya. Yang ditawarkan sebagai gantinya
     | "uang masuk paling sedikit", yang menjawab pertanyaan yang sama untuk
     | rombongan berharga sama.
     */

    /**
     * Pilihan urutan: kunci Orcha => [label, ikon].
     *
     * Kuncinya SAMA PERSIS dengan PendaftaranController::URUT di Orcha. Kunci
     * yang tidak dikenal di sana diabaikan tanpa galat, jadi salah ketik di
     * sini menghasilkan daftar yang urutannya tidak berubah sama sekali —
     * tanpa satu pun tanda bahwa pilihannya tidak sampai.
     */
    public const URUT = [
        'terbaru' => ['Terbaru mendaftar', 'bi-clock-history'],
        'terlama' => ['Paling lama mendaftar', 'bi-hourglass-bottom'],
        'berangkat_dekat' => ['Berangkat paling dekat', 'bi-airplane'],
        'berangkat_jauh' => ['Berangkat paling jauh', 'bi-airplane-engines'],
        'nama' => ['Nama A → Z', 'bi-sort-alpha-down'],
        'nama_z' => ['Nama Z → A', 'bi-sort-alpha-up'],
        'peserta_banyak' => ['Peserta terbanyak', 'bi-people-fill'],
        'peserta_sedikit' => ['Peserta tersedikit', 'bi-person'],
        'uang_masuk_kecil' => ['Uang masuk paling sedikit', 'bi-cash-coin'],
        'uang_masuk_besar' => ['Uang masuk paling banyak', 'bi-cash-stack'],
        /*
         | Sisa tagihan, akhirnya bisa diurut.
         |
         | Dulu tidak ada di daftar ini karena totalnya dihitung PHP, dan
         | menulis ulang hitungan itu sebagai SQL berarti dua hitungan untuk
         | satu angka uang. Sekarang angkanya DISALIN ke kolom total_tagihan
         | oleh kait saved pada modelnya di Orcha — aturannya tetap satu, yang
         | ada di SQL cuma pengurangan uang yang sudah diterima.
         */
        'sisa_besar' => ['Sisa tagihan terbesar', 'bi-exclamation-diamond'],
        'sisa_kecil' => ['Sisa tagihan terkecil', 'bi-check2-circle'],
    ];

    #[\Livewire\Attributes\Url(as: 'urut', except: 'terbaru')]
    public string $urut = 'terbaru';

    public function updatedUrut(): void
    {
        // Kunci yang tidak dikenal dipulangkan ke bawaannya di SINI, bukan
        // dibiarkan sampai ke Orcha: di sana ia diabaikan tanpa galat, dan
        // pemilih yang menampilkan pilihan yang tidak berlaku adalah
        // keterangan yang salah.
        if (! array_key_exists($this->urut, self::URUT)) {
            $this->urut = 'terbaru';
        }

        $this->halaman = 1;
    }

    /**
     * Mengurut dari kepala kolom, bergantian naik-turun.
     *
     * Dipakai berdampingan dengan pemilih urutan, bukan menggantinya. Kepala
     * kolom yang bisa diklik tidak terlihat bisa diklik sampai dicoba, dan
     * admin yang belum pernah mencobanya tidak akan menemukan urutan sama
     * sekali; sebaliknya, pemilih bertulisan sepuluh baris lebih lambat
     * dipakai orang yang sudah tahu ia mau menekan "Berangkat".
     */
    public function urutkanKolom(string $naik, string $turun): void
    {
        $this->urut = $this->urut === $naik ? $turun : $naik;
        $this->halaman = 1;
    }

    /*
     |--------------------------------------------------------------------------
     | Pilih beberapa sekaligus
     |--------------------------------------------------------------------------
     |
     | Mengabari dua puluh pendaftar satu rombongan sebelumnya berarti membuka
     | dua puluh halaman detail dan menyalin dua puluh pesan. Yang dikerjakan
     | di sini bukan pengiriman massal — WhatsApp tidak mengizinkannya dari
     | peramban — melainkan menyiapkan daftar nomor dan satu pesan yang sama,
     | sehingga yang tersisa menekan kirim.
     */

    /** @var array<int, string> */
    public array $dipilih = [];

    public bool $bukaPesanMassal = false;

    public string $pesanMassal = '';

    public function updatedDipilih(): void
    {
        // Pilihan yang tinggal nol menutup panelnya sendiri: panel pesan untuk
        // nol orang cuma menyisakan tombol yang tidak melakukan apa pun.
        if ($this->dipilih === []) {
            $this->bukaPesanMassal = false;
        }
    }

    /**
     * Mencentang atau mengosongkan seluruh baris yang SEDANG TAMPIL.
     *
     * Sengaja hanya yang tampil, bukan seluruh hasil saringan. Satu tekanan
     * yang memilih 300 orang yang tidak terlihat di layar adalah pilihan yang
     * tidak bisa diperiksa admin sebelum bertindak.
     */
    public function pilihSemuaTampil(array $id): void
    {
        $semua = collect($id)->map(fn ($satu) => (string) $satu)->values()->all();

        $this->dipilih = count(array_diff($semua, $this->dipilih)) === 0
            ? array_values(array_diff($this->dipilih, $semua))
            : array_values(array_unique(array_merge($this->dipilih, $semua)));

        if ($this->dipilih === []) {
            $this->bukaPesanMassal = false;
        }
    }

    public function kosongkanPilihan(): void
    {
        $this->dipilih = [];
        $this->bukaPesanMassal = false;
        $this->bukaGabung = false;
        $this->keadaanMassal = '';
        $this->pesanMassal = '';
    }

    /*
     |--------------------------------------------------------------------------
     | Menandai beberapa sekaligus
     |--------------------------------------------------------------------------
     |
     | Mengabari dua puluh pendaftar lalu menandai semuanya "sudah dihubungi"
     | sebelumnya berarti membuka dua puluh halaman. Yang bisa lewat sini cuma
     | penandaan — "Batal" sengaja tidak ditawarkan, karena membatalkan melepas
     | kursi dan mengabari daftar tunggu, dan dua puluh pembatalan dari satu
     | salah klik tidak bisa dibatalkan.
     */
    public string $keadaanMassal = '';

    public function ubahKeadaanMassal(): void
    {
        if ($this->dipilih === []) {
            $this->dispatch('toast-error', message: 'Belum ada pendaftaran yang dicentang.');

            return;
        }

        if ($this->keadaanMassal === '') {
            $this->dispatch('toast-error', message: 'Pilih dulu keadaan yang mau diberikan.');

            return;
        }

        $this->kirimPerubahan('/pendaftaran/status-massal', [
            'id' => collect($this->dipilih)->map(fn ($id) => (int) $id)->all(),
            'status' => $this->keadaanMassal,
        ], 'Keadaan pendaftaran yang dicentang diperbarui.');

        $this->dipilih = [];
        $this->keadaanMassal = '';
        $this->bukaPesanMassal = false;
    }

    /**
     * Keadaan yang boleh diberikan ke banyak baris sekaligus.
     *
     * "Batal" dibuang di SINI juga, bukan hanya di Orcha. Penjaga di sana yang
     * berwenang; yang di sini supaya pilihannya tidak pernah terlihat — tombol
     * yang ditawarkan lalu ditolak membuat admin mengira aplikasinya rusak.
     *
     * @return array<string, string>
     */
    public function pilihanKeadaanMassal(): array
    {
        return collect($this->rujukan('status_pendaftaran'))
            ->reject(fn ($label, $kunci) => $kunci === 'batal')
            ->all();
    }

    /*
     |--------------------------------------------------------------------------
     | Gabungkan dua pendaftaran
     |--------------------------------------------------------------------------
     |
     | Terjadi tiap kali pemesan mengisi borang dua kali — sekali untuk
     | dirinya, sekali untuk sisa keluarganya. Menghapus salah satunya bukan
     | jalan keluar begitu uangnya sudah masuk, dan biasanya memang sudah.
     */

    public bool $bukaGabung = false;

    public function bukaFormulirGabung(): void
    {
        if (count($this->dipilih) !== 2) {
            $this->dispatch('toast-error',
                message: 'Centang TEPAT DUA pendaftaran yang mau digabungkan.');

            return;
        }

        $this->bukaGabung = true;
    }

    public function tutupFormulirGabung(): void
    {
        $this->bukaGabung = false;
    }

    /**
     * Menggabungkan dua baris yang dicentang.
     *
     * $tujuan menentukan mana yang BERTAHAN — kode pemesanannya yang dipakai
     * seterusnya. Admin memilihnya sendiri, tidak ditebak dari yang lebih tua
     * atau yang lebih banyak pesertanya: yang menentukan biasanya kwitansi mana
     * yang sudah terlanjur dikirim ke pemesan, dan itu tidak terbaca dari data.
     */
    public function gabungkan(int $tujuan): void
    {
        $sumber = collect($this->dipilih)
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === $tujuan)
            ->first();

        if (! $sumber) {
            $this->dispatch('toast-error', message: 'Pilihannya tidak lengkap.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim("/pendaftaran/{$sumber}/gabungkan", ['ke' => $tujuan]);

            cache()->forget('orcha.perlu-ditindak');
            \App\Support\HitunganOrcha::lupakanSemua();

            $this->dipilih = [];
            $this->bukaGabung = false;
            $this->dispatch('order-updated',
                message: $balasan['pesan'] ?? 'Pendaftaran digabungkan.');
        } catch (\App\Exceptions\OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function bukaKirimMassal(): void
    {
        if ($this->dipilih === []) {
            $this->dispatch('toast-error', message: 'Belum ada pendaftaran yang dicentang.');

            return;
        }

        $this->bukaPesanMassal = true;

        if (trim($this->pesanMassal) === '') {
            $this->pesanMassal = "Selamat pagi, Bapak/Ibu.\n\n"
                ."Kami ingin mengabari perihal trip yang Bapak/Ibu ikuti.\n\n"
                .'Terima kasih.';
        }
    }

    public function tutupKirimMassal(): void
    {
        $this->bukaPesanMassal = false;
    }

    /**
     * Baris yang dicentang, lengkap dengan tautan WhatsApp masing-masing.
     *
     * Dirakit dari daftar yang sedang tampil. Centangan pada halaman lain
     * ikut tersimpan di $dipilih tetapi tidak bisa dirakit tautannya di sini —
     * nomornya tidak ada di tangan. Yang hilang itu DISEBUT lewat selisih
     * angkanya, bukan didiamkan: admin yang mencentang lima lalu melihat tiga
     * tautan perlu tahu dua sisanya ada di halaman sebelumnya.
     *
     * @param  array<int, array<string, mixed>>  $daftar
     * @return array<int, array<string, string>>
     */
    public function barisDipilih(array $daftar): array
    {
        return collect($daftar)
            ->filter(fn ($baris) => in_array((string) $baris['id'], $this->dipilih, true))
            ->map(fn ($baris) => [
                'id' => (string) $baris['id'],
                'nama' => (string) ($baris['nama'] ?? ''),
                'kode' => (string) ($baris['kode'] ?? ''),
                'whatsapp' => (string) ($baris['whatsapp'] ?? ''),
            ])
            ->values()
            ->all();
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
            // Bawaannya tidak dikirim: "terbaru" sudah urutan bawaan Orcha,
            // dan parameter yang selalu menempel membuat alamat halaman
            // berbeda tanpa ada yang berbeda.
            'urut' => $this->urut === 'terbaru' ? null : $this->urut,
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
            /*
             | Ringkasan satu rombongan: kursi terjual, uang masuk, sisa.
             |
             | Hanya ikut saat daftar disaring per rombongan — Orcha yang
             | memutuskannya, dan di luar itu kuncinya memang tidak dikirim.
             | Angkanya dipakai tiap kali rombongan diputuskan jadi berangkat
             | atau tidak, dan sebelum ini dirakit admin sendiri dari daftar.
             */
            'ringkasanRombongan' => $meta['rombongan'] ?? null,
            'pilihanUrut' => self::URUT,
            'pilihanKeadaanMassal' => $this->pilihanKeadaanMassal(),
            /*
             | Id baris yang SEDANG TAMPIL, untuk kotak "centang semua".
             |
             | Berupa teks, bukan angka: $dipilih diisi oleh wire:model pada
             | kotak centang, dan nilai dari peramban selalu datang sebagai
             | teks. Membandingkan teks dengan angka lewat in_array yang ketat
             | selalu memulangkan false — kotaknya lalu tidak pernah terlihat
             | tercentang walau pilihannya tersimpan.
             */
            'idTampil' => collect($hasil['data'] ?? [])
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all(),
            'barisDipilih' => $this->barisDipilih($hasil['data'] ?? []),
        ])->layout('livewire.layout.templateindex');
    }
}
