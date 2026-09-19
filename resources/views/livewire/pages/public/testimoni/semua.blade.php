@section('title')
    Testimoni Pelanggan | Phoenix Digital
@endsection

<main class="main sts-page">
    {{-- Gaya berdiri sendiri (prefiks sts-): kelas tm-* milik slider beranda,
         dan halaman ini susunannya kisi, bukan slider. Inline, bukan lewat
         Vite — public/build masuk .gitignore dan tidak ikut ter-deploy. --}}
    <style>
        .sts-page { --sts-ink: #1c1f26; --sts-muted: #6b7280; --sts-line: #eceff4; --sts-jingga: #f26522; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .sts-sec { padding: 42px 0 60px; }

        .sts-kepala { display: flex; align-items: center; gap: clamp(18px, 3vw, 40px); flex-wrap: wrap; padding: clamp(20px, 3vw, 30px); margin-bottom: 26px; background: #fff; border: 1px solid var(--sts-line); border-radius: 22px; box-shadow: 0 18px 40px -34px rgba(15, 23, 42, .55); }
        .sts-kepala-teks { flex: 1 1 280px; min-width: 0; }
        .sts-kicker { display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px; margin-bottom: 10px; border-radius: 99px; background: #fff5ef; color: var(--sts-jingga); font-size: .74rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .sts-kepala h1 { margin: 0 0 8px; font-size: clamp(1.5rem, 3.2vw, 2.1rem); font-weight: 800; color: var(--sts-ink); }
        .sts-kepala p { margin: 0; font-size: .95rem; line-height: 1.7; color: var(--sts-muted); }

        .sts-nilai { flex: 0 0 auto; display: flex; align-items: center; gap: 16px; padding: 16px 20px; border-radius: 18px; background: linear-gradient(140deg, #fff7ed, #fff); border: 1px solid #fde3cf; }
        .sts-nilai-angka { text-align: center; }
        .sts-nilai-angka b { display: block; font-size: 2.3rem; line-height: 1; font-weight: 800; color: var(--sts-ink); }
        .sts-nilai-angka small { display: block; margin-top: 4px; font-size: .74rem; color: var(--sts-muted); }
        .sts-bintang { color: #f59e0b; font-size: .92rem; letter-spacing: 1px; white-space: nowrap; }
        .sts-bintang .is-kosong { color: #e6e9ef; }
        .sts-bar { display: grid; gap: 4px; min-width: 150px; }
        .sts-bar-baris { display: flex; align-items: center; gap: 8px; font-size: .74rem; color: var(--sts-muted); }
        .sts-bar-alur { flex: 1 1 auto; height: 7px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
        .sts-bar-isi { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #fbbf24, #f59e0b); }

        .sts-saring { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
        .sts-chip { padding: 8px 15px; border-radius: 99px; border: 1px solid var(--sts-line); background: #fff; font-size: .82rem; font-weight: 700; color: #4b5563; cursor: pointer; transition: border-color .18s ease, color .18s ease, background .18s ease; }
        .sts-chip:hover { border-color: #f7c9ae; color: var(--sts-jingga); }
        .sts-chip.is-aktif { background: var(--sts-jingga); border-color: var(--sts-jingga); color: #fff; }
        .sts-chip i { color: #f59e0b; }
        .sts-chip.is-aktif i { color: #fff; }

        .sts-kisi { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); align-items: start; }
        .sts-kartu { position: relative; display: flex; flex-direction: column; padding: 22px; background: #fff; border: 1px solid var(--sts-line); border-radius: 20px; transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .sts-kartu:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(242, 101, 34, .12); border-color: #f7c9ae; }
        .sts-kutip { position: absolute; top: 16px; right: 18px; font-size: 1.6rem; color: #fbe3d3; }
        .sts-pesan { margin: 12px 0 16px; font-size: .92rem; line-height: 1.75; color: #374151; white-space: pre-line; overflow-wrap: anywhere; }
        .sts-orang { display: flex; align-items: center; gap: 12px; margin-top: auto; padding-top: 14px; border-top: 1px solid #f4f6f9; }
        .sts-avatar { flex: 0 0 44px; width: 44px; height: 44px; border-radius: 50%; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(140deg, #f26522, #f59e0b); color: #fff; font-weight: 800; }
        .sts-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .sts-meta { min-width: 0; }
        .sts-nama { display: block; font-size: .92rem; font-weight: 800; color: var(--sts-ink); }
        .sts-peran { display: block; font-size: .78rem; color: var(--sts-muted); }
        .sts-asli { display: inline-flex; align-items: center; gap: 5px; margin-top: 5px; padding: 3px 9px; border-radius: 99px; background: #ecfdf5; color: #15803d; font-size: .72rem; font-weight: 700; }

        .sts-ajakan { margin-top: 26px; }
        .sts-lanjut { display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap; margin-top: 16px; padding: 24px 26px; background: #fff; border: 1px solid var(--sts-line); border-radius: 20px; }
        .sts-lanjut b { display: block; font-size: 1.02rem; color: var(--sts-ink); }
        .sts-lanjut span { display: block; font-size: .88rem; color: var(--sts-muted); }
        .sts-lanjut-tombol { display: flex; gap: 10px; flex-wrap: wrap; }
        .sts-tombol { display: inline-flex; align-items: center; gap: 8px; padding: 11px 18px; border-radius: 12px; border: 1px solid var(--sts-line); background: #fff; color: #4b5563; font-size: .88rem; font-weight: 700; text-decoration: none; white-space: nowrap; }
        .sts-tombol:hover { border-color: #f7c9ae; color: var(--sts-jingga); }
        .sts-tombol.is-utama { background: var(--sts-jingga); border-color: var(--sts-jingga); color: #fff; }
        .sts-tombol.is-utama:hover { background: #d9550f; border-color: #d9550f; color: #fff; }

        /* Tinggi minimum pesan: dasar tiap baris kartu jadi rata. */
        .sts-pesan { min-height: 76px; }

        .sts-kosong { padding: 52px 20px; text-align: center; background: #fff; border: 1px dashed #e2e8f0; border-radius: 20px; }
        .sts-kosong i { font-size: 2rem; color: #cbd5e1; }
        .sts-kosong p { margin: 10px 0 0; color: var(--sts-muted); }

        .sts-halaman { margin-top: 26px; display: flex; justify-content: center; }
        .sts-halaman .pagination { gap: 6px; flex-wrap: wrap; }
        .sts-halaman .page-link { min-width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--sts-line) !important; border-radius: 12px !important; background: #fff !important; color: #4b5563 !important; font-weight: 700; font-size: .84rem; box-shadow: none !important; }
        .sts-halaman .page-item.active .page-link { background: var(--sts-jingga) !important; border-color: var(--sts-jingga) !important; color: #fff !important; }
        .sts-halaman .page-item.disabled .page-link { color: #c3c9d4 !important; background: #f8fafc !important; }
        .sts-halaman .pagination-wrap { gap: 10px; }

        @media (max-width: 767.98px) {
            .sts-nilai { width: 100%; }
            .sts-kisi { grid-template-columns: minmax(0, 1fr); }
        }
    </style>

    <section class="sts-sec">
        <div class="container">
            <header class="sts-kepala">
                <div class="sts-kepala-teks">
                    <span class="sts-kicker"><i class="bi bi-chat-quote-fill"></i> Testimoni</span>
                    <h1>Apa Kata Pelanggan Kami</h1>
                    <p>
                        {{ $total }} testimoni dari pelanggan Phoenix Digital.
                        Semuanya ditinjau admin terlebih dahulu, dan yang menulisnya adalah orang yang benar-benar memesan.
                    </p>
                </div>

                @if ($total)
                    <div class="sts-nilai">
                        <div class="sts-nilai-angka">
                            <b>{{ number_format($rata, 1, ',', '.') }}</b>
                            <span class="sts-bintang" aria-hidden="true">
                                @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star-fill {{ $i <= round($rata) ? '' : 'is-kosong' }}"></i>@endfor
                            </span>
                            <small>dari {{ $total }} testimoni</small>
                        </div>
                        <div class="sts-bar">
                            @foreach ($sebaran as $bintang => $jumlah)
                                <div class="sts-bar-baris">
                                    <span>{{ $bintang }}★</span>
                                    <span class="sts-bar-alur"><span class="sts-bar-isi" style="width: {{ $total ? round($jumlah / $total * 100) : 0 }}%"></span></span>
                                    <span>{{ $jumlah }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </header>

            @if ($total)
                <div class="sts-saring" role="group" aria-label="Saring menurut bintang">
                    <button type="button" class="sts-chip {{ $bintang === '' ? 'is-aktif' : '' }}" wire:click="setBintang('')">Semua</button>
                    @foreach ($sebaran as $nilai => $jumlah)
                        @if ($jumlah)
                            <button type="button" class="sts-chip {{ $bintang === (string) $nilai ? 'is-aktif' : '' }}" wire:click="setBintang('{{ $nilai }}')">
                                {{ $nilai }} <i class="bi bi-star-fill"></i> ({{ $jumlah }})
                            </button>
                        @endif
                    @endforeach
                </div>
            @endif

            @if ($testimoni->isEmpty())
                <div class="sts-kosong">
                    <i class="bi bi-stars"></i>
                    <p>{{ $bintang !== '' ? 'Belum ada testimoni dengan '.$bintang.' bintang.' : 'Belum ada testimoni. Jadilah yang pertama berbagi pengalaman Anda!' }}</p>
                </div>
            @else
                <div class="sts-kisi">
                    @foreach ($testimoni as $t)
                        <article class="sts-kartu" wire:key="sts-{{ $t->id }}">
                            <i class="bi bi-quote sts-kutip"></i>
                            <span class="sts-bintang" aria-label="Rating {{ (int) $t->rating }} dari 5">
                                @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star-fill {{ $i <= (int) $t->rating ? '' : 'is-kosong' }}"></i>@endfor
                            </span>
                            <p class="sts-pesan">{{ $t->pesan }}</p>
                            <div class="sts-orang">
                                {{-- WAJIB nama_publik, JANGAN $t->nama: pengirim anonim hanya boleh
                                     tampil sebagai huruf depan namanya di halaman publik. --}}
                                <span class="sts-avatar">
                                    @if ($t->foto && \Storage::disk('public')->exists('img/testimoni/'.$t->foto))
                                        <img src="{{ asset('storage/img/testimoni/'.$t->foto) }}" alt="{{ $t->nama_publik }}">
                                    @else
                                        {{ mb_strtoupper(mb_substr($t->nama_publik, 0, 1)) }}
                                    @endif
                                </span>
                                <span class="sts-meta">
                                    <span class="sts-nama">{{ $t->nama_publik }}</span>
                                    @if ($t->peran)
                                        <span class="sts-peran">{{ $t->peran }}</span>
                                    @endif
                                    @if ($t->customer_id && ($t->customer->belanja_selesai_count ?? 0) > 0)
                                        <span class="sts-asli" title="Pembeli asli — pesanannya sudah selesai">
                                            <i class="bi bi-patch-check-fill"></i> Pembeli Asli
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($testimoni->hasPages())
                    <div class="sts-halaman">{{ $testimoni->links('vendor.pagination') }}</div>
                @endif
            @endif

            {{-- Formulir kiriman ikut dibawa ke sini: pengunjung yang mendarat
                 langsung di halaman ini dulu harus balik ke beranda dulu. --}}
            <div class="sts-ajakan">
                <livewire:components.testimonials :tampilkan-daftar="false" />
            </div>

            <div class="sts-lanjut">
                <div>
                    <b>Siap mencoba sendiri?</b>
                    <span>Akun premium, tools AI, dan jasa cek plagiasi — semuanya bergaransi.</span>
                </div>
                <div class="sts-lanjut-tombol">
                    <a href="{{ route('shop.index') }}" wire:navigate class="sts-tombol is-utama"><i class="bi bi-bag-check"></i> Lihat Produk</a>
                    <a href="{{ route('homepage') }}" wire:navigate class="sts-tombol"><i class="bi bi-house"></i> Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </section>
</main>
