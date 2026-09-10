{{-- Panel penutup.

     Isinya sengaja TIDAK mengulang baris jaminan di kaki hero. Yang di hero
     menjawab "aman tidak belanja di sini" sebelum orang melihat barangnya; yang
     di sini menjawab pertanyaan yang baru muncul SETELAH ia melihat harga —
     soal keaslian lisensi dan keamanan transaksinya.

     Mengulang empat kalimat yang sama persis dua kali dalam satu halaman
     membuat keduanya berhenti dibaca. "Garansi uang kembali" sempat muncul di
     kedua tempat; sekarang ia hanya hidup di hero, dan itu dijaga uji supaya
     tidak kembali kembar. --}}
<section class="jaminan-bawah" aria-label="Jaminan Phoenix Digital">
    <style>
        /* Inline: public/build masuk .gitignore dan tidak ikut terdeploy. */
        .jaminan-bawah { padding: 8px 0 4px; }

        /* Panel putih bergaris tipis, bukan bidang persik.

           Sepanjang halaman ini kartu memakai bahasa yang sama — putih,
           bingkai 1px, sudut membulat. Satu panel berwarna persik di
           penghabisan terbaca seperti tempelan dari rancangan lain. Warnanya
           dipindah ke tempat yang justru berguna: ubin ikonnya. */
        .jb-panel {
            display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
            align-items: center; gap: 0;
            background: #fff; border: 1px solid #eceff4; border-radius: 20px;
            padding: 8px;
        }

        .jb-butir {
            display: flex; align-items: center; gap: 13px;
            padding: 18px 20px; min-width: 0;
            border-radius: 14px;
            transition: background .22s ease;
        }
        .jb-butir:hover { background: color-mix(in srgb, var(--c) 6%, #fff); }

        /* Pemisah setipis rambut antar butir, bukan bingkai penuh. Empat kotak
           di dalam satu kotak membuat panelnya terbaca berlapis-lapis; garis
           tegak sudah cukup memisahkan tanpa menambah satu lapis pun. */
        .jb-butir + .jb-butir, .jb-sosial {
            box-shadow: inset 1px 0 0 #f1f4f8;
        }

        /* Ubin ikon berwarna, seperti kartu Kategori Populer dan Cara Pesan. */
        .jb-ikon {
            flex: 0 0 auto; width: 40px; height: 40px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 12%, #fff);
            color: var(--c); font-size: 1.05rem;
        }
        /* IKON BENAR-BENAR DI TENGAH.

           Glif Bootstrap Icons dibungkus baris teks dengan tinggi baris
           bawaannya sendiri, jadi ia duduk sedikit di bawah pusat kotak —
           selisihnya kecil, tapi pada ubin 40px yang berjajar empat, mata
           langsung menangkapnya sebagai "ada yang miring".

           Tinggi barisnya dinolkan DAN ::before dijadikan block: keduanya
           perlu, karena tanpa yang kedua glifnya masih membawa ruang bawah
           dari font metriknya. */
        .jb-ikon i.bi { display: block; line-height: 1; }
        .jb-ikon i.bi::before { display: block; line-height: 1; }

        .jb-teks { min-width: 0; }
        .jb-teks strong {
            display: block;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700;
            font-size: .9rem; color: #1c1f26; line-height: 1.3; letter-spacing: -.01em;
        }
        .jb-teks span { display: block; font-size: .8rem; color: #8b939f; line-height: 1.4; margin-top: 2px; }

        /* Keterangan pelanggan: kesimpulan, bukan butir kelima. Dibedakan
           dengan latar hangat supaya ia berhenti dibaca sebagai satu jaminan
           lagi. */
        .jb-sosial {
            display: flex; align-items: center; gap: 13px; flex: 0 0 auto;
            padding: 18px 22px; border-radius: 14px;
            background: linear-gradient(135deg, #fff6ef, #fff);
        }
        .jb-sosial-ikon {
            flex: 0 0 auto; width: 40px; height: 40px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #f26522, #fb8b3c); color: #fff; font-size: 1.05rem;
            box-shadow: 0 6px 14px rgba(242, 101, 34, .26);
        }
        .jb-sosial-ikon i.bi { display: block; line-height: 1; }
        .jb-sosial-ikon i.bi::before { display: block; line-height: 1; }
        .jb-sosial .jb-teks strong {
            display: block;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
            font-size: 1rem; color: #1c1f26; line-height: 1.25; letter-spacing: -.015em;
        }
        /* Diikat ke .jb-teks, bukan ke seluruh <span> di dalam .jb-sosial.
           Aturan `.jb-sosial span` ikut mengenai UBIN IKONNYA — yang juga
           sebuah <span> — dan karena kekhususannya lebih tinggi daripada
           `.jb-sosial-ikon`, ia menimpa ukuran hurufnya: glifnya mengecil dari
           16,8px jadi 12,5px tanpa ada yang menyadarinya, dan ubin keempat
           berjajar itu jadi tidak seragam. */
        .jb-sosial .jb-teks span { display: block; font-size: .78rem; color: #8b7c6d; line-height: 1.4; margin-top: 2px; }

        @media (max-width: 1199.98px) {
            .jb-panel { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            /* Pemisah tegak tidak lagi menggambarkan apa pun begitu butirnya
               menumpuk jadi beberapa baris. */
            .jb-butir + .jb-butir, .jb-sosial { box-shadow: none; }
            .jb-sosial { grid-column: 1 / -1; justify-content: center; margin-top: 4px; }
        }
        @media (max-width: 575.98px) {
            .jb-panel { grid-template-columns: 1fr; padding: 6px; border-radius: 16px; }
            .jb-butir { padding: 14px 16px; gap: 11px; }
            /* Kelima ubin mengecil bersama. Empat mengecil dan satu tetap
               membuat yang satu itu terlihat seperti terlewat, bukan seperti
               disengaja. */
            .jb-ikon, .jb-sosial-ikon { width: 36px; height: 36px; font-size: .95rem; }
            .jb-sosial { padding: 16px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .jb-butir { transition: none; }
        }
    </style>

    <div class="container">
        <div class="jb-panel">
            @foreach ([
                ['#d97706', 'bi-tag-fill', 'Harga Terbaik', 'Tanpa biaya tersembunyi'],
                ['#2563eb', 'bi-patch-check-fill', 'Produk Original', 'Lisensi resmi & legal'],
                ['#16a34a', 'bi-lock-fill', 'Transaksi Aman', 'Data terlindungi'],
                ['#7c3aed', 'bi-arrow-repeat', 'Garansi Penggantian', 'Bila akun bermasalah'],
            ] as [$warna, $ikon, $judul, $sub])
            <div class="jb-butir" style="--c: {{ $warna }}">
                <span class="jb-ikon"><i class="bi {{ $ikon }}"></i></span>
                <span class="jb-teks">
                    <strong>{{ $judul }}</strong>
                    <span>{{ $sub }}</span>
                </span>
            </div>
            @endforeach

            <div class="jb-sosial">
                <span class="jb-sosial-ikon"><i class="bi bi-people-fill"></i></span>
                <span class="jb-teks">
                    <strong>Dipercaya 5.000+</strong>
                    <span>pelanggan di seluruh Indonesia</span>
                </span>
            </div>
        </div>
    </div>
</section>
