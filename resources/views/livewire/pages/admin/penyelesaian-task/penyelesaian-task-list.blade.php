@section('title')
Penyelesaian Task || PT. Asthana Cipta Mandiri
@stop
<div wire:poll.30s="recompute">
    <style>
        /* ===== Stat / summary ===== */
        .pt-stat {
            border: 1px solid #eef0f7;
            border-radius: 18px;
            padding: 16px 18px;
            background: linear-gradient(135deg, #ffffff, #fbfcff);
            display: flex;
            align-items: center;
            gap: 14px;
            height: 100%;
            box-shadow: 0 6px 18px rgba(108, 99, 255, .05);
        }

        .pt-stat .ico {
            width: 46px;
            height: 46px;
            border-radius: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.25rem;
            flex-shrink: 0;
            line-height: 1;
        }

        .pt-stat .ico i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
        }

        .pt-stat .ico i.bi::before {
            display: block;
            line-height: 1;
        }

        .pt-stat .lbl {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #94a3b8;
        }

        .pt-stat .val {
            font-weight: 800;
            font-size: 1.12rem;
            color: #1e293b;
            line-height: 1.1;
        }

        .g-green { background: linear-gradient(135deg, #10b981, #059669); }
        .g-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .g-purple { background: linear-gradient(135deg, #7c3aed, #4e46e5); }
        .g-slate { background: linear-gradient(135deg, #64748b, #475569); }

        /* ===== Kartu karyawan ===== */
        .pt-emp {
            border: 1px solid #eef0f7;
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #ffffff, #fbfcff);
            box-shadow: 0 6px 18px rgba(108, 99, 255, .05);
            transition: .18s;
        }

        .pt-emp:hover { box-shadow: 0 12px 28px rgba(76, 29, 149, .09); }
        .pt-emp.locked { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }

        .pt-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #7c3aed, #4e46e5);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
        }

        .pt-bonus-pill {
            background: linear-gradient(135deg, rgba(16, 185, 129, .12), rgba(5, 150, 105, .06));
            border: 1px solid rgba(16, 185, 129, .25);
            border-radius: 14px;
            padding: 6px 14px;
            text-align: right;
        }

        .pt-task-table thead th {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: #94a3b8;
            border-bottom: 1px solid #eef0f7;
            padding-bottom: 8px;
        }

        .pt-task-table tbody tr { border-bottom: 1px solid #f6f7fb; }
        .pt-task-table tbody tr:last-child { border-bottom: 0; }
        .pt-task-table td { padding: 10px 6px; }

        .pt-actbtn {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #eef0f7;
            background: #fff;
            transition: .15s;
        }

        .pt-actbtn:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0, 0, 0, .08); }
        .pt-cmt-badge { position: absolute; top: -6px; right: -6px; min-width: 17px; height: 17px; padding: 0 4px; border-radius: 999px; background: #ef4444; color: #fff; font-size: .62rem; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(239, 68, 68, .4); }

        .pt-actbtn i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
        }

        .pt-actbtn i.bi::before {
            display: block;
            line-height: 1;
        }

        /* ===== Modal glossy ===== */
        .pt-modal-back { position: fixed; inset: 0; background: rgba(15, 23, 42, .5); backdrop-filter: blur(2px); z-index: 1055; }
        .pt-modal { position: fixed; inset: 0; z-index: 1056; display: flex; align-items: flex-start; justify-content: center; padding: 4vh 12px; overflow-y: auto; }
        .pt-modal-card { background: #fff; border-radius: 22px; width: 100%; max-width: 560px; box-shadow: 0 30px 70px rgba(15, 23, 42, .32); overflow: hidden; }
        .pt-modal-head { padding: 20px 24px; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; display: flex; align-items: flex-start; justify-content: space-between; }
        .pt-modal-head h5, .pt-modal-head small, .pt-modal-head i.bi { color: #fff !important; }
        .pt-modal-head i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .pt-modal-head i.bi::before { line-height: 1; }
        .pt-modal-head .btn-close { filter: invert(1) grayscale(1) brightness(2); opacity: .85; }
        .pt-cmt { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f4f6fb; }
        .pt-cmt-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }

        /* ===== Diskusi (chat glossy) ===== */
        .pc-section-lbl { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; }
        .pc-thread { max-height: 340px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding: 4px 2px; }
        .pc-msg { display: flex; gap: 8px; max-width: 84%; }
        .pc-msg.right { align-self: flex-end; flex-direction: row-reverse; }
        .pc-av { width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .8rem; }
        .pc-av.adm { background: linear-gradient(135deg, #7c3aed, #4e46e5); }
        .pc-av.kar { background: linear-gradient(135deg, #0ea5e9, #2563eb); }
        .pc-bubble { background: #f4f6fb; border-radius: 14px; padding: 9px 13px; font-size: .87rem; color: #1e293b; }
        .pc-msg.right .pc-bubble { background: linear-gradient(135deg, rgba(124, 58, 237, .12), rgba(78, 70, 229, .07)); }
        .pc-bubble.pc-bubble-revisi, .pc-msg.right .pc-bubble.pc-bubble-revisi { background: linear-gradient(135deg, rgba(245, 158, 11, .16), rgba(217, 119, 6, .08)); border: 1px solid rgba(245, 158, 11, .45); }
        .pc-bubble .meta { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 2px; }
        .pc-bubble .who { font-weight: 700; font-size: .75rem; color: #334155; }
        .pc-bubble .when { font-size: .68rem; color: #94a3b8; white-space: nowrap; }
        .pc-role { font-size: .58rem; font-weight: 800; padding: 1px 6px; border-radius: 6px; text-transform: uppercase; letter-spacing: .3px; }
        .pc-role.adm { background: #ede9fe; color: #6d28d9; }
        .pc-role.kar { background: #e0f2fe; color: #0369a1; }

        /* ===== Composer ===== */
        .pc-composer { border: 1px solid #e6e8f2; border-radius: 14px; padding: 6px 14px; background: #fff; box-shadow: 0 4px 14px rgba(108, 99, 255, .05); transition: .15s; }
        .pc-composer:focus-within { border-color: #c7d2fe; box-shadow: 0 0 0 .18rem rgba(124, 58, 237, .12); }
        .pc-composer textarea, .pc-composer textarea:focus { border: none !important; outline: none !important; box-shadow: none !important; background: transparent; }
        .pc-composer textarea { resize: none; font-size: .9rem; line-height: 1.5; padding: 9px 0; text-align: left; max-height: 120px; }
        .pc-input-ico { color: #a3a9bd; font-size: 1rem; line-height: 1; flex-shrink: 0; }
        .pc-attach-chip { display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; border-radius: 8px; padding: 3px 8px; font-size: .76rem; color: #475569; }
        .pc-iconbtn { width: 38px; height: 38px; border-radius: 10px; border: 1px solid #eef0f7; background: #fff; color: #64748b; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: .15s; }
        .pc-iconbtn:hover { border-color: #c7d2fe; color: #6d28d9; }
        .pc-iconbtn i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .pc-send { border: none; border-radius: 10px; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; padding: 9px 18px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 6px 14px rgba(124, 58, 237, .28); transition: .15s; }
        .pc-send:hover { filter: brightness(1.05); transform: translateY(-1px); }
        .pc-send:disabled { opacity: .7; }
        .pc-send i.bi { display: inline-flex; align-items: center; line-height: 1; }

        /* ===== Picker karyawan (seragam dgn Order) ===== */
        .of-picker-btn { cursor: pointer; }
        .of-picker-btn::after { content: "\F282"; font-family: "bootstrap-icons"; float: right; color: #94a3b8; font-size: .8rem; }
        .of-pick-list { max-height: 320px; overflow-y: auto; text-align: left; display: flex; flex-direction: column; gap: .4rem; padding: .2rem; }
        .of-pick-item { display: block; width: 100%; text-align: left; border: 1px solid #e6e8f2; background: #fff; border-radius: 12px; padding: .7rem .9rem; font-weight: 600; color: #1e293b; font-size: .92rem; transition: all .15s ease; }
        .of-pick-item:hover { border-color: #6c63ff; background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.04)); transform: translateY(-1px); }
        .of-pick-empty { text-align: center; color: #94a3b8; padding: 1.5rem; font-size: .9rem; }
        /* ===== Picker kategori/label: baris + hapus + tambah ===== */
        .of-pick-row { display: flex; align-items: stretch; gap: .4rem; }
        .of-pick-row .of-pick-item { flex: 1 1 auto; width: auto; }
        .of-pick-del { flex: 0 0 auto; width: 44px; padding: 0; border: 1px solid #fee2e2; background: #fff5f5; color: #ef4444; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; transition: all .15s ease; }
        .of-pick-del:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: translateY(-1px); }
        .of-pick-add { display: flex; gap: .5rem; align-items: stretch; }
        .of-pick-add .form-control { flex: 1 1 auto; border-radius: 12px; }
        .of-pick-addbtn { flex: 0 0 auto; border-radius: 12px; font-weight: 600; white-space: nowrap; box-shadow: 0 6px 14px rgba(124, 58, 237, .22); display: inline-flex; align-items: center; justify-content: center; }
        .of-pick-del i.bi, .of-pick-addbtn i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .of-pick-msg { color: #ef4444; font-size: .82rem; margin-top: .35rem; min-height: 1rem; text-align: left; }
        .of-pick-confirm { display: flex; align-items: center; gap: .5rem; width: 100%; padding: .5rem .8rem; border: 1px dashed #fca5a5; border-radius: 12px; background: #fff5f5; color: #b91c1c; font-weight: 600; font-size: .88rem; }
        .of-pick-confirm span { margin-right: auto; }

        /* ===== Dropzone lampiran ===== */
        .tw-drop { position: relative; border: 2px dashed #dbe0ef; border-radius: 14px; background: linear-gradient(135deg, #fbfcff, #f7f8ff); padding: 20px 16px; text-align: center; transition: .18s; cursor: pointer; }
        .tw-drop:hover { border-color: #7c3aed; background: #f6f4ff; }
        .tw-drop input[type=file] { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .tw-drop .tw-drop-ico { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 6px; }
        .tw-drop .tw-drop-ico i.bi { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; line-height: 1; }
        .tw-thumb { position: relative; width: 78px; }
        .tw-thumb .media { width: 78px; height: 78px; border-radius: 12px; border: 1px solid #eef0f7; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .tw-thumb .media img { width: 100%; height: 100%; object-fit: cover; }
        .tw-thumb .media i.bi { font-size: 1.5rem; color: #94a3b8; }
        .tw-thumb .cap { font-size: .68rem; color: #64748b; text-align: center; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tw-thumb .rm { position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; border-radius: 50%; background: #ef4444; color: #fff; border: 2px solid #fff; display: flex; align-items: center; justify-content: center; font-size: .6rem; box-shadow: 0 2px 5px rgba(239,68,68,.4); }
        .tw-thumb .badge-new { position: absolute; bottom: 24px; left: 4px; font-size: .58rem; background: #10b981; color: #fff; padding: 1px 5px; border-radius: 6px; }
    </style>

    @php
        $badgeBonus = ['tepat_waktu'=>'success','terlambat'=>'warning','tidak_selesai'=>'danger','tidak_ada_info'=>'primary'];
        $labelBonus = ['tepat_waktu'=>'Tepat Waktu','terlambat'=>'Terlambat','tidak_selesai'=>'Tidak Selesai','tidak_ada_info'=>'Berjalan'];
        $badgeProg = ['belum'=>'secondary','dikerjakan'=>'info','selesai'=>'success'];
        $labelProg = ['belum'=>'Belum Dikerjakan','dikerjakan'=>'Dikerjakan','selesai'=>'Selesai'];
        $badgeBobot = ['ringan'=>'success','sedang'=>'warning','berat'=>'danger'];
    @endphp

    <div class="container-fluid">
        {{-- ===== HEADER ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-column flex-md-row gap-3">
                <div class="title-wrapper text-center text-md-start">
                    <h3 class="gradient-text fw-bold mb-1">Penyelesaian Task</h3>
                    <p class="text-muted mb-0 small">Satu pool budget dibagi ke semua karyawan — bobot &amp; status otomatis dari deadline.</p>
                </div>
                <button type="button" class="btn btn-success rounded-pill px-4 d-inline-flex align-items-center gap-2 shadow-sm pt-terapkan-btn">
                    <i class="bi bi-check2-circle"></i> Terapkan ke Gaji
                </button>
            </div>
        </div>

        {{-- ===== FILTER + BUDGET ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 px-4">
                <div class="d-flex align-items-center gap-2 text-dark fw-semibold mb-3">
                    <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0" style="width:40px;height:40px;font-size:1.1rem;border-radius:12px;">
                        <i class="bi bi-funnel"></i>
                    </span>
                    <span>Filter Periode</span>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label mb-1 fw-semibold text-muted" style="font-size:.78rem;">Bulan</label>
                        <select wire:model.live="bulan" class="form-select rounded-3">
                            @foreach($daftarBulan as $num => $nama)<option value="{{ $num }}">{{ $nama }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label mb-1 fw-semibold text-muted" style="font-size:.78rem;">Tahun</label>
                        <select wire:model.live="tahun" class="form-select rounded-3">
                            @foreach($daftarTahun as $th)<option value="{{ $th }}">{{ $th }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1 fw-semibold text-muted" style="font-size:.78rem;">Budget Pool (semua karyawan)</label>
                        <div class="position-relative">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3" style="pointer-events:none; z-index:5;">Rp</span>
                            <input type="text" wire:model.live.blur="budget" class="form-control rupiah rounded-3 fw-bold" style="padding-left:42px;" placeholder="0">
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <button type="button" wire:click="openCreateTask" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                            <i class="bi bi-plus-lg"></i> Tambah Task
                        </button>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-6 col-md-3"><div class="pt-stat"><span class="ico g-purple"><i class="bi bi-wallet2"></i></span><div><div class="lbl">Pool</div><div class="val">Rp {{ number_format($ringkasan['pool'],0,',','.') }}</div></div></div></div>
                    <div class="col-6 col-md-3"><div class="pt-stat"><span class="ico g-green"><i class="bi bi-cash-coin"></i></span><div><div class="lbl">Terpakai</div><div class="val text-success">Rp {{ number_format($ringkasan['terpakai'],0,',','.') }}</div></div></div></div>
                    <div class="col-6 col-md-3"><div class="pt-stat"><span class="ico g-blue"><i class="bi bi-piggy-bank"></i></span><div><div class="lbl">Sisa</div><div class="val text-primary">Rp {{ number_format($ringkasan['sisa'],0,',','.') }}</div></div></div></div>
                    <div class="col-6 col-md-3"><div class="pt-stat"><span class="ico g-slate"><i class="bi bi-lock-fill"></i></span><div><div class="lbl">Terkunci (Final)</div><div class="val text-secondary">Rp {{ number_format($ringkasan['lockedBonus'],0,',','.') }}</div></div></div></div>
                </div>
            </div>
        </div>

        {{-- ===== DAFTAR KARYAWAN + TASK ===== --}}
        @forelse ($rows as $row)
        <div class="pt-emp {{ $row['locked'] ? 'locked' : '' }}">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="pt-avatar">{{ strtoupper(substr($row['nama'],0,1)) }}</span>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:1.02rem;">{{ $row['nama'] }}</div>
                        <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                            @if($row['status_gaji'] !== 'none')
                            <span class="badge {{ $row['status_gaji']==='completed'?'bg-primary':'bg-warning' }} rounded-pill">Gaji: {{ ucfirst($row['status_gaji']) }}</span>
                            @else
                            <span class="badge bg-light text-secondary border rounded-pill"><i class="bi bi-exclamation-circle me-1"></i>Belum ada gaji</span>
                            @endif
                            @if($row['locked'])<span class="badge bg-secondary rounded-pill"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>@endif
                        </div>
                    </div>
                </div>
                <div class="pt-bonus-pill">
                    <div class="text-muted" style="font-size:.68rem; text-transform:uppercase; letter-spacing:.4px;">Bonus Task</div>
                    <div class="fw-bold text-success" style="font-size:1.15rem;">Rp {{ number_format($row['bonus'],0,',','.') }}</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table pt-task-table align-middle mb-0 text-center">
                    <thead>
                        <tr>
                            <th style="min-width:150px;">Task</th>
                            <th style="width:90px;">Bobot</th>
                            <th style="width:160px;">Durasi Pengerjaan</th>
                            <th style="width:170px;">Status</th>
                            <th style="width:110px;">Alokasi</th>
                            <th style="width:110px;">Dibayar</th>
                            <th style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($row['tasks'] as $t)
                        <tr>
                            <td class="fw-semibold text-dark">
                                {{ $t['nama'] }}
                                @if(!empty($t['kategori']))
                                <div class="mt-1 d-flex flex-wrap gap-1 justify-content-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary rounded-pill" style="font-size:.66rem;"><i class="bi bi-tag me-1"></i>{{ $t['kategori'] }}</span>
                                    @if(!empty($t['label']))<span class="badge bg-info-subtle text-info border border-info rounded-pill" style="font-size:.66rem;">{{ $t['label'] }}</span>@endif
                                </div>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $badgeBobot[$t['bobot']] ?? 'secondary' }}-subtle text-{{ $badgeBobot[$t['bobot']] ?? 'secondary' }} border border-{{ $badgeBobot[$t['bobot']] ?? 'secondary' }} rounded-pill text-capitalize">{{ $t['bobot'] }}</span></td>
                            <td style="font-size:.8rem;" class="text-muted">
                                <div><i class="bi bi-calendar-range me-1 text-primary"></i>{{ optional($t['deadline_mulai'])->translatedFormat('d M') }} – {{ optional($t['deadline_selesai'])->translatedFormat('d M Y') }}</div>
                                @if(!empty($t['durasi_hari']))
                                <div class="text-secondary mt-1" style="font-size:.72rem;"><i class="bi bi-hourglass-split me-1"></i>Durasi {{ $t['durasi_hari'] }} hari</div>
                                @endif
                                @php
                                    $tSelesai = $t['progress'] === 'selesai';
                                    $tLewat = ! $tSelesai && $t['bonus_status'] === 'tidak_selesai';
                                    $tSisa = $t['deadline_selesai'] ? (int) now()->startOfDay()->diffInDays($t['deadline_selesai']->copy()->startOfDay(), false) : null;
                                @endphp
                                <div class="mt-1">
                                    @if($tSelesai)
                                    <span class="badge bg-success-subtle text-success border border-success rounded-pill" style="font-size:.7rem;"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                    @elseif($tLewat)
                                    <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill" style="font-size:.7rem;"><i class="bi bi-exclamation-circle-fill me-1"></i>Terlambat</span>
                                    @elseif($tSisa === 0)
                                    <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill" style="font-size:.7rem;"><i class="bi bi-alarm-fill me-1"></i>Hari ini</span>
                                    @elseif(!is_null($tSisa))
                                    <span class="badge bg-info-subtle text-info border border-info rounded-pill" style="font-size:.7rem;"><i class="bi bi-hourglass-split me-1"></i>{{ $tSisa }} hari lagi</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-inline-flex flex-column align-items-center gap-1">
                                    <span class="badge bg-{{ $badgeProg[$t['progress']] ?? 'secondary' }}-subtle text-{{ $badgeProg[$t['progress']] ?? 'secondary' }} border border-{{ $badgeProg[$t['progress']] ?? 'secondary' }} rounded-pill text-nowrap">{{ $labelProg[$t['progress']] ?? ucfirst($t['progress']) }}</span>
                                    <span class="badge bg-{{ $badgeBonus[$t['bonus_status']] ?? 'secondary' }} rounded-pill text-nowrap">{{ $labelBonus[$t['bonus_status']] ?? $t['bonus_status'] }}</span>
                                </div>
                            </td>
                            <td>
                                @if($t['dikecualikan'])<span class="text-muted small">—</span>
                                @else<span class="text-muted">Rp {{ number_format($t['alokasi'],0,',','.') }}</span>@endif
                            </td>
                            <td class="fw-bold text-success">Rp {{ number_format($t['dibayar'],0,',','.') }}</td>
                            <td>
                                <div class="d-inline-flex gap-1">
                                    <button type="button" wire:click="openComments('{{ $t['task_id'] }}')" class="pt-actbtn text-info position-relative" title="Komentar">
                                        <i class="bi bi-chat-dots"></i>
                                        @if(!empty($t['komentar_baru']))
                                        <span class="pt-cmt-badge">{{ $t['komentar_baru'] > 9 ? '9+' : $t['komentar_baru'] }}</span>
                                        @endif
                                    </button>
                                    @if(!empty($t['locked_task']))
                                    <button type="button" wire:click="openReopen('{{ $t['task_id'] }}')" class="pt-actbtn text-warning" title="Buka kembali untuk revisi"><i class="bi bi-arrow-counterclockwise"></i></button>
                                    @endif
                                    <button type="button" wire:click="openEditTask('{{ $t['task_id'] }}')" class="pt-actbtn text-primary" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <button type="button" data-id="{{ $t['task_id'] }}" class="pt-actbtn text-danger pt-delete-btn" title="Hapus"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3" style="font-size:.85rem;"><i class="bi bi-inbox me-1"></i>Belum ada task untuk karyawan ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body py-5">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <div class="empty-state-icon-wrapper mb-3">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="color:#1e293b !important;">Belum Ada Task</h5>
                    <p class="text-muted mb-3" style="font-size:.95rem;">Belum ada task/gaji pada periode ini.</p>
                    <button type="button" wire:click="openCreateTask" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-plus-lg"></i> Tambah Task
                    </button>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- ===== Modal Task (create/edit) ===== --}}
    @if($showTaskModal)
    <div class="pt-modal-back" wire:click="$set('showTaskModal', false)"></div>
    <div class="pt-modal">
        <div class="pt-modal-card">
            <div class="pt-modal-head">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-{{ $editingTaskId ? 'pencil-square' : 'plus-circle' }} fs-5"></i>
                    <h5 class="fw-bold mb-0">{{ $editingTaskId ? 'Edit Task' : 'Tambah Task' }}</h5>
                </div>
                <button type="button" class="btn-close" wire:click="$set('showTaskModal', false)"></button>
            </div>
            <div class="p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Karyawan <span class="text-danger">*</span></label>
                    @php $selKar = $users->firstWhere('id', $t_user_id); @endphp
                    <button type="button" onclick="taskKaryawanPicker(this)"
                        class="form-select text-start of-picker-btn rounded-3 @error('t_user_id') is-invalid @enderror">
                        @if($selKar)
                        <span class="text-dark">{{ $selKar->name }}</span>
                        @else
                        <span class="text-muted">Pilih karyawan</span>
                        @endif
                    </button>
                    @error('t_user_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Task <span class="text-danger">*</span></label>
                    <input type="text" wire:model="t_nama" class="form-control rounded-3 @error('t_nama') is-invalid @enderror" placeholder="Mis. Bikin laporan bulanan">
                    @error('t_nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea wire:model="t_deskripsi" rows="2" class="form-control rounded-3" placeholder="Rincian task (opsional)"></textarea>
                </div>

                {{-- Kategori & Label — popup picker glossy (pola sama seperti pemilih karyawan) --}}
                @php
                    $selCat = $categories->firstWhere('id', (int) $t_category_id);
                    $selLab = $categoryLabels->firstWhere('id', (int) $t_label_id);
                @endphp
                <div id="taskPickData" hidden
                    data-categories='@json($categories->map(fn ($c) => ['id' => (string) $c->id, 'name' => $c->nama])->values())'
                    data-labels='@json($categoryLabels->map(fn ($l) => ['id' => (string) $l->id, 'name' => $l->nama])->values())'
                    data-selcat="{{ $t_category_id }}"></div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kategori</label>
                        <button type="button" onclick="taskKategoriPicker(this)" class="form-select text-start of-picker-btn rounded-3">
                            @if($selCat)<span class="text-dark">{{ $selCat->nama }}</span>
                            @else<span class="text-muted">Pilih kategori</span>@endif
                        </button>
                    </div>

                    @if($t_category_id)
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Label <span class="text-muted fw-normal" style="font-size:.8rem;">— mis. bug / improvement</span></label>
                        <button type="button" onclick="taskLabelPicker(this)" class="form-select text-start of-picker-btn rounded-3">
                            @if($selLab)<span class="text-dark">{{ $selLab->nama }}</span>
                            @else<span class="text-muted">Pilih label</span>@endif
                        </button>
                    </div>
                    @endif
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Lampiran <span class="text-muted fw-normal" style="font-size:.8rem;">— gambar/file, bisa banyak</span></label>

                    <div class="tw-drop" wire:loading.class="opacity-50" wire:target="newFiles">
                        <input type="file" wire:model="newFiles" multiple class="task-file-input" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt">
                        <span class="tw-drop-ico"><i class="bi bi-cloud-arrow-up"></i></span>
                        <div class="fw-semibold text-dark" style="font-size:.9rem;">Klik untuk pilih gambar / file</div>
                        <div class="text-muted" style="font-size:.76rem;">Bisa pilih banyak sekaligus &amp; menumpuk · <b>maks 2 MB</b> per file</div>
                        <div wire:loading wire:target="newFiles" class="text-primary small mt-1"><span class="spinner-border spinner-border-sm me-1"></span>Mengunggah...</div>
                    </div>
                    @error('newFiles.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('t_files.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                    @if(($editingTaskId && $editAttachments->count()) || !empty($t_files))
                    <div class="d-flex flex-wrap gap-3 mt-3">
                        {{-- Lampiran lama (mode edit) --}}
                        @if($editingTaskId)
                        @foreach($editAttachments as $att)
                        <div class="tw-thumb">
                            <a href="{{ Storage::url($att->path) }}" target="_blank" class="d-block text-decoration-none">
                                <div class="media">
                                    @if($att->isImage())<img src="{{ Storage::url($att->path) }}" alt="">@else<i class="bi bi-file-earmark-text"></i>@endif
                                </div>
                            </a>
                            <div class="cap">{{ $att->name }}</div>
                            <button type="button" wire:click="removeAttachment('{{ $att->id }}')" class="rm" title="Hapus"><i class="bi bi-x"></i></button>
                        </div>
                        @endforeach
                        @endif

                        {{-- File baru yang dipilih (preview) --}}
                        @foreach($t_files as $i => $file)
                        @php $isImg = str_starts_with((string) $file->getMimeType(), 'image/'); @endphp
                        <div class="tw-thumb">
                            <div class="media">
                                @if($isImg)<img src="{{ $file->temporaryUrl() }}" alt="">@else<i class="bi bi-file-earmark-arrow-up"></i>@endif
                            </div>
                            <span class="badge-new">Baru</span>
                            <div class="cap">{{ $file->getClientOriginalName() }}</div>
                            <button type="button" wire:click="removeNewFile({{ $i }})" class="rm" title="Batal"><i class="bi bi-x"></i></button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bobot</label>
                        <select wire:model="t_bobot" class="form-select rounded-3">
                            <option value="ringan">Ringan (1)</option>
                            <option value="sedang">Sedang (2)</option>
                            <option value="berat">Berat (3)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Deadline Mulai</label>
                        <input type="date" wire:model="t_deadline_mulai" class="form-control rounded-3 @error('t_deadline_mulai') is-invalid @enderror">
                        @error('t_deadline_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Deadline Selesai</label>
                        <input type="date" wire:model="t_deadline_selesai" class="form-control rounded-3 @error('t_deadline_selesai') is-invalid @enderror">
                        @error('t_deadline_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="px-4 pb-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-danger rounded-pill px-4" wire:click="$set('showTaskModal', false)">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-2" wire:click="saveTask">
                    <i class="bi bi-check2-circle"></i> Simpan
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Modal Buka Kembali (revisi) ===== --}}
    @if($showReopenModal && $reopenTask)
    <div class="pt-modal-back" wire:click="$set('showReopenModal', false)"></div>
    <div class="pt-modal">
        <div class="pt-modal-card">
            <div class="pt-modal-head">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-counterclockwise fs-5"></i>
                    <h5 class="fw-bold mb-0">Buka Kembali Task</h5>
                </div>
                <button type="button" class="btn-close" wire:click="$set('showReopenModal', false)"></button>
            </div>
            <div class="p-4">
                <p class="text-muted mb-3" style="font-size:.9rem;">
                    <b class="text-dark">{{ $reopenTask->nama }}</b> akan diaktifkan kembali menjadi
                    <span class="badge bg-info-subtle text-info border border-info rounded-pill">Dikerjakan</span>
                    agar karyawan bisa mengerjakan revisi. Alasan di bawah dikirim sebagai komentar &amp; notifikasi.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alasan revisi <span class="text-danger">*</span></label>
                    <textarea wire:model="reopen_alasan" rows="2" class="form-control rounded-3 @error('reopen_alasan') is-invalid @enderror" placeholder="Mis. Ada bug pada fitur login / revisi tanda tangan surat"></textarea>
                    @error('reopen_alasan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    @if($reopenTask->category && $reopenTask->category->labels->count())
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Label baru <span class="text-muted fw-normal" style="font-size:.8rem;">— opsional</span></label>
                        <select wire:model="reopen_label_id" class="form-select rounded-3">
                            <option value="">— Tanpa label —</option>
                            @foreach($reopenTask->category->labels as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Deadline baru <span class="text-danger">*</span></label>
                        <input type="date" wire:model="reopen_deadline" class="form-control rounded-3 @error('reopen_deadline') is-invalid @enderror">
                        @error('reopen_deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="px-4 pb-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-danger rounded-pill px-4" wire:click="$set('showReopenModal', false)">Batal</button>
                <button type="button" class="btn btn-warning rounded-pill px-4 d-inline-flex align-items-center gap-2" wire:click="bukaKembali">
                    <i class="bi bi-arrow-counterclockwise"></i> Buka Kembali
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Modal Komentar ===== --}}
    @if($showCommentModal && $activeTask)
    <div class="pt-modal-back" wire:click="$set('showCommentModal', false)"></div>
    <div class="pt-modal">
        <div class="pt-modal-card">
            <div class="pt-modal-head">
                <div>
                    <h5 class="fw-bold mb-1">{{ $activeTask->nama }}</h5>
                    <small class="text-white-50">{{ $activeTask->karyawan->name ?? '' }} · deadline {{ $activeTask->deadline_selesai?->translatedFormat('d M Y') }}</small>
                </div>
                <button type="button" class="btn-close" wire:click="$set('showCommentModal', false)"></button>
            </div>
            <div class="p-4">
                @if($activeTask->deskripsi)<p class="text-muted mb-2" style="font-size:.88rem;">{{ $activeTask->deskripsi }}</p>@endif
                @if($activeTask->attachments->count())
                <div class="mb-3">
                    <div class="fw-bold text-muted mb-1" style="font-size:.72rem; text-transform:uppercase; letter-spacing:.4px;"><i class="bi bi-paperclip me-1"></i>Lampiran</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($activeTask->attachments as $att)
                        @if($att->isImage())
                        <a href="{{ Storage::url($att->path) }}" target="_blank"><img src="{{ Storage::url($att->path) }}" style="width:56px;height:56px;object-fit:cover;border-radius:8px;"></a>
                        @else
                        <a href="{{ Storage::url($att->path) }}" target="_blank" class="border rounded-3 px-2 py-1 d-inline-flex align-items-center gap-1 text-decoration-none" style="font-size:.78rem;"><i class="bi bi-file-earmark"></i>{{ Str::limit($att->name, 18) }}</a>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="pc-section-lbl"><i class="bi bi-chat-dots me-1"></i>Diskusi</div>
                    <span class="badge bg-light text-secondary border rounded-pill">{{ $activeTask->comments->count() }} komentar</span>
                </div>
                <div class="pc-thread mb-3" wire:key="pc-thread-{{ $activeTask->comments->count() }}"
                    x-data x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight; })">
                    @forelse($activeTask->comments as $c)
                    @php
                        $mine = $c->user_id === auth()->id();
                        $isKar = $c->user_id === $activeTask->user_id;
                    @endphp
                    <div class="pc-msg {{ $mine ? 'right' : '' }}">
                        <div class="pc-av {{ $isKar ? 'kar' : 'adm' }}">{{ strtoupper(substr($c->user->name ?? '?',0,1)) }}</div>
                        <div class="pc-bubble {{ $c->type === 'revisi' ? 'pc-bubble-revisi' : '' }}">
                            <div class="meta">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="who">{{ $mine ? 'Anda' : ($c->user->name ?? '-') }}</span>
                                    <span class="pc-role {{ $isKar ? 'kar' : 'adm' }}">{{ $isKar ? 'Karyawan' : 'Admin' }}</span>
                                </span>
                                <span class="when">{{ $c->created_at->diffForHumans() }}</span>
                            </div>
                            @if($c->type === 'revisi')
                            <div class="mb-1"><span class="badge bg-warning-subtle text-warning border border-warning rounded-pill" style="font-size:.68rem;"><i class="bi bi-arrow-counterclockwise me-1"></i>Dibuka kembali untuk revisi</span></div>
                            @endif
                            @if($c->body)<div>{{ $c->body }}</div>@endif
                            @if($c->file_path)
                                @if($c->isImage())
                                <a href="{{ Storage::url($c->file_path) }}" target="_blank"><img src="{{ Storage::url($c->file_path) }}" style="max-width:170px; border-radius:10px; margin-top:6px; display:block;"></a>
                                @else
                                <a href="{{ Storage::url($c->file_path) }}" target="_blank" class="d-inline-flex align-items-center gap-1 mt-1" style="font-size:.8rem;"><i class="bi bi-paperclip"></i>{{ $c->file_name }}</a>
                                @endif
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3" style="font-size:.85rem;"><i class="bi bi-chat-left-dots d-block fs-4 mb-2 opacity-50"></i>Belum ada komentar.</p>
                    @endforelse
                </div>

                {{-- Composer --}}
                <div class="pc-composer">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-left-text pc-input-ico"></i>
                        <textarea wire:model="newComment" rows="1" class="form-control" placeholder="Tulis balasan untuk karyawan..."></textarea>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-2 pt-2 border-top">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label class="pc-iconbtn mb-0" title="Lampirkan file/gambar">
                                <i class="bi bi-paperclip"></i>
                                <input type="file" wire:model="commentFile" hidden>
                            </label>
                            @if($commentFile)
                            <span class="pc-attach-chip">
                                <i class="bi bi-file-earmark"></i>{{ Str::limit($commentFile->getClientOriginalName(), 20) }}
                                <button type="button" wire:click="$set('commentFile', null)" class="btn btn-sm p-0 text-danger d-inline-flex" title="Batal"><i class="bi bi-x-circle"></i></button>
                            </span>
                            @endif
                        </div>
                        <button type="button" class="pc-send" wire:click="addComment" wire:loading.attr="disabled" wire:target="addComment">
                            <i class="bi bi-send-fill"></i>
                            <span wire:loading.remove wire:target="addComment">Kirim</span>
                            <span wire:loading wire:target="addComment">...</span>
                        </button>
                    </div>
                    @error('commentFile')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
    @endif

    @include('livewire.layout.sweetalert')
</div>

@push('scripts')
    <script>
        document.addEventListener('input', function (e) {
            if (!e.target.classList.contains('rupiah')) return;
            let v = e.target.value.replace(/[^\d]/g, '');
            e.target.value = v ? Number(v).toLocaleString('id-ID') : '';
        }, true);

        // Data karyawan untuk picker (seragam dgn picker produk di Order)
        window.__taskUsers = @json($users->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])->values());

        if (!window.__taskPickerBound) {
            window.__taskPickerBound = true;
            const pickGlossy = {
                background: 'rgba(255, 255, 255, 0.92)', backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem'
            };
            window.taskKaryawanPicker = function (btn) {
                if (typeof Swal === 'undefined') return;
                const comp = btn.closest('[wire\\:id]'); if (!comp) return;
                const cid = comp.getAttribute('wire:id');
                const items = window.__taskUsers || [];
                const rows = items.length
                    ? items.map(it => `<button type="button" class="of-pick-item" data-id="${it.id}" data-search="${it.name.toLowerCase()}">${it.name}</button>`).join('')
                    : '<div class="of-pick-empty">Tidak ada data karyawan</div>';
                Swal.fire({
                    title: 'Pilih Karyawan',
                    html: `<input id="taskPickSearch" class="form-control mb-2" placeholder="Ketik untuk mencari...">
                           <div id="taskPickList" class="of-pick-list">${rows}</div>`,
                    ...pickGlossy,
                    didOpen: () => {
                        const search = document.getElementById('taskPickSearch');
                        const listEl = document.getElementById('taskPickList');
                        if (search) {
                            search.addEventListener('input', () => {
                                const q = search.value.toLowerCase();
                                listEl.querySelectorAll('.of-pick-item').forEach(b => { b.style.display = b.dataset.search.includes(q) ? '' : 'none'; });
                            });
                            setTimeout(() => search.focus(), 100);
                        }
                        listEl.querySelectorAll('.of-pick-item').forEach(b => {
                            b.addEventListener('click', () => {
                                Livewire.find(cid).set('t_user_id', b.dataset.id);
                                Swal.close();
                            });
                        });
                    }
                });
            };
        }

        // ===== Picker Kategori & Label (popup glossy, kelola + tambah + hapus) =====
        if (!window.__taskCatPickerBound) {
            window.__taskCatPickerBound = true;

            const catPickGlossy = {
                background: 'rgba(255, 255, 255, 0.92)', backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem'
            };

            function tpData() {
                const el = document.getElementById('taskPickData');
                if (!el) return { categories: [], labels: [] };
                try {
                    return {
                        categories: JSON.parse(el.dataset.categories || '[]'),
                        labels: JSON.parse(el.dataset.labels || '[]'),
                    };
                } catch (e) { return { categories: [], labels: [] }; }
            }

            function tpRows(items) {
                if (!items.length) return '<div class="of-pick-empty">Belum ada data. Tambah di bawah.</div>';
                return items.map(it => `
                    <div class="of-pick-row" data-row="${it.id}">
                        <button type="button" class="of-pick-item" data-id="${it.id}" data-search="${it.name.toLowerCase()}">${it.name}</button>
                        <button type="button" class="of-pick-del" data-del="${it.id}" title="Hapus"><i class="bi bi-trash"></i></button>
                    </div>`).join('');
            }

            function taskEntityPicker(btn, cfg) {
                if (typeof Swal === 'undefined') return;
                const comp = btn.closest('[wire\\:id]'); if (!comp) return;
                const cid = comp.getAttribute('wire:id');
                const lw = () => Livewire.find(cid);

                Swal.fire({
                    title: cfg.title,
                    html: `
                        <input id="tpSearch" class="form-control mb-2" placeholder="Cari...">
                        <div id="tpList" class="of-pick-list">${tpRows(cfg.items())}</div>
                        <div class="of-pick-add mt-3">
                            <input id="tpNew" class="form-control" placeholder="${cfg.addPlaceholder}">
                            <button type="button" id="tpAdd" class="btn btn-primary of-pick-addbtn"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
                        </div>
                        <div id="tpMsg" class="of-pick-msg"></div>`,
                    ...catPickGlossy,
                    didOpen: () => {
                        const listEl = document.getElementById('tpList');
                        const search = document.getElementById('tpSearch');
                        const newInp = document.getElementById('tpNew');
                        const addBtn = document.getElementById('tpAdd');
                        const msg = document.getElementById('tpMsg');

                        const applyFilter = () => {
                            const q = (search.value || '').toLowerCase();
                            listEl.querySelectorAll('.of-pick-row').forEach(row => {
                                const item = row.querySelector('.of-pick-item');
                                row.style.display = (item && item.dataset.search.includes(q)) ? '' : 'none';
                            });
                        };

                        const confirmDelete = (row, id) => {
                            if (!row) return;
                            const original = row.innerHTML;
                            const restore = () => { row.innerHTML = original; wireRow(row, id); };
                            row.innerHTML = `<div class="of-pick-confirm">
                                <span>Hapus item ini?</span>
                                <button type="button" class="btn btn-sm btn-danger of-pick-yes">Ya</button>
                                <button type="button" class="btn btn-sm btn-light of-pick-no">Batal</button></div>`;
                            row.querySelector('.of-pick-no').addEventListener('click', restore);
                            row.querySelector('.of-pick-yes').addEventListener('click', async () => {
                                await cfg.del(lw(), id);
                                rebuild();
                            });
                        };

                        function wireRow(row, id) {
                            row.querySelector('.of-pick-item')?.addEventListener('click', () => { cfg.pick(lw(), id); Swal.close(); });
                            row.querySelector('.of-pick-del')?.addEventListener('click', () => confirmDelete(row, id));
                        }

                        function rebuild() {
                            listEl.innerHTML = tpRows(cfg.items());
                            listEl.querySelectorAll('.of-pick-row').forEach(row => wireRow(row, row.dataset.row));
                            applyFilter();
                        }

                        search.addEventListener('input', applyFilter);
                        setTimeout(() => search.focus(), 100);

                        addBtn.addEventListener('click', async () => {
                            const name = (newInp.value || '').trim();
                            msg.textContent = '';
                            if (!name) { msg.textContent = 'Nama tidak boleh kosong.'; return; }
                            if (cfg.items().some(it => it.name.toLowerCase() === name.toLowerCase())) {
                                msg.textContent = 'Nama tersebut sudah ada.'; return;
                            }
                            await cfg.add(lw(), name);
                            Swal.close(); // item baru otomatis terpilih
                        });
                        newInp.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); addBtn.click(); } });

                        listEl.querySelectorAll('.of-pick-row').forEach(row => wireRow(row, row.dataset.row));
                    }
                });
            }

            window.taskKategoriPicker = function (btn) {
                taskEntityPicker(btn, {
                    title: 'Pilih Kategori',
                    addPlaceholder: 'Kategori baru, mis. Parafrase',
                    items: () => tpData().categories,
                    pick: (lw, id) => lw.set('t_category_id', id),
                    add: (lw, name) => { lw.set('newCategoryName', name, false); return lw.call('addCategory'); },
                    del: (lw, id) => lw.call('deleteCategory', id),
                });
            };

            window.taskLabelPicker = function (btn) {
                taskEntityPicker(btn, {
                    title: 'Pilih Label',
                    addPlaceholder: 'Label baru, mis. Bug',
                    items: () => tpData().labels,
                    pick: (lw, id) => lw.set('t_label_id', id),
                    add: (lw, name) => { lw.set('newLabelName', name, false); return lw.call('addLabel'); },
                    del: (lw, id) => lw.call('deleteLabel', id),
                });
            };
        }

        if (!window.__ptConfirmBound) {
            window.__ptConfirmBound = true;
            const glossyConfig = {
                background: 'rgba(255, 255, 255, 0.8)', backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel', title: 'swal-glossy-title' },
                buttonsStyling: false
            };
            document.addEventListener('click', function (event) {
                const t = event.target.closest('.pt-terapkan-btn');
                if (t) {
                    event.preventDefault();
                    const c = t.closest('[wire\\:id]'); if (!c) return;
                    Swal.fire({
                        title: 'Terapkan bonus ke gaji?',
                        text: 'Bonus penyelesaian task ditulis ke semua draft gaji (pending) periode ini. Gaji final (completed) tetap dikunci.',
                        icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, terapkan', cancelButtonText: 'Batal', ...glossyConfig
                    }).then(r => { if (r.isConfirmed) Livewire.find(c.getAttribute('wire:id')).call('terapkan'); });
                    return;
                }

                const del = event.target.closest('.pt-delete-btn');
                if (del) {
                    event.preventDefault();
                    const c = del.closest('[wire\\:id]'); if (!c) return;
                    const id = del.getAttribute('data-id');
                    Swal.fire({
                        title: 'Yakin hapus task?',
                        text: 'Task ini beserta komentar & lampirannya akan dihapus permanen.',
                        icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal', ...glossyConfig
                    }).then(r => { if (r.isConfirmed) Livewire.find(c.getAttribute('wire:id')).call('deleteTask', id); });
                }
            });
        }

        // Guard ukuran file lampiran (maks 2 MB) SEBELUM Livewire mengunggah,
        // agar tidak muncul "failed to upload" dari batas PHP. Pesan jelas & glossy.
        if (!window.__taskFileGuardBound) {
            window.__taskFileGuardBound = true;
            const MAX_MB = 2;
            const cfg = {
                background: 'rgba(255,255,255,0.92)', backdrop: 'rgba(139,92,246,0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold', confirmButton: 'btn-glossy-confirm' },
                buttonsStyling: false
            };
            document.addEventListener('change', function (e) {
                const input = e.target;
                if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.classList.contains('task-file-input')) return;
                const tooBig = Array.from(input.files || []).filter(f => f.size > MAX_MB * 1024 * 1024);
                if (tooBig.length) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    input.value = '';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'File terlalu besar',
                            html: 'Maksimal <b>' + MAX_MB + ' MB</b> per file.<br><span style="font-size:.85rem;color:#64748b;">Melebihi batas: ' + tooBig.map(f => f.name).join(', ') + '</span>',
                            confirmButtonText: 'Mengerti', ...cfg
                        });
                    }
                }
            }, true);
        }
    </script>
@endpush
