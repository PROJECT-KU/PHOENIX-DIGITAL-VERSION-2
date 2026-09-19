@section('title')
Data Banner || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.Banners.partials.banner-gaya')

    @php
        $bolehBuat = (bool) auth()->user()?->hasPermission('create_banners');
        $bolehUbah = (bool) auth()->user()?->hasPermission('edit_banners');
        $bolehHapus = (bool) auth()->user()?->hasPermission('delete_banners');
        $adaSaringan = $searchBanners || $keadaan;
        // Warna per keadaan tayang (Banners::keadaanTayang() mengembalikan warna Bootstrap).
        $gayaKeadaan = [
            'success' => ['is-hijau', '#16a34a', 'bi-broadcast'],
            'info' => ['is-biru', '#0284c7', 'bi-clock-history'],
            'danger' => ['is-merah', '#dc2626', 'bi-calendar-x'],
            'secondary' => ['is-abu', '#64748b', 'bi-eye-slash'],
        ];
        $kartuKeadaan = [
            '' => ['Semua', 'bi-images', '#7c3aed', $hitung['semua']],
            'tayang' => ['Sedang tayang', 'bi-broadcast', '#16a34a', $hitung['tayang']],
            'terjadwal' => ['Terjadwal', 'bi-clock-history', '#0284c7', $hitung['terjadwal']],
            'berakhir' => ['Jadwal berakhir', 'bi-calendar-x', '#dc2626', $hitung['berakhir']],
            'nonaktif' => ['Non-aktif', 'bi-eye-slash', '#64748b', $hitung['nonaktif']],
        ];
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Data Banner</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Banner pembuka di beranda toko — tayang otomatis sesuai status & jadwal, berurutan seperti di bawah.</span>
                </p>
            </div>
            @if ($bolehBuat)
                <div class="dsb-hero-aksi">
                    <a wire:navigate href="{{ route('admin.Banners.create') }}" class="dsb-tombol is-utama">
                        <i class="bi bi-plus-lg"></i><span>Tambah Banner</span>
                    </a>
                </div>
            @endif
        </header>

        {{-- ================== KEADAAN (sekaligus saringan) ================== --}}
        <nav class="bn-keadaan" aria-label="Saring menurut keadaan tayang">
            @foreach ($kartuKeadaan as $nilai => [$label, $ikon, $warna, $jumlah])
                <button type="button" class="bn-keadaan-btn {{ $keadaan === $nilai ? 'is-aktif' : '' }}" style="--c: {{ $warna }}"
                    wire:click="$set('keadaan', '{{ $nilai }}')" aria-pressed="{{ $keadaan === $nilai ? 'true' : 'false' }}">
                    <span class="bn-keadaan-ikon"><i class="bi {{ $ikon }}"></i></span>
                    <span class="bn-keadaan-teks"><b>{{ $jumlah }}</b><span>{{ $label }}</span></span>
                </button>
            @endforeach
        </nav>

        {{-- ================== CARI ================== --}}
        <section class="dsb-kartu bn-saring">
            <div class="dsb-kartu-isi bn-saring-isi">
                <div class="dsb-cari">
                    <i class="bi bi-search"></i>
                    <input type="search" class="dsb-isian" wire:model.live.debounce.300ms="searchBanners" placeholder="Cari judul atau deskripsi banner…" aria-label="Cari banner">
                    @if ($searchBanners)
                        <button type="button" class="dsb-cari-hapus" wire:click="$set('searchBanners', '')" title="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    @endif
                </div>
                <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="searchBanners,keadaan,gotoPage,nextPage,previousPage">
                    <span class="dsb-putar is-kecil"></span>Memuat…
                </span>
            </div>
        </section>

        {{-- ================== GALERI ================== --}}
        <section wire:loading.class="dsb-sedang-muat" wire:target="searchBanners,keadaan,gotoPage,nextPage,previousPage">
            @if ($Banners->isEmpty())
                <div class="dsb-kartu">
                    <div class="dsb-kosong">
                        <span class="dsb-kosong-ikon"><i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-images' }}"></i></span>
                        <p class="dsb-kosong-judul">{{ $adaSaringan ? 'Tidak ada banner yang cocok' : 'Belum ada banner' }}</p>
                        <p class="dsb-kosong-ket">{{ $adaSaringan ? 'Coba kata kunci atau keadaan lain.' : 'Tambahkan banner untuk tampil di beranda toko.' }}</p>
                        @if ($bolehBuat && ! $adaSaringan)
                            <a wire:navigate href="{{ route('admin.Banners.create') }}" class="dsb-tombol is-utama" style="margin-top: 14px;">
                                <i class="bi bi-plus-lg"></i><span>Tambah Banner</span>
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="bn-galeri">
                    @foreach ($Banners as $item)
                        @php
                            [$labelKeadaan, $warnaKeadaan] = $item->keadaanTayang();
                            [$lencanaKeadaan, $cKeadaan, $ikonKeadaan] = $gayaKeadaan[$warnaKeadaan] ?? $gayaKeadaan['secondary'];
                            $adaGambar = $item->gambarAda();
                            $hidup = $item->status === 'active';
                            $slide = $nomorSlide[$item->id] ?? null;
                            $bisaNaik = $bolehUbah && $item->id !== $idPertama;
                            $bisaTurun = $bolehUbah && $item->id !== $idTerakhir;
                        @endphp
                        <article class="bn-kartu {{ $item->sedangTayang() ? '' : 'is-mati' }}" style="--c: {{ $cKeadaan }}" wire:key="banner-{{ $item->id }}">
                            <button type="button" class="bn-gambar" wire:click="lihat('{{ $item->id }}')" title="Lihat detail {{ $item->judul }}">
                                @if ($adaGambar)
                                    <img src="{{ $item->gambarUrl() }}" alt="{{ $item->judul }}" loading="lazy">
                                @else
                                    <span class="bn-gambar-kosong"><span><i class="bi bi-image"></i></span>Gambar tidak ditemukan</span>
                                @endif
                                <span class="dsb-lencana {{ $lencanaKeadaan }} bn-lencana-keadaan"><i class="bi {{ $ikonKeadaan }}"></i>{{ $labelKeadaan }}</span>
                                @if ($slide)
                                    <span class="bn-slide" title="Urutan tampil di beranda">Slide {{ $slide }}</span>
                                @endif
                            </button>
                            <div class="bn-kartu-isi">
                                {{-- Di ponsel lencana di atas gambar disembunyikan; tampil di sini. --}}
                                <span class="dsb-lencana {{ $lencanaKeadaan }} bn-lencana-hp"><i class="bi {{ $ikonKeadaan }}"></i>{{ $labelKeadaan }}</span>
                                <h3 class="bn-judul" title="{{ $item->judul }}">{{ $item->judul }}</h3>
                                <div class="bn-waktu">
                                    <i class="bi bi-calendar-range"></i>
                                    <span>{{ $item->keteranganWaktu() ?? 'Disembunyikan dari beranda' }}</span>
                                </div>
                                <div class="bn-waktu bn-tujuan" title="Tujuan klik">
                                    <i class="bi bi-cursor"></i>
                                    <span>{{ $item->tautan ?: 'Halaman Belanja' }}</span>
                                </div>
                            </div>
                            <div class="bn-aksi">
                                <button type="button" class="dsb-tombol is-lembut is-mungil bn-aksi-utama" wire:click="lihat('{{ $item->id }}')">
                                    <i class="bi bi-eye"></i><span>Detail</span>
                                </button>
                                @if ($bolehUbah)
                                    <span class="bn-geser" role="group" aria-label="Ubah urutan">
                                        <button type="button" class="dsb-tabel-btn" wire:click="geser('{{ $item->id }}', 'naik')" @disabled(! $bisaNaik) title="Tampilkan lebih dulu"><i class="bi bi-chevron-up"></i></button>
                                        <button type="button" class="dsb-tabel-btn" wire:click="geser('{{ $item->id }}', 'turun')" @disabled(! $bisaTurun) title="Tampilkan belakangan"><i class="bi bi-chevron-down"></i></button>
                                    </span>
                                    <button type="button" class="dsb-tabel-btn bn-saklar {{ $hidup ? 'is-hidup' : '' }}" wire:click="alihStatus('{{ $item->id }}')"
                                        wire:loading.attr="disabled" wire:target="alihStatus('{{ $item->id }}')"
                                        title="{{ $hidup ? 'Sembunyikan dari beranda' : 'Aktifkan' }}">
                                        <i class="bi {{ $hidup ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                    <a wire:navigate href="{{ route('admin.Banners.edit', $item) }}" class="dsb-tabel-btn" title="Ubah"><i class="bi bi-pencil-square"></i></a>
                                @endif
                                @if ($bolehHapus)
                                    <button type="button" class="dsb-tabel-btn is-bahaya bn-hapus" data-id="{{ $item->id }}" data-judul="{{ $item->judul }}" title="Hapus"><i class="bi bi-trash3"></i></button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($Banners->hasPages())
                    <div class="bn-halaman">{{ $Banners->links('vendor.pagination') }}</div>
                @endif
            @endif
        </section>
    </div>

    {{-- ================== JENDELA DETAIL ================== --}}
    @if ($detail)
        @php
            [$dLabel, $dWarna] = $detail->keadaanTayang();
            [$dLencana, $dC, $dIkon] = $gayaKeadaan[$dWarna] ?? $gayaKeadaan['secondary'];
            $dAdaGambar = $detail->gambarAda();
            $fmt = fn ($t) => $t ? $t->locale('id')->translatedFormat('d M Y, H:i') : null;
        @endphp
        <div class="ts-modal-back" wire:click="tutupLihat"></div>
        <div class="ts-modal" wire:key="banner-detail-{{ $detail->id }}">
            <div class="ts-modal-card dsb is-datar bn-jendela" role="dialog" aria-modal="true" aria-label="Detail banner" tabindex="-1"
                x-on:keydown.escape.window="$wire.tutupLihat()">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: {{ $dC }}"><i class="bi {{ $dIkon }}"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">{{ $detail->judul }}</h5>
                        <span class="dsb-kartu-sub">{{ $detail->keteranganWaktu() ?? 'Disembunyikan dari beranda' }}</span>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="tutupLihat" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="dsb-jendela-isi bn-detail">
                    <div class="bn-detail-gambar">
                        @if ($dAdaGambar)
                            <img src="{{ $detail->gambarUrl() }}" alt="{{ $detail->judul }}">
                        @else
                            <span class="bn-gambar-kosong"><span><i class="bi bi-image"></i></span>Gambar tidak ditemukan di server</span>
                        @endif
                    </div>
                    <div class="bn-detail-info">
                        <div>
                            <span class="bn-detail-label">Keadaan di beranda</span>
                            <span class="dsb-lencana {{ $dLencana }}"><i class="bi {{ $dIkon }}"></i>{{ $dLabel }}</span>
                            @if ($detail->status === 'active' && ! $detail->sedangTayang())
                                <small class="bn-bantu">Status Active, tetapi di luar jadwal — tidak tampil untuk pengunjung.</small>
                            @endif
                        </div>
                        <div>
                            <span class="bn-detail-label">Jadwal tayang</span>
                            <div class="bn-jadwal">
                                <div><small>Mulai</small><b>{{ $fmt($detail->mulai_tayang) ?? 'Langsung' }}</b></div>
                                <div><small>Selesai</small><b>{{ $fmt($detail->selesai_tayang) ?? 'Tanpa batas' }}</b></div>
                            </div>
                        </div>
                        <div>
                            <span class="bn-detail-label">Tujuan klik</span>
                            <p><a href="{{ $detail->tautanTujuan() }}" target="_blank" rel="noopener">{{ $detail->tautan ?: 'Halaman Belanja' }} <i class="bi bi-box-arrow-up-right"></i></a></p>
                        </div>
                        <div>
                            <span class="bn-detail-label">Deskripsi</span>
                            <p>{{ $detail->deskripsi ?: 'Tanpa deskripsi.' }}</p>
                        </div>
                        <p class="bn-detail-waktu">
                            <span>Ditambahkan {{ $detail->created_at?->locale('id')->translatedFormat('d M Y') }}</span>
                            <span>Diperbarui {{ $detail->updated_at?->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                        </p>
                    </div>
                </div>
                <div class="dsb-jendela-kaki bn-detail-kaki">
                    @if ($dAdaGambar)
                        <a href="{{ $detail->gambarUrl() }}" download="{{ \Illuminate\Support\Str::slug($detail->judul) }}.{{ pathinfo($detail->gambar, PATHINFO_EXTENSION) }}" class="dsb-tombol is-lembut">
                            <i class="bi bi-download"></i><span>Unduh Gambar</span>
                        </a>
                    @endif
                    @if ($bolehUbah)
                        <a wire:navigate href="{{ route('admin.Banners.edit', $detail) }}" class="dsb-tombol is-utama"><i class="bi bi-pencil-square"></i><span>Ubah Banner</span></a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @include('livewire.layout.sweetalert')

    @push('scripts')
        <script>
            // Dipasang SEKALI di dokumen: halaman ini dibuka ulang lewat wire:navigate.
            // (Versi lama memasang pendengar klik baru tiap navigasi, dan pesannya
            // menyebut "data promo".)
            if (!window.__bannerDaftarTerpasang) {
                window.__bannerDaftarTerpasang = true;
                const gaya = {
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel', title: 'swal-glossy-title' },
                    buttonsStyling: false,
                };
                document.addEventListener('click', (e) => {
                    const hapus = e.target.closest('.bn-hapus');
                    if (!hapus || typeof Swal === 'undefined') return;
                    e.preventDefault();
                    Swal.fire({
                        title: 'Hapus banner ini?',
                        html: '<b>' + (hapus.dataset.judul || '').replace(/[<>&]/g, '') + '</b><br>Gambarnya ikut dihapus permanen dan banner hilang dari beranda.',
                        icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', ...gaya,
                    }).then((r) => {
                        if (!r.isConfirmed) return;
                        const komponen = hapus.closest('[wire\\:id]');
                        if (komponen) Livewire.find(komponen.getAttribute('wire:id')).call('deleteBanners', hapus.dataset.id);
                    });
                });
                window.addEventListener('Banners-deleted', () => {
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Terhapus', text: 'Banner berhasil dihapus.', icon: 'success', timer: 2000, showConfirmButton: false, ...gaya });
                });
                window.addEventListener('Banners-deleteError', (e) => {
                    const d = Array.isArray(e.detail) ? (e.detail[0] || {}) : (e.detail || {});
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Gagal', text: d.message || 'Banner gagal dihapus.', icon: 'error', timer: 2500, showConfirmButton: false, ...gaya });
                });
            }
        </script>
    @endpush
</div>
