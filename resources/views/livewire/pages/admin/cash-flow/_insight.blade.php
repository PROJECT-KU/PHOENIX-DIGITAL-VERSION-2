{{--
    Panel Insight Cash Flow — seluruhnya dihitung PHP. Instan, gratis, dan tidak
    ada satu pun data yang keluar dari server.

    Isinya:
      1. Bacaan otomatis ($insightNarasi) + strip kuartal + saran ($insightSaran)
         — selalu tampil begitu halaman dibuka.
      2. Analisa Lengkap ($analisaLengkap) — muncul saat tombol ditekan:
         keuangan + proyeksi ke depan, prospek produk, promo, penyelesaian task,
         kesehatan bisnis (public + admin), dan rencana aksi.

    Angka yang dipakai sama persis dengan data yang difilter — panel ini hanya
    MEMBACA, tidak mengubah tabel, filter, periode, atau PDF apa pun.
--}}
<style>
    .insight-card {
        border: 1px solid rgba(124, 58, 237, .14);
        background:
            radial-gradient(120% 140% at 50% -10%, rgba(124, 58, 237, .07), transparent 55%),
            #fff;
    }

    /* ===== Aturan ikon: SEMUA ikon di panel ini dipusatkan dgn cara sama.
       .bi punya vertical-align:-.125em bawaan + glyph di ::before, jadi flex di
       pembungkus SAJA belum cukup. Kuncinya: <i>-nya JUGA dibuat flex, sehingga
       glyph dipusatkan oleh flexbox (mengabaikan metrik/baseline font). ===== */
    .insight-ico {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        line-height: 1;
    }

    .insight-ico i.bi {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        vertical-align: 0;
    }

    .insight-ico i.bi::before {
        display: block;
        line-height: 1;
    }

    /* Ikon kepala panel (besar, di tengah atas) */
    .insight-head-ico {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, #7c3aed, #4e46e5);
        color: #fff;
        font-size: 1.4rem;
        box-shadow: 0 6px 16px rgba(124, 58, 237, .28);
    }

    /* ===== Bacaan otomatis ===== */
    .insight-item {
        border-radius: 14px;
        padding: 14px 16px;
        border: 1px solid #eef0f4;
        background: #fbfbfd;
    }

    .insight-item + .insight-item {
        margin-top: 12px;
    }

    .insight-item.nada-bahaya {
        border-color: rgba(220, 38, 38, .22);
        background: rgba(254, 226, 226, .35);
    }

    .insight-item.nada-peringatan {
        border-color: rgba(217, 119, 6, .22);
        background: rgba(254, 243, 199, .35);
    }

    .insight-item.nada-baik {
        border-color: rgba(5, 150, 105, .22);
        background: rgba(209, 250, 229, .35);
    }

    .insight-item.nada-info {
        border-color: rgba(37, 99, 235, .18);
        background: rgba(219, 234, 254, .3);
    }

    .insight-dot {
        width: 36px;
        height: 36px;
        border-radius: 11px;
        font-size: 1.05rem;
        color: #fff;
    }

    .nada-bahaya .insight-dot { background: #dc2626; }
    .nada-peringatan .insight-dot { background: #d97706; }
    .nada-baik .insight-dot { background: #059669; }
    .nada-info .insight-dot { background: #2563eb; }

    .insight-item-judul {
        font-weight: 800;
        font-size: .95rem;
        color: #1e293b;
        line-height: 1.3;
    }

    .insight-item-teks {
        font-size: .88rem;
        color: #475569;
        line-height: 1.55;
        margin-top: 3px;
    }

    /* ===== Judul bagian (ikon kecil + label, dipusatkan vertikal) ===== */
    .insight-section-head {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 14px;
    }

    .insight-section-head .insight-ico {
        font-size: 1rem;
    }

    .insight-section-label {
        font-weight: 700;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #64748b;
    }

    /* ===== Kuartal ===== */
    .q-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .q-box {
        border: 1px solid #eef0f4;
        border-radius: 12px;
        padding: 14px 10px;
        text-align: center;
        background: #fff;
    }

    .q-box.q-aktif {
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, .12);
    }

    .q-box.q-kosong { opacity: .5; }

    .q-label {
        font-weight: 800;
        font-size: .82rem;
        letter-spacing: .5px;
        color: #7c3aed;
    }

    .q-rentang {
        font-size: .68rem;
        color: #94a3b8;
        margin-bottom: 8px;
    }

    .q-net {
        font-weight: 800;
        font-size: .92rem;
    }

    .q-net.minus { color: #dc2626; }
    .q-net.plus { color: #059669; }

    @media (max-width: 575.98px) {
        .q-strip { grid-template-columns: repeat(2, 1fr); }
    }

    /* ===== Saran ===== */
    .saran-box {
        border-radius: 14px;
        border: 1px solid rgba(16, 185, 129, .22);
        background: linear-gradient(135deg, rgba(16, 185, 129, .05), rgba(5, 150, 105, .03));
        padding: 18px;
    }

    .saran-head-ico {
        width: 36px;
        height: 36px;
        border-radius: 11px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        font-size: 1.05rem;
        box-shadow: 0 4px 12px rgba(5, 150, 105, .22);
    }

    .saran-item {
        padding: 12px 0;
    }

    .saran-item + .saran-item {
        border-top: 1px dashed rgba(16, 185, 129, .22);
    }

    /* Ikon saran kini badge bulat yang dipusatkan — seragam dgn dot bacaan */
    .saran-item-ico {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: rgba(16, 185, 129, .12);
        color: #059669;
        font-size: 1rem;
    }

    .saran-item-teks {
        font-size: .89rem;
        line-height: 1.6;
        color: #334155;
    }

    /* ===== Analisa Lengkap (tanpa AI) ===== */
    .lengkap-box {
        border-radius: 14px;
        border: 1px solid rgba(37, 99, 235, .2);
        background: linear-gradient(135deg, rgba(37, 99, 235, .04), rgba(124, 58, 237, .03));
        padding: 18px;
    }

    .lengkap-head-ico {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        font-size: 1.15rem;
        box-shadow: 0 4px 12px rgba(37, 99, 235, .22);
    }

    .btn-lengkap {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        border: none;
        color: #fff;
        font-weight: 700;
        border-radius: 12px;
        padding: 9px 18px;
        white-space: nowrap;
    }

    .btn-lengkap:hover { filter: brightness(1.06); color: #fff; }
    .btn-lengkap:disabled { opacity: .65; }

    .lengkap-seksi {
        background: #fff;
        border: 1px solid #eef0f4;
        border-left: 4px solid #94a3b8;
        border-radius: 12px;
        padding: 13px 16px;
        margin-top: 12px;
    }

    .lengkap-seksi.nada-baik { border-left-color: #059669; }
    .lengkap-seksi.nada-peringatan { border-left-color: #d97706; }
    .lengkap-seksi.nada-bahaya { border-left-color: #dc2626; }
    .lengkap-seksi.nada-info { border-left-color: #2563eb; }

    .lengkap-seksi-ico {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        background: rgba(37, 99, 235, .1);
        color: #2563eb;
        font-size: .95rem;
    }

    .nada-baik .lengkap-seksi-ico { background: rgba(5, 150, 105, .12); color: #059669; }
    .nada-peringatan .lengkap-seksi-ico { background: rgba(217, 119, 6, .12); color: #d97706; }
    .nada-bahaya .lengkap-seksi-ico { background: rgba(220, 38, 38, .12); color: #dc2626; }

    .lengkap-seksi-judul {
        font-weight: 800;
        font-size: .9rem;
        color: #1e293b;
    }

    .lengkap-poin {
        margin: 10px 0 0;
        padding-left: 18px;
    }

    .lengkap-poin li {
        font-size: .87rem;
        line-height: 1.55;
        color: #334155;
        margin-bottom: 7px;
    }

    .lengkap-poin li:last-child { margin-bottom: 0; }
</style>

<div class="card border-0 shadow-sm rounded-4 insight-card overflow-hidden mb-4">
    <div class="card-body p-4">

        {{-- ============ KEPALA PANEL (dipusatkan) ============ --}}
        <div class="text-center mb-4">
            <div class="insight-ico insight-head-ico mx-auto mb-3">
                <i class="bi bi-lightbulb"></i>
            </div>
            <h5 class="fw-bold mb-1 text-dark">Insight Cash Flow</h5>
            <p class="text-muted mb-0 mx-auto" style="font-size: .85rem; max-width: 640px;">
                Bacaan otomatis dari angka periode <b>{{ $insightData['periode']['label'] }}</b> —
                dibandingkan periode sebelumnya, antar kuartal, dan sejak awal tahun.
            </p>
        </div>

        {{-- ============ LAPIS 1: BACAAN OTOMATIS (SELALU ADA) ============ --}}
        <div class="mb-4">
            @foreach ($insightNarasi as $n)
                <div class="insight-item nada-{{ $n['nada'] }} d-flex align-items-start gap-3">
                    <div class="insight-ico insight-dot">
                        @switch($n['nada'])
                            @case('bahaya') <i class="bi bi-exclamation-octagon-fill"></i> @break
                            @case('peringatan') <i class="bi bi-exclamation-triangle-fill"></i> @break
                            @case('baik') <i class="bi bi-check-circle-fill"></i> @break
                            @default <i class="bi bi-info-circle-fill"></i>
                        @endswitch
                    </div>
                    <div>
                        <div class="insight-item-judul">{{ $n['judul'] }}</div>
                        <div class="insight-item-teks">{{ $n['teks'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ============ KUARTAL (setahun dibagi 4) ============ --}}
        <div class="mb-4">
            <div class="insight-section-head">
                <span class="insight-ico"><i class="bi bi-calendar3"></i></span>
                <span class="insight-section-label">Per Kuartal</span>
            </div>
            <div class="q-strip">
                @foreach ($insightData['kuartal'] as $q)
                    <div class="q-box {{ $q['aktif'] ? 'q-aktif' : '' }} {{ $q['kosong'] ? 'q-kosong' : '' }}">
                        <div class="q-label">{{ $q['label'] }}</div>
                        <div class="q-rentang">{{ $q['rentang'] }}</div>
                        @if ($q['kosong'])
                            <div class="q-net text-muted">—</div>
                        @else
                            <div class="q-net {{ $q['net'] < 0 ? 'minus' : 'plus' }}">
                                {{ $q['net'] < 0 ? '−' : '+' }}Rp {{ number_format(abs($q['net']), 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ============ SARAN (dihitung dari angka) ============ --}}
        @if (! empty($insightSaran))
            <div class="saran-box mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="insight-ico saran-head-ico"><i class="bi bi-clipboard2-check-fill"></i></span>
                    <span class="fw-bold text-dark" style="font-size: .95rem;">Saran</span>
                </div>
                @foreach ($insightSaran as $s)
                    <div class="saran-item d-flex align-items-start gap-3">
                        <span class="insight-ico saran-item-ico"><i class="bi {{ $s['ikon'] }}"></i></span>
                        <div class="saran-item-teks">{{ $s['teks'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ============ ANALISA LENGKAP TANPA AI (instan, andal) ============ --}}
        <div class="lengkap-box mb-4">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="insight-ico lengkap-head-ico"><i class="bi bi-clipboard-data"></i></span>
                    <div>
                        <span class="fw-bold text-dark" style="font-size: .95rem;">Analisa Lengkap Menyeluruh</span>
                        <span class="text-muted d-block" style="font-size: .76rem;">Seluruh sistem — keuangan, produk, promo, task, bisnis + rencana ke depan. Langsung, tanpa menunggu.</span>
                    </div>
                </div>
                <button type="button" class="btn-lengkap insight-ico gap-1" wire:click="analisaTanpaAi" wire:loading.attr="disabled" wire:target="analisaTanpaAi">
                    <span class="insight-ico gap-1" wire:loading.remove wire:target="analisaTanpaAi">
                        <i class="bi bi-clipboard-data"></i>{{ ! empty($analisaLengkap) ? 'Muat Ulang' : 'Tampilkan Analisa Lengkap' }}
                    </span>
                    <span class="insight-ico gap-1" wire:loading wire:target="analisaTanpaAi">
                        <span class="spinner-border spinner-border-sm"></span>Menghitung…
                    </span>
                </button>
            </div>

            @forelse ($analisaLengkap as $seksi)
                <div class="lengkap-seksi nada-{{ $seksi['nada'] }}">
                    <div class="lengkap-seksi-head d-flex align-items-center gap-2">
                        <span class="insight-ico lengkap-seksi-ico"><i class="bi {{ $seksi['ikon'] }}"></i></span>
                        <span class="lengkap-seksi-judul">{{ $seksi['judul'] }}</span>
                    </div>
                    <ul class="lengkap-poin">
                        @foreach ($seksi['poin'] as $poin)
                            <li>{{ $poin }}</li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="text-muted mb-0" style="font-size: .86rem;">
                    Tekan tombol untuk melihat analisa menyeluruh dari seluruh sistem — dihitung langsung dari angkamu,
                    selalu akurat, nol data keluar dari server.
                </p>
            @endforelse
        </div>


    </div>
</div>
