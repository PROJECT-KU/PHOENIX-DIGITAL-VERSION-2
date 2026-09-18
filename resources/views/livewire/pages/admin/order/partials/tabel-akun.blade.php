{{-- Tabel tab berbasis akun: Segera Habis & Akun Habis (satu baris = satu akun).

     Variabel: $items (paginator), $jenisTab ('segera' | 'habis'), $adaSaringan,
     $bolehUbahPesanan, $bolehBuatPesanan, $lencanaLangganan. --}}
@php
    $segera = $jenisTab === 'segera';
    $kolomDihubungi = $segera ? 'ingat_perpanjang_at' : 'habis_notified_at';
    $idHalaman = $items->pluck('id')->map(fn ($id) => (string) $id)->all();
    $jumlahTerpilih = count($terpilih);
@endphp

@if ($items->isEmpty())
    <div class="dsb-kosong">
        <span class="dsb-kosong-ikon"><i class="bi {{ $segera ? 'bi-alarm' : 'bi-hourglass-bottom' }}"></i></span>
        <p class="dsb-kosong-judul">{{ $segera ? 'Tidak ada akun yang segera habis' : 'Belum ada akun habis' }}</p>
        <p class="dsb-kosong-ket">
            {{ $segera ? 'Tidak ada akun yang masa aktifnya berakhir dalam '.\App\Support\PengingatPerpanjangan::HARI.' hari ke depan' : 'Tidak ada item pesanan yang masa aktifnya sudah habis' }}{{ $adaSaringan ? ' pada saringan ini' : '' }}.
        </p>
    </div>
@else
    @if ($bolehUbahPesanan)
        {{-- Aksi massal: muncul hanya bila ada yang dicentang. --}}
        <div class="pt-massal {{ $jumlahTerpilih ? 'is-aktif' : '' }}">
            <label class="pt-centang">
                <input type="checkbox" x-data
                    :checked="{{ json_encode($idHalaman) }}.every(id => $wire.terpilih.includes(id))"
                    x-on:change="$wire.set('terpilih', $event.target.checked ? {{ json_encode($idHalaman) }} : [])">
                <span>Pilih semua di halaman ini</span>
            </label>
            @if ($jumlahTerpilih)
                <span class="pt-massal-aksi">
                    <b>{{ $jumlahTerpilih }} dipilih</b>
                    <button type="button" class="dsb-tombol is-utama is-mungil" wire:click="tandaiDihubungi" wire:loading.attr="disabled" wire:target="tandaiDihubungi">
                        <i class="bi bi-check2-all"></i><span>Tandai sudah dihubungi</span>
                    </button>
                    <button type="button" class="dsb-tombol is-lembut is-mungil" wire:click="$set('terpilih', [])">
                        <span>Batal pilih</span>
                    </button>
                </span>
            @endif
        </div>
    @endif

    <div class="dsb-tabel-bungkus">
        <table class="dsb-tabel">
            <thead>
                <tr>
                    @if ($bolehUbahPesanan)
                        <th class="pt-kol-centang"><span class="visually-hidden">Pilih</span></th>
                    @endif
                    <th>Akun</th>
                    <th class="k-sedang">Pelanggan</th>
                    <th>Masa Aktif</th>
                    <th class="k-lebar">Langganan</th>
                    <th>{{ $segera ? 'Diingatkan' : 'Diberi tahu' }}</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    @php
                        $sisa = \App\Support\PengingatPerpanjangan::sisaHari($item);
                        $tautanWa = \App\Support\PengingatPerpanjangan::tautanWa($item, $jenisTab);
                        $dihubungi = $item->{$kolomDihubungi};
                        $warnaSisa = $segera ? (2 < $sisa ? 'is-kuning' : 'is-merah') : 'is-merah';
                        $labelSisa = $segera ? ($sisa === 0 ? 'Berakhir hari ini' : ($sisa === 1 ? 'Besok' : $sisa.' hari lagi')) : 'Habis';
                    @endphp
                    <tr wire:key="akun-{{ $jenisTab }}-{{ $item->id }}">
                        @if ($bolehUbahPesanan)
                            <td class="pt-kol-centang" data-judul="Pilih">
                                <input type="checkbox" class="pt-centang-kotak" value="{{ $item->id }}" wire:model.live="terpilih" aria-label="Pilih {{ $item->product_name }}">
                            </td>
                        @endif
                        <td>
                            <div class="dsb-tabel-utama">
                                <span class="dsb-ikon is-kecil" style="--c: {{ $segera ? '#ea580c' : '#dc2626' }}"><i class="bi {{ $segera ? 'bi-alarm-fill' : 'bi-hourglass-bottom' }}"></i></span>
                                <span class="dsb-tabel-teks">
                                    <span class="dsb-tabel-judul">{{ $item->product_name }}</span>
                                    <span class="dsb-tabel-meta">
                                        <span class="dsb-lencana is-abu">{{ $item->order->order_number ?? '—' }}</span>
                                        <span class="dsb-tabel-samar"><i class="bi bi-person"></i>{{ $item->order->customer->nama ?? '—' }}</span>
                                    </span>
                                </span>
                            </div>
                        </td>
                        <td class="k-sedang" data-judul="Pelanggan">
                            <span class="dsb-tabel-teks">
                                <span class="dsb-tabel-angka">{{ $item->order->customer->nama ?? '—' }}</span>
                                <span class="dsb-tabel-meta">{{ $item->order->customer->no_hp ?? '' }}</span>
                            </span>
                        </td>
                        <td data-judul="Masa Aktif">
                            @if ($item->end_date)
                                <span class="dsb-tabel-teks">
                                    <span class="dsb-tabel-angka">s/d {{ $item->end_date->locale('id')->translatedFormat('d M Y') }}</span>
                                    <span class="dsb-tabel-meta"><span class="dsb-lencana {{ $warnaSisa }}">{{ $labelSisa }}</span></span>
                                </span>
                            @else
                                <span class="dsb-tabel-samar">—</span>
                            @endif
                        </td>
                        <td class="k-lebar" data-judul="Langganan">
                            <span class="dsb-lencana {{ $lencanaLangganan[$item->subscription_status] ?? 'is-abu' }}">{{ ucfirst($item->subscription_status ?: 'Tidak diketahui') }}</span>
                        </td>
                        <td data-judul="{{ $segera ? 'Diingatkan' : 'Diberi tahu' }}">
                            @if ($dihubungi)
                                <span class="dsb-lencana is-hijau" title="{{ $dihubungi->locale('id')->translatedFormat('d M Y H:i') }}">
                                    <i class="bi bi-check2-circle"></i>Sudah · {{ $dihubungi->locale('id')->translatedFormat('d M') }}
                                </span>
                            @else
                                <span class="dsb-lencana is-kuning"><i class="bi bi-exclamation-circle"></i>Belum</span>
                            @endif
                        </td>
                        <td data-judul="Aksi" style="text-align: right;">
                            <span class="dsb-tabel-aksi">
                                @if ($tautanWa)
                                    {{-- Buka WA dengan pesan siap kirim, sekaligus catat sudah dihubungi. --}}
                                    <a href="{{ $tautanWa }}" target="_blank" rel="noopener" class="pt-lanjut is-wa"
                                        @if ($bolehUbahPesanan) wire:click="tandaiDihubungi('{{ $item->id }}')" @endif
                                        title="{{ $segera ? 'Ingatkan lewat WhatsApp' : 'Beri tahu lewat WhatsApp' }}">
                                        <i class="bi bi-whatsapp"></i><span>{{ $segera ? 'Ingatkan' : 'Beri tahu' }}</span>
                                    </a>
                                @endif
                                @if ($bolehBuatPesanan && $item->product_id)
                                    <a wire:navigate href="{{ route('admin.pesanantoko.create', ['perpanjang' => $item->id]) }}" class="pt-lanjut is-ungu" title="Buat pesanan perpanjangan — pelanggan, produk & durasi terisi otomatis">
                                        <i class="bi bi-arrow-repeat"></i><span>Perpanjang</span>
                                    </a>
                                @endif
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
    @if ($items->hasPages())
        <div class="pt-halaman">{{ $items->links('vendor.pagination') }}</div>
    @endif
@endif
