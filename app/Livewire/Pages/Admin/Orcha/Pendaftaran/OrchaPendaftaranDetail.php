<?php

namespace App\Livewire\Pages\Admin\Orcha\Pendaftaran;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Satu pelanggan, selengkapnya.
 *
 * Daftar pendaftaran menjawab "siapa saja yang mendaftar"; halaman ini
 * menjawab pertanyaan yang muncul setelahnya, dan hampir selalu datang
 * bersamaan lewat WhatsApp: sudah bayar berapa, siapa saja yang ikut dan
 * dijemput di mana, riwayat kesehatannya sudah lengkap belum, serta apakah
 * ada pengajuan pembatalan. Sebelumnya jawabannya tersebar di empat menu.
 */
class OrchaPendaftaranDetail extends Component
{
    use IsianRupiah, MemanggilOrcha, WithFileUploads;

    public int $pendaftaranId;

    /** Surat pernyataan yang sudah ditandatangani, dipindai atau difoto. */
    public $suratTtd;

    public array $data = [];

    /**
     * Formulir pencatatan pembayaran yang diterima admin sendiri.
     *
     * Private trip dan study tour tidak lewat formulir konfirmasi publik.
     * Panitia mentransfer lalu mengabari lewat WhatsApp, kadang cuma dengan
     * kalimat "sudah ditransfer ya" tanpa tangkapan layar. Yang memastikan
     * uangnya benar-benar masuk adalah admin yang membuka mutasi rekening.
     *
     * Sebelum ini pemeriksaan itu tidak punya tempat pulang: uangnya sudah
     * diterima tetapi statusnya tertahan di "Baru", formulir kesehatannya
     * tetap tertutup, dan laporan keuangan menyebut nol.
     */
    public bool $bukaBayar = false;

    public array $bayar = [];

    public function mount(int $pendaftaran): void
    {
        $this->pendaftaranId = $pendaftaran;
        $this->kosongkanBayar();
    }

    private function kosongkanBayar(): void
    {
        $this->bayar = [
            'nominal' => '',
            // Bawaannya HARI INI, bukan kosong. Admin mencatatnya pada hari ia
            // membuka mutasi rekening, dan transfer yang dicek hari ini
            // hampir selalu masuk hari ini atau kemarin.
            'tanggal_transfer' => now()->toDateString(),
            'bank_pengirim' => '',
            'atas_nama_pengirim' => '',
            'jenis' => 'dp',
            'catatan' => '',
        ];
    }

    public function bukaFormulirBayar(): void
    {
        $this->kosongkanBayar();

        // Atas nama pengirim hampir selalu si pemesan. Diisikan lebih dulu
        // supaya yang paling sering benar tidak perlu diketik tiap kali.
        $this->bayar['atas_nama_pengirim'] = (string) ($this->data['nama'] ?? '');

        $this->bukaBayar = true;
        $this->resetValidation();
    }

    public function tutupFormulirBayar(): void
    {
        $this->bukaBayar = false;
        $this->kosongkanBayar();
    }

    /*
     | Nominalnya diformat ulang saat isiannya ditinggalkan.
     |
     | Nominal transfer sekolah berupa angka jutaan; "5000000" di layar memaksa
     | admin menghitung nolnya dengan jari sambil menatap mutasi rekening di
     | layar sebelah — dan itulah saat paling mudah meleset satu digit.
     */
    public function updatedBayarNominal(): void
    {
        $this->bayar['nominal'] = $this->keRupiah($this->angkaDari($this->bayar['nominal'] ?? ''));
    }

    public function catatBayar(): void
    {
        $this->validate([
            // Bentuknya bertitik, jadi yang divalidasi keberadaannya —
            // angkanya diperiksa di bawah, setelah titiknya dibuang.
            'bayar.nominal' => 'required|string',
            'bayar.tanggal_transfer' => 'required|date|before_or_equal:today',
            'bayar.bank_pengirim' => 'required|string|max:60',
            'bayar.atas_nama_pengirim' => 'required|string|max:120',
            'bayar.jenis' => 'required|string',
            'bayar.catatan' => 'nullable|string|max:1000',
        ], [], [
            'bayar.nominal' => 'nominal',
            'bayar.tanggal_transfer' => 'tanggal transfer',
            'bayar.bank_pengirim' => 'bank pengirim',
            'bayar.atas_nama_pengirim' => 'atas nama pengirim',
        ]);

        $nominal = $this->angkaDari($this->bayar['nominal']);

        if ($nominal < 1) {
            $this->addError('bayar.nominal', 'Nominal harus lebih dari nol.');

            return;
        }

        try {
            $hasil = $this->orcha()->kirim("/pendaftaran/{$this->pendaftaranId}/pembayaran", [
                'nominal' => $nominal,
                'tanggal_transfer' => $this->bayar['tanggal_transfer'],
                'bank_pengirim' => $this->bayar['bank_pengirim'],
                'atas_nama_pengirim' => $this->bayar['atas_nama_pengirim'],
                'jenis' => $this->bayar['jenis'],
                'catatan' => $this->bayar['catatan'] ?: null,
            ]);

            cache()->forget('orcha.perlu-ditindak');

            $this->tutupFormulirBayar();
            $this->dispatch('order-updated',
                message: $hasil['pesan'] ?? 'Pembayaran dicatat.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Menyimpan surat pernyataan yang sudah ditandatangani.
     *
     * Sistem sudah bisa menerbitkan suratnya, tetapi berkas yang kembali —
     * bermaterai, bertanda tangan pemesan dan para pengganti — sebelumnya tidak
     * punya tempat pulang. Selama itu ia cuma ada di percakapan WhatsApp satu
     * admin, dan hilang begitu ponselnya berganti.
     */
    public function updatedSuratTtd(): void
    {
        $this->validate([
            'suratTtd' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:8192',
        ], [], ['suratTtd' => 'berkas surat']);

        try {
            $this->orcha()->unggah(
                "/pendaftaran/{$this->pendaftaranId}/surat-penggantian-ttd",
                'surat',
                $this->suratTtd,
            );

            $this->dispatch('toast-sukses', message: 'Surat bertanda tangan tersimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }

        $this->suratTtd = null;
    }

    /** Mencabut surat yang salah unggah. */
    public function hapusSuratTtd(): void
    {
        try {
            $this->orcha()->hapus("/pendaftaran/{$this->pendaftaranId}/surat-penggantian-ttd");

            $this->dispatch('toast-sukses', message: 'Surat bertanda tangan dihapus.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Pilihan pesan WhatsApp yang pantas dikirim untuk keadaan pendaftaran ini.
     *
     * Tombol WhatsApp dulunya membuka percakapan kosong, dan admin mengetik
     * ulang kalimat yang sama berpuluh kali sehari — dengan angka yang harus
     * disalin sendiri dari layar sebelah. Salah satu angka salah ketik berarti
     * pelanggan mentransfer nominal yang keliru.
     *
     * Yang muncul hanya yang berlaku: pendaftaran lunas tidak menawarkan
     * tagihan, dan yang tidak pernah berganti peserta tidak menawarkan surat
     * pernyataan. Pilihan yang tidak relevan bukan sekadar mubazir — ia membuat
     * admin ragu apakah ia melewatkan sesuatu.
     *
     * @return array<int, array{kunci: string, judul: string, ringkas: string, ikon: string, rupa: string, pesan: string}>
     */
    public function pilihanPesan(): array
    {
        $tagihan = $this->data['tagihan'] ?? [];
        $kode = $this->data['kode'] ?? '';
        $nama = $this->data['nama'] ?? 'Kak';
        $paket = data_get($this->data, 'paket.nama') ?: 'perjalanan Anda';

        $berangkat = ! empty($this->data['tanggal_berangkat'])
            ? \Carbon\Carbon::parse($this->data['tanggal_berangkat'])->locale('id')->translatedFormat('l, j F Y')
            : null;

        /*
         | Emoji ditulis sebagai TITIK KODE, bukan huruf emojinya langsung.
         |
         | Pola yang sudah dipakai halaman detail order dan daftar pembayaran,
         | dengan alasan yang sudah diuji di sana: emoji rusak jadi tanda tanya
         | dalam perjalanan lewat respons server, sementara tiap pemeriksaan di
         | sisi kami tampak bersih. Penandanya dirakit jadi emoji di peramban
         | dengan String.fromCodePoint — lihat partial salin-wa.
         |
         | Bila skripnya tidak sempat jalan, penandanya dibuang begitu saja dan
         | kalimatnya tetap utuh. Kalimat lengkap tanpa emoji jauh lebih baik
         | daripada kalimat penuh tanda tanya.
         */
        $pakaiEmoji = (bool) config('orcha.emoji_wa', true);
        $e = fn (string $titikKode) => $pakaiEmoji ? "[[E:{$titikKode}]] " : '';

        $salam = 'Halo Kak '.$nama.($pakaiEmoji ? ' [[E:1F44B]]' : '')
            ."\n\nTerima kasih sudah mendaftar *{$paket}* bersama Orcha Journey."
            ."\nKode pendaftaran Anda: *{$kode}*";
        $penutup = "\n\n".$e('1F64F')."Terima kasih\n_Orcha Journey_";

        $pilihan = [];

        // ---------- Posisi pembayaran ----------
        $lunas = $tagihan['lunas'] ?? false;
        $pembatalan = $this->data['pembatalan'] ?? null;

        /*
         | Empat keadaan, empat kabar yang berbeda — dan sebelumnya cuma dua
         | yang punya pesan.
         |
         | Yang sudah lunas dan yang mengajukan pembatalan sama-sama tidak
         | menawarkan apa pun, sehingga popupnya berkata "tidak ada yang perlu
         | dikirimkan" justru pada dua keadaan yang paling sering ditanyakan
         | pelanggan lewat WhatsApp: "sudah lunas belum?" dan "uang saya
         | bagaimana?".
         */
        if ($pembatalan) {
            $sudahTeks = $tagihan['sudah_teks'] ?? null;
            $adaUang = ($tagihan['sudah'] ?? 0) > 0;

            $pilihan[] = [
                'kunci' => 'pembatalan',
                'judul' => 'Kabar pengajuan pembatalan',
                'ringkas' => 'Status: '.($pembatalan['status'] ?? 'diajukan')
                    .($adaUang ? ' · sudah membayar '.$sudahTeks : ' · belum ada pembayaran'),
                'ikon' => 'bi-x-octagon',
                'rupa' => 'orcha-ikon-awas',
                'pesan' => $salam
                    ."\n\n".$e('1F4CB')."*Pengajuan pembatalan*\n"
                    .'Pengajuan pembatalan Anda berstatus *'.($pembatalan['status'] ?? 'diajukan')."*.\n"
                    .($adaUang
                        // Pertanyaan pertama orang yang membatalkan bukan status
                        // pengajuannya, melainkan nasib uang yang sudah dikirim.
                        ? "Pembayaran yang sudah masuk: *{$sudahTeks}*.\n"
                            .'Pengembaliannya mengikuti Kebijakan Pengembalian dan Pembatalan '
                            .'Orcha Journey, dan akan kami kabari setelah diperiksa.'
                        : 'Belum ada pembayaran yang masuk untuk pendaftaran ini, jadi tidak ada '
                            .'dana yang perlu dikembalikan.')
                    .$penutup,
            ];
        } elseif ($lunas && $tagihan !== []) {
            $totalTeks = $tagihan['total_teks'] ?? '—';

            $pilihan[] = [
                'kunci' => 'lunas',
                'judul' => 'Konfirmasi pembayaran lunas',
                'ringkas' => 'Sudah lunas '.$totalTeks.' — tidak ada sisa',
                'ikon' => 'bi-patch-check',
                'rupa' => 'orcha-ikon-aman',
                'pesan' => $salam
                    ."\n\n".$e('2705')."*Pembayaran Anda sudah LUNAS*\n"
                    ."Total yang telah kami terima: *{$totalTeks}*.\nTidak ada sisa pembayaran."
                    .($berangkat ? "\n\n".$e('1F5D3')."Keberangkatan: {$berangkat}" : '')
                    ."\n\nSampai jumpa di titik jemput ya."
                    .$penutup,
            ];
        }

        // ---------- Tagihan ----------
        if ($tagihan !== [] && ! $lunas && ! $pembatalan) {
            $konfirmasi = $this->data['konfirmasi_pembayaran_tautan'] ?? null;
            $jenis = $tagihan['jenis_disarankan'] ?? 'dp';
            $sudahBayar = ($tagihan['sudah'] ?? 0) > 0;

            /*
             | Tiap medan diambil dengan nilai cadangan.
             |
             | Bentuk tagihan dari Orcha tidak selalu lengkap — pesanan tanpa
             | paket berharga mengembalikan larik kosong, dan sebagian jalur
             | mengirim angkanya tanpa versi teks. Mengambilnya langsung
             | membuat SELURUH halaman detail gagal 500 hanya karena satu kunci
             | hilang, dan yang hilang bukan bagian pentingnya.
             */
            $totalTeks = $tagihan['total_teks'] ?? '—';
            $sudahTeks = $tagihan['sudah_teks'] ?? '—';
            $sisaTeks = $tagihan['sisa_teks'] ?? '—';
            $dpTeks = $tagihan['dp_teks'] ?? '—';
            $dpPersen = $tagihan['dp_persen'] ?? null;

            $rincian = "\n\n".$e('1F4B0')."*Rincian biaya*\n"
                ."Total: {$totalTeks}\n"
                .($sudahBayar ? "Sudah dibayar: {$sudahTeks}\n" : '')
                .($jenis === 'dp'
                    ? 'Uang muka'.($dpPersen ? " ({$dpPersen}%)" : '').": *{$dpTeks}*"
                    : "Sisa pelunasan: *{$sisaTeks}*");

            $tenggat = $this->tenggatPelunasan();

            $pilihan[] = [
                'kunci' => 'tagihan',
                'judul' => $jenis === 'dp' ? 'Tagihan uang muka (DP)' : 'Tagihan pelunasan',
                'ringkas' => ($jenis === 'dp'
                    ? 'Menyebut nominal DP '.$dpTeks
                    : 'Menyebut sisa '.$sisaTeks.' yang harus dilunasi')
                    .($konfirmasi ? ' · membawa tautan kirim bukti' : ''),
                'ikon' => 'bi-cash-coin',
                'rupa' => 'orcha-ikon-omzet',
                'pesan' => $salam.$rincian
                    .($berangkat ? "\n\n".$e('1F5D3')."Keberangkatan: {$berangkat}" : '')
                    .($tenggat ? "\n".$e('23F3')."Mohon diselesaikan paling lambat *{$tenggat}*" : '')
                    ."\n\nPembayaran lewat transfer bank."
                    /*
                     | Buktinya dikirim lewat formulir, bukan dibalas ke percakapan.
                     |
                     | "Kirim buktinya ke sini" membuat gambar transfer menumpuk di
                     | percakapan lalu dicatat tangan satu per satu — dan yang
                     | terlewat baru ketahuan saat pelanggan menagih. Lewat formulir
                     | ia langsung masuk ke daftar Bukti Pembayaran, lengkap dengan
                     | nominal dan tanggalnya.
                     |
                     | Kode, jenis, dan nominalnya sudah terisi dari tautan; yang
                     | membuka tinggal melampirkan berkasnya.
                     */
                    .($konfirmasi
                        ? "\nSetelah transfer, mohon kirim bukti lewat tautan berikut:\n{$konfirmasi}"
                            ."\nKode dan nominalnya sudah terisi otomatis."
                        : "\nSetelah transfer, mohon kirim buktinya supaya kami catat.")
                    .$penutup,
            ];
        }

        // ---------- Riwayat kesehatan ----------
        $belumIsi = collect($this->data['peserta_belum_isi'] ?? [])->filter()->values();

        if ($belumIsi->isNotEmpty()) {
            /*
             | Tiap nama dibawakan tautan pribadinya, bukan cuma disebut.
             |
             | Sebelumnya pesannya menyuruh "buka menu Riwayat Kesehatan lalu
             | masukkan kode" — tiga langkah, dan kodenya mudah salah ketik.
             | Tautan yang sudah membawa kode dan nama membuat peserta tinggal
             | mengisi kondisinya sendiri, dan pemesan tinggal meneruskan tautan
             | yang tepat ke orang yang tepat.
             |
             | Tautannya dibuat Orcha, pemilik rutenya. Menyusunnya di sini
             | berarti menebak bentuk alamat yang sewaktu-waktu berubah tanpa
             | ada yang memberi tahu.
             */
            $tautanPeserta = $this->data['peserta_belum_isi_tautan'] ?? [];

            $daftar = $belumIsi
                ->map(function ($nama) use ($tautanPeserta) {
                    $tautan = $tautanPeserta[$nama] ?? null;

                    return $tautan ? "• *{$nama}*\n  {$tautan}" : "• {$nama}";
                })
                ->implode("\n\n");

            // Tanpa satu pun tautan — data lama, atau Orcha versi lawas — pesannya
            // kembali ke petunjuk manual daripada menyuruh mengetuk yang tidak ada.
            $adaTautan = collect($tautanPeserta)->filter()->isNotEmpty();

            $penutupIsi = $adaTautan
                ? "\n\nCukup buka tautan di atas, nama dan kodenya sudah terisi otomatis."
                    ."\nMohon diisi sebelum keberangkatan — data ini kami perlukan untuk "
                    .'keselamatan selama perjalanan.'
                : "\n\nMohon diisi sebelum keberangkatan lewat menu *Riwayat Kesehatan* di "
                    ."website kami, cukup dengan kode *{$kode}*.\nData ini kami perlukan untuk "
                    .'keselamatan selama perjalanan.';

            $pilihan[] = [
                'kunci' => 'kesehatan',
                'judul' => 'Riwayat kesehatan belum diisi',
                'ringkas' => $adaTautan
                    ? $belumIsi->count().' peserta belum mengisi — pesannya membawa tautan tiap orang'
                    : $belumIsi->count().' peserta belum mengisi',
                'ikon' => 'bi-heart-pulse',
                'rupa' => 'orcha-ikon-awas',
                'pesan' => $salam
                    ."\n\n".$e('1FA7A')."*Riwayat kesehatan peserta*\nPeserta berikut belum mengisi:\n\n{$daftar}"
                    .$penutupIsi
                    .$penutup,
            ];
        }

        // ---------- Kwitansi ----------
        if (! empty($this->data['kwitansi_tautan'])) {
            $pilihan[] = [
                'kunci' => 'kwitansi',
                'judul' => 'Kirim kwitansi',
                'ringkas' => 'Tautan unduh, berlaku 30 hari',
                'ikon' => 'bi-receipt',
                'rupa' => 'orcha-ikon-catat',
                'pesan' => $salam
                    ."\n\n".$e('1F9FE')."Berikut kwitansi pendaftaran Anda:\n{$this->data['kwitansi_tautan']}"
                    ."\n\nSimpan tautannya ya — berlaku 30 hari.".$penutup,
            ];
        }

        // ---------- Surat penggantian yang menunggu tanda tangan ----------
        if (! empty($this->data['surat_penggantian_kosong_tautan'])) {
            $sudahDitandatangani = ! empty($this->data['surat_penggantian']);

            $jumlahGanti = count($this->data['riwayat_penggantian'] ?? []);

            $pilihan[] = [
                'kunci' => 'surat-kosong',
                'judul' => 'Kirim surat pernyataan untuk ditandatangani',
                'ringkas' => $sudahDitandatangani
                    ? 'Sudah ada yang bertanda tangan — kirim ulang bila perlu diperbaiki'
                    : 'Belum ada yang bertanda tangan masuk',
                'ikon' => 'bi-pencil-square',
                'rupa' => $sudahDitandatangani ? 'orcha-ikon-catat' : 'orcha-ikon-awas',
                'pesan' => $salam
                    ."\n\n".$e('270D')."*Surat pernyataan penggantian peserta*\n"
                    ."Sehubungan dengan penggantian {$jumlahGanti} peserta pada pendaftaran ini, "
                    .'mohon surat berikut dicetak, ditandatangani di atas materai Rp10.000 oleh '
                    ."pemesan dan peserta pengganti, lalu dikirim kembali ke kami:\n"
                    ."{$this->data['surat_penggantian_kosong_tautan']}"
                    ."\n\nPenggantian peserta tidak dikenakan biaya sepanjang jumlah pesertanya tetap."
                    .$penutup,
            ];
        }

        // ---------- Salinan surat yang sudah ditandatangani ----------
        if (! empty($this->data['surat_penggantian'])) {
            $pilihan[] = [
                'kunci' => 'surat',
                'judul' => 'Kirim salinan surat bertanda tangan',
                'ringkas' => 'Salinan arsip, untuk pegangan pemesan',
                'ikon' => 'bi-file-earmark-pdf',
                'rupa' => 'orcha-ikon-aman',
                'pesan' => $salam
                    ."\n\n".$e('1F4C4').'Berikut salinan surat pernyataan penggantian peserta yang sudah '
                    ."ditandatangani:\n{$this->data['surat_penggantian']}".$penutup,
            ];
        }

        /*
         | Tiap pilihan membawa dua bentuk pesan.
         |
         | 'pesan' berpenanda, dipakai skrip untuk merakit emoji dan menyalin ke
         | papan tempel. 'polos' tanpa penanda, dipakai href sebagai cadangan
         | bila skripnya tidak sempat jalan.
         */
        return array_map(function ($satu) {
            $satu['polos'] = trim(preg_replace('/[ \t]+$/m', '',
                preg_replace('/\[\[E:[0-9A-F]+\]\] ?/', '', $satu['pesan'])));

            return $satu;
        }, $pilihan);
    }

    /** Tautan WhatsApp lewat api.whatsapp.com, bukan wa.me. */
    public function tautanWa(string $pesan): string
    {
        return \App\Support\TautanWa::kirim($this->data['whatsapp'] ?? null, $pesan);
    }

    /** Tenggat pelunasan menurut aturan Orcha, dalam kalimat siap kirim. */
    private function tenggatPelunasan(): ?string
    {
        $hari = (int) ($this->rujukan('pembayaran')['pelunasan_hari_sebelum'] ?? 0);

        if ($hari <= 0 || empty($this->data['tanggal_berangkat'])) {
            return null;
        }

        return \Carbon\Carbon::parse($this->data['tanggal_berangkat'])
            ->subDays($hari)
            ->locale('id')
            ->translatedFormat('j F Y');
    }

    public function ubahStatus(string $status): void
    {
        $this->kirimPerubahan(
            "/pendaftaran/{$this->pendaftaranId}/status",
            ['status' => $status],
            'Status pendaftaran diperbarui di Orcha.'
        );
    }

    public function render()
    {
        $hasil = $this->muat("/pendaftaran/{$this->pendaftaranId}");
        $this->data = $hasil['data'] ?? [];

        $this->data['riwayat_penggantian'] = $this->bernama($this->data['riwayat_penggantian'] ?? []);

        return view('livewire.pages.admin.orcha.pendaftaran.detail', [
            'pilihanPesan' => $this->pilihanPesan(),
            'pendaftaran' => $this->data,
            'pilihanStatus' => $this->rujukan('status_pendaftaran'),
            // Batas pelunasan diambil dari aturan Orcha, bukan ditulis angkanya
            // di sini: sekali H-5 diubah di config sana, halaman ini ikut —
            // dan tenggat yang berbeda antara dua aplikasi adalah janji yang
            // saling bertentangan di depan pelanggan yang sama.
            'aturanBayar' => $this->rujukan('pembayaran'),
            // Pilihan jenis pembayaran datang dari Orcha, bukan diketik di
            // sini: kunci yang berbeda antara kedua aplikasi ditolak validasi
            // di sana, dan penolakannya sampai ke admin sebagai pesan yang
            // tidak menunjuk apa pun.
            'pilihanJenisBayar' => $this->rujukan('jenis_pembayaran'),
        ])->layout('livewire.layout.templateindex');
    }

    /**
     * Menerjemahkan pencatat penggantian dari surel menjadi nama.
     *
     * Penggantian yang tercatat sebelum Agustus 2026 menyimpan SUREL admin,
     * karena itu yang dikirim OrchaClient waktu itu. Nilainya sudah terlanjur
     * menempel di data Orcha dan tidak bisa dibetulkan dari sana: Orcha tidak
     * mengenal pengguna lemon, jadi bagi dia surel itu sekadar teks.
     *
     * Yang mengenal keduanya justru halaman ini. Diterjemahkan saat ditampilkan,
     * bukan lewat penulisan ulang data — riwayat penggantian adalah arsip yang
     * menyatakan apa yang terjadi, dan menyuntingnya di belakang layar merusak
     * satu-satunya hal yang membuatnya berguna. Surel yang penggunanya sudah
     * dihapus dibiarkan apa adanya; menggantinya dengan "tidak diketahui"
     * membuang keterangan yang masih bisa dilacak.
     *
     * @param  array<int, array<string, mixed>>  $riwayat
     * @return array<int, array<string, mixed>>
     */
    private function bernama(array $riwayat): array
    {
        $surel = collect($riwayat)
            ->pluck('oleh')
            ->filter(fn ($oleh) => filled($oleh) && str_contains((string) $oleh, '@'))
            ->unique()
            ->values();

        if ($surel->isEmpty()) {
            return $riwayat;
        }

        $nama = User::whereIn('email', $surel)->pluck('name', 'email');

        return collect($riwayat)
            ->map(function ($satu) use ($nama) {
                $satu['oleh'] = $nama[$satu['oleh'] ?? ''] ?? ($satu['oleh'] ?? null);

                return $satu;
            })
            ->all();
    }
}
