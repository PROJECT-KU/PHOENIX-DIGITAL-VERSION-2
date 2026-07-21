{{-- Kartu task tunggal (solo). Butuh: $task, $reads, $manageGiverIds, badge maps --}}
@php
    $locked = $task->isLocked();
    $bs = $task->bonusStatus();
    $selesai = $task->progress === 'selesai';
    $lewat = ! $selesai && $bs === 'tidak_selesai';
    $accMap = ['belum'=>'secondary','dikerjakan'=>'info','selesai'=>'success'];
    $acc = $lewat ? 'danger' : ($accMap[$task->progress] ?? 'secondary');
    $sisa = (int) now()->startOfDay()->diffInDays($task->deadline_selesai->copy()->startOfDay(), false);
    $hariIni = ! $selesai && ! $lewat && $sisa === 0;
    $myLastRead = ($reads[$task->group_id] ?? null) ? \Illuminate\Support\Carbon::parse($reads[$task->group_id]) : null;
    $komentarBaru = $task->groupComments->where('user_id', '!=', auth()->id())
        ->filter(fn ($c) => ! $myLastRead || $c->created_at->gt($myLastRead))->count();
    $sayaPemberi = $task->assigned_by === auth()->id();
    $canManage = $task->assigned_by && in_array($task->assigned_by, $manageGiverIds);
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
                <span class="badge bg-danger rounded-pill ts-badge" title="Ada komentar baru"><i class="bi bi-chat-dots-fill me-1"></i>{{ $komentarBaru > 9 ? '9+' : $komentarBaru }} baru</span>
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

        <div class="ts-people mb-3">
            {{-- Task admin ber-assigned_by NULL, jadi pemberi kosong; pakai nama
                 pembuat (created_by) supaya tampil nama lengkap, bukan "Admin". --}}
            <span class="ts-person" title="Pemberi task"><i class="bi bi-person-badge"></i><span>{{ $task->pemberi?->name ?? $task->pembuat?->name ?? 'Admin' }}</span></span>
            <i class="bi bi-arrow-right ts-person-arrow"></i>
            <span class="ts-person" title="Ditugaskan ke"><i class="bi bi-person-check"></i><span>{{ $task->user_id === auth()->id() ? 'Anda' : ($task->karyawan?->name ?? '-') }}</span></span>
        </div>

        <div class="d-flex align-items-center justify-content-between">
            <span class="ts-meta"><i class="bi bi-calendar-range me-1"></i>{{ $task->deadline_mulai?->translatedFormat('d M') }} – {{ $task->deadline_selesai?->translatedFormat('d M Y') }}</span>
            @if($selesai)
            <span class="ts-deadchip" style="background:rgba(16,185,129,.12); color:#059669;"><i class="bi bi-check-circle-fill"></i>Selesai</span>
            @elseif($lewat)
            <span class="ts-deadchip" style="background:rgba(244,63,94,.12); color:#e11d48;"><i class="bi bi-exclamation-circle-fill"></i>Melebihi Deadline</span>
            @elseif($sisa === 0)
            <span class="ts-deadchip" style="background:rgba(245,158,11,.14); color:#d97706;"><i class="bi bi-alarm-fill"></i>Hari ini</span>
            @else
            <span class="ts-deadchip" style="background:rgba(59,130,246,.10); color:#2563eb;"><i class="bi bi-hourglass-split"></i>{{ $sisa }} hari lagi</span>
            @endif
        </div>

        @if($canManage)
        <div class="ts-manage">
            <span class="ts-manage-lbl"><i class="bi bi-people-fill"></i>{{ $sayaPemberi ? 'Task dari Anda' : 'Task bawahan Anda' }}</span>
            @if($locked)
            <button type="button" class="ts-mini-btn reopen" title="Buka kembali untuk revisi" wire:click.stop="openReopen('{{ $task->id }}')"><i class="bi bi-arrow-counterclockwise"></i></button>
            @endif
            <button type="button" class="ts-mini-btn edit" title="Edit task" wire:click.stop="openEditTask('{{ $task->id }}')"><i class="bi bi-pencil"></i></button>
            <button type="button" class="ts-mini-btn del" title="Hapus task" wire:click.stop="deleteTask('{{ $task->id }}')" wire:confirm="Hapus task ini? Tindakan tidak bisa dibatalkan."><i class="bi bi-trash"></i></button>
        </div>
        @endif
    </div>
</div>
