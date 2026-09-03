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
        /* Tanpa ini setiap toast diregangkan selebar wadah (380px), sehingga
           pesan pendek seperti "Artikel diperbarui." menyisakan ruang kosong
           selebar separuh kartu dan tombol tutupnya terlempar jauh dari
           tulisannya. flex-end membuat lebarnya mengikuti isi, tetap rata
           kanan. */
        align-items: flex-end;
        gap: 10px;
        pointer-events: none;
        max-width: min(380px, calc(100vw - 36px));
    }

    .orcha-toast {
        pointer-events: auto;
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: .8rem;
        padding: .9rem 2.4rem .95rem 1.05rem;
        border-radius: 16px;
        /* Putih yang menghangat ke ungu merek di sudut jauh. Putih rata terasa
           seperti kotak sistem; gradien setipis ini membuatnya sekeluarga
           dengan kartu di belakangnya tanpa ikut berteriak. */
        background: linear-gradient(148deg, #fff 0%, #fcfbff 55%, #f4f1fe 100%);
        border: 1px solid rgba(124, 58, 237, .16);
        /* Dua bayangan: satu berwarna merek supaya toast terasa menyala di
           atas halaman, satu netral supaya tepinya tetap jelas di latar putih. */
        box-shadow: 0 18px 40px -14px rgba(79, 70, 229, .38),
                    0 3px 12px rgba(31, 45, 61, .10);
        overflow: hidden;
        cursor: pointer;
        animation: orchaToastMasuk .42s cubic-bezier(.18, .95, .28, 1);
    }

    .orcha-toast:hover {
        box-shadow: 0 22px 46px -14px rgba(79, 70, 229, .46),
                    0 4px 14px rgba(31, 45, 61, .12);
    }

    /* Cahaya lembut di belakang ikon. Murni hiasan, jadi tidak boleh menangkap
       tetikus — tanpa pointer-events:none ia menutupi separuh toast dan
       menelan klik yang seharusnya menutupnya. */
    .orcha-toast::after {
        content: "";
        position: absolute;
        left: -34px;
        top: -46px;
        width: 132px;
        height: 132px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(124, 58, 237, .18), transparent 68%);
        pointer-events: none;
    }

    .orcha-toast .ikon {
        position: relative;
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: .9rem;
        background: linear-gradient(135deg, var(--orc-primer), var(--orc-primer-2));
        /* Garis putih tipis di dalam tepi atas: memberi kesan cembung, sama
           seperti tombol utama di halaman ini. */
        box-shadow: 0 6px 14px -3px rgba(124, 58, 237, .5),
                    inset 0 1px 0 rgba(255, 255, 255, .45);
    }

    /* Centangnya sendiri dijadikan wadah flex.

       Cakramnya sudah menengahkan isinya, tapi yang ditengahkan adalah KOTAK
       BARIS ikonnya — bukan glifnya. Tinggi kotak baris itu mengikuti
       line-height halaman, sedangkan bootstrap-icons menggambar glifnya di
       garis dasar dengan vertical-align bawaannya sendiri. Hasilnya centang
       duduk di bawah-kanan dan menyisakan tempat kosong di atas-kiri.

       Pola yang sama sudah dipakai .stat-icon-wrapper dan centang transmisi
       di gaya.blade.php; tanpa aturan ini cakramnya rata tapi isinya tidak. */
    .orcha-toast .ikon > i {
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        vertical-align: 0;
    }
    .orcha-toast .ikon > i::before { display: block; line-height: 1; }

    .orcha-toast .isi { min-width: 0; padding-top: 1px; }
    .orcha-toast .judul { font-weight: 800; font-size: .85rem; color: var(--orc-tinta); line-height: 1.25; letter-spacing: -.01em; }
    .orcha-toast .pesan { font-size: .82rem; color: #5b6a79; line-height: 1.45; margin-top: 2px; word-break: break-word; }

    .orcha-toast .tutup {
        position: absolute;
        top: 7px;
        right: 7px;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: #a9b6c4;
        font-size: 1.05rem;
        line-height: 1;
        padding: 0;
        cursor: pointer;
        transition: background .15s ease, color .15s ease;
    }
    .orcha-toast .tutup:hover { background: rgba(124, 58, 237, .1); color: var(--orc-primer); }

    /* Batang waktu: admin bisa melihat toast-nya akan pergi, jadi tidak
       menunggu-nunggu apakah masih ada yang perlu ditekan. */
    .orcha-toast .waktu {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--orc-primer), var(--orc-primer-2), #8ab6f9);
        transform-origin: left center;
        animation: orchaToastWaktu linear forwards;
    }

    .orcha-toast.pergi { animation: orchaToastKeluar .26s ease forwards; }

    @keyframes orchaToastMasuk {
        from { opacity: 0; transform: translateX(26px) scale(.96); }
        to   { opacity: 1; transform: none; }
    }
    @keyframes orchaToastKeluar {
        to { opacity: 0; transform: translateX(26px) scale(.96); }
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

        /*
         | Lamanya toast bertahan sebelum pergi sendiri.
         |
         | Dulu 3,6 detik dan admin kerap tidak sempat menyadarinya. Sebabnya
         | letak: toast terbit di pojok kanan ATAS, sedangkan yang barusan
         | ditekan admin — tombol simpan — ada di kaki formulir. Pandangannya
         | perlu berpindah dulu melintasi layar, dan sisa waktu setelah itu
         | tidak cukup untuk membaca judul beserta pesannya.
         |
         | Angkanya untuk kasus terburuk itu, bukan untuk yang kebetulan sudah
         | menatap pojok kanan atas. Tidak dibuat lebih panjang lagi karena
         | toast yang menetap terlalu lama mulai terbaca sebagai sesuatu yang
         | menunggu ditutup — padahal justru itu yang dihindari saat memilih
         | toast alih-alih SweetAlert.
         |
         | Yang membaca pelan tidak bergantung pada angka ini: menyentuhnya
         | menahan hitungan mundur, dan batang waktu di kaki toast
         | memperlihatkan sisa waktunya.
         */
        const LAMA = 7000;

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

    /* Konfirmasi .pcek-konfirmasi TIDAK dipasang di sini lagi.

       Penangannya pindah ke layout templateindex, satu salinan untuk seluruh
       layar admin. Dua salinan yang sama membuat gayanya berbeda suatu saat —
       dan dialog yang berbeda bentuk untuk tindakan yang sama berbahayanya
       membuat orang berhenti membacanya. */

</script>
