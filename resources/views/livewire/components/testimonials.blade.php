<div x-data="{ open: false, rating: @js($rating) }">
    {{-- Style inline: public/build gitignored, jadi CSS di file .css tak ikut ter-deploy. --}}
    <style>
        .tm-ok{display:flex;align-items:center;gap:.4rem;margin-top:.4rem;font-size:.82rem;color:#0f7b4a;font-weight:600;line-height:1.35}
        .tm-ok i{font-size:.95rem;flex:0 0 auto}
        .tm-anon{display:flex;align-items:flex-start;gap:.55rem;cursor:pointer;font-size:.86rem;color:#4b4640;line-height:1.4;margin:0}
        .tm-anon input[type=checkbox]{width:1.05rem;height:1.05rem;margin-top:.12rem;flex:0 0 auto;accent-color:#f59e0b;cursor:pointer}
        /* Popup "Tulis Testimoni" bisa di-scroll bila kontennya lebih tinggi dari
           layar (mis. di HP). Scoped ke modal testimoni saja; tutup tetap bisa
           via tombol X, klik area luar (backdrop), atau tombol Escape. */
        .fs-modal.tm-form-modal{max-height:90vh;max-height:90dvh;overflow-y:auto;-webkit-overflow-scrolling:touch}

        /* ===== IKON BENAR-BENAR DI TENGAH =====

           Glif Bootstrap Icons dibungkus baris teks dengan tinggi baris
           bawaannya sendiri, jadi di dalam wadah bulat atau kotak ia selalu
           duduk sedikit di bawah pusat. Selisihnya beberapa piksel — cukup
           untuk membuat lencana terlihat miring tanpa bisa ditunjuk sebabnya.

           Tinggi barisnya dinolkan DAN ::before dijadikan block: yang kedua
           perlu karena tanpa itu glifnya masih membawa ruang bawah dari
           metrik fontnya sendiri. */
        .tm-verified i.bi,
        .tm-avatar i.bi,
        .tm-thanks-ic i.bi,
        .tm-tulis i.bi,
        .tm-empty i.bi { display: block; line-height: 1; }
        .tm-verified i.bi::before,
        .tm-avatar i.bi::before,
        .tm-thanks-ic i.bi::before,
        .tm-tulis i.bi::before,
        .tm-empty i.bi::before { display: block; line-height: 1; }

        /* Lencana "Pembeli Asli" dijajarkan ke tengah dan ikonnya diberi ubin
           sendiri, bukan glif telanjang yang menempel pada teks. */
        .tm-verified i.bi {
            width: 26px; height: 26px; border-radius: 8px;
            display: flex !important; align-items: center; justify-content: center;
            background: rgba(15, 123, 63, .12); font-size: 1rem;
        }

        /* Tombol tulis testimoni: setenang tautan "Lihat Semua" di bagian lain,
           bukan tombol penuh. Ia ajakan sekunder — yang utama di halaman ini
           tetap membaca testimoninya. */
        .tm-tulis {
            display: inline-flex; align-items: center; gap: 8px; flex: 0 0 auto;
            background: #fff; border: 1px solid #eceff4; border-radius: 12px;
            padding: 10px 18px; cursor: pointer; white-space: nowrap;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 700; font-size: .88rem; color: #4b5563;
            transition: border-color .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .tm-tulis:hover {
            border-color: #f26522; color: #f26522;
            box-shadow: 0 8px 20px rgba(242, 101, 34, .12);
        }
        .tm-tulis i.bi { font-size: .95rem; }

        /* Kartu testimoni disamakan dengan kartu lain di beranda: angkat 3px,
           bayangan lembut. Lompatan 6px dengan bayangan pekat membuat kartu
           ini satu-satunya yang berperilaku berbeda di seluruh halaman. */
        .tm-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(242, 101, 34, .12);
            border-color: #f7c9ae;
        }

        @media (max-width: 767.98px) {
            .tm-tulis { width: 100%; justify-content: center; }
        }
    </style>
    <section id="testimoni" class="tm-section section">
        <div class="container">
            {{-- Tombol "Tulis Testimoni" masuk ke SISI KANAN kepala bagian,
                 sejajar judulnya — tempat yang sama dengan "Lihat Semua Produk"
                 dan "Lihat Semua Kategori" di bagian-bagian lain.

                 Sebelumnya ia berdiri sendiri di bawah kepala dengan margin
                 negatif, dan satu-satunya ajakan di halaman ini yang letaknya
                 berbeda dari semua ajakan lain akan terbaca sebagai tempelan,
                 bukan sebagai bagian dari susunannya. --}}
            <x-kepala-bagian
                ikon="bi-chat-quote-fill"
                kicker="Testimoni"
                judul="Apa Kata Pelanggan Kami"
                sub="Cerita nyata dari mereka yang sudah merasakan layanan Phoenix Digital.">
                <x-slot:aksi>
                    <button type="button" class="tm-tulis"
                        @click="open = true; rating = 5; $wire.set('submitted', false, false); $wire.set('rating', 5, false)">
                        <i class="bi bi-pencil-square"></i> Tulis Testimoni
                    </button>
                </x-slot:aksi>
            </x-kepala-bagian>

            @if ($testimonials->isNotEmpty())
                <div class="phoenix-tm-swiper swiper" wire:ignore>
                    <div class="swiper-wrapper">
                        @foreach ($testimonials as $t)
                            <div class="swiper-slide">
                                <div class="tm-card">
                                    <i class="bi bi-quote tm-quote"></i>
                                    <div class="tm-stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= (int) $t->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="tm-text">{{ $t->pesan }}</p>
                                    <div class="tm-person">
                                        {{-- WAJIB nama_publik, JANGAN $t->nama: kolom nama kini berisi
                                             nama ASLI, dan pengirim anonim hanya boleh tampil sebagai
                                             huruf depan (mis. "B•••") di halaman publik. --}}
                                        <span class="tm-avatar">
                                            @if ($t->foto && \Storage::disk('public')->exists('img/testimoni/' . $t->foto))
                                                <img src="{{ asset('storage/img/testimoni/' . $t->foto) }}" alt="{{ $t->nama_publik }}">
                                            @else
                                                {{ strtoupper(mb_substr($t->nama_publik, 0, 1)) }}
                                            @endif
                                        </span>
                                        <span class="tm-meta">
                                            <span class="tm-name">{{ $t->nama_publik }}</span>
                                            @if ($t->peran)
                                                <span class="tm-role">{{ $t->peran }}</span>
                                            @endif

                                            {{-- Label pembeli sungguhan. Sengaja kalimat utuh & baris
                                                 sendiri — pembaca kami orang tua, "13×" di samping nama
                                                 terlalu ringkas & tidak jelas artinya. Angkanya dihitung
                                                 hidup dari pesanan 'completed', jadi ikut naik sendiri. --}}
                                            @if ($t->customer_id && ($t->customer->belanja_selesai_count ?? 0) > 0)
                                                <span class="tm-verified" title="Pembeli asli — pesanannya sudah selesai">
                                                    <i class="bi bi-patch-check-fill"></i>
                                                    <span class="tm-verified-txt">
                                                        <b>Pembeli Asli</b>
                                                        <small>Sudah belanja {{ $t->customer->belanja_selesai_count }} kali</small>
                                                    </span>
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination tm-pagination"></div>
                </div>
            @else
                <div class="tm-empty">
                    <i class="bi bi-stars"></i>
                    <p>Belum ada testimoni. Jadilah yang pertama berbagi pengalaman Anda!</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ===== Modal: Tulis Testimoni (pelanggan) — kontrol Alpine, tak re-render slider ===== --}}
    <div class="fs-modal-overlay" x-show="open" x-cloak x-transition.opacity style="display:none"
        @click.self="open = false" @keydown.escape.window="open = false">
        <div class="fs-modal tm-form-modal" x-show="open" x-transition>
            <button type="button" class="fs-modal-close" @click="open = false" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>

            {{-- Layar terima kasih --}}
            <div class="tm-thanks" x-show="$wire.submitted" x-cloak>
                <span class="tm-thanks-ic"><i class="bi bi-check-lg"></i></span>
                <h4>Terima kasih! 🎉</h4>
                {{-- Dua pesan, dipilih Alpine dari $wire.terverifikasi — layar ini
                     memakai x-show (bukan render ulang Livewire), jadi cabangnya
                     harus di sisi Alpine juga. --}}
                <p x-show="$wire.terverifikasi" x-cloak>
                    Testimoni Anda berhasil dikirim dan akan <b>tampil setelah disetujui admin</b>.
                    Karena pesanan Anda sudah selesai, begitu disetujui Anda <b>otomatis jadi Member</b> —
                    mulai kumpulkan poin untuk potongan belanja berikutnya. 🎁
                </p>
                <p x-show="!$wire.terverifikasi">
                    Testimoni Anda berhasil dikirim dan akan <b>tampil setelah disetujui admin</b>.
                </p>
                <button type="button" class="ph-empty-btn" @click="open = false">Selesai</button>
            </div>

            {{-- Form --}}
            <div x-show="!$wire.submitted">
                <div class="fs-modal-head">
                    <div class="fs-modal-title">
                        <span class="fs-modal-eyebrow"><i class="bi bi-chat-heart-fill"></i> Testimoni</span>
                        <h4>Bagikan pengalaman Anda</h4>
                        <p>Ceritakan pengalaman Anda memakai layanan Phoenix Digital.</p>
                    </div>
                </div>

                <form wire:submit.prevent="submit" class="tm-form">
                    {{-- Nomor didahulukan: begitu diisi, nama pelanggan terdaftar
                         terisi otomatis (updatedNoHp). --}}
                    <div class="tm-form-row">
                        <label>No. WhatsApp <span class="req">*</span></label>
                        <input type="tel" inputmode="numeric" wire:model.blur="no_hp" class="form-control"
                            placeholder="08xxxxxxxxxx" maxlength="20"
                            wire:loading.attr="disabled" wire:target="no_hp">
                        @error('no_hp') <span class="tm-err">{{ $message }}</span> @enderror
                        {{-- Jaminan yang SEMUANYA benar. Sengaja TIDAK menulis "terenkripsi":
                             no_hp tersimpan apa adanya di database, jadi klaim itu bohong. --}}
                        <div class="tm-privacy">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span><b>Nomormu aman.</b> Tidak ditampilkan di testimoni &amp; tidak dibagikan ke
                                siapa pun — hanya untuk mencocokkan pesananmu. Kalau pesananmu sudah selesai,
                                testimoni ini bikin kamu <b>otomatis jadi Member</b>. 🎁</span>
                        </div>
                    </div>

                    <div class="tm-form-row">
                        <label>Nama <span class="req">*</span></label>
                        <input type="text" wire:model.defer="nama" class="form-control" placeholder="Nama Anda">
                        @if ($nomorDikenali)
                            <span class="tm-ok"><i class="bi bi-check-circle-fill"></i> Nomor dikenali — nama terisi otomatis.</span>
                        @endif
                        @error('nama') <span class="tm-err">{{ $message }}</span> @enderror
                    </div>

                    <div class="tm-form-row">
                        <label>Peran / Jabatan <span class="opt">(opsional)</span></label>
                        <input type="text" wire:model.defer="peran" class="form-control"
                            placeholder="Contoh: Mahasiswa / Peneliti">
                        @error('peran') <span class="tm-err">{{ $message }}</span> @enderror
                    </div>

                    <div class="tm-form-row">
                        <label>Rating <span class="req">*</span></label>
                        <div class="tm-rate-input">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" class="tm-rate-star" :class="rating >= {{ $i }} ? 'is-on' : ''"
                                    @click="rating = {{ $i }}; $wire.set('rating', {{ $i }}, false)"
                                    aria-label="Beri {{ $i }} bintang">
                                    <i class="bi" :class="rating >= {{ $i }} ? 'bi-star-fill' : 'bi-star'"></i>
                                </button>
                            @endfor
                            <span class="tm-rate-val" x-text="rating + '/5'"></span>
                        </div>
                        @error('rating') <span class="tm-err">{{ $message }}</span> @enderror
                    </div>

                    <div class="tm-form-row">
                        <label>Pesan <span class="req">*</span></label>
                        <textarea wire:model.defer="pesan" rows="4" class="form-control" maxlength="500"
                            placeholder="Tuliskan testimoni Anda di sini..."></textarea>
                        @error('pesan') <span class="tm-err">{{ $message }}</span> @enderror
                    </div>

                    {{-- Opsi anonim: hanya huruf depan nama yang tampil di testimoni. --}}
                    <div class="tm-form-row">
                        <label class="tm-anon">
                            <input type="checkbox" wire:model.live="anonim">
                            <span>Kirim sebagai <b>anonim</b> — di testimoni hanya <b>huruf depan</b> nama Anda yang tampil (mis. <b>B•••</b>).</span>
                        </label>
                        @if ($anonim)
                            <span class="tm-ok"><i class="bi bi-incognito"></i> Hanya huruf depan nama Anda yang tampil. Nomor tetap tidak pernah ditampilkan.</span>
                        @endif
                    </div>

                    <button type="submit" class="ph-empty-btn w-100 justify-content-center" wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit"><i class="bi bi-send"></i> Kirim Testimoni</span>
                        <span wire:loading wire:target="submit"><span class="spinner-border spinner-border-sm"></span> Mengirim...</span>
                    </button>
                    <p class="tm-form-note"><i class="bi bi-info-circle"></i> Testimoni tampil setelah disetujui admin.</p>
                </form>
            </div>
        </div>
    </div>
</div>
