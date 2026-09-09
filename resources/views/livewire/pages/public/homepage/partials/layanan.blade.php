{{-- Jasa pengecekan & parafrase.

     Sebelumnya jasa TIDAK punya tempat sama sekali di beranda, padahal ada di
     menu utama dan Cek Plagiasi Turnitin adalah produk terlaris nomor dua (39
     pesanan dalam 30 hari). Pengunjung yang datang mencari jasa harus menebak
     bahwa ia ada di balik menu "Layanan".

     Dipisahkan dari Produk Terlaris, bukan digabung, karena alur belinya
     berbeda: jasa menuntut pelanggan mengunggah berkas dan menunggu hasil,
     sedangkan akun premium dikirim begitu dibayar. Menyatukannya membuat
     pembeli akun mengira harus mengunggah sesuatu. --}}
@if ($layanan->isNotEmpty())
<section id="layanan-beranda" class="section">
    <style>
        /* Ditulis inline: public/build masuk .gitignore dan tidak ikut terdeploy. */
        /* TANPA kartu. Bagian ini sengaja jadi jeda di antara dua bagian
           berkartu (Produk Terlaris di atas, Paket Bundling di bawah): tiga
           kisi kartu berturut-turut membuat halaman terbaca sebagai satu pola
           yang berulang, sebanyak apa pun isinya berbeda.

           Pemisahnya cukup garis tegak tipis di antara kolom — cara yang lazim
           di tata letak cetak, dan tidak menambah kotak baru. */
        .ly-deret { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0 34px; }
        .ly-kartu {
            display: flex; flex-direction: column; gap: 10px;
            padding: 4px 0 4px 26px; border-left: 1px solid #eceff3;
            text-decoration: none; color: inherit;
            transition: border-color .18s ease;
        }
        .ly-kartu:first-child { padding-left: 0; border-left: 0; }
        .ly-kartu:hover { color: inherit; }
        .ly-ikon {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            background: var(--ly-lembut); color: var(--ly-warna); font-size: 1.25rem;
        }
        .ly-ikon i.bi { line-height: 1; }
        .ly-ikon i.bi::before { display: block; line-height: 1; }
        .ly-nama { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.05rem; color: #23272f; margin: 0; line-height: 1.3; }
        .ly-ket {
            color: #6b7280; font-size: .86rem; line-height: 1.6; margin: 0;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
        }
        .ly-kaki { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-top: auto; padding-top: 14px; border-top: 1px solid #f1f3f6; }
        .ly-harga b { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.15rem; color: #f26522; }
        .ly-harga span { font-size: .78rem; color: #9ca3af; font-weight: 600; }
        .ly-aksi { display: inline-flex; align-items: center; gap: 6px; font-size: .86rem; font-weight: 700; color: var(--ly-warna); }
        .ly-aksi i.bi { line-height: 1; }
        .ly-aksi i.bi::before { display: block; line-height: 1; }

        @media (max-width: 991.98px) {
            .ly-deret { grid-template-columns: repeat(2, 1fr); gap: 26px 30px; }
            /* Kolom pertama tiap baris tidak berpembatas kiri — pembatas di ujung
               baris menggantung tanpa apa pun di sebelahnya. */
            .ly-kartu:nth-child(2n+1) { padding-left: 0; border-left: 0; }
        }
        @media (max-width: 575.98px) {
            /* Menumpuk: pembatasnya ikut berputar jadi garis mendatar di atas
               tiap layanan, karena garis tegak tidak lagi memisahkan apa pun. */
            .ly-deret { grid-template-columns: 1fr; gap: 0; }
            .ly-kartu { padding: 20px 0; border-left: 0; border-top: 1px solid #eceff3; }
            .ly-kartu:first-child { padding-top: 4px; border-top: 0; }
        }
    </style>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <x-kepala-bagian
            ikon="bi-clipboard2-check-fill"
            kicker="Jasa Pengerjaan"
            judul="Layanan Cek & Parafrase"
            sub="Kirim naskah Anda, kami yang mengerjakan. Hasilnya diunduh lewat halaman pribadi Anda sendiri."
            :tautan-url="route('services')"
            tautan-teks="Selengkapnya" />

        <div class="ly-deret">
            @foreach ($layanan as $jasa)
                @php
                    $r = \App\Support\RagamJasa::dariJenis($jasa->jenisLayanan());
                    $perHalaman = $jasa->jasaPerHalaman();
                    $nilai = $perHalaman ? (int) $jasa->hargaPerHalaman() : (int) ($jasa->hargaSekali() ?? 0);
                @endphp
                <a class="ly-kartu" href="{{ route('shop.detail-product', $jasa->id) }}"
                    wire:key="layanan-{{ $jasa->id }}"
                    style="--ly-warna:{{ $r['warna'] }}; --ly-lembut:{{ $r['lembut'] }};">
                    <span class="ly-ikon"><i class="bi bi-{{ $r['ikon'] }}"></i></span>
                    <h3 class="ly-nama">{{ $jasa->nama_akun }}</h3>
                    @if ($jasa->deskripsi)
                        <p class="ly-ket">{{ \Illuminate\Support\Str::limit(strip_tags($jasa->deskripsi), 120) }}</p>
                    @endif
                    <div class="ly-kaki">
                        {{-- Jasa berbayar yang harganya belum terisi TIDAK boleh tampil
                             "Rp0": itu janji gratis yang akan ditagih di keranjang. --}}
                        <span class="ly-harga">
                            @if ($nilai > 0)
                                <b>Rp{{ number_format($nilai, 0, ',', '.') }}</b>
                                <span>{{ $perHalaman ? '/ halaman' : '/ cek' }}</span>
                            @else
                                <span>Lihat rincian harga</span>
                            @endif
                        </span>
                        <span class="ly-aksi">Pesan <i class="bi bi-arrow-right"></i></span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
