@php $merek = App\Support\MerekDipercaya::ambil(7); @endphp

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
        .dp-deret {
            flex: 1 1 320px; min-width: 0;
            display: flex; align-items: center; gap: 26px;
            overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none;
            padding-bottom: 2px;
        }
        .dp-deret::-webkit-scrollbar { display: none; }

        .dp-merek {
            flex: 0 0 auto;
            display: inline-flex; align-items: center; gap: 9px;
            text-decoration: none; white-space: nowrap;
        }

        .dp-merek img {
            width: 30px; height: 30px; object-fit: contain; border-radius: 7px;
            /* Warna logo yang beraneka ragam akan mengalahkan seluruh halaman.
               Diredam ke abu-abu, lalu kembali berwarna saat disentuh — pita
               ini penanda kepercayaan, bukan etalase kedua. */
            filter: grayscale(1); opacity: .62;
            transition: filter .25s ease, opacity .25s ease;
        }

        .dp-merek span {
            font-family: 'Poppins', sans-serif; font-weight: 700;
            font-size: 1rem; color: #7b8493; letter-spacing: -.01em;
            transition: color .25s ease;
        }

        .dp-merek:hover img { filter: grayscale(0); opacity: 1; }
        .dp-merek:hover span { color: #1c1f26; }

        .dp-lagi {
            flex: 0 0 auto; display: inline-flex; align-items: center; gap: 7px;
            color: #f26522; font-weight: 700; font-size: .88rem; text-decoration: none;
            white-space: nowrap;
        }
        .dp-lagi:hover { color: #d9531a; }
        .dp-lagi i.bi { line-height: 1; }

        @media (max-width: 767.98px) {
            .dp-kotak { padding: 16px 18px; gap: 12px 18px; }
            .dp-label { max-width: none; flex: 1 1 100%; font-size: .9rem; }
            .dp-deret { gap: 20px; }
            .dp-merek span { font-size: .92rem; }
        }
    </style>

    <div class="container">
        <div class="dp-kotak">
            <p class="dp-label">Dipercaya oleh ribuan pelanggan</p>

            <div class="dp-deret">
                @foreach ($merek as $m)
                <a class="dp-merek" href="{{ route('shop.index', ['search' => $m['nama']]) }}">
                    <img loading="lazy" src="{{ asset('storage/img/Product/'.$m['gambar']) }}" alt="{{ $m['nama'] }}">
                    <span>{{ $m['nama'] }}</span>
                </a>
                @endforeach
            </div>

            <a class="dp-lagi" href="{{ route('shop.index') }}">dan banyak lagi <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>
@endif
