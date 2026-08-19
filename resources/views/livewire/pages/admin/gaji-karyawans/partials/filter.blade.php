<div class="mb-3">
    <!--================== ACTION BAR: SEARCH + EXPORT + CREATE ==================-->
    <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-2 mb-3 header-action">
        <!-- Search -->
        <div class="form-group position-relative flex-grow-1 mb-0">
            <div class="form-control-icon">
                <i class="bi bi-search"></i>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                placeholder="Cari karyawan, ID transaksi, bank, no. rek, nominal, status, atau tanggal (mis. Juni 2026)...">
            @if ($search)
            <span wire:click="$set('search', '')"
                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
            </span>
            @endif
        </div>

        <!-- Generate + Export + Create -->
        <div class="d-flex gap-2">
            @if (auth()->user()->hasPermission('create_gajikaryawan'))
            <button type="button" class="btn btn-info text-white rounded-pill d-flex align-items-center justify-content-center gap-2 px-3 generate-gaji-btn"
                wire:loading.attr="disabled" wire:target="generateGaji" title="Buat draft gaji semua karyawan dari periode bulan sebelumnya">
                <span wire:loading.remove wire:target="generateGaji" class="d-flex align-items-center gap-2">
                    <i class="bi bi-magic"></i>
                    <span>Generate Gaji</span>
                </span>
            </button>
            @endif
            <button wire:click="downloadPdf" wire:loading.attr="disabled" wire:target="downloadPdf"
                class="btn btn-danger rounded-pill d-flex align-items-center justify-content-center gap-2 px-3">
                {{-- Selama berkas disiapkan & diunduh, isi tombol diganti spinner
                     tanpa teks. --}}
                <span wire:loading wire:target="downloadPdf" class="spinner-border spinner-border-sm"
                    role="status" aria-hidden="true"></span>
                <span wire:loading.remove wire:target="downloadPdf" class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <span>Export PDF</span>
                </span>
            </button>
            @if (auth()->user()->hasPermission('create_gajikaryawan'))
            <a class="btn btn-primary rounded-pill d-flex align-items-center justify-content-center gap-2 px-3"
                href="{{ route('admin.gajikaryawan.create') }}" wire:navigate>
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Gaji Karyawan</span>
            </a>
            @endif
        </div>
    </div>

    <!--================== FILTER PERIODE ==================-->
    <div class="card border-0 shadow-sm rounded-4 stat-card overflow-hidden">
        <div class="card-body p-3 px-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                    <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                        style="width: 40px; height: 40px; font-size: 1.1rem; border-radius: 12px;">
                        <i class="bi bi-funnel"></i>
                    </span>
                    <span>Filter Periode</span>
                </div>

                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                    <select wire:model.live="bulan" class="form-select rounded-3" style="min-width: 160px;">
                        <option value="">Semua Bulan</option>
                        @foreach ($daftarBulan as $num => $nama)
                        <option value="{{ $num }}">{{ $nama }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="tahun" class="form-select rounded-3" style="min-width: 130px;">
                        <option value="">Semua Tahun</option>
                        @foreach ($daftarTahun as $th)
                        <option value="{{ $th }}">{{ $th }}</option>
                        @endforeach
                    </select>

                    <button wire:click="resetFilter" type="button"
                        class="btn btn-light-danger rounded-3 d-inline-flex align-items-center justify-content-center"
                        title="Reset ke periode gaji berjalan">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>

            {{-- Rentang tanggal nyata dari periode gaji terpilih. Tidak ada pilihan
                 "Kalender" di sini: baris gaji menyimpan periodenya sendiri
                 (periode_bulan/periode_tahun), jadi memilih "Agustus" MEMANG berarti
                 21 Jul – 20 Agt. Chip ini menyamakan tampilannya dengan Cash Flow dkk. --}}
            <div class="mt-3 pt-3 border-top">
                @if ($siklusMulai && $siklusAkhir)
                <div class="siklus-chip d-inline-flex align-items-center gap-2">
                    <span class="siklus-chip-ico d-inline-flex align-items-center justify-content-center">
                        <i class="bi bi-calendar-range"></i>
                    </span>
                    <span class="siklus-chip-label">Periode</span>
                    {{-- locale('id') dipaksa karena APP_LOCALE=en (lihat CLAUDE.md) --}}
                    <span class="siklus-chip-date">{{ $siklusMulai->locale('id')->translatedFormat('d M Y') }}</span>
                    <i class="bi bi-arrow-right siklus-chip-arrow"></i>
                    <span class="siklus-chip-date">{{ $siklusAkhir->locale('id')->translatedFormat('d M Y') }}</span>
                </div>
                <div class="text-muted mt-2" style="font-size:.78rem;">
                    <i class="bi bi-info-circle me-1" style="vertical-align:-0.125em;"></i>Siklus Gaji ({{ \App\Support\PeriodeGaji::cutoffDay() + 1 }}–{{ \App\Support\PeriodeGaji::cutoffDay() }}) — gajian tanggal {{ \App\Support\PeriodeGaji::cutoffDay() }}. Sama dengan periode di <b>Cash Flow</b>, <b>Pengeluaran</b>, <b>Modal</b>, <b>Pemasukan</b>, & <b>Peminjaman</b>.
                </div>
                @else
                <span class="text-muted" style="font-size:.85rem;">
                    <i class="bi bi-info-circle me-1" style="vertical-align:-0.125em;"></i>Pilih <b>bulan</b> dan <b>tahun</b> untuk melihat rentang tanggalnya. Contoh: Juli → {{ \App\Support\PeriodeGaji::label(7, now()->year) }}.
                </span>
                @endif
            </div>
        </div>
    </div>
</div>