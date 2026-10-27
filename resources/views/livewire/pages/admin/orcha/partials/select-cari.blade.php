{{--
    Kotak pilih yang bisa dicari (Select2), seragam untuk seluruh layar Orcha.

    Dipanggil sekali per layar; @once menjaga asetnya tidak dimuat dua kali
    walau partialnya ikut beberapa kali.

    Dari CDN, bukan dari Vite: public/build tidak ikut terbawa saat deploy,
    jadi aset yang dibundel tidak pernah sampai ke server. Pola yang sama
    sudah dipakai layar Blog dan Permission.

    Dimuat LANGSUNG di sini, bukan lewat stack: layout templateindex — yang
    dipakai seluruh halaman Orcha — tidak punya @stack('scripts'), jadi apa
    pun yang didorong ke sana dibuang diam-diam. Pustakanya tidak pernah
    termuat dan kotak pilihnya tetap polos, tanpa satu pun galat di layar
    maupun di konsol.

    CARA PAKAI — tiga hal, ketiganya pernah jadi sumber bug:

    1. Pembungkusnya WAJIB wire:ignore. Select2 membangun DOM-nya sendiri di
       sebelah <select> aslinya; tanpa ini, morph Livewire membuangnya tiap
       kali halaman digambar ulang, dan yang tersisa kotak pilih telanjang
       atau dua kotak bertumpuk.

    2. Karena isinya diabaikan Livewire, wire:model TIDAK berlaku di
       dalamnya. Nilai awalnya ditandai @selected(), dan perubahannya
       didorong balik lewat $wire.set().

    3. Bila daftar pilihannya BISA BERUBAH dari server (destinasi yang
       mengikuti provinsi, vendor yang baru ditambahkan), beri wire:key yang
       ikut berubah bersama daftarnya. wire:ignore menahan morph, tetapi node
       dengan kunci berbeda tetap diganti utuh — dan Alpine memasang ulang
       Select2 dengan pilihan yang baru.
--}}
@once
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        /**
         * Memasang Select2 pada <select> di dalam $el, lalu menyambungkan
         * perubahannya ke properti Livewire bernama `ke`.
         *
         * Kotak carinya hanya muncul bila pilihannya memang banyak
         * (minimumResultsForSearch). Pada daftar enam baris, kotak cari
         * justru menambah satu benda yang harus diabaikan mata.
         */
        window.pasangSelectCari = function (el, ke, pilihan = {}) {
            const $select = window.jQuery(el).find('select');

            if (! $select.length || $select.data('select2')) {
                return;
            }

            $select.select2(Object.assign({
                width: '100%',
                dropdownParent: window.jQuery(el),
                minimumResultsForSearch: 8,
                language: {
                    noResults: () => 'Tidak ada yang cocok',
                    searching: () => 'Mencari…',
                },
            }, pilihan));

            $select.on('change', function () {
                const wire = window.Livewire.find(
                    el.closest('[wire\\:id]').getAttribute('wire:id')
                );

                wire.set(ke, window.jQuery(this).val());
            });
        };
    </script>

    <style>
        /* Select2 disamakan dengan .dsb-isian, bukan dibiarkan memakai
           rupa bawaannya — satu kotak pilih bersudut tajam di antara
           kotak-kotak membulat terbaca seperti kotak yang rusak. */
        .select2-container .select2-selection--single {
            height: 42px; border: 1px solid var(--dsb-tepi, #e9edf3);
            border-radius: 12px; background: #fff;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px; padding-left: 14px; padding-right: 30px;
            color: var(--dsb-tinta, #1c1f26); font-size: .88rem; font-weight: 600;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; right: 6px; }
        .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #94a3b8; font-weight: 500; }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
        }
        .select2-dropdown {
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px;
            box-shadow: 0 18px 38px -22px rgba(15, 23, 42, .45); overflow: hidden;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 10px; padding: 7px 10px;
        }
        .select2-container--default .select2-results__option { font-size: .86rem; padding: 7px 12px; }
        .select2-container--default .select2-results__option--highlighted[aria-selected] { background: #4f46e5; }
        .select2-container--default .select2-results__option[aria-selected=true] { background: #eef2ff; color: #4338ca; }
        .select2-results__option--group .select2-results__group { font-size: .72rem; letter-spacing: .04em; text-transform: uppercase; color: #94a3b8; }
    </style>
@endonce
