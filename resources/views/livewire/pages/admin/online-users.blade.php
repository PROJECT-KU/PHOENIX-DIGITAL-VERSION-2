<div class="card border-0 shadow-sm rounded-4 stat-card overflow-hidden"
    wire:poll.10s
    style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px);">

    <!-- Header dengan statistik karyawan aktif -->
    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark mb-0">Karyawan Online</h5>
        </div>
        <div class="badge bg-gradient-purple rounded-pill px-3 py-2 shadow-sm">
            {{ $users->where('online', true)->count() }} Aktif
        </div>
    </div>

    <div class="card-content pb-4 mt-3">
        @forelse($users as $user)
        <div class="recent-message d-flex px-4 py-3 align-items-center border-bottom border-light">
            <!-- Avatar dengan Indikator Status -->
            <div class="avatar avatar-lg position-relative">
                <img src="{{ $user->profile_photo && Storage::disk('public')->exists($user->profile_photo) ? Storage::url($user->profile_photo) : asset('mazer/compiled/jpg/1.jpg') }}" alt="{{ $user->name }}" class="rounded-circle shadow-sm">
                <!-- Titik indikator status -->
                <span class="position-absolute bottom-0 end-0 p-1 {{ $user->online ? 'bg-success' : 'bg-danger' }} border border-white rounded-circle"></span>
            </div>

            <div class="name ms-4">
                <h6 class="mb-0 fw-bold text-dark">{{ $user->name }}</h6>

                {{-- Status Badge Colorful --}}
                @if ($user->online)
                <span class="badge bg-light-success text-success mt-1 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> ONLINE
                </span>
                @else
                <span class="badge bg-light-danger text-danger mt-1 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> OFFLINE
                </span>
                @if ($user->last_seen_diff)
                <small class="d-block text-muted mt-1" style="font-size: 0.7rem;">
                    Terakhir: {{ $user->last_seen_diff }}
                </small>
                @endif
                @endif
            </div>
        </div>
        @empty
        <p class="text-muted px-4 py-3 text-center">Tidak ada karyawan yang tercatat.</p>
        @endforelse
    </div>
</div>