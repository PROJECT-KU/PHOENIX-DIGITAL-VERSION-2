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
</style>
