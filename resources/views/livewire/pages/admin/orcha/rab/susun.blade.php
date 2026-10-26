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
    $warnaStatus = [
        'draf' => 'bg-secondary-subtle text-secondary-emphasis',
        'dikirim' => 'bg-primary-subtle text-primary-emphasis',
        'disetujui' => 'bg-success-subtle text-success-emphasis',
        'batal' => 'bg-danger-subtle text-danger-emphasis',
    ];
    $kelasStatus = $warnaStatus[$statusKini] ?? $warnaStatus['draf'];
    $bolehJadiPendaftaran = ! $sudahPendaftaran && $statusKini !== 'batal' && ! $tanpaBiaya;

    $openTrip = ($rab['jenis'] ?? 'private') === 'open';
    $pesertaDasar = (int) ($r['peserta_dasar'] ?? ($rab['jumlah_peserta'] ?? 1));
    $simulasi = $r['simulasi'] ?? [];
    $impasPeserta = $r['bep_peserta'] ?? null;
    $adaImpas = $impasPeserta !== null;
    $impasTercapai = (bool) ($r['bep_tercapai'] ?? false);
    $dibawahImpas = $openTrip && $impasPeserta !== null && ! $impasTercapai;

    /*
     | Perbandingannya dirakit di sini, BUKAN di @php sebaris di antara
     | direktif. Tanda "lebih besar" di sana membuat Livewire melewatkan
     | penanda morph @foreach-nya, dan tiap pembaruan sesudahnya rusak di
     | Block.appendChild — tanpa satu pun galat yang menyebut sebabnya.
     | Dijaga PenandaMorphAdminTest.
     */
    $amanLaba = fn (array $s) => $s['laba'] >= 0;

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

        .rab-fakta { display: flex; flex-wrap: wrap; gap: .5rem; }
        .rab-fakta span { background: var(--orc-panel-latar); border: var(--orc-panel-garis); border-radius: 999px; padding: .3rem .8rem; font-size: .82rem; color: var(--orc-tinta); }
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
        .rb-sim { width: 100%; border-collapse: collapse; font-size: .76rem; }
        .rb-sim td { padding: 3px 6px; border-bottom: 1px solid #f4f7fa; white-space: nowrap; }
        .rb-sim tr:last-child td { border-bottom: 0; }
        .rb-sim tr.is-aman td { background: #f3faf6; }
        .rb-sim tr.is-impas td:first-child { box-shadow: inset 2px 0 0 #047857; font-weight: 700; }
        .rb-sim-pax { color: var(--dsb-redup, #6b7280); }
        .rb-sim-modal { color: #94a3b8; text-align: right; font-variant-numeric: tabular-nums; }
        .rb-sim-laba { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }

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
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="orcha-kode">{{ $rab['kode'] }}</span>
                                <span class="badge {{ $kelasStatus }}">{{ $rab['status_label'] }}</span>
                                @if ($rab['kedaluwarsa'])
                                    <span class="badge bg-warning-subtle text-warning-emphasis">Lewat masa berlaku</span>
                                @endif
                            </div>
                            <h5 class="fw-bold mb-1">{{ $rab['judul'] }}</h5>
                            <div class="text-muted small mb-2">
                                {{ $rab['nama_pelanggan'] }}
                                @if ($rab['whatsapp']) · {{ $rab['whatsapp'] }} @endif
                            </div>
                            <div class="rab-fakta">
                                <span><i class="bi bi-geo-alt"></i> {{ $rab['daerah'] ? $rab['daerah'].', ' : '' }}{{ $rab['provinsi'] }}</span>
                                <span><i class="bi bi-calendar3"></i> {{ $tanggalTeks }}</span>
                                <span><i class="bi bi-moon-stars"></i> {{ $rab['jumlah_hari'] }} hari {{ $rab['jumlah_malam'] }} malam</span>
                                <span><i class="bi bi-people"></i> {{ $rab['jumlah_peserta'] }} peserta</span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 align-items-start justify-content-lg-end">
                            <select class="dsb-isian rb-isian-ringkas" wire:change="ubahStatus($event.target.value)" title="Status RAB">
                                @foreach ($kosakata['status'] ?? [] as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected($kunci === $statusKini)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="$toggle('bukaKepala')" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                <i class="bi bi-sliders"></i> Data perjalanan
                            </button>
                            <a href="{{ route('admin.orcha.rab.pdf', ['rab' => $rabId, 'jenis' => 'penawaran']) }}" class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                                <i class="bi bi-file-earmark-pdf"></i> PDF Penawaran
                            </a>
                            <a href="{{ route('admin.orcha.rab.pdf', ['rab' => $rabId, 'jenis' => 'internal']) }}" class="dsb-tombol is-lembut" style="--ikon: #64748b"
                                title="Berisi modal & margin — jangan dikirim ke pelanggan">
                                <i class="bi bi-lock"></i> PDF Internal
                            </a>
                        </div>
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
                                    <input type="date" wire:model="kepala.tanggal_mulai" class="dsb-isian">
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
                                <button type="button" wire:click="$set('bukaKepala', false)" class="dsb-tombol is-lembut" style="--ikon: #64748b">Batal</button>
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
                                    <button type="button" wire:click="pilihHari({{ $h }})" class="{{ $h === $hariAktif ? 'aktif' : '' }}">
                                        Hari {{ $h }}<small>{{ count($kegiatanPerHari[$h] ?? []) }}</small>
                                    </button>
                                @endfor
                            </div>

                            @if ($hariAktifKosong)
                                <div class="rb-hari-kosong">
                                    <span class="dsb-ikon is-kecil" style="--c: #94a3b8"><i class="bi bi-calendar-x"></i></span>
                                    <div>
                                        <strong>Hari {{ $hariAktif }} masih kosong</strong>
                                        <span class="dsb-kartu-sub">Klik destinasi atau kegiatan di bawah. Dibiarkan kosong, di PDF tertulis "waktu bebas".</span>
                                    </div>
                                </div>
                            @endif

                            @foreach ($kegiatanAktif as $i => $k)
                                @php [$labelJenis, $warnaJenis] = $rupaDari($k); @endphp
                                <div class="rab-kegiatan" style="--c: {{ $warnaJenis }}"
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
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus" wire:click="hapusKegiatan({{ $i }})" title="Hapus kegiatan">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
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
                                    <div class="orcha-bagian-sub">Tiket dari itinerary sudah masuk sendiri. Sisanya — armada, hotel, pemandu — dipilih dari master harga.</div>
                                </div>
                            </div>

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
                                            <option value="">Pilih dari master harga…</option>
                                            @foreach ($masterPerKategori as $label => $isi)
                                                <optgroup label="{{ $label }}">
                                                    @foreach ($isi as $m)
                                                        <option value="{{ $m['id'] }}">{{ $m['nama'] }} — {{ $m['harga_teks'] }} / {{ $m['satuan_label'] }}</option>
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
                                                            <div class="rab-basi">
                                                                Master kini Rp {{ number_format($b['harga_master_kini'], 0, ',', '.') }} ·
                                                                <a href="#" wire:click.prevent="pakaiHargaMaster({{ $b['id'] }})">pakai harga baru</a>
                                                            </div>
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
                                                        <option value="{{ $kunci }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select wire:model.live="manual.satuan" class="dsb-isian">
                                                    @foreach ($kosakata['satuan'] ?? [] as $kunci => $label)
                                                        <option value="{{ $kunci }}">{{ $label }}</option>
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
                                                    <option value="">Vendor — belum ditentukan</option>
                                                    @foreach ($katalog['vendor'] ?? [] as $v)
                                                        <option value="{{ $v['id'] }}">{{ $v['nama'] }}@if (! empty($v['kontak'])) — {{ $v['kontak'] }}@endif</option>
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
                                <div class="orcha-label-kecil mb-1">Harga per orang</div>
                                <div class="rab-harga-besar">{{ $r['harga_per_orang_teks'] ?? 'Rp 0' }}</div>
                                <div class="text-muted small mb-3">Total {{ $r['harga_total_teks'] ?? 'Rp 0' }} untuk {{ $rab['jumlah_peserta'] }} peserta</div>

                                {{-- Pada open trip harga dan modal dihitung dari jumlah
                                     yang BERBEDA; tanpa disebut, angka di atas terbaca
                                     seolah dibagi jumlah peserta yang sama. --}}
                                @if ($openTrip)
                                    <div class="rb-dasar">
                                        <i class="bi bi-megaphone"></i>
                                        Open trip · harga dihitung atas dasar <strong>{{ $pesertaDasar }} peserta</strong>,
                                        modal di bawah ini atas {{ $rab['jumlah_peserta'] }} peserta.
                                    </div>
                                @endif

                                <div class="rab-baris-angka"><span>Modal total</span><strong>{{ $r['modal_total_teks'] ?? 'Rp 0' }}</strong></div>
                                <div class="rab-baris-angka text-muted"><span>Modal per kepala</span><span>{{ $r['modal_per_kepala_teks'] ?? 'Rp 0' }}</span></div>
                                <div class="rab-baris-angka"><span>Margin diminta</span><span>{{ $r['margin_diminta_teks'] ?? 'Rp 0' }}</span></div>
                                <div class="rab-baris-angka garis text-success">
                                    <span class="fw-bold">Untung</span>
                                    <strong>{{ $r['untung_teks'] ?? 'Rp 0' }}@isset($r['persen_untung']) <small>({{ str_replace('.', ',', $r['persen_untung']) }}%)</small>@endisset</strong>
                                </div>
                                <div class="rab-baris-angka text-muted">
                                    <span>Untung per orang</span><span>{{ $r['untung_per_orang_teks'] ?? 'Rp 0' }}</span>
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
                                        <table class="rb-sim">
                                            @foreach ($simulasi as $s)
                                                <tr class="{{ $amanLaba($s) ? 'is-aman' : '' }} {{ $s['peserta'] === $impasPeserta ? 'is-impas' : '' }}">
                                                    <td class="rb-sim-pax">{{ $s['peserta'] }} org</td>
                                                    <td class="rb-sim-modal">{{ $s['modal_teks'] }}</td>
                                                    <td class="rb-sim-laba" style="color: {{ $amanLaba($s) ? '#047857' : '#be123c' }}">{{ $s['laba_teks'] }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
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
                                                <option value="{{ $nilai }}">{{ $teks }}</option>
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
                                        <option value="">Pilih paket induk…</option>
                                        @foreach ($katalog['paket'] ?? [] as $p)
                                            <option value="{{ $p['id'] }}">{{ $p['nama'] }}</option>
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

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
