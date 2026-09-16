{{-- Gaya bersama untuk kedua pembanding (<x-banding-harian> & <x-banding-periode>).

     Ditulis di komponennya sendiri, bukan menumpang gaya dasbor: pembanding
     harian juga dipakai layar Cash Flow, yang tidak memuat berkas gaya dasbor
     sama sekali — kalau gayanya dititipkan ke sana, di Cash Flow ia tampil
     sebagai teks telanjang.

     Inline: public/build masuk .gitignore dan tidak ikut terdeploy. --}}
@once
<style>
    .bnd {
        display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
        margin-top: 8px; line-height: 1.4;
    }

    /* Angka perubahan dijadikan PIL berwarna, bukan sekadar teks berwarna:
       inilah satu-satunya bagian baris ini yang dibaca sekilas — apakah
       angkanya membaik atau memburuk — dan sebagai teks biasa ia tenggelam di
       antara nominal pembandingnya. */
    .bnd-pil {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 9px; border-radius: 999px;
        font-size: .72rem; font-weight: 800; letter-spacing: .01em; white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .bnd-pil i.bi { font-size: .78rem; line-height: 1; }
    .bnd-pil i.bi::before { display: block; line-height: 1; }

    /* Warnanya mengikuti BAIK/BURUK, bukan naik/turun — pengeluaran yang naik
       bukan kabar baik. Pemanggilnya yang memilih kelasnya. */
    .bnd-pil.is-baik { background: #dcfce7; color: #15803d; }
    .bnd-pil.is-buruk { background: #fee2e2; color: #b91c1c; }
    .bnd-pil.is-datar { background: #f1f5f9; color: #64748b; }

    .bnd-ket { color: #94a3b8; font-size: .73rem; }
    .bnd-ket b { color: #64748b; font-weight: 700; font-variant-numeric: tabular-nums; }
</style>
@endonce
