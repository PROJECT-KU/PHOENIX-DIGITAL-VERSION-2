{{--
    Pita "Orcha tidak terjangkau" dalam bahasa rupa dasbor.

    Tanpa ini layar yang datanya gagal diambil tampil seperti layar yang
    datanya memang kosong — "Belum ada RAB" di atas basis data yang penuh.
    Yang membacanya menyimpulkan datanya hilang, dan itu kesimpulan yang
    jauh lebih mahal daripada API yang sedang tidak menyahut.

    Variabel: $galat (string; kosong berarti aman).
--}}
@if ($galat)
    <section class="dsb-bagian">
        <div class="dsb-rak">
            <div class="dsb-kartu k-12 og-galat">
                <div class="dsb-kartu-isi og-galat-isi">
                    <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-exclamation-triangle-fill"></i></span>
                    <div>
                        <p class="dsb-kartu-judul mb-1">Data Orcha belum bisa ditampilkan</p>
                        <p class="dsb-kartu-sub mb-0">{{ $galat }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .og-galat { border-color: #fde68a; background: #fffbeb; }
        .og-galat-isi { display: flex; align-items: flex-start; gap: 12px; }
    </style>
@endif
