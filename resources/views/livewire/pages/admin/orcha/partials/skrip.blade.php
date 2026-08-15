{{--
    Pemberitahuan untuk halaman Orcha.

    Ditulis inline karena aset Vite tidak ikut ter-deploy. Bentuknya mengikuti
    SweetAlert glossy yang sudah dipakai halaman lemon lain supaya admin tidak
    melihat dua gaya pemberitahuan yang berbeda.
--}}
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
