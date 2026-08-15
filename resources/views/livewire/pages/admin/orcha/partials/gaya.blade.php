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
</style>
