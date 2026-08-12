{{-- Meta Pixel selalu mengirim document.location apa adanya dan tidak
     menyediakan cara menimpanya seperti page_location di GA4. Karena itu, di
     halaman bertoken pixel-nya tidak dimuat sama sekali — satu-satunya cara
     mencegah token pelanggan sampai ke Facebook. --}}
@unless (\App\Support\JalurAnalitik::peka())
@php($pixelId = config('services.meta.pixel_id'))
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', @json($pixelId));
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ urlencode($pixelId) }}&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->

{{-- Jembatan event Livewire -> Meta Pixel.
     Menambah ke keranjang tidak memuat ulang halaman, jadi event-nya dikirim
     dari server lewat dispatch('pixel-event') lalu diteruskan di sini.

     Dipasang ke `document` (bukan document.body) dengan penjaga sekali-pasang:
     wire:navigate mengganti seluruh <body> saat pindah halaman, sehingga
     pendengar yang menempel di body akan hilang diam-diam. --}}
<script>
    if (!window.__pixelJembatanTerpasang) {
        window.__pixelJembatanTerpasang = true;

        document.addEventListener('livewire:init', function () {
            Livewire.on('pixel-event', function (payload) {
                // Livewire 3 membungkus argumen bernama dalam sebuah array.
                var isi = Array.isArray(payload) ? payload[0] : payload;
                if (!isi || !isi.nama) return;

                // fbq tidak ada di halaman bertoken (pixel sengaja tidak dimuat).
                // Dibiarkan diam agar tidak melempar error di konsol pengunjung.
                if (typeof fbq !== 'function') return;

                fbq('track', isi.nama, isi.data || {});
            });
        });
    }
</script>
@endunless
