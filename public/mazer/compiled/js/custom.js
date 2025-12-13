document.addEventListener('click', function (e) {
    const btn = e.target.closest('.toggle-password');
    if (!btn) return; // kalau bukan tombol toggle, abaikan

    const span = btn.closest('td').querySelector('.password-mask');
    const realPassword = span.getAttribute('data-password') || '';
    const isVisible = span.getAttribute('data-visible') === 'true';

    if (isVisible) {
        // sembunyikan
        span.textContent = '••••••••';
        span.setAttribute('data-visible', 'false');
        btn.innerHTML = '<i class="bi bi-eye"></i>';
    } else {
        // tampilkan
        span.textContent = realPassword;
        span.setAttribute('data-visible', 'true');
        btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    }
});

function formatRupiah(value) {
    if (!value) return '';
    let number_string = value.toString().replace(/[^,\d]/g, '');
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
    return rupiah ? 'Rp ' + rupiah : '';
}

document.addEventListener("DOMContentLoaded", () => {
    if (typeof Livewire !== "undefined") {
        Livewire.on("swal", (data) => {
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,
                showConfirmButton: false,
                timer: 2000,
            }).then(() => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            });
        });
    }
});