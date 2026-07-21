<?php

use App\Livewire\Actions\Logout;
use App\Models\CustomerMessage;
use App\Models\Order;
use App\Models\OrderUpload;
use App\Models\ProductReview;
use App\Models\Testimoni;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        // Setelah logout arahkan ke halaman login, bukan beranda publik.
        $this->redirect(route('login'));
    }

    /**
     * Segarkan badge saat komponen lain mengubah hal yang dihitung badge
     * (mis. menyetujui testimoni, memproses pesanan) — tanpa refresh halaman.
     *
     * Body sengaja kosong: menerima event sudah memicu Livewire me-render ulang
     * komponen ini, sehingga with() menghitung ulang angkanya.
     */
    #[On('sidebar-badge-updated')]
    public function refreshBadge(): void
    {
        //
    }

    /**
     * Jumlah Pesanan Toko berstatus "paid" — sudah dibayar tapi belum diproses,
     * jadi perlu ditindaklanjuti. Ditampilkan sebagai badge di sidebar supaya
     * tidak terlewat. Hanya dihitung bila user memang boleh melihat menunya.
     */
    public function with(): array
    {
        $login = auth()->check();
        $u = $login ? auth()->user() : null;

        $pesananTokoPaid = $login && $u->hasPermission('view_pemesanantoko')
            ? Order::paid()->count() : 0;

        // Testimoni menunggu moderasi (status 'pending'). Otomatis habis saat
        // admin menyetujui (active) atau menolak (non-active) — seragam
        // dengan Ulasan Produk.
        $testimoniBaru = $login && $u->hasPermission('view_testimoni')
            ? Testimoni::menunggu()->count() : 0;

        // Ulasan produk menunggu moderasi. Status 'pending' sudah jelas artinya
        // (ditolak jadi 'hidden') — badge otomatis habis saat disetujui/disembunyikan.
        $ulasanBaru = $login && $u->hasPermission('view_productreview')
            ? ProductReview::where('status', 'pending')->count() : 0;

        // Pesan helpdesk yang belum dibaca admin. Otomatis berkurang saat admin
        // membuka pesan (markAsRead di halaman detail).
        $helpdeskBaru = $login && $u->hasPermission('view_customer_message')
            ? CustomerMessage::unread()->count() : 0;

        // Pengecekan plagiasi yang menunggu diproses. Penting karena paket 5x
        // diunggah bertahap: file ke-2 dst bisa masuk berhari-hari kemudian.
        $pengecekanBaru = $login && $u->hasPermission('view_pemesanantoko')
            ? OrderUpload::where('status', 'menunggu')->count() : 0;

        return [
            'pesananTokoPaid' => $pesananTokoPaid,
            'testimoniBaru' => $testimoniBaru,
            'ulasanBaru' => $ulasanBaru,
            'helpdeskBaru' => $helpdeskBaru,
            'pengecekanBaru' => $pengecekanBaru,

            // Badge di judul tab (mis. "(3) lemon") = jumlah hal BARU yang perlu
            // ditindaklanjuti: pesanan toko paid + testimoni + ulasan + helpdesk.
            // Aturan hitungnya sama dengan badge sidebar; ikut segar saat
            // sidebar-badge-updated di-dispatch.
            'titleBadge' => $pesananTokoPaid + $testimoniBaru + $ulasanBaru + $helpdeskBaru,
        ];
    }
}; ?>

<div id="sidebar">
    {{-- Badge di judul tab (mis. "(3) lemon"). data-n selalu diperbarui saat
         sidebar re-render (aturan hitung = badge sidebar). Skrip menaruh prefix
         "(N)" ke document.title & ikut segar via MutationObserver + navigasi. --}}
    <span id="ttl-badge" data-n="{{ (int) $titleBadge }}" hidden aria-hidden="true"></span>
    @script
    <script>
        (() => {
            const apply = () => {
                const el = document.getElementById('ttl-badge');
                const n = el ? (parseInt(el.dataset.n) || 0) : 0;
                const base = document.title.replace(/^\(\d+\)\s*/, '');
                document.title = n > 0 ? '(' + n + ') ' + base : base;
            };
            apply();
            const el = document.getElementById('ttl-badge');
            if (el && !el.__ttlObserved) {
                el.__ttlObserved = true;
                new MutationObserver(apply).observe(el, { attributes: true, attributeFilter: ['data-n'] });
            }
            if (!window.__ttlBadgeNav) {
                window.__ttlBadgeNav = true;
                document.addEventListener('livewire:navigated', apply);
            }
        })();
    </script>
    @endscript
    <style>
        /* ============ Sidebar — tema lemon (seragam dengan halaman login) ============ */
        #sidebar .sidebar-wrapper {
            background: #ffffff;
            border-right: 1px solid #eef0f4;
        }

        /* ----- Brand: logo & teks persis seperti login ----- */
        #sidebar .sidebar-header {
            padding: 1.15rem 1.1rem .5rem;
        }

        #sidebar .logo {
            padding: 0;
        }

        #sidebar .lemon-brand-side {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
        }

        #sidebar .lemon-logo {
            width: 44px;
            height: 44px;
            margin: 0;
            flex-shrink: 0;
            filter: drop-shadow(0 8px 14px rgba(202, 138, 4, .35));
        }

        #sidebar .lemon-spin {
            transform-box: fill-box;
            transform-origin: center;
            animation: lemonBob 4s ease-in-out infinite;
        }

        #sidebar .lemon-pulse {
            transform-box: fill-box;
            transform-origin: center;
            animation: lemonJuice 4s ease-in-out infinite;
        }

        @keyframes lemonBob {
            0%, 100% { transform: rotate(-8deg) translateY(0); }
            50% { transform: rotate(8deg) translateY(-5px); }
        }

        @keyframes lemonJuice {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        #sidebar .lsb-text {
            display: flex;
            flex-direction: column;
            line-height: 1;
        }

        #sidebar .lemon-brand {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -.5px;
            line-height: 1;
            margin: 0;
            background: linear-gradient(135deg, #ca8a04, #4d7c0f);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            white-space: nowrap;
        }

        #sidebar .lemon-by {
            font-size: .6rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 700;
            color: #a3a3a3;
            margin: 3px 0 0;
            white-space: nowrap;
        }

        /* ----- Judul section ----- */
        #sidebar .sidebar-title {
            color: #a0a6b4;
            font-size: .68rem;
            letter-spacing: .14em;
            text-transform: uppercase;
            font-weight: 700;
            padding: 0 1.1rem;
            margin: 1.35rem 0 .35rem;
        }

        /* ----- Item menu ----- */
        #sidebar .sidebar-menu {
            padding: 0 .35rem;
        }

        #sidebar .sidebar-item {
            margin: 3px .3rem;
        }

        #sidebar .sidebar-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            border-radius: 12px;
            padding: .68rem .85rem;
            color: #556070;
            font-weight: 600;
            font-size: .93rem;
            transition: background .18s ease, color .18s ease;
        }

        #sidebar .sidebar-link i {
            font-size: 1.08rem;
            color: #9aa0ae;
            min-width: 20px;
            text-align: center;
            transition: color .18s ease;
        }

        #sidebar .sidebar-link:hover {
            background: rgba(132, 204, 22, .10);
            color: #4d7c0f;
        }

        #sidebar .sidebar-link:hover i {
            color: #65a30d;
        }

        /* Aktif — item sederhana (Dashboard, Task) jadi pil gradien lime */
        #sidebar .sidebar-item.active:not(.has-sub)>.sidebar-link {
            background: linear-gradient(135deg, #84cc16, #4d7c0f);
            color: #fff;
            box-shadow: 0 8px 16px rgba(101, 163, 13, .28);
        }

        #sidebar .sidebar-item.active:not(.has-sub)>.sidebar-link i {
            color: #fff;
        }

        /* Aktif — parent (punya submenu): sorotan lembut */
        #sidebar .sidebar-item.has-sub.active>.sidebar-link {
            background: rgba(132, 204, 22, .10);
        }

        /* Recolor semua text-primary di sidebar jadi lime (state aktif parent) */
        #sidebar .text-primary {
            color: #4d7c0f !important;
        }

        #sidebar i.text-primary {
            color: #65a30d !important;
        }

        /* ----- Submenu ----- */
        #sidebar .submenu-link {
            border-radius: 10px;
            padding: .5rem .8rem .5rem 2.7rem;
            color: #6b7280;
            font-weight: 500;
            font-size: .88rem;
            transition: background .16s ease, color .16s ease;
        }

        #sidebar .submenu-link:hover {
            background: rgba(132, 204, 22, .08);
            color: #4d7c0f;
        }

        /* ===== Badge jumlah (mis. Pesanan Toko yang sudah dibayar) ===== */
        #sidebar .sidebar-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 19px;
            height: 19px;
            padding: 0 6px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: .67rem;
            font-weight: 700;
            line-height: 1;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(239, 68, 68, .45);
        }

        /* Menu dropdown punya chevron absolut di kanan (right:15px, lebar 20px) —
           beri jarak agar badge tidak tertimpa chevron. */
        #sidebar .sidebar-item.has-sub>.sidebar-link .sidebar-badge {
            margin-right: 24px;
        }

        /* .submenu-link aslinya block; dijadikan flex HANYA saat memuat badge
           supaya badge bisa didorong ke kanan tanpa mengubah submenu lain. */
        #sidebar .submenu-link.has-badge {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        #sidebar .submenu-item.active>.submenu-link {
            background: linear-gradient(135deg, #84cc16, #4d7c0f);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 8px 16px rgba(101, 163, 13, .28);
        }

        #sidebar .submenu-item.active>.submenu-link:hover {
            color: #fff;
        }

        /* ----- Logout ----- */
        #sidebar .sidebar-item button.sidebar-link {
            color: #e11d48;
        }

        #sidebar .sidebar-item button.sidebar-link i {
            color: #f43f5e;
        }

        #sidebar .sidebar-item button.sidebar-link:hover {
            background: rgba(244, 63, 94, .10);
            color: #be123c;
        }

        #sidebar .sidebar-item button.sidebar-link:hover i {
            color: #e11d48;
        }

        #sidebar .sidebar-toggler .sidebar-hide {
            color: #9aa0ae;
        }
    </style>
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ route('admin.dashboard') }}" class="lemon-brand-side text-decoration-none" wire:navigate>
                        @include('livewire.pages.auth.partials.lemon-logo')
                        <span class="lsb-text">
                            <span class="lemon-brand">lemon</span>
                            <span class="lemon-by">by acm</span>
                        </span>
                    </a>
                </div>
                <div class="sidebar-toggler x">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu</li>

                @if (auth()->user()->hasPermission('view_dashboard'))
                <li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class='sidebar-link' wire:navigate>
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('view_task'))
                <li class="sidebar-item {{ request()->routeIs('admin.task-saya.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.task-saya.index') }}" class="sidebar-link" wire:navigate>
                        <i class="bi bi-clipboard-check"></i>
                        <span>Task Saya</span>
                    </a>
                </li>

                @endif

                @if (auth()->user()->hasAnyPermission(['view_pesananrsc', 'view_pemesanantoko', 'view_ebook']))
                @php
                    // Badge Pesanan Toko = pesanan baru dibayar + pengecekan jasa
                    // yang menunggu. Digabung karena keduanya sama-sama pekerjaan
                    // di menu yang sama; rinciannya dijelaskan lewat title.
                    $pesananTokoBadge = $pesananTokoPaid + $pengecekanBaru;
                    $pesananTokoTitle = trim(
                        ($pesananTokoPaid > 0 ? "{$pesananTokoPaid} pesanan menunggu diproses. " : '').
                        ($pengecekanBaru > 0 ? "{$pengecekanBaru} pengecekan plagiasi menunggu." : '')
                    );
                @endphp
                <li
                    class="sidebar-item has-sub {{ request()->routeIs('admin.pesananrsc.*') || request()->routeIs('admin.pesanantoko.*') || request()->routeIs('admin.ebook.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0)"
                        class="sidebar-link {{ request()->routeIs('admin.pesananrsc.*') || request()->routeIs('admin.pesanantoko.*') || request()->routeIs('admin.ebook.*') ? 'text-primary fw-bold' : '' }}">
                        <i
                            class="bi bi-cart {{ request()->routeIs('admin.pesananrsc.*') || request()->routeIs('admin.pesanantoko.*') || request()->routeIs('admin.ebook.*') ? 'text-primary' : '' }}"></i>
                        <span
                            class="{{ request()->routeIs('admin.pesananrsc.*') || request()->routeIs('admin.pesanantoko.*') || request()->routeIs('admin.ebook.*') ? 'text-primary' : '' }}">
                            Pesanan
                        </span>
                        @if ($pesananTokoBadge > 0)
                        <span class="sidebar-badge ms-auto" title="{{ $pesananTokoTitle }}">
                            {{ $pesananTokoBadge > 99 ? '99+' : $pesananTokoBadge }}
                        </span>
                        @endif
                    </a>
                    <ul class="submenu">
                        @if (auth()->user()->hasPermission('view_pesananrsc'))
                        <li class="submenu-item {{ request()->routeIs('admin.pesananrsc.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.pesananrsc.index') }}" class="submenu-link">
                                Pesanan RSC
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_pemesanantoko'))
                        <li class="submenu-item {{ request()->routeIs('admin.pesanantoko.*') ? 'active' : '' }}">
                            <a wire:navigate class="submenu-link @if ($pesananTokoBadge > 0) has-badge @endif"
                                href="{{ route('admin.pesanantoko.index') }}">
                                <span>Pesanan Toko</span>
                                @if ($pesananTokoBadge > 0)
                                <span class="sidebar-badge ms-auto" title="{{ $pesananTokoTitle }}">
                                    {{ $pesananTokoBadge > 99 ? '99+' : $pesananTokoBadge }}
                                </span>
                                @endif
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_ebook'))
                        <li class="submenu-item {{ request()->routeIs('admin.ebook.*') ? 'active' : '' }}">
                            <a wire:navigate class="submenu-link" href="{{ route('admin.ebook.index') }}">Ebook
                                Bonus</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (auth()->user()->hasAnyPermission(['view_banners', 'view_testimoni', 'view_productreview', 'view_customer_message']))
                <li class="sidebar-item has-sub {{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.testimoni.*') || request()->routeIs('admin.reviews.*') || request()->routeIs('admin.customer-message.*')  ? 'active open' : '' }}">
                    <a href="javascript:void(0)"
                        class="sidebar-link {{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.testimoni.*') || request()->routeIs('admin.reviews.*') || request()->routeIs('admin.customer-message.*')  ? 'text-primary fw-bold' : '' }}">
                        <i class="bi bi-shop {{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.testimoni.*') || request()->routeIs('admin.reviews.*') || request()->routeIs('admin.customer-message.*')  ? 'text-primary' : '' }}"></i>
                        <span class="{{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.testimoni.*') || request()->routeIs('admin.reviews.*') || request()->routeIs('admin.customer-message.*') ? 'text-primary' : '' }}">
                            E-Commerce
                        </span>
                        @php
                            // Badge induk = gabungan semua yang butuh ditinjau di dalamnya.
                            $ecommerceBaru = $testimoniBaru + $ulasanBaru + $helpdeskBaru;
                            $ecommerceTitle = collect([
                                $testimoniBaru > 0 ? $testimoniBaru.' testimoni belum ditinjau' : null,
                                $ulasanBaru > 0 ? $ulasanBaru.' ulasan menunggu moderasi' : null,
                                $helpdeskBaru > 0 ? $helpdeskBaru.' pesan helpdesk belum dibaca' : null,
                            ])->filter()->implode(' • ');
                        @endphp
                        @if ($ecommerceBaru > 0)
                        <span class="sidebar-badge ms-auto" title="{{ $ecommerceTitle }}">
                            {{ $ecommerceBaru > 99 ? '99+' : $ecommerceBaru }}
                        </span>
                        @endif
                    </a>
                    <ul class="submenu">
                        @if (auth()->user()->hasPermission('view_banners'))
                        <li class="submenu-item {{ request()->routeIs('admin.Banners.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.Banners.index') }}" class="submenu-link">Data
                                Banner</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_testimoni'))
                        <li class="submenu-item {{ request()->routeIs('admin.testimoni.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.testimoni.index') }}"
                                class="submenu-link @if ($testimoniBaru > 0) has-badge @endif">
                                <span>Data Testimoni</span>
                                @if ($testimoniBaru > 0)
                                <span class="sidebar-badge ms-auto"
                                    title="{{ $testimoniBaru }} testimoni pelanggan belum ditinjau">
                                    {{ $testimoniBaru > 99 ? '99+' : $testimoniBaru }}
                                </span>
                                @endif
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_productreview'))
                        <li class="submenu-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.reviews.index') }}"
                                class="submenu-link @if ($ulasanBaru > 0) has-badge @endif">
                                <span>Moderasi Ulasan Produk</span>
                                @if ($ulasanBaru > 0)
                                <span class="sidebar-badge ms-auto"
                                    title="{{ $ulasanBaru }} ulasan menunggu moderasi">
                                    {{ $ulasanBaru > 99 ? '99+' : $ulasanBaru }}
                                </span>
                                @endif
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_customer_message'))
                        <li class="submenu-item {{ request()->routeIs('admin.customer-message.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.customer-message.index') }}"
                                class="submenu-link @if ($helpdeskBaru > 0) has-badge @endif">
                                <span>Pesan Pelanggan (Helpdesk)</span>
                                @if ($helpdeskBaru > 0)
                                <span class="sidebar-badge ms-auto"
                                    title="{{ $helpdeskBaru }} pesan belum dibaca">
                                    {{ $helpdeskBaru > 99 ? '99+' : $helpdeskBaru }}
                                </span>
                                @endif
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (auth()->user()->hasPermission('view_blog'))
                <li class="sidebar-item has-sub {{ request()->routeIs('admin.blog.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0)"
                        class="sidebar-link {{ request()->routeIs('admin.blog.*') ? 'text-primary fw-bold' : '' }}">
                        <i class="bi bi-journal-richtext {{ request()->routeIs('admin.blog.*') ? 'text-primary' : '' }}"></i>
                        <span class="{{ request()->routeIs('admin.blog.*') ? 'text-primary' : '' }}">
                            Blog
                        </span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item {{ request()->routeIs('admin.blog.index') || request()->routeIs('admin.blog.create') || request()->routeIs('admin.blog.edit') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.blog.index') }}" class="submenu-link">Semua Artikel</a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.blog.categories') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.blog.categories') }}" class="submenu-link">Kategori</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if (auth()->user()->hasPermission('view_promo'))
                <li class="sidebar-item has-sub {{ request()->routeIs('admin.promo.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0)"
                        class="sidebar-link {{ request()->routeIs('admin.promo.*') ? 'text-primary fw-bold' : '' }}">
                        <i class="bi bi-tag {{ request()->routeIs('admin.promo.*') ? 'text-primary' : '' }}"></i>
                        <span class="{{ request()->routeIs('admin.promo.*') ? 'text-primary' : '' }}">
                            Promo
                        </span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item {{ request()->routeIs('admin.promo.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.promo.index') }}" class="submenu-link">Data
                                Daftar Promo</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if (auth()->user()->hasAnyPermission(['view_dataakun', 'view_product', 'view_bundlings']))
                <li
                    class="sidebar-item has-sub {{ request()->routeIs('admin.DataAkun.*') || request()->routeIs('admin.product.*') || request()->routeIs('admin.Bundlings.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0)"
                        class="sidebar-link {{ request()->routeIs('admin.DataAkun.*') || request()->routeIs('admin.product.*') || request()->routeIs('admin.Bundlings.*') ? 'text-primary fw-bold' : '' }}">
                        <i
                            class="bi bi-box {{ request()->routeIs('admin.DataAkun.*') || request()->routeIs('admin.product.*') || request()->routeIs('admin.Bundlings.*') ? 'text-primary' : '' }}"></i>
                        <span
                            class="{{ request()->routeIs('admin.DataAkun.*') || request()->routeIs('admin.product.*') || request()->routeIs('admin.Bundlings.*') ? 'text-primary' : '' }}">
                            Produk
                        </span>
                    </a>
                    <ul class="submenu">
                        @if (auth()->user()->hasPermission('view_dataakun'))
                        <li class="submenu-item {{ request()->routeIs('admin.DataAkun.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.DataAkun.index') }}" class="submenu-link">Data Akun</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_product'))
                        <li class="submenu-item {{ request()->routeIs('admin.product.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.product.index') }}" class="submenu-link">Product</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_bundlings'))
                        <li class="submenu-item {{ request()->routeIs('admin.Bundlings.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.Bundlings.index') }}" class="submenu-link">Product Bundling</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (auth()->user()->hasPermission('view_customer'))
                <li class="sidebar-item has-sub {{ request()->routeIs('admin.customer.*') ? 'active' : '' }}">
                    <a href="#"
                        class="sidebar-link {{ request()->routeIs('admin.customer.*') ? 'text-primary fw-bold' : '' }}">
                        <i class="bi bi-people {{ request()->routeIs('admin.customer.*') ? 'text-primary' : '' }}"></i>
                        <span class="{{ request()->routeIs('admin.customer.*') ? 'text-primary' : '' }}">
                            Pelanggan
                        </span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item {{ request()->routeIs('admin.customer.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.customer.index') }}" class="submenu-link">Data
                                Pelanggan</a>
                        </li>
                    </ul>
                </li>
                @endif

                <!-- section menu data dan laporan -->
                @if (auth()->user()->hasAnyPermission(['view_cashflow', 'view_spending', 'view_modal', 'view_pemasukan', 'view_harga_modal', 'view_loan', 'view_gajikaryawan']))
                <li class="mt-4 sidebar-title">Data &amp; Laporan</li>
                <li
                    class="sidebar-item has-sub
                    {{ request()->routeIs('admin.spending.*') || request()->routeIs('admin.cashflow.*') || request()->routeIs('admin.loan.*') || request()->routeIs('admin.gajikaryawan.*') || request()->routeIs('admin.penyelesaian-task.*') || request()->routeIs('admin.pengembalian.*') || request()->routeIs('admin.modal.*') || request()->routeIs('admin.pemasukan.*') || request()->routeIs('admin.hargamodal.*') ? 'active open' : '' }}">

                    <a href="#"
                        class="sidebar-link {{ request()->routeIs('admin.spending.*') || request()->routeIs('admin.loan.*') || request()->routeIs('admin.gajikaryawan.*') || request()->routeIs('admin.penyelesaian-task.*') || request()->routeIs('admin.cashflow.*') || request()->routeIs('admin.pengembalian.*') || request()->routeIs('admin.modal.*') || request()->routeIs('admin.pemasukan.*') || request()->routeIs('admin.hargamodal.*') ? 'text-primary fw-bold' : '' }}">
                        <i
                            class="bi bi-cash-coin {{ request()->routeIs('admin.spending.*') || request()->routeIs('admin.loan.*') || request()->routeIs('admin.cashflow.*') || request()->routeIs('admin.gajikaryawan.*') || request()->routeIs('admin.penyelesaian-task.*') || request()->routeIs('admin.pengembalian.*') || request()->routeIs('admin.modal.*') || request()->routeIs('admin.pemasukan.*') || request()->routeIs('admin.hargamodal.*') ? 'text-primary' : '' }}"></i>
                        <span
                            class="{{ request()->routeIs('admin.spending.*') || request()->routeIs('admin.cashflow.*') || request()->routeIs('admin.loan.*') || request()->routeIs('admin.gajikaryawan.*') || request()->routeIs('admin.penyelesaian-task.*') || request()->routeIs('admin.pengembalian.*') || request()->routeIs('admin.modal.*') || request()->routeIs('admin.pemasukan.*') || request()->routeIs('admin.hargamodal.*') ? 'text-primary' : '' }}">
                            Keuangan
                        </span>
                    </a>

                    <ul class="submenu">
                        @if (auth()->user()->hasPermission('view_cashflow'))
                        <li class="submenu-item {{ request()->routeIs('admin.cashflow.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.cashflow.index') }}" class="submenu-link">
                                Cashflow
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_spending'))
                        <li class="submenu-item {{ request()->routeIs('admin.spending.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.spending.index') }}" class="submenu-link">
                                Pengeluaran
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_modal'))
                        <li class="submenu-item {{ request()->routeIs('admin.modal.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.modal.index') }}" class="submenu-link">
                                Modal
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_pemasukan'))
                        <li class="submenu-item {{ request()->routeIs('admin.pemasukan.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.pemasukan.index') }}" class="submenu-link">
                                Pemasukan Lainnya
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_harga_modal'))
                        <li class="submenu-item {{ request()->routeIs('admin.hargamodal.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.hargamodal.index') }}" class="submenu-link">
                                Harga Modal Akun
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_loan'))
                        <li
                            class="submenu-item {{ request()->routeIs('admin.loan.*') || request()->routeIs('admin.pengembalian.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.loan.index') }}" class="submenu-link">
                                Peminjaman
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_gajikaryawan'))
                        <li class="submenu-item {{ request()->routeIs('admin.gajikaryawan.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.gajikaryawan.index') }}" class="submenu-link">
                                Gaji Karyawan
                            </a>
                        </li>
                        @if (auth()->user()->hasPermission('manage_task'))
                        <li class="submenu-item {{ request()->routeIs('admin.penyelesaian-task.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.penyelesaian-task.index') }}" class="submenu-link">
                                Penyelesaian Task
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </li>
                @endif

                <li class="sidebar-item  has-sub {{ request()->routeIs('admin.account.*') ? 'active' : '' }}">
                    <a href="#"
                        class='sidebar-link {{ request()->routeIs('admin.account.*') ? 'text-primary fw-bold' : '' }}'>
                        <i class="bi bi-person {{ request()->routeIs('admin.account.*') ? 'text-primary' : '' }}"></i>
                        <span class="{{ request()->routeIs('admin.account.*') ? 'text-primary' : '' }}">Akun</span>
                    </a>

                    <ul class="submenu {{ request()->routeIs('admin.account.*') ? 'active' : '' }}">
                        {{-- Profil bisa diakses semua pengguna yang login --}}
                        <li class="submenu-item {{ request()->routeIs('admin.account.profile') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.account.profile') }}"
                                class="submenu-link">Pengaturan Profil</a>
                        </li>
                        @if (auth()->user()->hasPermission('view_roles'))
                        <li class="submenu-item  {{ request()->routeIs('admin.account.role') || request()->routeIs('admin.account.role.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.account.role') }}"
                                class="submenu-link">Pengaturan Role</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_permission'))
                        <li class="submenu-item {{ request()->routeIs('admin.account.permission') || request()->routeIs('admin.account.permission.*')? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.account.permission') }}"
                                class="submenu-link">Permission Akun</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_activity_log'))
                        <li class="submenu-item {{ request()->routeIs('admin.account.activity-log') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.account.activity-log') }}"
                                class="submenu-link">Log Aktivitas</a>
                        </li>
                        @endif
                    </ul>
                </li>

                <!-- section karir & karyawan-->
                @if (auth()->user()->hasPermission('view_presensi'))
                <li class="mt-4 sidebar-title">Kepegawaian</li>
                <li class="sidebar-item has-sub {{ request()->routeIs('admin.presensi.*') ? 'active open' : '' }}">
                    <a href="#"
                        class="sidebar-link {{ request()->routeIs('admin.presensi.*') ? 'text-primary fw-bold' : '' }}">
                        <i class="bi bi-fingerprint {{ request()->routeIs('admin.presensi.*') ? 'text-primary' : '' }}"></i>
                        <span class="{{ request()->routeIs('admin.presensi.*') ? 'text-primary' : '' }}">Presensi</span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item {{ request()->routeIs('admin.presensi.index') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.presensi.index') }}" class="submenu-link">Presensi Saya</a>
                        </li>
                        @if (auth()->user()->hasPermission('view_all_presensi'))
                        <li class="submenu-item {{ request()->routeIs('admin.presensi.rekap') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.presensi.rekap') }}" class="submenu-link">Rekap Presensi</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('manage_presensi_setting'))
                        <li class="submenu-item {{ request()->routeIs('admin.presensi.pengaturan') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.presensi.pengaturan') }}" class="submenu-link">Pengaturan</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (auth()->user()->hasAnyPermission(['view_karyawan', 'view_lowongan', 'view_pelamar', 'view_message']))
                <li class="mt-4 sidebar-title">Karyawan & Karir</li>
                <li
                    class="sidebar-item has-sub
                    {{ request()->routeIs('admin.lowongan.*') || request()->routeIs('admin.karyawan.*') || request()->routeIs('admin.pelamar.*') || request()->routeIs('admin.message.*') ? 'active' : '' }}">

                    <a href="#"
                        class="sidebar-link {{ request()->routeIs('admin.lowongan.*') || request()->routeIs('admin.karyawan.*') || request()->routeIs('admin.pelamar.*') || request()->routeIs('admin.message.*') ? 'text-primary fw-bold' : '' }}">
                        <i
                            class="bi bi-briefcase {{ request()->routeIs('admin.lowongan.*') || request()->routeIs('admin.karyawan.*') || request()->routeIs('admin.pelamar.*') || request()->routeIs('admin.message.*') ? 'text-primary' : '' }}"></i>
                        <span
                            class="{{ request()->routeIs('admin.lowongan.*') || request()->routeIs('admin.karyawan.*') || request()->routeIs('admin.pelamar.*') || request()->routeIs('admin.message.*') ? 'text-primary' : '' }}">
                            karir
                        </span>
                    </a>

                    <ul class="submenu">
                        @if (auth()->user()->hasPermission('view_karyawan'))
                        <li class="submenu-item {{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.karyawan.index') }}" class="submenu-link">
                                Karyawan
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_lowongan'))
                        <li class="submenu-item {{ request()->routeIs('admin.lowongan.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.lowongan.index') }}" class="submenu-link">
                                Lowongan Kerja
                            </a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_pelamar'))
                        <li class="submenu-item {{ request()->routeIs('admin.pelamar.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.pelamar.index') }}"
                                class="submenu-link">Pelamar</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_message'))
                        <li class="submenu-item {{ request()->routeIs('admin.message.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.message.index') }}"
                                class="submenu-link">Pesan Masuk</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <li class="sidebar-item">
                    <button wire:click="logout" class="sidebar-link btn btn-link w-100 text-start">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const sidebar = document.getElementById("sidebar");
        const toggler = document.querySelector(".sidebar-hide");

        toggler.addEventListener("click", (e) => {
            e.preventDefault();
            sidebar.classList.toggle("active");
        });
    });
</script>