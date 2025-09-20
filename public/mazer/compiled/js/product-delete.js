document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-product-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const productId = button.getAttribute('data-id');

            Swal.fire({
                title: 'Yakin hapus produk?',
                text: "Data tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    const livewireComponentId = button.closest('[wire\\:id]').getAttribute('wire:id');
                    Livewire.find(livewireComponentId).call('deleteProduct', productId);
                }
            });
        });
    });

    window.addEventListener('product-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Produk berhasil dihapus.',
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
