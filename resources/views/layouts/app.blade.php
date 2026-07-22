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

        {{-- Poller tak terlihat: badge judul tab + popup notifikasi OS. Terpisah
             dari sidebar supaya poll-nya tak menutup dropdown menu. --}}
        @auth
        <livewire:layout.notif-poller />
        @endauth

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

        {{-- Satu-satunya penampil alert dari session.

             Sebelumnya alert sukses tampil DUA KALI: versi polos dari sini, lalu
             versi glossy dari partial livewire.layout.sweetalert yang di-include
             halaman list. Keduanya membaca kunci session yang sama, sedangkan
             templateindex meng-extends layout ini — jadi dua-duanya jalan.

             Sekarang partial hanya mendefinisikan helper + mendengar event
             swal-success/swal-error, dan penampilan dari session hanya di sini.
             Kunci successCreated/successUpdated ikut dibaca karena beberapa form
             (mis. Blog) memakai kunci itu, bukan 'success'. --}}
        @php
            $flashSukses = session('successCreated') ?? session('successUpdated') ?? session('success');
            $flashGagal = session('errorCreated') ?? session('errorUpdated') ?? session('error');
        @endphp

        // Pakai helper glossy dari partial bila sudah ada, agar tampilannya
        // seragam; kalau halaman tidak meng-include partial, pakai cadangan
        // dengan gaya yang sama.
        window.fireGlossySwal = window.fireGlossySwal || function (title, text, icon) {
            if (typeof Swal === 'undefined') return;
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
        };

        function showAlertFromSession() {
            const sukses = @js($flashSukses);
            const gagal = @js($flashGagal);

            if (sukses) window.fireGlossySwal('Berhasil!', sukses, 'success');
            if (gagal) window.fireGlossySwal('Gagal!', gagal, 'error');
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
                navigator.serviceWorker.register('{{ asset('sw.js') }}').then((reg) => {
                    reg.update().catch(() => {}); // cek versi baru tiap muat halaman
                }).catch(() => {});

                // Saat service worker BARU mengambil alih (mis. setelah deploy), muat ulang
                // SEKALI agar kode/aset selalu terbaru — mencegah tampilan basi tanpa perlu
                // hapus cache manual. Tidak reload saat install pertama & tidak berulang.
                let phHadController = !!navigator.serviceWorker.controller;
                let phReloaded = false;
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    if (!phHadController || phReloaded) return;
                    phReloaded = true;
                    window.location.reload();
                });
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

        // ====== Suara "lemon" (jingle ceria) saat ada notifikasi baru ======
        // Hanya berbunyi saat website/PWA TERBUKA (foreground). Saat tertutup/
        // push/banner HP, suara mengikuti bawaan OS — web/PWA tak boleh set suara
        // kustom untuk notifikasi background (hanya app native seperti BRImo bisa).
        // Disintesis via Web Audio (tanpa file), di-unlock saat gestur pertama.
        window.lemonSoundOn = () => localStorage.getItem('lemon-sound') !== 'off';
        window.lemonChime = (function () {
            let ctx = null, last = 0;
            function ensureCtx() {
                try {
                    if (!ctx) ctx = new (window.AudioContext || window.webkitAudioContext)();
                    if (ctx.state === 'suspended') ctx.resume().catch(() => {});
                } catch (e) { ctx = null; }
                return ctx;
            }
            // Autoplay policy: AudioContext baru boleh bunyi setelah ada gestur user.
            document.addEventListener('click', ensureCtx, { once: true });
            document.addEventListener('keydown', ensureCtx, { once: true });

            // Satu "not" hangat & berkilau (fundamental + overtone oktaf & oktaf-2),
            // amplop cepat-lalu-meluruh seperti glockenspiel/marimba — nuansa premium
            // ala jingle m-banking (BRImo).
            function nada(ac, freq, mulai, durasi, puncak) {
                const g = ac.createGain();
                g.gain.setValueAtTime(0.0001, mulai);
                g.gain.exponentialRampToValueAtTime(puncak, mulai + 0.012); // attack renyah
                g.gain.exponentialRampToValueAtTime(0.0001, mulai + durasi); // decay lonceng
                g.connect(ac.destination);

                const o1 = ac.createOscillator(); o1.type = 'sine';     o1.frequency.value = freq;
                const o2 = ac.createOscillator(); o2.type = 'sine';     o2.frequency.value = freq * 2;
                const o3 = ac.createOscillator(); o3.type = 'triangle'; o3.frequency.value = freq * 4;
                const g2 = ac.createGain(); g2.gain.value = 0.28;  // overtone oktaf (kilau)
                const g3 = ac.createGain(); g3.gain.value = 0.06;  // sparkle tinggi (tipis)
                o1.connect(g);
                o2.connect(g2).connect(g);
                o3.connect(g3).connect(g);
                [o1, o2, o3].forEach(o => { o.start(mulai); o.stop(mulai + durasi + 0.03); });
            }
            // Ucapkan brand "lemon" via Text-to-Speech (biar jelas "ngomong lemon").
            function ucapLemon() {
                try {
                    if (!('speechSynthesis' in window)) return;
                    window.speechSynthesis.cancel(); // jangan menumpuk
                    const u = new SpeechSynthesisUtterance('lemon');
                    u.lang = 'en-US';   // pengucapan "lemon" lebih jelas dgn voice Inggris
                    u.rate = 0.95;
                    u.pitch = 1.2;      // sedikit ceria
                    u.volume = 1;
                    const vs = window.speechSynthesis.getVoices() || [];
                    const en = vs.find(v => /^en(-|_)/i.test(v.lang));
                    if (en) u.voice = en;
                    window.speechSynthesis.speak(u);
                } catch (e) {}
            }

            return function () {
                if (!window.lemonSoundOn()) return;       // dimatikan admin
                const now = Date.now();
                if (now - last < 3000) return;            // debounce: 1 bunyi per ~3 dtk
                last = now;
                const ac = ensureCtx();
                if (ac) {
                    const t = ac.currentTime;
                    // Motif "sukses" ceria menaik C–E–G–C (C mayor) + resolusi tinggi.
                    nada(ac, 1046.50, t + 0.00, 0.16, 0.22); // C6
                    nada(ac, 1318.51, t + 0.085, 0.16, 0.22); // E6
                    nada(ac, 1567.98, t + 0.17, 0.20, 0.21); // G6
                    nada(ac, 2093.00, t + 0.29, 0.42, 0.20); // C7 (resolusi, panjang)
                }
                // Setelah jingle naik, ucapkan "lemon".
                setTimeout(ucapLemon, ac ? 300 : 0);
            };
        })();

        // Tombol on/off suara (di panel lonceng). Preferensi disimpan per-perangkat.
        window.lemonToggleSound = function () {
            const mati = window.lemonSoundOn();           // sedang ON → akan dimatikan
            localStorage.setItem('lemon-sound', mati ? 'off' : 'on');
            window.lemonSoundSync();
            if (!mati) window.lemonChime();               // baru dinyalakan → putar contoh
        };
        // Selaraskan label tombol dgn preferensi (dipanggil ulang tiap render lonceng).
        window.lemonSoundSync = function () {
            document.querySelectorAll('.lemon-sound-toggle').forEach(function (btn) {
                const on = window.lemonSoundOn();
                btn.innerHTML = on
                    ? '🔊 Suara notifikasi: Aktif'
                    : '🔇 Suara notifikasi: Nonaktif';
                btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
        };

        // Bunyikan lemon saat unread lonceng NAIK (task/gaji/pesanan baru). Nilai
        // saat load jadi baseline (tak berbunyi utk yg sudah ada). Toast kategori
        // bisnis (pesanan/testimoni/ulasan/helpdesk) memanggil lemonChime sendiri
        // dari komponen notif-poller; debounce di atas mencegah bunyi dobel.
        window.__lemonPrevUnread = null;
        window.lemonBellChimeCheck = function () {
            const bell = document.getElementById('lemon-bell');
            const c = bell ? parseInt(bell.dataset.unread || '0', 10) : 0;
            if (window.__lemonPrevUnread !== null && c > window.__lemonPrevUnread) {
                window.lemonChime();
            }
            window.__lemonPrevUnread = c;
        };

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
        window.addEventListener('load', () => { window.lemonSyncBadge(); window.lemonFetchBadge(); window.lemonBellChimeCheck(); window.lemonSoundSync(); });
        document.addEventListener('livewire:navigated', () => { window.lemonSyncBadge(); window.lemonBellChimeCheck(); window.lemonSoundSync(); });

        // Saat app/tab kembali terlihat atau di-fokus → ambil count terbaru dari server.
        document.addEventListener('visibilitychange', () => { if (!document.hidden) window.lemonFetchBadge(); });
        window.addEventListener('focus', () => window.lemonFetchBadge());

        // Jalankan tiap kali Livewire selesai update (lonceng poll 30s / markAsRead / markAllRead).
        // lemonSyncBadge: update badge lokal cepat (dari DOM). lemonFetchBadgeSoon: rekonsiliasi
        // notifikasi pakai count fresh dari server (aman, menutup notif yg sudah dibaca).
        // Jaga submenu SEKSI AKTIF tetap terbuka. Submenu dibuka oleh JS Mazer
        // (class submenu-open + --submenu-height pada <ul>). Saat sidebar
        // re-render karena badge (approve/reject/baca pesan), Livewire mem-morph
        // DOM menu & status buka itu hilang -> submenu menutup sendiri. Di sini
        // kita buka lagi lewat inline-style (mengalahkan semua CSS) setelah tiap
        // commit selesai — termasuk commit re-render sidebar itu sendiri.
        window.lemonKeepSidebarOpen = function () {
            document.querySelectorAll('#sidebar .sidebar-item.has-sub.active > .submenu').forEach(function (sm) {
                sm.classList.remove('submenu-closed');
                sm.classList.add('submenu-open');
                // Inline-style mengalahkan semua CSS (Mazer tak punya !important
                // pada max-height/display untuk submenu di mode normal).
                sm.style.maxHeight = '1500px';
                sm.style.overflow = 'visible';
                sm.style.display = 'block';
            });
        };

        document.addEventListener('livewire:init', () => {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    // Sinkron (setelah morph, sebelum paint) agar submenu tidak
                    // sempat berkedip menutup saat badge di-update.
                    window.lemonKeepSidebarOpen();
                    setTimeout(window.lemonSyncBadge, 0);
                    setTimeout(window.lemonBellChimeCheck, 0); // bunyi lemon bila unread naik
                    setTimeout(window.lemonSoundSync, 0);      // label tombol suara di lonceng
                    lemonFetchBadgeSoon();
                });
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