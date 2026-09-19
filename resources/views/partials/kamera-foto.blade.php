@php
    // Dipakai lewat @include, jadi kelasnya datang sebagai variabel biasa —
    // $attributes hanya ada untuk komponen, dan dulu membuat tombol ini
    // memakai kelas admin yang tidak dikenal halaman publik (tampil polos).
    $kmTarget = $target ?? 'foto';
    $kmKelas = $kelas ?? 'tm-btn';
    $kmTeks = $teks ?? 'Ambil Foto';
@endphp

{{-- Pengambil foto lewat kamera, dipakai form testimoni admin & pelanggan.

     Tidak memakai <input capture> saja: atribut itu hanya berpengaruh di
     ponsel, sedangkan admin memakai laptop. Di sini kameranya dibuka lewat
     getUserMedia, hasilnya jadi File dan dikirim ke properti Livewire yang
     sama dengan unggahan biasa — jadi validasi & penyimpanannya satu jalur.

     Izin kamera hanya diminta SAAT tombol ditekan, tidak saat halaman dimuat.

     Seluruh elemen dibangun lewat createElement, TANPA string HTML: penanda
     morph Livewire menghitung tag di dalam string JavaScript sebagai elemen
     akar, dan komponennya langsung gagal dirender. --}}
@once
    <style>
        .km-lapis { position: fixed; inset: 0; z-index: 2147483000; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(15, 23, 42, .75); }
        .km-kotak { width: min(520px, 100%); background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 30px 60px -30px rgba(15, 23, 42, .8); }
        .km-kepala { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-bottom: 1px solid #eef2f7; font-weight: 800; color: #1c1f26; }
        .km-kepala .km-tutup { margin-left: auto; width: 32px; height: 32px; border: 0; border-radius: 9px; background: #f1f5f9; color: #475569; font-size: 1.1rem; line-height: 1; cursor: pointer; }
        .km-video { display: block; width: 100%; max-height: 60vh; background: #0f172a; object-fit: cover; }
        .km-kaki { display: flex; gap: 10px; justify-content: center; padding: 14px; }
        .km-tombol { display: inline-flex; align-items: center; gap: 8px; padding: 11px 18px; border-radius: 12px; border: 1px solid #e5e7eb; background: #fff; font-weight: 700; font-size: .88rem; color: #334155; cursor: pointer; }
        .km-tombol.is-utama { background: #f26522; border-color: #f26522; color: #fff; }
        .km-galat { padding: 16px; margin: 0; color: #b91c1c; font-size: .86rem; line-height: 1.6; }
    </style>

    <script>
        // Dipasang SEKALI di dokumen: wire:navigate mengganti <body>.
        if (!window.__kameraFotoTerpasang) {
            window.__kameraFotoTerpasang = true;

            const buat = (tag, kelas, teks) => {
                const el = document.createElement(tag);
                if (kelas) el.className = kelas;
                if (teks) el.textContent = teks;
                return el;
            };

            window.kameraFoto = {
                async buka(pemicu) {
                    const target = pemicu.dataset.target || 'foto';
                    const komponenEl = pemicu.closest('[wire\\:id]');
                    if (!komponenEl) return;
                    const komponen = Livewire.find(komponenEl.getAttribute('wire:id'));

                    const lapis = buat('div', 'km-lapis');
                    const kotak = buat('div', 'km-kotak');
                    const kepala = buat('div', 'km-kepala', 'Ambil Foto');
                    const silang = buat('button', 'km-tutup', '×');
                    silang.type = 'button';
                    silang.setAttribute('aria-label', 'Tutup');
                    kepala.appendChild(silang);

                    const video = buat('video', 'km-video');
                    video.autoplay = true;
                    video.muted = true;
                    video.setAttribute('playsinline', '');

                    const kaki = buat('div', 'km-kaki');
                    const batal = buat('button', 'km-tombol', 'Batal');
                    batal.type = 'button';
                    const jepret = buat('button', 'km-tombol is-utama', 'Jepret');
                    jepret.type = 'button';
                    kaki.append(batal, jepret);

                    kotak.append(kepala, video, kaki);
                    lapis.appendChild(kotak);
                    document.body.appendChild(lapis);

                    let aliran = null;
                    const tutup = () => {
                        if (aliran) aliran.getTracks().forEach((t) => t.stop());
                        lapis.remove();
                        document.removeEventListener('keydown', esc);
                    };
                    const esc = (e) => { if (e.key === 'Escape') tutup(); };
                    document.addEventListener('keydown', esc);
                    silang.addEventListener('click', tutup);
                    batal.addEventListener('click', tutup);
                    lapis.addEventListener('click', (e) => { if (e.target === lapis) tutup(); });

                    try {
                        aliran = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'user', width: { ideal: 1280 } },
                            audio: false,
                        });
                        video.srcObject = aliran;
                    } catch (err) {
                        // Izin ditolak / tidak ada kamera: katakan apa adanya, dan
                        // biarkan unggah berkas biasa tetap bisa dipakai.
                        video.remove();
                        jepret.remove();
                        kepala.firstChild.textContent = 'Kamera tidak bisa dibuka';
                        const galat = buat('p', 'km-galat', 'Pastikan izin kamera diperbolehkan di peramban ini. Anda tetap bisa memakai tombol unggah foto biasa.');
                        kotak.insertBefore(galat, kaki);
                        batal.textContent = 'Tutup';

                        return;
                    }

                    jepret.addEventListener('click', () => {
                        const kanvas = document.createElement('canvas');
                        kanvas.width = video.videoWidth || 1280;
                        kanvas.height = video.videoHeight || 720;
                        kanvas.getContext('2d').drawImage(video, 0, 0, kanvas.width, kanvas.height);
                        kanvas.toBlob((blob) => {
                            if (!blob) { tutup(); return; }
                            const berkas = new File([blob], 'kamera-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                            // Lewat jalur unggah Livewire yang sama dengan <input type=file>.
                            komponen.upload(target, berkas, () => {}, () => {}, () => {});
                            // Formulir yang punya pratinjau sisi peramban ikut diberi tahu.
                            window.dispatchEvent(new CustomEvent('foto-kamera', { detail: URL.createObjectURL(blob) }));
                            tutup();
                        }, 'image/jpeg', 0.9);
                    });
                },
            };
        }
    </script>
@endonce

<button type="button" class="{{ $kmKelas }}" data-target="{{ $kmTarget }}"
    onclick="window.kameraFoto.buka(this)">
    <i class="bi bi-camera"></i><span>{{ $kmTeks }}</span>
</button>
