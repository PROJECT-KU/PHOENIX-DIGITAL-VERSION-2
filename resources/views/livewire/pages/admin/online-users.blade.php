<div class="card" wire:poll.10s>
    <div class="card-header">
        <h4>Karyawan Online</h4>
    </div>

    <div class="card-content pb-4">
        @forelse($users as $user)
        <div class="recent-message d-flex px-4 py-3">
            <div class="avatar avatar-lg">
                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
            </div>
            <div class="name ms-4">
                <h5 class="mb-1">{{ $user->name }}</h5>

                {{-- Status Online / Offline --}}
                @if ($user->online)
                <span class="text-success">🟢 Online</span>
                @else
                <span class="text-danger">
                    🔴 Offline
                    @if ($user->last_seen_diff)
                    (Terakhir online {{ $user->last_seen_diff }})
                    @endif
                </span>
                @endif
            </div>
        </div>
        @empty
        <p class="text-muted px-4 py-3">Tidak ada karyawan yang tercatat.</p>
        @endforelse
    </div>
</div>