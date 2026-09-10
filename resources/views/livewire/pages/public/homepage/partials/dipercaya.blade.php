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
            font-family: 'Poppins', sans-serif; font-weight: 700; color: #1c1f26;
            font-size: .95rem; line-height: 1.35; margin: 0;
        }

        /* Logonya menggulir mendatar di layar sempit, bukan membungkus jadi
           beberapa baris: pita yang tiba-tiba setinggi tiga baris merusak
           irama halaman, sedangkan menggulir tetap terbaca sebagai satu pita. */
        /* space-between, bukan gap tetap. Deret ini melebar mengisi ruang yang
           tersisa, tapi isinya hanya lima sampai delapan merek — dengan jarak
           tetap, sisa ruangnya menumpuk jadi satu celah menganga di ujung
           kanan. Dibagi rata, celah itu berubah jadi jarak antar merek dan
           deretnya terbaca sebagai satu barisan yang disengaja.

           Saat isinya melebihi lebar (di layar sempit), space-between tidak
           berpengaruh apa-apa dan gap minimumnya yang berlaku. */
        .dp-deret {
            flex: 1 1 320px; min-width: 0;
            display: flex; align-items: center; justify-content: space-between; gap: 26px;
            overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none;
            padding-bottom: 2px;
            list-style: none; margin: 0;
        }
        .dp-deret::-webkit-scrollbar { display: none; }
        .dp-deret > li { flex: 0 0 auto; }

        .dp-merek {
            flex: 0 0 auto;
            display: inline-flex; align-items: center; gap: 9px;
            text-decoration: none; white-space: nowrap;
        }

        /* Ubin seukuran untuk semua logo. Gambar produk datang dengan rasio
           yang berbeda-beda — ada yang persegi, ada yang memanjang — dan tanpa
           ubin bersama, garis dasar tiap merek naik-turun sendiri dan deretnya
           terlihat goyah. */
        .dp-merek img {
            width: 34px; height: 34px; object-fit: contain; border-radius: 8px;
            padding: 2px; background: #fff;
            /* Warna logo yang beraneka ragam akan mengalahkan seluruh halaman.
               Diredam ke abu-abu, lalu kembali berwarna saat disentuh — pita
               ini penanda kepercayaan, bukan etalase kedua.

               Keabuan penuh membuat logo bergaris tipis nyaris lenyap, jadi
               disisakan sedikit warna dan opasitasnya dinaikkan. */
            filter: grayscale(.85); opacity: .78;
            transition: filter .25s ease, opacity .25s ease;
        }

        /* Bobot 600, bukan 700: nama merek di sini keterangan gambar, dan
           lima nama tebal berjajar akan menyaingi judul bagian mana pun yang
           ada di dekatnya. */
        .dp-merek span {
            font-family: 'Poppins', sans-serif; font-weight: 600;
            font-size: .95rem; color: #6b7280; letter-spacing: -.01em;
            transition: color .25s ease;
        }

        .dp-merek:hover img { filter: grayscale(0); opacity: 1; }
        .dp-merek:hover span { color: #1c1f26; }

        .dp-lagi {
            flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px;
            color: #f26522; font-weight: 600; font-size: .88rem; text-decoration: none;
            white-space: nowrap;
            padding-left: 26px; border-left: 1px solid #eef1f5;
        }
        .dp-lagi b { font-weight: 800; }
        .dp-lagi:hover { color: #d9531a; }
        .dp-lagi i.bi { line-height: 1; }

        @media (max-width: 767.98px) {
            .dp-kotak { padding: 16px 18px; gap: 12px 18px; }
            .dp-label { max-width: none; flex: 1 1 100%; font-size: .9rem; }
            .dp-deret { gap: 20px; justify-content: flex-start; }
            .dp-lagi { padding-left: 0; border-left: 0; }
            .dp-merek span { font-size: .92rem; }
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
