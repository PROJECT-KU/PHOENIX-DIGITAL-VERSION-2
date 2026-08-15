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

    /* ---------- Cip pilihan (destinasi & fasilitas) ---------- */
    .orcha-terpilih,
    .orcha-saran {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
    }

    .orcha-saran {
        max-height: 190px;
        overflow-y: auto;
        padding: .65rem;
        border: 1px dashed #dbe3ec;
        border-radius: .8rem;
        background: #fbfcfe;
    }

    .orcha-cip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .4rem .8rem;
        border-radius: 999px;
        border: 1px solid #dbe3ec;
        background: #fff;
        color: #445;
        font-size: .85rem;
        line-height: 1.2;
        transition: all .15s ease;
    }

    .orcha-cip:hover {
        border-color: #1d6fa5;
        color: #0f2d4a;
        background: #eef6fb;
    }

    .orcha-cip-aktif {
        background: linear-gradient(135deg, #1d6fa5, #0f2d4a);
        border-color: transparent;
        color: #fff;
        font-weight: 600;
    }

    .orcha-cip-aktif button {
        border: 0;
        background: transparent;
        color: #ffd772;
        font-size: 1.05rem;
        line-height: 1;
        padding: 0 0 0 .15rem;
    }

    .orcha-cip-aktif button:hover {
        color: #fff;
    }

    .orcha-hitung {
        background: #eef6fb;
        color: #0f2d4a;
        font-weight: 700;
    }

    /* ---------- Itinerary per hari ---------- */
    .orcha-hari {
        padding: 1rem;
        border: 1px solid #e8eef5;
        border-radius: .9rem;
        background: #fbfcfe;
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
