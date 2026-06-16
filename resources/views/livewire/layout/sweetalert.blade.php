@php
// Kumpulkan semua kemungkinan session
$successFlash = session('successCreated') ?? session('successUpdated') ?? session('success');
$errorFlash = session('errorCreated') ?? session('errorUpdated') ?? session('error');
@endphp

<script data-navigate-once>
    window.fireGlossySwal = (title, text, icon) => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                background: 'rgba(255, 255, 255, 0.9)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: {
                    popup: 'swal-glossy-popup rounded-4 shadow-lg border-0',
                    confirmButton: 'btn-glossy-confirm',
                    cancelButton: 'btn-glossy-cancel',
                    title: 'fw-bold'
                },
                buttonsStyling: false,
                timer: 2500,
                showConfirmButton: false
            });
        }
    };

    // Tangkap Event Dispatch dari Controller
    window.addEventListener('swal-error', (e) => {
        const msg = (e.detail.message || (e.detail[0] && e.detail[0].message) || 'Terjadi kesalahan sistem.');
        window.fireGlossySwal('Gagal!', msg, 'error');
    });

    window.addEventListener('swal-success', (e) => {
        const msg = (e.detail.message || (e.detail[0] && e.detail[0].message) || 'Berhasil disimpan!');
        window.fireGlossySwal('Berhasil!', msg, 'success');
    });
</script>

<script>
    (function() {
        // Ambil data session terbaru dari server
        const successMsg = @json($successFlash);
        const errorMsg = @json($errorFlash);

        // Langsung tampilkan jika ada pesan, abaikan jika kosong
        setTimeout(() => {
            if (successMsg) window.fireGlossySwal('Berhasil!', successMsg, 'success');
            if (errorMsg) window.fireGlossySwal('Gagal!', errorMsg, 'error');
        }, 100); // Jeda singkat agar transisi halaman Livewire selesai
    })();
</script>