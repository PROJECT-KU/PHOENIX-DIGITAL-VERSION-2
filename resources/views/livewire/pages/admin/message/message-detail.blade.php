
@section('title')
Detail Pesan Masuk || PT. Asthana Cipta Mandiri
@stop
<div class="container-fluid">
    <!--================== HEADER ==================-->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="title-wrapper text-center text-md-start">
                    <h3 class="gradient-text fw-bold mb-1">Detail Pesan Masuk</h3>
                    <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                        @php
                        $breadcrumbs = [
                        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                        ['name' => 'Pesan Masuk', 'url' => route('admin.message.index')],
                        ['name' => 'Detail Pesan'],
                        ];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </div>
                </div>
                <a href="{{ route('admin.message.index') }}" wire:navigate
                    class="btn btn-light rounded-pill d-inline-flex align-items-center justify-content-center gap-2 px-3">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <!--================== INFORMASI PENGIRIM ==================-->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="stat-icon-wrapper flex-shrink-0"
                    style="width: 56px; height: 56px; font-size: 1.5rem; border-radius: 16px; background: linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff; display:inline-flex; align-items:center; justify-content:center;">
                    {{ strtoupper(substr($message->name, 0, 1)) }}
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark text-capitalize">{{ $message->name }}</h5>
                    <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $message->email }}</small>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-calendar-event me-2"></i>Tanggal</span>
                        <span class="fw-semibold text-dark">{{ $message->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') }} WIB</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-pc-display me-2"></i>IP Pengirim</span>
                        <span class="fw-semibold text-dark">{{ $message->ip_address ?? '-' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-browser-chrome me-2"></i>User Agent</span>
                        <span class="fw-semibold text-dark text-end" style="max-width: 60%; font-size: 0.8rem;">{{ $message->user_agent ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-check-circle me-2"></i>Status</span>
                        <span class="badge bg-gradient-green rounded-pill px-3 py-1">Sudah Dibaca</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--================== ISI PESAN ==================-->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="stat-icon-wrapper" style="width: 38px; height: 38px; font-size: 1rem; border-radius: 11px; background: linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff; display:inline-flex; align-items:center; justify-content:center;">
                    <i class="bi bi-chat-left-text-fill"></i>
                </span>
                <h5 class="fw-bold mb-0 text-dark">Isi Pesan</h5>
            </div>
            <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #eef0f6;">
                <p class="mb-0 text-dark" style="white-space: pre-line; line-height: 1.7;">{{ $message->message }}</p>
            </div>

            <div class="mt-4">
                <a href="mailto:{{ $message->email }}"
                    class="btn btn-primary rounded-pill d-inline-flex align-items-center gap-2 px-4">
                    <i class="bi bi-reply-fill"></i>
                    <span>Balas via Email</span>
                </a>
            </div>
        </div>
    </div>
</div>
