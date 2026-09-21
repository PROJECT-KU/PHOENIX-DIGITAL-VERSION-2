<div wire:poll.30s>
    @if ($tampil)
    <style>
        /* Panel bot memakai bahasa rupa dasbor (kartu putih, bingkai tipis
           #e9edf3, ubin ikon berwarna) supaya tidak terbaca sebagai tempelan
           dari aplikasi lain di antara kartu-kartu dasbor. Nama kelasnya
           sengaja tetap bt-* — markup dan logikanya tidak disentuh. */
        .bt-panel { display: grid; gap: 14px; margin-bottom: clamp(20px, 3vw, 32px); }

        /* ---- Bilah status ---- */
        .bt-bar {
            display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
            padding: 16px 20px; background: #fff;
            border: 1px solid #e9edf3; border-radius: 18px;
        }
        .bt-ikon {
            flex: 0 0 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 15px; font-size: 1.32rem;
            background: linear-gradient(135deg, #ede9fe, #f5f3ff);
            border: 1px solid #ddd6fe; color: #7c3aed;
        }
        .bt-ikon i::before { display: block; line-height: 1; }
        .bt-judul { flex: 1 1 260px; min-width: 0; }
        .bt-judul b {
            display: block; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 700; font-size: 1rem; color: #1c1f26; line-height: 1.25;
        }
        .bt-judul small { color: #6b7280; font-size: .82rem; line-height: 1.5; }

        /* Lampu status: titiknya dari CSS, bukan ikon huruf — kotak glif selalu
           lebih lebar dari titiknya dan membuat jaraknya timpang. */
        /* Lencana status berdiri SEBARIS dengan tombol-tombolnya, jadi
           kotaknya disamakan: tinggi 36px dan tepi setebal 1px (bening) —
           persis .bt-btn. Tanpa itu lencananya 21px di antara tombol 36px dan
           terbaca seperti label yang tercecer, bukan status kartunya.
           Ukuran hurufnya sengaja TIDAK ikut dibesarkan: ia status, bukan
           tombol, dan tidak boleh mengajak ditekan. */
        .bt-lampu {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            box-sizing: border-box; min-height: 36px; padding: 0 13px;
            border: 1px solid transparent; border-radius: 999px;
            font-size: .72rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
        }
        .bt-lampu::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
        .bt-lampu.on { background: #dcfce7; color: #15803d; }
        .bt-lampu.off { background: #f1f5f9; color: #475569; }
        .bt-lampu.jeda { background: #fef9c3; color: #a16207; }

        .bt-aksi { display: flex; gap: 8px; flex-wrap: wrap; }
        .bt-btn {
            display: inline-flex; align-items: center; gap: 7px; white-space: nowrap;
            border: 1px solid #e9edf3; background: #fff; color: #475569;
            font-size: .82rem; font-weight: 700; padding: 9px 14px; border-radius: 11px;
            text-decoration: none; cursor: pointer;
            transition: color .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
        }
        .bt-btn i { line-height: 1; }
        .bt-btn i::before { display: block; line-height: 1; }
        @media (hover: hover) and (pointer: fine) {
            .bt-btn:hover { background: #7c3aed; border-color: transparent; color: #fff; box-shadow: 0 8px 18px rgba(124,58,237,.26); }
        }
        .bt-btn.utama { background: #7c3aed; border-color: transparent; color: #fff; box-shadow: 0 8px 18px rgba(124,58,237,.26); }
        .bt-btn.bahaya { background: #dc2626; border-color: transparent; color: #fff; }
        .bt-btn.hijau { background: #16a34a; border-color: transparent; color: #fff; }

        /* ---- Kartu pemberitahuan (token, kuota habis, dsb.) ---- */
        .bt-kartu { border-radius: 18px; padding: 16px 20px; border: 1px solid; }
        .bt-kartu h6 { display: flex; align-items: center; gap: 8px; font-weight: 800; margin: 0 0 4px; font-size: .95rem; }
        .bt-kartu p { margin: 0 0 10px; font-size: .84rem; line-height: 1.6; }
        .bt-kartu.merah { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .bt-kartu.kuning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .bt-kartu.ungu { background: #f5f3ff; border-color: #ddd6fe; color: #4c1d95; }
        .bt-kartu.biru { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
        .bt-kartu.biru .bt-baris { border-color: #bfdbfe; }

        /* Titik berdenyut: menandai pekerjaan yang sedang berjalan. */
        .bt-denyut { flex: 0 0 auto; width: 10px; height: 10px; border-radius: 50%; background: #2563eb; animation: btDenyut 1.6s ease-in-out infinite; }
        @keyframes btDenyut { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: .35; transform: scale(.72); } }
        @media (prefers-reduced-motion: reduce) { .bt-denyut { animation: none; } }

        .bt-tahap { display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 999px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
        .bt-tahap.jalan { background: #dbeafe; color: #1d4ed8; }
        .bt-tahap.perlu { background: #fee2e2; color: #b91c1c; }
        .bt-tahap.lengkapi { background: #fef3c7; color: #92400e; }
        .bt-tahap.beres { background: #dcfce7; color: #15803d; }

        /* Pekerjaan yang menunggu admin LEBIH dari sehari. Dibedakan dari
           "perlu admin" biasa: yang baru gagal sepuluh menit lalu dan yang
           sudah mengendap lima hari sama-sama merah, padahal hanya satu di
           antaranya yang membuat pelanggan menunggu tanpa kabar. */
        .bt-lama {
            display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 999px;
            background: #b91c1c; color: #fff; font-size: .72rem; font-weight: 800; white-space: nowrap;
        }
        .bt-lama i.bi { line-height: 1; }
        .bt-lama i.bi::before { display: block; line-height: 1; }
        .bt-baris.terbengkalai { border-color: #fca5a5; background: #fff5f5; box-shadow: inset 3px 0 0 #dc2626; }
        /* Penanda terbengkalai DI KEPALA kartu dibuat bergaris, bukan pekat:
           tab terpilih juga merah pekat, dan dua pil merah pekat bersebelahan
           terbaca seperti dua tab — padahal yang satu tidak bisa diklik. */
        .bt-lama.garis { background: #fff; color: #b91c1c; border: 1px solid #fca5a5; }

        /* ---- Kartu pantau: SELALU tampil, walau tidak ada pekerjaan ---- */
        .bt-pantau { background: #fff; border: 1px solid #e9edf3; border-radius: 18px; overflow: hidden; }
        .bt-pantau-kepala {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            padding: 15px 20px; border-bottom: 1px solid #f1f5f9; min-height: 68px;
        }
        .bt-pantau-kepala h6 {
            margin: 0 auto 0 0; display: flex; align-items: center; gap: 10px;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700;
            font-size: 1rem; color: #1c1f26;
        }
        /* Ubin ikon judul, sepadan dengan kepala kartu dasbor lainnya. */
        .bt-pantau-kepala h6 > i.bi {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 11px; font-size: .98rem;
            background: #eff6ff; border: 1px solid #bfdbfe; color: #0284c7;
        }
        .bt-pantau-kepala h6 > i.bi::before { display: block; line-height: 1; }

        /* Penghitung: chip putih berbingkai, seragam dengan chip kepala bagian. */
        .bt-hitung {
            display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border-radius: 999px;
            background: #f8fafc; border: 1px solid #e9edf3; color: #64748b; font-size: .74rem; font-weight: 600;
        }
        .bt-hitung b { color: #1c1f26; font-weight: 800; }
        .bt-hitung i.bi { line-height: 1; }
        .bt-hitung i.bi::before { display: block; line-height: 1; }

        /* Tab. Penghitung di kepala kartu SEKALIGUS menjadi tabnya: angkanya
           sudah dibaca admin untuk memutuskan ke mana melihat, jadi menaruh
           baris tab terpisah di bawahnya hanya mengulang informasi yang sama.

           Kenapa tab, bukan satu daftar panjang seperti sebelumnya: pekerjaan
           yang menunggu tindakan admin dulu berbaris di antara yang sedang
           berjalan dan yang sudah selesai. Pada hari yang ramai ia terdorong
           ke bawah dan terlewat — persis seperti notifikasi lonceng yang
           tenggelam di antara notifikasi lain. */
        .bt-tab {
            /* Warna tiap tab = warna lencana baris di dalamnya: merah untuk
               yang menuntut tindakan, biru untuk yang sedang berjalan, hijau
               untuk yang sudah selesai. Jadi warna yang dilihat admin di tab
               adalah warna yang sama yang menyambutnya setelah diklik. */
            --t: #64748b;
            display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border-radius: 999px;
            background: color-mix(in srgb, var(--t) 10%, #fff);
            border: 1px solid color-mix(in srgb, var(--t) 22%, #fff);
            color: color-mix(in srgb, var(--t) 78%, #000);
            font-size: .74rem; font-weight: 600; cursor: pointer; white-space: nowrap;
            transition: background .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
        }
        .bt-tab b { color: color-mix(in srgb, var(--t) 90%, #000); font-weight: 800; }
        .bt-tab i.bi { line-height: 1; }
        @media (hover: hover) and (pointer: fine) {
            .bt-tab:hover { border-color: color-mix(in srgb, var(--t) 45%, #fff); }
        }
        /* Tab terpilih TERISI PENUH warnanya, bukan sekadar lebih pekat: pada
           deretan pil berwarna pastel, beda kepekatan saja tidak cukup untuk
           menandai mana yang sedang dibuka. */
        .bt-tab.aktif {
            background: var(--t); border-color: transparent; color: #fff;
            box-shadow: 0 6px 14px color-mix(in srgb, var(--t) 30%, transparent);
        }
        .bt-tab.aktif b { color: #fff; }

        .bt-tab.perlu { --t: #dc2626; }   /* menuntut tindakan admin */
        .bt-tab.jalan { --t: #2563eb; }   /* sedang dikerjakan bot */
        .bt-tab.beres { --t: #16a34a; }   /* tuntas hari ini */

        .bt-pantau-isi { padding: 14px 20px 18px; display: grid; gap: 9px; }
        .bt-pantau .bt-baris { border-color: #eef1f6; }
        .bt-pantau .bt-baris.perlu { border-color: #fecaca; background: #fff7f7; }
        .bt-pantau .bt-baris.lengkapi { border-color: #fde68a; background: #fffcf3; }
        .bt-pantau .bt-baris.beres { border-color: #bbf7d0; background: #f7fffa; }

        .bt-kosong { display: flex; align-items: flex-start; gap: 12px; padding: 22px 2px; color: #6b7280; font-size: .85rem; line-height: 1.6; }
        .bt-kosong i { font-size: 1.3rem; color: #94a3b8; flex: 0 0 auto; }
        .bt-daftar { display: grid; gap: 9px; }

        /* Satu baris pekerjaan.
           Keterangan MEMAKAN sisa ruang (flex: 1), sehingga lencana dan tombol
           selalu terdorong rapat ke tepi kanan — berapa pun jumlahnya, dan
           sepanjang apa pun teks di sebelahnya. Jumlah tombolnya memang
           berubah-ubah (gagal tanpa kode dapat tombol "Coba lagi", yang perlu
           dilengkapi tidak), jadi kisi berkolom tetap justru meninggalkan
           kolom kosong di sebagian baris. */
        .bt-baris {
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
            background: #fff; border: 1px solid #eef1f6; border-radius: 14px;
            padding: 12px 14px; color: #334155;
        }
        .bt-baris > * { flex: 0 0 auto; }
        .bt-baris-isi { flex: 1 1 240px; min-width: 0; font-size: .82rem; line-height: 1.55; }
        .bt-baris-isi b { color: #0f172a; font-size: .88rem; }
        .bt-baris-isi span { display: block; color: #6b7280; }
        .bt-baris-isi a { color: #2563eb; }

        .bt-token { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
        .bt-token input { flex: 1 1 240px; min-width: 0; font-family: ui-monospace, monospace; font-size: .8rem; border: 1px solid #c4b5fd; border-radius: 11px; padding: 9px 12px; background: #fff; }

        @media (max-width: 991.98px), (pointer: coarse) {
            /* Keterangan mengambil satu baris penuh; lencana dan tombol
               berbaris di bawahnya, tidak berdesakan di sisa ruang sempit. */
            .bt-baris-isi { flex: 1 1 100%; }

            /* Tab pemilih adalah TOMBOL, dan setinggi 26px ia terlalu kecil
               untuk ditekan jempol dengan yakin. Hurufnya tidak ikut
               dibesarkan — yang ditambah kotak sentuhnya. */
            .bt-tab { min-height: 36px; padding-inline: 13px; }
        }
        @media (max-width: 575.98px) {
            .bt-bar { padding: 15px 16px; gap: 12px; }
            /* Basis judul dikecilkan supaya tetap sebaris dengan ubin ikonnya;
               pada basis 260px ikonnya terdorong sendirian ke baris pertama. */
            .bt-judul { flex: 1 1 150px; }
            .bt-ikon { flex-basis: 44px; height: 44px; border-radius: 13px; font-size: 1.2rem; }
            /* Dua kolom sama lebar: lencana + Jeda di baris pertama, Pasang
               skrip + Token baru di baris kedua. Tak ada yang tertinggal
               sendirian dengan sisi kanan kosong. Kalau jumlahnya ganjil,
               yang terakhir mengambil satu baris penuh. */
            .bt-aksi { width: 100%; display: grid; grid-template-columns: 1fr 1fr; }
            .bt-aksi > .bt-btn, .bt-aksi > .bt-lampu { justify-content: center; min-width: 0; }
            .bt-aksi > :last-child:nth-child(odd) { grid-column: 1 / -1; }
            .bt-pantau-kepala, .bt-pantau-isi { padding-inline: 16px; }
            .bt-pantau-kepala h6 { flex: 1 1 100%; }

            /* Penghitung di kepala kartu jadi kisi dua kolom sama lebar.
               Sebagai flex, tiap pil selebar teksnya sendiri — "perlu admin"
               dan "selesai hari ini" lebar, "antre" sempit — sehingga barisnya
               membungkus tak rata dan sisi kanan "berjalan" & "antre" kosong.
               Judul mengambil baris penuh; kalau jumlah pilnya ganjil (mis.
               penanda "terbengkalai" ikut muncul), yang terakhir mengambil
               satu baris penuh. */
            .bt-pantau-kepala { display: grid; grid-template-columns: 1fr 1fr; }
            .bt-pantau-kepala h6 { grid-column: 1 / -1; }
            .bt-pantau-kepala > .bt-tab,
            .bt-pantau-kepala > .bt-hitung,
            .bt-pantau-kepala > .bt-lama {
                justify-content: center; min-height: 36px; min-width: 0;
                white-space: normal; text-align: center; line-height: 1.25;
            }
            .bt-pantau-kepala > :last-child:nth-child(even) { grid-column: 1 / -1; }
        }
    </style>

    {{-- Tanpa .container-fluid: panel ini dirender DI DALAM kerangka dasbor
         yang sudah punya tepinya sendiri, dan padding tambahan dari Bootstrap
         membuat kartu bot masuk ~12px dibanding kartu di atas & di bawahnya. --}}
    <div>
        <div class="bt-panel">
            @if (! $skemaSiap)
                @if ($bolehAtur)
                <div class="bt-kartu kuning">
                    <h6><i class="bi bi-database-exclamation"></i> Bot Turnitin belum bisa dipakai</h6>
                    <p class="mb-0">Kolom database untuk bot belum ada. Jalankan SQL migrasi <code>2026_09_14_100000_tambah_bot_turnitin_di_order_uploads</code> di server.</p>
                </div>
                @endif
            @else
                {{-- Bilah status --}}
                <div class="bt-bar">
                    <span class="bt-ikon"><i class="bi bi-robot"></i></span>
                    <div class="bt-judul">
                        <b>Bot Turnitin (submitin.id)</b>
                        <small>
                            @if (! $dipasang)
                                Belum dipasang.
                            @elseif ($aktif)
                                Terakhir memberi kabar {{ $detak->locale('id')->diffForHumans() }}
                            @elseif ($detak)
                                Tidak aktif sejak {{ $detak->locale('id')->diffForHumans() }} — buka tab submitin.id di Chrome admin
                                · {{ $antrean }} unggahan di antrean
                            @else
                                Belum pernah terhubung — pasang skripnya di Chrome admin.
                            @endif
                        </small>
                    </div>
                    {{-- Lencana status DI DALAM deretan tombol, bukan saudaranya.
                         Sebagai saudara, di ponsel deretan tombol mengambil baris
                         penuh dan lencananya tertinggal sendirian di baris atas
                         dengan sisi kanan kosong. --}}
                    <div class="bt-aksi">
                        @if ($dipasang)
                            @if ($dijeda)
                                <span class="bt-lampu jeda">Dijeda</span>
                            @elseif ($aktif)
                                <span class="bt-lampu on">Aktif</span>
                            @else
                                <span class="bt-lampu off">Tidak aktif</span>
                            @endif
                            <button type="button" class="bt-btn" wire:click="alihkanJeda">
                                <i class="bi {{ $dijeda ? 'bi-play-fill' : 'bi-pause-fill' }}"></i> {{ $dijeda ? 'Lanjutkan' : 'Jeda' }}
                            </button>
                        @endif
                        @if ($bolehAtur)
                            <a class="bt-btn" href="{{ $urlSkrip }}" target="_blank" rel="noopener"><i class="bi bi-download"></i> Pasang skrip</a>
                            @if ($dipasang)
                                <button type="button" class="bt-btn pcek-konfirmasi" data-action="buatToken"
                                    data-title="Buat token baru?" data-text="Token lama langsung berhenti bekerja. Bot di Chrome admin perlu diisi token yang baru."
                                    data-confirm="Ya, buat baru" data-icon="warning">
                                    <i class="bi bi-key"></i> Token baru
                                </button>
                            @else
                                <button type="button" class="bt-btn utama" wire:click="buatToken">
                                    <i class="bi bi-key"></i> Buat token
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                @if ($tokenBaru)
                <div class="bt-kartu ungu">
                    <h6><i class="bi bi-key-fill"></i> Token bot — salin sekarang</h6>
                    <p>Tempel di panel bot pada tab submitin.id (tombol <b>Pengaturan</b>). Token ini tidak akan ditampilkan lagi. Jangan dibagikan di chat.</p>
                    <div class="bt-token">
                        <input type="text" readonly value="{{ $tokenBaru }}" id="bt-token-input" onclick="this.select()">
                        <button type="button" class="bt-btn utama" onclick="navigator.clipboard.writeText(document.getElementById('bt-token-input').value)"><i class="bi bi-clipboard"></i> Salin</button>
                        <button type="button" class="bt-btn" wire:click="tutupToken">Tutup</button>
                    </div>
                </div>
                @endif

                @if ($masalah && $aktif)
                <div class="bt-kartu kuning">
                    <h6><i class="bi bi-exclamation-circle"></i> Bot berhenti menunggu</h6>
                    <p class="mb-0">{{ $masalah }}</p>
                </div>
                @endif

                {{-- Peringatan pekerjaan terbengkalai. Ditaruh di ATAS kartu
                     pantau, bukan sebagai baris di dalamnya: kalau ia hanya
                     satu baris di antara baris lain, ia ikut terlewat persis
                     seperti pekerjaannya sendiri. --}}
                @if (! empty($terbengkalai) && $terbengkalai->isNotEmpty())
                    @php
                        $tertua = $terbengkalai->first();
                        $lamaTertua = \App\Support\BotTurnitin::menungguSejak($tertua)?->locale('id')->diffForHumans(null, true) ?? 'beberapa waktu';
                    @endphp
                    <div class="bt-kartu merah">
                        <h6><i class="bi bi-alarm-fill"></i> {{ $terbengkalai->count() }} pengecekan menunggu terlalu lama</h6>
                        <p class="mb-0">
                            Sudah lewat {{ \App\Support\BotTurnitin::TERBENGKALAI_JAM }} jam sejak bot berhenti dan menyerahkannya ke admin.
                            Yang terlama <b>{{ $lamaTertua }}</b>@if (optional($tertua->order)->order_number) — {{ $tertua->order->order_number }}@endif.
                            Pelanggannya menunggu selama itu tanpa kabar.
                        </p>
                    </div>
                @endif

                {{-- Pantauan pengecekan bot — SELALU tampil supaya admin punya
                     satu tempat tetap untuk melihat: nomor pesanan, tahap bot,
                     kode submitin, kabar terakhir, dan apakah perlu ditindak. --}}
                <div class="bt-pantau">
                    <div class="bt-pantau-kepala">
                        <h6><i class="bi bi-list-check"></i> Pengecekan Bot Turnitin</h6>

                        <button type="button" class="bt-tab perlu {{ $tab === 'perlu' ? 'aktif' : '' }}" wire:click="pilihTab('perlu')">
                            <i class="bi bi-person-exclamation"></i> <b>{{ $perluAdmin->count() }}</b> perlu admin
                        </button>
                        @if (! empty($terbengkalai) && $terbengkalai->isNotEmpty())
                            <span class="bt-lama garis" title="Menunggu tindakan admin lebih dari {{ \App\Support\BotTurnitin::TERBENGKALAI_JAM }} jam">
                                <i class="bi bi-alarm"></i> {{ $terbengkalai->count() }} terbengkalai
                            </span>
                        @endif

                        <button type="button" class="bt-tab jalan {{ $tab === 'berjalan' ? 'aktif' : '' }}" wire:click="pilihTab('berjalan')">
                            <i class="bi bi-arrow-repeat"></i> <b>{{ $berjalan->count() }}</b> berjalan
                        </button>

                        <button type="button" class="bt-tab beres {{ $tab === 'selesai' ? 'aktif' : '' }}" wire:click="pilihTab('selesai')">
                            <i class="bi bi-check2-circle"></i> <b>{{ $selesaiHariIni }}</b> selesai hari ini
                        </button>

                        {{-- Antrean tidak punya daftar sendiri (belum disentuh bot),
                             jadi tetap angka, bukan tab. --}}
                        <span class="bt-hitung"><i class="bi bi-hourglass"></i> <b>{{ $antrean }}</b> antre</span>
                    </div>
                    <div class="bt-pantau-isi">
                        @if ($tab === 'berjalan')
                        @forelse ($berjalan as $up)
                            <div class="bt-baris" wire:key="bt-jalan-{{ $up->id }}">
                                <span class="bt-denyut"></span>
                                <div class="bt-baris-isi">
                                    <b>{{ optional($up->order)->order_number ?? '—' }}</b>
                                    <span>
                                        Kabar terakhir {{ $up->bot_diperbarui_at?->locale('id')->diffForHumans() ?? '—' }}
                                        · mulai {{ $up->bot_diambil_at?->locale('id')->diffForHumans() ?? '—' }}
                                        · kode:
                                        @if ($up->bot_kode)
                                            <a href="https://submitin.id/status?order={{ urlencode($up->bot_kode) }}" target="_blank" rel="noopener">{{ $up->bot_kode }}</a>
                                        @else
                                            belum ada (belum terkirim)
                                        @endif
                                    </span>
                                </div>
                                <span class="bt-tahap jalan">
                                    @if ($up->bot_status === 'menunggu_hasil')
                                        <i class="bi bi-hourglass-split"></i> Menunggu laporan submitin
                                    @else
                                        <i class="bi bi-upload"></i> Mengunggah ke submitin
                                    @endif
                                </span>
                                @if ($up->order)
                                    <a class="bt-btn" href="{{ route('admin.pesanantoko.detail', $up->order) }}" wire:navigate><i class="bi bi-box-arrow-in-right"></i> Buka pesanan</a>
                                @endif
                            </div>
                        @empty
                            <div class="bt-kosong">
                                <i class="bi bi-hourglass"></i>
                                <span>
                                    Tidak ada pengecekan yang sedang dikerjakan bot.
                                    @if ($antrean > 0)
                                        {{ $antrean }} dokumen menunggu di antrean.
                                    @endif
                                </span>
                            </div>
                        @endforelse
                        @endif

                        @if ($tab === 'perlu')
                        @forelse ($perluAdmin as $up)
                            @php
                                $macet = \App\Support\BotTurnitin::macet($up);
                                $lengkapi = $up->bot_status === 'perlu_dilengkapi';
                            @endphp
                            @php
                                $jamMenunggu = \App\Support\BotTurnitin::lamaMenungguJam($up);
                                $terlaluLama = $jamMenunggu !== null && $jamMenunggu >= \App\Support\BotTurnitin::TERBENGKALAI_JAM;
                            @endphp
                            <div class="bt-baris {{ $lengkapi ? 'lengkapi' : 'perlu' }} {{ $terlaluLama ? 'terbengkalai' : '' }}" wire:key="bt-perlu-{{ $up->id }}">
                                <div class="bt-baris-isi">
                                    <b>{{ optional($up->order)->order_number ?? '—' }}</b>
                                    <span>
                                        Kabar terakhir {{ $up->bot_diperbarui_at?->locale('id')->diffForHumans() ?? '—' }}
                                        · kode:
                                        @if ($up->bot_kode)
                                            <a href="https://submitin.id/status?order={{ urlencode($up->bot_kode) }}" target="_blank" rel="noopener">{{ $up->bot_kode }}</a>
                                            — sudah terkirim, cek di sana sebelum mengirim ulang
                                        @else
                                            belum ada (belum terkirim ke submitin)
                                        @endif
                                    </span>
                                    @if ($up->bot_pesan)
                                        <span>{{ $up->bot_pesan }}</span>
                                    @endif
                                </div>
                                @if ($terlaluLama)
                                    <span class="bt-lama">
                                        <i class="bi bi-alarm"></i>
                                        Menunggu {{ \App\Support\BotTurnitin::menungguSejak($up)?->locale('id')->diffForHumans(null, true) }}
                                    </span>
                                @endif
                                <span class="bt-tahap {{ $lengkapi ? 'lengkapi' : 'perlu' }}">
                                    @if ($lengkapi)
                                        <i class="bi bi-puzzle"></i> Perlu dilengkapi admin
                                    @elseif ($macet)
                                        <i class="bi bi-exclamation-triangle"></i> Bot tidak memberi kabar — perlu admin
                                    @else
                                        <i class="bi bi-x-octagon"></i> Bot gagal — perlu admin
                                    @endif
                                </span>
                                @if ($up->order)
                                    <a class="bt-btn utama" href="{{ route('admin.pesanantoko.detail', $up->order) }}" wire:navigate><i class="bi bi-box-arrow-in-right"></i> Buka pesanan</a>
                                @endif
                                @if (! $up->bot_kode && $up->bot_status === 'gagal')
                                    <button type="button" class="bt-btn" wire:click="cobaLagi('{{ $up->id }}')"><i class="bi bi-arrow-repeat"></i> Coba lagi pakai bot</button>
                                @endif
                                @if (! $lengkapi)
                                    <button type="button" class="bt-btn" wire:click="ambilAlih('{{ $up->id }}')"><i class="bi bi-person-check"></i> Saya kerjakan manual</button>
                                @endif
                            </div>
                        @empty
                            <div class="bt-kosong">
                                <i class="bi bi-check2-circle"></i>
                                <span>Tidak ada yang menunggu tindakan Anda. Semua pengecekan tertangani bot.</span>
                            </div>
                        @endforelse
                        @endif

                        @if ($tab === 'selesai')
                        @forelse ($selesaiTerbaru as $up)
                            <div class="bt-baris beres" wire:key="bt-beres-{{ $up->id }}">
                                <div class="bt-baris-isi">
                                    <b>{{ optional($up->order)->order_number ?? '—' }}</b>
                                    <span>
                                        Selesai {{ $up->bot_diperbarui_at?->locale('id')->diffForHumans() ?? '—' }}
                                        · kode:
                                        @if ($up->bot_kode)
                                            <a href="https://submitin.id/status?order={{ urlencode($up->bot_kode) }}" target="_blank" rel="noopener">{{ $up->bot_kode }}</a>
                                        @else
                                            —
                                        @endif
                                        @if (! is_null($up->persentase))
                                            · kemiripan {{ $up->persentase }}%
                                        @endif
                                    </span>
                                </div>
                                <span class="bt-tahap beres"><i class="bi bi-check2-circle"></i> Selesai — hasil terkirim ke customer</span>
                                @if ($up->order)
                                    <a class="bt-btn" href="{{ route('admin.pesanantoko.detail', $up->order) }}" wire:navigate><i class="bi bi-box-arrow-in-right"></i> Buka pesanan</a>
                                @endif
                            </div>
                        @empty
                            <div class="bt-kosong">
                                <i class="bi bi-inbox"></i>
                                <span>
                                    Belum ada yang dituntaskan bot hari ini.
                                    @if ($antrean > 0)
                                        {{ $antrean }} dokumen menunggu — bot akan mengambilnya begitu dilanjutkan.
                                    @else
                                        Bot akan mengambilnya otomatis begitu customer mengunggah dokumen cek plagiasi.
                                    @endif
                                </span>
                            </div>
                        @endforelse
                        @endif
                    </div>
                </div>

                {{-- Kuota paket Standard habis --}}
                @if ($kuotaHabis)
                <div class="bt-kartu merah">
                    <h6><i class="bi bi-battery"></i> Kuota paket Standard di submitin.id habis</h6>
                    <p>{{ $kuotaHabis['pesan'] }} Bot berhenti mengambil antrean sejak {{ \Illuminate\Support\Carbon::parse($kuotaHabis['at'])->locale('id')->diffForHumans() }} — {{ $antrean }} unggahan menunggu.</p>
                    <div class="bt-aksi">
                        <a class="bt-btn" href="https://submitin.id/" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Buka submitin.id (Paket Hemat)</a>
                        <button type="button" class="bt-btn hijau" wire:click="kuotaSudahDiisi"><i class="bi bi-check2-circle"></i> Kuota sudah diisi — lanjutkan bot</button>
                    </div>
                </div>
                @endif

            @endif
        </div>
    </div>
    @endif
</div>
