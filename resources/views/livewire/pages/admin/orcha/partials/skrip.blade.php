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
</script>
