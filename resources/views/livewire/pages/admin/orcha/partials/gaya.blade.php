{{--
    Gaya khusus halaman Orcha.

    Ditulis inline, bukan lewat Vite: public/build tidak ikut ter-deploy, jadi
    CSS dari berkas terpisah tidak akan sampai ke server.

    Warnanya mengikuti merek Orcha (biru laut + emas) supaya admin langsung tahu
    sedang melihat data Orcha, bukan lemon.
--}}
<style>
    .orcha-lencana {
        background: linear-gradient(135deg, #0f2d4a, #1d6fa5);
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
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(15, 45, 74, .06);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .orcha-kartu:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 30px rgba(15, 45, 74, .12);
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
        background: linear-gradient(135deg, #1d6fa5, #0f2d4a);
        box-shadow: 0 8px 15px rgba(29, 111, 165, .25);
    }

    .orcha-ikon.perlu {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 8px 15px rgba(245, 158, 11, .25);
    }

    .orcha-angka {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1.1;
        color: #0f2d4a;
    }

    .orcha-tabel thead th {
        background: #f4f8fb;
        color: #0f2d4a;
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
        color: #0f2d4a;
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
    .orcha-cek-tutup > i {
        display: block;
        line-height: 1;
    }

    .orcha-cek-ikon > i::before,
    .orcha-ikon-kotak > i::before,
    .orcha-cek-tutup > i::before {
        display: block;
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
        border-left: 3px solid #1d6fa5;
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
        color: #0f2d4a;
        font-weight: 600;
    }

    .orcha-halaman .active .page-link {
        background: linear-gradient(135deg, #1d6fa5, #0f2d4a);
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
        border-color: #1d6fa5;
        box-shadow: 0 0 0 .2rem rgba(29, 111, 165, .12);
    }

    .orcha-form .form-label {
        color: #0f2d4a;
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
        border-color: #1d6fa5;
        box-shadow: 0 2px 8px rgba(29, 111, 165, .12);
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
        color: #0f2d4a;
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
        background: linear-gradient(135deg, #1d6fa5, #0f2d4a);
        align-items: center;
        padding-left: .85rem;
        color: #fff;
        font-size: .85rem;
        font-weight: 600;
        gap: .15rem;
    }

    .orcha-cip-aktif:hover {
        border-color: transparent;
        box-shadow: 0 3px 10px rgba(15, 45, 74, .28);
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
        background: #0f2d4a;
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
        background: rgba(15, 45, 74, .78);
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
        color: #1d6fa5;
        font-size: .72rem;
        font-weight: 700;
        text-decoration: underline;
    }

    .orcha-hitung-ulang:hover {
        color: #0f2d4a;
    }

    .orcha-hitung-ulang i {
        display: inline-flex;
        align-items: center;
        line-height: 1;
    }

    .orcha-hitung {
        background: #eef6fb;
        color: #0f2d4a;
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
        background: linear-gradient(135deg, #1d6fa5, #0f2d4a);
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
        border-color: #1d6fa5;
        color: #0f2d4a;
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
        border-color: #1d6fa5;
        color: #0f2d4a;
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
        color: #0f2d4a;
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
        background: linear-gradient(90deg, #1d6fa5, #0f2d4a);
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
        color: #0f2d4a;
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

    .orcha-cip-kondisi {
        font-size: .72rem;
        font-weight: 600;
        color: #0f2d4a;
        background: #eef6fb;
        border: 1px solid #cfe4f2;
        border-radius: 99px;
        padding: .2rem .58rem;
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
        color: #0f2d4a;
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
        vertical-align: -.115em;
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
        border-radius: .7rem;
        padding: .5rem .95rem;
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
        color: #1d6fa5;
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
        background: linear-gradient(135deg, #1d6fa5, #0f2d4a);
        color: #fff;
    }

    .orcha-btn-utama:hover {
        color: #fff;
        box-shadow: 0 6px 16px rgba(29, 111, 165, .34);
    }

    /* Merah muda — khusus data kesehatan, sewarna dengan ikon denyut nadinya */
    .orcha-btn-kesehatan {
        background: linear-gradient(135deg, #f43f5e, #be123c);
        color: #fff;
    }

    .orcha-btn-kesehatan:hover {
        color: #fff;
        box-shadow: 0 6px 16px rgba(190, 18, 60, .3);
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
        color: #0f2d4a;
    }

    .orcha-btn-lembut:hover {
        background: #f4f8fb;
        border-color: #1d6fa5;
        color: #0f2d4a;
        box-shadow: 0 4px 12px rgba(15, 45, 74, .08);
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
        color: #1d6fa5;
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
        background: #1d6fa5;
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
        border-color: #1d6fa5;
        background: linear-gradient(135deg, rgba(29, 111, 165, .10), rgba(15, 45, 74, .04));
        transform: translateY(-1px);
    }

    .orcha-pick-empty {
        text-align: center;
        color: #94a3b8;
        padding: 1.5rem;
        font-size: .9rem;
    }
</style>
