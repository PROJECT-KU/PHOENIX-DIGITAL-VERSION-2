{{--
    Penomoran halaman untuk data yang datang dari API Orcha.

    Bukan paginator Laravel — angkanya dari meta balasan API, dan perpindahannya
    lewat metode keHalaman() di komponen.

    Variabel: $meta, $alamatHalaman (dari trait MemanggilOrcha)
--}}
@php
    $halamanKini = (int) ($meta['halaman'] ?? 1);

    /*
     | Href DAN wire:click, dan keduanya memang diperlukan.
     |
     | Href sendirian pernah dipakai, dan rusaknya diam serta bersyarat: ia
     | dirakit dari request()->fullUrlWithQuery(), yang benar pada pemuatan
     | pertama dan hanya itu. Begitu admin mengetik di kotak cari, halaman
     | digambar ulang DI DALAM permintaan Livewire — dan request() di sana
     | adalah POST ke /livewire/update. Seluruh tombol nomor lalu menunjuk
     | "/livewire/update?halaman=2". Muat halaman, semuanya benar; ketik satu
     | huruf, semuanya rusak. Itu sebabnya ia bertahan lama.
     |
     | wire:click sendirian juga tidak cukup, dan alasannya sudah dijaga uji
     | sejak lama: berpindah halaman lalu menuntut JavaScript hidup, dan admin
     | yang skripnya gagal dimuat tidak punya cara lain sama sekali untuk
     | melihat halaman kedua.
     |
     | Jadi keduanya. Alamatnya dari $alamatHalaman — ditangkap komponen saat
     | mount, satu-satunya saat permintaannya benar-benar permintaan halaman —
     | dan wire:click.prevent mengambil alih selama JavaScript hidup supaya
     | perpindahannya tidak memuat ulang seluruh halaman.
     */
    $tautanHalaman = function (int $nomor) use ($alamatHalaman) {
        $bagian = parse_url($alamatHalaman ?: url()->current());
        parse_str($bagian['query'] ?? '', $kueri);

        $kueri['halaman'] = $nomor > 1 ? $nomor : null;
        $kueri = array_filter($kueri, fn ($nilai) => $nilai !== null && $nilai !== '');

        $dasar = ($bagian['scheme'] ?? 'http').'://'.($bagian['host'] ?? '')
            .(isset($bagian['port']) ? ':'.$bagian['port'] : '').($bagian['path'] ?? '/');

        return $kueri === [] ? $dasar : $dasar.'?'.http_build_query($kueri);
    };
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
                    <a class="page-link" href="{{ $tautanHalaman($halamanKini - 1) }}" wire:click.prevent="keHalaman($halamanKini - 1)">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                @if ($mulai > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $tautanHalaman(1) }}" wire:click.prevent="keHalaman(1)">1</a>
                    </li>
                    @if ($mulai > 2)
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    @endif
                @endif

                @for ($nomor = $mulai; $nomor <= $selesai; $nomor++)
                    <li class="page-item {{ $nomor === $halamanKini ? 'active' : '' }}">
                        <a class="page-link" href="{{ $tautanHalaman($nomor) }}" wire:click.prevent="keHalaman($nomor)">{{ $nomor }}</a>
                    </li>
                @endfor

                @if ($selesai < $halamanAkhir)
                    @if ($selesai < $halamanAkhir - 1)
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $tautanHalaman($halamanAkhir) }}" wire:click.prevent="keHalaman($halamanAkhir)">{{ $halamanAkhir }}</a>
                    </li>
                @endif

                <li class="page-item {{ $halamanKini >= $halamanAkhir ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $tautanHalaman($halamanKini + 1) }}" wire:click.prevent="keHalaman($halamanKini + 1)">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        @endif
    </div>
@endif
