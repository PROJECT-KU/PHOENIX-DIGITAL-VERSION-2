import Swal from "sweetalert2";

const Toast = Swal.mixin({
    toast: true,
    position: "top-start",
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
});

const Swal2 = Swal.mixin({});

document.addEventListener("livewire:init", () => {
    Livewire.on("success-add-to-cart", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil menambahkan produk ke keranjang",
        });
    });
    Livewire.on("success-delete-data", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil menghapus produk di keranjang",
        });
    });

    Livewire.on("confirm-delete-product-cart", (data) => {
        Swal2.fire({
            icon: "question",
            title: "hapus produk ini dari keranjang?",
            showCancelButton: true,
            cancelButtonText: "Batal",
            confirmButtonText: "Ya, hapus",
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch("delete-product-cart", { cartKey: data });
            }
        });
    });
    Livewire.on("confirm-empty-cart", () => {
        Swal2.fire({
            icon: "question",
            title: "Yakin ingin mengkosongkan keranjang?",
            showCancelButton: true,
            cancelButtonText: "Batal",
            confirmButtonText: "Ya, hapus",
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch("empty-cart");
            }
        });
    });
});
