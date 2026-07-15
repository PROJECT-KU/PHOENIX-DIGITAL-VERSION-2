<div>
    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-bag-check-fill"></i> Checkout</span>
                <h1>Selesaikan Pesanan</h1>
                <p>Lengkapi data & pilih promo Anda, lalu lanjutkan ke pembayaran.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li><a href="{{ route('cart') }}">Keranjang</a></li>
                    <li class="current">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="co-section">
        <div class="container">
            <form wire:submit="checkout">
                <div class="row g-4">
                    {{-- Kolom kiri: data & promo --}}
                    <div class="col-lg-7">
                        {{-- Informasi pelanggan --}}
                        <div class="co-card">
                            <div class="co-card-head"><i class="bi bi-person-fill"></i> Informasi Pelanggan</div>
                            <div class="co-card-body">
                                <div class="co-field">
                                    <label>Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                                    <div wire:ignore>
                                        <input type="tel" id="co-phone" class="form-control" autocomplete="tel"
                                            placeholder="812 3456 789" data-init="{{ $no_hp }}">
                                    </div>
                                    {{-- jembatan nilai E.164 ke Livewire (untuk lookup pelanggan) --}}
                                    <input type="hidden" id="co-phone-e164" wire:model="no_hp">
                                    @if ($isLoadingCustomer)
                                        <span class="co-found" style="color:var(--ph-muted)"><span class="spinner-border spinner-border-sm"></span> Mencari data...</span>
                                    @endif
                                    @error('no_hp') <span class="co-err">{{ $message }}</span> @enderror
                                    @if ($customerFound)
                                        <span class="co-found"><i class="bi bi-check-circle-fill"></i> Data pelanggan ditemukan</span>
                                    @endif
                                    <div class="co-note"><i class="bi bi-globe-americas"></i> Nomor Indonesia cukup ketik <b>08…</b> (otomatis +62). Untuk luar negeri, pilih bendera negaranya.</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="co-field">
                                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                                wire:model="nama" placeholder="Nama lengkap Anda" {{ $customerFound ? 'readonly' : '' }}>
                                            @error('nama') <span class="co-err">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="co-field">
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                wire:model="email" placeholder="email@contoh.com" {{ $customerFound ? 'readonly' : '' }}
                                                x-on:blur="if ($event.target.value.includes('@')) $wire.saveAbandonedCart($event.target.value)">
                                            @error('email') <span class="co-err">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="co-field">
                                    <label>Catatan <span class="co-opt">(opsional)</span></label>
                                    <textarea class="form-control" wire:model="customer_notes" rows="3"
                                        placeholder="Catatan tambahan untuk pesanan Anda"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Kode promo --}}
                        <div class="co-card">
                            <div class="co-card-head"><i class="bi bi-tag-fill"></i> Kode Promo</div>
                            <div class="co-card-body">
                                <div class="row g-2 align-items-start">
                                    <div class="col-7">
                                        <input type="text" class="form-control @error('kodePromo') is-invalid @enderror"
                                            wire:model="kodePromo"
                                            placeholder="{{ $promoBlokirGabung ? 'Tidak bisa digabung' : 'Masukkan kode promo (opsional)' }}"
                                            @if ($promoValid || $promoBlokirGabung) disabled @endif>
                                        @error('kodePromo') <span class="co-err">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-5">
                                        @if ($promoValid)
                                            <button type="button" class="co-btn co-btn-danger" wire:click="removePromo">
                                                <i class="bi bi-trash3-fill"></i> Hapus
                                            </button>
                                        @else
                                            <button type="button" class="co-btn co-btn-primary" wire:click="checkPromo"
                                                wire:loading.attr="disabled" wire:target="checkPromo"
                                                @if ($promoBlokirGabung) disabled @endif>
                                                <span wire:loading.remove wire:target="checkPromo"><i class="bi bi-check-circle-fill"></i> Pakai</span>
                                                <span wire:loading wire:target="checkPromo"><span class="spinner-border spinner-border-sm"></span></span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if ($promoBlokirGabung)
                                    <div class="alert alert-warning py-2 px-3 mb-0 mt-2" style="font-size:.8rem;">
                                        <i class="bi bi-info-circle me-1"></i>Promo <b>{{ $promoBlokirGabung }}</b> yang sedang
                                        aktif tidak bisa digabung dengan promo lain.
                                    </div>
                                @elseif ($promoMessage)
                                    <div class="co-alert {{ $promoValid ? 'co-alert-ok' : 'co-alert-err' }}">{{ $promoMessage }}</div>
                                @endif

                                @if (!empty($appliedPromos))
                                    @php
                                        // Flash Sale/promo otomatis diterapkan per produk, jadi bisa muncul
                                        // berkali-kali. Gabungkan per promo agar rapi: satu kartu, total diskon,
                                        // dan jumlah produk yang terkena.
                                        $groupedPromos = [];
                                        foreach ($appliedPromos as $p) {
                                            $key = $p['promo_id'] ?? ($p['kode_promo'] ?? $p['nama_promo']);
                                            if (!isset($groupedPromos[$key])) {
                                                $groupedPromos[$key] = $p;
                                                $groupedPromos[$key]['items_count'] = 1;
                                            } else {
                                                $groupedPromos[$key]['jumlah_diskon'] += $p['jumlah_diskon'];
                                                $groupedPromos[$key]['items_count']++;
                                            }
                                        }
                                    @endphp
                                    @foreach ($groupedPromos as $promo)
                                        <div class="co-applied">
                                            <div>
                                                <span class="co-applied-name">
                                                    @if (($promo['tipe_promo'] ?? '') === 'flash_sale')
                                                        <i class="bi bi-lightning-charge-fill" style="color:var(--ph-orange)"></i>
                                                    @endif
                                                    {{ $promo['nama_promo'] }}
                                                </span>
                                                @if ($promo['kode_promo'])
                                                    <code class="co-applied-code">{{ $promo['kode_promo'] }}</code>
                                                @endif
                                                @if (($promo['items_count'] ?? 1) > 1)
                                                    <span class="co-applied-count">× {{ $promo['items_count'] }} produk</span>
                                                @endif
                                                <span class="co-applied-sub">
                                                    Diskon
                                                    @if ($promo['tipe_diskon'] === 'persen')
                                                        {{ $promo['nilai_diskon'] }}%{{ ($promo['items_count'] ?? 1) > 1 ? ' / produk' : '' }}
                                                    @else
                                                        Rp {{ number_format($promo['nilai_diskon'], 0, ',', '.') }}{{ ($promo['items_count'] ?? 1) > 1 ? ' / produk' : '' }}
                                                    @endif
                                                </span>
                                            </div>
                                            <span class="co-applied-amt">− Rp {{ number_format($promo['jumlah_diskon'], 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- Kode referral --}}
                        @if ($showReferralInput)
                            <div class="co-card">
                                <div class="co-card-head"><i class="bi bi-people-fill"></i> Kode Referral <span class="co-opt" style="font-weight:600;font-size:.8rem;color:var(--ph-muted)">(opsional)</span></div>
                                <div class="co-card-body">
                                    <div class="row g-2">
                                        <div class="col-7">
                                            <input type="text" class="form-control" wire:model="referralCode"
                                                placeholder="Kode referral" maxlength="9" style="text-transform: uppercase;"
                                                {{ $referralValid ? 'readonly' : '' }}
                                                @if($promoBlokirReferral) disabled @endif>
                                        </div>
                                        <div class="col-5">
                                            @if (!$referralValid)
                                                <button class="co-btn co-btn-outline" type="button" wire:click="checkReferralCode"
                                                    wire:loading.attr="disabled" wire:target="checkReferralCode">
                                                    <span wire:loading.remove wire:target="checkReferralCode"><i class="bi bi-check-circle"></i> Cek</span>
                                                    <span wire:loading wire:target="checkReferralCode"><span class="spinner-border spinner-border-sm"></span></span>
                                                </button>
                                            @else
                                                <button class="co-btn co-btn-primary" type="button" disabled>
                                                    <i class="bi bi-check-circle-fill"></i> Valid
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($referralMessage)
                                        <div class="co-alert {{ $referralValid ? 'co-alert-ok' : 'co-alert-err' }}">
                                            <i class="bi {{ $referralValid ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                            {{ $referralMessage }}
                                        </div>
                                    @endif
                                    @if($promoBlokirReferral)
                                        <div class="alert alert-warning py-2 px-3 mb-0 mt-2" style="font-size:.8rem;">
                                            <i class="bi bi-info-circle me-1"></i>Kode referral tidak bisa dipakai bersama promo
                                            <b>{{ $promoBlokirReferral }}</b> yang sedang aktif.
                                        </div>
                                    @else
                                        <div class="co-note"><i class="bi bi-info-circle"></i> Punya kode referral dari teman? Masukkan untuk keuntungan bersama!</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Ajakan jadi member — hanya untuk yang BELUM member.
                             Kebalikan dari card Poin di bawah, jadi keduanya tidak
                             pernah muncul bersamaan. Pembeli baru (belum ada datanya)
                             juga belum member, jadi ikut melihat ajakan ini. --}}
                        @if (! $foundCustomer || $foundCustomer->status_member !== 'active')
                            <div class="co-card co-member-cta">
                                <div class="co-card-head"><i class="bi bi-stars"></i> Kamu Belum Jadi Member</div>
                                <div class="co-card-body">
                                    <p class="co-member-text">
                                        Sayang banget 😢 — padahal tiap belanja <b>Rp 50.000</b> bisa jadi
                                        <b>1 poin</b>, dan poinnya bikin belanja berikutnya
                                        <b>lebih murah</b>. Gratis, lho!
                                    </p>
                                    <a href="{{ route('member.info') }}" class="co-member-btn">
                                        <i class="bi bi-gift"></i> Lihat Syarat &amp; Keuntungannya
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- Poin member --}}
                        @if ($showPointsOption)
                            <div class="co-card co-points">
                                <div class="co-card-head"><i class="bi bi-star-fill"></i> Poin Member</div>
                                <div class="co-card-body">
                                    <div class="co-points-toggle" @if($promoBlokirPoin) style="opacity:.55;" @endif>
                                        <input class="form-check-input" type="checkbox" id="usePoints" role="switch"
                                            wire:model.live="usePoints" @if($promoBlokirPoin) disabled @endif>
                                        <label class="form-check-label" for="usePoints" style="cursor:{{ $promoBlokirPoin ? 'not-allowed' : 'pointer' }};">
                                            <strong>Gunakan Poin Member</strong>
                                            <div style="font-size:.82rem;color:var(--ph-muted);">
                                                Anda punya <b>{{ number_format($availablePoints, 0, ',', '.') }} poin</b>
                                                (senilai Rp {{ number_format($pointsValue, 0, ',', '.') }})
                                            </div>
                                        </label>
                                    </div>
                                    @if($promoBlokirPoin)
                                        <div class="alert alert-warning py-2 px-3 mb-0 mt-2" style="font-size:.8rem;">
                                            <i class="bi bi-info-circle me-1"></i>Poin tidak bisa dipakai bersama promo
                                            <b>{{ $promoBlokirPoin }}</b> yang sedang aktif.
                                        </div>
                                    @endif
                                    @if ($pointsExpireLabel)
                                        <div class="co-note" style="color:#b45309;"><i class="bi bi-clock-history" style="color:#b45309;"></i> Poin kadaluarsa pada <b>{{ $pointsExpireLabel }}</b></div>
                                    @endif
                                    @if ($usePoints)
                                        <div class="co-alert co-alert-ok" style="margin-top:12px;"><i class="bi bi-info-circle"></i> Poin akan digunakan untuk mengurangi total pembayaran.</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Kolom kanan: ringkasan --}}
                    <div class="col-lg-5">
                        <div class="co-summary">
                            <div class="co-summary-head"><i class="bi bi-receipt"></i> Ringkasan Pesanan</div>
                            <div class="co-summary-body">
                                <div class="co-sum-items">
                                    @foreach ($cart as $item)
                                        <div class="co-sum-item">
                                            <div>
                                                <div class="co-sum-item-name">{{ $item['product_name'] }}</div>
                                                <div class="co-sum-item-dur">
                                                    @if (($item['type'] ?? '') === 'bundling')
                                                        Paket Bundling
                                                    @else
                                                        {{ $item['duration_value'] }} {{ ucfirst($item['duration_type']) }}
                                                    @endif
                                                    &times;{{ $item['quantity'] }}
                                                </div>
                                            </div>
                                            <span class="co-sum-item-price">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="co-sum-row"><span>Subtotal</span><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></div>

                                @if ($uniqueCode > 0)
                                    <div class="co-sum-row">
                                        <span>Kode Unik <i class="bi bi-info-circle" title="Untuk verifikasi & biaya admin"></i></span>
                                        <strong>+ Rp {{ number_format($uniqueCode, 0, ',', '.') }}</strong>
                                    </div>
                                @endif
                                @if ($promoDiscount > 0)
                                    <div class="co-sum-row is-disc"><span>Diskon Promo</span><strong>− Rp {{ number_format($promoDiscount, 0, ',', '.') }}</strong></div>
                                @endif
                                @if ($referralDiscount > 0)
                                    <div class="co-sum-row is-disc"><span>Diskon Referral</span><strong>− Rp {{ number_format($referralDiscount, 0, ',', '.') }}</strong></div>
                                @endif
                                @if ($pointsDiscount > 0)
                                    <div class="co-sum-row is-disc"><span>Diskon Poin</span><strong>− Rp {{ number_format($pointsDiscount, 0, ',', '.') }}</strong></div>
                                @endif

                                @if ($totalDiscount > 0)
                                    <div class="co-sum-hemat"><span>Total Hemat</span><strong>Rp {{ number_format($totalDiscount, 0, ',', '.') }}</strong></div>
                                    <div class="co-sum-old">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
                                @endif

                                <div class="co-sum-total">
                                    <span>Total Pembayaran</span>
                                    <strong>Rp {{ number_format($finalTotal, 0, ',', '.') }}</strong>
                                </div>

                                <button type="button" wire:click="checkout" class="co-place"
                                    wire:loading.attr="disabled" wire:target="checkout">
                                    <span wire:loading.remove wire:target="checkout">
                                        <i class="bi bi-lock-fill"></i>
                                        {{ $finalTotal > 0 ? 'Bayar Sekarang' : 'Selesaikan Pesanan' }}
                                    </span>
                                    @if ($finalTotal > 0)
                                        <span wire:loading.remove wire:target="checkout" class="co-place-price">Rp {{ number_format($finalTotal, 0, ',', '.') }}</span>
                                    @endif
                                    <span wire:loading wire:target="checkout"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
                                </button>

                                <div class="co-note"><i class="bi bi-shield-check"></i> Transaksi aman — Transfer Bank &amp; QRIS.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
        <style>
            .co-card .iti { width: 100%; display: block; }
            .co-card #co-phone { border: 1px solid var(--ph-line); border-radius: 12px; padding: 11px 14px; font-size: .92rem; width: 100%; }
            .co-card #co-phone:focus { border-color: var(--ph-orange); box-shadow: 0 0 0 3px rgba(242, 101, 34, .15); }
            .co-card .iti--separate-dial-code .iti__selected-flag { background-color: var(--ph-soft); border-radius: 11px 0 0 11px; border-right: 1px solid var(--ph-line); padding: 0 10px 0 12px; }
            .co-card .iti__selected-flag:hover { background-color: #ffe6cf; }
            .co-card .iti--separate-dial-code .iti__selected-dial-code { color: var(--ph-ink); font-weight: 700; font-size: .9rem; margin-left: 8px; }
            .co-card .iti__arrow { border-top-color: var(--ph-orange); margin-left: 8px; }
            .co-card .iti__country-list { border: 1px solid var(--ph-line); border-radius: 14px; box-shadow: 0 16px 44px rgba(35, 39, 47, .16); font-size: .9rem; padding: 6px; }
            .co-card .iti__country { padding: 8px 10px; border-radius: 9px; }
            .co-card .iti__country.iti__highlight { background-color: var(--ph-soft); }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
        <script>
            (function () {
                function syncCoPhone() {
                    var input = document.querySelector('#co-phone');
                    var hidden = document.querySelector('#co-phone-e164');
                    if (!input || !hidden) return;
                    var iti = input._iti;
                    var num = '';
                    if (iti) {
                        try { num = iti.getNumber() || ''; } catch (e) {}
                        if (!num) {
                            try {
                                var dc = (iti.getSelectedCountryData() || {}).dialCode || '';
                                var local = (input.value || '').replace(/\D/g, '').replace(/^0+/, '');
                                num = local ? ('+' + dc + local) : '';
                            } catch (e) {}
                        }
                    } else {
                        num = (input.value || '').trim();
                    }
                    hidden.value = num;
                    hidden.dispatchEvent(new Event('input', { bubbles: true }));
                }

                function initCoPhone() {
                    var input = document.querySelector('#co-phone');
                    if (!input || input.dataset.itiInit || typeof window.intlTelInput === 'undefined') return;
                    input.dataset.itiInit = '1';
                    var iti;
                    try {
                        iti = window.intlTelInput(input, {
                            initialCountry: 'id',
                            preferredCountries: ['id', 'my', 'sg', 'us', 'sa', 'ae', 'gb', 'au'],
                            separateDialCode: true,
                            autoPlaceholder: 'aggressive',
                            utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js'
                        });
                    } catch (e) { return; }
                    input._iti = iti;

                    var initVal = input.getAttribute('data-init');
                    if (initVal) {
                        try { iti.setNumber(initVal); } catch (e) {}
                    } else {
                        fetch('https://ipapi.co/json/').then(function (r) { return r.json(); })
                            .then(function (d) { if (d && d.country_code && !input.value) { try { iti.setCountry(String(d.country_code).toLowerCase()); } catch (e) {} } })
                            .catch(function () {});
                    }

                    // Setiap ketikan: perbarui nilai input tersembunyi (wire:model deferred),
                    // jadi nomor ikut terkirim BERSAMA aksi checkout. Murni lokal (tanpa request).
                    input.addEventListener('input', syncCoPhone);
                    input.addEventListener('countrychange', syncCoPhone);

                    // Saat meninggalkan kolom nomor: sinkron + cari pelanggan lama (auto-isi
                    // nama & email). Aman karena tombol "Bayar" kini pakai wire:target="checkout",
                    // jadi request pencarian ini TIDAK menonaktifkan tombolnya.
                    input.addEventListener('blur', function () {
                        syncCoPhone();
                        var root = input.closest('[wire\\:id]');
                        var hidden = document.querySelector('#co-phone-e164');
                        if (!window.Livewire || !root || !hidden || hidden.value.length < 10) return;
                        var comp = window.Livewire.find(root.getAttribute('wire:id'));
                        if (comp && typeof comp.set === 'function') {
                            comp.set('no_hp', hidden.value); // live → updatedNoHp() → auto-isi
                        }
                    });
                }

                document.addEventListener('livewire:init', initCoPhone);
                document.addEventListener('livewire:navigated', initCoPhone);
                window.addEventListener('load', initCoPhone);
            })();
        </script>
    @endpush
</div>
