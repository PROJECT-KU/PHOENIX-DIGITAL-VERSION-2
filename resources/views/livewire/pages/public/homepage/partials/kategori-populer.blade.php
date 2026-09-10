@php $kategori = App\Support\KategoriBeranda::tersedia(); @endphp

@if (count($kategori) >= 3)
{{-- Kategori populer.

     Tiap kartu di sini dijamin BERISI: KategoriBeranda menghitung produknya
     lebih dulu dan membuang kategori yang kosong. Chip yang membawa pengunjung
     ke halaman kosong adalah janji yang tidak ditepati, dan pengunjung yang
     sekali tertipu berhenti mengklik apa pun di halaman ini. --}}
<section id="kategori" class="kategori-populer section">
    <style>
        /* Inline: public/build masuk .gitignore dan tidak ikut terdeploy. */

        /* KISI, bukan penggulung mendatar.
           Delapan kartu selebar 146px berjumlah 1.266px di wadah 1.230px —
           ia meleset 36 piksel, jadi barisnya menggulir sedikit sekali. Baris
           yang HAMPIR muat adalah yang terburuk: pengunjung tidak menyadari ada
           yang tersembunyi, dan yang tersembunyi tidak pernah dibuka. Sebagai
           kisi, semua kartu terlihat sekaligus dan barisnya berhenti goyah. */
        .kp-deret {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(164px, 1fr));
            gap: 14px;
        }

        .kp-kartu {
            position: relative; overflow: hidden;
            display: flex; flex-direction: column; align-items: flex-start;
            gap: 14px; padding: 20px 18px;
            background: #fff; border: 1px solid #eceff4; border-radius: 16px;
            text-decoration: none;
            transition: border-color .22s ease, transform .22s ease, box-shadow .22s ease;
        }

        /* Warna kategori dipakai sebagai --c, jadi satu blok aturan melayani
           kedelapan kartu tanpa satu pun kelas tambahan. */
        .kp-kartu:hover {
            border-color: color-mix(in srgb, var(--c) 35%, #fff);
            transform: translateY(-3px);
            box-shadow: 0 12px 28px color-mix(in srgb, var(--c) 18%, transparent);
        }

        /* Sapuan warna sangat samar di pojok kanan atas. Ia yang membedakan
           kartu satu dengan lainnya sebelum ikonnya sempat dibaca. */
        .kp-kartu::before {
            content: ""; position: absolute; top: -34px; right: -34px;
            width: 96px; height: 96px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent);
            transition: transform .3s ease;
        }
        .kp-kartu:hover::before { transform: scale(1.35); }

        .kp-ikon {
            position: relative; z-index: 1;
            width: 44px; height: 44px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 12%, #fff);
            color: var(--c); font-size: 1.3rem;
        }
        .kp-ikon i.bi { line-height: 1; }

        .kp-teks { position: relative; z-index: 1; }
        .kp-nama {
            display: block;
            font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .95rem;
            color: #1c1f26; line-height: 1.3; letter-spacing: -.01em;
        }
        /* Jumlahnya disebut, bukan disembunyikan: "9 produk" memberi tahu
           pengunjung apa yang menantinya sebelum ia mengklik. */
        .kp-jumlah { display: block; margin-top: 4px; font-size: .78rem; color: #98a1b0; }

        /* Kartu pertama — pintu ke seluruh katalog — berwarna penuh supaya
           deretnya punya titik mulai yang jelas. */
        .kp-kartu.kp-semua {
            background: linear-gradient(140deg, #f26522, #fb8b3c);
            border-color: transparent;
        }
        .kp-kartu.kp-semua::before { background: rgba(255, 255, 255, .16); }
        .kp-kartu.kp-semua .kp-ikon { background: rgba(255, 255, 255, .22); color: #fff; }
        .kp-kartu.kp-semua .kp-nama { color: #fff; }
        .kp-kartu.kp-semua .kp-jumlah { color: rgba(255, 255, 255, .78); }
        .kp-kartu.kp-semua:hover {
            border-color: transparent;
            box-shadow: 0 14px 30px rgba(242, 101, 34, .32);
        }

        @media (max-width: 767.98px) {
            .kp-deret { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .kp-kartu { padding: 16px 14px; gap: 11px; }
            .kp-ikon { width: 38px; height: 38px; font-size: 1.1rem; }
            .kp-nama { font-size: .88rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .kp-kartu, .kp-kartu::before { transition: none; }
        }
    </style>

    <div class="container">
        <x-kepala-bagian
            ikon="bi-grid-fill"
            kicker="Kategori Populer"
            judul="Temukan Solusi Terbaik untuk Kamu"
            sub="Pilih kategori sesuai kebutuhan dan tingkatkan produktivitasmu sekarang."
            :tautan-url="route('shop.index')"
            tautan-teks="Lihat Semua Kategori" />

        <div class="kp-deret">
            <a class="kp-kartu kp-semua" href="{{ route('shop.index') }}">
                <span class="kp-ikon"><i class="bi bi-grid-fill"></i></span>
                <span class="kp-teks">
                    <span class="kp-nama">Semua Kategori</span>
                    <span class="kp-jumlah">Lihat katalog</span>
                </span>
            </a>

            @foreach ($kategori as $k)
            <a class="kp-kartu" href="{{ $k['url'] }}" style="--c: {{ $k['warna'] }}">
                <span class="kp-ikon"><i class="bi {{ $k['ikon'] }}"></i></span>
                <span class="kp-teks">
                    <span class="kp-nama">{{ $k['label'] }}</span>
                    <span class="kp-jumlah">{{ $k['jumlah'] }} produk</span>
                </span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Tombol geser dibuang bersama penggulungnya: pada kisi, semua kartu
         sudah terlihat sekaligus dan tidak ada yang perlu digeser. --}}
</section>
@endif
