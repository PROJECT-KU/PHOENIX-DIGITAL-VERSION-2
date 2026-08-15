{{--
    Penomoran halaman untuk data yang datang dari API Orcha.

    Bukan paginator Laravel — angkanya dari meta balasan API, dan perpindahannya
    lewat metode keHalaman() di komponen.

    Variabel: $meta
--}}
@php
    $halamanKini = (int) ($meta['halaman'] ?? 1);
    $halamanAkhir = (int) ($meta['halaman_terakhir'] ?? 1);
    $total = (int) ($meta['total'] ?? 0);

    // Tampilkan jendela sempit di sekitar halaman aktif supaya tidak memanjang
    // saat datanya sudah ratusan.
    $mulai = max(1, $halamanKini - 2);
    $selesai = min($halamanAkhir, $halamanKini + 2);
@endphp

@if ($halamanAkhir > 1)
    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 mt-3">
        <span class="text-muted small">
            Halaman {{ $halamanKini }} dari {{ $halamanAkhir }} · {{ $total }} data
        </span>

        <nav class="orcha-halaman">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $halamanKini <= 1 ? 'disabled' : '' }}">
                    <button type="button" class="page-link" wire:click="keHalaman({{ $halamanKini - 1 }})">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </li>

                @if ($mulai > 1)
                    <li class="page-item">
                        <button type="button" class="page-link" wire:click="keHalaman(1)">1</button>
                    </li>
                    @if ($mulai > 2)
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    @endif
                @endif

                @for ($nomor = $mulai; $nomor <= $selesai; $nomor++)
                    <li class="page-item {{ $nomor === $halamanKini ? 'active' : '' }}">
                        <button type="button" class="page-link" wire:click="keHalaman({{ $nomor }})">{{ $nomor }}</button>
                    </li>
                @endfor

                @if ($selesai < $halamanAkhir)
                    @if ($selesai < $halamanAkhir - 1)
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    @endif
                    <li class="page-item">
                        <button type="button" class="page-link" wire:click="keHalaman({{ $halamanAkhir }})">{{ $halamanAkhir }}</button>
                    </li>
                @endif

                <li class="page-item {{ $halamanKini >= $halamanAkhir ? 'disabled' : '' }}">
                    <button type="button" class="page-link" wire:click="keHalaman({{ $halamanKini + 1 }})">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
@endif
