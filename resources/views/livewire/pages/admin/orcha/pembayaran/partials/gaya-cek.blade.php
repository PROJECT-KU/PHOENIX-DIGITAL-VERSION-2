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

        /* Pecahan nominal, di dalam kartu yang sama dengan angka besarnya.

           Ditaruh di sini, bukan di deretan fakta di bawah, karena
           pertanyaannya menempel pada angka itu sendiri: "858.889 ini
           berapa DP-nya?". Jawaban yang letaknya jauh dari pertanyaannya
           tetap membuat orang menerka. */
        .orcha-cek-pecahan {
            margin-top: .7rem;
            padding-top: .7rem;
            border-top: 1px dashed #c8dced;
        }

        .orcha-cek-pecahan .baris {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 1rem;
            font-size: .84rem;
            line-height: 1.9;
        }

        .orcha-cek-pecahan .baris > span:first-child {
            color: #5b7186;
        }

        .orcha-cek-pecahan .baris > span:last-child {
            font-weight: 700;
            color: var(--orc-tinta);
            white-space: nowrap;
        }

        /* Warna merek panelnya, bukan biru Orcha.

           Panel admin ini bertema lemon (ungu). Biru apa pun di tengahnya
           terbaca sebagai bug tampilan, bukan aksen — dan uji
           OrchaTampilanSeragamTest memang menjaganya. */
        .orcha-cek-pecahan .baris.unik > span:last-child {
            color: var(--orc-primer);
        }

        .orcha-cek-pecahan .tagihan {
            font-family: 'SFMono-Regular', Consolas, monospace;
            font-size: .74rem;
            color: #8398ab;
            margin-top: .45rem;
            word-break: break-all;
        }

        /* Kartu pemisah antar bagian.

           Untuk pembayaran gerbang, halamannya cuma satu kolom — dan satu
           kolom panjang tanpa pemisah terbaca sebagai satu paragraf raksasa.
           Kartu memberi mata tempat berhenti di antara "berapa", "siapa", dan
           "lalu bagaimana tagihannya". */
        .orcha-cek-kartu {
            padding: 1rem 1.1rem;
            border-radius: .9rem;
            border: 1px solid #e9eff5;
            background: #fff;
            margin-top: 1rem;
        }

        .orcha-cek-kartu > .kepala {
            font-size: .7rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #8398ab;
            font-weight: 700;
            margin-bottom: .7rem;
        }

        /* Keterangan kecil di dalam label pecahan: alasannya ikut terbaca,
           tanpa menuntut baris sendiri. */
        .orcha-cek-pecahan .baris em {
            font-style: normal;
            font-size: .76rem;
            color: #9aa9b8;
        }

        /* Pernyataan yang menutup pekerjaan. Hijau lembut, bukan hijau
           menyala: ini kabar baik yang sudah selesai, bukan peringatan. */
        .orcha-cek-beres {
            display: flex;
            align-items: flex-start;
            gap: .8rem;
            padding: .9rem 1.05rem;
            margin-top: 1rem;
            border-radius: .9rem;
            background: #f1faf5;
            border: 1px solid #cdeadd;
        }

        .orcha-cek-beres .ikon {
            flex: 0 0 auto;
            width: 1.9rem;
            height: 1.9rem;
            border-radius: 999px;
            background: #16a34a;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Jalurnya digambar simetris di viewBox 24x24, jadi menengahkannya
           cukup dengan memenuhi kotak — tanpa koreksi metrik font apa pun. */
        .orcha-cek-beres .ikon > svg {
            width: 62%;
            height: 62%;
            display: block;
        }

        .orcha-cek-beres .judul {
            margin: 0;
            font-weight: 700;
            font-size: .92rem;
            color: #14683f;
        }

        .orcha-cek-beres .isi {
            margin: .2rem 0 0;
            font-size: .84rem;
            line-height: 1.6;
            color: #3f6b56;
        }

        /* Posisi tagihan: tiga angka berdampingan, sisa dibedakan warnanya
           karena itu satu-satunya yang menuntut tindakan berikutnya. */
        .orcha-cek-posisi {
            margin-top: 1.15rem;
        }

        .orcha-cek-posisi .kotak {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .5rem;
        }

        .orcha-cek-posisi .sel {
            padding: .65rem .8rem;
            border-radius: .75rem;
            background: #f6f9fc;
            border: 1px solid #e5edf4;
        }

        .orcha-cek-posisi .lbl {
            display: block;
            font-size: .68rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #8398ab;
            font-weight: 700;
        }

        .orcha-cek-posisi .nil {
            display: block;
            margin-top: .15rem;
            font-size: .95rem;
            font-weight: 800;
            color: var(--orc-tinta);
            letter-spacing: -.01em;
        }

        .orcha-cek-posisi .sel.masuk .nil { color: var(--orc-primer); }
        .orcha-cek-posisi .sel.sisa { background: #fffaf0; border-color: #f2e0b8; }
        .orcha-cek-posisi .sel.sisa .nil { color: #96590d; }
        .orcha-cek-posisi .sel.lunas { background: #f1faf5; border-color: #cdeadd; }
        .orcha-cek-posisi .sel.lunas .nil { color: #14683f; }

        @media (max-width: 575.98px) {
            .orcha-cek-posisi .kotak { grid-template-columns: 1fr; }
        }

        /* Formulir keputusan yang dilipat.

           Ringkasannya sengaja terlihat seperti tautan tenang, bukan tombol:
           yang membukanya harus orang yang memang mencarinya, bukan orang yang
           kebetulan menyapu halaman dengan mata. */
        .orcha-cek-lanjut {
            margin-top: 1.4rem;
            border-top: 1px solid #eef2f7;
            padding-top: 1rem;
        }

        .orcha-cek-lanjut > summary {
            cursor: pointer;
            list-style: none;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .84rem;
            font-weight: 600;
            color: #6b7f92;
        }

        .orcha-cek-lanjut > summary::-webkit-details-marker { display: none; }
        .orcha-cek-lanjut > summary:hover { color: var(--orc-primer); }

        .orcha-cek-lanjut .ket {
            margin: .5rem 0 0;
            font-size: .8rem;
            color: #8398ab;
            line-height: 1.6;
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
        /* Ikon disejajarkan ke ATAS blok teks, bukan ke tengahnya.

           Menengahkannya benar selama semua blok setinggi sama. Begitu satu
           fakta punya baris ketiga — "Pemesan" membawa nomor telepon —
           tengahnya bergeser turun, dan ikon di kolom kiri tidak lagi sebaris
           dengan ikon di kolom kanan pada baris yang sama. Diukur dengan garis
           bantu: selisihnya 13px, cukup untuk terbaca sebagai tidak rapi
           meski tidak ada yang bisa menunjuk apa persisnya.

           Disejajarkan ke atas, ikon selalu bertemu label — dan label selalu
           ada, berapa pun baris di bawahnya. Nudge 2px menengahkannya terhadap
           pasangan label+nilai, bukan terhadap label saja. */
        .orcha-cek-fakta {
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            font-size: .88rem;
        }

        .orcha-cek-fakta > .orcha-cek-ikon {
            margin-top: 2px;
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

        /* Penanda bahwa catatannya akan terbaca pelanggan.

           Merah, dan hanya muncul saat Ditolak dipilih. Peringatan yang selalu
           tampil akan diabaikan dalam seminggu; yang muncul tepat pada saat
           bahayanya ada masih terbaca setahun kemudian. */
        .orcha-catatan-terbaca {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            margin-left: .15rem;
            padding: .1rem .45rem;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #f6cccc;
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
