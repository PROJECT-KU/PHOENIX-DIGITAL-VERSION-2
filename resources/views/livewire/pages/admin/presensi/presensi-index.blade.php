@section('title')
Presensi || lemon
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
            justify-content: center;
            line-height: 1;
        }

        .pr-ic.sm {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            font-size: 1rem;
        }

        .pr-sec-title {
            font-weight: 700;
            color: #1e293b;
            line-height: 1.1;
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
            align-items: center;
            gap: .65rem;
            padding: .6rem 0;
            border-bottom: 1px dashed #eef0f6;
            font-size: .88rem;
            color: #475569;
        }

        .pr-info-row:last-child {
            border-bottom: none;
        }

        .pr-info-ic {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(108, 99, 255, .10);
            color: #6c63ff;
            font-size: 1rem;
        }

        .pr-info-ic i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
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

        /* Titik "live" berdenyut untuk status Berjalan. */
        .pr-live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #0ea5e9;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(14, 165, 233, .55);
            animation: pr-pulse 1.4s infinite;
        }

        @keyframes pr-pulse {
            0% { box-shadow: 0 0 0 0 rgba(14, 165, 233, .55); }
            70% { box-shadow: 0 0 0 6px rgba(14, 165, 233, 0); }
            100% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0); }
        }

        .pr-live {
            padding: .55rem .9rem;
            border-radius: .75rem;
            background: linear-gradient(135deg, rgba(108, 99, 255, .10), rgba(78, 70, 229, .10));
            border: 1px solid rgba(108, 99, 255, .20);
            color: #4e46e5;
            font-size: .9rem;
        }

        .pr-live .pr-live-val {
            font-variant-numeric: tabular-nums;
        }

        .pr-live i.bi {
            line-height: 1;
        }

        .pr-notice {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .9rem 1rem;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(108, 99, 255, .09), rgba(14, 165, 233, .09));
            border: 1px solid rgba(108, 99, 255, .18);
        }

        .pr-notice-ic {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.15rem;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 6px 14px rgba(108, 99, 255, .28);
        }

        .pr-notice-ic i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .pr-notice-title {
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .pr-notice-sub {
            font-size: .82rem;
            color: #64748b;
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
                        <span class="pr-ic sm"><i class="bi bi-clipboard-check"></i></span>
                        <h5 class="pr-sec-title mb-0">Presensi Hari Ini</h5>
                    </div>

                    {{-- Status kehadiran --}}
                    @php $h = $this->todayHadir; @endphp
                    @if (! $h)
                    <div class="pr-notice mb-3">
                        <span class="pr-notice-ic"><i class="bi bi-fingerprint"></i></span>
                        <div>
                            <div class="pr-notice-title">Kamu belum absen masuk hari ini</div>
                            <div class="pr-notice-sub">Pilih jenis kehadiran di bawah untuk mulai presensi.</div>
                        </div>
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
                    <div class="pr-live d-flex align-items-center gap-2 mb-3" data-pr-since="{{ $h->waktu_masuk->timestamp }}">
                        <i class="bi bi-hourglass-split"></i>
                        <span>Sudah bekerja <b class="pr-live-val">…</b></span>
                    </div>
                    <button type="button" class="pr-btn pr-btn-out w-100" data-presensi-action="pulang"
                        data-id="{{ $h->id }}" data-tipe="{{ $h->tipe }}">
                        <i class="bi bi-box-arrow-right"></i> Absen Pulang
                    </button>
                    <small class="text-muted d-block mt-2">Bisa pulang minimal setelah kerja {{ rtrim(rtrim(number_format($minDurasiJam, 1, '.', ''), '0'), '.') }}
                        jam.@if ($h->tipe === 'hadir_offline') Pulang offline wajib dalam radius kantor.@endif</small>
                    @else
                    <div class="alert alert-success bg-success-subtle border-success rounded-3 mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success d-inline-flex align-items-center" style="line-height: 1;"></i>
                        <span class="d-inline-flex align-items-center">Presensi hari ini selesai. Terima kasih! 🙌</span>
                    </div>
                    @endif
                    @endif

                    <hr class="my-3">

                    {{-- Lembur --}}
                    @php $l = $this->todayLembur; @endphp
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="pr-ic sm amber"><i class="bi bi-moon-stars"></i></span>
                        <span class="pr-sec-title">Lembur</span>
                    </div>
                    @if ($l)
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="pr-status-pill" style="background:rgba(245,158,11,.14); color:#b45309;">
                            <i class="bi bi-hourglass-split"></i> Lembur berjalan sejak {{ $l->waktu_masuk->format('H:i') }}
                        </span>
                    </div>
                    <div class="pr-live d-flex align-items-center gap-2 mb-2" data-pr-since="{{ $l->waktu_masuk->timestamp }}">
                        <i class="bi bi-hourglass-split"></i>
                        <span>Sudah lembur <b class="pr-live-val">…</b></span>
                    </div>
                    <button type="button" class="pr-btn pr-btn-out w-100" data-presensi-action="pulang"
                        data-id="{{ $l->id }}" data-tipe="lembur">
                        <i class="bi bi-box-arrow-right"></i> Selesai Lembur
                    </button>
                    @else
                    <button type="button" class="pr-btn pr-btn-lembur w-100" data-presensi-action="lembur"
                        data-tipe="lembur">
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
                        <span class="pr-ic sm"><i class="bi bi-geo-alt"></i></span>
                        <h5 class="pr-sec-title mb-0">Aturan & Lokasi</h5>
                    </div>
                    <div class="pr-info-row">
                        <span class="pr-info-ic"><i class="bi bi-building"></i></span>
                        <div><b>{{ $officeNama ?: 'Kantor' }}</b>
                            @if ($officeLat === null)
                            <span class="badge bg-danger-subtle text-danger border border-danger ms-1">Belum diatur</span>
                            @else
                            <div class="text-muted small">{{ number_format($officeLat, 6) }},
                                {{ number_format($officeLng, 6) }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="pr-info-row">
                        <span class="pr-info-ic"><i class="bi bi-bullseye"></i></span>
                        <div>Radius maksimal absen <b>offline</b>: <b>{{ $radius }} meter</b></div>
                    </div>
                    <div class="pr-info-row">
                        <span class="pr-info-ic"><i class="bi bi-stopwatch"></i></span>
                        <div>Durasi kerja minimal sebelum pulang:
                            <b>{{ rtrim(rtrim(number_format($minDurasiJam, 1, '.', ''), '0'), '.') }} jam</b></div>
                    </div>
                    <div class="pr-info-row">
                        <span class="pr-info-ic"><i class="bi bi-globe2"></i></span>
                        <div>Hadir <b>online</b> & <b>lembur</b>: bebas lokasi, titik lokasi tetap direkam.</div>
                    </div>
                    @if (auth()->user()->hasPermission('manage_presensi_setting'))
                    <a href="{{ route('admin.presensi.pengaturan') }}"
                        class="btn btn-sm btn-outline-primary w-100 mt-2 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-sliders"></i> Ubah Pengaturan Presensi
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- ===== Riwayat ===== -->
        <div class="pr-card p-4 mt-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="pr-ic sm"><i class="bi bi-clock-history"></i></span>
                <h5 class="pr-sec-title mb-0">Riwayat Presensi Saya</h5>
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
                                <span class="badge pr-badge bg-info-subtle text-info border border-info">
                                    <span class="pr-live-dot"></span> Berjalan
                                </span>
                                @else
                                <span class="badge pr-badge bg-success-subtle text-success border border-success">
                                    <i class="bi bi-check-circle-fill"></i> Selesai
                                </span>
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
            <div class="mt-4">{{ $history->links('vendor.pagination') }}</div>
        </div>
    </div>

    @include('livewire.layout.sweetalert')

    @push('scripts')
    <script>
        (function () {
            if (window.__presensiIdxBound) return;
            window.__presensiIdxBound = true;

            // Jam berjalan + durasi kerja/lembur berjalan
            function prTick() {
                function p(n) { return String(n).padStart(2, '0'); }
                var el = document.getElementById('prClock');
                if (el) {
                    var d = new Date();
                    el.textContent = p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
                }
                var nowSec = Math.floor(Date.now() / 1000);
                document.querySelectorAll('[data-pr-since]').forEach(function (row) {
                    var since = parseInt(row.getAttribute('data-pr-since'), 10);
                    var val = row.querySelector('.pr-live-val');
                    if (!since || !val) return;
                    var diff = Math.max(0, nowSec - since);
                    var jam = Math.floor(diff / 3600);
                    var menit = Math.floor((diff % 3600) / 60);
                    var detik = diff % 60;
                    val.textContent = jam + ' jam ' + menit + ' menit ' + p(detik) + ' detik';
                });
            }
            prTick();
            if (window.__prClock) clearInterval(window.__prClock);
            window.__prClock = setInterval(prTick, 1000);

            // Baca lokasi GPS akurat (wajib untuk semua jenis presensi).
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
                }, function (err) {
                    Swal.close();
                    var msg = (err && err.code === 1) ? 'Izin lokasi ditolak. Izinkan akses lokasi untuk situs ini di browser.'
                        : (err && err.code === 2) ? 'Lokasi tidak dapat ditentukan. Nyalakan WiFi & aktifkan Layanan Lokasi (macOS) untuk browser Anda, lalu coba lagi.'
                        : (err && err.code === 3) ? 'Waktu membaca lokasi habis. Coba lagi.'
                        : 'Tidak bisa membaca lokasi. Nyalakan GPS/WiFi & izinkan akses lokasi.';
                    if (window.fireGlossySwal) window.fireGlossySwal('Gagal', msg, 'error');
                }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 });
            }

            document.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-presensi-action]');
                if (!btn) return;
                e.preventDefault();
                var action = btn.dataset.presensiAction;
                var tipe = btn.dataset.tipe;
                var id = btn.dataset.id;
                var cidEl = btn.closest('[wire\\:id]');
                var cid = cidEl ? cidEl.getAttribute('wire:id') : null;
                if (!cid || !window.Livewire) return;

                function doCall(lat, lng) {
                    var comp = window.Livewire.find(cid);
                    if (!comp) return;
                    if (action === 'masuk') comp.call('absenMasuk', tipe, lat, lng);
                    else if (action === 'lembur') comp.call('absenLembur', lat, lng);
                    else if (action === 'pulang') comp.call('absenPulang', id, lat, lng);
                }

                // Semua jenis presensi wajib GPS akurat.
                prWithLocation(function (lat, lng) { doCall(lat, lng); });
            });
        })();
    </script>
    @endpush
</div>
