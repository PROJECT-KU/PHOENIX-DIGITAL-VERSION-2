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
        .kp-deret {
            display: flex; gap: 14px; overflow-x: auto;
            scrollbar-width: none; -ms-overflow-style: none;
            padding: 4px 2px 6px; scroll-behavior: smooth;
            scroll-snap-type: x proximity;
        }
        .kp-deret::-webkit-scrollbar { display: none; }

        .kp-kartu {
            flex: 0 0 auto; width: 146px; scroll-snap-align: start;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 12px; padding: 22px 14px;
            background: #fff; border: 1px solid #eceff4; border-radius: 16px;
            text-decoration: none; position: relative;
            transition: border-color .22s ease, transform .22s ease, box-shadow .22s ease;
        }

        .kp-kartu:hover {
            border-color: #f7c9ae; transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(242, 101, 34, .10);
        }

        .kp-ikon {
            width: 44px; height: 44px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            background: #fff3ec; color: #f26522; font-size: 1.25rem;
            transition: background .22s ease, color .22s ease;
        }
        .kp-ikon i.bi { line-height: 1; }

        .kp-nama {
            font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .84rem;
            color: #1c1f26; text-align: center; line-height: 1.3; letter-spacing: -.01em;
        }

        /* Jumlahnya disebut, bukan disembunyikan: "9 produk" memberi tahu
           pengunjung apa yang menantinya sebelum ia mengklik. */
        .kp-jumlah { font-size: .72rem; color: #98a1b0; font-weight: 600; }

        /* Kartu pertama — pintu ke seluruh katalog — dibedakan dengan warna
           penuh supaya deretnya punya titik mulai yang jelas. */
        .kp-kartu.kp-semua { background: #fff6f0; border-color: #f9d6c0; }
        .kp-kartu.kp-semua .kp-ikon { background: #f26522; color: #fff; }
        .kp-kartu.kp-semua .kp-nama { color: #d9531a; }

        /* Tombol geser hanya masuk akal bila ada yang bisa digeser; di layar
           lebar deretnya sering sudah muat seluruhnya. */
        .kp-geser { display: inline-flex; gap: 8px; }
        .kp-geser button {
            width: 34px; height: 34px; border-radius: 50%;
            border: 1px solid #e6e9ef; background: #fff; color: #6b7280;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all .2s ease;
        }
        .kp-geser button:hover { border-color: #f26522; color: #f26522; }

        @media (max-width: 575.98px) {
            .kp-kartu { width: 128px; padding: 18px 12px; gap: 10px; }
            .kp-ikon { width: 40px; height: 40px; font-size: 1.1rem; }
        }
    </style>

    <div class="container">
        <x-kepala-bagian
            ikon="bi-grid-fill"
            kicker="Kategori Populer"
            judul="Temukan Solusi Terbaik untuk Kamu"
            sub="Pilih kategori sesuai kebutuhan dan tingkatkan produktivitasmu sekarang."
            :tautan-url="route('shop.index')"
            tautan-teks="Lihat Semua Kategori">
            <x-slot:aksi>
                <div class="kp-geser" aria-hidden="true">
                    <button type="button" data-arah="-1" aria-label="Geser ke kiri"><i class="bi bi-arrow-left"></i></button>
                    <button type="button" data-arah="1" aria-label="Geser ke kanan"><i class="bi bi-arrow-right"></i></button>
                </div>
            </x-slot:aksi>
        </x-kepala-bagian>

        <div class="kp-deret" id="kp-deret">
            <a class="kp-kartu kp-semua" href="{{ route('shop.index') }}">
                <span class="kp-ikon"><i class="bi bi-grid-fill"></i></span>
                <span class="kp-nama">Semua Kategori</span>
                <span class="kp-jumlah">Lihat katalog</span>
            </a>

            @foreach ($kategori as $k)
            <a class="kp-kartu" href="{{ $k['url'] }}">
                <span class="kp-ikon"><i class="bi {{ $k['ikon'] }}"></i></span>
                <span class="kp-nama">{{ $k['label'] }}</span>
                <span class="kp-jumlah">{{ $k['jumlah'] }} produk</span>
            </a>
            @endforeach
        </div>
    </div>

    <script>
        (function () {
            var deret = document.getElementById('kp-deret');
            if (!deret) return;

            document.querySelectorAll('.kp-geser button').forEach(function (b) {
                b.addEventListener('click', function () {
                    deret.scrollBy({ left: parseInt(b.dataset.arah, 10) * 320, behavior: 'smooth' });
                });
            });
        })();
    </script>
</section>
@endif
