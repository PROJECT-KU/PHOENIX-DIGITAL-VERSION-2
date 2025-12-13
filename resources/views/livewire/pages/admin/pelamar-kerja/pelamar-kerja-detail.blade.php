<div>
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <h3>Detail Pelamar Kerja</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pelamar', 'url' => route('admin.pelamar.index')],
        ['name' => 'Detail Data']
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>

    <!-- Informasi Pelamar -->
    <div class="mb-4 card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-bold" style="width: 150px;">Nama</td>
                            <td>: {{ $pelamar->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Email</td>
                            <td>: {{ $pelamar->email }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">No. Telepon</td>
                            <td>: {{ $pelamar->phone }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-bold" style="width: 150px;">Posisi</td>
                            <td>: {{ $pelamar->job->title }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tanggal Melamar</td>
                            <td>: {{ $pelamar->created_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }} WIB</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">IP Address</td>
                            <td>: {{ $pelamar->ip_address ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CV Preview -->
    <div class="mb-4 overflow-y-scroll card" style="min-height: 100vh;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Curriculum Vitae (CV)</h5>
            <a href="{{ Storage::url($pelamar->cv_path) }}"
                download
                class="btn btn-sm btn-primary">
                <i class="bi bi-download"></i> Download CV
            </a>
        </div>
        <div class="card-body">
            <div class="ratio" style="--bs-aspect-ratio: 141.42%;">
                <iframe
                    src="{{ Storage::url($pelamar->cv_path) }}"
                    type="application/pdf"
                    class="border-0"
                    style="width: 100%; height: 100%;">
                    <p>Browser Anda tidak mendukung preview PDF.
                        <a href="{{ Storage::url($pelamar->cv_path) }}" download>
                            Download CV
                        </a>
                    </p>
                </iframe>
            </div>
        </div>
    </div>

    <!-- Cover Letter Preview -->
    <div class="mb-4 card" style="min-height: 100vh;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Cover Letter</h5>
            <a href="{{ Storage::url($pelamar->cover_letter_path) }}"
                download
                class="btn btn-sm btn-primary">
                <i class="bi bi-download"></i> Download Cover Letter
            </a>
        </div>
        <div class="card-body">
            <div class="ratio" style="--bs-aspect-ratio: 141.42%;">
                <iframe
                    src="{{ Storage::url($pelamar->cover_letter_path) }}"
                    type="application/pdf"
                    class="border-0"
                    style="width: 100%; height: 100%;">
                    <p>Browser Anda tidak mendukung preview PDF.
                        <a href="{{ Storage::url($pelamar->cover_letter_path) }}" download>
                            Download Cover Letter
                        </a>
                    </p>
                </iframe>
            </div>
        </div>
    </div>
</div>