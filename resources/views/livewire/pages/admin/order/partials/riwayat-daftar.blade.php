{{-- Linimasa riwayat pesanan. $riwayat dari RiwayatPesanan::untuk(). --}}
@php
    $gayaAksi = [
        'dibuat' => ['bi-bag-plus-fill', '#7c3aed'],
        'status' => ['bi-arrow-left-right', '#0284c7'],
        'bukti' => ['bi-receipt', '#0891b2'],
        'dikirim' => ['bi-send-check-fill', '#16a34a'],
        'langganan' => ['bi-arrow-repeat', '#4f46e5'],
        'masa' => ['bi-calendar-event', '#4f46e5'],
        'wa' => ['bi-whatsapp', '#25d366'],
        'catatan' => ['bi-sticky-fill', '#d97706'],
        'jasa' => ['bi-file-earmark-check-fill', '#ea580c'],
        'diubah' => ['bi-pencil-square', '#7c3aed'],
    ];
    $adaSusunan = $riwayat->contains('dari_data', true);
@endphp
@if ($riwayat->isEmpty())
    <div class="dsb-kosong">
        <span class="dsb-kosong-ikon"><i class="bi bi-clock-history"></i></span>
        <p class="dsb-kosong-judul">Belum ada riwayat</p>
    </div>
@else
    <ol class="pt-riwayat">
        @foreach ($riwayat as $r)
            @php [$ikon, $warna] = $gayaAksi[$r['aksi']] ?? ['bi-dot', '#64748b']; @endphp
            <li class="pt-riwayat-baris" style="--c: {{ $warna }}">
                <span class="pt-riwayat-ikon"><i class="bi {{ $ikon }}"></i></span>
                <div class="pt-riwayat-teks">
                    <b>{{ $r['teks'] }}</b>
                    <span>
                        {{ $r['waktu']?->locale('id')->translatedFormat('d M Y, H:i') }}
                        @if ($r['dari_data'])
                            · <i>dari data pesanan</i>
                        @else
                            · {{ $r['oleh'] }}
                        @endif
                    </span>
                </div>
            </li>
        @endforeach
    </ol>
    @if ($adaSusunan)
        <p class="pt-riwayat-catatan"><i class="bi bi-info-circle"></i> Pesanan ini dibuat sebelum riwayat terperinci aktif. Baris "dari data pesanan" disusun dari tanggal yang tersimpan di pesanan.</p>
    @endif
@endif
