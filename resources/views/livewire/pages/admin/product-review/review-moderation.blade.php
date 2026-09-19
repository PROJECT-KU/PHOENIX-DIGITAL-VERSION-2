@section('title')
Moderasi Ulasan Produk || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.product-review.partials.ulasan-gaya')

    @php
        $bolehUbah = (bool) auth()->user()?->hasPermission('edit_productreview');
        $bolehHapus = (bool) auth()->user()?->hasPermission('delete_productreview');
        $kartuStatus = [
            'pending' => ['Menunggu', 'bi-hourglass-split', '#d97706'],
            'approved' => ['Disetujui', 'bi-check-circle-fill', '#16a34a'],
            'hidden' => ['Disembunyikan', 'bi-eye-slash-fill', '#64748b'],
            'all' => ['Semua', 'bi-star-fill', '#7c3aed'],
        ];
        $kosong = [
            'pending' => ['bi-inbox', 'Tidak ada yang menunggu', 'Semua ulasan sudah ditinjau.'],
            'approved' => ['bi-star', 'Belum ada ulasan disetujui', 'Ulasan yang disetujui tampil di halaman produknya.'],
            'hidden' => ['bi-eye-slash', 'Tidak ada ulasan disembunyikan', 'Ulasan yang disembunyikan tidak tampil di halaman produk.'],
            'all' => ['bi-star', 'Belum ada ulasan', 'Ulasan muncul setelah pembeli menulisnya di halaman produk.'],
        ];
        $idHalaman = $reviews->pluck('id')->map(fn ($i) => (string) $i)->all();
        $semuaTercentang = $idHalaman && ! array_diff($idHalaman, $pilih);
        $sasaranMuat = 'search,setFilter,gotoPage,nextPage,previousPage,fRating,fJenis,fDari,fSampai,urut,perHalaman,resetSaring';
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Moderasi Ulasan Produk</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Ulasan pembeli ditinjau di sini — yang disetujui tampil di halaman produk & paket.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <button type="button" class="dsb-tombol" wire:click="$set('lihatRingkasan', true)">
                    <i class="bi bi-bar-chart-line"></i><span>Per Produk</span>
                </button>
                <button type="button" class="dsb-tombol" wire:click="unduhExcel" wire:loading.attr="disabled" wire:target="unduhExcel">
                    <span wire:loading.remove wire:target="unduhExcel" class="ul-isi-tombol"><i class="bi bi-file-earmark-excel"></i><span>Excel</span></span>
                    <span wire:loading.inline-flex wire:target="unduhExcel" class="ul-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
                <button type="button" class="dsb-tombol" wire:click="unduhPdf" wire:loading.attr="disabled" wire:target="unduhPdf">
                    <span wire:loading.remove wire:target="unduhPdf" class="ul-isi-tombol"><i class="bi bi-file-earmark-pdf"></i><span>PDF</span></span>
                    <span wire:loading.inline-flex wire:target="unduhPdf" class="ul-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
            </div>
        </header>

        {{-- ================== STATUS (sekaligus tab moderasi) ================== --}}
        <nav class="ul-status" aria-label="Saring menurut status moderasi">
            @foreach ($kartuStatus as $nilai => [$label, $ikon, $warna])
                <button type="button" class="ul-status-btn {{ $filter === $nilai ? 'is-aktif' : '' }} {{ $nilai === 'pending' && 0 < $tabCounts['pending'] ? 'is-perlu' : '' }}"
                    style="--c: {{ $warna }}" wire:click="setFilter('{{ $nilai }}')" aria-pressed="{{ $filter === $nilai ? 'true' : 'false' }}">
                    <span class="ul-status-ikon"><i class="bi {{ $ikon }}"></i></span>
                    <span class="ul-status-teks"><b>{{ $tabCounts[$nilai] }}</b><span>{{ $label }}</span></span>
                </button>
            @endforeach
            @if ($tabCounts['arsip'] || $arsip)
                <button type="button" class="ul-status-btn {{ $arsip ? 'is-aktif' : '' }}" style="--c: #64748b"
                    wire:click="$set('arsip', {{ $arsip ? 'false' : 'true' }})" aria-pressed="{{ $arsip ? 'true' : 'false' }}">
                    <span class="ul-status-ikon"><i class="bi bi-archive-fill"></i></span>
                    <span class="ul-status-teks"><b>{{ $tabCounts['arsip'] }}</b><span>Arsip</span></span>
                </button>
            @endif
        </nav>

        {{-- ================== SEBARAN BINTANG ================== --}}
        <section class="dsb-kartu ul-sebaran">
            <div class="dsb-kartu-isi ul-sebaran-isi">
                <div class="ul-sebaran-nilai">
                    <b>{{ $rataRating ? number_format($rataRating, 1, ',', '.') : '–' }}</b>
                    <span class="ul-bintang" aria-hidden="true">
                        @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star-fill {{ $i <= round($rataRating) ? '' : 'is-kosong' }}"></i>@endfor
                    </span>
                    <small>Rata-rata dari {{ $tabCounts['approved'] }} ulasan disetujui</small>
                </div>
                <div class="ul-sebaran-bar">
                    @foreach ($sebaran as $barBintang => $bar)
                        <div class="ul-bar-baris">
                            <span class="ul-bar-label">{{ $barBintang }}<i class="bi bi-star-fill"></i></span>
                            <span class="ul-bar-alur"><span class="ul-bar-isi" style="width: {{ $bar['persen'] }}%"></span></span>
                            <span class="ul-bar-nilai">{{ $bar['jumlah'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="ul-sebaran-catatan">
                    <span class="ul-sebaran-ikon"><i class="bi bi-shop"></i></span>
                    <div>
                        <p><b>{{ $tabCounts['approved'] }}</b> ulasan tampil di halaman produk & paket.</p>
                        <p class="ul-catatan-kecil">Ulasan yang menunggu atau disembunyikan tidak dilihat pengunjung.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== CARI & SARING ================== --}}
        <section class="dsb-kartu ul-saring" x-data="{ buka: @js($this->adaSaring) }">
            <div class="dsb-kartu-isi ul-saring-isi">
                <div class="dsb-cari">
                    <i class="bi bi-search"></i>
                    <input type="search" class="dsb-isian" wire:model.live.debounce.300ms="search" placeholder="Cari produk, nama pengulas, atau isi ulasan…" aria-label="Cari ulasan">
                    @if ($search)
                        <button type="button" class="dsb-cari-hapus" wire:click="$set('search', '')" title="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    @endif
                </div>

                <select class="dsb-isian ul-pilih" wire:model.live="urut" aria-label="Urutkan">
                    <option value="baru" @selected($urut === 'baru')>Terbaru</option>
                    <option value="lama" @selected($urut === 'lama')>Terlama</option>
                    <option value="tinggi" @selected($urut === 'tinggi')>Bintang tertinggi</option>
                    <option value="rendah" @selected($urut === 'rendah')>Bintang terendah</option>
                </select>

                <select class="dsb-isian ul-pilih is-sempit" wire:model.live="perHalaman" aria-label="Jumlah per halaman">
                    @foreach ([12, 24, 48] as $n)
                        <option value="{{ $n }}" @selected($perHalaman === $n)>{{ $n }}/halaman</option>
                    @endforeach
                </select>

                <button type="button" class="ul-btn {{ $this->adaSaring ? 'is-aktif' : '' }}" x-on:click="buka = !buka" aria-label="Saringan lanjutan">
                    <i class="bi bi-funnel"></i><span>Saring</span>
                    @if ($this->adaSaring)<span class="ul-saring-titik"></span>@endif
                </button>

                {{-- Tampilan padat: meninjau puluhan ulasan lebih cepat lewat daftar. --}}
                <span class="ul-tampilan" role="group" aria-label="Tampilan daftar">
                    <button type="button" class="{{ $tampilan === 'kartu' ? 'is-aktif' : '' }}" wire:click="$set('tampilan', 'kartu')" title="Tampilan kartu" aria-label="Tampilan kartu"><i class="bi bi-grid"></i></button>
                    <button type="button" class="{{ $tampilan === 'daftar' ? 'is-aktif' : '' }}" wire:click="$set('tampilan', 'daftar')" title="Tampilan daftar" aria-label="Tampilan daftar"><i class="bi bi-list-ul"></i></button>
                </span>

                <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="{{ $sasaranMuat }}">
                    <span class="dsb-putar is-kecil"></span>Memuat…
                </span>
            </div>

            @if ($pilih)
                <p class="ul-ekspor-ket"><i class="bi bi-info-circle"></i> Unduhan akan berisi {{ count($pilih) }} ulasan yang dicentang saja.</p>
            @endif

            @if ($this->chipSaring)
                <div class="ul-chip-saring">
                    @foreach ($this->chipSaring as $chip)
                        <button type="button" class="ul-chip-lepas" wire:click="lepasSaring('{{ $chip['nama'] }}')" title="Lepas saringan ini">
                            {{ $chip['label'] }}<i class="bi bi-x-lg"></i>
                        </button>
                    @endforeach
                    <button type="button" class="ul-chip-lepas is-semua" wire:click="resetSaring">Bersihkan semua</button>
                </div>
            @endif

            <div class="ul-saring-lanjut" x-show="buka" x-collapse x-cloak>
                <div class="ul-saring-baris">
                    <label class="ul-saring-medan">
                        <span>Bintang</span>
                        <select class="dsb-isian" wire:model.live="fRating">
                            <option value="">Semua bintang</option>
                            @foreach (range(5, 1) as $b)
                                <option value="{{ $b }}">{{ $b }} bintang</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="ul-saring-medan">
                        <span>Jenis</span>
                        <select class="dsb-isian" wire:model.live="fJenis">
                            <option value="">Produk & paket</option>
                            <option value="produk">Produk satuan</option>
                            <option value="paket">Paket bundling</option>
                        </select>
                    </label>
                    <label class="ul-saring-medan">
                        <span>Ditulis dari</span>
                        <input type="date" class="dsb-isian" wire:model.live="fDari" max="{{ now()->toDateString() }}">
                    </label>
                    <label class="ul-saring-medan">
                        <span>Sampai</span>
                        <input type="date" class="dsb-isian" wire:model.live="fSampai" max="{{ now()->toDateString() }}">
                    </label>
                </div>
            </div>
        </section>

        {{-- ================== BILAH AKSI MASSAL ================== --}}
        @if ($pilih)
            <div class="ul-massal" role="region" aria-label="Aksi massal">
                <span class="ul-massal-jumlah"><b>{{ count($pilih) }}</b> dipilih</span>
                <div class="ul-massal-tombol">
                    @if ($arsip)
                        @if ($bolehHapus)
                            <button type="button" class="ul-btn is-setuju ul-konfirmasi" data-action="pulihkanTerpilih" data-icon="question"
                                data-title="Pulihkan {{ count($pilih) }} ulasan?" data-text="Semuanya kembali dengan status terakhirnya." data-confirm="Ya, pulihkan">
                                <i class="bi bi-arrow-counterclockwise"></i><span>Pulihkan</span>
                            </button>
                            <button type="button" class="ul-btn is-bahaya ul-konfirmasi" data-action="buangTerpilih" data-icon="warning"
                                data-title="Buang {{ count($pilih) }} ulasan permanen?" data-text="Tidak bisa dikembalikan lagi." data-confirm="Ya, buang">
                                <i class="bi bi-trash3"></i><span>Buang</span>
                            </button>
                        @endif
                    @else
                        @if ($bolehUbah)
                            <button type="button" class="ul-btn is-setuju ul-konfirmasi" data-action="setujuiTerpilih" data-icon="question"
                                data-title="Setujui {{ count($pilih) }} ulasan?" data-text="Semuanya akan tampil di halaman produk masing-masing." data-confirm="Ya, setujui">
                                <i class="bi bi-check-lg"></i><span>Setujui</span>
                            </button>
                            <button type="button" class="ul-btn is-sembunyi ul-konfirmasi" data-action="sembunyikanTerpilih" data-icon="warning"
                                data-title="Sembunyikan {{ count($pilih) }} ulasan?" data-text="Ulasannya tidak lagi tampil di halaman produk, tapi tidak dihapus." data-confirm="Ya, sembunyikan">
                                <i class="bi bi-eye-slash"></i><span>Sembunyikan</span>
                            </button>
                        @endif
                        @if ($bolehHapus)
                            <button type="button" class="ul-btn is-bahaya ul-konfirmasi" data-action="hapusTerpilih" data-icon="warning"
                                data-title="Arsipkan {{ count($pilih) }} ulasan?" data-text="Dipindahkan ke Arsip dan masih bisa dipulihkan." data-confirm="Ya, arsipkan">
                                <i class="bi bi-archive"></i><span>Arsipkan</span>
                            </button>
                        @endif
                    @endif
                    <button type="button" class="ul-btn" wire:click="lepasPilih"><i class="bi bi-x"></i><span>Lepas</span></button>
                </div>
            </div>
        @endif

        @if ($urungkan && $bolehUbah)
            <div class="ul-urungkan" role="status">
                <span>Ulasan <b>{{ $urungkan['nama'] }}</b> {{ $urungkan['aksi'] }}.</span>
                <button type="button" class="ul-btn" wire:click="urungkanTerakhir">
                    <i class="bi bi-arrow-counterclockwise"></i><span>Urungkan</span>
                </button>
                <button type="button" class="ul-urungkan-tutup" wire:click="tutupUrungkan" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
        @endif

        {{-- Kerangka pemuatan: tanpa ini kartu lama cuma meredup dan sekilas
             tampak seolah tidak ada yang berubah. --}}
        <div class="ul-kerangka" wire:loading.grid wire:target="{{ $sasaranMuat }}">
            @for ($i = 0; $i < 6; $i++)
                <div class="ul-kerangka-kartu">
                    <div class="ul-kerangka-kepala"><span class="ul-tulang is-kotak"></span><span class="ul-tulang" style="width: 55%"></span></div>
                    <span class="ul-tulang" style="width: 100%; height: 54px; margin-top: 12px;"></span>
                    <span class="ul-tulang" style="width: 42%; margin-top: 10px;"></span>
                </div>
            @endfor
        </div>

        {{-- ================== RAK ULASAN ================== --}}
        <section wire:loading.class="ul-sembunyi" wire:target="{{ $sasaranMuat }}">
            @if ($reviews->isEmpty())
                @php
                    [$kIkon, $kJudul, $kKet] = ($search || $this->adaSaring)
                        ? ['bi-funnel', 'Tidak ada ulasan yang cocok', 'Coba ubah kata kunci atau bersihkan saringan.']
                        : $kosong[$filter];
                @endphp
                <div class="dsb-kartu">
                    <div class="dsb-kosong">
                        <span class="dsb-kosong-ikon"><i class="bi {{ $kIkon }}"></i></span>
                        <p class="dsb-kosong-judul">{{ $kJudul }}</p>
                        <p class="dsb-kosong-ket">{{ $kKet }}</p>
                    </div>
                </div>
            @else
                <div class="ul-pilih-semua">
                    <label class="ul-centang">
                        <input type="checkbox" @checked($semuaTercentang) wire:click="pilihHalaman({{ \Illuminate\Support\Js::from($idHalaman) }})">
                        <span>Pilih semua di halaman ini</span>
                    </label>
                </div>

                <div class="ul-rak {{ $tampilan === 'daftar' ? 'is-daftar' : '' }}">
                    @foreach ($reviews as $item)
                        @php
                            [$stLabel, $stLencana, $stWarna] = $item->tampilanStatus();
                            $curiga = $item->kecurigaan();
                            $gambar = $item->gambarTarget();
                        @endphp
                        <article class="ul-kartu {{ in_array((string) $item->id, $pilih, true) ? 'is-dipilih' : '' }}"
                            style="--c: {{ $stWarna }}" wire:key="ulasan-{{ $item->id }}">
                            <div class="ul-kepala">
                                <label class="ul-centang is-kartu" title="Pilih untuk aksi massal">
                                    <input type="checkbox" value="{{ $item->id }}" wire:model.live="pilih">
                                </label>
                                <button type="button" class="ul-gambar" wire:click="lihat('{{ $item->id }}')" title="Lihat detail ulasan">
                                    @if ($gambar)
                                        <img src="{{ $gambar }}" alt="" loading="lazy">
                                    @else
                                        <i class="bi {{ $item->jenis === 'paket' ? 'bi-box-seam' : 'bi-bag' }}"></i>
                                    @endif
                                </button>
                                <div class="ul-kepala-teks">
                                    <p class="ul-produk">{!! \App\Support\SorotKata::pada($item->namaTarget(), $search) !!}</p>
                                    <span class="ul-pengulas">oleh {!! \App\Support\SorotKata::pada($item->nama, $search) !!}</span>
                                    <span class="ul-bintang" aria-label="Rating {{ (int) $item->rating }} dari 5">
                                        @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star-fill {{ $i <= (int) $item->rating ? '' : 'is-kosong' }}"></i>@endfor
                                    </span>
                                </div>
                                <span class="dsb-lencana {{ $stLencana }}">{{ $stLabel }}</span>
                            </div>

                            <div class="ul-isi">
                                <div class="ul-penanda">
                                    <span class="ul-tanda {{ $item->jenis === 'paket' ? 'is-paket' : 'is-produk' }}">
                                        <i class="bi {{ $item->jenis === 'paket' ? 'bi-box-seam' : 'bi-bag' }}"></i>{{ $item->jenis === 'paket' ? 'Paket' : 'Produk' }}
                                    </span>
                                    @if ($item->pembeliAsli())
                                        <span class="ul-tanda is-asli"><i class="bi bi-patch-check-fill"></i>Pembeli Asli</span>
                                    @endif
                                    @if ($item->status === 'approved')
                                        <span class="ul-tanda is-tayang"><i class="bi bi-shop"></i>Tampil di halaman produk</span>
                                    @endif
                                    @foreach ($curiga as $alasan)
                                        <span class="ul-tanda is-curiga"><i class="bi bi-exclamation-triangle-fill"></i>{{ $alasan }}</span>
                                    @endforeach
                                </div>

                                <p class="ul-teks">{!! \App\Support\SorotKata::pada(\Illuminate\Support\Str::limit($item->ulasan, 200), $search) !!}</p>
                                @if (200 < mb_strlen((string) $item->ulasan))
                                    <button type="button" class="ul-baca" wire:click="lihat('{{ $item->id }}')">Baca selengkapnya</button>
                                @endif
                                <div class="ul-waktu">Ditulis {{ $item->created_at?->locale('id')->diffForHumans() }}</div>
                            </div>

                            <div class="ul-aksi">
                                @if ($arsip)
                                    @if ($bolehHapus)
                                        <div class="ul-aksi-moderasi">
                                            <button type="button" class="ul-btn is-setuju ul-konfirmasi" data-action="pulihkan" data-arg="{{ $item->id }}" data-icon="question"
                                                data-title="Pulihkan ulasan ini?" data-text="Ulasan {{ $item->nama }} kembali dengan status terakhirnya." data-confirm="Ya, pulihkan">
                                                <i class="bi bi-arrow-counterclockwise"></i><span>Pulihkan</span>
                                            </button>
                                            <button type="button" class="ul-btn is-bahaya ul-konfirmasi" data-action="buangPermanen" data-arg="{{ $item->id }}" data-icon="warning"
                                                data-title="Buang permanen?" data-text="Ulasan {{ $item->nama }} tidak bisa dikembalikan." data-confirm="Ya, buang">
                                                <i class="bi bi-trash3"></i><span>Buang</span>
                                            </button>
                                        </div>
                                    @endif
                                @else
                                <div class="ul-aksi-moderasi">
                                    @if ($bolehUbah && $item->status !== 'approved')
                                        <button type="button" class="ul-btn is-setuju ul-konfirmasi"
                                            data-action="approve" data-arg="{{ $item->id }}" data-icon="question"
                                            data-title="Setujui ulasan ini?"
                                            data-text="Ulasan {{ $item->nama }} akan tampil di halaman {{ $item->namaTarget() }}."
                                            data-confirm="Ya, setujui">
                                            <i class="bi bi-check-lg"></i><span>Setujui</span>
                                        </button>
                                    @endif
                                    @if ($bolehUbah && $item->status !== 'hidden')
                                        <button type="button" class="ul-btn is-sembunyi ul-konfirmasi"
                                            data-action="reject" data-arg="{{ $item->id }}" data-icon="warning"
                                            data-title="Sembunyikan ulasan ini?"
                                            data-text="Ulasannya tidak lagi tampil di halaman produk, tapi tidak dihapus."
                                            data-confirm="Ya, sembunyikan">
                                            <i class="bi bi-eye-slash"></i><span>Sembunyikan</span>
                                        </button>
                                    @endif
                                </div>
                                <div class="ul-aksi-lain">
                                    <button type="button" class="ul-btn ul-btn-ikon" wire:click="lihat('{{ $item->id }}')" title="Detail" aria-label="Detail ulasan"><i class="bi bi-eye"></i></button>
                                    @if ($tautan = $item->tautanPublik())
                                        <a href="{{ $tautan }}" target="_blank" rel="noopener" class="ul-btn ul-btn-ikon" title="Buka halaman produknya" aria-label="Buka halaman produk"><i class="bi bi-box-arrow-up-right"></i></a>
                                    @endif
                                    @if ($bolehHapus)
                                        <button type="button" class="ul-btn ul-btn-ikon is-bahaya ul-konfirmasi"
                                            data-action="remove" data-arg="{{ $item->id }}" data-icon="warning"
                                            data-title="Arsipkan ulasan ini?" data-text="Ulasan dari {{ $item->nama }} dipindahkan ke Arsip dan masih bisa dipulihkan."
                                            data-confirm="Ya, arsipkan" title="Arsipkan" aria-label="Arsipkan ulasan"><i class="bi bi-trash3"></i></button>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($reviews->hasPages())
                    <div class="ul-halaman">{{ $reviews->links('vendor.pagination') }}</div>
                @endif
            @endif
        </section>
    </div>

    {{-- ================== JENDELA DETAIL ================== --}}
    @if ($detail)
        @php
            [$dLabel, $dLencana, $dWarna] = $detail->tampilanStatus();
            $dGambar = $detail->gambarTarget();
        @endphp
        <div class="ts-modal-back" wire:click="tutupLihat"></div>
        <div class="ts-modal" wire:key="ulasan-detail-{{ $detail->id }}">
            <div class="ts-modal-card dsb is-datar ul-jendela" role="dialog" aria-modal="true" aria-label="Detail ulasan" tabindex="-1"
                x-on:keydown.escape.window="$wire.tutupLihat()"
                x-on:keydown.arrow-left.window="$wire.detailTetangga(-1)"
                x-on:keydown.arrow-right.window="$wire.detailTetangga(1)"
                {{-- Pintasan hanya berlaku bila fokus tidak sedang di dalam isian. --}}
                x-on:keydown.window="
                    if (['INPUT','TEXTAREA','SELECT'].includes($event.target.tagName) || $event.metaKey || $event.ctrlKey) return;
                    if ($event.key === 's' || $event.key === 'S') { $event.preventDefault(); $wire.approve(@js((string) $detail->id)); }
                    if ($event.key === 't' || $event.key === 'T') { $event.preventDefault(); $wire.reject(@js((string) $detail->id)); }
                ">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: {{ $dWarna }}"><i class="bi bi-star-fill"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">Detail Ulasan</h5>
                        <span class="dsb-kartu-sub">{{ $detail->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                    </span>
                    <span class="ul-pintasan" aria-hidden="true">← → pindah · <b>S</b> setujui · <b>T</b> sembunyikan</span>
                    <span class="ul-jendela-nav">
                        <button type="button" class="ul-btn ul-btn-ikon" wire:click="detailTetangga(-1)" title="Sebelumnya (←)" aria-label="Ulasan sebelumnya"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="ul-btn ul-btn-ikon" wire:click="detailTetangga(1)" title="Berikutnya (→)" aria-label="Ulasan berikutnya"><i class="bi bi-chevron-right"></i></button>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="tutupLihat" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="dsb-jendela-isi ul-detail">
                    <div class="ul-detail-profil">
                        <span class="ul-gambar">
                            @if ($dGambar)
                                <img src="{{ $dGambar }}" alt="" loading="lazy">
                            @else
                                <i class="bi {{ $detail->jenis === 'paket' ? 'bi-box-seam' : 'bi-bag' }}"></i>
                            @endif
                        </span>
                        <div>
                            <b>{{ $detail->namaTarget() }}</b>
                            <span>{{ $detail->jenis === 'paket' ? 'Paket bundling' : 'Produk satuan' }}</span>
                            <span class="ul-bintang">
                                @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star-fill {{ $i <= (int) $detail->rating ? '' : 'is-kosong' }}"></i>@endfor
                            </span>
                        </div>
                        <span class="dsb-lencana {{ $dLencana }}" style="margin-left: auto;">{{ $dLabel }}</span>
                    </div>

                    @if ($detail->kecurigaan())
                        <div class="ul-peringatan">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>
                                <b>Perlu diperiksa</b>
                                <span>{{ implode(' · ', $detail->kecurigaan()) }}</span>
                            </div>
                        </div>
                    @endif

                    <blockquote class="ul-kutip">{{ $detail->ulasan }}</blockquote>

                    <div class="ul-info">
                        <div>
                            <small>Ditulis oleh</small>
                            <b>{{ $detail->nama }}</b>
                            @if ($detail->pembeliAsli())
                                <span class="ul-tanda is-asli" style="margin-top: 5px;"><i class="bi bi-patch-check-fill"></i>Pembeli Asli</span>
                            @endif
                        </div>
                        <div><small>Tampil di halaman produk</small><b>{{ $detail->status === 'approved' ? 'Ya' : 'Tidak — '.$dLabel }}</b></div>
                        <div>
                            <small>Ditinjau admin</small>
                            <b>
                                @if ($detail->ditinjau_at)
                                    {{ $detail->ditinjau_at->locale('id')->translatedFormat('d M Y, H:i') }}{{ $detail->peninjau ? ' · '.$detail->peninjau->name : '' }}
                                @elseif ($detail->status === 'pending')
                                    Belum ditinjau
                                @else
                                    {{-- Diputuskan sebelum jejak dicatat: "belum ditinjau" menyesatkan. --}}
                                    Tidak tercatat
                                @endif
                            </b>
                        </div>
                        @if ($detail->customer)
                            <div><small>Pelanggan terkait</small><b>{{ $detail->customer->nama }}</b></div>
                        @endif
                    </div>
                </div>
                <div class="dsb-jendela-kaki ul-detail-kaki">
                    <button type="button" class="ul-btn ul-salin" data-teks="{{ $detail->ulasan }}" title="Salin isi ulasan">
                        <i class="bi bi-clipboard"></i><span>Salin teks</span>
                    </button>
                    @if ($tautanDetail = $detail->tautanPublik())
                        <a href="{{ $tautanDetail }}" target="_blank" rel="noopener" class="ul-btn">
                            <i class="bi bi-box-arrow-up-right"></i><span>Buka halaman produk</span>
                        </a>
                    @endif
                    @if ($bolehUbah && ! $detail->trashed() && $detail->status !== 'hidden')
                        <button type="button" class="ul-btn is-sembunyi ul-konfirmasi" data-action="reject" data-arg="{{ $detail->id }}" data-icon="warning"
                            data-title="Sembunyikan ulasan ini?" data-text="Ulasannya tidak lagi tampil di halaman produk, tapi tidak dihapus." data-confirm="Ya, sembunyikan">
                            <i class="bi bi-eye-slash"></i><span>Sembunyikan</span>
                        </button>
                    @endif
                    @if ($bolehUbah && ! $detail->trashed() && $detail->status !== 'approved')
                        <button type="button" class="ul-btn is-setuju ul-konfirmasi" data-action="approve" data-arg="{{ $detail->id }}" data-icon="question"
                            data-title="Setujui ulasan ini?" data-text="Ulasan {{ $detail->nama }} akan tampil di halaman {{ $detail->namaTarget() }}." data-confirm="Ya, setujui">
                            <i class="bi bi-check-lg"></i><span>Setujui</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ================== RINGKASAN PER PRODUK ================== --}}
    @if ($lihatRingkasan)
        <div class="ts-modal-back" wire:click="$set('lihatRingkasan', false)"></div>
        <div class="ts-modal">
            <div class="ts-modal-card dsb is-datar ul-jendela" role="dialog" aria-modal="true" aria-label="Ringkasan ulasan per produk"
                x-on:keydown.escape.window="$wire.set('lihatRingkasan', false)">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #0ea5e9"><i class="bi bi-bar-chart-line"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">Ulasan per produk</h5>
                        <span class="dsb-kartu-sub">Dari ulasan yang disetujui, bintang terendah lebih dulu.</span>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="$set('lihatRingkasan', false)" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="dsb-jendela-isi">
                    @if ($ringkasanProduk->isEmpty())
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-bar-chart-line"></i></span>
                            <p class="dsb-kosong-judul">Belum ada ulasan disetujui</p>
                            <p class="dsb-kosong-ket">Rekap ini terisi setelah ada ulasan yang tampil di halaman produk.</p>
                        </div>
                    @else
                        <ol class="ul-rekap">
                            @foreach ($ringkasanProduk as $baris)
                                <li>
                                    <span class="ul-rekap-ikon"><i class="bi {{ $baris['jenis'] === 'paket' ? 'bi-box-seam' : 'bi-bag' }}"></i></span>
                                    <span class="ul-rekap-teks">
                                        <b>{{ $baris['nama'] }}</b>
                                        <small>{{ $baris['jumlah'] }} ulasan · {{ $baris['jenis'] === 'paket' ? 'paket' : 'produk' }}</small>
                                    </span>
                                    <span class="ul-rekap-nilai {{ $baris['rata'] < 4 ? 'is-rendah' : '' }}">
                                        {{ number_format($baris['rata'], 1, ',', '.') }}<i class="bi bi-star-fill"></i>
                                    </span>
                                    @if ($baris['tautan'])
                                        <a href="{{ $baris['tautan'] }}" target="_blank" rel="noopener" class="ul-btn ul-btn-ikon" title="Buka halamannya"><i class="bi bi-box-arrow-up-right"></i></a>
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
            if (!window.__ulasanModerasiTerpasang) {
                window.__ulasanModerasiTerpasang = true;
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
                    if (arg === undefined) hidup.call(metode); else hidup.call(metode, arg);
                };
                document.addEventListener('click', (e) => {
                    if (typeof Swal === 'undefined') return;

                    const salin = e.target.closest('.ul-salin');
                    if (salin) {
                        e.preventDefault();
                        const teks = salin.dataset.teks || '';
                        const sudah = () => Swal.fire({ title: 'Tersalin', text: 'Isi ulasan sudah disalin.', icon: 'success', timer: 1600, showConfirmButton: false, ...gaya });
                        if (navigator.clipboard?.writeText) {
                            navigator.clipboard.writeText(teks).then(sudah).catch(() => salinCadangan(teks, sudah));
                        } else {
                            salinCadangan(teks, sudah);
                        }
                        return;
                    }

                    const tombol = e.target.closest('.ul-konfirmasi');
                    if (tombol) {
                        e.preventDefault();
                        Swal.fire({
                            title: tombol.dataset.title || 'Lanjutkan?', text: tombol.dataset.text || '',
                            icon: tombol.dataset.icon || 'question', showCancelButton: true,
                            confirmButtonText: tombol.dataset.confirm || 'Ya', cancelButtonText: 'Batal', ...gaya,
                        }).then((r) => { if (r.isConfirmed) panggil(tombol, tombol.dataset.action, tombol.dataset.arg); });
                    }
                });
            }
        </script>
    @endpush
</div>
