<div class="kn-page">
    <style>
        /* ===== Halaman Hubungi Kami =====
           Bahasa visual sama dengan Beranda, Shop, Bundling, Layanan, dan
           Tentang Kami: kartu judul bersama (.ph-page-title), kartu putih
           bersudut 18px dengan warna per kartu (--c), ubin ikon berwarna.
           Kelas kn-*: aturan .ct-* di public-custom-styles.css server beku,
           jadi desain ini tidak bergantung padanya. Id #ct-form, #ct-phone,
           dan #ct-phone-e164 DIPERTAHANKAN — dipakai skrip widget telepon. */
        .kn-page { --kn-ink: #1c1f26; --kn-muted: #64748b; --kn-line: #eceff4; --kn-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .kn-section { padding: 18px 0 64px; }

        /* Ubin ikon bersama — glif tunggal selalu display:block + line-height:1 */
        .kn-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 44px; height: 44px; border-radius: 14px; font-size: 1.15rem;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c);
            transition: background .18s ease, color .18s ease;
        }
        .kn-ubin.is-padat {
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff)); color: #fff;
            box-shadow: 0 14px 26px -14px color-mix(in srgb, var(--c) 85%, transparent);
        }
        .kn-ubin i.bi, .kn-ubin i.bi::before { display: block; line-height: 1; }

        .kn-kepala { margin-bottom: 22px; }
        .kn-kepala h2 {
            margin: 10px 0 6px; font-family: var(--kn-font); font-weight: 800; letter-spacing: -.02em;
            font-size: clamp(1.35rem, 1.05rem + 1.1vw, 1.8rem); color: var(--kn-ink);
        }
        .kn-kepala p { margin: 0; max-width: 640px; color: var(--kn-muted); font-size: .95rem; line-height: 1.65; }

        .kn-grid { display: grid; grid-template-columns: minmax(0, .92fr) minmax(0, 1.08fr); gap: 24px; align-items: start; }
        .kn-kiri { display: grid; gap: 14px; min-width: 0; }

        /* Panel jingga: ilustrasi tetap putih di atas gradasi merek */
        .kn-panel {
            position: relative; padding: 26px 26px 24px; border-radius: 22px; overflow: hidden; color: #fff;
            /* Dasar jingga TUA dengan kilau tipis di atasnya: putih di atas jingga
               terang hanya berkontras 3,1 : 1 — terlalu pudar untuk teks biasa.
               Dasar padat juga membuat kontrasnya bisa diukur pasti. */
            background:
                radial-gradient(80% 120% at 100% 0%, rgba(255, 255, 255, .16), transparent 58%),
                linear-gradient(135deg, rgba(255, 255, 255, .1) 0%, rgba(255, 255, 255, 0) 55%),
                #c2410c;
            box-shadow: 0 22px 44px -28px rgba(194, 65, 12, .95);
        }
        .kn-illus { display: block; max-width: 320px; margin: 0 auto 14px; }
        .kn-illus svg { display: block; width: 100%; height: auto; }
        .kn-panel h3 { margin: 0 0 6px; font-family: var(--kn-font); font-weight: 800; font-size: 1.2rem; color: #fff; }
        .kn-panel p { margin: 0; font-size: .89rem; line-height: 1.65; color: rgba(255, 255, 255, .96); }
        .kn-panel-aksi { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .kn-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            height: 44px; padding: 0 18px; border-radius: 12px; border: 1.5px solid transparent;
            font-weight: 700; font-size: .87rem; text-decoration: none; white-space: nowrap;
            transition: background .18s, border-color .18s, color .18s, transform .18s;
        }
        .kn-btn i.bi, .kn-btn i.bi::before { display: block; line-height: 1; font-size: 1.02rem; }
        .kn-btn:hover { transform: translateY(-1px); }
        .kn-btn.is-putih { background: #fff; color: #c2410c; }
        .kn-btn.is-putih:hover { background: #fff7ef; color: #9a3412; }
        .kn-btn.is-tembus { background: rgba(255, 255, 255, .14); color: #fff; border-color: rgba(255, 255, 255, .45); }
        .kn-btn.is-tembus:hover { background: rgba(255, 255, 255, .24); color: #fff; }

        /* Kanal kontak */
        .kn-list { display: grid; gap: 12px; }
        .kn-item {
            display: flex; align-items: center; gap: 13px; padding: 14px 16px; text-decoration: none;
            background: #fff; border: 1px solid var(--kn-line); border-radius: 16px; min-width: 0;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        a.kn-item:hover {
            transform: translateY(-2px); border-color: color-mix(in srgb, var(--c) 38%, #fff);
            box-shadow: 0 16px 30px -24px color-mix(in srgb, var(--c) 85%, transparent);
        }
        a.kn-item:hover .kn-ubin { background: var(--c); color: #fff; }
        .kn-item-txt { display: flex; flex-direction: column; min-width: 0; flex: 1 1 auto; }
        .kn-item-txt small { font-size: .72rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: var(--kn-muted); }
        .kn-item-txt b { font-family: var(--kn-font); font-weight: 700; font-size: .92rem; line-height: 1.4; color: var(--kn-ink); word-break: break-word; }
        .kn-item-panah { flex-shrink: 0; color: color-mix(in srgb, var(--c) 70%, #0f172a); font-size: .85rem; }
        .kn-item-panah i.bi, .kn-item-panah i.bi::before { display: block; line-height: 1; }

        /* Media sosial */
        .kn-sosial { display: flex; flex-wrap: wrap; gap: 10px; }
        .kn-sos {
            display: inline-flex; align-items: center; gap: 9px; height: 44px; padding: 0 16px 0 8px; border-radius: 99px;
            background: #fff; border: 1px solid var(--kn-line); color: var(--kn-ink);
            font-size: .84rem; font-weight: 700; text-decoration: none;
            transition: border-color .2s ease, color .2s ease, transform .2s ease;
        }
        .kn-sos:hover { transform: translateY(-2px); border-color: color-mix(in srgb, var(--c) 45%, #fff); color: var(--c); }
        .kn-sos .kn-ubin { width: 30px; height: 30px; border-radius: 10px; font-size: .9rem; }

        /* ===== Kartu formulir ===== */
        .kn-form {
            background: #fff; border: 1px solid var(--kn-line); border-radius: 22px; padding: 28px 26px;
            box-shadow: 0 18px 40px -34px rgba(15, 23, 42, .5);
        }
        .kn-form-kepala { display: flex; align-items: center; gap: 13px; margin-bottom: 18px; }
        .kn-form-kepala h3 { margin: 0; font-family: var(--kn-font); font-weight: 800; font-size: 1.18rem; color: var(--kn-ink); }
        .kn-form-kepala p { margin: 2px 0 0; font-size: .85rem; color: var(--kn-muted); line-height: 1.5; }
        .kn-baris { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .kn-grup { margin-top: 14px; }
        .kn-label { display: block; margin-bottom: 7px; font-size: .82rem; font-weight: 700; color: #334155; }
        .kn-field { position: relative; }
        .kn-field > i.bi {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            display: block; line-height: 1; font-size: .95rem; color: #94a3b8; pointer-events: none;
        }
        .kn-field.is-area > i.bi { top: 16px; transform: none; }
        .kn-field .form-control {
            width: 100%; padding: 12px 14px 12px 40px; border: 1.5px solid #e8ecf2; border-radius: 12px;
            background: #fff; color: #0f172a; font-size: .92rem; box-shadow: none;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .kn-field .form-control::placeholder { color: #94a3b8; }
        .kn-field .form-control:focus { border-color: #f26522; box-shadow: 0 0 0 4px rgba(242, 101, 34, .14); }
        .kn-field .form-control.is-invalid { border-color: #dc2626; background-image: none; }
        .kn-err { display: block; margin-top: 6px; font-size: .78rem; font-weight: 600; color: #dc2626; }
        .kn-hint { display: block; margin-top: 7px; font-size: .78rem; color: var(--kn-muted); line-height: 1.5; }
        .kn-alert {
            display: flex; align-items: center; gap: 11px; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px;
            border: 1px solid #fecaca; background: #fef2f2; color: #b91c1c; font-size: .85rem; line-height: 1.5;
        }
        .kn-alert i.bi, .kn-alert i.bi::before { display: block; line-height: 1; font-size: 1.05rem; }
        .kn-submit {
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            width: 100%; height: 50px; margin-top: 18px; border: 0; border-radius: 13px;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-weight: 700; font-size: .95rem; cursor: pointer;
            box-shadow: 0 14px 26px -14px rgba(242, 101, 34, .85);
            transition: filter .16s ease, transform .16s ease;
        }
        .kn-submit:hover:not(:disabled) { filter: brightness(1.05); transform: translateY(-1px); }
        .kn-submit:disabled { opacity: .7; cursor: progress; }
        .kn-submit i.bi, .kn-submit i.bi::before { display: block; line-height: 1; }
        .kn-catatan {
            display: flex; align-items: center; gap: 11px; margin: 16px 0 0; padding: 12px 14px; border-radius: 14px;
            background: #f8fafc; border: 1px solid var(--kn-line); color: #475569; font-size: .83rem; line-height: 1.55;
        }
        .kn-catatan .kn-ubin { width: 30px; height: 30px; border-radius: 10px; font-size: .9rem; }

        @media (max-width: 991.98px) {
            .kn-grid { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 575.98px) {
            .kn-panel { padding: 20px 18px; }
            .kn-illus { max-width: 250px; }
            .kn-panel-aksi .kn-btn { flex: 1 1 auto; }
            .kn-form { padding: 20px 16px; }
            .kn-baris { grid-template-columns: minmax(0, 1fr); }
            .kn-sos { flex: 1 1 auto; justify-content: center; }
        }
        @media (prefers-reduced-motion: reduce) {
            a.kn-item:hover, .kn-sos:hover, .kn-btn:hover, .kn-submit:hover:not(:disabled) { transform: none; }
        }
    </style>

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

    <section class="kn-section">
        <div class="container">
            <div class="kn-kepala">
                <span class="ph-sec-eyebrow"><i class="bi bi-chat-dots-fill"></i> Kontak</span>
                <h2>Mari terhubung dengan kami</h2>
                <p>Pilih cara yang paling nyaman — WhatsApp, email, atau kirim pesan lewat formulir.</p>
            </div>

            <div class="kn-grid">
                {{-- Kiri: panel, kanal kontak, media sosial --}}
                <div class="kn-kiri">
                    <div class="kn-panel">
                        <div class="kn-illus" aria-hidden="true">
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
                        <p>Kami senang mendengar dari Anda. Hubungi lewat kanal di bawah ini — WhatsApp paling cepat dibalas.</p>

                        <div class="kn-panel-aksi">
                            <a class="kn-btn is-putih" href="{{ $kanal[0]['href'] }}" target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp"></i> Chat WhatsApp
                            </a>
                            <a class="kn-btn is-tembus" href="{{ $kanal[1]['href'] }}">
                                <i class="bi bi-envelope"></i> Kirim Email
                            </a>
                        </div>
                    </div>

                    <div class="kn-list">
                        @foreach ($kanal as $k)
                            @if ($k['href'])
                                <a class="kn-item" style="--c: {{ $k['warna'] }}" href="{{ $k['href'] }}"
                                    {!! $k['baru'] ? 'target="_blank" rel="noopener"' : '' !!}>
                                    <span class="kn-ubin"><i class="bi {{ $k['ikon'] }}"></i></span>
                                    <span class="kn-item-txt">
                                        <small>{{ $k['label'] }}</small>
                                        <b>{{ $k['nilai'] }}</b>
                                    </span>
                                    <span class="kn-item-panah"><i class="bi bi-arrow-up-right"></i></span>
                                </a>
                            @else
                                <div class="kn-item" style="--c: {{ $k['warna'] }}">
                                    <span class="kn-ubin"><i class="bi {{ $k['ikon'] }}"></i></span>
                                    <span class="kn-item-txt">
                                        <small>{{ $k['label'] }}</small>
                                        <b>{{ $k['nilai'] }}</b>
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="kn-sosial">
                        @foreach ($sosial as $s)
                            <a class="kn-sos" style="--c: {{ $s['warna'] }}" href="{{ $s['href'] }}"
                                target="_blank" rel="noopener" aria-label="{{ $s['label'] }}">
                                <span class="kn-ubin"><i class="bi {{ $s['ikon'] }}"></i></span> {{ $s['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Kanan: formulir --}}
                <div class="kn-form">
                    <div class="kn-form-kepala">
                        <span class="kn-ubin is-padat" style="--c: #f26522"><i class="bi bi-send-fill"></i></span>
                        <div>
                            <h3>Kirim Pesan</h3>
                            <p>Isi formulir di bawah, kami akan segera menghubungi Anda.</p>
                        </div>
                    </div>

                    @error('rate_limit')
                        <div class="kn-alert">
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

                        <div class="kn-baris">
                            <div>
                                <label class="kn-label" for="kn-nama">Nama Lengkap</label>
                                <div class="kn-field">
                                    <i class="bi bi-person"></i>
                                    <input type="text" id="kn-nama" wire:model="name"
                                        class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Nama Anda">
                                </div>
                                @error('name') <span class="kn-err">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="kn-label" for="kn-email">Alamat Email</label>
                                <div class="kn-field">
                                    <i class="bi bi-envelope"></i>
                                    <input type="email" id="kn-email" wire:model="email"
                                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="nama@contoh.com">
                                </div>
                                @error('email') <span class="kn-err">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="kn-grup">
                            <label class="kn-label" for="ct-phone">No. WhatsApp</label>
                            <div wire:ignore>
                                <input type="tel" id="ct-phone" class="form-control" autocomplete="tel"
                                    placeholder="812 3456 789" data-init="{{ $no_telp }}">
                            </div>
                            {{-- jembatan nilai E.164 ke Livewire --}}
                            <input type="hidden" id="ct-phone-e164" wire:model="no_telp">
                            @error('no_telp') <span class="kn-err">{{ $message }}</span> @enderror
                            <small class="kn-hint">Ketik nomor lokal Anda — kode negara ditambahkan otomatis. Untuk negara lain, pilih bendera.</small>
                        </div>

                        <div class="kn-grup">
                            <label class="kn-label" for="kn-pesan">Pesan</label>
                            <div class="kn-field is-area">
                                <i class="bi bi-chat-dots"></i>
                                <textarea id="kn-pesan" wire:model="message" rows="5"
                                    class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}"
                                    placeholder="Tulis pesan Anda di sini..."></textarea>
                            </div>
                            @error('message') <span class="kn-err">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="kn-submit" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save"><i class="bi bi-send-fill"></i> Kirim Pesan</span>
                            <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm"></span> Mengirim...</span>
                        </button>
                    </form>

                    <p class="kn-catatan" style="--c: #16a34a">
                        <span class="kn-ubin"><i class="bi bi-shield-check"></i></span>
                        <span>Data Anda hanya kami pakai untuk membalas pesan ini. Butuh jawaban cepat? Chat WhatsApp di samping.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
        <style>
            /* Widget nomor telepon disamakan dengan kolom lain di kartu ini. */
            .kn-form .iti { width: 100%; display: block; }
            .kn-form #ct-phone {
                border: 1.5px solid #e8ecf2; border-radius: 12px; padding: 12px 14px; font-size: .92rem; width: 100%;
                transition: border-color .16s ease, box-shadow .16s ease;
            }
            .kn-form #ct-phone:focus { border-color: #f26522; box-shadow: 0 0 0 4px rgba(242, 101, 34, .14); }

            /* Area bendera + kode negara (kiri) — seragam brand */
            .kn-form .iti--separate-dial-code .iti__selected-flag {
                background-color: var(--ph-soft); border-radius: 11px 0 0 11px;
                border-right: 1px solid var(--ph-line); padding: 0 10px 0 12px;
                transition: background-color .16s ease;
            }
            .kn-form .iti__selected-flag:hover,
            .kn-form .iti__selected-flag:focus { background-color: #ffe6cf; }
            .kn-form .iti--separate-dial-code .iti__selected-dial-code {
                color: var(--ph-ink); font-weight: 700; font-size: .9rem; margin-left: 8px;
            }
            .kn-form .iti__arrow { border-top-color: var(--ph-orange); margin-left: 8px; }
            .kn-form .iti__arrow--up { border-top-color: transparent; border-bottom-color: var(--ph-orange); }

            /* Dropdown daftar negara */
            .kn-form .iti__country-list {
                border: 1px solid var(--ph-line); border-radius: 14px; padding: 6px; margin-top: 8px;
                box-shadow: 0 16px 44px rgba(35, 39, 47, .16); font-size: .9rem; overflow-y: auto;
            }
            .kn-form .iti__country { padding: 8px 10px; border-radius: 9px; }
            .kn-form .iti__country.iti__highlight { background-color: var(--ph-soft); }
            .kn-form .iti__country .iti__country-name { color: var(--ph-ink); }
            .kn-form .iti__country .iti__dial-code { color: var(--ph-muted); }
            .kn-form .iti__divider { border-bottom: 1px solid var(--ph-line); margin: 4px 0; }
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
