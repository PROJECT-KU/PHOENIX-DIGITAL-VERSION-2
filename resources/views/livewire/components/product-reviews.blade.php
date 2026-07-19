<div class="rev-wrap" x-data="{ rating: @entangle('rating'), showForm: false }">
    {{-- Header + ringkasan --}}
    <div class="rev-top">
        <div>
            <span class="ph-sec-eyebrow"><i class="bi bi-star-fill"></i> Ulasan</span>
            <h2 class="rev-title">Ulasan Pelanggan</h2>
        </div>
        @if ($count > 0)
            <div class="rev-scorecard">
                <div class="rev-score">{{ number_format($avg, 1) }}</div>
                <div>
                    <div class="rev-stars">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= round($avg) ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </div>
                    <span class="rev-count">dari {{ $count }} ulasan</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Daftar ulasan --}}
    @if ($count > 0)
        <div class="rev-list">
            @foreach ($reviews as $r)
                <div class="rev-item">
                    <span class="rev-avatar">{{ strtoupper(mb_substr($r->nama, 0, 1)) }}</span>
                    <div class="rev-item-body">
                        <div class="rev-item-head">
                            <span class="rev-item-name">{{ $r->nama }}</span>
                            <span class="rev-item-date">{{ $r->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                        <span class="rev-item-stars">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $r->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </span>
                        <p class="rev-item-text">{{ $r->ulasan }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rev-empty">
            <i class="bi bi-chat-heart"></i>
            <p>Belum ada ulasan untuk produk ini. Jadilah yang pertama berbagi pengalaman!</p>
        </div>
    @endif

    {{-- Form ulasan --}}
    @if ($submitted)
        <div class="rev-thanks">
            <span class="rev-thanks-ic"><i class="bi bi-check-lg"></i></span>
            <div>
                <b>Terima kasih! 🎉</b>
                <p>Ulasan Anda terkirim &amp; akan <b>tampil setelah disetujui admin</b>.</p>
            </div>
            <button type="button" class="rev-again" wire:click="$set('submitted', false)">Tulis lagi</button>
        </div>
    @else
        <div class="rev-cta">
            <button type="button" class="rev-open-btn" x-show="!showForm" @click="showForm = true">
                <i class="bi bi-pencil-square"></i> Tulis Ulasan
            </button>

            <form wire:submit="submit" class="rev-form" x-show="showForm" x-cloak>
                <h4>Bagikan pengalaman Anda</h4>
                <div class="rev-field">
                    <label>Nama</label>
                    <input type="text" class="form-control" wire:model="nama" placeholder="Nama Anda" maxlength="60">
                    @error('nama') <span class="co-err">{{ $message }}</span> @enderror
                </div>
                <div class="rev-field">
                    <label>Rating</label>
                    <div class="rev-rate-input">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" @click="rating = {{ $i }}"
                                :class="rating >= {{ $i }} ? 'is-on' : ''" aria-label="Beri {{ $i }} bintang">
                                <i class="bi" :class="rating >= {{ $i }} ? 'bi-star-fill' : 'bi-star'"></i>
                            </button>
                        @endfor
                        <span class="rev-rate-val" x-text="rating + '/5'"></span>
                    </div>
                    @error('rating') <span class="co-err">{{ $message }}</span> @enderror
                </div>
                <div class="rev-field">
                    <label>Ulasan</label>
                    <textarea class="form-control" wire:model="ulasan" rows="3" maxlength="500" placeholder="Ceritakan pengalaman Anda memakai produk ini..."></textarea>
                    @error('ulasan') <span class="co-err">{{ $message }}</span> @enderror
                </div>
                <div class="rev-form-actions">
                    <button type="button" class="rev-cancel" @click="showForm = false">Batal</button>
                    <button type="submit" class="co-btn co-btn-primary" wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit"><i class="bi bi-send"></i> Kirim Ulasan</span>
                        <span wire:loading wire:target="submit"><span class="spinner-border spinner-border-sm"></span> Mengirim...</span>
                    </button>
                </div>
                <p class="rev-note"><i class="bi bi-info-circle"></i> Ulasan tampil setelah disetujui admin.</p>
            </form>
        </div>
    @endif
</div>
