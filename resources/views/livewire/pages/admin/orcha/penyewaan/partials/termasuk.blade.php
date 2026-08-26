{{-- ============ SUDAH TERMASUK APA SAJA ============
     Keterangan ini sudah ada sejak dulu berupa kalimat panjang, tapi harus
     dibaca sampai habis. Yang ditanya penyewa di loket — dan admin yang
     menjawabnya — cuma satu pos: BBM ditanggung siapa.

     Yang ditanggung penyewa ikut disebut, tidak disembunyikan. Menyebut yang
     termasuk saja membuat penyewa mengira sisanya juga ditanggung, dan itu baru
     ketahuan saat menagih. --}}
@if (! empty(data_get($sewa, 'kendaraan.termasuk')))
    <div class="orcha-termasuk mt-1">
        @foreach (data_get($sewa, 'kendaraan.termasuk') as $pos)
            <span class="orcha-cip-termasuk {{ $pos['termasuk'] ? 'ya' : 'tidak' }}">
                <i class="bi {{ $pos['termasuk'] ? 'bi-check-circle-fill' : 'bi-dash-circle' }}"></i>
                {{ $pos['label'] }}
                @if (! empty($pos['catatan']))
                    <span class="catatan">· {{ $pos['catatan'] }}</span>
                @elseif (! $pos['termasuk'])
                    <span class="catatan">· ditanggung penyewa</span>
                @endif
            </span>
        @endforeach
    </div>
@endif
