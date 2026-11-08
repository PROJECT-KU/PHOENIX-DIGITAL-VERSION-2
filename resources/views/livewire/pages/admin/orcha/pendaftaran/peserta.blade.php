@section('title')
Daftar Peserta || lemon
@stop

<div>
    {{-- Kerangka mengikuti Pesanan Toko & halaman detail pendaftaran (bahasa
         rupa dsb-*): satu kisi 12 kolom, kartu putih datar berkepala ikon,
         isian .dsb-isian. Tiga layar yang dikerjakan orang yang sama tidak
         perlu dipelajari tiga kali. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    {{-- Gaya khusus layar ini, ditulis INLINE: public/build di-gitignore, jadi
         berkas hasil build tidak pernah sampai ke server. --}}
    <style>
    /* ===== Dua blok dalam satu kartu =====
       Dua cara pengisian berbagi satu kartu melebar, bukan dua kartu menumpuk
       di kolom sempit: keduanya dipakai sekali di awal lalu ditinggalkan,
       sedangkan daftar namanya yang dikerjakan lama. */
    .ps-duo {
        display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        gap: clamp(16px, 2.2vw, 30px);
    }
    .ps-blok { min-width: 0; display: flex; flex-direction: column; }
    .ps-blok + .ps-blok {
        border-left: 1px solid #f1f5f9; padding-left: clamp(16px, 2.2vw, 30px);
    }
    @media (max-width: 991.98px) {
        .ps-duo { grid-template-columns: minmax(0, 1fr); }
        .ps-blok + .ps-blok {
            border-left: 0; padding-left: 0;
            border-top: 1px solid #f1f5f9; padding-top: 18px;
        }
    }
    .ps-blok-judul {
        display: flex; align-items: center; gap: 7px; margin-bottom: 6px;
        font-size: .72rem; font-weight: 800; letter-spacing: .05em;
        text-transform: uppercase; color: #94a3b8;
    }
    .ps-blok-judul i.bi { color: #7c3aed; font-size: .88rem; line-height: 1; }
    .ps-blok-judul i.bi::before { display: block; line-height: 1; }
    .ps-blok-ket { color: var(--dsb-redup); font-size: .8rem; line-height: 1.6; margin: 0 0 11px; }
    .ps-blok-ket code {
        background: #f1f5f9; color: #6d28d9; padding: 1px 5px;
        border-radius: 5px; font-size: .78rem;
    }
    /* Tombolnya menempel pada isinya sendiri, TIDAK ditambat ke dasar blok.

       Sempat ditambat (margin-top:auto) supaya kaki kedua blok rata; yang
       terjadi justru lubang pindah ke tengah blok yang lebih pendek — dan
       lubang di tengah terbaca seperti ada yang gagal dimuat, sedangkan sisa
       ruang di dasar blok terbaca sebagai jarak kartu biasa. */
    .ps-blok-kaki { padding-top: 11px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }

    /* ===== Contoh isi berkas =====
       Bentuk berkasnya diperlihatkan, bukan cuma disebut — dan sekaligus
       mengisi kaki blok unggah yang kalau tidak jadi petak putih. */
    .ps-contoh {
        margin-top: 12px; border: 1px solid var(--dsb-tepi); border-radius: 11px;
        overflow: hidden; background: #fff;
    }
    .ps-contoh-judul {
        padding: 7px 11px; background: #f8fafc; border-bottom: 1px solid var(--dsb-tepi);
        font-size: .72rem; font-weight: 700; color: #94a3b8;
        letter-spacing: .04em; text-transform: uppercase;
    }
    .ps-contoh-baris {
        display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr) minmax(0, 1fr);
        gap: 8px; padding: 7px 11px; font-size: .78rem; color: var(--dsb-tinta);
    }
    .ps-contoh-baris + .ps-contoh-baris { border-top: 1px solid #f5f7fa; }
    .ps-contoh-baris > span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ps-contoh-baris.is-judul { background: #fbfcfe; color: #64748b; font-weight: 700; font-size: .72rem; }
    .ps-contoh-baris .is-kosong { color: #cbd5e1; }

    /* ===== Lencana di kepala ===== */
    .ps-lencana-kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-top: 7px; }

    /* ===== Kotak petunjuk =====
       Paragraf biasa, bukan baris lentur. .orcha-tenggat dipasang inline-flex
       ber-gap sehingga tiap potongan kalimat jadi benda terpisah yang
       dibungkus sendiri-sendiri; di layar sempit satu kata bisa berdiri
       sendirian satu baris dan kalimatnya tidak lagi terbaca sebagai kalimat. */
    .ps-petunjuk {
        display: flex; gap: 10px; padding: 12px 14px; border-radius: 13px;
        background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
        font-size: .83rem; line-height: 1.6; margin: 0 0 14px;
    }
    .ps-petunjuk > i.bi { font-size: 1rem; line-height: 1.5; flex-shrink: 0; }
    .ps-petunjuk strong { color: #78350f; }
    .ps-catatan { color: var(--dsb-redup); font-size: .82rem; line-height: 1.6; margin: 0 0 14px; }

    /* ===== Judul kolom daftar =====
       Ditulis sekali di atas, bukan mengandalkan teks bayangan di dalam
       isian: teks bayangan hilang begitu isiannya terisi, dan pada daftar dua
       puluh baris tidak ada lagi yang memberi tahu kolom mana yang mana. */
    .ps-kepala-kolom { padding: 0 2px 7px; border-bottom: 1px solid #f1f5f9; margin-bottom: 4px; }
    .ps-kepala-kolom .orcha-label-kecil { margin: 0; }

    /* ===== Kaki daftar =====
       Menempel di dasar layar saat daftarnya panjang: pada rombongan empat
       puluh orang, tombol simpan yang hanya ada di ujung bawah berarti
       menggulung seluruh daftar untuk menyimpan satu koreksi. */
    .ps-kaki {
        position: sticky; bottom: 0; z-index: 3;
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 10px; margin: 16px -4px -4px; padding: 13px 15px;
        border-radius: 14px; background: #f8fafc; border: 1px solid var(--dsb-tepi);
    }
    .ps-kaki-hitung { color: var(--dsb-redup); font-size: .82rem; }
    .ps-kaki-hitung strong { color: var(--dsb-tinta); }
    .ps-kaki-aksi { display: flex; flex-wrap: wrap; gap: 8px; }
    @media (max-width: 575.98px) {
        .ps-kaki { flex-direction: column; align-items: stretch; }
        .ps-kaki-aksi > * { flex: 1 1 0; justify-content: center; }
    }

    /* Isian daftar memakai bentuk dasbor, bukan .form-control yang dipatok
       48px dengan tepi 2px oleh templateindex — pada daftar empat puluh baris
       selisih tinggi itu menjadi satu layar penuh. */
    .ps-peserta .dsb-isian { min-height: 36px; padding: 7px 11px; font-size: .84rem; }
    .ps-peserta textarea.dsb-isian { min-height: 152px; font-size: .84rem; }
    .ps-peserta .orcha-ganti-gabung .dsb-isian { flex: 1 1 auto; min-width: 0; }

    /* ===== Baris peserta di layar sempit =====

       Kisi Bootstrap menahan kelima kolom pada SATU baris betapa pun
       sempitnya layar. Di HP kolom nama tersisa sekitar 30px — isinya
       terpotong jadi "He", "Ri", "Di", dan daftar yang tidak terbaca sama
       saja dengan tidak ada. Di bawah 768px barisnya dibiarkan patah: nomor
       + penanda kesehatan + nama di baris pertama, titik jemput + tombolnya
       di baris kedua. Keduanya tetap satu baris data karena punya bingkai
       sendiri. */
    @media (max-width: 767.98px) {
        .ps-peserta .orcha-baris-peserta {
            border: 1px solid var(--dsb-tepi); border-radius: 13px;
            padding: 10px; margin-bottom: 9px;
        }
        .ps-peserta .orcha-baris-peserta > .col { flex: 1 1 150px; min-width: 140px; }
        .ps-peserta .orcha-baris-peserta > .col-4 {
            flex: 1 1 190px; width: auto; max-width: none;
        }
    }
    </style>

    @php
        $tercatat = (int) ($pendaftaran['jumlah_peserta'] ?? 0);
        $terisi = collect($barisPeserta)->filter(fn ($b) => trim($b['nama'] ?? '') !== '')->count();
        $lengkap = $tercatat > 0 && $terisi >= $tercatat;
    @endphp

    <div class="dsb ps-peserta">

        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        {{-- ============ KEPALA ============

             Kartu kepala yang lama menyisakan sepertiga lebar kosong di kanan
             judulnya. Di sini sisi kanan itu dipakai tindakannya, dan angka
             "berapa nama yang sudah terisi" naik ke lencana — itu pertanyaan
             pertama yang dibawa orang saat membuka halaman ini. --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Daftar Peserta</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <i class="bi bi-person-vcard me-1"></i>{{ $pendaftaran['nama'] ?? '' }}
                        · {{ $pendaftaran['paket']['nama'] ?? 'Paket menyusul' }}
                    </span>
                    <span class="d-block ps-lencana-kepala">
                        <span class="orcha-kode">{{ $pendaftaran['kode'] ?? '—' }}</span>
                        <span class="dsb-lencana {{ $lengkap ? 'is-hijau' : 'is-kuning' }}">
                            <i class="bi bi-people"></i>
                            {{ $terisi }} dari {{ $tercatat }} nama sudah diisi
                        </span>
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.pendaftaran.detail', $pendaftaranId) }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>

                {{-- Salinan tombol simpan di kepala, sepasang dengan yang di kaki
                     daftar. Pada rombongan empat puluh orang, admin yang
                     mengoreksi satu nama di baris ketiga tidak perlu menggulung
                     ke mana pun. --}}
                <button type="button" class="dsb-tombol is-utama" wire:click="simpan"
                    wire:loading.attr="disabled" wire:target="simpan">
                    <i class="bi bi-check-lg"></i><span>Simpan</span>
                </button>
            </div>
        </header>

        {{-- ============ ISI CEPAT ============ --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-lightning-charge"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Isi Banyak Nama Sekaligus</h2>
                            <span class="dsb-kartu-sub">Dua cara — pakai yang paling gampang buat Anda</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    <div class="ps-duo">
                        <div class="ps-blok">
                            <div class="ps-blok-judul"><i class="bi bi-clipboard-check"></i> Tempel dari Excel atau WhatsApp</div>
                            <p class="ps-blok-ket">
                                Satu nama per baris. Kalau ditulis dua kolom, kolom kedua
                                dianggap titik jemput. Nomor urut daftar WhatsApp dan baris
                                judul tidak ikut terbaca.
                                <br>
                                Mau <strong>mengganti peserta</strong>? Tulis
                                <code>Haha &gt; Wiam</code> — nama lama, panah, nama penggantinya.
                                Titik jemput penggantinya ditulis di kolom kedua seperti biasa:
                                <code>Haha &gt; Wiam, Klaten</code>. Dikosongkan berarti
                                penggantinya naik di titik yang sama.
                            </p>

                            <textarea class="dsb-isian orcha-tempel" rows="6" wire:model="tempelan"
                                placeholder="Budi Santoso&#10;Siti Rahmawati, Stasiun Tugu&#10;Haha &gt; Wiam, Klaten"></textarea>

                            <div class="ps-blok-kaki">
                                <button type="button" class="dsb-tombol is-lembut" style="--ikon: #16a34a"
                                    wire:click="tempel">
                                    <i class="bi bi-plus-circle"></i><span>Ambil namanya</span>
                                </button>
                            </div>
                        </div>

                        <div class="ps-blok">
                            <div class="ps-blok-judul"><i class="bi bi-file-earmark-arrow-up"></i> Unggah berkas dari panitia</div>
                            <p class="ps-blok-ket">
                                Excel atau CSV, paling besar 2 MB. Baris judulnya diabaikan.
                                Kolom ketiga <strong>Menggantikan</strong> boleh diisi nama lama
                                bila baris itu menggantikan peserta — titik jemput penggantinya
                                tetap di kolom kedua, dan yang dikosongkan ikut titik lamanya.
                            </p>

                            <input type="file" class="dsb-isian" wire:model="berkasPeserta"
                                accept=".xlsx,.xls,.csv,.txt">

                            <div wire:loading wire:target="berkasPeserta" class="ps-blok-ket mt-2 mb-0">
                                <span class="dsb-putar is-kecil"></span> Membaca berkas…
                            </div>
                            @error('berkasPeserta')
                                <div class="dsb-galat mt-2">{{ $message }}</div>
                            @enderror

                            {{-- Bentuk berkasnya diperlihatkan, bukan cuma disebut.

                                 Tiga kata "kolom pertama, kedua, ketiga" menuntut admin
                                 membayangkan sendiri tabelnya, dan yang salah membayangkan
                                 baru tahu setelah berkasnya ditolak. Contoh sebesar ini
                                 sekaligus mengisi kaki blok yang — karena blok di sebelahnya
                                 berisi kotak tempelan — kalau dibiarkan jadi petak putih
                                 setinggi tombolnya sendiri. --}}
                            <div class="ps-contoh">
                                <div class="ps-contoh-judul">Isi berkasnya kira-kira begini</div>
                                <div class="ps-contoh-baris is-judul">
                                    <span>Nama</span><span>Titik Jemput</span><span>Menggantikan</span>
                                </div>
                                <div class="ps-contoh-baris">
                                    <span>Budi Santoso</span><span>Jogja</span><span class="is-kosong">—</span>
                                </div>
                                <div class="ps-contoh-baris">
                                    <span>Wiam</span><span>Klaten</span><span>Haha</span>
                                </div>
                            </div>

                            <div class="ps-blok-kaki">
                                {{-- Templat dibuat untuk pendaftaran ini: barisnya sebanyak
                                     peserta yang tercatat, dan kolom titik jemputnya sudah
                                     berisi pilihan milik paketnya. Panitia memilih alih-alih
                                     mengetik, sehingga ejaan yang kembali ke sistem sama
                                     dengan yang sudah ditentukan. --}}
                                <a href="{{ route('admin.orcha.pendaftaran.peserta.templat', $pendaftaranId) }}"
                                    class="dsb-tombol is-lembut" style="--ikon: #16a34a">
                                    <i class="bi bi-download"></i><span>Unduh contoh berkasnya</span>
                                </a>

                                <span class="ps-blok-ket mb-0" style="flex: 1 1 180px">
                                    Nama biasa <strong>menambah</strong> daftar di bawah; nama yang
                                    menyatakan menggantikan seseorang <strong>menimpa</strong> barisnya.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ DAFTAR NAMA ============ --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-people-fill"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Nama Peserta</h2>
                            <span class="dsb-kartu-sub">Satu baris satu orang — nama dan titik jemputnya</span>
                        </span>
                    </div>

                    <div class="ps-kaki-aksi">
                        {{-- Sakelar pembagian bus & kamar.

                             Disembunyikan sampai diminta: untuk open trip berlima,
                             dua kolom yang selalu kosong hanya menyempitkan kolom
                             nama. Menyala sendiri bila pembagiannya sudah ada —
                             yang membuka rombongan yang sudah dibagi tidak boleh
                             melihat pembagiannya lenyap. --}}
                        <button type="button"
                            class="dsb-tombol {{ $bagiKelompok ? 'is-utama' : 'is-lembut' }}"
                            style="--ikon: #0284c7"
                            wire:click="$toggle('bagiKelompok')"
                            title="Tampilkan kolom pembagian bus dan kamar">
                            <i class="bi bi-bus-front"></i>
                            <span>{{ $bagiKelompok ? 'Sedang membagi bus & kamar' : 'Bagi bus & kamar' }}</span>
                        </button>

                        <button type="button" class="dsb-tombol is-lembut" style="--ikon: #16a34a"
                            wire:click="tambahBaris">
                            <i class="bi bi-plus-lg"></i><span>Tambah baris</span>
                        </button>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    {{-- Penggantian peserta dikerjakan DI SINI, dan itu tidak jelas dengan
                         sendirinya: admin hanya mengetik nama lain lalu menyimpan. Tanpa
                         kalimat ini, yang mencari tombol "ganti peserta" tidak akan
                         menemukan apa pun. --}}
                    <p class="ps-petunjuk">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>
                            <strong>Mau mengganti peserta?</strong> Tekan tombol
                            <i class="bi bi-arrow-left-right"></i> di baris orang yang berhalangan —
                            nama lamanya ditahan, dan nama pengganti diisi di kotak barunya sendiri.
                            Perubahannya dicatat beserta nama lama, dan surat pernyataannya bisa
                            diunduh dari halaman detail. Tidak ada biaya tambahan sepanjang jumlah
                            pesertanya tetap.
                        </span>
                    </p>

                    {{-- Peringatan yang harus dibaca SEBELUM mengetik: kaitan riwayat
                         kesehatan berdasarkan nama, jadi mengubah ejaan nama memutus
                         kaitannya. --}}
                    <p class="ps-catatan">
                        Peserta yang data kesehatannya sudah masuk ditandai
                        <i class="bi bi-heart-pulse-fill orcha-tanda-sehat"></i>.
                        Mengubah ejaan namanya memutus kaitan dengan data itu — datanya tidak
                        hilang, tetapi tidak lagi dikenali sebagai miliknya.
                    </p>

                    <div class="row g-2 d-none d-md-flex ps-kepala-kolom">
                        <div class="col-auto" style="width:2.9rem"></div>
                        <div class="col-auto" style="width:1.7rem"></div>
                        <div class="col"><span class="orcha-label-kecil">Nama peserta</span></div>
                        <div class="col-4"><span class="orcha-label-kecil">Titik jemput</span></div>
                        <div class="col-auto" style="width:5.4rem"></div>
                    </div>

                    @foreach ($barisPeserta as $urutan => $baris)
                        @php
                            $namaBaris = mb_strtolower(trim($baris['nama'] ?? ''));
                            $sudah = $namaBaris !== '' && in_array($namaBaris, $sudahIsi, true);
                        @endphp
                        <div class="row g-2 align-items-center orcha-baris-peserta"
                            wire:key="baris-peserta-{{ $urutan }}">
                            <div class="col-auto" style="width:2.9rem">
                                <span class="orcha-nomor-peserta">{{ $urutan + 1 }}</span>
                            </div>

                            {{-- Penanda riwayat kesehatan: satu ikon hati di kolom sempit,
                                 bukan lencana bertulisan.

                                 Lencana bertulisan menuntut kolom selebar 6,6rem yang pada
                                 baris tanpa riwayat berubah jadi lubang di tengah daftar —
                                 rapi barisnya, tapi bolong tampilannya. Kolomnya tetap ada
                                 meski kosong supaya tiap baris setinggi yang lain. --}}
                            <div class="col-auto text-center" style="width:1.7rem">
                                @if ($sudah)
                                    <i class="bi bi-heart-pulse-fill orcha-tanda-sehat"
                                        title="Data kesehatannya sudah masuk. Mengubah ejaan namanya memutus kaitan itu."></i>
                                @endif
                            </div>

                            {{-- value ditulis eksplisit: wire:model sendiri tidak menggambar
                                 nilainya di HTML dari server, dan baris hasil tempelan harus
                                 sudah terlihat sebelum admin menekan simpan — kalau tidak, ia
                                 menyimpan sesuatu yang belum sempat diperiksanya. --}}
                            <div class="col">
                                @if (filled($baris['gantikan'] ?? null))
                                    {{-- Nama lama ikut MASUK ke dalam kotaknya, mendahului
                                         panah: satu baris yang terbaca utuh sebagai
                                         "suparjiman → hafid".

                                         Ditaruh di baris terpisah di atas kotak, matanya harus
                                         melompat dua kali untuk merangkai satu kalimat — dan
                                         pada daftar dua puluh orang, lompatan itu berulang dua
                                         puluh kali. --}}
                                    <div class="orcha-ganti-gabung">
                                        <span class="orcha-ganti-lama">{{ $baris['gantikan'] }}</span>
                                        <i class="bi bi-arrow-right"></i>
                                        <input type="text" class="dsb-isian"
                                            wire:model="barisPeserta.{{ $urutan }}.nama"
                                            value="{{ $baris['nama'] ?? '' }}"
                                            placeholder="nama pengganti" autofocus>
                                    </div>
                                @else
                                    <input type="text" class="dsb-isian"
                                        wire:model="barisPeserta.{{ $urutan }}.nama"
                                        value="{{ $baris['nama'] ?? '' }}" placeholder="Nama peserta">
                                @endif
                            </div>

                            <div class="col-4">
                                {{-- Dipilih, bukan diketik.

                                     Mengetik bebas menghasilkan ejaan berbeda-beda untuk tempat
                                     yang sama — "Klaten" dan "klaten" — dan daftar jemputan lalu
                                     mencetak dua kelompok bernama sama, lalu sopir berhenti dua
                                     kali di tempat yang satu. --}}
                                @php
                                    /* Kotaknya tampil selama baris ini sedang diganti — tidak
                                       menunggu titiknya benar-benar berpindah.

                                       Sempat dibuat muncul hanya saat titiknya beda, dan itu
                                       justru yang membingungkan: kolom nama berubah bentuk,
                                       kolom di sebelahnya tidak, dan admin tidak punya cara
                                       tahu apakah titik jemputnya ikut terbawa ke pengganti
                                       atau terlewat sama sekali. Sekarang kedua kolom selalu
                                       berubah bersama-sama. */
                                    $titikPindah = filled($baris['gantikan'] ?? null)
                                        && filled($baris['gantikan_titik'] ?? null);
                                @endphp

                                {{-- Titik lamanya ikut dicoret, sebentuk dengan nama penggantinya
                                     di kolom sebelah: satu baris, dua perubahan, dibaca dengan cara
                                     yang sama. --}}
                                @if ($titikPindah)
                                    <div class="orcha-ganti-gabung">
                                        <span class="orcha-ganti-lama">{{ $baris['gantikan_titik'] }}</span>
                                        <i class="bi bi-arrow-right"></i>
                                @endif

                                @if ($pilihanTitik !== [])
                                    <select class="dsb-isian"
                                        wire:model="barisPeserta.{{ $urutan }}.titik_jemput">
                                        <option value="" @selected((string) ($this->barisPeserta[$urutan]['titik_jemput'] ?? '') === '')>— belum dipilih —</option>
                                        @foreach ($pilihanTitik as $titik)
                                            {{-- Dicocokkan tanpa memedulikan besar-kecil huruf:
                                                 data lama menyimpan "klaten" sedangkan daftar
                                                 pilihannya "Klaten", dan perbandingan persis huruf
                                                 membuat titik yang sudah terisi tampil sebagai
                                                 "belum dipilih" — lalu ikut hilang saat disimpan. --}}
                                            <option value="{{ $titik }}"
                                                @selected(mb_strtolower(trim($baris['titik_jemput'] ?? '')) === mb_strtolower($titik))>
                                                {{ $titik }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    {{-- Paket ini belum menentukan titik jemput mana pun.
                                         Isian bebas dibiarkan supaya admin tidak terkunci
                                         tanpa satu pun pilihan. --}}
                                    <input type="text" class="dsb-isian"
                                        wire:model="barisPeserta.{{ $urutan }}.titik_jemput"
                                        value="{{ $baris['titik_jemput'] ?? '' }}"
                                        placeholder="Titik jemput belum ditentukan paket">
                                @endif

                                @if ($titikPindah)
                                    </div>
                                @endif
                            </div>

                            {{-- Bus dan kamar, hanya saat pembagian dinyalakan.

                                 Untuk open trip berlima, dua kolom yang selalu kosong
                                 hanya menyempitkan kolom nama dan menambah benda yang
                                 harus diabaikan mata. Untuk study tour 120 orang,
                                 inilah yang dikerjakan seminggu sebelum berangkat. --}}
                            @if ($bagiKelompok)
                                <div class="col-12 col-md-2">
                                    <input type="text" class="dsb-isian"
                                        wire:model="barisPeserta.{{ $urutan }}.bus"
                                        value="{{ $baris['bus'] ?? '' }}"
                                        maxlength="60" placeholder="Bus">
                                </div>

                                <div class="col-12 col-md-2">
                                    <input type="text" class="dsb-isian"
                                        wire:model="barisPeserta.{{ $urutan }}.kamar"
                                        value="{{ $baris['kamar'] ?? '' }}"
                                        maxlength="60" placeholder="Kamar">
                                </div>
                            @endif

                            <div class="col-auto text-end" style="width:5.4rem">
                                @if (filled($baris['gantikan'] ?? null))
                                    <button type="button" class="orcha-hapus-baris orcha-tombol-batal"
                                        wire:click="batalGanti({{ $urutan }})"
                                        title="Batal mengganti, kembalikan nama lamanya">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                @elseif (filled($baris['nama'] ?? null))
                                    <button type="button" class="orcha-hapus-baris orcha-tombol-ganti"
                                        wire:click="mulaiGanti({{ $urutan }})"
                                        title="Peserta ini berhalangan, digantikan orang lain">
                                        <i class="bi bi-arrow-left-right"></i>
                                    </button>
                                @endif

                                <button type="button" class="orcha-hapus-baris"
                                    wire:click="hapusBaris({{ $urutan }})" title="Hapus baris ini">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    <div class="ps-kaki">
                        <span class="ps-kaki-hitung">
                            <strong>{{ $terisi }}</strong> nama sudah diisi dari
                            <strong>{{ $tercatat }}</strong> peserta yang tercatat
                        </span>

                        <div class="ps-kaki-aksi">
                            <a href="{{ route('admin.orcha.pendaftaran.detail', $pendaftaranId) }}"
                                wire:navigate class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                <i class="bi bi-x-lg"></i><span>Batal</span>
                            </a>
                            <button type="button" class="dsb-tombol is-utama" wire:click="simpan"
                                wire:loading.attr="disabled" wire:target="simpan">
                                <span wire:loading.remove wire:target="simpan" class="orcha-ikon-teks">
                                    <i class="bi bi-check-lg"></i> Simpan daftar peserta
                                </span>
                                <span wire:loading wire:target="simpan" class="orcha-ikon-teks">
                                    <span class="dsb-putar is-kecil"></span> Menyimpan…
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
