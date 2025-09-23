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
