{{-- Pemotret bawaan halaman.

     Isian berkas biasa hanya membuka pemilih berkas. Di ponsel, atribut
     capture bisa memaksa kamera — tapi itu justru menyembunyikan pilihan
     berkas, dan di laptop tidak berpengaruh sama sekali. Padahal admin di
     loket memang ingin memotret KTP saat itu juga, dari perangkat apa pun.

     Jadi kameranya dibuka sendiri lewat peramban: gambarnya diambil,
     dijadikan berkas, lalu dimasukkan ke isian yang sudah ada — sehingga
     jalur unggahnya tetap satu, sama dengan memilih berkas.

     Kamera hanya bisa dibuka pada sambungan aman (https) atau di localhost;
     itu aturan peramban, bukan pilihan kami. Kalau ditolak, tombolnya
     memberi tahu apa adanya dan pemilih berkas tetap tersedia.

     Ditulis inline sesuai aturan repo ini: public/build tidak ikut ter-deploy,
     jadi berkas skrip terpisah tidak akan sampai ke server. --}}
<div wire:ignore>
    <div id="orcha-kamera" class="orcha-kamera" hidden>
        <div class="orcha-kamera-kotak">
            <div class="orcha-kamera-kepala">
                <span>Ambil Foto</span>
                <button type="button" class="orcha-kamera-tutup" aria-label="Tutup kamera">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <video id="orcha-kamera-video" playsinline muted></video>
            <canvas id="orcha-kamera-kanvas" hidden></canvas>

            <div class="orcha-kamera-kaki">
                <span id="orcha-kamera-pesan"></span>
                <div class="d-flex gap-2">
                    <button type="button" class="orcha-btn orcha-btn-lembut orcha-kamera-tutup">Batal</button>
                    <button type="button" class="orcha-btn orcha-btn-utama" id="orcha-kamera-jepret">
                        <i class="bi bi-camera-fill"></i> Jepret
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .orcha-kamera {
            position: fixed;
            inset: 0;
            z-index: 1090;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            background: rgba(31, 45, 61, .82);
        }

        .orcha-kamera[hidden] { display: none; }

        .orcha-kamera-kotak {
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
            width: min(720px, 94vw);
            box-shadow: 0 24px 60px rgba(0, 0, 0, .45);
        }

        .orcha-kamera-kepala {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .8rem 1.1rem;
            background: var(--orc-primer-2);
            color: #fff;
            font-weight: 700;
            font-size: .9rem;
        }

        .orcha-kamera-tutup {
            border: 0;
            background: transparent;
            color: inherit;
            line-height: 1;
        }

        .orcha-kamera-kepala .orcha-kamera-tutup { font-size: .95rem; }

        #orcha-kamera-video {
            display: block;
            width: 100%;
            max-height: 62vh;
            background: var(--orc-primer-2);
            object-fit: contain;
        }

        .orcha-kamera-kaki {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            flex-wrap: wrap;
            padding: .8rem 1.1rem;
        }

        #orcha-kamera-pesan { font-size: .82rem; color: #b91c1c; }
    </style>

    <script>
        // Dipasang sekali. Halaman Livewire menggambar ulang isinya berkali-kali,
        // dan pendengar yang menempel di tombol akan ikut hilang tiap kali itu
        // terjadi — maka pendengarnya dipasang di dokumen.
        if (! window.orchaKameraSiap) {
            window.orchaKameraSiap = true;

            let aliran = null;
            let untukIsian = null;

            const kotak = () => document.getElementById('orcha-kamera');
            const video = () => document.getElementById('orcha-kamera-video');
            const pesan = () => document.getElementById('orcha-kamera-pesan');

            const tutup = () => {
                if (aliran) {
                    aliran.getTracks().forEach((jalur) => jalur.stop());
                    aliran = null;
                }
                if (kotak()) kotak().hidden = true;
                untukIsian = null;
            };

            const buka = async (idIsian) => {
                untukIsian = idIsian;
                if (! kotak()) return;

                // Dipindahkan ke <body> karena alasan yang sama dengan
                // pratinjau bukti: will-change:opacity pada #page-content
                // mengurung position:fixed ke kotak konten dan menenggelamkan
                // z-index-nya di bawah sidebar.
                if (kotak().parentElement !== document.body) {
                    document.body.appendChild(kotak());
                }

                kotak().hidden = false;
                pesan().textContent = '';

                try {
                    // facingMode 'environment' memilih kamera belakang di ponsel;
                    // di laptop diabaikan dan kamera depan yang dipakai.
                    aliran = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment', width: { ideal: 1920 } },
                        audio: false,
                    });
                    video().srcObject = aliran;
                    await video().play();
                } catch (e) {
                    pesan().textContent = 'Kamera tidak bisa dibuka: ' + (e.message || e.name)
                        + '. Gunakan pilih berkas.';
                }
            };

            document.addEventListener('click', function (e) {
                const pemicu = e.target.closest('[data-kamera-untuk]');

                if (pemicu) {
                    e.preventDefault();
                    buka(pemicu.dataset.kameraUntuk);
                    return;
                }

                if (e.target.closest('.orcha-kamera-tutup')) {
                    tutup();
                    return;
                }

                if (! e.target.closest('#orcha-kamera-jepret')) return;

                // Bingkai yang sedang tampil disalin ke kanvas, lalu dijadikan
                // berkas dan dimasukkan ke isian aslinya — supaya jalur
                // unggahnya sama persis dengan memilih berkas dari perangkat.
                const isian = document.getElementById(untukIsian);
                const kanvas = document.getElementById('orcha-kamera-kanvas');

                if (! isian || ! video().videoWidth) return;

                kanvas.width = video().videoWidth;
                kanvas.height = video().videoHeight;
                kanvas.getContext('2d').drawImage(video(), 0, 0);

                kanvas.toBlob(function (gumpalan) {
                    if (! gumpalan) return;

                    const berkas = new File([gumpalan], 'foto-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    const wadah = new DataTransfer();
                    wadah.items.add(berkas);
                    isian.files = wadah.files;
                    isian.dispatchEvent(new Event('change', { bubbles: true }));

                    tutup();
                }, 'image/jpeg', 0.9);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && kotak() && ! kotak().hidden) tutup();
            });
        }
    </script>
</div>
