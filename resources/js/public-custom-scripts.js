import Swal from "sweetalert2";

// Toast seragam brand (sama seperti "Masuk keranjang")
function phToast(message, title, icon) {
    Swal.fire({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 2400,
        timerProgressBar: true,
        html:
            '<div class="ph-toast">' +
            '<span class="ph-toast-ic"><i class="bi ' +
            (icon || "bi-check-circle-fill") +
            '"></i></span>' +
            '<div class="ph-toast-txt"><strong>' +
            (title || "Berhasil") +
            "</strong><span>" +
            (message || "") +
            "</span></div>" +
            "</div>",
        customClass: { popup: "ph-toast-popup" },
    });
}

// Konfirmasi seragam brand (tombol oranye, rounded)
function phConfirm(opts) {
    return Swal.fire({
        title: opts.title,
        text: opts.text || "",
        icon: opts.icon || "warning",
        showCancelButton: true,
        confirmButtonText: opts.confirmButtonText || "Ya, hapus",
        cancelButtonText: "Batal",
        confirmButtonColor: "#f26522",
        cancelButtonColor: "#eef0f2",
        reverseButtons: true,
        customClass: { popup: "ph-swal" },
    });
}

document.addEventListener("livewire:init", () => {
    Livewire.on("success-add-to-cart", (data) => {
        phToast(data.message || "Produk ditambahkan ke keranjang", "Masuk keranjang");
    });
    Livewire.on("success-delete-data", (data) => {
        phToast(data.message || "Keranjang dikosongkan", "Berhasil", "bi-trash-fill");
    });
    Livewire.on("success", (data) => {
        const msg = typeof data === "string" ? data : data && data.message;
        phToast(msg || "Berhasil", "Berhasil");
    });

    Livewire.on("confirm-delete-product-cart", (data) => {
        phConfirm({
            title: "Hapus produk ini?",
            text: "Produk akan dikeluarkan dari keranjang Anda.",
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch("delete-product-cart", { cartKey: data });
            }
        });
    });
    Livewire.on("confirm-empty-cart", () => {
        phConfirm({
            title: "Kosongkan keranjang?",
            text: "Semua produk di keranjang akan dihapus.",
            confirmButtonText: "Ya, kosongkan",
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch("empty-cart");
            }
        });
    });
});
