@section('title')
Data Pelamar Kerja || lemon
@stop

<div>
    {{-- Kerangka mengikuti Pesanan Toko (bahasa rupa dsb-*): kartu tab
         berangka, saringan, lalu tabel dsb. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.partials.kartu-tab-gaya')
    @include('livewire.pages.admin.pelamar-kerja.partials.berkas-gaya')

    {{-- Gaya khusus layar ini, ditulis INLINE: public/build di-gitignore. --}}
    <style>
    .plm-saring { display: grid; grid-template-columns: minmax(0, 1.6fr) repeat(2, minmax(0, 1fr)) auto; gap: 12px; align-items: end; }
    @media (max-width: 991.98px) { .plm-saring { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); } }
    @media (max-width: 575.98px) { .plm-saring { grid-template-columns: minmax(0, 1fr); } }
    .plm-medan-aksi { display: flex; align-items: flex-end; gap: 8px; flex-wrap: wrap; }

    .plm-avatar {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: .86rem; color: #fff;
    }
    .plm-tautan { text-decoration: none; color: inherit; display: flex; align-items: center; gap: 11px; min-width: 0; }
    .plm-tautan:hover .dsb-tabel-judul { color: #6d28d9; text-decoration: underline; }
    .plm-aksi { display: inline-flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap; }
    .plm-samar { color: var(--dsb-redup); }

    /* Nomor lamaran SELALU terlihat.

       Dulu memakai .dsb-tabel-samar, yang di sistem desain berarti "salinan
       kolom yang hilang di layar sempit": ia disembunyikan di layar lebar DAN
       di ponsel, jadi nomornya cuma muncul di rentang tablet. Padahal nomor
       inilah kunci pelamar mengecek statusnya sendiri, dan satu-satunya yang
       dicari admin saat pelamar menyebutkannya lewat telepon. */
    .plm-nomor {
        display: block; margin-top: 2px;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: .72rem; color: var(--dsb-redup, #6b7280); letter-spacing: .02em;
    }

    /* Keterangan kecil di bawah tanggal (mis. "berkas sudah dihapus").

       Sebelumnya ditulis "dsb-tabel-samar d-block" — dan .d-block Bootstrap
       ber-!important, jadi ia justru selalu tampil sementara nomor lamaran di
       baris sebelahnya selalu hilang. Dua kelas yang sama, dua hasil
       berlawanan, dua-duanya tidak disengaja. */
    .plm-catatan-sel { display: block; margin-top: 2px; font-size: .74rem; color: var(--dsb-redup, #6b7280); }

    /* Email panjang di ponsel.

       Alamat seperti bertojunikrisnanto@gmail.com lebih lebar dari kartu
       390px: tanpa ini ikon amplopnya terlempar ke barisnya sendiri dan
       alamatnya menabrak tepi kartu. */
    .plm-tabel .dsb-tabel-meta > span { min-width: 0; }
    .plm-tabel .dsb-tabel-meta { overflow-wrap: anywhere; }

    /* Tanda bahwa lamaran ini belum pernah dibuka siapa pun. */
    .plm-baru {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: .68rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
        color: #b45309;
    }
    .plm-baru .plm-titik { width: 6px; height: 6px; border-radius: 50%; background: #f59e0b; }

    /* ===== Aksi massal ===== */
    .plm-massal {
        display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
        padding: 11px 13px; margin: 0 0 2px; border-radius: 12px;
        background: #f5f3ff; border: 1px solid #ddd6fe; color: #5b21b6; font-size: .83rem;
    }
    .plm-massal-isi { flex: 1 1 200px; min-width: 0; }
    .plm-massal select { max-width: 220px; }
    .plm-cek { width: 16px; height: 16px; accent-color: #7c3aed; cursor: pointer; vertical-align: middle; }
    .plm-kolom-cek { width: 38px; }

    /* Nama tombol aksi: tersembunyi dari mata di layar lebar, tetap terbaca
       pembaca layar. Bukan display:none — yang itu ikut menghilangkannya dari
       pembaca layar juga. */
    .plm-aksi-teks {
        position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
        overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0;
    }
    @media (max-width: 767.98px) {
        .plm-aksi-teks {
            position: static; width: auto; height: auto; margin: 0;
            overflow: visible; clip: auto; white-space: normal;
        }
    }
    @media (max-width: 767.98px) {
        /* Di kartu ponsel, centangnya ikut baris "Pilih" biasa. */
        .plm-kolom-cek { width: auto; }

        /* Tiga sel yang isinya PANJANG memakai lebar penuh kartu.

           Bawaan kartu ponsel menaruh label di kiri dan nilainya di kanan
           pada baris yang sama — bagus untuk "Tahap: Wawancara", tetapi
           menyisakan sekitar 230px untuk alamat email, dan alamat seperti
           bertojunikrisnanto@gmail.com lalu patah di tengah kata. Hal yang
           sama memotong tulisan "berkas belum dibuka" di tepi kartu dan
           menumpuk tombol aksi jadi tiga baris. */
        .plm-tabel tbody td[data-judul="Pelamar"],
        .plm-tabel tbody td[data-judul="Melamar"],
        .plm-tabel tbody td[data-judul="Aksi"] { display: block; }
        .plm-tabel tbody td[data-judul="Pelamar"]::before,
        .plm-tabel tbody td[data-judul="Melamar"]::before,
        .plm-tabel tbody td[data-judul="Aksi"]::before { display: block; margin-bottom: 6px; }
        /* !important di sini melawan !important yang lain: selnya memakai
           .text-end Bootstrap (text-align: right !important) supaya tombolnya
           rata kanan di tabel layar lebar. Di kartu ponsel label "AKSI"
           ikut terlempar ke kanan sementara tombolnya di kiri. */
        .plm-tabel tbody td[data-judul="Aksi"] { text-align: left !important; }
        .plm-aksi { justify-content: flex-start; }
    }

    /* Keterangan "sedang membaca pelamar posisi X", saat layar ini dibuka
       lewat tautan dari daftar Lowongan. Tanpa itu, daftar yang tersaring
       terbaca sebagai seluruh pelamar — dan yang membacanya menyimpulkan
       lamaran lain hilang. */
    .plm-konteks {
        display: flex; align-items: center; gap: 9px; flex-wrap: wrap;
        margin-top: 12px; padding: 10px 13px; border-radius: 12px;
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;
        font-size: .82rem; line-height: 1.5;
    }
    .plm-konteks > i.bi { flex-shrink: 0; }
    </style>

    @php
        $bisaHapus = auth()->user()->hasPermission('delete_pelamar');
        $adaSaringan = $this->adaSaringan();
        $tahapAktif = (string) $this->filterStatus;
        $warnaTahap = [
            '' => '#7c3aed', 'baru' => '#0ea5e9', 'diseleksi' => '#6366f1',
            'wawancara' => '#d97706', 'diterima' => '#16a34a', 'ditolak' => '#64748b',
        ];
        $ikonTahap = [
            '' => 'bi-collection', 'baru' => 'bi-inbox-fill', 'diseleksi' => 'bi-file-earmark-text',
            'wawancara' => 'bi-chat-dots-fill', 'diterima' => 'bi-check-circle-fill', 'ditolak' => 'bi-slash-circle',
        ];
        $targetMuat = 'search,filterMonth,filterJob,filterStatus,setTahap,resetFilters,urutkan,terapkanTahapMassal,gotoPage,previousPage,nextPage';
        $bisaUbah = $this->bisaUbah;
        $idHalaman = $dataPelamar->pluck('id')->map(fn ($n) => (string) $n)->all();
        $semuaTercentang = $idHalaman !== [] && empty(array_diff($idHalaman, array_map('strval', $pilihan)));
        // Kolom yang bisa diurut: kunci => judul.
        $kolomUrut = ['nama' => 'Pelamar', 'posisi' => 'Posisi', 'tahap' => 'Tahap', 'terbaru' => 'Melamar'];
        $avatarGrad = ['#7c3aed,#6d28d9', '#2563eb,#0ea5e9', '#059669,#10b981', '#e11d48,#f43f5e', '#d97706,#f59e0b'];
    @endphp

    <div class="dsb plm-daftar">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Pelamar Kerja</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="d-block">
                        Semua lamaran yang masuk lewat situs — per posisi maupun lamaran umum.
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <button type="button" wire:click="ekspor" class="dsb-tombol is-lembut" style="--ikon: #16a34a"
                    wire:loading.attr="disabled" wire:target="ekspor"
                    title="Unduh Excel berisi persis yang sedang tampil">
                    <i class="bi bi-file-earmark-excel"></i><span>Unduh Excel</span>
                </button>
            </div>
        </header>

        {{-- ================== TAHAP ==================

             Satu kelompok: semua tab di sini cara melihat daftar yang sama,
             diurut mengikuti perjalanan lamaran. "Belum dibuka" sengaja
             paling kiri sesudah Semua — itu satu-satunya tumpukan yang
             menunggu dikerjakan orang. --}}
        <div class="dsb-rak dsb-bagian">
            <div class="k-12">
                {{-- Satu kelompok, jadi lebarnya penuh: bawaan .pt-grup-deret
                     dua kolom (7fr + 3fr) untuk layar Pesanan Toko yang
                     memang punya dua kelompok. Ditulis di sini DAN dijaga
                     aturan :has() di partialnya — :has() belum ada di
                     peramban lama, dan petak kosongnya kembali tanpa satu
                     pun tanda bahwa sebabnya dukungan CSS. --}}
                <div class="pt-grup-deret" style="--grup-kolom: minmax(0, 1fr)">
                    <nav class="pt-grup" aria-label="Tahap seleksi">
                        <div class="pt-grup-kepala">
                            <span class="pt-grup-judul">Tahap seleksi</span>
                            <span class="pt-grup-ket">Urut perjalanan lamaran</span>
                        </div>
                        <div class="pt-kartu-deret" style="--n: 6">
                            @php
                                $daftarTahap = [['', 'Semua lamaran']];
                                foreach (\App\Models\JobApplication::STATUS as $kunci => $info) {
                                    $daftarTahap[] = [$kunci, $info[0]];
                                }
                            @endphp
                            @foreach ($daftarTahap as [$kunci, $label])
                                @php
                                    $angka = $jumlahTahap[$kunci] ?? 0;
                                    $iniAktif = $tahapAktif === $kunci;
                                @endphp
                                <button type="button" wire:click="setTahap('{{ $kunci }}')"
                                    style="--c: {{ $warnaTahap[$kunci] ?? '#7c3aed' }}"
                                    class="pt-kartu-tab {{ $iniAktif ? 'is-aktif' : '' }} {{ $angka === 0 ? 'is-nol' : '' }}"
                                    aria-pressed="{{ $iniAktif ? 'true' : 'false' }}">
                                    <span class="pt-kartu-ikon"><i class="bi {{ $ikonTahap[$kunci] ?? 'bi-dot' }}"></i></span>
                                    <span class="pt-kartu-teks">
                                        <span class="pt-kartu-angka">{{ number_format($angka, 0, ',', '.') }}</span>
                                        <span class="pt-kartu-label">{{ $label }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </nav>
                </div>
            </div>

            {{-- ================== SARINGAN ================== --}}
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-isi">
                    <div class="plm-saring">
                        <div class="dsb-medan">
                            <label class="dsb-label" for="plm-cari">Cari pelamar</label>
                            <div class="dsb-cari">
                                <i class="bi bi-search"></i>
                                <input id="plm-cari" type="search" class="dsb-isian"
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="Nama, email, telepon, nomor lamaran, atau posisi">
                            </div>
                        </div>

                        <div class="dsb-medan">
                            <label class="dsb-label" for="plm-posisi">Posisi yang dilamar</label>
                            <select id="plm-posisi" wire:model.live="filterJob" class="dsb-isian">
                                <option value="" @selected((string) $this->filterJob === '')>Semua posisi</option>
                                <option value="umum" @selected((string) $this->filterJob === 'umum')>Lamaran umum</option>
                                @foreach ($jobList as $job)
                                    <option value="{{ $job->id }}" @selected((string) $this->filterJob === (string) $job->id)>{{ $job->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="dsb-medan">
                            <label class="dsb-label" for="plm-bulan">Bulan melamar</label>
                            <select id="plm-bulan" wire:model.live="filterMonth" class="dsb-isian">
                                <option value="" @selected((string) $this->filterMonth === '')>Semua bulan</option>
                                @foreach ($months as $month)
                                    <option value="{{ $month['value'] }}" @selected((string) $this->filterMonth === (string) $month['value'])>{{ $month['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="dsb-medan plm-medan-aksi">
                            @if ($adaSaringan)
                                <button type="button" wire:click="resetFilters" class="dsb-tombol is-lembut">
                                    <i class="bi bi-arrow-counterclockwise"></i><span>Tampilkan semua lagi</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($lowonganDisaring)
                        {{-- Layar ini bisa dibuka lewat tautan "7 orang" dari daftar
                             Lowongan. Tanpa keterangan ini, daftar yang tersaring
                             terbaca sebagai seluruh pelamar — dan yang membacanya
                             menyimpulkan lamaran lain hilang. --}}
                        <div class="plm-konteks">
                            <i class="bi bi-funnel-fill"></i>
                            <span>
                                Sedang menampilkan pelamar posisi
                                <strong>{{ $lowonganDisaring->title }}</strong> saja.
                            </span>
                            <a href="{{ route('admin.lowongan.index') }}" wire:navigate
                                class="dsb-tombol is-lembut" style="--ikon: #1e40af">
                                <i class="bi bi-arrow-left"></i><span>Kembali ke Lowongan</span>
                            </a>
                        </div>
                    @endif

                    @if ($adaSaringan)
                        <p class="plm-samar" style="margin: 12px 0 0; font-size: .8rem">
                            <i class="bi bi-info-circle"></i>
                            <strong>Unduh Excel</strong> ikut tersaring — isinya persis yang sedang tampil.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================== DAFTARNYA ================== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat" wire:target="{{ $targetMuat }}">
                <div class="pt-daftar-kepala" style="--c: {{ $warnaTahap[$tahapAktif] ?? '#7c3aed' }}">
                    <div class="pt-daftar-judul">
                        <b>{{ $tahapAktif === '' ? 'Semua lamaran' : (\App\Models\JobApplication::STATUS[$tahapAktif][0] ?? 'Lamaran') }}</b>
                        <span>
                            @if ($dataPelamar->total() > 0)
                                {{ number_format($dataPelamar->total(), 0, ',', '.') }} pelamar
                                {{-- Dulu di sini tertulis "belum ada yang membuka berkasnya" untuk
                                     tahap 'baru' — kalimat yang tidak pernah didukung data apa pun:
                                     tidak ada yang mencatat siapa membuka apa. Sekarang berkas yang
                                     dibuka benar-benar ditandai, dan tandanya ada per baris, bukan
                                     sebagai kesimpulan atas seluruh tumpukan. --}}
                            @else
                                Belum ada isinya
                            @endif
                        </span>
                    </div>
                </div>

                @if ($dataPelamar->isEmpty())
                    <div class="dsb-kartu-isi">
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon">
                                <i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-person-lines-fill' }}"></i>
                            </span>
                            <p class="dsb-kosong-judul">
                                {{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada pelamar' }}
                            </p>
                            <p class="dsb-kosong-ket">
                                {{ $adaSaringan
                                    ? 'Coba ganti kata pencarian, pilih posisi lain, atau tekan Semua lamaran.'
                                    : 'Lamaran yang masuk lewat halaman karier situs akan muncul di sini sendiri.' }}
                            </p>
                        </div>
                    </div>
                @else
                    @if ($bisaUbah && count($pilihan) > 0)
                        {{-- Aksi massal TIDAK mengirim surel, dan itu disebutkan di
                             sini, bukan cuma di kode. Satu klik yang mengirim belasan
                             surel tanpa pesan pribadi adalah kesalahan yang tidak bisa
                             ditarik kembali. --}}
                        <div class="dsb-kartu-isi" style="padding-bottom: 0">
                            <div class="plm-massal">
                                <span class="plm-massal-isi">
                                    <i class="bi bi-check2-square"></i>
                                    <strong>{{ count($pilihan) }} pelamar</strong> dicentang.
                                    Memindahkan tahapnya di sini <strong>tidak mengirim surel</strong> —
                                    untuk memberi tahu pelamar, buka lamarannya satu per satu.
                                </span>
                                <select wire:model="tahapMassal" class="dsb-isian" aria-label="Tahap tujuan">
                                    <option value="" @selected($this->tahapMassal === '')>Pindahkan ke tahap…</option>
                                    @foreach (\App\Models\JobApplication::STATUS as $kunciMassal => $infoMassal)
                                        <option value="{{ $kunciMassal }}" @selected($this->tahapMassal === $kunciMassal)>
                                            {{ $infoMassal[0] }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="dsb-tombol is-utama" wire:click="terapkanTahapMassal"
                                    wire:loading.attr="disabled" wire:target="terapkanTahapMassal">
                                    <i class="bi bi-arrow-right-circle"></i><span>Pindahkan</span>
                                </button>
                                <button type="button" class="dsb-tombol is-lembut" wire:click="bersihkanPilihan">
                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="dsb-tabel-bungkus">
                        <table class="dsb-tabel plm-tabel">
                            <thead>
                                <tr>
                                    @if ($bisaUbah)
                                        <th class="plm-kolom-cek">
                                            <input type="checkbox" class="plm-cek" title="Centang semua di halaman ini"
                                                aria-label="Centang semua pelamar di halaman ini"
                                                wire:click="{{ $semuaTercentang ? 'bersihkanPilihan' : 'pilihHalaman' }}"
                                                @checked($semuaTercentang)>
                                        </th>
                                    @endif
                                    @foreach ($kolomUrut as $kunciUrut => $judulUrut)
                                        <th wire:key="kolom-{{ $kunciUrut }}">
                                            <button type="button" wire:click="urutkan('{{ $kunciUrut }}')"
                                                class="dsb-tabel-urut {{ $urut === $kunciUrut ? 'aktif' : '' }}"
                                                title="Urutkan menurut {{ strtolower($judulUrut) }}">
                                                <span>{{ $judulUrut }}</span>
                                                <i class="bi {{ $urut === $kunciUrut ? ($arahUrut === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}"></i>
                                            </button>
                                        </th>
                                    @endforeach
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dataPelamar as $item)
                                    @php
                                        $lencanaBs = [
                                            'secondary' => 'is-abu', 'primary' => 'is-ungu', 'info' => 'is-biru',
                                            'success' => 'is-hijau', 'danger' => 'is-merah', 'warning' => 'is-kuning',
                                        ];
                                        $rupaTahap = $lencanaBs[$item->status_warna] ?? 'is-abu';
                                        $urlDetail = route('admin.pelamar.detail', $item->id);
                                        $adaBerkas = $item->ada_berkas;
                                        $belumDibuka = $adaBerkas && ! $item->berkas_dibuka_at;
                                        $jumlahLamaran = $emailBerulang[$item->email] ?? 0;
                                        $tercentang = in_array((string) $item->id, array_map('strval', $pilihan), true);
                                    @endphp
                                    <tr wire:key="pelamar-{{ $item->id }}">
                                        @if ($bisaUbah)
                                            <td data-judul="Pilih" class="plm-kolom-cek">
                                                <input type="checkbox" class="plm-cek" value="{{ $item->id }}"
                                                    wire:model.live="pilihan" @checked($tercentang)
                                                    aria-label="Pilih {{ $item->name }}">
                                            </td>
                                        @endif

                                        {{-- ===== Pelamar ===== --}}
                                        <td data-judul="Pelamar">
                                            <a wire:navigate href="{{ $urlDetail }}" class="plm-tautan">
                                                <span class="plm-avatar"
                                                    style="background: linear-gradient(135deg,{{ $avatarGrad[$loop->index % count($avatarGrad)] }});">
                                                    {{ mb_strtoupper(mb_substr($item->name, 0, 1)) }}
                                                </span>
                                                <span class="dsb-tabel-teks">
                                                    <span class="dsb-tabel-judul text-capitalize">{{ $item->name }}</span>
                                                    <span class="dsb-tabel-meta">
                                                        <span><i class="bi bi-envelope"></i>{{ $item->email }}</span>
                                                    </span>
                                                    <span class="plm-nomor">{{ $item->nomor_lamaran }}</span>
                                                </span>
                                            </a>

                                            @if ($jumlahLamaran > 1)
                                                {{-- Orang yang sama punya lamaran lain. Dihitung dari
                                                     SELURUH data, bukan halaman ini saja — lamaran
                                                     ganda justru paling sering terpisah beberapa
                                                     halaman, dan tanpa tanda ini dua baris itu
                                                     diperlakukan sebagai dua orang. --}}
                                                <span class="dsb-lencana is-kuning" style="margin-top: 6px"
                                                    title="Email yang sama punya {{ $jumlahLamaran }} lamaran">
                                                    <i class="bi bi-people"></i>{{ $jumlahLamaran }} lamaran
                                                </span>
                                            @endif
                                        </td>

                                        <td data-judul="Posisi">
                                            <span class="dsb-lencana is-biru text-capitalize">{{ $item->posisi_label }}</span>
                                        </td>

                                        <td data-judul="Tahap">
                                            <span class="dsb-lencana {{ $rupaTahap }}">{{ $item->status_label }}</span>
                                        </td>

                                        <td data-judul="Melamar">
                                            <span>{{ $item->created_at->locale('id')->isoFormat('D MMM YYYY') }}</span>
                                            @if (! $adaBerkas)
                                                {{-- Dua sebab, dan keduanya diringkas jadi satu kalimat
                                                     netral: lamaran ini memang dikirim tanpa lampiran
                                                     (CV boleh kosong sejak 10 Nov 2026), ATAU berkasnya
                                                     terlanjur dibuang pemangkas lama sebelum penghapusan
                                                     otomatis dicabut. Menebak salah satunya berarti
                                                     mengaku tahu yang tidak pernah dicatat. --}}
                                                <span class="plm-catatan-sel">tidak ada berkas</span>
                                            @elseif ($belumDibuka)
                                                {{-- "Belum dibuka" sekarang BENAR-BENAR dicatat, saat
                                                     berkasnya disajikan ke peramban. Dulu kalimat ini
                                                     cuma menebak dari status 'baru': berkas yang sudah
                                                     dibaca sepuluh kali tetap tertulis belum dibuka. --}}
                                                <span class="plm-catatan-sel">
                                                    <span class="plm-baru">
                                                        <span class="plm-titik"></span>berkas belum dibuka
                                                    </span>
                                                </span>
                                            @endif
                                        </td>

                                        <td data-judul="Aksi" class="text-end">
                                            <span class="plm-aksi">
                                                {{-- Baca CV-nya TANPA meninggalkan daftar.

                                                     Admin yang sedang menyaring sepuluh pelamar
                                                     kehilangan posisi gulung dan saringannya tiap kali
                                                     membuka halaman detail, lalu kembali ke daftar yang
                                                     sudah ter-reset — dan membaca sepuluh CV berarti
                                                     sepuluh kali begitu. --}}
                                                {{-- Tiap tombol membawa namanya sendiri.

                                                     Di layar lebar namanya disembunyikan (.plm-aksi-teks)
                                                     supaya kolomnya tetap ramping, tapi tetap terbaca
                                                     pembaca layar dan muncul sebagai tooltip; di kartu
                                                     ponsel namanya ditampilkan, karena di sana ruangnya
                                                     ada dan tiga ikon berjajar tanpa tulisan hanya bisa
                                                     ditebak. --}}
                                                @if ($adaBerkas)
                                                    <button type="button" wire:click="bukaPratinjau({{ $item->id }})"
                                                        class="dsb-tombol is-lembut" style="--ikon: #dc2626"
                                                        title="Baca berkas {{ $item->name }} di sini">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                        <span class="plm-aksi-teks">Baca berkas</span>
                                                    </button>
                                                @endif

                                                <a wire:navigate href="{{ $urlDetail }}"
                                                    class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                                                    title="Buka lamaran {{ $item->name }}">
                                                    <i class="bi bi-eye"></i>
                                                    <span class="plm-aksi-teks">Buka lamaran</span>
                                                </a>
                                                @if ($bisaHapus)
                                                    <button type="button" class="dsb-tombol is-bahaya delete-pelamar-btn"
                                                        data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                                        title="Hapus lamaran {{ $item->name }}">
                                                        <i class="bi bi-trash"></i>
                                                        <span class="plm-aksi-teks">Hapus</span>
                                                    </button>
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($dataPelamar->hasPages())
                        <div class="dsb-kartu-isi">
                            {{ $dataPelamar->links('vendor.pagination') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- ================== JENDELA PEMBACA BERKAS ==================

         Memakai kerangka .ts-modal milik sistem desain, BUKAN lapisan
         sendiri.

         Jendela ini sempat tertindih bilah samping dua kali, dan z-index
         BUKAN obatnya: menaikkannya dari 1050/1051 ke 1055/1056 tidak
         mengubah apa pun. Sebabnya ada di layout — #page-content dulu
         ber-will-change: opacity, yang mengurung semua isi halaman dalam
         satu konteks penumpukan, sehingga z-index sebesar apa pun di
         dalamnya tetap kalah dari bilah samping. Sudah dicabut di
         layouts/app.blade.php; keterangan lengkapnya di sana.

         Jendela selebar 560px tidak pernah memperlihatkan cacat itu karena
         tidak sampai menjorok ke wilayah bilah samping — yang 980px ini
         yang membuatnya terlihat. --}}
    @if ($pratinjau)
        <div class="ts-modal-back" wire:click="tutupPratinjau"></div>
        <div class="ts-modal" wire:key="pratinjau-{{ $pratinjau->id }}">
            <div class="ts-modal-card dsb is-datar is-pembaca" role="dialog" aria-modal="true"
                aria-label="Berkas lamaran {{ $pratinjau->name }}" tabindex="-1"
                x-on:keydown.escape.window="$wire.tutupPratinjau()">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #dc2626"><i class="bi bi-file-earmark-text"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul text-capitalize">{{ $pratinjau->name }}</h5>
                        <span class="dsb-kartu-sub">
                            {{ $pratinjau->posisi_label }} · {{ $pratinjau->nomor_lamaran }}
                        </span>
                    </span>
                    <a wire:navigate href="{{ route('admin.pelamar.detail', $pratinjau->id) }}"
                        class="dsb-tombol is-lembut" style="--ikon: #0284c7" title="Buka lamaran selengkapnya">
                        <i class="bi bi-eye"></i><span>Selengkapnya</span>
                    </a>
                    <button type="button" class="dsb-jendela-tutup" wire:click="tutupPratinjau" title="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="dsb-jendela-isi">
                    @include('livewire.pages.admin.pelamar-kerja.partials.berkas-pratinjau', [
                        'pelamar' => $pratinjau,
                        'aktif' => $berkasAktif,
                        'aksi' => 'bukaBerkas',
                    ])
                </div>
            </div>
        </div>
    @endif

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== SWEET ALERT DELETE (GLOSSY, SEPERTI BANNERS) ==================-->
<script>
    const glossyConfigPelamar = {
        background: 'rgba(255, 255, 255, 0.8)',
        backdrop: 'rgba(139, 92, 246, 0.15)',
        customClass: {
            popup: 'swal-glossy-popup',
            confirmButton: 'btn-glossy-confirm',
            cancelButton: 'btn-glossy-cancel',
            title: 'swal-glossy-title'
        },
        buttonsStyling: false
    };

    document.addEventListener('livewire:navigated', function() {
        document.body.addEventListener('click', function(event) {
            const button = event.target.closest('.delete-pelamar-btn');

            if (button) {
                event.preventDefault();
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');

                Swal.fire({
                    title: 'Yakin hapus pelamar?',
                    text: 'Data pelamar ' + (name || '') + ' (beserta berkas CV) akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigPelamar
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            Livewire.find(component.getAttribute('wire:id')).call('delete', id);
                        }
                    }
                });
            }
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->
