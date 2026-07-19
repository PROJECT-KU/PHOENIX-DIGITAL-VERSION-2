<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2">
    <title>{{ $ebook->judul }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #1f2330;
            font-family: 'Segoe UI', Arial, sans-serif;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .85rem 1.1rem;
            background: linear-gradient(135deg, #4e46e5, #6c63ff);
            color: #fff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        }

        .topbar .title {
            font-weight: 700;
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .topbar .badge {
            font-size: .72rem;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.35);
            padding: .25rem .6rem;
            border-radius: 999px;
            white-space: nowrap;
        }

        #pages {
            max-width: 900px;
            margin: 1.2rem auto;
            padding: 0 .8rem 3rem;
        }

        #pages canvas {
            display: block;
            width: 100%;
            height: auto;
            margin: 0 auto 1rem;
            border-radius: 6px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
            pointer-events: none;
        }

        .loader {
            color: #c7c9d9;
            text-align: center;
            padding: 3rem 1rem;
            font-size: .95rem;
        }

        .watermark {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 5;
            opacity: .07;
            background-repeat: repeat;
            background-position: center;
            transform: rotate(-30deg) scale(1.4);
            font-weight: 800;
        }

        .errbox {
            color: #fecaca;
            text-align: center;
            padding: 3rem 1rem;
        }
    </style>
</head>

<body>
    <div class="topbar">
        <div class="title">{{ $ebook->judul }}</div>
        <div class="badge">Hanya untuk dibaca · Phoenix Digital</div>
    </div>

    <div id="pages">
        <div class="loader" id="loader">Memuat dokumen…</div>
    </div>

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
            window.addEventListener('beforeprint', e => {
                document.body.style.display = 'none';
            });

            if (typeof pdfjsLib === 'undefined') {
                document.getElementById('pages').innerHTML =
                    '<div class="errbox">Gagal memuat pustaka tampilan.</div>';
                return;
            }
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            const RAW = @json(route('ebook.raw', $ebook->share_token));
            const container = document.getElementById('pages');
            const loader = document.getElementById('loader');

            // PDF.js mengirim header khusus → endpoint raw menolak akses langsung tanpa header ini
            pdfjsLib.getDocument({
                url: RAW,
                httpHeaders: {
                    'X-Ebook-View': '1'
                },
                withCredentials: false
            }).promise.then(async (pdf) => {
                loader.remove();
                const scale = (window.devicePixelRatio || 1) * 1.3;
                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const viewport = page.getViewport({
                        scale
                    });
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    container.appendChild(canvas);
                    await page.render({
                        canvasContext: ctx,
                        viewport
                    }).promise;
                }
            }).catch((err) => {
                loader.remove();
                container.innerHTML = '<div class="errbox">Dokumen tidak dapat ditampilkan.</div>';
            });
        })();
    </script>
</body>

</html>
