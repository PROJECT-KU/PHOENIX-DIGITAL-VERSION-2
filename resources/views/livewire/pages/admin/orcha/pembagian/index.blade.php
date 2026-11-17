@section('title')
Bagi Kursi & Kamar || lemon
@stop

<div>
    {{-- Partial gaya WAJIB disertakan, bukan diandaikan sudah ada.

         Layout templateindex tidak memuat bahasa rupa dsb-* sendiri; tiap layar
         yang memakainya menyertakannya. Tanpa tiga baris ini halaman tetap
         terbuka 200 dengan isi yang benar dan rupa yang hilang seluruhnya —
         galat yang tidak pernah muncul di log. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    {{-- Kartu angka memakai bahasa rupa yang SAMA dengan Pesanan Toko
         (.pt-kartu-tab). Empat kotak .dsb-stat selebar layar penuh — bentuk
         lama layar ini — menyisakan petak putih sepanjang separuh layar untuk
         empat angka yang masing-masing dua digit. --}}
    @include('livewire.pages.admin.partials.kartu-tab-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    {{-- <style> telanjang: layout templateindex tidak punya satu pun stack,
         jadi gaya yang didorong ke sana tidak pernah ikut tergambar. --}}
    <style>
        /* ===== Empat kartu angka berjajar =====
           Bahasa rupa dasbor hanya menyediakan .is-dua dan .is-tiga, dan
           .dsb-deret polos adalah satu kolom — empat kartu lalu menumpuk
           setinggi layar, sehingga angka yang harus dibandingkan satu sama lain
           ("8 dari 22 kursi") tidak pernah terlihat bersamaan. */
        .pbg-angka { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }

        /* ===== Daftar peserta: kepingan yang bisa dipilih =====
           Dua ketukan, bukan seret-lepas: pilih orangnya, lalu ketuk kursi atau
           kamarnya. Seret-lepas tidak bisa dipakai di ponsel, dan pembagian ini
           justru sering dikerjakan sambil berdiri. */
        /* Tingginya DIBATASI, lalu bergulir sendiri.

           Rombongan enam puluh orang membuat kolom ini menjulang jauh di atas
           petak kursinya, dan bahasa rupa dasbor menyamakan tinggi kartu yang
           bersebelahan — petaknya lalu duduk di pojok kiri atas sebuah kartu
           setinggi dua layar. Bergulir sendiri juga menjaga kepala kartunya
           ("Ketuk satu nama…") tetap terlihat sambil memilih. */
        .pbg-orang-deret {
            display: flex; flex-wrap: wrap; gap: 8px;
            max-height: 340px; overflow-y: auto; padding-right: 4px;
        }

        /* Ringkasan armada & kamar saat panelnya tertutup. */
        .pbg-fasil-ringkas { display: flex; flex-wrap: wrap; gap: 7px; }
        .pbg-fasil-cip {
            --c: #64748b;
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 11px; border-radius: 999px;
            background: color-mix(in srgb, var(--c) 7%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 28%, #fff);
            color: var(--c); font-size: .78rem; font-weight: 700;
        }
        .pbg-fasil-cip small { font-weight: 500; opacity: .8; }

        .pbg-orang {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 12px; border-radius: 12px; cursor: pointer;
            background: #fff; border: 1px solid var(--dsb-tepi);
            font-size: .82rem; text-align: left; color: var(--dsb-tinta);
        }
        .pbg-orang-nama { font-weight: 600; }
        .pbg-orang-meta { font-size: .72rem; color: var(--dsb-redup); display: block; }

        /* Jenis kelamin diberi warna, bukan hanya huruf. Pembagian kamar
           seluruhnya bergantung medan ini, dan huruf "L"/"P" sebesar 11px
           tidak bisa dipindai mata pada daftar seratus nama. */
        .pbg-orang.is-laki { border-color: #bae6fd; background: #f0f9ff; }
        .pbg-orang.is-perempuan { border-color: #fbcfe8; background: #fdf2f8; }
        .pbg-orang.is-belum { border-color: #fde68a; background: #fffbeb; }
        .pbg-orang.is-terpilih {
            border-color: #7c3aed; background: #7c3aed; color: #fff;
            box-shadow: 0 8px 18px rgba(124, 58, 237, .26);
        }
        .pbg-orang.is-terpilih .pbg-orang-meta { color: rgba(255, 255, 255, .8); }

        /* Yang berubah tetapi belum tersimpan diberi titik, bukan warna lain:
           warnanya sudah dipakai jenis kelamin, dan dua arti pada satu warna
           membuat keduanya berhenti terbaca. */
        .pbg-belum-simpan { width: 7px; height: 7px; border-radius: 50%; background: #f59e0b; flex-shrink: 0; }

        /* ===== Petak kursi ===== */
        .pbg-kendaraan { margin-bottom: 20px; }
        .pbg-kendaraan:last-child { margin-bottom: 0; }
        .pbg-kendaraan-kepala {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 11px;
        }
        .pbg-kendaraan-nama { font-weight: 700; font-size: .92rem; color: var(--dsb-tinta); }

        /* SATU kisi untuk seluruh kendaraan, bukan satu kisi per baris.

           Kisi per baris menentukan lebar kolomnya sendiri-sendiri dari isinya,
           dan bangku belakang — yang berisi dua kursi lalu tiga lorong — jadi
           punya lebar kolom yang berbeda dari baris di atasnya. Kursi 22
           bergeser beberapa piksel dari kursi 18 yang seharusnya tepat di
           atasnya, dan petak yang tidak lurus terbaca sebagai petak yang salah.

           Lebar kolomnya ditulis di atribut style tiap kendaraan, karena posisi
           lorongnya berbeda antara tata 2-2 dan 2-3. */
        /* Lebar kursi & lorong didefinisikan SEKALI, di sini.

           Sebelumnya angkanya ada di dua tempat: CSS untuk kotak kursinya, dan
           PHP yang merakit grid-template-columns petaknya. Saat kursinya
           dilebarkan, yang kedua tertinggal — kolomnya tetap sempit, kursinya
           melimpah ke kursi sebelah, dan nama yang tertimpa terbaca sebagai
           nama yang terpotong. Tidak ada galat; yang salah cuma apa yang
           terbaca di petak. */
        .pbg-petak {
            --pbg-kursi-w: 84px;
            --pbg-lorong-w: 22px;
            display: grid; gap: 6px; justify-content: start;
        }

        /* 72px, bukan 54px.

           Pada 54px nama terpotong di huruf kedelapan ("Budi S…", "Hendra…"),
           dan dua peserta yang nama depannya sama tidak bisa dibedakan dari
           petak — padahal membedakan mereka justru gunanya. Dua barisnya juga
           dibolehkan, supaya "Rina Wulandari" terbaca utuh. */
        .pbg-kursi {
            width: var(--pbg-kursi-w); min-height: 54px; border-radius: 9px; cursor: pointer;
            background: #fff; border: 1px dashed #cbd5e1; color: #94a3b8;
            font-size: .7rem; line-height: 1.15; padding: 4px 3px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 2px; overflow: hidden;
        }
        .pbg-kursi-nomor {
            display: inline-flex; align-items: center; gap: 3px;
            font-weight: 700; font-size: .66rem;
        }
        .pbg-kursi-nomor i.bi { font-size: .6rem; opacity: .55; }
        .pbg-kursi-nama {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            /* width, bukan max-width.

               Kotak -webkit-box menentukan lebarnya sendiri dari isi
               terpanjang (max-content) dan mengabaikan max-width, jadi nama
               sepanjang "Doni Saputra" meluber MELEWATI tepi kursinya ke kursi
               sebelah alih-alih turun ke baris kedua — dan yang terbaca di
               petak lalu nama yang salah. Yang lebih panjang lagi kebetulan
               terbungkus, sehingga cacatnya terlihat acak. */
            width: 100%; min-width: 0; font-size: .6rem; font-weight: 600;
            white-space: normal;
            overflow: hidden; text-align: center;
            /* break-word memenggal DI TENGAH KATA lebih dulu: "Budi Santoso"
               jadi "Budi Santos" + "o", dan baris keduanya terpotong clamp
               sehingga yang terbaca cuma nama yang salah eja. normal +
               overflow-wrap memenggal di spasi dulu, dan baru memotong kata
               yang memang tidak muat sendirian. */
            word-break: normal; overflow-wrap: break-word;
        }
        /* Kursi jendela hanya ditandai tipis di sisi luarnya. Pewarnaan penuh
           akan bersaing dengan warna jenis kelamin, dan dua arti pada satu
           warna membuat keduanya berhenti terbaca. */
        .pbg-kursi.is-jendela { box-shadow: inset 2px 0 0 #cbd5e1; }
        .pbg-kursi.is-isi { border-style: solid; }
        .pbg-kursi.is-laki { border-color: #7dd3fc; background: #e0f2fe; color: #075985; }
        .pbg-kursi.is-perempuan { border-color: #f9a8d4; background: #fce7f3; color: #9d174d; }
        .pbg-kursi.is-belum { border-color: #fcd34d; background: #fef3c7; color: #92400e; }
        .pbg-kursi.is-bentrok { border-color: #f87171; background: #fee2e2; color: #991b1b; }
        /* Lorong: ruang kosong, BUKAN kursi kosong. Dua hal yang tidak boleh
           terbaca sama — yang satu bisa diisi orang, yang lain tidak. */
        .pbg-lorong { width: var(--pbg-lorong-w); }

        /* Wadah bergulir untuk petaknya saja.

           Tata 2-3 selebar enam kolom tidak muat di ponsel. Tanpa wadah ini
           yang bergeser SELURUH halaman, dan halaman yang bisa digeser ke
           samping membuat tombol di tepi kanan hilang dari layar. */
        .pbg-petak-gulir { overflow-x: auto; padding-bottom: 6px; }

        /* Keterangan warna, sekali per kendaraan. */
        /* Ditambat ke DASAR kartunya: kartu petak duduk bersebelahan dengan
           daftar peserta yang lebih tinggi, dan keterangan yang menempel tepat
           di bawah petak meninggalkan petak putih di bawahnya sendiri. */
        .pbg-kunci-warna {
            display: flex; flex-wrap: wrap; gap: 13px;
            margin-top: auto; padding-top: 12px;
            font-size: .74rem; color: var(--dsb-redup);
        }
        /* Isi kartu petak memanjang mengisi tinggi kartunya, supaya
           margin-top:auto di atas punya ruang untuk mendorong.

           Kendaraan TERAKHIR ikut dijadikan flex. margin-top:auto hanya
           berlaku di dalam wadah flex atau grid; keterangan warnanya bersarang
           di dalam .pbg-kendaraan yang blok biasa, jadi tanpa baris kedua ini
           ia diam di tempat dan petak putih di bawahnya tetap ada — persis
           yang mau dihilangkan. */
        .pbg-kartu-petak .dsb-kartu-isi { display: flex; flex-direction: column; flex: 1; }
        .pbg-kartu-petak .pbg-kendaraan:last-child { display: flex; flex-direction: column; flex: 1; }

        /* Daftar peserta yang bergulir diberi jalur bilah gulir yang tetap,
           supaya kepingan di tepi kanan tidak bergeser saat bilahnya muncul. */
        .pbg-orang-deret { scrollbar-gutter: stable; }
        .pbg-kunci-warna span { display: inline-flex; align-items: center; gap: 5px; }
        .pbg-titik {
            width: 11px; height: 11px; border-radius: 3px; display: inline-block;
            border: 1px solid #cbd5e1;
        }
        .pbg-titik.is-laki { background: #e0f2fe; border-color: #7dd3fc; }
        .pbg-titik.is-perempuan { background: #fce7f3; border-color: #f9a8d4; }
        .pbg-titik.is-belum { background: #fef3c7; border-color: #fcd34d; }

        @media (hover: hover) and (pointer: fine) {
            .pbg-kursi:hover { border-color: #7c3aed; color: #5b21b6; }
            .pbg-orang:hover { border-color: #a78bfa; }
            .pbg-orang.is-terpilih:hover { border-color: #fff; }
        }

        /* ===== Papan kamar ===== */
        .pbg-kamar-rak {
            display: grid; gap: 11px;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        }
        .pbg-kamar {
            padding: 11px 13px; border-radius: 13px; cursor: pointer; text-align: left;
            background: #fff; border: 1px solid var(--dsb-tepi);
        }
        .pbg-kamar-kepala {
            display: flex; align-items: baseline; justify-content: space-between; gap: 8px;
            font-size: .84rem; font-weight: 700; color: var(--dsb-tinta);
        }
        .pbg-kamar-sisa { font-size: .7rem; font-weight: 600; color: var(--dsb-redup); }
        .pbg-kamar-isi { display: flex; flex-direction: column; gap: 4px; margin-top: 8px; }
        .pbg-kamar-orang {
            display: flex; align-items: center; justify-content: space-between; gap: 6px;
            font-size: .76rem; color: var(--dsb-tinta);
        }
        .pbg-kamar-kosong { font-size: .74rem; font-style: italic; color: #a3aab5; margin-top: 8px; }
        .pbg-kamar.is-laki { border-color: #bae6fd; background: #f0f9ff; }
        .pbg-kamar.is-perempuan { border-color: #fbcfe8; background: #fdf2f8; }
        .pbg-kamar.is-campur { border-color: #fdba74; background: #fff7ed; }
        .pbg-kamar.is-penuh { opacity: .95; }

        .pbg-keluarkan {
            border: 0; background: none; padding: 0 2px; cursor: pointer;
            color: #cbd5e1; font-size: .72rem; line-height: 1;
        }
        @media (hover: hover) and (pointer: fine) {
            .pbg-keluarkan:hover { color: #dc2626; }
            .pbg-kamar:hover { border-color: #a78bfa; }
        }

        /* ===== Bilah belum tersimpan, menempel di DASAR layar =====

           Papan kamar ada di bagian paling bawah halaman. Dengan tombol Simpan
           yang tinggal di kepala, admin yang baru memindahkan orang harus
           menggulir balik ke atas — dan di situlah ia paling mudah lupa. */
        .pbg-simpan-tempel {
            position: sticky; bottom: 12px; z-index: 30; margin: 16px 0 4px;
        }
        .pbg-simpan-tempel .pbg-simpan-bilah {
            box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
        }
        /* Halaman diberi ruang di dasarnya supaya bilah yang menempel tidak
           menutupi baris terakhir papan kamar. */
        .pbg-layar { padding-bottom: 86px; }

        /* ===== Peringatan yang diringkas ===== */
        .pbg-awas-lipat {
            padding: 10px 12px; border-radius: 11px;
            background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
            font-size: .82rem; line-height: 1.5;
        }
        .pbg-awas-lipat > summary { cursor: pointer; font-weight: 600; list-style: none; }
        .pbg-awas-lipat > summary::-webkit-details-marker { display: none; }
        .pbg-awas-lipat > summary i.bi { margin-right: 6px; }
        .pbg-awas-lipat[open] > summary { margin-bottom: 4px; }

        /* ===== Riwayat pembagian ===== */
        .pbg-riwayat { display: flex; flex-direction: column; gap: 8px; }
        .pbg-riwayat-baris {
            display: flex; align-items: center; gap: 11px; flex-wrap: wrap;
            padding: 10px 13px; border-radius: 12px;
            background: #fff; border: 1px solid var(--dsb-tepi); font-size: .82rem;
        }
        .pbg-riwayat-teks { flex: 1 1 220px; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
        .pbg-riwayat-teks > strong { font-size: .84rem; color: var(--dsb-tinta); }
        .pbg-riwayat-teks > span { font-size: .74rem; color: var(--dsb-redup); }

        .pbg-simpan-bilah {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
            padding: 12px 15px; border-radius: 13px;
            background: #fffbeb; border: 1px solid #fde68a; color: #78350f;
        }
        .pbg-simpan-teks { flex: 1 1 240px; min-width: 0; font-size: .84rem; line-height: 1.5; }
        .pbg-simpan-aksi { display: flex; gap: 8px; flex-wrap: wrap; margin-left: auto; }

        /* ===== Peringatan ===== */
        .pbg-awas { display: flex; flex-direction: column; gap: 8px; }
        .pbg-awas-baris {
            display: flex; align-items: flex-start; gap: 9px;
            padding: 10px 12px; border-radius: 11px; font-size: .82rem; line-height: 1.5;
        }
        .pbg-awas-baris.is-merah { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .pbg-awas-baris.is-kuning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

        /* ===== Borang kendaraan & kamar ===== */
        .pbg-fasilitas-baris {
            display: grid; gap: 9px; align-items: end; margin-bottom: 9px;
            grid-template-columns: minmax(0, 2fr) minmax(0, 2fr) minmax(0, 1fr) auto;
        }
        .pbg-kamar-baris {
            display: grid; gap: 9px; align-items: end; margin-bottom: 9px;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) auto;
        }
        @media (max-width: 575.98px) {
            .pbg-fasilitas-baris, .pbg-kamar-baris { grid-template-columns: 1fr 1fr; }
        }
        .pbg-deret { display: grid; gap: 9px; align-items: end; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); }
    </style>

    <div class="dsb pbg-layar">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Bagi Kursi &amp; Kamar</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <i class="bi bi-suitcase-lg me-1"></i>{{ $keberangkatan['paket']['nama'] ?? 'Paket' }}
                    </span>
                    <span class="d-block">
                        <i class="bi bi-calendar-event me-1"></i>Rombongan
                        {{ $keberangkatan['rentang_tanggal'] ?? '—' }}
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.pendaftaran', array_filter([
                        'rombongan' => $keberangkatan['id'] ?? null,
                    ])) }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Daftar Pendaftaran</span>
                </a>

                {{-- Dua berkas untuk dua tangan yang berbeda.

                     Denah kursi dibawa sopir dan tour leader; daftar kamar
                     diserahkan ke resepsionis hotel. Sebelum ada keduanya,
                     seluruh pembagian ini berakhir di satu tab peramban —
                     dan tour leader tetap berdiri di parkiran dengan catatan
                     tangan. --}}
                <a href="{{ route('admin.orcha.pembagian.kursi', $keberangkatanId) }}"
                    class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                    title="Denah tempat duduk untuk sopir dan tour leader">
                    <i class="bi bi-filetype-pdf"></i><span>Cetak Denah Kursi</span>
                </a>

                <a href="{{ route('admin.orcha.pembagian.kamar', $keberangkatanId) }}"
                    class="dsb-tombol is-lembut" style="--ikon: #db2777"
                    title="Daftar kamar untuk diserahkan ke hotel">
                    <i class="bi bi-filetype-pdf"></i><span>Cetak Daftar Kamar</span>
                </a>

                <button type="button" class="dsb-tombol {{ $this->adaUbahan() ? 'is-utama' : 'is-lembut is-mati' }}"
                    wire:click="simpan" @disabled(! $this->adaUbahan())>
                    <i class="bi bi-check2-circle"></i><span>Simpan Pembagian</span>
                </button>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ===== BILAH BELUM TERSIMPAN, MENEMPEL DI DASAR LAYAR =====

             Perubahan ditahan di layar sampai Simpan ditekan — menyimpan setiap
             ketukan berarti satu panggilan ke Orcha per orang untuk pekerjaan
             yang menyentuh seratus orang.

             Menempel di dasar, bukan ikut menggulir. Papan kamar ada di bagian
             paling bawah halaman; dengan tombol Simpan yang tinggal di kepala,
             admin yang baru memindahkan orang harus menggulir balik ke atas —
             dan di situlah ia paling mudah lupa, lalu menutup tab dan
             kehilangan satu jam kerja tanpa pernah tahu ia belum menyimpan. --}}
        @if ($this->adaUbahan())
            <div class="pbg-simpan-tempel">
                <div class="pbg-simpan-bilah">
                    <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-exclamation-triangle-fill"></i></span>
                    <div class="pbg-simpan-teks">
                        <strong>{{ count($ubahan) }} orang dipindahkan tetapi BELUM tersimpan.</strong>
                        Kalau halaman ini ditutup sekarang, pembagiannya kembali seperti semula.
                    </div>
                    <div class="pbg-simpan-aksi">
                        <button type="button" class="dsb-tombol is-lembut" style="--ikon: #64748b"
                            wire:click="batalkanUbahan">
                            <i class="bi bi-arrow-counterclockwise"></i><span>Buang</span>
                        </button>
                        <button type="button" class="dsb-tombol is-utama" wire:click="simpan">
                            <i class="bi bi-check2-circle"></i><span>Simpan sekarang</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== ANGKA RINGKAS =====

             Lima kartu dalam SATU baris, sebentuk dengan kartu tab Pesanan
             Toko. Bentuk sebelumnya empat kotak .dsb-stat selebar layar penuh
             yang menumpuk ke bawah: empat angka dua digit memakan tinggi
             separuh layar, dan angka yang harus dibandingkan satu sama lain
             ("8 dari 22 kursi") tidak pernah terlihat bersamaan.

             "Belum diisi L/P" diberi warna merah saat ada isinya: ia
             satu-satunya angka di baris ini yang MENGHALANGI pekerjaan. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="k-12">
                    <div class="pt-kartu-deret pbg-angka-deret" style="--n: 6">
                        <span class="pt-kartu-tab" style="--c: #16a34a">
                            <span class="pt-kartu-ikon"><i class="bi bi-people-fill"></i></span>
                            <span class="pt-kartu-teks">
                                <span class="pt-kartu-angka">{{ $ringkasan['total_peserta'] }}</span>
                                <span class="pt-kartu-label">Peserta</span>
                            </span>
                        </span>

                        <span class="pt-kartu-tab" style="--c: #0284c7">
                            <span class="pt-kartu-ikon"><i class="bi bi-gender-male"></i></span>
                            <span class="pt-kartu-teks">
                                <span class="pt-kartu-angka">{{ $ringkasan['laki'] }}</span>
                                <span class="pt-kartu-label">Laki-laki</span>
                            </span>
                        </span>

                        <span class="pt-kartu-tab" style="--c: #db2777">
                            <span class="pt-kartu-ikon"><i class="bi bi-gender-female"></i></span>
                            <span class="pt-kartu-teks">
                                <span class="pt-kartu-angka">{{ $ringkasan['perempuan'] }}</span>
                                <span class="pt-kartu-label">Perempuan</span>
                            </span>
                        </span>

                        <span class="pt-kartu-tab {{ $ringkasan['belum_jenis_kelamin'] === 0 ? 'is-nol' : 'is-aktif' }}"
                            style="--c: #dc2626"
                            title="Pembagian kamar otomatis tidak bisa jalan untuk peserta yang jenis kelaminnya belum diisi.">
                            <span class="pt-kartu-ikon"><i class="bi bi-gender-ambiguous"></i></span>
                            <span class="pt-kartu-teks">
                                <span class="pt-kartu-angka">{{ $ringkasan['belum_jenis_kelamin'] }}</span>
                                <span class="pt-kartu-label">Belum diisi L/P</span>
                            </span>
                        </span>

                        <span class="pt-kartu-tab" style="--c: #7c3aed">
                            <span class="pt-kartu-ikon"><i class="bi bi-grid-3x3-gap-fill"></i></span>
                            <span class="pt-kartu-teks">
                                <span class="pt-kartu-angka">{{ $ringkasan['sudah_kursi'] }}<small>/{{ $ringkasan['jumlah_kursi_tersedia'] }}</small></span>
                                <span class="pt-kartu-label">Kursi terisi</span>
                            </span>
                        </span>

                        <span class="pt-kartu-tab" style="--c: #b45309">
                            <span class="pt-kartu-ikon"><i class="bi bi-door-closed-fill"></i></span>
                            <span class="pt-kartu-teks">
                                <span class="pt-kartu-angka">{{ $ringkasan['sudah_kamar'] }}<small>/{{ $ringkasan['daya_tampung_kamar'] }}</small></span>
                                <span class="pt-kartu-label">Kamar terisi</span>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== PERINGATAN ===== --}}
        @if ($peringatan !== [])
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #dc2626"><i class="bi bi-exclamation-octagon-fill"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Perlu Diperiksa</h2>
                                <span class="dsb-kartu-sub">Dihitung dari yang sedang di layar, termasuk yang belum disimpan</span>
                            </span>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        {{-- Diringkas dulu, baru bisa dibuka.

                             Rombongan enam puluh orang dengan tiga puluh kamar
                             tercampur menghasilkan tiga puluh baris kuning, dan
                             daftar sepanjang itu berhenti dibaca pada baris
                             kelima — termasuk baris merah yang terselip di
                             antaranya. Yang merah karenanya dihitung terpisah
                             dan disebut lebih dulu. --}}
                        @php
                            $awasMerah = collect($peringatan)->where('jenis', 'kursi_kembar')->values();
                            $awasKuning = collect($peringatan)->where('jenis', '!=', 'kursi_kembar')->values();
                        @endphp

                        <div class="pbg-awas">
                            @if ($awasMerah->isNotEmpty())
                                {{-- Bentrokan kursi SELALU dibentangkan.

                                     Tidak ada keadaan sah di mana dua orang
                                     menempati satu kursi, jadi ini bukan hal
                                     yang perlu ditimbang admin — dan jumlahnya
                                     tidak pernah banyak. --}}
                                @foreach ($awasMerah as $satu)
                                    <div class="pbg-awas-baris is-merah">
                                        <i class="bi bi-x-octagon-fill"></i>
                                        <span>{{ $satu['pesan'] }}</span>
                                    </div>
                                @endforeach
                            @endif

                            @if ($awasKuning->count() > 3)
                                <details class="pbg-awas-lipat">
                                    <summary>
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        {{ $awasKuning->count() }} kamar perlu diperiksa — ketuk untuk melihat daftarnya
                                    </summary>
                                    <div class="pbg-awas mt-2">
                                        @foreach ($awasKuning as $satu)
                                            <div class="pbg-awas-baris is-kuning">
                                                <i class="bi bi-dot"></i>
                                                <span>{{ $satu['pesan'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @else
                                @foreach ($awasKuning as $satu)
                                    <div class="pbg-awas-baris is-kuning">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <span>{{ $satu['pesan'] }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== BAGI OTOMATIS ===== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-magic"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Bagi Otomatis</h2>
                            <span class="dsb-kartu-sub">
                                Kamar dibagi menurut jenis kelamin — perempuan tidak pernah disatukan
                                dengan laki-laki. Kursi diisi berurutan supaya satu pemesanan duduk berdekatan.
                            </span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="button" class="dsb-tombol is-utama" wire:click="bagiOtomatis('keduanya')">
                            <i class="bi bi-magic"></i><span>Bagi Kursi &amp; Kamar</span>
                        </button>
                        <button type="button" class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                            wire:click="bagiOtomatis('kursi')">
                            <i class="bi bi-bus-front"></i><span>Kursi saja</span>
                        </button>
                        <button type="button" class="dsb-tombol is-lembut" style="--ikon: #db2777"
                            wire:click="bagiOtomatis('kamar')">
                            <i class="bi bi-door-closed"></i><span>Kamar saja</span>
                        </button>

                        {{-- Bawaannya TIDAK menimpa. Admin yang sudah memindahkan
                             tiga orang atas permintaan khusus lalu menekan bagi
                             otomatis untuk sisanya tidak boleh kehilangan ketiga
                             perpindahan itu — dan kehilangannya tidak kelihatan,
                             karena hasilnya tetap berupa pembagian yang rapi. --}}
                        <label class="d-inline-flex align-items-center gap-2 ms-auto"
                            style="font-size:.82rem;color:var(--dsb-redup);cursor:pointer">
                            <input type="checkbox" class="form-check-input m-0"
                                wire:model="timpa" @checked($timpa)>
                            <span>Timpa juga yang sudah saya atur sendiri</span>
                        </label>
                    </div>

                    @if (($laporan['catatan'] ?? []) !== [] || ($laporan['sisa'] ?? []) !== [])
                        <div class="pbg-awas mt-3">
                            @foreach ($laporan['catatan'] ?? [] as $catatan)
                                <div class="pbg-awas-baris is-kuning">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>{{ $catatan }}</span>
                                </div>
                            @endforeach

                            @if (($laporan['sisa'] ?? []) !== [])
                                <div class="pbg-awas-baris is-kuning">
                                    <i class="bi bi-person-exclamation"></i>
                                    <span>
                                        Belum kebagian:
                                        {{ collect($laporan['sisa'])->pluck('nama')->implode(', ') }}.
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== RIWAYAT PEMBAGIAN =====

             Jalan pulang. Satu tekanan "Bagi Otomatis" dengan centang timpa
             menghapus satu jam penyusunan tangan, dan hasilnya tetap berupa
             pembagian yang rapi — jadi tanpa ini, kesalahannya baru ketahuan
             di hari keberangkatan.

             Berdampingan dengan kartu Kendaraan & Kamar dalam SATU rak: dua
             panel yang sama-sama tertutup secara bawaan, dan masing-masing
             selebar layar penuh berarti dua baris kepala kartu yang isinya
             cuma judul. Lebarnya ikut berubah bila yang satu tidak ada —
             kartu k-6 yang berdiri sendiri menyisakan separuh layar kosong,
             yaitu persis yang mau dihindari. --}}
        <div class="dsb-rak dsb-bagian">
        @if ($riwayat !== [])
                <div class="dsb-kartu k-6">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #64748b"><i class="bi bi-clock-history"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Kembalikan ke Pembagian Sebelumnya</h2>
                                <span class="dsb-kartu-sub">
                                    Sepuluh perubahan terakhir. Yang dikembalikan penempatannya —
                                    peserta yang sudah batal tidak ikut dihidupkan lagi.
                                </span>
                            </span>
                        </div>
                    </div>

                    <div class="dsb-kartu-isi">
                        <div class="pbg-riwayat">
                            @foreach ($riwayat as $potret)
                                <div class="pbg-riwayat-baris" wire:key="potret-{{ $potret['urutan'] }}">
                                    <span class="dsb-lencana is-abu">
                                        {{ $potret['sebab'] }}
                                    </span>

                                    <div class="pbg-riwayat-teks">
                                        <strong>
                                            {{ ! empty($potret['pada'])
                                                ? \Carbon\Carbon::parse($potret['pada'])->locale('id')->translatedFormat('d F Y, H:i').' WIB'
                                                : 'waktu tidak tercatat' }}
                                        </strong>
                                        <span>
                                            {{ $potret['sudah_kursi'] }} dapat kursi &middot;
                                            {{ $potret['sudah_kamar'] }} dapat kamar
                                            @if (! empty($potret['oleh']))
                                                &middot; oleh {{ $potret['oleh'] }}
                                            @endif
                                        </span>
                                    </div>

                                    <button type="button" class="dsb-tombol is-lembut" style="--ikon: #b45309"
                                        x-data="{ tanya: @js('Pembagian dikembalikan ke keadaan sebelum "'.$potret['sebab'].'". Yang sekarang ikut dipotret dulu, jadi langkah ini juga bisa dibatalkan.') }"
                                        x-on:click="
                                            if (await window.orchaKonfirmasi(tanya, { judul: 'Kembalikan?', tombol: 'Ya, kembalikan' })) {
                                                $wire.kembalikan({{ (int) $potret['urutan'] }});
                                            }
                                        ">
                                        <i class="bi bi-arrow-counterclockwise"></i><span>Kembalikan</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
        @endif

        {{-- ===== KENDARAAN & KAMAR YANG DISIAPKAN =====

             Dipisahkan dari penempatan orangnya. Armada dan blok kamar
             ditentukan sekali saat dipesan ke vendor; penempatan orangnya
             berubah sampai hari terakhir. --}}
            <div class="dsb-kartu {{ $riwayat !== [] ? 'k-6' : 'k-12' }}">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #0f766e"><i class="bi bi-sliders"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Kendaraan &amp; Kamar yang Disiapkan</h2>
                            <span class="dsb-kartu-sub">
                                {{ count($kendaraan) }} kendaraan
                                ({{ collect($kendaraan)->sum('jumlah_kursi') }} kursi) &middot;
                                {{ count($kamar) }} kamar
                                ({{ collect($kamar)->sum('kapasitas') }} tempat tidur)
                            </span>
                        </span>
                    </div>

                    <button type="button" class="dsb-tombol is-lembut" style="--ikon: #0f766e"
                        wire:click="$toggle('bukaFasilitas')">
                        <i class="bi {{ $bukaFasilitas ? 'bi-chevron-up' : 'bi-chevron-down' }}"></i>
                        <span>{{ $bukaFasilitas ? 'Tutup' : 'Atur' }}</span>
                    </button>
                </div>

                {{-- Tertutup pun badannya TERISI.

                     Kartu ini berdampingan dengan kartu riwayat yang lebih
                     tinggi, dan bahasa rupa dasbor menyamakan tinggi kartu
                     yang bersebelahan — badan kosong berarti petak putih
                     setinggi kartu sebelahnya. Yang ditaruh di sini bukan
                     pengisi: daftar armada & blok kamar adalah hal pertama
                     yang ditanya admin sebelum membagi ("busnya berapa?"). --}}
                @if (! $bukaFasilitas)
                    <div class="dsb-kartu-isi">
                        @if ($kendaraan === [] && $kamar === [])
                            <p class="orcha-kosong mb-0">
                                Belum ada kendaraan maupun kamar. Tekan <strong>Atur</strong> untuk
                                mendaftarkannya — petak kursi dan papan kamar di bawah baru bisa
                                terisi setelah itu.
                            </p>
                        @else
                            <div class="pbg-fasil-ringkas">
                                @foreach ($kendaraan as $satu)
                                    <span class="pbg-fasil-cip" style="--c: #0284c7">
                                        <i class="bi bi-bus-front"></i>
                                        {{ $satu['nama'] ?? '—' }}
                                        <small>{{ (int) ($satu['jumlah_kursi'] ?? 0) }} kursi · {{ $satu['tata'] ?? '' }}</small>
                                    </span>
                                @endforeach

                                @foreach ($kamar as $satu)
                                    <span class="pbg-fasil-cip" style="--c: #db2777">
                                        <i class="bi bi-door-closed"></i>
                                        {{ $satu['nama'] ?? '—' }}
                                        <small>{{ (int) ($satu['kapasitas'] ?? 0) }} orang</small>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @if ($bukaFasilitas)
                    <div class="dsb-kartu-isi">
                        <h3 class="dsb-sub">Kendaraan</h3>

                        @forelse ($kendaraan as $urutan => $satu)
                            <div class="pbg-fasilitas-baris" wire:key="kendaraan-{{ $urutan }}">
                                <div>
                                    <label class="dsb-label">Nama kendaraan</label>
                                    <input type="text" class="dsb-isian" maxlength="60"
                                        wire:model="kendaraan.{{ $urutan }}.nama"
                                        value="{{ $satu['nama'] ?? '' }}" placeholder="Bus 1">
                                </div>
                                <div>
                                    <label class="dsb-label">Tata tempat duduk</label>
                                    <select class="dsb-isian" wire:model="kendaraan.{{ $urutan }}.tata">
                                        @foreach ($pilihanTata as $kunci => $label)
                                            <option value="{{ $kunci }}"
                                                @selected(($satu['tata'] ?? '2-2') === $kunci)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="dsb-label">Jumlah kursi</label>
                                    <input type="number" min="1" max="80" class="dsb-isian"
                                        wire:model="kendaraan.{{ $urutan }}.jumlah_kursi"
                                        value="{{ $satu['jumlah_kursi'] ?? '' }}">
                                </div>
                                <div>
                                    <button type="button" class="orcha-hapus-baris"
                                        wire:click="hapusKendaraan({{ $urutan }})"
                                        title="Hapus kendaraan ini dari daftar">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="orcha-kosong">Belum ada kendaraan. Tambahkan satu supaya petak kursinya tergambar.</p>
                        @endforelse

                        <button type="button" class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                            wire:click="tambahKendaraan">
                            <i class="bi bi-plus-lg"></i><span>Tambah kendaraan</span>
                        </button>

                        <hr class="dsb-pisah">

                        <h3 class="dsb-sub">Kamar</h3>

                        {{-- Deret bernomor sekaligus.

                             Hotel memberi blok kamar berurutan ("201 sampai 215"),
                             dan mengetiknya satu per satu lima belas kali adalah
                             pekerjaan yang paling sering membuat fitur seperti ini
                             tidak dipakai sama sekali. --}}
                        <div class="pbg-deret mb-3">
                            <div>
                                <label class="dsb-label">Nomor awal</label>
                                <input type="text" class="dsb-isian" maxlength="20"
                                    wire:model="deretAwal" placeholder="201">
                            </div>
                            <div>
                                <label class="dsb-label">Berapa kamar</label>
                                <input type="number" min="1" max="60" class="dsb-isian" wire:model="deretJumlah">
                            </div>
                            <div>
                                <label class="dsb-label">Isi per kamar</label>
                                <input type="number" min="1" max="12" class="dsb-isian" wire:model="deretKapasitas">
                            </div>
                            <div>
                                <button type="button" class="dsb-tombol is-lembut" style="--ikon: #db2777"
                                    wire:click="tambahDeretKamar">
                                    <i class="bi bi-123"></i><span>Buat deretnya</span>
                                </button>
                            </div>
                        </div>

                        @forelse ($kamar as $urutan => $satu)
                            <div class="pbg-kamar-baris" wire:key="kamar-{{ $urutan }}">
                                <div>
                                    <label class="dsb-label">Nomor / nama kamar</label>
                                    <input type="text" class="dsb-isian" maxlength="60"
                                        wire:model="kamar.{{ $urutan }}.nama"
                                        value="{{ $satu['nama'] ?? '' }}" placeholder="201">
                                </div>
                                <div>
                                    <label class="dsb-label">Isi per kamar</label>
                                    <input type="number" min="1" max="12" class="dsb-isian"
                                        wire:model="kamar.{{ $urutan }}.kapasitas"
                                        value="{{ $satu['kapasitas'] ?? '' }}">
                                </div>
                                <div>
                                    <button type="button" class="orcha-hapus-baris"
                                        wire:click="hapusKamar({{ $urutan }})"
                                        title="Hapus kamar ini dari daftar">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="orcha-kosong">Belum ada kamar. Pakai "Buat deretnya" kalau hotel memberi blok bernomor.</p>
                        @endforelse

                        <div class="d-flex flex-wrap gap-2 justify-content-between mt-2">
                            <button type="button" class="dsb-tombol is-lembut" style="--ikon: #db2777"
                                wire:click="tambahKamar">
                                <i class="bi bi-plus-lg"></i><span>Tambah satu kamar</span>
                            </button>

                            <button type="button" class="dsb-tombol is-utama" wire:click="simpanFasilitas">
                                <i class="bi bi-check2"></i><span>Simpan Kendaraan &amp; Kamar</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== PESERTA & TEMPAT DUDUK, BERDAMPINGAN =====

             Semua peserta ada di daftar ini, yang sudah ditempatkan maupun
             belum — bukan hanya yang belum. Memindahkan orang yang sudah
             ditempatkan adalah separuh pekerjaannya ("tukar kursi Budi dan
             Agus"), dan daftar yang menyembunyikannya memaksa admin mencarinya
             di dalam petak.

             Berdampingan dengan petak kursinya, bukan di atasnya. Cara
             memakainya dua ketukan — pilih nama, lalu ketuk kursinya — dan
             dengan daftar nama di atas petak, kedua ketukan itu berjarak satu
             layar penuh: admin memilih nama, menggulir ke bawah, lalu lupa
             siapa yang sedang dipegangnya. --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-5">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-people-fill"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Peserta Rombongan Ini</h2>
                            <span class="dsb-kartu-sub">
                                Ketuk satu nama, lalu ketuk kursi atau kamarnya di bawah
                            </span>
                        </span>
                    </div>

                    @if ($terpilih !== '')
                        <span class="dsb-lencana is-ungu">
                            <i class="bi bi-hand-index-thumb"></i>
                            {{ collect($peserta)->firstWhere('kunci', $terpilih)['nama'] ?? 'Terpilih' }}
                            sedang dipegang
                        </span>
                    @endif
                </div>

                <div class="dsb-kartu-isi">
                    @if ($peserta === [])
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-person-dash"></i></span>
                            <p class="dsb-kosong-judul">Belum ada peserta di rombongan ini</p>
                            <p class="dsb-kosong-ket">
                                Pendaftarannya mungkin belum dimasukkan, atau nama pesertanya belum didata.
                                Buka daftar pendaftaran lalu lengkapi namanya dulu.
                            </p>
                        </div>
                    @else
                        <div class="pbg-orang-deret">
                            @foreach ($peserta as $satu)
                                @php
                                    $kelamin = $satu['jenis_kelamin'] ?? null;
                                    $rupa = $kelamin === 'Laki-laki' ? 'is-laki'
                                        : ($kelamin === 'Perempuan' ? 'is-perempuan' : 'is-belum');
                                    $tempat = collect([
                                        filled($satu['kursi'] ?? null)
                                            ? trim(($satu['bus'] ?? '').' kursi '.$satu['kursi'])
                                            : null,
                                        filled($satu['kamar'] ?? null) ? 'kamar '.$satu['kamar'] : null,
                                        // Titik jemput ikut terbaca di sini: yang
                                        // dibutuhkan sopir justru keduanya
                                        // bersamaan — siapa naik di mana, duduk
                                        // di mana.
                                        filled($satu['titik_jemput'] ?? null) ? $satu['titik_jemput'] : null,
                                    ])->filter()->implode(' · ');
                                @endphp

                                <button type="button" wire:key="orang-{{ $satu['kunci'] }}"
                                    class="pbg-orang {{ $terpilih === $satu['kunci'] ? 'is-terpilih' : $rupa }}"
                                    wire:click="pilih(@js($satu['kunci']))"
                                    title="{{ $satu['pemesan'] }} · {{ $satu['kode'] }}">
                                    @if ($satu['berubah'])
                                        <span class="pbg-belum-simpan" title="Belum tersimpan"></span>
                                    @endif
                                    <span>
                                        <span class="pbg-orang-nama">{{ $satu['nama'] }}</span>
                                        <span class="pbg-orang-meta">
                                            {{ $kelamin ?: 'L/P belum diisi' }}{{ $tempat !== '' ? ' · '.$tempat : '' }}
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        {{-- Melengkapi jenis kelamin DARI SINI.

                             Pembagian kamar yang berhenti karena tiga orang belum
                             diisi memaksa admin berpindah ke tiga pendaftaran
                             berbeda lalu kembali — dan yang terjadi berikutnya
                             bukan ia melengkapi data, melainkan ia membagi
                             kamarnya dengan tangan. --}}
                        @php
                            $belumKelamin = collect($peserta)->filter(
                                fn ($satu) => ! in_array($satu['jenis_kelamin'] ?? null, ['Laki-laki', 'Perempuan'], true)
                            );
                        @endphp

                        @if ($belumKelamin->isNotEmpty())
                            <div class="pbg-awas mt-3">
                                <div class="pbg-awas-baris is-kuning">
                                    <i class="bi bi-gender-ambiguous"></i>
                                    <span>
                                        <strong>{{ $belumKelamin->count() }} peserta belum diisi jenis kelaminnya.</strong>
                                        Pembagian kamar otomatis tidak menebaknya dari nama — nama Indonesia
                                        sering dipakai keduanya, dan tebakan yang salah berarti satu orang
                                        tidur di kamar yang salah. Isi di bawah ini:
                                    </span>
                                </div>
                            </div>

                            <div class="pbg-orang-deret mt-2">
                                @foreach ($belumKelamin as $satu)
                                    <span class="pbg-orang is-belum" wire:key="kelamin-{{ $satu['kunci'] }}">
                                        <span>
                                            <span class="pbg-orang-nama">{{ $satu['nama'] }}</span>
                                            <span class="pbg-orang-meta">{{ $satu['kode'] }}</span>
                                        </span>
                                        <span class="d-inline-flex gap-1">
                                            <button type="button" class="dsb-tabel-btn"
                                                wire:click="setKelamin(@js($satu['kunci']), 'Laki-laki')"
                                                title="Laki-laki">L</button>
                                            <button type="button" class="dsb-tabel-btn"
                                                wire:click="setKelamin(@js($satu['kunci']), 'Perempuan')"
                                                title="Perempuan">P</button>
                                        </span>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="dsb-kartu k-7 pbg-kartu-petak">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-bus-front"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Tempat Duduk</h2>
                            <span class="dsb-kartu-sub">
                                Depan di atas, lorong di tengah. Ketuk kursi berpenghuni
                                untuk menukar dengan yang sedang dipegang.
                            </span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    @if ($petak === [])
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-bus-front"></i></span>
                            <p class="dsb-kosong-judul">Belum ada kendaraan yang didaftarkan</p>
                            <p class="dsb-kosong-ket">
                                Buka <strong>Kendaraan &amp; Kamar yang Disiapkan</strong> di atas,
                                tambahkan busnya, lalu petak kursinya tergambar di sini.
                            </p>
                        </div>
                    @else
                        @php
                            // Penghuni dipetakan sekali untuk SELURUH petak: mencari
                            // di dalam gelung kursi berarti seratus pencarian untuk
                            // satu bus empat puluh empat kursi.
                            $penghuniKursi = [];

                            foreach ($peserta as $satu) {
                                if (blank($satu['kursi'] ?? null)) {
                                    continue;
                                }

                                $kunciKursi = trim((string) ($satu['bus'] ?? '')).'|'.trim((string) $satu['kursi']);
                                $penghuniKursi[$kunciKursi][] = $satu;
                            }
                        @endphp

                        @foreach ($petak as $kendaraanPetak)
                            <div class="pbg-kendaraan" wire:key="petak-{{ $loop->index }}">
                                <div class="pbg-kendaraan-kepala">
                                    <span class="pbg-kendaraan-nama">{{ $kendaraanPetak['nama'] }}</span>
                                    <span class="dsb-lencana is-abu">
                                        {{ $kendaraanPetak['jumlah_kursi'] }} kursi · tata {{ $kendaraanPetak['tata'] }}
                                    </span>
                                    @php
                                        $terisiBus = collect($peserta)->filter(
                                            fn ($satu) => (string) ($satu['bus'] ?? '') === $kendaraanPetak['nama']
                                                && filled($satu['kursi'] ?? null)
                                        )->count();
                                    @endphp
                                    <span class="dsb-lencana {{ $terisiBus === 0 ? 'is-abu' : 'is-hijau' }}">
                                        {{ $terisiBus }} terisi
                                    </span>
                                </div>

                                @php
                                    /* Posisi lorong dibaca dari baris PERTAMA, yang
                                       selalu baris penuh — baris terakhir bisa berupa
                                       bangku belakang yang seluruh sisanya null. */
                                    $posisiLorong = array_search(null, $kendaraanPetak['baris'][0] ?? [], true);

                                    /* Lebarnya dibaca dari peubah CSS, bukan
                                       diketik ulang sebagai angka. Angka yang
                                       ditulis di dua tempat pasti berbeda suatu
                                       hari — dan saat itu terjadi, kursinya
                                       melimpah ke kursi sebelah tanpa satu pun
                                       galat. */
                                    $kolomPetak = collect(range(0, max(0, ($kendaraanPetak['lebar'] ?? 5) - 1)))
                                        ->map(fn ($i) => $i === $posisiLorong
                                            ? 'var(--pbg-lorong-w)'
                                            : 'var(--pbg-kursi-w)')
                                        ->implode(' ');
                                @endphp

                                {{-- Wadah bergulir.

                                     Tata 2-3 selebar 6 kolom tidak muat di
                                     ponsel; tanpa wadah ini yang bergeser
                                     SELURUH halaman, bukan petaknya saja —
                                     dan halaman yang bisa digeser ke samping
                                     membuat tombol di tepi kanan hilang. --}}
                                <div class="pbg-petak-gulir">
                                <div class="pbg-petak" style="grid-template-columns: {{ $kolomPetak }}">
                                    @foreach ($kendaraanPetak['baris'] as $baris)
                                            @foreach ($baris as $sel)
                                                @if ($sel === null)
                                                    <span class="pbg-lorong"></span>
                                                @else
                                                    @php
                                                        /* Kursi jendela: kolom paling kiri dan paling
                                                           kanan. Permintaan yang paling sering datang —
                                                           "ibu saya minta dekat jendela" — sebelumnya
                                                           hanya bisa dijawab dengan menghitung kolom
                                                           pakai jari. */
                                                        $kolomIni = $loop->index;
                                                        $jendela = $kolomIni === 0
                                                            || $kolomIni === count($baris) - 1;

                                                        $isi = $penghuniKursi[$kendaraanPetak['nama'].'|'.$sel] ?? [];
                                                        $orang = $isi[0] ?? null;
                                                        $bentrok = count($isi) > 1;
                                                        $kelaminKursi = $orang['jenis_kelamin'] ?? null;
                                                        $rupaKursi = $bentrok ? 'is-bentrok'
                                                            : ($orang === null ? ''
                                                                : ($kelaminKursi === 'Laki-laki' ? 'is-isi is-laki'
                                                                    : ($kelaminKursi === 'Perempuan' ? 'is-isi is-perempuan' : 'is-isi is-belum')));
                                                    @endphp

                                                    <button type="button"
                                                        class="pbg-kursi {{ $rupaKursi }} {{ $jendela ? 'is-jendela' : '' }}"
                                                        wire:click="tempatkanKursi(@js($kendaraanPetak['nama']), @js($sel))"
                                                        title="{{ $orang
                                                            ? $orang['nama'].' — '.$orang['pemesan'].' ('.$orang['kode'].')'
                                                            : 'Kursi '.$sel.' masih kosong' }}">
                                                        <span class="pbg-kursi-nomor">
                                                            {{ $sel }}@if ($jendela)<i class="bi bi-window"></i>@endif
                                                        </span>
                                                        @if ($orang)
                                                            <span class="pbg-kursi-nama">{{ $orang['nama'] }}</span>
                                                        @endif
                                                    </button>
                                                @endif
                                            @endforeach
                                    @endforeach
                                </div>
                                </div>

                                {{-- Keterangan warna, sekali per kendaraan.

                                     Petak berwarna tanpa keterangan menuntut
                                     admin menebak artinya dari isi kursinya —
                                     dan tebakan itu salah persis pada kursi
                                     yang jenis kelaminnya belum diisi. --}}
                                <div class="pbg-kunci-warna">
                                    <span><i class="pbg-titik is-laki"></i> Laki-laki</span>
                                    <span><i class="pbg-titik is-perempuan"></i> Perempuan</span>
                                    <span><i class="pbg-titik is-belum"></i> L/P belum diisi</span>
                                    <span><i class="bi bi-window"></i> kursi jendela</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== PAPAN KAMAR ===== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #db2777"><i class="bi bi-door-closed-fill"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Kamar</h2>
                            <span class="dsb-kartu-sub">
                                Biru laki-laki, merah muda perempuan, oranye tercampur.
                                Kamar tercampur tidak dilarang — pasangan suami-istri memang satu
                                kamar — hanya ditandai supaya yang tidak disengaja tidak lolos.
                            </span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    @if ($kamar === [])
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-door-closed"></i></span>
                            <p class="dsb-kosong-judul">Belum ada kamar yang didaftarkan</p>
                            <p class="dsb-kosong-ket">
                                Buka <strong>Kendaraan &amp; Kamar yang Disiapkan</strong> di atas.
                                Kalau hotel memberi blok bernomor, pakai "Buat deretnya".
                            </p>
                        </div>
                    @else
                        <div class="pbg-kamar-rak">
                            @foreach ($kamar as $satuKamar)
                                @php
                                    $namaKamar = (string) ($satuKamar['nama'] ?? '');
                                    $kapasitas = (int) ($satuKamar['kapasitas'] ?? 0);
                                    $penghuni = collect($peserta)->filter(
                                        fn ($satu) => (string) ($satu['kamar'] ?? '') === $namaKamar
                                    )->values();
                                    $kelaminKamar = $penghuni->pluck('jenis_kelamin')->filter()->unique()->values();
                                    $rupaKamar = $penghuni->isEmpty() ? ''
                                        : ($kelaminKamar->count() > 1 ? 'is-campur'
                                            : ($kelaminKamar->first() === 'Laki-laki' ? 'is-laki'
                                                : ($kelaminKamar->first() === 'Perempuan' ? 'is-perempuan' : '')));
                                    $sisa = max(0, $kapasitas - $penghuni->count());
                                @endphp

                                <div class="pbg-kamar {{ $rupaKamar }} {{ $sisa === 0 ? 'is-penuh' : '' }}"
                                    wire:key="papan-kamar-{{ $namaKamar }}"
                                    wire:click="tempatkanKamar(@js($namaKamar))"
                                    title="{{ $sisa === 0
                                        ? 'Kamar '.$namaKamar.' sudah penuh'
                                        : 'Ketuk untuk menaruh yang sedang dipegang di kamar '.$namaKamar }}">
                                    <div class="pbg-kamar-kepala">
                                        <span>{{ $namaKamar }}</span>
                                        <span class="pbg-kamar-sisa">
                                            {{ $penghuni->count() }}/{{ $kapasitas }}
                                        </span>
                                    </div>

                                    @if ($penghuni->isEmpty())
                                        <div class="pbg-kamar-kosong">masih kosong</div>
                                    @else
                                        <div class="pbg-kamar-isi">
                                            @foreach ($penghuni as $orangKamar)
                                                <div class="pbg-kamar-orang">
                                                    <span>
                                                        @if ($orangKamar['berubah'])
                                                            <span class="pbg-belum-simpan d-inline-block me-1"></span>
                                                        @endif
                                                        {{ $orangKamar['nama'] }}
                                                    </span>
                                                    {{-- stopPropagation: tanpa itu, mengeluarkan
                                                         seseorang ikut terbaca sebagai ketukan pada
                                                         kamarnya — dan orang yang sedang dipegang
                                                         langsung masuk menggantikannya. --}}
                                                    <button type="button" class="pbg-keluarkan"
                                                        wire:click.stop="kosongkanKamar(@js($orangKamar['kunci']))"
                                                        title="Keluarkan {{ $orangKamar['nama'] }} dari kamar ini">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
