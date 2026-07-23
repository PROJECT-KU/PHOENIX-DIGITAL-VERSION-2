<div wire:poll.5s.keep-alive>
    {{-- Pembawa hitungan badge (total + per kategori), tak terlihat. Diperbarui
         tiap poll. Dipakai untuk:
         - badge di judul tab: "(N) lemon"
         - popup notifikasi OS (seperti WhatsApp) saat suatu kategori bertambah.
         keep-alive menjaga hitungan tetap segar meski admin sedang di
         aplikasi/tab lain, sehingga popup tetap muncul. Komponen ini SENGAJA
         terpisah dari sidebar agar poll tak me-render ulang menu (yang membuat
         dropdown/aktif hilang). --}}
    <span id="ttl-badge" hidden aria-hidden="true"
        data-n="{{ (int) $titleBadge }}"
        data-orders="{{ (int) $pesananTokoPaid }}"
        data-testimoni="{{ (int) $testimoniBaru }}"
        data-ulasan="{{ (int) $ulasanBaru }}"
        data-helpdesk="{{ (int) $helpdeskBaru }}"></span>
    @script
    <script>
        (() => {
            const el = document.getElementById('ttl-badge');

            // ===== Badge di judul tab: "(N) lemon" =====
            const applyTitle = () => {
                const n = el ? (parseInt(el.dataset.n) || 0) : 0;
                const base = document.title.replace(/^\(\d+\)\s*/, '');
                document.title = n > 0 ? '(' + n + ') ' + base : base;
            };

            // ===== Popup notifikasi OS (seperti WhatsApp) saat kategori bertambah =====
            const PESAN = {
                orders: 'Pesanan toko baru — sudah dibayar, siap diproses.',
                testimoni: 'Testimoni baru menunggu moderasi.',
                ulasan: 'Ulasan produk baru menunggu moderasi.',
                helpdesk: 'Pesan helpdesk baru masuk.',
            };
            const bacaCounts = () => ({
                orders: parseInt(el?.dataset.orders) || 0,
                testimoni: parseInt(el?.dataset.testimoni) || 0,
                ulasan: parseInt(el?.dataset.ulasan) || 0,
                helpdesk: parseInt(el?.dataset.helpdesk) || 0,
            });
            const notif = (teks) => {
                if (!('Notification' in window) || Notification.permission !== 'granted') return;
                try {
                    const n = new Notification('Phoenix Digital', {
                        body: teks,
                        icon: '/icons/apple-touch-icon.png',
                        tag: 'phoenix-admin',   // popup baru menggantikan yang lama, tidak menumpuk
                        renotify: true,
                        requireInteraction: true, // bertahan sampai diklik/ditutup — biar admin ngeh
                    });
                    n.onclick = () => { window.focus(); n.close(); };
                } catch (e) {}
            };
            // Baseline: nilai saat halaman dibuka TIDAK memicu notif — hanya kenaikan sesudahnya.
            let prev = null;
            const cekNotif = () => {
                const now = bacaCounts();
                if (prev === null) { prev = now; return; }
                let adaBaru = false;
                for (const k in PESAN) {
                    if (now[k] > prev[k]) { notif(PESAN[k]); adaBaru = true; }
                }
                // Suara "lemon" saat ada kategori baru (foreground). Debounce di
                // window.lemonChime mencegah bunyi dobel dgn pengecek unread lonceng.
                if (adaBaru && window.lemonChime) window.lemonChime();
                prev = now;
            };

            const perbarui = () => { applyTitle(); cekNotif(); };

            perbarui();
            if (el && !el.__ttlObserved) {
                el.__ttlObserved = true;
                // Amati semua data-* (total + per kategori) supaya kenaikan terdeteksi.
                new MutationObserver(perbarui).observe(el, { attributes: true });
            }
            if (!window.__ttlBadgeNav) {
                window.__ttlBadgeNav = true;
                document.addEventListener('livewire:navigated', perbarui);
            }
            // Minta izin notifikasi saat admin pertama kali klik (syarat gesture browser).
            if ('Notification' in window && Notification.permission === 'default' && !window.__ttlAskPerm) {
                window.__ttlAskPerm = true;
                document.addEventListener('click', function once() {
                    try { Notification.requestPermission(); } catch (e) {}
                    document.removeEventListener('click', once);
                }, { once: true });
            }
        })();
    </script>
    @endscript
</div>
