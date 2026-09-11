@php
    // Bintang penuh / setengah / kosong. Rata-rata 4,3 tampil sebagai empat
    // setengah bintang, bukan dibulatkan jadi empat — pembulatan menyembunyikan
    // perbedaan yang justru dicari pembeli saat membandingkan.
    $kelasBintang = fn ($nilai, $i) => $nilai >= $i - 0.25 ? 'bi-star-fill' : ($nilai >= $i - 0.75 ? 'bi-star-half' : 'bi-star');

    // Tiap pengulas mendapat warnanya sendiri, diturunkan dari namanya supaya
    // tetap sama di setiap kunjungan. Avatar jingga yang identik berjajar
    // terbaca sebagai satu tekstur, bukan sebagai beberapa orang berbeda.
    $palet = ['#7c3aed', '#2563eb', '#0d9488', '#db2777', '#d97706', '#4f46e5', '#16a34a'];
    $warnaPengulas = fn ($nama) => $palet[crc32(mb_strtolower(trim((string) $nama))) % count($palet)];

    // Saat belum ada ulasan, formulir LANGSUNG terbuka: satu-satunya hal yang
    // bisa dilakukan di bagian ini adalah menulis ulasan, dan menyembunyikannya
    // di balik tombol hanya menambah satu klik tanpa memberi apa-apa. Setelah
    // ada ulasan, yang dicari pengunjung adalah membacanya — formulir dilipat.
    $formLipat = $count > 0;

    // Dihitung DI SINI, bukan dalam @php di sela direktif blok. Livewire
    // melewati penanda morph sebuah direktif bila teks sesudahnya sampai tag
    // berikutnya memuat ">" (misal "$n > 0"), dan penanda yang timpang membuat
    // setiap pembaruan daftar (muat lagi, saring, urut) rusak.
    $lipat = fn ($teks) => mb_strlen((string) $teks) > 240 || substr_count((string) $teks, "\n") >= 4;
    $sisa = $jumlahTersaring - $reviews->count();

    // Semua perbandingan dirakit jadi boolean di sini, supaya ekspresi direktif
    // blok di bawah tidak pernah memuat ">" (lihat catatan di atas).
    $perMuat = \App\Livewire\Components\ProductReviews::PER_MUAT;
    $adaAlat = $count > $perMuat;
    $adaSisa = $sisa > 0;
    $semuaTampil = ! $adaSisa && $jumlahTersaring > $perMuat;
@endphp

<div class="ul" x-data="{ rating: @entangle('rating'), showForm: false }">
    <style>
        /* Inline: public/build masuk .gitignore dan tidak ikut terdeploy.
           Prefiks ul-, bukan rev- lama: aturan .rev-* di public-custom-styles.css
           di server sudah beku dan akan terus menempel ke kelas lama. */
        .ul { --uc: var(--c, #f26522); }
        .ul [x-cloak] { display: none !important; }
        .ul i.bi { line-height: 1; }
        .ul i.bi::before { display: block; line-height: 1; }

        /* Dua kolom: panel di kiri, formulir & ulasan di kanan. Formulir yang
           dibentangkan selebar kontainer (1.300px) membuat kolom nama dan
           kotak ulasan terlalu panjang untuk diisi, dan bintang rating
           terlempar jauh dari labelnya. */
        .ul-grid {
            display: grid; grid-template-columns: 360px minmax(0, 1fr);
            gap: 24px; align-items: start;
        }
        /* Kolom grid EKSPLISIT minmax(0, 1fr). Tanpa itu, kolom implisit
           berukuran auto ikut melebar selebar isi terlebarnya — deretan chip
           bintang yang sengaja tidak dibungkus di HP — dan seluruh kolom
           kanan terdorong keluar layar. */
        .ul-kanan { display: grid; grid-template-columns: minmax(0, 1fr); gap: 14px; min-width: 0; }

        /* ===== Panel kiri (ringkasan / ajakan) =====
           Bernada warna kategori produk, dengan sapuan pojok seperti Cara
           Pesan. Lingkarannya kecil dan utuh di dalam sudut — lingkaran besar
           di kartu pendek terpotong jadi noda. */
        .ul-ringkas, .ul-kosong {
            position: relative; overflow: hidden;
            border: 1px solid color-mix(in srgb, var(--uc) 16%, #eceff4); border-radius: 20px;
            background: linear-gradient(165deg, color-mix(in srgb, var(--uc) 8%, #fff) 0%, #fff 60%);
            padding: 26px;
        }
        .ul-ringkas::before, .ul-kosong::before {
            content: ""; position: absolute; top: -38px; right: -38px;
            width: 110px; height: 110px; border-radius: 50%;
            background: color-mix(in srgb, var(--uc) 10%, transparent);
        }
        .ul-ringkas > *, .ul-kosong > * { position: relative; }

        @media (min-width: 992px) {
            /* Panel menemani saat daftar ulasan digulir. */
            .ul-ringkas, .ul-kosong { position: sticky; top: 140px; }
        }

        .ul-ubin {
            flex: 0 0 auto; display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--uc) 12%, #fff); color: var(--uc);
        }
        /* Ubin utama penuh warna — satu titik warna tegas di panel yang
           selebihnya lembut, supaya mata tahu harus mulai dari mana. */
        .ul-ubin-utama {
            width: 56px; height: 56px; border-radius: 17px; font-size: 1.5rem;
            background: linear-gradient(140deg, var(--uc), color-mix(in srgb, var(--uc) 62%, #fff));
            color: #fff; box-shadow: 0 12px 24px -12px color-mix(in srgb, var(--uc) 80%, transparent);
        }
        .ul-panel-judul {
            margin: 18px 0 6px; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 800; font-size: 1.22rem; line-height: 1.3; letter-spacing: -.015em; color: #1c1f26;
        }
        .ul-panel-teks { margin: 0; font-size: .9rem; line-height: 1.65; color: #6b7280; }

        /* Tiga keterangan, masing-masing berwarna sendiri seperti Cara Pesan. */
        .ul-manfaat {
            list-style: none; padding: 18px 0 0; margin: 20px 0 0;
            border-top: 1px dashed color-mix(in srgb, var(--uc) 20%, #e5e7eb);
            display: grid; gap: 12px;
        }
        .ul-manfaat li { display: flex; align-items: center; gap: 12px; font-size: .86rem; line-height: 1.45; color: #4b5563; }
        .ul-manfaat b { color: #1c1f26; font-weight: 700; }
        .ul-manfaat-ic {
            flex: 0 0 auto; width: 34px; height: 34px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--m) 12%, #fff); color: var(--m); font-size: .95rem;
        }

        /* Ringkasan skor */
        .ul-skor { display: flex; align-items: center; gap: 16px; }
        .ul-angka {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
            font-size: 3.1rem; line-height: 1; letter-spacing: -.045em; color: #1c1f26;
        }
        .ul-angka small { font-size: 1rem; font-weight: 700; letter-spacing: 0; color: #9aa2ae; margin-left: 2px; }
        .ul-bintang { display: inline-flex; align-items: center; gap: 3px; color: #f59e0b; font-size: 1rem; }
        .ul-jumlah { display: block; margin-top: 7px; font-size: .82rem; color: #6b7280; }

        /* Sebaran 5→1. Satu angka rata-rata tidak memberi tahu apakah semua
           orang puas, atau separuh sangat puas dan separuh kecewa. */
        .ul-sebaran { list-style: none; padding: 0; margin: 18px 0 0; display: grid; gap: 1px; }
        /* Tiap batang juga penyaring: klik "4 ★" untuk membaca hanya ulasan
           bintang empat. Diperlebar 6px ke kiri-kanan supaya latar sorotnya
           punya ruang tanpa menggeser batang dari tepi panel. */
        .ul-sebaran-btn {
            width: calc(100% + 12px); margin: 0 -6px; padding: 5px 6px;
            display: grid; grid-template-columns: 30px minmax(0, 1fr) 20px;
            align-items: center; gap: 10px; border: 0; border-radius: 9px; background: transparent;
            font: inherit; font-size: .8rem; color: #6b7280; text-align: left; cursor: pointer;
            transition: background .15s ease;
        }
        .ul-sebaran-btn:hover:not(:disabled) { background: color-mix(in srgb, var(--uc) 7%, transparent); }
        .ul-sebaran-btn.is-aktif { background: color-mix(in srgb, var(--uc) 13%, #fff); }
        .ul-sebaran-btn:disabled { cursor: default; opacity: .55; }
        .ul-sebaran-btn:focus-visible { outline: 2px solid var(--uc); outline-offset: 1px; }
        .ul-lbl { display: inline-flex; align-items: center; gap: 4px; font-weight: 700; color: #374151; }
        .ul-lbl i.bi { color: #f59e0b; font-size: .72rem; }
        .ul-bar { height: 8px; border-radius: 99px; background: color-mix(in srgb, var(--uc) 8%, #f1f3f6); overflow: hidden; }
        .ul-bar span {
            display: block; height: 100%; width: var(--w, 0%); border-radius: inherit;
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
        }
        .ul-n { text-align: right; font-variant-numeric: tabular-nums; }

        /* Keterangan yang BENAR: ulasan memang ditahan sampai disetujui admin.
           Tidak ada klaim "pembeli terverifikasi" — formulir ini terbuka untuk
           siapa saja, dan klaim yang tidak bisa dibuktikan menggerus
           kepercayaan yang justru hendak dibangun. */
        .ul-tinjau {
            display: flex; align-items: center; gap: 10px;
            margin: 20px 0 0; padding-top: 16px; border-top: 1px dashed color-mix(in srgb, var(--uc) 20%, #e5e7eb);
            font-size: .8rem; line-height: 1.5; color: #6b7280;
        }
        .ul-tinjau .ul-ubin { width: 30px; height: 30px; border-radius: 9px; font-size: .9rem; }
        .ul-ringkas .ul-tombol { width: 100%; margin-top: 18px; }

        /* ===== Kartu ulasan ===== */
        .ul-kartu {
            --a: #f26522;
            position: relative; overflow: hidden;
            background: #fff; border: 1px solid #eceff4; border-radius: 16px; padding: 20px 22px;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .ul-kartu:hover {
            border-color: color-mix(in srgb, var(--a) 30%, #fff);
            box-shadow: 0 14px 28px -20px color-mix(in srgb, var(--a) 70%, transparent);
        }
        .ul-kutip {
            position: absolute; top: 14px; right: 18px;
            color: color-mix(in srgb, var(--a) 16%, #fff); font-size: 2.3rem;
        }
        .ul-kepala { display: flex; align-items: center; gap: 12px; padding-right: 44px; min-width: 0; }
        .ul-avatar {
            flex: 0 0 auto; width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--a) 14%, #fff); color: var(--a);
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
            font-size: 1.05rem; line-height: 1;
            /* Huruf kapital duduk sedikit di atas tengah kotak barisnya
               (diukur 0,66px); 1px di atas menurunkannya ke tengah bentuknya. */
            padding-top: 1px;
        }
        .ul-siapa { min-width: 0; }
        .ul-nama {
            display: block; font-weight: 700; font-size: .95rem; line-height: 1.3; color: #1c1f26;
            overflow-wrap: anywhere;
        }
        .ul-meta {
            display: flex; align-items: center; flex-wrap: wrap; gap: 4px 8px;
            margin-top: 4px; font-size: .78rem; color: #9aa2ae;
        }
        .ul-meta .ul-bintang { font-size: .78rem; gap: 2px; }
        .ul-titik { width: 3px; height: 3px; border-radius: 50%; background: #d1d5db; }
        .ul-teks {
            margin: 14px 0 0; font-size: .93rem; line-height: 1.7; color: #374151;
            white-space: pre-line; overflow-wrap: anywhere;
        }

        /* ===== Tombol ===== */
        .ul-tombol {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            height: 46px; padding: 0 22px; border: 0; border-radius: 13px; cursor: pointer;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-weight: 700; font-size: .92rem; white-space: nowrap; text-decoration: none;
            box-shadow: 0 10px 20px -10px rgba(242, 101, 34, .6);
            transition: filter .16s ease, transform .16s ease;
        }
        .ul-tombol:hover { filter: brightness(1.05); transform: translateY(-1px); color: #fff; }
        .ul-tombol:disabled { opacity: .7; cursor: wait; transform: none; }
        .ul-tombol-garis {
            background: #fff; color: #374151; border: 1.5px solid #e5e7eb; box-shadow: none;
        }
        .ul-tombol-garis:hover { border-color: var(--uc); color: var(--uc); filter: none; }

        /* ===== Formulir ===== */
        .ul-form {
            background: #fff; border: 1px solid #eceff4; border-radius: 20px; padding: 24px 26px;
            box-shadow: 0 22px 44px -36px rgba(15, 23, 42, .4);
            scroll-margin-top: 140px;
        }
        .ul-form-kepala {
            display: flex; align-items: center; gap: 12px;
            padding-bottom: 18px; margin-bottom: 20px; border-bottom: 1px solid #f2f4f7;
        }
        .ul-form-kepala .ul-ubin { width: 42px; height: 42px; border-radius: 12px; font-size: 1.1rem; }
        .ul-form-kepala h4 {
            margin: 0; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 800; font-size: 1.05rem; color: #1c1f26;
        }
        .ul-form-kepala p { margin: 2px 0 0; font-size: .82rem; color: #6b7280; }
        .ul-baris { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 18px; }
        .ul-field { margin-bottom: 18px; min-width: 0; }
        .ul-field > label, .ul-field > .ul-label {
            display: block; margin-bottom: 7px; font-size: .82rem; font-weight: 700; color: #374151;
        }
        .ul-field .form-control {
            height: auto; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 14px;
            font-size: .92rem; background: #fbfcfd; box-shadow: none;
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .ul-field .form-control::placeholder { color: #a3aab5; }
        .ul-field .form-control:focus {
            background: #fff; border-color: var(--uc);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--uc) 14%, transparent);
        }
        .ul-field textarea.form-control { min-height: 132px; resize: vertical; line-height: 1.6; }

        /* Bintang dalam satu wadah kuning lembut — tanpa wadah, lima bintang
           lepas mengambang dan tidak terbaca sebagai satu kontrol. Tingginya
           sama dengan kolom nama supaya kedua kolom berbaris lurus. */
        .ul-rate {
            display: inline-flex; align-items: center; gap: 2px; height: 48px;
            padding: 0 8px 0 6px; border-radius: 12px;
            background: #fffbeb; border: 1px solid #fde68a;
        }
        .ul-rate button {
            width: 34px; height: 34px; padding: 0; border: 0; border-radius: 9px; background: transparent;
            display: flex; align-items: center; justify-content: center;
            color: #e5d3a1; font-size: 1.3rem; cursor: pointer;
            transition: transform .14s ease, color .14s ease;
        }
        .ul-rate button:hover { transform: scale(1.14); }
        .ul-rate button.is-on { color: #f59e0b; }
        .ul-rate button:focus-visible { outline: 2px solid #f59e0b; outline-offset: 1px; }
        .ul-rate-lbl {
            margin-left: 6px; padding: 5px 11px; border-radius: 99px; min-width: 88px; text-align: center;
            background: #fff; color: #b45309; font-size: .76rem; font-weight: 700; white-space: nowrap;
            box-shadow: 0 1px 2px rgba(180, 83, 9, .12);
        }
        .ul-hitung { display: block; margin-top: 6px; text-align: right; font-size: .74rem; color: #9aa2ae; }
        .ul-err { display: block; margin-top: 6px; font-size: .78rem; color: #e11d48; }
        .ul-kaki {
            display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
            padding-top: 18px; border-top: 1px solid #f2f4f7;
        }
        .ul-catatan { display: inline-flex; align-items: center; gap: 7px; margin: 0; font-size: .78rem; color: #9aa2ae; }
        .ul-aksi { display: flex; gap: 10px; flex-wrap: wrap; }

        /* ===== Terima kasih ===== */
        .ul-terima {
            display: flex; align-items: center; gap: 16px;
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 18px; padding: 20px 22px;
        }
        .ul-terima-ic {
            flex: 0 0 auto; width: 46px; height: 46px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: #16a34a; color: #fff; font-size: 1.25rem;
            box-shadow: 0 10px 20px -10px rgba(22, 163, 74, .7);
        }
        .ul-terima-teks { flex: 1 1 auto; min-width: 0; }
        .ul-terima-teks b { display: block; color: #14532d; font-size: .98rem; }
        .ul-terima-teks p { margin: 2px 0 0; font-size: .86rem; line-height: 1.55; color: #166534; }

        /* ===== Bila ulasannya banyak =====
           Saring bintang + urutan di atas daftar, lima ulasan per muatan, dan
           ulasan panjang dilipat empat baris. Tanpa ini, seratus ulasan
           menjadi kolom setinggi belasan layar yang tak bisa disaring. */
        .ul-alat { display: flex; align-items: center; justify-content: space-between; gap: 10px 16px; flex-wrap: wrap; }
        .ul-chip-deret { display: flex; gap: 8px; flex-wrap: wrap; min-width: 0; }
        .ul-chip {
            flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px;
            height: 36px; padding: 0 14px; border-radius: 99px;
            border: 1px solid #e5e7eb; background: #fff; color: #374151;
            font-size: .82rem; font-weight: 700; white-space: nowrap; cursor: pointer;
            transition: border-color .15s ease, background .15s ease, color .15s ease;
        }
        .ul-chip i.bi { color: #f59e0b; font-size: .76rem; }
        .ul-chip span { font-weight: 600; color: #9aa2ae; font-variant-numeric: tabular-nums; }
        .ul-chip:hover { border-color: color-mix(in srgb, var(--uc) 40%, #e5e7eb); }
        .ul-chip.is-aktif {
            background: var(--uc); border-color: var(--uc); color: #fff;
            box-shadow: 0 8px 16px -10px color-mix(in srgb, var(--uc) 85%, transparent);
        }
        .ul-chip.is-aktif i.bi, .ul-chip.is-aktif span { color: rgba(255, 255, 255, .85); }
        .ul-urut {
            display: inline-flex; align-items: center; gap: 8px; height: 36px; margin: 0; padding: 0 6px 0 12px;
            border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; color: #6b7280; font-size: .82rem;
        }
        .ul-urut select {
            border: 0; background: transparent; font: inherit; font-weight: 700; color: #1c1f26;
            padding: 0 2px; cursor: pointer; outline: none;
        }
        .ul-urut:focus-within { border-color: var(--uc); box-shadow: 0 0 0 3px color-mix(in srgb, var(--uc) 14%, transparent); }

        .ul-teks.is-lipat {
            display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;
        }
        .ul-baca {
            margin-top: 8px; padding: 0; border: 0; background: none;
            color: var(--uc); font-weight: 700; font-size: .82rem; cursor: pointer;
        }
        .ul-baca:hover { text-decoration: underline; }

        .ul-lagi {
            display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 50px;
            border: 1.5px dashed color-mix(in srgb, var(--uc) 35%, #e5e7eb); border-radius: 14px;
            background: color-mix(in srgb, var(--uc) 4%, #fff); color: var(--uc);
            font-weight: 700; font-size: .9rem; cursor: pointer;
            transition: background .15s ease, border-color .15s ease;
        }
        .ul-lagi:hover { background: color-mix(in srgb, var(--uc) 9%, #fff); border-style: solid; }
        .ul-lagi:disabled { opacity: .7; cursor: wait; }
        .ul-lagi small { font-weight: 600; font-size: .8rem; color: #9aa2ae; }
        .ul-habis { margin: 2px 0 0; text-align: center; font-size: .8rem; color: #9aa2ae; }

        @media (max-width: 1199.98px) {
            .ul-grid { grid-template-columns: 320px minmax(0, 1fr); }
            .ul-baris { grid-template-columns: minmax(0, 1fr); gap: 0; }
        }
        @media (max-width: 991.98px) {
            .ul-grid { grid-template-columns: minmax(0, 1fr); }
            .ul-baris { grid-template-columns: minmax(0, 1fr) auto; gap: 18px; }
        }
        @media (max-width: 575.98px) {
            .ul-ringkas, .ul-kosong { padding: 22px 20px; }
            .ul-form { padding: 20px 18px; }
            .ul-kartu { padding: 18px; }
            .ul-baris { grid-template-columns: minmax(0, 1fr); gap: 0; }
            .ul-rate { width: 100%; justify-content: flex-start; }
            .ul-rate-lbl { margin-left: auto; }
            .ul-kaki { flex-direction: column-reverse; align-items: stretch; }
            .ul-aksi .ul-tombol { flex: 1 1 0; }
            .ul-terima { flex-wrap: wrap; }
            .ul-terima .ul-tombol { width: 100%; }
            /* Chip bintang menggulir mendatar, bukan membungkus jadi dua baris. */
            /* nowrap: pada flex berarah kolom yang boleh membungkus, lebar
               tiap baris mengikuti isinya, bukan wadahnya — deretan chip
               lalu melebar keluar layar alih-alih menggulir. */
            .ul-alat { flex-direction: column; align-items: stretch; flex-wrap: nowrap; }
            .ul-chip-deret { flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none; padding: 2px; margin: -2px; }
            .ul-chip-deret::-webkit-scrollbar { display: none; }
            .ul-urut select { flex: 1 1 auto; min-width: 0; }
            .ul-lagi small { display: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ul-kartu, .ul-tombol, .ul-rate button, .ul-field .form-control,
            .ul-chip, .ul-lagi, .ul-sebaran-btn { transition: none; }
        }
    </style>

    <x-kepala-bagian
        ikon="bi-star-fill"
        kicker="Ulasan"
        judul="Ulasan Pelanggan"
        :sub="$count > 0 ? 'Pengalaman pembeli yang sudah memakai produk ini.' : 'Pendapat jujur dari pembeli, untuk pembeli.'" />

    <div class="ul-grid">
        @if ($count > 0)
            <aside class="ul-ringkas" aria-label="Ringkasan rating">
                <div class="ul-skor">
                    <span class="ul-angka">{{ number_format($avg, 1, ',', '') }}<small>/5</small></span>
                    <div>
                        <span class="ul-bintang" aria-label="Rating {{ number_format($avg, 1, ',', '') }} dari 5">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $kelasBintang($avg, $i) }}"></i>
                            @endfor
                        </span>
                        <span class="ul-jumlah">dari {{ $count }} ulasan</span>
                    </div>
                </div>

                <ul class="ul-sebaran">
                    @for ($b = 5; $b >= 1; $b--)
                        @php $n = $sebaran[$b] ?? 0; @endphp
                        <li>
                            <button type="button" class="ul-sebaran-btn {{ $bintang === $b ? 'is-aktif' : '' }}"
                                wire:click="saringBintang({{ $b }})" @disabled($n === 0)
                                aria-pressed="{{ $bintang === $b ? 'true' : 'false' }}"
                                aria-label="Tampilkan ulasan bintang {{ $b }} ({{ $n }})">
                                <span class="ul-lbl">{{ $b }} <i class="bi bi-star-fill"></i></span>
                                <span class="ul-bar"><span style="--w: {{ round($n / $count * 100) }}%"></span></span>
                                <span class="ul-n">{{ $n }}</span>
                            </button>
                        </li>
                    @endfor
                </ul>

                <p class="ul-tinjau">
                    <span class="ul-ubin"><i class="bi bi-shield-check"></i></span>
                    <span>Setiap ulasan ditinjau admin sebelum ditampilkan.</span>
                </p>

                @unless ($submitted)
                    <button type="button" class="ul-tombol" x-show="!showForm"
                        @click="showForm = true; $nextTick(() => $refs.form.scrollIntoView({ behavior: 'smooth', block: 'nearest' }))">
                        <i class="bi bi-pencil-square"></i> Tulis Ulasan
                    </button>
                @endunless
            </aside>
        @else
            <aside class="ul-kosong">
                <span class="ul-ubin ul-ubin-utama"><i class="bi bi-chat-heart-fill"></i></span>
                <h3 class="ul-panel-judul">Belum ada ulasan</h3>
                <p class="ul-panel-teks">Jadilah yang pertama berbagi pengalaman memakai produk ini.</p>

                <ul class="ul-manfaat">
                    <li style="--m: #2563eb">
                        <span class="ul-manfaat-ic"><i class="bi bi-shield-check"></i></span>
                        <span><b>Ditinjau admin</b> sebelum ditampilkan</span>
                    </li>
                    <li style="--m: #16a34a">
                        <span class="ul-manfaat-ic"><i class="bi bi-lightning-charge-fill"></i></span>
                        <span><b>Cukup nama</b> dan beberapa kalimat</span>
                    </li>
                    <li style="--m: #7c3aed">
                        <span class="ul-manfaat-ic"><i class="bi bi-people-fill"></i></span>
                        <span><b>Membantu pembeli lain</b> memilih produk</span>
                    </li>
                </ul>
            </aside>
        @endif

        <div class="ul-kanan">
            @if ($submitted)
                <div class="ul-terima">
                    <span class="ul-terima-ic"><i class="bi bi-check-lg"></i></span>
                    <div class="ul-terima-teks">
                        <b>Terima kasih atas ulasanmu!</b>
                        <p>Ulasan akan tampil di sini setelah disetujui admin.</p>
                    </div>
                    <button type="button" class="ul-tombol ul-tombol-garis" wire:click="$set('submitted', false)">Tulis lagi</button>
                </div>
            @else
                {{-- Sengaja tanpa direktif blok di dalam tag: Livewire tidak
                     memasang penanda morph untuk direktif yang disangkanya di
                     dalam tag, dan pasangannya jadi timpang. Nilainya dicetak
                     biasa lewat kurung kurawal ganda. --}}
                <form wire:submit="submit" class="ul-form" x-ref="form" x-show="{{ $formLipat ? 'showForm' : 'true' }}" {{ $formLipat ? 'x-cloak' : '' }}>
                    <div class="ul-form-kepala">
                        <span class="ul-ubin"><i class="bi bi-pencil-square"></i></span>
                        <div>
                            <h4>{{ $formLipat ? 'Bagikan pengalamanmu' : 'Tulis ulasan pertama' }}</h4>
                            <p>Ulasan yang jujur paling membantu pembeli berikutnya.</p>
                        </div>
                    </div>

                    <div class="ul-baris">
                        <div class="ul-field">
                            <label for="ul-nama">Nama</label>
                            <input id="ul-nama" type="text" class="form-control" wire:model="nama" placeholder="Nama kamu" maxlength="60" autocomplete="name">
                            @error('nama') <span class="ul-err">{{ $message }}</span> @enderror
                        </div>
                        <div class="ul-field">
                            <span class="ul-label" id="ul-rating-lbl">Rating</span>
                            <div class="ul-rate" role="radiogroup" aria-labelledby="ul-rating-lbl">
                                @for ($i = 1; $i <= 5; $i++)
                                    <button type="button" role="radio" @click="rating = {{ $i }}"
                                        :class="rating >= {{ $i }} ? 'is-on' : ''" :aria-checked="rating === {{ $i }}"
                                        aria-label="Beri {{ $i }} bintang">
                                        <i class="bi bi-star-fill"></i>
                                    </button>
                                @endfor
                                <span class="ul-rate-lbl" x-text="['', 'Sangat kurang', 'Kurang', 'Cukup', 'Bagus', 'Sangat puas'][rating] || ''"></span>
                            </div>
                            @error('rating') <span class="ul-err">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="ul-field" x-data="{ n: {{ mb_strlen((string) $ulasan) }} }">
                        <label for="ul-ulasan">Ulasan</label>
                        <textarea id="ul-ulasan" class="form-control" wire:model="ulasan" rows="4" maxlength="500"
                            @input="n = $event.target.value.length"
                            placeholder="Apa yang kamu suka? Bagaimana proses pengirimannya?"></textarea>
                        <span class="ul-hitung"><span x-text="n"></span>/500</span>
                        @error('ulasan') <span class="ul-err">{{ $message }}</span> @enderror
                    </div>

                    <div class="ul-kaki">
                        <p class="ul-catatan"><i class="bi bi-info-circle"></i> Tampil setelah disetujui admin.</p>
                        <div class="ul-aksi">
                            @if ($formLipat)
                                <button type="button" class="ul-tombol ul-tombol-garis" @click="showForm = false">Batal</button>
                            @endif
                            <button type="submit" class="ul-tombol" wire:loading.attr="disabled" wire:target="submit">
                                <span wire:loading.remove wire:target="submit"><i class="bi bi-send-fill"></i></span>
                                <span wire:loading wire:target="submit"><span class="spinner-border spinner-border-sm"></span></span>
                                Kirim Ulasan
                            </button>
                        </div>
                    </div>
                </form>
            @endif

            {{-- Alat daftar hanya muncul bila ulasannya lebih banyak dari yang
                 tampil sekaligus — untuk lima ulasan atau kurang, semuanya
                 sudah terlihat dan penyaring hanya menambah kerumitan. --}}
            @if ($adaAlat)
                <div class="ul-alat">
                    <div class="ul-chip-deret" role="group" aria-label="Saring menurut bintang">
                        <button type="button" class="ul-chip {{ $bintang === null ? 'is-aktif' : '' }}"
                            wire:click="saringBintang" aria-pressed="{{ $bintang === null ? 'true' : 'false' }}">
                            Semua <span>{{ $count }}</span>
                        </button>
                        @foreach ([5, 4, 3, 2, 1] as $b)
                            @if (! empty($sebaran[$b]))
                                <button type="button" class="ul-chip {{ $bintang === $b ? 'is-aktif' : '' }}"
                                    wire:click="saringBintang({{ $b }})" aria-pressed="{{ $bintang === $b ? 'true' : 'false' }}">
                                    <i class="bi bi-star-fill"></i> {{ $b }} <span>{{ $sebaran[$b] }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>
                    <label class="ul-urut">
                        <i class="bi bi-sort-down"></i>
                        <select wire:model.live="urut" aria-label="Urutkan ulasan">
                            <option value="terbaru">Terbaru</option>
                            <option value="tertinggi">Rating tertinggi</option>
                            <option value="terendah">Rating terendah</option>
                        </select>
                    </label>
                </div>
            @endif

            @foreach ($reviews as $r)
                <article class="ul-kartu" style="--a: {{ $warnaPengulas($r->nama) }}" wire:key="ulasan-{{ $r->id }}" x-data="{ buka: false, lebih: false }">
                    <i class="bi bi-quote ul-kutip" aria-hidden="true"></i>
                    <div class="ul-kepala">
                        <span class="ul-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim($r->nama), 0, 1)) }}</span>
                        <div class="ul-siapa">
                            <span class="ul-nama">{{ $r->nama }}</span>
                            <span class="ul-meta">
                                <span class="ul-bintang" aria-label="{{ $r->rating }} dari 5 bintang">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $r->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </span>
                                <span class="ul-titik" aria-hidden="true"></span>
                                {{-- locale('id') karena APP_LOCALE=en: tanpanya bulan tercetak "Aug", bukan "Agt". --}}
                                <time datetime="{{ $r->created_at?->toDateString() }}">{{ $r->created_at?->locale('id')->translatedFormat('j M Y') }}</time>
                            </span>
                        </div>
                    </div>
                    @if ($lipat($r->ulasan))
                        {{-- Tombolnya muncul hanya bila teks BENAR-BENAR terpotong, diukur
                             di peramban: ulasan 300 karakter muat tiga baris di desktop
                             tetapi tujuh baris di HP, dan tombol yang tidak membuka apa-apa
                             hanya membuat orang ragu apakah tautannya rusak. --}}
                        <p class="ul-teks is-lipat" :class="{ 'is-lipat': !buka }"
                            x-init="$nextTick(() => lebih = $el.scrollHeight > $el.clientHeight + 1)">{{ $r->ulasan }}</p>
                        <button type="button" class="ul-baca" x-show="lebih" x-cloak @click="buka = !buka" x-text="buka ? 'Tutup' : 'Baca selengkapnya'">Baca selengkapnya</button>
                    @else
                        <p class="ul-teks">{{ $r->ulasan }}</p>
                    @endif
                </article>
            @endforeach

            @if ($adaSisa)
                <button type="button" class="ul-lagi" wire:click="muatLagi" wire:loading.attr="disabled" wire:target="muatLagi">
                    <span wire:loading.remove wire:target="muatLagi"><i class="bi bi-chevron-down"></i></span>
                    <span wire:loading wire:target="muatLagi"><span class="spinner-border spinner-border-sm"></span></span>
                    Tampilkan {{ min($perMuat, $sisa) }} ulasan lagi
                    <small>· {{ $sisa }} tersisa</small>
                </button>
            @elseif ($semuaTampil)
                <p class="ul-habis">Semua {{ $jumlahTersaring }} ulasan sudah ditampilkan.</p>
            @endif
        </div>
    </div>
</div>
