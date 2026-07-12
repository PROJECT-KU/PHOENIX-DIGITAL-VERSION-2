/* Phoenix Digital PWA service worker
   - Konservatif: TIDAK meng-cache HTML halaman (auth/CSRF/Livewire tetap fresh).
   - Hanya cache aset statis + halaman offline sederhana.
   - Versi cache OTOMATIS mengikuti build (di-inject server dari hash manifest Vite),
     jadi setiap deploy → cache lama otomatis dibuang. Tidak perlu bump manual. */

const CACHE = 'phoenix-__SW_VERSION__';
const OFFLINE_URL = '/offline.html';
const PRECACHE = [
  OFFLINE_URL,
  '/icons/icon-192.png',
  '/icons/icon-512.png',
  '/icons/icon-maskable-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return; // jangan sentuh POST/Livewire update

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return; // biarkan lintas-origin apa adanya

  // Navigasi halaman: network-first, fallback ke halaman offline.
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }

  // Aset statis (css/js/gambar/font): stale-while-revalidate.
  if (/\.(css|js|png|jpg|jpeg|svg|gif|webp|ico|woff2?|ttf)$/i.test(url.pathname)) {
    event.respondWith(
      caches.open(CACHE).then((cache) =>
        cache.match(req).then((cached) => {
          const network = fetch(req).then((res) => {
            if (res && res.status === 200) cache.put(req, res.clone());
            return res;
          }).catch(() => cached);
          return cached || network;
        })
      )
    );
  }
});

/* ====== Web Push (opsional, aktif bila subscription dikirim dari server) ====== */
self.addEventListener('push', (event) => {
  let data = {};
  try { data = event.data ? event.data.json() : {}; } catch (e) { data = {}; }

  const title = data.title || 'Phoenix Digital';
  const options = {
    body: data.body || '',
    icon: '/icons/icon-192.png',
    badge: '/icons/icon-192.png',
    data: { url: data.url || '/' },
  };

  const count = typeof data.unread === 'number' ? data.unread : null;
  event.waitUntil((async () => {
    await self.registration.showNotification(title, options);
    if (count !== null && self.navigator.setAppBadge) {
      count > 0 ? self.navigator.setAppBadge(count) : self.navigator.clearAppBadge();
    }
  })());
});

/* ====== Rekonsiliasi badge (macOS: badge Dock = jumlah notifikasi aktif) ======
   Halaman mengirim jumlah unread terkini; SW menutup kelebihan notifikasi supaya
   jumlah notifikasi = unread (0 → tutup semua) sehingga badge Dock ikut turun. */
self.addEventListener('message', (event) => {
  const data = event.data || {};
  if (data.type !== 'lemon-reconcile') return;

  const unread = typeof data.unread === 'number' ? Math.max(0, data.unread) : 0;
  const GRACE_MS = 3000; // jangan tutup notifikasi yang baru muncul < 3 dtk (lindungi banner baru)
  event.waitUntil((async () => {
    const all = await self.registration.getNotifications();
    // Hanya kandidat yang sudah cukup "tua" yang boleh ditutup.
    const now = Date.now();
    const closable = all.filter(n => !n.timestamp || (now - n.timestamp) > GRACE_MS);
    const excess = all.length - unread;
    if (excess > 0) {
      // Tutup yang tertua lebih dulu, sebanyak kelebihannya (dibatasi yang closable).
      closable.sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));
      for (let i = 0; i < Math.min(excess, closable.length); i++) {
        closable[i].close();
      }
    }
    if (self.navigator.setAppBadge) {
      unread > 0 ? self.navigator.setAppBadge(unread) : self.navigator.clearAppBadge();
    }
  })());
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const target = (event.notification.data && event.notification.data.url) || '/';
  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((list) => {
      for (const c of list) {
        if ('focus' in c) { c.navigate(target); return c.focus(); }
      }
      return self.clients.openWindow(target);
    })
  );
});
