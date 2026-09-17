@section('title')
Data Pesanan RSC || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*) supaya layar ini terbaca
         sebagai bagian dari aplikasi yang sama. Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.pemesanan-r-s-c.partials.rsc-gaya')

    @php
        // Izin dihitung sekali di sini: dua kondisi izin berkurung bersarang
        // dalam satu berkas membuat Livewire salah memasang penanda morph.
        $bolehBuat = auth()->user()->hasPermission('create_pesananrsc');
        $bolehEdit = auth()->user()->hasPermission('edit_pesananrsc');
        $bolehHapus = auth()->user()->hasPermission('delete_pesananrsc');
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        // Warna tiap status. Sama persis di daftar, detail, dan formulir —
        // status yang berganti warna antar-layar terbaca sebagai status lain.
        $lencanaStatus = ['baru' => 'is-hijau', 'perpanjang' => 'is-biru', 'pengganti' => 'is-kuning', 'habis' => 'is-luring'];
        $adaSaringan = $search || $filterMonth || $filterYear || $statusFilter || $akunFilter || $picFilter || $masaFilter;
        $batasSegera = \App\Livewire\Pages\Admin\PemesananRSC\PemesananrscList::BATAS_SEGERA;
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Pemesanan RSC</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Batch camp, peserta, dan akun yang dipakai tiap batch.</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <button wire:click="openExportModal" type="button" class="dsb-tombol is-lembut">
                    <i class="bi bi-download"></i><span>Unduh</span>
                </button>
                @if ($bolehBuat)
                    <a wire:navigate href="{{ route('admin.pesananrsc.create') }}" class="dsb-tombol is-utama">
                        <i class="bi bi-plus-lg"></i><span>Tambah Batch</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ================== RINGKASAN ==================
             Empat angka yang sebelumnya tidak ada di mana pun: layar ini hanya
             menampilkan tabel, jadi "berapa peserta bulan ini" harus dijumlah
             sendiri dari kolomnya. Semua mengikuti saringan yang aktif. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #7c3aed">
                    <span class="dsb-kepala-ikon"><i class="bi bi-mortarboard-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Ringkasan</span>
                        <h2 class="dsb-judul">Keadaan Batch</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-collection"></i>{{ $ringkas['batch'] }} batch</span>
                            <span class="dsb-chip is-samar">{{ $adaSaringan ? 'Mengikuti saringan di bawah' : 'Seluruh data' }}</span>
                        </div>
                    </div>
                </div>

                <article class="dsb-stat is-utama k-6" style="--c: #16a34a">
                    <span class="dsb-ikon"><i class="bi bi-cash-stack"></i></span>
                    <p class="dsb-stat-label">Nilai Pemesanan</p>
                    <p class="dsb-stat-nilai">{{ $rupiah($ringkas['nilai']) }}</p>
                    <p class="dsb-stat-ket"><i class="bi bi-info-circle"></i><span>Dicatat sekali saat batch Baru — sama dengan Cash Flow</span></p>
                </article>

                <article class="dsb-stat is-utama k-6" style="--c: #7c3aed">
                    <span class="dsb-ikon"><i class="bi bi-people-fill"></i></span>
                    <p class="dsb-stat-label">Total Peserta</p>
                    <p class="dsb-stat-nilai">{{ number_format($ringkas['peserta'], 0, ',', '.') }}<span class="dsb-stat-satuan">orang</span></p>
                    <p class="dsb-stat-ket"><i class="bi bi-collection"></i><span>Tersebar di {{ $ringkas['batch'] }} batch</span></p>
                </article>

                <article class="dsb-stat k-4" style="--c: #0284c7">
                    <span class="dsb-ikon"><i class="bi bi-collection-fill"></i></span>
                    <p class="dsb-stat-label">Jumlah Batch</p>
                    <p class="dsb-stat-nilai">{{ $ringkas['batch'] }}<span class="dsb-stat-satuan">batch</span></p>
                    <p class="dsb-stat-ket"><i class="bi bi-list-ul"></i><span>{{ $pemesananrsc->count() }} tampil di halaman ini</span></p>
                </article>

                <article class="dsb-stat k-4" style="--c: {{ $ringkas['berjalan'] > 0 ? '#d97706' : '#64748b' }}">
                    <span class="dsb-ikon"><i class="bi bi-broadcast"></i></span>
                    <p class="dsb-stat-label">Camp Berjalan</p>
                    <p class="dsb-stat-nilai">{{ $ringkas['berjalan'] }}<span class="dsb-stat-satuan">batch</span></p>
                    <p class="dsb-stat-ket"><i class="bi bi-calendar-check"></i><span>{{ $ringkas['berjalan'] > 0 ? 'Sedang berlangsung hari ini' : 'Tidak ada camp hari ini' }}</span></p>
                </article>

                {{-- Kartu ini bisa diklik: menyaring daftar ke batch yang akunnya
                     segera berakhir. Status TIDAK diubah otomatis — buku kas hanya
                     mencatat status "baru", jadi mengubahnya ke "habis" akan
                     menghapus pemasukan batch itu dari Cash Flow. --}}
                <button type="button" wire:click="saringMasa('segera')"
                    class="dsb-stat k-4 rsc-stat-tombol {{ $masaFilter === 'segera' ? 'is-dipilih' : '' }}"
                    style="--c: {{ $ringkas['segera'] > 0 ? '#e11d48' : '#64748b' }}"
                    aria-pressed="{{ $masaFilter === 'segera' ? 'true' : 'false' }}">
                    <span class="dsb-ikon"><i class="bi bi-hourglass-bottom"></i></span>
                    <span class="dsb-stat-label">Akun Segera Berakhir</span>
                    <span class="dsb-stat-nilai">{{ $ringkas['segera'] }}<span class="dsb-stat-satuan">batch</span></span>
                    <span class="dsb-stat-ket">
                        <i class="bi {{ $masaFilter === 'segera' ? 'bi-funnel-fill' : 'bi-calendar-x' }}"></i>
                        <span>
                            @if ($masaFilter === 'segera')
                                Sedang disaring — klik lagi untuk melepas
                            @else
                                Dalam {{ $batasSegera }} hari{{ $ringkas['lewat'] ? ' · '.$ringkas['lewat'].' sudah lewat' : '' }}
                            @endif
                        </span>
                    </span>
                </button>
            </div>
        </section>

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #0284c7">
                    <span class="dsb-kepala-ikon"><i class="bi bi-funnel-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Tampilan</span>
                        <h2 class="dsb-judul">Cari &amp; Saring</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip is-memuat" wire:loading.inline-flex
                                wire:target="search,filterMonth,filterYear,statusFilter,akunFilter,picFilter,masaFilter,saringMasa,urutkan,resetFilters,gotoPage,nextPage,previousPage">
                                <span class="dsb-putar is-kecil"></span>Menyaring…
                            </span>
                            <span class="dsb-chip is-samar">Pencarian mencakup camp, batch, peserta, no. telp, akun, PIC, dan tanggal</span>
                        </div>
                    </div>
                </div>

                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="rsc-saring">
                            <div class="dsb-medan rsc-medan-cari">
                                <label class="dsb-label" for="rsc-cari">Cari</label>
                                <div class="dsb-cari">
                                    <i class="bi bi-search"></i>
                                    <input id="rsc-cari" type="search" class="dsb-isian"
                                        wire:model.live.debounce.300ms="search" placeholder="Camp, peserta, no. telp, akun…">
                                    @if ($search)
                                        <button type="button" class="dsb-cari-hapus" wire:click="$set('search', '')" title="Hapus pencarian">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="rsc-status">Status</label>
                                <select id="rsc-status" class="dsb-isian" wire:model.live="statusFilter">
                                    <option value="">Semua status</option>
                                    <option value="baru">Baru</option>
                                    {{-- Perpanjangan kini lewat Pemesanan Toko; opsi hanya
                                         tampil selama masih ada batch lama berstatus itu. --}}
                                    @if ($adaPerpanjang)
                                        <option value="perpanjang">Perpanjang</option>
                                    @endif
                                    <option value="pengganti">Pengganti</option>
                                    <option value="habis">Habis</option>
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="rsc-bulan">Bulan mulai camp</label>
                                <select id="rsc-bulan" class="dsb-isian" wire:model.live="filterMonth">
                                    <option value="">Semua bulan</option>
                                    @foreach ($months as $month)
                                        <option value="{{ $month['value'] }}">{{ ucfirst($month['label']) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="rsc-tahun">Tahun</label>
                                <select id="rsc-tahun" class="dsb-isian" wire:model.live="filterYear">
                                    <option value="">Semua tahun</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="rsc-akun">Akun utama</label>
                                <select id="rsc-akun" class="dsb-isian" wire:model.live="akunFilter">
                                    <option value="">Semua akun</option>
                                    @foreach ($pilihanAkun as $pa)
                                        <option value="{{ $pa->id }}">{{ $pa->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="rsc-pic">PIC</label>
                                <select id="rsc-pic" class="dsb-isian" wire:model.live="picFilter">
                                    <option value="">Semua PIC</option>
                                    @foreach ($pilihanPic as $pp)
                                        <option value="{{ $pp->id }}">{{ $pp->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="rsc-masa">Masa akun</label>
                                <select id="rsc-masa" class="dsb-isian" wire:model.live="masaFilter">
                                    <option value="">Semua masa</option>
                                    <option value="segera">Berakhir ≤ {{ $batasSegera }} hari</option>
                                    <option value="lewat">Sudah lewat</option>
                                </select>
                            </div>
                        </div>

                        @if ($adaSaringan)
                            <div class="rsc-saring-kaki">
                                <span class="dsb-kartu-sub"><i class="bi bi-funnel"></i> {{ $ringkas['batch'] }} batch cocok dengan saringan</span>
                                <button type="button" wire:click="resetFilters" class="dsb-tombol is-lembut">
                                    <i class="bi bi-x-circle"></i><span>Kosongkan saringan</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian" wire:loading.class="dsb-sedang-muat"
            wire:target="search,filterMonth,filterYear,statusFilter,akunFilter,picFilter,masaFilter,saringMasa,urutkan,resetFilters,gotoPage,nextPage,previousPage">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #16a34a">
                    <span class="dsb-kepala-ikon"><i class="bi bi-list-task"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Daftar</span>
                        <h2 class="dsb-judul">Batch Camp</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-sort-down"></i>{{ ['dibuat' => 'Urut waktu dibuat', 'mulai' => 'Urut jadwal camp', 'berakhir' => 'Urut masa akun', 'peserta' => 'Urut jumlah peserta', 'total' => 'Urut total'][$urut] ?? 'Terbaru dibuat di atas' }}</span>
                            <span class="dsb-chip is-samar">Klik judul kolom untuk mengurutkan</span>
                            <span class="dsb-chip is-samar">Satu baris = satu batch</span>
                        </div>
                    </div>
                </div>

                <div class="k-12">
                    @if ($pemesananrsc->isEmpty())
                        <div class="dsb-kartu">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-inbox' }}"></i></span>
                                @if ($adaSaringan)
                                    <p class="dsb-kosong-judul">Tidak ada batch yang cocok</p>
                                    <p class="dsb-kosong-ket">Tidak ada batch yang cocok dengan saringan yang sedang aktif.</p>
                                    <button type="button" wire:click="resetFilters" class="dsb-tombol is-utama" style="margin-top: 14px;">
                                        <i class="bi bi-x-circle"></i><span>Kosongkan saringan</span>
                                    </button>
                                @else
                                    <p class="dsb-kosong-judul">Belum ada pemesanan RSC</p>
                                    <p class="dsb-kosong-ket">Batch yang dibuat akan muncul di sini.</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="dsb-kartu">
                            <div class="dsb-tabel-bungkus">
                                <table class="dsb-tabel">
                                    <thead>
                                        @php
                                            $kolomTabel = [
                                                ['Batch', 'dibuat', ''],
                                                ['Akun', null, 'k-sedang'],
                                                ['Jadwal Camp', 'mulai', 'k-lebar'],
                                                ['Masa Akun', 'berakhir', 'k-sedang'],
                                                ['Peserta', 'peserta', ''],
                                                ['Status', null, 'k-sedang'],
                                                ['Total', 'total', ''],
                                            ];
                                        @endphp
                                        <tr>
                                            @foreach ($kolomTabel as [$judul, $kunci, $kelas])
                                                <th class="{{ $kelas }}">
                                                    @if ($kunci)
                                                        <button type="button" class="dsb-tabel-urut {{ $urut === $kunci ? 'aktif' : '' }}"
                                                            wire:click="urutkan('{{ $kunci }}')" title="Urutkan menurut {{ strtolower($judul) }}">
                                                            <span>{{ $judul }}</span>
                                                            <i class="bi {{ $urut === $kunci ? ($arahUrut === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}"></i>
                                                        </button>
                                                    @else
                                                        {{ $judul }}
                                                    @endif
                                                </th>
                                            @endforeach
                                            <th style="text-align: right;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pemesananrsc as $item)
                                            @php
                                                $key = $item->nama_camp.'|'.$item->batch_camp;
                                                $tambahan = $akunTambahanPerBatch[$key] ?? [];
                                                $utama = $item->dataakun?->nama_akun;
                                                // Newline ASLI ("\n"), bukan entity — {{ }} meng-escape '&' jadi
                                                // '&#10;' tak akan jadi baris baru. title native menampilkan \n multi-baris.
                                                $judulTambahan = "Akun tambahan:\n• ".implode("\n• ", $tambahan);
                                                $mulai = $item->tanggal_mulai_camp ? \Carbon\Carbon::parse($item->tanggal_mulai_camp) : null;
                                                $akhir = $item->tanggal_akhir_camp ? \Carbon\Carbon::parse($item->tanggal_akhir_camp) : null;
                                                $berjalan = $mulai && $akhir && today()->between($mulai->copy()->startOfDay(), $akhir->copy()->endOfDay());
                                                $berakhirAkun = $item->akun_berakhir ? \Carbon\Carbon::parse($item->akun_berakhir)->startOfDay() : null;
                                                $sisaHari = $berakhirAkun ? (int) today()->diffInDays($berakhirAkun, false) : null;
                                                $masa = null;
                                                if ($berakhirAkun && $item->status !== 'habis') {
                                                    if ($sisaHari < 0) {
                                                        // Abu-abu, bukan merah: masa sudah lewat hanyalah
                                                        // keterangan; statusnya bisa diubah ke "Habis".
                                                        $masa = ['is-abu', 'bi-calendar-x', 'Sudah berakhir'];
                                                    } elseif ($sisaHari === 0) {
                                                        $masa = ['is-merah', 'bi-hourglass-bottom', 'Berakhir hari ini'];
                                                    } elseif ($sisaHari <= $batasSegera) {
                                                        $masa = ['is-kuning', 'bi-hourglass-split', $sisaHari.' hari lagi'];
                                                    }
                                                }
                                                $urlDetail = route('admin.pesananrsc.detail', ['nama_camp' => urlencode($item->nama_camp), 'batch_camp' => urlencode($item->batch_camp)]);
                                            @endphp
                                            <tr wire:key="rsc-{{ md5($key) }}" class="{{ $berjalan ? 'is-tanda' : '' }}" @if ($berjalan) style="--c: #d97706" @endif>
                                                <td>
                                                    <a href="{{ $urlDetail }}" wire:navigate class="dsb-tabel-utama rsc-tautan-baris">
                                                        <span class="dsb-ikon is-kecil" style="--c: {{ $berjalan ? '#d97706' : '#7c3aed' }}">
                                                            <i class="bi {{ $berjalan ? 'bi-broadcast' : 'bi-mortarboard-fill' }}"></i>
                                                        </span>
                                                        <span class="dsb-tabel-teks">
                                                            <span class="dsb-tabel-judul">{{ $item->nama_camp }}</span>
                                                            <span class="dsb-tabel-meta">
                                                                <span class="dsb-lencana is-ungu">Batch #{{ $item->batch_camp }}</span>
                                                                @if ($berjalan)
                                                                    <span class="dsb-lencana is-kuning"><i class="bi bi-broadcast"></i>Berjalan</span>
                                                                @endif
                                                                @if ($masa)
                                                                    {{-- Salinan kolom Masa Akun untuk layar sempit. --}}
                                                                    <span class="dsb-lencana {{ $masa[0] }} rsc-masa-sempit"><i class="bi {{ $masa[1] }}"></i>{{ $masa[2] }}</span>
                                                                @endif
                                                                {{-- Salinan kolom yang disembunyikan di layar sempit. --}}
                                                                <span class="dsb-tabel-samar">
                                                                    <i class="bi bi-person-badge"></i>{{ $utama ?? 'Tanpa akun' }}{{ count($tambahan) ? ' +'.count($tambahan) : '' }}
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </a>
                                                </td>

                                                <td class="k-sedang" data-judul="Akun">
                                                    @if (count($tambahan) === 0)
                                                        <span class="dsb-tabel-angka">{{ $utama ?? '—' }}</span>
                                                    @else
                                                        {{-- Akun utama tampil, sisanya ringkas di lencana "+N".
                                                             Nama akun tambahan muncul saat hover. --}}
                                                        <span class="rsc-akun">
                                                            <span class="dsb-tabel-angka">{{ $utama ?? '—' }}</span>
                                                            <span class="dsb-lencana is-biru" style="cursor: help;" title="{{ $judulTambahan }}">+{{ count($tambahan) }}</span>
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="k-lebar" data-judul="Jadwal">
                                                    <span class="dsb-tabel-teks">
                                                        <span class="dsb-tabel-angka">
                                                            {{ $mulai?->locale('id')->translatedFormat('d M') ?? '—' }}
                                                            &ndash; {{ $akhir?->locale('id')->translatedFormat('d M Y') ?? '—' }}
                                                        </span>
                                                        @if ($mulai && $akhir)
                                                            <span class="dsb-tabel-meta">{{ (int) $mulai->diffInDays($akhir) + 1 }} hari</span>
                                                        @endif
                                                    </span>
                                                </td>

                                                <td class="k-sedang" data-judul="Masa Akun">
                                                    <span class="dsb-tabel-teks">
                                                        <span class="dsb-tabel-angka">{{ $berakhirAkun?->locale('id')->translatedFormat('d M Y') ?? '—' }}</span>
                                                        @if ($masa)
                                                            <span class="dsb-tabel-meta"><span class="dsb-lencana {{ $masa[0] }}"><i class="bi {{ $masa[1] }}"></i>{{ $masa[2] }}</span></span>
                                                        @endif
                                                    </span>
                                                </td>

                                                <td data-judul="Peserta">
                                                    <span class="dsb-lencana is-nila"><i class="bi bi-people-fill"></i>{{ $item->total_peserta }} orang</span>
                                                </td>

                                                <td class="k-sedang" data-judul="Status">
                                                    <span class="dsb-lencana {{ $lencanaStatus[$item->status] ?? 'is-abu' }}">{{ ucfirst($item->status) }}</span>
                                                </td>

                                                <td data-judul="Total">
                                                    <span class="dsb-tabel-angka rsc-total">{{ $rupiah($item->total_harga) }}</span>
                                                </td>

                                                <td data-judul="Aksi" style="text-align: right;">
                                                    <span class="dsb-tabel-aksi">
                                                        <a wire:navigate href="{{ $urlDetail }}" class="dsb-tabel-btn" title="Lihat detail">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        @if ($bolehEdit)
                                                            <a wire:navigate href="{{ route('admin.pesananrsc.edit', ['nama_camp' => $item->nama_camp, 'batch_camp' => $item->batch_camp]) }}"
                                                                class="dsb-tabel-btn" title="Edit batch">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                        @endif
                                                        @if ($bolehHapus)
                                                            <button type="button" title="Hapus batch"
                                                                class="dsb-tabel-btn is-bahaya rsc-delete-batch"
                                                                data-nama="{{ $item->nama_camp }}" data-batch="{{ $item->batch_camp }}"
                                                                data-total="{{ $item->total_peserta }}">
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

                            {{-- Satu kali saja. Versi lama mencetak pagination DUA kali —
                                 sekali di bawah tabel, sekali lagi di bawah jendela WA yang
                                 tersembunyi — sehingga ada dua deret nomor halaman. --}}
                            @if ($pemesananrsc->hasPages())
                                <div class="rsc-halaman">
                                    {{ $pemesananrsc->links('vendor.pagination') }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    {{-- ================== JENDELA UNDUH ================== --}}
    @if ($showExportModal)
        <div class="ts-modal-back rsc-modal-back" wire:click="closeExportModal"></div>
        <div class="ts-modal rsc-modal">
            <div class="ts-modal-card dsb is-datar rsc-modal-card" role="dialog" aria-modal="true"
                aria-label="Unduh data pemesanan RSC" tabindex="-1" data-tutup="showExportModal">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-cloud-arrow-down-fill"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">Unduh Data Peserta</h5>
                        <span class="dsb-kartu-sub">Pilih batch, lalu unduh sebagai Excel atau invoice PDF</span>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="closeExportModal" title="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="dsb-jendela-isi">
                    <div class="dsb-cari" style="margin-bottom: 12px;">
                        <i class="bi bi-search"></i>
                        <input type="search" class="dsb-isian" placeholder="Cari nama camp atau batch…"
                            wire:model.live.debounce.300ms="searchBatchExport">
                    </div>

                    <div class="rsc-pilih-batch">
                        @forelse ($this->availableBatchesForExport as $batch)
                            @php $dipilih = in_array($batch->key, $selectedBatches); @endphp
                            <label class="rsc-pilih-baris {{ $dipilih ? 'is-dipilih' : '' }}" wire:key="ex-{{ md5($batch->key) }}">
                                <input type="checkbox" value="{{ $batch->key }}" wire:model.live="selectedBatches">
                                <span class="dsb-ikon is-kecil" style="--c: {{ $dipilih ? '#16a34a' : '#94a3b8' }}">
                                    <i class="bi {{ $dipilih ? 'bi-check-lg' : 'bi-mortarboard' }}"></i>
                                </span>
                                <span class="rsc-pilih-nama">{{ $batch->nama_camp }}</span>
                                <span class="dsb-lencana is-ungu">Batch {{ $batch->batch_camp }}</span>
                            </label>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-inbox"></i></span>
                                <p class="dsb-kosong-judul">Batch tidak ditemukan</p>
                            </div>
                        @endforelse
                    </div>
                    @error('selectedBatches')<p class="dsb-galat" style="margin-top: 8px;">{{ $message }}</p>@enderror
                </div>

                <div class="dsb-jendela-kaki">
                    <span class="dsb-jendela-kaki-ket">
                        <i class="bi bi-check2-square"></i><span><b>{{ count($selectedBatches) }}</b> batch dipilih</span>
                    </span>
                    <span class="dsb-jendela-aksi">
                        @if (! empty($selectedBatches))
                            <a href="{{ route('admin.preview.invoice', ['batches' => $selectedBatches]) }}"
                                target="_blank" rel="noopener" class="dsb-tombol is-lembut is-penuh-sempit">
                                <i class="bi bi-eye"></i><span>Pratinjau</span>
                            </a>
                        @endif
                        <button type="button" class="dsb-tombol is-bahaya is-penuh-sempit" wire:click="exportInvoice"
                            wire:loading.attr="disabled" wire:target="exportInvoice" @disabled(empty($selectedBatches))>
                            <i class="bi bi-file-earmark-pdf"></i><span>Invoice PDF</span>
                        </button>
                        <button type="button" class="dsb-tombol is-hijau is-penuh-sempit" wire:click="exportExcel"
                            wire:loading.attr="disabled" wire:target="exportExcel" @disabled(empty($selectedBatches))>
                            <i class="bi bi-file-earmark-excel"></i><span>Excel</span>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.layout.sweetalert')
</div>

<!--================== SWEET ALERT DELETE (glossy, seragam banners) ==================-->
<script>
    if (!window.__rscDeleteBound) {
        window.__rscDeleteBound = true;

        const rscGlossyConfig = {
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

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.rsc-delete-batch');
            if (!button) return;
            event.preventDefault();

            const nama = button.dataset.nama;
            const batch = button.dataset.batch;
            const total = button.dataset.total;

            Swal.fire({
                title: 'Yakin hapus batch ini?',
                html: `Batch <b>#${batch}</b> — <b>${nama}</b><br><span class="text-danger">${total} peserta &amp; data cashflow terkait akan dihapus permanen!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                ...rscGlossyConfig
            }).then((result) => {
                if (result.isConfirmed) {
                    const comp = button.closest('[wire\\:id]');
                    if (comp) Livewire.find(comp.getAttribute('wire:id')).call('deleteBatch', nama, batch);
                }
            });
        });

        window.addEventListener('batch-deleted', (e) => {
            Swal.fire({
                title: 'Terhapus!',
                text: (e.detail && e.detail.message) || 'Batch berhasil dihapus.',
                icon: 'success',
                timer: 2500,
                showConfirmButton: false,
                ...rscGlossyConfig
            });
        });

        window.addEventListener('batch-delete-error', (e) => {
            Swal.fire({
                title: 'Gagal!',
                text: (e.detail && e.detail.message) || 'Gagal menghapus batch.',
                icon: 'error',
                timer: 3000,
                showConfirmButton: false,
                ...rscGlossyConfig
            });
        });
    }
</script>
<!--================== END SWEET ALERT DELETE ==================-->
