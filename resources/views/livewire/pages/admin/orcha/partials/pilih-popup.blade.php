{{--
    Pemilih berbentuk jendela, seragam untuk seluruh layar Orcha.

    Kotak pilih bawaan — dan Select2 sekalipun — hanya bisa menampilkan
    SATU baris teks per pilihan, dan tidak punya tempat untuk perbuatan.
    Yang dibutuhkan borang Master Harga tiga-tiganya sekaligus: nama,
    keterangan di bawahnya (daerah, kontak vendor), penanda "sudah punya
    tiket", dan — khusus destinasi — tombol membuang nama dari katalog.
    Daftar yang dibuka di jendela punya ruang untuk itu semua.

    CARA PAKAI — markahnya satu bentuk saja:

        <div class="op-pilih" wire:key="...">
            <button type="button" class="op-pilih-tombol"
                onclick="pilihPopup(this)"
                data-ke="isian.destinasi"
                data-judul="Pilih Destinasi"
                data-cari="Cari nama destinasi…"
                data-kosong="Tidak terikat destinasi"
                data-hapus-aksi="hapusDestinasiKatalog"
                data-daftar="{{ json_encode($baris) }}">
                …
            </button>
        </div>

    Tiap baris di data-daftar: { nilai, label, ket, lencana, hapus }.
    `hapus` berisi id yang dikirim ke data-hapus-aksi, atau null bila baris
    itu memang tidak boleh dihapus.

    EMPAT hal yang masing-masing pernah jadi sumber bug:

    1. Daftarnya dibaca ULANG dari atribut tiap kali jendela dibuka, bukan
       disimpan di memori saat halaman digambar. Sesudah satu nama dihapus,
       Livewire menggambar ulang tombolnya dengan daftar yang baru; yang
       menyalin daftarnya sekali akan terus menampilkan nama yang sudah
       tidak ada.

    2. Pembungkusnya TIDAK boleh wire:ignore. Berbeda dari Select2, jendela
       ini tidak membangun apa pun di sebelah tombolnya — yang digambar
       Livewire cuma tombol biasa, dan justru harus ikut tergambar ulang
       supaya nilainya mengikuti server.

    3. Nilainya didorong lewat $wire.set(), dan nama propertinya boleh
       bersarang ("isian.destinasi"). Tombolnya sendiri tidak menyimpan
       apa pun.

    4. Jendelanya menempel di <body> seperti seluruh SweetAlert, jadi
       .dsb-kartu yang ber-overflow:hidden tidak memotongnya. Inilah sebab
       pemilih ini tidak memakai dropdown yang digantung di dalam kartu.
--}}
@once
    <script>
        (function () {
        /*
         | Tiap tanda tutup di dalam skrip ini ditulis dengan garis miring
         | yang diloloskan: kurung-sudut, garis-miring-balik, garis miring.
         |
         | Bukan gaya — keharusan. Pengurai HTML milik libxml (yang dipakai
         | DOMDocument, dan lewatnya pemeriksa akar-tunggal Livewire)
         | MENGAKHIRI isi skrip pada tanda tutup apa pun, bukan hanya pada
         | penutup skripnya sendiri. Satu tanda tutup div yang ditulis polos
         | di dalam string membuat skripnya tertutup lebih awal: sisanya
         | diurai sebagai markah, elemen akarnya jadi lebih dari satu, dan
         | seluruh layar mati dengan MultipleRootElementsDetectedException
         | yang menunjuk komponennya — bukan baris yang bersalah.
         |
         | Bentuk yang diloloskan sama artinya bagi JavaScript dan tidak
         | terlihat oleh pengurai HTML. Berlaku untuk KOMENTAR juga, termasuk
         | komentar ini: yang pertama kali ditulis di sini memuat contoh
         | mentahnya, dan dengan itu menghidupkan kembali bug yang sedang
         | dijelaskannya. Dijaga PemuatPilihPopupTest.
         */
            /** Mengubah teks jadi aman dimasukkan sebagai HTML. */
            function aman(teks) {
                return String(teks ?? '').replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function barisHtml(b, terpilih) {
                var dipilih = String(b.nilai ?? '') === String(terpilih ?? '');

                return '<div class="op-baris' + (dipilih ? ' is-terpilih' : '') + '"'
                    + ' data-nilai="' + aman(b.nilai ?? '') + '"'
                    + ' data-cari="' + aman(((b.label ?? '') + ' ' + (b.ket ?? '')).toLowerCase()) + '">'
                    + '<button type="button" class="op-baris-pilih">'
                    + '<span class="op-baris-teks">'
                    + '<span class="op-baris-label">' + aman(b.label ?? b.nilai) + '<\/span>'
                    + (b.ket ? '<span class="op-baris-ket">' + aman(b.ket) + '<\/span>' : '')
                    + '<\/span>'
                    + (b.lencana ? '<span class="op-baris-lencana">' + aman(b.lencana) + '<\/span>' : '')
                    + (dipilih ? '<i class="bi bi-check-lg op-baris-centang"><\/i>' : '')
                    + '<\/button>'
                    + (b.hapus ? '<button type="button" class="op-baris-hapus" data-hapus="' + aman(b.hapus)
                        + '" title="Buang dari daftar"><i class="bi bi-trash3"><\/i><\/button>' : '')
                    + '<\/div>';
            }

            /**
             * Membuka jendela pemilih untuk satu tombol.
             *
             * @param {HTMLElement} tombol elemen yang membawa seluruh data-*
             */
            window.pilihPopup = function (tombol) {
                if (typeof Swal === 'undefined') {
                    return;
                }

                var d = tombol.dataset;
                var daftar = [];

                try {
                    daftar = JSON.parse(d.daftar || '[]');
                } catch (e) {
                    daftar = [];
                }

                var terpilih = d.terpilih || '';
                var komponen = tombol.closest('[wire\\:id]');
                var wire = komponen ? window.Livewire.find(komponen.getAttribute('wire:id')) : null;

                var isi = daftar.map(function (b) { return barisHtml(b, terpilih); }).join('');

                var kosong = d.kosong
                    ? barisHtml({ nilai: '', label: d.kosong }, terpilih)
                    : '';

                Swal.fire({
                    title: d.judul || 'Pilih',
                    html: '<div class="op-popup">'
                        + (d.keterangan ? '<p class="op-popup-ket">' + aman(d.keterangan) + '<\/p>' : '')
                        + '<div class="op-cari-bungkus">'
                        + '<i class="bi bi-search"><\/i>'
                        + '<input type="text" class="op-cari" placeholder="' + aman(d.cari || 'Cari…') + '">'
                        + '<\/div>'
                        + '<div class="op-daftar">' + kosong + isi + '<\/div>'
                        + '<p class="op-kosong-pesan" hidden>Tidak ada yang cocok.<\/p>'
                        + '<\/div>',
                    width: 520,
                    showConfirmButton: false,
                    showCloseButton: true,
                    customClass: { popup: 'op-swal' },
                    didOpen: function (modal) {
                        var cari = modal.querySelector('.op-cari');
                        var baris = Array.prototype.slice.call(modal.querySelectorAll('.op-baris'));
                        var pesanKosong = modal.querySelector('.op-kosong-pesan');

                        /* Kotak carinya tidak difokuskan di layar sentuh: papan
                           ketik yang terbuka sendiri menutupi daftarnya, dan yang
                           dicari justru hilang dari pandangan. */
                        if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                            cari.focus();
                        }

                        function saring() {
                            var kunci = cari.value.trim().toLowerCase();
                            var terlihat = 0;

                            baris.forEach(function (el) {
                                var cocok = kunci === '' || (el.dataset.cari || '').indexOf(kunci) !== -1;
                                el.hidden = ! cocok;
                                if (cocok) { terlihat++; }
                            });

                            pesanKosong.hidden = terlihat > 0;
                        }

                        cari.addEventListener('input', saring);

                        // Enter memilih baris terlihat yang pertama: mengetik tiga
                        // huruf lalu harus meraih tetikus adalah dua perbuatan
                        // untuk satu maksud.
                        cari.addEventListener('keydown', function (e) {
                            if (e.key !== 'Enter') { return; }
                            e.preventDefault();
                            var pertama = baris.find(function (el) { return ! el.hidden; });
                            if (pertama) { pertama.querySelector('.op-baris-pilih').click(); }
                        });

                        modal.addEventListener('click', function (e) {
                            var pilih = e.target.closest('.op-baris-pilih');

                            if (pilih && wire) {
                                wire.set(d.ke, pilih.closest('.op-baris').dataset.nilai);
                                Swal.close();

                                return;
                            }

                            var hapus = e.target.closest('.op-baris-hapus');

                            if (! hapus || ! wire || ! d.hapusAksi) {
                                return;
                            }

                            var baris0 = hapus.closest('.op-baris');
                            var nama = baris0.querySelector('.op-baris-label').textContent;

                            Swal.fire({
                                title: 'Buang "' + nama + '" dari daftar?',
                                html: 'Namanya hilang dari kotak pilih. Baris harga, RAB, dan itinerary '
                                    + 'yang memakainya <b>tidak ikut terhapus<\/b> — dan kalau masih dipakai '
                                    + 'salah satunya, permintaan ini akan ditolak.'
                                    + '<br><br>Bisa dikembalikan: tambahkan lagi dengan nama yang sama.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Buang',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#dc2626',
                                reverseButtons: true,
                            }).then(function (hasil) {
                                if (! hasil.isConfirmed) {
                                    // Dibuka lagi supaya pembatalan tidak berarti
                                    // mencari ulang dari awal.
                                    window.pilihPopup(tombol);

                                    return;
                                }

                                wire.call(d.hapusAksi, Number(hapus.dataset.hapus));
                            });
                        });
                    },
                });
            };
        })();
    </script>

    <style>
        .op-swal { border-radius: 20px; padding-bottom: 8px; }
        .op-swal .swal2-title { font-size: 1.05rem; font-weight: 700; color: var(--dsb-tinta, #1c1f26); padding-top: 18px; }
        .op-swal .swal2-html-container { margin: 10px 18px 0; overflow: visible; }
        .op-popup { text-align: left; }
        .op-popup-ket { margin: 0 0 10px; font-size: .8rem; line-height: 1.5; color: var(--dsb-redup, #6b7280); }

        .op-cari-bungkus { position: relative; }
        .op-cari-bungkus > .bi { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .9rem; }
        .op-cari {
            width: 100%; min-height: 42px; padding: 9px 13px 9px 38px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px; background: #fff;
            color: var(--dsb-tinta, #1c1f26); font-size: .88rem; font-weight: 600; font-family: inherit;
        }
        .op-cari:focus { border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); outline: 0; }

        /* Tingginya dipatok supaya jendelanya tidak tumbuh melewati layar pada
           katalog yang panjang — yang digulung daftarnya, bukan halamannya. */
        .op-daftar { margin-top: 10px; max-height: 46vh; overflow-y: auto; }
        .op-kosong-pesan { margin: 14px 0 4px; font-size: .84rem; color: var(--dsb-redup, #6b7280); text-align: center; }

        .op-baris { display: flex; align-items: stretch; gap: 4px; border-radius: 11px; }
        .op-baris[hidden] { display: none; }
        .op-baris:hover { background: #f8fafc; }
        .op-baris.is-terpilih { background: #eef2ff; }

        .op-baris-pilih {
            flex: 1 1 auto; min-width: 0; display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border: 0; background: transparent; text-align: left; font-family: inherit;
        }
        .op-baris-teks { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
        .op-baris-label { font-size: .88rem; font-weight: 600; color: var(--dsb-tinta, #1c1f26); }
        .op-baris-ket { font-size: .76rem; color: var(--dsb-redup, #6b7280); }
        .op-baris-lencana {
            flex: 0 0 auto; padding: 2px 8px; border-radius: 999px; font-size: .68rem; font-weight: 700;
            background: #dcfce7; color: #15803d; letter-spacing: .02em;
        }
        .op-baris-centang { flex: 0 0 auto; color: #4338ca; }

        .op-baris-hapus {
            flex: 0 0 auto; width: 38px; border: 0; border-radius: 10px;
            background: transparent; color: #fca5a5; font-size: .9rem;
        }
        /* Merahnya terlihat SEJAK DIAM, bukan baru saat disentuh. Tombol
           buang yang menyamar abu-abu sampai detik terakhir ditekan tanpa
           disadari — dan di sini yang ditekan membuang nama dari katalog,
           bukan sekadar menutup sesuatu. */
        .op-baris:hover .op-baris-hapus { color: #ef4444; }
        .op-baris-hapus:hover { background: #fee2e2; color: #dc2626; }

        /* ===== Tombol pemicunya di borang ===== */
        .op-pilih { position: relative; }
        .op-pilih-tombol {
            width: 100%; min-height: 40px; display: flex; align-items: center; gap: 10px;
            padding: 8px 13px; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 11px;
            background: #fff; color: var(--dsb-tinta, #1c1f26);
            font-size: .86rem; font-weight: 600; font-family: inherit; text-align: left;
        }
        .op-pilih-tombol:hover { border-color: #cbd5e1; }
        .op-pilih-tombol:focus-visible { border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); outline: 0; }
        .op-pilih-tombol.is-galat { border-color: #fecaca; }
        .op-pilih-nilai { flex: 1 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        /* Kosong ditulis abu-abu, seperti placeholder kotak isian di sebelahnya —
           kalau sewarna nilai, "Belum ditentukan" terbaca sebagai pilihan. */
        .op-pilih-tombol.is-kosong .op-pilih-nilai { color: #9aa5b5; font-weight: 500; }
        .op-pilih-tombol > .bi-chevron-down { flex: 0 0 auto; color: #94a3b8; font-size: .75rem; }
    </style>
@endonce
