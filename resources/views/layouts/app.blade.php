<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ session('theme', 'light') }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'lemon')</title>

    <!-- Favicon (logo lemon, seragam dengan login & sidebar) -->
    <link rel="icon" href="{{ asset('lemon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">

    <!-- PWA (installable + badge notifikasi seperti WhatsApp).
         Manifest di-serve lewat route ber-auth → hanya user login yang bisa install. -->
    @auth
    {{-- crossorigin=use-credentials WAJIB: manifest di-serve lewat route auth,
         browser hrs mengirim cookie sesi agar manifest ter-load (kalau tidak → ikon install tak muncul). --}}
    <link rel="manifest" href="{{ route('pwa.manifest') }}" crossorigin="use-credentials">
    <meta name="vapid-public-key" content="{{ config('services.webpush.public_key') }}">
    @endauth
    <meta name="theme-color" content="#84cc16">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="lemon by acm">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />


    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/app.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/iconly.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/custom.css') }}">

    @stack('scripts-head')
    @stack('styles')

    <style>
        /* Transisi halus saat berpindah menu (wire:navigate).
           Fade opacity murni (di-composite GPU) — tetap mulus meski halaman berat.
           Dipicu via requestAnimationFrame di JS (tanpa reflow paksa yang bikin sendat). */
        #page-content { will-change: opacity; }

        @media (prefers-reduced-motion: reduce) {
            #page-content { transition: none !important; opacity: 1 !important; }
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <livewire:layout.sidebar />

        <div id="main">
            <header class="mb-3 d-flex align-items-center justify-content-between">
                <a href="#" class="burger-btn d-block">
                    <i class="bi bi-list fs-3"></i>
                </a>
                @auth
                <div class="ms-auto">
                    <livewire:layout.notification-bell />
                </div>
                @endauth
            </header>
            <div id="page-content">
                {{ $slot }}
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT ==================-->
    @push('scripts')
    <script>
        window.addEventListener('swal-alert', (event) => {
            Swal.fire({
                icon: event.detail[0].type,
                title: event.detail[0].title,
                text: event.detail[0].message,
                timer: 2000,
                showConfirmButton: false
            });
        });

        window.addEventListener('swal-confirm', (event) => {
            Swal.fire({
                icon: event.detail[0].type,
                title: event.detail[0].title,
                text: event.detail[0].message,
                confirmButtonText: 'OK',
                allowOutsideClick: false
            });
        });

        function showAlertFromSession() {
            @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @js(session('success')),
                timer: 2000,
                showConfirmButton: false
            });
            @endif

            @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: @js(session('error'))
            });
            @endif
        }

        // 1. Saat pertama kali load page
        window.addEventListener('load', () => {
            showAlertFromSession();
        });

        // 2. Saat Livewire selesai re-render
        document.addEventListener('livewire:navigated', () => {
            showAlertFromSession();
            // Transisi halus antar halaman TANPA reflow paksa (offsetWidth) yang bikin
            // sendat di halaman berat. Pakai requestAnimationFrame: set opacity 0 lalu
            // fade ke 1 di frame berikutnya.
            const pc = document.getElementById('page-content');
            if (pc) {
                pc.style.transition = 'none';
                pc.style.opacity = '0';
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        pc.style.transition = 'opacity .28s ease';
                        pc.style.opacity = '1';
                    });
                });
            }
        });

        document.addEventListener('livewire:load', () => {
            Livewire.hook('message.processed', () => {
                showAlertFromSession();
            });
        });

        document.addEventListener('show-alert', function(event) {
            const detail = event.detail[0] || event.detail;

            Swal.fire({
                icon: detail.type,
                title: detail.type === 'success' ? 'Berhasil!' : 'Gagal!',
                text: detail.message,
                timer: detail.type === 'success' ? 2000 : null,
                showConfirmButton: detail.type !== 'success'
            });
        });
        document.addEventListener('alpine:init', () => {
            Alpine.directive('currency', (el, {}, {
                cleanup
            }) => {
                const formatRupiah = (value) => {
                    if (!value) return '';
                    let number = value.toString().replace(/[^0-9]/g, '');
                    if (!number) return '';
                    return 'Rp ' + parseInt(number).toLocaleString('id-ID');
                };

                const parseRupiah = (value) => {
                    return value.replace(/[^0-9]/g, '');
                };

                let isFormatting = false;

                const handleInput = (e) => {
                    if (isFormatting) return;

                    isFormatting = true;
                    const rawValue = parseRupiah(e.target.value);
                    e.target.value = formatRupiah(rawValue);
                    isFormatting = false;
                };

                // Format initial value hanya sekali
                const initializeValue = () => {
                    if (el.value && !el.dataset.formatted) {
                        // Hanya format jika belum ada prefix "Rp"
                        if (!el.value.toString().startsWith('Rp')) {
                            el.value = formatRupiah(el.value);
                        }
                        el.dataset.formatted = 'true';
                    }
                };

                // Tunggu Livewire selesai render
                if (window.Livewire) {
                    Livewire.hook('morph.updated', () => {
                        initializeValue();
                    });
                }

                // Untuk initial load
                setTimeout(initializeValue, 50);

                el.addEventListener('input', handleInput);

                cleanup(() => {
                    el.removeEventListener('input', handleInput);
                });
            });
        });
    </script>
    @endpush
    <!--================== END ==================-->

    <!--================== PWA + BADGE NOTIFIKASI ==================-->
    @push('scripts')
    <script>
        // 1) Daftarkan service worker (installable + siap push di masa depan)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(() => {});
            });
        }

        // 2) Badge angka di ikon PWA (seperti WhatsApp) — sinkron dgn lonceng.
        //    Web (tab browser) & aplikasi terinstall adalah konteks TERPISAH. Supaya saat
        //    baca notif di web, badge Dock aplikasi ikut turun TANPA refresh, kedua konteks
        //    saling kabari lewat BroadcastChannel (instan bila aplikasi sedang terbuka).
        const lemonBadgeCh = ('BroadcastChannel' in window) ? new BroadcastChannel('lemon-badge') : null;

        // Minta service worker (dibagi web & aplikasi) merapikan notifikasi agar
        // jumlahnya = unread. Di macOS badge Dock = jumlah notifikasi aktif, jadi ini
        // yang benar-benar menurunkan badge Dock saat notif dibaca — walau dipicu dari web.
        function lemonReconcileSW(count) {
            const sw = navigator.serviceWorker;
            if (sw && sw.controller) {
                try { sw.controller.postMessage({ type: 'lemon-reconcile', unread: count }); } catch (e) {}
            }
        }
        // Set/clear badge di konteks ini SAJA. TIDAK menutup notifikasi di sini —
        // karena count bisa berasal dari DOM yang basi & bisa menutup banner baru.
        function lemonApplyBadge(count) {
            if (!('setAppBadge' in navigator)) return;
            try {
                if (count > 0) navigator.setAppBadge(count);
                else navigator.clearAppBadge();
            } catch (e) {}
        }
        // Set badge di konteks ini + kabari konteks lain (web ↔ aplikasi).
        function lemonBroadcastBadge(count) {
            lemonApplyBadge(count);
            if (lemonBadgeCh) { try { lemonBadgeCh.postMessage(count); } catch (e) {} }
        }
        // Terima kabar dari konteks lain → cukup terapkan (jangan broadcast balik).
        if (lemonBadgeCh) {
            lemonBadgeCh.onmessage = (e) => lemonApplyBadge(parseInt(e.data || 0, 10));
        }

        // Baca jumlah unread dari komponen lonceng lalu set + broadcast.
        window.lemonSyncBadge = function () {
            const bell = document.getElementById('lemon-bell');
            const count = bell ? parseInt(bell.dataset.unread || '0', 10) : 0;
            lemonBroadcastBadge(count);
        };

        // Ambil jumlah unread TERBARU dari server → set + broadcast + rapikan notifikasi.
        // HANYA di sini notifikasi ditutup (pakai count fresh dari server, bukan DOM basi),
        // jadi banner notif baru tak pernah tertutup mendadak.
        window.lemonFetchBadge = async function () {
            try {
                const res = await fetch('{{ route('notifications.unread-count') }}', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const j = await res.json();
                const c = parseInt(j.count || 0, 10);
                lemonBroadcastBadge(c);
                lemonReconcileSW(c); // tutup notifikasi berlebih sesuai unread terbaru
                // Kalau sudah 0 tapi ada notif yang masih dalam masa tenggang (baru <3dtk),
                // ulangi sekali setelah tenggang lewat supaya badge benar-benar bersih.
                if (c === 0) setTimeout(() => lemonReconcileSW(0), 3400);
            } catch (e) {}
        };

        // Debounce supaya banyak commit beruntun tak memicu banyak fetch.
        let lemonFetchTimer = null;
        function lemonFetchBadgeSoon() {
            clearTimeout(lemonFetchTimer);
            lemonFetchTimer = setTimeout(() => window.lemonFetchBadge(), 400);
        }

        // Jalankan saat load & tiap kali pindah halaman (wire:navigate).
        window.addEventListener('load', () => { window.lemonSyncBadge(); window.lemonFetchBadge(); });
        document.addEventListener('livewire:navigated', window.lemonSyncBadge);

        // Saat app/tab kembali terlihat atau di-fokus → ambil count terbaru dari server.
        document.addEventListener('visibilitychange', () => { if (!document.hidden) window.lemonFetchBadge(); });
        window.addEventListener('focus', () => window.lemonFetchBadge());

        // Jalankan tiap kali Livewire selesai update (lonceng poll 30s / markAsRead / markAllRead).
        // lemonSyncBadge: update badge lokal cepat (dari DOM). lemonFetchBadgeSoon: rekonsiliasi
        // notifikasi pakai count fresh dari server (aman, menutup notif yg sudah dibaca).
        document.addEventListener('livewire:init', () => {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => { setTimeout(window.lemonSyncBadge, 0); lemonFetchBadgeSoon(); });
            });
        });

        // 3) Web Push — notifikasi tetap masuk & badge update walau app tertutup / di iPhone.
        (function () {
            const vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
            const VAPID = vapidMeta ? vapidMeta.content : '';
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

            function urlB64ToUint8(base64) {
                const pad = '='.repeat((4 - (base64.length % 4)) % 4);
                const b64 = (base64 + pad).replace(/-/g, '+').replace(/_/g, '/');
                const raw = atob(b64);
                const arr = new Uint8Array(raw.length);
                for (let i = 0; i < raw.length; i++) arr[i] = raw.charCodeAt(i);
                return arr;
            }
            function encoding() {
                return (window.PushManager && PushManager.supportedContentEncodings || ['aesgcm'])[0];
            }
            async function ready() {
                return ('serviceWorker' in navigator) ? navigator.serviceWorker.ready : null;
            }
            async function currentSub() {
                const reg = await ready();
                return (reg && reg.pushManager) ? reg.pushManager.getSubscription() : null;
            }
            async function post(url, body) {
                return fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify(body),
                });
            }
            async function subscribe() {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    alert('Browser/perangkat ini tidak mendukung notifikasi.');
                    return;
                }
                if (!VAPID) return;
                const perm = await Notification.requestPermission();
                if (perm !== 'granted') { updateBtn(); return; }
                const reg = await ready();
                let sub = await reg.pushManager.getSubscription();
                if (!sub) {
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlB64ToUint8(VAPID),
                    });
                }
                const j = sub.toJSON();
                await post('{{ route('push.subscribe') }}', { endpoint: sub.endpoint, keys: j.keys, contentEncoding: encoding() });
                updateBtn();
            }
            async function unsubscribe() {
                const sub = await currentSub();
                if (sub) {
                    await post('{{ route('push.unsubscribe') }}', { endpoint: sub.endpoint });
                    await sub.unsubscribe();
                }
                updateBtn();
            }
            async function toggle() {
                const sub = await currentSub();
                if (sub && Notification.permission === 'granted') await unsubscribe();
                else await subscribe();
            }
            async function updateBtn() {
                const btn = document.getElementById('lemon-push-toggle');
                if (!btn) return;
                if (!('PushManager' in window) || !('Notification' in window)) { btn.style.display = 'none'; return; }
                const sub = await currentSub();
                const on = !!sub && Notification.permission === 'granted';
                btn.textContent = on ? '🔕 Matikan notifikasi perangkat' : '🔔 Aktifkan notifikasi perangkat';
            }
            async function silentResync() {
                if (('Notification' in window) && Notification.permission === 'granted') {
                    const sub = await currentSub();
                    if (sub) {
                        const j = sub.toJSON();
                        post('{{ route('push.subscribe') }}', { endpoint: sub.endpoint, keys: j.keys, contentEncoding: encoding() }).catch(() => {});
                    }
                }
                updateBtn();
            }

            window.lemonPush = { subscribe, unsubscribe, toggle, refresh: updateBtn };
            window.addEventListener('load', () => {
                if ('serviceWorker' in navigator) navigator.serviceWorker.ready.then(silentResync).catch(() => {});
            });
            document.addEventListener('livewire:navigated', updateBtn);
        })();
    </script>
    @endpush
    <!--================== END ==================-->

    <!-- script kebutuhan template -->
    <script src="{{ asset('mazer/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>

    <script src="{{ asset('mazer/compiled/js/app.js') }}"></script>
    <script src="{{ asset('mazer/compiled/js/custom.js') }}"></script>
    <script src="{{ asset('mazer/compiled/js/DataAkun-delete.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')
</body>




</html>