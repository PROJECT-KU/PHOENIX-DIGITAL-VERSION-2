{{-- Gaya & perakit pembaca berkas pelamar.

     Dipakai bersama layar daftar (jendela sembul) dan layar detail, jadi
     definisinya hidup di satu tempat: disalin ke dua berkas, yang satu pasti
     tertinggal begitu yang lain disentuh.

     Inline, bukan lewat Vite: public/build masuk .gitignore dan tidak ikut
     ter-deploy. --}}
@once
<style>
    .pbk { display: flex; flex-direction: column; min-height: 0; }

    /* ===== Tab berkas ===== */
    .pbk-tab { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .pbk-tombol {
        display: inline-flex; align-items: center; gap: 7px; cursor: pointer;
        padding: 8px 14px; border-radius: 999px; font-size: .82rem; font-weight: 700;
        background: #fff; color: #475569; border: 1px solid var(--dsb-tepi, #e9edf3);
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }
    .pbk-tombol i.bi { font-size: .9rem; line-height: 1; }
    .pbk-tombol:hover:not(:disabled) { border-color: #c4b5fd; color: #6d28d9; }
    .pbk-tombol.is-aktif { background: #7c3aed; color: #fff; border-color: transparent; }
    /* Berkas yang tidak dilampirkan tetap TERLIHAT, hanya tidak bisa ditekan:
       tab yang hilang membuat admin mengira berkasnya belum dimuat. */
    .pbk-tombol.is-kosong { opacity: .5; cursor: default; }
    .pbk-tanda { font-weight: 800; color: #94a3b8; }

    /* ===== Bingkai pembaca =====
       Tingginya mengikuti layar, bukan dipatok piksel: pada laptop 13 inci
       bingkai setinggi A4 penuh mendorong tombol unduh keluar jangkauan. */
    .pbk-bingkai {
        border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px;
        background: #f1f5f9; flex: 1 1 auto; min-height: 0;
        height: min(72vh, 860px); overflow: auto; overscroll-behavior: contain;
        padding: 14px; display: flex; flex-direction: column; align-items: center; gap: 14px;
    }
    .pbk-bingkai canvas {
        display: block; max-width: 100%; height: auto;
        background: #fff; border-radius: 6px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, .12);
    }
    .pbk-muat, .pbk-gagal {
        margin: auto; text-align: center; color: var(--dsb-redup, #6b7280);
        font-size: .84rem; line-height: 1.6; max-width: 380px;
    }
    .pbk-gagal strong { display: block; color: var(--dsb-tinta, #1c1f26); margin-bottom: 3px; }

    .pbk-kaki {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 10px; margin-top: 11px;
    }
    .pbk-kaki-ket { color: var(--dsb-redup, #6b7280); font-size: .76rem; }
    .pbk-kaki-aksi { display: inline-flex; flex-wrap: wrap; gap: 8px; }

    .pbk-kabar {
        display: flex; gap: 10px; padding: 12px 14px; border-radius: 12px;
        background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
        color: var(--dsb-redup, #6b7280); font-size: .82rem; line-height: 1.6;
    }
    .pbk-kabar > i.bi { line-height: 1.5; flex-shrink: 0; }
    .pbk-kabar strong { color: var(--dsb-tinta, #1c1f26); }

    /* Jendela pembaca memakai kerangka .ts-modal milik sistem desain (lihat
       dasbor-gaya) — yang berbeda hanya lebarnya, karena halaman A4 di dalam
       kotak 560px terbaca sebesar perangko. */
    .ts-modal-card.is-pembaca { max-width: 980px; }
    .ts-modal-card.is-pembaca .pbk-bingkai { height: min(64vh, 740px); }
</style>

{{-- PDF digambar SENDIRI, tidak diserahkan ke penampil bawaan peramban.

     <iframe src="berkas.pdf"> bergantung pada penampil PDF peramban, dan
     Chrome yang disetel "Download PDFs instead of automatically opening
     them" menggantinya dengan kotak abu bertombol "Open" — isinya tidak
     pernah terbaca tanpa satu klik lagi, dan setelan itu ada di mesin tiap
     admin, bukan di tangan kita. pdf.js menggambarnya ke <canvas>, jadi
     hasilnya sama di semua peramban apa pun setelannya.

     Diletakkan di sini, bukan di dalam @script komponen: partial ini dipakai
     dua komponen, dan @script hanya berjalan untuk komponen pemiliknya. --}}
<script>
(() => {
    if (window.pbkSiapPasang) return;
    window.pbkSiapPasang = true;

    // Versi dipatok: "latest" membuat tampilan berubah sendiri saat pustakanya
    // merilis versi baru, dan rusaknya baru ketahuan dari keluhan.
    const PUSTAKA = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
    const PEKERJA = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let janjiPustaka = null;

    function muatPustaka() {
        if (window.pdfjsLib) return Promise.resolve(window.pdfjsLib);
        if (janjiPustaka) return janjiPustaka;

        janjiPustaka = new Promise((selesai, gagal) => {
            const s = document.createElement('script');
            s.src = PUSTAKA;
            s.onload = () => {
                window.pdfjsLib.GlobalWorkerOptions.workerSrc = PEKERJA;
                selesai(window.pdfjsLib);
            };
            s.onerror = () => gagal(new Error('pustaka pdf gagal dimuat'));
            document.head.appendChild(s);
        });

        return janjiPustaka;
    }

    function pesanGagal(wadah) {
        wadah.innerHTML =
            '<p class="pbk-gagal"><strong>Berkasnya tidak bisa digambar di sini.</strong>' +
            'Pakai tombol <em>Buka di tab baru</em> atau <em>Unduh</em> di bawah.</p>';
    }

    async function gambar(wadah) {
        // Ditandai SEBELUM menggambar: tanpa ini, pembaruan Livewire yang
        // datang di tengah penggambaran memicu penggambaran kedua pada wadah
        // yang sama, dan halamannya tergambar dobel.
        wadah.dataset.pbkSiap = '1';
        wadah.innerHTML = '<p class="pbk-muat">Membuka berkas…</p>';

        try {
            const pdfjs = await muatPustaka();
            const berkas = await pdfjs.getDocument({ url: wadah.dataset.pdf, withCredentials: true }).promise;

            // Wadahnya bisa sudah dilepas dari halaman saat berkasnya selesai
            // diambil (admin menutup jendela, atau berpindah tab).
            if (! wadah.isConnected) return;

            wadah.innerHTML = '';

            const lebar = Math.max(320, wadah.clientWidth - 28);
            const ketajaman = Math.min(window.devicePixelRatio || 1, 2);

            for (let n = 1; n <= berkas.numPages; n++) {
                const halaman = await berkas.getPage(n);
                if (! wadah.isConnected) return;

                const ukuranAsli = halaman.getViewport({ scale: 1 });
                const skala = lebar / ukuranAsli.width;
                const ukuran = halaman.getViewport({ scale: skala * ketajaman });

                const kanvas = document.createElement('canvas');
                kanvas.width = ukuran.width;
                kanvas.height = ukuran.height;
                kanvas.style.width = lebar + 'px';
                wadah.appendChild(kanvas);

                await halaman.render({ canvasContext: kanvas.getContext('2d'), viewport: ukuran }).promise;
            }
        } catch (e) {
            if (wadah.isConnected) pesanGagal(wadah);
        }
    }

    window.pbkGambarPdf = function () {
        document.querySelectorAll('[data-pdf]:not([data-pbk-siap])').forEach(gambar);
    };

    /*
     | Dipasang di TIGA tempat, dan ketiganya perlu.
     |
     | livewire:navigated — berpindah halaman lewat wire:navigate; listener
     | 'livewire:initialized' hanya menyala sekali dan mati di halaman
     | berikutnya.
     | morph.updated — ganti tab berkas & buka/tutup jendela: wadahnya baru,
     | halamannya tidak dimuat ulang.
     | Pemanggilan langsung — muat pertama, saat skrip ini sudah terpasang
     | sebelum kedua peristiwa di atas terjadi.
     */
    document.addEventListener('livewire:navigated', () => window.pbkGambarPdf());
    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', () => window.pbkGambarPdf());
    });
    window.pbkGambarPdf();
})();
</script>
@endonce
