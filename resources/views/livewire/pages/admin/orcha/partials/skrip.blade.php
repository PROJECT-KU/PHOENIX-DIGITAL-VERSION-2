{{--
    Pemberitahuan untuk halaman Orcha.

    Ditulis inline karena aset Vite tidak ikut ter-deploy.

    SEMUANYA memakai SweetAlert glossy milik lemon — sukses maupun gagal.

    Dulu sukses memakai toast sendiri di pojok kanan atas. Dua bentuk
    pemberitahuan untuk satu hal yang sama membuat layar Orcha terasa bukan
    bagian dari lemon, dan pada layar yang penangan lamanya masih hidup
    keduanya bahkan terbit BERBARENGAN: Detail Pesanan memasang
    Livewire.on('order-updated') sekali lalu sengaja membiarkannya bertahan
    lintas wire:navigate, sehingga satu unggahan di Galeri menerbitkan satu
    dialog DAN satu toast sekaligus.

    Bedanya tinggal satu dan tetap disengaja: sukses menutup sendiri,
    sedangkan GAGAL menunggu ditekan "Mengerti". Pesan gagal berarti
    pekerjaannya TIDAK tersimpan; itu harus menghentikan langkah, bukan lewat
    begitu saja sementara admin sudah pindah ke baris berikutnya.
--}}

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
        const teks = pesan || 'Berhasil diperbarui.';

        /* Memakai helper lemon (window.fireGlossySwal dari layouts/app), bukan
           Swal.fire sendiri: kalau rupa dialog lemon suatu saat disetel ulang,
           layar Orcha ikut berubah tanpa ada yang perlu ingat menyentuh berkas
           ini. Dua salinan gaya untuk satu dialog pasti berbeda suatu hari. */
        if (typeof window.fireGlossySwal === 'function') {
            window.fireGlossySwal(judul || 'Berhasil', teks, 'success');

            return true;
        }

        /* Jaring pengaman. Helper lemon hidup di layout, jadi secara teori
           selalu ada — tetapi kabar sukses yang hilang tanpa jejak jauh lebih
           mahal daripada beberapa baris cadangan ini. */
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: judul || 'Berhasil',
                text: teks,
                icon: 'success',
                timer: 2500,
                showConfirmButton: false,
            });
        }

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
        try { sessionStorage.setItem('orcha-kabar', pesan || ''); } catch (e) {}
    };

    (function tampilkanTitipan() {
        const tampil = () => {
            let pesan = null;
            try {
                pesan = sessionStorage.getItem('orcha-kabar');
                if (pesan !== null) sessionStorage.removeItem('orcha-kabar');
            } catch (e) { return; }

            if (pesan) window.orchaToast(pesan);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', tampil);
        } else {
            tampil();
        }
    })();

    /* Penangan peristiwa Livewire untuk halaman Orcha.

       DIPASANG LANGSUNG kalau Livewire sudah hidup, dan hanya menunggu
       `livewire:initialized` kalau belum. Perbedaan itu bukan kehati-hatian
       berlebih — tanpanya seluruh pemberitahuan Orcha MATI pada jalur
       pemakaian yang paling biasa:

         1. Admin membuka lemon dan mendarat di dasbor toko. Halaman itu
            tidak memuat partial ini, jadi belum ada penangan Orcha.
         2. Livewire menyala dan mengirim `livewire:initialized` SEKALI —
            dikirim di dalam start2(), dan tidak pernah diulang.
         3. Admin menekan menu Orcha di bilah samping. Seluruh menu memakai
            wire:navigate, jadi body ditukar TANPA muat ulang halaman.
            Livewire menjalankan ulang tiap <script> di body baru
            (prepNewBodyScriptTagsToRun), jadi berkas ini ikut jalan lagi —
            lalu mendaftar menunggu `livewire:initialized` yang SUDAH LEWAT
            dan tidak akan datang lagi.

       Akibatnya: tombol Simpan di Vendor, Master Harga, dan RAB mengirim
       `orcha-sukses-pindah` ke ruang kosong. Tidak ada toast, tidak ada
       perpindahan, tidak ada galat — dan datanya SUDAH tersimpan di Orcha,
       jadi gejalanya menipu dua arah.

       Yang membuatnya sulit tertangkap: menekan F5 di halaman Orcha
       memperbaikinya sendiri, karena itu muat penuh. Jadi pengujian manual
       dan potret layar — yang selalu memuat halamannya langsung — selalu
       melihatnya bekerja. */
    const pasangPenangan = () => {
        if (window.__orchaNotifBound) return;
        window.__orchaNotifBound = true;

        /* Nilai yang SAMA dengan window.fireGlossySwal di layouts/app —
           hanya tombolnya yang ditambahkan, karena dialog gagal wajib
           ditekan, tidak boleh menutup sendiri. */
        const glossy = {
            background: 'rgba(255, 255, 255, 0.9)',
            backdrop: 'rgba(139, 92, 246, 0.15)',
            customClass: {
                popup: 'swal-glossy-popup rounded-4 shadow-lg border-0',
                confirmButton: 'btn-glossy-confirm',
                title: 'fw-bold',
            },
            buttonsStyling: false,
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
                try { sessionStorage.removeItem('orcha-kabar'); } catch (err) {}
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
    };

    if (window.Livewire) {
        pasangPenangan();
    } else {
        document.addEventListener('livewire:initialized', pasangPenangan);
    }

    /* Isian uang bertitik SAMBIL diketik.
       Server tetap yang memegang angkanya (wire:model.blur memformat ulang
       dengan aturan yang sama), ini hanya supaya admin tidak menunggu pindah
       kolom dulu untuk melihat "1.430.000". Ditulis inline karena berkas Vite
       tidak ikut ter-deploy. */
    if (!window.__orchaUangBound) {
        window.__orchaUangBound = true;

        /* Diekspor supaya layar lain memakai pemformat yang SAMA.

           Master Harga mencerminkan angka yang sedang diketik ke kotak
           "Hari kerja"; menyalin regexnya ke sana berarti dua pemformat untuk
           satu aturan, dan yang satu pasti tertinggal saat yang lain disentuh
           — lalu dua angka yang seharusnya kembar tampil berbeda di satu
           layar yang sama. */
        const bertitik = (angka) => angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        window.orchaBertitik = bertitik;

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
