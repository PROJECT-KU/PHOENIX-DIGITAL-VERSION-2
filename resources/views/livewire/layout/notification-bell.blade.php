<div wire:poll.30s x-data="{ open: false }" class="position-relative" style="z-index: 1050;"
    id="lemon-bell" data-unread="{{ $unread }}">
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

        /* ===== Bagian Orcha =====
           Warnanya sengaja beda dari lemon: navy-emas, sama dengan pita mode
           Orcha di bilah samping. Yang dilihat admin sekilas harus langsung
           menjawab "ini aplikasi yang mana", tanpa membaca judulnya. */
        .nb-orcha-badge { position: absolute; top: -6px; left: -6px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; background: #ffd772; color: #5b3d00; font-size: .68rem; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(15,45,74,.28); }
        .nb-orcha-judul { padding: 8px 16px 6px; font-size: .66rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: #1d6fa5; background: #f4f9fd; border-bottom: 1px solid #e6eef5; display: flex; align-items: center; gap: .35rem; }
        .nb-orcha-judul i.bi { display: flex; align-items: center; line-height: 1; }
        .nb-item.nb-orcha { background: #fbfdff; text-decoration: none; }
        .nb-item.nb-orcha:hover { background: #f2f8fd; }
        .nb-orcha .nb-ico { background: linear-gradient(135deg, #0f2d4a, #1d6fa5); }
        .nb-empty { padding: 28px 16px; text-align: center; color: #94a3b8; font-size: .85rem; }
        .nb-foot { padding: 10px 16px; border-top: 1px solid #f1f5f9; text-align: center; }
        .nb-push-btn { border: 0; background: transparent; color: #4d7c0f; font-size: .78rem; font-weight: 600; cursor: pointer; padding: 4px 8px; border-radius: 8px; transition: .12s; }
        .nb-push-btn:hover { background: rgba(132,204,22,.12); }
    </style>

    <button type="button" class="nb-btn" @click="open = !open" title="Notifikasi">
        <i class="bi bi-bell"></i>
        @if($unread > 0)
        <span class="nb-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif

        {{-- Lencana kedua, di sisi berlawanan dan berwarna emas: pekerjaan
             Orcha. Dipisah dari lencana merah lemon karena keduanya berbeda
             sifatnya — yang merah hilang begitu dibaca, yang emas hilang hanya
             kalau dikerjakan. --}}
        @if($orchaJumlah > 0)
        <span class="nb-orcha-badge" title="{{ $orchaJumlah }} hal di Orcha menunggu ditindak">
            {{ $orchaJumlah > 99 ? '99+' : $orchaJumlah }}
        </span>
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
            @if($orcha->isNotEmpty())
                <div class="nb-orcha-judul">
                    <i class="bi bi-water"></i> Orcha Journey
                </div>

                {{-- Tautan biasa, bukan tombol: tidak ada yang perlu ditandai
                     dibaca — yang perlu dilakukan adalah membuka halamannya. --}}
                @foreach($orcha as $o)
                    <a href="{{ $o['tautan'] }}" wire:navigate class="nb-item nb-orcha">
                        <span class="nb-ico"><i class="bi {{ $o['ikon'] }}"></i></span>
                        <div class="flex-grow-1">
                            <div class="nb-title">{{ $o['judul'] }}</div>
                            <div class="nb-body">{{ $o['isi'] }}</div>
                            <div class="nb-time">
                                <i class="bi bi-arrow-right-circle me-1"></i>{{ $o['aksi'] }}
                            </div>
                        </div>
                    </a>
                @endforeach

                <div class="nb-orcha-judul" style="color:#4f46e5;background:#f7f8ff;border-color:#eef0f7">
                    <i class="bi bi-lemon"></i> lemon
                </div>
            @endif

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
        <div class="nb-foot">
            <button type="button" id="lemon-push-toggle" class="nb-push-btn"
                onclick="window.lemonPush && window.lemonPush.toggle()">🔔 Aktifkan notifikasi perangkat</button>
            {{-- Label diselaraskan dari localStorage oleh window.lemonSoundSync (tiap render). --}}
            <button type="button" class="nb-push-btn lemon-sound-toggle"
                onclick="window.lemonToggleSound()">🔊 Suara notifikasi: Aktif</button>
        </div>
    </div>
</div>
