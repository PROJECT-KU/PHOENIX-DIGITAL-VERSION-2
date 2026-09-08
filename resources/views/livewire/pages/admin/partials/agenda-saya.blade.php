{{-- Agenda kegiatan milik pengguna yang sedang masuk.
     Satu berkas dipakai dua dasbor (karyawan & perusahaan): pengurus pun ikut
     diundang rapat, dan menyalin markupnya berarti dua tempat yang harus
     diubah setiap kali rincian kegiatan bertambah. --}}
@if (isset($agendaSaya))
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <style>
        /* Inline: public/build tidak ikut terdeploy ke server. */
        .ag-baris {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px; border-radius: 13px;
            border: 1px solid #eef1f6; background: #fff; margin-bottom: 9px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .ag-baris:last-child { margin-bottom: 0; }
        .ag-baris:hover { border-color: #e3e8ef; box-shadow: 0 4px 12px -6px rgba(15,23,42,.18); }
        .ag-pita { width: 4px; align-self: stretch; border-radius: 999px; flex: 0 0 auto; background: var(--ag-warna); }
        .ag-isi { flex: 1 1 auto; min-width: 0; }
        .ag-isi b { display: block; font-size: .9rem; color: #1e293b; line-height: 1.35; }
        .ag-meta { font-size: .77rem; color: #94a3b8; display: flex; flex-wrap: wrap; gap: 2px 12px; margin-top: 3px; }
        .ag-meta span { display: inline-flex; align-items: center; gap: 5px; }
        .ag-meta i.bi { line-height: 1; font-size: .82rem; }
        .ag-meta i.bi::before { display: block; line-height: 1; }
        .ag-lencana {
            font-size: .68rem; font-weight: 700; padding: 3px 9px; border-radius: 999px;
            flex: 0 0 auto; background: var(--ag-lembut); color: var(--ag-warna);
        }
        /* Hari ini perlu terbaca sekilas: itu satu-satunya baris yang menuntut
           tindakan hari ini, sisanya sekadar bekal perencanaan. */
        .ag-hariini { border-color: #ddd8fb; background: #fbfaff; }
        .ag-tanda-hariini {
            font-size: .66rem; font-weight: 800; letter-spacing: .05em;
            color: #6d28d9; text-transform: uppercase;
        }
        .ag-kosong { text-align: center; padding: 26px 14px; color: #a8b3c4; font-size: .85rem; }
        .ag-kosong i.bi { display: block; font-size: 1.6rem; margin-bottom: 9px; color: #d7dee8; line-height: 1; }
    </style>

    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="stat-icon-wrapper"
                    style="width: 38px; height: 38px; font-size: 1rem; border-radius: 11px; background: linear-gradient(135deg,#a78bfa,#6d28d9); color:#fff;">
                    <i class="bi bi-calendar3"></i>
                </span>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Agenda Saya</h6>
                    <span class="text-muted" style="font-size: 0.85rem;">Kegiatan yang Anda ikuti</span>
                </div>
            </div>

            @if (\Illuminate\Support\Facades\Route::has('admin.kegiatan.index') && auth()->user()->hasPermission('view_kegiatan'))
            <a href="{{ route('admin.kegiatan.index') }}" wire:navigate
                class="text-decoration-none fw-semibold" style="font-size: .82rem; color:#6d28d9;">
                Lihat kalender
            </a>
            @endif
        </div>

        @forelse ($agendaSaya as $k)
        <div class="ag-baris {{ $k->mulai->isToday() ? 'ag-hariini' : '' }}"
            style="--ag-warna:{{ $k->warna() }}; --ag-lembut:{{ $k->lembut() }};">
            <span class="ag-pita"></span>
            <div class="ag-isi">
                <b>{{ $k->judul }}</b>
                <div class="ag-meta">
                    @if ($k->mulai->isToday())
                    <span class="ag-tanda-hariini">Hari ini</span>
                    @else
                    <span><i class="bi bi-calendar-event"></i>
                        {{ $k->mulai->locale('id')->translatedFormat('D, d M') }}</span>
                    @endif
                    <span><i class="bi bi-clock"></i> {{ $k->rentangWaktu() }}</span>
                    @if ($k->lokasi)
                    <span><i class="bi bi-geo-alt"></i> {{ $k->lokasi }}</span>
                    @endif
                </div>
            </div>
            <span class="ag-lencana">{{ $k->label() }}</span>
        </div>
        @empty
        <div class="ag-kosong">
            <i class="bi bi-calendar2-check"></i>
            Tidak ada kegiatan yang menunggu Anda.
        </div>
        @endforelse
    </div>
</div>
@endif
