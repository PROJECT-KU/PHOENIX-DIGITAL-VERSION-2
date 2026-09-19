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
            @if ($bolehBuat)
                <div class="dsb-hero-aksi">
                    <a wire:navigate href="{{ route('admin.testimoni.create') }}" class="dsb-tombol is-utama">
                        <i class="bi bi-plus-lg"></i><span>Tambah Testimoni</span>
                    </a>
                </div>
            @endif
        </header>

        {{-- ================== STATUS (sekaligus tab moderasi) ================== --}}
        <nav class="tm-status" aria-label="Saring menurut status moderasi">
            @foreach ($kartuStatus as $nilai => [$label, $ikon, $warna])
                <button type="button" class="tm-status-btn {{ $filter === $nilai ? 'is-aktif' : '' }} {{ $nilai === 'pending' && 0 < $tabCounts['pending'] ? 'is-perlu' : '' }}"
                    style="--c: {{ $warna }}" wire:click="setFilter('{{ $nilai }}')" aria-pressed="{{ $filter === $nilai ? 'true' : 'false' }}">
                    <span class="tm-status-ikon"><i class="bi {{ $ikon }}"></i></span>
                    <span class="tm-status-teks"><b>{{ $tabCounts[$nilai] }}</b><span>{{ $label }}</span></span>
                </button>
            @endforeach
            <div class="tm-status-btn is-info" style="--c: #f59e0b" title="Rata-rata rating testimoni yang tampil di publik">
                <span class="tm-status-ikon"><i class="bi bi-star-fill"></i></span>
                <span class="tm-status-teks"><b>{{ $rataRating ? number_format($rataRating, 1, ',', '.') : '–' }}</b><span>Rata-rata rating</span></span>
            </div>
        </nav>

        {{-- ================== CARI ================== --}}
        <section class="dsb-kartu tm-saring">
            <div class="dsb-kartu-isi tm-saring-isi">
                <div class="dsb-cari">
                    <i class="bi bi-search"></i>
                    <input type="search" class="dsb-isian" wire:model.live.debounce.300ms="searchTestimoni" placeholder="Cari nama, peran, atau isi testimoni…" aria-label="Cari testimoni">
                    @if ($searchTestimoni)
                        <button type="button" class="dsb-cari-hapus" wire:click="$set('searchTestimoni', '')" title="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    @endif
                </div>
                <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="searchTestimoni,setFilter,gotoPage,nextPage,previousPage">
                    <span class="dsb-putar is-kecil"></span>Memuat…
                </span>
            </div>
        </section>

        {{-- ================== RAK TESTIMONI ================== --}}
        <section wire:loading.class="dsb-sedang-muat" wire:target="searchTestimoni,setFilter,gotoPage,nextPage,previousPage">
            @if ($Testimoni->isEmpty())
                @php [$kIkon, $kJudul, $kKet] = $searchTestimoni ? ['bi-funnel', 'Tidak ada testimoni yang cocok', 'Coba kata kunci lain.'] : $kosong[$filter]; @endphp
                <div class="dsb-kartu">
                    <div class="dsb-kosong">
                        <span class="dsb-kosong-ikon"><i class="bi {{ $kIkon }}"></i></span>
                        <p class="dsb-kosong-judul">{{ $kJudul }}</p>
                        <p class="dsb-kosong-ket">{{ $kKet }}</p>
                    </div>
                </div>
            @else
                <div class="tm-rak">
                    @foreach ($Testimoni as $item)
                        @php [$stLencana, $stWarna, $stLabel] = $gayaStatus[$item->status] ?? ['is-abu', '#64748b', ucfirst($item->status)]; @endphp
                        <article class="tm-kartu" style="--c: {{ $stWarna }}" wire:key="testi-{{ $item->id }}">
                            <div class="tm-kepala">
                                <button type="button" class="tm-avatar-tombol" wire:click="lihat('{{ $item->id }}')" style="border: 0; padding: 0; background: none;" title="Lihat detail">
                                    @include('livewire.pages.admin.testimoni.partials.avatar', ['item' => $item])
                                </button>
                                <div class="tm-kepala-teks">
                                    <p class="tm-nama">{{ $item->nama }}</p>
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
                                <p class="tm-pesan">{{ \Illuminate\Support\Str::limit($item->pesan, 200) }}</p>
                                @if (200 < mb_strlen((string) $item->pesan))
                                    <button type="button" class="tm-baca" wire:click="lihat('{{ $item->id }}')">Baca selengkapnya</button>
                                @endif
                                @include('livewire.pages.admin.testimoni.partials.lencana-keaslian', ['item' => $item])
                                <div class="tm-waktu">{{ $item->source === 'customer' ? 'Dikirim' : 'Diinput admin' }} {{ $item->created_at?->locale('id')->diffForHumans() }}</div>
                            </div>
                            <div class="tm-aksi">
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
                                            <button type="button" class="tm-btn is-tolak tm-konfirmasi"
                                                data-action="reject" data-arg="{{ $item->id }}" data-icon="warning"
                                                data-title="Tolak testimoni ini?"
                                                data-text="Testimoni {{ $item->nama }} akan disembunyikan dari publik."
                                                data-confirm="Ya, tolak">
                                                <i class="bi bi-x-lg"></i><span>{{ $item->status === 'active' ? 'Sembunyikan' : 'Tolak' }}</span>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                                <div class="tm-aksi-lain">
                                    <button type="button" class="tm-btn tm-btn-ikon" wire:click="lihat('{{ $item->id }}')" title="Detail" aria-label="Detail testimoni"><i class="bi bi-eye"></i></button>
                                    @if ($bolehUbah)
                                        <a wire:navigate href="{{ route('admin.testimoni.edit', $item) }}" class="tm-btn tm-btn-ikon" title="Ubah" aria-label="Ubah testimoni"><i class="bi bi-pencil"></i></a>
                                    @endif
                                    @if ($bolehHapus)
                                        <button type="button" class="tm-btn tm-btn-ikon is-bahaya tm-hapus" data-id="{{ $item->id }}" data-nama="{{ $item->nama }}" title="Hapus" aria-label="Hapus testimoni"><i class="bi bi-trash3"></i></button>
                                    @endif
                                </div>
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
                x-on:keydown.escape.window="$wire.tutupLihat()">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: {{ $dWarna }}"><i class="bi bi-chat-quote-fill"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">Detail Testimoni</h5>
                        <span class="dsb-kartu-sub">{{ $detail->source === 'customer' ? 'Dikirim pelanggan' : 'Diinput admin' }} · {{ $detail->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</span>
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

                    <blockquote class="tm-kutip">{{ $detail->pesan }}</blockquote>

                    <div>
                        <span class="tm-detail-label">Keaslian pengirim</span>
                        @include('livewire.pages.admin.testimoni.partials.lencana-keaslian', ['item' => $detail])
                    </div>

                    <div class="tm-info">
                        <div><small>Tampil di publik sebagai</small><b>{{ $detail->nama_publik }}</b></div>
                        <div><small>Nomor WhatsApp</small><b>{{ $detail->no_hp ?: '—' }}</b></div>
                    </div>
                </div>
                <div class="dsb-jendela-kaki tm-detail-kaki">
                    @if ($detail->no_hp && $bolehPelanggan)
                        <a wire:navigate href="{{ route('admin.customer.index', ['searchCustomer' => $detail->no_hp_cari]) }}" class="tm-btn" title="Buka Data Pelanggan untuk nomor ini">
                            <i class="bi bi-person-lines-fill"></i><span>Data Pelanggan</span>
                        </a>
                    @endif
                    @if ($bolehUbah)
                        <a wire:navigate href="{{ route('admin.testimoni.edit', $detail) }}" class="tm-btn"><i class="bi bi-pencil"></i><span>Ubah</span></a>
                        @if ($detail->status !== 'non-active')
                            <button type="button" class="tm-btn is-tolak tm-konfirmasi" data-action="reject" data-arg="{{ $detail->id }}" data-icon="warning"
                                data-title="Tolak testimoni ini?" data-text="Testimoni {{ $detail->nama }} akan disembunyikan dari publik." data-confirm="Ya, tolak">
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
                const panggil = (el, metode, arg) => {
                    const komponen = el.closest('[wire\\:id]');
                    if (komponen) Livewire.find(komponen.getAttribute('wire:id')).call(metode, arg);
                };
                document.addEventListener('click', (e) => {
                    if (typeof Swal === 'undefined') return;
                    // Setujui / Tolak — teks dibaca dari data-* tombolnya.
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
                    const hapus = e.target.closest('.tm-hapus');
                    if (hapus) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Hapus testimoni ini?',
                            text: 'Testimoni dari ' + (hapus.dataset.nama || '') + ' dihapus permanen beserta fotonya.',
                            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', ...gaya,
                        }).then((r) => { if (r.isConfirmed) panggil(hapus, 'deleteTestimoni', hapus.dataset.id); });
                    }
                });
                window.addEventListener('testimoni-deleted', () => {
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Terhapus', text: 'Testimoni berhasil dihapus.', icon: 'success', timer: 2000, showConfirmButton: false, ...gaya });
                });
                window.addEventListener('testimoni-deleteError', (e) => {
                    const d = Array.isArray(e.detail) ? (e.detail[0] || {}) : (e.detail || {});
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Gagal', text: d.message || 'Testimoni gagal dihapus.', icon: 'error', timer: 2500, showConfirmButton: false, ...gaya });
                });
            }
        </script>
    @endpush
</div>
