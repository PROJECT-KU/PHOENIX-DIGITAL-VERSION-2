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
    .plm-nomor { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .74rem; }
    .plm-aksi { display: inline-flex; gap: 6px; justify-content: flex-end; }
    .plm-samar { color: var(--dsb-redup); }

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
        $targetMuat = 'search,filterMonth,filterJob,filterStatus,setTahap,resetFilters,gotoPage,previousPage,nextPage';
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
                                @if ($tahapAktif === 'baru')
                                    — belum ada yang membuka berkasnya
                                @endif
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
                    <div class="dsb-tabel-bungkus">
                        <table class="dsb-tabel plm-tabel">
                            <thead>
                                <tr>
                                    <th>Pelamar</th>
                                    <th>Posisi</th>
                                    <th>Tahap</th>
                                    <th>Melamar</th>
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
                                        $adaBerkas = (bool) ($item->cv_path || $item->cover_letter_path || $item->lampiran_path);
                                    @endphp
                                    <tr wire:key="pelamar-{{ $item->id }}">
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
                                                    <span class="dsb-tabel-samar plm-nomor">{{ $item->nomor_lamaran }}</span>
                                                </span>
                                            </a>
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
                                                {{-- Berkasnya sudah dihapus sesuai masa simpan. Disebut
                                                     di sini supaya admin tidak mengira sistemnya rusak
                                                     saat tombol unduh di halaman detail tidak ada. --}}
                                                <span class="dsb-tabel-samar d-block">berkas sudah dihapus</span>
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
                                                @if ($adaBerkas)
                                                    <button type="button" wire:click="bukaPratinjau({{ $item->id }})"
                                                        class="dsb-tombol is-lembut" style="--ikon: #dc2626"
                                                        title="Baca berkasnya di sini">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                    </button>
                                                @endif

                                                <a wire:navigate href="{{ $urlDetail }}"
                                                    class="dsb-tombol is-lembut" style="--ikon: #0284c7" title="Buka lamaran ini">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if ($bisaHapus)
                                                    <button type="button" class="dsb-tombol is-bahaya delete-pelamar-btn"
                                                        data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                                        title="Hapus lamaran ini">
                                                        <i class="bi bi-trash"></i>
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
         sendiri. Lapisan buatan sendiri tadinya ber-z-index 1050/1051 —
         di bawah bilah samping, jadi jendelanya tertindih dan separuh
         kirinya hilang. Kerangka bersama sudah memakai 1055/1056 dan
         sudah terbukti di empat jendela layar Task. --}}
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
