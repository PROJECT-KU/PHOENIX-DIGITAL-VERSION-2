@section('title')
Detail Pendaftaran || lemon
@stop

@php
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanBukti = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;

    $wa = fn ($nomor) => 'https://wa.me/' . preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $nomor));

    $tagihan = $pendaftaran['tagihan'] ?? [];
    $pembayaran = $pendaftaran['pembayaran'] ?? [];
    $pembatalan = $pendaftaran['pembatalan'] ?? null;
    $peserta = $pendaftaran['peserta'] ?? [];
    $belumIsi = collect($pendaftaran['peserta_belum_isi'] ?? [])
        ->map(fn ($nama) => mb_strtolower(trim($nama)))
        ->all();

    $persenBayar = ($tagihan['total'] ?? 0) > 0
        ? min(100, round(($tagihan['sudah'] ?? 0) / $tagihan['total'] * 100))
        : 0;

    // Jarak ke keberangkatan dan tenggat pelunasannya. Inilah yang menentukan
    // tindakan admin hari ini — bukan tanggalnya sendiri — dan menghitungnya
    // dari kalender adalah pekerjaan yang berulang setiap kali halaman dibuka.
    $berangkat = ! empty($pendaftaran['tanggal_berangkat'])
        ? \Carbon\Carbon::parse($pendaftaran['tanggal_berangkat'])->startOfDay()
        : null;
    $sisaHari = $berangkat ? now()->startOfDay()->diffInDays($berangkat, false) : null;

    $hariPelunasan = (int) ($aturanBayar['pelunasan_hari_sebelum'] ?? 0);
    $tenggat = $berangkat && $hariPelunasan > 0 ? $berangkat->copy()->subDays($hariPelunasan) : null;
    $lunas = $tagihan['lunas'] ?? false;
    $tenggatLewat = $tenggat && ! $lunas && $tenggat->isPast();
@endphp
<div>
    {{-- Kerangka mengikuti Pesanan Toko (bahasa rupa dsb-*): satu kisi 12
         kolom, kartu putih datar, kepala kartu berikon. Dua layar yang
         dikerjakan orang yang sama tidak perlu dipelajari dua kali. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    {{-- Gaya khusus layar ini. Ditulis INLINE, bukan lewat Vite: public/build
         di-gitignore sehingga berkas hasil build tidak pernah sampai ke
         server. --}}
    <style>
    /* ===== Bilah perkakas =====
       Satu baris: berkas & tindakan di kiri, keadaan pendaftaran di kanan.
       Yang kiri melebar mengisi sisa ruang (flex: 1) supaya pada layar lebar
       tidak ada petak kosong menganggur di tengah baris. */
    /* Kisi dua sel, bukan satu baris lentur yang membungkus.

       Dengan baris lentur, tombol yang tidak muat turun ke baris kedua
       sedangkan kotak keadaan tetap didorong ke ujung kanan — menyisakan
       lubang selebar separuh kartu di antara keduanya. Pada kisi, keadaan
       duduk di selnya sendiri dan tetap sejajar betapa pun banyak baris
       yang dipakai tombol di kiri. */
    .pd-perkakas {
        display: grid; grid-template-columns: minmax(0, 1fr) auto;
        align-items: center; gap: 14px;
    }
    .pd-perkakas-kiri {
        display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 0;
    }
    .pd-perkakas-kanan { display: flex; align-items: center; gap: 9px; }
    .pd-perkakas-kanan .orcha-label-kecil { margin: 0; white-space: nowrap; }
    .pd-pilih-status { width: auto; min-width: 170px; font-weight: 700; }

    /* Di HP keadaan turun ke barisnya sendiri dan melebar penuh: kotak pilih
       selebar 170px yang berbagi baris dengan label membuat keduanya terpotong. */
    @media (max-width: 767.98px) {
        .pd-perkakas { grid-template-columns: minmax(0, 1fr); }
        .pd-perkakas-kanan { justify-content: space-between; }
        .pd-pilih-status { flex: 1 1 auto; min-width: 0; max-width: 60%; }
    }
    @media (max-width: 575.98px) {
        .pd-perkakas-kiri > .dsb-tombol { flex: 1 1 auto; justify-content: center; }
    }

    /* ===== Deret lencana di kepala ===== */
    .pd-lencana-kepala {
        display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-top: 7px;
    }

    /* ===== Dua blok dalam satu kartu =====
       Keterangan pemesan dan keterangan perjalanan masing-masing terlalu
       pendek untuk jadi kartu sendiri. Dulu keduanya kartu terpisah di dua
       kolom berbeda, dan yang pendek meninggalkan petak putih sepanjang
       setengah layar di sebelah daftar peserta. */
    .pd-duo {
        display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: clamp(16px, 2.2vw, 30px);
    }
    .pd-blok { min-width: 0; }
    .pd-blok + .pd-blok {
        border-left: 1px solid #f1f5f9; padding-left: clamp(16px, 2.2vw, 30px);
    }
    @media (max-width: 991.98px) {
        .pd-duo { grid-template-columns: minmax(0, 1fr); }
        .pd-blok + .pd-blok {
            border-left: 0; padding-left: 0;
            border-top: 1px solid #f1f5f9; padding-top: 18px;
        }
    }
    .pd-blok-judul {
        display: flex; align-items: center; gap: 7px; margin-bottom: 14px;
        font-size: .72rem; font-weight: 800; letter-spacing: .05em;
        text-transform: uppercase; color: #94a3b8;
    }
    .pd-blok-judul i.bi { color: #7c3aed; font-size: .88rem; line-height: 1; }
    .pd-blok-judul i.bi::before { display: block; line-height: 1; }

    /* ===== Kartu pembatalan =====
       Pita merah di tepi kiri, bukan bingkai merah seluruh kartu: yang perlu
       ditandai urgensinya, bukan seluruh isinya. */
    .pd-kartu-batal { border-left: 4px solid #dc2626; }

    /* ===== Keterangan kecil di bawah isian ===== */
    .pd-bantu { color: var(--dsb-redup); font-size: .76rem; margin-top: 5px; }

    /* Lencana keadaan pembayaran: bentuk Bootstrap dilepas, warnanya tetap
       dari .orcha-lencana-bayar-* supaya satu arti satu warna di seluruh
       panel Orcha. */
    .pd-lencana-bayar { border-radius: 999px; padding: 5px 10px; font-size: .72rem; font-weight: 700; }

    /* Dua tombol kaki kartu peserta: setengah lebar masing-masing, ditambat
       ke dasar kartu lewat .dsb-kartu-isi.is-tegak. */
    .pd-kaki-peserta { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; margin-top: 16px; }
    .pd-kaki-peserta > * { width: 100%; justify-content: center; }
    @media (max-width: 575.98px) { .pd-kaki-peserta { grid-template-columns: 1fr; } }

    /* Ubin ringkasan uang sama tinggi walau kalimatnya beda panjang. */
    .pd-detail .orcha-ringkas { height: 100%; }

    /* Kalimat tenggat: paragraf biasa, bukan baris lentur.

       .orcha-tenggat dipasang inline-flex ber-gap, jadi tiap potongan kalimat
       jadi benda terpisah yang dibungkus sendiri-sendiri. Di layar sempit "Rp"
       berdiri sendirian satu baris dengan jarak ganjil di kanannya, dan
       kalimatnya tidak lagi terbaca sebagai kalimat. */
    .pd-detail .orcha-tenggat { display: block; line-height: 1.65; }
    .pd-detail .orcha-tenggat > i { margin-right: 5px; }

    /* ===== Pita jemputan =====
       Selebar kartu, berubin. Satu ubin per titik, melebar sendiri mengisi
       baris supaya tidak ada sisa ruang di ujung kanan. */
    .pd-jemput { margin-top: clamp(16px, 2vw, 22px); padding-top: clamp(16px, 2vw, 22px); border-top: 1px solid #f1f5f9; }
    .pd-jemput-rak { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 10px; }
    .pd-jemput-rak > .orcha-jemput-blok { margin: 0; }

    /* ===== Kaki kartu pembayaran =====
       Tiga angka penutup, ditambat ke dasar kartu oleh is-tegak. */
    .pd-kaki-bayar {
        margin-top: 16px; padding: 13px 15px; border-radius: 13px;
        background: #f8fafc; border: 1px solid var(--dsb-tepi);
        display: flex; flex-direction: column; gap: 7px; font-size: .82rem;
    }
    .pd-kaki-bayar .baris { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; }
    .pd-kaki-bayar .baris > span:first-child { color: var(--dsb-redup); }
    .pd-kaki-bayar strong { font-variant-numeric: tabular-nums; }
    .pd-kaki-bayar .is-redup { font-size: .76rem; color: #94a3b8; }
    .pd-kaki-bayar .is-redup em { font-style: normal; }
    .pd-kaki-bayar .is-lunas strong { color: #15803d; }
    .pd-kaki-bayar .is-kurang strong { color: #b45309; }
    .pd-kaki-bayar .baris + .baris:last-child { border-top: 1px dashed #e2e8f0; padding-top: 8px; }

    /* ===== Pita: kartu setinggi satu baris =====
       Dipakai bagian yang isinya memang cuma satu kalimat. Kartu penuh untuk
       satu kalimat meninggalkan petak putih sebesar dirinya sendiri. */
    .pd-pita { display: flex; align-items: center; gap: 13px; flex-wrap: wrap; }
    .pd-pita > .dsb-ikon { flex-shrink: 0; }
    .pd-pita-teks { flex: 1 1 260px; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
    .pd-pita-teks > strong { font-size: .92rem; color: var(--dsb-tinta); }
    .pd-pita-teks > span { font-size: .82rem; color: var(--dsb-redup); line-height: 1.5; }
    .pd-pita-teks b { color: var(--dsb-tinta); }

    /* Kaki kartu peserta: penunjuk arah + dua tombol, ditambat ke dasar. */
    .pd-kaki { margin-top: 18px; }
    .pd-penunjuk {
        display: flex; gap: 8px; margin: 0 0 12px;
        color: var(--dsb-redup); font-size: .8rem; line-height: 1.55;
    }
    .pd-penunjuk > i.bi { color: #7c3aed; line-height: 1.45; flex-shrink: 0; }
    .pd-penunjuk strong { color: var(--dsb-tinta); }

    /* Tombol yang tidak bisa ditekan. Tetap terbaca, tetapi tidak menjanjikan
       apa-apa: abu rata, tanpa angkatan saat disentuh. */
    .dsb-tombol.is-mati {
        background: #f8fafc; color: #94a3b8; border-color: var(--dsb-tepi);
        cursor: default; box-shadow: none;
    }
    .dsb-tombol.is-mati i.bi { color: #cbd5e1; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-tombol.is-mati:hover { transform: none; border-color: var(--dsb-tepi); color: #94a3b8; }
    }
    </style>

    <div class="dsb pd-detail">

        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        @if (empty($pendaftaran))
            <div class="dsb-kartu">
                <div class="dsb-kartu-isi dsb-kosong">
                    <span class="dsb-kosong-ikon"><i class="bi bi-person-x"></i></span>
                    <p class="dsb-kosong-judul">Data pendaftaran ini belum bisa ditampilkan</p>
                    <p class="dsb-kosong-ket">
                        Sambungan ke Orcha sedang tidak bisa dipakai, atau pendaftarannya sudah dihapus.
                    </p>
                    <a href="{{ route('admin.orcha.pendaftaran') }}" class="dsb-tombol is-lembut mt-3"
                        style="--ikon: #64748b">
                        <i class="bi bi-arrow-left"></i><span>Kembali ke daftar</span>
                    </a>
                </div>
            </div>
        @else

            {{-- ============ KEPALA ============

                 Mengikuti detail Pesanan Toko: nama besar, baris tanggal
                 berikon, lalu deret lencana keadaan — yang dicari mata saat
                 layar dibuka adalah "sudah sampai mana orang ini", bukan
                 judul halamannya.

                 Identitas dan bilah tindakan DISATUKAN. Dulu keduanya dua
                 kartu bertumpuk, dan yang atas menyisakan sepertiga lebar
                 kosong di kanan nama. --}}
            <header class="dsb-hero">
                <div class="dsb-hero-teks">
                    <h1 class="dsb-salam">{{ $pendaftaran['nama'] }}</h1>
                    <p class="dsb-hero-ket">
                        <span class="d-block">
                            <i class="bi bi-calendar3 me-1"></i>Mendaftar
                            {{ \Carbon\Carbon::parse($pendaftaran['dibuat_pada'])->locale('id')->translatedFormat('l, d F Y · H:i') }}
                        </span>
                        <span class="d-block pd-lencana-kepala">
                            <span class="orcha-kode">{{ $pendaftaran['kode'] }}</span>

                            @if ($berangkat)
                                <span class="dsb-lencana {{ $sisaHari !== null && $sisaHari < 0 ? 'is-abu' : ($sisaHari !== null && $sisaHari <= $hariPelunasan ? 'is-merah' : 'is-biru') }}">
                                    <i class="bi bi-calendar-event"></i>
                                    @if ($sisaHari > 0)
                                        Berangkat {{ $berangkat->translatedFormat('d M Y') }} · {{ $sisaHari }} hari lagi
                                    @elseif ($sisaHari === 0)
                                        Berangkat hari ini
                                    @else
                                        Sudah berangkat {{ $berangkat->translatedFormat('d M Y') }}
                                    @endif
                                </span>
                            @endif

                            {{-- Keadaan pembayaran disebut di kepala, bukan cuma
                                 di kartu biaya jauh di bawah: itu pertanyaan
                                 pertama yang dibawa orang saat membuka halaman
                                 ini. --}}
                            @if ($lunas)
                                <span class="dsb-lencana is-hijau"><i class="bi bi-check-circle-fill"></i>Sudah lunas</span>
                            @elseif ($tenggatLewat)
                                <span class="dsb-lencana is-merah">
                                    <i class="bi bi-exclamation-octagon-fill"></i>Lewat tanggal pelunasan
                                </span>
                            @elseif (($tagihan['sisa'] ?? 0) > 0)
                                <span class="dsb-lencana is-kuning">
                                    <i class="bi bi-cash-coin"></i>Kurang {{ $tagihan['sisa_teks'] ?? '' }}
                                </span>
                            @endif
                        </span>
                    </p>
                </div>

                <div class="dsb-hero-aksi">
                    <a href="{{ route('admin.orcha.pendaftaran') }}" wire:navigate
                        class="dsb-tombol is-lembut" style="--ikon: #64748b">
                        <i class="bi bi-arrow-left"></i><span>Kembali</span>
                    </a>
                    <button type="button" class="dsb-tombol is-aman" onclick="orchaBukaLembar('pilihanWa')"
                        title="Kirim pesan siap pakai lewat WhatsApp — nominalnya terisi sendiri">
                        <i class="bi bi-whatsapp"></i><span>Kirim WhatsApp</span>
                    </button>
                </div>
            </header>

            @include('livewire.pages.admin.orcha.partials.galat-dsb')

            {{-- ============ BILAH PERKAKAS ============

                 Satu baris: berkas yang bisa diunduh di kiri, keadaan
                 pendaftaran di kanan. Tombolnya menyebut APA yang didapat,
                 bukan nama berkasnya — "Manifes PDF" adalah kata kami. --}}
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi pd-perkakas">
                        <div class="pd-perkakas-kiri">
                            {{-- Jaring pengaman saat surat tidak sampai: admin bisa
                                 mengunduh kwitansi yang sama persis dengan yang
                                 dikirim ke pelanggan, lalu meneruskannya lewat
                                 WhatsApp. Tidak menuntut izin data kesehatan —
                                 isinya biaya. --}}
                            <a href="{{ route('admin.orcha.pendaftaran.kwitansi', $pendaftaranId) }}"
                                class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                                title="Bukti pembayaran yang sama dengan yang dikirim ke pelanggan">
                                <i class="bi bi-receipt"></i><span>Bukti Pembayaran</span>
                            </a>

                            {{-- Dua berkas untuk dua pembaca: Excel untuk kantor,
                                 PDF untuk tour leader di lapangan. Keduanya memuat
                                 data kesehatan, jadi ikut dijaga izin yang sama. --}}
                            @if (auth()->user()->hasPermission('view_orcha_kesehatan'))
                                <a href="{{ route('admin.orcha.pendaftaran.pdf', $pendaftaranId) }}"
                                    class="dsb-tombol is-lembut" style="--ikon: #dc2626"
                                    title="Daftar peserta untuk dibawa tour leader di lapangan">
                                    <i class="bi bi-filetype-pdf"></i><span>Daftar Peserta (PDF)</span>
                                </a>
                                <a href="{{ route('admin.orcha.pendaftaran.excel', $pendaftaranId) }}"
                                    class="dsb-tombol is-lembut" style="--ikon: #16a34a"
                                    title="Data lengkap untuk diolah di kantor">
                                    <i class="bi bi-file-earmark-spreadsheet"></i><span>Data Lengkap (Excel)</span>
                                </a>
                            @endif

                            {{-- Jalan ke jejak pesanan INI, bukan ke seluruh jejak.
                                 Pertanyaan "siapa yang mengubah nominalnya?" muncul
                                 saat admin sedang menatap pesanan yang bermasalah,
                                 bukan saat ia kebetulan membuka halaman audit. --}}
                            <a href="{{ route('admin.orcha.jejak-audit', ['cari' => $pendaftaran['kode']]) }}"
                                class="dsb-tombol is-lembut" style="--ikon: #7c3aed"
                                title="Siapa mengubah apa pada pendaftaran ini">
                                <i class="bi bi-clock-history"></i><span>Riwayat Perubahan</span>
                            </a>

                            {{-- Mencatat pembayaran adalah TINDAKAN, bukan keterangan
                                 yang menempel pada ringkasan biaya. Disembunyikan saat
                                 sudah lunas atau batal: mencatat pembayaran pada
                                 pesanan yang selesai hampir selalu salah orang. --}}
                            @if (! $lunas && ($pendaftaran['status'] ?? '') !== 'batal')
                                <button type="button" wire:click="bukaFormulirBayar"
                                    class="dsb-tombol is-utama"
                                    title="Untuk transfer yang dikabari lewat WhatsApp dan sudah Anda cocokkan dengan mutasi rekening">
                                    <i class="bi bi-cash-coin"></i><span>Catat Uang Masuk</span>
                                </button>
                            @endif
                        </div>

                        {{-- Keadaan di ujung kanan, berlabel. Satu kotak putih
                             bertuliskan "DP Masuk" tidak memberi tahu apakah itu
                             kabar baik atau pekerjaan yang menunggu, jadi warnanya
                             mengikuti keadaannya. --}}
                        <div class="pd-perkakas-kanan">
                            <span class="orcha-label-kecil">Keadaan</span>
                            @if ($pilihanStatus === [])
                                <span class="dsb-lencana is-abu"
                                    title="Daftar pilihan belum bisa diambil dari Orcha, jadi keadaannya belum bisa diubah dari sini.">
                                    <i class="bi bi-wifi-off"></i>
                                    {{ $pendaftaran['status_label'] ?? $pendaftaran['status'] }}
                                </span>
                            @else
                                <select class="dsb-isian pd-pilih-status status-{{ $pendaftaran['status'] }}"
                                    wire:change="ubahStatus($event.target.value)">
                                    @foreach ($pilihanStatus as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected($pendaftaran['status'] === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ RINGKASAN UANG ============ --}}
            @if ($tagihan)
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0ea5e9"><i class="bi bi-receipt"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Ringkasan Uang</h2>
                                <span class="dsb-kartu-sub">Berapa yang ditagih, berapa yang sudah masuk, berapa yang masih ditunggu</span>
                            </span>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        <div class="row g-3">
                            @foreach ([
                                ['Total tagihan', $tagihan['total_teks'], '', 'bi-receipt'],
                                ['Sudah dibayar', $tagihan['sudah_teks'], 'lunas', 'bi-cash-coin'],
                                ['Belum dibayar', $tagihan['sisa_teks'], ($tagihan['lunas'] ?? false) ? 'lunas' : 'sisa', 'bi-hourglass-split'],
                                /* Dua angka yang memang berbeda, dan keduanya disebut.

                                   "42 orang" pada rombongan yang dua di antaranya guru
                                   pendamping gratis membuat siapa pun mengalikannya dengan
                                   harga satuan dan mendapat angka yang tidak cocok dengan
                                   total tagihan di sebelahnya — lalu menyimpulkan salah
                                   satunya salah. */
                                ['Peserta', ($pendaftaran['pendamping_gratis'] ?? 0) > 0
                                    ? $pendaftaran['jumlah_peserta'].' orang · '.$pendaftaran['peserta_dibayar'].' ditagih'
                                    : $pendaftaran['jumlah_peserta'].' orang', '', 'bi-people'],
                            ] as [$label, $nilai, $kelas, $ikon])
                                <div class="col-6 col-lg-3">
                                    <div class="orcha-ringkas {{ $kelas }}">
                                        <div class="orcha-label-kecil">
                                            <i class="bi {{ $ikon }}"></i> {{ $label }}
                                        </div>
                                        <div class="angka">{{ $nilai }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between" style="font-size:.78rem">
                                <span class="text-muted">Sudah terbayar</span>
                                <span class="fw-bold text-dark">{{ $persenBayar }}%</span>
                            </div>
                            <div class="orcha-palang mt-1 {{ $lunas ? 'lunas' : '' }}">
                                <span style="width: {{ $persenBayar }}%"></span>
                            </div>

                            {{-- Persen saja belum menjawab pertanyaan berikutnya: kapan
                                 sisanya harus masuk. Tenggatnya dihitung dari aturan Orcha
                                 (H-{{ $hariPelunasan }}), bukan angka yang ditulis di sini. --}}
                            <div class="mt-2" style="font-size:.8rem">
                                @if ($lunas)
                                    <span class="orcha-tenggat lunas">
                                        <i class="bi bi-check-circle-fill"></i> Sudah lunas — tidak ada sisa yang perlu ditagih.
                                    </span>
                                @elseif ($tenggat)
                                    <span class="orcha-tenggat {{ $tenggatLewat ? 'lewat' : '' }}">
                                        <i class="bi bi-{{ $tenggatLewat ? 'exclamation-triangle-fill' : 'clock-history' }}"></i>
                                        Sisa <strong>{{ $tagihan['sisa_teks'] }}</strong>
                                        {{ $tenggatLewat ? 'sudah melewati batas pelunasan' : 'dilunasi paling lambat' }}
                                        <strong>{{ $tenggat->translatedFormat('d M Y') }}</strong>
                                        (H-{{ $hariPelunasan }} sebelum berangkat)
                                    </span>
                                @elseif (! $berangkat)
                                    <span class="text-muted">
                                        Sisa <strong>{{ $tagihan['sisa_teks'] }}</strong> — batas pelunasan
                                        mengikuti tanggal berangkat, yang belum dijadwalkan.
                                    </span>
                                @else
                                    {{-- Tanggalnya ada, tapi aturan pelunasan tidak terbaca
                                         (rujukan dari Orcha gagal diambil). Kalimatnya berhenti
                                         di yang memang diketahui: menuduh jadwalnya belum ada
                                         padahal tanggalnya tertulis di layar yang sama membuat
                                         admin meragukan seluruh halaman. --}}
                                    <span class="text-muted">
                                        Sisa yang perlu ditagih <strong>{{ $tagihan['sisa_teks'] }}</strong>.
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Mencatat pembayaran yang diterima admin sendiri.

                             Private trip dan study tour tidak lewat formulir
                             konfirmasi publik: panitia mentransfer lalu mengabari
                             lewat WhatsApp, kadang tanpa tangkapan layar. Yang
                             memastikan uangnya masuk adalah admin yang membuka
                             mutasi rekening — dan sebelum ini pemeriksaan itu
                             tidak punya tempat pulang.

                             Disembunyikan saat sudah lunas: tombol yang selalu ada
                             tetapi tidak selalu berguna hanya menambah benda yang
                             harus diabaikan mata, dan mencatat pembayaran pada
                             pesanan yang sudah lunas hampir selalu salah orang. --}}
                        @if (! $lunas && ($pendaftaran['status'] ?? '') !== 'batal')
                            {{-- Tombol pembukanya kini di bilah perkakas atas; yang
                                 tinggal di sini formulirnya, tepat di bawah angka yang
                                 sedang diubahnya. --}}
                            <div @class(['pt-3 mt-3 border-top' => $bukaBayar])>
                                @if ($bukaBayar)
                                    <div class="orcha-bagian-kepala">
                                        <div class="orcha-bagian-nomor"><i class="bi bi-cash-coin"></i></div>
                                        <div>
                                            <div class="orcha-bagian-judul">Catat pembayaran diterima</div>
                                            <div class="orcha-bagian-sub">
                                                Langsung terhitung sebagai diterima — yang dicatat di sini
                                                adalah hasil pemeriksaan Anda sendiri, bukan klaim pelanggan
                                                yang masih perlu dicek. Tercatat di jejak audit atas nama Anda.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-lg-4">
                                            <label class="dsb-label">Nominal <span class="dsb-wajib">*</span></label>
                                            {{-- .orcha-rupiah menaruh "Rp" DI DALAM kotaknya —
                                                 pola yang sudah dipakai seluruh isian uang di
                                                 lemon. Kotak "Rp" terpisah membuat layar ini
                                                 satu-satunya yang berbeda bentuknya. --}}
                                            <div class="orcha-rupiah">
                                                <input type="text" inputmode="numeric"
                                                    wire:model.blur="bayar.nominal"
                                                    value="{{ $bayar['nominal'] }}" placeholder="5.000.000"
                                                    class="dsb-isian @error('bayar.nominal') is-galat @enderror">
                                            </div>
                                            @error('bayar.nominal')
                                                <div class="dsb-galat">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-lg-4">
                                            <label class="dsb-label">Tanggal transfer <span class="dsb-wajib">*</span></label>
                                            <input type="date" wire:model="bayar.tanggal_transfer"
                                                value="{{ $bayar['tanggal_transfer'] }}"
                                                max="{{ now()->toDateString() }}"
                                                class="dsb-isian @error('bayar.tanggal_transfer') is-galat @enderror">
                                            @error('bayar.tanggal_transfer')
                                                <div class="dsb-galat">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-lg-4">
                                            <label class="dsb-label">Jenis <span class="dsb-wajib">*</span></label>
                                            <select wire:model="bayar.jenis" class="dsb-isian">
                                                @foreach ($pilihanJenisBayar as $kunci => $label)
                                                    <option value="{{ $kunci }}" @selected($bayar['jenis'] === $kunci)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 col-lg-4">
                                            <label class="dsb-label">Bank pengirim <span class="dsb-wajib">*</span></label>
                                            <input type="text" maxlength="60" wire:model="bayar.bank_pengirim"
                                                value="{{ $bayar['bank_pengirim'] }}" placeholder="BCA"
                                                class="dsb-isian @error('bayar.bank_pengirim') is-galat @enderror">
                                            @error('bayar.bank_pengirim')
                                                <div class="dsb-galat">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-lg-8">
                                            <label class="dsb-label">Atas nama pengirim <span class="dsb-wajib">*</span></label>
                                            <input type="text" maxlength="120" wire:model="bayar.atas_nama_pengirim"
                                                value="{{ $bayar['atas_nama_pengirim'] }}"
                                                class="dsb-isian @error('bayar.atas_nama_pengirim') is-galat @enderror">
                                            {{-- Keduanya yang dipakai mencocokkan dengan mutasi
                                                 rekening saat ada yang mempersoalkan. Catatan
                                                 uang tanpa asal-usulnya cuma angka yang harus
                                                 dipercaya. --}}
                                            <div class="pd-bantu">Sesuai yang tertera di mutasi rekening.</div>
                                            @error('bayar.atas_nama_pengirim')
                                                <div class="dsb-galat">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-lg-8">
                                            <label class="dsb-label">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                                            <input type="text" maxlength="200" wire:model="bayar.catatan"
                                                value="{{ $bayar['catatan'] }}" class="dsb-isian"
                                                placeholder="Cicilan ke-2, dana komite tahap 1, ...">
                                        </div>

                                        {{-- Bukti transfer, dan sengaja TIDAK wajib.

                                             Di formulir publik bukti wajib karena tanpa gambar
                                             tidak ada yang bisa dicek. Di sini yang mencatat
                                             justru orang yang sudah mengecek — dan sebagian
                                             panitia memang cuma menulis "sudah ditransfer ya"
                                             tanpa tangkapan layar apa pun.

                                             Mewajibkannya berarti pembayaran yang nyata tidak
                                             bisa dicatat karena kurang sebuah gambar, dan yang
                                             terjadi berikutnya bukan admin mengejar gambarnya
                                             melainkan pembayarannya tidak dicatat sama sekali. --}}
                                        <div class="col-12 col-lg-4">
                                            <label class="dsb-label">
                                                Bukti transfer <span class="text-muted fw-normal">(opsional)</span>
                                            </label>

                                            <input type="file" accept="image/*" wire:model="buktiBayar"
                                                class="dsb-isian @error('buktiBayar') is-galat @enderror">

                                            <div wire:loading wire:target="buktiBayar" class="pd-bantu">
                                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                                    aria-hidden="true"></span>Mengunggah…
                                            </div>

                                            @error('buktiBayar')
                                                <div class="dsb-galat">{{ $message }}</div>
                                            @else
                                                <div wire:loading.remove wire:target="buktiBayar" class="pd-bantu">
                                                    Tangkapan layar mutasi rekening, bila ada.
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-end pt-3 mt-3 border-top">
                                        <button type="button" wire:click="tutupFormulirBayar"
                                            class="orcha-btn orcha-btn-lembut">Batal</button>

                                        <button type="button" wire:click="catatBayar"
                                            wire:loading.attr="disabled" wire:target="catatBayar"
                                            class="orcha-btn orcha-btn-utama">
                                            <span wire:loading.remove wire:target="catatBayar">
                                                <i class="bi bi-check2-circle"></i>
                                                Catat pembayaran
                                            </span>
                                            <span wire:loading wire:target="catatBayar">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                                    aria-hidden="true"></span>Menyimpan…
                                            </span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if ($pembatalan)
            @php
                /*
                 | Rekeningnya HANYA ditunjukkan bila memang ada yang dikirim.
                 |
                 | Dulu ia selalu tampil sebagai "Rekening pengembalian: ...".
                 | Pada pengajuan yang potongannya sebesar seluruh pembayaran —
                 | kembali Rp 0 — kalimat itu terbaca sebagai perintah
                 | mentransfer ke sana, padahal di fitur Pembatalan memang tidak
                 | ada yang dikembalikan. Admin yang awam mengerjakannya.
                 |
                 | Yang menentukan ANGKANYA, bukan ada tidaknya rekening.
                 */
                $perkiraan = $pembatalan['perkiraan'] ?? null;
                $adaKembali = ($perkiraan['kembali'] ?? 0) > 0;
                $sudahDikirim = ($pembatalan['status'] ?? '') === 'dana_dikirim';
            @endphp

            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12 pd-kartu-batal">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #dc2626"><i class="bi bi-x-octagon"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Ada Pengajuan Pembatalan</h2>
                                <span class="dsb-kartu-sub">Diajukan dari halaman Pembatalan — yang perlu Anda putuskan ada di bawah</span>
                            </span>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        <div class="orcha-bagian-kepala mb-3">
                            <div class="orcha-bagian-nomor batal"><i class="bi bi-x-octagon"></i></div>
                            <div class="flex-grow-1">
                                <div class="orcha-bagian-judul d-flex flex-wrap align-items-center gap-2">
                                    Ada pengajuan pembatalan
                                    <span class="orcha-status-batal" data-status="{{ $pembatalan['status'] }}">
                                        {{ $pembatalan['status_label'] ?? $pembatalan['status'] }}
                                    </span>
                                </div>
                                <div class="orcha-bagian-sub">
                                    {{ $pembatalan['jumlah_dibatalkan'] }} peserta ·
                                    {{ $pembatalan['alasan_label'] }} · diajukan
                                    {{ \Carbon\Carbon::parse($pembatalan['dibuat_pada'])->locale('id')->translatedFormat('j M Y') }}
                                    oleh {{ $pembatalan['nama_pemohon'] }}
                                </div>
                            </div>
                        </div>

                        @if ($pembatalan['penjelasan'])
                            <div class="orcha-alasan orcha-alasan-tenang mb-3">
                                <span class="orcha-label-kecil mb-0">
                                    <i class="bi bi-chat-quote"></i> Penjelasan pemohon
                                </span>
                                <div class="mt-1">"{{ $pembatalan['penjelasan'] }}"</div>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                @include('livewire.pages.admin.orcha.partials.medan', [
                                    'label' => 'Sudah dibayar',
                                    'nilai' => $perkiraan['dibayar_teks'] ?? null,
                                ])
                            </div>
                            <div class="col-12 col-md-4">
                                @include('livewire.pages.admin.orcha.partials.medan', [
                                    'label' => 'Potongan' . (isset($perkiraan['persen']) ? ' (' . $perkiraan['persen'] . '%)' : ''),
                                    'nilai' => $perkiraan['potongan_teks'] ?? null,
                                ])
                            </div>
                            <div class="col-12 col-md-4">
                                @include('livewire.pages.admin.orcha.partials.medan', [
                                    'label' => 'Dikembalikan',
                                    'nilai' => $perkiraan['kembali_teks'] ?? null,
                                ])
                            </div>
                        </div>

                        {{-- Kalimatnya menyebut PERBUATANNYA, bukan sekadar angka:
                             yang membaca kartu ini sedang memutuskan mau berbuat apa. --}}
                        @if (! $adaKembali)
                            <div class="orcha-alasan orcha-alasan-tenang mt-3">
                                <span class="orcha-label-kecil mb-0">
                                    <i class="bi bi-slash-circle"></i> Tidak ada dana yang dikembalikan
                                </span>
                                <div class="mt-1">
                                    Potongannya sebesar seluruh pembayaran yang sudah masuk, jadi
                                    <strong>tidak ada yang perlu ditransfer</strong>. Rekening pemohon
                                    sengaja tidak ditampilkan supaya tidak terlanjur dikirimi.
                                </div>
                            </div>
                        @elseif ($sudahDikirim)
                            <div class="orcha-alasan orcha-alasan-tenang mt-3">
                                <span class="orcha-label-kecil mb-0">
                                    <i class="bi bi-check2-circle"></i> Dana sudah ditandai terkirim
                                </span>
                                <div class="mt-1">
                                    Dikirim ke <strong>{{ $pembatalan['rekening'] }}</strong>.
                                    Tidak perlu ditransfer lagi.
                                </div>
                            </div>
                        @else
                            <div class="orcha-alasan orcha-alasan-sedang mt-3">
                                <span class="orcha-label-kecil mb-0">
                                    <i class="bi bi-send-exclamation"></i> Perlu ditransfer ke pemohon
                                </span>
                                <div class="mt-1">
                                    <strong>{{ $perkiraan['kembali_teks'] }}</strong> ke
                                    <strong>{{ $pembatalan['rekening'] }}</strong>.
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('admin.orcha.pembatalan.detail', $pembatalan['id']) }}" wire:navigate
                            class="orcha-btn orcha-btn-lembut orcha-aksi-sewa mt-3">
                            <i class="bi bi-box-arrow-up-right"></i> Buka pengajuannya
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- ============ PEMESAN & PERJALANAN ============

                 Dua kartu yang dulu bertumpuk di dua kolom berbeda, kini satu.
                 Keduanya berisi keterangan tetap yang dibaca sekali di awal, dan
                 masing-masing terlalu pendek untuk berdiri sendiri — kartu yang
                 isinya empat baris pada kolom setinggi daftar peserta meninggalkan
                 petak putih sepanjang setengah layar. --}}
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-person-vcard"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Pemesan & Perjalanan</h2>
                                <span class="dsb-kartu-sub">Siapa yang dihubungi, dan rombongan ini berangkat ke mana</span>
                            </span>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        <div class="pd-duo">
                            <div class="pd-blok">
                                <div class="pd-blok-judul"><i class="bi bi-person-lines-fill"></i> Yang memesan</div>

                                <div class="row g-3">
                                    @foreach ([
                                        ['Nama lengkap', $pendaftaran['nama'], null],
                                        ['Nomor WhatsApp', $pendaftaran['whatsapp'], $wa($pendaftaran['whatsapp'])],
                                        ['Email', $pendaftaran['email'] ?: '—', $pendaftaran['email'] ? 'mailto:' . $pendaftaran['email'] : null],
                                        ['Keadaan sekarang', $pendaftaran['status_label'], null],
                                    ] as [$label, $nilai, $tautan])
                                        <div class="col-12 col-md-6">
                                            <div class="orcha-label-kecil">{{ $label }}</div>
                                            @if ($tautan)
                                                <a href="{{ $tautan }}" target="_blank" rel="noopener"
                                                    class="fw-bold text-decoration-none">{{ $nilai }}</a>
                                            @else
                                                <div class="fw-bold">{{ $nilai }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Catatan pemesan dan catatan sistem berbagi SATU kolom
                                     di basis data, dan itu tidak bisa diubah dari sini.

                                     Yang bisa diubah labelnya. Sebelum ini seluruh isinya
                                     diberi judul "Catatan dari pemesan" — termasuk baris
                                     "[Sistem] Kursi dilepas otomatis … tidak ada pembayaran
                                     dalam 72 jam" yang ditulis LepaskanKursiTertahan. Admin
                                     yang membuka pemesanan batal lalu membaca alasan
                                     pembatalan seolah pelanggan sendiri yang mengetiknya.

                                     Dipisah di sini, bukan di Orcha: memecah kolomnya berarti
                                     memindahkan data lama, dan yang rusak sekarang cuma
                                     keterangannya. --}}
                                @php
                                    $barisCatatan = preg_split('/\r?\n/', (string) $pendaftaran['catatan']);
                                    $catatanSistem = array_values(array_filter(
                                        $barisCatatan,
                                        fn ($b) => str_starts_with(trim($b), '[Sistem]')
                                    ));
                                    $catatanPemesan = trim(implode("\n", array_filter(
                                        $barisCatatan,
                                        fn ($b) => ! str_starts_with(trim($b), '[Sistem]')
                                    )));
                                @endphp

                                @if ($catatanPemesan !== '')
                                    <div class="mt-3 p-3 rounded-3 bg-light">
                                        <div class="orcha-label-kecil">Catatan dari pemesan</div>
                                        <div style="font-size:.9rem">{{ $catatanPemesan }}</div>
                                    </div>
                                @endif

                                @if ($catatanSistem !== [])
                                    {{-- Warna berbeda, dan itu perlu: yang membacanya sedang
                                         mencari sebab, dan sebab yang ditulis mesin tidak boleh
                                         tertukar dengan kalimat orang. --}}
                                    <div class="orcha-catatan-sistem mt-3">
                                        <div class="orcha-label-kecil">
                                            <i class="bi bi-robot"></i> Dicatat sistem
                                        </div>
                                        @foreach ($catatanSistem as $baris)
                                            <div style="font-size:.86rem">
                                                {{ trim(str_replace('[Sistem]', '', $baris)) }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="pd-blok">
                                <div class="pd-blok-judul"><i class="bi bi-suitcase-lg"></i> Perjalanannya</div>

                                <div class="d-flex flex-column gap-3">
                                    @foreach ([
                                        ['Paket', $pendaftaran['paket']['nama'] ?: '—', 'bi-map'],
                                        ['Tanggal berangkat', $berangkat
                                            ? $berangkat->locale('id')->translatedFormat('l, d F Y')
                                                . ($sisaHari > 0 ? ' · ' . $sisaHari . ' hari lagi' : ($sisaHari === 0 ? ' · hari ini' : ''))
                                            : 'Menyusul', 'bi-calendar-event'],
                                        ['Titik jemput rombongan', $pendaftaran['titik_jemput'] ?: 'Dikonfirmasi tim', 'bi-geo-alt'],
                                    ] as [$label, $nilai, $ikon])
                                        <div class="d-flex gap-3">
                                            <div class="orcha-ikon-kotak bg-gradient-blue">
                                                <i class="bi {{ $ikon }}"></i>
                                            </div>
                                            <div>
                                                <div class="orcha-label-kecil">{{ $label }}</div>
                                                <div class="fw-bold">{{ $nilai }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Siapa dijemput di mana, selebar kartu.

                             Dulu ia tinggal di kolom kanan dan memanjangkannya sendirian;
                             kolom kiri yang cuma empat baris lalu menyisakan petak putih
                             setengah kartu. Satu pita mendatar memakai ruang yang sama
                             tanpa meninggalkan lubang — dan memang begitu cara sopir
                             membacanya: per titik, bukan per orang. --}}
                        @if (! empty($pendaftaran['jemput_per_titik']))
                            <div class="pd-jemput">
                                <div class="pd-blok-judul"><i class="bi bi-sign-turn-right"></i> Siapa dijemput di mana</div>
                                <div class="pd-jemput-rak">
                                    @foreach ($pendaftaran['jemput_per_titik'] as $titik => $orang)
                                        <div class="orcha-jemput-blok">
                                            <div class="orcha-jemput-judul">
                                                <i class="bi bi-geo-alt-fill"></i>
                                                {{ $titik }}
                                                <span class="orcha-jemput-jumlah">{{ count($orang) }} orang</span>
                                            </div>
                                            <div class="orcha-jemput-nama">{{ implode(' · ', $orang) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============ PESERTA & PEMBAYARAN ============

                 Berdampingan pada satu kisi, jadi kedua kartu sama tinggi dan
                 tepi bawahnya segaris. Tombol peserta ditambat ke dasar kartunya
                 (is-tegak) supaya tidak menggantung di tengah. --}}
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-7">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-people-fill"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Peserta & Titik Jemput</h2>
                                <span class="dsb-kartu-sub">Nama yang berangkat, dari mana dijemput, dan siapa yang belum mengisi kesehatannya</span>
                            </span>
                        </div>
                        <span class="dsb-lencana {{ ($pendaftaran['kesehatan_lengkap'] ?? false) ? 'is-hijau' : 'is-kuning' }}">
                            <i class="bi bi-heart-pulse"></i>
                            {{ $pendaftaran['kesehatan_terisi'] ?? 0 }} dari {{ $pendaftaran['jumlah_peserta'] }} sudah mengisi kesehatan
                        </span>
                    </div>
                    <div class="dsb-kartu-isi is-tegak">

                    {{-- Tautan pengisian, siap dikirim ulang.

                         Yang paling sering diminta bukan pengiriman pertama
                         melainkan pengiriman ULANG, berhari-hari kemudian,
                         kepada peserta yang belum juga mengisi. Sebelum ini
                         admin merangkainya sendiri dari ingatan — dan kode
                         enam huruf acak yang salah satu hurufnya membawa
                         orang ke halaman yang menolaknya.

                         Muncul hanya selama masih ada yang belum mengisi:
                         tautan yang tetap terpampang setelah semuanya lengkap
                         cuma menambah benda yang harus diabaikan mata. --}}
                    @if (! ($pendaftaran['kesehatan_lengkap'] ?? false) && ($pendaftaran['tautan_kesehatan'] ?? null))
                        @php
                            $panggil = trim(explode(' ', trim($pendaftaran['nama'] ?? ''))[0] ?? '');

                            $pesanKesehatan = "Halo Kak {$panggil}, mohon tiap peserta mengisi riwayat "
                                . "kesehatan sebelum berangkat ya.\n\n"
                                . 'Kode pemesanan: ' . ($pendaftaran['kode'] ?? '') . "\n"
                                . $pendaftaran['tautan_kesehatan'] . "\n\n"
                                . 'Yang kami butuhkan golongan darah, alergi, dan kontak darurat.';

                            $waKesehatan = 'https://api.whatsapp.com/send?phone='
                                . preg_replace('/^0/', '62', preg_replace('/\D/', '', $pendaftaran['whatsapp'] ?? ''))
                                . '&text=' . rawurlencode($pesanKesehatan);
                        @endphp

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 mb-3 rounded-4"
                            style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="small text-break" style="min-width:0">
                                <div class="text-muted" style="font-size:.72rem">Tautan pengisian riwayat kesehatan</div>
                                <a href="{{ $pendaftaran['tautan_kesehatan'] }}" target="_blank" rel="noopener">
                                    {{ $pendaftaran['tautan_kesehatan'] }}
                                </a>
                            </div>

                            <a href="{{ $waKesehatan }}" target="_blank" rel="noopener"
                                class="orcha-btn orcha-btn-lembut orcha-btn-kecil">
                                <i class="bi bi-whatsapp"></i>
                                Kirim ulang
                            </a>
                        </div>
                    @endif

                    @forelse ($peserta as $satu)
                        @php
                            $sudahIsi = ! in_array(mb_strtolower(trim($satu['nama'] ?? '')), $belumIsi, true);
                            $inisial = collect(explode(' ', trim($satu['nama'] ?? '?')))->filter()->take(2)
                                ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))->implode('');
                        @endphp
                        <div class="orcha-peserta">
                            <span class="orcha-inisial">{{ $inisial ?: '?' }}</span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $satu['nama'] ?: '—' }}</div>
                                <div class="text-muted" style="font-size:.78rem">
                                    <i class="bi bi-geo-alt"></i>
                                    {{ $satu['titik_jemput'] ?: 'Titik jemput belum dipilih' }}
                                </div>
                            </div>
                            @if ($sudahIsi)
                                <span class="orcha-lencana-aman"><i class="bi bi-check-circle-fill"></i> Kesehatan terisi</span>
                            @else
                                <span class="orcha-lencana-awas"><i class="bi bi-clock-history"></i> Belum mengisi</span>
                            @endif
                        </div>
                    @empty
                        {{-- Keadaan ini yang dulu membuat rombongannya hilang dari
                             daftar absen keberangkatan. Sekarang disebutkan apa adanya,
                             lengkap dengan jalan keluarnya di tombol bawah. --}}
                        <div class="orcha-kosong-peserta">
                            <i class="bi bi-person-dash"></i>
                            <div>
                                <strong>Nama peserta belum didata.</strong>
                                Pendaftaran ini tercatat {{ $pendaftaran['jumlah_peserta'] }} orang,
                                tetapi namanya belum ada satu pun — sehingga rombongan ini
                                belum bisa masuk daftar absen keberangkatan.
                            </div>
                        </div>
                    @endforelse

                    {{-- Kaki kartu: penunjuk arah dan dua tombolnya satu blok, jadi
                         keduanya ditambat bersama ke DASAR kartu (is-tegak). Pada kisi
                         yang menyamakan tinggi kartu, kaki yang mengapung di tengah
                         meninggalkan petak putih di bawahnya. --}}
                    <div class="pd-kaki">
                        {{-- Papan penunjuk untuk yang mencari "ganti peserta": tindakannya
                             ada di halaman daftar peserta, bukan di tombol tersendiri. --}}
                        <p class="pd-penunjuk">
                            <i class="bi bi-arrow-left-right"></i>
                            <span>
                                Ada yang berhalangan dan diganti orang lain? Ganti namanya lewat
                                <strong>{{ $peserta === [] ? 'Isi nama peserta' : 'Ubah nama peserta' }}</strong>.
                                Nama lamanya tetap tersimpan, dan surat pernyataannya muncul di halaman ini.
                            </span>
                        </p>

                        <div class="pd-kaki-peserta">
                            {{-- Nama peserta bukan data kesehatan, jadi tidak dijaga izin
                                 khusus itu: siapa pun yang boleh mengurus pendaftaran boleh
                                 melengkapinya. --}}
                            <a href="{{ route('admin.orcha.pendaftaran.peserta', $pendaftaranId) }}"
                                wire:navigate class="dsb-tombol is-utama">
                                <i class="bi bi-pencil-square"></i>
                                <span>{{ $peserta === [] ? 'Isi nama peserta' : 'Ubah nama peserta' }}</span>
                            </a>

                            @if (! auth()->user()->hasPermission('view_orcha_kesehatan'))
                                <span class="dsb-tombol is-lembut is-mati"
                                    title="Butuh izin khusus untuk membuka data kesehatan">
                                    <i class="bi bi-lock"></i><span>Data kesehatan terkunci</span>
                                </span>
                            @elseif (($pendaftaran['jumlah_riwayat_kesehatan'] ?? 0) > 0)
                                {{-- Halaman tersendiri, bukan popup: isinya panjang, dan
                                     rombongan dua belas orang tidak muat di jendela yang
                                     separuhnya sudah terpakai bingkai. --}}
                                <a href="{{ route('admin.orcha.pendaftaran.kesehatan', $pendaftaranId) }}"
                                    wire:navigate class="dsb-tombol is-lembut" style="--ikon: #e11d48">
                                    <i class="bi bi-heart-pulse"></i>
                                    <span>Lihat data kesehatan ({{ $pendaftaran['jumlah_riwayat_kesehatan'] }})</span>
                                </a>
                            @else
                                <span class="dsb-tombol is-lembut is-mati"
                                    title="Peserta mengisinya sendiri lewat tautan di atas">
                                    <i class="bi bi-info-circle"></i><span>Belum ada yang mengisi kesehatan</span>
                                </span>
                            @endif
                        </div>
                    </div>
                    </div>
                </div>
                <div class="dsb-kartu k-5">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-cash-stack"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Uang Masuk</h2>
                                <span class="dsb-kartu-sub">Tiap transfer yang sudah tercatat</span>
                            </span>
                        </div>
                        <a href="{{ route('admin.orcha.pembayaran', ['cari' => $pendaftaran['kode']]) }}"
                            class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                            title="Halaman pembayaran, sudah tersaring ke pemesanan ini">
                            <i class="bi bi-list-check"></i><span>Buka daftarnya</span>
                        </a>
                    </div>
                    <div class="dsb-kartu-isi is-tegak">

                    @php
                        /*
                         | Total kode unik seluruh pemesanan ini.
                         |
                         | Kode unik TIDAK mengurangi tagihan — ia penanda, bukan
                         | cicilan. Tetapi uangnya nyata masuk ke rekening, dan
                         | selisih antara "uang diterima" dan "masuk ke tagihan"
                         | itulah yang bikin admin ragu saat mencocokkan dengan
                         | dashboard DOKU. Dijumlahkan sekali di sini supaya
                         | selisihnya punya angka, bukan jadi teka-teki.
                         */
                        $totalKodeUnik = collect($pembayaran)
                            ->sum(fn ($b) => (int) ($b['rincian']['kode_unik'] ?? 0));
                        $totalDiterima = collect($pembayaran)
                            ->where('status', 'diterima')
                            ->sum(fn ($b) => (int) ($b['rincian']['total'] ?? $b['nominal']));
                    @endphp

                    @forelse ($pembayaran as $bayar)
                        @php $lewatGerbang = ($bayar['kanal'] ?? 'transfer') === 'doku'; @endphp

                        <div class="d-flex gap-3 pb-3 mb-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                            {{-- Pembayaran gerbang tidak menempati kolom gambar sama
                                 sekali — tidak dengan foto, dan tidak dengan kotak
                                 pengganti.

                                 Kotak "Tanpa bukti" jujur untuk transfer manual yang
                                 buktinya belum dilampirkan: di sana memang ada yang
                                 hilang. Untuk pembayaran gerbang tidak ada yang
                                 hilang, dan kotak apa pun di tempat itu tetap
                                 terbaca sebagai tempat gambar — admin lalu mencari
                                 berkas yang tidak akan pernah ada. Metodenya
                                 disebutkan di barisnya, tempat keterangan memang
                                 seharusnya berada. --}}
                            @if ($lewatGerbang)
                            @elseif ($bayar['bukti'])
                                {{-- Dibuka menumpang di halaman ini, bukan di tab baru:
                                     admin yang sedang mencocokkan pembayaran tidak perlu
                                     kehilangan posisi gulungnya. --}}
                                <img src="{{ $tautanBukti($bayar['bukti']) }}" alt="Bukti transfer"
                                    class="orcha-bukti"
                                    data-bukti="{{ $tautanBukti($bayar['bukti']) }}"
                                    data-bukti-keterangan="{{ $bayar['nominal_formatted'] }} · {{ $bayar['jenis_label'] }} · {{ $bayar['bank_pengirim'] }} a.n. {{ $bayar['atas_nama_pengirim'] }}"
                                    title="Klik untuk memperbesar">
                            @else
                                {{-- Kotak abu kosong menimbulkan pertanyaan sendiri
                                     ("gambarnya gagal dimuat?"), jadi keadaannya
                                     disebutkan apa adanya. --}}
                                <div class="orcha-bukti orcha-bukti-kosong">
                                    <i class="bi bi-image"></i>
                                    <span>Tanpa bukti</span>
                                </div>
                            @endif

                            <div class="flex-grow-1">
                                {{-- Nominal yang MASUK TAGIHAN, dan itu yang paling besar
                                     di baris ini. Untuk pembayaran gerbang ia berbeda
                                     dari uang yang diterima — pecahannya menyusul di
                                     bawah, tetapi yang pertama ditangkap mata harus
                                     angka yang menggerakkan sisa tagihan. --}}
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <span class="orcha-nominal-utama">{{ $bayar['nominal_formatted'] }}</span>
                                    <span class="badge orcha-lencana-bayar-{{ $bayar['status'] }} pd-lencana-bayar">
                                        {{ $bayar['status_label'] }}
                                    </span>
                                </div>

                                {{-- Jenis dipisahkan jadi keping, tanggal dibiarkan
                                     redup.

                                     Keduanya dulu satu baris abu dipisah titik, dan
                                     "Pelunasan · 05 Sep 2026" terbaca sebagai satu
                                     frasa. Padahal jenisnya yang menentukan arti baris
                                     ini — uang muka, angsuran, atau pelunasan
                                     menggerakkan status pesanan dengan cara yang
                                     berbeda — sedangkan tanggalnya cuma keterangan. --}}
                                <div class="orcha-jenis-tanggal">
                                    <span class="jenis jenis-{{ $bayar['jenis'] }}">{{ $bayar['jenis_label'] }}</span>
                                    <span class="tgl">
                                        {{ $bayar['tanggal_transfer'] ? \Carbon\Carbon::parse($bayar['tanggal_transfer'])->locale('id')->translatedFormat('d M Y') : '—' }}
                                    </span>
                                </div>
                                {{-- Untuk pembayaran gerbang, yang berguna bukan
                                     "a.n. siapa" — nama itu nama pemesannya sendiri —
                                     melainkan pecahan angkanya. --}}
                                @if ($lewatGerbang)
                                    {{-- Metodenya naik ke sini, menggantikan kolom
                                         gambar yang sudah tidak ada. --}}
                                    {{-- Metodenya berwarna merek, bukan abu.

                                         Ia satu-satunya keterangan yang menggantikan
                                         kolom gambar yang sudah dihapus — kalau ikut
                                         redup seperti sisanya, kolom itu terbaca
                                         kosong begitu saja. --}}
                                    <div class="orcha-metode-bayar">
                                        <i class="bi bi-lightning-charge-fill"></i>
                                        {{ $bayar['bank_pengirim'] }}
                                    </div>

                                    {{-- Hitungannya diberi warna per peran, bukan
                                         satu blok abu seragam.

                                         Tiga angka berdampingan dalam satu warna
                                         memaksa mata membacanya sebagai kalimat —
                                         padahal yang perlu ditangkap justru
                                         hubungannya: mana yang diterima, mana yang
                                         masuk tagihan, mana yang cuma penanda.
                                         Warna mengerjakan pemisahan itu tanpa
                                         menambah satu kata pun. --}}
                                    @if ($rincian = $bayar['rincian'] ?? null)
                                        {{-- Labelnya "Dibayar", bukan "Diterima".

                                             Lencana status di kanan atas juga berbunyi
                                             DITERIMA, dan artinya lain sama sekali —
                                             yang satu keadaan catatannya, yang satu
                                             uang yang masuk. Dua kata sama dalam satu
                                             baris memaksa pembacanya menebak mana yang
                                             dimaksud. --}}
                                        <div class="orcha-pecah-bayar">
                                            <span class="lbl">Dibayar</span>
                                            <span class="tot">Rp {{ number_format($rincian['total'], 0, ',', '.') }}</span>
                                            <span class="op">=</span>
                                            <span class="pokok">Rp {{ number_format($rincian['pokok'], 0, ',', '.') }}</span>
                                            <span class="op">+</span>
                                            <span class="unik">kode unik {{ number_format($rincian['kode_unik'], 0, ',', '.') }}</span>
                                        </div>
                                        <div class="orcha-nomor-tagihan">
                                            <i class="bi bi-receipt-cutoff"></i> {{ $rincian['invoice'] }}
                                        </div>
                                    @endif
                                @else
                                    {{-- Jalur manual: banknya adalah METODE-nya, jadi
                                         diberi bobot yang sama dengan metode gerbang.
                                         Nama pengirim tetap redup — ia yang dicocokkan
                                         saat ragu, bukan yang dipindai sekilas. --}}
                                    <div class="orcha-metode-bayar bank">
                                        <i class="bi bi-bank"></i>
                                        {{ $bayar['bank_pengirim'] }}
                                        <span class="atas-nama">a.n. {{ $bayar['atas_nama_pengirim'] }}</span>
                                    </div>
                                @endif

                                {{-- Catatan admin diredam menjadi baris terakhir yang
                                     paling ringan. Ia keterangan, bukan angka — dan
                                     baris yang seberat angkanya membuat mata berhenti
                                     di tempat yang salah. --}}
                                @if ($bayar['catatan_admin'])
                                    <div class="orcha-catatan-baris">
                                        <span class="lbl">Catatan admin</span>
                                        {{ $bayar['catatan_admin'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-cash-coin"></i></span>
                            <p class="dsb-kosong-judul">Belum ada uang yang masuk</p>
                            <p class="dsb-kosong-ket">
                                Begitu transfer tercatat — dari pelanggan sendiri maupun yang
                                Anda catat lewat <em>Catat Uang Masuk</em> — barisnya muncul di sini.
                            </p>
                        </div>
                    @endforelse

                    {{-- Kaki kartu: penutup hitungan, ditambat ke DASAR kartu.

                         Dua gunanya sekaligus. Yang pertama menutup pertanyaan
                         "jadi totalnya berapa" tanpa menyuruh admin menjumlah
                         sendiri baris-baris di atasnya. Yang kedua mengisi kaki
                         kartu yang — karena kartu di sebelahnya lebih panjang —
                         kalau dibiarkan jadi petak putih sepertiga kartu.

                         Kode unik disebut hanya bila memang ada. Inilah selisih
                         antara uang yang masuk rekening dan angka yang mengurangi
                         tagihan; tanpa baris ini, admin yang mencocokkan dengan
                         dasbor DOKU menemukan selisih beberapa ribu dan tidak
                         punya cara tahu itu wajar. --}}
                    @if ($pembayaran !== [])
                        <div class="pd-kaki-bayar">
                            <div class="baris">
                                <span>Sudah diterima</span>
                                <strong>Rp {{ number_format($totalDiterima, 0, ',', '.') }}</strong>
                            </div>
                            @if ($totalKodeUnik > 0)
                                <div class="baris is-redup">
                                    <span>Di antaranya kode unik <em>(penanda, bukan cicilan)</em></span>
                                    <span>Rp {{ number_format($totalKodeUnik, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="baris {{ ($tagihan['lunas'] ?? false) ? 'is-lunas' : 'is-kurang' }}">
                                <span>{{ ($tagihan['lunas'] ?? false) ? 'Tidak ada sisa' : 'Masih ditunggu' }}</span>
                                <strong>{{ ($tagihan['lunas'] ?? false) ? 'Lunas' : ($tagihan['sisa_teks'] ?? '—') }}</strong>
                            </div>
                        </div>
                    @endif
                    </div>
                </div>
            </div>

            {{-- ============ ANGSURAN ============

                 BERAPA KALI DITENTUKAN SISTEM. Layar ini menampilkan yang
                 diizinkan berikut nominalnya, dan admin memilih dari situ —
                 tidak pernah mengetik angkanya sendiri.

                 Nominalnya ikut ditampilkan untuk tiap pilihan, bukan hanya
                 jumlah terminnya. Admin yang menjelaskan lewat WhatsApp butuh
                 angka itu di layarnya; "boleh 3x" tanpa nominal adalah janji
                 yang tidak bisa dinilai orang yang sedang menghitung
                 kemampuannya. --}}
            {{-- Kartu ini TIDAK ditampilkan untuk pesanan yang sudah lunas.

                 Tidak ada sisa tagihan yang bisa diangsur, jadi tidak ada
                 keputusan yang menunggu admin — dan kartu yang isinya hanya
                 penolakan menyuruh orang membaca sesuatu yang tidak
                 berkonsekuensi apa pun.

                 Pengecualiannya: pesanan yang lunas LEWAT angsuran. Di sana
                 jadwalnya adalah riwayat, dan riwayat yang hilang begitu lunas
                 justru yang dicari saat ada yang dipersoalkan. --}}
            @if (empty($angsuran['lunas']) || ! empty($angsuran['rencana']))
            @php
                // Dihitung dari terminnya sendiri, bukan dari status
                // pesanan: yang ditanyakan kartu ini "jadwalnya sudah
                // tuntas belum", dan itu dijawab baris-barisnya.
                $terminRencana = $angsuran['rencana']['termin'] ?? [];
                $rencanaSelesai = $terminRencana !== []
                    && collect($terminRencana)->every(fn ($t) => $t['status'] === 'lunas');
                $adaTelat = collect($terminRencana)->contains(fn ($t) => $t['status'] === 'telat');

                // Dirakit di sini, bukan disusun dari potongan di dalam
                // markup: "{{ n }}×" pada baris terpisah dari katanya
                // menghasilkan baris baru di tengah label, dan yang
                // membaca sumbernya tidak melihat kalimat utuhnya.
                $labelRencana = ($angsuran['rencana']['jumlah_termin'] ?? 0).'× '
                    .($rencanaSelesai ? 'selesai' : ($adaTelat ? 'ada yang telat' : 'berjalan'));

                /*
                 | Kartu penuh hanya bila memang ada isinya.
                 |
                 | Selama belum ada jadwal dan formulirnya belum dibuka, seluruh isi
                 | kartu ini satu kalimat. Kartu selebar layar untuk satu kalimat
                 | meninggalkan petak putih sebesar dirinya sendiri — dan petak itu
                 | terbaca seperti ada yang gagal dimuat, bukan seperti "memang
                 | belum ada apa-apa di sini".
                 */
                $angsuranPita = empty($angsuran['rencana']) && ! $formulirAngsuran;
                $bolehAngsur = (bool) ($angsuran['boleh_diangsur'] ?? false);
            @endphp

            @if ($angsuranPita)
                <div class="dsb-rak dsb-bagian">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-isi pd-pita">
                            <span class="dsb-ikon is-kecil" style="--c: #b45309"><i class="bi bi-calendar2-week"></i></span>

                            <div class="pd-pita-teks">
                                <strong>Bayar bertahap (angsuran)</strong>
                                @if ($bolehAngsur)
                                    <span>
                                        Pesanan ini boleh dicicil sampai
                                        <b>{{ $angsuran['maks_termin'] }} kali</b>. Berikan hanya bila
                                        pelanggan memang memintanya.
                                    </span>
                                @else
                                    {{-- Alasannya disebut, bukan sekadar "tidak boleh": admin yang
                                         harus menjelaskan ke pelanggan butuh kalimatnya. --}}
                                    <span>{{ $angsuran['alasan'] ?? 'Pesanan ini belum bisa dicicil.' }}</span>
                                @endif
                            </div>

                            @if ($bolehAngsur)
                                <button type="button" class="dsb-tombol is-utama"
                                    wire:click="bukaFormulirAngsuran">
                                    <i class="bi bi-plus-lg"></i><span>Buat jadwal cicilan</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @else

            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #b45309"><i class="bi bi-calendar2-week"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Bayar Bertahap (Angsuran)</h2>
                                <span class="dsb-kartu-sub">Jadwal cicilan untuk pemesanan ini — berapa kali dan kapan batasnya</span>
                            </span>
                        </div>
                        @if (! empty($angsuran['rencana']))
                            <span class="dsb-lencana {{ $adaTelat ? 'is-merah' : 'is-hijau' }}">{{ $labelRencana }}</span>
                        @endif
                    </div>
                    <div class="dsb-kartu-isi">

                    @if (! empty($angsuran['rencana']))
                        @php $rencana = $angsuran['rencana']; @endphp

                        <ul class="orcha-termin">
                            @foreach ($rencana['termin'] as $termin)
                                <li class="baris {{ $termin['status'] }}">
                                    <span class="urut">{{ $termin['urutan'] }}</span>

                                    <div class="isi">
                                        <div class="fw-semibold">
                                            {{ $termin['urutan'] === 1 ? 'Uang muka' : 'Angsuran ke-' . ($termin['urutan'] - 1) }}
                                        </div>
                                        <div class="text-muted" style="font-size:.76rem">
                                            Jatuh tempo
                                            {{ \Carbon\Carbon::parse($termin['jatuh_tempo'])->locale('id')->translatedFormat('j F Y') }}
                                            @if ($termin['kurang'] > 0 && $termin['kurang'] < $termin['nominal'])
                                                · kurang Rp {{ number_format($termin['kurang'], 0, ',', '.') }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="fw-bold">{{ $termin['nominal_teks'] }}</div>
                                        <span class="tanda">
                                            {{ ['lunas' => 'Lunas', 'telat' => 'Telat', 'menunggu' => 'Menunggu'][$termin['status']] }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        @if ($rencana['catatan'])
                            <div class="orcha-cek-catatan mt-3">{{ $rencana['catatan'] }}</div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                            <span class="text-muted" style="font-size:.76rem">
                                Dibuat {{ $rencana['dibuat_oleh'] ?: 'admin' }}
                                @if ($rencana['dibuat_pada'])
                                    · {{ \Carbon\Carbon::parse($rencana['dibuat_pada'])->locale('id')->translatedFormat('j M Y') }}
                                @endif
                            </span>

                            {{-- Tidak ada yang bisa dibatalkan dari jadwal yang sudah
                                 tuntas, dan menawarkannya bukan sekadar tidak berguna
                                 — merusak. Yang tersisa dari rencana selesai cuma
                                 catatannya: bukti bahwa pelanggan diberi keringanan,
                                 jadwalnya apa, dan ia menyelesaikannya. Itu persis
                                 yang dicari saat belakangan ada yang dipersoalkan.

                                 Endpoint-nya juga menolak, bukan cuma tombolnya yang
                                 disembunyikan — layar boleh dilewati. --}}
                            @if ($rencanaSelesai)
                                <span class="orcha-rencana-tuntas">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Seluruh termin lunas — jadwalnya disimpan sebagai riwayat
                                </span>
                            @else
                                {{-- Membatalkan rencana TIDAK mengubah tagihannya, dan itu
                                     disebut di dialognya — admin yang mengira ia sedang
                                     menghapus utang pelanggan akan ragu menekannya. --}}
                                <button type="button" class="orcha-btn orcha-btn-bahaya pcek-konfirmasi"
                                    data-action="batalkanAngsuran"
                                    data-title="Batalkan rencana angsuran?"
                                    data-text="Jadwalnya dihapus, tetapi tagihannya tidak berubah — uang yang sudah masuk tetap masuk, dan sisanya kembali jatuh tempo H-{{ $aturanBayar['pelunasan_hari_sebelum'] ?? 5 }} sebelum berangkat."
                                    data-confirm="Ya, batalkan">
                                    <i class="bi bi-x-circle"></i> Batalkan rencana
                                </button>
                            @endif
                        </div>

                    @else
                        {{-- Angka yang sedang DIBAGI, disebut lebih dulu.

                             Admin yang memilih "3×" sedang membagi sebuah angka, dan
                             angka itu harus ada di layar yang sama — bukan diingat
                             dari kartu lain yang sudah tergulung ke atas. --}}
                        <div class="orcha-angsuran-konteks">
                            <span class="lbl">Yang dibagi</span>
                            <span class="nil">{{ $angsuran['total_teks'] }}</span>
                            <span class="ket">seluruh tagihan pesanan ini</span>
                        </div>

                        {{-- Arti "jatuh tempo" dijelaskan sekali di sini, bukan
                             diulang di tiap baris.

                             Yang ditanyakan admin bukan tanggalnya melainkan sifatnya:
                             apakah itu hari pelanggan harus membayar, atau batas
                             akhirnya. Perbedaannya nyata — yang mengira itu tanggal
                             pasti akan menelepon pelanggan yang sebenarnya belum
                             terlambat. --}}
                        <p class="orcha-angsuran-arahan">
                            Pilih berapa kali pelanggan akan membayar. Termin pertama selalu
                            sebesar uang muka — kursinya baru ditahan setelah itu masuk.
                            Tanggal di bawah adalah <strong>batas akhir tiap termin</strong>;
                            pelanggan boleh membayar lebih awal, dan termin terakhir selalu
                            jatuh tepat di batas pelunasan.
                        </p>

                        <div class="orcha-pilih-termin">
                            @foreach ($angsuran['pilihan'] as $opsi)
                                @php $terpilih = $terminDipilih === $opsi['jumlah_termin']; @endphp

                                <label class="opsi {{ $terpilih ? 'aktif' : '' }}">
                                    <input type="radio" wire:model.live="terminDipilih"
                                        value="{{ $opsi['jumlah_termin'] }}" class="d-none">

                                    <div class="kepala">
                                        <div>
                                            <span class="judul">{{ $opsi['jumlah_termin'] }}× pembayaran</span>
                                            {{-- Diterjemahkan jadi kalimat, karena "3×" saja
                                                 tidak memberi tahu apa isinya. --}}
                                            <span class="sub">
                                                uang muka + {{ $opsi['jumlah_termin'] - 1 }} angsuran
                                            </span>
                                        </div>

                                        <span class="tandanya">
                                            <i class="bi {{ $terpilih ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                        </span>
                                    </div>

                                    <ul class="rinci">
                                        @foreach ($opsi['termin'] as $t)
                                            <li>
                                                <span class="urut">{{ $t['urutan'] }}</span>
                                                <span class="apa">{{ $t['label'] }}</span>
                                                {{-- Tanggalnya DIBERI LABEL, tidak berdiri
                                                     telanjang.

                                                     "26 Sep 2026" sendirian tidak menjawab
                                                     pertanyaan pertama yang muncul: itu
                                                     tanggal apa — hari bayarnya, atau batas
                                                     akhirnya? Admin yang menebak salah akan
                                                     menjanjikan hal yang salah pula ke
                                                     pelanggan.

                                                     Kata yang dipakai "jatuh tempo", sama
                                                     dengan jadwal berjalan, halaman
                                                     pembayaran pelanggan, dan surat
                                                     pengingatnya. Dua istilah untuk satu hal
                                                     lebih membingungkan daripada satu
                                                     istilah yang perlu dipelajari sekali. --}}
                                                <span class="kapan">
                                                    <span class="lbl">Jatuh tempo</span>
                                                    {{ \Carbon\Carbon::parse($t['jatuh_tempo'])->locale('id')->translatedFormat('j M Y') }}
                                                </span>
                                                <span class="berapa">{{ $t['nominal_teks'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </label>
                            @endforeach
                        </div>

                        {{-- Akibat yang akan terjadi, disebut SEBELUM tombolnya ditekan.

                             Admin awam tidak tahu apa yang berubah bagi pelanggan setelah
                             ia menekan Terbitkan — dan yang tidak tahu cenderung tidak
                             menekan sama sekali, lalu mengurus angsurannya lewat
                             percakapan seperti sebelum fitur ini ada. --}}
                        <div class="orcha-angsuran-akibat">
                            <i class="bi bi-info-circle"></i>
                            <div>
                                Setelah diterbitkan, pelanggan melihat jadwal ini di halaman
                                pembayaran dan membayar tiap termin sendiri. Ia diingatkan lewat
                                email <strong>{{ $angsuran['ingatkan_hari_sebelum'] }} hari</strong>
                                sebelum tiap jatuh tempo, dan <strong>kotak surat kantor</strong>
                                dikabari bila ada termin yang terlewat.
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="dsb-label">
                                Catatan <span class="text-muted fw-normal">(internal — hanya dibaca admin)</span>
                            </label>
                            <input type="text" class="dsb-isian" wire:model="catatanAngsuran"
                                placeholder="Mis. diminta wali murid, dibayar per gajian.">
                            <div class="pd-bantu">
                                Alasan keringanan ini diberikan — berguna saat admin lain
                                membukanya berbulan-bulan kemudian.
                            </div>
                        </div>

                        {{-- Terbitkan di kanan, Batal di kiri — urutan yang sama
                             dengan kaki lembar cek pembayaran, supaya tangan admin
                             tidak perlu belajar dua kebiasaan di satu panel. --}}
                        {{-- Dua tombol setengah lebar, bukan sepasang tombol kecil di
                             pojok kanan. Kaki kartu yang separuhnya kosong terbaca
                             seperti ada yang gagal dimuat — dan tombol lebar juga lebih
                             mudah ditekan di layar sentuh, tempat sebagian admin
                             mengurus pesanan. --}}
                        <div class="row g-2 mt-3">
                            <div class="col-6">
                                <button type="button"
                                    class="orcha-btn orcha-btn-lembut orcha-tombol-lembar"
                                    wire:click="tutupFormulirAngsuran">
                                    <i class="bi bi-x-lg"></i> Batal
                                </button>
                            </div>

                            <div class="col-6">
                                <button type="button"
                                    class="orcha-btn orcha-btn-utama orcha-tombol-lembar"
                                    wire:click="simpanAngsuran" wire:target="simpanAngsuran"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="simpanAngsuran"
                                        class="orcha-ikon-teks">
                                        <i class="bi bi-check-lg"></i> Terbitkan jadwal
                                    </span>
                                    <span wire:loading wire:target="simpanAngsuran">Menyimpan…</span>
                                </button>
                            </div>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
            @endif
            @endif

            {{-- ============ RIWAYAT PERUBAHAN NAMA ============
                 Nama lama tidak hilang saat peserta diganti. Ia yang membayar,
                 atau yang riwayat kesehatannya sudah masuk — dan pertanyaan
                 "dulu siapa yang didaftarkan" hampir selalu muncul belakangan,
                 saat tidak ada lagi yang mengingatnya. --}}
            @if (! empty($pendaftaran['riwayat_penggantian']))
            @php
                $riwayat = $pendaftaran['riwayat_penggantian'];
                $jumlahGanti = count($riwayat);
            @endphp

            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #4f46e5"><i class="bi bi-arrow-left-right"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Peserta yang Diganti Orang Lain</h2>
                                <span class="dsb-kartu-sub">Nama lamanya tetap tercatat, dan surat pernyataannya bisa diunduh di sini</span>
                            </span>
                        </div>
                        <span class="dsb-lencana is-ungu">{{ $jumlahGanti }} kali penggantian</span>
                    </div>
                    <div class="dsb-kartu-isi">

                    <p class="text-muted small mb-3">
                        Penggantian peserta tidak dikenakan biaya sepanjang jumlahnya tetap.
                        Surat pernyataannya berbentuk PDF resmi bermaterai — tinggal
                        dicetak, ditandatangani para pihak, lalu diarsipkan.
                    </p>

                    {{-- Terbaru di atas, dan nomornya dihitung dari yang terlama:
                         baris teratas menyandang nomor terbesar. Urutan bacanya
                         mengikuti yang dicari admin, penomorannya mengikuti urutan
                         kejadian — dua hal berbeda yang keduanya perlu benar. --}}
                    <div class="orcha-ganti-runtun">
                    @foreach (array_reverse($riwayat) as $urutanBalik => $ganti)
                        <div class="orcha-ganti-baris">
                            <span class="orcha-ganti-nomor">{{ $jumlahGanti - $urutanBalik }}</span>

                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="orcha-ganti-lama">{{ $ganti['dari'] ?: '—' }}</span>
                                <i class="bi bi-arrow-right" style="color:#14a06a"></i>
                                <span class="orcha-ganti-baru">{{ $ganti['ke'] ?: 'tanpa pengganti' }}</span>

                            </div>

                            {{-- Titik jemputnya disebut di barisnya sendiri, bukan
                                 ditumpuk di baris nama: yang membacanya sedang mencari
                                 satu hal — di mana orang ini naik. --}}
                            @if (! empty($ganti['dari_titik']) || ! empty($ganti['ke_titik']))
                                @php
                                    $titikTetap = ! empty($ganti['dari_titik'])
                                        && mb_strtolower(trim($ganti['dari_titik']))
                                            === mb_strtolower(trim($ganti['ke_titik'] ?? ''));
                                @endphp

                                <div class="orcha-ganti-titik">
                                    <span><i class="bi bi-geo-alt"></i> Titik jemput</span>

                                    {{-- Titik yang tidak berpindah tidak dicoret. Coretan
                                         berarti "sudah tidak berlaku", dan titik yang justru
                                         masih dipakai pengganti tidak boleh terbaca begitu —
                                         sopir membaca kartu ini untuk tahu di mana berhenti. --}}
                                    @if ($titikTetap)
                                        <span class="orcha-ganti-baru">{{ $ganti['ke_titik'] }}</span>
                                        <span>tetap, tidak berpindah</span>
                                    @else
                                        <span class="orcha-ganti-lama">{{ $ganti['dari_titik'] ?: '—' }}</span>
                                        <i class="bi bi-arrow-right" style="color:#14a06a"></i>
                                        <span class="orcha-ganti-baru">{{ $ganti['ke_titik'] ?: 'belum dipilih' }}</span>
                                    @endif
                                </div>
                            @endif
                            <div class="orcha-ganti-jejak">
                                <i class="bi bi-clock-history"></i>
                                {{ ! empty($ganti['pada'])
                                    ? \Carbon\Carbon::parse($ganti['pada'])->locale('id')->translatedFormat('d F Y, H:i').' WIB'
                                    : 'waktu tidak tercatat' }}
                                @if (! empty($ganti['oleh']))
                                    &middot; dicatat oleh {{ $ganti['oleh'] }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                    </div>

                    {{-- Satu surat untuk seluruh pendaftaran, bukan satu per baris.

                         Pihak yang menyatakan sama, pendaftaran yang dirujuk sama,
                         kebijakan yang mendasarinya sama — yang berbeda cuma barisnya.
                         Tombol di tiap baris membuat pemesan menandatangani dua berkas
                         bermaterai untuk satu pemesanan yang sama. --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3"
                        style="border-top:1px dashed #eef2f7">
                        <span class="text-muted" style="font-size:.78rem">
                            <i class="bi bi-info-circle"></i>
                            Satu surat memuat seluruh
                            {{ $jumlahGanti }} penggantian di atas — cukup ditandatangani sekali.
                        </span>

                        <a class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                            href="{{ route('admin.orcha.pendaftaran.surat-penggantian', $pendaftaranId) }}">
                            <i class="bi bi-file-earmark-pdf"></i> Unduh surat pernyataan
                        </a>
                    </div>

                    {{-- ===== SURAT YANG SUDAH DITANDATANGANI =====

                         Surat yang sudah dicetak dan ditandatangani perlu jalan pulang
                         ke sistem. Tanpa ini ia cuma ada di percakapan WhatsApp satu
                         admin, dan hilang begitu ponselnya berganti — padahal justru
                         berkas inilah buktinya, bukan PDF kosong yang diunduh tadi. --}}
                    @if (! empty($pendaftaran['surat_penggantian']))
                        <div class="orcha-surat-ttd orcha-surat-ttd-ada mt-3">
                            <i class="bi bi-patch-check-fill"></i>

                            <div class="flex-grow-1">
                                <div class="fw-bold" style="font-size:.84rem;color:#0b7a4b">
                                    Surat bertanda tangan sudah diarsipkan
                                </div>
                                <div class="text-muted" style="font-size:.75rem">
                                    @if (! empty($pendaftaran['surat_penggantian_pada']))
                                        Diunggah
                                        {{ \Carbon\Carbon::parse($pendaftaran['surat_penggantian_pada'])
                                            ->locale('id')->translatedFormat('d F Y, H:i') }} WIB
                                    @else
                                        Waktu unggahnya tidak tercatat
                                    @endif
                                </div>
                            </div>

                            <a class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                                href="{{ $pendaftaran['surat_penggantian'] }}" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i> Lihat
                            </a>

                            {{-- Mengganti berkas memakai isian yang sama dengan mengunggah
                                 pertama kali: yang lama otomatis tergantikan, jadi admin
                                 tidak perlu menghapus dulu baru mengunggah. --}}
                            <label class="orcha-btn orcha-btn-lembut orcha-btn-kecil mb-0"
                                style="cursor:pointer">
                                <i class="bi bi-arrow-repeat"></i> Ganti
                                <input type="file" wire:model="suratTtd" class="d-none"
                                    accept=".pdf,.jpg,.jpeg,.png,.webp">
                            </label>

                            {{-- Sama seperti galeri: SweetAlert, bukan dialog bawaan
                                 peramban yang menampilkan nama host di atas
                                 kalimatnya. --}}
                            <button type="button" class="orcha-btn orcha-btn-kecil orcha-btn-bahaya pcek-konfirmasi"
                                data-action="hapusSuratTtd"
                                data-title="Hapus surat bertanda tangan?"
                                data-text="Berkasnya ikut terhapus dari server. Surat kosongnya tetap bisa diunduh ulang untuk ditandatangani lagi."
                                data-confirm="Ya, hapus" data-icon="warning">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @else
                        <div class="orcha-surat-ttd mt-3">
                            <i class="bi bi-cloud-arrow-up"></i>

                            <div class="flex-grow-1">
                                <div class="fw-bold" style="font-size:.84rem;color: var(--orc-tinta)">
                                    Sudah ditandatangani? Unggah ke sini
                                </div>
                                <div class="text-muted" style="font-size:.75rem">
                                    Hasil pindaian atau foto dari WhatsApp sama-sama diterima
                                    — PDF, JPG, atau PNG, maksimal 8 MB.
                                </div>
                            </div>

                            <label class="orcha-btn orcha-btn-lembut orcha-btn-kecil mb-0"
                                style="cursor:pointer">
                                <i class="bi bi-upload"></i> Pilih berkas
                                <input type="file" wire:model="suratTtd" class="d-none"
                                    accept=".pdf,.jpg,.jpeg,.png,.webp">
                            </label>
                        </div>
                    @endif

                    <div wire:loading wire:target="suratTtd" class="text-muted mt-2"
                        style="font-size:.78rem">
                        <i class="bi bi-arrow-repeat"></i> Mengunggah berkas…
                    </div>

                    @error('suratTtd')
                        <div class="text-danger mt-2" style="font-size:.78rem">{{ $message }}</div>
                    @enderror
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.pratinjau-bukti')
    {{-- ============ PILIHAN PESAN WHATSAPP ============

         Pesannya disusun di komponen, bukan di sini: nominal dan tenggatnya
         berasal dari aturan Orcha yang sama dengan yang dipakai kwitansi, jadi
         angka di percakapan tidak pernah berbeda dari angka di berkas. --}}
    {{-- ============ PILIHAN PESAN WHATSAPP ============

         Popup berdiri sendiri, bukan modal Bootstrap.

         Kelasnya memang terpasang, tetapi JS-nya tidak pernah dimuat —
         resources/js/bootstrap.js di repo ini berisi axios, bukan Bootstrap —
         dan aset Vite pun tidak ikut ter-deploy. Tombol ber-data-bs-toggle
         karenanya diam saja di server, dan itu baru ketahuan setelah dipakai.

         Pesannya disusun di komponen, bukan di sini: nominal dan tenggatnya
         berasal dari aturan Orcha yang sama dengan yang dipakai kwitansi, jadi
         angka di percakapan tidak pernah berbeda dari angka di berkas. --}}
    <div class="orcha-lembar" id="pilihanWa" hidden>
        <div class="orcha-lembar-tirai" onclick="orchaTutupLembar('pilihanWa')"></div>

        <div class="orcha-lembar-isi" role="dialog" aria-modal="true" aria-label="Kirim pesan WhatsApp">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <div class="fw-bold orcha-judul-ikon" style="font-size:1.05rem">
                        <i class="bi bi-whatsapp" style="color:#25d366"></i>
                        Kirim pesan ke {{ $pendaftaran['nama'] ?? 'pemesan' }}
                    </div>
                    <div class="text-muted" style="font-size:.8rem">
                        Pesannya sudah terisi lengkap dengan angka dan tautannya — tinggal periksa lalu kirim.
                    </div>
                </div>

                <button type="button" class="orcha-hapus-baris" aria-label="Tutup"
                    onclick="orchaTutupLembar('pilihanWa')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            @forelse ($pilihanPesan as $pilihan)
                {{-- href memuat versi POLOS; data-wa-pesan memuat yang berpenanda.

                     Skrip di partial salin-wa merakit emojinya di peramban lalu
                     menyusun ulang tautannya saat diklik — emoji tidak pernah ikut
                     melewati respons server, dan justru di perjalanan itulah ia
                     berubah jadi tanda tanya. Bila skripnya tidak sempat jalan,
                     yang terkirim tetap kalimat utuh tanpa emoji. --}}
                <a class="orcha-pilihan-wa"
                    href="{{ $this->tautanWa($pilihan['polos']) }}"
                    data-wa-pesan="{{ $pilihan['pesan'] }}"
                    target="_blank" rel="noopener">
                    <span class="orcha-ikon {{ $pilihan['rupa'] }}">
                        <i class="bi {{ $pilihan['ikon'] }}"></i>
                    </span>

                    <span class="flex-grow-1">
                        <span class="d-block fw-bold" style="font-size:.92rem;color: var(--orc-tinta)">
                            {{ $pilihan['judul'] }}
                        </span>
                        <span class="d-block text-muted" style="font-size:.78rem">
                            {{ $pilihan['ringkas'] }}
                        </span>
                    </span>

                    <i class="bi bi-box-arrow-up-right text-muted"></i>
                </a>
            @empty
                <p class="text-muted text-center mb-0 py-2" style="font-size:.88rem">
                    Tidak ada yang perlu ditagih atau dikirimkan untuk pendaftaran ini —
                    pembayarannya lunas dan riwayat kesehatannya sudah lengkap.
                </p>
            @endforelse

            {{-- Selalu ada, di bawah pilihan yang sudah terisi: kadang yang perlu
                 disampaikan memang tidak ada di daftar mana pun. --}}
            <a class="orcha-pilihan-wa orcha-pilihan-wa-polos"
                href="{{ $this->tautanWa('') ?: $wa($pendaftaran['whatsapp']) }}"
                target="_blank" rel="noopener">
                <span class="orcha-ikon orcha-ikon-netral">
                    <i class="bi bi-chat-dots"></i>
                </span>

                <span class="flex-grow-1">
                    <span class="d-block fw-bold" style="font-size:.92rem;color: var(--orc-tinta)">
                        Buka percakapan kosong
                    </span>
                    <span class="d-block text-muted" style="font-size:.78rem">
                        Menulis sendiri, tanpa pesan siap pakai
                    </span>
                </span>

                <i class="bi bi-box-arrow-up-right text-muted"></i>
            </a>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.salin-wa')
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
