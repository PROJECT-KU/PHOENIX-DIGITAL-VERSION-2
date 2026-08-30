{{-- Pratinjau bukti transfer.

     Sebelumnya bukti dibuka di tab baru. Admin yang sedang mencocokkan
     pembayaran harus berpindah bolak-balik, dan tiap kembali halamannya
     dimuat ulang — daftar yang sudah digulung kembali ke atas.

     Gambarnya kini tampil menumpang di atas halaman yang sama, jadi
     halamannya tidak ke mana-mana.

     Ditulis inline, bukan lewat Vite: public/build tidak ikut ter-deploy,
     jadi berkas skrip terpisah tidak akan sampai ke server.

     wire:ignore dipasang karena bagian ini murni urusan tampilan; Livewire
     tidak perlu menggambar ulang atau ikut menyimpan keadaannya. --}}
<div wire:ignore>
    <div id="orcha-pratinjau" class="orcha-pratinjau" hidden>
        <button type="button" class="orcha-pratinjau-tutup" aria-label="Tutup pratinjau">
            <i class="bi bi-x-lg"></i>
        </button>

        <figure class="orcha-pratinjau-isi">
            <img src="" alt="Bukti transfer">
            <figcaption></figcaption>
        </figure>
    </div>

    <style>
        .orcha-pratinjau {
            position: fixed;
            inset: 0;
            z-index: 1080;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3.5rem 1.25rem 1.5rem;
            background: rgba(31, 45, 61, .82);
            backdrop-filter: blur(2px);
        }

        .orcha-pratinjau[hidden] { display: none; }

        .orcha-pratinjau-isi {
            margin: 0;
            max-width: min(920px, 92vw);
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .6rem;
        }

        .orcha-pratinjau-isi img {
            max-width: 100%;
            max-height: 78vh;
            object-fit: contain;
            border-radius: .9rem;
            background: #fff;
            box-shadow: 0 24px 60px rgba(0, 0, 0, .45);
        }

        .orcha-pratinjau-isi figcaption {
            color: #cfe4f2;
            font-size: .84rem;
            text-align: center;
        }

        .orcha-pratinjau-tutup {
            position: absolute;
            top: 1rem;
            right: 1.15rem;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .14);
            color: #fff;
            font-size: .95rem;
            line-height: 1;
            transition: background .15s ease;
        }

        .orcha-pratinjau-tutup:hover { background: rgba(255, 255, 255, .28); }

        /* Gambar kecil yang bisa diklik: sekadar penanda bahwa ia bisa dibuka. */
        [data-bukti] { cursor: zoom-in; }
    </style>

    <script>
        // Dipasang sekali saja. Halaman Livewire menggambar ulang isinya berkali-
        // kali, dan pendengar yang menempel di gambar akan ikut hilang setiap
        // kali itu terjadi — maka pendengarnya dipasang di dokumen.
        if (! window.orchaPratinjauSiap) {
            window.orchaPratinjauSiap = true;

            /*
             * Dipindahkan ke <body> sebelum ditampilkan.
             *
             * Layout lemon memasang will-change:opacity pada #page-content.
             * Sifatnya tidak kelihatan tapi berakibat dua hal: elemen
             * position:fixed di dalamnya diukur terhadap kotak konten — bukan
             * terhadap layar — dan seluruh isinya masuk ke lapisan tersendiri,
             * sehingga z-index setinggi apa pun tetap kalah oleh sidebar.
             *
             * Hasilnya pratinjau bukti tergambar hanya di area konten dan
             * tertutup sidebar. Memindahkannya keluar dari #page-content
             * mengembalikannya ke ukuran layar penuh, tanpa perlu mengutak-atik
             * layout yang dipakai seluruh halaman lemon.
             */
            const keBody = (kotak) => {
                if (kotak.parentElement !== document.body) {
                    document.body.appendChild(kotak);
                }
            };

            document.addEventListener('click', function (e) {
                const pemicu = e.target.closest('[data-bukti]');
                const kotak = document.getElementById('orcha-pratinjau');
                if (! kotak) return;

                if (pemicu) {
                    e.preventDefault();
                    keBody(kotak);
                    kotak.querySelector('img').src = pemicu.dataset.bukti;
                    kotak.querySelector('figcaption').textContent = pemicu.dataset.buktiKeterangan || '';
                    kotak.hidden = false;
                    document.body.style.overflow = 'hidden';
                    return;
                }

                // Klik pada latar gelap atau tombol tutup menutup pratinjau;
                // klik pada gambarnya sendiri tidak.
                if (! kotak.hidden && (e.target === kotak || e.target.closest('.orcha-pratinjau-tutup'))) {
                    kotak.hidden = true;
                    document.body.style.overflow = '';
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                const kotak = document.getElementById('orcha-pratinjau');
                if (kotak && ! kotak.hidden) {
                    kotak.hidden = true;
                    document.body.style.overflow = '';
                }
            });
        }
    </script>
</div>
