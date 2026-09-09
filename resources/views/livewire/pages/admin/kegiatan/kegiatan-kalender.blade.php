@section('title')
Kalender Kegiatan || lemon
@stop

<div>
    <style>
        /* Ditulis inline: public/build tidak ikut terdeploy ke server. */

        /* ===== Tombol =====
           Layout admin menyetel `.btn .bi { display: flex }` untuk SEMUA tombol.
           Kotak flex itu elemen blok, jadi setiap ikon di dalam tombol turun ke
           barisnya sendiri — itulah sebab "Tambah" dan "Hari ini" tampil dua
           baris dengan tinggi yang tidak seragam.

           Perbaikannya bukan melawan aturan itu, melainkan menjadikan TOMBOLNYA
           wadah flex: ikon dan teks lalu berdiri sebagai dua item sebaris,
           rata tengah, dengan jarak dari `gap` (bukan margin) sehingga sisa
           ruang kiri-kanan tetap seimbang. */
        .kg-btn {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 7px;
            line-height: 1;
            white-space: nowrap;
        }
        .kg-btn i.bi { font-size: 1rem !important; line-height: 1; flex: 0 0 auto; }
        .kg-btn i.bi::before { display: block; line-height: 1; }
        /* Jarak sudah dipegang gap; margin bawaan hanya menggeser teks dari tengah. */
        .kg-btn i.me-1 { margin-right: 0 !important; }

        /* Semua tombol batang alat setinggi sama persis, apa pun isinya. */
        .kg-alat .kg-btn { height: 42px; padding: 0 14px !important; }
        .kg-alat .kg-nav { width: 42px; padding: 0 !important; }
        /* Panah sendirian tanpa teks pendamping terbaca lebih tipis daripada
           ikon yang berdampingan dengan tulisan; diperbesar sedikit agar
           bobotnya terasa sama dengan "Hari ini" di sebelahnya. */
        .kg-alat .kg-nav i.bi { font-size: 1.15rem !important; }

        /* Navigasi bulan & "Hari ini": satu kelompok berwarna ungu lembut.
           Sengaja TIDAK ungu pekat — "Tambah" adalah aksi utama halaman ini dan
           harus tetap yang paling menonjol. Nada lembut memakai bahasa yang sama
           dengan chip legenda: lembut berarti tersedia, pekat berarti terpilih. */
        .kg-jelajah {
            background: #f3ecfe !important;
            border: 1px solid #e6d9fb !important;
            color: #6d28d9 !important;
            box-shadow: 0 2px 6px -2px rgba(109,40,217,.18);
        }
        .kg-jelajah:hover {
            background: #e9dcfd !important;
            border-color: #d8c4f8 !important;
            color: #5b21b6 !important;
            box-shadow: 0 6px 14px -5px rgba(109,40,217,.42);
        }
        .kg-jelajah:focus-visible { outline: 2px solid #a78bfa; outline-offset: 2px; }

        /* Tombol kecil di dalam kartu. `.btn { padding: 10px 20px !important }`
           dari layout berlaku juga untuk .btn-sm, jadi tingginya harus disetel
           tegas di sini — kalau tidak, tombol "kecil" sama besar dengan yang biasa. */
        .kg-btn-kecil { height: 34px; padding: 0 13px !important; font-size: .81rem; }
        .kg-btn-kecil i.bi { font-size: .88rem !important; }

        /* Ubah & Hapus tidak boleh sama-sama abu-abu. Keduanya berdampingan dan
           hanya salah satunya yang tak bisa dibatalkan; warna netral membuat
           tangan bergerak ke keduanya dengan kepercayaan diri yang sama.
           Merahnya sudah terlihat sebelum kursor menyentuhnya. */
        .kg-ubah {
            background: #f3ecfe !important; border: 1px solid #e6d9fb !important; color: #6d28d9 !important;
        }
        .kg-ubah:hover { background: #e9dcfd !important; border-color: #d8c4f8 !important; color: #5b21b6 !important; }

        .kg-hapus {
            background: #ffeef1 !important; border: 1px solid #ffd8df !important; color: #e11d48 !important;
        }
        .kg-hapus:hover { background: #ffe0e6 !important; border-color: #ffc2cd !important; color: #be123c !important; }

        /* ===== Ringkasan kepala halaman ===== */
        .kg-ringkas-teks { line-height: 1.35; text-align: right; }
        .kg-ringkas-teks b { display: block; font-size: .98rem; color: #1e293b; }
        .kg-ringkas-teks small { color: #94a3b8; }

        /* ===== Batang alat ===== */
        .kg-bulan {
            min-width: 148px; text-align: center; font-weight: 700;
            font-size: 1rem; color: #1e293b; letter-spacing: -.01em;
        }

        /* Legenda jenis. Saat tidak terpilih chip memakai warna JENISNYA sendiri
           dalam nada lembut — bukan putih dengan titik kecil. Dengan begitu
           batang saring sekaligus menjadi legenda: warna di chip sama persis
           dengan warna kegiatannya di kisi. */
        /* Tetap satu baris bersama navigasi bulan. Ukurannya dirampingkan
           secukupnya agar tujuh chip muat di sisa ruang sebelah sidebar; masih
           boleh membungkus di layar sempit, tapi tidak lagi pada lebar biasa. */
        .kg-saring { display: flex; flex-wrap: wrap; gap: 6px; }

        /* Saat legenda duduk di kanan, baris yang terpaksa membungkus ikut rata
           kanan — kalau rata kiri, sisanya terlihat menggantung di tengah kartu. */
        @media (min-width: 992px) { .kg-saring { justify-content: flex-end; } }
        .kg-chip {
            border: 1px solid transparent; border-radius: 999px;
            padding: 0 13px; height: 42px;
            font-size: .81rem; font-weight: 600;
            display: inline-flex; align-items: center; gap: 6px;
            cursor: pointer; transition: transform .12s ease, box-shadow .15s ease, filter .15s ease;
            background: var(--kg-lembut); color: var(--kg-warna);
        }
        .kg-chip i.bi { font-size: .9rem; line-height: 1; }
        .kg-chip i.bi::before { display: block; line-height: 1; }
        .kg-chip:hover { filter: brightness(.97); }
        .kg-chip.aktif {
            background: var(--kg-warna); color: #fff;
            box-shadow: 0 6px 14px -4px var(--kg-warna);
        }
        .kg-chip:active { transform: scale(.97); }
        .kg-titik { width: 9px; height: 9px; border-radius: 50%; flex: 0 0 auto; background: var(--kg-warna); }
        /* Keterangan, bukan tombol: tidak menanggapi kursor sama sekali. */
        .kg-chip-mati { cursor: default; }
        .kg-chip-mati:hover { filter: none; }

        .kg-catatan-libur {
            display: flex; align-items: flex-start; gap: 8px;
            margin: 14px 0 0; padding-top: 13px; border-top: 1px solid #f1f4f8;
            font-size: .78rem; line-height: 1.6; color: #94a3b8;
        }
        .kg-catatan-libur i.bi { flex: 0 0 auto; margin-top: .18em; line-height: 1; }
        .kg-catatan-libur i.bi::before { display: block; line-height: 1; }
        .kg-catatan-libur b { color: #64748b; }
        .kg-chip.aktif .kg-titik { background: rgba(255,255,255,.9); }

        /* ===== Kisi kalender =====
           Sel hari jadi latar; balok kegiatan duduk di lapisan sendiri di
           atasnya dan bebas melintasi kolom. Semua ukuran diturunkan dari tiga
           angka di bawah ini, jadi mengubah tinggi sel atau jarak antar sel
           tidak akan membuat balok meleset dari kolomnya. */
        .kg-kisi-ukuran {
            --kg-jarak: 7px;      /* jarak antar sel */
            --kg-sisip: 7px;      /* jarak balok dari tepi sel */
            --kg-atas: 36px;      /* ruang untuk angka tanggal */
            --kg-tinggi: 20px;    /* tinggi satu balok */
            --kg-antar: 4px;      /* jarak antar balok */
        }

        .kg-kepala-hari {
            display: grid; grid-template-columns: repeat(7, 1fr); gap: var(--kg-jarak);
            padding: 0 0 6px;
        }
        .kg-kepala-hari span {
            text-align: center; font-size: .72rem; text-transform: uppercase;
            letter-spacing: .07em; color: #a8b3c4; font-weight: 700;
        }

        .kg-minggu {
            position: relative;
            display: grid; grid-template-columns: repeat(7, 1fr); gap: var(--kg-jarak);
            margin-bottom: var(--kg-jarak);
        }
        .kg-minggu:last-child { margin-bottom: 0; }

        .kg-sel {
            min-height: calc(var(--kg-atas) + 3 * (var(--kg-tinggi) + var(--kg-antar)) + 16px);
            padding: 8px;
            border: 1px solid #eef1f6; border-radius: 16px; background: #fff;
            cursor: pointer;
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .kg-sel:hover { border-color: #ddd8fb; box-shadow: 0 6px 16px -6px rgba(109,40,217,.28); }
        .kg-sel.kg-luar { background: #fbfcfd; border-color: #f2f5f8; }
        .kg-sel.kg-luar .kg-angka { color: #cbd5e1; }
        .kg-sel.kg-pekan { background: #fcfcfe; }
        /* Hari yang dipakai kegiatan ikut berbingkai warna kegiatannya, sehingga
           8-9 September terbaca sebagai satu blok dan bukan dua kotak terpisah.
           Latarnya hanya disapu tipis — kalau sepekat baloknya, judul kegiatan
           di atasnya justru tenggelam. */
        .kg-sel.kg-terpakai {
            border-color: var(--kg-tepi);
            background: var(--kg-tepi-lembut);
        }
        .kg-sel.kg-terpakai:hover {
            border-color: var(--kg-tepi);
            box-shadow: 0 6px 16px -6px var(--kg-tepi);
        }

        /* Tanggal terpilih tetap menang: itu jawaban atas klik yang baru saja
           dilakukan, dan harus terlihat betapa pun ramainya kegiatan hari itu. */
        .kg-sel.kg-terpilih,
        .kg-sel.kg-terpakai.kg-terpilih { border-color: #a78bfa; box-shadow: 0 0 0 3px rgba(167,139,250,.22); }
        .kg-angka {
            font-size: .82rem; font-weight: 700; color: #64748b;
            width: 27px; height: 27px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 50%; line-height: 1;
        }
        .kg-sel.kg-hariini .kg-angka {
            background: linear-gradient(135deg, #a78bfa, #6d28d9); color: #fff;
            box-shadow: 0 4px 10px -2px rgba(109,40,217,.45);
        }
        .kg-lebih {
            position: absolute; left: 8px; right: 8px; bottom: 7px;
            font-size: .7rem; color: #a8b3c4; font-weight: 600;
        }

        /* ===== Hari libur & peringatan nasional =====
           Tanggal merah memakai MERAH, bukan warna jenis kegiatan mana pun:
           itu satu-satunya konvensi kalender yang sudah dipahami semua orang
           tanpa perlu melihat legenda. */
        .kg-sel.kg-merah .kg-angka { color: #dc2626; }
        .kg-sel.kg-merah { border-color: #f8d4d4; }
        .kg-sel.kg-merah.kg-terpakai { border-color: var(--kg-tepi); }

        /* Nama peringatan ditulis kecil di bawah angka. Dipotong satu baris —
           sel harus tetap seukuran tetangganya berapa pun panjang namanya. */
        .kg-tanda {
            display: block; margin-top: 2px; font-size: .64rem; line-height: 1.25;
            font-weight: 700; color: #94a3b8;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .kg-tanda-libur { color: #dc2626; }
        .kg-sel { position: relative; }

        /* Balok kegiatan. Lebar satu kolom = (100% - 6 jarak) / 7; sisi kirinya
           digeser sebanyak kolom yang dilewati, ditambah jarak antar sel yang
           ikut terlewati. Itu sebabnya baloknya menempel persis di atas selnya
           berapa pun lebar layarnya. */
        .kg-balok {
            --kg-lebar-kolom: calc((100% - 6 * var(--kg-jarak)) / 7);
            position: absolute;
            left: calc(var(--kg-kolom) * (var(--kg-lebar-kolom) + var(--kg-jarak)) + var(--kg-sisip));
            width: calc(var(--kg-rentang) * var(--kg-lebar-kolom)
                        + (var(--kg-rentang) - 1) * var(--kg-jarak)
                        - 2 * var(--kg-sisip));
            top: calc(var(--kg-atas) + var(--kg-lajur) * (var(--kg-tinggi) + var(--kg-antar)));
            height: var(--kg-tinggi);

            display: flex; align-items: center; gap: 5px;
            padding: 0 8px; border: 0; border-radius: 6px;
            font-size: .73rem; font-weight: 600; text-align: left;
            background: var(--kg-lembut); color: var(--kg-warna);
            border-left: 3px solid var(--kg-warna);
            transition: filter .12s ease, box-shadow .15s ease;
        }
        .kg-balok span {
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;
        }
        .kg-balok:hover { filter: brightness(.96); box-shadow: 0 4px 10px -4px var(--kg-warna); }
        .kg-balok.kg-lewat { opacity: .5; }

        /* Terpotong batas minggu: ujungnya dibuat rata, bukan membulat. Ujung
           membulat berarti "selesai di sini", dan kegiatannya belum selesai. */
        .kg-balok.kg-sambung-kiri {
            border-left: 0; border-top-left-radius: 0; border-bottom-left-radius: 0;
            left: calc(var(--kg-kolom) * (var(--kg-lebar-kolom) + var(--kg-jarak)));
            width: calc(var(--kg-rentang) * var(--kg-lebar-kolom)
                        + (var(--kg-rentang) - 1) * var(--kg-jarak) - var(--kg-sisip));
            box-shadow: inset 3px 0 0 var(--kg-warna);
        }
        .kg-balok.kg-sambung-kanan {
            border-top-right-radius: 0; border-bottom-right-radius: 0;
            width: calc(var(--kg-rentang) * var(--kg-lebar-kolom)
                        + (var(--kg-rentang) - 1) * var(--kg-jarak) - var(--kg-sisip));
        }
        .kg-balok.kg-sambung-kiri.kg-sambung-kanan {
            left: calc(var(--kg-kolom) * (var(--kg-lebar-kolom) + var(--kg-jarak)));
            width: calc(var(--kg-rentang) * var(--kg-lebar-kolom)
                        + (var(--kg-rentang) - 1) * var(--kg-jarak));
        }

        /* ===== Daftar hari terpilih & agenda ===== */
        .kg-judul-kartu { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0; }
        /* Tanpa bingkai dan bayangan: ini kartu di dalam kartu, dan kotak di
           dalam kotak membuat halaman terasa penuh padahal isinya sedikit.
           Cukup pita warna di kiri sebagai penanda, dan garis rambut sebagai
           pemisah — sama seperti kartu Agenda Terdekat di sebelahnya. */
        .kg-baris {
            display: flex; gap: 12px; padding: 14px 2px;
            border-bottom: 1px solid #f2f5f9;
        }
        .kg-baris:last-child { border-bottom: 0; padding-bottom: 2px; }
        /* Pita memakai NADA kegiatan itu, agar sebuah kegiatan berwarna sama di
           kisi maupun di daftar. Lencana di sebelahnya tetap warna jenis murni —
           dialah yang harus selalu cocok dengan chip legenda. */
        .kg-pita { width: 4px; border-radius: 999px; flex: 0 0 auto; background: var(--kg-nada, var(--kg-warna)); }
        .kg-baris-isi { flex: 1 1 auto; min-width: 0; }
        .kg-baris-isi b { display: block; font-size: .93rem; color: #1e293b; line-height: 1.35; }
        .kg-meta { font-size: .78rem; color: #94a3b8; display: flex; flex-wrap: wrap; gap: 3px 14px; margin-top: 4px; }
        .kg-meta span { display: inline-flex; align-items: center; gap: 5px; }
        .kg-meta i.bi { line-height: 1; font-size: .85rem; }
        .kg-meta i.bi::before { display: block; line-height: 1; }
        .kg-lencana {
            font-size: .7rem; font-weight: 700; padding: 3px 10px; border-radius: 999px;
            display: inline-flex; align-items: center; gap: 5px; flex: 0 0 auto;
            background: var(--kg-lembut); color: var(--kg-warna);
        }
        .kg-lencana i.bi { font-size: .78rem; line-height: 1; }
        .kg-lencana i.bi::before { display: block; line-height: 1; }
        .kg-catatan { font-size: .82rem; color: #64748b; line-height: 1.6; margin-top: 7px; white-space: pre-line; }
        .kg-peserta { font-size: .75rem; color: #64748b; margin-top: 7px; display: flex; align-items: center; gap: 6px; }
        .kg-peserta i.bi { line-height: 1; flex: 0 0 auto; }
        .kg-peserta i.bi::before { display: block; line-height: 1; }

        .kg-kosong { text-align: center; padding: 34px 14px; color: #a8b3c4; font-size: .87rem; }

        /* ===== Agenda terdekat ===== */
        .kg-agenda-baris {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 2px; border-bottom: 1px solid #f2f5f9;
        }
        .kg-agenda-baris:last-child { border-bottom: 0; padding-bottom: 2px; }
        .kg-agenda-titik { width: 8px; height: 8px; border-radius: 50%; background: var(--kg-nada); flex: 0 0 auto; }
        .kg-agenda-nama {
            flex: 1 1 auto; min-width: 0; font-size: .87rem; color: #1e293b; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        /* Waktu tidak ikut menyusut: judul yang terpotong masih bisa ditebak dari
           beberapa huruf pertama, tapi "10 Sep" yang terpotong tidak memberi tahu
           apa pun. */
        .kg-agenda-waktu { flex: 0 0 auto; font-size: .78rem; color: #94a3b8; white-space: nowrap; }

        /* ===== Modal =====
           Latar TIDAK bergulir: yang bergulir hanya isi modalnya, dengan kepala
           dan kaki dipatok. `overscroll-behavior: contain` menahan gulirannya
           agar tidak merembet ke halaman di belakang begitu isinya mentok. */
        .kg-modal-latar {
            position: fixed; inset: 0; z-index: 1055;
            background: rgba(15,23,42,.5);
            backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            padding: 24px 14px;
            overscroll-behavior: contain;
        }
        .kg-modal {
            width: 100%; max-width: 640px;
            max-height: calc(100vh - 48px);
            display: flex; flex-direction: column;
            background: #fff; border-radius: 20px; overflow: hidden;
            box-shadow: 0 24px 60px -12px rgba(15,23,42,.4);
        }
        /* flex-basis 0 + min-height 0 wajib: tanpa keduanya form ikut memanjang
           mengikuti isinya, kaki modal terdorong keluar layar, dan yang bergulir
           kembali jadi halamannya — persis yang hendak dihindari. */
        .kg-modal-badan { display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; }

        /* Kepala memakai warna jenis yang sedang dipilih. */
        .kg-modal-kepala {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 22px; flex: 0 0 auto;
            background: var(--kg-warna);
            background-image: linear-gradient(180deg, rgba(255,255,255,.18), transparent 70%);
            color: #fff;
        }
        .kg-modal-tanda {
            width: 42px; height: 42px; flex: 0 0 auto;
            display: flex; align-items: center; justify-content: center;
            border-radius: 13px; background: rgba(255,255,255,.22);
        }
        .kg-modal-tanda i.bi { font-size: 1.15rem; line-height: 1; }
        .kg-modal-tanda i.bi::before { display: block; line-height: 1; }
        .kg-modal-tajuk { flex: 1 1 auto; min-width: 0; line-height: 1.3; }
        .kg-modal-tajuk b { display: block; font-size: 1.08rem; }
        .kg-modal-tajuk small { opacity: .82; font-size: .8rem; }
        .kg-modal-tutup {
            width: 36px; height: 36px; flex: 0 0 auto; border: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: 11px; background: rgba(255,255,255,.18); color: #fff;
            transition: background .15s ease;
        }
        .kg-modal-tutup:hover { background: rgba(255,255,255,.32); }
        .kg-modal-tutup i.bi { font-size: .9rem; line-height: 1; }
        .kg-modal-tutup i.bi::before { display: block; line-height: 1; }

        .kg-modal-isi { padding: 22px; overflow-y: auto; overscroll-behavior: contain; min-height: 0; }
        .kg-modal-kaki {
            display: flex; justify-content: flex-end; gap: 10px;
            padding: 16px 22px; flex: 0 0 auto;
            background: #f8fafc; border-top: 1px solid #eef1f6;
        }
        /* Tombol simpan ikut warna jenis — sekaligus penegas terakhir sebelum
           kegiatannya benar-benar tersimpan sebagai jenis itu. */
        .kg-simpan {
            background: var(--kg-warna) !important; border: 0 !important; color: #fff !important;
            box-shadow: 0 8px 18px -6px var(--kg-warna);
        }
        .kg-simpan:hover { filter: brightness(1.06); color: #fff !important; }

        /* Panel waktu: empat isian yang saling terkait dikumpulkan jadi satu. */
        .kg-panel { background: var(--kg-lembut); border-radius: 16px; padding: 16px; }
        .kg-panel-judul {
            display: flex; justify-content: space-between; align-items: center;
            gap: 12px; margin-bottom: 14px;
        }
        .kg-panel-judul > span {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: .78rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; color: var(--kg-warna);
        }
        .kg-panel-judul i.bi { line-height: 1; font-size: .9rem; }
        .kg-panel-judul i.bi::before { display: block; line-height: 1; }
        .kg-panel .form-check-label { font-size: .84rem; color: #475569; font-weight: 600; }
        .kg-panel .form-control { background: #fff; }
        .kg-bisik { display: block; font-size: .74rem; color: #94a3b8; margin-top: 4px; }

        .kg-pilih-jenis { display: flex; flex-wrap: wrap; gap: 8px; }
        .kg-pilih-jenis .kg-chip { padding: 0 15px; font-size: .84rem; }

        .kg-hitung {
            font-size: .73rem; font-weight: 700; padding: 3px 10px; border-radius: 999px;
            background: var(--kg-lembut); color: var(--kg-warna);
        }
        .kg-peserta-kotak {
            max-height: 200px; overflow-y: auto; overscroll-behavior: contain;
            border: 1px solid #e9edf3; border-radius: 14px; padding: 6px;
        }
        .kg-peserta-baris {
            display: flex; align-items: center; gap: 10px;
            padding: 7px 10px; border-radius: 10px; cursor: pointer;
            font-size: .87rem; color: #334155; transition: background .12s ease;
        }
        .kg-peserta-baris:hover { background: #f6f8fb; }
        .kg-peserta-baris input:checked + span { color: var(--kg-warna); font-weight: 600; }

        @media (max-width: 575.98px) {
            .kg-modal-latar { padding: 12px 10px; }
            .kg-modal { max-height: calc(100vh - 24px); border-radius: 16px; }
            .kg-modal-isi { padding: 16px; }
            .kg-modal-kaki { padding: 14px 16px; }
        }

        @media (max-width: 767.98px) {
            .kg-kisi-ukuran { --kg-jarak: 4px; --kg-sisip: 4px; --kg-atas: 28px; --kg-tinggi: 6px; --kg-antar: 3px; }
            .kg-sel { padding: 5px; border-radius: 12px; }
            /* Di layar sempit judulnya mustahil terbaca; baloknya disederhanakan
               jadi pita warna saja, yang tetap memberi tahu ADA kegiatan, jenisnya,
               dan sampai tanggal berapa ia membentang. */
            .kg-balok { font-size: 0; padding: 0; border-radius: 999px; border-left: 0; background: var(--kg-warna); gap: 0; }
            .kg-balok.kg-sambung-kiri { box-shadow: none; }
            .kg-lebih { display: none; }
            .kg-bulan { min-width: 0; flex: 1 1 auto; font-size: .95rem; }
            .kg-alat .kg-btn { height: 40px; padding: 0 13px !important; }
            .kg-alat .kg-nav { width: 40px; }
            .kg-chip { height: 38px; font-size: .8rem; padding: 0 13px; }
        }
    </style>

    @php
        $jenisPeta = \App\Models\Kegiatan::JENIS;
    @endphp

    <div class="page-heading">
        {{-- ===== Kepala halaman ===== --}}
        <div class="page-title mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="gradient-text fw-bold mb-1">Kalender Kegiatan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Kalender Kegiatan']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 kg-alat">
                        <div class="kg-ringkas-teks">
                            <b>{{ $jumlahBulanIni }} kegiatan</b>
                            <small>{{ $namaBulan }}</small>
                        </div>

                        @if ($this->bolehTambah)
                        <button type="button" class="btn btn-primary kg-btn" wire:click="buatBaru">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Batang alat ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 kg-alat">

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn kg-nav kg-btn kg-jelajah" wire:click="bulanSebelumnya"
                            aria-label="Bulan sebelumnya">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span class="kg-bulan">{{ $namaBulan }}</span>
                        <button type="button" class="btn kg-nav kg-btn kg-jelajah" wire:click="bulanBerikutnya"
                            aria-label="Bulan berikutnya">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <button type="button" class="btn kg-btn kg-jelajah ms-1" wire:click="keHariIni">
                            <i class="bi bi-calendar-check-fill"></i> Hari ini
                        </button>
                    </div>

                    <div class="kg-saring">
                        <button type="button"
                            class="kg-chip {{ $saringJenis === '' ? 'aktif' : '' }}"
                            style="--kg-warna:#6d28d9; --kg-lembut:#f2ecfd;"
                            wire:click="$set('saringJenis', '')">
                            Semua
                        </button>
                        @foreach ($jenisPeta as $kunci => $j)
                        <button type="button"
                            class="kg-chip {{ $saringJenis === $kunci ? 'aktif' : '' }}"
                            style="--kg-warna:{{ $j['warna'] }}; --kg-lembut:{{ $j['lembut'] }};"
                            wire:click="$set('saringJenis', '{{ $kunci }}')">
                            <span class="kg-titik"></span>
                            {{ $j['label'] }}
                        </button>
                        @endforeach

                        <button type="button"
                            class="kg-chip {{ $hanyaSaya ? 'aktif' : '' }}"
                            style="--kg-warna:#0f766e; --kg-lembut:#e6f4f2;"
                            wire:click="$toggle('hanyaSaya')">
                            <i class="bi bi-person-check-fill"></i> Saya saja
                        </button>

                        {{-- Keterangan, bukan saringan: tanggal merah bukan kegiatan
                             yang bisa disembunyikan. Dibuat tidak bisa diklik supaya
                             tidak ada yang mencoba menyaringnya. --}}
                        <span class="kg-chip kg-chip-mati" style="--kg-warna:#dc2626; --kg-lembut:#fef2f2;">
                            <span class="kg-titik"></span> Libur nasional
                        </span>
                    </div>

                </div>

                {{-- Berterus terang, bukan diam-diam: libur yang tanggalnya
                     berpindah tiap tahun (Idul Fitri, Nyepi, Waisak, dan
                     seterusnya) ditetapkan lewat SKB dan TIDAK dihitung sendiri
                     oleh sistem. Menghitungnya lewat konversi kalender akan
                     menghasilkan tanggal yang meyakinkan tapi bisa meleset
                     sehari — dan orang mengatur cuti serta tenggat dari
                     kalender ini. Selama belum diisi, lebih baik dikatakan. --}}
                @unless ($bergerakTerisi)
                <p class="kg-catatan-libur">
                    <i class="bi bi-info-circle"></i>
                    {{-- Seluruh kalimat dibungkus SATU span: pembungkusnya flex, dan tanpa
                         ini setiap elemen di dalamnya (termasuk <b>) jadi item flex
                         tersendiri lalu terlempar ke ujung baris. --}}
                    <span>
                        Tanggal merah yang ditandai baru yang jatuh pada tanggal tetap tiap tahun.
                        Libur yang tanggalnya berpindah — Idul Fitri, Nyepi, Waisak, dan sejenisnya —
                        belum diisi untuk {{ $tahun }}; tambahkan sebagai kegiatan berjenis
                        <b>Libur</b> bila diperlukan.
                    </span>
                </p>
                @endunless
            </div>
        </div>

        {{-- ===== Kisi kalender =====
             Bukan tabel: kegiatan yang membentang beberapa hari harus tampil
             sebagai SATU balok melintasi kolom-kolomnya. Di dalam tabel, satu
             baris kegiatan mau tak mau terpotong oleh dinding tiap sel. Karena
             itu sel harinya menjadi latar, dan baloknya diletakkan di lapisan
             sendiri di atasnya. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-2 p-md-3 kg-kisi-ukuran">
                <div class="kg-kepala-hari">
                    @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $h)
                    <span>{{ $h }}</span>
                    @endforeach
                </div>

                @foreach ($minggu as $iMinggu => $m)
                <div class="kg-minggu" wire:key="minggu-{{ $iMinggu }}">

                    {{-- Lapisan bawah: sel hari, yang bisa diklik --}}
                    @foreach ($m['hari'] as $iHari => $sel)
                    @php
                        $tepi = $m['tepi'][$iHari];
                        $tanda = $sel['penanda'];
                        $merah = $tanda && $tanda['libur'];
                    @endphp
                    <div class="kg-sel
                                {{ $sel['bulanIni'] ? '' : 'kg-luar' }}
                                {{ $sel['akhirPekan'] ? 'kg-pekan' : '' }}
                                {{ $sel['hariIni'] ? 'kg-hariini' : '' }}
                                {{ $merah ? 'kg-merah' : '' }}
                                {{ $tepi ? 'kg-terpakai' : '' }}
                                {{ $tanggalTerpilih === $sel['tanggal'] ? 'kg-terpilih' : '' }}"
                        @if ($tepi) style="--kg-tepi:{{ $tepi['warna'] }}; --kg-tepi-lembut:{{ $tepi['lembut'] }};" @endif
                        wire:key="sel-{{ $sel['tanggal'] }}"
                        wire:click="pilihTanggal('{{ $sel['tanggal'] }}')">

                        <span class="kg-angka">{{ $sel['angka'] }}</span>

                        @if ($tanda)
                            {{-- Judul lengkap ditaruh di atribut title: nama peringatan
                                 sering lebih panjang daripada lebar selnya, dan dipotong
                                 di tengah kata lebih membingungkan daripada membantu. --}}
                            <span class="kg-tanda {{ $merah ? 'kg-tanda-libur' : '' }}"
                                title="{{ $tanda['nama'] }}{{ $merah ? ' — hari libur nasional' : ' — hari peringatan, tetap hari kerja' }}">
                                {{ $tanda['nama'] }}
                            </span>
                        @endif

                        @if ($m['lebih'][$iHari] > 0)
                        <span class="kg-lebih">+{{ $m['lebih'][$iHari] }} lagi</span>
                        @endif
                    </div>
                    @endforeach

                    {{-- Lapisan atas: balok kegiatan --}}
                    @foreach ($m['balok'] as $b)
                    @php $k = $b['kegiatan']; @endphp
                    <button type="button"
                        class="kg-balok
                               {{ $k->sudahLewat() ? 'kg-lewat' : '' }}
                               {{ $b['sambungKiri'] ? 'kg-sambung-kiri' : '' }}
                               {{ $b['sambungKanan'] ? 'kg-sambung-kanan' : '' }}"
                        {{-- Nada, bukan warna jenis mentah: dua Acara yang tanggalnya
                             bertumpuk kalau tidak akan terbaca sebagai satu batang panjang. --}}
                        style="--kg-warna:{{ $k->warnaBalok() }}; --kg-lembut:{{ $k->lembutBalok() }};
                               --kg-kolom:{{ $b['kolom'] }}; --kg-rentang:{{ $b['rentang'] }}; --kg-lajur:{{ $b['lajur'] }};"
                        wire:key="balok-{{ $iMinggu }}-{{ $k->id }}"
                        wire:click="pilihTanggal('{{ $m['hari'][$b['kolom']]['tanggal'] }}')"
                        title="{{ $k->judul }} — {{ $k->rentangWaktu() }}">
                        <span>{{ $k->judul }}</span>
                    </button>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

        <div class="row g-4">
            {{-- ===== Hari terpilih ===== --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="kg-judul-kartu">
                                @if ($tanggalTerpilih)
                                {{ \Illuminate\Support\Carbon::parse($tanggalTerpilih)->locale('id')->translatedFormat('l, d F Y') }}
                                @else
                                Kegiatan Hari Terpilih
                                @endif
                            </h5>

                            @if ($tanggalTerpilih && $this->bolehTambah)
                            <button type="button" class="btn btn-sm btn-outline-primary kg-btn kg-btn-kecil"
                                wire:click="buatBaru('{{ $tanggalTerpilih }}')">
                                <i class="bi bi-plus-lg"></i> Tambah di tanggal ini
                            </button>
                            @endif
                        </div>

                        @forelse ($daftarKegiatanTerpilih as $k)
                        <div class="kg-baris" wire:key="pilih-{{ $k->id }}"
                            style="--kg-warna:{{ $k->warna() }}; --kg-lembut:{{ $k->lembut() }}; --kg-nada:{{ $k->warnaBalok() }};">
                            <span class="kg-pita"></span>
                            <div class="kg-baris-isi">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <b>{{ $k->judul }}</b>
                                    <span class="kg-lencana">
                                        <i class="bi bi-{{ $k->ikon() }}"></i>{{ $k->label() }}
                                    </span>
                                </div>

                                <div class="kg-meta">
                                    <span><i class="bi bi-clock"></i> {{ $k->rentangWaktu() }}</span>
                                    @if ($k->lokasi)
                                    <span><i class="bi bi-geo-alt"></i> {{ $k->lokasi }}</span>
                                    @endif
                                </div>

                                @if ($k->deskripsi)
                                <div class="kg-catatan">{{ $k->deskripsi }}</div>
                                @endif

                                @if ($k->peserta->isNotEmpty())
                                <div class="kg-peserta">
                                    <i class="bi bi-people"></i>
                                    {{ $k->peserta->pluck('name')->implode(', ') }}
                                </div>
                                @endif

                                @if ($this->bolehUbah || $this->bolehHapus)
                                <div class="mt-3 d-flex gap-2">
                                    @if ($this->bolehUbah)
                                    <button type="button" class="btn btn-sm kg-btn kg-btn-kecil kg-ubah"
                                        wire:click="sunting('{{ $k->id }}')">
                                        <i class="bi bi-pencil"></i> Ubah
                                    </button>
                                    @endif
                                    @if ($this->bolehHapus)
                                    <button type="button" class="btn btn-sm kg-btn kg-btn-kecil kg-hapus hapus-kegiatan-btn"
                                        data-id="{{ $k->id }}" data-judul="{{ $k->judul }}">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="kg-kosong">
                            @if ($tanggalTerpilih)
                            <i class="bi bi-calendar2-x"></i>
                            Tidak ada kegiatan pada tanggal ini.
                            @else
                            <i class="bi bi-hand-index-thumb"></i>
                            Klik salah satu tanggal untuk melihat kegiatannya.
                            @endif
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ===== Agenda terdekat =====
                 Sengaja dibuat paling sederhana di halaman ini: satu baris tipis
                 per kegiatan, dipisah garis rambut, tanpa kotak dan tanpa kartu
                 di dalam kartu. Ia hanya perlu menjawab "apa yang menunggu saya
                 setelah ini" — begitu tiap barisnya diberi bingkai dan bayangan
                 sendiri, ia mulai bersaing perhatian dengan kisi kalender di
                 atasnya, yang justru inti halaman. --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h5 class="kg-judul-kartu mb-3">Agenda Terdekat</h5>

                        @forelse ($berikutnya as $k)
                        <div class="kg-agenda-baris" wire:key="next-{{ $k->id }}"
                            style="--kg-nada:{{ $k->warnaBalok() }};"
                            title="{{ $k->judul }} — {{ $k->rentangWaktu() }}">
                            <span class="kg-agenda-titik"></span>
                            <span class="kg-agenda-nama">{{ $k->judul }}</span>
                            <span class="kg-agenda-waktu">
                                {{ $k->mulai->locale('id')->translatedFormat('d M') }}
                                @unless ($k->seharian) · {{ $k->mulai->translatedFormat('H:i') }} @endunless
                            </span>
                        </div>
                        @empty
                        <div class="kg-kosong">
                            <i class="bi bi-calendar2-check"></i>
                            Belum ada agenda mendatang.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Modal form =====
         Modal mengambil WARNA JENIS yang sedang dipilih: memilih "Tenggat"
         membuat kepalanya berubah merah mawar seketika. Warnanya bukan hiasan —
         ia menegaskan pilihan yang baru saja dibuat di tempat yang sedang
         dilihat mata, sehingga salah jenis lebih sulit lolos.

         Kepala dan kaki dipatok; hanya isinya yang bergulir. Tombol "Simpan"
         karena itu selalu terlihat, sepanjang apa pun daftar pesertanya. --}}
    @if ($formTampil)
    @php $jAktif = $jenisPeta[$jenis] ?? $jenisPeta['lainnya']; @endphp
    <div class="kg-modal-latar" wire:key="form-kegiatan"
        style="--kg-warna:{{ $jAktif['warna'] }}; --kg-lembut:{{ $jAktif['lembut'] }};"
        x-data="{
            init() {
                /* Latar dikunci agar yang bergulir hanya modalnya. Lebar bilah
                   gulir diganti jadi padding supaya halaman di belakang tidak
                   tersentak melebar saat bilahnya hilang. */
                const geser = window.innerWidth - document.documentElement.clientWidth;
                document.body.style.overflow = 'hidden';
                document.body.style.paddingRight = geser + 'px';
            },
            destroy() {
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            },
        }"
        x-on:keydown.escape.window="$wire.tutupForm()">

        <div class="kg-modal">

            {{-- Kepala --}}
            <div class="kg-modal-kepala">
                <div class="kg-modal-tanda">
                    <i class="bi bi-{{ $jAktif['ikon'] }}"></i>
                </div>
                <div class="kg-modal-tajuk">
                    <b>{{ $formId ? 'Ubah Kegiatan' : 'Tambah Kegiatan' }}</b>
                    <small>{{ $jAktif['label'] }}</small>
                </div>
                <button type="button" class="kg-modal-tutup" wire:click="tutupForm" aria-label="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form wire:submit="simpan" class="kg-modal-badan">

                {{-- Isi yang bergulir --}}
                <div class="kg-modal-isi">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('judul') is-invalid @enderror"
                            wire:model="judul" placeholder="Mis. Rapat mingguan tim produk" autofocus>
                        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Jenis</label>
                        <div class="kg-pilih-jenis">
                            @foreach ($jenisPeta as $kunci => $j)
                            <button type="button"
                                class="kg-chip {{ $jenis === $kunci ? 'aktif' : '' }}"
                                style="--kg-warna:{{ $j['warna'] }}; --kg-lembut:{{ $j['lembut'] }};"
                                wire:click="$set('jenis', '{{ $kunci }}')">
                                <i class="bi bi-{{ $j['ikon'] }}"></i>
                                {{ $j['label'] }}
                            </button>
                            @endforeach
                        </div>
                        @error('jenis') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Waktu dikumpulkan dalam satu panel: empat isian yang saling
                         terkait lebih mudah dibaca sebagai satu keputusan. --}}
                    <div class="kg-panel mb-4">
                        <div class="kg-panel-judul">
                            <span><i class="bi bi-clock-history"></i> Waktu</span>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="kg-seharian"
                                    wire:model.live="seharian">
                                <label class="form-check-label" for="kg-seharian">Seharian</label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Tanggal mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggalMulai') is-invalid @enderror"
                                    wire:model="tanggalMulai">
                                @error('tanggalMulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if (! $seharian)
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Jam mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('jamMulai') is-invalid @enderror"
                                    wire:model="jamMulai">
                                @error('jamMulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @endif

                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Tanggal selesai</label>
                                <input type="date" class="form-control @error('tanggalSelesai') is-invalid @enderror"
                                    wire:model="tanggalSelesai">
                                <small class="kg-bisik">Kosongkan bila selesai di hari yang sama.</small>
                                @error('tanggalSelesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if (! $seharian)
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Jam selesai</label>
                                <input type="time" class="form-control @error('jamSelesai') is-invalid @enderror"
                                    wire:model="jamSelesai">
                                <small class="kg-bisik">Boleh kosong bila belum pasti.</small>
                                @error('jamSelesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lokasi</label>
                        <input type="text" class="form-control @error('lokasi') is-invalid @enderror"
                            wire:model="lokasi" placeholder="Mis. Ruang rapat lantai 2 / Google Meet">
                        @error('lokasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" rows="3"
                            wire:model="deskripsi" placeholder="Agenda, hal yang perlu disiapkan, dsb."></textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">Peserta</label>
                            {{-- Jumlahnya disebut supaya orang tidak perlu menghitung
                                 sendiri centang yang tergulung di dalam kotak. --}}
                            <span class="kg-hitung">{{ count($peserta) }} dipilih</span>
                        </div>
                        <div class="kg-peserta-kotak">
                            @foreach ($semuaKaryawan as $u)
                            <label class="kg-peserta-baris" wire:key="peserta-{{ $u->id }}">
                                <input class="form-check-input m-0" type="checkbox"
                                    value="{{ $u->id }}" wire:model.live="peserta">
                                <span>{{ $u->name }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('peserta') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Kaki --}}
                <div class="kg-modal-kaki kg-alat">
                    <button type="button" class="btn btn-light border kg-btn" wire:click="tutupForm">
                        Batal
                    </button>
                    <button type="submit" class="btn kg-btn kg-simpan" wire:loading.attr="disabled">
                        <i class="bi bi-check2"></i>
                        <span wire:loading.remove wire:target="simpan">Simpan</span>
                        <span wire:loading wire:target="simpan">Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!--================== SWEET ALERT HAPUS ==================-->
    <script data-navigate-once>
        document.addEventListener('click', function (event) {
            const tombol = event.target.closest('.hapus-kegiatan-btn');
            if (!tombol) return;

            event.preventDefault();

            Swal.fire({
                title: 'Hapus kegiatan?',
                text: '"' + (tombol.getAttribute('data-judul') || '') + '" akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                background: 'rgba(255, 255, 255, 0.95)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                buttonsStyling: false,
                customClass: {
                    popup: 'swal-glossy-popup rounded-4 shadow-lg border-0',
                    confirmButton: 'btn-glossy-confirm',
                    cancelButton: 'btn-glossy-cancel',
                    title: 'fw-bold'
                }
            }).then((hasil) => {
                if (!hasil.isConfirmed) return;

                const komponen = tombol.closest('[wire\\:id]');
                if (komponen) {
                    Livewire.find(komponen.getAttribute('wire:id')).call('hapus', tombol.getAttribute('data-id'));
                }
            });
        });
    </script>
    <!--================== END SWEET ALERT HAPUS ==================-->
</div>
