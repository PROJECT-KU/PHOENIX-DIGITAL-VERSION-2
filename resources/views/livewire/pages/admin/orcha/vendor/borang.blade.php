@section('title')
{{ $ubah ? 'Ubah Vendor' : 'Vendor Baru' }} || lemon
@stop

@php
    /*
     | Bahan yang dibaca partial medan — sama persis dengan yang dirakit
     | daftar dan rincian vendor. Dirakit di sini, bukan dipindah ke
     | komponen, supaya ketiganya tetap satu partial: warna jenis adalah
     | urusan tampilan, dan menaruhnya di PHP akan membuat layar berikutnya
     | merakitnya sendiri lalu berselisih warna.
     */
    $jenisKini = trim((string) ($isian['jenis'] ?? ''));
    $jenisAsing = $jenisKini !== '' && ! in_array($jenisKini, $pilihanJenis, true);

    $rupaJenis = [
        'Armada' => ['#0369a1', 'bi-bus-front'],
        'Akomodasi' => ['#6d28d9', 'bi-building'],
        'Konsumsi' => ['#e0912f', 'bi-cup-hot'],
        'Tiket' => ['#1f7a44', 'bi-ticket-perforated'],
        'Pemandu' => ['#0e7490', 'bi-person-badge'],
    ];
    $rupaDari = fn ($j) => $rupaJenis[$j] ?? ['#64748b', 'bi-shop'];
@endphp

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan
         daftar vendor dan halaman rinciannya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    <style>
        /* Ditulis telanjang di badan komponen, bukan dititipkan ke tumpukan:
           templateindex tidak menyediakan satu pun tempat tumpuk, jadi yang
           dititipkan ke sana hilang tanpa galat dan halamannya tetap 200. */
        .vb-kaki {
            position: sticky; bottom: 0; z-index: 3;
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
            margin-top: 16px; padding: 12px 16px; border-radius: 15px;
            background: rgba(255, 255, 255, .92); backdrop-filter: blur(6px);
            border: 1px solid var(--dsb-tepi, #e9edf3);
            box-shadow: 0 6px 22px rgba(15, 23, 42, .07);
        }
        .vb-kaki-ket { font-size: .8rem; color: var(--dsb-redup, #6b7280); margin-right: auto; }

        @media (max-width: 575.98px) {
            .vb-kaki { flex-direction: column; align-items: stretch; }
            .vb-kaki-ket { margin-right: 0; }
            .vb-kaki .dsb-tombol { justify-content: center; }
        }
    </style>

    <div class="dsb">
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-shop"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">
                                    {{ $ubah ? ($namaTersimpan !== '' ? 'Ubah '.$namaTersimpan : 'Ubah Vendor') : 'Vendor Baru' }}
                                </h3>
                                <span class="dsb-kartu-sub">
                                    Nomornya dibaca <strong>hidup</strong> — diubah di sini, RAB lama pun ikut menunjuk nomor yang baru.
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-kartu-isi">
                        @include('livewire.pages.admin.orcha.vendor.partials.medan')
                    </div>
                </div>
            </div>
        </section>

        <div class="vb-kaki">
            <span class="vb-kaki-ket">
                @if ($ubah)
                    Harga yang sudah memakai vendor ini ikut menunjuk nomor yang baru.
                @else
                    Setelah disimpan, vendornya langsung bisa dipilih di Master Harga.
                @endif
            </span>
            <a href="{{ $this->tujuanKembali() }}" class="dsb-tombol is-lembut" style="--ikon: #64748b" wire:navigate>
                <i class="bi bi-x-lg"></i><span>Batal</span>
            </a>
            <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                class="dsb-tombol is-utama">
                <i class="bi bi-check2-circle" wire:loading.remove wire:target="simpan"></i>
                <span class="dsb-putar" wire:loading wire:target="simpan"></span>
                <span>{{ $ubah ? 'Simpan perubahan' : 'Simpan' }}</span>
            </button>
        </div>
    </div>

    {{-- Tanpa ini, Simpan terlihat seperti tidak ditekan: pesan dan
         perpindahannya dikirim lewat event, dan yang mendengarkannya hidup di
         partial ini. Lihat PenangganEventOrchaTest. --}}
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
