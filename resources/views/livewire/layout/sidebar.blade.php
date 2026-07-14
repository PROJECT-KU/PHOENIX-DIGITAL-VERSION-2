<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/');
    }
}; ?>

<div id="sidebar">
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
                            <a wire:navigate class="submenu-link" href="{{ route('admin.pesanantoko.index') }}">Pesanan
                                Toko</a>
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
                            <a wire:navigate href="{{ route('admin.testimoni.index') }}" class="submenu-link">Data
                                Testimoni</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_productreview'))
                        <li class="submenu-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.reviews.index') }}" class="submenu-link">Moderasi Ulasan Produk</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_customer_message'))
                        <li class="submenu-item {{ request()->routeIs('admin.customer-message.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.customer-message.index') }}" class="submenu-link">Pesan Pelanggan (Helpdesk)</a>
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