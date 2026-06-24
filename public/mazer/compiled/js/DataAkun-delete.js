document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-DataAkun-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const DataAkunId = button.getAttribute('data-id');

            Swal.fire({
                title: 'Yakin hapus Data Akun?',
                text: "Data tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    const livewireComponentId = button.closest('[wire\\:id]').getAttribute('wire:id');
                    Livewire.find(livewireComponentId).call('deleteDataAkun', DataAkunId);
                }
            });
        });
    });

    window.addEventListener('DataAkun-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data Akun berhasil dihapus.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    });

    window.addEventListener('delete-error', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: e.detail.message,
            icon: 'error'
        });
    });

});

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-DataProduct-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const productId = button.getAttribute('data-id');

            Swal.fire({
                title: 'Yakin hapus Produk?',
                text: "Data produk yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    const livewireComponentId = button.closest('[wire\\:id]').getAttribute('wire:id');
                    Livewire.find(livewireComponentId).call('deleteDataProduct', productId);
                }
            });
        });
    });

    // Event sukses dari Livewire
    window.addEventListener('DataProduct-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data produk berhasil dihapus.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // Event error dari Livewire
    window.addEventListener('delete-product-error', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: e.detail.message,
            icon: 'error'
        });
    });

});

// ===== Glossy delete: Peminjaman & Pengembalian (gaya seperti fitur Banners) =====
(function () {
    const glossyConfig = {
        background: 'rgba(255, 255, 255, 0.8)',
        backdrop: 'rgba(139, 92, 246, 0.15)',
        customClass: {
            popup: 'swal-glossy-popup',
            confirmButton: 'btn-glossy-confirm',
            cancelButton: 'btn-glossy-cancel',
            title: 'swal-glossy-title'
        },
        buttonsStyling: false
    };

    function konfirmasiHapus(button, teks, metode = 'delete') {
        const id = button.getAttribute('data-id');
        const component = button.closest('[wire\\:id]');
        if (!component) return;

        Swal.fire({
            title: 'Yakin hapus data?',
            text: teks,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            ...glossyConfig
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.find(component.getAttribute('wire:id')).call(metode, id);
            }
        });
    }

    // Delegasi klik (tetap berfungsi setelah Livewire re-render / navigasi)
    document.addEventListener('click', function (event) {
        const loanBtn = event.target.closest('.delete-Loan-btn');
        if (loanBtn) {
            event.preventDefault();
            konfirmasiHapus(loanBtn, 'Data peminjaman ini tidak bisa dikembalikan!');
            return;
        }

        const pengembalianBtn = event.target.closest('.delete-pengembalian-btn');
        if (pengembalianBtn) {
            event.preventDefault();
            konfirmasiHapus(pengembalianBtn, 'Data pengembalian ini tidak bisa dikembalikan!');
            return;
        }

        const spendingBtn = event.target.closest('.delete-spending-btn');
        if (spendingBtn) {
            event.preventDefault();
            konfirmasiHapus(spendingBtn, 'Data pengeluaran ini tidak bisa dikembalikan!');
            return;
        }

        const gajiBtn = event.target.closest('.delete-gajikaryawan-btn');
        if (gajiBtn) {
            event.preventDefault();
            konfirmasiHapus(gajiBtn, 'Data gaji karyawan ini tidak bisa dikembalikan!', 'deletegajikaryawan');
            return;
        }

        // Generate gaji massal (konfirmasi glossy)
        const generateBtn = event.target.closest('.generate-gaji-btn');
        if (generateBtn) {
            event.preventDefault();
            const component = generateBtn.closest('[wire\\:id]');
            if (!component) return;

            Swal.fire({
                title: 'Generate gaji untuk semua karyawan?',
                text: 'Draft gaji (status pending) dibuat dari gaji periode BULAN SEBELUMNYA untuk periode terpilih. Yang sudah ada akan dilewati.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, generate',
                cancelButtonText: 'Batal',
                ...glossyConfig
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.find(component.getAttribute('wire:id')).call('generateGaji');
                }
            });
        }
    });

    // Notifikasi sukses
    window.addEventListener('loan-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data peminjaman berhasil dihapus.',
            icon: 'success',
            timer: 2500,
            showConfirmButton: false,
            ...glossyConfig
        });
    });

    window.addEventListener('pengembalian-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data pengembalian berhasil dihapus.',
            icon: 'success',
            timer: 2500,
            showConfirmButton: false,
            ...glossyConfig
        });
    });

    // Notifikasi gagal
    window.addEventListener('delete-loan-error', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: (e.detail && e.detail.message) ? e.detail.message : 'Terjadi kesalahan saat menghapus data.',
            icon: 'error',
            timer: 2500,
            showConfirmButton: false,
            ...glossyConfig
        });
    });

    window.addEventListener('delete-pengembalian-error', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: (e.detail && e.detail.message) ? e.detail.message : 'Terjadi kesalahan saat menghapus data.',
            icon: 'error',
            timer: 2500,
            showConfirmButton: false,
            ...glossyConfig
        });
    });

    window.addEventListener('spending-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data pengeluaran berhasil dihapus.',
            icon: 'success',
            timer: 2500,
            showConfirmButton: false,
            ...glossyConfig
        });
    });

    window.addEventListener('spending-delete-error', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: (e.detail && e.detail.message) ? e.detail.message : 'Terjadi kesalahan saat menghapus data.',
            icon: 'error',
            timer: 2500,
            showConfirmButton: false,
            ...glossyConfig
        });
    });

    window.addEventListener('gajikaryawan-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data gaji karyawan berhasil dihapus.',
            icon: 'success',
            timer: 2500,
            showConfirmButton: false,
            ...glossyConfig
        });
    });

    window.addEventListener('gajikaryawan-delete-error', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: (e.detail && e.detail.message) ? e.detail.message : 'Terjadi kesalahan saat menghapus data.',
            icon: 'error',
            timer: 2500,
            showConfirmButton: false,
            ...glossyConfig
        });
    });

    window.addEventListener('gaji-generated', (e) => {
        Swal.fire({
            title: 'Berhasil!',
            text: (e.detail && e.detail.message) ? e.detail.message : 'Draft gaji berhasil dibuat.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false,
            ...glossyConfig
        });
    });

    window.addEventListener('gaji-generate-info', (e) => {
        Swal.fire({
            title: 'Info',
            text: (e.detail && e.detail.message) ? e.detail.message : '',
            icon: 'info',
            ...glossyConfig
        });
    });
})();
