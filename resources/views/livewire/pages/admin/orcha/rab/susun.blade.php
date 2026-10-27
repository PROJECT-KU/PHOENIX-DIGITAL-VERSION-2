@section('title')
Susun RAB || lemon
@stop

{{-- Penyusun RAB.

     Semua perbandingan dirakit jadi boolean di blok php di bawah ini, bukan
     ditulis di kondisi direktif: tanda "lebih besar" di kondisi direktif blok
     membuat Livewire melewatkan penanda morph-nya (PenandaMorphAdminTest). --}}
@php
    $ada = $rab !== [];
    $r = $rab['ringkasan'] ?? [];
    $jumlahHari = max(1, (int) ($rab['jumlah_hari'] ?? 1));
    $sudahPendaftaran = ! empty($rab['kode_pendaftaran']);
    $statusKini = $rab['status'] ?? 'draf';
    $kosakata = $katalog['kosakata'] ?? [];

    $kegiatanPerHari = [];
    foreach ($itinerary as $i => $k) {
        $kegiatanPerHari[$k['hari_ke']][$i] = $k;
    }
    $kegiatanAktif = $kegiatanPerHari[$hariAktif] ?? [];

    /*
     | Hari mana saja yang digambar.
     |
     | Dihitung DI SINI, bukan lewat direktif php sebaris di tengah berkas:
     | blok php berikutnya akan ikut tertelan sampai penutupnya, dan
     | galatnya menunjuk penutup perulangan yang tidak bersalah.
     |
     | Nama direktifnya sengaja TIDAK ditulis utuh di komentar ini —
     | penjaga penanda morph memindai sumbernya mentah, dan kata di dalam
     | komentar pun ikut terhitung sebagai direktif.
     */
    $hariDitampilkan = $semuaHari ? $kegiatanPerHari : [$hariAktif => $kegiatanAktif];

    // Dirakit di sini juga, dengan alasan yang sama: direktif php sebaris
    // di tengah berkas membuat blok php berikutnya ikut tertelan.
    $tautanWa = $ada ? $this->tautanWa() : null;
    $hariAktifKosong = $kegiatanAktif === [];
    $idxPertama = array_key_first($kegiatanAktif);
    $idxTerakhir = array_key_last($kegiatanAktif);

    $destinasiDipakai = collect($itinerary)->pluck('destinasi')->filter()->flip()->all();
    $kata = mb_strtolower(trim($cariDestinasi));
    $semuaDestinasi = collect($katalog['destinasi'] ?? []);
    $destinasi = $semuaDestinasi
        ->filter(fn ($d) => $kata === '' || str_contains(mb_strtolower($d['nama'].' '.($d['daerah'] ?? '')), $kata))
        ->values()->all();

    /*
     | Dua keadaan kosong yang berbeda, dan sebelumnya keduanya diberi
     | kalimat yang sama.
     |
     | Katalognya memang kosong untuk provinsi ini — itu pekerjaan di menu
     | Destinasi Populer. Atau katalognya penuh tetapi pencariannya tidak
     | menemukan apa-apa — itu bukan pekerjaan siapa pun, cuma kata yang
     | tidak ada di daftar. Mengaku "tidak ada destinasi katalog untuk
     | provinsi ini" padahal ada sepuluh membuat admin pergi mengisi data
     | yang sudah terisi.
     */
    $katalogKosong = $semuaDestinasi->isEmpty();
    $cariTanpaHasil = ! $katalogKosong && $destinasi === [];
    $tanpaDestinasi = $destinasi === [];

    $masterPerKategori = collect($katalog['master'] ?? [])->groupBy('kategori_label')->all();
    // Urutan kategorinya sudah dari Orcha (tiket, transportasi, akomodasi, …).
    $barisPerKategori = collect($r['baris'] ?? [])->groupBy('kategori_label')->all();
    $tanpaBiaya = $barisPerKategori === [];
    $adaTanpaTiket = $tanpaTiket !== [];
    $hargaBasi = (bool) ($r['ada_harga_basi'] ?? false);
    $manualBerkapasitas = in_array($manual['satuan'] ?? '', ['unit_hari', 'kamar_malam'], true);
    $marginPersen = $marginJenis === 'persen';
    $marginBelumTersimpan = $marginJenis !== ($rab['margin_jenis'] ?? $marginJenis);

    $tanggalTeks = ! empty($rab['tanggal_mulai'])
        ? \Illuminate\Support\Carbon::parse($rab['tanggal_mulai'])->locale('id')->translatedFormat('j M Y')
        : 'Tanggal belum pasti';
    $bolehJadiPendaftaran = ! $sudahPendaftaran && $statusKini !== 'batal' && ! $tanpaBiaya;

    $openTrip = ($rab['jenis'] ?? 'private') === 'open';
    $pesertaDasar = (int) ($r['peserta_dasar'] ?? ($rab['jumlah_peserta'] ?? 1));
    $simulasi = $r['simulasi'] ?? [];
    $impasPeserta = $r['bep_peserta'] ?? null;
    $adaImpas = $impasPeserta !== null;
    $impasTercapai = (bool) ($r['bep_tercapai'] ?? false);
    $dibawahImpas = $openTrip && $impasPeserta !== null && ! $impasTercapai;

    // Negosiasi. Dirakit di sini bersama perbandingan lain, bukan di @php
    // sebaris di antara direktif — lihat catatan di bawah.
    $negoAktif = (bool) ($r['nego_aktif'] ?? false);
    $negoRugi = (bool) ($r['nego_rugi'] ?? false);
    $negoSelisih = (int) ($r['nego_selisih'] ?? 0);
    $negoTurun = $negoSelisih < 0;
    $negoSelisihTeks = 'Rp '.number_format(abs($negoSelisih), 0, ',', '.');

    /*
     | Perbandingannya dirakit di sini, BUKAN di @php sebaris di antara
     | direktif. Tanda "lebih besar" di sana membuat Livewire melewatkan
     | penanda morph @foreach-nya, dan tiap pembaruan sesudahnya rusak di
     | Block.appendChild — tanpa satu pun galat yang menyebut sebabnya.
     | Dijaga PenandaMorphAdminTest.
     */
    $amanLaba = fn (array $s) => $s['laba'] >= 0;

    /*
     | Angka pendek untuk tabel simulasi.
     |
     | Panel ringkasannya cuma selebar sepertiga layar, dan tiga kolom
     | rupiah penuh membuat kolom terakhir — justru yang paling dicari —
     | terpotong tepi kartu. Yang dibaca di tabel ini besarannya, bukan
     | rupiah ke rupiah; angka telitinya sudah ada di atas.
     */
    $pendek = function ($angka) {
        $n = (int) $angka;
        $tanda = $n < 0 ? '−' : '';
        $a = abs($n);

        if ($a >= 1_000_000) {
            return $tanda.'Rp '.rtrim(rtrim(number_format($a / 1_000_000, 1, ',', '.'), '0'), ',').' jt';
        }

        if ($a >= 1_000) {
            return $tanda.'Rp '.number_format($a / 1_000, 0, ',', '.').' rb';
        }

        return $tanda.'Rp '.number_format($a, 0, ',', '.');
    };

    /*
     | Warna per jenis kegiatan — peta yang SAMA dengan kedua PDF, supaya
     | satu perjalanan tidak memakai warna berbeda untuk hal yang sama di
     | layar dan di berkas yang dicetak dari layar itu.
     |
     | Jenisnya datang dari Orcha (BerkasRab::jenis), tidak ditebak di sini.
     */
    $rupaKegiatan = [
        'destinasi' => ['Destinasi', '#1f7a44'],
        // Bukan biru merek lama: menuliskannya langsung dilarang di layar
        // Orcha (OrchaTampilanSeragamTest), dan satu tempat yang terlewat
        // cukup untuk meninggalkan biru di tengah halaman ungu. Birunya
        // diganti di sini DAN di kedua PDF, supaya tetap sepasang.
        //
        // Kodenya sengaja tidak dikutip di komentar ini: pemindainya membaca
        // seluruh berkas, jadi menyebut yang dilarang untuk menjelaskan
        // larangannya justru melanggarnya.
        'perjalanan' => ['Perjalanan', '#0369a1'],
        'makan' => ['Makan / singgah', '#e0912f'],
        'snack' => ['Snack', '#0e7490'],
        'istirahat' => ['Istirahat', '#6d28d9'],
        'lain' => ['Kegiatan', '#64748b'],
    ];
    $rupaDari = fn (array $k) => $rupaKegiatan[$k['jenis'] ?? 'lain'] ?? $rupaKegiatan['lain'];

    // Total tiap kategori, dikunci labelnya — supaya pita kategori di tabel
    // biaya membawa angkanya sendiri, bukan cuma nama yang melintang.
    $totalKategori = collect($r['per_kategori'] ?? [])->keyBy('label')->all();
@endphp

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan
         dasbor Orcha, jejak audit, dan daftar RAB. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    <style>
        /* Dua kolom lewat sistem 12 kolom rak dsb (k-8 / k-4), bukan grid
           bikinan sendiri: ".dsb-rak > *" memaksa span 12 pada tiap anaknya,
           jadi template kolom buatan sendiri kalah tanpa satu pun tanda —
           keduanya tetap bertumpuk dan terbaca seperti CSS yang tidak sampai. */
        .rb-kolom { display: flex; flex-direction: column; gap: clamp(12px, 1.2vw, 16px); min-width: 0; }

        /* Isian yang bergandengan dengan tombol atau satuannya.

           Bukan .input-group Bootstrap lagi: aturan lebarnya dirancang untuk
           .form-control, dan .dsb-isian yang selebar penuh melempar pasangannya
           ke baris berikutnya — terbaca seperti tata letak yang jebol. */
        .rb-gandeng { display: flex; align-items: stretch; gap: 8px; }
        .rb-gandeng > .dsb-isian { flex: 1 1 auto; min-width: 0; }
        .rb-gandeng > .dsb-tombol, .rb-gandeng > .rb-gandeng-teks { flex: 0 0 auto; }
        /* Dua kolom: jadwal yang sedang disusun, dan persediaan yang tinggal
           diklik. Lebar kanannya dipatok supaya cip-cipnya melipat rapat —
           deret cip yang melebar membuat baris terakhirnya hampir kosong. */
        /* Jadwal selebar kartu, pemilih di BAWAHnya — bukan dua kolom.

           Dua kolom sempat dicoba dan salah: baris kegiatan butuh lebar
           (rentang jam + nama + keterangan + empat tombol), dan dijepit jadi
           sepertiga kartu ia meluber keluar kolomnya. Yang menyebabkan ruang
           kosong di susunan lama bukan arah tumpukannya, melainkan deret cip
           yang melipat ragged — itu yang diperbaiki di bawah. */
        .rb-itin-jadwal { min-width: 0; display: flex; flex-direction: column; }
        .rb-kegiatan-bebas { margin-top: 10px; }

        .rb-itin-pilih {
            margin-top: 14px; padding: 13px; border-radius: 13px;
            background: #fbfcfe; border: 1px solid var(--dsb-tepi, #e9edf3);
        }
        /* Kepala pemilih: judul di kiri, kotak cari di kanan — satu baris,
           bukan dua, karena keduanya pendek. */
        .rb-pilih-kepala {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 4px 12px; margin-bottom: 10px;
        }
        .rb-pilih-judul { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
        .rb-pilih-kepala strong { font-size: .86rem; color: var(--dsb-tinta, #1c1f26); }
        .rb-pilih-kepala .rab-tiket { color: #b7791f; }
        .rb-pilih-cari { width: auto; min-width: 220px; flex: 0 1 260px; }
        /* Pesan kosong duduk di dalam grid cip, jadi ia harus MEMBENTANG
           seluruh kolomnya. Tanpa itu ia terjepit selebar satu cip (120px)
           dan kalimatnya terpotong di tengah — pesan yang menjelaskan
           keadaan justru jadi keadaan yang perlu dijelaskan. */
        .rb-pilih-kosong {
            grid-column: 1 / -1; font-size: .76rem;
            color: var(--dsb-redup, #6b7280); line-height: 1.5;
        }
        .rb-kosong-aksi { display: flex; flex-wrap: wrap; gap: 6px; }
        .rb-kosong-aksi .dsb-tombol { flex: 0 0 auto; }

        .rb-pilih-pisah {
            margin: 11px 0 7px; padding-top: 10px; border-top: 1px solid var(--dsb-tepi, #e9edf3);
            font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
            color: var(--dsb-redup, #6b7280);
        }

        /* Hari kosong: kotak berpenanda, bukan satu baris miring yang
           melayang — yang melayang terbaca sebagai sisa, bukan sebagai
           keadaan yang disengaja. */
        .rb-hari-kosong {
            /* flex: 1 — mengisi sisa tinggi kolomnya, jadi hari yang masih
               kosong tampil sebagai bidang yang memang disediakan, bukan
               sebagai kekosongan yang tertinggal. */
            flex: 1; display: flex; align-items: center; gap: 10px;
            padding: 13px; border-radius: 12px;
            background: #f8fafc; border: 1px dashed var(--dsb-tepi, #e9edf3);
        }
        .rb-hari-kosong strong { display: block; font-size: .86rem; color: var(--dsb-tinta, #1c1f26); }
        .rb-hari-kosong .dsb-kartu-sub { line-height: 1.45; }

        /* Deret kegiatan baku. Chip, bukan tombol penuh: ia pilihan cepat,
           bukan tindakan utama di kartu ini. */

        .rb-chip-tombol { cursor: pointer; border: 1px solid var(--dsb-tepi, #e9edf3); background: #fff; }
        .rb-chip-tombol:hover { border-color: #c7d2fe; color: #4338ca; }
        .rb-chip-tombol:disabled { opacity: .5; cursor: default; }

        .rb-gandeng-teks {
            display: inline-flex; align-items: center; padding: 0 12px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 11px;
            background: #f8fafc; color: var(--dsb-redup, #6b7280); font-size: .85rem; font-weight: 600;
        }
        .rb-kartu { margin-bottom: 0; }
        .rb-isian-ringkas { width: auto; min-width: 128px; }

        @media (max-width: 991.98px) {
            .rab-lengket { position: static; }
        }

        /* Kolom kiri yang MENYERAP sisa ruang. Tanpa ini
           justify-content-between menyisakan lubang di tengah kartu, dan
           keping faktanya terlipat lebih awal daripada seharusnya. */
        .rb-kepala-info { flex: 1 1 auto; min-width: 0; }
        .rb-kepala-aksi { display: flex; flex-direction: column; gap: .5rem; align-items: flex-start; flex: 0 0 auto; }
        .rb-aksi-baris { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
        .rb-aksi-label {
            font-size: .68rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
            color: var(--dsb-redup, #94a3b8); white-space: nowrap;
        }
        /* Satu latar membuat ketiga tombol terbaca sebagai SATU kelompok,
           jadi "Penawaran" dan "Internal" tidak perlu mengulang kata PDF. */
        .rb-aksi-berkas {
            background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
            border-radius: 15px; padding: .35rem .6rem;
        }
        @media (min-width: 992px) {
            .rb-kepala-aksi { align-items: flex-end; }
            .rb-aksi-baris { justify-content: flex-end; }
        }

        .rab-fakta { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .9rem; }
        .rab-fakta span {
            background: var(--orc-panel-latar); border: var(--orc-panel-garis); border-radius: 999px;
            padding: .28rem .7rem; font-size: .8rem; color: var(--orc-tinta); white-space: nowrap;
        }
        .rab-fakta i { color: var(--dsb-redup, #94a3b8); margin-right: .15rem; }
        .rab-hari-tab { display: flex; flex-wrap: wrap; gap: .5rem; }
        .rab-hari-tab button { border: 2px solid #e2e8f0; background: #fff; border-radius: 12px; padding: .45rem .9rem; font-weight: 600; font-size: .85rem; color: var(--orc-tinta); }
        .rab-hari-tab button.aktif { border-color: var(--orc-primer); background: var(--orc-gradien); color: #fff; }
        .rab-hari-tab small { opacity: .75; font-weight: 500; margin-left: .25rem; }
        /* Flex-wrap, bukan grid berkolom tetap: media query mengukur lebar
           LAYAR, sementara yang menjepit baris ini lebar WADAHnya. Di layar
           lebar dengan wadah sempit, aturan ponselnya tidak pernah menyala
           dan barisnya meluber tanpa satu pun tanda. */
        .rab-kegiatan {
            display: flex; flex-wrap: wrap; align-items: center; gap: .5rem .6rem;
            padding: .75rem .75rem .75rem .9rem; border: var(--orc-panel-garis);
            border-radius: 14px; background: #fff; margin-bottom: .5rem;
            /* Pita warna di tepi kiri, bukan latar penuh: sepuluh kartu
               berlatar warna membuat kotak isian di dalamnya susah dibaca,
               dan yang perlu dibedakan cuma JENISnya — bukan seluruh
               kartunya. Warnanya sama dengan yang dipakai kedua PDF. */
            box-shadow: inset 4px 0 0 var(--c, #64748b);
        }
        .rab-kegiatan .rab-jam { order: 1; flex: 0 0 auto; }
        /* Basis 140px, bukan 200: flex memutuskan pindah baris dari JUMLAH
           basis, bukan dari lebar setelah tumbuh. Dengan 200, jumlahnya
           melewati lebar kartu sedikit saja — dan tombolnya terlempar ke
           baris sendiri walau ruangnya sebenarnya cukup. */
        .rab-kegiatan .rab-nama { order: 2; flex: 1 1 140px; min-width: 0; }
        /* Tombol di baris PERTAMA bersama jam dan namanya; keterangan turun
           sendiri ke baris kedua.

           Tanpa order, keterangan yang selebar penuh berdiri lebih dulu dan
           mendorong tombolnya ke baris KETIGA — satu kegiatan jadi setinggi
           tiga baris, dan sepuluh kegiatan jadi layar yang harus digulir
           dua kali. */
        .rab-kegiatan .rab-kegiatan-aksi { order: 3; flex: 0 0 auto; }

        .rab-waktu { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .rab-waktu-isian { display: flex; align-items: center; gap: 4px; min-width: 0; }
        .rab-waktu-isian .dsb-isian { min-width: 0; padding-left: 8px !important; padding-right: 4px; }
        .rab-waktu-pisah { color: #94a3b8; font-size: .8rem; flex: 0 0 auto; }
        /* Baris kedua: penanda jenis, lamanya, lalu keterangannya. */
        .rab-ket-baris {
            /* align-items: stretch — pil jenis dan pil lama setinggi kotak
               isian di sebelahnya, bukan lencana kecil yang melayang di
               tengahnya. Diregangkan, bukan dipatok angka: tinggi .dsb-isian
               milik sistem desain dan boleh berubah tanpa memberi tahu
               berkas ini. */
            order: 4; display: flex; flex-wrap: wrap; align-items: stretch;
            gap: 6px; flex: 1 1 100%; min-width: 0;
        }
        /* Basis 180px: di kartu lebar keterangannya sebaris dengan kedua pil,
           di ponsel ia turun sendiri ke baris berikutnya. Tanpa itu kotaknya
           terjepit jadi selebar dua kata — kotak isian yang tidak bisa
           dibaca isinya sama saja dengan tidak ada. */
        .rab-ket-baris .rab-ket { flex: 1 1 180px; min-width: 0; }

        .rab-pil-jenis, .rab-pil-lama {
            flex: 0 0 auto; display: inline-flex; align-items: center;
            /* Sudutnya menyamai .dsb-isian, bukan bulat penuh: setinggi kotak
               isian, bentuk kapsul membuat ketiganya terbaca seperti tiga
               benda yang kebetulan bersebelahan.
               Tingginya TIDAK dipatok angka — kotak isian di kartu ini
               dipatok 38px oleh aturan di atas, bukan 40px bawaan sistem
               desain, dan angka yang disalin tangan akan meleset lagi begitu
               salah satunya berubah. Biar diregangkan oleh barisnya.

               min-height di sini LANTAI, bukan patokan: saat kotak isiannya
               turun ke baris sendiri di layar sempit, tidak ada lagi yang
               bisa diikuti — tanpa lantai keduanya menciut jadi 18px dan
               kembali terbaca sebagai lencana kecil. */
            padding: 0 13px; min-height: 34px; border-radius: 11px;
            font-size: .74rem; font-weight: 700; white-space: nowrap;
        }
        /* color-mix memberi latar setipis warnanya sendiri; peramban yang
           belum mengenalnya mengabaikan baris ini dan menyisakan tulisan
           berwarna tanpa latar — masih terbaca, cuma kurang tegas. */
        .rab-pil-jenis { color: var(--c, #64748b); background: color-mix(in srgb, var(--c, #64748b) 10%, transparent); }
        .rab-pil-lama { color: var(--dsb-redup, #6b7280); background: #f1f5f9; }
        .rab-kegiatan .dsb-isian { height: 38px !important; min-height: 38px; font-size: .88rem; padding-left: .75rem !important; }
        .rab-kegiatan-aksi { display: flex; gap: .3rem; align-items: center; }
        .rab-kegiatan-aksi .btn { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
        .rab-kegiatan-aksi .dsb-isian { width: 74px; padding-right: 1.6rem; }
        /* Cip di dalam GRID, bukan flex-wrap.

           Flex-wrap meninggalkan sisa di ujung kanan tiap baris sebesar cip
           yang tidak muat lagi — dan pada baris terakhir sisanya bisa
           selebar setengah kartu. Grid auto-fill membagi lebarnya rata, jadi
           tiap baris selalu habis. */
        .rab-destinasi,
        .rb-baku-deret {
            /* 120px: di kartu lebar memberi empat kolom, di ponsel masih muat
               dua. Angkanya dihitung dari ruang yang BENAR-BENAR tersedia —
               lebar panel dikurangi padding 13px di kedua sisinya. Dengan
               146px ponsel jatuh ke satu kolom dan daftarnya jadi dua kali
               lebih panjang tanpa alasan. */
            display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 6px; padding: 1px;
        }
        .rab-destinasi { max-height: 196px; overflow-y: auto; }
        /* Dibiarkan membungkus, bukan dipotong.

           Nama yang panjang ("Penjemputan peserta") terpotong di kedua
           ujungnya karena isinya ditengahkan — dan tombol yang namanya tidak
           terbaca utuh tidak bisa dipilih dengan yakin. Satu baris yang
           menjadi dua lebih murah daripada nama yang hilang. */
        .rab-destinasi > button, .rb-baku-deret > button {
            width: 100%; justify-content: center; text-align: center;
            white-space: normal; line-height: 1.3;
        }
        .rab-destinasi button { border: 1px solid #dbe3ec; background: #fff; border-radius: 999px; padding: .4rem .8rem; font-size: .83rem; color: var(--orc-tinta); display: inline-flex; gap: .35rem; align-items: center; }
        .rab-destinasi button:hover { border-color: var(--orc-primer); color: var(--orc-primer); }
        .rab-destinasi button.dipakai { background: #eafaf1; border-color: #9fd6b4; color: #14683f; font-weight: 600; }
        .rab-destinasi .tiket { color: #b7791f; }
        .rab-lengket { position: sticky; top: 90px; }

        .rab-muat {
            display: none; align-items: center; gap: 9px;
            margin-bottom: 14px; padding: 10px 15px; border-radius: 13px;
            background: #eef2ff; border: 1px solid #c7d2fe;
            font-size: .83rem; font-weight: 600; color: #3730a3;
        }
        .rab-tertahan { border-color: #fde68a !important; background: #fffbeb !important; }
        .rab-titik {
            display: inline-block; width: 7px; height: 7px; margin-left: 5px;
            border-radius: 999px; background: #b45309;
        }
        .rb-pegangan {
            display: inline-flex; align-items: center; justify-content: center;
            width: 24px; height: 32px; color: #cbd5e1; flex: 0 0 auto;
        }
        .rb-pegangan:hover { color: #94a3b8; }

        .rb-hari-aksi { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .rb-hari-aksi-label {
            font-size: .72rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
            color: var(--dsb-redup, #6b7280); margin-right: auto;
        }
        .rb-hari-salin { width: auto; height: 34px; padding: 0 30px 0 11px; font-size: .8rem; }
        .rab-hari-tab .rb-semua-hari { margin-left: auto; }
        .rb-hari-kepala {
            display: flex; align-items: baseline; gap: 8px; margin: 14px 0 8px;
            padding-bottom: 6px; border-bottom: 1px solid #eef2f7;
        }
        .rb-hari-kepala span {
            font-size: .78rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
            color: var(--orc-primer, #4f46e5);
        }
        .rb-hari-kepala small { font-size: .72rem; color: var(--dsb-redup, #6b7280); }

        /* Urungkan: biru, bukan merah. Ia jalan keluar dari kesalahan,
           bukan kesalahan itu sendiri. */
        .rb-urung {
            display: flex; flex-wrap: wrap; align-items: center; gap: 9px;
            padding: 10px 13px; border-radius: 12px;
            background: #eff6ff; border: 1px solid #bfdbfe;
        }
        .rb-urung > i { color: #1d4ed8; }
        .rb-urung-teks { flex: 1 1 180px; min-width: 0; font-size: .82rem; color: #1e3a8a; }

        .rab-tertinggal {
            display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
            margin-bottom: 14px; padding: 11px 14px; border-radius: 13px;
            background: #eff6ff; border: 1px solid #bfdbfe;
        }
        .rab-tertinggal > i { color: #1d4ed8; font-size: 1.02rem; }
        .rab-tertinggal-teks { flex: 1 1 240px; min-width: 0; font-size: .83rem; line-height: 1.45; color: #1e3a8a; }

        .rab-tertahan-ket {
            display: inline-flex; align-items: center; gap: 6px; margin-right: auto;
            font-size: .79rem; font-weight: 600; color: #92400e;
        }
        .rab-harga-besar { font-size: 1.9rem; font-weight: 800; color: var(--orc-primer); line-height: 1.1; }
        .rab-baris-angka { display: flex; justify-content: space-between; font-size: .88rem; padding: .3rem 0; color: var(--orc-tinta); }
        .rab-baris-angka.garis { border-top: 1px dashed #e2e8f0; margin-top: .3rem; padding-top: .55rem; }
        .rab-pilih-margin { display: grid; grid-template-columns: repeat(3, 1fr); gap: .4rem; }
        .rab-pilih-margin label { border: 2px solid #e2e8f0; border-radius: 10px; padding: .45rem .3rem; text-align: center; font-size: .78rem; font-weight: 600; cursor: pointer; color: var(--orc-tinta); }
        .rab-pilih-margin label.aktif { border-color: var(--orc-primer); color: var(--orc-primer); background: #f5f0ff; }
        .rab-pilih-margin input { display: none; }
        .rab-tabel-biaya .dsb-isian { height: 36px !important; min-height: 36px; font-size: .85rem; padding-left: .6rem !important; text-align: right; }
        /* Pita kategori: namanya di kiri, totalnya di kanan. */
        .rab-kat td {
            background: var(--orc-panel-latar) !important; font-size: .74rem; letter-spacing: .07em;
            text-transform: uppercase; font-weight: 700; color: var(--orc-primer);
            padding-top: .45rem !important; padding-bottom: .45rem !important;
        }
        /* Angkanya tidak ikut text-transform pitanya: "RP 100.000" bukan cara
           menulis rupiah, dan huruf besar pada singkatan mata uang terbaca
           seperti salah ketik. */
        .rab-kat td:nth-child(2) { font-variant-numeric: tabular-nums; letter-spacing: 0; text-transform: none; }

        /* Kaki tabel: angka yang dicari mata sesudah membaca rinciannya. */
        .rab-kaki td {
            background: #f8fafc; border-top: 2px solid var(--dsb-tepi, #e9edf3);
            font-size: .82rem; font-weight: 800; color: var(--dsb-tinta, #1c1f26);
            padding-top: .6rem !important; padding-bottom: .6rem !important;
        }
        .rab-kaki td:nth-child(2) { font-variant-numeric: tabular-nums; }

        /* Kotak pilih master TIDAK boleh tumbuh sesuka hati.

           Dengan flex-grow bebas ia memakan seluruh sisa baris, dan tombol
           terakhir ("Biaya manual") terlempar ke baris sendiri dengan ruang
           kosong selebar kartu di sebelahnya. Basis 300px membuat jumlah
           basis seluruh bilah muat satu baris; sisanya tetap diberikan
           kepadanya. */
        .rb-biaya-pilih { flex: 1 1 300px; min-width: 0; }

        /* Nama biaya + penjelasan hitungannya, bertumpuk di satu sel. */
        /* Panel kendaraan: satu baris per pilihan, angkanya sejajar supaya
           yang dibandingkan memang kolomnya — bukan mata yang melompat. */
        .rb-armada {
            margin-bottom: 14px; padding: 12px 14px;
            background: #f5fafd; border: 1px solid #cfe6f3; border-radius: 13px;
        }
        .rb-armada-kepala { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
        .rb-armada-teks { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .rb-armada-teks strong { font-size: .9rem; color: var(--dsb-tinta, #1c1f26); }
        .rb-armada-deret { display: flex; flex-direction: column; gap: 6px; }
        .rb-armada-pil {
            display: grid; grid-template-columns: minmax(0, 1fr) auto auto auto;
            align-items: center; gap: 10px;
            padding: 9px 11px; border-radius: 11px;
            background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3);
        }
        .rb-armada-pil.is-saran { border-color: #7cc0e0; box-shadow: 0 0 0 3px rgba(3, 105, 161, .08); }
        .rb-armada-pil.is-dipakai { background: #f8fafc; }
        .rb-armada-nama { font-weight: 700; font-size: .85rem; color: var(--dsb-tinta, #1c1f26); min-width: 0; }
        .rb-armada-tanda {
            display: inline-block; margin-left: 6px; padding: 1px 7px 2px; border-radius: 7px;
            background: #e0f2fe; color: #0369a1; font-size: .66rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .03em;
            /* Utuh atau pindah baris sekalian — "PALING / MURAH" terbelah dua
               baris terbaca seperti dua lencana yang berbeda. */
            white-space: nowrap;
        }
        .rb-armada-hitung { display: flex; flex-direction: column; gap: 1px; text-align: right; }
        .rb-armada-unit { font-weight: 700; font-size: .82rem; color: #0369a1; }
        .rb-armada-angka { font-weight: 700; font-size: .88rem; white-space: nowrap; }
        .rb-armada-sudah {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: .75rem; font-weight: 600; color: #047857; white-space: nowrap;
        }

        @media (max-width: 575.98px) {
            .rb-armada-pil { grid-template-columns: minmax(0, 1fr) auto; }
            .rb-armada-hitung { grid-column: 1; text-align: left; }
            .rb-armada-angka { grid-column: 2; grid-row: 2; }
        }

        .rab-biaya-nama { font-weight: 700; color: var(--dsb-tinta, #1c1f26); font-size: .88rem; }
        .rab-biaya-hitung { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; margin-top: 3px; }
        .rab-biaya-vendor {
            display: inline-flex; align-items: center; flex-wrap: wrap; gap: 5px; margin-top: 3px;
            font-size: .76rem; font-weight: 600; color: #0369a1;
        }
        .rab-vendor-tautan { color: inherit; text-decoration: none; }
        .rab-vendor-tautan:hover { text-decoration: underline; }
        .rab-vendor-nomor {
            padding: 1px 7px 2px; border-radius: 7px;
            background: #e0f2fe; color: #075985; font-size: .72rem; font-weight: 700;
        }
        .rab-pil-satuan {
            display: inline-flex; align-items: center; padding: 1px 7px 2px; border-radius: 7px;
            background: #eef2f7; color: var(--dsb-redup, #6b7280);
            font-size: .68rem; font-weight: 700; white-space: nowrap;
        }
        .rab-hitung-teks { font-size: .76rem; color: var(--dsb-redup, #6b7280); }

        /* Di ponsel tabel biaya berhenti jadi tabel: tiap baris jadi kartu
           bertumpuk, aturan yang sama dengan daftar lemon lain. Tanpa ini ia
           menggulir mendatar di dalam pembungkusnya — dan subtotal berikut
           tombol hapusnya tidak pernah terlihat tanpa digeser lebih dulu,
           padahal subtotal itulah yang dicari mata. */
        @media (max-width: 767.98px) {
            .rab-tabel-biaya { min-width: 0; }
            .rab-tabel-biaya thead { display: none; }
            .rab-tabel-biaya tbody tr, .rab-tabel-biaya tfoot tr {
                display: block; padding: 11px 13px; border-bottom: 1px solid #f2f5f9;
            }
            .rab-tabel-biaya tbody td, .rab-tabel-biaya tfoot td {
                display: flex; align-items: center; justify-content: space-between; gap: 10px;
                padding: 3px 0 !important; border: 0; text-align: left !important;
            }
            .rab-tabel-biaya td[data-judul]::before {
                content: attr(data-judul);
                color: var(--dsb-redup, #6b7280); font-size: .7rem; font-weight: 700;
                letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
            }
            /* Isian tidak selebar penuh di ponsel: labelnya di kiri, kotaknya
               di kanan — sama seperti baris lain yang berpasangan. */
            .rab-tabel-biaya td[data-judul] .dsb-isian { width: 130px; flex: 0 0 auto; }
            .rab-tabel-biaya .rab-sel-aksi { justify-content: flex-end; }

            /* Sel nama biaya tetap blok: dengan space-between namanya
               terlempar ke kiri dan keterangannya ke kanan, padahal
               keduanya satu kalimat yang dibaca berurutan. */
            .rab-tabel-biaya tbody td:first-child { display: block; }

            /* Bilah atas: kotak pilih sebaris penuh, dua tombolnya membagi
               baris berikutnya — kalau tidak, "Tarik ulang" berdiri sendiri
               dengan separuh baris kosong di sebelahnya. */
            .rb-biaya-pilih { flex: 1 1 100%; }
            .rb-biaya-bilah .dsb-tombol { flex: 1 1 auto; justify-content: center; }

            /* Pita kategori & kaki tetap satu baris: nama di kiri, angka di
               kanan. Sel ketiganya kosong dan hanya menambah baris hampa. */
            .rab-tabel-biaya tr.rab-kat, .rab-tabel-biaya tr.rab-kaki { display: flex; align-items: center; justify-content: space-between; }
            .rab-tabel-biaya tr.rab-kat td, .rab-tabel-biaya tr.rab-kaki td { display: block; }
            .rab-tabel-biaya tr.rab-kat td:last-child, .rab-tabel-biaya tr.rab-kaki td:last-child { display: none; }
        }
        .rab-basi { font-size: .75rem; color: #b42318; }
        /* Beda harga BUKAN peringatan: ia bisa berarti harga master naik
           ATAU tim menemukan yang lebih murah. Merah membuat kabar baik
           terbaca seperti kesalahan. */
        .rab-basi.is-beda { color: #64748b; }
        .rab-basi.is-beda a { color: #4f46e5; }
        .rb-catatan-baris { margin-top: 5px; font-size: .78rem; padding-top: 5px; padding-bottom: 5px; }

        /* ------------------------------ NEGOSIASI ------------------------------ */
        .rb-nego-tanda {
            display: flex; align-items: flex-start; gap: 7px; margin: -8px 0 14px;
            font-size: .8rem; color: #4338ca; line-height: 1.45;
        }
        .rb-nego-tanda i { margin-top: 2px; }
        .rb-nego {
            margin: 14px 0; border: 1px solid var(--dsb-tepi, #e9edf3);
            border-radius: 14px; background: #fff; overflow: hidden;
        }
        .rb-nego.is-hidup { border-color: #c7d2fe; background: #f8f9ff; }
        .rb-nego-kepala {
            display: flex; align-items: center; gap: 10px; width: 100%;
            padding: 10px 12px; border: 0; background: transparent; text-align: left;
        }
        .rb-nego-judul { display: flex; flex-direction: column; min-width: 0; flex: 1 1 auto; }
        .rb-nego-judul strong { font-size: .88rem; color: var(--dsb-tinta, #1c1f26); }
        .rb-nego-judul .dsb-kartu-sub { line-height: 1.4; }
        .rb-nego-panah { font-size: .7rem; color: #94a3b8; transition: transform .18s ease; }
        .rb-nego-panah.is-buka { transform: rotate(180deg); }
        .rb-nego-isi {
            display: flex; flex-direction: column; gap: 10px;
            padding: 0 12px 12px; border-top: 1px solid #eef1f6; padding-top: 12px;
        }
        .rb-nego-batas {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px;
            padding: 9px 11px; border-radius: 11px; background: #fffbeb; border: 1px solid #fde68a;
            font-size: .78rem; color: #92400e; line-height: 1.45;
        }
        .rb-nego-lepas { align-self: flex-start; }
        .rb-nego-rugi {
            display: flex; align-items: flex-start; gap: 7px;
            padding: 9px 12px; background: #fef2f2; border-top: 1px solid #fecaca;
            font-size: .78rem; color: #b42318; line-height: 1.45;
        }
        .rb-nego-rugi i { margin-top: 2px; }

        /* Titik impas: hijau bila rombongan yang ada sudah menutup biaya
           tetapnya, kuning bila belum. Bukan angka telanjang — yang
           ditanyakan admin "aman atau belum", dan warna menjawabnya sebelum
           satu angka pun dibaca. */
        .rb-impas {
            display: flex; align-items: flex-start; gap: 10px;
            margin-top: 12px; padding: 11px 13px; border-radius: 13px;
            border: 1px solid; 
        }
        .rb-impas.is-aman { background: #f6fbf8; border-color: #d1fae5; }
        .rb-impas.is-waspada { background: #fffbeb; border-color: #fde68a; }
        .rb-impas-teks { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
        .rb-impas-teks strong { font-size: .95rem; color: var(--dsb-tinta, #1c1f26); }
        .rb-impas-teks .dsb-kartu-sub { line-height: 1.45; }

        .rb-dasar {
            display: flex; align-items: flex-start; gap: 7px; margin-bottom: 12px;
            padding: 8px 11px; border-radius: 11px; background: #fffbeb; border: 1px solid #fde68a;
            font-size: .78rem; color: #92400e; line-height: 1.45;
        }

        /* Simulasi: baris yang sudah tidak rugi berlatar hijau tipis, baris
           titik impasnya bergaris kiri — angka saja menuntut pembacanya
           membandingkan belasan baris dengan mata. */
        /* Digulung di dalam kotaknya: enam belas baris membuat panel
           ringkasan memanjang keluar layar, dan yang pertama hilang dari
           pandangan justru harga jualnya di paling atas. */
        .rb-sim-bungkus { max-height: 232px; overflow-y: auto; border-radius: 10px; border: 1px solid #eef2f7; }
        .rb-sim { width: 100%; border-collapse: collapse; font-size: .76rem; }
        .rb-sim thead th {
            position: sticky; top: 0; z-index: 1;
            padding: 6px 8px; background: #f8fafc; border-bottom: 1px solid #e9edf3;
            font-size: .67rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
            color: var(--dsb-redup, #6b7280); white-space: nowrap;
        }
        .rb-sim td { padding: 4px 8px; border-bottom: 1px solid #f4f7fa; white-space: nowrap; }
        .rb-sim tbody tr:last-child td { border-bottom: 0; }
        .rb-sim tr.is-aman td { background: #f3faf6; }
        .rb-sim tr.is-impas td { background: #ecfdf5; }
        .rb-sim tr.is-impas td:first-child { box-shadow: inset 2px 0 0 #047857; font-weight: 700; }
        .rb-sim-pax { color: var(--dsb-redup, #6b7280); }
        .rb-sim-tanda {
            display: inline-block; margin-left: 5px; padding: 0 5px; border-radius: 999px;
            background: #d1fae5; color: #047857; font-size: .6rem; font-weight: 800;
            letter-spacing: .04em; text-transform: uppercase;
        }
        .rb-sim-modal { color: #94a3b8; text-align: right; font-variant-numeric: tabular-nums; }
        .rb-sim-laba { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }
        .rb-sim-laba.is-untung { color: #047857; }
        .rb-sim-laba.is-rugi { color: #be123c; }

        /* ===== Untung: jawaban layar ini, bukan baris keempat ===== */
        .rb-untung-kartu {
            display: flex; flex-direction: column; gap: 1px;
            margin: 10px 0 2px; padding: 11px 13px; border-radius: 13px;
            border: 1px solid var(--c); background: color-mix(in srgb, var(--c) 7%, #fff);
        }
        .rb-untung-kartu.is-sehat { --c: #047857; }
        .rb-untung-kartu.is-tipis { --c: #b45309; }
        .rb-untung-kartu.is-rugi { --c: #be123c; }
        .rb-untung-kepala {
            display: flex; align-items: center; justify-content: space-between; gap: 8px;
            font-size: .72rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
            color: var(--c);
        }
        .rb-untung-persen {
            padding: 1px 7px; border-radius: 999px; letter-spacing: 0;
            background: color-mix(in srgb, var(--c) 14%, #fff);
        }
        .rb-untung-nilai { font-size: 1.22rem; font-weight: 800; color: var(--c); line-height: 1.2; }
        .rb-untung-kaki { font-size: .74rem; color: var(--dsb-redup, #6b7280); }

        /* ===== Belum ada biaya ===== */
        .rb-belum {
            display: flex; flex-direction: column; align-items: center; gap: 5px;
            padding: 22px 16px; text-align: center;
        }
        .rb-belum-ikon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 42px; height: 42px; margin-bottom: 3px; border-radius: 13px;
            background: #f1f5f9; color: #94a3b8; font-size: 1.1rem;
        }
        .rb-belum strong { font-size: .92rem; color: var(--dsb-tinta, #1c1f26); }
        .rb-belum span:last-child { font-size: .78rem; line-height: 1.5; color: var(--dsb-redup, #6b7280); }

        .rb-sadar {
            display: flex; align-items: flex-start; gap: 8px; margin-bottom: 10px;
            padding: 9px 11px; border-radius: 11px; background: #fef2f2; border: 1px solid #fecaca;
            font-size: .76rem; color: #991b1b; line-height: 1.45; cursor: pointer;
        }
        .rb-sadar input { margin-top: 2px; flex: 0 0 auto; }
        @media (max-width: 575.98px) {
            .rab-kegiatan-aksi { justify-content: flex-end; }
        }
    </style>

    <div class="dsb">
        {{-- Menarik biaya dari master dan menyimpan kepala adalah panggilan
             ke Orcha yang bisa makan waktu. Tanpa tanda, layarnya terbaca
             seperti membeku dan orang menekan tombolnya lagi.
             display:none ditulis sendiri — lihat catatan "wire:loading kalah
             bobot CSS". --}}
        <div class="rab-muat" wire:loading.flex
            wire:target="tarikBiaya,simpanKepala,tambahDariMaster,tambahManual,pakaiKendaraan,ubahStatus,jadikanPendaftaran,hapusBiaya,simpanItinerary">
            <span class="dsb-putar"></span>
            <span>Sedang menyimpan ke Orcha…</span>
        </div>

        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Susun RAB</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Klik destinasinya; jam, tiket, dan harganya mengikuti.</span>
                    <span class="d-block">Setiap klik langsung tersimpan — tidak ada tombol Simpan yang bisa terlupa.</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.rab') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                    <i class="bi bi-arrow-left"></i><span>Daftar RAB</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        @if ($ada)
            {{-- ========================= KEPALA RAB ========================= --}}
            <section class="dsb-bagian">
            <div class="dsb-rak">
            <div class="dsb-kartu k-12 rb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                        <div class="rb-kepala-info">
                            {{-- Statusnya TIDAK dilencanakan di sini: kotak pilih di
                                 sebelah kanan sudah menuliskannya, dan dua kata yang
                                 sama di satu kartu terbaca seperti salah satunya basi. --}}
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="orcha-kode">{{ $rab['kode'] }}</span>
                                @if ($rab['kedaluwarsa'])
                                    <span class="badge bg-warning-subtle text-warning-emphasis">Lewat masa berlaku</span>
                                @endif
                            </div>
                            <h5 class="fw-bold mb-1">{{ $rab['judul'] }}</h5>
                            <div class="text-muted small mb-2">
                                {{ $rab['nama_pelanggan'] }}
                                @if ($rab['whatsapp']) · {{ $rab['whatsapp'] }} @endif
                            </div>
                        </div>

                        {{-- DUA baris yang disengaja, bukan satu baris yang kebetulan
                             pecah. Enam kendali berjajar membuat yang terakhir —
                             PDF Internal — terdampar sendirian di baris kedua,
                             dan tombol yang menggantung terbaca seperti sisa. --}}
                        <div class="rb-kepala-aksi">
                            <div class="rb-aksi-baris">
                                <select class="dsb-isian rb-isian-ringkas" wire:change="ubahStatus($event.target.value)" title="Status RAB">
                                    @foreach ($kosakata['status'] ?? [] as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected($kunci === $statusKini)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                {{-- Panel ini hanya tersimpan saat Simpan ditekan.
                                     Tanpa tanda, menutupnya atau berpindah halaman
                                     membuang perubahannya tanpa sepatah kata. --}}
                                <button type="button" wire:click="$toggle('bukaKepala')"
                                    class="dsb-tombol is-lembut {{ $this->kepalaBerubah() ? 'rab-tertahan' : '' }}"
                                    style="--ikon: {{ $this->kepalaBerubah() ? '#b45309' : '#64748b' }}">
                                    <i class="bi bi-sliders"></i> Data perjalanan
                                    @if ($this->kepalaBerubah())<span class="rab-titik" title="Ada perubahan yang belum disimpan"></span>@endif
                                </button>
                                {{-- Penawaran SELESAI disiapkan di layar ini, dan di
                                     sinilah orang menekan kirim — bukan di daftar. --}}
                                @if ($tautanWa)
                                    <a href="{{ $tautanWa }}" target="_blank" rel="noopener"
                                        class="dsb-tombol is-lembut" style="--ikon: #1f7a44"
                                        title="Kirim penawarannya lewat WhatsApp">
                                        <i class="bi bi-whatsapp"></i> WhatsApp
                                    </a>
                                @endif
                            </div>

                            <div class="rb-aksi-baris rb-aksi-berkas">
                                {{-- Nama tombolnya dipendekkan jadi "Penawaran" dan
                                     "Internal"; label inilah yang menyebut PDF sekali
                                     untuk ketiganya. --}}
                                <span class="rb-aksi-label">PDF</span>
                                {{-- Dilihat dulu. Memeriksa hasilnya berarti unduh → buka →
                                     tutup → ulang, berkali-kali dalam satu penawaran. --}}
                                <a href="{{ route('admin.orcha.rab.pdf', ['rab' => $rabId, 'jenis' => 'penawaran', 'pratinjau' => 1]) }}"
                                    target="_blank" rel="noopener" class="dsb-tombol is-lembut" style="--ikon: #0369a1"
                                    title="Lihat penawarannya tanpa mengunduh">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                                <a href="{{ route('admin.orcha.rab.pdf', ['rab' => $rabId, 'jenis' => 'penawaran']) }}" class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                                    <i class="bi bi-file-earmark-pdf"></i> Penawaran
                                </a>
                                <a href="{{ route('admin.orcha.rab.pdf', ['rab' => $rabId, 'jenis' => 'internal']) }}" class="dsb-tombol is-lembut" style="--ikon: #64748b"
                                    title="Berisi modal & margin — jangan dikirim ke pelanggan">
                                    <i class="bi bi-lock"></i> Internal
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="rab-fakta">
                        <span><i class="bi bi-geo-alt"></i> {{ $rab['daerah'] ? $rab['daerah'].', ' : '' }}{{ $rab['provinsi'] }}</span>
                        <span><i class="bi bi-calendar3"></i> {{ $tanggalTeks }}</span>
                        <span><i class="bi bi-moon-stars"></i> {{ $rab['jumlah_hari'] }} hari {{ $rab['jumlah_malam'] }} malam</span>
                        <span><i class="bi bi-people"></i> {{ $rab['jumlah_peserta'] }} peserta</span>
                    </div>

                    @if ($bukaKepala)
                        <div class="border-top mt-3 pt-3">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="dsb-label">Judul <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="kepala.judul" maxlength="150" class="dsb-isian @error('kepala.judul') is-galat @enderror">
                                    @error('kepala.judul')<div class="dsb-galat">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="dsb-label">Nama pelanggan <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="kepala.nama_pelanggan" maxlength="120" class="dsb-isian @error('kepala.nama_pelanggan') is-galat @enderror">
                                    @error('kepala.nama_pelanggan')<div class="dsb-galat">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="dsb-label">WhatsApp</label>
                                    <input type="text" wire:model="kepala.whatsapp" maxlength="32" class="dsb-isian">
                                    <div class="dsb-kartu-sub">Wajib sebelum dijadikan pendaftaran.</div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="dsb-label">Email</label>
                                    <input type="email" wire:model="kepala.email" maxlength="150" class="dsb-isian @error('kepala.email') is-galat @enderror">
                                    @error('kepala.email')<div class="dsb-galat">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="dsb-label">Kota / daerah</label>
                                    <input type="text" wire:model="kepala.daerah" maxlength="80" class="dsb-isian">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="dsb-label">Tanggal berangkat</label>
                                    <input type="date" wire:model.live="kepala.tanggal_mulai" class="dsb-isian">
                                </div>
                                <div class="col-6 col-md-3">
                                    {{-- Yang disepakati dengan pelanggan tanggalnya, bukan
                                         jumlah harinya — dan yang meleset satu hari
                                         menggeser seluruh modal akomodasi. --}}
                                    <label class="dsb-label">Tanggal pulang</label>
                                    <input type="date" wire:model.live="kepala.tanggal_pulang" class="dsb-isian"
                                        min="{{ $kepala['tanggal_mulai'] ?? '' }}">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="dsb-label">Penawaran berlaku sampai</label>
                                    <input type="date" wire:model="kepala.berlaku_sampai" class="dsb-isian">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="dsb-label">Hari</label>
                                    <input type="number" min="1" max="30" wire:model="kepala.jumlah_hari" class="dsb-isian @error('kepala.jumlah_hari') is-galat @enderror">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="dsb-label">Malam</label>
                                    <input type="number" min="0" max="30" wire:model="kepala.jumlah_malam" class="dsb-isian @error('kepala.jumlah_malam') is-galat @enderror">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="dsb-label">Peserta</label>
                                    <input type="number" min="1" max="500" wire:model="kepala.jumlah_peserta" class="dsb-isian @error('kepala.jumlah_peserta') is-galat @enderror">
                                </div>
                                @error('kepala.jumlah_hari')<div class="col-12 text-danger small mt-1">{{ $message }}</div>@enderror
                                @error('kepala.jumlah_malam')<div class="col-12 text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="col-12 col-md-6">
                                    <label class="dsb-label">Catatan di penawaran <span class="text-muted fw-normal">(dibaca pelanggan)</span></label>
                                    <textarea wire:model="kepala.catatan_penawaran" rows="3" maxlength="3000" class="dsb-isian"
                                        placeholder="Harga belum termasuk tiket pesawat PP."></textarea>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="dsb-label">Catatan internal <span class="text-muted fw-normal">(hanya di PDF internal)</span></label>
                                    <textarea wire:model="kepala.catatan" rows="3" maxlength="3000" class="dsb-isian"
                                        placeholder="Vendor, hasil negosiasi, hal yang perlu dicek."></textarea>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end pt-3">
                                @if ($this->kepalaBerubah())
                                    <span class="rab-tertahan-ket">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        Ada perubahan yang belum disimpan.
                                    </span>
                                    <button type="button" class="dsb-tombol is-lembut pcek-konfirmasi" style="--ikon: #b45309"
                                        data-action="batalKepala"
                                        data-title="Buang perubahannya?"
                                        data-text="Yang sudah diketik di panel ini akan kembali seperti semula."
                                        data-confirm="Ya, buang" data-icon="warning">Batal</button>
                                @else
                                    <button type="button" wire:click="batalKepala" class="dsb-tombol is-lembut" style="--ikon: #64748b">Batal</button>
                                @endif
                                <button type="button" wire:click="simpanKepala" wire:loading.attr="disabled" wire:target="simpanKepala" class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                                    <i class="bi bi-check2-circle"></i> Simpan
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            </div>
            </section>

            <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="k-8 rb-kolom">
                    {{-- ========================= ITINERARY ========================= --}}
                    <div class="dsb-kartu k-12 rb-kartu">
                        <div class="dsb-kartu-isi">
                            <div class="orcha-bagian-kepala">
                                <div class="orcha-bagian-nomor">1</div>
                                <div>
                                    <div class="orcha-bagian-judul">Itinerary</div>
                                    <div class="orcha-bagian-sub">Pilih harinya, lalu klik destinasinya. Jamnya ditebak sendiri (08.00, lalu +2 jam) dan tiketnya langsung ikut terhitung.</div>
                                </div>
                            </div>

                            {{-- Dua kolom: jadwal yang sedang disusun di kiri, benda
                                 yang bisa diklik di kanan.

                                 Sebelumnya semuanya bertumpuk satu kolom — deret cip
                                 melipat jadi dua baris yang baris keduanya hampir
                                 kosong, dan kartunya memanjang sampai jadwalnya sendiri
                                 terdorong keluar layar. Yang dipakai sekarang ruang
                                 mendatar yang tadinya menganggur. --}}
                            <div class="rb-itin">
                            <div class="rb-itin-jadwal">
                            <div class="rab-hari-tab mb-3">
                                @for ($h = 1; $h <= $jumlahHari; $h++)
                                    <button type="button" wire:click="pilihHari({{ $h }})"
                                        class="{{ $h === $hariAktif && ! $semuaHari ? 'aktif' : '' }}">
                                        Hari {{ $h }}<small>{{ count($kegiatanPerHari[$h] ?? []) }}</small>
                                    </button>
                                @endfor

                                {{-- Memeriksa alur lima hari berarti lima kali klik, dan
                                     membandingkan antarhari mustahil. --}}
                                <button type="button" wire:click="$toggle('semuaHari')"
                                    class="rb-semua-hari {{ $semuaHari ? 'aktif' : '' }}"
                                    title="Tampilkan seluruh hari sekaligus">
                                    <i class="bi bi-list-ul"></i> Semua
                                </button>
                            </div>

                            @unless ($semuaHari)
                                <div class="rb-hari-aksi mb-3">
                                    <span class="rb-hari-aksi-label">Hari {{ $hariAktif }}</span>

                                    @if ($jumlahHari > 1)
                                        <select class="dsb-isian rb-hari-salin"
                                            wire:change="salinHari($event.target.value); $event.target.value = ''"
                                            aria-label="Salin hari ini ke hari lain">
                                            <option value="">Salin ke hari…</option>
                                            @for ($h = 1; $h <= $jumlahHari; $h++)
                                                @if ($h !== $hariAktif)
                                                    <option value="{{ $h }}">Hari {{ $h }}</option>
                                                @endif
                                            @endfor
                                        </select>
                                    @endif

                                    @unless ($hariAktifKosong)
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus pcek-konfirmasi"
                                            data-action="kosongkanHari"
                                            data-title="Kosongkan hari {{ $hariAktif }}?"
                                            data-text="{{ count($kegiatanAktif) }} kegiatan akan dibuang, dan ini tidak bisa diurungkan."
                                            data-confirm="Ya, kosongkan" data-icon="warning"
                                            title="Buang seluruh kegiatan hari ini">
                                            <i class="bi bi-eraser"></i>
                                        </button>
                                    @endunless
                                </div>
                            @endunless

                            {{-- Urungkan: dialog menahan kesalahan yang disadari; yang
                                 tidak disadari — klik meleset di baris sebelahnya — hanya
                                 bisa ditolong oleh ini. --}}
                            @if ($kegiatanTerhapus !== [])
                                <div class="rb-urung mb-3">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    <span class="rb-urung-teks">
                                        <strong>{{ $kegiatanTerhapus['nama'] ?: 'Kegiatan tanpa nama' }}</strong> dihapus.
                                    </span>
                                    <button type="button" wire:click="urungkanHapus" class="dsb-tombol is-awas">
                                        <i class="bi bi-arrow-counterclockwise"></i><span>Kembalikan</span>
                                    </button>
                                    <button type="button" wire:click="lupakanUrungkan" class="dsb-tombol is-lembut" title="Tutup">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            @endif

                            @if ($hariAktifKosong)
                                <div class="rb-hari-kosong">
                                    <span class="dsb-ikon is-kecil" style="--c: #94a3b8"><i class="bi bi-calendar-x"></i></span>
                                    <div>
                                        <strong>Hari {{ $hariAktif }} masih kosong</strong>
                                        <span class="dsb-kartu-sub">Klik destinasi atau kegiatan di bawah. Dibiarkan kosong, di PDF tertulis "waktu bebas".</span>
                                    </div>
                                </div>
                            @endif

                            @foreach ($hariDitampilkan as $hariIni => $kegiatanHari)
                                @if ($semuaHari)
                                    {{-- Kepala hari hanya muncul di mode semua hari: pada
                                         satu hari ia mengulang apa yang sudah tertulis di
                                         tab yang sedang menyala. --}}
                                    <div class="rb-hari-kepala">
                                        <span>Hari {{ $hariIni }}</span>
                                        <small>{{ count($kegiatanHari) }} kegiatan</small>
                                    </div>
                                @endif

                            {{-- Seret hanya pada satu hari: di mode semua hari, urutan
                                 yang dikirim akan bercampur antarhari dan hasilnya
                                 susunan yang tidak diminta siapa pun. --}}
                            <div @if (! $semuaHari) x-data x-init="pasangSeretUrut($el, 'susunUlang')" @endif>
                            @foreach ($kegiatanHari as $i => $k)
                                @php [$labelJenis, $warnaJenis] = $rupaDari($k); @endphp
                                <div class="rab-kegiatan" style="--c: {{ $warnaJenis }}"
                                    data-urut="{{ $i }}"
                                    wire:key="kg-{{ $i }}-{{ md5($k['nama'].$k['hari_ke']) }}">
                                    {{-- Rentang waktu, bukan satu jam. Yang ditanyakan
                                         pembaca jadwal bukan "mulai kapan" melainkan
                                         "memakan waktu berapa lama, dan kapan rombongan
                                         bisa bergerak lagi". Lamanya dihitung Orcha dari
                                         kedua jam ini — tidak disimpan, jadi tidak bisa
                                         berselisih dengan jamnya. --}}
                                    <div class="rab-jam rab-waktu">
                                        <div class="rab-waktu-isian">
                                            <input type="time" wire:model.blur="itinerary.{{ $i }}.jam"
                                                class="dsb-isian" title="Jam mulai" aria-label="Jam mulai">
                                            <span class="rab-waktu-pisah">–</span>
                                            <input type="time" wire:model.blur="itinerary.{{ $i }}.jam_selesai"
                                                class="dsb-isian" title="Jam selesai" aria-label="Jam selesai">
                                        </div>
                                    </div>
                                    <input type="text" wire:model.blur="itinerary.{{ $i }}.nama" maxlength="150" class="dsb-isian fw-semibold rab-nama"
                                        title="{{ $k['destinasi'] ? 'Destinasi: '.$k['destinasi'] : 'Kegiatan' }}">
                                    {{-- Lamanya di sini, bukan menggantung di bawah kotak
                                         jam: di sana ia menambah satu baris sendiri dan
                                         menyisakan ruang kosong di sebelahnya. Di sini ia
                                         mengisi baris yang memang sudah ada. --}}
                                    <div class="rab-ket-baris">
                                        <span class="rab-pil-jenis">{{ $labelJenis }}</span>
                                        @if (($k['durasi_teks'] ?? '') !== '')
                                            <span class="rab-pil-lama">{{ $k['durasi_teks'] }}</span>
                                        @endif
                                        <input type="text" wire:model.blur="itinerary.{{ $i }}.keterangan" maxlength="500"
                                            class="dsb-isian rab-ket" placeholder="Keterangan untuk pelanggan (boleh kosong)">
                                    </div>
                                    <div class="rab-kegiatan-aksi">
                                        @unless ($semuaHari)
                                            <span class="rb-pegangan" data-seret-pegangan title="Seret untuk memindahkan">
                                                <i class="bi bi-grip-vertical"></i>
                                            </span>
                                        @endunless
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-lihat" wire:click="geser({{ $i }}, -1)"
                                            title="Naikkan" @disabled($i === $idxPertama)><i class="bi bi-arrow-up"></i></button>
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-lihat" wire:click="geser({{ $i }}, 1)"
                                            title="Turunkan" @disabled($i === $idxTerakhir)><i class="bi bi-arrow-down"></i></button>
                                        <select class="dsb-isian" title="Pindah ke hari lain"
                                            wire:change="pindahHari({{ $i }}, $event.target.value)">
                                            @for ($h = 1; $h <= $jumlahHari; $h++)
                                                <option value="{{ $h }}" @selected($h === $hariAktif)>H{{ $h }}</option>
                                            @endfor
                                        </select>
                                        {{-- Dikonfirmasi, sama seperti menghapus biaya di
                                             sebelahnya. Satu kegiatan bisa berisi jam,
                                             keterangan, dan destinasi yang diketik tangan. --}}
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus pcek-konfirmasi"
                                            data-action="hapusKegiatan" data-arg="{{ $i }}"
                                            data-title="Hapus kegiatan ini?"
                                            data-text="{{ $k['nama'] ?: 'Kegiatan tanpa nama' }}"
                                            data-confirm="Ya, hapus" data-icon="warning" title="Hapus kegiatan">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                            @endforeach

                            {{-- Kegiatan yang tidak ada di daftar mana pun. Tetap di
                                 kolom jadwal, karena ia menambah ke hari yang sedang
                                 dibuka — bukan memilih dari persediaan. --}}
                            <div class="rb-gandeng rb-kegiatan-bebas">
                                <input type="text" wire:model="kegiatanBaru" wire:keydown.enter.prevent="tambahKegiatan" maxlength="150"
                                    class="dsb-isian" placeholder="Kegiatan lain…">
                                <button type="button" wire:click="tambahKegiatan" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                    <i class="bi bi-plus-lg"></i> Tambah
                                </button>
                            </div>
                            </div>

                            {{-- Kolom kanan: persediaan yang tinggal diklik. Satu klik
                                 mengganti sekitar lima belas ketukan, dan jamnya ikut
                                 ditebak — yang tersisa cuma membetulkan yang memang
                                 perlu dibetulkan. --}}
                            <aside class="rb-itin-pilih">
                                <div class="rb-pilih-kepala">
                                    <div class="rb-pilih-judul">
                                        <strong>Tambah ke Hari {{ $hariAktif }}</strong>
                                        <span class="dsb-kartu-sub">
                                            <i class="bi bi-ticket-perforated rab-tiket"></i> bertiket = harganya sudah ada
                                        </span>
                                    </div>
                                    <input type="text" wire:model.live.debounce.300ms="cariDestinasi"
                                        class="dsb-isian rb-pilih-cari" placeholder="Cari destinasi…">
                                </div>

                                <div class="rab-destinasi">
                                @foreach ($destinasi as $d)
                                    <button type="button" wire:key="ds-{{ md5($d['nama']) }}"
                                        wire:click="tambahDestinasi(@js($d['nama']))"
                                        class="{{ isset($destinasiDipakai[$d['nama']]) ? 'dipakai' : '' }}"
                                        title="{{ $d['daerah'] }}">
                                        @if ($d['punya_tiket'])<i class="bi bi-ticket-perforated tiket"></i>@endif
                                        {{ $d['nama'] }}
                                    </button>
                                @endforeach
                                @if ($cariTanpaHasil)
                                    {{-- Kata yang dicari sering memang bukan destinasi:
                                         nama di rincian biaya datang dari MASTER HARGA,
                                         daftar yang berbeda dari katalog destinasi. Yang
                                         ditawarkan di sini jalan keluarnya, bukan
                                         sekadar kabar bahwa hasilnya nihil. --}}
                                    <div class="rb-pilih-kosong">
                                        <p class="mb-2">Tidak ada destinasi bernama <strong>{{ $cariDestinasi }}</strong> di katalog provinsi ini.</p>
                                        <div class="rb-kosong-aksi">
                                            <button type="button" wire:click="tambahDariCari" class="dsb-tombol is-lembut" style="--ikon: #047857">
                                                <i class="bi bi-plus-lg"></i> Tambah sebagai kegiatan
                                            </button>
                                            <button type="button" wire:click="$set('cariDestinasi', '')" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                                <i class="bi bi-x-circle"></i> Kosongkan pencarian
                                            </button>
                                        </div>
                                        <p class="mt-2 mb-0">Mencari nama yang ada di rincian biaya? Itu dari <strong>Master Harga</strong>, daftar yang berbeda dari katalog destinasi.</p>
                                    </div>
                                @elseif ($katalogKosong)
                                    <div class="rb-pilih-kosong">Tidak ada destinasi katalog untuk provinsi ini. Tambahkan lewat menu Destinasi Populer, atau ketik sebagai kegiatan lain.</div>
                                @endif
                                </div>

                                <div class="rb-pilih-pisah">Kegiatan umum</div>
                                <div class="rb-baku-deret">
                                    @foreach ($this::KEGIATAN_BAKU as $kb)
                                        <button type="button" wire:key="kb-{{ md5($kb) }}"
                                            wire:click="tambahKegiatanBaku(@js($kb))"
                                            wire:loading.attr="disabled" wire:target="tambahKegiatanBaku"
                                            class="dsb-chip rb-chip-tombol">{{ $kb }}</button>
                                    @endforeach
                                </div>
                            </aside>
                            </div>
                        </div>
                    </div>

                    {{-- ========================= BIAYA ========================= --}}
                    <div class="dsb-kartu k-12 rb-kartu">
                        <div class="dsb-kartu-isi">
                            <div class="orcha-bagian-kepala">
                                <div class="orcha-bagian-nomor">2</div>
                                <div>
                                    <div class="orcha-bagian-judul">Rincian biaya</div>
                                    <div class="orcha-bagian-sub">Tiket dari itinerary ikut masuk dan ikut pergi sendiri. Sisanya — armada, hotel, pemandu — dipilih dari master harga.</div>
                                </div>
                            </div>

                            {{-- ===== Tiket dihapus, tempatnya masih dikunjungi =====

                                 Arah sebaliknya. Dibiarkan begitu, tiketnya akan
                                 tertarik lagi pada "Tarik ulang" berikutnya —
                                 penghapusan yang hidup lagi tanpa ada yang
                                 menyadarinya. Itinerary tidak diubah diam-diam:
                                 ia rencana yang dibaca pelanggan. --}}
                            @if ($tiketTertinggal !== '')
                                <div class="rab-tertinggal">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span class="rab-tertinggal-teks">
                                        Kunjungan ke <strong>{{ $tiketTertinggal }}</strong> masih ada di itinerary,
                                        jadi tiketnya akan ikut tertarik lagi saat <strong>Tarik ulang</strong>.
                                    </span>
                                    <button type="button" wire:click="buangKunjungan"
                                        wire:loading.attr="disabled" wire:target="buangKunjungan"
                                        class="dsb-tombol is-awas">
                                        <i class="bi bi-calendar-x" wire:loading.remove wire:target="buangKunjungan"></i>
                                        <span class="dsb-putar" wire:loading wire:target="buangKunjungan"></span>
                                        <span>Buang juga dari itinerary</span>
                                    </button>
                                    <button type="button" wire:click="lupakanTertinggal" class="dsb-tombol is-lembut"
                                        title="Biarkan kunjungannya">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            @endif

                            {{-- ===== Kendaraan yang muat untuk rombongan ini =====

                                 Master harga menyimpan "kendaraan ini muat berapa";
                                 di sinilah angka itu bertemu jumlah peserta. Tanpa
                                 panel ini admin membagi di kepala setiap kali — dan
                                 yang paling sering terjadi bukan salah hitung,
                                 melainkan tidak dihitung sama sekali: dipilih Hiace
                                 karena itu yang biasa, satu unit, lalu dua belas
                                 orang tidak kebagian kursi. --}}
                            @php($saranKendaraan = $this->saranKendaraan())
                            @if ($saranKendaraan !== [])
                                <div class="rb-armada">
                                    <div class="rb-armada-kepala">
                                        <span class="dsb-ikon is-kecil" style="--c: #0369a1"><i class="bi bi-bus-front"></i></span>
                                        <div class="rb-armada-teks">
                                            <strong>Kendaraan untuk {{ $rab['jumlah_peserta'] }} peserta</strong>
                                            <span class="dsb-kartu-sub">
                                                Termurah lebih dulu. Angkanya sudah termasuk {{ $rab['jumlah_hari'] }} hari —
                                                dihitung dengan rumus yang sama dengan rincian di bawah.
                                            </span>
                                        </div>
                                    </div>

                                    <div class="rb-armada-deret">
                                        @foreach ($saranKendaraan as $i => $k)
                                            <div class="rb-armada-pil {{ $i === 0 ? 'is-saran' : '' }} {{ $k['sudah'] ? 'is-dipakai' : '' }}">
                                                <div class="rb-armada-nama">
                                                    {{ $k['nama'] }}
                                                    @if ($i === 0)<span class="rb-armada-tanda">paling murah</span>@endif
                                                </div>
                                                <div class="rb-armada-hitung">
                                                    <span class="rb-armada-unit">{{ $k['unit'] }} unit</span>
                                                    <span class="dsb-kartu-sub">
                                                        muat {{ $k['muat'] }}/unit ·
                                                        @if ($k['sisa'] === 0)
                                                            pas, tanpa kursi kosong
                                                        @else
                                                            {{ $k['sisa'] }} kursi kosong
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="rb-armada-angka">{{ 'Rp '.number_format($k['total'], 0, ',', '.') }}</div>
                                                @if ($k['sudah'])
                                                    <span class="rb-armada-sudah"><i class="bi bi-check2"></i> sudah di RAB</span>
                                                @else
                                                    <button type="button" wire:click="pakaiKendaraan({{ $k['id'] }})"
                                                        wire:loading.attr="disabled" wire:target="pakaiKendaraan"
                                                        class="dsb-tombol is-lembut" style="--ikon: #0369a1">
                                                        <i class="bi bi-plus-lg"></i><span>Pakai</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex flex-wrap gap-2 align-items-center mb-3 rb-biaya-bilah">
                                <button type="button" wire:click="tarikBiaya" wire:loading.attr="disabled" wire:target="tarikBiaya"
                                    class="dsb-tombol is-lembut" style="--ikon: #64748b"
                                    title="Tarik ulang biaya dari itinerary — untuk destinasi yang harga tiketnya baru diisi di master setelah itinerary dibuat">
                                    <i class="bi bi-arrow-repeat"></i> Tarik ulang
                                </button>
                                <div class="rb-biaya-pilih">
                                    <div class="rb-gandeng">
                                        <select wire:model="pilihMaster" class="dsb-isian">
                                            <option value="" @selected((string) $this->pilihMaster === '')>Pilih dari master harga…</option>
                                            @foreach ($masterPerKategori as $label => $isi)
                                                <optgroup label="{{ $label }}">
                                                    @foreach ($isi as $m)
                                                        <option value="{{ $m['id'] }}" @selected((string) $this->pilihMaster === (string) $m['id'])>{{ $m['nama'] }} — {{ $m['harga_teks'] }} / {{ $m['satuan_label'] }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        <button type="button" wire:click="tambahDariMaster" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                            <i class="bi bi-plus-lg"></i> Tambah
                                        </button>
                                    </div>
                                </div>

                                {{-- Di bilah ini, bukan sebagai tautan menggantung di
                                     kiri bawah kartu. Ia sejenis dengan dua tombol di
                                     sebelahnya — sama-sama cara memasukkan biaya — dan
                                     kartunya jadi bisa ditutup oleh angkanya. --}}
                                <button type="button" wire:click="$set('bukaManual', true)"
                                    class="dsb-tombol is-lembut" style="--ikon: #b45309"
                                    title="Untuk biaya yang belum ada di master harga">
                                    <i class="bi bi-pencil"></i> Biaya manual
                                </button>
                            </div>

                            @if ($adaTanpaTiket)
                                <div class="alert alert-warning border-0 rounded-3 small py-2">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Belum ada harga tiket di master untuk: <strong>{{ implode(', ', $tanpaTiket) }}</strong>.
                                    Bila memang berbayar, tambahkan manual di bawah atau isi di
                                    <a href="{{ route('admin.orcha.master-harga') }}" target="_blank">Master Harga</a>.
                                </div>
                            @endif

                            @if ($hargaBasi)
                                <div class="alert alert-danger border-0 rounded-3 small py-2">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Sebagian harga master sudah berubah sejak masuk ke RAB ini. Baris yang bertanda merah masih memakai harga lama.
                                </div>
                            @endif

                            <div class="orcha-gulung">
                                {{-- Kolom "Perhitungan" dibubarkan: isinya kalimat pendek
                                     ("Rp 10.000 × 10 orang") di kolom selebar sepertiga
                                     tabel, jadi tiap baris menyisakan lubang di
                                     tengahnya. Kini ia menempel di bawah nama biayanya,
                                     tempat ia memang menjelaskan. --}}
                                <table class="table align-middle orcha-tabel rab-tabel-biaya mb-0">
                                    <thead>
                                        <tr>
                                            <th>Biaya</th>
                                            <th style="width: 130px" class="text-end">Harga satuan</th>
                                            <th style="width: 84px" class="text-end">Jumlah</th>
                                            <th style="width: 130px" class="text-end">Subtotal</th>
                                            <th style="width: 48px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($barisPerKategori as $label => $isi)
                                            {{-- Pita kategori membawa angkanya sendiri. Sebagai
                                                 nama yang melintang sendirian, ia cuma garis
                                                 pemisah yang kebetulan bertulisan. --}}
                                            <tr class="rab-kat">
                                                <td colspan="3">{{ $label }}</td>
                                                <td class="text-end">{{ $totalKategori[$label]['total_teks'] ?? '' }}</td>
                                                <td></td>
                                            </tr>
                                            @foreach ($isi as $b)
                                                <tr wire:key="by-{{ $b['id'] }}">
                                                    <td>
                                                        <div class="rab-biaya-nama">{{ $b['nama'] }}</div>
                                                        {{-- Dari siapa biaya ini dibeli, berikut
                                                             nomornya. Dibaca hidup dari daftar
                                                             vendor, jadi yang tertulis selalu
                                                             nomor yang berlaku hari ini — bukan
                                                             nomor saat penawaran dibuat.

                                                             Ada di sini supaya menelepon vendor
                                                             tidak menuntut pindah menu: yang
                                                             harus dicari di layar lain adalah
                                                             yang tidak sempat dicari. --}}
                                                        @if (! empty($b['vendor']))
                                                            <div class="rab-biaya-vendor">
                                                                <i class="bi bi-shop"></i>
                                                                {{-- Buntu sebelumnya: vendornya terbaca,
                                                                     tetapi rinciannya hanya bisa dibuka
                                                                     lewat menu lain. --}}
                                                                <a href="{{ route('admin.orcha.vendor.detail', $b['vendor_id']) }}" wire:navigate
                                                                    class="rab-vendor-tautan">{{ $b['vendor'] }}</a>
                                                                @if (! empty($b['vendor_kontak']))
                                                                    <span class="rab-vendor-nomor">{{ $b['vendor_kontak'] }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <div class="rab-biaya-hitung">
                                                            <span class="rab-pil-satuan">{{ $b['satuan_label'] }}</span>
                                                            @if ($b['kapasitas'])
                                                                <span class="rab-pil-satuan">kapasitas {{ $b['kapasitas'] }}</span>
                                                            @endif
                                                            <span class="rab-hitung-teks">{{ $b['penjelasan'] }}</span>
                                                        </div>
                                                        @isset($b['harga_master_kini'])
                                                            {{-- KEDUA arah ditawarkan, dan sengaja tidak ditebak
                                                                 mana yang dimaksud.

                                                                 Angkanya sendiri tidak bisa membedakan "tim
                                                                 menemukan hotel lebih murah" dari "harga master
                                                                 naik": keduanya sama-sama membuat baris ini lebih
                                                                 murah daripada master. Yang tahu bedanya cuma orang
                                                                 yang menyuntingnya, jadi dialah yang memilih. --}}
                                                            <div class="rab-basi is-beda">
                                                                <i class="bi bi-arrow-left-right"></i>
                                                                Master Rp {{ number_format($b['harga_master_kini'], 0, ',', '.') }} ·
                                                                baris ini Rp {{ $this->selisihMasterTeks($b) }} lebih
                                                                {{ $this->temuanLebihMurah($b) ? 'murah' : 'mahal' }} ·
                                                                <a href="#" wire:click.prevent="pakaiHargaMaster({{ $b['id'] }})"
                                                                    title="Harga master yang dipakai di baris ini">pakai harga master</a> ·
                                                                <a href="#" wire:click.prevent="simpanKeMaster({{ $b['id'] }})"
                                                                    title="Master harga ikut berubah, dan RAB berikutnya memakai angka ini">simpan ke master</a>
                                                            </div>
                                                            {{-- Harga yang berbeda tanpa sebabnya tidak bisa dipakai
                                                                 lagi: beberapa bulan kemudian tidak ada yang bisa
                                                                 membedakannya dari salah ketik. --}}
                                                            <input type="text" maxlength="200" class="dsb-isian rb-catatan-baris"
                                                                wire:model.blur="catatanBaris.{{ $b['id'] }}"
                                                                placeholder="Kenapa berbeda? mis. hasil nego, low season, kamar triple">
                                                        @endisset
                                                    </td>
                                                    <td data-judul="Harga satuan"><input type="text" inputmode="numeric" wire:model.blur="hargaBaris.{{ $b['id'] }}" class="dsb-isian"></td>
                                                    <td data-judul="Jumlah"><input type="number" min="1" max="100" wire:model.blur="jumlahBaris.{{ $b['id'] }}" class="dsb-isian"></td>
                                                    <td data-judul="Subtotal" class="text-end fw-bold text-nowrap">{{ $b['subtotal_teks'] }}</td>
                                                    <td class="text-end rab-sel-aksi">
                                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus pcek-konfirmasi"
                                                            data-action="hapusBiaya" data-arg="{{ $b['id'] }}"
                                                            data-title="Hapus {{ $b['nama'] }}?" data-confirm="Ya, hapus" data-icon="warning"
                                                            title="Hapus biaya">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        @if ($tanpaBiaya)
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    Belum ada biaya. Klik satu destinasi di atas — tiketnya ikut terhitung sendiri.
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                    {{-- Kaki tabel menutup kartunya dengan angka yang
                                         memang dicari mata sesudah membaca rinciannya —
                                         sebelumnya kartunya berakhir pada satu tautan
                                         yang menggantung sendirian di kiri bawah. --}}
                                    @if (! $tanpaBiaya)
                                        <tfoot>
                                            <tr class="rab-kaki">
                                                <td colspan="3">Modal total</td>
                                                <td class="text-end">{{ $r['modal_total_teks'] ?? 'Rp 0' }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>

                            <div class="{{ $bukaManual ? 'mt-3' : '' }}">
                                @if ($bukaManual)
                                    <div class="border rounded-4 p-3">
                                        <div class="fw-semibold small mb-2">Biaya manual</div>
                                        <div class="row g-2">
                                            <div class="col-12 col-md-4">
                                                <input type="text" wire:model="manual.nama" maxlength="150" class="dsb-isian @error('manual.nama') is-galat @enderror" placeholder="Dokumentasi drone">
                                                @error('manual.nama')<div class="dsb-galat">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select wire:model="manual.kategori" class="dsb-isian">
                                                    @foreach ($kosakata['kategori'] ?? [] as $kunci => $label)
                                                        <option value="{{ $kunci }}" @selected((string) ($this->manual['kategori'] ?? '') === (string) $kunci)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select wire:model.live="manual.satuan" class="dsb-isian">
                                                    @foreach ($kosakata['satuan'] ?? [] as $kunci => $label)
                                                        <option value="{{ $kunci }}" @selected((string) ($this->manual['satuan'] ?? '') === (string) $kunci)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <input type="text" inputmode="numeric" wire:model.blur="manual.harga_satuan" class="dsb-isian @error('manual.harga_satuan') is-galat @enderror" placeholder="Harga">
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <input type="number" min="1" wire:model="manual.kapasitas" class="dsb-isian @error('manual.kapasitas') is-galat @enderror"
                                                    placeholder="{{ $manualBerkapasitas ? 'Kapasitas' : '—' }}" @disabled(! $manualBerkapasitas)>
                                            </div>
                                            {{-- Justru di biaya manual vendor dadakan muncul:
                                                 sewa perahu, jip, pemandu lokal. Tanpa kotak ini
                                                 biaya manual tidak pernah bisa punya kontak. --}}
                                            <div class="col-12">
                                                <select wire:model="manual.vendor_id" class="dsb-isian">
                                                    <option value="" @selected((string) ($this->manual['vendor_id'] ?? '') === '')>Vendor — belum ditentukan</option>
                                                    @foreach ($katalog['vendor'] ?? [] as $v)
                                                        <option value="{{ $v['id'] }}" @selected((string) ($this->manual['vendor_id'] ?? '') === (string) $v['id'])>{{ $v['nama'] }}@if (! empty($v['kontak'])) — {{ $v['kontak'] }}@endif</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 justify-content-end mt-2">
                                            <button type="button" wire:click="$set('bukaManual', false)" class="dsb-tombol is-lembut" style="--ikon: #64748b">Batal</button>
                                            <button type="button" wire:click="tambahManual" class="dsb-tombol is-lembut" style="--ikon: #4f46e5"><i class="bi bi-plus-lg"></i> Tambahkan</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================= RINGKASAN ========================= --}}
                <div class="k-4 rb-kolom">
                    <div class="rab-lengket">
                        <div class="dsb-kartu k-12 rb-kartu">
                            <div class="dsb-kartu-isi">
                                {{-- $tanpaBiaya yang sudah dipakai tabel di sebelah,
                                     bukan penanda kembar: dua penanda untuk keadaan yang
                                     sama akan berselisih begitu salah satunya diubah. --}}
                                @if ($tanpaBiaya)
                                    {{-- Satu kalimat, bukan sembilan angka nol berjajar.

                                         Angka nol yang berderet terbaca seperti hitungan
                                         yang gagal, bukan seperti pekerjaan yang belum
                                         dimulai — dan inilah yang pertama dilihat admin
                                         saat RAB-nya baru dibuat. --}}
                                    <div class="rb-belum">
                                        <span class="rb-belum-ikon"><i class="bi bi-calculator"></i></span>
                                        <strong>Angkanya muncul setelah ada biaya</strong>
                                        <span>
                                            Tarik tiket dari itinerary, atau pilih armada &amp; hotel di
                                            <strong>Rincian biaya</strong>. Harga jual, untung, dan titik
                                            impasnya dihitung dari situ.
                                        </span>
                                    </div>
                                @else
                                    <div class="orcha-label-kecil mb-1">Harga per orang</div>
                                    <div class="rab-harga-besar">{{ $r['harga_per_orang_teks'] ?? 'Rp 0' }}</div>
                                    <div class="text-muted small mb-3">Total {{ $r['harga_total_teks'] ?? 'Rp 0' }} untuk {{ $rab['jumlah_peserta'] }} peserta</div>

                                    {{-- Angka besar di atas berhenti jadi hasil hitungan
                                         begitu ada kesepakatan. Tanpa baris ini, admin yang
                                         mengubah margin lalu melihat harganya tidak bergerak
                                         akan mengira layarnya rusak. --}}
                                    @if ($negoAktif)
                                        <div class="rb-nego-tanda">
                                            <i class="bi bi-hand-thumbs-up"></i>
                                            <span>Harga <strong>disepakati</strong>, bukan dihitung — hitungannya {{ $r['harga_asli_teks'] ?? 'Rp 0' }}.</span>
                                        </div>
                                    @endif

                                    {{-- Pada open trip harga dan modal dihitung dari jumlah
                                         yang BERBEDA; tanpa disebut, angka di atas terbaca
                                         seolah dibagi jumlah peserta yang sama. --}}
                                    @if ($openTrip)
                                        {{-- Kalimatnya dibungkus satu <span>: pembungkusnya
                                             flex, dan tanpa ini tiap potongan teks jadi item
                                             flex sendiri — kata bercetak tebal di tengah
                                             kalimat terlempar ke kolomnya sendiri. --}}
                                        <div class="rb-dasar">
                                            <i class="bi bi-megaphone"></i>
                                            <span>Open trip · harga dihitung atas dasar <strong>{{ $pesertaDasar }} peserta</strong>, modal di bawah ini atas {{ $rab['jumlah_peserta'] }} peserta.</span>
                                        </div>
                                    @endif

                                    <div class="rab-baris-angka"><span>Modal total</span><strong>{{ $r['modal_total_teks'] ?? 'Rp 0' }}</strong></div>
                                    <div class="rab-baris-angka text-muted"><span>Modal per kepala</span><span>{{ $r['modal_per_kepala_teks'] ?? 'Rp 0' }}</span></div>
                                    <div class="rab-baris-angka"><span>Margin diminta</span><span>{{ $r['margin_diminta_teks'] ?? 'Rp 0' }}</span></div>

                                    {{-- PELANGGAN MENAWAR.

                                         Harga jual biasanya diturunkan dari modal + margin.
                                         Saat menawar arahnya terbalik: yang diketahui admin
                                         adalah angkanya, bukan margin berapa persen yang
                                         kebetulan menghasilkan angka itu. Tanpa panel ini
                                         satu-satunya jalan adalah mengutak-atik margin
                                         sampai harganya kebetulan jatuh pas — dan karena
                                         pembulatannya ke atas, angka persis sering memang
                                         tidak bisa dicapai. --}}
                                    <div class="rb-nego {{ $negoAktif ? 'is-hidup' : '' }}">
                                        <button type="button" wire:click="$toggle('bukaNego')" class="rb-nego-kepala">
                                            <span class="dsb-ikon is-kecil" style="--c: {{ $negoAktif ? '#4f46e5' : '#94a3b8' }}">
                                                <i class="bi bi-chat-left-dots"></i>
                                            </span>
                                            <span class="rb-nego-judul">
                                                <strong>Pelanggan menawar</strong>
                                                <span class="dsb-kartu-sub">
                                                    @if ($negoAktif)
                                                        Disepakati {{ $r['harga_per_orang_teks'] ?? 'Rp 0' }} · {{ $negoTurun ? 'turun' : 'naik' }} {{ $negoSelisihTeks }}
                                                    @else
                                                        Ketik harga yang diminta, untungnya dihitung ulang
                                                    @endif
                                                </span>
                                            </span>
                                            <i class="bi bi-chevron-down rb-nego-panah {{ $bukaNego ? 'is-buka' : '' }}"></i>
                                        </button>

                                        @if ($bukaNego)
                                            <div class="rb-nego-isi">
                                                <div class="dsb-medan">
                                                    <label class="dsb-label" for="rb-nego-harga">Harga disepakati per orang</label>
                                                    <input id="rb-nego-harga" type="text" inputmode="numeric" class="dsb-isian"
                                                        wire:model.blur="hargaNego" placeholder="{{ $r['harga_asli_teks'] ?? 'Rp 0' }}">
                                                    <span class="dsb-kartu-sub">Kosongkan untuk kembali ke harga hitungan.</span>
                                                </div>

                                                <div class="dsb-medan">
                                                    <label class="dsb-label" for="rb-nego-alasan">Alasan</label>
                                                    <input id="rb-nego-alasan" type="text" maxlength="200" class="dsb-isian"
                                                        wire:model.blur="alasanNego" placeholder="mis. pelanggan lama, rombongan ketiga tahun ini">
                                                    <span class="dsb-kartu-sub">Ikut tercetak di PDF internal, tidak di penawaran.</span>
                                                </div>

                                                {{-- Batas aman diberikan sebagai TOMBOL, bukan sekadar
                                                     angka yang harus diketik ulang. Mengetik ulang angka
                                                     yang tertulis di sebelahnya adalah kesempatan salah
                                                     ketik yang tidak perlu ada — dan salah ketiknya
                                                     berupa harga di bawah modal. --}}
                                                <div class="rb-nego-batas">
                                                    <span>
                                                        Di bawah <strong>{{ $r['harga_batas_teks'] ?? 'Rp 0' }}</strong> per orang, trip ini rugi.
                                                    </span>
                                                    <button type="button" wire:click="pakaiBatasAman" class="dsb-tombol is-lembut" style="--ikon: #b45309">
                                                        <i class="bi bi-shield-check"></i><span>Pakai batas aman</span>
                                                    </button>
                                                </div>

                                                @if ($negoAktif)
                                                    <button type="button" wire:click="hapusNego" class="dsb-tombol is-lembut rb-nego-lepas" style="--ikon: #64748b">
                                                        <i class="bi bi-arrow-counterclockwise"></i><span>Lepas kesepakatan</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Kesepakatan di bawah modal TIDAK ditolak: kadang
                                             memang itu keputusannya. Yang wajib adalah
                                             angkanya terlihat. --}}
                                        @if ($negoRugi)
                                            <div class="rb-nego-rugi">
                                                <i class="bi bi-exclamation-octagon"></i>
                                                <span>Harga ini <strong>di bawah modal</strong> — trip ini rugi {{ $r['untung_teks'] ?? 'Rp 0' }}.</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Untung diangkat jadi kartunya sendiri: ia jawaban
                                         dari seluruh layar ini, bukan baris keempat dalam
                                         deretan yang semuanya berbobot sama. --}}
                                    @php($untungSehat = (float) ($r['persen_untung'] ?? 0) >= 10)
                                    @php($untungRugi = (float) ($r['persen_untung'] ?? 0) < 0)
                                    <div class="rb-untung-kartu {{ $untungRugi ? 'is-rugi' : ($untungSehat ? 'is-sehat' : 'is-tipis') }}">
                                        <div class="rb-untung-kepala">
                                            <span>Untung</span>
                                            @isset($r['persen_untung'])
                                                <span class="rb-untung-persen">{{ str_replace('.', ',', $r['persen_untung']) }}%</span>
                                            @endisset
                                        </div>
                                        <strong class="rb-untung-nilai">{{ $r['untung_teks'] ?? 'Rp 0' }}</strong>
                                        <span class="rb-untung-kaki">{{ $r['untung_per_orang_teks'] ?? 'Rp 0' }} per orang</span>
                                    </div>

                                    {{-- Titik impas.

                                         Sewa bus, honor koordinator, dan dokumentasi dibayar
                                         sama saja entah yang berangkat sepuluh atau lima belas
                                         orang — dan itulah yang membuat rombongan kecil
                                         diam-diam merugi walau harganya terlihat untung di
                                         atas kertas. --}}
                                    <div class="rb-impas {{ $impasTercapai ? 'is-aman' : 'is-waspada' }}">
                                        <span class="dsb-ikon is-kecil" style="--c: {{ $impasTercapai ? '#047857' : '#b45309' }}">
                                            <i class="bi {{ $impasTercapai ? 'bi-check2-circle' : 'bi-exclamation-triangle' }}"></i>
                                        </span>
                                        <div class="rb-impas-teks">
                                            <span class="dsb-label mb-0">Balik modal mulai</span>
                                            @if ($adaImpas)
                                                <strong>{{ $impasPeserta }} peserta</strong>
                                                <span class="dsb-kartu-sub">
                                                    Biaya tetap {{ $r['modal_tetap_teks'] ?? 'Rp 0' }} ·
                                                    tiap kepala menyumbang {{ $r['sumbangan_per_orang_teks'] ?? 'Rp 0' }}
                                                </span>
                                            @else
                                                <strong>Belum bisa dihitung</strong>
                                                <span class="dsb-kartu-sub">
                                                    Harga jualnya belum menutup biaya yang menempel per orang —
                                                    menambah peserta tidak menolong.
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Tabel yang menjawab pertanyaan sebenarnya sebelum
                                         memberangkatkan open trip: "dapat sekian orang, kita
                                         untung atau rugi?"

                                         Dihitung ulang di tiap jumlah, bukan dibagi rata —
                                         biaya tetapnya ikut berubah saat kamar atau armada
                                         bertambah, dan tangga itulah yang membuat satu
                                         peserta tambahan kadang justru menambah rugi. --}}
                                    @if ($openTrip && $simulasi !== [])
                                        <div class="border-top mt-3 pt-3">
                                            <div class="orcha-label-kecil mb-2">Kalau yang datang sekian</div>
                                            {{-- Enam belas baris tanpa judul kolom menuntut
                                                 pembacanya menebak angka mana yang mana.
                                                 Digulung di dalam kotaknya sendiri supaya
                                                 panelnya tidak memanjang keluar layar. --}}
                                            <div class="rb-sim-bungkus">
                                                <table class="rb-sim">
                                                    <thead>
                                                        <tr>
                                                            <th>Datang</th>
                                                            <th class="text-end">Modal</th>
                                                            <th class="text-end">Untung</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($simulasi as $s)
                                                            <tr class="{{ $amanLaba($s) ? 'is-aman' : '' }} {{ $s['peserta'] === $impasPeserta ? 'is-impas' : '' }}">
                                                                <td class="rb-sim-pax">
                                                                    {{ $s['peserta'] }} org
                                                                    @if ($s['peserta'] === $impasPeserta)<span class="rb-sim-tanda">impas</span>@endif
                                                                </td>
                                                                <td class="rb-sim-modal" title="{{ $s['modal_teks'] }}">{{ $pendek($s['modal']) }}</td>
                                                                <td class="rb-sim-laba {{ $amanLaba($s) ? 'is-untung' : 'is-rugi' }}" title="{{ $s['laba_teks'] }}">{{ $pendek($s['laba']) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <div class="border-top mt-3 pt-3">
                                    <div class="orcha-label-kecil mb-2">Margin</div>
                                    <div class="rab-pilih-margin mb-2">
                                        @foreach ($kosakata['margin_jenis'] ?? [] as $kunci => $label)
                                            <label class="{{ $marginJenis === $kunci ? 'aktif' : '' }}">
                                                <input type="radio" value="{{ $kunci }}" wire:model.live="marginJenis">{{ $label }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @if ($marginPersen)
                                        <div class="rb-gandeng mb-2">
                                            <input type="text" inputmode="numeric" wire:model.blur="marginNilai" class="dsb-isian" placeholder="20">
                                            <span class="rb-gandeng-teks">%</span>
                                        </div>
                                    @else
                                        <div class="orcha-rupiah mb-2">
                                            <input type="text" inputmode="numeric" wire:model.blur="marginNilai" class="orcha-uang dsb-isian" placeholder="150.000">
                                        </div>
                                    @endif
                                    @if ($marginBelumTersimpan)
                                        <div class="small text-warning-emphasis mb-2"><i class="bi bi-info-circle"></i> Isi angkanya — jenis margin baru tersimpan setelah angkanya diisi.</div>
                                    @endif
                                    <div class="d-flex align-items-center gap-2 small">
                                        <span class="text-muted">Bulatkan ke atas per</span>
                                        <select wire:model.live="pembulatan" class="dsb-isian rb-isian-ringkas">
                                            @foreach ([1 => 'Rp 1', 1000 => 'Rp 1.000', 5000 => 'Rp 5.000', 10000 => 'Rp 10.000'] as $nilai => $teks)
                                                <option value="{{ $nilai }}" @selected((string) $this->pembulatan === (string) $nilai)>{{ $teks }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @unless ($tanpaBiaya)
                                    <div class="border-top mt-3 pt-3">
                                        <div class="orcha-label-kecil mb-2">Per kategori</div>
                                        @foreach ($r['per_kategori'] ?? [] as $kat)
                                            <div class="rab-baris-angka"><span>{{ $kat['label'] }}</span><span>{{ $kat['total_teks'] }}</span></div>
                                        @endforeach
                                    </div>
                                @endunless
                            </div>
                        </div>

                        {{-- ===================== JADIKAN PENDAFTARAN ===================== --}}
                        <div class="dsb-kartu k-12 rb-kartu">
                            <div class="dsb-kartu-isi">
                                <div class="orcha-label-kecil mb-2"><i class="bi bi-clipboard-check"></i> Pelanggan setuju?</div>
                                @if ($sudahPendaftaran)
                                    <p class="small mb-2">Sudah dijadikan pendaftaran <span class="orcha-kode">{{ $rab['kode_pendaftaran'] }}</span>.</p>
                                    @if ($rab['pendaftaran_id'])
                                        <a href="{{ route('admin.orcha.pendaftaran.detail', $rab['pendaftaran_id']) }}" class="dsb-tombol is-lembut" style="--ikon: #64748b" wire:navigate>
                                            <i class="bi bi-box-arrow-up-right"></i> Buka pendaftaran
                                        </a>
                                    @endif
                                @elseif ($bolehJadiPendaftaran)
                                    <p class="small text-muted mb-2">
                                        Harga jual, modal per orang, dan biaya tetapnya terisi sendiri dari RAB ini — laporan keuntungan akan sama persis.
                                    </p>
                                    <select wire:model="paketId" class="dsb-isian mb-2 @error('paketId') is-galat @enderror">
                                        <option value="" @selected((string) $this->paketId === '')>Pilih paket induk…</option>
                                        @foreach ($katalog['paket'] ?? [] as $p)
                                            <option value="{{ $p['id'] }}" @selected((string) $this->paketId === (string) $p['id'])>{{ $p['nama'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('paketId')<div class="dsb-galat mb-2">{{ $message }}</div>@enderror

                                    {{-- Tidak pernah tercentang sendiri. Yang menekan
                                         tombol ini biasanya sedang mengejar tenggat, dan
                                         centang yang sudah disingkirkan untuknya bukan
                                         persetujuan. --}}
                                    @if ($dibawahImpas)
                                        <label class="rb-sadar">
                                            <input type="checkbox" wire:model.live="abaikanImpas">
                                            <span>
                                                Peserta baru {{ $rab['jumlah_peserta'] }} orang, balik modal mulai
                                                {{ $impasPeserta }} orang. Saya tetap memberangkatkan dan menerima ruginya.
                                            </span>
                                        </label>
                                    @endif
                                    <button type="button" class="dsb-tombol is-lembut w-100 justify-content-center pcek-konfirmasi" style="--ikon: #047857"
                                        data-action="jadikanPendaftaran"
                                        data-title="Jadikan pendaftaran?"
                                        data-text="RAB ditandai disetujui dan pendaftaran baru dibuat dengan harga {{ $r['harga_per_orang_teks'] ?? '' }} per orang."
                                        data-confirm="Ya, buat pendaftaran" data-icon="question">
                                        <i class="bi bi-check2-circle"></i> Jadikan Pendaftaran
                                    </button>
                                @else
                                    <p class="small text-muted mb-0">Tersedia setelah RAB berisi biaya dan tidak berstatus batal.</p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            </section>
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.seret-urut')
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
