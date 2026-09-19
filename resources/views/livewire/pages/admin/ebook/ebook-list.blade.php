@section('title')
Ebook Bonus || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.ebook.partials.ebook-gaya')

    @php
        $bolehBuat = (bool) auth()->user()?->hasPermission('create_ebook');
        $bolehUbah = (bool) auth()->user()?->hasPermission('edit_ebook');
        $bolehHapus = (bool) auth()->user()?->hasPermission('delete_ebook');
        $adaSaringan = $search || $statusFilter;
        // Warna sampul bergilir supaya rak ebook tidak seragam abu-abu.
        $palet = ['#7c3aed', '#0284c7', '#16a34a', '#ea580c', '#db2777', '#4f46e5', '#0d9488', '#d97706'];
        $saringStatus = ['' => ['Semua', 'bi-grid-fill'], 'active' => ['Aktif', 'bi-check-circle-fill'], 'non-active' => ['Nonaktif', 'bi-pause-circle-fill']];
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Ebook Bonus</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Pustaka ebook yang dibagikan sebagai bonus pesanan — pelanggan membukanya view-only lewat tautan.</span>
                </p>
            </div>
            @if ($bolehBuat || ($bolehUbah && $jumlahSaran))
                <div class="dsb-hero-aksi">
                    @if ($bolehUbah && $jumlahSaran)
                        <button type="button" class="dsb-tombol is-lembut" wire:click="bukaSaran" title="Pasang ebook yang paling sering dikirim sebagai bawaan tiap produk">
                            <i class="bi bi-magic"></i><span>Saran bawaan <b class="eb-hitung">{{ $jumlahSaran }}</b></span>
                        </button>
                    @endif
                    @if ($bolehBuat)
                        <a wire:navigate href="{{ route('admin.ebook.create') }}" class="dsb-tombol is-utama">
                            <i class="bi bi-plus-lg"></i><span>Tambah Ebook</span>
                        </a>
                    @endif
                </div>
            @endif
        </header>

        {{-- ================== RINGKASAN ================== --}}
        <section class="eb-stat-deret" aria-label="Ringkasan ebook">
            <article class="dsb-stat" style="--c: #7c3aed">
                <span class="dsb-ikon"><i class="bi bi-journal-bookmark-fill"></i></span>
                <p class="dsb-stat-label">Total Ebook</p>
                <p class="dsb-stat-nilai">{{ $ringkas['total'] }}<span class="dsb-stat-satuan">judul</span></p>
            </article>
            <article class="dsb-stat" style="--c: #16a34a">
                <span class="dsb-ikon"><i class="bi bi-check-circle-fill"></i></span>
                <p class="dsb-stat-label">Aktif</p>
                <p class="dsb-stat-nilai">{{ $ringkas['aktif'] }}<span class="dsb-stat-satuan">bisa dipilih</span></p>
            </article>
            <article class="dsb-stat" style="--c: #94a3b8">
                <span class="dsb-ikon"><i class="bi bi-pause-circle-fill"></i></span>
                <p class="dsb-stat-label">Nonaktif</p>
                <p class="dsb-stat-nilai">{{ $ringkas['nonaktif'] }}<span class="dsb-stat-satuan">tautan mati</span></p>
            </article>
            <article class="dsb-stat" style="--c: #ea580c">
                <span class="dsb-ikon"><i class="bi bi-send-check-fill"></i></span>
                <p class="dsb-stat-label">Dikirim ke Pesanan</p>
                <p class="dsb-stat-nilai">{{ number_format($ringkas['dikirim'], 0, ',', '.') }}<span class="dsb-stat-satuan">kali</span></p>
            </article>
        </section>

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-kartu eb-saring">
            <div class="dsb-kartu-isi eb-saring-isi">
                <div class="dsb-cari eb-cari">
                    <i class="bi bi-search"></i>
                    <input type="search" class="dsb-isian" wire:model.live.debounce.300ms="search" placeholder="Cari judul atau deskripsi ebook…" aria-label="Cari ebook">
                    @if ($search)
                        <button type="button" class="dsb-cari-hapus" wire:click="$set('search', '')" title="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    @endif
                </div>
                <div class="eb-segmen" role="group" aria-label="Saring status">
                    @foreach ($saringStatus as $nilai => [$label, $ikon])
                        <button type="button" wire:click="$set('statusFilter', '{{ $nilai }}')"
                            class="eb-segmen-btn {{ $statusFilter === $nilai ? 'is-aktif' : '' }}" aria-pressed="{{ $statusFilter === $nilai ? 'true' : 'false' }}">
                            <i class="bi {{ $ikon }}"></i><span>{{ $label }}</span>
                        </button>
                    @endforeach
                </div>
                <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="search,statusFilter,gotoPage,nextPage,previousPage">
                    <span class="dsb-putar is-kecil"></span>Memuat…
                </span>
            </div>
        </section>

        {{-- ================== RAK EBOOK ================== --}}
        <section wire:loading.class="dsb-sedang-muat" wire:target="search,statusFilter,gotoPage,nextPage,previousPage">
            @if ($ebooks->isEmpty())
                <div class="dsb-kartu">
                    <div class="dsb-kosong">
                        <span class="dsb-kosong-ikon"><i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-journal-plus' }}"></i></span>
                        <p class="dsb-kosong-judul">{{ $adaSaringan ? 'Tidak ada ebook yang cocok' : 'Belum ada ebook' }}</p>
                        <p class="dsb-kosong-ket">{{ $adaSaringan ? 'Coba kata kunci atau status lain.' : 'Tambahkan ebook agar bisa dipilih saat memproses pesanan.' }}</p>
                        @if ($bolehBuat && ! $adaSaringan)
                            <a wire:navigate href="{{ route('admin.ebook.create') }}" class="dsb-tombol is-utama" style="margin-top: 14px;">
                                <i class="bi bi-plus-lg"></i><span>Tambah Ebook</span>
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="eb-rak">
                    @foreach ($ebooks as $item)
                        @php
                            $warna = $palet[(($ebooks->currentPage() - 1) * $ebooks->perPage() + $loop->index) % count($palet)];
                            $aktif = $item->status === 'active';
                            $ukuran = $item->ukuranFileLabel();
                        @endphp
                        <article class="eb-kartu {{ $aktif ? '' : 'is-nonaktif' }}" style="--c: {{ $warna }}" wire:key="ebook-{{ $item->id }}">
                            <button type="button" class="eb-sampul" wire:click="lihat('{{ $item->id }}')" title="Lihat detail {{ $item->judul }}">
                                <span class="eb-sampul-ikon"><i class="bi bi-book-half"></i></span>
                                <span class="eb-sampul-pdf">PDF</span>
                                <span class="dsb-lencana {{ $aktif ? 'is-hijau' : 'is-abu' }} eb-sampul-status">
                                    <i class="bi {{ $aktif ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' }}"></i>{{ $aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </button>
                            <div class="eb-kartu-isi">
                                <h3 class="eb-judul" title="{{ $item->judul }}">{{ $item->judul }}</h3>
                                <p class="eb-desk">{{ $item->deskripsi ?: 'Tanpa deskripsi.' }}</p>
                                <div class="eb-meta">
                                    {{-- Di ponsel lencana status di sampul disembunyikan; tampil di sini. --}}
                                    <span class="eb-meta-status {{ $aktif ? 'is-aktif' : '' }}"><i class="bi {{ $aktif ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' }}"></i>{{ $aktif ? 'Aktif' : 'Nonaktif' }}</span>
                                    <span><i class="bi bi-send-check"></i>{{ $item->order_items_count }} pesanan</span>
                                    <span title="Berapa kali halaman baca dibuka"><i class="bi bi-eye"></i>{{ number_format($item->dibuka_count, 0, ',', '.') }}× dibuka</span>
                                    @if (! $aktif)
                                        <span class="is-peringatan"><i class="bi bi-link-45deg"></i>Tautan pelanggan mati</span>
                                    @endif
                                    @if ($item->produk_bawaan_count)
                                        <span class="is-bawaan"><i class="bi bi-magic"></i>Bawaan {{ $item->produk_bawaan_count }} produk</span>
                                    @endif
                                    @if ($ukuran)
                                        <span><i class="bi bi-hdd"></i>{{ $ukuran }}</span>
                                    @else
                                        <span class="is-peringatan"><i class="bi bi-exclamation-triangle"></i>Berkas tidak ditemukan</span>
                                    @endif
                                </div>
                            </div>
                            <div class="eb-aksi">
                                <button type="button" class="dsb-tombol is-lembut is-mungil eb-aksi-utama" wire:click="lihat('{{ $item->id }}')">
                                    <i class="bi bi-eye"></i><span>Detail</span>
                                </button>
                                @if ($ukuran)
                                    <a href="{{ $item->getAdminDownloadUrl() }}" class="dsb-tabel-btn" title="Unduh PDF"><i class="bi bi-download"></i></a>
                                @endif
                                @if ($bolehUbah)
                                    <a wire:navigate href="{{ route('admin.ebook.edit', $item) }}" class="dsb-tabel-btn" title="Ubah"><i class="bi bi-pencil-square"></i></a>
                                @endif
                                @if ($bolehHapus)
                                    <button type="button" class="dsb-tabel-btn is-bahaya eb-hapus" data-id="{{ $item->id }}" data-judul="{{ $item->judul }}" title="Hapus"><i class="bi bi-trash3"></i></button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($ebooks->hasPages())
                    <div class="eb-halaman">{{ $ebooks->links('vendor.pagination') }}</div>
                @endif
            @endif
        </section>
    </div>

    {{-- ================== JENDELA SARAN EBOOK BAWAAN ================== --}}
    @if ($saranBuka)
        @php $jumlahDipilih = count($saranPilih); @endphp
        <div class="ts-modal-back" wire:click="tutupSaran"></div>
        <div class="ts-modal" wire:key="ebook-saran">
            <div class="ts-modal-card dsb is-datar eb-jendela" role="dialog" aria-modal="true" aria-label="Saran ebook bawaan" tabindex="-1"
                x-on:keydown.escape.window="$wire.tutupSaran()">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #ea580c"><i class="bi bi-magic"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">Terapkan Ebook Bawaan</h5>
                        <span class="dsb-kartu-sub">Dari riwayat: ebook yang paling sering dikirim untuk tiap produk</span>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="tutupSaran" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="dsb-jendela-isi eb-saran-daftar">
                    @forelse ($saran as $x)
                        @php
                            $pid = (string) $x['produk']->id;
                            $dicentang = isset($saranPilih[$pid]);
                        @endphp
                        <button type="button" class="eb-saran-baris {{ $dicentang ? 'is-pilih' : '' }}" wire:click="alihSaran('{{ $pid }}', '{{ $x['ebook']->id }}')" wire:key="sb-{{ $pid }}">
                            <span class="eb-produk-centang"><i class="bi bi-check-lg"></i></span>
                            <span class="eb-saran-teks">
                                <b>{{ $x['produk']->nama_akun }}</b>
                                <span><i class="bi bi-arrow-right"></i> {{ $x['ebook']->judul }} · dikirim {{ $x['n'] }}×</span>
                                @if ($x['sekarang'])
                                    <small>Bawaan saat ini: {{ $x['sekarang']->judul }} — akan diganti bila dicentang</small>
                                @endif
                            </span>
                        </button>
                    @empty
                        <p class="eb-kosong-kecil">Semua produk sudah sesuai saran.</p>
                    @endforelse
                    <p class="eb-detail-waktu">Hanya produk yang menerima ebook yang sama minimal {{ $this::SARAN_MIN }}×. Produk yang sudah punya bawaan lain tidak tercentang otomatis.</p>
                </div>
                <div class="dsb-jendela-kaki eb-detail-kaki">
                    <button type="button" class="dsb-tombol is-lembut" wire:click="tutupSaran"><span>Batal</span></button>
                    <button type="button" class="dsb-tombol is-utama" wire:click="terapkanSaran" wire:loading.attr="disabled" wire:target="terapkanSaran" @disabled(! $jumlahDipilih)>
                        <i class="bi bi-check2-circle"></i><span>Terapkan ke {{ $jumlahDipilih }} produk</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================== JENDELA DETAIL ================== --}}
    @if ($detail)
        @php
            $dAktif = $detail->status === 'active';
            $dUkuran = $detail->ukuranFileLabel();
            $dLink = $detail->getViewUrl();
        @endphp
        <div class="ts-modal-back" wire:click="tutupLihat"></div>
        <div class="ts-modal" wire:key="ebook-detail-{{ $detail->id }}">
            <div class="ts-modal-card dsb is-datar eb-jendela" role="dialog" aria-modal="true" aria-label="Detail ebook" tabindex="-1"
                x-on:keydown.escape.window="$wire.tutupLihat()">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-book-half"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">{{ $detail->judul }}</h5>
                        <span class="dsb-kartu-sub">Ebook bonus · PDF view-only</span>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="tutupLihat" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="dsb-jendela-isi eb-detail">
                    <div class="eb-detail-angka">
                        <div style="--c: {{ $dAktif ? '#16a34a' : '#94a3b8' }}">
                            <span><i class="bi {{ $dAktif ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' }}"></i></span>
                            <b>{{ $dAktif ? 'Aktif' : 'Nonaktif' }}</b><small>Status</small>
                        </div>
                        <div style="--c: #ea580c">
                            <span><i class="bi bi-send-check-fill"></i></span>
                            <b>{{ $detail->order_items_count }}</b><small>Dikirim</small>
                        </div>
                        <div style="--c: #4f46e5">
                            <span><i class="bi bi-eye-fill"></i></span>
                            <b>{{ number_format($detail->dibuka_count, 0, ',', '.') }}</b><small>Dibuka</small>
                        </div>
                        <div style="--c: {{ $dUkuran ? '#0284c7' : '#dc2626' }}">
                            <span><i class="bi {{ $dUkuran ? 'bi-file-earmark-pdf-fill' : 'bi-exclamation-triangle-fill' }}"></i></span>
                            <b>{{ $dUkuran ?: 'Hilang' }}</b><small>Berkas</small>
                        </div>
                    </div>

                    @if (! $dAktif)
                        <div class="eb-detail-peringatan">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span><b>Ebook nonaktif</b> — tautan yang sudah dikirim ke pelanggan tidak bisa dibuka (pelanggan melihat halaman "ebook tidak tersedia").</span>
                        </div>
                    @endif

                    <div class="eb-detail-blok">
                        <span class="eb-detail-label">Deskripsi</span>
                        <p>{{ $detail->deskripsi ?: 'Tanpa deskripsi.' }}</p>
                    </div>

                    @if ($dLink)
                        <div class="eb-detail-blok">
                            <span class="eb-detail-label">Tautan untuk pelanggan (view-only)</span>
                            <div class="eb-tautan">
                                <code>{{ $dLink }}</code>
                                <button type="button" class="dsb-tombol is-lembut is-mungil eb-salin" data-salin="{{ $dLink }}">
                                    <i class="bi bi-clipboard"></i><span>Salin</span>
                                </button>
                                <a href="{{ $dLink }}" target="_blank" rel="noopener" class="dsb-tabel-btn" title="Buka seperti pelanggan"><i class="bi bi-box-arrow-up-right"></i></a>
                            </div>
                            <small class="eb-detail-ket">
                                Terakhir dibuka {{ $detail->terakhir_dibuka_at ? $detail->terakhir_dibuka_at->locale('id')->diffForHumans() : 'belum pernah' }}.
                                @if ($bolehUbah)
                                    <button type="button" class="eb-tautan-baru" data-id="{{ $detail->id }}">Buat tautan baru</button> bila tautan ini tersebar ke luar.
                                @endif
                            </small>
                        </div>
                    @endif

                    <div class="eb-detail-blok">
                        <span class="eb-detail-label">Bawaan untuk produk</span>
                        @if ($detail->produkBawaan->isNotEmpty())
                            <div class="eb-detail-produk">
                                @foreach ($detail->produkBawaan as $pb)
                                    <span class="dsb-lencana is-kuning"><i class="bi bi-magic"></i>{{ $pb->nama_akun }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="eb-kosong-kecil">Belum ada — atur di <b>Ubah Ebook</b> supaya tercentang otomatis saat memproses pesanan.</p>
                        @endif
                    </div>

                    <div class="eb-detail-blok">
                        <span class="eb-detail-label">Terakhir dikirim ke</span>
                        @forelse ($detailPesanan as $it)
                            @if ($it->order)
                                <a wire:navigate href="{{ route('admin.pesanantoko.detail', $it->order) }}" class="eb-pesanan">
                                    <span class="dsb-lencana is-abu">{{ $it->order->order_number }}</span>
                                    <span class="eb-pesanan-nama">{{ $it->order->customer->nama ?? 'Tanpa nama' }}</span>
                                    <span class="eb-pesanan-produk">{{ $it->product_name }}</span>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            @endif
                        @empty
                            <p class="eb-kosong-kecil">Belum pernah dikirim ke pesanan.</p>
                        @endforelse
                    </div>

                    <p class="eb-detail-waktu">
                        Ditambahkan {{ $detail->created_at?->locale('id')->translatedFormat('d M Y') }}
                        · diperbarui {{ $detail->updated_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                    </p>
                </div>
                <div class="dsb-jendela-kaki eb-detail-kaki">
                    @if ($dUkuran)
                        <a href="{{ $detail->getAdminDownloadUrl() }}" class="dsb-tombol is-lembut"><i class="bi bi-download"></i><span>Unduh PDF</span></a>
                    @endif
                    @if ($bolehUbah)
                        <a wire:navigate href="{{ route('admin.ebook.edit', $detail) }}" class="dsb-tombol is-utama"><i class="bi bi-pencil-square"></i><span>Ubah Ebook</span></a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @include('livewire.layout.sweetalert')

    @push('scripts')
        <script>
            // Dipasang SEKALI di dokumen: halaman ini dibuka ulang lewat wire:navigate.
            // (Versi lama memasang pendengar klik baru tiap navigasi.)
            if (!window.__ebookDaftarTerpasang) {
                window.__ebookDaftarTerpasang = true;
                const gaya = {
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel', title: 'swal-glossy-title' },
                    buttonsStyling: false,
                };
                document.addEventListener('click', (e) => {
                    const salin = e.target.closest('.eb-salin');
                    if (salin) {
                        navigator.clipboard?.writeText(salin.dataset.salin).then(() => {
                            const teks = salin.querySelector('span');
                            teks.textContent = 'Tersalin';
                            setTimeout(() => { teks.textContent = 'Salin'; }, 1600);
                        });
                        return;
                    }
                    const baru = e.target.closest('.eb-tautan-baru');
                    if (baru && typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Buat tautan baru?',
                            html: 'Tautan lama <b>langsung mati untuk semua pelanggan</b> yang pernah menerimanya. Pakai hanya bila tautan ini tersebar ke luar.',
                            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, buat baru', cancelButtonText: 'Batal', ...gaya,
                        }).then((r) => {
                            if (!r.isConfirmed) return;
                            const komponen = baru.closest('[wire\\:id]');
                            if (komponen) Livewire.find(komponen.getAttribute('wire:id')).call('buatTautanBaru', baru.dataset.id);
                        });
                        return;
                    }
                    const hapus = e.target.closest('.eb-hapus');
                    if (!hapus || typeof Swal === 'undefined') return;
                    e.preventDefault();
                    Swal.fire({
                        title: 'Hapus ebook ini?',
                        html: '<b>' + (hapus.dataset.judul || '').replace(/[<>&]/g, '') + '</b><br>Berkas PDF-nya ikut dihapus permanen. Tautan yang sudah dikirim ke pelanggan tidak bisa dibuka lagi.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                        ...gaya,
                    }).then((r) => {
                        if (!r.isConfirmed) return;
                        const komponen = hapus.closest('[wire\\:id]');
                        if (komponen) Livewire.find(komponen.getAttribute('wire:id')).call('deleteEbook', hapus.dataset.id);
                    });
                });
                window.addEventListener('Ebook-deleted', () => {
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Terhapus', text: 'Ebook berhasil dihapus.', icon: 'success', timer: 2000, showConfirmButton: false, ...gaya });
                });
                window.addEventListener('Ebook-deleteError', (e) => {
                    const d = Array.isArray(e.detail) ? (e.detail[0] || {}) : (e.detail || {});
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Gagal', text: d.message || 'Ebook gagal dihapus.', icon: 'error', timer: 2500, showConfirmButton: false, ...gaya });
                });
            }
        </script>
    @endpush
</div>
