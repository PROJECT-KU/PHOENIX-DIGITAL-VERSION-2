{{--
    Gaya khusus halaman Orcha.

    Ditulis inline, bukan lewat Vite: public/build tidak ikut ter-deploy, jadi
    CSS dari berkas terpisah tidak akan sampai ke server.

    Warnanya mengikuti merek Orcha (biru laut + emas) supaya admin langsung tahu
    sedang melihat data Orcha, bukan lemon.
--}}
<style>
    /*
     | Token tampilan halaman Orcha.
     |
     | Nilainya SENGAJA menyalin lemon (templateindex.blade.php) supaya admin
     | tidak merasa berpindah aplikasi saat menekan "Ganti ke Orcha". Sebelum
     | ini warnanya ditulis tersebar — 78 kali untuk dua biru saja — sehingga
     | menyeragamkannya berarti menyunting ratusan tempat satu per satu.
     |
     | Sekarang cukup mengubah nilai di sini. Itu juga yang membuat perubahan
     | ini bisa dibatalkan: kembalikan --orc-primer ke var(--orc-primer) dan
     | --orc-primer-2 ke var(--orc-primer-2), seluruh halaman kembali biru.
     |
     | CATATAN: --orc-tinta BUKAN warna merek. Ia warna TULISAN. Dulu kebetulan
     | memakai biru tua yang sama dengan warna merek, dan itu jebakan — mengganti
     | warna merek secara buta ikut mengubah seluruh teks jadi ungu.
     */
    :root {
        /* Merek — menyalin .btn-primary dan .gradient-text milik lemon. */
        --orc-primer: #7c3aed;
        --orc-primer-2: #4f46e5;
        --orc-primer-lembut: #8b5cf6;
        --orc-gradien: linear-gradient(135deg, #7c3aed, #4f46e5);

        /* Tulisan — skala slate yang sama dengan halaman lemon lain. */
        --orc-tinta: #1f2d3d;

        /* Geometri — menyalin .btn, .form-control, .form-select, .card lemon. */
        --orc-radius-tombol: 12px;
        --orc-radius-isian: 12px;
        /* Panel DALAM, menyalin .bf-panel milik lemon (18px). Kartu terluar
           halaman Orcha sudah memakai .card lemon, jadi tidak perlu diatur di
           sini — dan memaksanya 28px justru membuat radius bertumpuk. */
        --orc-radius-kartu: 18px;
        --orc-panel-latar: #f8fafc;
        --orc-panel-garis: 1px solid #eef0f7;
        --orc-padding-tombol: 10px 20px;
        --orc-garis-isian: 2px solid #e2e8f0;
        --orc-tinggi-isian: 48px;
    }

    .orcha-lencana {
        background: linear-gradient(135deg, var(--orc-primer-2), var(--orc-primer));
        color: #ffd772;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: .3rem .8rem;
        border-radius: 999px;
    }

    .orcha-tombol {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .orcha-kartu {
        border: 0;
        border-radius: var(--orc-radius-kartu);
        box-shadow: 0 4px 20px rgba(31, 45, 61, .06);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .orcha-kartu:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 30px rgba(31, 45, 61, .12);
    }

    .orcha-ikon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #fff;
        line-height: 1;
        background: linear-gradient(135deg, var(--orc-primer), var(--orc-primer-2));
        box-shadow: 0 8px 15px rgba(124, 58, 237, .25);
    }

    .orcha-ikon.perlu {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 8px 15px rgba(245, 158, 11, .25);
    }

    /* Ikon kartu tindakan: warna mengikuti MAKNANYA, bukan satu warna untuk
       semua.

       Lima kartu bernavy sama membuat mata tidak punya alasan berhenti di
       salah satunya — padahal justru itu gunanya: menemukan mana yang perlu
       dikerjakan lebih dulu. */
    .orcha-ikon-daftar { background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                         box-shadow: 0 8px 15px rgba(59, 130, 246, .25); }
    .orcha-ikon-sewa   { background: linear-gradient(135deg, #8b5cf6, #6d28d9);
                         box-shadow: 0 8px 15px rgba(139, 92, 246, .25); }
    .orcha-ikon-bayar  { background: linear-gradient(135deg, #10b981, #047857);
                         box-shadow: 0 8px 15px rgba(16, 185, 129, .25); }
    .orcha-ikon-batal  { background: linear-gradient(135deg, #ef4444, #b91c1c);
                         box-shadow: 0 8px 15px rgba(239, 68, 68, .25); }
    .orcha-ikon-pesan  { background: linear-gradient(135deg, #f59e0b, #d97706);
                         box-shadow: 0 8px 15px rgba(245, 158, 11, .25); }

    /* Yang nol diredupkan, bukan disembunyikan: tempatnya tetap sama setiap
       hari, jadi admin belajar letaknya dan berhenti membaca satu per satu. */
    .orcha-kartu-tindakan.kosong .orcha-ikon {
        background: #eef1f7;
        color: #94a3b8;
        box-shadow: none;
    }

    .orcha-kartu-tindakan.kosong .orcha-angka {
        color: #cbd5e1;
    }

    /* Yang berisi diberi pita di tepi kiri — terbaca bahkan saat kartunya
       tersaput di ujung mata. */
    .orcha-kartu-tindakan.ada {
        border-left: 4px solid #f59e0b !important;
    }

    .orcha-angka {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--orc-tinta);
    }

    .orcha-tabel thead th {
        background: #f4f8fb;
        color: var(--orc-tinta);
        font-size: .74rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .orcha-tabel td {
        vertical-align: middle;
    }

    .orcha-kode {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 700;
        color: var(--orc-tinta);
        white-space: nowrap;
    }

    /* Pasangan ikon dan teks.
       bootstrap-icons memasang vertical-align sendiri pada tiap glifnya, dan
       nilainya dipatok terhadap ukuran huruf ikon — bukan terhadap teks di
       sebelahnya. Begitu keduanya berbeda ukuran atau ketebalan, ikonnya
       melayang naik sedikit di atas garis teks.
       Di dalam flex, vertical-align tidak berlaku sama sekali, dan yang
       menentukan tingginya adalah align-items. Karena itu perataannya
       diserahkan ke flex, bukan ditambal dengan geseran em yang harus
       dihitung ulang tiap kali ukuran hurufnya berubah. */
    .orcha-ikon-teks {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }

    .orcha-ikon-teks > i {
        line-height: 1;
        flex: 0 0 auto;
    }

    /* .orcha-label-kecil memakai display:block, dan kekhususannya sama dengan
       .orcha-ikon-teks — yang tertulis belakangan di berkas ini yang menang.
       Akibatnya pada label ber-ikon, flex-nya TIDAK PERNAH berlaku: ikonnya
       kembali jadi elemen inline, align-items tidak berpengaruh, dan koreksi
       apa pun mandul.
       Butuh dua kelas supaya menang tanpa !important. */
    .orcha-label-kecil.orcha-ikon-teks {
        display: inline-flex;
    }

    /* Ikon yang duduk sendirian di dalam kotak.

       Kotaknya sudah menengahkan isinya dengan flex, tapi yang ditengahkan
       adalah KOTAK BARIS ikonnya — bukan glifnya. Tinggi kotak baris itu
       mengikuti line-height halaman (1,5), sedangkan glifnya digambar di
       garis dasar, dekat sisi bawah kotak baris. Hasilnya glif turun sekitar
       5px: kotak elemennya di tengah, gambarnya tidak.

       Diukur, bukan dikira: sebelum ini ruang di atas ikon 14px sedangkan di
       bawahnya 4px. Alat ukur yang membandingkan kotak elemen tidak melihat
       apa pun — mata melihatnya, dan mata benar.

       line-height 1 membuat kotak baris sepas glifnya, dan vertical-align
       bawaan bootstrap-icons dinolkan karena geseran itu memang ditujukan
       untuk ikon yang berdampingan dengan teks, bukan yang berdiri sendiri
       di dalam kotak. */
    .orcha-cek-ikon > i,
    .orcha-ikon-kotak > i,
    .orcha-cek-tutup > i,
    .orcha-bagian-nomor > i {
        display: block;
        line-height: 1;
    }

    .orcha-cek-ikon > i::before,
    .orcha-ikon-kotak > i::before,
    .orcha-cek-tutup > i::before,
    .orcha-bagian-nomor > i::before {
        display: block;
    }

    /* Kotak ikon berlatar gradien (.orcha-ikon di kartu dashboard & kartu
       keuntungan, .orcha-hero-ikon di kartu utama keuntungan).

       Gejalanya sama — glif duduk di bawah tengah — tetapi sebabnya BUKAN
       vertical-align, jadi obat di atas tidak menyembuhkannya. Sudah diukur di
       peramban: kotak elemen <i>-nya dipatok 16 x 16 px oleh gaya bawaan ikon,
       sedangkan glifnya digambar sebesar font kotaknya (21,6 px di kartu
       angka, 24 px di kartu utama). Kotak <i> itu sendiri sudah tepat di
       tengah — terukur 16 px di atas dan 16 px di bawahnya — tetapi glif yang
       lebih besar daripada kotaknya meluber, dan luberan itu jatuh seluruhnya
       ke bawah dan ke kanan karena isi blok mulai dari pojok kiri atas.

       Karena itu <i>-nya dijadikan wadah flex yang menengahkan glifnya:
       luberannya terbagi rata ke atas-bawah dan kiri-kanan, sehingga yang
       terlihat mata benar-benar di tengah. Ukuran 16 px-nya sengaja tidak
       diutak-atik — yang salah bukan kotaknya, melainkan letak luberannya. */
    .orcha-ikon > i,
    .orcha-jatuhkan-ikon > i,
    .orcha-hero-ikon > i,
    .orcha-foto-kosong > i,
    .orcha-etalase-foto .kosong > i {
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .orcha-ikon > i::before,
    .orcha-jatuhkan-ikon > i::before,
    .orcha-hero-ikon > i::before,
    .orcha-foto-kosong > i::before,
    .orcha-etalase-foto .kosong > i::before {
        display: block;
        line-height: 1;
    }

    /* Kotak ikon bawaan layout (.stat-icon-wrapper, .empty-state-icon-wrapper)
       SENGAJA tidak disentuh.

       Keduanya sudah menengahkan glifnya dengan tepat — terukur 20px di
       keempat sisinya — karena ikonnya sendiri dijadikan wadah flex. Aturan
       di atas sempat saya berlakukan ke sini juga, dan justru merusaknya:
       glif turun 12px sekaligus bergeser 12px ke kanan, persis yang terlihat
       pada lingkaran di halaman pembatalan.

       Yang sudah benar tidak perlu diseragamkan. */

    /* Isian tanpa ikon tidak lagi menyisakan tempat kosong untuk ikon.

       Layout lemon memberi SETIAP .form-control ruang 45px di kiri, karena
       isian pencarian di halaman-halaman lama selalu berikon. Isian yang tidak
       berikon ikut kena: teksnya mulai jauh dari tepi kiri, menggantung tanpa
       apa pun di sebelahnya, dan tidak sejajar dengan label maupun keterangan
       di bawahnya.

       Ruang itu dikembalikan hanya untuk isian yang ikonnya memang ada —
       dikenali dari .form-control-icon yang berdiri sebagai saudaranya di
       dalam pembungkus yang sama. Gaya ini hanya termuat di halaman Orcha,
       jadi halaman lemon lainnya tidak tersentuh. */
    .form-control {
        padding-left: 20px !important;
    }

    .form-control-icon ~ .form-control {
        padding-left: 45px !important;
    }

    /* Layout yang sama memasang padding tegak 0. Pada isian satu baris itu
       tidak terasa karena tingginya sudah dipatok, tapi pada kotak berbaris
       banyak teksnya menempel ke garis atas. */
    textarea.form-control {
        padding-top: .55rem !important;
        padding-bottom: .55rem !important;
    }

    /* Kepala kelompok: satu pesanan, berapa pun bukti transfernya.
       Dibedakan dengan latar dan garis kiri supaya mata langsung menemukan
       batas antar pesanan tanpa harus membaca kodenya baris demi baris. */
    .orcha-tabel tr.orcha-grup > td {
        background: #eef5fa;
        border-left: 3px solid var(--orc-primer);
        padding-top: .55rem;
        padding-bottom: .55rem;
    }

    .orcha-tabel tr.orcha-grup:hover > td {
        background: #eef5fa;
    }

    .orcha-tabel tr.orcha-anggota > td:first-child {
        border-left: 3px solid #dbe7f0;
    }

    .orcha-halaman .page-link {
        border: 0;
        border-radius: .6rem;
        margin: 0 .15rem;
        color: var(--orc-tinta);
        font-weight: 600;
    }

    .orcha-halaman .active .page-link {
        background: linear-gradient(135deg, var(--orc-primer), var(--orc-primer-2));
        color: #fff;
    }

    /* Tabel lebar tetap bisa digulung di layar kecil, tanpa memaksa halaman
       ikut bergeser ke samping. */
    .orcha-gulung {
        overflow-x: auto;
    }

    /* ---------- Formulir: isian lebih lega ---------- */
    .orcha-form .form-control,
    .orcha-form .form-select {
        padding: .7rem .9rem;
        font-size: .975rem;
        border-radius: .7rem;
        border-color: #e3e8ef;
    }

    .orcha-form .form-control:focus,
    .orcha-form .form-select:focus {
        border-color: var(--orc-primer);
        box-shadow: 0 0 0 .2rem rgba(124, 58, 237, .12);
    }

    .orcha-form .form-label {
        color: var(--orc-tinta);
        margin-bottom: .35rem;
    }

    /* ---------- Cip pilihan (destinasi & fasilitas) ----------
       Satu irama jarak dipakai seluruh kartu: judul .35rem, petunjuk 1rem,
       antar cip .5rem, antar blok 1rem. */
    .orcha-kepala-kartu {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .35rem;
    }

    .orcha-petunjuk {
        color: #6b7785;
        font-size: .82rem;
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .orcha-kosong {
        display: block;
        color: #98a2b3;
        font-size: .82rem;
        font-style: italic;
        margin-bottom: 1rem;
    }

    .orcha-terpilih,
    .orcha-saran {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: 1rem;
    }

    .orcha-saran {
        max-height: 210px;
        overflow-y: auto;
        padding: .75rem;
        border: 1px dashed #dbe3ec;
        border-radius: .8rem;
        background: #fbfcfe;
        align-content: flex-start;
    }

    /* Cip = satu wadah berisi tombol pilih dan tombol hapus. Keduanya SELALU
       terlihat, tidak muncul-hilang saat disorot. */
    .orcha-cip {
        display: inline-flex;
        align-items: stretch;
        border-radius: 999px;
        border: 1px solid #dbe3ec;
        background: #fff;
        overflow: hidden;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .orcha-cip:hover {
        border-color: var(--orc-primer);
        box-shadow: 0 2px 8px rgba(124, 58, 237, .12);
    }

    .orcha-cip button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        background: transparent;
        font-size: .85rem;
        line-height: 1;
        color: #445;
        transition: background .15s ease, color .15s ease;
    }

    /* Ikon selalu sejajar tengah dengan tulisannya. */
    .orcha-cip button i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
    }

    .orcha-cip-pilih {
        gap: .4rem;
        padding: .5rem .85rem;
    }

    .orcha-cip-pilih:hover {
        background: #eef6fb;
        color: var(--orc-tinta);
    }

    .orcha-cip-pilih i {
        font-size: .8rem;
        opacity: .7;
    }

    /* Hapus dari daftar pilihan — merah terlihat tanpa perlu disorot. */
    .orcha-cip-hapus {
        padding: .5rem .65rem;
        border-left: 1px solid #f0dbe0 !important;
        color: #d63955 !important;
        background: #fef6f7;
    }

    .orcha-cip-hapus:hover {
        background: #f8d7dd;
        color: #93122c !important;
    }

    .orcha-cip-hapus i {
        font-size: .72rem;
    }

    /* SUDAH MASUK PAKET — hijau, jelas beda dari yang belum dipilih. */
    .orcha-cip-sudah {
        border-color: #9fd6b4;
        background: #eafaf1;
    }

    .orcha-cip-sudah:hover {
        border-color: #34996a;
        box-shadow: 0 2px 8px rgba(52, 153, 106, .18);
    }

    .orcha-cip-sudah .orcha-cip-pilih {
        color: #14683f;
        font-weight: 700;
    }

    .orcha-cip-sudah .orcha-cip-pilih:hover {
        background: #d8f3e4;
        color: #0d4e2f;
    }

    .orcha-cip-sudah .orcha-cip-pilih i {
        color: #1a8552;
        opacity: 1;
    }

    .orcha-cip-sudah .orcha-cip-hapus {
        border-left-color: #c3e6d2 !important;
        background: #f4fbf7;
    }

    /* Cip terpilih di baris atas: tetap gelap saat disorot, silangnya kontras. */
    .orcha-cip-aktif {
        border-color: transparent;
        background: linear-gradient(135deg, var(--orc-primer), var(--orc-primer-2));
        align-items: center;
        padding-left: .85rem;
        color: #fff;
        font-size: .85rem;
        font-weight: 600;
        gap: .15rem;
    }

    .orcha-cip-aktif:hover {
        border-color: transparent;
        box-shadow: 0 3px 10px rgba(31, 45, 61, .28);
    }

    .orcha-cip-buang {
        padding: .5rem .7rem .5rem .35rem;
        color: #ffd772 !important;
    }

    .orcha-cip-buang:hover {
        background: rgba(255, 255, 255, .18);
        color: #fff !important;
    }

    .orcha-cip-buang i {
        font-size: .9rem;
    }

    /* Awalan "Rp" dalam kotak terpisah (input-group) sudah tidak dipakai —
       seluruh isian uang kini memakai .orcha-rupiah, yang menaruh "Rp" di
       dalam isiannya. Aturannya dihapus, bukan ditinggalkan menganggur:
       gaya yang tidak lagi menempel pada apa pun akan dipakai ulang keliru
       oleh orang berikutnya yang mencarinya. */

    /* Pratinjau kartu: perbandingan 16:10, sama seperti kartu paket di website. */
    .orcha-pratinjau-kartu {
        position: relative;
        aspect-ratio: 16 / 10;
        border-radius: .8rem;
        overflow: hidden;
        background: var(--orc-primer-2);
    }

    .orcha-pratinjau-kartu img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        opacity: .92;
    }

    .orcha-pratinjau-label {
        position: absolute;
        left: .55rem;
        bottom: .5rem;
        padding: .2rem .55rem;
        border-radius: 999px;
        background: rgba(31, 45, 61, .78);
        color: #ffd772;
        font-size: .64rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    /* Lencana penayangan — warnanya menerangkan keadaan, bukan hiasan. */
    .orcha-lencana-tayang,
    .orcha-lencana-terjadwal,
    .orcha-lencana-berakhir,
    .orcha-lencana-draf,
    .orcha-lencana-arsip {
        font-weight: 700;
        font-size: .68rem;
        letter-spacing: .03em;
        text-transform: uppercase;
        flex: 0 0 auto;
    }

    .orcha-lencana-tayang {
        background: #eafaf1;
        color: #14683f;
    }

    .orcha-lencana-terjadwal {
        background: #fff4e0;
        color: #96590d;
    }

    .orcha-lencana-berakhir {
        background: #eef0f4;
        color: #5b6472;
    }

    .orcha-lencana-draf {
        background: #eef6fb;
        color: #14618f;
    }

    .orcha-lencana-arsip {
        background: #fef6f7;
        color: #a33a51;
    }

    /* Lencana status bukti pembayaran */
    .orcha-lencana-bayar-menunggu,
    .orcha-lencana-bayar-diterima,
    .orcha-lencana-bayar-ditolak {
        font-weight: 700;
        font-size: .68rem;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .orcha-lencana-bayar-menunggu {
        background: #fff4e0;
        color: #96590d;
    }

    .orcha-lencana-bayar-diterima {
        background: #eafaf1;
        color: #14683f;
    }

    .orcha-lencana-bayar-ditolak {
        background: #fef6f7;
        color: #a33a51;
    }

    /* Ikon di tombol tambah baris ikut sejajar tengah dengan tulisannya. */
    .orcha-tambah-baris i,
    .orcha-bahaya i,
    .orcha-otomatis i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
        font-size: .85em;
    }

    .orcha-otomatis {
        background: #eafaf1;
        color: #14683f;
        font-weight: 700;
        font-size: .62rem;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .orcha-hitung-ulang {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border: 0;
        background: transparent;
        padding: 0;
        color: var(--orc-primer);
        font-size: .72rem;
        font-weight: 700;
        text-decoration: underline;
    }

    .orcha-hitung-ulang:hover {
        color: var(--orc-tinta);
    }

    .orcha-hitung-ulang i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
    }

    .orcha-hitung {
        background: #eef6fb;
        color: var(--orc-tinta);
        font-weight: 700;
        flex: 0 0 auto;
    }

    /* Kotak isian + tombol Tambah: diberi jarak, tidak lagi menempel. */
    .orcha-tambah {
        display: flex;
        gap: .6rem;
        align-items: stretch;
    }

    .orcha-tambah .form-control {
        flex: 1 1 auto;
    }

    .orcha-tambah .btn {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        padding: .7rem 1.1rem;
        white-space: nowrap;
    }

    /* ---------- Itinerary per hari ---------- */
    .orcha-hari {
        padding: 1rem;
        border: 1px solid #e8eef5;
        border-radius: .9rem;
        background: #fbfcfe;
        margin-bottom: 1rem;
    }

    /* Baris agenda: jarak antar kolom & antar baris sama dengan kartu lain. */
    .orcha-baris-agenda {
        display: flex;
        gap: .5rem;
        margin-bottom: .5rem;
    }

    .orcha-baris-agenda .btn {
        flex: 0 0 auto;
    }

    .orcha-nomor-hari {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #fff;
        background: linear-gradient(135deg, var(--orc-primer), var(--orc-primer-2));
    }

    /* Kolom jam: cukup lebar supaya "07.00" tidak terpotong, dan padding
       samping dikecilkan karena isinya memang pendek. */
    .orcha-jam {
        flex: 0 0 128px;
        text-align: center;
        padding-left: .5rem !important;
        padding-right: .5rem !important;
        font-variant-numeric: tabular-nums;
        letter-spacing: .02em;
    }

    @media (max-width: 575.98px) {
        .orcha-jam {
            flex-basis: 104px;
        }
    }

    /* Tambah kegiatan / Tambah hari — biru merek, bukan abu-abu yang
       tenggelam di antara isian. */
    .orcha-tambah-baris {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        border: 1px dashed #9ec7e0;
        background: #f2f9fd;
        color: #14618f;
        font-size: .84rem;
        font-weight: 600;
        border-radius: .7rem;
        padding: .5rem .9rem;
        transition: all .15s ease;
    }

    .orcha-tambah-baris:hover {
        background: #dcedf8;
        border-color: var(--orc-primer);
        color: var(--orc-tinta);
    }

    .orcha-tambah-hari {
        width: 100%;
        border-style: solid;
        padding: .75rem;
    }

    /* Tombol aksi di tabel: tiap tindakan punya warnanya sendiri supaya
       terbedakan sekilas — bukan tiga tombol abu-abu yang sama rupa. */
    .orcha-aksi {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .6rem;
        border: 1px solid transparent;
        padding: .35rem .6rem;
        transition: all .15s ease;
    }

    .orcha-aksi i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
        font-size: .82rem;
    }

    /* Lihat di website — biru laut, sama dengan warna merek Orcha */
    .orcha-aksi-lihat {
        background: #eef6fb;
        border-color: #c7e2f2;
        color: #14618f;
    }

    .orcha-aksi-lihat:hover {
        background: #d8ecf8;
        border-color: var(--orc-primer);
        color: var(--orc-tinta);
    }

    /* Hapus — merah, dan merahnya terlihat tanpa perlu disentuh dulu.
       Tindakan yang tidak bisa dibatalkan tidak pantas menyamar jadi tombol
       biasa sampai kursor lewat di atasnya. */
    .orcha-aksi-hapus {
        background: #fdecee;
        border-color: #f6c9cd;
        color: #9b2530;
    }

    .orcha-aksi-hapus:hover {
        background: #fbd9dd;
        border-color: #c2323c;
        color: #7f1d28;
    }

    /* Ubah — emas merek, tindakan yang paling sering dipakai */
    .orcha-aksi-ubah {
        background: #fff6e3;
        border-color: #f3ddb0;
        color: #8a5a09;
    }

    .orcha-aksi-ubah:hover {
        background: #ffedcb;
        border-color: #d9a441;
        color: #6b4406;
    }

    /* Tombol bahaya: merahnya terlihat tanpa perlu disorot. */
    .orcha-bahaya {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        border: 1px solid #f1b6c1;
        background: #fef6f7;
        color: #c2415a;
        font-weight: 600;
        border-radius: .7rem;
        transition: all .15s ease;
    }

    .orcha-bahaya:hover {
        background: #f8d7dd;
        border-color: #d63955;
        color: #93122c;
    }

    .orcha-bahaya i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
    }

    /* Kolom kanan ikut turun saat digulung, seperti halaman paket di publik.
       Hanya di layar lebar — di layar sempit kolomnya sudah menumpuk. */
    @media (min-width: 1200px) {
        .orcha-lengket {
            position: sticky;
            top: 1rem;
        }
    }

    /* Tombol "Tambah" di kepala halaman daftar.

       Tingginya dipatok pada angka yang sama dengan isian di layout —
       .form-control dan .form-select di sana dipasang height: 48px — supaya
       tombol, kotak pencarian, dan penyaring di satu baris berhenti di garis
       yang sama. Sebelumnya tombolnya 44px: empat piksel lebih pendek, cukup
       untuk membuat barisnya terlihat tidak rata di setiap halaman daftar.

       Sengaja TIDAK dipasang pada .orcha-tombol, karena kelas itu juga dipakai
       tombol kecil (btn-sm) di Etalase yang memang tidak boleh ikut membesar. */
    .orcha-tombol-tambah {
        height: 48px;
    }

    /* Ikon di dalam tombol selalu sejajar tengah dengan tulisannya. */
    .orcha-form .btn,
    .orcha-tombol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        white-space: nowrap;
    }

    .orcha-form .btn i,
    .orcha-tombol i,
    .orcha-ikon i,
    .orcha-nomor-hari i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
    }

    /* ============ DETAIL PELANGGAN & RIWAYAT KESEHATAN ============
       Gayanya ditulis di sini, bukan lewat Vite: public/build tidak ikut
       terkirim ke server, jadi kelas yang hanya ada di berkas CSS tidak
       pernah sampai. */

    .orcha-label-kecil {
        font-size: .68rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 600;
        display: block;
    }

    /* ---- ringkasan angka di kepala halaman detail ---- */
    .orcha-ringkas {
        background: linear-gradient(135deg, #f8fbfd 0%, #eef6fb 100%);
        border: 1px solid #dbe7f0;
        border-radius: 1rem;
        padding: .95rem 1.1rem;
        height: 100%;
    }

    .orcha-ringkas .angka {
        font-size: 1.28rem;
        font-weight: 800;
        color: var(--orc-tinta);
        line-height: 1.25;
    }

    .orcha-ringkas.sisa .angka { color: #b45309; }
    .orcha-ringkas.lunas .angka { color: #047857; }

    /* ---- palang kemajuan pembayaran ---- */
    .orcha-palang {
        height: 9px;
        background: #e8eef5;
        border-radius: 99px;
        overflow: hidden;
    }

    .orcha-palang span {
        display: block;
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, var(--orc-primer), var(--orc-primer-2));
    }

    .orcha-palang.lunas span { background: linear-gradient(90deg, #10b981, #047857); }

    /* ---- baris peserta ---- */
    .orcha-peserta {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .6rem .8rem;
        border: 1px solid #eef2f7;
        border-radius: .85rem;
        background: #fff;
    }

    .orcha-peserta + .orcha-peserta { margin-top: .45rem; }

    .orcha-inisial {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: .82rem;
        letter-spacing: .02em;
        color: var(--orc-tinta);
        background: linear-gradient(135deg, #dbeafe, #cfe4f2);
    }

    .orcha-inisial-awas {
        color: #7f1d1d;
        background: linear-gradient(135deg, #fee2e2, #fecaca);
    }

    /* ---- kartu riwayat kesehatan ---- */
    .orcha-kesehatan {
        border: 1px solid #e6edf3;
        border-left: 4px solid #cfe4f2;
        border-radius: 1rem;
        padding: 1rem 1.1rem;
        background: #fff;
    }

    .orcha-kesehatan + .orcha-kesehatan { margin-top: .85rem; }

    /* Pita merah di sisi kiri: saat rombongannya dua belas orang, lencana
       kecil di pojok kartu tidak pernah terbaca. */
    .orcha-kesehatan-awas {
        border-left-color: #dc2626;
        background: linear-gradient(90deg, #fff7f7 0%, #fff 42%);
    }

    .orcha-lencana-awas,
    .orcha-lencana-aman {
        font-size: .7rem;
        font-weight: 700;
        padding: .28rem .6rem;
        border-radius: 99px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }

    .orcha-lencana-awas { color: #b91c1c; background: #fee2e2; }
    .orcha-lencana-aman { color: #047857; background: #d1fae5; }
    .orcha-lencana-catat { color: #8a6410; background: #fef3c7; }

    /* Alasan di balik penandanya, supaya admin tidak menebak bagian mana yang
       membuat peserta ini ditandai. */
    .orcha-alasan {
        font-size: .84rem;
        border-radius: .65rem;
        padding: .5rem .75rem;
        line-height: 1.5;
    }

    .orcha-alasan-tinggi { background: #fff5f5; border-left: 3px solid #dc2626; color: #7f1d1d; }
    .orcha-alasan-sedang { background: #fffaf0; border-left: 3px solid #f59e0b; color: #78350f; }
    .orcha-alasan ul { font-size: .84rem; }
    .orcha-alasan-tenang { background: #f4f9fd; border-left: 3px solid #2b7fb8; color: var(--orc-tinta); }

    /* Perincian biaya sewa: bacaan, bukan isian — jadi ditulis rapat dan tanpa
       garis tabel, supaya tidak menyaingi kotak denda yang memang disunting. */
    .orcha-rincian-sewa { width: 100%; font-size: .82rem; }
    .orcha-rincian-sewa td { padding: .18rem 0; border: 0; vertical-align: top; }
    .orcha-rincian-sewa td:last-child { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .orcha-rincian-sewa .catatan { display: block; font-size: .74rem; opacity: .72; }
    .orcha-rincian-sewa .jumlah td { padding-top: .4rem; border-top: 1px solid rgba(43, 127, 184, .28); font-weight: 700; }

    /* Sisa yang masih harus dibayar: angka yang dibacakan saat menagih, jadi
       ia yang paling menonjol di antara semua baris. */
    .orcha-rincian-sewa .jumlah td.sisa { color: #b45309; }

    /* ===== Lembar serah terima =====
       Lembarnya panjang dan isinya bermacam-macam — waktu, angka, pemeriksaan
       bodi, uang. Sebagai satu kartu datar, semuanya terbaca sederajat dan
       admin kehilangan tempat saat menggulung. Tiap urusan dapat kartunya
       sendiri dengan kepala bernomor, jadi urutan kerjanya terlihat. */
    .orcha-bagian-kepala { display: flex; align-items: center; gap: .75rem; margin-bottom: 1rem; }

    .orcha-bagian-nomor {
        width: 36px; height: 36px; flex: none;
        display: flex; align-items: center; justify-content: center;
        border-radius: 12px; color: #fff; font-size: 1rem;
        background: linear-gradient(135deg, #3d9bd4, var(--orc-primer));
        box-shadow: 0 5px 14px rgba(43, 127, 184, .3);
    }

    .orcha-bagian-judul { font-weight: 700; color: var(--orc-tinta); line-height: 1.25; }
    .orcha-bagian-sub { font-size: .79rem; color: #64748b; line-height: 1.45; }

    /* Dua kolom berdampingan: keadaan saat diserahkan, dan saat kembali.
       Bentuknya sengaja kembar supaya yang dibandingkan bagian yang sama —
       itulah seluruh guna lembar ini saat penyewa membantah adanya kerusakan. */
    .orcha-keadaan {
        border: 1px solid #dceaf4; border-radius: 16px;
        padding: .95rem 1rem 1rem; height: 100%; background: #fafdff;
    }

    .orcha-keadaan.kembali { border-color: #d5e9dc; background: #fafdfb; }

    .orcha-keadaan-kepala {
        display: flex; align-items: center; gap: .45rem;
        font-size: .72rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: var(--orc-primer); margin-bottom: .85rem;
    }

    .orcha-keadaan.kembali .orcha-keadaan-kepala { color: #1f7a44; }

    .orcha-keadaan-kepala i {
        display: flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 7px;
        background: #e7f2fa; font-size: .8rem;
        /* Kotaknya sudah ditengahkan flex terhadap KOTAK BARIS tulisan.
           Tulisannya huruf besar semua, jadi tintanya tidak mengisi kotak baris
           itu sampai bawah — pusat tintanya lebih tinggi daripada pusat kotak.
           Terukur 1,3px; digeser turun 1px, sisanya di bawah satu piksel. */
        position: relative;
        top: 1px;
    }

    .orcha-keadaan.kembali .orcha-keadaan-kepala i { background: #e6f4ea; }

    /* ============ ISI wire:loading DISEMBUNYIKAN SEJAK AWAL ============

       Livewire menyembunyikannya dengan menulis style="display:none" saat
       halaman dipasang, lalu membukanya saat ada permintaan berjalan. Artinya
       sebelum skripnya sempat jalan — atau bila ia tidak jalan sama sekali —
       pemintal dan tulisan "Menyimpan…" tergambar dari awal, berdampingan
       dengan tulisan tombol yang sebenarnya. Yang terbaca admin: tombol yang
       sedang bekerja padahal belum ditekan.

       Maka disembunyikan lewat gaya, bukan hanya lewat skrip. Livewire menulis
       display-nya sebagai gaya sebaris, dan gaya sebaris menang atas aturan di
       sini — jadi begitu permintaannya berjalan, ia tetap muncul.

       Hanya [wire:loading] polos. [wire:loading.attr] adalah atribut lain
       (yang menonaktifkan tombol) dan tidak ikut tersembunyi. */
    [wire\:loading] { display: none; }

    /* Tombol penutup lembar serah terima.

       Ukurannya sengaja lebih besar daripada tombol lain di halaman ini:
       inilah satu-satunya penekanan yang membuat pekerjaan sepanjang lembar
       tadi benar-benar tercatat. Sebagai tombol seukuran tombol "Sekarang" di
       tengah lembar, ia terbaca sederajat dengan perkakas kecil — padahal
       menutup lembar tanpa menekannya berarti dendanya tidak pernah ditagihkan.

       Tingginya 52px, sedikit di atas isian yang 48px, supaya jelas ia penutup
       dan bukan medan isian berikutnya. */
    .orcha-tombol-lembar {
        width: 100%;
        min-height: 52px;
        font-size: .95rem;
        border-radius: .85rem;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .orcha-tombol-lembar i { font-size: 1.05em; }

    /* Di layar sempit teksnya boleh turun baris daripada terpotong. */
    @media (max-width: 400px) {
        .orcha-tombol-lembar { white-space: normal; }
    }

    /* Apa saja yang tercakup harga sewanya.

       Keterangannya sudah ada sejak dulu berupa kalimat — "BBM dan tol termasuk
       (+Rp 300.000/hari), parkir ditanggung penyewa" — tapi harus dibaca sampai
       habis. Yang ditanya di loket cuma satu pos: BBM ditanggung siapa. Bentuk
       lencana bisa dijawab dengan melirik.

       Yang ditanggung penyewa TIDAK disembunyikan. Menyebut yang termasuk saja
       membuat penyewa mengira sisanya juga ditanggung — dan itu baru ketahuan
       saat menagih. */
    .orcha-termasuk { display: flex; flex-wrap: wrap; gap: .4rem; }

    .orcha-cip-termasuk {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .76rem;
        font-weight: 600;
        border-radius: 99px;
        padding: .26rem .68rem;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .orcha-cip-termasuk.ya { background: #e6f4ea; border-color: #cbe8d4; color: #1f7a44; }
    .orcha-cip-termasuk.tidak { background: #f4f6f9; border-color: #e2e8f0; color: #64748b; }

    /* Catatan nominalnya menyusul di dalam lencana yang sama, bukan lencana
       kedua: "BBM" dan "Rp 300.000/hari" adalah satu keterangan. */
    .orcha-cip-termasuk .catatan { font-weight: 500; opacity: .8; }

    /* Lencananya sudah inline-flex, tapi yang ditengahkan flex adalah KOTAK
       BARIS ikonnya — bukan glifnya. Tinggi kotak baris itu mengikuti
       line-height lencana, sedangkan glifnya digambar di garis dasar, dekat
       sisi bawah. Terukur turun 2,23px dari tengah hurufnya. */
    .orcha-cip-termasuk i {
        display: block;
        line-height: 1;
        font-size: .95em;
        /* line-height 1 saja mengangkatnya terlalu jauh — terukur 2,14px di atas
           tengah huruf. Tintanya memang tidak duduk di tengah kotak em-nya.
           Digeser turun 2px; sisanya di bawah seperlima piksel. */
        position: relative;
        top: 2px;
    }

    .orcha-cip-termasuk i::before { display: block; }

    /* Medan bacaan pada halaman detail.

       Sebelumnya label dan nilainya ditumpuk polos, dua belas pasang berderet
       tanpa batas apa pun — mata tidak punya pegangan mana pasangan yang mana,
       dan admin yang awam membaca halaman itu seperti membaca daftar kata.

       Bentuknya sengaja menyerupai kotak isian di lembar serah terima, supaya
       kedua halaman terbaca sebagai satu keluarga. Bedanya hanya ini tidak bisa
       diketik — dan itu terlihat dari latarnya yang rata, bukan putih. */
    .orcha-medan {
        height: 100%;
        padding: .58rem .8rem .62rem;
        /* Menyalin .form-control lemon: garis 2px #e2e8f0, radius 12px. */
        border: var(--orc-garis-isian);
        border-radius: var(--orc-radius-isian);
        background: #fff;
    }

    .orcha-medan .orcha-label-kecil { margin-bottom: .1rem; }

    .orcha-medan .isi {
        font-weight: 700;
        font-size: .92rem;
        line-height: 1.4;
        color: var(--orc-tinta);
        word-break: break-word;
    }

    /* Yang belum terisi ditulis lebih redup: "—" setebal nilai sungguhan
       membuat halaman terlihat penuh padahal separuhnya kosong. */
    .orcha-medan .isi.kosong { color: #9aa8b8; font-weight: 600; }

    .orcha-medan .isi a { color: inherit; text-decoration: none; }
    .orcha-medan .isi a:hover { text-decoration: underline; }

    /* Selisih kilometer: hitungan yang selama ini dikerjakan admin di kepala. */
    .orcha-selisih {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .76rem; font-weight: 600; color: #1f7a44;
        background: #e6f4ea; border-radius: 99px; padding: .18rem .6rem;
    }

    .orcha-cip-kondisi {
        font-size: .72rem;
        font-weight: 600;
        color: var(--orc-tinta);
        background: #eef6fb;
        border: 1px solid #cfe4f2;
        border-radius: 99px;
        padding: .2rem .58rem;
    }

    /* ===== Daftar kerusakan baru =====
       Sebelumnya tiga kalimat berderet — "Bodi samping kiri : baik → lecet /
       minor" — yang harus dibaca satu per satu sampai habis untuk tahu bagian
       mana saja yang rusak. Nama bagian, kondisi awal, dan kondisi akhir
       bercampur dalam satu baris tanpa kolom, jadi mata tidak bisa menyusurinya
       ke bawah.

       Sekarang tiap bagian jadi barisnya sendiri: namanya di kiri, perubahan
       kondisinya di kanan sebagai dua lencana. Yang dicari admin — bagian mana
       yang memburuk dan jadi apa — terbaca dengan melirik. */
    .orcha-rusak { display: flex; flex-direction: column; gap: .35rem; margin-top: .6rem; }

    .orcha-rusak-baris {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        padding: .45rem .7rem;
        border-radius: 10px;
        background: rgba(255, 255, 255, .72);
        border: 1px solid rgba(185, 28, 28, .12);
    }

    .orcha-rusak-bagian { font-weight: 700; font-size: .86rem; color: #7f1d1d; }

    .orcha-rusak-ubah { display: inline-flex; align-items: center; gap: .4rem; }

    /* Panahnya dijadikan kotak lentur supaya tintanya duduk setinggi kedua
       lencana; sebagai huruf biasa ia jatuh ke garis dasar. */
    .orcha-rusak-ubah .panah {
        display: flex;
        align-items: center;
        color: #b91c1c;
        opacity: .7;
        font-size: .8rem;
    }

    /* line-height 1 memaskan kotak barisnya ke glif — tanpa itu panahnya turun
       3px dari tinta tulisan di lencana sebelahnya. Sisanya 1px, digeser. */
    .orcha-rusak-ubah .panah i {
        display: block;
        line-height: 1;
        position: relative;
        top: 1px;
    }

    .orcha-cip-kondisi.awal { background: #f4f6f9; border-color: #e2e8f0; color: #64748b; }
    .orcha-cip-kondisi.akhir { background: #fee2e2; border-color: #f6c9cd; color: #9b2530; }

    /* Lencana jumlah pada kepala kotaknya. */
    .orcha-rusak-jumlah {
        font-size: .7rem;
        font-weight: 700;
        color: #9b2530;
        background: #fee2e2;
        border-radius: 99px;
        padding: .1rem .5rem;
    }

    .orcha-kotak-medis {
        background: #fafcfd;
        border: 1px solid #eef2f7;
        border-radius: .75rem;
        padding: .55rem .8rem;
    }

    .orcha-baris-medis {
        padding: .32rem 0;
        font-size: .86rem;
    }

    .orcha-baris-medis + .orcha-baris-medis { border-top: 1px dashed #e6edf3; }

    .orcha-kotak-darurat {
        background: #f7fbfd;
        border: 1px dashed #cfe4f2;
        border-radius: .75rem;
        padding: .55rem .8rem;
        font-size: .86rem;
        color: var(--orc-tinta);
    }

    .orcha-tautan-wa {
        color: #128c7e;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .orcha-tautan-wa:hover { text-decoration: underline; }

    /* ---- bukti pembayaran ---- */
    .orcha-bukti {
        width: 62px;
        height: 62px;
        object-fit: cover;
        border-radius: .7rem;
        border: 1px solid #e6edf3;
    }

    .orcha-garis-waktu {
        border-left: 2px solid #e8eef5;
        padding-left: 1rem;
        margin-left: .45rem;
    }

    .orcha-garis-waktu > div { position: relative; }

    /* ============ IKON SEJAJAR TULISAN ============
       Huruf ikon Bootstrap duduk sedikit di bawah garis dasar huruf biasa,
       jadi di dalam kalimat ia terlihat melorot. Dua penanganan, sesuai
       wadahnya:

       1. Wadah yang memang berjajar (tombol, lencana, tautan) memakai flex
          dengan align-items:center — ikonnya ikut tengah dengan sendirinya.
       2. Ikon yang menempel pada tulisan biasa diangkat sedikit dengan
          vertical-align. Nilainya tidak bulat karena memang mengikuti selisih
          garis dasar huruf, bukan angka yang dikira-kira.

       Aturan ini hanya berlaku di halaman Orcha: berkas gaya ini disisipkan
       per halaman, tidak mengubah tampilan lemon yang lain. */

    .orcha-label-kecil i,
    .orcha-ringkas i,
    .orcha-peserta i,
    .orcha-kotak-darurat i,
    .orcha-kotak-medis i,
    .orcha-kesehatan i,
    .alert i {
        /* Nilainya diukur ulang dengan membandingkan TINTA ikon terhadap TINTA
           tulisan di sebelahnya, bukan kotak elemennya. Pada -.115em ikonnya
           masih turun 1,94px dari tengah hurufnya — cukup untuk terlihat pada
           label sekecil ini, dan alat ukur yang membandingkan kotak tidak
           melihatnya sama sekali.

           Satuannya em, bukan piksel, supaya nilainya tetap benar di label
           11px maupun di kalimat peringatan yang hurufnya lebih besar.

           Tandanya positif: vertical-align negatif justru MENURUNKAN ikon. */
        vertical-align: .058em;
    }

    /* Judul kartu memakai cara yang sama dengan tombol dan lencana: flex,
       bukan vertical-align. Diukur dari halaman hasil render, cara flex
       menempatkan ikonnya tepat setinggi tulisan, sedangkan vertical-align
       pada huruf sebesar judul justru menjatuhkannya beberapa piksel. */
    .orcha-judul-ikon {
        display: flex;
        align-items: center;
        gap: .45rem;
    }

    .orcha-judul-ikon i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
        vertical-align: 0;
        font-size: .95em;
    }

    /* Wadah ikon ukuran kartu.
       Wadah bawaan lemon berukuran 56 px dengan ikon 1,75rem — pas untuk kartu
       angka di dashboard, terlalu besar untuk baris keterangan. Mengecilkan
       kotaknya saja lewat gaya menempel membuat ikonnya justru sesak, karena
       ukuran hurufnya tetap. Jadi keduanya diatur bersama di sini. */
    .orcha-ikon-kotak {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        line-height: 1;
        color: #fff;
    }

    .orcha-ikon-kotak i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
        vertical-align: 0;
    }

    /* Wadah bulat berisi satu ikon: benar-benar di tengah, mendatar maupun
       menurun, berapa pun ukuran yang dipasang. */
    .orcha-ringkas .stat-icon-wrapper,
    .stat-icon-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .stat-icon-wrapper i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        vertical-align: middle;
    }

    /* Lencana kecil: ikon dan tulisannya satu baris, tidak pernah terpisah
       saat tempatnya sempit. */
    .orcha-lencana-awas,
    .orcha-lencana-aman,
    .orcha-lencana-catat,
    .orcha-tautan-wa,
    .orcha-tautan-balik {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        line-height: 1.15;
    }

    .orcha-lencana-awas i,
    .orcha-lencana-aman i,
    .orcha-lencana-catat i,
    .orcha-tautan-wa i,
    .orcha-tautan-balik i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
        vertical-align: 0;
    }

    /* ============ TOMBOL BERWARNA ============
       Semua tombol pernah berwarna abu-abu yang sama, sehingga tindakan
       terpenting di layar tidak pernah menonjol. Warnanya sekarang mengikuti
       ARTI tindakannya, bukan selera: hijau untuk menghubungi lewat WhatsApp
       (warna yang sudah dikenal orang), biru laut merek untuk tindakan utama,
       merah muda untuk data kesehatan, dan putih bergaris untuk tindakan
       netral seperti menutup atau kembali.

       Gradasinya tipis dan bayangannya baru muncul saat disentuh — panel admin
       yang dipakai berjam-jam tidak boleh berkilau berlebihan. */

    .orcha-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        border: 1px solid transparent;
        /* Menyalin .btn lemon: radius 12px, padding 10px 20px. */
        border-radius: var(--orc-radius-tombol);
        padding: var(--orc-padding-tombol);
        font-size: .86rem;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
        text-decoration: none;
        transition: transform .14s ease, box-shadow .14s ease, filter .14s ease;
    }

    .orcha-btn i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
        font-size: .95em;
    }

    .orcha-btn:hover {
        transform: translateY(-1px);
        filter: saturate(1.06);
        text-decoration: none;
    }

    .orcha-btn:active { transform: translateY(0); }

    .orcha-btn:disabled,
    .orcha-btn[disabled] {
        opacity: .6;
        transform: none;
        box-shadow: none;
    }

    /* ============ ISIAN RUPIAH ============
       "Rp" ditaruh DI DALAM kotak isian, bukan sebagai kotak tempelan di
       sebelahnya. Kotak tempelan memecah satu isian jadi dua kotak bersebelahan
       dengan garis di tengahnya, dan pada baris yang berisi beberapa isian uang
       hasilnya terlihat seperti tabel yang patah-patah. */
    .orcha-rupiah {
        position: relative;
    }

    .orcha-rupiah::before {
        content: 'Rp';
        position: absolute;
        left: .85rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: .86rem;
        font-weight: 700;
        color: #94a3b8;
        pointer-events: none;
        z-index: 3;
    }

    /* !important-nya terpaksa: aturan "isian tanpa ikon" di atas memakai
       !important untuk mengalahkan padding-left 45px milik layout, dan
       !important mengalahkan kekhususan apa pun. Tanpa penyeimbang ini,
       angkanya mulai di 20px dan bertumpuk dengan awalan "Rp" — terjadi di
       seluruh isian rupiah, termasuk lembar serah terima kendaraan. */
    .orcha-rupiah .form-control {
        padding-left: 2.4rem !important;
        padding-right: .75rem;
        font-weight: 600;
    }

    /* Isian uang di dalam tabel: sempit, dan angkanya rata kanan. Tanpa
       penyesuaian ini "Rp" di kiri memakan ruang yang sama sedangkan angkanya
       tumbuh ke kiri — pada nominal jutaan, digit depannya tenggelam di balik
       "Rp" dan yang terbaca tinggal ekornya. */
    .orcha-rupiah-kecil::before {
        left: .55rem;
        font-size: .76rem;
    }

    .orcha-rupiah-kecil .form-control {
        padding-left: 1.95rem !important;
        padding-right: .5rem;
        font-size: .84rem;
        /* Cukup untuk "999.999.999" tanpa menyentuh awalan Rp */
        min-width: 9.5rem;
    }

    .orcha-rupiah .form-control:focus + span,
    .orcha-rupiah:focus-within::before {
        color: var(--orc-primer);
    }

    .orcha-btn-kecil {
        padding: .34rem .7rem;
        font-size: .78rem;
        border-radius: .6rem;
    }

    /* Hijau WhatsApp — warna yang sudah dikenal, jadi tidak perlu dibaca dulu */
    .orcha-btn-wa {
        background: linear-gradient(135deg, #25d366, #128c7e);
        color: #fff;
    }

    /* Tombol aksi WhatsApp di dalam tabel — hijau khas WhatsApp supaya
       terbedakan dari aksi lain tanpa perlu dibaca dulu. */
    .orcha-aksi-wa {
        background: #e7f8ee;
        border-color: #b7e6cb;
        color: #128c7e;
    }

    .orcha-aksi-wa:hover {
        background: #d3f2e0;
        border-color: #25d366;
        color: #0b6b5e;
    }

    .orcha-btn-wa:hover {
        color: #fff;
        box-shadow: 0 6px 16px rgba(18, 140, 126, .32);
    }

    /* Biru laut merek — tindakan utama halaman */
    .orcha-btn-utama {
        background: linear-gradient(135deg, var(--orc-primer), var(--orc-primer-2));
        color: #fff;
    }

    .orcha-btn-utama:hover {
        color: #fff;
        box-shadow: 0 6px 16px rgba(124, 58, 237, .34);
    }

    /* Merah muda — khusus data kesehatan, sewarna dengan ikon denyut nadinya */
    /* Membuka riwayat kesehatan.

       Dulu merah menyala. Merah di aplikasi ini berarti hapus atau rugi, dan
       tombol semerah itu membuat admin baru ragu menekannya — padahal yang
       terjadi cuma membuka data, bukan mengubah apa pun. Hijau kebiruan:
       tetap menonjol sebagai tindakan utama di kartunya, tanpa memberi
       peringatan yang tidak ada. */
    .orcha-btn-kesehatan {
        background: linear-gradient(135deg, #0b7a4b, #14a06a);
        color: #fff;
    }

    .orcha-btn-kesehatan:hover {
        color: #fff;
        box-shadow: 0 6px 16px rgba(11, 122, 75, .3);
    }

    /* Emas merek — tindakan pendukung yang tetap perlu terlihat */
    .orcha-btn-emas {
        background: linear-gradient(135deg, #ffc74e, #f59e0b);
        color: #4a3208;
    }

    .orcha-btn-emas:hover {
        color: #4a3208;
        box-shadow: 0 6px 16px rgba(245, 158, 11, .3);
    }

    /* Netral — menutup, kembali, membatalkan tampilan (bukan menghapus data) */
    .orcha-btn-lembut {
        background: #fff;
        border-color: #dbe7f0;
        color: var(--orc-tinta);
    }

    .orcha-btn-lembut:hover {
        background: #f4f8fb;
        border-color: var(--orc-primer);
        color: var(--orc-tinta);
        box-shadow: 0 4px 12px rgba(31, 45, 61, .08);
    }

    /* Tautan kembali: bukan tombol penuh, tapi tetap punya wilayah sentuh */
    .orcha-tautan-balik {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .8rem;
        font-weight: 600;
        color: #64748b;
        text-decoration: none;
        padding: .2rem .5rem .2rem .1rem;
        border-radius: .5rem;
        transition: color .14s ease, background .14s ease;
    }

    .orcha-tautan-balik:hover {
        color: var(--orc-primer);
        background: #eef6fb;
    }

    .orcha-garis-waktu > div::before {
        content: '';
        position: absolute;
        left: -1.42rem;
        top: .45rem;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--orc-primer);
        box-shadow: 0 0 0 3px #fff;
    }

        /* Pratinjau dan pemilih berkas sebagai satu kotak: keduanya satu
           keputusan, dan gambar yang melayang tanpa pembatas tidak terbaca
           sebagai pasangan isiannya. */
        .orcha-foto-kotak {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .75rem;
            border: 1px solid #e3e8ef;
            border-radius: 14px;
            background: #fbfdff;
        }

        .orcha-foto-kotak.galat {
            border-color: #f6c9cd;
            background: #fdf7f8;
        }

        .orcha-foto-rupa {
            position: relative;
            width: 5.5rem;
            height: 4rem;
            flex-shrink: 0;
            border-radius: 10px;
            overflow: hidden;
            background: #eef2f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .orcha-foto-rupa img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Ikon dibungkus kotak yang memusatkan sendiri isinya — ikon telanjang
           tingginya ditentukan kotak barisnya, bukan oleh font-size-nya. */
        .orcha-foto-kosong {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            color: #94a3b8;
            font-size: 1.15rem;
        }

        .orcha-foto-isi { flex: 1; min-width: 0; }

        .orcha-foto-isi .form-text { margin-bottom: 0; }

        /* Kolom kanan panjang: yang lengket PALANG TOMBOLNYA, bukan kolomnya.

           Dipakai bersama formulir armada dan destinasi. Sebelumnya aturan ini
           hanya ada di berkas armada, sehingga halaman lain yang memakai nama
           kelas yang sama mendapatkan namanya saja tanpa perilakunya — terukur
           di peramban: position-nya kembali static dan tombol Simpan ikut
           tergulung hilang. */
        .orcha-aksi-paku {
            position: sticky;
            bottom: 0;
            z-index: 3;
            margin: 0 -.25rem;
            padding: .75rem .25rem .25rem;
            background: linear-gradient(to bottom, rgba(246, 248, 251, 0), #f6f8fb 28%);
        }

        /* Kolom kanan setinggi barisnya, dan TIDAK lengket sendiri.

           Isinya — foto, pratinjau, ringkasan, tombol — terukur ~1.000px,
           lebih tinggi dari jendela 813px. Kolom lengket yang lebih tinggi dari
           layar menyembunyikan bagian bawahnya untuk selamanya; menambahkan
           gulungan sendiri di dalamnya memang membuatnya terjangkau, tetapi
           lewat scrollbar kedua yang tidak akan ditemukan admin — tombol Simpan
           tidak pantas bersembunyi di balik itu.

           Yang lengket cukup PALANG TOMBOLNYA (.orcha-aksi-paku, sticky bottom).
           Supaya palang itu tetap menempel sepanjang halaman, pembungkusnya
           harus setinggi kolomnya: selama pembungkus masih terlihat, palangnya
           ikut terlihat. Kolom kiri yang jauh lebih panjang tidak lagi
           meninggalkan kolom kanan tampak kosong tanpa tombol.

           padding-bottom menjaga tombol Batal tidak menempel persis pada kartu
           Kondisi Unit di bawahnya — keduanya akan terbaca seperti satu
           tumpukan. */
        @media (min-width: 1200px) {
            .orcha-lengket-panjang {
                position: static;
                height: 100%;
                padding-bottom: 2rem;
                display: flex;
                flex-direction: column;
            }

            /* Sisa ruang kolom diserap SEBELUM palang tombol, jadi posisi
               alaminya di dasar kolom.

               Tanpa ini palang berhenti tepat di bawah kartu terakhir — sekitar
               700px di atas dasar kolom — dan sticky bottom hanya menahannya
               sampai titik itu terlewat. Terukur di peramban: tombol lepas dari
               pandangan pada gulungan ke-1.400px. Dengan penyerap ini, palangnya
               tetap terlihat sampai dasar halaman. */
            .orcha-lengket-panjang .orcha-aksi-paku {
                margin-top: auto;
            }
        }

        /* Pemilih berdaftar (SweetAlert + pencarian): merek & nama unit di
           armada, dan provinsi di destinasi. Dipindahkan ke gaya bersama karena
           dipakai lebih dari satu halaman — aturan yang hanya ada di satu berkas
           membuat halaman lain mendapatkan nama kelasnya tanpa rupanya. */
    .orcha-picker {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .orcha-picker:disabled {
        cursor: not-allowed;
        opacity: .65;
    }

    .orcha-pick-list {
        max-height: 340px;
        overflow-y: auto;
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: .4rem;
        padding: .2rem;
    }

    .orcha-pick-row {
        display: flex;
        align-items: stretch;
        gap: .4rem;
    }

    .orcha-pick-row .orcha-pick-item {
        flex: 1 1 auto;
        min-width: 0;
    }
    .orcha-pick-del {
        flex: 0 0 auto;
        width: 40px;
        border: 1px solid #f3c9c9;
        background: #fff5f5;
        color: #c0392b;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        transition: all .15s ease;
    }

    .orcha-pick-del:hover {
        background: #c0392b;
        border-color: #c0392b;
        color: #fff;
    }

    .orcha-pick-del:disabled {
        opacity: .5;
        cursor: wait;
    }

    .orcha-pick-item {
        display: block;
        width: 100%;
        text-align: left;
        border: 1px solid #e6e8f2;
        background: #fff;
        border-radius: 12px;
        padding: .7rem .9rem;
        font-weight: 600;
        color: #1e293b;
        font-size: .92rem;
        transition: all .15s ease;
    }

    .orcha-pick-item:hover {
        border-color: var(--orc-primer);
        background: linear-gradient(135deg, rgba(124, 58, 237, .10), rgba(31, 45, 61, .04));
        transform: translateY(-1px);
    }

    .orcha-pick-empty {
        text-align: center;
        color: #94a3b8;
        padding: 1.5rem;
        font-size: .9rem;
    }

    /* ============ MARGIN & KEUNTUNGAN ============
       Angka untung tidak pernah tampil sebagai teks abu-abu di antara isian
       lain: ia yang paling dicari admin di halaman ini, dan tiga keadaannya —
       terhitung, rugi, belum diisi — harus bisa dibedakan sekilas tanpa
       membaca angkanya dulu. */
    .orcha-margin {
        display: flex;
        align-items: flex-start;
        gap: .55rem;
        padding: .7rem .85rem;
        border-radius: 12px;
        font-size: .85rem;
        line-height: 1.45;
        background: #eafaf1;
        color: #14683f;
        border: 1px solid rgba(20, 104, 63, .16);
    }

    .orcha-margin i {
        font-size: 1rem;
        line-height: 1.35;
    }

    .orcha-margin.rugi {
        background: #fdecec;
        color: #a12a2a;
        border-color: rgba(161, 42, 42, .18);
    }

    .orcha-margin.kosong {
        background: #f6f7fb;
        color: #64748b;
        border-color: #e6e8f2;
    }

    /* Kartu utama keuntungan.
       SENGAJA TIDAK memakai kelas .card. Layout lemon memasang
       `background: rgba(255,255,255,.8) !important` pada .card, dan !important
       itu mengalahkan kekhususan apa pun — gradiennya hilang sementara teks
       putihnya tetap putih, jadi angka terpenting di halaman ini tidak terbaca
       sama sekali. Bentuk kartunya ditulis ulang di sini (radius dan bayangan
       disamakan dengan .card) supaya tidak perlu berbalas !important. */
    .orcha-untung-hero {
        border-radius: 28px;
        padding: 1.6rem 1.75rem;
        color: #fff;
        background: linear-gradient(135deg, #0b7a4b, #12a463 55%, #14c07a);
        box-shadow: 0 14px 34px rgba(11, 122, 75, .28);
        height: 100%;
    }

    .orcha-untung-hero.rugi {
        background: linear-gradient(135deg, #8f1f1f, #c0392b 55%, #e05252);
        box-shadow: 0 14px 34px rgba(143, 31, 31, .28);
    }

    .orcha-untung-hero.kosong {
        background: linear-gradient(135deg, #334155, #475569 60%, #64748b);
        box-shadow: 0 14px 34px rgba(51, 65, 85, .24);
    }

    .orcha-hero-ikon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        line-height: 1;
        color: #fff;
        background: rgba(255, 255, 255, .18);
        border: 1px solid rgba(255, 255, 255, .28);
        flex: 0 0 auto;
    }

    .orcha-hero-label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .09em;
        text-transform: uppercase;
        opacity: .88;
    }

    .orcha-hero-angka {
        font-size: 2.15rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -.01em;
        word-break: break-word;
    }

    .orcha-hero-sub {
        font-size: .82rem;
        opacity: .9;
    }

    /* Batang susun: omzet = modal + untung, dalam satu garis.
       Dua angka rupiah berjajar tidak memberi tahu perbandingannya; panjang
       potongan memberi tahu — dan di sinilah margin tipis terlihat setipis
       kenyataannya. */
    .orcha-pecah {
        display: flex;
        height: 10px;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(255, 255, 255, .22);
    }

    .orcha-pecah > span {
        display: block;
        height: 100%;
    }

    .orcha-pecah .bagian-modal {
        background: rgba(255, 255, 255, .45);
    }

    .orcha-pecah .bagian-untung {
        background: #ffd772;
    }

    /* Batang susun di atas kartu putih. Bentuknya sama dengan yang di kartu
       utama, tapi warnanya tidak bisa ikut: di sana potongannya putih tembus
       pandang di atas gradien hijau, di sini latarnya putih — putih tembus
       pandang berarti tidak terlihat apa pun. */
    .orcha-pecah-terang {
        height: 8px;
        background: #eef1f7;
    }

    .orcha-pecah-terang .bagian-modal {
        background: #cbd5e1;
    }

    .orcha-pecah-terang .bagian-untung {
        background: linear-gradient(90deg, #0b7a4b, #14c07a);
    }

    .orcha-pecah-terang .bagian-rugi {
        background: linear-gradient(90deg, #a12a2a, #e05252);
    }

    /* Daftar yang digulung ke dalam kartunya sendiri.

       Batang gulirnya dibuat tipis dan sewarna latar supaya tidak menarik
       perhatian lebih daripada angka di sebelahnya, tapi tetap terlihat —
       daftar yang bisa digulir tanpa tanda apa pun sama saja dengan daftar yang
       terpotong diam-diam. */
    .orcha-gulung-tegak {
        max-height: 19rem;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding-right: .35rem;
    }

    .orcha-gulung-tegak::-webkit-scrollbar {
        width: 6px;
    }

    .orcha-gulung-tegak::-webkit-scrollbar-track {
        background: transparent;
    }

    .orcha-gulung-tegak::-webkit-scrollbar-thumb {
        background: #dbe2ec;
        border-radius: 999px;
    }

    .orcha-gulung-tegak::-webkit-scrollbar-thumb:hover {
        background: #c3cddc;
    }

    .orcha-legenda {
        display: flex;
        flex-wrap: wrap;
        gap: .25rem 1.1rem;
        font-size: .76rem;
        opacity: .95;
    }

    .orcha-legenda span.titik {
        width: 9px;
        height: 9px;
        border-radius: 3px;
        display: inline-block;
    }

    .orcha-legenda .titik-modal {
        background: rgba(255, 255, 255, .5);
    }

    .orcha-legenda .titik-untung {
        background: #ffd772;
    }

    /* Kartu angka pendamping: ikon bergradien seperti kartu dashboard, supaya
       halaman ini terlihat sekeluarga dengan halaman lemon lainnya. */
    .orcha-ikon-omzet {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        box-shadow: 0 8px 15px rgba(59, 130, 246, .25);
    }

    .orcha-ikon-modal {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 8px 15px rgba(245, 158, 11, .25);
    }

    .orcha-ikon-potensi {
        background: linear-gradient(135deg, #7c3aed, #4f46e5);
        box-shadow: 0 8px 15px rgba(124, 58, 237, .25);
    }

    /* Nominal rupiah tidak boleh patah di tengah.
       "Rp 17.160.000" yang jatuh jadi dua baris — "Rp" sendirian di atas —
       terbaca sekilas seperti angka yang lain sama sekali. Lebih baik hurufnya
       menyusut sedikit pada layar sempit daripada angkanya terpotong. */
    .orcha-untung-kartu .nilai {
        font-size: clamp(1.05rem, 1.5vw, 1.3rem);
        font-weight: 800;
        color: var(--orc-tinta);
        line-height: 1.25;
        white-space: nowrap;
    }

    .orcha-untung-kartu .keterangan {
        font-size: .76rem;
    }

    /* Batang perbandingan di kartu ringkas (per jenis layanan, per bulan).

       Tabel "Keuntungan per paket" sempat memakainya juga, lalu dilepas: satu
       baris di sana sudah berisi tujuh angka, dan batang di kolom terakhir hanya
       menambah satu hal untuk dibaca demi keterangan yang sudah disebutkan angka
       persennya. Di kartu ringkas batangnya tetap berguna — barisnya pendek dan
       memang untuk dibandingkan sekilas.

       Varian .lebar ikut dibuang bersama pemakaian di tabel itu: satu-satunya
       alasan batang ini pernah dipatok sempit adalah supaya berjajar rapi di
       tepi kanan kolom tabel. */
    .orcha-batang {
        height: 6px;
        border-radius: 999px;
        background: #eef1f7;
        overflow: hidden;
        width: 100%;
    }

    .orcha-batang > span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #0b7a4b, #14c07a);
    }

    .orcha-batang.rugi > span {
        background: linear-gradient(90deg, #8f1f1f, #e05252);
    }

    .orcha-untung-nilai {
        font-weight: 700;
        color: #0b7a4b;
    }

    .orcha-untung-nilai.rugi {
        color: #a12a2a;
    }

    .orcha-untung-nilai.kosong {
        color: #94a3b8;
        font-weight: 600;
    }

    /* Chip margin per orang: angka yang paling sering dibandingkan antar paket,
       jadi ia diberi bentuk sendiri alih-alih ikut jadi teks tabel biasa. */
    .orcha-chip-margin {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        background: #eafaf1;
        color: #0b7a4b;
        white-space: nowrap;
    }

    .orcha-chip-margin.rugi {
        background: #fdecec;
        color: #a12a2a;
    }

    .orcha-chip-margin.kosong {
        background: #f1f5f9;
        color: #94a3b8;
        font-weight: 600;
    }

    /* Nomor urut paket: menegaskan bahwa tabelnya BERURUT dari yang paling
       menghasilkan — urutan yang mudah terlewat kalau hanya tersirat. */
    .orcha-urutan {
        width: 26px;
        height: 26px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .76rem;
        font-weight: 800;
        color: var(--orc-tinta);
        background: #eef4f9;
        flex: 0 0 auto;
    }

    .orcha-urutan.puncak {
        color: #fff;
        background: linear-gradient(135deg, #0b7a4b, #14c07a);
    }

    /* Baris rekap di kartu samping (per jenis layanan, per bulan). */
    .orcha-rekap-baris {
        padding: .7rem 0;
        border-bottom: 1px dashed #e6e8f2;
    }

    .orcha-rekap-baris:last-child {
        border-bottom: 0;
    }

    .orcha-rekap-porsi {
        font-size: .72rem;
        font-weight: 700;
        color: #64748b;
        white-space: nowrap;
    }

    /* Penanda naik/turun antar bulan. Hijau-merahnya sengaja disamakan dengan
       warna untung-rugi di halaman ini, supaya admin tidak perlu menghafal dua
       kamus warna dalam satu layar. */
    .orcha-ubah {
        display: inline-flex;
        align-items: center;
        gap: .15rem;
        padding: .1rem .45rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
        background: #eafaf1;
        color: #0b7a4b;
        white-space: nowrap;
    }

    .orcha-ubah.turun {
        background: #fdecec;
        color: #a12a2a;
    }

    .orcha-ubah i {
        font-size: .7rem;
        line-height: 1;
    }

    /* ============ PENOMORAN HALAMAN ============
       Bentuk tombolnya TIDAK ditulis ulang di sini. Layout lemon sudah memberi
       gaya lengkap untuk .page-link — sudut membulat, bingkai, angkat saat
       disentuh, dan gradien pada halaman aktif — semuanya dengan !important.
       Menulis ulang di sini hanya menghasilkan aturan yang kalah dan menyesatkan
       siapa pun yang membacanya nanti.

       Yang ditambahkan cuma dua hal yang memang belum ada. */

    /* Keterangan jumlah baris di sebelah kiri penomoran. */
    .orcha-halaman-info {
        font-size: .82rem;
        color: #64748b;
    }

    .orcha-halaman-info strong {
        color: var(--orc-tinta);
        font-weight: 700;
    }

    /* Titik-titik pemenggal bukan tombol, jadi bingkai dan latarnya dilepas —
       kalau tidak, ia tampak bisa ditekan padahal tidak. !important-nya
       terpaksa: gaya .page-item.disabled milik layout juga memakainya. */
    .orcha-halaman .page-item.disabled > span.page-link {
        background: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
    }

    /* Ikon panah di tombol sebelumnya/berikutnya: glif sendirian di dalam
       kotak, gejalanya sama dengan kotak ikon di atas. */
    .orcha-halaman .page-link > i {
        display: block;
        line-height: 1;
    }

    .orcha-halaman .page-link > i::before {
        display: block;
    }

    /* ============ DAFTAR PENDAFTARAN OPEN TRIP ============ */

    /* Cip keterangan di dalam sel: jumlah peserta, titik jemput, hitungan
       mundur. Bentuknya sengaja kecil dan tenang — yang perlu menonjol di baris
       ini cuma nama pemesan dan statusnya. */
    .orcha-cip-peserta,
    .orcha-cip-jemput,
    .orcha-cip-hari,
    .orcha-cip-sehat {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .15rem .5rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1.5;
    }

    .orcha-cip-peserta > i,
    .orcha-cip-jemput > i,
    .orcha-cip-sehat > i {
        font-size: .72rem;
        line-height: 1;
    }

    .orcha-cip-peserta {
        background: #eef4f9;
        color: #14618f;
    }

    .orcha-cip-awas {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .15rem .5rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1.5;
        background: #fff4e0;
        color: #8a5a09;
        cursor: help;
    }

    .orcha-cip-awas > i {
        font-size: .72rem;
        line-height: 1;
    }

    .orcha-cip-jemput {
        background: #f6f7fb;
        color: #475569;
        cursor: help;
        max-width: 13rem;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Hitungan mundur ke keberangkatan. Warnanya berubah pada H-5 — batas
       pelunasan menurut config Orcha — karena sejak titik itu pertanyaannya
       bukan lagi "kapan" melainkan "sudah lunas belum". */
    .orcha-cip-hari {
        background: #eef4f9;
        color: #14618f;
    }

    .orcha-cip-hari.dekat {
        background: #fff4e0;
        color: #8a5a09;
    }

    .orcha-cip-hari.lewat {
        background: #f1f5f9;
        color: #94a3b8;
    }

    /* Kelengkapan riwayat kesehatan: angka dan batang kecil di bawahnya.
       Angkanya menjawab "berapa", batangnya menjawab "kurang berapa" tanpa
       perlu mengurangi sendiri. */
    .orcha-cip-sehat {
        background: #fff4e0;
        color: #8a5a09;
        cursor: help;
    }

    .orcha-cip-sehat.lengkap {
        background: #eafaf1;
        color: #0b7a4b;
        cursor: default;
    }

    .orcha-cip-sehat.kosong {
        background: #fdecec;
        color: #a12a2a;
    }

    .orcha-sehat-batang {
        height: 4px;
        width: 4.2rem;
        margin-inline: auto;
        border-radius: 999px;
        background: #eef1f7;
        overflow: hidden;
    }

    .orcha-sehat-batang > span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #f59e0b, #ffc74e);
    }

    .orcha-sehat-batang.lengkap > span {
        background: linear-gradient(90deg, #0b7a4b, #14c07a);
    }

    /* Pilihan status berwarna.

       Tetap sebuah <select> — status memang perlu bisa diubah dari daftar, dan
       menggantinya dengan menu buatan sendiri berarti bergantung pada JavaScript
       yang belum tentu ada di halaman ini. Yang ditambahkan cuma warnanya,
       supaya sebaris pesanan bisa dinilai sebelum tulisannya dibaca. */
    .orcha-pilih-status {
        border: 1px solid transparent !important;
    }

    .orcha-pilih-status.status-baru {
        background-color: #fff4e0 !important;
        border-color: #f3ddb0 !important;
        color: #8a5a09 !important;
    }

    .orcha-pilih-status.status-dihubungi {
        background-color: #eef6fb !important;
        border-color: #c7e2f2 !important;
        color: #14618f !important;
    }

    .orcha-pilih-status.status-dp_masuk {
        background-color: #eef2ff !important;
        border-color: #c7d2fe !important;
        color: #3730a3 !important;
    }

    .orcha-pilih-status.status-lunas {
        background-color: #eafaf1 !important;
        border-color: #b7e4cd !important;
        color: #0b7a4b !important;
    }

    .orcha-pilih-status.status-batal {
        background-color: #fdecec !important;
        border-color: #f6c9cd !important;
        color: #a12a2a !important;
    }

    /* Tombol aksi bertulisan. Ikon sendirian menghemat tempat tapi memaksa
       admin baru menebak — dan tebakan yang salah di kolom aksi berarti
       membuka halaman yang bukan tujuannya. */
    .orcha-aksi-berlabel {
        gap: .35rem;
        font-size: .76rem;
        font-weight: 600;
    }

    /* Riwayat kesehatan — hijau kebiruan. Sebelumnya merah menyala, padahal
       merah di halaman ini berarti hapus atau rugi. */
    .orcha-aksi-sehat {
        background: #eafaf1;
        border-color: #b7e4cd;
        color: #0b7a4b;
    }

    .orcha-aksi-sehat:hover {
        background: #d7f3e5;
        border-color: #0b7a4b;
        color: #085434;
    }

    /* ============ DETAIL PENDAFTARAN ============ */

    /* Kalimat tenggat pelunasan di bawah palang kemajuan. Diberi latar, bukan
       dibiarkan jadi teks abu lain di antara teks abu: inilah satu-satunya
       kalimat di kartu itu yang menyebut pekerjaan yang belum selesai. */
    .orcha-tenggat {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .7rem;
        border-radius: 10px;
        background: #fff4e0;
        color: #8a5a09;
        font-weight: 600;
    }

    .orcha-tenggat.lunas {
        background: #eafaf1;
        color: #0b7a4b;
    }

    .orcha-tenggat.lewat {
        background: #fdecec;
        color: #a12a2a;
    }

    .orcha-tenggat > i {
        line-height: 1;
    }

    /* Satu blok per titik jemput. */
    .orcha-jemput-blok {
        padding: .5rem .7rem;
        border-radius: 10px;
        background: #f6f9fc;
        border: 1px solid #e6eef5;
        margin-bottom: .5rem;
    }

    .orcha-jemput-blok:last-child {
        margin-bottom: 0;
    }

    .orcha-jemput-judul {
        display: flex;
        align-items: center;
        gap: .35rem;
        font-size: .82rem;
        font-weight: 700;
        color: var(--orc-tinta);
    }

    .orcha-jemput-judul > i {
        line-height: 1;
        color: var(--orc-primer);
    }

    .orcha-jemput-jumlah {
        margin-left: auto;
        font-size: .7rem;
        font-weight: 700;
        color: #64748b;
        background: #fff;
        border: 1px solid #e6eef5;
        border-radius: 999px;
        padding: .05rem .45rem;
    }

    .orcha-jemput-nama {
        font-size: .78rem;
        color: #64748b;
        margin-top: .15rem;
    }

    /* Bukti yang memang tidak ada — dibedakan dari gambar yang gagal dimuat. */
    .orcha-bukti-kosong {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .15rem;
        background: #f6f7fb;
        border: 1px dashed #d9e0ea;
        color: #94a3b8;
        font-size: .64rem;
        text-align: center;
        line-height: 1.2;
    }

    .orcha-bukti-kosong > i {
        font-size: 1rem;
        line-height: 1;
    }

    /* Keadaan "nama peserta belum didata" di kartu peserta. Diberi warna
       kuning, bukan abu: ini bukan sekadar data kosong, melainkan pekerjaan
       yang menghalangi rombongannya masuk manifes. */
    .orcha-kosong-peserta {
        display: flex;
        gap: .6rem;
        align-items: flex-start;
        padding: .8rem .9rem;
        border-radius: 12px;
        background: #fffaf0;
        border: 1px solid #f3ddb0;
        color: #8a6410;
        font-size: .85rem;
        line-height: 1.5;
    }

    .orcha-kosong-peserta > i {
        font-size: 1.05rem;
        line-height: 1.4;
    }

    /* Tombol pengosong di dalam kotak cari. Ditaruh DI DALAM isiannya, bukan
       di sebelahnya: yang dikosongkan adalah isian itu, dan tombol yang berdiri
       terpisah menyisakan pertanyaan mengosongkan apa. */
    .orcha-cari-bersih {
        position: absolute;
        right: .6rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 4;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.6rem;
        height: 1.6rem;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: #eef1f7;
        color: #64748b;
        transition: all .15s ease;
    }

    .orcha-cari-bersih:hover {
        background: #dde4ee;
        color: var(--orc-tinta);
    }

    .orcha-cari-bersih > i {
        display: block;
        line-height: 1;
        font-size: .68rem;
    }

    /* Isian yang membawa tombol pengosong perlu ruang di kanan supaya teks
       panjang tidak menyelip di bawah tombolnya. */
    .form-group .form-control.pe-5 {
        padding-right: 2.6rem !important;
    }

    /* Kartu tindakan pada halaman detail.

       Diberi latar sedikit berbeda supaya terbaca sebagai bilah perkakas, bukan
       kartu isi ketiga yang menuntut dibaca. Yang di dalamnya bukan keterangan
       melainkan hal-hal yang dikerjakan. */
    .orcha-kartu-tindakan {
        background: linear-gradient(180deg, #fbfdff, #f4f8fb) !important;
        border: 1px solid #e6eef5 !important;
    }

    /* Kartu ringkas di halaman riwayat kesehatan: warnanya mengikuti tingkat
       perhatian, bukan seragam biru — angka yang paling menuntut tindakan
       harus terbaca lebih dulu daripada yang aman. */
    .orcha-ikon-awas {
        background: linear-gradient(135deg, #b91c1c, #ef4444);
        box-shadow: 0 8px 15px rgba(185, 28, 28, .25);
    }

    .orcha-ikon-catat {
        background: linear-gradient(135deg, #b45309, #f59e0b);
        box-shadow: 0 8px 15px rgba(180, 83, 9, .25);
    }

    .orcha-ikon-aman {
        background: linear-gradient(135deg, #0b7a4b, #14c07a);
        box-shadow: 0 8px 15px rgba(11, 122, 75, .25);
    }

    /* Daftar nama yang menuntut kesiapan sebelum berangkat. */
    /* Kartu "pengganti belum mengisi": kuning, bukan merah.

       Merah sudah dipakai kartu di bawahnya untuk kondisi kesehatan yang
       menuntut kesiapan — hal yang bisa membahayakan orang. Yang ini soal data
       yang belum lengkap: mendesak, tetapi bukan bahaya. Dua warna berbeda
       supaya yang benar-benar merah tidak kehilangan artinya. */
    /* Kartu ringkasan bentuk ringkas: ikon di samping angka, bukan di atasnya.

       Bentuk bertumpuk milik halaman Keuntungan pas di sana — kartunya sedikit
       dan angkanya besar-besar. Di halaman kesehatan ia jadi empat kotak
       setinggi 176px yang menghabiskan layar pertama demi empat angka,
       sementara yang benar-benar dicari admin — siapa perlu disiapkan, siapa
       belum mengisi — terdorong ke bawah lipatan. */
    .orcha-kartu-ringkas {
        /* Layout memberi margin-bottom 2,2rem pada SETIAP .card — masuk akal
           untuk kartu yang bertumpuk sendiri-sendiri, mubazir untuk kartu di
           dalam baris ber-gutter yang sudah mengatur jaraknya. Ikut menambah
           tinggi barisnya, dan sisanya muncul sebagai ruang kosong di dalam
           kartu karena tingginya diratakan. */
        margin-bottom: 0 !important;
    }

    .orcha-kartu-ringkas .card-body {
        padding: .85rem 1rem !important;
    }

    .orcha-kartu-ringkas .orcha-ikon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border-radius: 10px;
        font-size: .95rem;
    }

    .orcha-kartu-ringkas .nilai {
        font-size: 1.15rem;
        line-height: 1.15;
    }

    .orcha-kartu-ringkas .keterangan {
        font-size: .72rem;
        line-height: 1.4;
        margin-top: .45rem;
    }

    /* ============ PILIHAN PESAN WHATSAPP ============ */

    .orcha-lembar {
        position: fixed;
        inset: 0;
        z-index: 1080;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .orcha-lembar[hidden] {
        display: none;
    }

    .orcha-lembar-tirai {
        position: absolute;
        inset: 0;
        background: rgba(31, 45, 61, .35);
        backdrop-filter: blur(2px);
    }

    .orcha-lembar-isi {
        position: relative;
        width: 100%;
        max-width: 34rem;
        max-height: 85vh;
        overflow-y: auto;
        padding: 1.15rem 1.25rem;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 24px 60px rgba(31, 45, 61, .22);
    }

    /* Satu pilihan pesan: satu baris yang bisa diketuk seluruhnya.

       Bukan tombol kecil di ujung kanan — yang diketuk admin sambil terburu
       adalah judulnya, dan sasaran seukuran teks meleset lebih sering daripada
       yang diakui siapa pun. */
    .orcha-pilihan-wa {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .7rem .85rem;
        margin-bottom: .5rem;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        background: #fff;
        text-decoration: none;
        transition: all .15s ease;
    }

    .orcha-pilihan-wa:hover {
        border-color: #b7e4cd;
        background: #f6fdf9;
        transform: translateX(3px);
    }

    /* Percakapan kosong dipisahkan garis dan diredupkan: ia jalan keluar
       terakhir, bukan pilihan setara dengan pesan yang sudah terisi. */
    .orcha-pilihan-wa-polos {
        margin-top: .85rem;
        margin-bottom: 0;
        border-style: dashed;
        background: #fbfcfe;
    }

    .orcha-ikon-netral {
        background: #eef1f7;
        color: #64748b;
    }

    /* Ikon di dalam pilihan lebih kecil daripada ikon kartu ringkasan: barisnya
       memang lebih pendek, dan ikon 48px membuat teksnya tampak menempel. */
    .orcha-pilihan-wa .orcha-ikon {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        border-radius: 11px;
        font-size: 1rem;
    }

    .orcha-kartu-tunggu {
        background: #fffaf0 !important;
        border: 1px solid #f3ddb0 !important;
    }

    /* Kartu arsip: abu sepenuhnya, tanpa satu pun warna yang menuntut tindakan.

       Isinya orang yang tidak berangkat. Apa pun yang tampak mendesak di sini
       adalah perhatian yang diambil dari peserta yang benar-benar ikut. */
    .orcha-kartu-arsip {
        background: #fafbfc !important;
        border: 1px solid #e6e8f2 !important;
    }

    .orcha-lencana-arsip {
        display: inline-block;
        padding: .1rem .55rem;
        border-radius: 999px;
        background: #eef1f7;
        color: #64748b;
        font-size: .76rem;
        font-weight: 700;
    }

    /* Kartu di dalam arsip diredupkan, dan baru penuh saat disentuh.

       Isinya tetap lengkap — sewaktu-waktu masih ditanyakan, misalnya saat
       pemesan menyanggah penggantiannya — tetapi tidak boleh sama menonjolnya
       dengan peserta yang berangkat. Pita merah "perlu perhatian" di kartu
       arsip pernah membuat admin menyiapkan obat untuk orang yang tidak
       datang. */
    .orcha-arsip-isi {
        opacity: .62;
        filter: saturate(.55);
        transition: opacity .2s ease, filter .2s ease;
    }

    .orcha-arsip-isi:hover {
        opacity: 1;
        filter: none;
    }

    .orcha-kartu-siaga {
        background: #fff5f5 !important;
        border: 1px solid #f6c9cd !important;
    }

    .orcha-siaga-baris {
        padding: .45rem 0;
        border-bottom: 1px dashed #f6c9cd;
        color: #7f1d1d;
    }

    .orcha-siaga-baris:last-child {
        border-bottom: 0;
    }

    /* ============ HALAMAN DAFTAR PESERTA ============ */

    /* Kotak tempelan.

       Layout lemon memaksa `height: 48px !important` pada SETIAP .form-control —
       aturan yang masuk akal untuk isian sebaris, tetapi memangkas textarea jadi
       setinggi satu baris sehingga `rows` diabaikan dan tempelannya tidak
       terlihat sama sekali. !important-nya terpaksa dibalas !important. */
    .orcha-tempel {
        height: auto !important;
        min-height: 9rem;
        padding: .7rem .9rem !important;
        line-height: 1.6;
        font-size: .88rem;
    }

    /* Baris peserta: nomor, penanda, nama, titik jemput, tombol hapus.

       Diberi garis pemisah tipis, bukan hanya sorotan saat kursor lewat: pada
       daftar dua puluh baris, mata butuh patokan tetap untuk tidak melompat
       baris — dan sorotan cuma ada di baris yang sedang disentuh. */
    .orcha-baris-peserta {
        padding: .5rem .6rem;
        border-radius: 12px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s ease;
    }

    .orcha-baris-peserta:last-of-type {
        border-bottom: 0;
    }

    /* Nama lama dan isian penggantinya duduk dalam SATU kotak.

       Bingkainya milik pembungkus, dan isian di dalamnya dilucuti bingkainya
       sendiri — supaya yang terbaca satu medan utuh: "suparjiman → hafid",
       bukan dua benda yang kebetulan bertetangga. */
    /* Ukurannya menyamai kotak isian biasa, bukan ukurannya sendiri.

       Kotak ini menggantikan satu .form-control di dalam baris, dan layout
       memaksa semua .form-control setinggi 48px dengan bingkai 2px dan sudut
       12px. Waktu kotak ini memakai ukurannya sendiri (34px, bingkai 1px),
       baris penggantian tampak menciut di sebelah baris biasa — seolah
       isiannya rusak, padahal cuma beda ukuran. */
    .orcha-ganti-gabung {
        display: flex;
        align-items: center;
        gap: .45rem;
        padding: 0 .9rem;
        border: 2px solid #b7e4cd;
        border-radius: 12px;
        background: #fbfffd;
        height: 48px;
    }

    /* Ikut menyala saat isiannya disorot, supaya kotaknya terbaca sebagai satu
       isian utuh — bukan hiasan yang kebetulan memuat kolom. */
    .orcha-ganti-gabung:focus-within {
        border-color: #14a06a;
        box-shadow: 0 0 0 4px rgba(20, 160, 106, .1);
    }

    .orcha-ganti-gabung > i {
        color: #14a06a;
        font-size: .78rem;
        line-height: 1;
        flex: 0 0 auto;
    }

    /* Tinggi 48px milik layout dan bingkainya sendiri dilucuti: keduanya
       ditetapkan dengan !important, jadi harus dibalas !important. */
    .orcha-ganti-gabung .form-control,
    .orcha-ganti-gabung .form-select {
        height: auto !important;
        min-height: 0;
        border: 0 !important;
        box-shadow: none !important;
        padding: 0 !important;
        font-size: .95rem;
        font-weight: 700;
        color: #0b7a4b !important;
    }

    /* Warna latarnya saja yang dihapus, bukan seluruh background.

       .form-select menaruh panah dropdownnya di background-image; menulis
       `background: transparent` menghapus panah itu sekalian, dan kotak
       pilihan tanpa panah tidak lagi terbaca sebagai kotak pilihan. */
    .orcha-ganti-gabung .form-control {
        background: transparent !important;
    }

    .orcha-ganti-gabung .form-select {
        background-color: transparent !important;
        /* Ruang untuk panahnya sendiri; padding di atas menyapu bersih. */
        padding-right: 1.5rem !important;
    }

    /* Teks bayangannya tetap abu: ia belum nama siapa pun. */
    .orcha-ganti-gabung .form-control::placeholder {
        color: #94a3b8 !important;
        font-weight: 400;
    }

    /* Titik jemput yang belum dipilih bukan nama pengganti: abu, bukan hijau
       tebal, supaya tidak tampak sudah terisi padahal belum. */
    .orcha-ganti-gabung .form-select:invalid,
    .orcha-ganti-gabung .form-select option[value=""] {
        color: #94a3b8 !important;
        font-weight: 400;
    }

    .orcha-ganti-gabung .orcha-ganti-lama {
        /* Tidak ikut menyusut. Sebagai anak flex ia rela mengecil demi isian di
           sebelahnya, dan "suparjiman" berubah jadi "suparji…" padahal kotaknya
           masih lapang — nama yang dicoret jadi tidak terbaca, padahal justru
           itu yang harus jelas. Batas 55% tetap ada untuk nama yang benar-benar
           panjang. */
        flex: 0 0 auto;
        font-size: .82rem;
        padding: .12rem .55rem;
        white-space: nowrap;
        max-width: 55%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Penanda riwayat kesehatan: satu ikon, bukan lencana bertulisan yang
       menuntut kolom lebar dan meninggalkan lubang di baris tanpa riwayat. */
    .orcha-tanda-sehat {
        color: #14a06a;
        font-size: .9rem;
        line-height: 1;
        cursor: help;
    }

    .orcha-baris-peserta:hover {
        background: #f8fbfd;
    }

    .orcha-nomor-peserta {
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .76rem;
        font-weight: 700;
        color: #64748b;
        background: #eef1f7;
        flex: 0 0 auto;
    }

    /* Tombol baris: masing-masing berwarna sejak awal, tidak menunggu disentuh.

       Sempat dibuat abu semua supaya daftarnya tenang, tetapi tenang di sini
       berarti tidak terlihat: tombol ganti dan tombol batal luput sama sekali,
       dan yang paling sering dicari justru harus ditebak letaknya. Tiga
       tindakan, tiga warna — biru mengganti, kuning membatalkan, merah
       menghapus. */
    .orcha-hapus-baris {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #f6c9cd;
        border-radius: 9px;
        background: #fdecee;
        color: #9b2530;
        transition: all .15s ease;
    }

    .orcha-hapus-baris:hover {
        background: #fbd9dd;
        border-color: #c2323c;
        color: #7f1d28;
    }

    .orcha-hapus-baris > i {
        display: block;
        line-height: 1;
        font-size: .78rem;
    }

    /* Mengganti orang: biru laut. Bukan merah — yang terjadi bukan penghapusan,
       melainkan satu nama berpindah ke nama lain. */
    .orcha-tombol-ganti {
        background: #eef6fb;
        border-color: #c7e2f2;
        color: #14618f;
    }

    .orcha-tombol-ganti:hover {
        background: #d8ecf8;
        border-color: var(--orc-primer);
        color: var(--orc-tinta);
    }

    /* Membatalkan penggantian: kuning, warna yang sama dengan "sedang menunggu"
       di halaman lain — keadaannya memang belum selesai. */
    .orcha-tombol-batal {
        background: #fff4e0;
        border-color: #f3ddb0;
        color: #8a5a09;
    }

    .orcha-tombol-batal:hover {
        background: #ffedcb;
        border-color: #d9a441;
        color: #6b4406;
    }

    /* Penanda peserta yang riwayat kesehatannya sudah masuk. */
    .orcha-cip-sudah {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .1rem .45rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        background: #eafaf1;
        color: #0b7a4b;
        white-space: nowrap;
        cursor: help;
    }

    .orcha-cip-sudah > i {
        font-size: .66rem;
        line-height: 1;
    }

    /* Baris tombol yang menempel di dasar kartu saat daftarnya panjang. */
    .orcha-aksi-lengket {
        position: sticky;
        bottom: 0;
        /* Latar penuh dengan garis pemisah, bukan gradasi.

           Gradasi membuat baris di belakangnya menembus separuh — terbaca
           sebagai noda, bukan sebagai bilah. Yang menempel di atas isi halaman
           harus jelas batasnya, supaya mata tahu mana bilah dan mana daftar. */
        background: #fff;
        border-top: 1px solid #eef1f7;
        padding: .85rem 0 .15rem;
        margin-top: .75rem;
    }

    /* Baris riwayat perubahan nama peserta. */
    /* Riwayat penggantian: garis waktu bernomor, bukan daftar bergaris putus.

       Bentuk sebelumnya tiga baris mengambang tanpa jangkar kiri: mata tidak
       punya tempat memulai, tinggi tiap baris berbeda-beda mengikuti ada
       tidaknya titik jemput, dan garis putus-putus antarbaris membaginya tanpa
       menyusunnya. Padahal isinya berurutan waktu — yang terbaru di atas — dan
       itulah yang seharusnya terbaca lebih dulu. */
    .orcha-ganti-runtun {
        position: relative;
        padding-left: 2.1rem;
    }

    /* Batang penghubung antarperistiwa, ditarik di belakang bulatan nomor.

       Ujung bawahnya diukur dari bulatan TERAKHIR, bukan dari dasar kartu:
       baris paling bawah tingginya berubah-ubah mengikuti ada tidaknya titik
       jemput, dan batang yang berhenti di dasar kartu menjuntai melewati
       bulatan terakhir seperti daftar yang terpotong. Diselesaikan dengan
       menutupi sisanya memakai kotak sewarna latar. */
    .orcha-ganti-runtun::before {
        content: '';
        position: absolute;
        /* Diukur, bukan dikira-kira: titik tengah bulatan nomor jatuh 17,88px
           dari tepi kiri runtun, jadi batang selebar 2px harus mulai 1px
           sebelumnya. Nilai .82rem yang lama membuat batangnya 3,76px di kiri
           bulatan — cukup untuk terlihat meleset, tidak cukup untuk terlihat
           disengaja. */
        left: 1.055rem;
        top: .95rem;
        bottom: 0;
        width: 2px;
        background: linear-gradient(#dbe7f0, #eef2f7);
    }

    /* Penutup ekor batang: setinggi bagian baris terakhir yang berada DI BAWAH
       bulatannya, diukur dari dasar kartu ke atas. */
    .orcha-ganti-baris:last-child::before {
        content: '';
        position: absolute;
        /* Ikut bergeser bersama batangnya, dan selebar 3px supaya menutup
           batang 2px itu sepenuhnya walau pembulatan piksel meleset setengah. */
        left: -1.076rem;
        top: 1.5rem;
        bottom: -.6rem;
        width: 3px;
        background: #fff;
    }

    /* Panah antarcip: ditengahkan terhadap TINTA teks, bukan terhadap kotaknya.

       Kotak panahnya sendiri sudah pas — terukur 16px dengan celah 8px di kiri
       dan kanan, dan titik tengah kotaknya berimpit dengan titik tengah kedua
       cip. Yang meleset glifnya di dalam kotak itu: bootstrap-icons menggambar
       glif pada garis dasar dengan vertical-align bawaannya sendiri, sehingga
       tintanya duduk 5,62px di bawah tinta teks di cip sebelahnya — cukup untuk
       terbaca sebagai panah yang melorot.

       Ditangani seperti ikon lain di berkas ini: <i>-nya dijadikan wadah flex
       yang menengahkan glifnya, dan vertical-align bawaannya dilucuti. */
    .orcha-ganti-baris i.bi,
    .orcha-ganti-titik i.bi {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        vertical-align: 0;
    }

    .orcha-ganti-baris {
        position: relative;
        padding: .75rem .9rem;
        margin-bottom: .55rem;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        background: #fff;
        transition: all .15s ease;
    }

    .orcha-ganti-baris:last-child {
        margin-bottom: 0;
    }

    .orcha-ganti-baris:hover {
        border-color: #cfe4f2;
        background: #fbfdff;
    }

    /* Bulatan bernomor, duduk di atas batang penghubungnya. Nomornya urutan
       kejadian dihitung dari yang terlama, jadi "1" selalu penggantian
       pertama — admin bisa menyebutnya dalam percakapan tanpa mengarang
       sebutan sendiri. */
    .orcha-ganti-nomor {
        position: absolute;
        left: -1.72rem;
        top: .85rem;
        width: 1.35rem;
        height: 1.35rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .68rem;
        font-weight: 700;
        color: #14618f;
        background: #eef6fb;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #cfe4f2;
    }

    /* Baris teratas = penggantian terbaru. Diberi warna penuh supaya yang
       paling mungkin dicari tidak perlu dihitung sendiri dari tanggalnya. */
    .orcha-ganti-baris:first-child .orcha-ganti-nomor {
        color: #fff;
        background: var(--orc-primer);
        box-shadow: 0 0 0 1px var(--orc-primer);
    }

    /* ============ SEWA KENDARAAN ============ */

    /* Pemilih status yang ringkas.

       Layout memaksa tinggi 48px pada SETIAP .form-select — masuk akal untuk
       isian formulir, terlalu besar untuk satu sel tabel yang berdampingan
       dengan teks sebaris. Satu baris tabel jadi setinggi dua, dan daftar
       sepuluh sewa memakan layar yang seharusnya cukup untuk dua puluh. */
    /* Ukuran pemilih status dipakai BERSAMA daftar sewa kendaraan dan daftar
       pendaftaran open trip.

       Sebelumnya yang di pendaftaran berbentuk pil 999px setinggi bawaan
       <select>, sementara yang di sewa kendaraan kotak membulat 34px — dua
       layar yang dikerjakan berurutan dengan kendali yang sama gunanya, tetapi
       terlihat dari dua aplikasi berbeda.

       WARNANYA tetap terpisah: status pendaftaran (baru, dihubungi, dp masuk,
       lunas, batal) dan status penyewaan (baru, dikonfirmasi, dp masuk,
       berjalan, selesai, batal) bukan daftar yang sama, dan menyatukan
       warnanya berarti satu status kehilangan warnanya diam-diam begitu daftar
       yang lain berubah. */
    .orcha-status-ringkas,
    .orcha-pilih-status {
        height: 34px !important;
        min-height: 34px;
        padding: 0 1.9rem 0 .6rem !important;
        border-width: 1px !important;
        border-radius: 9px !important;
        font-size: .78rem;
        font-weight: 600;
        background-position: right .5rem center;
        background-size: 12px 9px;
        width: auto;
        min-width: 8.5rem;
    }

    /* Warnanya mengikuti status, bukan abu seragam: dalam daftar sepuluh baris,
       yang dicari admin adalah yang belum ditangani — dan itu harus terbaca
       tanpa membaca tulisannya satu per satu. */
    .orcha-status-ringkas[data-status="baru"] {
        border-color: #c7e2f2 !important;
        background-color: #eef6fb;
        color: #14618f;
    }

    .orcha-status-ringkas[data-status="berjalan"],
    .orcha-status-ringkas[data-status="diproses"] {
        border-color: #f3ddb0 !important;
        background-color: #fff4e0;
        color: #8a5a09;
    }

    .orcha-status-ringkas[data-status="selesai"] {
        border-color: #b7e4cd !important;
        background-color: #eafaf1;
        color: #0b7a4b;
    }

    .orcha-status-ringkas[data-status="dibatalkan"] {
        border-color: #f6c9cd !important;
        background-color: #fdecee;
        color: #9b2530;
    }

    /* Dua tombol di kolom aksi disamakan tingginya.

       Terukur sebelumnya: ikon detail 58x38 dan tombol serah terima 127x29 —
       dua benda bersebelahan dengan tinggi berbeda sembilan piksel, dan
       garis bawahnya tidak pernah sejajar. Keduanya kini 34px, tinggi yang
       sama dengan pemilih status di sebelahnya. */
    .orcha-aksi-sewa {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        height: 34px;
        padding: 0 .7rem;
        border-radius: 9px;
        white-space: nowrap;
        vertical-align: middle;
    }

    /* Yang hanya berisi ikon dibuat sepersegi, supaya tidak melebar sia-sia. */
    .orcha-aksi-sewa.ikon-saja {
        width: 34px;
        padding: 0;
    }

    /* Judul kolom daftar sewa ditengahkan.

       Hanya judulnya. Isinya tetap rata kiri — nama penyewa dan nama unit
       panjangnya berbeda-beda, dan deretan teks yang ditengahkan membuat tepi
       kirinya bergerigi sehingga mata kehilangan garis untuk menyusuri kolom.

       Berlaku untuk SEMUA kolom, termasuk yang isinya rata kanan — karena itu
       .text-end bawaan Bootstrap pada dua judul itu perlu ditimpa; kalau tidak,
       barisnya setengah tengah setengah kanan dan justru terlihat lebih tidak
       rapi daripada sebelum diubah. */
    .orcha-tabel-sewa thead th,
    .orcha-tabel-sewa thead th.text-end,
    .orcha-tabel-bayar thead th,
    .orcha-tabel-bayar thead th.text-end,
    .orcha-tabel-batal thead th,
    .orcha-tabel-batal thead th.text-end { text-align: center !important; }

    /* ===== Status pengajuan pembatalan =====
       Sebelumnya semuanya lencana abu-abu yang sama: "Diajukan", "Disetujui",
       dan "Ditolak" terlihat serupa, padahal ketiganya menuntut perbuatan yang
       sama sekali berbeda. Warnanya mengikuti maknanya, bukan selera —
       kuning menunggu dikerjakan, biru sedang jalan, hijau uangnya sudah
       berangkat, merah ditutup tanpa pengembalian. */
    .orcha-status-batal {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .72rem;
        font-weight: 700;
        border-radius: 99px;
        padding: .22rem .6rem;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .orcha-status-batal i { display: flex; align-items: center; line-height: 1; font-size: .9em; }

    .orcha-status-batal[data-status="diajukan"]    { background: #fff4e0; border-color: #f3ddb0; color: #8a5a09; }
    .orcha-status-batal[data-status="diproses"]    { background: #eef6fb; border-color: #c7e2f2; color: #14618f; }
    .orcha-status-batal[data-status="disetujui"]   { background: #eef2ff; border-color: #c7d2fe; color: #3730a3; }
    .orcha-status-batal[data-status="dana_dikirim"]{ background: #e6f4ea; border-color: #cbe8d4; color: #1f7a44; }
    .orcha-status-batal[data-status="ditolak"]     { background: #fee2e2; border-color: #f6c9cd; color: #9b2530; }

    /* Baris tabel sewa lebih rapat: selnya tidak lagi menampung isian setinggi
       48px, jadi ruang kosongnya bisa dikembalikan. */
    .orcha-tabel-sewa td {
        padding-top: .5rem !important;
        padding-bottom: .5rem !important;
        vertical-align: middle !important;
    }

    /* ============ GALERI PERJALANAN ============ */

    .orcha-unggah-galeri {
        border-radius: 14px;
    }

    /* Sasaran pilih-berkas yang lebar, bukan tombol "Choose files" bawaan.

       Tombol bawaan peramban selebar teksnya sendiri dan tampilannya berbeda di
       tiap sistem — di layar yang seluruhnya sudah bergaya, ia satu-satunya
       yang terlihat asing. Yang diketuk admin sekarang seluruh kotaknya. */
    .orcha-jatuhkan {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        width: 100%;
        padding: 1.6rem 1rem;
        border: 2px dashed #cfe4f2;
        border-radius: 14px;
        background: #f8fbfd;
        cursor: pointer;
        text-align: center;
        transition: all .15s ease;
    }

    .orcha-jatuhkan:hover {
        border-color: var(--orc-primer);
        background: #f2f8fc;
    }

    .orcha-jatuhkan-ikon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: #fff;
        background: linear-gradient(135deg, var(--orc-primer), var(--orc-primer-2));
        box-shadow: 0 8px 15px rgba(124, 58, 237, .25);
        margin-bottom: .35rem;
    }

    /* Saat memproses, ikonnya BERPUTAR — bukan diam sambil menyisakan tebakan.

       Unggahan foto besar memakan beberapa detik, dan selama itu layar tidak
       berubah sama sekali. Yang dilakukan admin berikutnya bisa ditebak:
       mengetuk lagi, lalu bertanya kenapa fotonya masuk dua kali. */
    .orcha-jatuhkan.sibuk {
        border-color: var(--orc-primer);
        background: #f2f8fc;
        cursor: progress;
    }

    .orcha-jatuhkan.sibuk .orcha-jatuhkan-ikon > i::before {
        content: "\f130";                 /* bi-arrow-repeat */
        animation: orcha-putar 900ms linear infinite;
    }

    @keyframes orcha-putar {
        to { transform: rotate(360deg); }
    }

    /* Kartu pratayang: gambar di atas, isian keterangannya sendiri di bawah.

       Sebelumnya satu keterangan untuk seluruh unggahan. Itu benar untuk foto
       serombongan dari acara yang sama, tetapi salah begitu admin memilih foto
       dari beberapa trip sekaligus — dan yang terjadi bukan admin mengeluh,
       melainkan keterangannya diisi asal-asalan lalu tidak pernah dibetulkan. */
    .orcha-pratayang-kartu {
        border: 1px solid #eef2f7;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .orcha-pratayang-kartu .orcha-galeri-kotak {
        border-radius: 0;
    }

    .orcha-pratayang-isi {
        padding: .5rem .55rem;
    }

    .orcha-pratayang-isi .form-control {
        height: 34px !important;
        font-size: .78rem;
        border-width: 1px !important;
        border-radius: 9px !important;
        padding: 0 .6rem !important;
    }

    /* Saklar "langsung tampil": bentuknya sendiri, bukan form-switch bawaan
       yang di layar ini tampak kecil dan tidak berpasangan dengan apa pun. */
    .orcha-saklar {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .45rem .8rem;
        border: 1px solid #eef2f7;
        border-radius: 999px;
        background: #fff;
        cursor: pointer;
        user-select: none;
        transition: all .15s ease;
    }

    .orcha-saklar:hover { border-color: #cfe4f2; }

    .orcha-saklar input { display: none; }

    .orcha-saklar .alur {
        position: relative;
        width: 38px;
        height: 21px;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background .15s ease;
        flex: 0 0 38px;
    }

    .orcha-saklar .alur::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background: #fff;
        transition: transform .15s ease;
    }

    .orcha-saklar input:checked + .alur { background: #14a06a; }
    .orcha-saklar input:checked + .alur::after { transform: translateX(17px); }

    .orcha-saklar .tulisan {
        font-size: .82rem;
        font-weight: 600;
        color: var(--orc-tinta);
        white-space: nowrap;
    }

    /* Baris kaki unggahan: isian samakan, saklar, tombol — sejajar dan tidak
       saling berdesakan di layar sempit. */
    /* Semua yang berjajar di baris ini setinggi 48px.

       Layout memaksa tinggi itu pada SETIAP .form-control, sementara tombol dan
       saklar memakai tingginya masing-masing — hasilnya tiga benda sebaris
       dengan tiga tinggi berbeda, dan barisnya terbaca seperti belum selesai
       disusun. Angkanya mengikuti isian, bukan sebaliknya: isiannya yang tidak
       bisa diubah tanpa melawan !important milik layout. */
    .orcha-baris-unggah {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .75rem;
    }

    .orcha-baris-unggah .orcha-saklar,
    .orcha-baris-unggah .orcha-btn {
        height: 48px;
        border-radius: 12px;
    }

    .orcha-baris-unggah .orcha-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        padding: 0 1.1rem;
        white-space: nowrap;
    }

    .orcha-baris-unggah .orcha-saklar {
        padding: 0 1rem;
    }

    .orcha-samakan {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex: 1 1 22rem;
        min-width: 0;
    }

    .orcha-samakan .form-control {
        flex: 1 1 auto;
        min-width: 0;
    }

    .orcha-baris-unggah .orcha-btn-utama {
        margin-left: auto;
    }

    .orcha-jatuhkan-judul {
        font-weight: 700;
        font-size: .92rem;
        color: var(--orc-tinta);
    }

    .orcha-jatuhkan-catatan {
        font-size: .78rem;
        color: #94a3b8;
    }


    /* Petak foto, bukan baris tabel: isinya gambar, dan yang dinilai admin saat
       menyusun galeri adalah fotonya sendiri — nama berkas tidak memberi tahu
       apa pun tentang pantas atau tidaknya sebuah foto dipajang. */
    .orcha-galeri-petak {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: .85rem;
    }

    .orcha-galeri-kartu {
        border: 1px solid #eef2f7;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        transition: all .15s ease;
    }

    .orcha-galeri-kartu:hover {
        border-color: #cfe4f2;
        box-shadow: 0 10px 24px rgba(31, 45, 61, .08);
    }

    /* Foto yang disembunyikan diredupkan, bukan dihilangkan: admin tetap perlu
       melihatnya untuk memutuskan ditampilkan lagi atau dihapus. */
    .orcha-galeri-sembunyi {
        opacity: .55;
        filter: saturate(.4);
    }

    .orcha-galeri-sembunyi:hover {
        opacity: 1;
        filter: none;
    }

    /* Perbandingan sisi tetap 4:3 supaya petaknya rata, berapa pun ukuran foto
       aslinya. Tanpa itu satu foto potret membuat barisnya melar sendirian. */
    .orcha-galeri-kotak {
        position: relative;
        aspect-ratio: 4 / 3;
        background: #eef1f7;
        overflow: hidden;
    }

    .orcha-galeri-kotak img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Nomor urut di atas fotonya sendiri: yang disusun admin adalah urutan
       tampil, dan menaruh angkanya jauh dari fotonya membuat ia harus
       bolak-balik memastikan mana milik mana. */
    .orcha-galeri-nomor {
        position: absolute;
        top: .5rem;
        left: .5rem;
        min-width: 1.6rem;
        padding: .1rem .45rem;
        border-radius: 999px;
        background: rgba(31, 45, 61, .82);
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        text-align: center;
    }

    .orcha-galeri-penanda {
        position: absolute;
        bottom: .5rem;
        left: .5rem;
        padding: .12rem .5rem;
        border-radius: 999px;
        background: rgba(155, 37, 48, .9);
        color: #fff;
        font-size: .7rem;
        font-weight: 600;
    }

    .orcha-galeri-isi {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .55rem .65rem;
    }

    .orcha-galeri-keterangan {
        flex: 1 1 auto;
        min-width: 0;
        font-size: .78rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .orcha-galeri-pratayang {
        border-radius: 12px;
        border: 2px dashed #cfe4f2;
    }

    /* Formulir sunting menggantikan kartunya di tempat: yang disunting tetap
       terlihat di sebelah isian, jadi admin tidak perlu mengingat foto mana
       yang tadi diketuk. */
    .orcha-galeri-sunting {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid #cfe4f2;
        border-radius: 14px;
        background: #f8fbfd;
        box-shadow: 0 8px 22px rgba(124, 58, 237, .07);
    }

    .orcha-galeri-sunting .orcha-galeri-kotak {
        width: 170px;
        flex: 0 0 170px;
        border-radius: 12px;
    }

    /* Baris tindakan formulir sunting: tiga kolom sama lebar.

       Bentuk flex yang saling mendorong (saklar ke kiri, dua tombol ke kanan)
       menyisakan jurang di antaranya, dan lebar jurangnya berubah-ubah
       mengikuti lebar layar — di kartu selebar ini jaraknya jadi mencolok.
       Dibagi rata, ketiganya punya tempat tetap berapa pun lebar layarnya. */
    .orcha-baris-aksi {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .6rem;
        align-items: stretch;
    }

    .orcha-baris-aksi > * {
        height: 48px;
        border-radius: 12px;
        width: 100%;
    }

    .orcha-baris-aksi .orcha-btn,
    .orcha-baris-aksi .orcha-saklar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        padding: 0 .9rem;
        white-space: nowrap;
    }

    /* Di layar sempit ketiganya bertumpuk: tiga kolom selebar 100px membuat
       tulisannya terpotong, dan tombol yang labelnya terpotong berhenti
       menjelaskan apa yang akan terjadi. */
    @media (max-width: 34rem) {
        .orcha-baris-aksi {
            grid-template-columns: 1fr;
        }
    }

    /* Ikon di dalam kotak peringatan Bootstrap.

       bootstrap-icons menggambar glifnya di garis dasar dengan vertical-align
       bawaannya sendiri, jadi di dalam baris flex ia duduk lebih rendah
       daripada teks di sebelahnya — cukup untuk terbaca sebagai ceklis yang
       melorot. Ditangani seperti ikon lain di berkas ini. */
    .alert i.bi {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        vertical-align: 0;
        flex: 0 0 auto;
    }

    /* ============ GRAFIK DASHBOARD ============

       Digambar dengan SVG dan CSS, tanpa pustaka grafik: aset Vite tidak ikut
       ter-deploy untuk halaman admin ini, jadi pustaka yang menuntut bundel
       hanya akan menghasilkan kotak kosong di server. */
    .orcha-grafik {
        width: 100%;
        height: 190px;
        overflow: visible;
    }

    .orcha-grafik-batang {
        transition: opacity .15s ease;
    }

    .orcha-grafik-batang:hover {
        opacity: .78;
    }

    .orcha-grafik-sumbu { font-size: .68rem; fill: #94a3b8; }
    .orcha-grafik-nilai { font-size: .68rem; font-weight: 700; fill: var(--orc-primer-2); }

    .orcha-grafik-kunci {
        display: flex;
        flex-wrap: wrap;
        gap: .9rem;
        font-size: .76rem;
        color: #64748b;
    }

    .orcha-grafik-kunci .tanda {
        display: inline-block;
        width: .7rem;
        height: .7rem;
        border-radius: 3px;
        margin-right: .35rem;
        vertical-align: -1px;
    }

    /* Batang mendatar untuk perbandingan kategori — bentuk yang sudah dipakai
       halaman Keuntungan, dipakai ulang supaya admin tidak belajar dua bahasa
       untuk hal yang sama. */
    .orcha-baris-batang {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .45rem 0;
    }

    .orcha-baris-batang .nama { flex: 0 0 38%; font-size: .82rem; color: #475569; }

    .orcha-baris-batang .jalur {
        flex: 1 1 auto;
        height: 8px;
        border-radius: 999px;
        background: #eef1f7;
        overflow: hidden;
    }

    .orcha-baris-batang .isi { display: block; height: 100%; border-radius: 999px; }

    .orcha-baris-batang .angka {
        flex: 0 0 auto;
        min-width: 3.4rem;
        text-align: right;
        font-size: .82rem;
        font-weight: 700;
        color: var(--orc-tinta);
    }

    /* Kotak unggah surat bertanda tangan.

       Ditaruh di kaki kartu riwayat, bukan di kartu tersendiri: yang mengunduh
       suratnya dan yang mengunggah balasannya orang yang sama dalam satu
       urusan, dan memisahkannya berarti admin harus ingat bahwa keduanya
       berpasangan. */
    .orcha-surat-ttd {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .6rem;
        padding: .7rem .85rem;
        border: 1px dashed #cfe4f2;
        border-radius: 12px;
        background: #f8fbfd;
    }

    .orcha-surat-ttd > i {
        font-size: 1.15rem;
        color: var(--orc-primer);
        line-height: 1;
    }

    /* Sudah ada berkasnya: garis utuh dan hijau, bukan putus-putus.
       Putus-putus berarti "tempat kosong menunggu diisi", dan itu justru
       kebalikan keadaannya. */
    .orcha-surat-ttd-ada {
        border-style: solid;
        border-color: #b7e4cd;
        background: #f6fdf9;
    }

    .orcha-surat-ttd-ada > i {
        color: #14a06a;
    }

    /* Tombol hapus di dalam kotak itu: merah sejak awal, sebentuk dengan tombol
       hapus baris di penyunting peserta. */
    .orcha-btn-bahaya {
        border: 1px solid #f6c9cd;
        background: #fdecee;
        color: #9b2530;
    }

    .orcha-btn-bahaya:hover {
        background: #fbd9dd;
        border-color: #c2323c;
        color: #7f1d28;
    }

    /* Waktu dan pelaku: satu baris kecil di bawah, dipisahkan garis tipis.
       Sebelumnya menempel langsung di bawah nama dengan ukuran yang mirip
       titik jemput, sehingga tiga keterangan berbeda jenis terbaca sebagai
       satu gumpalan abu. */
    .orcha-ganti-jejak {
        margin-top: .5rem;
        padding-top: .45rem;
        border-top: 1px dashed #eef2f7;
        font-size: .74rem;
        color: #94a3b8;
    }

    /* Baris titik jemput: diberi latar sendiri supaya terbaca sebagai
       keterangan tambahan, bukan sebagai penggantian kedua. */
    .orcha-ganti-titik {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .45rem;
        margin-top: .5rem;
        padding: .3rem .55rem;
        border-radius: 8px;
        background: #f8fafc;
        font-size: .76rem;
        color: #64748b;
    }

    /* Nama lama: cip abu yang dicoret. Nama baru: hijau.

       Dua warna untuk dua keadaan yang berbeda — yang satu sudah tidak ikut,
       yang satu yang berangkat — dan keduanya dipakai sama di isian penggantian
       maupun di kartu riwayat, supaya admin tidak belajar dua bahasa untuk satu
       kejadian. */
    .orcha-ganti-lama {
        display: inline-block;
        padding: .08rem .5rem;
        border-radius: 999px;
        /* Merah, bukan abu. Abu berarti "kurang penting"; yang terjadi di sini
           justru seseorang dikeluarkan dari rombongan — dan itu harus terbaca
           sekilas, berpasangan dengan hijau di sebelah panah. */
        background: #fdecec;
        color: #a12a2a;
        font-weight: 600;
        font-size: .78rem;
        text-decoration: line-through;
        text-decoration-color: #e9a1a5;
    }

    .orcha-ganti-baru {
        display: inline-block;
        padding: .08rem .5rem;
        border-radius: 999px;
        background: #eafaf1;
        color: #0b7a4b;
        font-weight: 700;
        font-size: .82rem;
    }

    /* Penanda di atas kartu kesehatan milik peserta yang sudah diganti. */
    .orcha-arsip-penanda {
        display: flex;
        flex-wrap: wrap;
        /* Tengah, bukan atas: isinya kini cip nama yang tingginya melebihi
           satu baris teks, dan rata atas membuat ikonnya menggantung. */
        align-items: center;
        gap: .55rem;
        padding: .6rem .85rem;
        margin-bottom: -.4rem;
        border-radius: 12px 12px 0 0;
        background: #f1f5f9;
        color: #64748b;
        font-size: .82rem;
        line-height: 1.5;
    }

    .orcha-arsip-penanda > i {
        line-height: 1.4;
    }

    /* Judul kolom di tengah.

       Diminta untuk tabel rincian, yang kolomnya rapat dan angkanya
       berdekatan: judul yang menggantung di tepi kiri kolom sebelahnya mudah
       terbaca sebagai milik kolom yang salah. Isinya TIDAK ikut ditengahkan —
       nominal rupiah tetap rata kanan supaya digit satuannya berbaris lurus
       dan besar-kecilnya bisa dibandingkan sekilas. */
    .orcha-tabel-judul-tengah thead th {
        text-align: center;
        vertical-align: middle;
    }

    /* Kolomnya diberi napas sedikit lebih lebar, karena keluhannya memang
       soal data yang terlalu berdempetan. */
    .orcha-tabel-judul-tengah thead th,
    .orcha-tabel-judul-tengah tbody td {
        padding-left: .75rem;
        padding-right: .75rem;
    }

    /* Lebar minimum kolom nama — HANYA untuk tabel rincian keuntungan.

       Tanpa ini, pemesan dan paket yang paling dulu dikorbankan saat ruang
       menyempit: "Open Trip Banyuwangi" melipat jadi tiga baris sementara kolom
       angka di sebelahnya tetap lapang, dan satu baris pesanan jadi setinggi
       empat baris teks.

       Kelasnya sendiri, bukan menumpang .orcha-tabel-judul-tengah: angka nth-child
       itu mengikuti susunan kolom tabel rincian, dan tabel lain dengan susunan
       berbeda justru terdorong melebihi kartunya — persis yang terjadi pada
       daftar pendaftaran, yang kolom aksinya sampai terpotong. */
    .orcha-tabel-rincian tbody td:nth-child(2),
    .orcha-tabel-rincian thead th:nth-child(2) {
        min-width: 8.5rem;
    }

    .orcha-tabel-rincian tbody td:nth-child(3),
    .orcha-tabel-rincian thead th:nth-child(3) {
        min-width: 10.5rem;
    }

    /* Tombol rentang tanggal: satu kelompok menyatu, bukan empat tombol lepas
       yang tampak seperti empat perintah berbeda. */
    .orcha-rentang {
        display: inline-flex;
        flex-wrap: wrap;
        gap: .35rem;
        padding: .3rem;
        border-radius: 14px;
        background: #f1f5f9;
    }

    .orcha-rentang button {
        border: 0;
        background: transparent;
        border-radius: 10px;
        padding: .38rem .85rem;
        font-size: .82rem;
        font-weight: 600;
        color: #475569;
        transition: all .15s ease;
    }

    .orcha-rentang button:hover {
        background: #e2e8f0;
        color: var(--orc-tinta);
    }

    .orcha-rentang button.aktif {
        background: linear-gradient(135deg, var(--orc-primer-2), var(--orc-primer));
        color: #fff;
        box-shadow: 0 6px 14px rgba(124, 58, 237, .25);
    }

    @media (max-width: 575.98px) {
        .orcha-untung-hero {
            padding: 1.3rem 1.15rem;
            border-radius: 22px;
        }

        .orcha-hero-angka {
            font-size: 1.8rem;
        }
    }

    /* ============ PESAN KONTAK: LENCANA KEPERLUAN & KEADAAN BACA ============
       Dipakai di kotak masuk DAN di halaman detailnya, jadi tinggal di sini
       supaya pesan yang sama dikenali lewat warna yang sama di kedua halaman —
       bukan dua salinan yang lambat laun berbeda sendiri.

       Satu warna untuk tiap keperluan. Enam jenis pertanyaan yang dijawab
       dengan cara berbeda-beda — study tour disusun per rombongan, sewa
       kendaraan dicek ketersediaan unitnya, kerja sama diteruskan ke orang
       lain — dan admin memilah kotak masuknya persis menurut itu. Warnanya
       sekadar mempercepat pemilahan; namanya tetap tertulis dan ikonnya tetap
       berbeda, jadi tidak ada yang hilang bila warnanya tidak tertangkap.

       Sengaja dijauhkan dari biru penanda "belum dibaca": biru di layar ini
       sudah dipakai untuk keadaan baca, bukan untuk kategori. */
    .orcha-lencana-keperluan,
    .orcha-status-pesan {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .72rem;
        font-weight: 700;
        border-radius: 99px;
        padding: .22rem .6rem;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .orcha-lencana-keperluan i,
    .orcha-status-pesan i { display: flex; align-items: center; line-height: 1; font-size: .95em; }

    .orcha-lencana-keperluan { background: #f4f8fb; border-color: #dbe7f0; color: #46617c; }

    .orcha-lencana-keperluan[data-keperluan="open_trip"]      { background: #e6f6f4; border-color: #bfe3dd; color: #0f6b60; }
    .orcha-lencana-keperluan[data-keperluan="private_trip"]   { background: #f2edfd; border-color: #ddd0fa; color: #5b3fc4; }
    .orcha-lencana-keperluan[data-keperluan="study_tour"]     { background: #fff2e2; border-color: #f5d9b2; color: #8a5209; }
    .orcha-lencana-keperluan[data-keperluan="sewa_kendaraan"] { background: #eaeffb; border-color: #ccd8f5; color: #2a3f8f; }
    .orcha-lencana-keperluan[data-keperluan="kerja_sama"]     { background: #fdecef; border-color: #f7ccd4; color: #9b2547; }
    .orcha-lencana-keperluan[data-keperluan="lainnya"]        { background: #f1f4f7; border-color: #dde4ea; color: #5b7186; }

    /* Yang belum dibaca dibuat PEKAT, satu-satunya lencana berlatar penuh di
       barisnya. Lencana keperluan di sebelahnya semuanya pucat, jadi mata
       menemukan keadaan bacanya lebih dulu — bukan tenggelam di antara enam
       warna kategori. */
    .orcha-status-pesan[data-baca="belum"] { background: var(--orc-primer); border-color: var(--orc-primer); color: #fff; }
    .orcha-status-pesan[data-baca="sudah"] { background: #f1f4f7; border-color: #dde4ea; color: #5b7186; }


    /* ============ TOMBOL UTAMA FORMULIR ============
       Setinggi tombol simpan di formulir paket wisata: 38px dengan huruf 1rem.

       Sebelumnya tombol simpan/batal di formulir armada memakai ukuran
       .orcha-btn apa adanya — 34,5px dengan huruf 13,76px — sehingga dua
       formulir yang dikerjakan berurutan terasa dari dua aplikasi berbeda.
       Ukurannya diikat ke min-height, bukan ke jarak dalam saja: jarak dalam
       yang dihitung mundur dari tinggi akan meleset sendiri begitu ukuran
       hurufnya berubah. */
    .orcha-btn-besar {
        min-height: 38px;
        padding: .5rem 1rem;
        font-size: 1rem;
    }

    /* Bungkus isi tombol dipatok kotak barisnya.
       Ikon di dalamnya adalah wadah flex setinggi glifnya sendiri, dan tanpa
       patokan ini isinya bisa sedikit lebih tinggi daripada kotak baris —
       tombolnya melar 0,6px melewati 38px dan tidak lagi sama persis dengan
       tombol di sebelahnya. Terukur di peramban, bukan ditebak. */
    .orcha-btn-besar > span {
        display: inline-flex;
        align-items: center;
        line-height: 1.2;
    }

    /* ...TETAPI bungkus pemintalnya tetap tersembunyi sampai ditekan.

       Aturan di atas menyebut kelas DAN elemen, jadi ia lebih kuat daripada
       penyembunyi [wire:loading] di lembar ini — akibatnya tiap tombol
       .orcha-btn-besar menggambar "Simpan Menyimpan…" berjajar sebelum
       tombolnya disentuh. Terbaca sebagai halaman yang sedang bekerja padahal
       diam.

       Dikembalikan dengan pemilih yang lebih kuat lagi, bukan dengan
       !important: Livewire menampilkannya lewat gaya sebaris, dan gaya sebaris
       kalah oleh !important — pemintalnya justru tidak akan pernah muncul. */
    .orcha-btn-besar > span[wire\:loading] {
        display: none;
    }


    /* ============ KARTU PILIHAN ============
       Dipakai memilih transmisi di formulir armada DAN memilih jenis unit
       di halaman Bagian Pemeriksaan. Tinggal di sini karena dua halaman
       memakainya; dua salinan sejajar lambat laun berbeda sendiri. */
        /* Kartu pilihan (transmisi): sasaran kliknya seluruh kartu, dan yang
       terpilih ditandai warna SEKALIGUS tanda centang — warna saja tidak
       cukup bagi yang sulit membedakannya. */
    .orcha-pilih-kartu {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
        gap: .6rem;
    }

    .orcha-kartu-pilihan {
        position: relative;
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .75rem .9rem;
        border: 1.5px solid #dbe7f0;
        border-radius: .8rem;
        background: #fff;
        cursor: pointer;
        transition: all .15s ease;
    }

    .orcha-kartu-pilihan input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .orcha-kartu-pilihan:hover { border-color: #b9d0e2; }

    .orcha-kartu-pilihan.aktif {
        border-color: var(--orc-primer);
        background: #f2f8fc;
    }

    .orcha-kartu-pilihan .rupa {
        font-size: 1.15rem;
        line-height: 1;
        color: #94a3b8;
    }

    .orcha-kartu-pilihan.aktif .rupa { color: var(--orc-primer); }

    .orcha-kartu-pilihan .judul {
        display: block;
        font-weight: 700;
        font-size: .9rem;
        color: var(--orc-tinta);
    }

    .orcha-kartu-pilihan .ket {
        display: block;
        font-size: .76rem;
        color: #94a3b8;
    }

    .orcha-kartu-pilihan .tanda {
        position: absolute;
        top: .5rem;
        right: .6rem;
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        background: var(--orc-primer);
        color: #fff;
        font-size: .62rem;
        opacity: 0;
        transform: scale(.6);
        transition: all .15s ease;
    }

    /* Cakramnya sudah memakai flex, tapi yang ditengahkan adalah KOTAK BARIS
       ikonnya, bukan glifnya: kotak itu setinggi line-height halaman dan
       bootstrap-icons masih menggeser glifnya turun (vertical-align: sub).
       Lebar kotaknya pun lebih besar daripada lebar glifnya, sehingga
       centangnya duduk di kiri-bawah dan menyisakan tempat kosong di
       kanan-atas — terukur 3px ke kiri dan 1,4px ke bawah pada cakram 18px.

       Ikonnya sendiri dijadikan wadah flex, mengikuti pola yang sudah
       dipakai .stat-icon-wrapper: kotaknya menyusut sepas glifnya dan
       vertical-align jadi tidak berlaku lagi. */
    .orcha-kartu-pilihan .tanda i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        vertical-align: middle;
    }

    .orcha-kartu-pilihan.aktif .tanda { opacity: 1; transform: scale(1); }


    /* ============ SAKELAR-KARTU ============
       Dipakai formulir armada (lepas kunci, tayang di website) DAN halaman
       Bagian Pemeriksaan (ikut diperiksa / tidak). Tinggal di sini karena dua
       halaman memakainya. */
    /* Sakelar penayangan: kartunya sendiri ikut berubah warna, karena yang
       diputuskan di sini adalah unitnya terlihat pelanggan atau tidak. */
    .orcha-sakelar-kartu {
        display: flex;
        align-items: center;
        gap: .85rem;
        width: 100%;
        margin: 0;
        padding: .85rem 1rem;
        border: 1.5px solid #dbe7f0;
        border-radius: .8rem;
        background: #f8fafc;
        cursor: pointer;
        transition: all .15s ease;
    }

    .orcha-sakelar-kartu.nyala {
        border-color: #1a8a52;
        background: #f0faf5;
    }

    .orcha-sakelar-kartu .rupa {
        flex: 0 0 2.4rem;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: .7rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e8edf2;
        color: #64748b;
        font-size: 1.05rem;
        line-height: 1;
    }

    /* Ikonnya sendiri dijadikan wadah flex.

       Kotaknya sudah ditengahkan, tapi yang ditengahkan adalah KOTAK BARIS
       ikonnya — bukan glifnya. Terukur dari tintanya di dalam cakram 38px:
       meleset sampai 4,9px ke bawah dan 2,8px ke samping. Pola yang sama
       sudah dipakai .stat-icon-wrapper dan centang transmisi. */
    .orcha-sakelar-kartu .rupa i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        vertical-align: middle;
    }

    .orcha-sakelar-kartu .rupa > i { line-height: 1; }

    .orcha-sakelar-kartu.nyala .rupa { background: #d7f0e2; color: #126b40; }

    .orcha-sakelar-kartu .isi { flex: 1 1 auto; }

    .orcha-sakelar-kartu .judul {
        display: block;
        font-weight: 700;
        font-size: .9rem;
        color: var(--orc-tinta);
    }

    .orcha-sakelar-kartu .ket {
        display: block;
        font-size: .78rem;
        color: #64748b;
    }

    .orcha-sakelar-kartu .form-check-input {
        width: 2.4rem;
        height: 1.3rem;
        cursor: pointer;
    }

    .orcha-sakelar-kartu.nyala .form-check-input:checked {
        background-color: #1a8a52;
        border-color: #1a8a52;
    }


    /* ============ LENCANA JENIS UNIT ============
       Mobil, HiAce, dan Bus dibedakan warnanya, bukan tiga lencana biru yang
       sama. Yang dibaca admin di kolom ini bukan "unit apa saja" melainkan
       "bagian ini berlaku untuk yang mana" — dan tiga lencana serupa memaksa
       tiap katanya dieja ulang baris demi baris.

       Ikonnya ikut berbeda, jadi pemilahannya tetap terbaca oleh yang sulit
       membedakan warna. */
    .orcha-cip-jenis {
        display: inline-flex;
        align-items: center;
        gap: .28rem;
        font-size: .72rem;
        font-weight: 700;
        border-radius: 99px;
        padding: .2rem .58rem;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .orcha-cip-jenis i { display: flex; align-items: center; line-height: 1; font-size: .95em; }

    .orcha-cip-jenis[data-jenis="mobil"] { background: #e6f6f4; border-color: #bfe3dd; color: #0f6b60; }
    .orcha-cip-jenis[data-jenis="hiace"] { background: #fff2e2; border-color: #f5d9b2; color: #8a5209; }
    .orcha-cip-jenis[data-jenis="bus"]   { background: #eaeffb; border-color: #ccd8f5; color: #2a3f8f; }


    /* ============ KEADAAN BAGIAN PEMERIKSAAN ============
       Warnanya mengikuti MAKNANYA, bukan sekadar dibedakan.

       Sebelumnya lencananya dipinjam dari layar lain: yang aktif memakai
       lencana "sudah dibaca" milik pesan kontak — abu-abu pudar, yang justru
       terbaca seperti sesuatu yang dimatikan — dan yang nonaktif memakai
       lencana "ditolak" milik pembatalan, merah, seolah ada yang salah.
       Padahal menonaktifkan bagian adalah tindakan yang benar dan disengaja,
       bukan kegagalan.

       Sekarang: hijau untuk yang sedang berlaku, abu netral untuk yang
       sengaja dimatikan. Merah disimpan untuk hal yang memang keliru. */
    .orcha-cip-keadaan {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .72rem;
        font-weight: 700;
        border-radius: 99px;
        padding: .22rem .6rem;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .orcha-cip-keadaan i { display: flex; align-items: center; line-height: 1; font-size: .95em; }

    .orcha-cip-keadaan[data-keadaan="aktif"]    { background: #e6f4ea; border-color: #cbe8d4; color: #1f7a44; }
    .orcha-cip-keadaan[data-keadaan="nonaktif"] { background: #f1f4f7; border-color: #dde4ea; color: #5b7186; }


    /* Penutup pemintal selama berkas gambar naik.

       TIDAK menyentuh display sama sekali. Aturan display pada elemen
       ber-wire:loading akan mengalahkan penyembunyi [wire:loading] di atas —
       keduanya sama kuat, dan yang ditulis belakangan menang — sehingga
       pemintalnya tergambar sebelum unggahan dimulai.

       Ditengahkan lewat text-align dan line-height, bukan flex: elemen yang
       position-nya absolute diperlakukan sebagai blok apa pun display yang
       dikembalikan Livewire saat menampilkannya. */
    .orcha-foto-naik {
        position: absolute;
        inset: 0;
        background: rgba(238, 242, 246, .88);
        text-align: center;
        line-height: 4rem;
        color: var(--orc-primer);
    }


    /* Status yang hanya bisa DIBACA, karena daftar pilihannya belum sampai
       dari Orcha. Bentuknya sama dengan pemilihnya supaya barisnya tidak
       berubah tinggi, tetapi jelas tidak bisa ditekan. */
    .orcha-status-diam {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        height: 34px;
        min-width: 8.5rem;
        padding: 0 .7rem;
        border: 1px dashed #cbd5e1;
        border-radius: 9px;
        background: #f8fafc;
        color: #64748b;
        font-size: .78rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .orcha-status-diam i { display: flex; align-items: center; line-height: 1; font-size: .95em; }


    /* Kartu pengajuan pembatalan di detail pendaftaran: pita merah di tepi
       kiri, bukan seluruh kartunya merah menyala. Yang merah seluruhnya
       menuntut perhatian terus-menerus padahal isinya bacaan. */
    .orcha-kartu-batal { border-left: 4px solid #dc3545 !important; }

    .orcha-bagian-nomor.batal {
        background: linear-gradient(135deg, #e8636f, #c2323c);
        box-shadow: 0 5px 14px rgba(194, 50, 60, .3);
    }


    /* Tombol yang sengaja mati: terlihat sebagai tombol supaya sebaris tombol
       tetap sejajar, tetapi jelas tidak bisa ditekan.

       Dipakai daftar Bagian Pemeriksaan (bagian yang tidak boleh dihapus) dan
       daftar Pendaftaran Open Trip (riwayat kesehatan yang belum ada isinya).
       Sebaris tombol yang salah satu selnya berisi teks polos membuat kolom
       Aksi terlihat bolong. */
    .orcha-aksi-mati {
        background: #f1f4f7;
        border: 1px solid #dde4ea;
        color: #9aa8b8;
        cursor: not-allowed;
    }


    /* Kedua tombol riwayat kesehatan selebar sama.

       "Belum ada riwayat" 147px sedangkan "Riwayat (2)" 106px — terukur di
       peramban. Karena keduanya berdampingan dengan tombol Detail dan
       dirapatkan ke kanan, selisih 41px itu MENGGESER seluruh pasangannya:
       barisnya tidak lagi sejajar dengan baris di atas dan di bawahnya.

       Lebarnya dipatok sekali di sini, bukan diperbaiki dengan memendekkan
       tulisannya: "Belum ada riwayat" memang kalimat yang perlu dibaca utuh
       oleh admin yang sedang menyiapkan manifes. */
    .orcha-aksi-riwayat {
        min-width: 9.25rem;
        justify-content: center;
    }


    /* ============ DASHBOARD ORCHA ============ */

    /* Kartu etalase: ikon kecil di atas angkanya, bukan angka telanjang di
       tengah kotak putih. Enam kotak beridentik tidak membedakan yang menuntut
       perbuatan dari yang cuma keterangan isi etalase. */
    .orcha-ikon-kecil {
        width: 34px;
        height: 34px;
        font-size: .95rem;
        border-radius: 10px;
    }

    .orcha-kartu-etalase-tautan { display: block; color: inherit; }

    .orcha-kartu-etalase { transition: transform .14s ease, box-shadow .14s ease; }

    .orcha-kartu-etalase-tautan[href]:hover .orcha-kartu-etalase {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(31, 45, 61, .1) !important;
    }

    /* Kartu uang: pita berwarna di tepi kiri sesuai artinya — masuk, keluar,
       sisa, dan yang belum tentu jadi. */
    .orcha-kartu-uang { border-left: 4px solid #cbd5e1 !important; }
    .orcha-kartu-uang[data-rupa="omzet"] { border-left-color: #2b7fb8 !important; }
    .orcha-kartu-uang[data-rupa="modal"] { border-left-color: #f59e0b !important; }
    .orcha-kartu-uang[data-rupa="untung"] { border-left-color: #1a8a52 !important; }
    .orcha-kartu-uang[data-rupa="potensi"] { border-left-color: #94a3b8 !important; }

    .orcha-angka-uang {
        font-size: 1.32rem;
        font-weight: 800;
        line-height: 1.2;
        color: var(--orc-tinta);
        word-break: break-word;
    }

    .orcha-kartu-uang[data-rupa="untung"] .orcha-angka-uang { color: #1a8a52; }
    .orcha-kartu-uang[data-rupa="potensi"] .orcha-angka-uang { color: #64748b; }

    /* Grafik omzet & keuntungan digambar ApexCharts, jadi gaya batang buatan
       tangan yang dulu ada di sini DIHAPUS — bukan ditinggalkan menganggur.
       Gaya yang tidak lagi menempel pada apa pun akan dipakai ulang keliru
       oleh orang berikutnya yang mencarinya. */

    /* Lencana status di dua daftar terbaru. Dua peta TERPISAH: status
       pendaftaran dan status penyewaan punya kata yang sama ("baru", "batal")
       tetapi daftar keadaannya berbeda, dan menyatukannya membuat satu status
       kehilangan warnanya diam-diam begitu daftar yang lain berubah. */
    .orcha-cip-status-daftar,
    .orcha-cip-status-sewa {
        display: inline-flex;
        align-items: center;
        font-size: .7rem;
        font-weight: 700;
        border-radius: 99px;
        padding: .18rem .55rem;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .orcha-cip-status-daftar { background: #f1f4f7; border-color: #dde4ea; color: #5b7186; }
    .orcha-cip-status-daftar.status-baru      { background: #fff4e0; border-color: #f3ddb0; color: #8a5a09; }
    .orcha-cip-status-daftar.status-dihubungi { background: #eef6fb; border-color: #c7e2f2; color: #14618f; }
    .orcha-cip-status-daftar.status-dp_masuk  { background: #eef2ff; border-color: #c7d2fe; color: #3730a3; }
    .orcha-cip-status-daftar.status-lunas     { background: #e6f4ea; border-color: #cbe8d4; color: #1f7a44; }
    .orcha-cip-status-daftar.status-batal     { background: #fee2e2; border-color: #f6c9cd; color: #9b2530; }

    .orcha-cip-status-sewa { background: #f1f4f7; border-color: #dde4ea; color: #5b7186; }
    .orcha-cip-status-sewa[data-status="baru"]        { background: #eef6fb; border-color: #c7e2f2; color: #14618f; }
    .orcha-cip-status-sewa[data-status="dikonfirmasi"]{ background: #eef2ff; border-color: #c7d2fe; color: #3730a3; }
    .orcha-cip-status-sewa[data-status="dp_masuk"]    { background: #fff4e0; border-color: #f3ddb0; color: #8a5a09; }
    .orcha-cip-status-sewa[data-status="berjalan"]    { background: #e6f6f4; border-color: #bfe3dd; color: #0f6b60; }
    .orcha-cip-status-sewa[data-status="selesai"]     { background: #e6f4ea; border-color: #cbe8d4; color: #1f7a44; }
    .orcha-cip-status-sewa[data-status="batal"]       { background: #fee2e2; border-color: #f6c9cd; color: #9b2530; }

    /* Layar kecil: angka besar dikecilkan supaya tidak membungkus jadi dua
       baris di dalam kartu selebar setengah layar. */
    @media (max-width: 575.98px) {
        .orcha-angka-uang { font-size: 1.05rem; }
        .orcha-angka { font-size: 1.3rem; }
    }

    /* ============ PRATINJAU PROMO ============

       Kotak yang memperlihatkan kalimat yang akan dibaca pelanggan. Tulisannya
       dirakit sistem, bukan diketik admin — jadi kotak ini satu-satunya tempat
       admin bisa memastikan yang tersimpan memang yang ia maksud.

       Sengaja TIDAK memakai .card: ia berada DI DALAM kartu formulir, dan
       kartu di dalam kartu membuat kedalamannya tidak lagi terbaca. Yang
       dipakai latar dan garis tipis. */
    .orcha-pratinjau-promo {
        border: 1px dashed var(--orc-primer);
        border-radius: var(--orc-radius-kartu, 14px);
        background: linear-gradient(150deg, #fbfaff, #f4f1fe);
        padding: 1.1rem 1.2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    /* Belum ada yang bisa ditunjukkan: garisnya diredupkan supaya kotak ini
       terbaca sebagai tempat yang MENUNGGU diisi, bukan sebagai peringatan. */
    .orcha-pratinjau-promo.kosong {
        border-color: #d7dce6;
        background: #fbfcfe;
    }

    .orcha-pratinjau-judul {
        font-weight: 800;
        font-size: 1.02rem;
        line-height: 1.35;
        color: var(--orc-tinta);
    }

    .orcha-pratinjau-ket {
        font-size: .84rem;
        line-height: 1.55;
        color: #5b6a79;
        margin-top: .45rem;
    }

    .orcha-pratinjau-ket em { color: var(--orc-primer-2); font-style: normal; }
</style>

{{-- Isian rupiah dirapikan SAAT DIKETIK, di peramban.

     Sebelumnya perapiannya dikerjakan server saat isian ditinggalkan
     (wire:model.blur), dan selama mengetik yang terlihat "1000000" — deretan
     angka yang harus dihitung nolnya dengan jari. Pada harga rundingan yang
     dirapatkan lewat telepon, satu nol yang terlewat adalah selisih sepuluh
     kali lipat.

     DIKERJAKAN DI PERAMBAN, bukan dengan wire:model.live. Bolak-balik ke
     server tiap ketukan membuat isiannya tersendat pada sambungan yang lambat,
     dan setiap balasan menggambar ulang kotaknya — kursor melompat ke ujung
     tepat saat orangnya sedang mengetik di tengah.

     KURSORNYA DIJAGA. Menyetel value begitu saja memindahkan kursor ke ujung
     setiap kali titik pemisah bertambah; yang membetulkan satu digit di tengah
     angka lalu harus mengetik ulang seluruhnya. Yang dihitung DIGIT sebelum
     kursor, bukan posisi hurufnya — posisi huruf bergeser saat titiknya
     bertambah, digitnya tidak.

     Dipasang sebagai satu pendengar di document, bukan per isian: Livewire
     menggambar ulang kotaknya kapan saja, dan pendengar yang menempel pada
     elemennya sendiri ikut hilang bersamanya tanpa ada yang menyadarinya.

     Server tetap merapikan sekali lagi saat isiannya ditinggalkan — itu yang
     membersihkan tempelan seperti "Rp 1.430.000,-" dan yang berlaku saat
     JavaScript mati sama sekali. --}}
<script>
    (function () {
        // Sekali saja walau partial ini disertakan lebih dari satu kali di
        // satu halaman: pendengar ganda memformat dua kali dan kursornya
        // dilempar dua kali pula.
        if (window.__orchaRupiahTerpasang) return;
        window.__orchaRupiahTerpasang = true;

        /* Dikelompokkan sendiri, bukan lewat toLocaleString.
           toLocaleString membaca setelan bahasa peramban — pada peramban
           berbahasa Inggris pemisahnya jadi koma, dan angka yang sama tampil
           berbeda di dua komputer di ruangan yang sama. */
        function berTitik(angka) {
            return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        document.addEventListener('input', function (peristiwa) {
            var kotak = peristiwa.target;

            if (!kotak.matches || !kotak.matches('.orcha-rupiah input')) return;

            var kursor = kotak.selectionStart;
            var digitSebelumKursor = kotak.value.slice(0, kursor).replace(/\D/g, '').length;

            // Dibatasi lima belas digit: di atas itu angkanya bukan rupiah
            // yang masuk akal, dan yang mengetiknya hampir pasti keyboard yang
            // tertahan.
            var angka = kotak.value.replace(/\D/g, '').slice(0, 15);
            var rapi = berTitik(angka);

            if (rapi === kotak.value) return;

            kotak.value = rapi;

            /* Kursor diletakkan sesudah digit ke-N yang sama, berapa pun titik
               yang sekarang menyelinap di antaranya.

               TIDAK ADA TANDA KURUNG SUDUT PEMBUKA DI SELURUH SKRIP INI, dan
               itu disengaja. strip_tags membacanya sebagai awal sebuah tag
               lalu menelan segalanya sampai penutupnya — termasuk isi halaman
               yang sah. Livewire memakai strip_tags di assertSee, sehingga
               satu operator perbandingan di sini membuat uji layar LAIN gagal
               menemukan angka yang jelas-jelas tergambar.

               Ditemukan begitu: uji dasbor mendadak merah karena skrip di
               berkas ini. Perbandingannya dibalik supaya bahayanya hilang
               sama sekali, bukan sekadar dihindari sekali ini. */
            var terhitung = 0;
            var posisi = 0;

            while (rapi.length > posisi && digitSebelumKursor > terhitung) {
                if (/[0-9]/.test(rapi.charAt(posisi))) {
                    terhitung++;
                }
                posisi++;
            }

            kotak.setSelectionRange(posisi, posisi);
        });
    })();
</script>
