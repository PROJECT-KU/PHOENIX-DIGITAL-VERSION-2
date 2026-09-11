@php
    // Bintang penuh / setengah / kosong. Rata-rata 4,3 tampil sebagai empat
    // setengah bintang, bukan dibulatkan jadi empat — pembulatan menyembunyikan
    // perbedaan yang justru dicari pembeli saat membandingkan.
    $kelasBintang = fn ($nilai, $i) => $nilai >= $i - 0.25 ? 'bi-star-fill' : ($nilai >= $i - 0.75 ? 'bi-star-half' : 'bi-star');

    // Tiap pengulas mendapat warnanya sendiri, diturunkan dari namanya supaya
    // tetap sama di setiap kunjungan. Avatar jingga yang identik berjajar
    // terbaca sebagai satu tekstur, bukan sebagai beberapa orang berbeda.
    $palet = ['#7c3aed', '#2563eb', '#0d9488', '#db2777', '#d97706', '#4f46e5', '#16a34a'];
    $warnaPengulas = fn ($nama) => $palet[crc32(mb_strtolower(trim((string) $nama))) % count($palet)];
@endphp

<div class="ul" x-data="{ rating: @entangle('rating'), showForm: false }">
    <style>
        /* Inline: public/build masuk .gitignore dan tidak ikut terdeploy.
           Prefiks ul-, bukan rev- lama: aturan .rev-* di public-custom-styles.css
           di server sudah beku dan akan terus menempel ke kelas lama. */
        .ul { --uc: var(--c, #f26522); }
        .ul [x-cloak] { display: none !important; }
        .ul i.bi { line-height: 1; }
        .ul i.bi::before { display: block; line-height: 1; }

        /* Ubin ikon: glif sendirian di tengah kotak berwarna. */
        .ul-ubin {
            flex: 0 0 auto; display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--uc) 12%, #fff); color: var(--uc);
        }

        /* Sapuan warna di pojok kartu — pola yang sama dengan Cara Pesan dan
           Kategori Populer, jadi bagian ini terasa satu keluarga dengan beranda. */
        .ul-ringkas, .ul-kosong { position: relative; overflow: hidden; }
        .ul-ringkas::before, .ul-kosong::before {
            content: ""; position: absolute; top: -46px; right: -46px;
            width: 132px; height: 132px; border-radius: 50%;
            background: color-mix(in srgb, var(--uc) 11%, transparent);
        }
        .ul-ringkas > *, .ul-kosong > * { position: relative; }

        /* ===== Ringkasan + daftar ===== */
        .ul-grid {
            display: grid; grid-template-columns: 320px minmax(0, 1fr);
            gap: 22px; align-items: start;
        }
        .ul-ringkas {
            background: #fff; border: 1px solid #eceff4; border-radius: 18px; padding: 24px;
        }
        .ul-skor { display: flex; align-items: center; gap: 16px; }
        .ul-angka {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
            font-size: 3.1rem; line-height: 1; letter-spacing: -.045em; color: #1c1f26;
        }
        .ul-angka small { font-size: 1rem; font-weight: 700; letter-spacing: 0; color: #9aa2ae; margin-left: 2px; }
        .ul-bintang { display: inline-flex; align-items: center; gap: 3px; color: #f59e0b; font-size: 1rem; }
        .ul-jumlah { display: block; margin-top: 7px; font-size: .82rem; color: #6b7280; }

        /* Sebaran 5→1. Satu angka rata-rata tidak memberi tahu apakah semua
           orang puas, atau separuh sangat puas dan separuh kecewa. */
        .ul-sebaran { list-style: none; padding: 0; margin: 22px 0 0; display: grid; gap: 9px; }
        .ul-sebaran li {
            display: grid; grid-template-columns: 30px minmax(0, 1fr) 20px;
            align-items: center; gap: 10px; font-size: .8rem; color: #6b7280;
        }
        .ul-lbl { display: inline-flex; align-items: center; gap: 4px; font-weight: 700; color: #374151; }
        .ul-lbl i.bi { color: #f59e0b; font-size: .72rem; }
        .ul-bar { height: 8px; border-radius: 99px; background: #f1f3f6; overflow: hidden; }
        .ul-bar span {
            display: block; height: 100%; width: var(--w, 0%); border-radius: inherit;
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
        }
        .ul-n { text-align: right; font-variant-numeric: tabular-nums; }

        /* Keterangan yang BENAR: ulasan memang ditahan sampai disetujui admin.
           Tidak ada klaim "pembeli terverifikasi" — formulir ini terbuka untuk
           siapa saja, dan klaim yang tidak bisa dibuktikan menggerus
           kepercayaan yang justru hendak dibangun. */
        .ul-tinjau {
            display: flex; align-items: center; gap: 10px;
            margin-top: 20px; padding-top: 16px; border-top: 1px dashed #e8ebf0;
            font-size: .8rem; line-height: 1.5; color: #6b7280;
        }
        .ul-tinjau .ul-ubin { width: 30px; height: 30px; border-radius: 9px; font-size: .9rem; }

        .ul-daftar { display: grid; gap: 14px; }
        .ul-kartu {
            --a: #f26522;
            position: relative; overflow: hidden;
            background: #fff; border: 1px solid #eceff4; border-radius: 16px; padding: 20px 22px;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .ul-kartu:hover {
            border-color: color-mix(in srgb, var(--a) 30%, #fff);
            box-shadow: 0 14px 28px -20px color-mix(in srgb, var(--a) 70%, transparent);
        }
        .ul-kutip {
            position: absolute; top: 14px; right: 18px;
            color: color-mix(in srgb, var(--a) 16%, #fff); font-size: 2.3rem;
        }
        .ul-kepala { display: flex; align-items: center; gap: 12px; padding-right: 44px; min-width: 0; }
        .ul-avatar {
            flex: 0 0 auto; width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--a) 14%, #fff); color: var(--a);
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
            font-size: 1.05rem; line-height: 1;
            /* Huruf kapital duduk sedikit di atas tengah kotak barisnya
               (diukur 0,66px); 1px di atas menurunkannya ke tengah bentuknya. */
            padding-top: 1px;
        }
        .ul-siapa { min-width: 0; }
        .ul-nama {
            display: block; font-weight: 700; font-size: .95rem; line-height: 1.3; color: #1c1f26;
            overflow-wrap: anywhere;
        }
        .ul-meta {
            display: flex; align-items: center; flex-wrap: wrap; gap: 4px 8px;
            margin-top: 4px; font-size: .78rem; color: #9aa2ae;
        }
        .ul-meta .ul-bintang { font-size: .78rem; gap: 2px; }
        .ul-titik { width: 3px; height: 3px; border-radius: 50%; background: #d1d5db; }
        .ul-teks {
            margin: 14px 0 0; font-size: .93rem; line-height: 1.7; color: #374151;
            white-space: pre-line; overflow-wrap: anywhere;
        }

        /* ===== Belum ada ulasan ===== */
        .ul-kosong {
            display: flex; align-items: center; gap: 20px;
            background: #fff; border: 1px solid #eceff4; border-radius: 18px; padding: 26px 28px;
        }
        .ul-kosong > .ul-ubin { width: 60px; height: 60px; border-radius: 18px; font-size: 1.6rem; }
        .ul-kosong-teks { flex: 1 1 auto; min-width: 0; }
        .ul-kosong-teks h3 {
            margin: 0; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 800; font-size: 1.1rem; line-height: 1.3; color: #1c1f26;
        }
        .ul-kosong-teks p { margin: 5px 0 0; font-size: .9rem; line-height: 1.6; color: #6b7280; }

        /* ===== Tombol ===== */
        .ul-tombol {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            height: 44px; padding: 0 20px; border: 0; border-radius: 12px; cursor: pointer;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-weight: 700; font-size: .9rem; white-space: nowrap; text-decoration: none;
            box-shadow: 0 8px 18px -8px rgba(242, 101, 34, .55);
            transition: filter .16s ease, transform .16s ease;
        }
        .ul-tombol:hover { filter: brightness(1.05); transform: translateY(-1px); }
        .ul-tombol:disabled { opacity: .7; cursor: wait; transform: none; }
        .ul-tombol-garis {
            background: #fff; color: #374151; border: 1.5px solid #e5e7eb; box-shadow: none;
        }
        .ul-tombol-garis:hover { border-color: var(--uc); color: var(--uc); filter: none; }

        /* ===== Formulir ===== */
        .ul-form {
            margin-top: 18px; background: #fff; border: 1px solid #eceff4; border-radius: 18px;
            padding: 24px; scroll-margin-top: 110px;
        }
        .ul-form-kepala { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .ul-form-kepala .ul-ubin { width: 42px; height: 42px; border-radius: 12px; font-size: 1.1rem; }
        .ul-form-kepala h4 {
            margin: 0; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 800; font-size: 1.05rem; color: #1c1f26;
        }
        .ul-form-kepala p { margin: 2px 0 0; font-size: .82rem; color: #6b7280; }
        .ul-baris { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .ul-field { margin-bottom: 16px; min-width: 0; }
        .ul-field > label {
            display: block; margin-bottom: 7px; font-size: .82rem; font-weight: 700; color: #374151;
        }
        .ul-field .form-control {
            border: 1px solid #e5e7eb; border-radius: 11px; padding: 11px 14px;
            font-size: .92rem; box-shadow: none;
        }
        .ul-field .form-control:focus {
            border-color: var(--uc);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--uc) 16%, transparent);
        }
        .ul-field textarea.form-control { min-height: 120px; resize: vertical; }

        /* Bintang masukan setinggi kolom nama di sebelahnya, supaya kedua
           kolom berbaris lurus. */
        .ul-rate { display: flex; align-items: center; gap: 2px; min-height: 46px; flex-wrap: wrap; }
        .ul-rate button {
            width: 38px; height: 38px; padding: 0; border: 0; border-radius: 10px; background: transparent;
            display: flex; align-items: center; justify-content: center;
            color: #d1d5db; font-size: 1.4rem; cursor: pointer;
            transition: transform .14s ease, color .14s ease;
        }
        .ul-rate button:hover { transform: scale(1.12); }
        .ul-rate button.is-on { color: #f59e0b; }
        .ul-rate button:focus-visible { outline: 2px solid var(--uc); outline-offset: 1px; }
        .ul-rate-lbl {
            margin-left: 8px; padding: 4px 11px; border-radius: 99px;
            background: #fff7e6; color: #b45309; font-size: .78rem; font-weight: 700; white-space: nowrap;
        }
        .ul-hitung { display: block; margin-top: 6px; text-align: right; font-size: .74rem; color: #9aa2ae; }
        .ul-err { display: block; margin-top: 6px; font-size: .78rem; color: #e11d48; }
        .ul-kaki {
            display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
            margin-top: 4px; padding-top: 16px; border-top: 1px solid #f2f4f7;
        }
        .ul-catatan { display: inline-flex; align-items: center; gap: 7px; margin: 0; font-size: .78rem; color: #9aa2ae; }
        .ul-aksi { display: flex; gap: 10px; flex-wrap: wrap; }

        /* ===== Terima kasih ===== */
        .ul-terima {
            display: flex; align-items: center; gap: 16px; margin-top: 18px;
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 16px; padding: 18px 20px;
        }
        .ul-terima-ic {
            flex: 0 0 auto; width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: #16a34a; color: #fff; font-size: 1.2rem;
        }
        .ul-terima-teks { flex: 1 1 auto; min-width: 0; }
        .ul-terima-teks b { display: block; color: #14532d; font-size: .95rem; }
        .ul-terima-teks p { margin: 2px 0 0; font-size: .85rem; line-height: 1.55; color: #166534; }

        @media (max-width: 991.98px) {
            .ul-grid { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 575.98px) {
            .ul-kosong { flex-direction: column; text-align: center; padding: 24px 20px; gap: 14px; }
            .ul-kosong .ul-tombol { width: 100%; }
            .ul-baris { grid-template-columns: minmax(0, 1fr); gap: 0; }
            .ul-form, .ul-ringkas { padding: 20px 18px; }
            .ul-kartu { padding: 18px; }
            .ul-kaki { flex-direction: column-reverse; align-items: stretch; }
            .ul-aksi .ul-tombol { flex: 1 1 0; }
            .ul-terima { flex-wrap: wrap; }
            .ul-terima .ul-tombol { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ul-kartu, .ul-tombol, .ul-rate button { transition: none; }
        }
    </style>

    <x-kepala-bagian
        ikon="bi-star-fill"
        kicker="Ulasan"
        judul="Ulasan Pelanggan"
        :sub="$count > 0 ? 'Pengalaman pembeli yang sudah memakai produk ini.' : 'Pengalamanmu membantu pembeli lain memilih dengan yakin.'">
        @if ($count > 0 && ! $submitted)
            <x-slot:aksi>
                <button type="button" class="ul-tombol" x-show="!showForm"
                    @click="showForm = true; $nextTick(() => $refs.form.scrollIntoView({ behavior: 'smooth', block: 'nearest' }))">
                    <i class="bi bi-pencil-square"></i> Tulis Ulasan
                </button>
            </x-slot:aksi>
        @endif
    </x-kepala-bagian>

    @if ($count > 0)
        <div class="ul-grid">
            <aside class="ul-ringkas" aria-label="Ringkasan rating">
                <div class="ul-skor">
                    <span class="ul-angka">{{ number_format($avg, 1, ',', '') }}<small>/5</small></span>
                    <div>
                        <span class="ul-bintang" aria-label="Rating {{ number_format($avg, 1, ',', '') }} dari 5">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $kelasBintang($avg, $i) }}"></i>
                            @endfor
                        </span>
                        <span class="ul-jumlah">dari {{ $count }} ulasan</span>
                    </div>
                </div>

                <ul class="ul-sebaran">
                    @for ($b = 5; $b >= 1; $b--)
                        @php $n = $sebaran[$b] ?? 0; @endphp
                        <li>
                            <span class="ul-lbl">{{ $b }} <i class="bi bi-star-fill"></i></span>
                            <span class="ul-bar"><span style="--w: {{ round($n / $count * 100) }}%"></span></span>
                            <span class="ul-n">{{ $n }}</span>
                        </li>
                    @endfor
                </ul>

                <p class="ul-tinjau">
                    <span class="ul-ubin"><i class="bi bi-shield-check"></i></span>
                    <span>Setiap ulasan ditinjau admin sebelum ditampilkan.</span>
                </p>
            </aside>

            <div class="ul-daftar">
                @foreach ($reviews as $r)
                    <article class="ul-kartu" style="--a: {{ $warnaPengulas($r->nama) }}">
                        <i class="bi bi-quote ul-kutip" aria-hidden="true"></i>
                        <div class="ul-kepala">
                            <span class="ul-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim($r->nama), 0, 1)) }}</span>
                            <div class="ul-siapa">
                                <span class="ul-nama">{{ $r->nama }}</span>
                                <span class="ul-meta">
                                    <span class="ul-bintang" aria-label="{{ $r->rating }} dari 5 bintang">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $r->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </span>
                                    <span class="ul-titik" aria-hidden="true"></span>
                                    {{-- locale('id') karena APP_LOCALE=en: tanpanya bulan tercetak "Aug", bukan "Agu". --}}
                                    <time datetime="{{ $r->created_at?->toDateString() }}">{{ $r->created_at?->locale('id')->translatedFormat('j M Y') }}</time>
                                </span>
                            </div>
                        </div>
                        <p class="ul-teks">{{ $r->ulasan }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    @elseif (! $submitted)
        <div class="ul-kosong">
            <span class="ul-ubin"><i class="bi bi-chat-heart"></i></span>
            <div class="ul-kosong-teks">
                <h3>Belum ada ulasan</h3>
                <p>Sudah memakai produk ini? Ceritakan pengalamanmu — cukup satu menit.</p>
            </div>
            <button type="button" class="ul-tombol" x-show="!showForm"
                @click="showForm = true; $nextTick(() => $refs.form.scrollIntoView({ behavior: 'smooth', block: 'nearest' }))">
                <i class="bi bi-pencil-square"></i> Tulis Ulasan Pertama
            </button>
        </div>
    @endif

    @if ($submitted)
        <div class="ul-terima">
            <span class="ul-terima-ic"><i class="bi bi-check-lg"></i></span>
            <div class="ul-terima-teks">
                <b>Terima kasih atas ulasanmu!</b>
                <p>Ulasan akan tampil di sini setelah disetujui admin.</p>
            </div>
            <button type="button" class="ul-tombol ul-tombol-garis" wire:click="$set('submitted', false)">Tulis lagi</button>
        </div>
    @else
        <form wire:submit="submit" class="ul-form" x-ref="form" x-show="showForm" x-cloak>
            <div class="ul-form-kepala">
                <span class="ul-ubin"><i class="bi bi-pencil-square"></i></span>
                <div>
                    <h4>Bagikan pengalamanmu</h4>
                    <p>Ulasan yang jujur paling membantu pembeli berikutnya.</p>
                </div>
            </div>

            <div class="ul-baris">
                <div class="ul-field">
                    <label for="ul-nama">Nama</label>
                    <input id="ul-nama" type="text" class="form-control" wire:model="nama" placeholder="Nama kamu" maxlength="60" autocomplete="name">
                    @error('nama') <span class="ul-err">{{ $message }}</span> @enderror
                </div>
                <div class="ul-field">
                    <label id="ul-rating-lbl">Rating</label>
                    <div class="ul-rate" role="radiogroup" aria-labelledby="ul-rating-lbl">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" role="radio" @click="rating = {{ $i }}"
                                :class="rating >= {{ $i }} ? 'is-on' : ''" :aria-checked="rating === {{ $i }}"
                                aria-label="Beri {{ $i }} bintang">
                                <i class="bi" :class="rating >= {{ $i }} ? 'bi-star-fill' : 'bi-star'"></i>
                            </button>
                        @endfor
                        <span class="ul-rate-lbl" x-text="['', 'Sangat kurang', 'Kurang', 'Cukup', 'Bagus', 'Sangat puas'][rating] || ''"></span>
                    </div>
                    @error('rating') <span class="ul-err">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="ul-field" x-data="{ n: {{ mb_strlen((string) $ulasan) }} }">
                <label for="ul-ulasan">Ulasan</label>
                <textarea id="ul-ulasan" class="form-control" wire:model="ulasan" rows="4" maxlength="500"
                    @input="n = $event.target.value.length"
                    placeholder="Apa yang kamu suka? Bagaimana proses pengirimannya?"></textarea>
                <span class="ul-hitung"><span x-text="n"></span>/500</span>
                @error('ulasan') <span class="ul-err">{{ $message }}</span> @enderror
            </div>

            <div class="ul-kaki">
                <p class="ul-catatan"><i class="bi bi-info-circle"></i> Tampil setelah disetujui admin.</p>
                <div class="ul-aksi">
                    <button type="button" class="ul-tombol ul-tombol-garis" @click="showForm = false">Batal</button>
                    <button type="submit" class="ul-tombol" wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit"><i class="bi bi-send"></i></span>
                        <span wire:loading wire:target="submit"><span class="spinner-border spinner-border-sm"></span></span>
                        Kirim Ulasan
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
