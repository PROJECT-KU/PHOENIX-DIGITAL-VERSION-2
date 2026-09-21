{{-- Pintasan papan tik daftar artikel: "/" untuk mencari, "t" untuk berganti
     bentuk kartu/tabel. Dipasang SEKALI lewat penjaga global — mendaftarkan
     pendengar di tiap livewire:navigated membuatnya menumpuk. --}}
@once
<script data-navigate-once>
    if (!window.__blPintasan) {
        window.__blPintasan = true;

        document.addEventListener('keydown', function (e) {
            if (e.metaKey || e.ctrlKey || e.altKey) return;

            // Jangan rampas tombol saat orang sedang mengetik.
            const fokus = document.activeElement;
            if (fokus && (fokus.tagName === 'INPUT' || fokus.tagName === 'TEXTAREA' || fokus.tagName === 'SELECT' || fokus.isContentEditable)) return;

            if (e.key === '/') {
                const cari = document.getElementById('bl-cari');
                if (!cari) return;
                e.preventDefault();
                cari.focus();
                cari.select();
                return;
            }

            if (e.key === 't' || e.key === 'T') {
                const saklar = document.querySelector('.bl-saklar button:not(.is-aktif)');
                if (!saklar) return;
                e.preventDefault();
                saklar.click();
            }
        });
    }
</script>
@endonce
