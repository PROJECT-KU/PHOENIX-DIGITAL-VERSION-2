@section('title')
Data Testimoni || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.testimoni.partials.testimoni-gaya')

    @php
        $bolehBuat = (bool) auth()->user()?->hasPermission('create_testimoni');
        $bolehUbah = (bool) auth()->user()?->hasPermission('edit_testimoni');
        $bolehHapus = (bool) auth()->user()?->hasPermission('delete_testimoni');
        $bolehPelanggan = (bool) auth()->user()?->hasPermission('view_customer');
        $kartuStatus = [
            'pending' => ['Menunggu', 'bi-hourglass-split', '#d97706'],
            'active' => ['Disetujui', 'bi-check-circle-fill', '#16a34a'],
            'non-active' => ['Ditolak', 'bi-x-circle-fill', '#dc2626'],
            'all' => ['Semua', 'bi-chat-quote-fill', '#7c3aed'],
        ];
        $gayaStatus = [
            'pending' => ['is-kuning', '#d97706', 'Menunggu'],
            'active' => ['is-hijau', '#16a34a', 'Disetujui'],
            'non-active' => ['is-merah', '#dc2626', 'Ditolak'],
        ];
        $kosong = [
            'pending' => ['bi-inbox', 'Tidak ada yang menunggu', 'Semua testimoni sudah ditinjau.'],
            'active' => ['bi-chat-quote', 'Belum ada testimoni disetujui', 'Testimoni yang disetujui tampil di halaman publik.'],
            'non-active' => ['bi-x-circle', 'Tidak ada testimoni ditolak', 'Testimoni yang ditolak disembunyikan dari publik.'],
            'all' => ['bi-chat-quote', 'Belum ada testimoni', 'Tambahkan testimoni atau tunggu kiriman pelanggan.'],
        ];
        $idHalaman = $Testimoni->pluck('id')->all();
        $semuaTercentang = $idHalaman && ! array_diff($idHalaman, $pilih);
        $sasaranMuat = 'searchTestimoni,setFilter,gotoPage,nextPage,previousPage,fRating,fSumber,fVerifikasi,fAnonim,fDari,fSampai,urut,perHalaman,arsip,resetSaring';
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Data Testimoni</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Kiriman pelanggan ditinjau di sini — yang disetujui tampil di halaman publik.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <button type="button" class="dsb-tombol" wire:click="$set('lihatAktivitas', true)" style="--ikon: #2563eb">
                    <i class="bi bi-clock-history"></i><span>Aktivitas</span>
                </button>
                <button type="button" class="dsb-tombol" wire:click="unduhExcel" wire:loading.attr="disabled" wire:target="unduhExcel" style="--ikon: #16a34a">
                    <span wire:loading.remove wire:target="unduhExcel" class="tm-isi-tombol"><i class="bi bi-file-earmark-excel"></i><span>Excel</span></span>
                    <span wire:loading.inline-flex wire:target="unduhExcel" class="tm-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
                <button type="button" class="dsb-tombol" wire:click="unduhPdf" wire:loading.attr="disabled" wire:target="unduhPdf" style="--ikon: #dc2626">
                    <span wire:loading.remove wire:target="unduhPdf" class="tm-isi-tombol"><i class="bi bi-file-earmark-pdf"></i><span>PDF</span></span>
                    <span wire:loading.inline-flex wire:target="unduhPdf" class="tm-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
                @if ($bolehBuat)
                    <a wire:navigate href="{{ route('admin.testimoni.create') }}" class="dsb-tombol is-utama">
                        <i class="bi bi-plus-lg"></i><span>Tambah Testimoni</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ================== STATUS (sekaligus tab moderasi) ================== --}}
        <nav class="tm-status" aria-label="Saring menurut status moderasi">
            @foreach ($kartuStatus as $nilai => [$label, $ikon, $warna])
                <button type="button" class="tm-status-btn {{ ! $arsip && $filter === $nilai ? 'is-aktif' : '' }} {{ $nilai === 'pending' && 0 < $tabCounts['pending'] ? 'is-perlu' : '' }}"
                    style="--c: {{ $warna }}" wire:click="setFilter('{{ $nilai }}')" aria-pressed="{{ ! $arsip && $filter === $nilai ? 'true' : 'false' }}">
                    <span class="tm-status-ikon"><i class="bi {{ $ikon }}"></i></span>
                    <span class="tm-status-teks"><b>{{ $tabCounts[$nilai] }}</b><span>{{ $label }}</span></span>
                </button>
            @endforeach
            @if ($tabCounts['arsip'] || $arsip)
                <button type="button" class="tm-status-btn {{ $arsip ? 'is-aktif' : '' }}" style="--c: #64748b"
                    wire:click="$set('arsip', {{ $arsip ? 'false' : 'true' }})" aria-pressed="{{ $arsip ? 'true' : 'false' }}">
                    <span class="tm-status-ikon"><i class="bi bi-archive-fill"></i></span>
                    <span class="tm-status-teks"><b>{{ $tabCounts['arsip'] }}</b><span>Arsip</span></span>
                </button>
            @endif
        </nav>

        {{-- ================== SEBARAN BINTANG ================== --}}
        <section class="dsb-kartu tm-sebaran">
            <div class="dsb-kartu-isi tm-sebaran-isi">
                <div class="tm-sebaran-nilai">
                    <b>{{ $rataRating ? number_format($rataRating, 1, ',', '.') : '–' }}</b>
                    <span class="tm-bintang" aria-hidden="true">
                        @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star-fill {{ $i <= round($rataRating) ? '' : 'is-kosong' }}"></i>@endfor
                    </span>
                    <small>Rata-rata dari {{ $tabCounts['active'] }} testimoni disetujui</small>
                </div>
                <div class="tm-sebaran-bar">
                    @foreach ($sebaran as $bintang => $b)
                        <div class="tm-bar-baris">
                            <span class="tm-bar-label">{{ $bintang }}<i class="bi bi-star-fill"></i></span>
                            <span class="tm-bar-alur"><span class="tm-bar-isi" style="width: {{ $b['persen'] }}%"></span></span>
                            <span class="tm-bar-nilai">{{ $b['jumlah'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="tm-sebaran-catatan">
                    <span class="tm-sebaran-ikon"><i class="bi bi-display"></i></span>
                    <div>
                    <p><b>{{ min($jumlahTampil, $maksBeranda) }}</b> dari {{ $jumlahTampil }} testimoni tampil di beranda.</p>
                    @if ($jumlahTampil > $maksBeranda)
                        <p class="tm-catatan-kecil">Pakai <i class="bi bi-star-fill"></i> Sorot untuk menaikkan testimoni pilihan.</p>
                    @else
                        <p class="tm-catatan-kecil">Bintang di bawah {{ \App\Models\Testimoni::RATING_MIN_TAMPIL }} tidak tampil kecuali disorot.</p>
                    @endif
                    <div class="tm-beranda-atur">
                        @if ($bolehUbah)
                            <label>
                                <span>Muat</span>
                                <select class="dsb-isian" wire:model.live="jumlahBeranda" aria-label="Banyak testimoni di beranda">
                                    @foreach ([3, 6, 9, 12, 15, 18, 24] as $n)
                                        <option value="{{ $n }}" @selected($jumlahBeranda === $n)>{{ $n }} kartu</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                        <button type="button" class="tm-baca" wire:click="$set('pratinjauBeranda', true)">Lihat urutannya</button>
                    </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== CARI & SARING ================== --}}
        <section class="dsb-kartu tm-saring" x-data="{ buka: @js($this->adaSaring) }">
            <div class="dsb-kartu-isi tm-saring-isi">
                <div class="dsb-cari">
                    <i class="bi bi-search"></i>
                    <input type="search" class="dsb-isian" wire:model.live.debounce.300ms="searchTestimoni" placeholder="Cari nama, peran, atau isi testimoni…" aria-label="Cari testimoni">
                    @if ($searchTestimoni)
                        <button type="button" class="dsb-cari-hapus" wire:click="$set('searchTestimoni', '')" title="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    @endif
                </div>

                <select class="dsb-isian tm-pilih" wire:model.live="urut" aria-label="Urutkan">
                    <option value="baru" @selected($urut === 'baru')>Terbaru</option>
                    <option value="lama" @selected($urut === 'lama')>Terlama</option>
                    <option value="tunggu" @selected($urut === 'tunggu')>Paling lama menunggu</option>
                    <option value="tinggi" @selected($urut === 'tinggi')>Bintang tertinggi</option>
                    <option value="rendah" @selected($urut === 'rendah')>Bintang terendah</option>
                </select>

                <select class="dsb-isian tm-pilih is-sempit" wire:model.live="perHalaman" aria-label="Jumlah per halaman">
                    @foreach ([12, 24, 48] as $n)
                        <option value="{{ $n }}" @selected($perHalaman === $n)>{{ $n }}/halaman</option>
                    @endforeach
                </select>

                <button type="button" class="tm-btn {{ $this->adaSaring ? 'is-aktif' : '' }}" x-on:click="buka = !buka" aria-label="Saringan lanjutan">
                    <i class="bi bi-funnel"></i><span>Saring</span>
                    @if ($this->adaSaring)<span class="tm-saring-titik"></span>@endif
                </button>

                {{-- Tampilan padat: meninjau puluhan kiriman lebih cepat lewat daftar. --}}
                <span class="tm-tampilan" role="group" aria-label="Tampilan daftar">
                    <button type="button" class="{{ $tampilan === 'kartu' ? 'is-aktif' : '' }}" wire:click="$set('tampilan', 'kartu')" title="Tampilan kartu" aria-label="Tampilan kartu"><i class="bi bi-grid"></i></button>
                    <button type="button" class="{{ $tampilan === 'daftar' ? 'is-aktif' : '' }}" wire:click="$set('tampilan', 'daftar')" title="Tampilan daftar" aria-label="Tampilan daftar"><i class="bi bi-list-ul"></i></button>
                </span>

                <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="{{ $sasaranMuat }}">
                    <span class="dsb-putar is-kecil"></span>Memuat…
                </span>
            </div>

            @if ($pilih)
                <p class="tm-ekspor-ket"><i class="bi bi-info-circle"></i> Ekspor akan berisi {{ count($pilih) }} testimoni yang dicentang saja.</p>
            @endif

            @if ($this->chipSaring)
                {{-- Saringan aktif tetap terlihat walau panel Saring ditutup. --}}
                <div class="tm-chip-saring">
                    @foreach ($this->chipSaring as $chip)
                        <button type="button" class="tm-chip-lepas" wire:click="lepasSaring('{{ $chip['nama'] }}')" title="Lepas saringan ini">
                            {{ $chip['label'] }}<i class="bi bi-x-lg"></i>
                        </button>
                    @endforeach
                    <button type="button" class="tm-chip-lepas is-semua" wire:click="resetSaring">Bersihkan semua</button>
                </div>
            @endif

            <div class="tm-saring-lanjut" x-show="buka" x-collapse x-cloak>
                <div class="tm-saring-baris">
                    <label class="tm-saring-medan">
                        <span>Bintang</span>
                        <select class="dsb-isian" wire:model.live="fRating">
                            <option value="">Semua bintang</option>
                            @foreach (range(5, 1) as $b)
                                <option value="{{ $b }}">{{ $b }} bintang</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="tm-saring-medan">
                        <span>Sumber</span>
                        <select class="dsb-isian" wire:model.live="fSumber">
                            <option value="">Semua sumber</option>
                            <option value="customer">Kiriman pelanggan</option>
                            <option value="admin">Diinput admin</option>
                        </select>
                    </label>
                    <label class="tm-saring-medan">
                        <span>Pembeli</span>
                        <select class="dsb-isian" wire:model.live="fVerifikasi">
                            <option value="">Semua</option>
                            <option value="ya">Tertaut pelanggan</option>
                            <option value="tidak">Tidak tertaut</option>
                        </select>
                    </label>
                    <label class="tm-saring-medan">
                        <span>Anonim</span>
                        <select class="dsb-isian" wire:model.live="fAnonim">
                            <option value="">Semua</option>
                            <option value="ya">Anonim</option>
                            <option value="tidak">Nama tampil</option>
                        </select>
                    </label>
                    <label class="tm-saring-medan">
                        <span>Sorot</span>
                        <select class="dsb-isian" wire:model.live="fSorot">
                            <option value="">Semua</option>
                            <option value="ya">Disorot</option>
                            <option value="tidak">Tanpa sorot</option>
                        </select>
                    </label>
                    <label class="tm-saring-medan">
                        <span>Di beranda</span>
                        <select class="dsb-isian" wire:model.live="fBeranda">
                            <option value="">Semua</option>
                            <option value="ya">Tampil di beranda</option>
                            <option value="tidak">Tidak tampil</option>
                        </select>
                    </label>
                    <label class="tm-saring-medan">
                        <span>Dikirim dari</span>
                        <input type="date" class="dsb-isian" wire:model.live="fDari" max="{{ now()->toDateString() }}">
                    </label>
                    <label class="tm-saring-medan">
                        <span>Sampai</span>
                        <input type="date" class="dsb-isian" wire:model.live="fSampai" max="{{ now()->toDateString() }}">
                    </label>
                </div>
                @if ($this->adaSaring || $searchTestimoni)
                    <button type="button" class="tm-btn is-bahaya tm-saring-reset" wire:click="resetSaring">
                        <i class="bi bi-arrow-counterclockwise"></i><span>Bersihkan saringan</span>
                    </button>
                @endif
            </div>
        </section>

        {{-- ================== BILAH AKSI MASSAL ================== --}}
        @if ($pilih && ($bolehUbah || $bolehHapus))
            <div class="tm-massal" role="region" aria-label="Aksi massal">
                <span class="tm-massal-jumlah"><b>{{ count($pilih) }}</b> dipilih</span>
                <div class="tm-massal-tombol">
                    @if ($arsip)
                        @if ($bolehHapus)
                            <button type="button" class="tm-btn is-setuju tm-konfirmasi" data-action="pulihkanTerpilih" data-icon="question"
                                data-title="Pulihkan {{ count($pilih) }} testimoni?" data-text="Semuanya kembali dengan status terakhirnya." data-confirm="Ya, pulihkan">
                                <i class="bi bi-arrow-counterclockwise"></i><span>Pulihkan terpilih</span>
                            </button>
                            <button type="button" class="tm-btn is-tolak tm-konfirmasi" data-action="buangTerpilih" data-icon="warning"
                                data-title="Buang {{ count($pilih) }} testimoni permanen?" data-text="Beserta fotonya, dan tidak bisa dikembalikan." data-confirm="Ya, buang">
                                <i class="bi bi-trash3"></i><span>Buang terpilih</span>
                            </button>
                        @endif
                    @else
                        @if ($bolehUbah)
                            <button type="button" class="tm-btn is-setuju tm-konfirmasi" data-action="setujuiTerpilih" data-icon="question"
                                data-title="Setujui {{ count($pilih) }} testimoni?" data-text="Semua yang dipilih akan tampil di publik. Pengirim yang berhak otomatis menjadi member."
                                data-confirm="Ya, setujui semua">
                                <i class="bi bi-check-lg"></i><span>Setujui</span>
                            </button>
                            <button type="button" class="tm-btn is-tolak" wire:click="bukaTolak('massal')">
                                <i class="bi bi-x-lg"></i><span>Tolak</span>
                            </button>
                            <button type="button" class="tm-btn" wire:click="sorotTerpilih(true)" title="Naikkan ke barisan depan beranda">
                                <i class="bi bi-star-fill"></i><span>Sorot</span>
                            </button>
                            <button type="button" class="tm-btn" wire:click="sorotTerpilih(false)">
                                <i class="bi bi-star"></i><span>Lepas sorot</span>
                            </button>
                        @endif
                        @if ($bolehHapus)
                            <button type="button" class="tm-btn is-bahaya tm-konfirmasi" data-action="arsipkanTerpilih" data-icon="warning"
                                data-title="Arsipkan {{ count($pilih) }} testimoni?" data-text="Dipindahkan ke Arsip dan masih bisa dipulihkan." data-confirm="Ya, arsipkan">
                                <i class="bi bi-archive"></i><span>Arsipkan</span>
                            </button>
                        @endif
                    @endif
                    <button type="button" class="tm-btn" wire:click="lepasPilih"><i class="bi bi-x"></i><span>Lepas</span></button>
                </div>
            </div>
        @endif

        {{-- ================== RAK TESTIMONI ================== --}}
        @if ($urungkan && $bolehUbah)
            <div class="tm-urungkan" role="status">
                <span><b>{{ $urungkan['nama'] }}</b> {{ $urungkan['aksi'] }}.</span>
                <button type="button" class="tm-btn" wire:click="urungkanTerakhir">
                    <i class="bi bi-arrow-counterclockwise"></i><span>Urungkan</span>
                </button>
                <button type="button" class="tm-urungkan-tutup" wire:click="tutupUrungkan" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
        @endif

        {{-- Kerangka pemuatan: tanpa ini kartu lama cuma meredup dan sekilas
             tampak seolah tidak ada yang berubah. --}}
        <div class="tm-kerangka" wire:loading.grid wire:target="{{ $sasaranMuat }}">
            @for ($i = 0; $i < 6; $i++)
                <div class="tm-kerangka-kartu">
                    <div class="tm-kerangka-kepala"><span class="tm-tulang is-bulat"></span><span class="tm-tulang" style="width: 55%"></span></div>
                    <span class="tm-tulang" style="width: 100%; height: 54px; margin-top: 12px;"></span>
                    <span class="tm-tulang" style="width: 42%; margin-top: 10px;"></span>
                </div>
            @endfor
        </div>

        <section wire:loading.class="tm-sembunyi" wire:target="{{ $sasaranMuat }}">
            @if ($Testimoni->isEmpty())
                @php
                    [$kIkon, $kJudul, $kKet] = $arsip
                        ? ['bi-archive', 'Arsip kosong', 'Testimoni yang dihapus tersimpan di sini sebelum dibuang permanen.']
                        : (($searchTestimoni || $this->adaSaring)
                            ? ['bi-funnel', 'Tidak ada testimoni yang cocok', 'Coba ubah kata kunci atau bersihkan saringan.']
                            : $kosong[$filter]);
                @endphp
                <div class="dsb-kartu">
                    <div class="dsb-kosong">
                        <span class="dsb-kosong-ikon"><i class="bi {{ $kIkon }}"></i></span>
                        <p class="dsb-kosong-judul">{{ $kJudul }}</p>
                        <p class="dsb-kosong-ket">{{ $kKet }}</p>
                    </div>
                </div>
            @else
                @if ($bolehUbah || ($arsip && $bolehHapus))
                    <div class="tm-pilih-semua">
                        <label class="tm-centang">
                            <input type="checkbox" @checked($semuaTercentang) wire:click="pilihHalaman({{ \Illuminate\Support\Js::from($idHalaman) }})">
                            <span>Pilih semua di halaman ini</span>
                        </label>
                    </div>
                @endif

                @if ($tampilan === 'daftar')
                    <div class="tm-daftar-kepala">
                        <button type="button" class="{{ $urut === 'nama' ? 'is-aktif' : '' }}" wire:click="$set('urut', 'nama')">
                            Pengirim @if ($urut === 'nama')<i class="bi bi-caret-down-fill"></i>@endif
                        </button>
                        <button type="button" class="{{ in_array($urut, ['baru', 'lama'], true) ? 'is-aktif' : '' }}" wire:click="$set('urut', '{{ $urut === 'baru' ? 'lama' : 'baru' }}')">
                            Isi testimoni · {{ $urut === 'lama' ? 'terlama' : 'terbaru' }}
                            @if (in_array($urut, ['baru', 'lama'], true))<i class="bi bi-caret-down-fill"></i>@endif
                        </button>
                        <span>Tindakan</span>
                    </div>
                @endif

                <div class="tm-rak {{ $tampilan === 'daftar' ? 'is-daftar' : '' }}">
                    @foreach ($Testimoni as $item)
                        @php
                            [$stLencana, $stWarna, $stLabel] = $gayaStatus[$item->status] ?? ['is-abu', '#64748b', ucfirst($item->status)];
                            $posisi = $nomorTampil[$item->id] ?? null;
                            $tampilBeranda = $posisi && $posisi <= $maksBeranda;
                            $curiga = $item->kecurigaan($konteksCuriga);
                            $menungguHari = $item->menungguHari();
                        @endphp
                        <article class="tm-kartu {{ $item->sorot ? 'is-sorot' : '' }} {{ in_array($item->id, $pilih, true) ? 'is-dipilih' : '' }}"
                            style="--c: {{ $stWarna }}" wire:key="testi-{{ $item->id }}">
                            <div class="tm-kepala">
                                @if ($bolehUbah || ($arsip && $bolehHapus))
                                    <label class="tm-centang is-kartu" title="Pilih untuk aksi massal">
                                        <input type="checkbox" value="{{ $item->id }}" wire:model.live="pilih">
                                    </label>
                                @endif
                                <button type="button" class="tm-avatar-tombol" wire:click="lihat('{{ $item->id }}')" style="border: 0; padding: 0; background: none;" title="Lihat detail">
                                    @include('livewire.pages.admin.testimoni.partials.avatar', ['item' => $item])
                                </button>
                                <div class="tm-kepala-teks">
                                    <p class="tm-nama">{!! \App\Support\SorotKata::pada($item->nama, $searchTestimoni) !!}</p>
                                    @if ($item->peran)
                                        <span class="tm-peran">{{ $item->peran }}</span>
                                    @endif
                                    <span class="tm-bintang" aria-label="Rating {{ (int) $item->rating }} dari 5">
                                        @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star-fill {{ $i <= (int) $item->rating ? '' : 'is-kosong' }}"></i>@endfor
                                    </span>
                                </div>
                                <span class="dsb-lencana {{ $stLencana }}">{{ $stLabel }}</span>
                            </div>

                            <div class="tm-isi">
                                {{-- Penanda cepat: posisi tayang, sorotan, umur antrean, kecurigaan. --}}
                                <div class="tm-penanda">
                                    @if ($item->sorot)
                                        <span class="tm-tanda is-sorot"><i class="bi bi-star-fill"></i>Disorot</span>
                                    @endif
                                    @if ($item->status === 'active')
                                        @if ($tampilBeranda)
                                            <span class="tm-tanda is-tayang"><i class="bi bi-display"></i>Beranda #{{ $posisi }}</span>
                                        @elseif ($item->tersembunyiKarenaRating())
                                            <span class="tm-tanda is-diam"><i class="bi bi-eye-slash"></i>Bintang {{ $item->rating }} — tidak tampil</span>
                                        @else
                                            <span class="tm-tanda is-diam"><i class="bi bi-eye-slash"></i>Di luar {{ $maksBeranda }} teratas</span>
                                        @endif
                                    @endif
                                    @if ($item->sudahDihubungi())
                                        <span class="tm-tanda is-dihubungi"><i class="bi bi-whatsapp"></i>Sudah dihubungi</span>
                                    @endif
                                    @if ($menungguHari >= 1)
                                        <span class="tm-tanda {{ $menungguHari >= 3 ? 'is-lama' : 'is-tunggu' }}">
                                            <i class="bi bi-clock-history"></i>Menunggu {{ $menungguHari }} hari
                                        </span>
                                    @endif
                                    @foreach ($curiga as $alasan)
                                        <span class="tm-tanda is-curiga"><i class="bi bi-exclamation-triangle-fill"></i>{{ $alasan }}</span>
                                    @endforeach
                                </div>

                                <p class="tm-pesan">{!! \App\Support\SorotKata::pada(\Illuminate\Support\Str::limit($item->pesan, 200), $searchTestimoni) !!}</p>
                                @if (200 < mb_strlen((string) $item->pesan))
                                    <button type="button" class="tm-baca" wire:click="lihat('{{ $item->id }}')">Baca selengkapnya</button>
                                @endif
                                @include('livewire.pages.admin.testimoni.partials.lencana-keaslian', ['item' => $item])
                                <div class="tm-waktu">
                                    @if ($item->no_hp)
                                        <span class="tm-nomor-daftar"><i class="bi bi-whatsapp"></i>{{ $item->no_hp }}</span>
                                    @endif
                                    {{ $item->source === 'customer' ? 'Dikirim' : 'Diinput admin' }} {{ $item->created_at?->locale('id')->diffForHumans() }}
                                    @if ($arsip && $item->deleted_at)
                                        · diarsipkan {{ $item->deleted_at->locale('id')->diffForHumans() }}
                                    @endif
                                </div>
                            </div>

                            <div class="tm-aksi">
                                @if ($arsip)
                                    @if ($bolehHapus)
                                        <div class="tm-aksi-moderasi">
                                            <button type="button" class="tm-btn is-setuju tm-konfirmasi" data-action="pulihkan" data-arg="{{ $item->id }}" data-icon="question"
                                                data-title="Pulihkan testimoni ini?" data-text="Testimoni {{ $item->nama }} dikembalikan dengan status terakhirnya." data-confirm="Ya, pulihkan">
                                                <i class="bi bi-arrow-counterclockwise"></i><span>Pulihkan</span>
                                            </button>
                                            <button type="button" class="tm-btn is-tolak tm-konfirmasi" data-action="buangPermanen" data-arg="{{ $item->id }}" data-icon="warning"
                                                data-title="Buang permanen?" data-text="Testimoni {{ $item->nama }} beserta fotonya dihapus dan tidak bisa dikembalikan." data-confirm="Ya, buang">
                                                <i class="bi bi-trash3"></i><span>Buang</span>
                                            </button>
                                        </div>
                                    @endif
                                @else
                                    @if ($bolehUbah)
                                        {{-- Setujui/Tolak lewat konfirmasi: menyetujui menampilkan testimoni di
                                             publik DAN bisa menjadikan pengirimnya member. --}}
                                        <div class="tm-aksi-moderasi">
                                            @if ($item->status !== 'active')
                                                <button type="button" class="tm-btn is-setuju tm-konfirmasi"
                                                    data-action="approve" data-arg="{{ $item->id }}" data-icon="question"
                                                    data-title="Setujui testimoni ini?"
                                                    data-text="Testimoni {{ $item->nama }} akan TAMPIL DI PUBLIK{{ $item->customer && $item->customer->status_member !== 'active' ? ' dan pengirimnya otomatis menjadi MEMBER' : '' }}."
                                                    data-confirm="Ya, setujui">
                                                    <i class="bi bi-check-lg"></i><span>Setujui</span>
                                                </button>
                                            @endif
                                            @if ($item->status !== 'non-active')
                                                <button type="button" class="tm-btn is-tolak" wire:click="bukaTolak('{{ $item->id }}')">
                                                    <i class="bi bi-x-lg"></i><span>{{ $item->status === 'active' ? 'Sembunyikan' : 'Tolak' }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="tm-aksi-lain">
                                        @if ($bolehUbah && $item->status === 'active')
                                            <button type="button" class="tm-btn tm-btn-ikon {{ $item->sorot ? 'is-sorot' : '' }}" wire:click="alihSorot('{{ $item->id }}')"
                                                title="{{ $item->sorot ? 'Lepas sorotan' : 'Sorot ke beranda' }}" aria-label="Sorot testimoni">
                                                <i class="bi {{ $item->sorot ? 'bi-star-fill' : 'bi-star' }}"></i>
                                            </button>
                                            @if (! $item->tersembunyiKarenaRating())
                                                <span class="tm-geser">
                                                    <button type="button" class="tm-btn tm-btn-ikon" wire:click="naikkanKeAtas('{{ $item->id }}')" title="Jadikan urutan pertama" aria-label="Jadikan urutan pertama di beranda"><i class="bi bi-chevron-double-up"></i></button>
                                                    <button type="button" class="tm-btn tm-btn-ikon" wire:click="geser('{{ $item->id }}', 'naik')" title="Naikkan urutan" aria-label="Naikkan urutan"><i class="bi bi-chevron-up"></i></button>
                                                    <button type="button" class="tm-btn tm-btn-ikon" wire:click="geser('{{ $item->id }}', 'turun')" title="Turunkan urutan" aria-label="Turunkan urutan"><i class="bi bi-chevron-down"></i></button>
                                                </span>
                                            @endif
                                        @endif
                                        <button type="button" class="tm-btn tm-btn-ikon" wire:click="lihat('{{ $item->id }}')" title="Detail" aria-label="Detail testimoni"><i class="bi bi-eye"></i></button>
                                        @if ($bolehUbah)
                                            <a wire:navigate href="{{ route('admin.testimoni.edit', $item) }}" class="tm-btn tm-btn-ikon" title="Ubah" aria-label="Ubah testimoni"><i class="bi bi-pencil"></i></a>
                                        @endif
                                        @if ($bolehHapus)
                                            <button type="button" class="tm-btn tm-btn-ikon is-bahaya tm-hapus" data-id="{{ $item->id }}" data-nama="{{ $item->nama }}" title="Arsipkan" aria-label="Arsipkan testimoni"><i class="bi bi-trash3"></i></button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($Testimoni->hasPages())
                    <div class="tm-halaman">{{ $Testimoni->links('vendor.pagination') }}</div>
                @endif
            @endif
        </section>
    </div>

    {{-- ================== JENDELA DETAIL ================== --}}
    @if ($detail)
        @php [$dLencana, $dWarna, $dLabel] = $gayaStatus[$detail->status] ?? ['is-abu', '#64748b', ucfirst($detail->status)]; @endphp
        <div class="ts-modal-back" wire:click="tutupLihat"></div>
        <div class="ts-modal" wire:key="testi-detail-{{ $detail->id }}">
            <div class="ts-modal-card dsb is-datar tm-jendela" role="dialog" aria-modal="true" aria-label="Detail testimoni" tabindex="-1"
                x-on:keydown.escape.window="$wire.tutupLihat()"
                x-on:keydown.arrow-left.window="$wire.detailTetangga(-1)"
                x-on:keydown.arrow-right.window="$wire.detailTetangga(1)"
                {{-- Pintasan hanya berlaku bila fokus tidak sedang di dalam isian. --}}
                x-on:keydown.window="
                    if (['INPUT','TEXTAREA','SELECT'].includes($event.target.tagName) || $event.metaKey || $event.ctrlKey) return;
                    if ($event.key === 's' || $event.key === 'S') { $event.preventDefault(); $wire.approve(@js($detail->id)); }
                    if ($event.key === 't' || $event.key === 'T') { $event.preventDefault(); $wire.bukaTolak(@js($detail->id)); }
                ">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: {{ $dWarna }}"><i class="bi bi-chat-quote-fill"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">Detail Testimoni</h5>
                        <span class="dsb-kartu-sub">{{ $detail->source === 'customer' ? 'Dikirim pelanggan' : 'Diinput admin' }} · {{ $detail->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                    </span>
                    <span class="tm-pintasan" aria-hidden="true">← → pindah · <b>S</b> setujui · <b>T</b> tolak</span>
                    <span class="tm-jendela-nav">
                        <button type="button" class="tm-btn tm-btn-ikon" wire:click="detailTetangga(-1)" title="Sebelumnya (←)" aria-label="Testimoni sebelumnya"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="tm-btn tm-btn-ikon" wire:click="detailTetangga(1)" title="Berikutnya (→)" aria-label="Testimoni berikutnya"><i class="bi bi-chevron-right"></i></button>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="tutupLihat" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="dsb-jendela-isi tm-detail">
                    <div class="tm-detail-profil">
                        @include('livewire.pages.admin.testimoni.partials.avatar', ['item' => $detail])
                        <div>
                            <b>{{ $detail->nama }}</b>
                            <span>{{ $detail->peran ?: 'Tanpa peran' }}</span>
                            <span class="tm-bintang">
                                @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star-fill {{ $i <= (int) $detail->rating ? '' : 'is-kosong' }}"></i>@endfor
                            </span>
                        </div>
                        <span class="dsb-lencana {{ $dLencana }}" style="margin-left: auto;">{{ $dLabel }}</span>
                    </div>

                    @if ($detail->kecurigaan())
                        <div class="tm-peringatan">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>
                                <b>Perlu diperiksa</b>
                                <span>{{ implode(' · ', $detail->kecurigaan()) }}</span>
                            </div>
                        </div>
                    @endif

                    <blockquote class="tm-kutip">{{ $detail->pesan }}</blockquote>

                    <div>
                        <span class="tm-detail-label">Keaslian pengirim</span>
                        @include('livewire.pages.admin.testimoni.partials.lencana-keaslian', ['item' => $detail])
                    </div>

                    @if ($produkDibeli->isNotEmpty())
                        <div>
                            <span class="tm-detail-label">Pernah membeli</span>
                            <div class="tm-chip-deret">
                                @foreach ($produkDibeli as $produk)
                                    <span class="dsb-lencana is-biru"><i class="bi bi-bag-check"></i>{{ $produk }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="tm-info">
                        <div><small>Tampil di publik sebagai</small><b>{{ $detail->nama_publik }}</b></div>
                        <div><small>Nomor WhatsApp</small><b>{{ $detail->no_hp ?: '—' }}</b></div>
                        <div>
                            <small>Tampil di beranda</small>
                            <b>
                                @php $dPosisi = $nomorTampil[$detail->id] ?? null; @endphp
                                @if ($detail->status !== 'active')
                                    Belum — {{ $dLabel }}
                                @elseif ($dPosisi && $dPosisi <= $maksBeranda)
                                    Ya, urutan #{{ $dPosisi }}
                                @elseif ($detail->tersembunyiKarenaRating())
                                    Tidak (bintang {{ $detail->rating }})
                                @else
                                    Tidak (di luar {{ $maksBeranda }} teratas)
                                @endif
                            </b>
                        </div>
                        <div>
                            <small>Ditinjau admin</small>
                            <b>
                                @if ($detail->ditinjau_at)
                                    {{ $detail->ditinjau_at->locale('id')->translatedFormat('d M Y, H:i') }}{{ $detail->peninjau ? ' · '.$detail->peninjau->name : '' }}
                                @elseif ($detail->status === 'pending')
                                    Belum ditinjau
                                @else
                                    {{-- Sudah disetujui/ditolak tapi tanpa catatan: keputusannya diambil
                                         sebelum jejak moderasi ada. "Belum ditinjau" di sini menyesatkan. --}}
                                    Tidak tercatat
                                @endif
                            </b>
                            @if (! $detail->ditinjau_at && $detail->status !== 'pending')
                                <small class="tm-info-ket">Diputuskan sebelum jejak moderasi dicatat.</small>
                            @endif
                        </div>
                    </div>

                    @if ($detail->foto && \Storage::disk('public')->exists('img/testimoni/'.$detail->foto))
                        <div>
                            <span class="tm-detail-label">Foto pengirim</span>
                            <a href="{{ asset('storage/img/testimoni/'.$detail->foto) }}" target="_blank" rel="noopener" class="tm-foto-besar" title="Buka ukuran penuh">
                                <img src="{{ asset('storage/img/testimoni/'.$detail->foto) }}" alt="Foto {{ $detail->nama_publik }}">
                            </a>
                        </div>
                    @endif

                    @if ($riwayatDetail->isNotEmpty())
                        <div>
                            <span class="tm-detail-label">Jejak moderasi</span>
                            <ol class="tm-jejak">
                                @foreach ($riwayatDetail as $jejak)
                                    @php [$jIkon, $jWarna, $jLabel] = $jejak->tampilan(); @endphp
                                    <li>
                                        <span class="tm-jejak-ikon" style="--c: {{ $jWarna }}"><i class="bi {{ $jIkon }}"></i></span>
                                        <span class="tm-jejak-teks">
                                            <b>{{ $jLabel }}</b>
                                            @if ($jejak->keterangan)<span>{{ $jejak->keterangan }}</span>@endif
                                            <small>{{ $jejak->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}{{ $jejak->user ? ' · '.$jejak->user->name : '' }}</small>
                                        </span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    @if ($detail->alasan_tolak)
                        <div class="tm-alasan">
                            <span class="tm-detail-label">Alasan penolakan</span>
                            <p>{{ $detail->alasan_tolak }}</p>
                        </div>
                    @endif
                </div>
                <div class="dsb-jendela-kaki tm-detail-kaki">
                    <button type="button" class="tm-btn tm-salin" data-teks="{{ $detail->pesan }}" title="Salin isi testimoni">
                        <i class="bi bi-clipboard"></i><span>Salin teks</span>
                    </button>
                    @if ($detail->tautanWa())
                        <a href="{{ $detail->tautanWa('Halo '.$detail->nama.', terima kasih atas testimoninya untuk Phoenix Digital 🙏') }}" target="_blank" rel="noopener" class="tm-btn is-wa">
                            <i class="bi bi-whatsapp"></i><span>Balas WhatsApp</span>
                        </a>
                        @if ($bolehUbah)
                            <button type="button" class="tm-btn {{ $detail->sudahDihubungi() ? 'is-dihubungi' : '' }}" wire:click="alihDihubungi('{{ $detail->id }}')"
                                title="{{ $detail->sudahDihubungi() ? 'Lepas penanda' : 'Tandai supaya tidak dihubungi dua kali' }}">
                                <i class="bi {{ $detail->sudahDihubungi() ? 'bi-check2-circle' : 'bi-circle' }}"></i>
                                <span>{{ $detail->sudahDihubungi() ? 'Sudah dihubungi' : 'Tandai dihubungi' }}</span>
                            </button>
                        @endif
                    @endif
                    @if ($detail->no_hp && $bolehPelanggan)
                        <a wire:navigate href="{{ route('admin.customer.index', ['searchCustomer' => $detail->no_hp_cari]) }}" class="tm-btn" title="Buka Data Pelanggan untuk nomor ini">
                            <i class="bi bi-person-lines-fill"></i><span>Data Pelanggan</span>
                        </a>
                    @endif
                    @if ($bolehUbah && ! $detail->trashed())
                        <a wire:navigate href="{{ route('admin.testimoni.edit', $detail) }}" class="tm-btn"><i class="bi bi-pencil"></i><span>Ubah</span></a>
                        @if ($detail->status !== 'non-active')
                            <button type="button" class="tm-btn is-tolak" wire:click="bukaTolak('{{ $detail->id }}')">
                                <i class="bi bi-x-lg"></i><span>{{ $detail->status === 'active' ? 'Sembunyikan' : 'Tolak' }}</span>
                            </button>
                        @endif
                        @if ($detail->status !== 'active')
                            <button type="button" class="tm-btn is-setuju tm-konfirmasi" data-action="approve" data-arg="{{ $detail->id }}" data-icon="question"
                                data-title="Setujui testimoni ini?"
                                data-text="Testimoni {{ $detail->nama }} akan TAMPIL DI PUBLIK{{ $detail->customer && $detail->customer->status_member !== 'active' ? ' dan pengirimnya otomatis menjadi MEMBER' : '' }}."
                                data-confirm="Ya, setujui">
                                <i class="bi bi-check-lg"></i><span>Setujui</span>
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ================== JENDELA ALASAN TOLAK ================== --}}
    @if ($tolakId)
        @php $massal = $tolakId === 'massal'; @endphp
        <div class="ts-modal-back" wire:click="tutupTolak"></div>
        <div class="ts-modal">
            <div class="ts-modal-card dsb is-datar tm-jendela is-ramping" role="dialog" aria-modal="true" aria-label="Alasan penolakan"
                x-on:keydown.escape.window="$wire.tutupTolak()">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #dc2626"><i class="bi bi-x-circle-fill"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">{{ $massal ? 'Tolak '.count($pilih).' testimoni' : 'Tolak testimoni' }}</h5>
                        <span class="dsb-kartu-sub">{{ $massal ? 'Alasan yang sama dipakai untuk semua yang dipilih.' : ($tolak?->nama ? 'Kiriman '.$tolak->nama : '') }}</span>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="tutupTolak" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="dsb-jendela-isi">
                    <span class="tm-detail-label">Alasan (disimpan sebagai catatan admin)</span>
                    <div class="tm-alasan-pilihan">
                        @foreach (\App\Livewire\Pages\Admin\Testimoni\TestimoniList::ALASAN_TOLAK as $a)
                            <button type="button" class="tm-alasan-chip {{ $tolakAlasan === $a ? 'is-aktif' : '' }}" wire:click="$set('tolakAlasan', @js($a))">{{ $a }}</button>
                        @endforeach
                    </div>
                    <input type="text" class="dsb-isian" wire:model="tolakAlasan" maxlength="191" placeholder="Atau tulis alasan sendiri (opsional)…">
                    <p class="tm-catatan-kecil">Testimoni disembunyikan dari publik. Keanggotaan yang sudah diberikan tidak dicabut.</p>
                </div>
                <div class="dsb-jendela-kaki">
                    <button type="button" class="tm-btn" wire:click="tutupTolak">Batal</button>
                    <button type="button" class="tm-btn is-tolak is-tegas" wire:click="{{ $massal ? 'tolakTerpilih' : 'reject' }}">
                        <i class="bi bi-x-lg"></i><span>{{ $massal ? 'Tolak semua' : 'Tolak testimoni' }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================== PRATINJAU URUTAN BERANDA ================== --}}
    @if ($pratinjauBeranda)
        <div class="ts-modal-back" wire:click="$set('pratinjauBeranda', false)"></div>
        <div class="ts-modal">
            <div class="ts-modal-card dsb is-datar tm-jendela" role="dialog" aria-modal="true" aria-label="Pratinjau urutan beranda"
                x-on:keydown.escape.window="$wire.set('pratinjauBeranda', false)">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-display"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">Urutan di beranda</h5>
                        <span class="dsb-kartu-sub">Beginilah pengunjung melihatnya, dari kiri ke kanan.</span>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="$set('pratinjauBeranda', false)" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="dsb-jendela-isi">
                    @if ($urutanBeranda->isEmpty())
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-display"></i></span>
                            <p class="dsb-kosong-judul">Beranda belum menampilkan testimoni</p>
                            <p class="dsb-kosong-ket">Setujui testimoni bintang {{ \App\Models\Testimoni::RATING_MIN_TAMPIL }} ke atas, atau sorot yang bintangnya lebih rendah.</p>
                        </div>
                    @else
                        <ol class="tm-pratinjau-daftar">
                            @foreach ($urutanBeranda as $i => $t)
                                <li>
                                    <span class="tm-pratinjau-no">{{ $i + 1 }}</span>
                                    @include('livewire.pages.admin.testimoni.partials.avatar', ['item' => $t])
                                    <span class="tm-pratinjau-teks">
                                        <b>{{ $t->nama_publik }}</b>
                                        <span class="tm-bintang">@for ($b = 1; $b <= 5; $b++)<i class="bi bi-star-fill {{ $b <= (int) $t->rating ? '' : 'is-kosong' }}"></i>@endfor</span>
                                        <small>{{ \Illuminate\Support\Str::limit($t->pesan, 90) }}</small>
                                    </span>
                                    @if ($t->sorot)
                                        <span class="tm-tanda is-sorot"><i class="bi bi-star-fill"></i>Disorot</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                        <p class="tm-catatan-kecil">Nama yang tampil sudah memperhitungkan pengirim anonim.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ================== AKTIVITAS MODERASI ================== --}}
    @if ($lihatAktivitas)
        <div class="ts-modal-back" wire:click="$set('lihatAktivitas', false)"></div>
        <div class="ts-modal">
            <div class="ts-modal-card dsb is-datar tm-jendela" role="dialog" aria-modal="true" aria-label="Aktivitas moderasi"
                x-on:keydown.escape.window="$wire.set('lihatAktivitas', false)">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #0ea5e9"><i class="bi bi-clock-history"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">Aktivitas moderasi</h5>
                        <span class="dsb-kartu-sub">Keputusan terbaru dari semua testimoni.</span>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="$set('lihatAktivitas', false)" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="dsb-jendela-isi">
                    @if ($aktivitas->isEmpty())
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-clock-history"></i></span>
                            <p class="dsb-kosong-judul">Belum ada aktivitas</p>
                            <p class="dsb-kosong-ket">Jejak tercatat sejak keputusan moderasi berikutnya.</p>
                        </div>
                    @else
                        <ol class="tm-jejak">
                            @foreach ($aktivitas as $jejak)
                                @php [$aIkon, $aWarna, $aLabel] = $jejak->tampilan(); @endphp
                                <li>
                                    <span class="tm-jejak-ikon" style="--c: {{ $aWarna }}"><i class="bi {{ $aIkon }}"></i></span>
                                    <span class="tm-jejak-teks">
                                        <b>{{ $aLabel }} — {{ $jejak->testimoni?->nama ?? 'testimoni terhapus' }}</b>
                                        @if ($jejak->keterangan)<span>{{ $jejak->keterangan }}</span>@endif
                                        <small>{{ $jejak->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}{{ $jejak->user ? ' · '.$jejak->user->name : ' · sistem' }}</small>
                                    </span>
                                    @if ($jejak->testimoni)
                                        <button type="button" class="tm-btn tm-btn-ikon" wire:click="$set('lihatAktivitas', false); lihat('{{ $jejak->testimoni->id }}')" title="Buka testimoninya"><i class="bi bi-box-arrow-up-right"></i></button>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @include('livewire.layout.sweetalert')

    @push('scripts')
        <script>
            // Dipasang SEKALI di dokumen (bukan body): wire:navigate mengganti <body>.
            if (!window.__testimoniDaftarTerpasang) {
                window.__testimoniDaftarTerpasang = true;
                const gaya = {
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel', title: 'swal-glossy-title' },
                    buttonsStyling: false,
                };
                // execCommand: peramban lama & konteks tanpa HTTPS tidak punya
                // navigator.clipboard, dan tombol Salin harus tetap bekerja.
                const salinCadangan = (teks, selesai) => {
                    const ta = document.createElement('textarea');
                    ta.value = teks;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.left = '-9999px';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); selesai(); } catch (e) { /* diam: tak ada yang bisa dilakukan */ }
                    document.body.removeChild(ta);
                };
                const panggil = (el, metode, arg) => {
                    const komponen = el.closest('[wire\\:id]');
                    if (!komponen) return;
                    const hidup = Livewire.find(komponen.getAttribute('wire:id'));
                    // Tanpa arg: aksi massal (setujuiTerpilih) tidak menerima parameter.
                    if (arg === undefined) hidup.call(metode); else hidup.call(metode, arg);
                };
                document.addEventListener('click', (e) => {
                    if (typeof Swal === 'undefined') return;
                    // Setujui / Tolak / Pulihkan — teks dibaca dari data-* tombolnya.
                    const tombol = e.target.closest('.tm-konfirmasi');
                    if (tombol) {
                        e.preventDefault();
                        Swal.fire({
                            title: tombol.dataset.title || 'Lanjutkan?', text: tombol.dataset.text || '',
                            icon: tombol.dataset.icon || 'question', showCancelButton: true,
                            confirmButtonText: tombol.dataset.confirm || 'Ya', cancelButtonText: 'Batal', ...gaya,
                        }).then((r) => { if (r.isConfirmed) panggil(tombol, tombol.dataset.action, tombol.dataset.arg); });
                        return;
                    }
                        const salin = e.target.closest('.tm-salin');
                    if (salin) {
                        e.preventDefault();
                        const teks = salin.dataset.teks || '';
                        const sudah = () => Swal.fire({ title: 'Tersalin', text: 'Isi testimoni sudah disalin.', icon: 'success', timer: 1600, showConfirmButton: false, ...gaya });
                        if (navigator.clipboard?.writeText) {
                            navigator.clipboard.writeText(teks).then(sudah).catch(() => salinCadangan(teks, sudah));
                        } else {
                            salinCadangan(teks, sudah);
                        }
                        return;
                    }
                    const hapus = e.target.closest('.tm-hapus');
                    if (hapus) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Arsipkan testimoni ini?',
                            text: 'Testimoni dari ' + (hapus.dataset.nama || '') + ' dipindahkan ke Arsip dan bisa dipulihkan.',
                            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, arsipkan', cancelButtonText: 'Batal', ...gaya,
                        }).then((r) => { if (r.isConfirmed) panggil(hapus, 'deleteTestimoni', hapus.dataset.id); });
                    }
                });
                window.addEventListener('testimoni-deleted', () => {
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Diarsipkan', text: 'Testimoni dipindahkan ke Arsip.', icon: 'success', timer: 2000, showConfirmButton: false, ...gaya });
                });
                window.addEventListener('testimoni-deleteError', (e) => {
                    const d = Array.isArray(e.detail) ? (e.detail[0] || {}) : (e.detail || {});
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Gagal', text: d.message || 'Testimoni gagal diarsipkan.', icon: 'error', timer: 2500, showConfirmButton: false, ...gaya });
                });
            }
        </script>
    @endpush
</div>
