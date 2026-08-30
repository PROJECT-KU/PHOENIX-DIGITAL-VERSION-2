{{-- Gaya lembar cek pembayaran dan daftar berkelompoknya.

     Dipisah ke partial karena dipakai dua halaman sejak lembar ceknya berdiri
     sendiri. Ditulis inline, bukan lewat Vite: public/build tidak ikut
     ter-deploy, jadi berkas gaya terpisah tidak akan sampai ke server. --}}
    <style>
        .orcha-cek-kepala {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.35rem;
            background: linear-gradient(135deg, var(--orc-primer), var(--orc-primer-2));
            color: #fff;
            border-radius: 1rem 1rem 0 0;
        }

        /* Ikon diratakan lewat flex, bukan vertical-align — lihat catatan pada
           .orcha-ikon-teks di partial gaya. */
        .orcha-cek-judul {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-weight: 700;
            font-size: 1.05rem;
            line-height: 1.2;
        }

        .orcha-cek-judul > i { line-height: 1; }

        .orcha-cek-kode {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .82rem;
            color: #cfe4f2;
            margin-top: .2rem;
        }

        .orcha-cek-pisah { opacity: .55; }

        .orcha-cek-tutup {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            font-size: .85rem;
            line-height: 1;
            transition: background .15s ease;
        }

        .orcha-cek-tutup:hover { background: rgba(255, 255, 255, .3); }

        /* Angka yang dicocokkan dengan mutasi rekening. Diberi ruang sendiri
           supaya tidak perlu dicari di antara keterangan lain. */
        .orcha-cek-nominal {
            padding: .9rem 1.1rem;
            border-radius: .9rem;
            background: linear-gradient(135deg, #f4f8fb, #e8f1f8);
            border-left: 4px solid var(--orc-primer);
        }

        .orcha-cek-nominal .angka {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--orc-tinta);
            line-height: 1.15;
            letter-spacing: -.01em;
        }

        .orcha-cek-tanggal {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .8rem;
            color: #5b7186;
            margin-top: .15rem;
        }

        .orcha-cek-tanggal > i { line-height: 1; }

        /* Ikonnya ditengahkan terhadap seluruh pasangan label-nilai, bukan
           digantung di baris pertama: yang dilihat mata sebagai satu kesatuan
           adalah kotak keterangannya, bukan barisnya satu per satu. */
        .orcha-cek-fakta {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: .88rem;
        }

        .orcha-cek-ikon {
            flex: 0 0 32px;
            width: 32px;
            height: 32px;
            border-radius: .6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef5fa;
            color: var(--orc-primer);
            font-size: .9rem;
        }

        .orcha-cek-catatan {
            padding: .75rem .9rem;
            border-radius: .7rem;
            background: #f7f9fb;
            border-left: 3px solid #cfdbe6;
            font-size: .85rem;
            color: #3c5468;
        }

        /* Bingkai bukti: tingginya dibatasi supaya struk yang panjang tidak
           mendorong tombol keputusan keluar layar. Utuhnya dilihat lewat
           pratinjau, yang memang untuk itu. */
        .orcha-cek-bukti {
            position: relative;
            border-radius: .9rem;
            overflow: hidden;
            border: 1px solid #e3ecf3;
            background: #f7f9fb;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 12rem;
            max-height: 26rem;
        }

        .orcha-cek-bukti img {
            max-width: 100%;
            max-height: 26rem;
            object-fit: contain;
        }

        .orcha-cek-perbesar {
            position: absolute;
            left: 50%;
            bottom: .65rem;
            transform: translateX(-50%);
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .7rem;
            border-radius: 2rem;
            background: rgba(31, 45, 61, .78);
            color: #fff;
            font-size: .74rem;
            white-space: nowrap;
        }

        .orcha-cek-kosong {
            border: 1px dashed #d5e1ea;
            border-radius: .9rem;
            padding: 2rem 1rem;
            text-align: center;
            background: #fafcfd;
        }

        .orcha-cek-putus {
            margin-top: 1.25rem;
            padding-top: 1.1rem;
            border-top: 1px solid #eef2f6;
        }

        .orcha-cek-pratayang > summary {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .82rem;
            font-weight: 600;
            color: var(--orc-primer);
            cursor: pointer;
        }

        .orcha-cek-pratayang > summary > i { line-height: 1; }

        .orcha-cek-pratayang pre {
            margin: .6rem 0 .5rem;
            padding: .85rem 1rem;
            border-radius: .7rem;
            border: 1px solid #e3ecf3;
            background: #f7f9fb;
            color: #24384a;
            font-family: inherit;
            font-size: .84rem;
            line-height: 1.55;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .orcha-cek-pratayang p {
            font-size: .78rem;
            color: #5b7186;
        }

        .orcha-cek-pilihan {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .orcha-cek-status { margin: 0; }

        .orcha-cek-status input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .orcha-cek-status span {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem .95rem;
            border-radius: .7rem;
            border: 1.5px solid #dbe7f0;
            background: #fff;
            color: #5b7186;
            font-size: .86rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s ease;
        }

        .orcha-cek-status span > i { line-height: 1; }

        .orcha-cek-status span:hover { border-color: #b9d0e2; }

        /* Warnanya baru muncul saat dipilih. Sebelum admin memutuskan, tidak
           ada pilihan yang pantas terlihat seperti sudah dipilih. */
        .orcha-cek-status-menunggu input:checked+span {
            border-color: #d99a19;
            background: #fdf6e7;
            color: #8a6110;
        }

        .orcha-cek-status-diterima input:checked+span {
            border-color: #1a8a52;
            background: #e9f7f0;
            color: #126b40;
        }

        .orcha-cek-status-ditolak input:checked+span {
            border-color: #c2323c;
            background: #fdecee;
            color: #9b2530;
        }

        .orcha-cek-status input:focus-visible+span {
            outline: 2px solid var(--orc-primer);
            outline-offset: 2px;
        }

        @media (max-width: 575.98px) {
            .orcha-cek-nominal .angka { font-size: 1.45rem; }

            .orcha-cek-pilihan { flex-direction: column; }

            .orcha-cek-status span { justify-content: center; }
        }
    </style>
