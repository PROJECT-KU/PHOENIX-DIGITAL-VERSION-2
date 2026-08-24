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
    $perHalaman = (int) ($meta['per_halaman'] ?? 0);

    // Rentang baris yang sedang dilihat: "1–10 dari 37" menjawab pertanyaan yang
    // sebenarnya ada di kepala admin — sudah sampai mana saya, dan masih ada
    // berapa lagi — sedangkan "halaman 1 dari 4" menyerahkan perkaliannya
    // kepada yang membaca.
    $baris1 = $total > 0 && $perHalaman > 0 ? ($halamanKini - 1) * $perHalaman + 1 : 0;
    $barisAkhir = $perHalaman > 0 ? min($total, $halamanKini * $perHalaman) : $total;

    // Tampilkan jendela sempit di sekitar halaman aktif supaya tidak memanjang
    // saat datanya sudah ratusan.
    $mulai = max(1, $halamanKini - 2);
    $selesai = min($halamanAkhir, $halamanKini + 2);
@endphp

{{-- Keterangan jumlah tampil walaupun halamannya cuma satu: yang ditanya
     admin lebih sering "ada berapa semuanya", bukan "ini halaman berapa". --}}
@if ($total > 0)
    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 mt-3">
        <span class="orcha-halaman-info">
            @if ($total > 0 && $perHalaman > 0)
                Menampilkan <strong>{{ $baris1 }}–{{ $barisAkhir }}</strong> dari
                <strong>{{ $total }}</strong> data
            @else
                <strong>{{ $total }}</strong> data
            @endif
        </span>

        @if ($halamanAkhir > 1)
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
        @endif
    </div>
@endif
