{{-- Folder task grup (1 task ke banyak penerima). Butuh: $gtasks, $reads, $manageGiverIds, badge maps --}}
@php
    $first = $gtasks->first();
    $total = $gtasks->count();
    $selesaiCount = $gtasks->where('progress', 'selesai')->count();
    $anyManage = $gtasks->contains(fn ($t) => $t->assigned_by && in_array($t->assigned_by, $manageGiverIds));
    $anyLocked = $gtasks->contains(fn ($t) => $t->isLocked());
    $myLastRead = ($reads[$first->group_id] ?? null) ? \Illuminate\Support\Carbon::parse($reads[$first->group_id]) : null;
    $komentarBaru = $first->groupComments->where('user_id', '!=', auth()->id())
        ->filter(fn ($c) => ! $myLastRead || $c->created_at->gt($myLastRead))->count();
    // Apakah SAYA disebut (@nama-depan) di komentar grup yg belum saya baca?
    $myFirst = mb_strtolower((string) \Illuminate\Support\Str::of(auth()->user()->name)->trim()->explode(' ')->first());
    $disebutSaya = $myFirst !== '' && $first->groupComments->contains(function ($c) use ($myLastRead, $myFirst) {
        return $c->user_id !== auth()->id()
            && (! $myLastRead || $c->created_at->gt($myLastRead))
            && $c->body
            && preg_match('/@'.preg_quote($myFirst, '/').'(?![\p{L}\p{N}_])/ui', $c->body);
    });
    $pemberiName = $first->pemberi?->name ?? 'Admin';
    // Urutkan: sub-task milik saya dulu, lalu sisanya per nama.
    $members = $gtasks->sortBy(fn ($t) => [$t->user_id === auth()->id() ? 0 : 1, $t->karyawan?->name ?? ''])->values();
@endphp
<div class="col-12">
    <div class="ts-folder" x-data="{ open: true }">
        <div class="ts-folder-head" @click="open = !open">
            <span class="ts-folder-ico"><i class="bi" :class="open ? 'bi-folder2-open' : 'bi-folder-fill'"></i></span>
            <div class="ts-folder-info">
                <div class="ts-folder-title">
                    {{ $first->nama }}
                    <span class="ts-folder-count"><i class="bi bi-people-fill me-1"></i>{{ $total }} penerima</span>
                    @if($disebutSaya)
                    <span class="badge rounded-pill ts-badge ms-1 ts-mentioned-badge"><i class="bi bi-at me-1"></i>Anda disebut</span>
                    @endif
                    @if($komentarBaru)
                    <span class="badge bg-danger rounded-pill ts-badge ms-1"><i class="bi bi-chat-dots-fill me-1"></i>{{ $komentarBaru > 9 ? '9+' : $komentarBaru }} baru</span>
                    @endif
                </div>
                <div class="ts-folder-meta">
                    <span><i class="bi bi-person-badge me-1"></i>{{ $pemberiName }}</span>
                    <span><i class="bi bi-calendar-range me-1"></i>{{ $first->deadline_selesai?->translatedFormat('d M Y') }}</span>
                    @if($first->category)<span class="badge bg-primary-subtle text-primary border border-primary rounded-pill ts-badge"><i class="bi bi-tag me-1"></i>{{ $first->category->nama }}</span>@endif
                    <span class="badge bg-{{ $badgeBobot[$first->bobot] ?? 'secondary' }}-subtle text-{{ $badgeBobot[$first->bobot] ?? 'secondary' }} border border-{{ $badgeBobot[$first->bobot] ?? 'secondary' }} rounded-pill text-capitalize ts-badge">{{ $first->bobot }}</span>
                </div>
            </div>
            <div class="ts-folder-side">
                <span class="ts-folder-progress"><b>{{ $selesaiCount }}</b>/{{ $total }} selesai</span>
                <button type="button" class="ts-folder-chat" wire:click.stop="openGroupChat('{{ $first->group_id }}')">
                    <i class="bi bi-chat-dots"></i>
                    <span>Diskusi</span>
                    @if($komentarBaru)<span class="ts-folder-chat-badge">{{ $komentarBaru > 9 ? '9+' : $komentarBaru }}</span>@endif
                </button>
                @if($anyManage)
                <button type="button" class="ts-mini-btn edit" title="Edit task grup" wire:click.stop="openEditTask('{{ $first->id }}')"><i class="bi bi-pencil"></i></button>
                <button type="button" class="ts-mini-btn del" title="Hapus seluruh grup" wire:click.stop="deleteGroup('{{ $first->group_id }}')" wire:confirm="Hapus SELURUH grup task ini ({{ $total }} penerima)? Tidak bisa dibatalkan."><i class="bi bi-trash"></i></button>
                @endif
                <i class="bi ts-folder-chev" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            </div>
        </div>

        <div class="ts-folder-body" x-show="open" x-transition.opacity>
            <div class="row g-2">
                @foreach($members as $m)
                @php
                    $mLocked = $m->isLocked();
                    $mbs = $m->bonusStatus();
                    $mSelesai = $m->progress === 'selesai';
                    $mLewat = ! $mSelesai && $mbs === 'tidak_selesai';
                    $mMine = $m->user_id === auth()->id();
                    $mCanManage = $m->assigned_by && in_array($m->assigned_by, $manageGiverIds);
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="ts-sub {{ $mMine ? 'mine' : '' }} {{ $mLocked ? 'locked' : '' }}" wire:click="openTask('{{ $m->id }}')">
                        <div class="ts-sub-top">
                            <span class="ts-sub-av">{{ strtoupper(substr($m->karyawan?->name ?? '?', 0, 1)) }}</span>
                            <span class="ts-sub-name">{{ $mMine ? 'Anda' : ($m->karyawan?->name ?? '-') }}</span>
                            @if($mMine)<span class="badge bg-primary-subtle text-primary border border-primary rounded-pill ts-badge" style="font-size:.6rem;">Anda</span>@endif
                        </div>
                        <div class="ts-sub-badges">
                            <span class="badge bg-{{ $badgeProg[$m->progress] ?? 'secondary' }}-subtle text-{{ $badgeProg[$m->progress] ?? 'secondary' }} border border-{{ $badgeProg[$m->progress] ?? 'secondary' }} rounded-pill ts-badge">{{ $labelProg[$m->progress] ?? ucfirst($m->progress) }}</span>
                            <span class="badge bg-{{ $badgeBonus[$mbs] ?? 'secondary' }} rounded-pill ts-badge">{{ $labelBonus[$mbs] ?? $mbs }}</span>
                            @if($mLocked)<span class="badge bg-secondary rounded-pill ts-badge"><i class="bi bi-lock-fill"></i></span>@endif
                        </div>
                        @if($mLocked && $mCanManage)
                        <div class="ts-sub-foot">
                            <button type="button" class="ts-mini-btn reopen" title="Buka kembali revisi" wire:click.stop="openReopen('{{ $m->id }}')"><i class="bi bi-arrow-counterclockwise"></i></button>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
