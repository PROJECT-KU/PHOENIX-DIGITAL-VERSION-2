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