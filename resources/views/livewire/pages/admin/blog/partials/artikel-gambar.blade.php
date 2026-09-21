{{-- Perbesar gambar sampul.

     Elemennya dirakit lewat DOM (createElement), bukan potongan HTML dalam
     string: teks HTML di dalam <script> memuat ">" yang membuat Livewire
     melewatkan penanda morph di direktif sekitarnya — halamannya tetap muat,
     tapi pembaruan Livewire berikutnya patah. --}}
@once
<script data-navigate-once>
    if (!window.__blGambarBesar) {
        window.__blGambarBesar = true;

        document.addEventListener('click', function (e) {
            const tombol = e.target.closest && e.target.closest('.bl-gambar-besar');
            if (!tombol) return;
            e.preventDefault();

            const alamat = tombol.getAttribute('data-gambar');
            if (!alamat) return;

            // SweetAlert belum termuat — gambarnya tetap bisa dilihat.
            if (typeof Swal === 'undefined') { window.open(alamat, '_blank', 'noopener'); return; }

            const bingkai = document.createElement('div');
            bingkai.style.cssText = 'display:flex;align-items:center;justify-content:center;width:100%';

            const gambar = document.createElement('img');
            gambar.src = alamat;
            gambar.alt = 'Sampul artikel';
            gambar.style.cssText = 'max-width:88vw;max-height:82vh;width:auto;height:auto;object-fit:contain;border-radius:12px';
            bingkai.appendChild(gambar);

            Swal.fire({
                html: bingkai,
                background: 'rgba(255,255,255,.94)',
                backdrop: 'rgba(124, 58, 237, .18)',
                customClass: { popup: 'shadow rounded-4 border-0' },
                showConfirmButton: false,
                showCloseButton: true,
                width: 'auto',
                padding: '1rem',
            });
        });
    }
</script>
@endonce
