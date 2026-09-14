@php
    // Delapan, bukan tujuh: pita ini bekerja lewat JUMLAH nama yang dikenali,
    // dan tiap merek tambahan menambah peluang pengunjung menemukan satu yang
    // ia kenal. Yang berkas gambarnya tidak ada tersaring sendiri, jadi angka
    // ini batas atas, bukan janji.
    $merek = App\Support\MerekDipercaya::ambil(8);
    $totalProduk = App\Support\MerekDipercaya::jumlahProduk();
    $sisaProduk = max(0, $totalProduk - count($merek));
@endphp

@if (count($merek) >= 4)
{{-- Pita merek. Muncul hanya bila ada CUKUP merek untuk membentuk deretan —
     tiga logo berjajar di pita selebar layar terbaca sebagai toko yang sepi,
     kebalikan dari maksud pita ini. --}}
<section id="dipercaya" class="dipercaya-pita" aria-label="Merek yang tersedia">
    <style>
        /* Inline: public/build masuk .gitignore dan tidak ikut terdeploy. */
        .dipercaya-pita { padding: 8px 0 4px; }

        .dp-kotak {
            display: flex; align-items: center; gap: 10px 26px; flex-wrap: wrap;
            background: #fff; border: 1px solid #eceff4; border-radius: 18px;
            padding: 20px 26px;
        }

        .dp-label {
            flex: 0 0 auto; max-width: 190px;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700; color: #1c1f26;
            font-size: .95rem; line-height: 1.35; margin: 0;
        }

        /* Membungkus, bukan menggulir. Tiap merek kini sebuah PIL dengan lebar
           sendiri-sendiri; dipaksa satu baris, pil terakhir akan terpotong tepi
           kartu tanpa ada yang memberi tahu bahwa ia bisa digeser. Dibungkus,
           seluruh merek terlihat sekaligus dan barisnya tetap rapi.

           (Di layar sempit aturannya dibalik: menggulir dengan tepi memudar —
           lihat blok @media di bawah.) */
        .dp-deret {
            flex: 1 1 320px; min-width: 0;
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
            scrollbar-width: none; -ms-overflow-style: none;
            list-style: none; margin: 0;
        }
        .dp-deret::-webkit-scrollbar { display: none; }
        .dp-deret > li { flex: 0 0 auto; }

        /* PIL, bukan logo yang mengambang lepas. Bentuk yang sama dipakai di
           semua ukuran layar supaya pita ini terbaca sebagai satu komponen,
           bukan dua tampilan berbeda yang kebetulan isinya sama. */
        .dp-merek {
            flex: 0 0 auto;
            display: inline-flex; align-items: center; gap: 9px;
            padding: 6px 13px 6px 6px;
            background: #fff; border: 1px solid #eceff4; border-radius: 999px;
            box-shadow: 0 2px 8px rgba(28, 31, 38, .05);
            text-decoration: none; white-space: nowrap;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .dp-merek:hover {
            border-color: #dfe4ec; transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(28, 31, 38, .09);
        }

        /* Ubin seukuran untuk semua logo. Gambar produk datang dengan rasio
           yang berbeda-beda — ada yang persegi, ada yang memanjang — dan tanpa
           ubin bersama, garis dasar tiap merek naik-turun sendiri dan deretnya
           terlihat goyah. */
        /* 44px dan BERWARNA PENUH.

           Versi lamanya 34px dan diredam ke abu-abu, dengan alasan agar warna
           logo tidak mengalahkan halaman. Yang terjadi justru sebaliknya: di
           ukuran sekecil itu logo bergaris tipis nyaris lenyap, dan pita yang
           seharusnya jadi bukti malah terbaca sebagai deretan noda kelabu.
           Yang meredam warnanya kini BINGKAI pilnya, bukan logonya. */
        .dp-merek img {
            width: 44px; height: 44px; object-fit: contain; border-radius: 12px;
            padding: 3px; background: #f8fafc;
        }

        .dp-merek span {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700;
            font-size: .9rem; color: #1c1f26; letter-spacing: -.01em;
        }

        .dp-lagi {
            flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px;
            color: #f26522; font-weight: 700; font-size: .88rem; text-decoration: none;
            white-space: nowrap;
            padding: 11px 16px; border-radius: 999px;
            border: 1px dashed rgba(242, 101, 34, .45); background: rgba(242, 101, 34, .06);
        }
        .dp-lagi b { font-weight: 800; }
        .dp-lagi:hover { color: #d9531a; }
        .dp-lagi i.bi { line-height: 1; }

        /* ===== Tablet =====
           Label di kiri hanya masuk akal bila deret pilnya muat sebaris dua.
           Di lebar tablet ia jadi tiga baris pil di kanan sementara kolom kiri
           tinggal ruang kosong setinggi kartu. Labelnya naik ke atas, dan
           seluruh lebar kartu dipakai deretnya. */
        @media (max-width: 991.98px) {
            .dp-kotak { align-items: flex-start; gap: 12px 18px; }
            .dp-label { flex: 1 1 100%; max-width: none; }
            .dp-deret { flex: 1 1 100%; }
        }

        /* ===== Layar sempit =====
           Bentuk pilnya sama dengan layar lebar; yang berubah hanya cara
           deretnya disusun. Membungkus di layar HP membuat pita ini setinggi
           empat baris, jadi di sini ia kembali menggulir mendatar — dengan
           tepi memudar sebagai tanda masih ada lanjutannya. */
        @media (max-width: 767.98px) {
            .dp-kotak { padding: 16px 0 14px; gap: 10px; border-radius: 16px; }
            .dp-label {
                max-width: none; flex: 1 1 100%; font-size: .95rem; font-weight: 800;
                padding: 0 16px;
            }

            /* Deretnya menembus tepi kartu supaya pil pertama & terakhir
               menyentuh tepi layar — isyarat baku "masih ada lanjutannya". */
            .dp-deret {
                flex-wrap: nowrap; overflow-x: auto;
                gap: 8px; justify-content: flex-start;
                padding: 2px 16px; margin: 0;
                scroll-snap-type: x proximity; scroll-padding-left: 16px;
                -webkit-mask-image: linear-gradient(90deg, #000 0, #000 calc(100% - 26px), transparent 100%);
                mask-image: linear-gradient(90deg, #000 0, #000 calc(100% - 26px), transparent 100%);
            }
            .dp-deret > li { scroll-snap-align: start; }

            /* Ajakan ke katalog jadi tombol selebar kartu, bukan pil yang
               tersembunyi di ujung deretan yang menggulir. */
            .dp-lagi { margin: 2px 16px 0; flex: 1 1 100%; justify-content: center; font-size: .9rem; }
        }

    </style>

    <div class="container">
        <div class="dp-kotak">
            <p class="dp-label">Dipercaya oleh ribuan pelanggan</p>

            {{-- Daftar, bukan sekadar deretan <a>. Pembaca layar mengumumkan
                 "daftar 5 butir" sebelum membacanya, jadi pendengarnya tahu
                 sedang menghadapi kumpulan merek, bukan tautan acak. --}}
            <ul class="dp-deret">
                @foreach ($merek as $m)
                <li>
                    <a class="dp-merek" href="{{ route('shop.index', ['search' => $m['nama']]) }}">
                        {{-- alt KOSONG, bukan nama mereknya.

                             Namanya sudah tercetak tepat di sebelah gambar ini.
                             Mengisi alt dengan nama yang sama membuat pembaca
                             layar melafalkannya dua kali ("ChatGPT ChatGPT"),
                             dan teks yang disalin dari halaman pun ikut terbawa
                             dobel. Gambar yang isinya sudah dikatakan teks di
                             sebelahnya adalah gambar HIASAN. --}}
                        <img loading="lazy" alt="" aria-hidden="true"
                             src="{{ asset('storage/img/Product/'.$m['gambar']) }}">
                        <span>{{ $m['nama'] }}</span>
                    </a>
                </li>
                @endforeach
            </ul>

            {{-- Ditutup ANGKA, bukan "dan banyak lagi".

                 "Banyak" tidak bisa diperiksa siapa pun dan karena itu tidak
                 menambah kepercayaan apa pun — ia hanya mengisi ruang. Jumlah
                 yang sebenarnya bisa dihitung sendiri oleh pengunjung begitu ia
                 membuka katalog, dan justru itulah yang membuatnya dipercaya. --}}
            <a class="dp-lagi" href="{{ route('shop.index') }}">
                @if ($sisaProduk > 0)
                    <b>+{{ $sisaProduk }}</b> tools lainnya
                @else
                    Lihat katalog
                @endif
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
@endif
