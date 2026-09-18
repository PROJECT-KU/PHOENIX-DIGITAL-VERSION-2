@section('title')
Data Pesanan || lemon
@stop
<div wire:poll.15s="watchNewPayments">
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.order.partials.toko-gaya')

    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $jumlahLanjut = $this->jumlahSaringanLanjut();
        $adaSaringan = $search || $filterMonth || $filterYear || $jumlahLanjut;
        $bolehUbahPesanan = (bool) auth()->user()?->hasPermission('edit_pemesanantoko');
        $bolehBuatPesanan = (bool) auth()->user()?->hasPermission('create_pemesanantoko');
        $tabAkun = $this->tabItem();
        $targetMuat = 'search,filterMonth,filterYear,tglDari,tglSampai,metode,produk,jenis,urut,perHalaman,resetFilters,setTab,gotoPage,nextPage,previousPage';
        // Warna Bootstrap dari Order::labelStatus()/labelPembayaran() → lencana dasbor.
        $lencana = [
            'success' => 'is-hijau', 'warning' => 'is-kuning', 'info' => 'is-biru', 'primary' => 'is-ungu',
            'danger' => 'is-merah', 'secondary' => 'is-abu', 'dark' => 'is-abu', 'light' => 'is-abu',
        ];
        $namaStatus = [
            'DRAFT' => 'Draft', 'PENDING' => 'Menunggu bayar', 'PAID' => 'Dibayar', 'PROCESSING' => 'Diproses',
            'COMPLETED' => 'Selesai', 'CANCELLED' => 'Dibatalkan', 'SEDANG DIPROSES' => 'Sedang dicek',
        ];
        $lencanaLangganan = ['baru' => 'is-ungu', 'perpanjang' => 'is-hijau', 'pengganti' => 'is-biru', 'habis' => 'is-merah'];
        $tabs = [
            'all' => ['Semua', 'bi-list-check', '#7c3aed'],
            'neworder' => ['Pesanan Baru', 'bi-bag-plus-fill', '#16a34a'],
            'berjalan' => ['Pengecekan Berjalan', 'bi-hourglass-split', '#0284c7'],
            'processing' => ['Diproses', 'bi-gear-fill', '#d97706'],
            'completed' => ['Selesai', 'bi-bag-check-fill', '#4f46e5'],
            'cancelled' => ['Dibatalkan', 'bi-x-circle-fill', '#e11d48'],
            'draft' => ['Draft', 'bi-inbox-fill', '#64748b'],
            'catatan' => ['Ada Catatan', 'bi-sticky-fill', '#d97706'],
            'segera' => ['Segera Habis', 'bi-alarm-fill', '#ea580c'],
            'habis' => ['Akun Habis', 'bi-hourglass-bottom', '#dc2626'],
        ];
        $judulDaftar = ['segera' => 'Akun yang Segera Habis', 'habis' => 'Akun yang Sudah Habis'][$activeTab] ?? 'Pesanan';
        $ketUrut = ['segera' => 'Paling dekat habis di atas', 'habis' => 'Masa aktif terbaru di atas'][$activeTab] ?? $this::URUTAN[$urut];
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Pesanan Toko</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">
                        Pesanan dari toko online beserta pembayaran dan pengecekannya
                        <span class="dsb-segar"><i class="bi bi-broadcast"></i>Pembayaran baru dipantau otomatis</span>
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                @if ($bolehUbahPesanan)
                    <button type="button" wire:click="unduhExcel" wire:loading.attr="disabled" wire:target="unduhExcel" class="dsb-tombol is-lembut" title="Unduh Excel sesuai tab & saringan yang tampil">
                        <span wire:loading.remove wire:target="unduhExcel" class="pt-isi-tombol"><i class="bi bi-file-earmark-excel"></i><span>Unduh Excel</span></span>
                        <span wire:loading.inline-flex wire:target="unduhExcel" class="pt-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                    </button>
                @endif
                @if ($bolehBuatPesanan)
                    <a wire:navigate href="{{ route('admin.pesanantoko.create') }}" class="dsb-tombol is-utama">
                        <i class="bi bi-plus-lg"></i><span>Tambah Pesanan</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #0284c7">
                    <span class="dsb-kepala-ikon"><i class="bi bi-funnel-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Tampilan</span>
                        <h2 class="dsb-judul">Cari &amp; Saring</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="{{ $targetMuat }}">
                                <span class="dsb-putar is-kecil"></span>Memuat…
                            </span>
                            <span class="dsb-chip is-samar">Kode pesanan, nama, no. HP, email, produk, atau username akun</span>
                        </div>
                    </div>
                </div>

                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="pt-saring">
                            <div class="dsb-medan">
                                <label class="dsb-label" for="pt-cari">Cari</label>
                                <div class="dsb-cari">
                                    <i class="bi bi-search"></i>
                                    <input id="pt-cari" type="search" class="dsb-isian" wire:model.live.debounce.300ms="search"
                                        placeholder="Cari pesanan…">
                                    @if ($search)
                                        <button type="button" class="dsb-cari-hapus" wire:click="$set('search', '')" title="Hapus pencarian">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="dsb-medan">
                                <label class="dsb-label" for="pt-bulan">Bulan</label>
                                <select id="pt-bulan" class="dsb-isian" wire:model.live="filterMonth">
                                    <option value="">Semua bulan</option>
                                    @foreach ($months as $month)
                                        <option value="{{ $month['value'] }}">{{ ucfirst($month['label']) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="dsb-medan">
                                <label class="dsb-label" for="pt-tahun">Tahun</label>
                                <select id="pt-tahun" class="dsb-isian" wire:model.live="filterYear">
                                    <option value="">Semua tahun</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Saringan lanjutan: terbuka sendiri bila ada yang aktif. --}}
                        <div x-data="{ buka: {{ $jumlahLanjut ? 'true' : 'false' }} }" class="pt-lanjutan">
                            <div class="pt-lanjutan-kepala">
                                <button type="button" class="dsb-tombol is-lembut is-mungil" x-on:click="buka = !buka" :aria-expanded="buka">
                                    <i class="bi bi-sliders"></i><span>Saringan lanjutan</span>
                                    @if ($jumlahLanjut)
                                        <span class="pt-tab-jumlah is-isi">{{ $jumlahLanjut }}</span>
                                    @endif
                                    <i class="bi bi-chevron-down pt-lanjutan-panah" :class="buka && 'is-buka'"></i>
                                </button>
                                <div class="pt-urut">
                                    {{-- Tab akun punya urutan tetap (paling dekat habis / terbaru habis). --}}
                                    @unless ($tabAkun)
                                        <label class="dsb-label" for="pt-urut">Urutkan</label>
                                        <select id="pt-urut" class="dsb-isian" wire:model.live="urut">
                                            @foreach ($this::URUTAN as $kunci => $label)
                                                <option value="{{ $kunci }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @endunless
                                    <label class="dsb-label" for="pt-baris">Baris</label>
                                    <select id="pt-baris" class="dsb-isian pt-baris" wire:model.live="perHalaman">
                                        @foreach ($this::PILIHAN_BARIS as $n)
                                            <option value="{{ $n }}">{{ $n }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="pt-saring pt-saring-lanjut" x-show="buka" x-cloak>
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pt-dari">Dari tanggal</label>
                                    <input id="pt-dari" type="date" class="dsb-isian" wire:model.live="tglDari">
                                </div>
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pt-sampai">Sampai tanggal</label>
                                    <input id="pt-sampai" type="date" class="dsb-isian" wire:model.live="tglSampai">
                                </div>
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pt-metode">Metode bayar</label>
                                    <select id="pt-metode" class="dsb-isian" wire:model.live="metode">
                                        <option value="">Semua metode</option>
                                        @foreach ($this::METODE as $kunci => $label)
                                            <option value="{{ $kunci }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pt-jenis">Jenis</label>
                                    <select id="pt-jenis" class="dsb-isian" wire:model.live="jenis">
                                        <option value="">Akun &amp; jasa</option>
                                        <option value="akun">Akun saja</option>
                                        <option value="jasa">Jasa saja (cek plagiasi, AI, parafrase)</option>
                                    </select>
                                </div>
                                <div class="dsb-medan pt-medan-produk">
                                    <label class="dsb-label" for="pt-produk">Produk</label>
                                    <select id="pt-produk" class="dsb-isian" wire:model.live="produk">
                                        <option value="">Semua produk</option>
                                        @foreach ($produkPilihan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama_akun }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        @if ($adaSaringan)
                            <div class="pt-saring-kaki">
                                <span class="dsb-kartu-sub"><i class="bi bi-funnel"></i> Angka di tab mengikuti saringan</span>
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
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: {{ $tabs[$activeTab][2] ?? '#16a34a' }}">
                    <span class="dsb-kepala-ikon"><i class="bi {{ $tabs[$activeTab][1] ?? 'bi-list-task' }}"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Daftar</span>
                        <h2 class="dsb-judul">{{ $judulDaftar }}</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-sort-down"></i>{{ $ketUrut }}</span>
                            <span class="dsb-chip is-samar">{{ $tabAkun ? 'Satu baris = satu akun' : 'Satu baris = satu pesanan' }}</span>
                        </div>
                    </div>
                </div>

                <div class="k-12">
                    <nav class="pt-tab-deret" aria-label="Saring menurut status"
                        x-data x-init="const t = $el.querySelector('.is-aktif'); if (t) $el.scrollLeft = t.offsetLeft - 12">
                        @foreach ($tabs as $kunci => [$label, $ikon, $warna])
                            <button type="button" wire:click="setTab('{{ $kunci }}')" style="--c: {{ $warna }}"
                                class="pt-tab {{ $activeTab === $kunci ? 'is-aktif' : '' }}"
                                aria-pressed="{{ $activeTab === $kunci ? 'true' : 'false' }}">
                                <i class="bi {{ $ikon }}"></i>
                                <span>{{ $label }}</span>
                                <span class="pt-tab-jumlah">{{ $tabCounts[$kunci] > 999 ? '999+' : $tabCounts[$kunci] }}</span>
                            </button>
                        @endforeach
                    </nav>
                </div>

                <div class="k-12" wire:loading.class="dsb-sedang-muat" wire:target="{{ $targetMuat }}">
                    <div class="dsb-kartu">
                    @if ($tabAkun)
                        @include('livewire.pages.admin.order.partials.tabel-akun', [
                            'items' => $activeTab === 'segera' ? $segeraItems : $habisItems,
                            'jenisTab' => $activeTab,
                        ])
                    @else
                        @if ($orders->isEmpty())
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-inbox' }}"></i></span>
                                <p class="dsb-kosong-judul">{{ $adaSaringan ? 'Tidak ada pesanan yang cocok' : 'Belum ada pesanan' }}</p>
                                <p class="dsb-kosong-ket">{{ $adaSaringan ? 'Coba ubah kata kunci atau periode.' : 'Pesanan pada tab ini akan muncul di sini.' }}</p>
                                @if ($adaSaringan)
                                    <button type="button" wire:click="resetFilters" class="dsb-tombol is-utama" style="margin-top: 14px;">
                                        <i class="bi bi-x-circle"></i><span>Kosongkan saringan</span>
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="dsb-tabel-bungkus">
                                <table class="dsb-tabel">
                                    <thead>
                                        <tr>
                                            <th>Pesanan</th>
                                            <th class="k-lebar">Produk</th>
                                            <th>Total</th>
                                            <th class="k-sedang">Pembayaran</th>
                                            <th>Status</th>
                                            <th class="k-lebar">Tanggal</th>
                                            <th style="text-align: right;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orders as $order)
                                            @php
                                                [$stTeks, $stWarna] = $order->labelStatus();
                                                $pay = $order->labelPembayaran();
                                                $produk = $order->items->map(fn ($it) => $it->product_name ?: $it->product?->nama_akun)->filter()->values();
                                                $warnaIkon = ['success' => '#16a34a', 'warning' => '#d97706', 'info' => '#0284c7', 'primary' => '#4f46e5', 'danger' => '#e11d48'][$stWarna] ?? '#64748b';
                                                $urlDetail = route('admin.pesanantoko.detail', $order);
                                                // Catatan terlihat dari daftar: admin tidak perlu membuka pesanan satu per satu.
                                                $catatanPelanggan = trim((string) $order->customer_notes);
                                                $catatanAdmin = $order->items->pluck('processing_notes')->map(fn ($c) => trim((string) $c))->filter()->implode("\n");
                                                $catatanUntuk = $order->items->pluck('account_notes')->map(fn ($c) => trim((string) $c))->filter()->implode("\n");
                                                $adaCatatan = $catatanAdmin || $catatanPelanggan || $catatanUntuk;
                                                // Yang masih perlu ditindaklanjuti: catatan internal, atau catatan
                                                // pelanggan yang belum ditandai ditangani.
                                                $pelangganTerbuka = $catatanPelanggan && ! $order->catatan_ditangani_at;
                                                $catatanTerbuka = $catatanAdmin || $pelangganTerbuka;
                                                $kelasCatatan = $catatanAdmin ? 'is-admin' : ($pelangganTerbuka ? 'is-pelanggan' : 'is-selesai');
                                            @endphp
                                            <tr wire:key="order-{{ $order->id }}" @class(['is-tanda' => $order->status === 'paid']) @if ($order->status === 'paid') style="--c: #16a34a" @endif>
                                                <td>
                                                    <a wire:navigate href="{{ $urlDetail }}" class="dsb-tabel-utama pt-tautan">
                                                        <span class="dsb-ikon is-kecil" style="--c: {{ $warnaIkon }}"><i class="bi bi-bag-fill"></i></span>
                                                        <span class="dsb-tabel-teks">
                                                            <span class="dsb-tabel-judul">{{ $order->customer->nama ?? 'Tanpa nama' }}</span>
                                                            <span class="dsb-tabel-meta">
                                                                <span class="dsb-lencana is-abu">{{ $order->order_number }}</span>
                                                                @if ($order->pengecekan_menunggu_count > 0)
                                                                    <span class="dsb-lencana is-kuning" title="{{ $order->pengecekan_menunggu_count }} pengecekan menunggu diproses">
                                                                        <i class="bi bi-hourglass-split"></i>{{ $order->pengecekan_menunggu_count }} cek menunggu
                                                                    </span>
                                                                @endif
                                                                @if ($order->pengecekan_diproses_count > 0)
                                                                    <span class="dsb-lencana is-biru" title="{{ $order->pengecekan_diproses_count }} pengecekan sedang dikerjakan (hasil belum diunggah)">
                                                                        <i class="bi bi-gear-wide-connected"></i>{{ $order->pengecekan_diproses_count }} sedang dicek
                                                                    </span>
                                                                @endif
                                                                {{-- Salinan kolom yang disembunyikan di layar sempit. --}}
                                                                <span class="dsb-tabel-samar"><i class="bi bi-clock"></i>{{ $order->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                                                            </span>

                                                        </span>
                                                    </a>
                                                </td>
                                                <td class="k-lebar {{ $produk->isEmpty() ? 'is-kosong' : '' }}" data-judul="Produk">
                                                    @if ($produk->isEmpty())
                                                        <span class="dsb-tabel-samar">—</span>
                                                    @else
                                                        <span class="dsb-tabel-angka" title="{{ $produk->implode(', ') }}">
                                                            {{ \Illuminate\Support\Str::limit($produk->first(), 28) }}
                                                            @if ($produk->count() > 1)
                                                                <span class="dsb-lencana is-biru">+{{ $produk->count() - 1 }}</span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                </td>
                                                <td data-judul="Total"><span class="pt-total">{{ $rupiah($order->total) }}</span></td>
                                                <td class="k-sedang" data-judul="Pembayaran">
                                                    {{-- Nama & warna dari Order::labelPembayaran(), sumber yang sama dgn detail. --}}
                                                    @if ($pay)
                                                        <span class="dsb-lencana {{ $lencana[$pay[2]] ?? 'is-abu' }}"><i class="bi {{ $pay[1] }}"></i>{{ $pay[0] }}</span>
                                                    @else
                                                        <span class="dsb-tabel-samar">—</span>
                                                    @endif
                                                </td>
                                                <td data-judul="Status">
                                                    <span class="dsb-lencana {{ $lencana[$stWarna] ?? 'is-abu' }}">{{ $namaStatus[$stTeks] ?? $stTeks }}</span>
                                                </td>
                                                <td class="k-lebar" data-judul="Tanggal">
                                                    <span class="dsb-tabel-teks">
                                                        <span class="dsb-tabel-angka">{{ $order->created_at->locale('id')->translatedFormat('d M Y') }}</span>
                                                        <span class="dsb-tabel-meta">{{ $order->created_at->format('H:i') }}</span>
                                                    </span>
                                                </td>
                                                <td data-judul="Aksi" style="text-align: right;">
                                                    <span class="dsb-tabel-aksi">
                                                        @if ($order->status === 'draft' && $order->payment_method === 'qris_dinamis')
                                                            <a href="{{ route('admin.pesanantoko.qris', $order) }}" class="pt-lanjut" title="Lanjutkan pembayaran QRIS">
                                                                <i class="bi bi-play-fill"></i><span>Lanjutkan</span>
                                                            </a>
                                                        @elseif ($order->status === 'draft' && in_array($order->payment_method, ['transfer', 'qris_statis'], true))
                                                            {{-- Draft pembayaran manual: lanjutannya unggah bukti. Setelah
                                                                 bukti masuk, pesanan menjadi pending dan baru boleh diproses. --}}
                                                            <a href="{{ route('admin.pesanantoko.unggah-bukti', $order) }}" class="pt-lanjut" title="Unggah bukti pembayaran">
                                                                <i class="bi bi-upload"></i><span>Unggah Bukti</span>
                                                            </a>
                                                        @endif
                                                        {{-- Catatan dibuka sebagai jendela kecil, tidak memakan baris. --}}
                                                        @if ($adaCatatan)
                                                            <button type="button" class="dsb-tabel-btn pt-catatan-btn {{ $kelasCatatan }}"
                                                                title="{{ $catatanTerbuka ? 'Lihat catatan (belum ditangani)' : 'Lihat catatan (sudah ditangani)' }}" aria-label="Lihat catatan pesanan {{ $order->order_number }}"
                                                                data-nomor="{{ $order->order_number }}" data-nama="{{ $order->customer->nama ?? '' }}"
                                                                data-admin="{{ $catatanAdmin }}" data-pelanggan="{{ $catatanPelanggan }}" data-untuk="{{ $catatanUntuk }}"
                                                                data-ditangani="{{ $order->catatan_ditangani_at ? '1' : '' }}"
                                                                data-bisa-selesai="{{ $catatanTerbuka && $bolehUbahPesanan ? '1' : '' }}"
                                                                data-order="{{ $order->id }}" data-komponen="{{ $this->getId() }}">
                                                                <i class="bi bi-sticky-fill"></i>
                                                            </button>
                                                        @endif
                                                        <a wire:navigate href="{{ $urlDetail }}" class="dsb-tabel-btn" title="Detail pesanan">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($orders->hasPages())
                                <div class="pt-halaman">{{ $orders->links('vendor.pagination') }}</div>
                            @endif
                        @endif
                    @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->


    @push('scripts')
        <script>
            // Jendela catatan pesanan (daftar). Satu pendengar di dokumen, dipasang
            // sekali, karena halaman ini dibuka ulang lewat wire:navigate.
            if (!window.__ptCatatanTerpasang) {
                window.__ptCatatanTerpasang = true;
                const esc = (t) => String(t).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                const tutup = () => document.getElementById('pt-catatan-jendela')?.remove();
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && document.getElementById('pt-catatan-jendela')) { e.preventDefault(); tutup(); } });
                document.addEventListener('livewire:navigating', tutup);
                window.addEventListener('catatan-diselesaikan', (e) => {
                    const d = Array.isArray(e.detail) ? (e.detail[0] || {}) : (e.detail || {});
                    if (typeof Swal === 'undefined') return;
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: d.pesan || 'Catatan ditandai selesai.', timer: 2200, showConfirmButton: false,
                        background: 'rgba(255,255,255,.95)', customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0' } });
                });
                document.addEventListener('click', (e) => {
                    if (e.target.closest('[data-tutup-catatan]')) { tutup(); return; }
                    const selesai = e.target.closest('[data-selesaikan-catatan]');
                    if (selesai) {
                        const asal = window.__ptCatatanAsal;
                        selesai.disabled = true;
                        selesai.querySelector('span').textContent = 'Menyimpan…';
                        window.Livewire?.find(asal.komponen)?.call('selesaikanCatatan', asal.order).then(tutup);
                        return;
                    }
                    const btn = e.target.closest('.pt-catatan-btn');
                    if (!btn) return;
                    e.preventDefault();
                    window.__ptCatatanAsal = { komponen: btn.dataset.komponen, order: btn.dataset.order };
                    const blok = (ikon, judul, teks, warna) => teks ? `
                        <div class="pt-cj-blok" style="--c:${warna}">
                            <div class="pt-cj-judul"><i class="bi ${ikon}"></i> ${judul}</div>
                            <div class="pt-cj-isi">${esc(teks).replace(/\n/g, '<br>')}</div>
                        </div>` : '';
                    const wadah = document.createElement('div');
                    wadah.id = 'pt-catatan-jendela';
                    wadah.innerHTML = `
                        <div class="ts-modal-back" data-tutup-catatan></div>
                        <div class="ts-modal">
                            <div class="ts-modal-card dsb is-datar" role="dialog" aria-modal="true" aria-label="Catatan pesanan" tabindex="-1" style="max-width:480px">
                                <div class="dsb-jendela-kepala">
                                    <span class="dsb-ikon is-kecil" style="--c:#d97706"><i class="bi bi-sticky-fill"></i></span>
                                    <span class="dsb-jendela-teks">
                                        <h5 class="dsb-jendela-judul">Catatan Pesanan</h5>
                                        <span class="dsb-kartu-sub">${esc(btn.dataset.nomor)}${btn.dataset.nama ? ' · ' + esc(btn.dataset.nama) : ''}</span>
                                    </span>
                                    <button type="button" class="dsb-jendela-tutup" data-tutup-catatan title="Tutup"><i class="bi bi-x-lg"></i></button>
                                </div>
                                <div class="dsb-jendela-isi">
                                    ${blok('bi-lock-fill', 'Catatan admin (internal)', btn.dataset.admin, '#d97706')}
                                    ${blok('bi-chat-left-text-fill', 'Catatan dari pelanggan' + (btn.dataset.ditangani ? ' · sudah ditangani' : ''), btn.dataset.pelanggan, '#16a34a')}
                                    ${blok('bi-chat-heart', 'Catatan untuk pelanggan', btn.dataset.untuk, '#0284c7')}
                                </div>
                                ${btn.dataset.bisaSelesai ? `
                                <div class="dsb-jendela-kaki">
                                    <span class="dsb-jendela-kaki-ket">Catatan internal dihapus; catatan pelanggan hanya ditandai ditangani.</span>
                                    <button type="button" class="dsb-tombol is-utama is-mungil" data-selesaikan-catatan>
                                        <i class="bi bi-check2-all"></i><span>Tandai selesai</span>
                                    </button>
                                </div>` : ''}
                            </div>
                        </div>`;
                    document.body.appendChild(wadah);
                });
            }
        </script>
        <script>
            // Notifikasi saat pembayaran QRIS baru terdeteksi (dari polling watchNewPayments)
            if (!window.__orderPaidToastBound) {
                window.__orderPaidToastBound = true;
                window.addEventListener('order-paid-toast', function (e) {
                    var d = e.detail || {};
                    if (Array.isArray(d)) d = d[0] || {};
                    var amount = new Intl.NumberFormat('id-ID').format(d.total || 0);
                    if (typeof Swal === 'undefined') return;
                    Swal.fire({
                        title: 'Pembayaran Diterima!',
                        html: 'Pesanan <b>' + (d.orderNumber || '') + '</b><br>' +
                            (d.customerName || '') + ' · <b>Rp ' + amount + '</b>',
                        icon: 'success',
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdrop: 'rgba(16, 185, 129, 0.15)',
                        customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                        buttonsStyling: false,
                        timer: 4500,
                        showConfirmButton: false,
                    });
                });
            }
        </script>
    @endpush
</div>