@section('title')
Arsip Surat || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.surat.partials.surat-gaya')

    @php
        /*
         | Semua perbandingan dirakit di sini.
         |
         | Tanda "lebih besar" di dalam kondisi direktif blok membuat Livewire
         | melewatkan penanda morph-nya, dan halamannya jadi timpang tanpa
         | satu pun galat (PenandaMorphAdminTest menjaganya).
         */
        $tabs = $this::TAB;
        $urutan = $this::URUTAN;
        $pilihanBaris = $this::PILIHAN_BARIS;
        [$labelTab, $ikonTab, $warnaTab, $ketTab] = $tabs[$tab] ?? $tabs['semua'];
        $adaSaringan = $this->adaSaringan();
        $kosong = $surat->isEmpty();
        $targetMuat = 'cari,klasifikasi,sifat,status,tglDari,tglSampai,urut,perHalaman,pilihTab,kosongkanSaringan,gotoPage,nextPage,previousPage';

        $warnaSifat = ['biasa' => '#64748b', 'segera' => '#d97706', 'sangat_segera' => '#e11d48', 'rahasia' => '#7c3aed'];
        $warnaStatus = ['baru' => '#d97706', 'diproses' => '#0284c7', 'selesai' => '#16a34a', 'diarsipkan' => '#64748b'];
        $kelasSifat = ['segera' => 'is-segera', 'sangat_segera' => 'is-sangat-segera', 'rahasia' => 'is-rahasia'];
        $ikonArah = ['masuk' => 'bi-envelope-arrow-down', 'keluar' => 'bi-envelope-arrow-up'];
        $warnaArah = ['masuk' => '#0ea5c6', 'keluar' => '#17a974'];
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero sr-hero-daftar">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Arsip Surat</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Buku agenda surat masuk & keluar beserta klasifikasi, disposisi, dan tempat berkas fisiknya.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.surat.dashboard') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                    <i class="bi bi-grid-fill"></i><span>Dashboard</span>
                </a>
                @if ($bolehCatat)
                    <a href="{{ route('admin.surat.create', ['arah' => 'masuk']) }}" wire:navigate class="dsb-tombol is-utama">
                        <i class="bi bi-envelope-arrow-down"></i><span>Surat Masuk</span>
                    </a>
                    <a href="{{ route('admin.surat.create', ['arah' => 'keluar']) }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #17a974">
                        <i class="bi bi-envelope-arrow-up"></i><span>Surat Keluar</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ================== TAB & SARINGAN ================== --}}
        {{-- Tanpa judul besar: datanya harus sudah terlihat di layar pertama. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="k-12">
                    <nav class="sr-tab-deret" aria-label="Kelompok surat">
                        @foreach ($tabs as $kunci => [$label, $ikon, $warna, $ket])
                            @php
                                $jumlah = (int) ($jumlahTab[$kunci] ?? 0);
                                $aktif = $tab === $kunci;
                                $nol = $jumlah === 0;
                            @endphp
                            <button type="button" wire:click="pilihTab('{{ $kunci }}')" style="--c: {{ $warna }}"
                                class="sr-tab {{ $aktif ? 'is-aktif' : '' }} {{ $nol ? 'is-nol' : '' }}"
                                title="{{ $ket }}" aria-pressed="{{ $aktif ? 'true' : 'false' }}">
                                <span class="sr-tab-ikon"><i class="bi {{ $ikon }}"></i></span>
                                <span class="sr-tab-teks">
                                    <span class="sr-tab-angka">{{ number_format($jumlah, 0, ',', '.') }}</span>
                                    <span class="sr-tab-label">{{ $label }}</span>
                                </span>
                            </button>
                        @endforeach
                    </nav>
                </div>

                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="sr-saring">
                            <div class="dsb-medan sr-medan-cari">
                                <label class="dsb-label" for="sr-cari">Cari</label>
                                <div class="dsb-cari">
                                    <i class="bi bi-search"></i>
                                    <input id="sr-cari" type="search" class="dsb-isian" wire:model.live.debounce.400ms="cari"
                                        placeholder="Nomor agenda, nomor surat, perihal, pengirim, atau lemari">
                                </div>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="sr-klasifikasi">Klasifikasi</label>
                                <select id="sr-klasifikasi" class="dsb-isian" wire:model.live="klasifikasi">
                                    <option value="">Semua klasifikasi</option>
                                    @foreach ($daftarKlasifikasi as $k)
                                        <option value="{{ $k->id }}" @selected((string) $this->klasifikasi === (string) $k->id)>{{ $k->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="sr-sifat">Sifat</label>
                                <select id="sr-sifat" class="dsb-isian" wire:model.live="sifat">
                                    <option value="">Semua sifat</option>
                                    @foreach (\App\Models\Surat\Surat::SIFAT as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected($this->sifat === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="sr-status">Status</label>
                                <select id="sr-status" class="dsb-isian" wire:model.live="status">
                                    <option value="">Semua status</option>
                                    @foreach (\App\Models\Surat\Surat::STATUS as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected($this->status === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="sr-dari">Tanggal surat dari</label>
                                <input id="sr-dari" type="date" class="dsb-isian" wire:model.live="tglDari">
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="sr-sampai">Sampai</label>
                                <input id="sr-sampai" type="date" class="dsb-isian" wire:model.live="tglSampai">
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="sr-urut">Urutkan</label>
                                <select id="sr-urut" class="dsb-isian" wire:model.live="urut">
                                    @foreach ($urutan as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected($this->urut === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="sr-baris">Baris</label>
                                <select id="sr-baris" class="dsb-isian" wire:model.live="perHalaman">
                                    @foreach ($pilihanBaris as $n)
                                        <option value="{{ $n }}" @selected((int) $this->perHalaman === $n)>{{ $n }} baris</option>
                                    @endforeach
                                </select>
                            </div>

                            @if ($adaSaringan)
                                <div class="dsb-medan">
                                    <label class="dsb-label">&nbsp;</label>
                                    <button type="button" wire:click="kosongkanSaringan" class="dsb-tombol is-lembut" style="--ikon: #e11d48">
                                        <i class="bi bi-x-circle"></i><span>Kosongkan</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="k-12" wire:loading.class="dsb-sedang-muat" wire:target="{{ $targetMuat }}">
                    <div class="dsb-kartu">
                        <div class="sr-daftar-kepala" style="--c: {{ $warnaTab }}">
                            <span class="dsb-ikon is-kecil"><i class="bi {{ $ikonTab }}"></i></span>
                            <div class="sr-daftar-judul">
                                <b>{{ $labelTab }}</b>
                                <span>{{ $ketTab }} · {{ $urutan[$urut] ?? '' }}</span>
                            </div>
                            <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="{{ $targetMuat }}">
                                <span class="dsb-putar is-kecil"></span>Memuat…
                            </span>
                        </div>

                        @if ($kosong)
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-inbox' }}"></i></span>
                                <p class="dsb-kosong-judul">
                                    {{ $adaSaringan ? 'Tidak ada surat yang cocok' : 'Belum ada surat di kelompok ini' }}
                                </p>
                                <p class="dsb-kosong-ket">
                                    {{ $adaSaringan
                                        ? 'Coba ubah kata pencarian, klasifikasi, atau periode tanggalnya.'
                                        : 'Surat yang masuk kelompok ini akan muncul di sini setelah dicatat.' }}
                                </p>
                                @if ($adaSaringan)
                                    <button type="button" wire:click="kosongkanSaringan" class="dsb-tombol is-utama" style="margin-top: 14px;">
                                        <i class="bi bi-x-circle"></i><span>Kosongkan saringan</span>
                                    </button>
                                @elseif ($bolehCatat)
                                    <a href="{{ route('admin.surat.create', ['arah' => 'masuk']) }}" wire:navigate class="dsb-tombol is-utama" style="margin-top: 14px;">
                                        <i class="bi bi-plus-lg"></i><span>Catat surat</span>
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="dsb-tabel-bungkus">
                                <table class="dsb-tabel sr-tabel">
                                    <thead>
                                        <tr>
                                            <th>Nomor Agenda</th>
                                            {{-- Klasifikasi TIDAK berkolom sendiri.
                                                 Delapan kolom membuat "Perihal" — satu-satunya
                                                 kolom berisi kalimat — terlipat empat baris
                                                 sementara kolom lencana menyisakan ruang. Kodenya
                                                 tetap terbaca sebagai keterangan di bawah perihal,
                                                 dan penyaringannya tetap ada di bilah saringan. --}}
                                            <th class="k-lebar sr-kol-perihal">Perihal</th>
                                            <th class="k-sedang sr-kol-pihak">Dari / Kepada</th>
                                            <th>Sifat</th>
                                            <th>Status</th>
                                            <th class="k-sedang sr-kol-tanggal">Tanggal Surat</th>
                                            <th class="sr-kol-aksi" style="text-align: right;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($surat as $s)
                                            @php
                                                $url = route('admin.surat.detail', $s);
                                                $adaNomor = filled($s->nomor_surat);
                                                $adaLemari = filled($s->lokasi_fisik);
                                            @endphp
                                            <tr class="{{ $kelasSifat[$s->sifat] ?? '' }}">
                                                <td>
                                                    <a wire:navigate href="{{ $url }}" class="dsb-tabel-utama">
                                                        <span class="dsb-ikon is-kecil" style="--c: {{ $warnaArah[$s->arah] ?? '#64748b' }}">
                                                            <i class="bi {{ $ikonArah[$s->arah] ?? 'bi-envelope' }}"></i>
                                                        </span>
                                                        <span class="dsb-tabel-teks">
                                                            <span class="dsb-tabel-judul sr-agenda">{{ $s->nomor_agenda }}</span>
                                                            <span class="dsb-tabel-meta">{{ $s->arah_label }}</span>
                                                        </span>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="dsb-tabel-teks">
                                                        <span class="dsb-tabel-judul">{{ $s->perihal }}</span>
                                                        <span class="dsb-tabel-meta">
                                                            {{ $adaNomor ? 'No. '.$s->nomor_surat : 'Tanpa nomor surat' }}
                                                        </span>
                                                        <span class="dsb-tabel-meta">
                                                            @if ($s->klasifikasi)
                                                                <span class="sr-agenda">{{ $s->klasifikasi->kode }}</span> · {{ $s->klasifikasi->nama }}
                                                            @else
                                                                Belum diklasifikasi
                                                            @endif
                                                        </span>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="sr-pihak">
                                                        <span class="dsb-tabel-judul">{{ $s->pihak }}</span>
                                                        <span class="dsb-tabel-meta">{{ $s->pihak_label }}</span>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="dsb-lencana" style="--c: {{ $warnaSifat[$s->sifat] ?? '#64748b' }}">{{ $s->sifat_label }}</span>
                                                </td>
                                                <td>
                                                    <span class="dsb-lencana" style="--c: {{ $warnaStatus[$s->status] ?? '#64748b' }}">{{ $s->status_label }}</span>
                                                </td>
                                                <td>
                                                    <span class="dsb-tabel-teks">
                                                        <span class="dsb-tabel-judul">{{ $s->tanggal_surat_teks }}</span>
                                                        <span class="dsb-tabel-meta">
                                                            {{ $adaLemari ? $s->lokasi_fisik : 'Tempat berkas belum dicatat' }}
                                                        </span>
                                                    </span>
                                                </td>
                                                <td class="dsb-tabel-aksi">
                                                    <a wire:navigate href="{{ $url }}" class="dsb-tabel-btn" title="Buka surat">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if ($surat->hasPages())
                                <div class="sr-halaman">{{ $surat->links('vendor.pagination') }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
