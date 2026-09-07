@section('title')
Jeda Layanan || lemon
@stop

<div>
    <style>
        /* Ditulis inline: public/build tidak ikut terdeploy ke server. */

        /* Glyph Bootstrap Icons punya line-height bawaan yang menariknya turun,
           sehingga terlihat melenceng di dalam kotak yang sudah di-flex-center.
           Pola perbaikan yang sama dipakai di daftar produk. */
        .jl-ikon i.bi { display: flex; align-items: center; justify-content: center; line-height: 1; position: relative; z-index: 1; }
        .jl-ikon i.bi::before { display: block; line-height: 1; }

        /* Warna keadaan dipegang di sini, bukan memakai .bg-gradient-red bersama:
           merah terbaca "rusak", padahal jeda adalah keadaan sementara yang
           disengaja. Jingga menyatu dengan pita tepi dan lencana kartunya. */
        .jl-ikon { position: relative; overflow: hidden; border-radius: 18px; }
        .jl-ikon::after {
            content: ""; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,.24), transparent 62%);
        }
        .jl-hijau {
            background: linear-gradient(135deg, #34d399, #059669);
            box-shadow: 0 8px 18px rgba(5,150,105,.26), 0 0 0 5px rgba(16,185,129,.10);
        }
        .jl-jingga {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            box-shadow: 0 8px 18px rgba(217,119,6,.26), 0 0 0 5px rgba(245,158,11,.13);
            animation: jl-denyut 2.8s ease-in-out infinite;
        }
        /* Denyut halus menarik mata ke satu-satunya kartu yang butuh perhatian. */
        @keyframes jl-denyut {
            0%, 100% { box-shadow: 0 8px 18px rgba(217,119,6,.26), 0 0 0 5px rgba(245,158,11,.13); }
            50%      { box-shadow: 0 8px 18px rgba(217,119,6,.32), 0 0 0 10px rgba(245,158,11,.05); }
        }
        @media (prefers-reduced-motion: reduce) { .jl-jingga { animation: none; } }

        .jl-ringkas { display: flex; align-items: center; gap: 14px; }
        .jl-ringkas-teks { line-height: 1.35; }
        .jl-ringkas-teks b { display: block; font-size: .98rem; }
        .jl-ringkas-teks small { color: #94a3b8; }

        .jl-catatan {
            font-size: .86rem; line-height: 1.65; color: #64748b;
            background: #f8fafc; border: 1px solid #eef2f7; border-radius: 14px;
            padding: 16px 18px; margin-bottom: 22px;
        }
        .jl-catatan b { color: #475569; }

        .jl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: stretch; }
        @media (max-width: 1150px) { .jl-grid { grid-template-columns: 1fr; } }

        .jl-kartu {
            position: relative; overflow: hidden;
            border: 1px solid #e8ebf1; border-radius: 18px; padding: 22px 22px 22px 26px;
            background: #fff; display: flex; flex-direction: column; gap: 18px;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        /* Pita tepi: keadaan kartu terbaca sekilas tanpa membaca tulisannya. */
        .jl-kartu::before {
            content: ""; position: absolute; top: 0; bottom: 0; left: 0; width: 4px;
            background: #22c55e;
        }
        .jl-kartu:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(15, 23, 42, .08); }
        .jl-kartu.is-jeda { border-color: #f6dcae; background: #fffdf7; }
        .jl-kartu.is-jeda::before { background: #f59e0b; }

        .jl-atas { display: flex; align-items: center; gap: 14px; }
        .jl-judul { min-width: 0; }
        .jl-jenis {
            font-size: .64rem; font-weight: 700; letter-spacing: .09em;
            text-transform: uppercase; color: #b6c0cd; margin-bottom: 2px;
        }
        .jl-kartu.is-jeda .jl-jenis { color: #c99a4e; }
        .jl-nama { font-weight: 700; font-size: 1.02rem; line-height: 1.25; margin-bottom: 6px; }

        .jl-pil {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 11px; border-radius: 999px;
            font-size: .72rem; font-weight: 700; letter-spacing: .01em;
            background: #dcfce7; color: #15803d;
        }
        .jl-pil::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .jl-kartu.is-jeda .jl-pil { background: #fef3c7; color: #b45309; }

        .jl-blok { border-top: 1px dashed #e8ebf1; padding-top: 15px; }
        .jl-kartu.is-jeda .jl-blok { border-top-color: #f2e3c4; }
        .jl-label {
            font-size: .67rem; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #a0aab8; margin-bottom: 9px;
        }

        .jl-chips { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 6px; }
        .jl-chips li {
            font-size: .76rem; padding: 5px 11px; border-radius: 9px;
            background: #f1f5f9; color: #475569; border: 1px solid #e6ebf1;
        }
        .jl-kartu.is-jeda .jl-chips li { background: #fef6e4; color: #92400e; border-color: #f6dcae; }
        .jl-kosong { font-size: .78rem; color: #a0aab8; font-style: italic; }

        .jl-pesan { display: flex; gap: 8px; }
        .jl-pesan .form-control {
            border-radius: 11px; font-size: .82rem; padding: 9px 12px;
            border-color: #e2e8f0; background: #fbfcfe;
        }
        .jl-pesan .form-control:focus { background: #fff; }
        .jl-pesan .btn {
            border-radius: 11px; white-space: nowrap; font-size: .8rem; font-weight: 600;
            padding-inline: 16px; border-color: #e2e8f0; color: #475569; background: #fff;
        }
        .jl-pesan .btn:hover { background: #f1f5f9; color: #1e293b; }

        .jl-pratinjau { margin-top: 10px; }
        .jl-pratinjau span {
            display: block; font-size: .66rem; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #b6c0cd; margin-bottom: 3px;
        }
        .jl-pratinjau p {
            margin: 0; font-size: .79rem; line-height: 1.55; color: #64748b; font-style: italic;
        }
        .jl-kartu.is-jeda .jl-pratinjau p { color: #92400e; }

        .jl-bagian {
            display: flex; align-items: baseline; gap: 10px;
            font-size: .74rem; font-weight: 700; letter-spacing: .09em;
            text-transform: uppercase; color: #94a3b8;
            padding-bottom: 9px; margin: 0 0 16px; border-bottom: 2px solid #eef2f7;
        }
        .jl-bagian span {
            font-size: .68rem; letter-spacing: .04em; text-transform: none;
            color: #b6c0cd; font-weight: 600;
        }

        .jl-hampa {
            display: flex; align-items: center; gap: 13px;
            padding: 18px 20px; border-radius: 14px;
            background: #f6fbf8; border: 1px solid #d9efe4; color: #15803d;
        }
        .jl-hampa > i { font-size: 1.35rem; line-height: 1; }
        .jl-hampa b { display: block; font-size: .9rem; }
        .jl-hampa span { display: block; font-size: .8rem; color: #5f8b74; margin-top: 1px; }

        .jl-akun-daftar { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        @media (max-width: 1000px) { .jl-akun-daftar { grid-template-columns: 1fr; } }
        .jl-akun {
            position: relative; overflow: hidden;
            border: 1px solid #f6dcae; background: #fffdf7; border-radius: 16px;
            padding: 18px 18px 18px 22px; display: flex; flex-direction: column; gap: 13px;
        }
        /* Pita tepi sama seperti kartu jasa, supaya keduanya bicara bahasa yang sama. */
        .jl-akun::before {
            content: ""; position: absolute; top: 0; bottom: 0; left: 0; width: 4px; background: #f59e0b;
        }
        .jl-akun-atas { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
        .jl-akun-nama { font-weight: 700; font-size: .98rem; margin-bottom: 5px; }
        .jl-akun .jl-pil { background: #fef3c7; color: #b45309; }
        .jl-akun .jl-pratinjau p { color: #92400e; }

        .jl-sub {
            font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
            color: #a0aab8; margin: 22px 0 11px;
        }

        .jl-aktif-daftar { display: grid; grid-template-columns: repeat(3, 1fr); gap: 9px; }
        @media (max-width: 1200px) { .jl-aktif-daftar { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 720px)  { .jl-aktif-daftar { grid-template-columns: 1fr; } }

        .jl-aktif {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            padding: 10px 12px 10px 14px; border: 1px solid #e6ebf1; border-radius: 11px;
            background: #fff; transition: border-color .16s ease, background .16s ease;
        }
        .jl-aktif:hover { border-color: #d7dee7; background: #fbfcfe; }
        .jl-aktif-nama {
            display: inline-flex; align-items: center; gap: 8px; min-width: 0;
            font-size: .85rem; color: #334155; font-weight: 600;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .jl-aktif-nama i { color: #10b981; font-size: .9rem; line-height: 1; flex-shrink: 0; }
        /* Abu-abu membuatnya terbaca seperti tombol mati, padahal inilah aksi
           utama di daftar ini. Warnanya menyamai tombol "Jeda layanan" pada
           kartu jasa: tindakan yang sama, bahasa yang sama. */
        .jl-aktif-tombol {
            flex-shrink: 0; border: 1px solid #f0c2c2; background: #fff; color: #d63c3c;
            border-radius: 8px; padding: 5px 12px; font-size: .76rem; font-weight: 600;
            transition: background .16s ease, border-color .16s ease, color .16s ease;
        }
        .jl-aktif-tombol:hover { border-color: #dc3545; background: #fdf1f1; color: #b02a2a; }


        /* align-items:start — tanpa ini kartu yang TERBUKA ikut memanjang
           mengikuti tetangganya yang tertutup (yang punya kolom keterangan),
           menyisakan ruang kosong besar tanpa isi. */
        .jl-fitur-daftar {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; align-items: start;
        }
        @media (max-width: 1000px) { .jl-fitur-daftar { grid-template-columns: 1fr; } }

        .jl-fitur {
            position: relative; overflow: hidden;
            border: 1px solid #e6ebf1; border-radius: 13px; background: #fff;
            padding: 14px 14px 14px 18px; display: flex; flex-direction: column; gap: 11px;
        }
        .jl-fitur::before {
            content: ""; position: absolute; top: 0; bottom: 0; left: 0; width: 3px; background: #22c55e;
        }
        .jl-fitur.is-tutup { border-color: #f6dcae; background: #fffdf7; }
        .jl-fitur.is-tutup::before { background: #f59e0b; }

        .jl-fitur-atas { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
        .jl-fitur-teks { min-width: 0; }
        .jl-fitur-nama {
            display: inline-flex; align-items: center; gap: 7px;
            font-weight: 700; font-size: .9rem; color: #334155;
        }
        .jl-fitur-nama i.bi { color: #10b981; font-size: .9rem; line-height: 1; display: block; }
        .jl-fitur.is-tutup .jl-fitur-nama { color: #92400e; }
        .jl-fitur.is-tutup .jl-fitur-nama i.bi { color: #d97706; }
        .jl-fitur-ket { font-size: .77rem; color: #94a3b8; margin-top: 2px; }
        .jl-fitur.is-tutup .jl-fitur-ket { color: #b08c56; }

        /* Tombol buka memakai bahasa hijau, sama seperti "Buka kembali" lain. */
        .jl-aktif-tombol.is-buka { border-color: #bfe3cd; color: #197a4b; }
        .jl-aktif-tombol.is-buka:hover { border-color: #197a4b; background: #f1faf5; color: #14603b; }

        .jl-aksi { margin-top: auto; padding-top: 4px; }
        .jl-aksi .btn { width: 100%; border-radius: 12px; font-weight: 600; padding: 11px; }

        /* ===== Satu aturan untuk SEMUA tombol berikon di halaman ini =====
           Glyph Bootstrap Icons membawa line-height sendiri, jadi meluruskannya
           menuntut dua hal sekaligus: elemen <i> DAN pseudo ::before-nya. Aturan
           ini sempat tersebar di tiga tempat dan dua di antaranya lupa ::before,
           sehingga ikonnya melenceng — disatukan agar tak terulang. */
        .jl-aksi .btn,
        .jl-akun-atas .btn,
        .jl-aktif-tombol { display: inline-flex; align-items: center; justify-content: center; gap: 7px; }

        .jl-aksi .btn i.bi,
        .jl-akun-atas .btn i.bi,
        .jl-aktif-tombol i.bi { display: flex; align-items: center; line-height: 1; }

        .jl-aksi .btn i.bi::before,
        .jl-akun-atas .btn i.bi::before,
        .jl-aktif-tombol i.bi::before { display: block; line-height: 1; }

        .jl-aksi .btn i.bi { font-size: 1rem; }
        .jl-akun-atas .btn i.bi { font-size: .85rem; }
        .jl-aktif-tombol i.bi { font-size: .72rem; }
    </style>

    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="gradient-text fw-bold mb-1">Jeda Layanan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Jeda Layanan']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    @php
                        // Ringkasan harus menghitung KEDUANYA. Menghitung jasa saja
                        // membuat kepala halaman berkata "semua menerima pesanan"
                        // padahal ada produk akun yang tertutup.
                        $jenisDijeda = collect($jeda)->filter(fn ($j) => $j['dijeda'])->keys();
                        $adaJeda = $jenisDijeda->isNotEmpty() || $akunDijeda->isNotEmpty();

                        $bagian = [];
                        if ($jenisDijeda->isNotEmpty()) {
                            $bagian[] = $jenisDijeda->map(fn ($k) => $labelJeda[$k])->implode(', ');
                        }
                        if ($akunDijeda->isNotEmpty()) {
                            $bagian[] = $akunDijeda->count().' produk akun';
                        }
                    @endphp
                    <div class="jl-ringkas">
                        <span class="stat-icon-wrapper jl-ikon {{ $adaJeda ? 'jl-jingga' : 'jl-hijau' }}">
                            <i class="bi bi-{{ $adaJeda ? 'bag-x-fill' : 'bag-check-fill' }}"></i>
                        </span>
                        <div class="jl-ringkas-teks">
                            <b>
                                @if ($adaJeda)
                                {{-- Ampersand ditulis polos: {{ }} sudah meng-escape, menulis &amp; di sini
                                     akan tampil mentah sebagai &amp;amp; --}}
                                Dijeda: {{ implode(' & ', $bagian) }}
                                @else
                                Semua layanan menerima pesanan
                                @endif
                            </b>
                            <small>Status pemesanan layanan jasa &amp; produk akun</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @unless ($bolehKelola)
        <div class="alert alert-light border d-flex align-items-center gap-2 rounded-4" role="alert">
            <i class="bi bi-eye"></i>
            <span>Anda hanya dapat melihat status ini. Mengubahnya membutuhkan izin <b>Kelola Jeda Layanan</b>.</span>
        </div>
        @endunless

        {{-- Tiga kartu terpisah: ketiganya menutup hal yang BERBEDA — tombol beli
             per jenis, tombol beli per produk, dan halamannya sendiri. Digabung
             dalam satu kartu, ketiganya terbaca sebagai satu hal yang sama. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="jl-bagian">Layanan Jasa</div>

                <p class="jl-catatan">
                    Menjeda layanan menutup <b>pemesanan baru</b> saja. Halaman produknya <b>tetap tampil</b> di toko
                    lengkap dengan harga — hanya tombol belinya yang ditutup, diganti keterangan yang Anda tulis di
                    bawah. Pelanggan yang sudah membayar tetap bisa mengunggah berkas memakai sisa kuotanya.
                    Tiap jenis berdiri sendiri: menjeda Cek Plagiasi tidak menyentuh Cek AI.
                </p>

                <div class="jl-grid">
                    @foreach ($labelJeda as $jenis => $label)
                    @php
                        // Judul memakai NAMA PRODUK ASLI bila jenis ini hanya
                        // menaungi satu produk — itu nama yang admin kenal dari
                        // katalog. Bila lebih dari satu, judulnya kembali ke nama
                        // jenis dan produknya didaftar sebagai keping di bawah.
                        $namaProduk = $produkPerJenis[$jenis];
                        $tunggal = count($namaProduk) === 1;
                        $judul = $tunggal ? $namaProduk[0] : $label;
                    @endphp
                    <div class="jl-kartu {{ $jeda[$jenis]['dijeda'] ? 'is-jeda' : '' }}">
                        <div class="jl-atas">
                            {{-- Satu objek, dua keadaan: tas berpesan diterima vs ditutup.
                                 Lebih cepat terbaca daripada centang lawan jeda, yang
                                 bentuknya sama sekali tak berhubungan. --}}
                            <span class="stat-icon-wrapper jl-ikon {{ $jeda[$jenis]['dijeda'] ? 'jl-jingga' : 'jl-hijau' }}">
                                <i class="bi bi-{{ $jeda[$jenis]['dijeda'] ? 'bag-x-fill' : 'bag-check-fill' }}"></i>
                            </span>
                            <div class="jl-judul">
                                <div class="jl-jenis">{{ $label }}</div>
                                <div class="jl-nama">{{ $judul }}</div>
                                <span class="jl-pil">
                                    {{ $jeda[$jenis]['dijeda'] ? 'Pesanan ditutup' : 'Menerima pesanan' }}
                                </span>
                            </div>
                        </div>

                        @unless ($tunggal)
                        {{-- Dilewati bila judulnya sudah nama produk itu sendiri. --}}
                        <div class="jl-blok">
                            <div class="jl-label">Produk yang tercakup</div>
                            @if (empty($namaProduk))
                            <div class="jl-kosong">Belum ada produk jenis ini.</div>
                            @else
                            <ul class="jl-chips">
                                @foreach ($namaProduk as $nama)
                                <li>{{ $nama }}</li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        @endunless

                        <div class="jl-blok">
                            <div class="jl-label">Keterangan untuk pembeli</div>
                            <div class="jl-pesan">
                                <input type="text" class="form-control" maxlength="200"
                                    wire:model="jeda.{{ $jenis }}.pesan"
                                    placeholder="Opsional — ada kalimat bawaan"
                                    @disabled(! $bolehKelola)>
                                <button class="btn" type="button"
                                    wire:click="simpanPesan('{{ $jenis }}')"
                                    wire:loading.attr="disabled" wire:target="simpanPesan('{{ $jenis }}')"
                                    @disabled(! $bolehKelola)>
                                    Simpan
                                </button>
                            </div>

                            {{-- Apa yang BENAR-BENAR dibaca pembeli, bukan tebakan admin. --}}
                            <div class="jl-pratinjau">
                                <span>Dibaca pembeli</span>
                                <p>&ldquo;{{ \App\Support\JedaLayanan::pesan($jenis) }}&rdquo;</p>
                            </div>
                        </div>

                        @if ($bolehKelola)
                        <div class="jl-aksi">
                            <button type="button"
                                class="btn {{ $jeda[$jenis]['dijeda'] ? 'btn-success' : 'btn-outline-danger' }} pcek-konfirmasi"
                                data-action="alihkanJeda" data-arg="{{ $jenis }}"
                                data-title="{{ $jeda[$jenis]['dijeda'] ? 'Buka kembali '.$label.'?' : 'Jeda '.$label.'?' }}"
                                data-text="{{ $jeda[$jenis]['dijeda'] ? 'Pembeli bisa memesan layanan ini lagi.' : 'Pembeli tidak bisa memesan layanan ini sampai dibuka lagi. Produknya tetap tampil di toko.' }}"
                                data-confirm="{{ $jeda[$jenis]['dijeda'] ? 'Ya, buka' : 'Ya, jeda' }}"
                                data-icon="{{ $jeda[$jenis]['dijeda'] ? 'question' : 'warning' }}">
                                <i class="bi bi-{{ $jeda[$jenis]['dijeda'] ? 'play-fill' : 'pause-fill' }}"></i>
                                {{ $jeda[$jenis]['dijeda'] ? 'Buka kembali' : 'Jeda layanan' }}
                            </button>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- ===== Produk akun =====
                     Dijeda satu per satu: tidak ada pengelompokan alami seperti jasa,
                     dan yang bermasalah biasanya satu produk saja. Yang ditampilkan
                     hanya yang SEDANG dijeda — 24 produk kalau didaftar semua. --}}
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="jl-bagian">
                    Produk Akun
                    <span>{{ $akunDijeda->count() }} dijeda dari {{ $akunDijeda->count() + $akunAktif->count() }}</span>
                </div>

                @if ($akunDijeda->isEmpty())
                <div class="jl-hampa">
                    <i class="bi bi-bag-check"></i>
                    <div>
                        <b>Semua produk akun menerima pesanan</b>
                        <span>Tekan Jeda pada daftar di bawah bila ada yang perlu ditutup sementara.</span>
                    </div>
                </div>
                @else
                <div class="jl-akun-daftar">
                    @foreach ($akunDijeda as $produk)
                    <div class="jl-akun">
                        <div class="jl-akun-atas">
                            <div>
                                <div class="jl-akun-nama">{{ $produk->nama_akun }}</div>
                                <span class="jl-pil">Pesanan ditutup</span>
                            </div>
                            @if ($bolehKelola)
                            <button type="button" class="btn btn-sm btn-success pcek-konfirmasi"
                                data-action="alihkanProduk" data-arg="{{ $produk->id }}"
                                data-title="Buka kembali {{ $produk->nama_akun }}?"
                                data-text="Pembeli bisa memesan produk ini lagi."
                                data-confirm="Ya, buka" data-icon="question">
                                <i class="bi bi-play-fill"></i> Buka kembali
                            </button>
                            @endif
                        </div>

                        <div class="jl-pesan">
                            <input type="text" class="form-control" maxlength="200"
                                wire:model="pesanProduk.{{ $produk->id }}"
                                placeholder="Opsional — ada kalimat bawaan"
                                @disabled(! $bolehKelola)>
                            <button class="btn" type="button"
                                wire:click="simpanPesanProduk('{{ $produk->id }}')"
                                wire:loading.attr="disabled" wire:target="simpanPesanProduk('{{ $produk->id }}')"
                                @disabled(! $bolehKelola)>
                                Simpan
                            </button>
                        </div>

                        <div class="jl-pratinjau">
                            <span>Dibaca pembeli</span>
                            <p>&ldquo;{{ \App\Support\JedaLayanan::pesanProduk($produk) }}&rdquo;</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if ($akunAktif->isNotEmpty())
                {{-- Yang menerima pesanan: daftar RINGKAS satu baris. Kartu penuh
                     untuk dua puluhan produk hanya akan mengubur yang benar-benar
                     butuh perhatian, yaitu yang sedang dijeda di atas. --}}
                <div class="jl-sub">Menerima pesanan &middot; {{ $akunAktif->count() }} produk</div>

                <div class="jl-aktif-daftar">
                    @foreach ($akunAktif as $produk)
                    <div class="jl-aktif">
                        <span class="jl-aktif-nama">
                            <i class="bi bi-bag-check-fill"></i>
                            {{ $produk->nama_akun }}
                        </span>
                        @if ($bolehKelola)
                        <button type="button" class="jl-aktif-tombol pcek-konfirmasi"
                            data-action="alihkanProduk" data-arg="{{ $produk->id }}"
                            data-title="Jeda {{ $produk->nama_akun }}?"
                            data-text="Pembeli tidak bisa memesan produk ini sampai dibuka lagi. Produknya tetap tampil di toko."
                            data-confirm="Ya, jeda" data-icon="warning">
                            <i class="bi bi-pause-fill"></i> Jeda
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- ===== Halaman publik =====
                     Berbeda dari dua bagian di atas: yang ditutup bukan tombol
                     belinya, melainkan halamannya sendiri — diganti pemberitahuan
                     sementara tautannya TETAP ada di menu — sama seperti produk yang
                     dijeda tetap tampil di toko. Dipakai saat halamannya yang sedang
                     dikerjakan, bukan barangnya yang habis. --}}
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="jl-bagian">
                    Halaman Publik
                    <span>{{ count($fiturTutup) }} ditutup dari {{ $jumlahFitur }}</span>
                </div>

                <p class="jl-catatan">
                    Pengunjung yang membuka halaman tertutup melihat pemberitahuan, bukan halaman kosong, dan
                    <b>tautannya tetap ada di menu</b>. Anda sendiri <b>tetap bisa membukanya</b> selama masih
                    masuk sebagai admin, jadi hasil perbaikan bisa diperiksa sebelum dibuka untuk umum.
                    Halaman pembayaran, struk, tautan pengecekan, serta syarat &amp; kebijakan privasi
                    <b>tidak pernah bisa ditutup</b> — menutupnya akan menelantarkan pelanggan yang sudah membayar.
                </p>

                @if (empty($fiturTutup))
                <div class="jl-hampa">
                    <i class="bi bi-eye-fill"></i>
                    <div>
                        <b>Semua halaman publik terbuka</b>
                        <span>Tekan Tutup pada daftar di bawah bila ada yang sedang diperbaiki.</span>
                    </div>
                </div>
                @else
                {{-- Yang ditutup naik ke atas sebagai kartu penuh — sama seperti
                     produk akun yang dijeda. Yang butuh perhatian tidak boleh
                     terselip di tengah daftar. --}}
                <div class="jl-akun-daftar">
                    @foreach ($fiturTutup as $kunci => $info)
                    <div class="jl-akun">
                        <div class="jl-akun-atas">
                            <div>
                                <div class="jl-akun-nama">{{ $info['label'] }}</div>
                                <span class="jl-pil">Ditutup</span>
                                <div class="jl-fitur-ket" style="margin-top:5px">{{ $info['ket'] }}</div>
                            </div>
                            @if ($bolehKelola)
                            <button type="button" class="btn btn-sm btn-success pcek-konfirmasi"
                                data-action="alihkanFitur" data-arg="{{ $kunci }}"
                                data-title="Buka kembali {{ $info['label'] }}?"
                                data-text="Pengunjung bisa membuka halaman ini lagi."
                                data-confirm="Ya, buka" data-icon="question">
                                <i class="bi bi-play-fill"></i> Buka kembali
                            </button>
                            @endif
                        </div>

                        <div class="jl-pesan">
                            <input type="text" class="form-control" maxlength="200"
                                wire:model="pesanFitur.{{ $kunci }}"
                                placeholder="Opsional — ada kalimat bawaan"
                                @disabled(! $bolehKelola)>
                            <button class="btn" type="button"
                                wire:click="simpanPesanFitur('{{ $kunci }}')"
                                wire:loading.attr="disabled" wire:target="simpanPesanFitur('{{ $kunci }}')"
                                @disabled(! $bolehKelola)>
                                Simpan
                            </button>
                        </div>

                        <div class="jl-pratinjau">
                            <span>Dibaca pengunjung</span>
                            <p>&ldquo;{{ \App\Support\FiturPublik::pesan($kunci) }}&rdquo;</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if (! empty($fiturBuka))
                <div class="jl-sub">Terbuka &middot; {{ count($fiturBuka) }} halaman</div>

                <div class="jl-fitur-daftar">
                    @foreach ($fiturBuka as $kunci => $info)
                    <div class="jl-fitur">
                        <div class="jl-fitur-atas">
                            <div class="jl-fitur-teks">
                                <div class="jl-fitur-nama">
                                    <i class="bi bi-eye-fill"></i>
                                    {{ $info['label'] }}
                                </div>
                                <div class="jl-fitur-ket">{{ $info['ket'] }}</div>
                            </div>
                            @if ($bolehKelola)
                            <button type="button" class="jl-aktif-tombol pcek-konfirmasi"
                                data-action="alihkanFitur" data-arg="{{ $kunci }}"
                                data-title="Tutup {{ $info['label'] }}?"
                                data-text="Pengunjung yang membukanya melihat pemberitahuan perbaikan. Tautannya tetap ada di menu."
                                data-confirm="Ya, tutup" data-icon="warning">
                                <i class="bi bi-pause-fill"></i> Tutup
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ===== Modul admin =====
             Bukan pengganti izin: izin menentukan siapa yang BOLEH, ini
             menentukan apakah modulnya sedang BISA dipakai sama sekali —
             mis. saat gaji satu periode sedang dihitung ulang. --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="jl-bagian">
                    Modul Admin
                    <span>{{ count($modulTutup) }} ditutup dari {{ $jumlahModul }}</span>
                </div>

                <p class="jl-catatan">
                    Menutup modul <b>tidak mencabut izin siapa pun</b> — begitu dibuka, semua kembali seperti
                    semula. Karyawan yang membukanya melihat pemberitahuan perbaikan. <b>Anda yang memegang izin
                    Kelola Jeda Layanan tetap bisa masuk</b>, supaya hasil perbaikan bisa diperiksa sebelum
                    dibuka untuk yang lain. Dasbor, Akun Profil, dan halaman ini sendiri
                    <b>tidak pernah bisa ditutup</b>, agar tidak ada keadaan terkunci tanpa jalan keluar.
                </p>

                @if (empty($modulTutup))
                <div class="jl-hampa">
                    <i class="bi bi-tools"></i>
                    <div>
                        <b>Semua modul admin bisa dipakai</b>
                        <span>Tekan Tutup pada daftar di bawah bila ada yang sedang diperbaiki.</span>
                    </div>
                </div>
                @else
                <div class="jl-akun-daftar">
                    @foreach ($modulTutup as $kunci => $info)
                    <div class="jl-akun">
                        <div class="jl-akun-atas">
                            <div>
                                <div class="jl-akun-nama">{{ $info['label'] }}</div>
                                <span class="jl-pil">Ditutup</span>
                                <div class="jl-fitur-ket" style="margin-top:5px">{{ $info['ket'] }}</div>
                            </div>
                            @if ($bolehKelola)
                            <button type="button" class="btn btn-sm btn-success pcek-konfirmasi"
                                data-action="alihkanModul" data-arg="{{ $kunci }}"
                                data-title="Buka kembali {{ $info['label'] }}?"
                                data-text="Karyawan bisa memakai modul ini lagi."
                                data-confirm="Ya, buka" data-icon="question">
                                <i class="bi bi-play-fill"></i> Buka kembali
                            </button>
                            @endif
                        </div>

                        <div class="jl-pesan">
                            <input type="text" class="form-control" maxlength="200"
                                wire:model="pesanModul.{{ $kunci }}"
                                placeholder="Opsional — ada kalimat bawaan"
                                @disabled(! $bolehKelola)>
                            <button class="btn" type="button"
                                wire:click="simpanPesanModul('{{ $kunci }}')"
                                wire:loading.attr="disabled" wire:target="simpanPesanModul('{{ $kunci }}')"
                                @disabled(! $bolehKelola)>
                                Simpan
                            </button>
                        </div>

                        <div class="jl-pratinjau">
                            <span>Dibaca karyawan</span>
                            <p>&ldquo;{{ \App\Support\FiturAdmin::pesan($kunci) }}&rdquo;</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if (! empty($modulBuka))
                <div class="jl-sub">Bisa dipakai &middot; {{ count($modulBuka) }} modul</div>

                <div class="jl-fitur-daftar">
                    @foreach ($modulBuka as $kunci => $info)
                    <div class="jl-fitur">
                        <div class="jl-fitur-atas">
                            <div class="jl-fitur-teks">
                                <div class="jl-fitur-nama">
                                    <i class="bi bi-check-circle-fill"></i>
                                    {{ $info['label'] }}
                                </div>
                                <div class="jl-fitur-ket">{{ $info['ket'] }}</div>
                            </div>
                            @if ($bolehKelola)
                            <button type="button" class="jl-aktif-tombol pcek-konfirmasi"
                                data-action="alihkanModul" data-arg="{{ $kunci }}"
                                data-title="Tutup {{ $info['label'] }}?"
                                data-text="Karyawan yang membukanya melihat pemberitahuan perbaikan. Izin mereka tidak dicabut, dan Anda sendiri tetap bisa masuk."
                                data-confirm="Ya, tutup" data-icon="warning">
                                <i class="bi bi-pause-fill"></i> Tutup
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    @include('livewire.layout.sweetalert')
</div>
