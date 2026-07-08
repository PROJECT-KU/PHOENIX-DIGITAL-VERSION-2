@section('title')
Task Saya || PT. Asthana Cipta Mandiri
@stop
<div wire:poll.30s>
    <style>
        /* ===== Kartu task ===== */
        .ts-card {
            position: relative;
            border: 1px solid #eef0f7;
            border-radius: 18px;
            padding: 18px 18px 16px;
            background: linear-gradient(135deg, #ffffff, #fbfcff);
            box-shadow: 0 6px 18px rgba(108, 99, 255, .05);
            height: 100%;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            overflow: hidden;
        }

        .ts-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }

        .ts-card:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(76, 29, 149, .12); border-color: #ddd6fe; }
        .ts-card.acc-success::before { background: linear-gradient(#10b981, #059669); }
        .ts-card.acc-info::before { background: linear-gradient(#0ea5e9, #2563eb); }
        .ts-card.acc-danger::before { background: linear-gradient(#f43f5e, #e11d48); }
        .ts-card.acc-warning::before { background: linear-gradient(#f59e0b, #d97706); }
        .ts-card.acc-primary::before { background: linear-gradient(#7c3aed, #4e46e5); }
        .ts-card.acc-secondary::before { background: linear-gradient(#94a3b8, #64748b); }
        .ts-card.locked { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }

        /* ===== Deadline HARI INI: kartu ditonjolkan ===== */
        .ts-card.ts-today {
            border-color: #fbbf24;
            background: linear-gradient(135deg, #fffdf5, #fff6e5);
            box-shadow: 0 10px 26px rgba(245, 158, 11, .22);
            animation: tsTodayPulse 1.8s ease-in-out infinite;
        }
        .ts-card.ts-today:hover { border-color: #f59e0b; box-shadow: 0 16px 34px rgba(245, 158, 11, .30); }
        .ts-card.ts-today::before { background: linear-gradient(#f59e0b, #d97706) !important; width: 6px; }
        @keyframes tsTodayPulse {
            0%, 100% { box-shadow: 0 8px 22px rgba(245, 158, 11, .18); }
            50% { box-shadow: 0 12px 30px rgba(245, 158, 11, .36); }
        }
        .ts-today-ribbon {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .68rem; font-weight: 800; letter-spacing: .3px; text-transform: uppercase;
            color: #b45309; background: rgba(245, 158, 11, .16);
            padding: 3px 9px; border-radius: 999px; margin-bottom: 8px;
        }

        .ts-title { font-weight: 800; color: #1e293b; font-size: 1.02rem; line-height: 1.25; }
        .ts-meta { font-size: .8rem; color: #64748b; }

        .ts-deadchip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .76rem;
            font-weight: 700;
            padding: 5px 11px;
            border-radius: 999px;
        }

        .ts-badge { font-weight: 700; letter-spacing: .2px; }

        /* ===== Modal glossy ===== */
        .ts-modal-back { position: fixed; inset: 0; background: rgba(15, 23, 42, .5); backdrop-filter: blur(2px); z-index: 1055; }
        .ts-modal { position: fixed; inset: 0; z-index: 1056; display: flex; align-items: flex-start; justify-content: center; padding: 4vh 12px; overflow-y: auto; }
        .ts-modal-card { background: #fff; border-radius: 22px; width: 100%; max-width: 560px; box-shadow: 0 30px 70px rgba(15, 23, 42, .32); overflow: hidden; }
        .ts-modal-head { padding: 22px 24px; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; position: relative; }
        .ts-modal-head h5, .ts-modal-head small, .ts-modal-head .badge { color: #fff; }
        .ts-modal-head .btn-close { filter: invert(1) grayscale(1) brightness(2); opacity: .9; position: absolute; top: 18px; right: 20px; }

        .ts-section-lbl { font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin-bottom: 8px; }

        /* Chat bubbles */
        .ts-thread { max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding: 2px; }
        .ts-msg { display: flex; gap: 8px; max-width: 85%; }
        .ts-msg.mine { align-self: flex-end; flex-direction: row-reverse; }
        .ts-msg-av { width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; font-size: .8rem; background: linear-gradient(135deg, #94a3b8, #64748b); }
        .ts-msg.mine .ts-msg-av { background: linear-gradient(135deg, #7c3aed, #4e46e5); }
        .ts-bubble { background: #f4f6fb; border-radius: 14px; padding: 8px 12px; font-size: .86rem; color: #1e293b; }
        .ts-msg.mine .ts-bubble { background: linear-gradient(135deg, rgba(124, 58, 237, .12), rgba(78, 70, 229, .07)); }
        .ts-bubble.ts-bubble-revisi { background: linear-gradient(135deg, rgba(245, 158, 11, .16), rgba(217, 119, 6, .08)); border: 1px solid rgba(245, 158, 11, .45); }
        .ts-msg.mine .ts-bubble.ts-bubble-revisi { background: linear-gradient(135deg, rgba(245, 158, 11, .16), rgba(217, 119, 6, .08)); }
        .ts-bubble .who { font-weight: 700; font-size: .74rem; color: #475569; }
        .ts-bubble .when { font-size: .68rem; color: #94a3b8; }

        /* ===== Composer ===== */
        .ts-composer { border: 1px solid #e6e8f2; border-radius: 14px; padding: 6px 14px; background: #fff; box-shadow: 0 4px 14px rgba(108, 99, 255, .05); transition: .15s; }
        .ts-composer:focus-within { border-color: #c7d2fe; box-shadow: 0 0 0 .18rem rgba(124, 58, 237, .12); }
        .ts-composer textarea, .ts-composer textarea:focus { border: none !important; outline: none !important; box-shadow: none !important; background: transparent; }
        .ts-composer textarea { resize: none; font-size: .9rem; line-height: 1.5; padding: 9px 0; text-align: left; max-height: 120px; }
        .ts-input-ico { color: #a3a9bd; font-size: 1rem; line-height: 1; flex-shrink: 0; }
        .ts-attach-chip { display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; border-radius: 8px; padding: 3px 8px; font-size: .76rem; color: #475569; }
        .ts-iconbtn { width: 38px; height: 38px; border-radius: 10px; border: 1px solid #eef0f7; background: #fff; color: #64748b; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: .15s; }
        .ts-iconbtn:hover { border-color: #c7d2fe; color: #6d28d9; }
        .ts-iconbtn i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .ts-send { border: none; border-radius: 10px; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; padding: 9px 18px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 6px 14px rgba(124, 58, 237, .28); transition: .15s; }
        .ts-send:hover { filter: brightness(1.05); transform: translateY(-1px); }
        .ts-send:disabled { opacity: .7; }
        .ts-send i.bi { display: inline-flex; align-items: center; line-height: 1; }

        /* ===== Empty state glossy ===== */
        .ts-empty-card { border: 1px solid #eef0f7; background: linear-gradient(135deg, #ffffff, #faf9ff); }
        .ts-empty { padding: 56px 24px; }
        .ts-empty-badge {
            width: 96px; height: 96px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #7c3aed, #4e46e5);
            color: #fff; font-size: 2.6rem;
            box-shadow: 0 18px 40px rgba(124, 58, 237, .32), inset 0 2px 8px rgba(255, 255, 255, .45);
            position: relative;
        }
        .ts-empty-badge::after {
            content: ""; position: absolute; inset: -9px; border-radius: 50%;
            border: 2px solid rgba(124, 58, 237, .14);
        }
        .ts-empty-badge i { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .ts-empty h5 { color: #1e293b; }
        .ts-empty p { max-width: 430px; margin-inline: auto; }
        .ts-empty-btn {
            display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid #ddd6fe; background: #fff; color: #6d28d9; font-weight: 600;
            box-shadow: 0 6px 14px rgba(124, 58, 237, .12); transition: .15s;
        }
        .ts-empty-btn:hover { background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; border-color: transparent; transform: translateY(-1px); }
        .ts-empty-btn i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
    </style>

    @php
        $badgeBobot = ['ringan'=>'success','sedang'=>'warning','berat'=>'danger'];
        $badgeProg = ['belum'=>'secondary','dikerjakan'=>'info','selesai'=>'success'];
        $labelProg = ['belum'=>'Belum Dikerjakan','dikerjakan'=>'Dikerjakan','selesai'=>'Selesai'];
        $badgeBonus = ['tepat_waktu'=>'success','terlambat'=>'warning','tidak_selesai'=>'danger','tidak_ada_info'=>'primary'];
        $labelBonus = ['tepat_waktu'=>'Tepat Waktu','terlambat'=>'Terlambat','tidak_selesai'=>'Tidak Selesai','tidak_ada_info'=>'Berjalan'];
    @endphp

    <div class="container-fluid">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h3 class="gradient-text fw-bold mb-1">Task Saya</h3>
                <p class="text-muted mb-0 small">Daftar task Anda beserta deadline &amp; statusnya. Klik kartu untuk detail, komentar, dan unggah file.</p>
            </div>
        </div>

        {{-- Filter Periode (pola sama seperti Pengeluaran) --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 px-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                        <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0" style="width:40px;height:40px;font-size:1.1rem;border-radius:12px;">
                            <i class="bi bi-funnel"></i>
                        </span>
                        <span>Filter Periode</span>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <select wire:model.live="bulan" class="form-select rounded-3" style="min-width:160px;">
                            <option value="">Semua Bulan</option>
                            @foreach($daftarBulan as $num => $nama)
                            <option value="{{ $num }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="tahun" class="form-select rounded-3" style="min-width:130px;">
                            <option value="">Semua Tahun</option>
                            @foreach($daftarTahun as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                            @endforeach
                        </select>
                        @if($bulan || $tahun)
                        <button wire:click="resetFilter" type="button" class="btn btn-danger rounded-3" title="Reset filter">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @forelse($tasks as $task)
            @php
                $locked = $task->isLocked();
                $bs = $task->bonusStatus();
                $selesai = $task->progress === 'selesai';
                $lewat = ! $selesai && $bs === 'tidak_selesai';
                // Aksen kartu MENGIKUTI warna badge status agar konsisten
                $accMap = ['belum'=>'secondary','dikerjakan'=>'info','selesai'=>'success'];
                $acc = $lewat ? 'danger' : ($accMap[$task->progress] ?? 'secondary');
                $sisa = (int) now()->startOfDay()->diffInDays($task->deadline_selesai->copy()->startOfDay(), false);
                $hariIni = ! $selesai && ! $lewat && $sisa === 0;
                // Komentar baru dari admin yang belum dibaca karyawan
                $komentarBaru = $task->comments->where('user_id', '!=', $task->user_id)
                    ->whereNull('karyawan_read_at')->count();
            @endphp
            <div class="col-12 col-md-6 col-xl-4">
                <div class="ts-card acc-{{ $acc }} {{ $locked ? 'locked' : '' }} {{ $hariIni ? 'ts-today' : '' }}" wire:click="openTask('{{ $task->id }}')">
                    @if($hariIni)
                    <div class="ts-today-ribbon"><i class="bi bi-alarm-fill"></i>Deadline hari ini</div>
                    @endif
                    <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                        <span class="ts-title">{{ $task->nama }}</span>
                        <span class="d-inline-flex align-items-center gap-1 flex-shrink-0">
                            @if($komentarBaru)
                            <span class="badge bg-danger rounded-pill ts-badge" title="Ada komentar baru dari admin"><i class="bi bi-chat-dots-fill me-1"></i>{{ $komentarBaru > 9 ? '9+' : $komentarBaru }} baru</span>
                            @endif
                            <span class="badge bg-{{ $badgeBobot[$task->bobot] ?? 'secondary' }}-subtle text-{{ $badgeBobot[$task->bobot] ?? 'secondary' }} border border-{{ $badgeBobot[$task->bobot] ?? 'secondary' }} rounded-pill text-capitalize ts-badge">{{ $task->bobot }}</span>
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-1 mb-3 flex-wrap">
                        <span class="badge bg-{{ $badgeProg[$task->progress] ?? 'secondary' }}-subtle text-{{ $badgeProg[$task->progress] ?? 'secondary' }} border border-{{ $badgeProg[$task->progress] ?? 'secondary' }} rounded-pill ts-badge">{{ $labelProg[$task->progress] ?? ucfirst($task->progress) }}</span>
                        <span class="badge bg-{{ $badgeBonus[$bs] ?? 'secondary' }} rounded-pill ts-badge">{{ $labelBonus[$bs] ?? $bs }}</span>
                        @if($task->category)<span class="badge bg-primary-subtle text-primary border border-primary rounded-pill ts-badge"><i class="bi bi-tag me-1"></i>{{ $task->category->nama }}</span>@endif
                        @if($task->label)<span class="badge bg-info-subtle text-info border border-info rounded-pill ts-badge">{{ $task->label->nama }}</span>@endif
                        @if($locked)<span class="badge bg-secondary rounded-pill ts-badge"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>@endif
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <span class="ts-meta"><i class="bi bi-calendar-range me-1"></i>{{ $task->deadline_mulai?->translatedFormat('d M') }} – {{ $task->deadline_selesai?->translatedFormat('d M Y') }}</span>
                        @if($selesai)
                        <span class="ts-deadchip" style="background:rgba(16,185,129,.12); color:#059669;"><i class="bi bi-check-circle-fill"></i>Selesai</span>
                        @elseif($lewat)
                        <span class="ts-deadchip" style="background:rgba(244,63,94,.12); color:#e11d48;"><i class="bi bi-exclamation-circle-fill"></i>Terlambat</span>
                        @elseif($sisa === 0)
                        <span class="ts-deadchip" style="background:rgba(245,158,11,.14); color:#d97706;"><i class="bi bi-alarm-fill"></i>Hari ini</span>
                        @else
                        <span class="ts-deadchip" style="background:rgba(59,130,246,.10); color:#2563eb;"><i class="bi bi-hourglass-split"></i>{{ $sisa }} hari lagi</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 ts-empty-card">
                    <div class="card-body ts-empty text-center">
                        <div class="ts-empty-badge mb-3"><i class="bi {{ ($bulan || $tahun) ? 'bi-calendar-x' : 'bi-clipboard-check' }}"></i></div>
                        <h5 class="fw-bold mb-2">{{ ($bulan || $tahun) ? 'Tidak Ada Task di Periode Ini' : 'Belum Ada Task' }}</h5>
                        <p class="text-muted mb-0" style="font-size:.95rem;">
                            @if($bulan || $tahun)
                            Tidak ada task pada bulan &amp; tahun yang dipilih. Coba ganti periode di filter atas, atau tampilkan semua.
                            @else
                            Task yang ditugaskan kepada Anda akan muncul di sini.
                            @endif
                        </p>
                        @if($bulan || $tahun)
                        <button type="button" wire:click="resetFilter" class="btn btn-sm rounded-pill px-4 mt-3 ts-empty-btn"><i class="bi bi-arrow-counterclockwise me-1"></i>Tampilkan Semua Periode</button>
                        @endif
                    </div>
                </div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ===== Modal detail task ===== --}}
    @if($showModal && $activeTask)
    @php $locked = $activeTask->isLocked(); $bs = $activeTask->bonusStatus(); @endphp
    <div class="ts-modal-back" wire:click="$set('showModal', false)"></div>
    <div class="ts-modal">
        <div class="ts-modal-card">
            <div class="ts-modal-head">
                <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                <h5 class="fw-bold mb-2" style="max-width: 90%;">{{ $activeTask->nama }}</h5>
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="badge bg-light text-dark rounded-pill">{{ $labelProg[$activeTask->progress] ?? ucfirst($activeTask->progress) }}</span>
                    <span class="badge bg-{{ $badgeBonus[$bs] ?? 'secondary' }} rounded-pill border border-light">{{ $labelBonus[$bs] ?? $bs }}</span>
                    @if($locked)<span class="badge bg-dark bg-opacity-25 rounded-pill"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>@endif
                </div>
            </div>
            <div class="p-4">
                @if($activeTask->deskripsi)<p class="text-muted mb-3" style="font-size:.9rem;">{{ $activeTask->deskripsi }}</p>@endif

                <div class="d-flex align-items-center gap-3 mb-3 flex-wrap" style="font-size:.85rem;">
                    <span><i class="bi bi-calendar-range me-1 text-primary"></i>{{ $activeTask->deadline_mulai?->translatedFormat('d M Y') }} – {{ $activeTask->deadline_selesai?->translatedFormat('d M Y') }}</span>
                    <span class="text-capitalize"><i class="bi bi-bar-chart me-1 text-primary"></i>Bobot: {{ $activeTask->bobot }}</span>
                </div>

                {{-- Lampiran --}}
                @if($activeTask->attachments->count())
                <div class="mb-3">
                    <div class="ts-section-lbl"><i class="bi bi-paperclip me-1"></i>Lampiran</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($activeTask->attachments as $att)
                        @if($att->isImage())
                        <a href="{{ Storage::url($att->path) }}" target="_blank"><img src="{{ Storage::url($att->path) }}" style="width:58px;height:58px;object-fit:cover;border-radius:10px;"></a>
                        @else
                        <a href="{{ Storage::url($att->path) }}" target="_blank" class="border rounded-3 px-2 py-1 d-inline-flex align-items-center gap-1 text-decoration-none" style="font-size:.78rem;"><i class="bi bi-file-earmark"></i>{{ Str::limit($att->name, 18) }}</a>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Diskusi --}}
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="ts-section-lbl mb-0"><i class="bi bi-chat-dots me-1"></i>Diskusi</div>
                    <span class="badge bg-light text-secondary border rounded-pill">{{ $activeTask->comments->count() }} komentar</span>
                </div>
                <div class="ts-thread mb-3" wire:key="ts-thread-{{ $activeTask->comments->count() }}"
                    x-data x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight; })">
                    @forelse($activeTask->comments as $c)
                    @php $mine = $c->user_id === auth()->id(); @endphp
                    <div class="ts-msg {{ $mine ? 'mine' : '' }}">
                        <div class="ts-msg-av">{{ strtoupper(substr($c->user->name ?? '?',0,1)) }}</div>
                        <div class="ts-bubble {{ $c->type === 'revisi' ? 'ts-bubble-revisi' : '' }}">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <span class="who">{{ $mine ? 'Anda' : ($c->user->name ?? '-') }}</span>
                                <span class="when">{{ $c->created_at->diffForHumans() }}</span>
                            </div>
                            @if($c->type === 'revisi')
                            <div class="mt-1"><span class="badge bg-warning-subtle text-warning border border-warning rounded-pill" style="font-size:.68rem;"><i class="bi bi-arrow-counterclockwise me-1"></i>Dibuka kembali untuk revisi</span></div>
                            @endif
                            @if($c->body)<div class="mt-1">{{ $c->body }}</div>@endif
                            @if($c->file_path)
                                @if($c->isImage())
                                <a href="{{ Storage::url($c->file_path) }}" target="_blank"><img src="{{ Storage::url($c->file_path) }}" style="max-width:160px; border-radius:10px; margin-top:6px;"></a>
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
                <div class="ts-composer">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-left-text ts-input-ico"></i>
                        <textarea wire:model="newComment" rows="1" class="form-control" placeholder="Tulis komentar / laporan..."></textarea>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-2 pt-2 border-top">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label class="ts-iconbtn mb-0" title="Lampirkan file/gambar">
                                <i class="bi bi-paperclip"></i>
                                <input type="file" wire:model="commentFile" hidden>
                            </label>
                            @if($commentFile)
                            <span class="ts-attach-chip">
                                <i class="bi bi-file-earmark"></i>{{ Str::limit($commentFile->getClientOriginalName(), 20) }}
                                <button type="button" wire:click="$set('commentFile', null)" class="btn btn-sm p-0 text-danger d-inline-flex" title="Batal"><i class="bi bi-x-circle"></i></button>
                            </span>
                            @endif
                        </div>
                        <button type="button" class="ts-send" wire:click="addComment" wire:loading.attr="disabled" wire:target="addComment">
                            <i class="bi bi-send-fill"></i>
                            <span wire:loading.remove wire:target="addComment">Kirim</span>
                            <span wire:loading wire:target="addComment">...</span>
                        </button>
                    </div>
                    @error('commentFile')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Footer aksi status --}}
            <div class="px-4 py-3 d-flex align-items-center justify-content-between gap-2 flex-wrap" style="background:#fbfcff; border-top:1px solid #eef0f7;">
                @if($locked)
                <span class="text-muted d-inline-flex align-items-center gap-2" style="font-size:.83rem;">
                    <i class="bi bi-lock-fill"></i>
                    {{ $activeTask->progress==='selesai' ? 'Task sudah selesai — status terkunci.' : 'Melewati deadline → Tidak Selesai. Status terkunci.' }}
                </span>
                @else
                <span class="text-muted" style="font-size:.8rem;"><i class="bi bi-info-circle me-1"></i>Perbarui status task Anda</span>
                <div class="d-flex gap-2">
                    @if($activeTask->progress !== 'dikerjakan')
                    <button type="button" wire:click="mulaiKerjakan('{{ $activeTask->id }}')" class="btn btn-outline-info btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1"><i class="bi bi-play-circle"></i> Mulai Kerjakan</button>
                    @endif
                    @if($activeTask->progress === 'dikerjakan')
                    <button type="button" data-id="{{ $activeTask->id }}" class="btn btn-success btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 ts-selesai-btn"><i class="bi bi-check2-circle"></i> Tandai Selesai</button>
                    @else
                    <button type="button" disabled class="btn btn-success btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 opacity-50" style="cursor:not-allowed;" title="Klik 'Mulai Kerjakan' dulu"><i class="bi bi-lock-fill"></i> Tandai Selesai</button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @include('livewire.layout.sweetalert')

    <script>
        if (!window.__tsConfirmBound) {
            window.__tsConfirmBound = true;
            const glossyConfig = {
                background: 'rgba(255, 255, 255, 0.8)', backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel', title: 'swal-glossy-title' },
                buttonsStyling: false
            };
            document.addEventListener('click', function (event) {
                const b = event.target.closest('.ts-selesai-btn');
                if (!b) return;
                event.preventDefault();
                const c = b.closest('[wire\\:id]'); if (!c) return;
                const id = b.getAttribute('data-id');
                Swal.fire({
                    title: 'Tandai task selesai?',
                    text: 'Waktu penyelesaian dicatat sekarang & status akan terkunci.',
                    icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, selesai!', cancelButtonText: 'Batal', ...glossyConfig
                }).then(r => { if (r.isConfirmed) Livewire.find(c.getAttribute('wire:id')).call('tandaiSelesai', id); });
            });
        }

        // Bersihkan ?open_task dari URL agar hard refresh tidak membuka popup lagi.
        (function () {
            if (window.location.search.includes('open_task')) {
                const url = new URL(window.location.href);
                url.searchParams.delete('open_task');
                window.history.replaceState({}, '', url.pathname + (url.search ? url.search : ''));
            }
        })();
    </script>
</div>
