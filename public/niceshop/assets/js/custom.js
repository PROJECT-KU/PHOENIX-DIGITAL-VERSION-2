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

(function () {
    // Elemen #countdown kini dirender SETELAH QRIS dibuat (wire:init prepareQris),
    // jadi init tidak boleh hanya di DOMContentLoaded. Fungsi ini dipanggil ulang
    // saat halaman load, navigasi, dan setiap update Livewire — dengan satu penjaga
    // timer supaya tidak dobel.
    var phCdTimer = null;

    function initCountdown() {
        var startEl = document.getElementById('countdown');
        if (!startEl || phCdTimer) return; // belum muncul, atau timer sudah jalan

        var expiredTime = new Date(startEl.dataset.expired).getTime();
        if (isNaN(expiredTime)) return;
        var fired = false;

        phCdTimer = setInterval(function () {
            var el = document.getElementById('countdown');
            if (!el) { clearInterval(phCdTimer); phCdTimer = null; return; }

            var distance = expiredTime - Date.now();

            if (distance <= 0) {
                clearInterval(phCdTimer); phCdTimer = null;
                el.innerHTML = 'Kadaluarsa';
                if (!fired) {
                    fired = true;
                    // Trigger cek di server → batalkan pesanan & arahkan ke halaman expired
                    var comp = el.closest('[wire\\:id]');
                    if (comp && window.Livewire) {
                        try { window.Livewire.find(comp.getAttribute('wire:id')).call('checkPaymentStatus'); }
                        catch (e) { setTimeout(function () { location.reload(); }, 1000); }
                    } else {
                        setTimeout(function () { location.reload(); }, 1000);
                    }
                }
                return;
            }

            var totalSec = Math.floor(distance / 1000);
            var h = Math.floor(totalSec / 3600);
            var m = Math.floor((totalSec % 3600) / 60);
            var s = totalSec % 60;
            el.innerHTML = (h > 0 ? (h + 'j ') : '') + m + 'm ' + String(s).padStart(2, '0') + 'd';
        }, 1000);
    }

    document.addEventListener('DOMContentLoaded', initCountdown);
    document.addEventListener('livewire:navigated', initCountdown);
    document.addEventListener('livewire:init', function () {
        if (window.Livewire && typeof Livewire.hook === 'function') {
            // Setiap commit Livewire selesai (mis. prepareQris merender QRIS + countdown),
            // coba mulai countdown-nya.
            Livewire.hook('commit', function (payload) {
                if (payload && typeof payload.succeed === 'function') {
                    payload.succeed(function () { setTimeout(initCountdown, 40); });
                }
            });
        }
    });
})();

document.addEventListener('livewire:init', () => {

    var phPayDone = false;

    Livewire.on('payment-success', (event) => {
        if (phPayDone) return; phPayDone = true;
        var url = (event && event.url) ? event.url : '/';
        Swal.fire({
            icon: 'success',
            title: 'Pembayaran Berhasil! 🎉',
            text: 'Terima kasih atas pembayaran Anda. Anda akan diarahkan ke beranda.',
            confirmButtonText: 'Ke Beranda',
            confirmButtonColor: '#f26522',
            allowOutsideClick: false,
            timer: 3500,
            timerProgressBar: true,
            customClass: { popup: 'ph-swal' }
        }).then(() => { window.location.href = url; });
    });

    Livewire.on('payment-expired', (event) => {
        if (phPayDone) return; phPayDone = true;
        var url = (event && event.url) ? event.url : '/';
        Swal.fire({
            icon: 'error',
            title: 'Waktu Pembayaran Habis',
            text: 'Pesanan dibatalkan karena melewati batas waktu pembayaran. Anda akan diarahkan ke beranda.',
            confirmButtonText: 'Ke Beranda',
            confirmButtonColor: '#f26522',
            allowOutsideClick: false,
            timer: 4000,
            timerProgressBar: true,
            customClass: { popup: 'ph-swal' }
        }).then(() => { window.location.href = url; });
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