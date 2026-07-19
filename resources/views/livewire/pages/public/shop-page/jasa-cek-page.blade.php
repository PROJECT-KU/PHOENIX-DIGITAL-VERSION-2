<div wire:poll.20s="refreshStatus">
    <style>
        #ph-page-lines { display: none !important; }
        .cek-wrap { max-width: 640px; margin: 0 auto; }
        .cek-quota { background: linear-gradient(135deg,#fff7ed,#fffdf9); border:1px solid #fde68a; border-radius:16px; padding:18px 18px 16px; margin-bottom:16px; }
        .cek-quota-top { display:flex; align-items:baseline; justify-content:space-between; gap:8px; margin-bottom:10px; }
        .cek-quota-num { font-size:1.35rem; font-weight:800; color:#b45309; }
        .cek-quota-num small { font-weight:600; font-size:.85rem; color:var(--ph-muted); }
        .cek-bar { height:12px; border-radius:99px; background:#fde9c8; overflow:hidden; }
        .cek-bar > span { display:block; height:100%; border-radius:99px; background:linear-gradient(90deg,#fbbf24,#f26522); transition:width .4s ease; }
        .cek-item { border:1px solid var(--ph-line); border-radius:14px; padding:13px 14px; margin-bottom:10px; background:#fff; }
        .cek-item-top { display:flex; align-items:center; gap:10px; }
        .cek-file { flex:1; min-width:0; }
        .cek-file b { display:block; font-size:.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .cek-file span { font-size:.75rem; color:var(--ph-muted); }
        .cek-chip { display:inline-flex; align-items:center; gap:5px; font-size:.76rem; font-weight:700; padding:4px 10px; border-radius:99px; white-space:nowrap; }
        .cek-chip.warning { background:#fef3c7; color:#b45309; }
        .cek-chip.info { background:#e0f2fe; color:#0369a1; }
        .cek-chip.success { background:#dcfce7; color:#15803d; }
        .cek-chip.secondary { background:#f1f5f9; color:#64748b; }
        .cek-meta { margin-top:9px; padding-top:9px; border-top:1px dashed var(--ph-line); display:flex; flex-wrap:wrap; align-items:center; gap:8px; }
        .cek-persen { display:inline-flex; align-items:center; gap:6px; font-weight:800; font-size:.86rem; padding:5px 12px; border-radius:10px; background:#eef2ff; color:#4338ca; }
        .cek-dl { display:inline-flex; align-items:center; gap:6px; font-size:.82rem; font-weight:700; padding:6px 13px; border-radius:10px; background:#16a34a; color:#fff; text-decoration:none; }
        .cek-dl:hover { background:#15803d; color:#fff; }
        .cek-excl { font-size:.75rem; color:var(--ph-muted); }
        .cek-linkbox { display:flex; gap:8px; align-items:center; background:#f8fafc; border:1px dashed var(--ph-line); border-radius:10px; padding:8px 10px; margin-top:6px; }
        .cek-linkbox code { flex:1; min-width:0; font-size:.78rem; color:#334155; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .cek-copy { border:0; background:var(--ph-orange); color:#fff; border-radius:8px; padding:6px 12px; font-size:.78rem; font-weight:700; cursor:pointer; white-space:nowrap; }
        /* --- Pengaturan pemeriksaan: flat, tanpa kotak dalam kotak --- */
        .cek-set { margin-top:18px; padding-top:16px; border-top:1px solid var(--ph-line); }
        .cek-set + .cek-set { margin-top:16px; }
        .cek-set-label { display:block; font-size:.82rem; font-weight:700; color:#1e293b; margin-bottom:2px; }
        .cek-set-hint { display:block; font-size:.76rem; color:var(--ph-muted); line-height:1.4; margin-bottom:10px; }
        /* Chip toggle — grid auto-fit: melebar merata, tak menyisakan ruang kosong */
        .cek-chips { display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:8px; }
        .cek-chip { position:relative; display:flex; align-items:center; gap:8px; padding:11px 14px 11px 12px; border:1.5px solid var(--ph-line); border-radius:12px; background:#fff; font-size:.84rem; font-weight:600; color:#64748b; cursor:pointer; user-select:none; transition:border-color .18s, background .18s, color .18s; }
        .cek-chip:hover { border-color:#fcd9a8; color:#b45309; }
        .cek-chip input { position:absolute; opacity:0; width:0; height:0; pointer-events:none; }
        .cek-chip:has(input:checked) { border-color:#f59e0b; background:#fffbeb; color:#b45309; }
        .cek-chip:has(input:focus-visible) { box-shadow:0 0 0 3px rgba(245,158,11,.22); }
        /* Kotak centang mini di dalam chip */
        .cek-chip-box { width:17px; height:17px; flex-shrink:0; border:1.5px solid #cbd5e1; border-radius:5px; display:flex; align-items:center; justify-content:center; background:#fff; transition:background .18s, border-color .18s; }
        .cek-chip-box i.bi { font-size:.62rem; color:#fff; opacity:0; line-height:1; display:flex; transition:opacity .18s; }
        .cek-chip-box i.bi::before { display:block; line-height:1; }
        .cek-chip:has(input:checked) .cek-chip-box { background:#f59e0b; border-color:#f59e0b; }
        .cek-chip:has(input:checked) .cek-chip-box i.bi { opacity:1; }
        .cek-chip-ic { font-size:.95rem; display:flex; align-items:center; line-height:1; }
        .cek-chip-ic::before { display:block; line-height:1; }
        /* Ambang "exclude source" — ramah orang awam */
        .cek-amb { margin-top:11px; padding:13px; border:1px solid #fde9c8; border-radius:12px; background:#fffdf8; }
        .cek-amb-why { display:flex; align-items:flex-start; gap:8px; font-size:.79rem; color:#92400e; line-height:1.5; margin-bottom:11px; }
        .cek-amb-why i.bi { flex-shrink:0; margin-top:.15rem; font-size:.9rem; display:flex; line-height:1; }
        .cek-amb-why i.bi::before { display:block; line-height:1; }
        .cek-amb-why b { color:#78350f; }
        /* Label langkah */
        .cek-amb-step { display:block; font-size:.77rem; font-weight:700; color:#92400e; margin-bottom:7px; }
        /* Kotak angka dengan satuan tampil di dalamnya */
        .cek-amb-inputwrap { position:relative; display:block; }
        .cek-amb-inputwrap .cek-amb-num { width:100%; padding-right:74px; }
        .cek-amb-suffix { position:absolute; top:50%; right:5px; transform:translateY(-50%); min-width:62px; text-align:center; padding:6px 10px; border-radius:8px; background:#fef3c7; color:#b45309; font-size:.82rem; font-weight:700; pointer-events:none; }
        /* Konfirmasi bahasa manusia */
        .cek-amb-echo { display:flex; align-items:flex-start; gap:7px; margin-top:10px; font-size:.78rem; color:#15803d; line-height:1.45; }
        .cek-amb-echo i.bi { flex-shrink:0; margin-top:.12rem; display:flex; line-height:1; }
        .cek-amb-echo i.bi::before { display:block; line-height:1; }
        .cek-amb-num { font-size:.95rem; font-weight:700; padding:11px 12px; border:1px solid var(--ph-line); border-radius:10px; background:#fff; color:#334155; outline:none; transition:border-color .18s, box-shadow .18s; }
        .cek-amb-num:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.13); }
        .cek-amb-unit { display:flex; border:1px solid var(--ph-line); border-radius:10px; overflow:hidden; background:#fff; }
        .cek-amb-unit button { flex:1; border:0; background:transparent; padding:11px 12px; font-size:.85rem; font-weight:700; color:#94a3b8; cursor:pointer; transition:background .18s, color .18s; }
        .cek-amb-unit button + button { border-left:1px solid var(--ph-line); }
        .cek-amb-unit button:hover { color:#b45309; background:#fff7ed; }
        .cek-amb-unit button.is-on { background:#f59e0b; color:#fff; }
        /* Rincian "Layanan Anda" — berlabel & berwarna agar jelas jenisnya */
        .lyn-item { padding-bottom:11px; margin-bottom:11px; border-bottom:1px solid var(--ph-line); }
        .lyn-item:last-child { border-bottom:0; padding-bottom:0; margin-bottom:0; }
        .lyn-name { font-weight:800; font-size:.92rem; color:#1e293b; margin-bottom:8px; }
        .lyn-row { display:flex; align-items:flex-start; gap:9px; margin-top:6px; }
        .lyn-key { flex-shrink:0; width:74px; padding-top:3px; font-size:.68rem; font-weight:700;
            letter-spacing:.04em; text-transform:uppercase; color:#94a3b8; }
        .lyn-vals { display:flex; flex-wrap:wrap; gap:5px; min-width:0; }
        .lyn-chip { display:inline-flex; align-items:center; gap:5px; padding:4px 11px; border-radius:99px;
            font-size:.76rem; font-weight:600; line-height:1.4; border:1px solid transparent; white-space:nowrap; }
        .lyn-chip i.bi { font-size:.62rem; display:flex; align-items:center; line-height:1; }
        .lyn-chip i.bi::before { display:block; line-height:1; }
        .lyn-chip.is-scope { background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8; }
        .lyn-chip.is-skip { background:#f1f5f9; border-color:#e2e8f0; color:#64748b; }
        .lyn-chip.is-addon { background:#fffbeb; border-color:#fde68a; color:#b45309; }
        @media (max-width:479px) {
            .lyn-row { flex-direction:column; gap:3px; }
            .lyn-key { width:auto; padding-top:0; }
        }

        /* Panel jaminan privasi */
        .cek-trust { margin-top:14px; padding:15px 16px; border:1px solid #bbf7d0; border-radius:16px; background:linear-gradient(180deg,#f0fdf4,#fff); }
        .cek-trust-head { display:flex; align-items:center; gap:8px; font-weight:800; font-size:.88rem; color:#15803d; margin-bottom:10px; }
        .cek-trust-head i.bi { display:flex; align-items:center; line-height:1; font-size:1rem; }
        .cek-trust-head i.bi::before { display:block; line-height:1; }
        .cek-trust-list { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:8px; }
        .cek-trust-list li { display:flex; align-items:flex-start; gap:9px; font-size:.81rem; color:#334155; line-height:1.5; }
        .cek-trust-list li i.bi { flex-shrink:0; color:#16a34a; font-size:.92rem; margin-top:.12rem; display:flex; align-items:center; line-height:1; }
        .cek-trust-list li i.bi::before { display:block; line-height:1; }
        .cek-trust-list b { color:#15803d; font-weight:700; }
        /* Field seragam (ambang & catatan) */
        .cek-field { width:100%; font-size:.85rem; padding:10px 12px; border:1px solid var(--ph-line); border-radius:10px; background:#fff; color:#334155; outline:none; transition:border-color .18s, box-shadow .18s; }
        .cek-field::placeholder { color:#94a3b8; }
        .cek-field:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.13); }
        .cek-pulse { width:8px; height:8px; border-radius:50%; background:#0ea5e9; animation:cekPulse 1.1s infinite; }
        @keyframes cekPulse { 0%,100%{opacity:.35; transform:scale(.8);} 50%{opacity:1; transform:scale(1.2);} }
        /* Dropzone unggah */
        /* Pemilih jenis pemeriksaan (pesanan multi-jenis) */
        .cek-jenis { margin-bottom:14px; }
        .cek-jenis-label { display:block; font-size:.82rem; font-weight:700; color:#1e293b; margin-bottom:8px; }
        .cek-jenis-opts { display:flex; flex-wrap:wrap; gap:9px; }
        .cek-jenis-opt { position:relative; display:flex; flex-direction:column; gap:2px; flex:1 1 150px;
            padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:12px; background:#fff; cursor:pointer;
            transition:border-color .18s, background .18s, box-shadow .18s; }
        .cek-jenis-opt:hover { border-color:#93c5fd; background:#f8fbff; }
        .cek-jenis-opt.is-on { border-color:#2563eb; background:#eff6ff; box-shadow:0 2px 8px rgba(37,99,235,.10); }
        .cek-jenis-opt input { position:absolute; opacity:0; width:0; height:0; }
        .cek-jenis-name { font-size:.86rem; font-weight:700; color:#1e293b; }
        .cek-jenis-opt.is-on .cek-jenis-name { color:#1d4ed8; }
        .cek-jenis-sisa { font-size:.72rem; color:#94a3b8; }
        .cek-jenis-note { display:flex; align-items:center; gap:6px; margin-top:8px; font-size:.78rem; color:#1d4ed8; }
        .cek-jenis-note i.bi { display:flex; line-height:1; }
        .cek-jenis-tag { display:inline-block; margin-left:6px; padding:1px 8px; border-radius:99px;
            background:#eff6ff; color:#1d4ed8; font-size:.68rem; font-weight:700; vertical-align:middle; }

        .cek-drop { position:relative; display:block; border:2px dashed #fcd9a8; border-radius:14px; background:#fffdf8; padding:22px 16px; text-align:center; cursor:pointer; transition:border-color .2s, background .2s; }
        .cek-drop:hover { border-color:#f59e0b; background:#fff7ed; }
        .cek-drop-input { position:absolute; inset:0; width:100%; height:100%; opacity:0; cursor:pointer; }
        .cek-drop-ic { font-size:1.9rem; color:#f59e0b; display:block; line-height:1; margin-bottom:8px; }
        .cek-drop-title { font-weight:700; color:#b45309; font-size:.92rem; }
        .cek-drop-hint { font-size:.76rem; color:var(--ph-muted); margin-top:3px; }
        .cek-drop-file { display:flex; align-items:center; justify-content:center; gap:8px; flex-wrap:wrap; }
        .cek-drop-file .bi-file-earmark-check { color:#16a34a; font-size:1.35rem; }
        .cek-drop-fname { font-weight:700; color:#15803d; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .cek-drop-change { font-size:.74rem; color:#b45309; background:#fef3c7; padding:3px 9px; border-radius:99px; }
        .cek-drop-loading { color:#b45309; font-weight:700; font-size:.9rem; }
        .cek-spin { display:inline-block; animation:cekSpin 1s linear infinite; }
        @keyframes cekSpin { to { transform:rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .cek-pulse, .cek-spin { animation:none; } }
    </style>

    <section class="cart-section">
        <div class="container">
            <div class="cek-wrap">
                <div class="ph-empty" style="padding-bottom:6px;">
                    <span class="ph-sec-eyebrow" style="margin-bottom:10px;"><i class="bi bi-shield-check"></i> Cek Plagiasi</span>
                    <h3 class="ph-empty-title" style="margin-bottom:4px;">Halaman Pengecekan Anda</h3>
                    <p class="ph-empty-sub">
                        Pesanan <span style="font-family:'Courier New',monospace; font-weight:700; color:var(--ph-orange);">{{ $order->order_number }}</span>.
                        Simpan halaman ini untuk mengunggah file &amp; mengunduh hasil.
                    </p>
                </div>

                {{-- ===== Kuota ===== --}}
                <div class="cek-quota">
                    <div class="cek-quota-top">
                        <div><i class="bi bi-collection" style="color:#b45309;"></i> <b style="color:#92400e;">Sisa Pengecekan</b></div>
                        <div class="cek-quota-num">{{ $sisa }} <small>dari {{ $kuota }}</small></div>
                    </div>
                    <div class="cek-bar"><span style="width: {{ $kuota > 0 ? round($terpakai / $kuota * 100) : 0 }}%;"></span></div>
                    <div style="font-size:.78rem; color:var(--ph-muted); margin-top:8px;">
                        <i class="bi bi-info-circle"></i> Sudah dipakai {{ $terpakai }} kali. Tiap unggahan mengurangi 1 kuota (tanpa bayar lagi).
                    </div>
                </div>

                {{-- ===== Form unggah / kunci kuota ===== --}}
                @if ($order->bisaUploadPengecekan())
                <div class="pay-card">
                    <div class="pay-card-head" style="color:#b45309;"><i class="bi bi-cloud-arrow-up"></i> Unggah File untuk Diperiksa</div>
                    <div class="pay-card-body">
                        <div wire:key="cek-form-{{ $terpakai }}" x-data="{ fileName: '' }"
                            x-on:cek-file-ditolak.window="fileName = ''; $refs.dokumenInput && ($refs.dokumenInput.value = '')">

                            {{-- Pemilih jenis — hanya bila pesanan punya >1 jenis pemeriksaan.
                                 Tiap dokumen dipilih untuk pemeriksaan yang mana, dengan
                                 aturan bahasa & kuota masing-masing. --}}
                            @if (count($jenisTersisa) > 1)
                            <div class="cek-jenis">
                                <span class="cek-jenis-label">Dokumen ini untuk pemeriksaan:</span>
                                <div class="cek-jenis-opts">
                                    @foreach ($jenisTersisa as $kode => $info)
                                    <label class="cek-jenis-opt {{ $jenisPilihan === $kode ? 'is-on' : '' }}">
                                        <input type="radio" wire:model.live="jenisPilihan" value="{{ $kode }}">
                                        <span class="cek-jenis-name">{{ $info['label'] }}</span>
                                        <span class="cek-jenis-sisa">sisa {{ $info['sisa'] }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @error('jenisPilihan') <div style="color:#dc2626; font-size:.8rem; margin-top:6px;">{{ $message }}</div> @enderror
                                @if ($jenisPilihan === 'ai')
                                <div class="cek-jenis-note"><i class="bi bi-translate"></i> Dokumen untuk Cek AI wajib berbahasa Inggris.</div>
                                @endif
                            </div>
                            @endif

                            <label class="cek-drop">
                                <input type="file" wire:model="dokumen" accept=".pdf,.docx" class="cek-drop-input"
                                    x-ref="dokumenInput"
                                    x-on:change="fileName = ($event.target.files[0] && $event.target.files[0].name) || ''">

                                {{-- Sedang mengunggah --}}
                                <div wire:loading wire:target="dokumen" class="cek-drop-loading">
                                    <i class="bi bi-arrow-repeat cek-spin"></i> Mengunggah file…
                                </div>

                                {{-- Belum/sudah pilih file --}}
                                <div wire:loading.remove wire:target="dokumen">
                                    <div class="cek-drop-empty" x-show="!fileName">
                                        <i class="bi bi-cloud-arrow-up cek-drop-ic"></i>
                                        <div class="cek-drop-title">Pilih file atau seret ke sini</div>
                                        <div class="cek-drop-hint">PDF atau DOCX &middot; maksimal 20 MB</div>
                                    </div>
                                    <div class="cek-drop-file" x-show="fileName" style="display:none;">
                                        <i class="bi bi-file-earmark-check"></i>
                                        <span class="cek-drop-fname" x-text="fileName"></span>
                                        <span class="cek-drop-change"><i class="bi bi-arrow-repeat"></i> Ganti file</span>
                                    </div>
                                </div>
                            </label>
                            @error('dokumen') <div style="color:#dc2626; font-size:.8rem; margin-top:8px;">{{ $message }}</div> @enderror

                            {{-- Pengaturan pemeriksaan — hanya untuk layanan yang memakai exclude.
                                 Cek AI menilai teks utuh, jadi panel ini disembunyikan kecuali
                                 customer membeli add-on cek plagiasi. --}}
                            @if ($perluExclude)
                            <div class="cek-set">
                                <span class="cek-set-label">Kecualikan dari pemeriksaan</span>
                                <span class="cek-set-hint">Bagian yang dicentang tidak dihitung sebagai kemiripan. Biarkan apa adanya bila ragu.</span>

                                <div class="cek-chips">
                                    <label class="cek-chip">
                                        <input type="checkbox" wire:model="exclude_bibliografi">
                                        <span class="cek-chip-box"><i class="bi bi-check-lg"></i></span>
                                        <i class="bi bi-journal-bookmark cek-chip-ic"></i>
                                        <span>Exclude Daftar Pustaka</span>
                                    </label>

                                    <label class="cek-chip">
                                        <input type="checkbox" wire:model="exclude_kutipan">
                                        <span class="cek-chip-box"><i class="bi bi-check-lg"></i></span>
                                        <i class="bi bi-quote cek-chip-ic"></i>
                                        <span>Exclude Kutipan</span>
                                    </label>

                                    <label class="cek-chip">
                                        <input type="checkbox" wire:model.live="exclude_sumber_kecil">
                                        <span class="cek-chip-box"><i class="bi bi-check-lg"></i></span>
                                        <i class="bi bi-funnel cek-chip-ic"></i>
                                        <span>Exclude Source</span>
                                    </label>
                                </div>

                                @if ($exclude_sumber_kecil)
                                {{-- Ambang exclude source — pilihan siap pakai, ramah orang awam --}}
                                @php $ambNilai = (int) $ambang_nilai; @endphp
                                <div class="cek-amb">
                                    <div class="cek-amb-why">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <span>Kemiripan yang <b>sangat kecil</b> (misalnya hanya satu kalimat umum yang kebetulan sama) tidak ikut dihitung, supaya hasilnya lebih adil.</span>
                                    </div>

                                    {{-- 1) Pilih satuan --}}
                                    <span class="cek-amb-step">1. Hitung berdasarkan</span>
                                    <div class="cek-amb-unit">
                                        <button type="button" wire:click="$set('ambang_satuan','persen')"
                                            class="{{ $ambang_satuan === 'persen' ? 'is-on' : '' }}">Persen (%)</button>
                                        <button type="button" wire:click="$set('ambang_satuan','kata')"
                                            class="{{ $ambang_satuan === 'kata' ? 'is-on' : '' }}">Jumlah kata</button>
                                    </div>

                                    {{-- 2) Isi angka — satuan tampil di dalam kotak --}}
                                    <span class="cek-amb-step" style="margin-top:11px;">2. Abaikan sumber di bawah</span>
                                    <div class="cek-amb-inputwrap">
                                        <input type="number" min="1" inputmode="numeric"
                                            wire:model.live.debounce.400ms="ambang_nilai" class="cek-amb-num"
                                            placeholder="{{ $ambang_satuan === 'persen' ? '5' : '10' }}">
                                        <span class="cek-amb-suffix">{{ $ambang_satuan === 'persen' ? '%' : 'kata' }}</span>
                                    </div>

                                    @if ($ambNilai > 0)
                                    <div class="cek-amb-echo">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Sumber dengan kemiripan di bawah <b>{{ $ambNilai }}{{ $ambang_satuan === 'persen' ? '%' : ' kata' }}</b> akan diabaikan.
                                    </div>
                                    @endif

                                    @error('ambang_nilai') <div style="color:#dc2626; font-size:.78rem; margin-top:6px;">{{ $message }}</div> @enderror
                                </div>
                                @endif
                            </div>
                            @endif

                            <div class="cek-set">
                                <span class="cek-set-label">Catatan untuk admin</span>
                                <span class="cek-set-hint">Opsional — mis. “tolong exclude bab lampiran juga”.</span>
                                <textarea wire:model="catatan" rows="2" placeholder="Tulis permintaan khusus di sini…" class="cek-field"></textarea>
                                @error('catatan') <div style="color:#dc2626; font-size:.78rem; margin-top:5px;">{{ $message }}</div> @enderror
                            </div>

                            <button type="button" wire:click="uploadDokumen" wire:loading.attr="disabled"
                                wire:target="uploadDokumen,dokumen" class="ph-empty-btn" style="width:100%; justify-content:center; margin-top:20px;">
                                <span wire:loading.remove wire:target="uploadDokumen,dokumen"><i class="bi bi-cloud-arrow-up"></i> Kirim untuk Diperiksa</span>
                                <span wire:loading wire:target="uploadDokumen,dokumen"><i class="bi bi-hourglass-split"></i> Mengunggah…</span>
                            </button>
                        </div>
                    </div>
                </div>
                @elseif ($sisa <= 0 && $kuota > 0)
                <div class="pay-card" style="border-color:#fecaca; background:linear-gradient(180deg,#fef2f2,#fff);">
                    <div class="pay-card-body" style="text-align:center;">
                        <i class="bi bi-check2-all" style="font-size:1.8rem; color:#dc2626;"></i>
                        <p style="font-weight:700; color:#b91c1c; margin:8px 0 2px;">Jumlah pengecekan Anda sudah maksimal ({{ $terpakai }}/{{ $kuota }}).</p>
                        <p style="font-size:.84rem; color:var(--ph-muted); margin:0;">Anda tetap bisa mengunduh hasil di bawah kapan saja.</p>
                    </div>
                </div>
                @endif

                {{-- ===== Rincian layanan yang dipesan =====
                     Ditampilkan untuk SEMUA pesanan jasa — dulu hanya muncul bila ada
                     add-on/halaman, sehingga layanan polos (mis. cek AI tanpa add-on)
                     tak punya keterangan apa pun tentang apa yang dibeli. --}}
                @php
                    $itemJasa = $order->items->filter(fn ($i) => (bool) optional($i->product)->butuh_file);
                    $adaRincian = $itemJasa->isNotEmpty();
                @endphp
                @if ($adaRincian)
                <div class="pay-card" style="margin-top:14px;">
                    <div class="pay-card-head"><i class="bi bi-list-ul"></i> Layanan Anda</div>
                    <div class="pay-card-body">
                        @foreach ($itemJasa as $it)
                        <div class="lyn-item">
                            <div class="lyn-name">{{ $it->product_name }}</div>

                            {{-- Cakupan pengerjaan (halaman) --}}
                            @if ($it->jumlah_halaman)
                            <div class="lyn-row">
                                <span class="lyn-key">Dikerjakan</span>
                                <span class="lyn-chip is-scope">{{ $it->halaman_dihitung ?? $it->jumlah_halaman }} dari {{ $it->jumlah_halaman }} halaman</span>
                            </div>
                            @else
                            {{-- Jasa paket: tampilkan jumlah pengecekan yang dibeli --}}
                            <div class="lyn-row">
                                <span class="lyn-key">Paket</span>
                                <span class="lyn-chip is-scope">{{ $it->duration_value }}× pengecekan</span>
                            </div>
                            @endif

                            {{-- Halaman yang tidak dikerjakan --}}
                            @if ($it->halaman_dikecualikan)
                            <div class="lyn-row">
                                <span class="lyn-key">Dilewati</span>
                                <span class="lyn-chip is-skip">Halaman {{ $it->halamanDikecualikanRingkas() ?? $it->halaman_dikecualikan }}</span>
                            </div>
                            @endif

                            {{-- Tambahan berbayar (add-on) --}}
                            @if (! empty($it->addons))
                            <div class="lyn-row">
                                <span class="lyn-key">Tambahan</span>
                                <span class="lyn-vals">
                                    @foreach ($it->addons as $ad)
                                    <span class="lyn-chip is-addon"><i class="bi bi-plus-lg"></i> {{ $ad['nama'] ?? '-' }}</span>
                                    @endforeach
                                </span>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ===== Jaminan privasi & keaslian ===== --}}
                <div class="cek-trust">
                    <div class="cek-trust-head"><i class="bi bi-shield-lock-fill"></i> Jaminan Privasi &amp; Keamanan</div>
                    <ul class="cek-trust-list">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span><b>No Repository</b> — file Anda <b>tidak disimpan</b> ke database Turnitin. Jadi dokumen Anda tidak akan terdeteksi sebagai kemiripan pada pengecekan berikutnya.</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span><b>100% Turnitin</b> — pengecekan dilakukan memakai Turnitin asli, bukan alat lain.</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span><b>Aman &amp; rahasia</b> — dokumen bersifat pribadi dan <b>tidak disebarluaskan</b> ke pihak mana pun.</span>
                        </li>
                    </ul>
                </div>

                {{-- ===== Riwayat pengecekan ===== --}}
                <div class="pay-card" style="margin-top:14px;">
                    <div class="pay-card-head"><i class="bi bi-list-check"></i> Riwayat Pengecekan</div>
                    <div class="pay-card-body">
                        @forelse ($pengecekan as $up)
                        <div class="cek-item" wire:key="cek-row-{{ $up->id }}">
                            <div class="cek-item-top">
                                <i class="bi bi-file-earmark-text" style="color:var(--ph-orange); font-size:1.2rem;"></i>
                                <div class="cek-file">
                                    <b>{{ $up->nama_asli }}
                                        @if ($up->jenisLabel())
                                        <span class="cek-jenis-tag">{{ $up->jenisLabel() }}</span>
                                        @endif
                                    </b>
                                    <span>{{ $up->ukuranLabel() }} · {{ $up->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <span class="cek-chip {{ $up->statusWarna() }}">
                                    @if ($up->status === 'diproses') <span class="cek-pulse"></span> @else <i class="bi {{ $up->statusIcon() }}"></i> @endif
                                    {{ $up->statusLabel() }}
                                </span>
                            </div>

                            @if (in_array($up->status, ['menunggu', 'diproses']))
                            <div style="font-size:.78rem; color:var(--ph-muted); margin-top:8px;">
                                <i class="bi bi-clock"></i> Perkiraan {{ $estimasiWaktu }}. Hasil akan muncul otomatis di halaman ini.
                            </div>
                            @endif

                            @if ($up->status === 'selesai')
                            <div class="cek-meta">
                                @if (! is_null($up->persentase))
                                <span class="cek-persen"><i class="bi bi-graph-up"></i> Plagiasi: {{ $up->persentase }}%</span>
                                @endif
                                @if (! is_null($up->persentase_ai))
                                <span class="cek-persen" style="background:#e0f2fe; color:#0369a1;"><i class="bi bi-robot"></i> AI: {{ $up->persentase_ai }}%</span>
                                @endif
                                @if ($up->hasil_docx_path)
                                <a class="cek-dl" href="{{ route('jasa.cek.hasil-docx', ['token' => $order->share_token, 'upload' => $up->id]) }}">
                                    <i class="bi bi-file-earmark-word"></i> Dokumen Hasil
                                </a>
                                @endif
                                @if ($up->hasil_path)
                                <a class="cek-dl" href="{{ route('jasa.cek.hasil', ['token' => $order->share_token, 'upload' => $up->id]) }}">
                                    <i class="bi bi-download"></i> Hasil Plagiasi
                                </a>
                                @endif
                                @if ($up->hasil_ai_path)
                                <a class="cek-dl" style="background:#0ea5e9;" href="{{ route('jasa.cek.hasil-ai', ['token' => $order->share_token, 'upload' => $up->id]) }}">
                                    <i class="bi bi-robot"></i> Hasil AI
                                </a>
                                @endif
                            </div>
                            @endif

                            @if ($up->status === 'dibatalkan')
                            <div style="font-size:.78rem; color:var(--ph-muted); margin-top:8px;">
                                <i class="bi bi-info-circle"></i> Pengecekan dibatalkan — kuota Anda dikembalikan.
                            </div>
                            @endif

                            @if ($up->exclude_bibliografi || $up->exclude_kutipan || $up->exclude_sumber_kecil || $up->exclude_cover || $up->exclude_daftar_isi || $up->halaman_dikecualikan || $up->catatan)
                            <div class="cek-excl" style="margin-top:8px;">
                                <i class="bi bi-sliders"></i> Kecualikan: {{ $up->ringkasanExclude() }}
                                @if ($up->catatan) <br><i class="bi bi-chat-left-text"></i> Catatan: {{ $up->catatan }} @endif
                            </div>
                            @endif
                        </div>
                        @empty
                        <p style="text-align:center; color:var(--ph-muted); font-size:.88rem; margin:6px 0;">
                            <i class="bi bi-inbox"></i> Belum ada file yang diunggah. Silakan unggah file pertama Anda di atas.
                        </p>
                        @endforelse
                    </div>
                </div>

                {{-- ===== Simpan link ===== --}}
                <div class="pay-card" style="margin-top:14px;">
                    <div class="pay-card-head"><i class="bi bi-link-45deg"></i> Link Halaman Ini</div>
                    <div class="pay-card-body">
                        <p style="font-size:.83rem; color:var(--ph-muted); margin:0 0 4px;">Simpan link ini agar bisa kembali kapan saja (unggah &amp; unduh hasil):</p>
                        <div class="cek-linkbox">
                            <code id="cek-permalink">{{ url('/cek/'.$order->share_token) }}</code>
                            <button type="button" class="cek-copy" onclick="cekSalinLink()"><i class="bi bi-clipboard"></i> Salin</button>
                        </div>
                    </div>
                </div>

                <p class="cart-summary-note" style="justify-content:center; margin-top:16px;">
                    <i class="bi bi-shield-lock"></i> Link ini bersifat rahasia — jangan bagikan ke orang lain.
                </p>
            </div>
        </div>
    </section>

    <script>
        function cekToast(message, title, icon) {
            if (typeof window.phToast === 'function') window.phToast(message, title, icon);
            else if (typeof Swal !== 'undefined') Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2400, title: title, text: message });
        }
        function cekSalinLink() {
            var el = document.getElementById('cek-permalink');
            var txt = el ? el.textContent.trim() : '';
            var done = function () { cekToast('Simpan link ini untuk kembali kapan saja.', 'Link disalin', 'bi-clipboard-check'); };
            if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(txt).then(done).catch(done);
            else { var r=document.createRange(); r.selectNode(el); window.getSelection().removeAllRanges(); window.getSelection().addRange(r); try{document.execCommand('copy');}catch(e){} done(); }
        }
        (function () {
            if (window.__phCekBound) return;
            window.__phCekBound = true;
            function bind() {
                Livewire.on('cek-success', (e) => cekToast((e && (e.message ?? (Array.isArray(e) ? e[0]?.message : null))) || 'Dokumen terunggah.', 'Berhasil', 'bi-cloud-arrow-up-fill'));
                Livewire.on('cek-error', (e) => cekToast((e && (e.message ?? (Array.isArray(e) ? e[0]?.message : null))) || 'Gagal.', 'Gagal', 'bi-exclamation-triangle-fill'));
            }
            if (window.Livewire) bind(); else document.addEventListener('livewire:init', bind);
        })();
    </script>
</div>
