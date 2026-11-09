
@section('title')
Detail Pelamar Kerja || lemon
@stop
<div class="container-fluid">
    @php
        // Dirakit di sini: perbandingan di dalam direktif blok membuat penanda
        // morph Livewire timpang (PenandaMorphAdminTest).
        $daftarStatus = \App\Models\JobApplication::STATUS;
        $statusSama = $statusBaru === $pelamar->status;
        $adaTemplatSurel = \App\Mail\StatusLamaranMail::adaTemplat($statusBaru);
        $diubahLabel = $pelamar->status_diubah_at?->locale('id')->isoFormat('D MMM YYYY, HH:mm');
    @endphp
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
                    <span class="badge bg-gradient-blue rounded-pill px-3 py-1 mt-1 text-capitalize">{{ $pelamar->posisi_label }}</span>
                    <span class="badge rounded-pill px-3 py-1 mt-1 bg-{{ $pelamar->status_warna }}">{{ $pelamar->status_label }}</span>
                    <div class="small text-muted mt-1">No. lamaran <strong class="text-dark user-select-all">{{ $pelamar->nomor_lamaran ?? '-' }}</strong></div>
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
                <div class="col-md-6">
                    <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                        <span class="text-muted text-nowrap"><i class="bi bi-link-45deg me-2"></i>Portofolio</span>
                        @if ($pelamar->portofolio_url)
                            <a href="{{ $pelamar->portofolio_url }}" target="_blank" rel="noopener noreferrer" class="fw-semibold text-truncate">{{ $pelamar->portofolio_url }}</a>
                        @else
                            <span class="fw-semibold text-dark">-</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-megaphone me-2"></i>Tahu Lowongan dari</span>
                        <span class="fw-semibold text-dark">{{ $pelamar->sumber_info_label ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--================== TAHAP SELEKSI ==================-->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="stat-icon-wrapper" style="width: 38px; height: 38px; font-size: 1rem; border-radius: 11px; background: linear-gradient(135deg,#2455e6,#0ea5c6); color:#fff; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="bi bi-signpost-split"></i>
                        </span>
                        <h5 class="fw-bold mb-0 text-dark">Tahap Seleksi</h5>
                    </div>
                    <p class="small text-muted mb-3">
                        Pelamar bisa melihat tahap ini di halaman Cek Status Lamaran situs.
                        @if ($diubahLabel)
                            Terakhir diubah {{ $diubahLabel }} WIB.
                        @endif
                    </p>

                    <div class="d-flex flex-wrap gap-2 mb-3" role="radiogroup" aria-label="Tahap seleksi">
                        @foreach ($daftarStatus as $kunciStatus => $infoStatus)
                            <button type="button" wire:click="$set('statusBaru', '{{ $kunciStatus }}')" wire:key="tahap-{{ $kunciStatus }}"
                                class="btn btn-sm rounded-pill px-3 {{ $statusBaru === $kunciStatus ? 'btn-'.$infoStatus[1] : 'btn-outline-'.$infoStatus[1] }}"
                                role="radio" aria-checked="{{ $statusBaru === $kunciStatus ? 'true' : 'false' }}">
                                {{ $infoStatus[0] }}
                            </button>
                        @endforeach
                    </div>

                    @if ($adaTemplatSurel)
                        <div class="mb-3">
                            <label class="form-label fw-semibold small" for="pesanPelamar">Pesan untuk pelamar <span class="text-muted fw-normal">(opsional, ikut di surel)</span></label>
                            <textarea id="pesanPelamar" class="form-control" rows="3" wire:model="pesanPelamar" maxlength="2000" placeholder="mis. Wawancara Senin, 17 Nov 2026 pukul 10.00 WIB lewat Google Meet. Tautan menyusul.">{{ $pesanPelamar }}</textarea>
                            @error('pesanPelamar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="kirimEmail" wire:model="kirimEmail" @checked($kirimEmail)>
                            <label class="form-check-label small" for="kirimEmail">Kirim surel pemberitahuan ke {{ $pelamar->email }}</label>
                        </div>
                    @endif

                    <button type="button" class="btn btn-primary rounded-pill px-4" wire:click="simpanStatus" wire:loading.attr="disabled" wire:target="simpanStatus" @disabled($statusSama)>
                        <span wire:loading.remove wire:target="simpanStatus"><i class="bi bi-check2-circle me-1"></i>Simpan tahap</span>
                        <span wire:loading wire:target="simpanStatus"><span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="stat-icon-wrapper" style="width: 38px; height: 38px; font-size: 1rem; border-radius: 11px; background: linear-gradient(135deg,#e5940a,#ff8a3d); color:#fff; display:inline-flex; align-items:center; justify-content:center;">
                            <i class="bi bi-journal-text"></i>
                        </span>
                        <h5 class="fw-bold mb-0 text-dark">Catatan Internal</h5>
                    </div>
                    <p class="small text-muted mb-3">Hanya terlihat oleh tim di lemon, tidak pernah dikirim ke pelamar.</p>
                    <textarea class="form-control flex-grow-1 mb-3" rows="5" wire:model="catatanInternal" maxlength="5000" placeholder="Kesan wawancara, nilai tes, rekomendasi…">{{ $catatanInternal }}</textarea>
                    <div>
                        <button type="button" class="btn btn-outline-primary rounded-pill px-4" wire:click="simpanCatatan" wire:loading.attr="disabled" wire:target="simpanCatatan">
                            <i class="bi bi-save me-1"></i>Simpan catatan
                        </button>
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

    <!--================== BERKAS PENDUKUNG (opsional) ==================-->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="stat-icon-wrapper" style="width: 38px; height: 38px; font-size: 1rem; border-radius: 11px; background: linear-gradient(135deg,#17a974,#0ea5c6); color:#fff; display:inline-flex; align-items:center; justify-content:center;">
                        <i class="bi bi-paperclip"></i>
                    </span>
                    <h5 class="fw-bold mb-0 text-dark">Berkas Pendukung</h5>
                    <span class="badge rounded-pill px-2 py-1" style="background:#f1f5f9; color:#64748b;">opsional</span>
                </div>
                @if ($pelamar->lampiran_path)
                <a href="{{ route('admin.pelamar.lampiran', $pelamar) }}" download
                    class="btn btn-primary btn-sm rounded-pill d-inline-flex align-items-center gap-1 px-3">
                    <i class="bi bi-download"></i> <span>Download</span>
                </a>
                @endif
            </div>
            @if ($pelamar->lampiran_path)
            <div class="ratio rounded-3 overflow-hidden" style="--bs-aspect-ratio: 141.42%;">
                <iframe src="{{ route('admin.pelamar.lampiran', $pelamar) }}" type="application/pdf" class="border-0" style="width: 100%; height: 100%;">
                    <p>Browser Anda tidak mendukung preview PDF. <a href="{{ route('admin.pelamar.lampiran', $pelamar) }}" download>Download berkas pendukung</a></p>
                </iframe>
            </div>
            @else
            <div class="text-center text-muted py-4">
                <i class="bi bi-paperclip fs-1 d-block mb-2"></i>
                <p class="mb-0">Pelamar tidak melampirkan berkas pendukung.</p>
            </div>
            @endif
        </div>
    </div>
</div>
