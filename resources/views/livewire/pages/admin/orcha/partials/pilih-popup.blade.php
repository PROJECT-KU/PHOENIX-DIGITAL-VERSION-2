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
                    + (b.sunting ? '<button type="button" class="op-baris-sunting"'
                        + ' data-sunting="' + aman(b.sunting) + '"'
                        + ' data-nama="' + aman(b.label ?? '') + '"'
                        + ' data-kontak="' + aman(b.kontak ?? '') + '"'
                        + ' title="Ubah nama"><i class="bi bi-pencil"><\/i><\/button>' : '')
                    + (b.hapus ? '<button type="button" class="op-baris-hapus" data-hapus="' + aman(b.hapus)
                        + '" title="Buang dari daftar"><i class="bi bi-trash3"><\/i><\/button>' : '')
                    + '<\/div>';
            }

            /**
             * Jendela isian kecil untuk menambah / mengubah satu nama.
             *
             * Dipakai dua jalur sekaligus supaya keduanya tidak pernah
             * berbeda rupa. Memulangkan null bila dibatalkan — pemanggilnya
             * membuka kembali daftarnya, sebab membatalkan tidak boleh
             * berarti mencari dari awal lagi.
             *
             * @returns {Promise<{nama: string, kontak: string}|null>}
             */
            function bukaBorang(opsi) {
                return Swal.fire({
                    title: opsi.judul,
                    html: '<div class="op-borang">'
                        + '<label class="op-borang-label">' + aman(opsi.labelNama) + '<\/label>'
                        + '<input type="text" class="op-borang-isian" id="op-nama" value="'
                        + aman(opsi.nama) + '" autocomplete="off">'
                        + (opsi.pakaiKontak
                            ? '<label class="op-borang-label">Kontak (boleh dikosongkan)<\/label>'
                                + '<input type="text" class="op-borang-isian" id="op-kontak" value="'
                                + aman(opsi.kontak) + '" autocomplete="off" '
                                + 'placeholder="08\u2026">'
                            : '')
                        + '<\/div>',
                    width: 460,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: { popup: 'op-swal' },
                    didOpen: function (modal) {
                        var isian = modal.querySelector('#op-nama');
                        isian.focus();
                        isian.select();
                    },
                    preConfirm: function () {
                        var modal = Swal.getPopup();
                        var nama = (modal.querySelector('#op-nama').value || '').trim();

                        if (nama === '') {
                            Swal.showValidationMessage('Namanya belum diisi.');

                            return false;
                        }

                        var kontakEl = modal.querySelector('#op-kontak');

                        return { nama: nama, kontak: kontakEl ? (kontakEl.value || '').trim() : '' };
                    },
                }).then(function (hasil) {
                    return hasil.isConfirmed ? hasil.value : null;
                });
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
                        /* Menambah dikerjakan DI SINI, bukan lewat tombol
                           terpisah di bawah kotak pilih. Yang sedang dicari
                           admin saat ia menyadari namanya belum ada adalah
                           daftar ini — menutup jendela dulu untuk menekan
                           tombol lain berarti kehilangan tempatnya. */
                        + (d.tambahAksi || d.ketikBaru
                            ? '<button type="button" class="op-tambah"><i class="bi bi-plus-circle"><\/i> '
                                + aman(d.tambahLabel || 'Tambah baru') + '<\/button>'
                            : '')
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

                            /* ---- Tambah baru ---- */
                            if (e.target.closest('.op-tambah')) {
                                bukaBorang({
                                    judul: d.tambahLabel || 'Tambah baru',
                                    nama: '',
                                    kontak: '',
                                    pakaiKontak: d.pakaiKontak === '1',
                                    labelNama: d.tambahMedan || 'Nama',
                                }).then(function (isi) {
                                    if (! isi) { window.pilihPopup(tombol); return; }

                                    /* Provinsi tidak punya rekaman apa pun — daftarnya
                                       diturunkan dari yang sudah dipakai. Jadi "menambah"
                                       di sana berarti memakai nama yang diketik apa
                                       adanya, bukan memanggil peladen. */
                                    if (d.ketikBaru && ! d.tambahAksi) {
                                        if (wire) { wire.set(d.ke, isi.nama); }

                                        return;
                                    }

                                    if (wire && d.tambahAksi) {
                                        wire.call(d.tambahAksi, isi.nama, isi.kontak);
                                    }
                                });

                                return;
                            }

                            /* ---- Ubah nama ---- */
                            var sunting = e.target.closest('.op-baris-sunting');

                            if (sunting && wire && d.suntingAksi) {
                                bukaBorang({
                                    judul: 'Ubah nama',
                                    nama: sunting.dataset.nama || '',
                                    kontak: sunting.dataset.kontak || '',
                                    pakaiKontak: d.pakaiKontak === '1',
                                    labelNama: d.tambahMedan || 'Nama',
                                }).then(function (isi) {
                                    if (! isi) { window.pilihPopup(tombol); return; }

                                    wire.call(d.suntingAksi, Number(sunting.dataset.sunting), isi.nama, isi.kontak);
                                });

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

        /* Pensil: ungu, bukan merah. Warnanya memisahkan "ubah" dari
           "buang" sebelum ikonnya sempat dibaca — dua tombol berdampingan
           yang sewarna akan tertekan silang cepat atau lambat. */
        .op-baris-sunting {
            flex: 0 0 auto; width: 36px; border: 0; border-radius: 10px;
            background: transparent; color: #c4b5fd; font-size: .88rem;
        }
        .op-baris:hover .op-baris-sunting { color: #7c3aed; }
        .op-baris-sunting:hover { background: #f5f3ff; color: #6d28d9; }

        /* ===== Tombol tambah di kaki jendela ===== */
        .op-tambah {
            display: flex; align-items: center; justify-content: center; gap: 7px;
            width: 100%; min-height: 42px; margin-top: 10px; padding: 0 14px;
            border: 1px dashed #ddd6fe; border-radius: 12px;
            background: #faf9ff; color: #6d28d9;
            font-size: .84rem; font-weight: 700; cursor: pointer;
        }
        .op-tambah:hover { background: #f5f3ff; border-color: #a78bfa; }

        /* ===== Borang kecil (tambah & ubah) ===== */
        .op-borang { text-align: left; padding: 2px 4px 4px; }
        .op-borang-label {
            display: block; margin: 10px 0 5px;
            font-size: .78rem; font-weight: 700; color: var(--dsb-tinta, #1c1f26);
        }
        .op-borang-label:first-child { margin-top: 0; }
        .op-borang-isian {
            width: 100%; min-height: 42px; padding: 9px 13px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 11px; background: #fff;
            color: var(--dsb-tinta, #1c1f26); font-size: .88rem; font-family: inherit;
        }
        .op-borang-isian:focus {
            outline: none; border-color: #c4b5fd; box-shadow: 0 0 0 3px rgba(124, 58, 237, .14);
        }

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
