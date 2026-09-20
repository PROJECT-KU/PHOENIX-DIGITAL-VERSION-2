@section('title')
Detail Pesanan RSC || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.pemesanan-r-s-c.partials.rsc-gaya')

    @php
        // Izin dihitung sekali di sini: dua kondisi izin berkurung bersarang
        // dalam satu berkas membuat Livewire salah memasang penanda morph.
        $bolehBuat = auth()->user()->hasPermission('create_pesananrsc');
        $bolehEdit = auth()->user()->hasPermission('edit_pesananrsc');
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        // locale('id'): APP_LOCALE=en, tanpa ini bulan tertulis "Aug", bukan "Agu".
        $tgl = fn ($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d M Y') : '—';
        $lencanaStatus = ['baru' => 'is-hijau', 'perpanjang' => 'is-biru', 'pengganti' => 'is-kuning', 'habis' => 'is-luring'];
        $warnaStatus = ['baru' => '#16a34a', 'perpanjang' => '#0284c7', 'pengganti' => '#d97706', 'habis' => '#e11d48'];
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">{{ $batchData->nama_camp ?? $nama_camp }}</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    @if ($batchData)
                        <span class="d-block rsc-lencana-kepala">
                            <span class="dsb-lencana is-ungu">Batch #{{ $batchData->batch_camp }}</span>
                            <span class="dsb-lencana {{ $lencanaStatus[$batchData->status] ?? 'is-abu' }}">{{ ucfirst($batchData->status) }}</span>
                            <span class="dsb-lencana {{ $metode_harga === 'per_akun' ? 'is-biru' : 'is-nila' }}">
                                <i class="bi {{ $metode_harga === 'per_akun' ? 'bi-collection-fill' : 'bi-people-fill' }}"></i>
                                {{ $metode_harga === 'per_akun' ? 'Harga per akun' : 'Harga per peserta' }}
                            </span>
                        </span>
                    @endif
                </p>
            </div>

            {{-- Aksi batch ini. Sebelumnya halaman detail tidak punya satu pun
                 tombol: mengunduh invoice batch yang sedang dibuka berarti
                 kembali ke daftar, membuka jendela unduh, lalu mencari dan
                 mencentang batch yang sama. --}}
            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.preview.invoice', ['batches' => [$nama_camp.'|'.$batch_camp]]) }}"
                    target="_blank" rel="noopener" class="dsb-tombol is-lembut">
                    <i class="bi bi-eye"></i><span>Pratinjau</span>
                </a>
                <button type="button" wire:click="unduhExcel" class="dsb-tombol is-lembut"
                    wire:loading.attr="disabled" wire:target="unduhExcel" style="--ikon: #16a34a">
                    <i class="bi bi-file-earmark-excel"></i><span>Excel</span>
                </button>
                <button type="button" wire:click="unduhInvoice" class="dsb-tombol is-lembut"
                    wire:loading.attr="disabled" wire:target="unduhInvoice" style="--ikon: #dc2626">
                    <i class="bi bi-file-earmark-pdf"></i><span>Invoice</span>
                </button>
                @if ($bolehBuat)
                    {{-- Batch berikutnya biasanya berisi kategori, akun, PIC, dan
                         peserta yang sama: disalin, lalu tinggal isi nomor
                         batch & jadwal baru. --}}
                    <a wire:navigate href="{{ route('admin.pesananrsc.create', ['salin' => $nama_camp.'|'.$batch_camp]) }}"
                        class="dsb-tombol is-lembut">
                        <i class="bi bi-files"></i><span>Salin Batch</span>
                    </a>
                @endif
                @if ($bolehEdit)
                    <a wire:navigate href="{{ route('admin.pesananrsc.edit', ['nama_camp' => $nama_camp, 'batch_camp' => $batch_camp]) }}"
                        class="dsb-tombol is-utama">
                        <i class="bi bi-pencil"></i><span>Edit Batch</span>
                    </a>
                @endif
            </div>
        </header>

        @if ($batchData)
            @php
                $mulai = $batchData->tanggal_mulai_camp ? \Carbon\Carbon::parse($batchData->tanggal_mulai_camp) : null;
                $akhir = $batchData->tanggal_akhir_camp ? \Carbon\Carbon::parse($batchData->tanggal_akhir_camp) : null;
                $durasi = $mulai && $akhir ? (int) $mulai->diffInDays($akhir) + 1 : null;
                $berjalan = $mulai && $akhir && today()->between($mulai->copy()->startOfDay(), $akhir->copy()->endOfDay());
                $perAkun = $metode_harga === 'per_akun';

                // Keadaan jadwal dalam kata: lebih cepat dibaca daripada dua tanggal.
                if (! $mulai) {
                    $jadwal = ['#64748b', 'Jadwal belum diisi'];
                } elseif ($berjalan) {
                    $jadwal = ['#d97706', 'Sedang berlangsung'];
                } elseif (today()->lt($mulai)) {
                    $jadwal = ['#0284c7', 'Mulai '.(int) today()->diffInDays($mulai).' hari lagi'];
                } else {
                    $jadwal = ['#16a34a', 'Sudah selesai'];
                }
            @endphp

            {{-- ================== RINGKASAN ================== --}}
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kepala" style="--c: {{ $warnaStatus[$batchData->status] ?? '#7c3aed' }}">
                        <span class="dsb-kepala-ikon"><i class="bi bi-mortarboard-fill"></i></span>
                        <div class="dsb-kepala-teks">
                            <span class="dsb-kicker">Ringkasan</span>
                            <h2 class="dsb-judul">Keadaan Batch</h2>
                            <div class="dsb-chip-deret">
                                <span class="dsb-chip"><i class="bi bi-calendar-range"></i>{{ $tgl($mulai) }} – {{ $tgl($akhir) }}</span>
                                <span class="dsb-chip is-samar">{{ $jadwal[1] }}</span>
                            </div>
                        </div>
                    </div>

                    <article class="dsb-stat is-utama k-6" style="--c: #16a34a">
                        <span class="dsb-ikon"><i class="bi bi-cash-stack"></i></span>
                        <p class="dsb-stat-label">Total Harga</p>
                        <p class="dsb-stat-nilai">{{ $rupiah($batchData->total_harga) }}</p>
                        <p class="dsb-stat-ket">
                            <i class="bi bi-calculator"></i>
                            <span>
                                @if ($perAkun)
                                    {{ (int) $batchData->jumlah_pemesanan }} bulan × {{ $rupiah($sumHargaAkun) }} ({{ count($akunBreakdown) }} akun)
                                @else
                                    {{ $batchData->total_peserta }} peserta × {{ $rupiah($batchData->harga_satuan) }}
                                @endif
                            </span>
                        </p>
                    </article>

                    <article class="dsb-stat is-utama k-6" style="--c: #7c3aed">
                        <span class="dsb-ikon"><i class="bi bi-people-fill"></i></span>
                        <p class="dsb-stat-label">Peserta</p>
                        <p class="dsb-stat-nilai">{{ $batchData->total_peserta }}<span class="dsb-stat-satuan">orang</span></p>
                        <p class="dsb-stat-ket"><i class="bi bi-person-badge"></i><span>PIC: {{ $batchData->users->name ?? '—' }}</span></p>
                    </article>

                    <article class="dsb-stat k-4" style="--c: {{ $jadwal[0] }}">
                        <span class="dsb-ikon"><i class="bi bi-calendar-range"></i></span>
                        <p class="dsb-stat-label">Durasi Camp</p>
                        <p class="dsb-stat-nilai">{{ $durasi ?? '—' }}<span class="dsb-stat-satuan">hari</span></p>
                        <p class="dsb-stat-ket"><i class="bi bi-clock"></i><span>{{ $jadwal[1] }}</span></p>
                    </article>

                    <article class="dsb-stat k-4" style="--c: #d97706">
                        <span class="dsb-ikon"><i class="bi {{ $perAkun ? 'bi-collection-fill' : 'bi-tag-fill' }}"></i></span>
                        <p class="dsb-stat-label">{{ $perAkun ? 'Harga '.count($akunBreakdown).' Akun' : 'Harga Satuan' }}</p>
                        <p class="dsb-stat-nilai">{{ $rupiah($perAkun ? $sumHargaAkun : $batchData->harga_satuan) }}</p>
                        <p class="dsb-stat-ket"><i class="bi bi-info-circle"></i><span>{{ $perAkun ? 'Per bulan, semua akun' : 'Per peserta per bulan' }}</span></p>
                    </article>

                    <article class="dsb-stat k-4" style="--c: #0284c7">
                        <span class="dsb-ikon"><i class="bi bi-hourglass-split"></i></span>
                        <p class="dsb-stat-label">Masa Akun</p>
                        <p class="dsb-stat-nilai">{{ (int) $batchData->jumlah_pemesanan }}<span class="dsb-stat-satuan">bulan</span></p>
                        <p class="dsb-stat-ket"><i class="bi bi-calendar-x"></i><span>Berakhir {{ $tgl($batchData->tanggal_berakhir) }}</span></p>
                    </article>
                </div>
            </section>

            {{-- ================== RINCIAN ================== --}}
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kepala" style="--c: #0284c7">
                        <span class="dsb-kepala-ikon"><i class="bi bi-card-list"></i></span>
                        <div class="dsb-kepala-teks">
                            <span class="dsb-kicker">Rincian</span>
                            <h2 class="dsb-judul">Batch &amp; Akun</h2>
                            <div class="dsb-chip-deret">
                                <span class="dsb-chip"><i class="bi bi-person-badge"></i>{{ 1 + $extraAkuns->count() }} akun</span>
                                <span class="dsb-chip is-samar">Password disamarkan — tekan ikon mata untuk melihatnya</span>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-kartu k-4">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-folder2-open"></i></span>
                                <div><h3 class="dsb-kartu-judul">Batch</h3><span class="dsb-kartu-sub">Jadwal camp</span></div>
                            </div>
                        </div>
                        <div class="dsb-kartu-isi">
                            <div class="dsb-data">
                                <div class="dsb-data-baris"><span class="dsb-data-label"><i class="bi bi-tag"></i>Kategori</span><span class="dsb-data-nilai">{{ $batchData->nama_camp }}</span></div>
                                <div class="dsb-data-baris"><span class="dsb-data-label"><i class="bi bi-hash"></i>Batch</span><span class="dsb-data-nilai">#{{ $batchData->batch_camp }}</span></div>
                                <div class="dsb-data-baris"><span class="dsb-data-label"><i class="bi bi-calendar-event"></i>Mulai</span><span class="dsb-data-nilai">{{ $tgl($mulai) }}</span></div>
                                <div class="dsb-data-baris"><span class="dsb-data-label"><i class="bi bi-calendar-check"></i>Berakhir</span><span class="dsb-data-nilai">{{ $tgl($akhir) }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-kartu k-4" x-data="{ lihat: false }">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-person-badge-fill"></i></span>
                                <div><h3 class="dsb-kartu-judul">Akun Utama</h3><span class="dsb-kartu-sub">{{ $batchData->dataakun->nama_akun ?? 'Belum dipilih' }}</span></div>
                            </div>
                            @php
                                $teksKredensial = implode("\n", array_filter([
                                    ($batchData->dataakun->nama_akun ?? 'Akun'),
                                    'Username: '.($batchData->username ?? '-'),
                                    'Password: '.($batchData->password ?? '-'),
                                    $batchData->link_akses ? 'Link: '.$batchData->link_akses : null,
                                ]));
                            @endphp
                            <button type="button" class="dsb-tabel-btn rsc-salin" data-salin="{{ $teksKredensial }}"
                                title="Salin username, password, dan link" aria-label="Salin kredensial akun utama">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <div class="dsb-kartu-isi">
                            <div class="dsb-data">
                                <div class="dsb-data-baris"><span class="dsb-data-label"><i class="bi bi-person"></i>Username</span><span class="dsb-data-nilai rsc-rahasia-nilai">{{ $batchData->username ?? '—' }}</span></div>
                                <div class="dsb-data-baris">
                                    <span class="dsb-data-label"><i class="bi bi-key"></i>Password</span>
                                    <span class="dsb-data-nilai rsc-rahasia">
                                        <span class="rsc-rahasia-nilai" x-show="! lihat">••••••••</span>
                                        <span class="rsc-rahasia-nilai" x-show="lihat" x-cloak>{{ $batchData->password ?? '—' }}</span>
                                        <button type="button" class="dsb-tabel-btn" x-on:click="lihat = ! lihat"
                                            x-bind:aria-label="lihat ? 'Sembunyikan password' : 'Tampilkan password'">
                                            <i class="bi" x-bind:class="lihat ? 'bi-eye-slash' : 'bi-eye'"></i>
                                        </button>
                                    </span>
                                </div>
                                <div class="dsb-data-baris">
                                    <span class="dsb-data-label"><i class="bi bi-link-45deg"></i>Link</span>
                                    <span class="dsb-data-nilai">
                                        @if ($batchData->link_akses)
                                            <a href="{{ $batchData->link_akses }}" target="_blank" rel="noopener" title="{{ $batchData->link_akses }}">
                                                {{ Str::limit($batchData->link_akses, 24) }} <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        @else — @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-kartu k-4">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-sliders"></i></span>
                                <div><h3 class="dsb-kartu-judul">Pemesanan</h3><span class="dsb-kartu-sub">PIC & masa akun</span></div>
                            </div>
                        </div>
                        <div class="dsb-kartu-isi">
                            <div class="dsb-data">
                                <div class="dsb-data-baris"><span class="dsb-data-label"><i class="bi bi-person-check"></i>PIC</span><span class="dsb-data-nilai">{{ $batchData->users->name ?? '—' }}</span></div>
                                <div class="dsb-data-baris"><span class="dsb-data-label"><i class="bi bi-flag"></i>Status</span><span class="dsb-data-nilai"><span class="dsb-lencana {{ $lencanaStatus[$batchData->status] ?? 'is-abu' }}">{{ ucfirst($batchData->status) }}</span></span></div>
                                <div class="dsb-data-baris"><span class="dsb-data-label"><i class="bi bi-calendar-plus"></i>Dipesan</span><span class="dsb-data-nilai">{{ $tgl($batchData->tanggal_pemesanan) }}</span></div>
                                <div class="dsb-data-baris"><span class="dsb-data-label"><i class="bi bi-calendar-x"></i>Akun berakhir</span><span class="dsb-data-nilai">{{ $tgl($batchData->tanggal_berakhir) }}</span></div>
                            </div>
                        </div>
                    </div>

                    @if ($perAkun)
                        <div class="dsb-kartu k-12">
                            <div class="dsb-kartu-kepala">
                                <div class="dsb-kartu-kepala-kiri">
                                    <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-calculator-fill"></i></span>
                                    <div>
                                        <h3 class="dsb-kartu-judul">Rincian Harga per Akun</h3>
                                        <span class="dsb-kartu-sub">Total dihitung dari jumlah harga akun × durasi, bukan per peserta</span>
                                    </div>
                                </div>
                            </div>
                            <div class="dsb-kartu-isi">
                                <div class="rsc-harga-akun">
                                    @foreach ($akunBreakdown as $ab)
                                        <div class="rsc-harga-akun-item">
                                            <span class="rsc-harga-akun-nama">
                                                <span class="dsb-ikon is-kecil" style="--c: {{ $ab['utama'] ? '#d97706' : '#0284c7' }}; width: 26px; height: 26px; border-radius: 8px; font-size: .75rem;">
                                                    <i class="bi {{ $ab['utama'] ? 'bi-star-fill' : 'bi-collection' }}"></i>
                                                </span>
                                                <span>{{ $ab['nama'] }}</span>
                                                @if ($ab['utama'])<span class="dsb-lencana is-kuning">Utama</span>@endif
                                            </span>
                                            <b>{{ $rupiah($ab['harga']) }}</b>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="rsc-harga-akun-rumus">
                                    <span><i class="bi bi-calculator"></i> {{ (int) $batchData->jumlah_pemesanan }} bulan × {{ $rupiah($sumHargaAkun) }}</span>
                                    <b>{{ $rupiah($batchData->total_harga) }}</b>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($extraAkuns->count())
                        <div class="dsb-kartu k-12">
                            <div class="dsb-kartu-kepala">
                                <div class="dsb-kartu-kepala-kiri">
                                    <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-collection-fill"></i></span>
                                    <div><h3 class="dsb-kartu-judul">Akun Tambahan</h3><span class="dsb-kartu-sub">{{ $extraAkuns->count() }} akun selain akun utama</span></div>
                                </div>
                            </div>
                            <div class="dsb-kartu-isi">
                                <div class="rsc-harga-akun">
                                    @foreach ($extraAkuns as $ea)
                                        <div class="rsc-akun-kartu" x-data="{ lihat: false }" wire:key="ea-{{ $ea->id }}">
                                            @php
                                                $namaEa = $ea->nama_akun ?? optional($ea->dataakun)->nama_akun ?? 'Akun';
                                                $teksEa = implode("\n", array_filter([
                                                    $namaEa,
                                                    'Username: '.($ea->username ?? '-'),
                                                    'Password: '.($ea->password ?? '-'),
                                                    $ea->link_akses ? 'Link: '.$ea->link_akses : null,
                                                ]));
                                            @endphp
                                            <div class="rsc-akun-kartu-judul">
                                                <span class="dsb-ikon is-kecil" style="--c: #16a34a; width: 28px; height: 28px; border-radius: 9px; font-size: .8rem;"><i class="bi bi-person-badge-fill"></i></span>
                                                <span style="flex: 1 1 auto; min-width: 0;">{{ $namaEa }}</span>
                                                <button type="button" class="dsb-tabel-btn rsc-salin" data-salin="{{ $teksEa }}"
                                                    title="Salin username, password, dan link" aria-label="Salin kredensial {{ $namaEa }}">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                            <div class="dsb-data">
                                                <div class="dsb-data-baris"><span class="dsb-data-label">Username</span><span class="dsb-data-nilai rsc-rahasia-nilai">{{ $ea->username ?? '—' }}</span></div>
                                                <div class="dsb-data-baris">
                                                    <span class="dsb-data-label">Password</span>
                                                    <span class="dsb-data-nilai rsc-rahasia">
                                                        <span class="rsc-rahasia-nilai" x-show="! lihat">••••••••</span>
                                                        <span class="rsc-rahasia-nilai" x-show="lihat" x-cloak>{{ $ea->password ?? '—' }}</span>
                                                        <button type="button" class="dsb-tabel-btn" x-on:click="lihat = ! lihat"
                                                            x-bind:aria-label="lihat ? 'Sembunyikan password' : 'Tampilkan password'">
                                                            <i class="bi" x-bind:class="lihat ? 'bi-eye-slash' : 'bi-eye'"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                                <div class="dsb-data-baris">
                                                    <span class="dsb-data-label">Link</span>
                                                    <span class="dsb-data-nilai">
                                                        @if ($ea->link_akses)
                                                            <a href="{{ $ea->link_akses }}" target="_blank" rel="noopener" title="{{ $ea->link_akses }}">{{ Str::limit($ea->link_akses, 22) }} <i class="bi bi-box-arrow-up-right"></i></a>
                                                        @else — @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($batchData->deskripsi)
                        <div class="dsb-kartu k-12">
                            <div class="dsb-kartu-kepala">
                                <div class="dsb-kartu-kepala-kiri">
                                    <span class="dsb-ikon is-kecil" style="--c: #64748b"><i class="bi bi-card-text"></i></span>
                                    <div><h3 class="dsb-kartu-judul">Deskripsi</h3></div>
                                </div>
                            </div>
                            <div class="dsb-kartu-isi"><p class="rsc-uraian">{{ $batchData->deskripsi }}</p></div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ================== PESERTA ================== --}}
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kepala" style="--c: #16a34a">
                        <span class="dsb-kepala-ikon"><i class="bi bi-people-fill"></i></span>
                        <div class="dsb-kepala-teks">
                            <span class="dsb-kicker">Peserta</span>
                            <h2 class="dsb-judul">Daftar Peserta</h2>
                            <div class="dsb-chip-deret">
                                <span class="dsb-chip"><i class="bi bi-people"></i>{{ $pesertaList->count() }} orang</span>
                                <span class="dsb-chip"><i class="bi bi-cash-stack"></i>{{ $rupiah($batchData->total_harga) }}</span>
                                <span class="dsb-chip is-samar"><i class="bi bi-whatsapp"></i>{{ (collect($waPeserta)->first()['jenis'] ?? null) === 'habis' ? 'Tombol WA mengirim info masa aktif habis' : 'Tombol WA mengirim akses akun' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="k-12">
                        <div class="dsb-kartu" x-data="{ q: '' }">
                            @if ($pesertaList->count() > 5)
                                {{-- Cari di peramban: semua peserta batch sudah ada di
                                     halaman, tidak perlu bolak-balik ke server. --}}
                                <div class="rsc-cari-peserta">
                                    <div class="dsb-cari">
                                        <i class="bi bi-search"></i>
                                        <input type="search" class="dsb-isian" x-model="q" placeholder="Cari nama, no. telp, atau ID transaksi…" aria-label="Cari peserta">
                                    </div>
                                    <span class="dsb-kartu-sub" x-show="q" x-cloak>
                                        <span x-text="[...$root.querySelectorAll('tbody tr')].filter(r => r.dataset.cari.includes(q.toLowerCase().trim())).length"></span> cocok
                                    </span>
                                </div>
                            @endif
                            @if ($pesertaList->isEmpty())
                                <div class="dsb-kosong">
                                    <span class="dsb-kosong-ikon"><i class="bi bi-inbox"></i></span>
                                    <p class="dsb-kosong-judul">Belum ada peserta</p>
                                    <p class="dsb-kosong-ket">Tidak ada data peserta pada batch ini.</p>
                                </div>
                            @else
                                <div class="dsb-tabel-bungkus">
                                    <table class="dsb-tabel">
                                        <thead>
                                            <tr>
                                                <th>Peserta</th>
                                                <th class="k-sedang">ID Transaksi</th>
                                                <th>No. Telp</th>
                                                <th class="k-lebar">Durasi</th>
                                                {{-- Mode per akun: harga tidak dihitung per orang, jadi
                                                     kolom harganya tidak punya arti di sini. --}}
                                                @unless ($perAkun)
                                                    <th class="k-sedang" style="text-align: right;">Harga</th>
                                                    <th style="text-align: right;">Total</th>
                                                @endunless
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pesertaList as $index => $peserta)
                                                @php
                                                    $digitWa = preg_replace('/\D/', '', (string) $peserta->telp_pembeli);
                                                    $cariPs = mb_strtolower($peserta->nama_pembeli.' '.$peserta->telp_pembeli.' '.$digitWa.' '.$peserta->id_transaksi);
                                                @endphp
                                                <tr wire:key="ps-{{ $peserta->id }}" data-cari="{{ $cariPs }}"
                                                    x-show="! q || $el.dataset.cari.includes(q.toLowerCase().trim())">
                                                    <td>
                                                        <div class="dsb-tabel-utama">
                                                            <span class="dsb-avatar is-kecil" style="--c: #7c3aed">{{ \Illuminate\Support\Str::substr($peserta->nama_pembeli ?? '?', 0, 1) }}</span>
                                                            <span class="dsb-tabel-teks">
                                                                <span class="dsb-tabel-judul">{{ $peserta->nama_pembeli ?? '—' }}</span>
                                                                <span class="dsb-tabel-meta">
                                                                    <span>#{{ $index + 1 }}</span>
                                                                    <span class="dsb-tabel-samar">{{ $peserta->id_transaksi }}</span>
                                                                </span>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="k-sedang" data-judul="ID Transaksi"><span class="dsb-lencana is-abu rsc-rahasia-nilai">{{ $peserta->id_transaksi }}</span></td>
                                                    <td data-judul="No. Telp">
                                                        <span class="rsc-telp">
                                                            <span class="dsb-tabel-angka">{{ $peserta->telp_pembeli ?? '—' }}</span>
                                                            @php $wa = $waPeserta[$peserta->id] ?? null; @endphp
                                                            @if ($wa && $wa['url'])
                                                                {{-- api.whatsapp.com dengan pesan siap kirim, sama seperti
                                                                     Pesanan Toko. Isinya dirakit di server dan hanya
                                                                     dibuka oleh admin sendiri. --}}
                                                                <a href="{{ $wa['url'] }}" target="_blank" rel="noopener"
                                                                    class="dsb-tabel-btn rsc-wa {{ $wa['jenis'] === 'habis' ? 'is-habis' : '' }}"
                                                                    title="{{ $wa['jenis'] === 'habis' ? 'Kirim info masa aktif habis' : 'Kirim akses akun' }} ke {{ $peserta->nama_pembeli }}"
                                                                    aria-label="{{ $wa['jenis'] === 'habis' ? 'Kirim info masa aktif habis' : 'Kirim akses akun' }} ke {{ $peserta->nama_pembeli }} lewat WhatsApp">
                                                                    <i class="bi bi-whatsapp"></i>
                                                                </a>
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td class="k-lebar" data-judul="Durasi"><span class="dsb-tabel-angka">{{ $peserta->jumlah_pemesanan ?? '—' }} bulan</span></td>
                                                    @unless ($perAkun)
                                                        <td class="k-sedang" data-judul="Harga" style="text-align: right;"><span class="dsb-tabel-angka">{{ $rupiah($peserta->harga_satuan ?? 0) }}</span></td>
                                                        <td data-judul="Total" style="text-align: right;"><span class="dsb-tabel-angka rsc-total">{{ $rupiah($peserta->total ?? 0) }}</span></td>
                                                    @endunless
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="dsb-kosong" x-show="q && ! [...$root.querySelectorAll('tbody tr')].some(r => r.dataset.cari.includes(q.toLowerCase().trim()))" x-cloak>
                                        <span class="dsb-kosong-ikon"><i class="bi bi-search"></i></span>
                                        <p class="dsb-kosong-judul">Tidak ada peserta yang cocok</p>
                                    </div>
                                </div>

                                <div class="rsc-harga-akun-rumus" style="margin: 0; padding: 14px clamp(14px, 2vw, 20px);">
                                    <span><i class="bi bi-cash-stack"></i> Total batch{{ $perAkun ? ' (per akun)' : '' }}</span>
                                    <b>{{ $rupiah($batchData->total_harga) }}</b>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <script>
        // Tombol salin kredensial. Satu pendengar di dokumen (dipasang sekali),
        // karena halaman ini dibuka ulang lewat wire:navigate.
        if (!window.__rscSalinTerpasang) {
            window.__rscSalinTerpasang = true;
            document.addEventListener('click', async (e) => {
                const tombol = e.target.closest('.rsc-salin');
                if (!tombol) return;
                const ikon = tombol.querySelector('i');
                try {
                    await navigator.clipboard.writeText(tombol.dataset.salin);
                    ikon.className = 'bi bi-clipboard-check';
                    tombol.classList.add('is-tersalin');
                } catch (_) {
                    ikon.className = 'bi bi-x-lg';
                }
                setTimeout(() => { ikon.className = 'bi bi-clipboard'; tombol.classList.remove('is-tersalin'); }, 1600);
            });
        }
    </script>
</div>
