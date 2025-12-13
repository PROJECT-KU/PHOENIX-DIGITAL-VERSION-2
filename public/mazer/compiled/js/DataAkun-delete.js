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

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-Loan-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const loanId = button.getAttribute('data-id');

            Swal.fire({
                title: 'Yakin hapus Loan?',
                text: "Data loan yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    const livewireComponentId = button.closest('[wire\\:id]').getAttribute('wire:id');
                    Livewire.find(livewireComponentId).call('delete', loanId);
                }
            });
        });
    });

    // Event sukses dari Livewire
    window.addEventListener('loan-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data loan berhasil dihapus.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // Event error dari Livewire
    window.addEventListener('delete-loan-error', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: e.detail.message,
            icon: 'error'
        });
    });

});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.delete-Loan-btn');
    if (!button) return; // bukan tombol hapus

    event.preventDefault();
    const loanId = button.getAttribute('data-id');

    Swal.fire({
        title: 'Yakin hapus Loan?',
        text: "Data loan yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            const livewireComponentId = button.closest('[wire\\:id]').getAttribute('wire:id');
            Livewire.find(livewireComponentId).call('delete', loanId);
        }
    });
});

// Event sukses
window.addEventListener('loan-deleted', () => {
    Swal.fire({
        title: 'Terhapus!',
        text: 'Data loan berhasil dihapus.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
});

// Event gagal
window.addEventListener('delete-loan-error', (e) => {
    Swal.fire({
        title: 'Gagal!',
        text: e.detail.message,
        icon: 'error'
    });
});

// pengenbalian
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-pengembalian-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const pengembalianId = button.getAttribute('data-id');

            Swal.fire({
                title: 'Yakin hapus Loan?',
                text: "Data pengembalian yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    const livewireComponentId = button.closest('[wire\\:id]').getAttribute('wire:id');
                    Livewire.find(livewireComponentId).call('delete', pengembalianId);
                }
            });
        });
    });

    // Event sukses dari Livewire
    window.addEventListener('pengembalian-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data pengembalian berhasil dihapus.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // Event error dari Livewire
    window.addEventListener('delete-pengembalian-error', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: e.detail.message,
            icon: 'error'
        });
    });

});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.delete-pengembalian-btn');
    if (!button) return; // bukan tombol hapus

    event.preventDefault();
    const pengembalianId = button.getAttribute('data-id');

    Swal.fire({
        title: 'Yakin hapus Loan?',
        text: "Data pengembalian yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            const livewireComponentId = button.closest('[wire\\:id]').getAttribute('wire:id');
            Livewire.find(livewireComponentId).call('delete', pengembalianId);
        }
    });
});

// Event sukses
window.addEventListener('pengembalian-deleted', () => {
    Swal.fire({
        title: 'Terhapus!',
        text: 'Data pengembalian berhasil dihapus.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
});

// Event gagal
window.addEventListener('delete-pengembalian-error', (e) => {
    Swal.fire({
        title: 'Gagal!',
        text: e.detail.message,
        icon: 'error'
    });
});


