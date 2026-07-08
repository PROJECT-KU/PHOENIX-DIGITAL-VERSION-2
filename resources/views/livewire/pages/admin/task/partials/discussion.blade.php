{{-- Diskusi grup (dibagikan semua penerima). Butuh: $activeTask, $avatarPalette, badge maps --}}
@php
    $groupComments = $activeTask->groupComments;
    $pinned = $groupComments->filter->isPinned();
    $threadColors = [];
    foreach ($groupComments as $c) {
        if ($c->user_id !== auth()->id() && ! isset($threadColors[$c->user_id])) {
            $threadColors[$c->user_id] = $avatarPalette[count($threadColors) % count($avatarPalette)];
        }
    }
@endphp
<div class="d-flex align-items-center justify-content-between mb-2">
    <div class="ts-section-lbl mb-0"><i class="bi bi-chat-dots me-1"></i>Diskusi Grup</div>
    <span class="badge bg-light text-secondary border rounded-pill">{{ $groupComments->count() }} komentar</span>
</div>

@if($pinned->isNotEmpty())
<div class="ts-pinned mb-2">
    <div class="ts-pinned-lbl"><i class="bi bi-pin-angle-fill me-1"></i>Disematkan ({{ $pinned->count() }})</div>
    @foreach($pinned as $c)
    <div class="ts-pin-item">
        <i class="bi bi-pin-angle-fill ts-pin-ico"></i>
        <span class="ts-pin-who">{{ $c->user_id === auth()->id() ? 'Anda' : ($c->user->name ?? '-') }}:</span>
        <span class="ts-pin-body">{{ \Illuminate\Support\Str::limit($c->body ?: ($c->file_name ?: 'Lampiran'), 90) }}</span>
        <button type="button" class="ts-pin-x" wire:click="togglePin('{{ $c->id }}')" title="Lepas sematan"><i class="bi bi-x-lg"></i></button>
    </div>
    @endforeach
</div>
@endif

<div class="ts-thread mb-3" wire:key="ts-thread-{{ $groupComments->count() }}-{{ $pinned->count() }}"
    x-data x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight; })">
    @forelse($groupComments as $c)
    @php $mine = $c->user_id === auth()->id(); $ac = $threadColors[$c->user_id] ?? $avatarPalette[0]; @endphp
    <div class="ts-msg {{ $mine ? 'mine' : '' }}">
        <div class="ts-msg-av" @unless($mine) style="background: linear-gradient(135deg, {{ $ac[0] }}, {{ $ac[1] }});" @endunless>{{ strtoupper(substr($c->user->name ?? '?',0,1)) }}</div>
        <div class="ts-bubble {{ $c->type === 'revisi' ? 'ts-bubble-revisi' : '' }} {{ $c->isPinned() ? 'ts-bubble-pinned' : '' }}" @if(!$mine && $c->type !== 'revisi') style="border-left: 3px solid {{ $ac[0] }};" @endif>
            <div class="d-flex align-items-center justify-content-between gap-2">
                <span class="who" @unless($mine) style="color: {{ $ac[1] }};" @endunless>{{ $mine ? 'Anda' : ($c->user->name ?? '-') }}</span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span class="when">{{ $c->created_at->diffForHumans() }}</span>
                    <button type="button" class="ts-pin-btn {{ $c->isPinned() ? 'active' : '' }}" wire:click="togglePin('{{ $c->id }}')" title="{{ $c->isPinned() ? 'Lepas sematan' : 'Sematkan' }}"><i class="bi bi-pin-angle{{ $c->isPinned() ? '-fill' : '' }}"></i></button>
                </span>
            </div>
            @if($c->type === 'revisi')
            <div class="mt-1"><span class="badge bg-warning-subtle text-warning border border-warning rounded-pill" style="font-size:.68rem;"><i class="bi bi-arrow-counterclockwise me-1"></i>Dibuka kembali untuk revisi</span></div>
            @endif
            @if($c->body)
            @php $bodyHtml = preg_replace('/@([\p{L}\p{N}_]+)/u', '<span class="ts-mention">@$1</span>', e($c->body)); @endphp
            <div class="mt-1">{!! nl2br($bodyHtml) !!}</div>
            @endif
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

{{-- Composer (dengan @mention utk task grup) --}}
<div class="ts-composer" x-data="tsMention(@js($mentionMembers ?? []))">
    <div class="d-flex align-items-center gap-2 position-relative">
        <i class="bi bi-chat-left-text ts-input-ico"></i>
        <textarea x-ref="ta" wire:model="newComment" rows="1" class="form-control"
            @input="onInput" @keydown="onKeydown" @keyup="onInput"
            placeholder="{{ !empty($mentionMembers) ? 'Tulis komentar… ketik @ untuk menyebut anggota' : 'Tulis komentar / laporan...' }}"></textarea>
        <div class="ts-mention-menu" x-show="open" x-cloak @click.outside="open=false" style="display:none;">
            <template x-for="(m, i) in filtered" :key="m">
                <button type="button" class="ts-mention-item" :class="{ active: i === active }"
                    @click="pick(m)" @mouseenter="active = i"><i class="bi bi-at"></i><span x-text="m"></span></button>
            </template>
        </div>
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
