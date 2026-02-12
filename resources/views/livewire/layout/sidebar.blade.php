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
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ route('admin.dashboard') }}" class="" wire:navigate>
                        <small>Phoenix</small>
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

                <li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class='sidebar-link' wire:navigate>
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if (auth()->user()->hasAnyRole(['admin', 'admin-mimin']))
                <li
                    class="sidebar-item has-sub {{ request()->routeIs('admin.pesananrsc.*') || request()->routeIs('admin.pesanantoko.*') ? 'active open' : '' }}">
                    <a href="javascript:void(0)"
                        class="sidebar-link {{ request()->routeIs('admin.pesananrsc.*') || request()->routeIs('admin.pesanantoko.*') ? 'text-primary fw-bold' : '' }}">
                        <i
                            class="bi bi-cart {{ request()->routeIs('admin.pesananrsc.*') || request()->routeIs('admin.pesanantoko.*') ? 'text-primary' : '' }}"></i>
                        <span
                            class="{{ request()->routeIs('admin.pesananrsc.*') || request()->routeIs('admin.pesanantoko.*') ? 'text-primary' : '' }}">
                            Pesanan
                        </span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item {{ request()->routeIs('admin.pesananrsc.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.pesananrsc.index') }}" class="submenu-link">
                                Pesanan RSC
                            </a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.pesanantoko.*') ? 'active' : '' }}">
                            <a wire:navigate class="submenu-link" href="{{ route('admin.pesanantoko.index') }}">Pesanan
                                Toko</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if (auth()->user()->hasAnyRole(['admin', 'finance', 'admin-mimin']))
                <li class="sidebar-item has-sub {{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.customer-message.*')  ? 'active open' : '' }}">
                    <a href="javascript:void(0)"
                        class="sidebar-link {{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.customer-message.*')  ? 'text-primary fw-bold' : '' }}">
                        <i class="bi bi-shop {{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.customer-message.*')  ? 'text-primary' : '' }}"></i>
                        <span class="{{ request()->routeIs('admin.Banners.*') || request()->routeIs('admin.customer-message.*') ? 'text-primary' : '' }}">
                            E-Commerce
                        </span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item {{ request()->routeIs('admin.Banners.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.Banners.index') }}" class="submenu-link">Data
                                Banner</a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.customer-message.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.customer-message.index') }}" class="submenu-link">Pesan Masuk</a>
                        </li>
                    </ul>
                </li>

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
                        <li class="submenu-item {{ request()->routeIs('admin.DataAkun.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.DataAkun.index') }}" class="submenu-link">Data Akun</a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.product.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.product.index') }}" class="submenu-link">Product</a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.Bundlings.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.Bundlings.index') }}" class="submenu-link">Product Bundling</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if (auth()->user()->hasAnyRole(['admin', 'admin-mimin']))
                <li class="sidebar-item has-sub {{ request()->routeIs('admin.customer.*') ? 'active' : '' }}">
                    <a href="#"
                        class="sidebar-link {{ request()->routeIs('admin.customer.*') ? 'text-primary fw-bold' : '' }}">
                        <i class="bi bi-people {{ request()->routeIs('admin.customer.*') ? 'text-primary' : '' }}"></i>
                        <span class="{{ request()->routeIs('admin.customer.*') ? 'text-primary' : '' }}">
                            Pelanggan
                        </span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item {{ request()->routeIs('admin.customer.index') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.customer.index') }}" class="submenu-link">Data
                                Pelanggan</a>
                        </li>
                    </ul>
                </li>
                @endif

                <!-- section menu data dan laporan -->
                @if (auth()->user()->hasAnyRole(['admin', 'finance']))
                <li class="mt-4 sidebar-title">Data &amp; Laporan</li>
                <li
                    class="sidebar-item has-sub
                    {{ request()->routeIs('admin.spending.*') || request()->routeIs('admin.cashflow.*') || request()->routeIs('admin.loan.*') || request()->routeIs('admin.gajikaryawan.*') || request()->routeIs('admin.pengembalian.*') ? 'active' : '' }}">

                    <a href="#"
                        class="sidebar-link {{ request()->routeIs('admin.spending.*') || request()->routeIs('admin.loan.*') || request()->routeIs('admin.gajikaryawan.*') || request()->routeIs('admin.cashflow.*') || request()->routeIs('admin.pengembalian.*') ? 'text-primary fw-bold' : '' }}">
                        <i
                            class="bi bi-cash-coin {{ request()->routeIs('admin.spending.*') || request()->routeIs('admin.loan.*') || request()->routeIs('admin.cashflow.*') || request()->routeIs('admin.gajikaryawan.*') || request()->routeIs('admin.pengembalian.*') ? 'text-primary' : '' }}"></i>
                        <span
                            class="{{ request()->routeIs('admin.spending.*') || request()->routeIs('admin.cashflow.*') || request()->routeIs('admin.loan.*') || request()->routeIs('admin.gajikaryawan.*') || request()->routeIs('admin.pengembalian.*') ? 'text-primary' : '' }}">
                            Keuangan
                        </span>
                    </a>

                    <ul class="submenu">
                        <li class="submenu-item {{ request()->routeIs('admin.cashflow.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.cashflow.index') }}" class="submenu-link">
                                Cashflow
                            </a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.spending.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.spending.index') }}" class="submenu-link">
                                Pengeluaran
                            </a>
                        </li>
                        <li
                            class="submenu-item {{ request()->routeIs('admin.loan.*') || request()->routeIs('admin.pengembalian.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.loan.index') }}" class="submenu-link">
                                Peminjaman
                            </a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.gajikaryawan.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.gajikaryawan.index') }}" class="submenu-link">
                                Gaji Karyawan
                            </a>
                        </li>
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
                        @if (auth()->user()->hasAnyRole(['admin', 'finance', 'admin-mimin']))
                        <li class="submenu-item {{ request()->routeIs('admin.account.profile') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.account.profile') }}"
                                class="submenu-link">Pengaturan Profil</a>
                        </li>
                        @endif
                        @if (auth()->user()->hasRole('admin'))
                        <li class="submenu-item  {{ request()->routeIs('admin.account.role') || request()->routeIs('admin.account.role.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.account.role') }}"
                                class="submenu-link">Pengaturan Role</a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.account.permission') || request()->routeIs('admin.account.permission.*')? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.account.permission') }}"
                                class="submenu-link">Permission Akun</a>
                        </li>
                        @endif
                    </ul>
                </li>

                <!-- section karir & karyawan-->
                @if (auth()->user()->hasAnyRole(['admin', 'finance']))
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
                        <li class="submenu-item {{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.karyawan.index') }}" class="submenu-link">
                                Karyawan
                            </a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.lowongan.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.lowongan.index') }}" class="submenu-link">
                                Lowongan Kerja
                            </a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.pelamar.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.pelamar.index') }}"
                                class="submenu-link">Pelamar</a>
                        </li>
                        <li class="submenu-item {{ request()->routeIs('admin.message.*') ? 'active' : '' }}">
                            <a wire:navigate href="{{ route('admin.message.index') }}"
                                class="submenu-link">Pesan Masuk</a>
                        </li>
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