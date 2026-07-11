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

            // highlight terpilih
            document.querySelectorAll(".price-option").forEach((el) => {
                el.classList.remove("active-option");
            });
            this.closest(".price-option").classList.add("active-option");
        });
    });
});

window.addEventListener("cart-success", (event) => {
    Swal.fire({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 2400,
        timerProgressBar: true,
        html:
            '<div class="ph-toast">' +
              '<span class="ph-toast-ic"><i class="bi bi-cart-check-fill"></i></span>' +
              '<div class="ph-toast-txt">' +
                '<strong>Masuk keranjang</strong>' +
                '<span>' + (event.detail.message || 'Produk ditambahkan.') + '</span>' +
              '</div>' +
            '</div>',
        customClass: { popup: 'ph-toast-popup' },
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
window.addEventListener("success", (event) => {
    Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: event.detail.message,
        timer: 1500,
        showConfirmButton: false,
    });
});

window.addEventListener("error", (event) => {
    Swal.fire({
        icon: "error",
        title: "Gagal!",
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

document.addEventListener('DOMContentLoaded', function () {

    const countdownElement = document.getElementById('countdown');

    if (!countdownElement) {
        return;
    }

    const expiredAt = countdownElement.dataset.expired;

    const expiredTime = new Date(expiredAt).getTime();

    const timer = setInterval(() => {

        const now = new Date().getTime();

        const distance = expiredTime - now;

        if (distance <= 0) {

            clearInterval(timer);

            countdownElement.innerHTML = 'QRIS Kadaluarsa';

            setTimeout(() => {
                location.reload();
            }, 1000);

            return;
        }

        const minutes = Math.floor(
    distance / (1000 * 60)
);

const seconds = Math.floor(
    (distance % (1000 * 60)) / 1000
);

countdownElement.innerHTML =
    `${minutes}:${String(seconds).padStart(2, '0')}`;

        countdownElement.innerHTML =
            `${hours}j ${minutes}m ${seconds}d`;

    }, 1000);

});

document.addEventListener('livewire:init', () => {

    Livewire.on('payment-success', (event) => {

        Swal.fire({
            icon: 'success',
            title: 'Pembayaran Berhasil 🎉',
            html: `
                <p>Terima kasih atas pembayaran Anda.</p>
                <small>Mengarahkan ke halaman sukses...</small>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
            timer: 2500,
            timerProgressBar: true
        });

        setTimeout(() => {
            window.location.href = event.url;
        }, 2500);

    });

});

// scrol pagination
window.addEventListener('scroll', () => {
    if (
        window.innerHeight + window.scrollY >=
        document.body.offsetHeight - 500
    ) {
        Livewire.dispatch('loadMore');
    }
});