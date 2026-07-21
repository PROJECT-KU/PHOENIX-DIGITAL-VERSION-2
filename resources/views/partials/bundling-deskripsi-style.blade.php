{{-- Gaya deskripsi bundling yang rapi (intro + daftar bercentang).
     Ditaruh inline di blade (bukan public-custom-styles.css) supaya ikut
     git pull tanpa perlu rsync public/build. Include SEKALI per halaman. --}}
<style>
    .bdesk { text-align: center; margin-bottom: 20px; }
    .bdesk-p { color: var(--ph-muted, #6b7280); font-size: .92rem; line-height: 1.65; margin: 0 auto 10px; max-width: 46ch; }
    .bdesk-p:first-child { color: var(--ph-ink, #23272f); font-weight: 600; }
    .bdesk-p:last-of-type { margin-bottom: 14px; }
    .bdesk-list { list-style: none; margin: 0 auto; padding: 0; display: grid; gap: 8px; width: fit-content; max-width: 100%; }
    .bdesk-list li { display: flex; align-items: flex-start; gap: 9px; font-size: .9rem; line-height: 1.5; color: var(--ph-ink, #23272f); text-align: left; }
    .bdesk-list li i { color: #16a34a; font-size: 1rem; flex: 0 0 auto; margin-top: 1px;
        transform-origin: center; animation: bdeskPulse 2.4s ease-in-out infinite;
        animation-delay: calc(var(--i, 0) * 200ms); }
    @keyframes bdeskPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.16); } }

    /* Catatan (📌/🎯/⚡): blok terpisah di bawah daftar centang. */
    .bdesk-notes { margin: 14px auto 0; max-width: 46ch; display: grid; gap: 8px;
        padding-top: 12px; border-top: 1px dashed var(--ph-line, #ecdcc7); }
    .bdesk-note { display: flex; gap: 9px; align-items: flex-start; text-align: left;
        font-size: .88rem; line-height: 1.55; color: var(--ph-muted, #6b7280); margin: 0; }
    .bdesk-note-ic { flex: 0 0 auto; font-size: 1rem; line-height: 1.4; }

    /* Teaser pada kartu homepage: intro singkat, dipotong 2 baris. */
    .bdesk-teaser { display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    @media (prefers-reduced-motion: reduce) { .bdesk-list li i { animation: none; } }

    /* ===== Header kartu bundling otomatis (seragam, gantikan banner upload) ===== */
    .bh { background: linear-gradient(150deg, #fff4e2 0%, #ffe9cf 55%, #ffe0bd 100%);
        padding: 18px 20px 22px; text-align: center; }
    .bh-top { display: flex; align-items: center; justify-content: space-between; min-height: 24px; margin-bottom: 14px; }
    .bh-badge { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #fba919, #f26522);
        color: #fff; font-weight: 700; font-size: .72rem; padding: 5px 11px; border-radius: 999px;
        box-shadow: 0 6px 16px rgba(242, 101, 34, .3); }
    .bh-num { font-size: .72rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #c2410c; }
    .bh-pills { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px; margin-bottom: 14px; }
    .bh-plus { color: #c2410c; font-weight: 800; font-size: 1.05rem; }
    .bh-pill { display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #f3ddc0;
        border-radius: 999px; padding: 5px 14px 5px 5px; font-weight: 700; font-size: .85rem; color: #23272f;
        box-shadow: 0 3px 10px rgba(180, 90, 30, .08); }
    .bh-av { width: 26px; height: 26px; border-radius: 50%; display: grid; place-items: center; color: #fff;
        font-size: .8rem; font-weight: 800; flex: 0 0 auto; }
    .bh-name { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.5rem; color: #23272f;
        line-height: 1.15; text-wrap: balance; margin: 0; }
    @media (max-width: 575.98px) { .bh-name { font-size: 1.25rem; } .bh-pill { font-size: .8rem; } }

    /* Tombol pembungkus header di kartu homepage (klik → detail). */
    .bd-card-head-btn { display: block; width: 100%; border: 0; padding: 0; background: none; cursor: pointer; text-align: center; }
    .bd-card-head-btn .bh { transition: filter .2s ease; }
    .bd-card:hover .bd-card-head-btn .bh { filter: brightness(1.02); }
    /* Di kartu halaman bundling, header menyatu dgn sudut kartu. */
    .bdl-card .bh { border-radius: 16px; margin: -.4rem -.2rem 1rem; }
</style>
