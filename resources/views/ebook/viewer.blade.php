<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $ebook->judul }} · Phoenix Digital</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <style>
        :root {
            --ungu: #6d28d9;
            --ungu-2: #4f46e5;
            --latar: #eef0f6;
            --tinta: #1c1f26;
            --redup: #64748b;
        }

        * { box-sizing: border-box; }
        [hidden] { display: none !important; }

        html, body {
            margin: 0;
            padding: 0;
            background: var(--latar);
            color: var(--tinta);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }

        /* ===== Bilah atas ===== */
        .bilah {
            position: sticky; top: 0; z-index: 10;
            display: flex; align-items: center; gap: 12px;
            padding: 10px clamp(12px, 3vw, 22px);
            background: rgba(255, 255, 255, .92);
            backdrop-filter: saturate(160%) blur(10px);
            -webkit-backdrop-filter: saturate(160%) blur(10px);
            border-bottom: 1px solid #e5e7f0;
        }
        .bilah-logo {
            flex: 0 0 38px; width: 38px; height: 38px; border-radius: 11px;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--ungu-2), var(--ungu));
            box-shadow: 0 6px 14px -6px rgba(79, 70, 229, .6);
        }
        .bilah-logo img { width: 24px; height: 24px; object-fit: contain; }
        .bilah-teks { flex: 1 1 auto; min-width: 0; }
        .bilah-judul { margin: 0; font-size: .98rem; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .bilah-sub { display: block; font-size: .72rem; color: var(--redup); }
        .chip {
            flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 10px; border-radius: 999px; font-size: .74rem; font-weight: 700; white-space: nowrap;
        }
        .chip-halaman { background: #f1f5f9; color: #334155; font-variant-numeric: tabular-nums; }
        .chip-baca { background: #ede9fe; color: var(--ungu); }
        @media (max-width: 575.98px) {
            .chip-baca { display: none; }
            .bilah-judul { font-size: .9rem; }
        }

        /* Garis kemajuan memuat, menempel di bawah bilah */
        .kemajuan { position: absolute; left: 0; bottom: -1px; height: 3px; width: 0; background: linear-gradient(90deg, var(--ungu-2), #a78bfa); transition: width .2s ease; }

        /* ===== Halaman PDF ===== */
        #halaman { max-width: 900px; margin: 18px auto; padding: 0 clamp(8px, 2vw, 14px) 48px; }
        #halaman canvas {
            display: block; width: 100%; height: auto; margin: 0 auto 14px;
            background: #fff; border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .06), 0 10px 28px -12px rgba(15, 23, 42, .25);
            pointer-events: none;
        }

        /* ===== Keadaan memuat & galat ===== */
        .kotak {
            max-width: 420px; margin: 12vh auto 0; padding: 28px 24px; text-align: center;
            background: #fff; border-radius: 20px; border: 1px solid #e5e7f0;
            box-shadow: 0 18px 40px -24px rgba(15, 23, 42, .35);
        }
        .kotak-ikon {
            width: 60px; height: 60px; margin: 0 auto 14px; border-radius: 18px;
            display: flex; align-items: center; justify-content: center; font-size: 1.6rem;
            background: #ede9fe; color: var(--ungu);
        }
        .kotak.is-galat .kotak-ikon { background: #fee2e2; color: #dc2626; }
        .kotak h2 { margin: 0 0 6px; font-size: 1.05rem; }
        .kotak p { margin: 0; font-size: .88rem; color: var(--redup); line-height: 1.55; }
        .putar { width: 26px; height: 26px; border-radius: 50%; border: 3px solid #ddd6fe; border-top-color: var(--ungu); animation: putar .8s linear infinite; }
        @keyframes putar { to { transform: rotate(360deg); } }
        .tombol {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; margin-top: 16px;
            min-height: 44px; padding: 0 18px; border-radius: 12px; border: 0; cursor: pointer;
            font-size: .88rem; font-weight: 700; text-decoration: none;
            background: linear-gradient(135deg, var(--ungu-2), var(--ungu)); color: #fff;
        }
        .tombol.is-wa { background: #25d366; }
        .tombol-deret { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
        .kaki { text-align: center; font-size: .74rem; color: #94a3b8; padding: 0 16px 28px; }
    </style>
</head>

<body>
    <header class="bilah">
        <span class="bilah-logo"><img src="{{ asset('favicon.png') }}" alt="Phoenix Digital"></span>
        <div class="bilah-teks">
            <h1 class="bilah-judul">{{ $ebook->judul }}</h1>
            <span class="bilah-sub">Ebook bonus · Phoenix Digital Warehouse</span>
        </div>
        <span class="chip chip-baca">🔒 Hanya untuk dibaca</span>
        <span class="chip chip-halaman" id="penunjuk" hidden>1 / 1</span>
        <span class="kemajuan" id="kemajuan"></span>
    </header>

    <main id="halaman">
        <div class="kotak" id="memuat">
            <div class="kotak-ikon"><span class="putar"></span></div>
            <h2>Menyiapkan ebook…</h2>
            <p id="memuat-ket">Mohon tunggu sebentar.</p>
        </div>
    </main>

    <p class="kaki">Ebook ini khusus untuk pelanggan Phoenix Digital. Mohon tidak disebarluaskan.</p>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        (function() {
            // ===== Proteksi dasar (mempersulit copy/save/print) =====
            document.addEventListener('contextmenu', e => e.preventDefault());
            document.addEventListener('dragstart', e => e.preventDefault());
            document.addEventListener('keydown', function(e) {
                const k = (e.key || '').toLowerCase();
                if ((e.ctrlKey || e.metaKey) && ['s', 'p', 'c', 'u', 'a'].includes(k)) {
                    e.preventDefault();
                }
                if (k === 'printscreen') {
                    e.preventDefault();
                }
            });
            window.addEventListener('beforeprint', () => {
                document.body.style.display = 'none';
            });

            const wadah = document.getElementById('halaman');
            const kemajuan = document.getElementById('kemajuan');
            const penunjuk = document.getElementById('penunjuk');
            const WA = 'https://wa.me/6289505967995?text=' + encodeURIComponent('Halo Phoenix Digital, ebook "' + @json($ebook->judul) + '" tidak bisa saya buka.');

            const galat = (judul, ket) => {
                kemajuan.style.width = '0';
                wadah.innerHTML =
                    '<div class="kotak is-galat"><div class="kotak-ikon">!</div><h2></h2><p></p>' +
                    '<div class="tombol-deret"><button type="button" class="tombol" onclick="location.reload()">Muat ulang</button>' +
                    '<a class="tombol is-wa" target="_blank" rel="noopener">Hubungi kami</a></div></div>';
                wadah.querySelector('h2').textContent = judul;
                wadah.querySelector('p').textContent = ket;
                wadah.querySelector('a').href = WA;
            };

            if (typeof pdfjsLib === 'undefined') {
                galat('Penampil gagal dimuat', 'Periksa koneksi internet Anda, lalu muat ulang halaman ini.');
                return;
            }
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            const RAW = @json(route('ebook.raw', $ebook->share_token));

            // PDF.js mengirim header khusus → endpoint raw menolak akses langsung tanpa header ini
            const tugas = pdfjsLib.getDocument({
                url: RAW,
                httpHeaders: { 'X-Ebook-View': '1' },
                withCredentials: false
            });
            tugas.onProgress = (p) => {
                if (p.total) kemajuan.style.width = Math.min(100, Math.round(p.loaded / p.total * 60)) + '%';
            };

            tugas.promise.then(async (pdf) => {
                const ket = document.getElementById('memuat-ket');
                const scale = (window.devicePixelRatio || 1) * 1.3;
                const kanvas = [];
                for (let i = 1; i <= pdf.numPages; i++) {
                    if (ket) ket.textContent = 'Menyiapkan halaman ' + i + ' dari ' + pdf.numPages + '…';
                    const page = await pdf.getPage(i);
                    const viewport = page.getViewport({ scale });
                    const canvas = document.createElement('canvas');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    canvas.dataset.halaman = i;
                    await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                    kanvas.push(canvas);
                    // Halaman pertama langsung ditampilkan; sisanya menyusul.
                    if (i === 1) document.getElementById('memuat')?.remove();
                    wadah.appendChild(canvas);
                    kemajuan.style.width = (60 + Math.round(i / pdf.numPages * 40)) + '%';
                }
                setTimeout(() => { kemajuan.style.opacity = '0'; }, 400);

                // Penunjuk "halaman x / y" mengikuti halaman yang sedang terlihat.
                penunjuk.hidden = false;
                penunjuk.textContent = '1 / ' + pdf.numPages;
                const amati = new IntersectionObserver((daftar) => {
                    daftar.forEach((d) => {
                        if (d.isIntersecting) penunjuk.textContent = d.target.dataset.halaman + ' / ' + pdf.numPages;
                    });
                }, { rootMargin: '-45% 0px -45% 0px' });
                kanvas.forEach((c) => amati.observe(c));
            }).catch(() => {
                galat('Ebook tidak dapat ditampilkan', 'Coba muat ulang halaman ini. Bila masih gagal, hubungi kami lewat WhatsApp.');
            });
        })();
    </script>
</body>

</html>
