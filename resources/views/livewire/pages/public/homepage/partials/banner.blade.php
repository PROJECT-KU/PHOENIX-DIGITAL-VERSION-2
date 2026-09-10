@php
    $multiBanner = $banners->count() > 1;

    /**
     * Menyorot EKOR judul dengan warna jingga.
     *
     * Judul hero disimpan admin sebagai satu kalimat utuh, jadi tidak ada
     * medan terpisah untuk "bagian yang diwarnai". Aturannya dibuat dapat
     * ditebak: yang disorot adalah bagian setelah koma terakhir — itu memang
     * bagian yang menjanjikan sesuatu ("…, Gratis & Langsung Untung!").
     * Bila tidak ada koma, dua kata terakhir yang disorot.
     *
     * Selalu mengembalikan HTML yang sudah di-escape: judulnya diketik manusia
     * lewat panel admin, dan tidak ada alasan mempercayainya mentah-mentah.
     */
    $aksenJudul = function (?string $judul) {
        $judul = trim((string) $judul);

        if ($judul === '') {
            return '';
        }

        if (str_contains($judul, ',')) {
            $depan = Str::beforeLast($judul, ',').',';
            $ekor = trim(Str::afterLast($judul, ','));
        } else {
            $kata = preg_split('/\s+/', $judul) ?: [];

            // Judul sangat pendek tidak dipotong: menyorot dua dari tiga kata
            // membuat warnanya terlihat asal, bukan disengaja.
            if (count($kata) < 4) {
                return e($judul);
            }

            $depan = implode(' ', array_slice($kata, 0, -2));
            $ekor = implode(' ', array_slice($kata, -2));
        }

        return e($depan).' <span class="ph-aksen">'.e($ekor).'</span>';
    };
@endphp

<style>
    /* Ditulis inline, bukan di resources/css/public-custom-styles.css: berkas itu
       dikompilasi Vite ke public/build, yang MASUK .gitignore dan tidak ikut
       terdeploy — salinan di server masih tertanggal 19 Agustus. Aturan yang
       ditulis di sana tidak akan pernah sampai ke pengunjung lewat git pull. */

    /* Chip mengambang hanya masuk akal saat hero berdampingan dengan kartunya:
       chip mengisi ruang kosong di sekelilingnya. Begitu tata letaknya menumpuk
       (di bawah 992px) ruang kosong itu hilang, dan chip hanya bisa mendarat DI
       ATAS tulisan — di tablet, "Proses Instan" menutupi kata "Checkout" pada
       judul hero. Seluruh lapisannya disembunyikan, bukan sebagian. */
    @media (max-width: 991.98px) {
        .ph-hero-deco { display: none !important; }
    }

    /* ===== Banner: poster tampil di dalam layar laptop =====

       Gambar banner di toko ini adalah POSTER LENGKAP (1254x1254): judul,
       badge, dan ilustrasinya sudah tercetak di dalam gambar. Menempelkannya
       apa adanya di samping judul hero membuat satu pesan tampil dua kali
       bersebelahan dan saling berebut — itu sumber kesan monoton sebelumnya.

       Dibingkai sebagai layar laptop, posternya berubah peran: ia bukan lagi
       pesaing judul di sebelahnya, melainkan "yang sedang tampil di layar".
       Dua hal yang tadinya bertabrakan jadi satu adegan.

       Laptopnya DILUKIS dengan CSS, bukan berkas gambar: mockup sebagai berkas
       berarti satu unduhan besar lagi dan akan ikut buram di layar retina.

       Isi layar dan seluruh teks di kiri tetap datang dari data banner yang
       diunggah admin — tidak ada satu pun yang dipatok di sini. */

    /* Kolom gambar dilebarkan dari 52% ke 56%, dan laptopnya ikut membesar.
       Poster yang diunggah adalah aset paling berharga di hero ini; sebelumnya
       justru dia yang tampil paling kecil. */
    .ph-hero .ph-hero-media {
        background: none; border: 0; border-radius: 0; margin: 0;
        flex: 1 1 56%;
        display: flex; align-items: center; justify-content: center;
        padding: 30px 30px 30px 0;
        position: relative;
    }
    .ph-hero .ph-hero-text { flex: 1 1 44%; padding: 44px 26px 44px 50px; }

    /* Cahaya hangat di belakang laptop. Inilah yang mengangkatnya dari
       permukaan kartu — tanpa ini laptop dan latar sama-sama datar, dan tidak
       ada yang terbaca sebagai "di depan". */
    .ph-hero .ph-hero-media::before {
        content: ""; position: absolute; z-index: 0;
        width: 78%; aspect-ratio: 1; left: 50%; top: 50%; transform: translate(-50%, -50%);
        background: radial-gradient(circle, rgba(242, 101, 34, .16) 0%, rgba(242, 101, 34, 0) 68%);
        pointer-events: none;
    }
    .ph-hero .ph-hero-media::after { display: none; }

    /* Laptop dilihat sedikit menyerong, bukan tegak lurus dari depan.

       Persegi panjang yang dilihat lurus dari depan tidak pernah terbaca
       sebagai benda — ia terbaca sebagai bingkai. Kemiringan kecil inilah yang
       membedakan "gambar laptop" dari "kotak berisi gambar", dan itu perbedaan
       yang paling terasa dibanding rancangan.

       Sudutnya sengaja kecil (7 derajat): lebih dari itu, poster di dalam
       layarnya ikut menyerong sampai tulisannya sulit dibaca — padahal justru
       poster itu yang ingin ditunjukkan. */
    .ph-laptop {
        position: relative; z-index: 1;
        width: 100%; max-width: 610px;
        transform: perspective(1400px) rotateY(-7deg) rotateX(3deg);
        transform-style: preserve-3d;
    }

    /* --- Tutup layar --- */
    .ph-laptop-layar {
        position: relative;
        background: linear-gradient(160deg, #2b313d 0%, #1b2029 60%, #232937 100%);
        border-radius: 14px 14px 6px 6px;
        /* Bibir bawah lebih tebal daripada tiga sisi lainnya — begitulah bezel
           laptop sungguhan, dan mata mengenali proporsi itu sebelum mengenali
           detail apa pun. */
        padding: 11px 11px 20px;
        box-shadow:
            0 26px 50px rgba(28, 31, 38, .28),
            0 2px 0 rgba(255, 255, 255, .12) inset;
    }
    /* Kamera kecil. Tanpa ini bidang gelap di atas layar terbaca sebagai
       kesalahan jarak, bukan sebagai bingkai. */
    .ph-laptop-layar::before {
        content: ""; position: absolute; top: 5px; left: 50%; transform: translateX(-50%);
        width: 5px; height: 5px; border-radius: 50%; background: #4b5364;
    }

    .ph-laptop-isi {
        position: relative; overflow: hidden; border-radius: 5px;
        /* 4:3, bukan 16:10. Poster yang diunggah berbentuk PERSEGI; di layar
           16:10 ia menyisakan bilah kosong hampir seperlima lebar di kiri dan
           kanan, dan bilah itulah yang membuat posternya terlihat seperti
           gambar yang salah ukuran. Pada 4:3 sisanya tinggal separuhnya, dan
           latar layar dibuat sewarna supaya sisanya terbaca sebagai wallpaper,
           bukan sebagai kekurangan. */
        aspect-ratio: 4 / 3;
        background: linear-gradient(135deg, #ffeeda, #fff7ef);
    }
    .ph-laptop-isi img {
        width: 100%; height: 100%; display: block;
        /* contain, BUKAN cover. Poster persegi yang dipaksa memenuhi layar 16:10
           kehilangan sekitar sepertiga tingginya — dan pada poster ini yang
           terpotong justru judul di atas dan ajakan di bawah. Lebih baik
           tersisa bidang kosong di kiri-kanan daripada memotong isinya.
           (Banner mendatar — misalnya 1600x1000 — akan memenuhi layar penuh.) */
        object-fit: contain; object-position: center;
        -webkit-mask-image: none; mask-image: none;
        padding: 0;
    }
    /* Pantulan cahaya tipis melintang layar. */
    .ph-laptop-isi::after {
        content: ""; position: absolute; inset: 0; pointer-events: none;
        background: linear-gradient(112deg, rgba(255, 255, 255, .22) 0%, rgba(255, 255, 255, 0) 34%);
    }

    /* --- Engsel & alas --- */
    /* Garis engsel tipis di bawah tutup. Tanpa itu tutup dan alas terlihat
       seperti dua benda terpisah yang kebetulan bertumpuk. */
    .ph-laptop-layar::after {
        content: ""; position: absolute; left: 22%; right: 22%; bottom: 3px; height: 2px;
        border-radius: 2px; background: rgba(255, 255, 255, .10);
    }

    .ph-laptop-alas {
        position: relative; height: 14px; margin: 0 auto;
        /* Julurannya dipatok piksel, bukan persen. Dengan 116% alas ikut
           membesar seiring layar dan di ponsel ia melebihi lebar kartunya
           sendiri — ujungnya lalu terpotong tepi kartu dan terlihat rusak. */
        width: calc(100% + 24px); max-width: none; left: -12px;
        background: linear-gradient(180deg, #e3e7ee 0%, #c2c9d5 42%, #9aa2b1 100%);
        /* MENIRUS, bukan kotak. Alas laptop selalu lebih lebar di bagian bawah
           karena tutupnya bersandar di tepi belakang — dan siluet menirus
           itulah yang membuat orang langsung mengenalinya sebagai laptop.
           Sebelumnya alasnya balok lurus, jadi bentuknya terbaca seperti
           bingkai foto bertumpu papan, bukan laptop. */
        clip-path: polygon(1.6% 0, 98.4% 0, 100% 100%, 0 100%);
        box-shadow: 0 16px 26px rgba(28, 31, 38, .22);
    }
    /* Takik pembuka layar di tepi depan. */
    .ph-laptop-alas span {
        position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
        width: 84px; height: 4px; border-radius: 4px 4px 0 0; background: #8b93a2;
    }

    /* --- Kartu mengambang di sekeliling laptop --- */
    .ph-hero-deco { pointer-events: none; }
    .ph-hero .ph-kartu {
        position: absolute; z-index: 5;
        background: rgba(255, 255, 255, .92);
        -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, .9); border-radius: 14px;
        padding: 10px 13px;
        /* Bayangan dilembutkan. Bayangan pekat pada elemen kecil membuatnya
           tampak ditempel, bukan melayang — dan tiga elemen yang tampak
           ditempel adalah tiga tambalan. */
        box-shadow: 0 10px 30px rgba(35, 39, 47, .10), 0 2px 6px rgba(35, 39, 47, .05);
        animation: phChipFloat 5s ease-in-out infinite;
    }

    /* Kartu A — angka pelanggan */
    .ph-hero .ph-kartu-a {
        top: 9%; left: 48%;
        display: flex; align-items: center; gap: 10px; white-space: nowrap;
        animation-delay: 0s;
    }
    .ph-hero .ph-kartu-ikon {
        width: 34px; height: 34px; border-radius: 50%; flex: 0 0 auto;
        display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #f26522, #fb8b3c); color: #fff; font-size: .95rem;
    }
    .ph-hero .ph-kartu-a b {
        display: block; font-family: 'Poppins', sans-serif; font-weight: 800;
        font-size: 1.05rem; color: #1c1f26; line-height: 1.15;
    }
    .ph-hero .ph-kartu-a small { display: block; font-size: .74rem; color: #8b94a3; }
    .ph-hero .ph-kartu-naik { color: #16a34a; font-size: 1rem; }

    /* Kartu B — nilai ulasan. Sengaja GELAP: tiga kartu putih berjajar akan
       kembali terbaca sebagai satu hiasan yang diulang. */
    .ph-hero .ph-kartu-b {
        top: 4%; right: 2%;
        background: #1c2029; border-color: #1c2029; text-align: center;
        padding: 13px 18px; animation-delay: .9s;
    }
    .ph-hero .ph-bintang { display: block; color: #fbbf24; font-size: .82rem; letter-spacing: 1px; }
    .ph-hero .ph-kartu-b b {
        display: block; margin-top: 4px;
        font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.5rem;
        color: #fff; line-height: 1.1;
    }
    .ph-hero .ph-kartu-b b small { font-size: .85rem; font-weight: 700; color: rgba(255, 255, 255, .55); }
    .ph-hero .ph-kartu-b > small { display: block; font-size: .68rem; color: rgba(255, 255, 255, .6); }

    /* Kartu C — daftar keuntungan */
    .ph-hero .ph-kartu-c {
        bottom: 8%; right: 0;
        display: flex; flex-direction: column; gap: 7px;
        padding: 14px 16px; animation-delay: 1.6s;
    }
    .ph-hero .ph-centang {
        display: flex; align-items: center; gap: 8px; white-space: nowrap;
        font-size: .8rem; font-weight: 600; color: #3f4652;
    }
    .ph-hero .ph-centang i.bi { color: #16a34a; font-size: .9rem; line-height: 1; }

    /* Tulisan tangan di tepi kanan, di antara kartu nilai dan kartu daftar —
       urutan yang sama seperti pada rancangan. */
    .ph-hero .ph-tulisan {
        position: absolute; z-index: 5; top: 27%; right: 0; width: 168px;
        color: #f26522; text-align: center; transform: rotate(-6deg);
    }
    .ph-hero .ph-tulisan span {
        display: block;
        font-family: 'Caveat', 'Segoe Script', 'Bradley Hand', cursive;
        font-weight: 700; font-size: 1.5rem; line-height: 1.15;
    }
    .ph-hero .ph-tulisan svg {
        display: block; width: 60px; height: auto; margin: 2px 0 0 auto;
        transform: rotate(6deg);
    }

    /* Di bawah 1200px laptopnya menyempit dan kartu mulai saling menimpa.
       Yang paling besar dilepas lebih dulu, bukan semuanya sekaligus. */
    @media (max-width: 1199.98px) {
        .ph-hero .ph-kartu-c, .ph-hero .ph-tulisan { display: none; }
        .ph-hero .ph-kartu-a { left: 42%; }
    }

    /* --- Baris jaminan di kaki hero --- */
    /* Datar, tanpa bingkai. Empat kartu berbingkai di sini akan menyaingi
       kartu hero yang menampungnya — kotak di dalam kotak. Yang dibutuhkan
       hanya keterangan kaki, jadi cukup ikon dan dua baris teks. */
    /* Jaminan diturunkan bobotnya: ia keterangan kaki, dan sebelumnya empat
       blok berikon sebesar itu bersaing dengan tombol tepat di atasnya. */
    .ph-hero .ph-jaminan {
        display: flex; flex-wrap: wrap; gap: 12px 26px; margin-top: 14px;
        padding-top: 20px; border-top: 1px solid rgba(242, 101, 34, .12);
    }
    .ph-hero .ph-jaminan-butir {
        flex: 1 1 176px; min-width: 0;
        display: flex; align-items: center; gap: 11px;
    }
    .ph-hero .ph-jaminan-ikon {
        flex: 0 0 auto; width: 28px; height: 28px; border-radius: 9px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fff2ea; font-size: .82rem;
    }
    /* !important terpaksa: aturan lama .ph-trust-ico i memakai color:#fff
       !important untuk latar gradient. Latar di sini lembut, jadi glif putih
       akan hilang sama sekali. */
    .ph-hero .ph-jaminan-ikon i.bi { line-height: 1; color: #f26522 !important; }
    .ph-hero .ph-jaminan-butir > span { display: flex; flex-direction: column; min-width: 0; }
    .ph-hero .ph-jaminan-butir { gap: 9px; }
    .ph-hero .ph-jaminan-butir strong {
        font-family: 'Poppins', sans-serif; font-weight: 600; font-size: .8rem;
        color: #3f4652; line-height: 1.25;
    }
    .ph-hero .ph-jaminan-butir small { font-size: .72rem; color: #a0a8b4; line-height: 1.35; }

    @media (max-width: 575.98px) {
        .ph-hero .ph-jaminan { gap: 12px 18px; }
        .ph-hero .ph-jaminan-butir { flex: 1 1 44%; }
    }

    /* --- Sisi teks --- */
    .ph-hero .ph-hero-text { gap: 18px; }

    /* Hirarki tipografi dipertegas. Sebelumnya judul dan deskripsi hanya
       berbeda sedikit bobotnya, jadi keduanya terbaca sama penting — dan blok
       teks yang semuanya sama penting selalu terbaca datar. */
    .ph-hero .ph-hero-title { letter-spacing: -.03em; line-height: 1.04; }
    .ph-hero .ph-hero-desc { font-size: 1rem; line-height: 1.65; color: #7b8493; }

    /* Lencana ditenangkan: ia keterangan, bukan pengumuman. */
    .ph-hero .ph-hero-eyebrow {
        font-size: .72rem; letter-spacing: .14em; padding: 6px 13px;
        background: #fff; border-color: #f3ddcc;
    }

    /* SATU aksi utama. Dua tombol berbobot sama membuat pengunjung memilih
       lebih dulu sebelum bertindak, dan sebagian memilih untuk tidak
       keduanya. "Lihat Katalog" diturunkan jadi tautan — tetap ada bagi yang
       memang mencarinya, tanpa menyaingi tombol belanja. */
    .ph-hero .ph-hero-actions { gap: 8px; align-items: center; margin-top: 4px; }
    .ph-hero .ph-btn-primary { font-size: 1.02rem; padding: 15px 32px; }
    .ph-hero .ph-btn-ghost {
        background: none; border: 0; padding: 15px 18px;
        font-size: 1rem; color: #6b7280 !important; box-shadow: none;
    }
    .ph-hero .ph-btn-ghost:hover {
        background: none; color: #f26522 !important; text-decoration: underline; text-underline-offset: 4px;
    }
    /* Deskripsi dua baris: isinya mengulang kalimat yang sudah tercetak
       besar-besar di posternya sendiri. */
    .ph-hero .ph-hero-desc { -webkit-line-clamp: 2; line-clamp: 2; }

    /* --- Pita aksen di puncak kartu: bergerak, bukan diam --- */
    .phoenix-hero-swiper::before {
        background: linear-gradient(90deg, #f26522, #fba919, #f26522, #fba919);
        background-size: 300% 100%;
        animation: phPitaGeser 9s linear infinite;
    }
    @keyframes phPitaGeser {
        0% { background-position: 0% 0; }
        100% { background-position: 300% 0; }
    }

    @media (prefers-reduced-motion: reduce) {
        .phoenix-hero-swiper::before { animation: none; }
        .ph-hero .ph-chip { animation: none; }
    }

    @media (max-width: 991.98px) {
        .ph-hero .ph-hero-media { padding: 24px 22px 18px; }
        /* Padding kiri 50px / kanan 26px hanya masuk akal saat teks berdampingan
           dengan gambar. Begitu tata letaknya menumpuk, ketimpangan itu terbaca
           sebagai teks yang melenceng ke kanan. */
        .ph-hero .ph-hero-text { padding: 28px 24px 32px; }
        /* Tegak lurus di layar sempit: kemiringan memakan lebar yang sudah
           tidak ada sisanya, dan poster di dalamnya jadi makin kecil. */
        .ph-laptop { max-width: 420px; transform: none; }
    }
    @media (max-width: 575.98px) {
        .ph-hero .ph-hero-media { padding: 18px 16px 14px; }
        .ph-hero .ph-hero-text { padding: 24px 20px 28px; }
        /* Tombol tautan sejajar dengan tombol utama di layar sempit; dengan
           padding 18px ia terlihat menjorok tanpa sebab. */
        .ph-hero .ph-btn-ghost { padding: 12px 4px; }
        .ph-laptop-layar { padding: 8px 8px 14px; border-radius: 11px 11px 5px 5px; }
        .ph-laptop-alas { height: 11px; width: calc(100% + 16px); left: -8px; }
        .ph-laptop-alas span { width: 62px; height: 3px; }
    }

    /* ===== GAYA PRATINJAU (hanya aktif lewat ?gaya=) =====
       Ketiganya memakai markup yang sama persis; yang berbeda hanya warnanya.
       Begitu satu dipilih, dua sisanya dibuang dan yang terpilih jadi bawaan. */

    /* --- Gaya 2: gelap & tegas --- */
    .ph-gaya-2 .phoenix-hero-swiper { border-color: #232937; }
    .ph-gaya-2 .ph-hero-slide {
        background:
            radial-gradient(120% 120% at 8% 0%, rgba(242, 101, 34, .22) 0%, rgba(242, 101, 34, 0) 52%),
            linear-gradient(120deg, #1b2029 0%, #232937 58%, #1b2029 100%);
    }
    .ph-gaya-2 .ph-hero-title { color: #fff; }
    .ph-gaya-2 .ph-hero-title .ph-aksen { color: #fba919; }
    .ph-gaya-2 .ph-hero-desc { color: rgba(255, 255, 255, .70); }
    .ph-gaya-2 .ph-hero-eyebrow {
        background: rgba(242, 101, 34, .16); border-color: rgba(242, 101, 34, .38); color: #fba919;
    }
    .ph-gaya-2 .ph-btn-ghost {
        background: rgba(255, 255, 255, .06); border-color: rgba(255, 255, 255, .22); color: #fff !important;
    }
    .ph-gaya-2 .ph-btn-ghost:hover { background: rgba(255, 255, 255, .12); color: #fff !important; }
    .ph-gaya-2 .ph-jaminan-ikon { background: rgba(242, 101, 34, .16); }
    .ph-gaya-2 .ph-jaminan-ikon i.bi { color: #fba919 !important; }
    .ph-gaya-2 .ph-jaminan-butir strong { color: #fff; }
    .ph-gaya-2 .ph-jaminan-butir small { color: rgba(255, 255, 255, .55); }
    /* Layar laptop jadi satu-satunya bidang terang di kartu gelap — poster
       Anda praktis menyala sendiri, dan ke situlah mata jatuh lebih dulu. */
    .ph-gaya-2 .ph-laptop-layar { box-shadow: 0 30px 60px rgba(0, 0, 0, .45), 0 0 0 1px rgba(255, 255, 255, .07); }
    .ph-gaya-2 .ph-kartu-b { background: #fff; border-color: #eceff4; }
    .ph-gaya-2 .ph-kartu-b b { color: #1c1f26; }
    .ph-gaya-2 .ph-kartu-b b small, .ph-gaya-2 .ph-kartu-b > small { color: #8b94a3; }
    .ph-gaya-2 .ph-tulisan { color: #fba919; }

    /* --- Gaya 3: jingga penuh, warna merek --- */
    .ph-gaya-3 .phoenix-hero-swiper { border-color: #e0571b; }
    .ph-gaya-3 .ph-hero-slide {
        background:
            radial-gradient(110% 110% at 92% 8%, rgba(255, 255, 255, .30) 0%, rgba(255, 255, 255, 0) 55%),
            linear-gradient(122deg, #f26522 0%, #fb8b3c 54%, #fba919 100%);
    }
    .ph-gaya-3 .ph-hero-title { color: #fff; }
    .ph-gaya-3 .ph-hero-title .ph-aksen { color: #23272f; }
    .ph-gaya-3 .ph-hero-desc { color: rgba(255, 255, 255, .88); }
    .ph-gaya-3 .ph-hero-eyebrow {
        background: rgba(255, 255, 255, .18); border-color: rgba(255, 255, 255, .42); color: #fff;
    }
    /* Tombol utama dibalik jadi putih. Tombol jingga di atas latar jingga
       hilang sama sekali — itu kesalahan paling sering pada hero berwarna. */
    .ph-gaya-3 .ph-btn-primary {
        background: #fff; color: #d9531a !important; box-shadow: 0 12px 28px rgba(120, 45, 8, .28);
    }
    .ph-gaya-3 .ph-btn-ghost {
        background: rgba(255, 255, 255, .12); border-color: rgba(255, 255, 255, .55); color: #fff !important;
    }
    .ph-gaya-3 .ph-btn-ghost:hover { background: rgba(255, 255, 255, .22); color: #fff !important; }
    .ph-gaya-3 .ph-jaminan-ikon { background: rgba(255, 255, 255, .20); }
    .ph-gaya-3 .ph-jaminan-ikon i.bi { color: #fff !important; }
    .ph-gaya-3 .ph-jaminan-butir strong { color: #fff; }
    .ph-gaya-3 .ph-jaminan-butir small { color: rgba(255, 255, 255, .78); }
    .ph-gaya-3 .ph-laptop-layar { box-shadow: 0 30px 58px rgba(120, 45, 8, .38); }
    .ph-gaya-3 .ph-tulisan { color: #fff; }
    .ph-gaya-3 .phoenix-hero-swiper::before { display: none; }

    /* ===== Aksen jingga pada ekor judul =====
       Judul hero satu-satunya tulisan sebesar itu di halaman; membiarkannya
       satu warna membuat mata membacanya sebagai balok, bukan sebagai kalimat
       dengan penekanan. */
    .ph-hero-title .ph-aksen { color: #f26522; }

    /* Hanya jarak luar bagian hero yang dirapatkan. Padding di dalam slide
       SENGAJA tidak disentuh: slide diatur Swiper dengan lebar tetap, dan
       mengubah paddingnya membuat teks meluber keluar lalu terpotong tepi. */
    @media (max-width: 991.98px) {
        .ph-hero.section { padding: 14px 0 10px; }
    }
</style>

@php
    // Sakelar PRATINJAU, sementara. Tanpa ?gaya= di alamat, halaman tampil
    // persis seperti biasa — jadi pengunjung tidak pernah melihat apa pun yang
    // berubah. Dipakai supaya pemilik toko bisa membandingkan tiga arah desain
    // langsung di halaman aslinya, bukan lewat gambar contoh yang belum tentu
    // sama dengan hasil jadinya. Dihapus begitu satu gaya dipilih.
    $gaya = in_array(request('gaya'), ['2', '3'], true) ? 'ph-gaya-'.request('gaya') : '';
@endphp

<section id="hero" class="ph-hero section {{ $gaya }}">
    <div class="container">
        {{-- Chip mengambang (ala flip.id) untuk mengisi area kosong --}}
        <div class="ph-hero-deco" aria-hidden="true">
            {{-- Kartu mengambang di sekeliling laptop.

                 Sebelumnya empat pil putih berisi satu kalimat pendek. Pil
                 seragam yang berulang empat kali terbaca sebagai hiasan yang
                 sama diulang-ulang, bukan sebagai empat keterangan berbeda.
                 Diganti tiga kartu yang MASING-MASING beda bentuk dan beda
                 isi: satu angka, satu nilai, satu daftar. --}}
            <div class="ph-kartu ph-kartu-a">
                <span class="ph-kartu-ikon"><i class="bi bi-people-fill"></i></span>
                <span>
                    <b>5.000+</b>
                    <small>Pelanggan aktif</small>
                </span>
                <i class="bi bi-graph-up-arrow ph-kartu-naik"></i>
            </div>

            <div class="ph-kartu ph-kartu-b">
                <span class="ph-bintang">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </span>
                <b>4.9<small>/5</small></b>
                <small>Dari 5.000+ pelanggan</small>
            </div>

            {{-- Tulisan tangan + panah, seperti pada rancangan.

                 Ditulis dengan huruf sambung dan dimiringkan sedikit supaya
                 terbaca sebagai catatan tangan di atas gambar, bukan sebagai
                 satu label lagi. Kalau ia memakai huruf yang sama dengan
                 sisanya, ia hanya jadi teks kelima yang ikut berebut.

                 Panahnya SVG, bukan berkas gambar: satu garis lengkung tidak
                 pantas dibayar dengan satu unduhan. --}}
            <div class="ph-tulisan">
                <span>Upgrade Produktivitasmu Sekarang!</span>
                <svg viewBox="0 0 88 54" fill="none" aria-hidden="true">
                    <path d="M79 5c3 16-4 30-18 37C46 49 28 46 15 36"
                          stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/>
                    <path d="M8 41c2.6-2.4 5-4 8-5.2M21 28c-3 3-5 5.6-6.6 8.6"
                          stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/>
                </svg>
            </div>

            <div class="ph-kartu ph-kartu-c">
                {{-- Tiga, bukan empat. Daftar di kartu mengambang bukan tempat
                     memuat semua keunggulan — ia hanya isyarat. Empat baris
                     membuat kartunya setinggi seperempat hero dan berhenti
                     terbaca sebagai catatan tempel. --}}
                @foreach ([
                    'Harga lebih hemat',
                    'Akses produk premium',
                    'Poin reward tiap transaksi',
                ] as $butir)
                <span class="ph-centang"><i class="bi bi-check-circle-fill"></i> {{ $butir }}</span>
                @endforeach
            </div>
        </div>

        <div class="swiper phoenix-hero-swiper" data-aos="fade-up" data-multi="{{ $multiBanner ? '1' : '0' }}">
            <div class="swiper-wrapper">
                @forelse ($banners as $banner)
                <div class="swiper-slide">
                    <article class="ph-hero-slide">
                        <div class="ph-hero-text">
                            <span class="ph-hero-eyebrow"><i class="bi bi-award-fill"></i> Akun Premium, Lisensi &amp; Tools AI</span>
                            <h1 class="ph-hero-title">{!! $aksenJudul($banner->judul) !!}</h1>
                            @if ($banner->deskripsi)
                            <p class="ph-hero-desc">{{ $banner->deskripsi }}</p>
                            @endif
                            <div class="ph-hero-actions">
                                <a href="{{ route('shop.index') }}" class="ph-btn-primary">
                                    Belanja Sekarang <i class="bi bi-arrow-right"></i>
                                </a>
                                <a href="{{ route('shop.index') }}" class="ph-btn-ghost">Lihat Katalog</a>
                            </div>

                            <div class="ph-jaminan">
                                @foreach ([
                                    ['bi-lightning-charge-fill', 'Proses Instan', 'Langsung aktif'],
                                    ['bi-shield-check', 'Aman &amp; Terpercaya', 'Garansi uang kembali'],
                                    ['bi-headset', 'Bantuan 24/7', 'Siap membantu'],
                                    ['bi-people-fill', '5.000+ Pelanggan', 'Telah bergabung'],
                                ] as [$ikon, $judulJ, $subJ])
                                <div class="ph-jaminan-butir">
                                    <span class="ph-jaminan-ikon"><i class="bi {{ $ikon }}"></i></span>
                                    <span>
                                        <strong>{!! $judulJ !!}</strong>
                                        <small>{{ $subJ }}</small>
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="ph-hero-media">
                            {{-- Slide PERTAMA sengaja TIDAK lazy.

                                 Ia adalah gambar terbesar yang langsung terlihat
                                 saat halaman dibuka, jadi menundanya justru
                                 memperlambat kesan halaman terbuka — kebalikan
                                 dari tujuan lazy loading. fetchpriority="high"
                                 menyuruh browser mengambilnya lebih dulu di
                                 antara semua unduhan.

                                 Slide kedua dan seterusnya belum terlihat sampai
                                 pengunjung menggeser, jadi itu yang di-lazy. --}}
                            {{-- Laptop digambar dengan CSS, bukan berkas gambar.
                                 Mockup sebagai berkas berarti satu unduhan besar
                                 lagi, dan ia akan ikut buram di layar retina.
                                 Bentuk sesederhana ini lebih baik dilukis. --}}
                            <div class="ph-laptop">
                                <div class="ph-laptop-layar">
                                    <div class="ph-laptop-isi">
                                        <img src="{{ asset('storage/img/banners/' . $banner->gambar) }}"
                                            alt="{{ $banner->judul ?? 'Banner' }}"
                                            @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                                    </div>
                                </div>
                                <div class="ph-laptop-alas" aria-hidden="true"><span></span></div>
                            </div>
                        </div>
                    </article>
                </div>
                @empty
                <div class="swiper-slide">
                    <article class="ph-hero-slide ph-hero-slide--empty">
                        <div class="ph-hero-text">
                            <span class="ph-hero-eyebrow"><i class="bi bi-award-fill"></i> Akun Premium, Lisensi &amp; Tools AI</span>
                            <h1 class="ph-hero-title">Solusi Akun &amp; Lisensi <span class="ph-aksen">Digital Terpercaya</span></h1>
                            <p class="ph-hero-desc">Akun premium, lisensi, dan tools AI untuk riset &amp; produktivitas — proses cepat dan bergaransi.</p>
                            <div class="ph-hero-actions">
                                <a href="{{ route('shop.index') }}" class="ph-btn-primary">Belanja Sekarang <i class="bi bi-arrow-right"></i></a>
                                <a href="{{ route('shop.index') }}" class="ph-btn-ghost">Lihat Katalog</a>
                            </div>

                            <div class="ph-jaminan">
                                @foreach ([
                                    ['bi-lightning-charge-fill', 'Proses Instan', 'Langsung aktif'],
                                    ['bi-shield-check', 'Aman &amp; Terpercaya', 'Garansi uang kembali'],
                                    ['bi-headset', 'Bantuan 24/7', 'Siap membantu'],
                                    ['bi-people-fill', '5.000+ Pelanggan', 'Telah bergabung'],
                                ] as [$ikon, $judulJ, $subJ])
                                <div class="ph-jaminan-butir">
                                    <span class="ph-jaminan-ikon"><i class="bi {{ $ikon }}"></i></span>
                                    <span>
                                        <strong>{!! $judulJ !!}</strong>
                                        <small>{{ $subJ }}</small>
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="ph-hero-media ph-hero-media--empty">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </article>
                </div>
                @endforelse
            </div>

            @if ($multiBanner)
            <div class="swiper-pagination"></div>
            @endif
        </div>

    </div>
</section>
