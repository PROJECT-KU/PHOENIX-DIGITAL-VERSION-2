
@section('title')
Detail Pelamar Kerja || lemon
@stop
<div class="container-fluid">
    <!--================== HEADER ==================-->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="title-wrapper text-center text-md-start">
                    <h3 class="gradient-text fw-bold mb-1">Detail Pelamar Kerja</h3>
                    <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                        @php
                        $breadcrumbs = [
                        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                        ['name' => 'Data Pelamar', 'url' => route('admin.pelamar.index')],
                        ['name' => 'Detail Data'],
                        ];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </div>
                </div>
                <a href="{{ route('admin.pelamar.index') }}" wire:navigate
                    class="btn btn-light rounded-pill d-inline-flex align-items-center justify-content-center gap-2 px-3">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <!--================== INFORMASI PELAMAR ==================-->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="stat-icon-wrapper flex-shrink-0"
                    style="width: 56px; height: 56px; font-size: 1.5rem; border-radius: 16px; background: linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff; display:inline-flex; align-items:center; justify-content:center;">
                    {{ strtoupper(substr($pelamar->name, 0, 1)) }}
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark text-capitalize">{{ $pelamar->name }}</h5>
                    <span class="badge bg-gradient-blue rounded-pill px-3 py-1 mt-1 text-capitalize">{{ $pelamar->job->title ?? '-' }}</span>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-envelope me-2"></i>Email</span>
                        <span class="fw-semibold text-dark">{{ $pelamar->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-telephone me-2"></i>No. Telepon</span>
                        <span class="fw-semibold text-dark">{{ $pelamar->phone ?: '-' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-calendar-event me-2"></i>Tanggal Melamar</span>
                        <span class="fw-semibold text-dark">{{ $pelamar->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') }} WIB</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-pc-display me-2"></i>IP Address</span>
                        <span class="fw-semibold text-dark">{{ $pelamar->ip_address ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--================== CV PREVIEW ==================-->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="stat-icon-wrapper" style="width: 38px; height: 38px; font-size: 1rem; border-radius: 11px; background: linear-gradient(135deg,#059669,#10b981); color:#fff; display:inline-flex; align-items:center; justify-content:center;">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                    </span>
                    <h5 class="fw-bold mb-0 text-dark">Curriculum Vitae (CV)</h5>
                </div>
                @if ($pelamar->cv_path)
                <a href="{{ route('admin.pelamar.cv', $pelamar) }}" download
                    class="btn btn-primary btn-sm rounded-pill d-inline-flex align-items-center gap-1 px-3">
                    <i class="bi bi-download"></i> <span>Download CV</span>
                </a>
                @endif
            </div>
            @if ($pelamar->cv_path)
            <div class="ratio rounded-3 overflow-hidden" style="--bs-aspect-ratio: 141.42%;">
                <iframe src="{{ route('admin.pelamar.cv', $pelamar) }}" type="application/pdf" class="border-0" style="width: 100%; height: 100%;">
                    <p>Browser Anda tidak mendukung preview PDF. <a href="{{ route('admin.pelamar.cv', $pelamar) }}" download>Download CV</a></p>
                </iframe>
            </div>
            @else
            <div class="text-center text-muted py-5">
                <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                <p class="mb-0">Pelamar tidak melampirkan CV.</p>
            </div>
            @endif
        </div>
    </div>

    <!--================== COVER LETTER PREVIEW ==================-->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="stat-icon-wrapper" style="width: 38px; height: 38px; font-size: 1rem; border-radius: 11px; background: linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff; display:inline-flex; align-items:center; justify-content:center;">
                        <i class="bi bi-envelope-paper-fill"></i>
                    </span>
                    <h5 class="fw-bold mb-0 text-dark">Cover Letter</h5>
                </div>
                @if ($pelamar->cover_letter_path)
                <a href="{{ route('admin.pelamar.surat', $pelamar) }}" download
                    class="btn btn-primary btn-sm rounded-pill d-inline-flex align-items-center gap-1 px-3">
                    <i class="bi bi-download"></i> <span>Download</span>
                </a>
                @endif
            </div>
            @if ($pelamar->cover_letter_path)
            <div class="ratio rounded-3 overflow-hidden" style="--bs-aspect-ratio: 141.42%;">
                <iframe src="{{ route('admin.pelamar.surat', $pelamar) }}" type="application/pdf" class="border-0" style="width: 100%; height: 100%;">
                    <p>Browser Anda tidak mendukung preview PDF. <a href="{{ route('admin.pelamar.surat', $pelamar) }}" download>Download Cover Letter</a></p>
                </iframe>
            </div>
            @else
            <div class="text-center text-muted py-5">
                <i class="bi bi-envelope-x fs-1 d-block mb-2"></i>
                <p class="mb-0">Pelamar tidak melampirkan cover letter.</p>
            </div>
            @endif
        </div>
    </div>
</div>
