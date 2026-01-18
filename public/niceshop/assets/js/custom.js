function formatRupiah(number) {
    if (!number || isNaN(number)) return "Rp 0";
    return "Rp " + new Intl.NumberFormat("id-ID").format(number);
}

document.addEventListener("DOMContentLoaded", () => {
    const salePrice = document.getElementById("salePrice");
    const regularPrice = document.getElementById("regularPrice");

    if (!salePrice || !regularPrice) return;

    regularPrice.style.display = "none";

    document.querySelectorAll('input[name="price_option"]').forEach((radio) => {
        radio.addEventListener("change", function () {
            const harga = parseInt(this.dataset.value) || 0;
            const multiplier = parseInt(this.dataset.multiplier) || 1;
            const regular = parseInt(this.dataset.regular) || 0;

            salePrice.textContent = formatRupiah(harga);

            let hargaCoret = harga * multiplier;

            if (this.value === "perbulan" && regular > 0) {
                hargaCoret = regular;
            }

            regularPrice.textContent = formatRupiah(hargaCoret);
            regularPrice.style.display = "inline-block";

            document.querySelectorAll(".price-option").forEach((el) => {
                el.classList.remove("active-option");
            });
            this.closest(".price-option").classList.add("active-option");
        });
    });
});

window.addEventListener("cart-success", (event) => {
    Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: event.detail.message,
        timer: 1500,
        showConfirmButton: false,
    });
});

window.addEventListener("cart-error", (event) => {
    Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: event.detail.message,
        showConfirmButton: true,
    });
});

window.addEventListener("redirect-home", () => {
    setTimeout(() => {
        window.location.href = "/cart";
    }, 1600);
});
