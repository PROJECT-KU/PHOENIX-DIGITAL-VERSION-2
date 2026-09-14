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

        /* SATU BARIS yang digeser, bukan membungkus. Pita setinggi dua-tiga
           baris kehilangan bentuknya sebagai pita, dan tingginya ikut berubah
           tiap kali jumlah mereknya berubah. */
        .dp-geser { position: relative; flex: 1 1 320px; min-width: 0; }
        .dp-deret {
            display: flex; align-items: center; flex-wrap: nowrap; gap: 10px;
            overflow-x: auto; scroll-behavior: smooth;
            scroll-snap-type: x proximity; scroll-padding-left: 4px;
            scrollbar-width: none; -ms-overflow-style: none;
            list-style: none; margin: 0; padding: 4px;
        }
        .dp-deret > li { scroll-snap-align: start; }

        /* Tepi memudar: tanda deretnya berlanjut. Sisi yang sudah mentok
           dimatikan lewat kelas .di-awal / .di-akhir dari skrip di bawah. */
        /* Selebar tombol panah + sedikit lagi: pil yang kebetulan berada di
           balik tombol ikut memudar, jadi tombolnya tidak terbaca sebagai
           bagian dari pil itu. */
        .dp-geser::before, .dp-geser::after {
            content: ""; position: absolute; top: 0; bottom: 0; width: 52px; z-index: 1;
            pointer-events: none; opacity: 1; transition: opacity .2s ease;
        }
        .dp-geser::before { left: 0; background: linear-gradient(90deg, #fff, rgba(255, 255, 255, 0)); }
        .dp-geser::after { right: 0; background: linear-gradient(270deg, #fff, rgba(255, 255, 255, 0)); }
        .dp-geser.di-awal::before { opacity: 0; }
        .dp-geser.di-akhir::after { opacity: 0; }

        /* Tombol panah: di layar sentuh deret bisa digeser jari, di desktop
           tidak ada isyarat apa pun tanpa tombol ini. Disembunyikan saat
           seluruh merek sudah muat (kelas .muat dari skrip). */
        .dp-panah {
            position: absolute; top: 50%; transform: translateY(-50%); z-index: 2;
            width: 34px; height: 34px; border-radius: 50%; border: 1px solid #e6eaf0;
            background: #fff; color: #1c1f26; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(28, 31, 38, .12);
            transition: background .2s ease, color .2s ease, opacity .2s ease;
        }
        .dp-panah:hover { background: #f26522; border-color: #f26522; color: #fff; }
        .dp-panah i.bi { line-height: 1; font-size: .95rem; }
        .dp-panah i.bi::before { display: block; line-height: 1; }
        .dp-panah.kiri { left: -6px; }
        .dp-panah.kanan { right: -6px; }
        .dp-geser.muat .dp-panah,
        .dp-geser.di-awal .dp-panah.kiri,
        .dp-geser.di-akhir .dp-panah.kanan { opacity: 0; pointer-events: none; }
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
            .dp-geser { flex: 1 1 100%; }
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
               menyentuh tepi layar — isyarat baku "masih ada lanjutannya".
               Tombol panah tidak dipakai: di layar sentuh jari sudah cukup,
               dan tombolnya hanya akan menutupi logo. */
            .dp-deret { gap: 8px; padding: 2px 16px; scroll-padding-left: 16px; }
            .dp-panah { display: none; }
            .dp-geser::before, .dp-geser::after { width: 26px; }

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
            <div class="dp-geser" data-geser>
                {{-- aria-hidden: tombol ini hanya pintasan tetikus. Pembaca layar
                     menelusuri daftar mereknya langsung, jadi mengumumkan
                     "tombol geser" hanya menambah kebisingan. --}}
                <button type="button" class="dp-panah kiri" data-arah="-1" aria-hidden="true" tabindex="-1">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="dp-panah kanan" data-arah="1" aria-hidden="true" tabindex="-1">
                    <i class="bi bi-chevron-right"></i>
                </button>

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
            </div>

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

    {{-- Tombol geser + penanda ujung deret.

         Dipasang SEKALI untuk seluruh sesi dan mencari elemennya tiap kali
         dipanggil: halaman dibuka lewat wire:navigate, jadi elemen yang
         ditangkap sekali di awal sudah bukan elemen yang ada di layar. --}}
    <script data-navigate-once>
        (function () {
            if (window.__dpGeserTerpasang) return;
            window.__dpGeserTerpasang = true;

            const perbarui = (geser) => {
                const deret = geser.querySelector('.dp-deret');
                if (!deret) return;
                const sisa = deret.scrollWidth - deret.clientWidth;
                geser.classList.toggle('muat', sisa <= 2);
                geser.classList.toggle('di-awal', deret.scrollLeft <= 2);
                geser.classList.toggle('di-akhir', deret.scrollLeft >= sisa - 2);
            };

            const pasang = () => {
                document.querySelectorAll('[data-geser]').forEach((geser) => {
                    perbarui(geser);
                    if (geser.dataset.siap) return;
                    geser.dataset.siap = '1';

                    const deret = geser.querySelector('.dp-deret');
                    deret.addEventListener('scroll', () => perbarui(geser), { passive: true });
                    new ResizeObserver(() => perbarui(geser)).observe(deret);

                    geser.querySelectorAll('.dp-panah').forEach((tombol) => {
                        tombol.addEventListener('click', () => {
                            // Digeser selebar yang terlihat dikurangi satu pil,
                            // supaya selalu ada merek yang ikut berpindah dan
                            // pembaca tidak kehilangan tempatnya.
                            const langkah = Math.max(160, deret.clientWidth - 120);
                            deret.scrollBy({ left: langkah * Number(tombol.dataset.arah), behavior: 'smooth' });
                        });
                    });
                });
            };

            document.addEventListener('DOMContentLoaded', pasang);
            document.addEventListener('livewire:navigated', pasang);
            window.addEventListener('load', pasang);
            pasang();
        })();
    </script>
</section>
@endif
