{{-- Cermin angka rupiah yang diketik.

     Tinggal di partial sendiri sejak isiannya pindah ke halaman tersendiri:
     elemen bertanda data-cermin hidup di borang, sedangkan skripnya dulu
     tertinggal di daftar — dan cermin tanpa skrip tidak pernah berubah,
     tanpa satu pun galat. --}}
@once
    <script>
        document.addEventListener('input', (e) => {
            const sumber = e.target;
            if (!sumber.id) return;

            const cermin = document.querySelectorAll('[data-cermin="' + sumber.id + '"]');
            if (!cermin.length) return;

            const angka = (sumber.value || '').replace(/\D/g, '');
            /* Pemformat yang sama dengan isian uangnya sendiri — lihat
               orcha/partials/skrip. Cadangan apa adanya kalau skripnya
               belum termuat: lebih baik angka tanpa titik daripada
               kotak yang diam. */
            const rapi = window.orchaBertitik ? window.orchaBertitik(angka) : angka;

            cermin.forEach((el) => { el.textContent = angka === '' ? '—' : 'Rp ' + rapi; });
        });
    </script>
@endonce
