@section('title')
Data Lowongan Pekerjaan || lemon
@stop

<div>
    {{-- Kerangka mengikuti Pesanan Toko (bahasa rupa dsb-*): satu kisi 12
         kolom, kartu tab berangka, kartu putih datar. Kartu tab dipakai
         bersama lewat partial — satu rupa, satu definisi. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.partials.kartu-tab-gaya')

    {{-- Gaya khusus layar ini, ditulis INLINE: public/build di-gitignore,
         jadi berkas hasil build tidak pernah sampai ke server. --}}
    <style>
    .lw-saring { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) auto; gap: 12px; align-items: end; }
    @media (max-width: 767.98px) { .lw-saring { grid-template-columns: minmax(0, 1fr); } }
    .lw-medan-aksi { display: flex; align-items: flex-end; }
    .lw-medan-aksi:empty { display: none; }

    .lw-cip-deret { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }

    /* Nama lowongan: tautan ke layar ubahnya, bukan sekadar tulisan.
       Yang membuka daftar ini hampir selalu mau membuka salah satunya, dan
       tombol pensil selebar 32px di ujung kanan adalah sasaran terkecil di
       seluruh baris. */
    .lw-tautan { text-decoration: none; color: inherit; display: block; min-width: 0; }
    .lw-tautan:hover .dsb-tabel-judul { color: #6d28d9; text-decoration: underline; }

    .lw-aksi { display: inline-flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap; }
    /* Lencana yang bisa ditekan: tetap berupa lencana, tetapi jelas bahwa ia
       membawa ke suatu tempat. */
    .lw-lencana-tautan { text-decoration: none; }
    .lw-lencana-tautan:hover { filter: brightness(.95); text-decoration: underline; }

    /* Peringatan batas lamaran, di barisnya sendiri.

       Lowongan yang masih bertanda aktif tetapi tanggalnya sudah lewat
       BERHENTI tampil di situs — tanpa satu pun tanda di layar ini sebelum
       sekarang. Yang mengiranya masih menarik pelamar menunggu lamaran yang
       tidak akan datang. */
    .lw-awas { color: #b45309; font-weight: 700; }
    .lw-samar { color: var(--dsb-redup); }
    </style>

    @php
        // Dirakit sekali di sini: kondisi auth()->user()->… yang berulang di
        // direktif membuat penanda morph Livewire timpang (PenandaMorphAdminTest).
        $bisaTambah = auth()->user()->hasPermission('create_lowongan');
        $bisaUbah = auth()->user()->hasPermission('edit_lowongan');
        $bisaHapus = auth()->user()->hasPermission('delete_lowongan');
        $bisaAksi = $bisaUbah || $bisaHapus;
        $adaSaringan = $this->adaSaringan();
        $warnaTab = $tabs[$tabAktif][2] ?? '#7c3aed';
        $targetMuat = 'search,filterUnit,setTab,bersihkanSaringan,gotoPage,previousPage,nextPage';
    @endphp

    <div class="dsb lw-daftar">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Lowongan Pekerjaan</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="d-block">
                        Posisi yang sedang dibuka di tiga perusahaan — inilah yang dilihat orang di situs.
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                @if ($bisaTambah)
                    <a wire:navigate href="{{ route('admin.lowongan.create') }}" class="dsb-tombol is-utama">
                        <i class="bi bi-plus-lg"></i><span>Buka Lowongan Baru</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ================== TAB ==================

             Dua kelompok, dan pemisahannya disengaja: yang atas cara MELIHAT
             daftar, yang bawah PEKERJAAN YANG MENUNGGU. Digabung, "Lewat
             batas lamaran" tenggelam jadi salah satu pilihan menonton —
             padahal ia satu-satunya yang diam-diam berhenti menarik
             pelamar. --}}
        <div class="dsb-rak">
            <div class="k-12">
                <div class="pt-grup-deret">
                    @foreach ($grupTab as [$judulGrup, $ketGrup, $kunciGrup])
                        <nav class="pt-grup" aria-label="{{ $judulGrup }}">
                            <div class="pt-grup-kepala">
                                <span class="pt-grup-judul">{{ $judulGrup }}</span>
                                <span class="pt-grup-ket">{{ $ketGrup }}</span>
                            </div>
                            <div class="pt-kartu-deret" style="--n: {{ count($kunciGrup) }}">
                                @foreach ($kunciGrup as $kunci)
                                    @php
                                        [$label, $ikon, $warna] = $tabs[$kunci];
                                        $angka = $jumlahTab[$kunci] ?? 0;
                                        $iniAktif = $tabAktif === $kunci;
                                    @endphp
                                    <button type="button" wire:click="setTab('{{ $kunci }}')" style="--c: {{ $warna }}"
                                        class="pt-kartu-tab {{ $iniAktif ? 'is-aktif' : '' }} {{ $angka === 0 ? 'is-nol' : '' }}"
                                        aria-pressed="{{ $iniAktif ? 'true' : 'false' }}">
                                        <span class="pt-kartu-ikon"><i class="bi {{ $ikon }}"></i></span>
                                        <span class="pt-kartu-teks">
                                            <span class="pt-kartu-angka">{{ number_format($angka, 0, ',', '.') }}</span>
                                            <span class="pt-kartu-label">{{ $label }}</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </nav>
                    @endforeach
                </div>
            </div>

            {{-- ================== SARINGAN ================== --}}
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-isi">
                    <div class="lw-saring">
                        <div class="dsb-medan">
                            <label class="dsb-label" for="lw-cari">Cari lowongan</label>
                            <div class="dsb-cari">
                                <i class="bi bi-search"></i>
                                <input id="lw-cari" type="search" class="dsb-isian"
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="Nama posisi, kota, atau ringkasannya">
                            </div>
                        </div>

                        <div class="dsb-medan">
                            <label class="dsb-label" for="lw-unit">Perusahaan</label>
                            <select id="lw-unit" wire:model.live="filterUnit" class="dsb-isian">
                                <option value="" @selected((string) $this->filterUnit === '')>Semua perusahaan</option>
                                @foreach ($pilihanUnit as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected((string) $this->filterUnit === (string) $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="dsb-medan lw-medan-aksi">
                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-lembut">
                                    <i class="bi bi-arrow-counterclockwise"></i><span>Tampilkan semua lagi</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== DAFTARNYA ================== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat" wire:target="{{ $targetMuat }}">
                <div class="pt-daftar-kepala" style="--c: {{ $warnaTab }}">
                    <div class="pt-daftar-judul">
                        <b>{{ $tabs[$tabAktif][0] ?? 'Semua lowongan' }}</b>
                        <span>
                            @if ($dataLowongan->total() > 0)
                                {{ number_format($dataLowongan->total(), 0, ',', '.') }} lowongan
                                @if ($tabAktif === 'lewat_batas')
                                    — masih bertanda aktif, tetapi sudah tidak tampil di situs
                                @endif
                            @else
                                Belum ada isinya
                            @endif
                        </span>
                    </div>
                </div>

                @if ($dataLowongan->isEmpty())
                    <div class="dsb-kartu-isi">
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon">
                                <i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-briefcase' }}"></i>
                            </span>
                            <p class="dsb-kosong-judul">
                                {{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada lowongan' }}
                            </p>
                            <p class="dsb-kosong-ket">
                                {{ $adaSaringan
                                    ? 'Coba ganti kata pencarian, pilih perusahaan lain, atau tekan Semua lowongan.'
                                    : 'Lowongan yang dibuka di sini langsung tampil di halaman karier situs.' }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="dsb-tabel-bungkus">
                        <table class="dsb-tabel lw-tabel">
                            <thead>
                                <tr>
                                    <th>Posisi</th>
                                    <th>Pelamar</th>
                                    <th>Batas lamaran</th>
                                    <th>Keadaan</th>
                                    @if ($bisaAksi)
                                        <th class="text-end">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dataLowongan as $lowongan)
                                    @php
                                        /*
                                         | Dirakit di PHP, bukan lewat direktif bersarang: direktif
                                         | di dalam direktif membuat penanda morph Livewire timpang
                                         | (PenandaMorphAdminTest).
                                         */
                                        $lewatBatas = $lowongan->lewat_batas;
                                        $aktif = $lowongan->is_active === 'active';
                                        $batas = $lowongan->batas_lamaran;
                                        $sisaHari = $batas ? now()->startOfDay()->diffInDays($batas, false) : null;
                                        $jumlahPelamar = (int) ($lowongan->applications_count ?? 0);
                                        $belumDisentuh = (int) ($lowongan->applications_baru_count ?? 0);
                                        $urlBagikan = route('situs.lowongan', $lowongan->slug);
                                        $urlUbah = $bisaUbah ? route('admin.lowongan.edit', $lowongan->id) : null;
                                    @endphp
                                    <tr wire:key="lowongan-{{ $lowongan->id }}">
                                        {{-- ===== Posisi ===== --}}
                                        <td data-judul="Posisi">
                                            @if ($urlUbah)
                                                <a wire:navigate href="{{ $urlUbah }}" class="lw-tautan">
                                                    <span class="dsb-tabel-teks">
                                                        <span class="dsb-tabel-judul text-capitalize">{{ $lowongan->title }}</span>
                                                        <span class="dsb-tabel-meta">
                                                            <span><i class="bi bi-building"></i>{{ $lowongan->unit_label }}</span>
                                                        </span>
                                                    </span>
                                                </a>
                                            @else
                                                <span class="dsb-tabel-teks">
                                                    <span class="dsb-tabel-judul text-capitalize">{{ $lowongan->title }}</span>
                                                    <span class="dsb-tabel-meta">
                                                        <span><i class="bi bi-building"></i>{{ $lowongan->unit_label }}</span>
                                                    </span>
                                                </span>
                                            @endif

                                            <span class="lw-cip-deret">
                                                @if ($lowongan->jenis_label)
                                                    <span class="dsb-lencana is-abu">{{ $lowongan->jenis_label }}</span>
                                                @endif
                                                @if ($lowongan->pola_kerja_label)
                                                    <span class="dsb-lencana is-abu">{{ $lowongan->pola_kerja_label }}</span>
                                                @endif
                                                @if ($lowongan->lokasi)
                                                    <span class="dsb-lencana is-abu">
                                                        <i class="bi bi-geo-alt"></i>{{ $lowongan->lokasi }}
                                                    </span>
                                                @endif
                                            </span>
                                        </td>

                                        {{-- ===== Pelamar =====
                                             Pertanyaan pertama yang dibawa orang saat membuka layar
                                             ini, dan dulu tidak terjawab di sini sama sekali. --}}
                                        <td data-judul="Pelamar">
                                            @if ($jumlahPelamar > 0)
                                                {{-- Tautan, bukan sekadar angka: yang membaca "7 orang"
                                                     berikutnya mau membuka ketujuhnya. --}}
                                                <a wire:navigate href="{{ route('admin.pelamar.index', ['lowongan' => $lowongan->id]) }}"
                                                    class="dsb-lencana is-biru lw-lencana-tautan"
                                                    title="Buka pelamar posisi ini">
                                                    <i class="bi bi-people-fill"></i>{{ number_format($jumlahPelamar, 0, ',', '.') }} orang
                                                </a>
                                                @if ($belumDisentuh > 0)
                                                    {{-- "7 pelamar" bisa berarti tujuh berkas menumpuk
                                                         belum dibaca, atau tujuh yang sudah tuntas
                                                         diproses — dua keadaan yang menuntut hal
                                                         berbeda, dan satu angka tidak bisa
                                                         membedakannya. --}}
                                                    <span class="dsb-tabel-samar d-block lw-awas">
                                                        {{ $belumDisentuh }} belum dibuka
                                                    </span>
                                                @endif
                                            @else
                                                <span class="lw-samar">Belum ada</span>
                                            @endif
                                        </td>

                                        {{-- ===== Batas lamaran ===== --}}
                                        <td data-judul="Batas lamaran">
                                            @if (! $batas)
                                                <span class="lw-samar">Tanpa batas</span>
                                            @elseif ($lewatBatas)
                                                <span class="lw-awas">
                                                    {{ $batas->locale('id')->translatedFormat('j M Y') }}
                                                </span>
                                                <span class="dsb-tabel-samar d-block">sudah lewat</span>
                                            @else
                                                <span>{{ $batas->locale('id')->translatedFormat('j M Y') }}</span>
                                                @if ($aktif && $sisaHari !== null && $sisaHari <= 7)
                                                    <span class="dsb-tabel-samar d-block">
                                                        {{ $sisaHari === 0 ? 'hari terakhir' : 'tinggal '.$sisaHari.' hari' }}
                                                    </span>
                                                @endif
                                            @endif
                                        </td>

                                        {{-- ===== Keadaan =====
                                             Tiga keadaan, bukan dua. "Lewat batas" adalah lowongan
                                             yang masih bertanda aktif tetapi sudah berhenti tampil
                                             di situs — dan tanpa keadaan ketiga ini, ia terbaca
                                             sama persis dengan yang benar-benar sedang dibuka. --}}
                                        <td data-judul="Keadaan">
                                            @if ($lewatBatas)
                                                <span class="dsb-lencana is-kuning"
                                                    title="Masih bertanda aktif, tetapi batas lamarannya sudah lewat — tidak tampil di situs.">
                                                    <i class="bi bi-hourglass-bottom"></i>Lewat batas
                                                </span>
                                            @elseif ($aktif)
                                                <span class="dsb-lencana is-hijau">
                                                    <i class="bi bi-check-circle-fill"></i>Sedang dibuka
                                                </span>
                                            @else
                                                <span class="dsb-lencana is-abu">
                                                    <i class="bi bi-slash-circle"></i>Ditutup
                                                </span>
                                            @endif
                                        </td>

                                        @if ($bisaAksi)
                                            <td data-judul="Aksi" class="text-end">
                                                <span class="lw-aksi">
                                                    {{-- Lihat seperti yang dilihat pelamar.

                                                         Rutenya sudah lama ada (meta OG lalu dialihkan ke
                                                         situs), tetapi tidak ada satu layar admin pun yang
                                                         menautkannya — jadi untuk menyebarkan lowongan ke
                                                         WhatsApp, tautannya harus dirakit sendiri. --}}
                                                    @if ($aktif && ! $lewatBatas)
                                                        <a href="{{ $urlBagikan }}" target="_blank" rel="noopener"
                                                            class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                                                            title="Lihat & bagikan — ini yang dibuka pelamar">
                                                            <i class="bi bi-box-arrow-up-right"></i>
                                                        </a>
                                                    @endif

                                                    {{-- Tutup sekali tekan, hanya untuk yang memang sudah
                                                         lewat batas: ia sudah berhenti tampil di situs, dan
                                                         membuka layar ubah hanya untuk satu kotak pilih
                                                         adalah lima langkah untuk keputusan yang sudah
                                                         jelas — lalu dibiarkan menumpuk karena repot. --}}
                                                    @if ($bisaUbah && $lewatBatas)
                                                        <button type="button" class="dsb-tombol is-lembut tutup-lowongan-btn"
                                                            style="--ikon: #64748b"
                                                            data-id="{{ $lowongan->id }}" data-name="{{ $lowongan->title }}"
                                                            title="Tutup lowongan ini">
                                                            <i class="bi bi-slash-circle"></i>
                                                        </button>
                                                    @endif

                                                    @if ($bisaTambah)
                                                        <button type="button" wire:click="gandakan({{ $lowongan->id }})"
                                                            class="dsb-tombol is-lembut" style="--ikon: #7c3aed"
                                                            title="Salin jadi lowongan baru (belum tayang)">
                                                            <i class="bi bi-copy"></i>
                                                        </button>
                                                    @endif

                                                    @if ($bisaUbah)
                                                        <a href="{{ route('admin.lowongan.edit', $lowongan->id) }}" wire:navigate
                                                            class="dsb-tombol is-lembut" style="--ikon: #d97706" title="Ubah lowongan ini">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                    @endif
                                                    @if ($bisaHapus)
                                                        <button type="button" class="dsb-tombol is-bahaya delete-lowongan-btn"
                                                            data-id="{{ $lowongan->id }}" data-name="{{ $lowongan->title }}"
                                                            title="Hapus lowongan ini">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($dataLowongan->hasPages())
                        <div class="dsb-kartu-isi">
                            {{ $dataLowongan->links('vendor.pagination') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== SWEET ALERT DELETE (GLOSSY, SEPERTI BANNERS) ==================-->
<script>
    const glossyConfigLowongan = {
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
            /* Menutup lowongan BUKAN menghapusnya: ia bisa dibuka lagi kapan
               saja. Dialognya karena itu bertanya biasa, bukan memperingatkan
               seperti dialog hapus — dialog yang sama persis membuat keduanya
               terasa sama berat, dan yang berat cenderung tidak ditekan. */
            const tutup = event.target.closest('.tutup-lowongan-btn');

            if (tutup) {
                event.preventDefault();
                const idTutup = tutup.getAttribute('data-id');
                const namaTutup = tutup.getAttribute('data-name');

                Swal.fire({
                    title: 'Tutup lowongan ini?',
                    text: '"' + (namaTutup || '') + '" tidak akan tampil lagi di situs. Bisa dibuka kembali lewat tombol Ubah.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, tutup',
                    cancelButtonText: 'Batal',
                    ...glossyConfigLowongan
                }).then((result) => {
                    if (result.isConfirmed) {
                        const komponen = tutup.closest('[wire\\:id]');
                        if (komponen) {
                            Livewire.find(komponen.getAttribute('wire:id')).call('tutup', idTutup);
                        }
                    }
                });

                return;
            }

            const button = event.target.closest('.delete-lowongan-btn');

            if (button) {
                event.preventDefault();
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');

                Swal.fire({
                    title: 'Yakin hapus lowongan?',
                    text: 'Lowongan "' + (name || '') + '" akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigLowongan
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
