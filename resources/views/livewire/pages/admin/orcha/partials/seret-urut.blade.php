{{--
    Seret-dan-lepas untuk daftar yang urutannya berarti.

    Memindahkan kegiatan dari urutan delapan ke satu berarti tujuh kali
    menekan panah — dan tiap klik adalah satu penyimpanan ke Orcha.

    DIMUAT LANGSUNG, bukan lewat @push('scripts'): layout templateindex —
    yang dipakai seluruh halaman Orcha — tidak punya @stack('scripts'), jadi
    apa pun yang didorong ke sana dibuang diam-diam. Gejalanya: pustakanya
    tidak pernah termuat, seretnya tidak bekerja, dan tidak ada galat di
    layar maupun konsol.
--}}
@once
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
        /**
         * Memasang seret pada sebuah wadah.
         *
         * @param {HTMLElement} wadah  elemen yang anak-anaknya bisa diseret
         * @param {string} ke          nama metode Livewire yang menerima urutannya
         *
         * Yang dikirim ISI data-urut tiap anak, bukan posisinya: posisi di
         * layar berubah saat harinya disaring, sedangkan data-urut menunjuk
         * baris yang sama apa pun yang sedang ditampilkan.
         */
        window.pasangSeretUrut = function (wadah, ke) {
            if (!window.Sortable || wadah.dataset.seretTerpasang === '1') {
                return;
            }

            wadah.dataset.seretTerpasang = '1';

            window.Sortable.create(wadah, {
                animation: 140,
                // Hanya dari pegangannya: menyeret dari mana saja membuat
                // kotak isian tidak bisa diseleksi dengan tetikus.
                handle: '[data-seret-pegangan]',
                ghostClass: 'rb-seret-bayang',
                onEnd: function () {
                    const urutan = Array.from(wadah.children)
                        .map((el) => el.dataset.urut)
                        .filter((u) => u !== undefined);

                    const komponen = wadah.closest('[wire\\:id]');

                    if (komponen) {
                        window.Livewire.find(komponen.getAttribute('wire:id')).call(ke, urutan);
                    }
                },
            });
        };
    </script>

    <style>
        [data-seret-pegangan] { cursor: grab; }
        [data-seret-pegangan]:active { cursor: grabbing; }
        .rb-seret-bayang { opacity: .4; }
    </style>
@endonce
