<div wire:poll.30s x-data="{ open: false }" class="position-relative" style="z-index: 1050;">
    <style>
        .nb-btn { width: 44px; height: 44px; border-radius: 14px; border: 1px solid #eef0f7; background: #fff; color: #475569; display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; line-height: 1; transition: .15s; }
        .nb-btn:hover { border-color: #c7d2fe; color: #4f46e5; }
        .nb-btn i.bi { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; line-height: 1; }
        .nb-btn i.bi::before { display: block; line-height: 1; }
        .nb-badge { position: absolute; top: -6px; right: -6px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; background: #ef4444; color: #fff; font-size: .68rem; font-weight: 700; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(239,68,68,.4); }
        .nb-panel { position: absolute; right: 0; top: 54px; width: 360px; max-width: 90vw; background: #fff; border: 1px solid #eef0f7; border-radius: 16px; box-shadow: 0 18px 45px rgba(15,23,42,.18); overflow: hidden; }
        .nb-head { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
        .nb-list { max-height: 380px; overflow-y: auto; }
        .nb-item { display: flex; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #f6f7fb; cursor: pointer; transition: .12s; text-align: left; width: 100%; background: transparent; border-left: 0; border-right: 0; border-top: 0; }
        .nb-item:hover { background: #f8faff; }
        .nb-item.unread { background: rgba(79,70,229,.05); }
        .nb-ico { width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem; line-height: 1; }
        .nb-ico i.bi { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; line-height: 1; }
        .nb-ico i.bi::before { display: block; line-height: 1; }
        .nb-title { font-weight: 700; font-size: .85rem; color: #1e293b; }
        .nb-body { font-size: .8rem; color: #64748b; line-height: 1.35; }
        .nb-time { font-size: .7rem; color: #94a3b8; margin-top: 2px; }
        .nb-empty { padding: 28px 16px; text-align: center; color: #94a3b8; font-size: .85rem; }
    </style>

    <button type="button" class="nb-btn" @click="open = !open" title="Notifikasi">
        <i class="bi bi-bell"></i>
        @if($unread > 0)
        <span class="nb-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
    </button>

    <div x-show="open" x-transition @click.outside="open = false" class="nb-panel" style="display:none;">
        <div class="nb-head">
            <span class="fw-bold text-dark">Notifikasi</span>
            @if($unread > 0)
            <button type="button" wire:click="markAllRead" class="btn btn-sm btn-link text-decoration-none p-0" style="font-size:.78rem;">Tandai semua dibaca</button>
            @endif
        </div>
        <div class="nb-list">
            @forelse($items as $n)
            @php $d = $n->data; @endphp
            <button type="button" wire:click="markAsRead('{{ $n->id }}')"
                class="nb-item {{ $n->read_at ? '' : 'unread' }}">
                <span class="nb-ico bg-{{ $d['color'] ?? 'primary' }}"><i class="bi {{ $d['icon'] ?? 'bi-bell' }}"></i></span>
                <div class="flex-grow-1">
                    <div class="nb-title">{{ $d['title'] ?? 'Notifikasi' }}</div>
                    <div class="nb-body">{{ $d['body'] ?? '' }}</div>
                    <div class="nb-time"><i class="bi bi-clock me-1"></i>{{ $n->created_at->diffForHumans() }}</div>
                </div>
                @unless($n->read_at)<span class="bg-primary rounded-circle mt-1" style="width:8px;height:8px;flex-shrink:0;"></span>@endunless
            </button>
            @empty
            <div class="nb-empty"><i class="bi bi-bell-slash fs-4 d-block mb-2 opacity-50"></i>Belum ada notifikasi.</div>
            @endforelse
        </div>
    </div>
</div>
