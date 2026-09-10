<section id="best-sellers" @class(['bd-section section' => $bundlings->isNotEmpty()]) @if ($bundlings->isEmpty()) style="display:none" @endif>
    @include('partials.bundling-deskripsi-style')

    {{-- Gaya ditulis INLINE, bukan di resources/css: berkas CSS dibangun Vite
         dan public/build tidak ikut deploy, jadi gaya di sana tidak pernah
         sampai ke server. --}}
    <style>
        /* Pita krem di belakang bagian ini dicabut.

           Beranda kini berlatar satu warna, dan bagian promo di atasnya sudah
           dijadikan bening lebih dulu. Satu bagian yang masih membawa pita
           kremnya sendiri terbaca seperti sisipan dari halaman lain — dan
           aturan lamanya memakai !important, jadi harus dilawan setara.

           Animasi conic yang berputar di baliknya ikut dimatikan: ia berputar
           terus-menerus tanpa menyampaikan apa pun. */
        #best-sellers.bd-section.section {
            background: none !important;
        }
        #best-sellers.bd-section.section::before,
        #best-sellers.bd-section.section::after {
            display: none !important;
        }

        /* Kartu memenuhi tinggi kolomnya. Tanpa ini kartu yang isinya lebih
           pendek (misalnya tanpa keterangan promo) berhenti lebih dulu, dan
           deretnya terlihat bergerigi di tepi bawah. */
        #best-sellers .row > [class*="col-"] { display: flex; }
        #best-sellers .row > [class*="col-"] > * { width: 100%; }

        .bd-promo-note {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(255, 138, 0, .1);
            color: var(--ph-orange, #fb8c00);
            font-size: .78rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .bd-promo-kode {
            font-weight: 500;
            opacity: .9;
        }

        .bd-promo-kode b {
            letter-spacing: .3px;
        }
    </style>
    @if ($bundlings->isNotEmpty())
    <div class="container">
        <x-kepala-bagian
            ikon="bi-box2-heart-fill"
            kicker="Hemat Lebih"
            judul="Paket Bundling"
            sub="Gabungan beberapa akun premium dalam satu paket — lebih lengkap & lebih hemat."
            :tautan-url="$diBeranda ? route('bundling.product-bundlings') : null"
            tautan-teks="Lihat Semua Paket" />

        <div class="row g-4 justify-content-center">
            @forelse ($bundlings as $item)
                {{-- Di beranda dua per baris pada tablet, bukan tiga.

                     Beranda hanya memuat EMPAT paket; dengan tiga per baris ia
                     jadi 3+1, dan satu kartu kesepian di baris terakhir selalu
                     terbaca seperti ada yang gagal dimuat. Dua per baris
                     menghasilkan 2+2 yang rapi.

                     Di halaman paket tersendiri jumlahnya banyak dan berhalaman,
                     jadi tiga per baris tetap yang paling pas di sana. --}}
                <div class="col-6 {{ $diBeranda ? 'col-md-6' : 'col-md-4' }} col-lg-3" wire:key="bundling-{{ $item->id }}">
                    @include('partials.kartu-paket', ['item' => $item])
                </div>
            @empty
                <div class="col-12">
                    <div class="bd-empty"><i class="bi bi-box-seam"></i> Belum ada paket bundling saat ini.</div>
                </div>
            @endforelse
        </div>

        @if ($diBeranda)
            {{-- Tombol "Lihat Semua Paket" di bawah kisi DIHAPUS.

                 Tautan dengan tulisan yang sama persis sudah ada di kepala
                 bagian — dan keduanya menuju halaman yang BERBEDA: yang di
                 kepala ke /bundling, yang di bawah ke /bundling/product. Dua
                 tautan bertulisan sama yang berujung di tempat berbeda bukan
                 sekadar mubazir; ia membuat pengunjung yang sudah mengklik
                 salah satunya ragu apakah ia sudah melihat semuanya.

                 Yang disisakan tautan di kepala bagian, sama seperti seluruh
                 bagian lain di beranda, dan tujuannya disatukan ke
                 /bundling/product — halaman yang juga dituju kartu kategori
                 "Paket Bundling". --}}
        @else
            {{-- Paginasi seragam dengan halaman shop: pembungkus .ph-pagination
                 yang menengahkan, dan hanya tampil bila memang lebih dari
                 satu halaman. --}}
            @if ($bundlings->hasPages())
                <div class="mt-5 ph-pagination">
                    {{ $bundlings->links('pagination.ph') }}
                </div>
            @endif
        @endif
    </div>

    @endif
</section>
