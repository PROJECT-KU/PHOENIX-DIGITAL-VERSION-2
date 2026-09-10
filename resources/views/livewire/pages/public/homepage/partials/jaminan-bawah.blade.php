{{-- Panel penutup.

     Isinya sengaja TIDAK mengulang pita jaminan di atas halaman. Yang di atas
     menjawab "aman tidak belanja di sini" sebelum orang melihat barangnya; yang
     di sini menjawab pertanyaan yang baru muncul SETELAH ia melihat harga —
     soal keaslian lisensi dan keamanan transaksinya.

     Mengulang empat kalimat yang sama persis dua kali dalam satu halaman
     membuat keduanya berhenti dibaca. --}}
<section class="jaminan-bawah" aria-label="Jaminan Phoenix Digital">
    <style>
        /* Inline: public/build masuk .gitignore dan tidak ikut terdeploy. */
        .jaminan-bawah { padding: 8px 0 4px; }

        .jb-panel {
            display: flex; align-items: center; flex-wrap: wrap; gap: 22px 34px;
            background: linear-gradient(120deg, #fdf3ec 0%, #fdeadb 100%);
            border: 1px solid #f8e2d2; border-radius: 22px;
            padding: 26px 32px;
        }

        .jb-butir {
            flex: 1 1 190px; min-width: 0;
            display: flex; align-items: center; gap: 12px;
        }
        .jb-ikon {
            flex: 0 0 auto; width: 36px; height: 36px; border-radius: 11px;
            display: inline-flex; align-items: center; justify-content: center;
            background: #fff; border: 1px solid #f6e2d1; color: #f26522; font-size: 1rem;
        }
        .jb-ikon i.bi { line-height: 1; }
        .jb-butir strong {
            display: block; font-family: 'Poppins', sans-serif; font-weight: 700;
            font-size: .88rem; color: #1c1f26; line-height: 1.3;
        }
        .jb-butir span { display: block; font-size: .78rem; color: #8b7c6d; line-height: 1.4; }

        /* Garis pemisah tipis sebelum keterangan pelanggan: tanpa itu, angka
           "5.000+" terbaca sebagai butir kelima, padahal ia kesimpulan. */
        .jb-sosial {
            flex: 0 0 auto; display: flex; align-items: center; gap: 12px;
            padding-left: 30px; border-left: 1px solid #f2d9c4;
        }
        .jb-sosial-ikon {
            width: 40px; height: 40px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: #fff; border: 1px solid #f6e2d1; color: #f26522; font-size: 1.05rem;
        }
        .jb-sosial-ikon i.bi { line-height: 1; }
        .jb-sosial strong {
            display: block; font-family: 'Poppins', sans-serif; font-weight: 700;
            font-size: .88rem; color: #1c1f26; line-height: 1.3;
        }
        .jb-sosial span { display: block; font-size: .78rem; color: #8b7c6d; }

        @media (max-width: 1199.98px) {
            .jb-sosial { padding-left: 0; border-left: 0; flex: 1 1 100%; }
        }
        @media (max-width: 575.98px) {
            .jb-panel { padding: 20px 18px; gap: 16px; border-radius: 18px; }
            .jb-butir { flex: 1 1 100%; }
        }
    </style>

    <div class="container">
        <div class="jb-panel">
            @foreach ([
                ['bi-tag-fill', 'Harga Terbaik', 'Garansi uang kembali'],
                ['bi-patch-check-fill', 'Produk Original', 'Lisensi resmi & legal'],
                ['bi-lock-fill', 'Transaksi Aman', 'Data terlindungi'],
                ['bi-arrow-repeat', 'Garansi Penggantian', 'Bila akun bermasalah'],
            ] as [$ikon, $judul, $sub])
            <div class="jb-butir">
                <span class="jb-ikon"><i class="bi {{ $ikon }}"></i></span>
                <span>
                    <strong>{{ $judul }}</strong>
                    <span>{{ $sub }}</span>
                </span>
            </div>
            @endforeach

            <div class="jb-sosial">
                <span class="jb-sosial-ikon"><i class="bi bi-people-fill"></i></span>
                <span>
                    <strong>Dipercaya 5.000+</strong>
                    <span>pelanggan di seluruh Indonesia</span>
                </span>
            </div>
        </div>
    </div>
</section>
