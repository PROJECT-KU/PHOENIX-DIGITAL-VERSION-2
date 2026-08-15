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
       Satu irama jarak dipakai seluruh kartu: judul .75rem, antar blok 1rem,
       antar cip .5rem. */
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

    .orcha-petunjuk i {
        font-size: .7rem;
        vertical-align: middle;
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

    /* Cip = satu wadah berisi tombol pilih dan (bila ada) tombol hapus.
       Keduanya SELALU terlihat, tidak muncul-hilang saat disorot — supaya
       tidak perlu menebak di mana harus mengarahkan kursor. */
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
        border: 0;
        background: transparent;
        font-size: .85rem;
        line-height: 1.2;
        color: #445;
        transition: background .15s ease, color .15s ease;
    }

    .orcha-cip-pilih {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .45rem .8rem;
    }

    .orcha-cip-pilih:hover {
        background: #eef6fb;
        color: #0f2d4a;
    }

    .orcha-cip-pilih i {
        font-size: .78rem;
        opacity: .75;
    }

    /* Tombol hapus dari daftar pilihan — merah lembut, terlihat tanpa disorot. */
    .orcha-cip-hapus {
        padding: .45rem .6rem;
        border-left: 1px solid #eef0f4 !important;
        color: #c2415a !important;
    }

    .orcha-cip-hapus:hover {
        background: #fdecef;
        color: #a01c37 !important;
    }

    .orcha-cip-hapus i {
        font-size: .68rem;
    }

    /* Sudah masuk paket: tetap bisa diklik untuk dikeluarkan. */
    .orcha-cip-sudah {
        border-color: #b9d9ec;
        background: #eef6fb;
    }

    .orcha-cip-sudah .orcha-cip-pilih {
        color: #0f2d4a;
        font-weight: 600;
    }

    .orcha-cip-sudah .orcha-cip-pilih i {
        color: #1d6fa5;
        opacity: 1;
    }

    /* Cip terpilih di baris atas. Warnanya tetap gelap saat disorot, dan
       tanda silangnya tetap kontras — dulu ikut memudar sampai tak terbaca. */
    .orcha-cip-aktif {
        border-color: transparent;
        background: linear-gradient(135deg, #1d6fa5, #0f2d4a);
        align-items: center;
        padding-left: .8rem;
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
        padding: .45rem .7rem .45rem .3rem;
        color: #ffd772 !important;
    }

    .orcha-cip-buang:hover {
        background: rgba(255, 255, 255, .16);
        color: #fff !important;
    }

    .orcha-cip-buang i {
        font-size: .9rem;
    }

    .orcha-hitung {
        background: #eef6fb;
        color: #0f2d4a;
        font-weight: 700;
        flex: 0 0 auto;
    }

    .orcha-tambah .form-control,
    .orcha-tambah .btn {
        padding-top: .7rem;
        padding-bottom: .7rem;
    }

    .orcha-tambah .btn {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
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

    /* Kolom jam sempit saja — isinya cuma "07.00" */
    .orcha-jam {
        flex: 0 0 108px;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }

    @media (max-width: 575.98px) {
        .orcha-jam {
            flex-basis: 84px;
        }
    }
</style>
