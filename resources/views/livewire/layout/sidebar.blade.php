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
        .asthana-brand {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
        }

        .asthana-brand .asthana-logo {
            height: 50px;
            width: auto;
            max-width: none;
            flex-shrink: 0;
        }

        .asthana-brand .ab-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.14;
        }

        .asthana-brand .ab-main {
            font-weight: 800;
            font-size: .96rem;
            letter-spacing: .01em;
            color: #1e293b;
            white-space: nowrap;
        }

        .asthana-brand .ab-sub {
            font-weight: 600;
            font-size: .68rem;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: #8a90a3;
            margin-top: 1px;
            white-space: nowrap;
        }

        .sidebar-header .logo {
            padding: .35rem 0;
        }
    </style>
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ route('admin.dashboard') }}" class="asthana-brand text-decoration-none" wire:navigate>
                        <img src="{{ asset('storage/img/archive/logo-icon.png') }}" alt="Asthana Cipta Mandiri"
                            class="asthana-logo" onerror="this.style.display='none'">
                        <span class="ab-text">
                            <span class="ab-main">PT. Asthana</span>
                            <span class="ab-sub">Cipta Mandiri</span>
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

                @if (auth()->user()->hasAnyPermission(['view_banners', 'view_customer_message']))
                <li class="sidebar-item has-sub {{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.customer-message.*')  ? 'active open' : '' }}">
                    <a href="javascript:void(0)"
                        class="sidebar-link {{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.customer-message.*')  ? 'text-primary fw-bold' : '' }}">
                        <i class="bi bi-shop {{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.customer-message.*')  ? 'text-primary' : '' }}"></i>
                        <span class="{{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.customer-message.*') ? 'text-primary' : '' }}">
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
                        @if (auth()->user()->hasPermission('view_customer_message'))
                        <li class="submenu-item {{ request()->routeIs('admin.customer-message.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.customer-message.index') }}" class="submenu-link">Pesan Pelanggan (Helpdesk)</a>
                        </li>
                        @endif
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
                            <a href="{{ route('admin.DataAkun.index') }}" class="submenu-link">Data Akun</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_product'))
                        <li class="submenu-item {{ request()->routeIs('admin.product.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.product.index') }}" class="submenu-link">Product</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasPermission('view_bundlings'))
                        <li class="submenu-item {{ request()->routeIs('admin.Bundlings.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.Bundlings.index') }}" class="submenu-link">Product Bundling</a>
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