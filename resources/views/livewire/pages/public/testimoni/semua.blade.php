@section('title')
    Testimoni Pelanggan | Phoenix Digital
@endsection

<main class="main">
    {{-- Kerangka halaman mengikuti Shop & Tentang Kami: kartu judul bersama
         (.page-title.ph-page-title + .ph-page-head + .breadcrumbs), lalu isi di
         dalam .section > .container. Kartu testimoninya memakai kelas .tm-*
         yang SAMA dengan slider beranda, jadi bentuk & perilakunya identik
         tanpa menyalin gayanya.

         Gaya khusus halaman ini ditulis inline (prefiks tms-): public/build
         masuk .gitignore dan tidak ikut ter-deploy. --}}
    <style>
        .tms-ringkas {
            display: flex; align-items: center; gap: clamp(16px, 3vw, 34px); flex-wrap: wrap;
            padding: clamp(18px, 2.4vw, 24px); margin-bottom: 22px;
            background: #fff; border: 1px solid var(--ph-line, #eceff4); border-radius: 18px;
        }
        .tms-nilai { display: flex; align-items: center; gap: 14px; }
        .tms-nilai b { font-family: 'Poppins', sans-serif; font-size: 2.4rem; line-height: 1; font-weight: 800; color: var(--ph-ink, #1c1f26); }
        .tms-nilai small { display: block; margin-top: 4px; font-size: .78rem; color: var(--ph-muted, #6b7280); }
        .tms-bar { flex: 1 1 260px; min-width: 0; display: grid; gap: 5px; }
        .tms-bar-baris { display: flex; align-items: center; gap: 10px; font-size: .78rem; color: var(--ph-muted, #6b7280); }
        .tms-bar-alur { flex: 1 1 auto; height: 8px; border-radius: 999px; background: #f1f3f7; overflow: hidden; }
        .tms-bar-isi { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg, #f5a623, #f26522); }
        .tms-bar-nilai { min-width: 22px; text-align: right; font-weight: 700; color: #4b5563; }

        /* Chip saringan memakai bahasa yang sama dengan tombol "Tulis Testimoni"
           di beranda: kotak putih bergaris tipis, jingga saat aktif. */
        .tms-saring { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
        .tms-chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 16px; border-radius: 12px; cursor: pointer;
            background: #fff; border: 1px solid var(--ph-line, #eceff4);
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 700; font-size: .85rem; color: #4b5563;
            transition: border-color .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .tms-chip:hover { border-color: var(--ph-orange, #f26522); color: var(--ph-orange, #f26522); }
        .tms-chip.is-aktif { background: var(--ph-orange, #f26522); border-color: var(--ph-orange, #f26522); color: #fff; }
        .tms-chip i { color: #f5a623; font-size: .8rem; }
        .tms-chip.is-aktif i { color: #fff; }
        .tms-jumlah { margin-left: auto; font-size: .82rem; color: var(--ph-muted, #6b7280); }
        @media (max-width: 575.98px) { .tms-jumlah { margin-left: 0; flex-basis: 100%; } }

        /* Kartu memenuhi tinggi kolomnya — pola yang sama dipakai Bundling,
           supaya deret kartunya tidak bergerigi di tepi bawah. */
        .tms-kisi > [class*="col-"] { display: flex; }
        .tms-kisi > [class*="col-"] > .tm-card { width: 100%; }
        .tms-kisi .tm-text { min-height: 78px; }

        .tms-alat { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
        .tms-cari { flex: 1 1 260px; display: flex; align-items: center; gap: 9px; margin: 0; padding: 10px 15px; background: #fff; border: 1px solid var(--sts-line, #eceff4); border-radius: 12px; }
        .tms-cari i { color: #9aa1ad; }
        .tms-cari input { flex: 1 1 auto; min-width: 0; border: 0; outline: 0; background: none; font-size: .9rem; color: var(--sts-ink, #1c1f26); }
        .tms-urut { display: inline-flex; align-items: center; gap: 8px; margin: 0; padding: 8px 14px; background: #fff; border: 1px solid var(--sts-line, #eceff4); border-radius: 12px; font-size: .82rem; font-weight: 700; color: var(--sts-muted, #6b7280); }
        .tms-urut select { border: 0; outline: 0; background: none; font-weight: 700; font-size: .85rem; color: var(--sts-ink, #1c1f26); }
        .tms-baca { margin-top: 2px; padding: 0; border: 0; background: none; font-size: .82rem; font-weight: 700; color: var(--sts-jingga, #f26522); cursor: pointer; }
        .tms-tanggal { display: block; font-size: .74rem; color: #9aa1ad; margin-top: 2px; }
        [x-cloak] { display: none !important; }

        .tms-kosong { padding: 54px 20px; text-align: center; background: #fff; border: 1px dashed var(--ph-line, #eceff4); border-radius: 18px; }
        .tms-kosong i { font-size: 2rem; color: #cbd5e1; }
        .tms-kosong p { margin: 10px 0 0; color: var(--ph-muted, #6b7280); }

        .tms-lanjut {
            display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap;
            margin-top: 20px; padding: 24px 26px;
            background: #fff; border: 1px solid var(--ph-line, #eceff4); border-radius: 18px;
        }
        .tms-lanjut b { display: block; font-family: 'Poppins', sans-serif; font-size: 1.05rem; color: var(--ph-ink, #1c1f26); }
        .tms-lanjut span { display: block; font-size: .9rem; color: var(--ph-muted, #6b7280); }
        .tms-tombol { display: flex; gap: 10px; flex-wrap: wrap; }
        .tms-btn {
            display: inline-flex; align-items: center; gap: 8px; padding: 11px 18px; border-radius: 12px;
            border: 1px solid var(--ph-line, #eceff4); background: #fff; color: #4b5563;
            font-weight: 700; font-size: .88rem; text-decoration: none; white-space: nowrap;
        }
        .tms-btn:hover { border-color: var(--ph-orange, #f26522); color: var(--ph-orange, #f26522); }
        .tms-btn.is-utama { background: var(--ph-orange, #f26522); border-color: var(--ph-orange, #f26522); color: #fff; }
        .tms-btn.is-utama:hover { background: #d9550f; border-color: #d9550f; color: #fff; }
        .tms-btn i.bi, .tms-btn i.bi::before { display: block; line-height: 1; }

        @media (max-width: 767.98px) {
            .tms-nilai { flex: 1 1 100%; }
            .tms-lanjut { padding: 20px; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-chat-quote-fill"></i> Testimoni</span>
                <h1>Apa Kata Pelanggan Kami</h1>
                <p>
                    {{ $total }} cerita dari pelanggan Phoenix Digital. Semuanya ditinjau admin lebih dulu,
                    dan yang menulisnya orang yang benar-benar memesan.
                </p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}" wire:navigate>Beranda</a></li>
                    <li class="current">Testimoni</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="section">
        <div class="container">
            @if ($total)
                <div class="tms-ringkas">
                    <div class="tms-nilai">
                        <b>{{ number_format($rata, 1, ',', '.') }}</b>
                        <div>
                            <span class="tm-stars" aria-hidden="true">
                                @for ($i = 1; $i <= 5; $i++)<i class="bi {{ $i <= round($rata) ? 'bi-star-fill' : 'bi-star' }}"></i>@endfor
                            </span>
                            <small>dari {{ $total }} testimoni</small>
                        </div>
                    </div>
                    <div class="tms-bar">
                        {{-- $barBintang, BUKAN $bintang: nama itu akan menimpa properti
                             saringan komponen untuk sisa halaman — chip saringan jadi
                             tidak pernah tampak aktif dan keterangan jumlahnya salah. --}}
                        @foreach ($sebaran as $barBintang => $barJumlah)
                            <div class="tms-bar-baris">
                                <span>{{ $barBintang }} <i class="bi bi-star-fill" style="color:#f5a623"></i></span>
                                <span class="tms-bar-alur"><span class="tms-bar-isi" style="width: {{ $total ? round($barJumlah / $total * 100) : 0 }}%"></span></span>
                                <span class="tms-bar-nilai">{{ $barJumlah }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="tms-alat">
                    <label class="tms-cari">
                        <i class="bi bi-search"></i>
                        <input type="search" wire:model.live.debounce.400ms="cari" placeholder="Cari kata di testimoni…" aria-label="Cari testimoni">
                    </label>
                    <label class="tms-urut">
                        <span>Urutkan</span>
                        <select wire:model.live="urut" aria-label="Urutkan testimoni">
                            <option value="pilihan" @selected($urut === 'pilihan')>Pilihan kami</option>
                            <option value="baru" @selected($urut === 'baru')>Terbaru</option>
                            <option value="tinggi" @selected($urut === 'tinggi')>Bintang tertinggi</option>
                        </select>
                    </label>
                </div>

                <div class="tms-saring" role="group" aria-label="Saring menurut bintang">
                    <button type="button" class="tms-chip {{ $bintang === '' ? 'is-aktif' : '' }}" wire:click="setBintang('')">Semua</button>
                    @foreach ($sebaran as $nilai => $jumlah)
                        @if ($jumlah)
                            <button type="button" class="tms-chip {{ $bintang === (string) $nilai ? 'is-aktif' : '' }}" wire:click="setBintang('{{ $nilai }}')">
                                {{ $nilai }} <i class="bi bi-star-fill"></i> ({{ $jumlah }})
                            </button>
                        @endif
                    @endforeach

                    {{-- Jumlah hasil setelah menyaring: tanpa ini pengunjung tidak
                         tahu berapa banyak yang sedang ia lihat. --}}
                    @if ($testimoni->total())
                        <span class="tms-jumlah">
                            Menampilkan {{ $testimoni->count() }} dari {{ $testimoni->total() }}{{ $bintang !== '' ? ' testimoni '.$bintang.' bintang' : ' testimoni' }}{{ $cari !== '' ? ' untuk "'.$cari.'"' : '' }}
                        </span>
                    @endif
                </div>
            @endif

            @if ($testimoni->isEmpty())
                <div class="tms-kosong">
                    <i class="bi bi-stars"></i>
                    <p>{{ $bintang !== '' ? 'Belum ada testimoni dengan '.$bintang.' bintang.' : 'Belum ada testimoni. Jadilah yang pertama berbagi pengalaman Anda!' }}</p>
                </div>
            @else
                <div class="row g-4 tms-kisi">
                    @foreach ($testimoni as $t)
                        <div class="col-lg-4 col-md-6" wire:key="sts-{{ $t->id }}">
                            {{-- Kelas .tm-card dst. sama persis dengan slider beranda. --}}
                            <div class="tm-card">
                                <i class="bi bi-quote tm-quote"></i>
                                <div class="tm-stars">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= (int) $t->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </div>
                                @php $tmPanjang = mb_strlen((string) $t->pesan) > 180; @endphp
                                <div x-data="{ penuh: false }">
                                    <p class="tm-text" x-show="!penuh">{{ $tmPanjang ? \Illuminate\Support\Str::limit($t->pesan, 180) : $t->pesan }}</p>
                                    @if ($tmPanjang)
                                        <p class="tm-text" x-show="penuh" x-cloak>{{ $t->pesan }}</p>
                                        <button type="button" class="tms-baca" x-on:click="penuh = !penuh"
                                            x-text="penuh ? 'Tutup' : 'Baca selengkapnya'">Baca selengkapnya</button>
                                    @endif
                                </div>
                                <div class="tm-person">
                                    {{-- WAJIB nama_publik, JANGAN $t->nama: pengirim anonim hanya boleh
                                         tampil sebagai huruf depan namanya di halaman publik. --}}
                                    <span class="tm-avatar">
                                        @if ($t->foto && \Storage::disk('public')->exists('img/testimoni/'.$t->foto))
                                            <img src="{{ asset('storage/img/testimoni/'.$t->foto) }}" alt="{{ $t->nama_publik }}" loading="lazy">
                                        @else
                                            {{ strtoupper(mb_substr($t->nama_publik, 0, 1)) }}
                                        @endif
                                    </span>
                                    <span class="tm-meta">
                                        <span class="tm-name">{{ $t->nama_publik }}</span>
                                        @if ($t->peran)
                                            <span class="tm-role">{{ $t->peran }}</span>
                                        @endif
                                        <span class="tms-tanggal">{{ $t->created_at?->locale('id')->translatedFormat('F Y') }}</span>
                                        @if ($t->customer_id && ($t->customer->belanja_selesai_count ?? 0) > 0)
                                            <span class="tm-verified" title="Pembeli asli — pesanannya sudah selesai">
                                                <i class="bi bi-patch-check-fill"></i>
                                                <span class="tm-verified-txt">
                                                    <b>Pembeli Asli</b>
                                                    <small>Sudah belanja {{ $t->customer->belanja_selesai_count }} kali</small>
                                                </span>
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($testimoni->hasPages())
                    <div class="mt-5 ph-pagination">
                        {{ $testimoni->links('pagination.ph') }}
                    </div>
                @endif
            @endif

            {{-- Formulir kiriman ikut dibawa ke sini: pengunjung yang mendarat
                 langsung di halaman ini dulu harus balik ke beranda dulu. --}}
            <div class="mt-4">
                <livewire:components.testimonials :tampilkan-daftar="false" />
            </div>

            <div class="tms-lanjut">
                <div>
                    <b>Siap mencoba sendiri?</b>
                    <span>Akun premium, tools AI, dan jasa cek plagiasi — semuanya bergaransi.</span>
                </div>
                <div class="tms-tombol">
                    <a href="{{ route('shop.index') }}" wire:navigate class="tms-btn is-utama"><i class="bi bi-bag-check"></i> Lihat Produk</a>
                    <a href="{{ route('homepage') }}" wire:navigate class="tms-btn"><i class="bi bi-house"></i> Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </section>
</main>
