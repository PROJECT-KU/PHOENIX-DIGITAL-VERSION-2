@section('title')
Data Pesanan || lemon
@stop
<div wire:poll.15s="watchNewPayments">
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.order.partials.toko-gaya')

    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $adaSaringan = $search || $filterMonth || $filterYear;
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
            'habis' => ['Akun Habis', 'bi-hourglass-bottom', '#dc2626'],
        ];
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
                @if (auth()->user()->hasPermission('create_pemesanantoko'))
                    <a wire:navigate href="{{ route('admin.pesanantoko.create') }}" class="dsb-tombol is-utama">
                        <i class="bi bi-plus-lg"></i><span>Tambah Pesanan</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ================== RINGKASAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #7c3aed">
                    <span class="dsb-kepala-ikon"><i class="bi bi-bag-heart-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Ringkasan</span>
                        <h2 class="dsb-judul">Keadaan Pesanan</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-receipt"></i>{{ number_format($tabCounts['all'], 0, ',', '.') }} pesanan</span>
                            <span class="dsb-chip is-samar">{{ $adaSaringan ? 'Mengikuti saringan di bawah' : 'Seluruh data' }}</span>
                        </div>
                    </div>
                </div>

                <article class="dsb-stat is-utama k-6" style="--c: #16a34a">
                    <span class="dsb-ikon"><i class="bi bi-cash-stack"></i></span>
                    <p class="dsb-stat-label">Omzet Pesanan</p>
                    <p class="dsb-stat-nilai">{{ $rupiah($ringkas['omzet']) }}</p>
                    <p class="dsb-stat-ket"><i class="bi bi-check2-circle"></i><span>Dari {{ number_format($ringkas['lunas'], 0, ',', '.') }} pesanan yang sudah dibayar</span></p>
                </article>

                <button type="button" wire:click="setTab('neworder')" class="dsb-stat is-utama k-6 pt-stat-tombol"
                    style="--c: {{ $ringkas['perluProses'] > 0 ? '#d97706' : '#64748b' }}">
                    <span class="dsb-ikon"><i class="bi bi-lightning-charge-fill"></i></span>
                    <span class="dsb-stat-label">Perlu Diproses</span>
                    <span class="dsb-stat-nilai">{{ $ringkas['perluProses'] }}<span class="dsb-stat-satuan">pesanan</span></span>
                    <span class="dsb-stat-ket"><i class="bi bi-arrow-right-circle"></i><span>{{ $ringkas['perluProses'] > 0 ? 'Sudah dibayar, akunnya belum dikirim' : 'Tidak ada yang menunggu' }}</span></span>
                </button>

                <button type="button" wire:click="setTab('neworder')" class="dsb-stat k-4 pt-stat-tombol" style="--c: #0284c7">
                    <span class="dsb-ikon"><i class="bi bi-bag-plus-fill"></i></span>
                    <span class="dsb-stat-label">Pesanan Baru</span>
                    <span class="dsb-stat-nilai">{{ $tabCounts['neworder'] }}<span class="dsb-stat-satuan">pesanan</span></span>
                    <span class="dsb-stat-ket"><i class="bi bi-clock-history"></i><span>Menunggu bayar atau belum diproses</span></span>
                </button>

                <button type="button" wire:click="setTab('berjalan')" class="dsb-stat k-4 pt-stat-tombol"
                    style="--c: {{ $ringkas['cekMenunggu'] > 0 ? '#7c3aed' : '#64748b' }}">
                    <span class="dsb-ikon"><i class="bi bi-file-earmark-check-fill"></i></span>
                    <span class="dsb-stat-label">Pengecekan Menunggu</span>
                    <span class="dsb-stat-nilai">{{ $ringkas['cekMenunggu'] }}<span class="dsb-stat-satuan">berkas</span></span>
                    <span class="dsb-stat-ket"><i class="bi bi-hourglass-split"></i><span>{{ $tabCounts['berjalan'] }} pesanan sedang berjalan</span></span>
                </button>

                <button type="button" wire:click="setTab('habis')" class="dsb-stat k-4 pt-stat-tombol"
                    style="--c: {{ $ringkas['habisBelum'] > 0 ? '#e11d48' : '#64748b' }}">
                    <span class="dsb-ikon"><i class="bi bi-bell-fill"></i></span>
                    <span class="dsb-stat-label">Akun Habis Belum Diberi Tahu</span>
                    <span class="dsb-stat-nilai">{{ $ringkas['habisBelum'] }}<span class="dsb-stat-satuan">akun</span></span>
                    <span class="dsb-stat-ket"><i class="bi bi-hourglass-bottom"></i><span>Dari {{ $tabCounts['habis'] }} akun yang sudah habis</span></span>
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
                            <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="search,filterMonth,filterYear,resetFilters,setTab,gotoPage,nextPage,previousPage">
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

                        @if ($adaSaringan)
                            <div class="pt-saring-kaki">
                                <span class="dsb-kartu-sub"><i class="bi bi-funnel"></i> Angka tab & ringkasan mengikuti saringan</span>
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
                        <h2 class="dsb-judul">{{ $activeTab === 'habis' ? 'Akun yang Sudah Habis' : 'Pesanan' }}</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-sort-down"></i>{{ $activeTab === 'habis' ? 'Masa aktif terbaru di atas' : 'Terbaru di atas' }}</span>
                            <span class="dsb-chip is-samar">{{ $activeTab === 'habis' ? 'Satu baris = satu akun' : 'Satu baris = satu pesanan' }}</span>
                        </div>
                    </div>
                </div>

                <div class="k-12">
                    <nav class="pt-tab-deret" aria-label="Saring menurut status">
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

                <div class="k-12" wire:loading.class="dsb-sedang-muat" wire:target="search,filterMonth,filterYear,resetFilters,setTab,gotoPage,nextPage,previousPage">
                    <div class="dsb-kartu">
                    @if ($activeTab === 'habis')
                        @if ($habisItems->isEmpty())
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-hourglass-bottom"></i></span>
                                <p class="dsb-kosong-judul">Belum ada akun habis</p>
                                <p class="dsb-kosong-ket">Tidak ada item pesanan yang masa aktifnya sudah habis{{ $adaSaringan ? ' pada saringan ini' : '' }}.</p>
                            </div>
                        @else
                            <div class="dsb-tabel-bungkus">
                                <table class="dsb-tabel">
                                    <thead>
                                        <tr>
                                            <th>Akun</th>
                                            <th class="k-sedang">Pelanggan</th>
                                            <th>Masa Aktif</th>
                                            <th class="k-lebar">Langganan</th>
                                            <th>Pemberitahuan</th>
                                            <th style="text-align: right;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($habisItems as $item)
                                            <tr wire:key="habis-{{ $item->id }}">
                                                <td>
                                                    <div class="dsb-tabel-utama">
                                                        <span class="dsb-ikon is-kecil" style="--c: #dc2626"><i class="bi bi-hourglass-bottom"></i></span>
                                                        <span class="dsb-tabel-teks">
                                                            <span class="dsb-tabel-judul">{{ $item->product_name }}</span>
                                                            <span class="dsb-tabel-meta">
                                                                <span class="dsb-lencana is-abu">{{ $item->order->order_number ?? '—' }}</span>
                                                                <span class="dsb-tabel-samar"><i class="bi bi-person"></i>{{ $item->order->customer->nama ?? '—' }}</span>
                                                            </span>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="k-sedang" data-judul="Pelanggan"><span class="dsb-tabel-angka">{{ $item->order->customer->nama ?? '—' }}</span></td>
                                                <td data-judul="Masa Aktif">
                                                    @if ($item->end_date)
                                                        <span class="dsb-tabel-teks">
                                                            <span class="dsb-tabel-angka">s/d {{ \Carbon\Carbon::parse($item->end_date)->locale('id')->translatedFormat('d M Y') }}</span>
                                                            <span class="dsb-tabel-meta"><span class="dsb-lencana {{ $item->isHabis() ? 'is-merah' : 'is-hijau' }}">{{ $item->getRemainingLabel() }}</span></span>
                                                        </span>
                                                    @else
                                                        <span class="dsb-tabel-samar">—</span>
                                                    @endif
                                                </td>
                                                <td class="k-lebar" data-judul="Langganan">
                                                    <span class="dsb-lencana {{ $lencanaLangganan[$item->subscription_status] ?? 'is-abu' }}">{{ ucfirst($item->subscription_status ?: 'Tidak diketahui') }}</span>
                                                </td>
                                                <td data-judul="Pemberitahuan">
                                                    @if ($item->habis_notified_at)
                                                        <span class="dsb-lencana is-hijau" title="Diberi tahu {{ $item->habis_notified_at->locale('id')->translatedFormat('d M Y H:i') }}">
                                                            <i class="bi bi-check2-circle"></i>Sudah
                                                        </span>
                                                    @else
                                                        <span class="dsb-lencana is-kuning"><i class="bi bi-exclamation-circle"></i>Belum</span>
                                                    @endif
                                                </td>
                                                <td data-judul="Aksi" style="text-align: right;">
                                                    <span class="dsb-tabel-aksi">
                                                        @if ($item->order)
                                                            <a wire:navigate href="{{ route('admin.pesanantoko.detail', $item->order) }}" class="dsb-tabel-btn" title="Detail pesanan">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($habisItems->hasPages())
                                <div class="pt-halaman">{{ $habisItems->links('vendor.pagination') }}</div>
                            @endif
                        @endif
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