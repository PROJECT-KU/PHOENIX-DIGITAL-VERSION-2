<div>
    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-headset"></i> Hubungi Kami</span>
                <h1>Hubungi Kami</h1>
                <p>Ada pertanyaan atau ingin memesan? Tim Phoenix Digital siap membantu dengan respons cepat.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li class="current">Hubungi Kami</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="ct-section">
        <div class="container">
            <div class="ph-sec-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-chat-dots-fill"></i> Kontak</span>
                <h2 class="ph-sec-title">Mari terhubung dengan kami</h2>
                <p class="ph-sec-sub">Pilih cara yang paling nyaman — WhatsApp, email, atau kirim pesan lewat formulir.</p>
            </div>

            <div class="ct-grid">
                {{-- Panel Informasi Kontak --}}
                <div class="ct-info">
                    <div class="ct-info-deco"></div>

                    <div class="ct-info-illus" aria-hidden="true">
                        <svg viewBox="0 0 340 190" fill="none" xmlns="http://www.w3.org/2000/svg">
                            {{-- gelembung besar --}}
                            <rect x="34" y="34" width="188" height="98" rx="24" fill="#ffffff" opacity=".16" />
                            <rect x="34" y="34" width="188" height="98" rx="24" fill="none" stroke="#ffffff" stroke-opacity=".5" stroke-width="2" />
                            <path d="M78 132 L78 156 L104 132 Z" fill="#ffffff" opacity=".16" />
                            <circle cx="88" cy="83" r="8" fill="#ffffff" />
                            <circle cx="128" cy="83" r="8" fill="#ffffff" opacity=".8" />
                            <circle cx="168" cy="83" r="8" fill="#ffffff" opacity=".6" />
                            {{-- gelembung kecil (balasan) --}}
                            <rect x="196" y="92" width="108" height="66" rx="20" fill="#ffffff" />
                            <rect x="214" y="112" width="60" height="7" rx="3.5" fill="#f26522" opacity=".55" />
                            <rect x="214" y="128" width="40" height="7" rx="3.5" fill="#f26522" opacity=".3" />
                            <path d="M244 158 L244 176 L266 158 Z" fill="#ffffff" />
                            {{-- amplop --}}
                            <g transform="translate(250,30)">
                                <rect x="0" y="0" width="58" height="40" rx="9" fill="#ffffff" />
                                <path d="M5 7 L29 26 L53 7" fill="none" stroke="#f26522" stroke-width="2.6" stroke-opacity=".6" stroke-linecap="round" stroke-linejoin="round" />
                            </g>
                            {{-- aksen --}}
                            <circle cx="30" cy="150" r="4" fill="#ffffff" opacity=".7" />
                            <path d="M312 150 l2.6 6.4 6.4 2.6 -6.4 2.6 -2.6 6.4 -2.6 -6.4 -6.4 -2.6 6.4 -2.6z" fill="#ffffff" opacity=".85" />
                        </svg>
                    </div>

                    <h3>Informasi Kontak</h3>
                    <p>Kami senang mendengar dari Anda. Hubungi lewat kanal di bawah ini.</p>

                    <div class="ct-info-list">
                        <a class="ct-info-item"
                            href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20bertanya."
                            target="_blank" rel="noopener">
                            <span class="ct-info-ic"><i class="bi bi-whatsapp"></i></span>
                            <span class="ct-info-txt">
                                <small>WhatsApp</small>
                                <b>0895-0596-7995</b>
                            </span>
                        </a>
                        <a class="ct-info-item" href="mailto:halo@phoenixdigital.id">
                            <span class="ct-info-ic"><i class="bi bi-envelope-fill"></i></span>
                            <span class="ct-info-txt">
                                <small>Email</small>
                                <b>halo@phoenixdigital.id</b>
                            </span>
                        </a>
                        <div class="ct-info-item">
                            <span class="ct-info-ic"><i class="bi bi-geo-alt-fill"></i></span>
                            <span class="ct-info-txt">
                                <small>Alamat</small>
                                <b>Jl. Durmo, Ngemplak, Mlati, Sleman, Yogyakarta</b>
                            </span>
                        </div>
                        <div class="ct-info-item">
                            <span class="ct-info-ic"><i class="bi bi-clock-fill"></i></span>
                            <span class="ct-info-txt">
                                <small>Jam Operasional</small>
                                <b>Setiap hari · 08.00–21.00 WIB</b>
                            </span>
                        </div>
                    </div>

                    <div class="ct-socials">
                        <a href="https://web.facebook.com/profile.php?id=61586376808425" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.instagram.com/phoenixdigital.id/" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="https://www.tiktok.com/@phoenix_digitalwarehouse" target="_blank" rel="noopener" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                    </div>
                </div>

                {{-- Kartu Formulir --}}
                <div class="ct-form-card">
                    <h3 class="ct-form-title">Kirim Pesan</h3>
                    <p class="ct-form-sub">Isi formulir di bawah, kami akan segera menghubungi Anda.</p>

                    @error('rate_limit')
                        <div class="ct-alert ct-alert-error">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <form wire:submit="save" id="ct-form">
                        {{-- Honeypot --}}
                        <div style="display:none; opacity:0; position:absolute; left:-9999px;">
                            <label for="website_url">Website</label>
                            <input type="text" wire:model="website_url" id="website_url" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="ct-label">Nama Lengkap</label>
                                <div class="ct-input-wrap">
                                    <i class="bi bi-person"></i>
                                    <input type="text" wire:model="name"
                                        class="form-control @error('name') is-invalid @enderror" placeholder="Nama Anda">
                                </div>
                                @error('name') <span class="ct-err">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="ct-label">Alamat Email</label>
                                <div class="ct-input-wrap">
                                    <i class="bi bi-envelope"></i>
                                    <input type="email" wire:model="email"
                                        class="form-control @error('email') is-invalid @enderror" placeholder="nama@contoh.com">
                                </div>
                                @error('email') <span class="ct-err">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-12">
                                <label class="ct-label">No. WhatsApp</label>
                                <div wire:ignore>
                                    <input type="tel" id="ct-phone" class="form-control" autocomplete="tel"
                                        placeholder="812 3456 789" data-init="{{ $no_telp }}">
                                </div>
                                {{-- jembatan nilai E.164 ke Livewire --}}
                                <input type="hidden" id="ct-phone-e164" wire:model="no_telp">
                                @error('no_telp') <span class="ct-err">{{ $message }}</span> @enderror
                                <small class="ct-hint">Ketik nomor lokal Anda — kode negara ditambahkan otomatis. Untuk negara lain, pilih bendera.</small>
                            </div>

                            <div class="col-12">
                                <label class="ct-label">Pesan</label>
                                <div class="ct-input-wrap ct-input-textarea">
                                    <i class="bi bi-chat-dots"></i>
                                    <textarea wire:model="message" rows="5"
                                        class="form-control @error('message') is-invalid @enderror"
                                        placeholder="Tulis pesan Anda di sini..."></textarea>
                                </div>
                                @error('message') <span class="ct-err">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-12">
                                <button type="submit" class="ct-submit" wire:loading.attr="disabled" wire:target="save">
                                    <span wire:loading.remove wire:target="save"><i class="bi bi-send-fill"></i> Kirim Pesan</span>
                                    <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm"></span> Mengirim...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
        <style>
            .ct-form-card .iti { width: 100%; display: block; }
            .ct-form-card #ct-phone {
                border: 1px solid var(--ph-line); border-radius: 12px; padding: 11px 14px; font-size: .92rem; width: 100%;
                transition: border-color .16s ease, box-shadow .16s ease;
            }
            .ct-form-card #ct-phone:focus { border-color: var(--ph-orange); box-shadow: 0 0 0 3px rgba(242, 101, 34, .15); }

            /* Area bendera + kode negara (kiri) — seragam brand */
            .ct-form-card .iti--separate-dial-code .iti__selected-flag {
                background-color: var(--ph-soft);
                border-radius: 11px 0 0 11px;
                border-right: 1px solid var(--ph-line);
                padding: 0 10px 0 12px;
                transition: background-color .16s ease;
            }
            .ct-form-card .iti__selected-flag:hover,
            .ct-form-card .iti__selected-flag:focus { background-color: #ffe6cf; }
            .ct-form-card .iti--separate-dial-code .iti__selected-dial-code {
                color: var(--ph-ink); font-weight: 700; font-size: .9rem; margin-left: 8px;
            }
            .ct-form-card .iti__arrow { border-top-color: var(--ph-orange); margin-left: 8px; }
            .ct-form-card .iti__arrow--up { border-top-color: transparent; border-bottom-color: var(--ph-orange); }

            /* Dropdown daftar negara */
            .ct-form-card .iti__country-list {
                border: 1px solid var(--ph-line); border-radius: 14px; padding: 6px; margin-top: 8px;
                box-shadow: 0 16px 44px rgba(35, 39, 47, .16); font-size: .9rem; overflow-y: auto;
            }
            .ct-form-card .iti__country { padding: 8px 10px; border-radius: 9px; }
            .ct-form-card .iti__country.iti__highlight { background-color: var(--ph-soft); }
            .ct-form-card .iti__country .iti__country-name { color: var(--ph-ink); }
            .ct-form-card .iti__country .iti__dial-code { color: var(--ph-muted); }
            .ct-form-card .iti__divider { border-bottom: 1px solid var(--ph-line); margin: 4px 0; }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
        <script>
            (function () {
                function syncPhone() {
                    var input = document.querySelector('#ct-phone');
                    var hidden = document.querySelector('#ct-phone-e164');
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

                function initCtPhone() {
                    var input = document.querySelector('#ct-phone');
                    if (!input || input.dataset.itiInit) return;
                    if (typeof window.intlTelInput === 'undefined') return;
                    input.dataset.itiInit = '1';

                    var iti;
                    try {
                        iti = window.intlTelInput(input, {
                            initialCountry: 'id', // default siap seketika (input langsung bisa diketik)
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
                        // Deteksi negara via IP secara terpisah (tak memblokir input)
                        fetch('https://ipapi.co/json/').then(function (r) { return r.json(); })
                            .then(function (d) {
                                if (d && d.country_code && !input.value) {
                                    try { iti.setCountry(String(d.country_code).toLowerCase()); syncPhone(); } catch (e) {}
                                }
                            }).catch(function () {});
                    }

                    input.addEventListener('input', syncPhone);
                    input.addEventListener('blur', syncPhone);
                    input.addEventListener('countrychange', syncPhone);
                    var form = input.closest('form');
                    if (form) form.addEventListener('submit', syncPhone, true);
                    syncPhone();
                }

                document.addEventListener('livewire:init', initCtPhone);
                document.addEventListener('livewire:navigated', initCtPhone);
                window.addEventListener('load', initCtPhone);

                // Toast seragam saat pesan terkirim
                window.addEventListener('contact-success', function (e) {
                    // Kosongkan semua field via JS (server tidak me-reset agar tampilan tidak hilang)
                    var form = document.querySelector('#ct-form');
                    if (form) {
                        form.querySelectorAll('input[wire\\:model], textarea[wire\\:model]').forEach(function (el) {
                            el.value = '';
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                        });
                    }
                    var input = document.querySelector('#ct-phone');
                    if (input) { input.value = ''; if (input._iti) { try { input._iti.setNumber(''); } catch (er) {} } }
                    if (typeof Swal === 'undefined') return;
                    Swal.fire({
                        toast: true, position: 'top-end', showConfirmButton: false, timer: 2800, timerProgressBar: true,
                        html: '<div class="ph-toast">' +
                            '<span class="ph-toast-ic"><i class="bi bi-check-circle-fill"></i></span>' +
                            '<div class="ph-toast-txt"><strong>Pesan terkirim</strong><span>' +
                            ((e.detail && e.detail.message) ? e.detail.message : 'Terima kasih!') + '</span></div></div>',
                        customClass: { popup: 'ph-toast-popup' }
                    });
                });
            })();
        </script>
    @endpush
</div>
