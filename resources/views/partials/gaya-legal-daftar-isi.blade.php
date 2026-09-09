{{-- Gaya halaman legal berdaftar-isi: dipakai Syarat & Ketentuan dan Kebijakan
     Privasi. Keduanya berstruktur sama persis, jadi gayanya dikumpulkan di satu
     berkas — menyalinnya ke tiap halaman berarti perbaikan berikutnya harus
     diingat dua kali, dan pasti ada yang terlewat.

     @once: bila suatu saat dua bagian berdaftar-isi muncul di halaman yang sama,
     blok <style> ini tetap tercetak sekali.

     Ditulis inline, bukan di resources/css/public-custom-styles.css: berkas itu
     dikompilasi Vite ke public/build yang MASUK .gitignore dan tidak ikut
     terdeploy — salinan di server masih tertanggal 19 Agustus, jadi aturan yang
     ditulis di sana tidak akan pernah sampai ke pengunjung lewat git pull. --}}
@once
<style>
    /* Teks hukum dibaca BERURUTAN — pasal belakangan mengacu pada yang di depan
       — jadi memecahnya jadi dua kolom seimbang (seperti FAQ) justru memaksa
       mata naik-turun. Yang dibutuhkan pembaca di sini bukan "semua terlihat
       sekaligus", melainkan "bisa langsung melompat ke pasal yang dicari". */
    .lg-lebar .legal-card { max-width: 1180px; }
    .lg-lebar .legal-hero { padding-top: 34px; padding-bottom: 26px; }
    .lg-lebar .legal-hero h1 { margin-bottom: 6px; }

    .lg-tata { display: grid; grid-template-columns: 250px 1fr; gap: 36px; align-items: start; }

    /* Lengket, dengan tinggi maksimum & gulir sendiri: kalau daftarnya lebih
       tinggi daripada layar, tanpa ini bagian bawahnya mustahil dijangkau. */
    .lg-nav {
        position: sticky; top: 90px; max-height: calc(100vh - 110px); overflow-y: auto;
        border-right: 1px solid #f1f3f6; padding-right: 18px;
    }
    .lg-nav b {
        display: block; font-size: .68rem; font-weight: 800; letter-spacing: .08em;
        text-transform: uppercase; color: #a8b3c4; margin-bottom: 10px; padding-left: 12px;
    }
    .lg-nav a {
        display: block; padding: 8px 12px; border-radius: 10px; margin-bottom: 2px;
        font-size: .86rem; font-weight: 600; color: #6b7280; text-decoration: none;
        border-left: 2px solid transparent; transition: background .15s ease, color .15s ease;
    }
    .lg-nav a:hover { background: #fff3e6; color: #d9531a; border-left-color: #f26522; }

    /* Sasaran lompatan diberi jarak dari tepi atas: tanpa ini judul pasal yang
       dilompati tersembunyi di balik bilah navigasi yang menempel di puncak
       layar, dan pembaca mendarat di tengah paragraf. */
    .lg-lebar .legal-block { scroll-margin-top: 90px; }

    /* Tanggal pembaruan, seperti halaman ketentuan Orcha. Pada dokumen hukum ini
       bukan hiasan: pembaca perlu tahu versi mana yang sedang ia setujui. */
    .lg-diperbarui {
        display: block; margin-top: 18px; padding: 12px;
        border-top: 1px solid #f1f3f6; font-size: .76rem; color: #a8b3c4; line-height: 1.5;
    }
    .lg-diperbarui b { display: block; color: #6b7280; font-weight: 700; }

    /* ===== Menenangkan tampilan =====
       Lencana judul sebelumnya kotak bergradasi jingga penuh dengan bayangan,
       terulang di setiap pasal. Sembilan kotak menyala berderet ke bawah membuat
       halaman hukum terlihat seperti brosur, dan setiap judul berebut perhatian
       dengan judul di bawahnya. Diturunkan jadi nada datar: warnanya tetap
       jingga merek, tapi tidak lagi bersinar. */
    .lg-lebar .legal-block h2 span {
        background: #fff3e6; color: #d9531a;
        width: 26px; height: 26px; border-radius: 8px; font-size: .8rem; box-shadow: none;
    }
    .lg-lebar .legal-block h2 { font-size: 1.06rem; gap: 10px; margin-bottom: 10px; }

    /* Teks tombol tidak terlihat: `.legal-block a { color: jingga }` bernilai
       0,1,1 dan mengalahkan `.co-btn-primary { color: #fff }` yang hanya 0,1,0 —
       tombolnya berada DI DALAM .legal-block, jadi ia mewarisi warna tautan
       halaman legal dan tulisannya jingga di atas latar jingga.

       Aturan ini pernah ditulis langsung di halaman Member, lalu ikut terbuang
       saat blok <style>-nya diganti partial ini. Ditaruh di sini supaya
       nasibnya terikat pada gaya bersama, bukan pada satu berkas halaman. */
    .lg-lebar .legal-block a.co-btn-primary { color: #fff; }
    .lg-lebar .legal-block a.co-btn-primary:hover { color: #fff; text-decoration: none; }

    /* Blok sorotan: pembatas jingga tebal mengelilingi seluruh kotak menariknya
       terlalu jauh dari sekitarnya. Cukup satu pita di tepi kiri — sorotannya
       tetap terbaca tanpa memotong alur bacaan. */
    .lg-lebar .legal-block.legal-highlight {
        border: 0; border-left: 3px solid #f7c9a3; border-radius: 0 12px 12px 0;
        background: #fffaf4; padding: 16px 18px;
    }

    @media (max-width: 991.98px) {
        .lg-tata { grid-template-columns: 1fr; gap: 20px; }
        .lg-nav {
            position: static; max-height: none; overflow: visible;
            border-right: 0; padding-right: 0; border-bottom: 1px solid #f1f3f6; padding-bottom: 14px;
        }
        /* Mendatar di layar sempit: daftar tegak sepanjang sembilan baris justru
           mendorong isinya turun jauh dari pandangan. */
        .lg-nav-tautan { display: flex; flex-wrap: wrap; gap: 6px; }
        .lg-nav a { border-left: 0; background: #f8fafc; padding: 6px 11px; font-size: .8rem; }
    }
</style>
@endonce
