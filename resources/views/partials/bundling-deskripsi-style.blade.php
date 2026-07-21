{{-- Gaya deskripsi bundling yang rapi (intro + daftar bercentang).
     Ditaruh inline di blade (bukan public-custom-styles.css) supaya ikut
     git pull tanpa perlu rsync public/build. Include SEKALI per halaman. --}}
<style>
    .bdesk { text-align: center; }
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
</style>
