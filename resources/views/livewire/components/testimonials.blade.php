<div x-data="{ open: false, rating: @js($rating) }">
    <section id="testimoni" class="tm-section section">
        <div class="container">
            <div class="ph-sec-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-chat-quote-fill"></i> Testimoni</span>
                <h2 class="ph-sec-title">Apa kata pelanggan kami</h2>
                <p class="ph-sec-sub">Cerita nyata dari mereka yang sudah merasakan layanan Phoenix Digital.</p>
                <button type="button" class="ph-empty-btn tm-share-btn"
                    @click="open = true; rating = 5; $wire.set('submitted', false, false); $wire.set('rating', 5, false)">
                    <i class="bi bi-pencil-square"></i> Tulis Testimoni
                </button>
            </div>

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
                                        <span class="tm-avatar">
                                            @if ($t->foto && \Storage::disk('public')->exists('img/testimoni/' . $t->foto))
                                                <img src="{{ asset('storage/img/testimoni/' . $t->foto) }}" alt="{{ $t->nama }}">
                                            @else
                                                {{ strtoupper(mb_substr($t->nama, 0, 1)) }}
                                            @endif
                                        </span>
                                        <span class="tm-meta">
                                            <span class="tm-name">{{ $t->nama }}</span>
                                            @if ($t->peran)
                                                <span class="tm-role">{{ $t->peran }}</span>
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
                <p>Testimoni Anda berhasil dikirim dan akan <b>tampil setelah disetujui admin</b>.</p>
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
                    <div class="tm-form-row">
                        <label>Nama <span class="req">*</span></label>
                        <input type="text" wire:model.defer="nama" class="form-control" placeholder="Nama Anda">
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
