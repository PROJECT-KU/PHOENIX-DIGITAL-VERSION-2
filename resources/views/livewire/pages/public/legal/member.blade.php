@section('title')
    Keuntungan & Syarat Member | Phoenix Digital
@endsection

<main class="main mbr-page">
    <style>
        /* ===== Halaman Member =====
           Bahasa visual sama dengan Beranda, Shop, Bundling, Layanan, Tentang,
           Kontak, dan FAQ: kartu judul bersama (.ph-page-title), kartu putih
           bersudut 18px dengan warna per kartu (--c), ubin ikon berwarna.
           Kelas mbr-*: gaya .legal-*/.lg-* dipakai bersama Syarat, Privasi, dan
           FAQ (sebagian beku di server), jadi halaman ini berdiri sendiri. */
        .mbr-page { --mbr-ink: #1c1f26; --mbr-muted: #64748b; --mbr-line: #eceff4; --mbr-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .mbr-section { padding: 18px 0 64px; }

        /* Ubin ikon — glif tunggal selalu display:block + line-height:1 */
        .mbr-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 44px; height: 44px; border-radius: 14px; font-size: 1.15rem;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 85%, #0f172a);
        }
        .mbr-ubin.is-padat {
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff)); color: #fff;
            box-shadow: 0 14px 26px -14px color-mix(in srgb, var(--c) 85%, transparent);
        }
        .mbr-ubin.is-kecil { width: 34px; height: 34px; border-radius: 11px; font-size: .92rem; }
        .mbr-ubin i.bi, .mbr-ubin i.bi::before { display: block; line-height: 1; }

        .mbr-grid { display: grid; grid-template-columns: minmax(0, .82fr) minmax(0, 2fr); gap: 24px; align-items: start; }

        /* Daftar isi */
        .mbr-toc { position: sticky; top: 96px; }
        .mbr-toc-kartu { padding: 20px 18px; background: #fff; border: 1px solid var(--mbr-line); border-radius: 20px; box-shadow: 0 18px 40px -34px rgba(15, 23, 42, .5); }
        .mbr-toc-kepala { display: flex; align-items: center; gap: 11px; margin-bottom: 14px; }
        .mbr-toc-kepala b { font-family: var(--mbr-font); font-weight: 800; font-size: 1rem; color: var(--mbr-ink); }
        .mbr-toc-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 4px; }
        .mbr-toc-list a {
            display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 11px;
            color: #475569; font-size: .85rem; line-height: 1.4; text-decoration: none;
            transition: background .18s ease, color .18s ease;
        }
        .mbr-toc-list a:hover { background: color-mix(in srgb, var(--c) 8%, #fff); color: color-mix(in srgb, var(--c) 80%, #0f172a); }
        .mbr-diperbarui {
            display: flex; flex-direction: column; gap: 2px; margin-top: 14px; padding-top: 13px;
            border-top: 1px dashed #e8ecf2; font-size: .78rem; color: var(--mbr-muted);
        }
        .mbr-diperbarui b { font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }

        /* Kepala bagian */
        .mbr-blok { scroll-margin-top: 100px; }
        .mbr-blok + .mbr-blok { margin-top: 40px; }
        .mbr-kepala { display: flex; align-items: center; gap: 13px; margin-bottom: 16px; }
        .mbr-kepala h2 { margin: 0; font-family: var(--mbr-font); font-weight: 800; font-size: 1.25rem; letter-spacing: -.01em; color: var(--mbr-ink); }
        .mbr-kepala p { margin: 2px 0 0; font-size: .86rem; color: var(--mbr-muted); line-height: 1.5; }

        /* Kartu umum */
        .mbr-kartu {
            position: relative; background: #fff; border: 1px solid var(--mbr-line); border-radius: 18px; padding: 20px;
            overflow: hidden; transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .mbr-kartu::before {
            content: ""; position: absolute; top: -38px; right: -38px; width: 108px; height: 108px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 10%, transparent); transition: transform .35s ease;
        }
        .mbr-kartu:hover {
            transform: translateY(-4px); border-color: color-mix(in srgb, var(--c) 35%, #fff);
            box-shadow: 0 18px 34px -22px color-mix(in srgb, var(--c) 75%, transparent);
        }
        .mbr-kartu:hover::before { transform: scale(1.25); }
        .mbr-kartu > * { position: relative; }

        /* Langkah */
        .mbr-langkah { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .mbr-lk-atas { display: flex; align-items: center; justify-content: space-between; margin-bottom: 13px; }
        .mbr-lk-no { font-family: var(--mbr-font); font-weight: 800; font-size: 1.7rem; line-height: 1; color: var(--c); }
        .mbr-kartu h3 { margin: 0 0 5px; font-family: var(--mbr-font); font-weight: 800; font-size: 1.02rem; color: var(--mbr-ink); }
        .mbr-kartu p { margin: 0; font-size: .87rem; line-height: 1.65; color: var(--mbr-muted); }
        .mbr-kartu p b { color: #334155; }
        .mbr-aktif {
            display: flex; align-items: center; gap: 13px; margin-top: 16px; padding: 16px 18px; border-radius: 18px;
            border: 1px solid color-mix(in srgb, var(--c) 22%, #eceff4);
            background: linear-gradient(135deg, color-mix(in srgb, var(--c) 9%, #fff) 0%, #fff 68%);
        }
        /* Dipersempit ke .mbr-aktif-teks: ditulis sebagai .mbr-aktif span, aturan
           ini ikut mengenai ubin ikon dan mematahkan display:flex-nya. */
        .mbr-aktif-teks b { display: block; font-family: var(--mbr-font); font-weight: 800; font-size: .98rem; color: var(--mbr-ink); }
        .mbr-aktif-teks span { display: block; margin-top: 3px; font-size: .86rem; line-height: 1.6; color: #475569; }

        /* Keuntungan */
        .mbr-untung { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .mbr-chip {
            display: inline-flex; align-items: center; height: 24px; padding: 0 10px; margin-top: 12px; border-radius: 99px;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 55%, #0f172a);
            font-size: .73rem; font-weight: 800;
        }

        /* Contoh hitungan */
        .mbr-hitung { display: grid; gap: 14px; }
        .mbr-alur { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; align-items: stretch; }
        .mbr-alur-item {
            display: flex; flex-direction: column; gap: 6px; padding: 16px; border-radius: 16px;
            background: #fff; border: 1px solid var(--mbr-line); text-align: center; align-items: center;
        }
        .mbr-alur-label { font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--mbr-muted); }
        .mbr-alur-nilai { font-family: var(--mbr-font); font-weight: 800; font-size: 1.25rem; line-height: 1.2; color: var(--c); }
        .mbr-alur-ket { font-size: .78rem; color: var(--mbr-muted); line-height: 1.45; }
        .mbr-sisa {
            display: flex; align-items: center; gap: 13px; padding: 15px 17px; border-radius: 16px;
            border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; font-size: .88rem; line-height: 1.6;
        }
        .mbr-sisa b { color: #14532d; }

        /* Syarat */
        .mbr-syarat { display: grid; gap: 10px; }
        .mbr-syarat-item {
            display: flex; align-items: flex-start; gap: 13px; padding: 14px 16px;
            background: #fff; border: 1px solid var(--mbr-line); border-radius: 16px;
            font-size: .87rem; line-height: 1.65; color: #475569;
        }
        .mbr-syarat-item b { color: #334155; }

        /* Ajakan penutup */
        .mbr-cta {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px 24px;
            margin-top: 24px; padding: 24px 26px; border-radius: 20px; overflow: hidden; color: #fff;
            background:
                radial-gradient(70% 130% at 100% 0%, rgba(251, 169, 25, .32), transparent 60%),
                linear-gradient(135deg, #23272f 0%, #3a2a20 100%);
        }
        .mbr-cta-kiri { display: flex; align-items: center; gap: 15px; flex: 1 1 340px; min-width: 0; }
        .mbr-cta .mbr-ubin { background: rgba(251, 169, 25, .18); color: #fbbf24; }
        .mbr-cta-teks b { display: block; font-family: var(--mbr-font); font-weight: 800; font-size: 1.1rem; color: #fff; }
        .mbr-cta-teks span { display: block; margin-top: 3px; font-size: .88rem; color: rgba(255, 255, 255, .8); line-height: 1.55; }
        .mbr-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 46px; padding: 0 20px;
            border-radius: 13px; background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-weight: 700; font-size: .9rem; text-decoration: none; white-space: nowrap;
            box-shadow: 0 12px 22px -12px rgba(242, 101, 34, .85); transition: filter .16s ease, transform .16s ease;
        }
        .mbr-btn:hover { color: #fff; filter: brightness(1.05); transform: translateY(-1px); }
        .mbr-btn i.bi, .mbr-btn i.bi::before { display: block; line-height: 1; font-size: 1.02rem; }

        @media (max-width: 991.98px) {
            .mbr-grid { grid-template-columns: minmax(0, 1fr); }
            .mbr-toc { position: static; }
            .mbr-toc-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .mbr-untung { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .mbr-toc-list { grid-template-columns: minmax(0, 1fr); }
            .mbr-langkah, .mbr-untung, .mbr-alur { grid-template-columns: minmax(0, 1fr); }
            .mbr-kepala h2 { font-size: 1.12rem; }
            .mbr-cta { padding: 20px 16px; }
            .mbr-cta .mbr-btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .mbr-kartu, .mbr-kartu:hover, .mbr-btn:hover { transform: none; }
            .mbr-kartu::before { transition: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Gratis, selamanya</span>
                <h1>Jadi Member Phoenix</h1>
                <p>Tanpa biaya, tanpa ribet. Kumpulkan poin dari setiap belanja dan tukar jadi potongan di
                    pembelian berikutnya.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li class="current">Member</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="mbr-section">
        <div class="container">
            <div class="mbr-grid">
                {{-- Daftar isi --}}
                <aside class="mbr-toc">
                    <div class="mbr-toc-kartu" style="--c: #f26522">
                        <div class="mbr-toc-kepala">
                            <span class="mbr-ubin is-kecil"><i class="bi bi-list-ul"></i></span>
                            <b>Daftar Isi</b>
                        </div>
                        <ul class="mbr-toc-list">
                            @foreach ($bagian as $i => $b)
                                <li>
                                    <a href="#mb-{{ $i + 1 }}" style="--c: {{ $b['warna'] }}">
                                        <span class="mbr-ubin is-kecil"><i class="bi {{ $b['ikon'] }}"></i></span>
                                        <span>{{ $b['judul'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <span class="mbr-diperbarui">
                            <b>Terakhir diperbarui</b>
                            <span>{{ \App\Livewire\Pages\Public\Legal\MemberPage::DIPERBARUI }}</span>
                        </span>
                    </div>
                </aside>

                <div>
                    {{-- 1. Caranya --}}
                    <div class="mbr-blok" id="mb-1">
                        <div class="mbr-kepala">
                            <span class="mbr-ubin is-padat" style="--c: {{ $bagian[0]['warna'] }}"><i class="bi {{ $bagian[0]['ikon'] }}"></i></span>
                            <div>
                                <h2>{{ $bagian[0]['judul'] }}</h2>
                                <p>Tanpa formulir pendaftaran — cukup belanja lalu ceritakan pengalamanmu.</p>
                            </div>
                        </div>

                        <div class="mbr-langkah">
                            @foreach ($langkah as $i => $l)
                                <div class="mbr-kartu" style="--c: {{ $l['warna'] }}">
                                    <div class="mbr-lk-atas">
                                        <span class="mbr-ubin is-padat"><i class="bi {{ $l['ikon'] }}"></i></span>
                                        <span class="mbr-lk-no">{{ sprintf('%02d', $i + 1) }}</span>
                                    </div>
                                    <h3>{{ $l['judul'] }}</h3>
                                    <p>{!! $l['teks'] !!}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mbr-aktif" style="--c: #16a34a">
                            <span class="mbr-ubin"><i class="bi bi-patch-check-fill"></i></span>
                            <div class="mbr-aktif-teks">
                                <b>Status member langsung aktif</b>
                                <span>Begitu testimonimu disetujui admin, status Member menyala otomatis — tanpa perlu
                                    menghubungi siapa pun. Kode referral pun langsung kamu terima.</span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Keuntungan --}}
                    <div class="mbr-blok" id="mb-2">
                        <div class="mbr-kepala">
                            <span class="mbr-ubin is-padat" style="--c: {{ $bagian[1]['warna'] }}"><i class="bi {{ $bagian[1]['ikon'] }}"></i></span>
                            <div>
                                <h2>{{ $bagian[1]['judul'] }}</h2>
                                <p>Tiga keuntungan yang berlaku selama kamu jadi member.</p>
                            </div>
                        </div>

                        <div class="mbr-untung">
                            @foreach ($keuntungan as $k)
                                <div class="mbr-kartu" style="--c: {{ $k['warna'] }}">
                                    <span class="mbr-ubin is-padat"><i class="bi {{ $k['ikon'] }}"></i></span>
                                    <h3 style="margin-top:13px;">{{ $k['judul'] }}</h3>
                                    <p>{!! $k['teks'] !!}</p>
                                    <span class="mbr-chip">{{ $k['chip'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. Contoh hitungan --}}
                    <div class="mbr-blok" id="mb-3">
                        <div class="mbr-kepala">
                            <span class="mbr-ubin is-padat" style="--c: {{ $bagian[2]['warna'] }}"><i class="bi {{ $bagian[2]['ikon'] }}"></i></span>
                            <div>
                                <h2>{{ $bagian[2]['judul'] }}</h2>
                                <p>Misal kamu belanja Rp {{ number_format($contohBelanja, 0, ',', '.') }}.</p>
                            </div>
                        </div>

                        <div class="mbr-hitung">
                            <div class="mbr-alur">
                                <div class="mbr-alur-item" style="--c: #2563eb">
                                    <span class="mbr-ubin is-kecil"><i class="bi bi-bag"></i></span>
                                    <span class="mbr-alur-label">Belanja</span>
                                    <span class="mbr-alur-nilai">Rp {{ number_format($contohBelanja, 0, ',', '.') }}</span>
                                    <span class="mbr-alur-ket">Dibagi Rp {{ number_format($perPoin, 0, ',', '.') }} per poin</span>
                                </div>
                                <div class="mbr-alur-item" style="--c: #d97706">
                                    <span class="mbr-ubin is-kecil"><i class="bi bi-coin"></i></span>
                                    <span class="mbr-alur-label">Poin didapat</span>
                                    <span class="mbr-alur-nilai">{{ $contohPoin }} poin</span>
                                    <span class="mbr-alur-ket">1 poin = Rp {{ number_format($nilaiPoin, 0, ',', '.') }}</span>
                                </div>
                                <div class="mbr-alur-item" style="--c: #16a34a">
                                    <span class="mbr-ubin is-kecil"><i class="bi bi-ticket-perforated"></i></span>
                                    <span class="mbr-alur-label">Jadi potongan</span>
                                    <span class="mbr-alur-nilai">Rp {{ number_format($contohNilai, 0, ',', '.') }}</span>
                                    <span class="mbr-alur-ket">Dipakai kapan saja</span>
                                </div>
                            </div>

                            <div class="mbr-sisa">
                                <span class="mbr-ubin is-kecil" style="--c: #16a34a"><i class="bi bi-piggy-bank-fill"></i></span>
                                <span>Sisa <b>Rp {{ number_format($contohSisa, 0, ',', '.') }}</b> <b>tidak hangus</b> —
                                    disimpan dan dijumlahkan ke belanja berikutnya. Jadi belanja kecil pun tidak sia-sia.</span>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Syarat --}}
                    <div class="mbr-blok" id="mb-4">
                        <div class="mbr-kepala">
                            <span class="mbr-ubin is-padat" style="--c: {{ $bagian[3]['warna'] }}"><i class="bi {{ $bagian[3]['ikon'] }}"></i></span>
                            <div>
                                <h2>{{ $bagian[3]['judul'] }}</h2>
                                <p>Ringkas dan tanpa huruf kecil tersembunyi.</p>
                            </div>
                        </div>

                        <div class="mbr-syarat">
                            @foreach ($syarat as $s)
                                <div class="mbr-syarat-item" style="--c: {{ $s['warna'] }}">
                                    <span class="mbr-ubin is-kecil"><i class="bi {{ $s['ikon'] }}"></i></span>
                                    <span>{!! $s['teks'] !!}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Rute checkout dipakai langsung, BUKAN url()->previous() — kalau halaman
                         ini dibuka dari tempat lain, previous() melempar ke sana padahal
                         tombolnya jelas-jelas bertuliskan "Kembali ke Checkout". --}}
                    <div class="mbr-cta">
                        <div class="mbr-cta-kiri">
                            <span class="mbr-ubin is-kecil" style="--c: #fba919"><i class="bi bi-bag-check-fill"></i></span>
                            <div class="mbr-cta-teks">
                                <b>Siap lanjut belanja?</b>
                                <span>Selesaikan pesananmu — poinnya mulai terkumpul begitu pesanan dibayar.</span>
                            </div>
                        </div>
                        <a class="mbr-btn" href="{{ route('checkout') }}">
                            <i class="bi bi-arrow-left"></i> Kembali ke Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
