@section('title')
Presensi || PT. Asthana Cipta Mandiri
@stop

<div id="presensiRoot">
    <style>
        .pr-card {
            border: 1px solid rgba(108, 99, 255, 0.14);
            border-radius: 1.1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(248, 249, 255, 0.96));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
        }

        .pr-ic {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            flex-shrink: 0;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
        }

        .pr-ic.green {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .pr-ic.amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .pr-ic i.bi {
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        .pr-clock {
            font-weight: 800;
            font-size: 2.1rem;
            letter-spacing: .01em;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
        }

        .pr-btn {
            border-radius: 12px;
            font-weight: 700;
            padding: .8rem 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border: none;
        }

        .pr-btn i.bi {
            display: inline-flex;
            align-items: center;
            line-height: 1;
            font-size: 1.05em;
        }

        .pr-btn-off {
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            color: #fff;
        }

        .pr-btn-on {
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: #fff;
        }

        .pr-btn-out {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }

        .pr-btn-lembur {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
        }

        .pr-btn:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }

        .pr-info-row {
            display: flex;
            align-items: flex-start;
            gap: .55rem;
            padding: .55rem 0;
            border-bottom: 1px dashed #eef0f6;
            font-size: .88rem;
            color: #475569;
        }

        .pr-info-row:last-child {
            border-bottom: none;
        }

        .pr-info-row i.bi {
            color: #6c63ff;
            margin-top: .15rem;
        }

        .pr-status-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .8rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 700;
        }

        .pr-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-weight: 600;
        }

        .pr-badge i.bi,
        .pr-status-pill i.bi {
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }
    </style>

    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start">
                        <h3 class="gradient-text fw-bold mb-1">Presensi</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Presensi']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>
                    <div class="text-center text-md-end">
                        <div class="pr-clock" id="prClock">--:--:--</div>
                        <small class="text-muted">{{ now()->translatedFormat('l, d F Y') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- ===== Status & Aksi ===== -->
            <div class="col-lg-7">
                <div class="pr-card p-4 h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-clipboard-check text-primary fs-5"></i>
                        <h5 class="fw-bold mb-0">Presensi Hari Ini</h5>
                    </div>

                    {{-- Status kehadiran --}}
                    @php $h = $this->todayHadir; @endphp
                    @if (! $h)
                    <div class="alert alert-light border rounded-3 d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-info-circle text-muted"></i>
                        <span>Kamu belum absen masuk hari ini. Pilih jenis kehadiran di bawah.</span>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-6">
                            <button type="button" class="pr-btn pr-btn-off w-100" data-presensi-action="masuk"
                                data-tipe="hadir_offline">
                                <i class="bi bi-building-check"></i> Masuk (Hadir Offline)
                            </button>
                        </div>
                        <div class="col-sm-6">
                            <button type="button" class="pr-btn pr-btn-on w-100" data-presensi-action="masuk"
                                data-tipe="hadir_online">
                                <i class="bi bi-globe2"></i> Masuk (Hadir Online)
                            </button>
                        </div>
                    </div>
                    <small class="text-muted d-block">Offline harus dalam radius kantor (maks {{ $radius }} m). Online
                        bebas lokasi, tetap dicatat titiknya.</small>
                    @else
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="pr-status-pill"
                            style="background:rgba(16,185,129,.12); color:#059669;">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk {{ $h->waktu_masuk->format('H:i') }} ·
                            {{ $h->tipe_label }}
                        </span>
                        @if ($h->is_selesai)
                        <span class="pr-status-pill" style="background:rgba(239,68,68,.12); color:#dc2626;">
                            <i class="bi bi-box-arrow-right"></i> Pulang {{ $h->waktu_pulang->format('H:i') }}
                        </span>
                        <span class="pr-status-pill" style="background:rgba(108,99,255,.12); color:#4e46e5;">
                            <i class="bi bi-stopwatch"></i> {{ $h->durasi_label }}
                        </span>
                        @endif
                    </div>
                    @if (! $h->is_selesai)
                    <button type="button" class="pr-btn pr-btn-out w-100" data-presensi-action="pulang"
                        data-id="{{ $h->id }}">
                        <i class="bi bi-box-arrow-right"></i> Absen Pulang
                    </button>
                    <small class="text-muted d-block mt-2">Bisa pulang minimal setelah kerja {{ rtrim(rtrim(number_format($minDurasiJam, 1, '.', ''), '0'), '.') }}
                        jam.@if ($h->tipe === 'hadir_offline') Pulang offline wajib dalam radius kantor.@endif</small>
                    @else
                    <div class="alert alert-success bg-success-subtle border-success rounded-3 mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Presensi hari ini selesai. Terima kasih! 🙌</span>
                    </div>
                    @endif
                    @endif

                    <hr class="my-3">

                    {{-- Lembur --}}
                    @php $l = $this->todayLembur; @endphp
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-moon-stars text-warning"></i>
                        <span class="fw-semibold">Lembur</span>
                    </div>
                    @if ($l)
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="pr-status-pill" style="background:rgba(245,158,11,.14); color:#b45309;">
                            <i class="bi bi-hourglass-split"></i> Lembur berjalan sejak {{ $l->waktu_masuk->format('H:i') }}
                        </span>
                    </div>
                    <button type="button" class="pr-btn pr-btn-out w-100" data-presensi-action="pulang"
                        data-id="{{ $l->id }}">
                        <i class="bi bi-box-arrow-right"></i> Selesai Lembur
                    </button>
                    @else
                    <button type="button" class="pr-btn pr-btn-lembur w-100" data-presensi-action="lembur">
                        <i class="bi bi-moon-stars"></i> Mulai Lembur
                    </button>
                    <small class="text-muted d-block mt-2">Lembur bebas lokasi (dicatat titiknya), tanpa syarat radius &
                        durasi minimal.</small>
                    @endif
                </div>
            </div>

            <!-- ===== Info Lokasi ===== -->
            <div class="col-lg-5">
                <div class="pr-card p-4 h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-geo-alt text-primary fs-5"></i>
                        <h5 class="fw-bold mb-0">Aturan & Lokasi</h5>
                    </div>
                    <div class="pr-info-row">
                        <i class="bi bi-building"></i>
                        <div><b>{{ $officeNama ?: 'Kantor' }}</b>
                            @if ($officeLat === null)
                            <span class="badge bg-danger-subtle text-danger border border-danger ms-1">Belum diatur</span>
                            @else
                            <div class="text-muted small">{{ number_format($officeLat, 6) }},
                                {{ number_format($officeLng, 6) }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="pr-info-row"><i class="bi bi-bullseye"></i>
                        <div>Radius maksimal absen <b>offline</b>: <b>{{ $radius }} meter</b></div>
                    </div>
                    <div class="pr-info-row"><i class="bi bi-stopwatch"></i>
                        <div>Durasi kerja minimal sebelum pulang:
                            <b>{{ rtrim(rtrim(number_format($minDurasiJam, 1, '.', ''), '0'), '.') }} jam</b></div>
                    </div>
                    <div class="pr-info-row"><i class="bi bi-globe2"></i>
                        <div>Hadir <b>online</b> & <b>lembur</b>: bebas lokasi, titik lokasi tetap direkam.</div>
                    </div>
                    @can('manage_presensi_setting')
                    <a href="{{ route('admin.presensi.pengaturan') }}"
                        class="btn btn-sm btn-outline-primary w-100 mt-2 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-sliders"></i> Ubah Pengaturan Presensi
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- ===== Riwayat ===== -->
        <div class="pr-card p-4 mt-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-clock-history text-primary fs-5"></i>
                <h5 class="fw-bold mb-0">Riwayat Presensi Saya</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr style="text-align:center;">
                            <th style="width:50px;">No</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Masuk</th>
                            <th>Pulang</th>
                            <th>Durasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $p)
                        <tr style="text-align:center;">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->tanggal->translatedFormat('d M Y') }}</td>
                            <td>
                                @if ($p->tipe === 'hadir_offline')
                                <span class="badge pr-badge bg-primary-subtle text-primary border border-primary"><i class="bi bi-building-check"></i> Offline</span>
                                @elseif ($p->tipe === 'hadir_online')
                                <span class="badge pr-badge bg-info-subtle text-info border border-info"><i class="bi bi-globe2"></i> Online</span>
                                @else
                                <span class="badge pr-badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-moon-stars"></i> Lembur</span>
                                @endif
                            </td>
                            <td>{{ $p->waktu_masuk->format('H:i') }}</td>
                            <td>{{ $p->waktu_pulang ? $p->waktu_pulang->format('H:i') : '—' }}</td>
                            <td>{{ $p->durasi_label }}</td>
                            <td>
                                @if ($p->status === 'aktif')
                                <span class="badge bg-success">Berjalan</span>
                                @else
                                <span class="badge bg-secondary">Selesai</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="empty-state-icon-wrapper mb-3">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1" style="color:#1e293b !important;">Belum Ada Riwayat</h5>
                                    <p class="text-muted mb-0" style="font-size:0.95rem;">Riwayat presensimu akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $history->links() }}</div>
        </div>
    </div>

    @include('livewire.layout.sweetalert')

    @script
    <script>
        // Jam berjalan
        function prTick() {
            var el = document.getElementById('prClock');
            if (!el) return;
            var d = new Date();
            function p(n) { return String(n).padStart(2, '0'); }
            el.textContent = p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
        }
        prTick();
        if (window.__prClock) clearInterval(window.__prClock);
        window.__prClock = setInterval(prTick, 1000);

        // Baca lokasi lalu jalankan callback
        function prWithLocation(onOk) {
            if (!navigator.geolocation) {
                if (window.fireGlossySwal) window.fireGlossySwal('Tidak Didukung', 'Browser tidak mendukung lokasi (GPS).', 'error');
                return;
            }
            Swal.fire({
                title: 'Membaca lokasi…',
                html: 'Pastikan GPS aktif & izinkan akses lokasi.',
                allowOutsideClick: false,
                background: 'rgba(255,255,255,0.95)',
                customClass: { popup: 'swal-glossy-popup', title: 'swal-glossy-title' },
                didOpen: function () { Swal.showLoading(); }
            });
            navigator.geolocation.getCurrentPosition(function (pos) {
                Swal.close();
                onOk(pos.coords.latitude, pos.coords.longitude);
            }, function () {
                Swal.close();
                if (window.fireGlossySwal) window.fireGlossySwal('Gagal', 'Tidak bisa membaca lokasi. Aktifkan GPS & izinkan akses lokasi di browser.', 'error');
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
        }

        var root = document.getElementById('presensiRoot');
        if (root && !root.__prBound) {
            root.__prBound = true;
            root.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-presensi-action]');
                if (!btn) return;
                var action = btn.dataset.presensiAction;
                var tipe = btn.dataset.tipe;
                var id = btn.dataset.id;
                prWithLocation(function (lat, lng) {
                    if (action === 'masuk') $wire.call('absenMasuk', tipe, lat, lng);
                    else if (action === 'lembur') $wire.call('absenLembur', lat, lng);
                    else if (action === 'pulang') $wire.call('absenPulang', id, lat, lng);
                });
            });
        }
    </script>
    @endscript
</div>
