{{--
    Gaya medan formulir vendor — DIPAKAI BERSAMA tiga layar.

    Dulu aturan ini hidup di dalam <style> halaman daftar saja, padahal
    partial medan-nya dipakai juga oleh halaman "Vendor Baru" dan halaman
    rincian. Di dua layar itu .vd-medan-rak tidak punya aturan sama sekali,
    jadi tiap medan jatuh selebar penuh satu per baris dan separuh layar
    menganga kosong — tanpa galat, tanpa gejala selain "kok renggang".

    @once supaya tidak tertulis dua kali bila satu halaman memuat partialnya
    lebih dari sekali.
--}}
@once
    <style>
        /* Medan berbagi baris; yang panjang minta satu baris penuh sendiri. */
        .vd-medan-rak { display: flex; flex-wrap: wrap; gap: 14px 16px; align-items: flex-start; }
        .vd-medan-rak > .dsb-medan { flex: 1 1 240px; min-width: 0; }
        .vd-medan-rak > .dsb-medan.is-lebar { flex-basis: 100%; }
        .vd-medan-rak .dsb-kartu-sub,
        .vd-medan-rak .dsb-galat { display: block; margin-top: 5px; }
        .vd-medan-rak .dsb-label-ket { margin-left: 4px; font-weight: 500; }

        /* Pilihan jenis: chip melar berbagi baris dengan kotak ketiknya, jadi
           barisnya habis terpakai berapa pun jumlah chipnya. */
        .vd-jenis { display: flex; flex-wrap: wrap; gap: 6px; }
        .vd-jenis-chip {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            flex: 1 1 120px; padding: 8px 11px; border-radius: 10px; cursor: pointer;
            background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3);
            color: var(--dsb-tinta, #1c1f26); font-size: .79rem; font-weight: 600; white-space: nowrap;
        }
        .vd-jenis-chip i { color: var(--c, #64748b); }
        .vd-jenis-chip:hover { border-color: #c7d2fe; }
        .vd-jenis-chip.is-aktif { border-color: var(--c, #4f46e5); background: #f8fafc; box-shadow: 0 0 0 3px rgba(99, 102, 241, .08); }
        .vd-jenis-lain { display: flex; align-items: center; gap: 8px; margin-top: 7px; }
        .vd-jenis-lain .dsb-kartu-sub { flex: 0 0 auto; }
        .vd-jenis-isi { flex: 1 1 auto; min-width: 0; }
        .vd-jenis-asing { display: flex; align-items: flex-start; gap: 6px; margin-top: 6px; }

        .vd-sakelar { margin-top: 14px; }
        .vd-kaki { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; }
    </style>
@endonce
