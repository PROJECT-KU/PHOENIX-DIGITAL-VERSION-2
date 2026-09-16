{{-- Agenda kegiatan milik pengguna yang sedang masuk.
     Satu berkas dipakai dua dasbor (karyawan & perusahaan): pengurus pun ikut
     diundang rapat, dan menyalin markupnya berarti dua tempat yang harus
     diubah setiap kali rincian kegiatan bertambah. --}}
@if (isset($agendaSaya))
{{-- Memakai kerangka kartu dasbor (dsb-kartu): sebelumnya .card bawaan
     template, sehingga radius, bingkai, dan bayangannya berbeda dari kartu di
     sekelilingnya — terlihat seperti tempelan dari halaman lain. --}}
<div class="dsb-kartu h-100">
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

    <div class="dsb-kartu-kepala">
        <div class="dsb-kartu-kepala-kiri">
            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-calendar3"></i></span>
            <div>
                <h3 class="dsb-kartu-judul">Agenda Saya</h3>
                <span class="dsb-kartu-sub">Kegiatan yang Anda ikuti</span>
            </div>
        </div>

        @if (\Illuminate\Support\Facades\Route::has('admin.kegiatan.index') && auth()->user()->hasPermission('view_kegiatan'))
        <a href="{{ route('admin.kegiatan.index') }}" wire:navigate class="dsb-tautan" style="--c: #7c3aed">
            <span>Kalender</span><i class="bi bi-arrow-right"></i>
        </a>
        @endif
    </div>

    <div class="dsb-kartu-isi">

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
