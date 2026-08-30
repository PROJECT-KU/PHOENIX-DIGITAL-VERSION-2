{{--
    Pemberitahuan untuk halaman Orcha.

    Ditulis inline karena aset Vite tidak ikut ter-deploy.

    SUKSES memakai toast, GAGAL tetap memakai SweetAlert. Bedanya disengaja:
    pesan sukses cuma mengabarkan bahwa yang barusan ditekan memang berhasil —
    admin tidak perlu berhenti, apalagi menekan tombol untuk menutupnya.
    Sedangkan pesan gagal berarti pekerjaannya TIDAK tersimpan; itu harus
    menghentikan langkah dan menunggu diakui, bukan lewat begitu saja di pojok
    layar sementara admin sudah pindah ke baris berikutnya.

    Gaya dan skripnya ditaruh di SATU berkas ini supaya tidak mungkin ada
    halaman yang memuat perilakunya tanpa memuat tampilannya.
--}}

<style>
    .orcha-toast-wadah {
        position: fixed;
        top: 18px;
        right: 18px;
        z-index: 100050;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
        max-width: min(380px, calc(100vw - 36px));
    }

    .orcha-toast {
        pointer-events: auto;
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .85rem 2.2rem .85rem .9rem;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e6eef5;
        box-shadow: 0 14px 34px rgba(31, 45, 61, .16);
        overflow: hidden;
        cursor: pointer;
        animation: orchaToastMasuk .32s cubic-bezier(.22, .9, .24, 1);
    }

    /* Pita warna di tepi kiri: penanda yang terbaca sebelum satu kata pun
       dibaca, dan tetap terlihat oleh yang sulit membedakan warna karena
       ikonnya ikut membedakan. */
    .orcha-toast::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: linear-gradient(180deg, var(--orc-primer), var(--orc-primer-2));
    }

    .orcha-toast .ikon {
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: .95rem;
        background: linear-gradient(135deg, var(--orc-primer), var(--orc-primer-2));
        box-shadow: 0 4px 10px rgba(124, 58, 237, .3);
    }

    .orcha-toast .isi { min-width: 0; }
    .orcha-toast .judul { font-weight: 800; font-size: .84rem; color: var(--orc-tinta); line-height: 1.25; }
    .orcha-toast .pesan { font-size: .82rem; color: #51606f; line-height: 1.45; margin-top: 1px; word-break: break-word; }

    .orcha-toast .tutup {
        position: absolute;
        top: 6px;
        right: 8px;
        border: 0;
        background: transparent;
        color: #a9b6c4;
        font-size: 1rem;
        line-height: 1;
        padding: 4px;
        cursor: pointer;
    }
    .orcha-toast .tutup:hover { color: #51606f; }

    /* Batang waktu: admin bisa melihat toast-nya akan pergi, jadi tidak
       menunggu-nunggu apakah masih ada yang perlu ditekan. */
    .orcha-toast .waktu {
        position: absolute;
        left: 5px;
        right: 0;
        bottom: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--orc-primer), #7fd4f3);
        transform-origin: left center;
        animation: orchaToastWaktu linear forwards;
    }

    .orcha-toast.pergi { animation: orchaToastKeluar .26s ease forwards; }

    @keyframes orchaToastMasuk {
        from { opacity: 0; transform: translateX(22px) scale(.97); }
        to   { opacity: 1; transform: none; }
    }
    @keyframes orchaToastKeluar {
        to { opacity: 0; transform: translateX(22px) scale(.97); }
    }
    @keyframes orchaToastWaktu {
        from { transform: scaleX(1); }
        to   { transform: scaleX(0); }
    }

    @media (max-width: 575.98px) {
        .orcha-toast-wadah { left: 12px; right: 12px; top: 12px; max-width: none; }
    }

    /* Hormati pengguna yang mengurangi animasi: toast tetap muncul dan tetap
       pergi sendiri, hanya tanpa gerak. */
    @media (prefers-reduced-motion: reduce) {
        .orcha-toast, .orcha-toast.pergi { animation: none; }
        .orcha-toast .waktu { animation: none; transform: scaleX(0); }
    }
</style>
<script>
    /* Popup sederhana untuk halaman Orcha.

       Ditulis sendiri karena Bootstrap JS tidak pernah dimuat di aplikasi ini
       — resources/js/bootstrap.js berisi axios — dan aset Vite tidak ikut
       ter-deploy. Modal ber-data-bs-toggle akan diam saja di server. */
    window.orchaBukaLembar = window.orchaBukaLembar || function (id) {
        const lembar = document.getElementById(id);
        if (! lembar) return;

        lembar.hidden = false;
        document.body.style.overflow = 'hidden';
    };

    window.orchaTutupLembar = window.orchaTutupLembar || function (id) {
        const lembar = document.getElementById(id);
        if (! lembar) return;

        lembar.hidden = true;
        document.body.style.overflow = '';
    };

    /* Esc menutup lembar mana pun yang sedang terbuka. Tanpa ini satu-satunya
       jalan keluar adalah menemukan tombol silangnya. */
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;

        document.querySelectorAll('.orcha-lembar:not([hidden])')
            .forEach((l) => window.orchaTutupLembar(l.id));
    });

    /* Satu pintu untuk semua pemberitahuan sukses di halaman Orcha — dari
       peristiwa Livewire (hapus, ubah status) maupun dari sesi (setelah
       menyimpan lalu berpindah halaman). Karena kodenya satu, tampilan dan
       lamanya pasti sama. */
    window.orchaToast = window.orchaToast || function (pesan, judul) {
        let wadah = document.querySelector('.orcha-toast-wadah');

        if (! wadah) {
            wadah = document.createElement('div');
            wadah.className = 'orcha-toast-wadah';
            // aria-live: pembaca layar ikut mendengar kabarnya. Tanpa ini
            // pemberitahuan yang muncul-dan-pergi sendiri sama sekali tidak
            // sampai ke yang memakai pembaca layar.
            wadah.setAttribute('aria-live', 'polite');
            wadah.setAttribute('aria-atomic', 'false');
            document.body.appendChild(wadah);
        }

        const LAMA = 3600;

        const toast = document.createElement('div');
        toast.className = 'orcha-toast';
        toast.setAttribute('role', 'status');

        const ikon = document.createElement('span');
        ikon.className = 'ikon';
        ikon.innerHTML = '<i class="bi bi-check-lg"></i>';

        const isi = document.createElement('div');
        isi.className = 'isi';

        const judulEl = document.createElement('div');
        judulEl.className = 'judul';
        judulEl.textContent = judul || 'Berhasil';

        const pesanEl = document.createElement('div');
        pesanEl.className = 'pesan';
        // textContent, bukan innerHTML: pesannya sebagian memuat nama yang
        // diketik admin (judul artikel, nama kategori), dan itu tidak boleh
        // ditafsirkan sebagai markup.
        pesanEl.textContent = pesan || 'Berhasil diperbarui.';

        const tutup = document.createElement('button');
        tutup.type = 'button';
        tutup.className = 'tutup';
        tutup.setAttribute('aria-label', 'Tutup pemberitahuan');
        tutup.innerHTML = '&times;';

        const waktu = document.createElement('span');
        waktu.className = 'waktu';
        waktu.style.animationDuration = LAMA + 'ms';

        isi.append(judulEl, pesanEl);
        toast.append(ikon, isi, tutup, waktu);
        wadah.appendChild(toast);

        let jam = null;

        const pergi = () => {
            clearTimeout(jam);
            if (toast.classList.contains('pergi')) return;
            toast.classList.add('pergi');
            setTimeout(() => toast.remove(), 260);
        };

        // Ditahan selama kursor di atasnya: pesan yang panjang tidak keburu
        // hilang saat admin baru mulai membacanya.
        toast.addEventListener('mouseenter', () => {
            clearTimeout(jam);
            waktu.style.animationPlayState = 'paused';
        });
        toast.addEventListener('mouseleave', () => {
            waktu.style.animationPlayState = 'running';
            jam = setTimeout(pergi, 1200);
        });

        toast.addEventListener('click', pergi);
        jam = setTimeout(pergi, LAMA);

        return true;
    };

    /* Nama lama dipertahankan supaya pemanggil yang sudah ada — termasuk yang
       dipicu dari sesi setelah berpindah halaman — tidak perlu diubah. */
    window.orchaSukses = window.orchaSukses || function (pesan) {
        return window.orchaToast(pesan);
    };

    /* Pesan sukses yang harus menyeberangi perpindahan halaman.

       Disimpan di sessionStorage lalu ditampilkan di halaman TUJUAN. Cara lama
       menampilkannya dulu di halaman asal lalu menunggu popupnya menutup baru
       berpindah — admin menunggu tanpa alasan, dan kabarnya muncul di halaman
       yang sudah ia tinggalkan. */
    window.orchaSuksesNanti = window.orchaSuksesNanti || function (pesan) {
        try { sessionStorage.setItem('orcha-toast', pesan || ''); } catch (e) {}
    };

    (function tampilkanTitipan() {
        const tampil = () => {
            let pesan = null;
            try {
                pesan = sessionStorage.getItem('orcha-toast');
                if (pesan !== null) sessionStorage.removeItem('orcha-toast');
            } catch (e) { return; }

            if (pesan) window.orchaToast(pesan);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', tampil);
        } else {
            tampil();
        }
    })();

    document.addEventListener('livewire:initialized', () => {
        if (window.__orchaNotifBound) return;
        window.__orchaNotifBound = true;

        const glossy = {
            background: 'rgba(255,255,255,.96)',
            backdrop: 'rgba(31, 45, 61,.25)',
            customClass: { popup: 'shadow rounded-4' },
        };

        const ambilPesan = (e, bawaan) =>
            (e && (e.message ?? (Array.isArray(e) ? e[0]?.message : null))) || bawaan;

        Livewire.on('order-updated', (e) => {
            window.orchaSukses(ambilPesan(e, 'Berhasil diperbarui.'));
        });

        // Pesan sukses yang tidak menyeberang halaman.
        Livewire.on('toast-sukses', (e) => {
            window.orchaToast(ambilPesan(e, 'Berhasil disimpan.'));
        });

        /* Simpan lalu berpindah halaman.

           Kabarnya dititipkan ke halaman TUJUAN, lalu berpindah seketika.

           Cara lama menampilkan popup dulu di halaman asal dan menunggu sampai
           menutup baru berpindah — admin menunggu 2,6 detik tanpa alasan, dan
           kabarnya muncul di halaman yang justru sedang ia tinggalkan. Yang
           ingin ia lihat adalah daftarnya sudah bertambah. */
        Livewire.on('orcha-sukses-pindah', (e) => {
            const rincian = Array.isArray(e) ? e[0] : e;
            const tujuan = rincian?.url;

            window.orchaSuksesNanti(ambilPesan(e, 'Berhasil disimpan.'));

            if (tujuan) {
                window.location.href = tujuan;
            } else {
                // Tidak ke mana-mana: tampilkan langsung, jangan sampai
                // titipannya menggantung sampai halaman berikutnya.
                window.orchaToast(ambilPesan(e, 'Berhasil disimpan.'));
                try { sessionStorage.removeItem('orcha-toast'); } catch (err) {}
            }
        });

        /* Gagal TETAP SweetAlert, bukan toast.

           Pesan gagal berarti pekerjaannya tidak tersimpan. Itu harus
           menghentikan langkah dan menunggu diakui — bukan lewat di pojok layar
           sementara admin sudah pindah ke baris berikutnya dan mengira
           perubahannya sudah masuk. */
        Livewire.on('toast-error', (e) => {
            Swal.fire({
                title: 'Gagal',
                text: ambilPesan(e, 'Perubahan tidak tersimpan.'),
                icon: 'error',
                confirmButtonText: 'Mengerti',
                ...glossy,
            });
        });
    });

    /* Isian uang bertitik SAMBIL diketik.
       Server tetap yang memegang angkanya (wire:model.blur memformat ulang
       dengan aturan yang sama), ini hanya supaya admin tidak menunggu pindah
       kolom dulu untuk melihat "1.430.000". Ditulis inline karena berkas Vite
       tidak ikut ter-deploy. */
    if (!window.__orchaUangBound) {
        window.__orchaUangBound = true;

        const bertitik = (angka) => angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        document.addEventListener('input', (e) => {
            const el = e.target;
            if (!el.classList || !el.classList.contains('orcha-uang')) return;

            const angka = el.value.replace(/\D/g, '');
            const baru = angka === '' ? '' : bertitik(angka);
            if (baru === el.value) return;

            // Jaga posisi kursor: hitung berapa digit sebelum kursor, lalu
            // taruh kursor setelah digit ke-sekian pada teks yang baru.
            const digitSebelumKursor = el.value.slice(0, el.selectionStart).replace(/\D/g, '').length;
            el.value = baru;

            let posisi = 0, terhitung = 0;
            while (posisi < baru.length && terhitung < digitSebelumKursor) {
                if (/\d/.test(baru[posisi])) terhitung++;
                posisi++;
            }
            el.setSelectionRange(posisi, posisi);
        });
    }

    /* Konfirmasi seragam untuk tombol .pcek-konfirmasi — pola yang sama dengan
       halaman lemon lain. Penanda global dipakai bersama supaya tidak terpasang
       dua kali kalau halaman lain sudah memasangnya. */
    if (!window.__pcekKonfirmasiBound) {
        window.__pcekKonfirmasiBound = true;

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.pcek-konfirmasi');
            if (!btn) return;
            e.preventDefault();

            const method = btn.dataset.action;
            const arg = btn.dataset.arg || null;
            const component = btn.closest('[wire\\:id]');
            if (!method || !component) return;

            const jalankan = () => {
                const lw = Livewire.find(component.getAttribute('wire:id'));
                if (lw) arg ? lw.call(method, arg) : lw.call(method);
            };

            if (typeof Swal === 'undefined') { jalankan(); return; }

            Swal.fire({
                title: btn.dataset.title || 'Anda yakin?',
                text: btn.dataset.text || '',
                icon: btn.dataset.icon || 'question',
                showCancelButton: true,
                confirmButtonText: btn.dataset.confirm || 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                background: 'rgba(255,255,255,.96)',
                backdrop: 'rgba(31, 45, 61,.25)',
                // Tombolnya digaya kelas .btn-glossy-* milik lemon, jadi gaya
                // bawaan SweetAlert dimatikan supaya tidak bertumpuk.
                buttonsStyling: false,
                customClass: {
                    popup: 'shadow rounded-4',
                    confirmButton: 'btn-glossy-confirm',
                    cancelButton: 'btn-glossy-cancel',
                },
            }).then((r) => { if (r.isConfirmed) jalankan(); });
        });
    }
</script>
