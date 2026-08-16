{{-- Menyalin pesan WhatsApp ke papan tempel saat tombolnya ditekan.

     Tautan wa.me yang kami hasilkan sudah benar — terbukti berisi UTF-8 yang
     sah (👋 terkirim sebagai %F0%9F%91%8B). Tapi yang membaca tautan itu bukan
     kami: peramban menyerahkannya ke aplikasi WhatsApp lewat skema whatsapp://,
     dan sebagian versi aplikasi salah membaca sandi persennya sehingga tiap
     emoji berubah jadi "�" sebelum sempat tampil.

     Itu di luar jangkauan kode ini. Yang bisa kami kerjakan adalah menyediakan
     jalan yang tidak melewati penerjemahan itu sama sekali: teksnya disalin apa
     adanya ke papan tempel. Tempel (⌘V / Ctrl+V) selalu memindahkan karakter
     yang sama persis — tidak ada sandi yang perlu dibaca ulang.

     Jadi admin cukup menempel bila emojinya berantakan, tanpa mengetik ulang.

     Ditulis inline sesuai aturan repo: public/build tidak ikut ter-deploy. --}}
<div wire:ignore>
    <div id="orcha-salin-kabar" class="orcha-salin-kabar" hidden>
        <i class="bi bi-clipboard-check"></i>
        <span>Pesan disalin. Bila emojinya berantakan di WhatsApp, tempel saja (⌘V).</span>
    </div>

    <style>
        .orcha-salin-kabar {
            position: fixed;
            left: 50%;
            bottom: 1.5rem;
            transform: translateX(-50%);
            z-index: 1095;
            display: flex;
            align-items: center;
            gap: .55rem;
            max-width: min(520px, 92vw);
            padding: .7rem 1.1rem;
            border-radius: .8rem;
            background: #0f2d4a;
            color: #fff;
            font-size: .85rem;
            box-shadow: 0 12px 30px rgba(15, 45, 74, .35);
        }

        .orcha-salin-kabar[hidden] { display: none; }

        .orcha-salin-kabar > i { line-height: 1; font-size: 1rem; }
    </style>

    <script>
        // Perakit emoji.
        //
        // Server mengirim penanda "[[E:1F44B]]", bukan emojinya langsung, lalu
        // emojinya dirakit di sini dengan String.fromCodePoint. Dengan begitu
        // emoji tidak pernah ikut melewati respons server — dan justru di
        // perjalanan itulah ia berubah jadi tanda tanya.
        //
        // Polanya menyalin halaman detail order, yang sudah lama memakai cara
        // ini dengan alasan yang sama tertulis di komentarnya.
        window.orchaRakitEmoji = function (teks) {
            return (teks || '').replace(/\[\[E:([0-9A-F]+)\]\]/g,
                (_, kode) => String.fromCodePoint(parseInt(kode, 16)));
        };

        // Pratayang di lembar cek pembayaran ikut dirakit, supaya yang dilihat
        // admin sama persis dengan yang akan dikirim — termasuk emojinya.
        const orchaGambarPratayang = () => {
            document.querySelectorAll('[data-wa-pratayang]').forEach(function (kotak) {
                kotak.textContent = window.orchaRakitEmoji(kotak.dataset.waPratayang);
            });
        };

        document.addEventListener('livewire:navigated', orchaGambarPratayang);
        document.addEventListener('DOMContentLoaded', orchaGambarPratayang);
        // Livewire menggambar ulang lembarnya tiap kali isian berubah.
        document.addEventListener('livewire:update', orchaGambarPratayang);
        orchaGambarPratayang();

        // Dipasang sekali; pendengarnya di dokumen supaya tidak ikut hilang
        // tiap kali Livewire menggambar ulang daftarnya.
        if (! window.orchaSalinSiap) {
            window.orchaSalinSiap = true;

            let jedaKabar = null;

            document.addEventListener('click', function (e) {
                const tautan = e.target.closest('[data-wa-pesan]');
                if (! tautan) return;

                const pesan = window.orchaRakitEmoji(tautan.dataset.waPesan);
                if (! pesan) return;

                // Tautannya disusun ulang di sini dengan pesan beremoji.
                // Yang tertulis di href hanyalah cadangan tanpa emoji, untuk
                // keadaan ketika skrip ini tidak sempat jalan.
                if (tautan.tagName === 'A' && tautan.href.includes('api.whatsapp.com')) {
                    const nomor = new URL(tautan.href).searchParams.get('phone') || '';
                    tautan.href = 'https://api.whatsapp.com/send?phone=' + nomor
                        + '&text=' + encodeURIComponent(pesan);
                }

                // Tautannya tetap dibiarkan terbuka seperti biasa; penyalinan
                // ini tambahan, bukan pengganti.
                const kabari = () => {
                    const kotak = document.getElementById('orcha-salin-kabar');
                    if (! kotak) return;

                    kotak.hidden = false;
                    clearTimeout(jedaKabar);
                    jedaKabar = setTimeout(() => { kotak.hidden = true; }, 6000);
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(pesan).then(kabari).catch(() => {});
                    return;
                }

                // Halaman yang dibuka lewat http biasa tidak diberi akses
                // papan tempel oleh peramban. Cara lama masih jalan di sana.
                const bak = document.createElement('textarea');
                bak.value = pesan;
                bak.style.cssText = 'position:fixed;top:-1000px';
                document.body.appendChild(bak);
                bak.select();
                try { document.execCommand('copy'); kabari(); } catch (_) {}
                document.body.removeChild(bak);
            });
        }
    </script>
</div>
