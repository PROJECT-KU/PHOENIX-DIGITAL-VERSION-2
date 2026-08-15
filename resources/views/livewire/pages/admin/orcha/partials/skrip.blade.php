{{--
    Pemberitahuan untuk halaman Orcha.

    Ditulis inline karena aset Vite tidak ikut ter-deploy. Bentuknya mengikuti
    SweetAlert glossy yang sudah dipakai halaman lemon lain supaya admin tidak
    melihat dua gaya pemberitahuan yang berbeda.
--}}
@if (session('orcha_sukses'))
    {{-- Pesan dari halaman sebelumnya (mis. setelah menyimpan lalu berpindah
         ke daftar). Ditampilkan sekali, lalu hilang bersama sesinya. --}}
    <script>
        // livewire:navigated menyala baik saat halaman dimuat biasa maupun saat
        // berpindah lewat wire:navigate — DOMContentLoaded tidak menyala pada
        // yang kedua, dan itulah cara formulir ini berpindah.
        document.addEventListener('livewire:navigated', () => {
            const tampilkan = () => Swal.fire({
                title: 'Berhasil',
                text: @js(session('orcha_sukses')),
                icon: 'success',
                timer: 2600,
                showConfirmButton: false,
                background: 'rgba(255,255,255,.96)',
                backdrop: 'rgba(15,45,74,.25)',
                customClass: { popup: 'shadow rounded-4' },
            });

            typeof Swal === 'undefined' ? setTimeout(tampilkan, 300) : tampilkan();
        }, { once: true });
    </script>
@endif

<script>
    document.addEventListener('livewire:initialized', () => {
        if (window.__orchaNotifBound) return;
        window.__orchaNotifBound = true;

        const glossy = {
            background: 'rgba(255,255,255,.96)',
            backdrop: 'rgba(15,45,74,.25)',
            customClass: { popup: 'shadow rounded-4' },
        };

        const ambilPesan = (e, bawaan) =>
            (e && (e.message ?? (Array.isArray(e) ? e[0]?.message : null))) || bawaan;

        Livewire.on('order-updated', (e) => {
            Swal.fire({
                title: 'Berhasil',
                text: ambilPesan(e, 'Berhasil diperbarui.'),
                icon: 'success',
                timer: 2200,
                showConfirmButton: false,
                ...glossy,
            });
        });

        Livewire.on('toast-error', (e) => {
            Swal.fire({
                title: 'Gagal',
                text: ambilPesan(e, 'Perubahan tidak tersimpan.'),
                icon: 'error',
                confirmButtonText: 'Mengerti',
                ...glossy,
            });
        });
    });

    /* Isian uang bertitik SAMBIL diketik.
       Server tetap yang memegang angkanya (wire:model.blur memformat ulang
       dengan aturan yang sama), ini hanya supaya admin tidak menunggu pindah
       kolom dulu untuk melihat "1.430.000". Ditulis inline karena berkas Vite
       tidak ikut ter-deploy. */
    if (!window.__orchaUangBound) {
        window.__orchaUangBound = true;

        const bertitik = (angka) => angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        document.addEventListener('input', (e) => {
            const el = e.target;
            if (!el.classList || !el.classList.contains('orcha-uang')) return;

            const angka = el.value.replace(/\D/g, '');
            const baru = angka === '' ? '' : bertitik(angka);
            if (baru === el.value) return;

            // Jaga posisi kursor: hitung berapa digit sebelum kursor, lalu
            // taruh kursor setelah digit ke-sekian pada teks yang baru.
            const digitSebelumKursor = el.value.slice(0, el.selectionStart).replace(/\D/g, '').length;
            el.value = baru;

            let posisi = 0, terhitung = 0;
            while (posisi < baru.length && terhitung < digitSebelumKursor) {
                if (/\d/.test(baru[posisi])) terhitung++;
                posisi++;
            }
            el.setSelectionRange(posisi, posisi);
        });
    }

    /* Konfirmasi seragam untuk tombol .pcek-konfirmasi — pola yang sama dengan
       halaman lemon lain. Penanda global dipakai bersama supaya tidak terpasang
       dua kali kalau halaman lain sudah memasangnya. */
    if (!window.__pcekKonfirmasiBound) {
        window.__pcekKonfirmasiBound = true;

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.pcek-konfirmasi');
            if (!btn) return;
            e.preventDefault();

            const method = btn.dataset.action;
            const arg = btn.dataset.arg || null;
            const component = btn.closest('[wire\\:id]');
            if (!method || !component) return;

            const jalankan = () => {
                const lw = Livewire.find(component.getAttribute('wire:id'));
                if (lw) arg ? lw.call(method, arg) : lw.call(method);
            };

            if (typeof Swal === 'undefined') { jalankan(); return; }

            Swal.fire({
                title: btn.dataset.title || 'Anda yakin?',
                text: btn.dataset.text || '',
                icon: btn.dataset.icon || 'question',
                showCancelButton: true,
                confirmButtonText: btn.dataset.confirm || 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                background: 'rgba(255,255,255,.96)',
                backdrop: 'rgba(15,45,74,.25)',
                // Tombolnya digaya kelas .btn-glossy-* milik lemon, jadi gaya
                // bawaan SweetAlert dimatikan supaya tidak bertumpuk.
                buttonsStyling: false,
                customClass: {
                    popup: 'shadow rounded-4',
                    confirmButton: 'btn-glossy-confirm',
                    cancelButton: 'btn-glossy-cancel',
                },
            }).then((r) => { if (r.isConfirmed) jalankan(); });
        });
    }
</script>
