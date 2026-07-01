@section('title')
Pengaturan Presensi || PT. Asthana Cipta Mandiri
@stop

@assets
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endassets

<div id="presensiSettingRoot">
    <style>
        #presensiSettingRoot .bi {
            line-height: 1;
            vertical-align: middle;
        }

        .pr-sec-ic {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
        }

        .pr-sec-ic i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }

        #prMap {
            height: 300px;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e6e8f2;
            z-index: 0;
        }

        #prSearchResults .list-group-item-action {
            cursor: pointer;
        }
    </style>

    <div class="container-fluid">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start">
                        <h3 class="gradient-text fw-bold mb-1">Pengaturan Presensi</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Presensi', 'url' => route('admin.presensi.index')], ['name' => 'Pengaturan']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>
                    <a href="{{ route('admin.presensi.index') }}"
                        class="btn btn-light rounded-3 d-flex align-items-center justify-content-center gap-2 px-4">
                        <i class="bi bi-arrow-left"></i> <span>Kembali</span>
                    </a>
                </div>
            </div>
        </div>

        <form wire:submit="save">
            <div class="row g-4">
                {{-- ===== Lokasi Kantor ===== --}}
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="pr-sec-ic"><i class="bi bi-geo-alt-fill"></i></span>
                                <h5 class="fw-bold mb-0">Lokasi Kantor</h5>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Lokasi</label>
                                <input type="text" wire:model="lokasiNama"
                                    class="form-control @error('lokasiNama') is-invalid @enderror"
                                    placeholder="mis. Kantor Pusat">
                                @error('lokasiNama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Pencarian + Peta (dikontrol JS, tidak disentuh Livewire) --}}
                            <label class="form-label fw-semibold">Cari & Tandai Lokasi di Peta</label>
                            <div wire:ignore>
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" id="prSearchInput" class="form-control"
                                        placeholder="Ketik alamat / nama tempat, mis. Malioboro Yogyakarta">
                                    <button type="button" id="prSearchBtn"
                                        class="btn btn-primary d-flex align-items-center justify-content-center gap-1 px-3">
                                        <i class="bi bi-search"></i> <span>Cari</span>
                                    </button>
                                </div>
                                <div id="prSearchResults" class="list-group mb-2"></div>
                                <div id="prMap" data-lat="{{ $lat }}" data-lng="{{ $lng }}"></div>
                                <button type="button" id="prPakaiLokasi"
                                    class="btn btn-outline-primary rounded-3 mt-3 d-flex align-items-center justify-content-center gap-2 px-4">
                                    <i class="bi bi-crosshair"></i> <span>Gunakan Lokasi Saya (GPS)</span>
                                </button>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Latitude</label>
                                    <input type="text" wire:model="lat"
                                        class="form-control @error('lat') is-invalid @enderror" placeholder="-7.797068">
                                    @error('lat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Longitude</label>
                                    <input type="text" wire:model="lng"
                                        class="form-control @error('lng') is-invalid @enderror" placeholder="110.370529">
                                    @error('lng') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            @if ($lat && $lng)
                            <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank" rel="noopener"
                                class="btn btn-link btn-sm mt-2 ps-0 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-box-arrow-up-right"></i> <span>Cek di Google Maps</span>
                            </a>
                            @endif
                            <p class="text-muted small mt-1 mb-0">
                                <i class="bi bi-info-circle"></i> Cari alamat lalu klik hasilnya, atau geser marker /
                                klik peta. Koordinat terisi otomatis.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ===== Aturan ===== --}}
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="pr-sec-ic" style="background:linear-gradient(135deg,#10b981,#059669);">
                                    <i class="bi bi-shield-check"></i>
                                </span>
                                <h5 class="fw-bold mb-0">Aturan Presensi</h5>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Radius Absen Offline (meter)</label>
                                <input type="number" min="10" max="5000" wire:model="radius"
                                    class="form-control @error('radius') is-invalid @enderror">
                                @error('radius') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Jarak maksimal karyawan dari kantor untuk absen offline.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Durasi Kerja Minimal (jam)</label>
                                <input type="number" step="0.5" min="0" max="24" wire:model="minDurasiJam"
                                    class="form-control @error('minDurasiJam') is-invalid @enderror">
                                @error('minDurasiJam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Karyawan baru bisa absen pulang setelah kerja selama ini
                                    (fleksibel).</small>
                            </div>

                            <button type="submit"
                                class="btn btn-primary w-100 rounded-3 mt-2 d-flex align-items-center justify-content-center gap-2"
                                wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save"
                                    class="d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-save"></i> Simpan Pengaturan
                                </span>
                                <span wire:loading wire:target="save" class="d-inline-flex align-items-center gap-2">
                                    <span class="spinner-border spinner-border-sm"></span> Menyimpan…
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @include('livewire.layout.sweetalert')

    @script
    <script>
        (function () {
            var mapEl = document.getElementById('prMap');
            if (!mapEl || mapEl.__init || typeof L === 'undefined') return;
            mapEl.__init = true;

            var hasStart = !!(mapEl.dataset.lat && mapEl.dataset.lng);
            var lat0 = parseFloat(mapEl.dataset.lat) || -6.200000;
            var lng0 = parseFloat(mapEl.dataset.lng) || 106.816666;

            var map = L.map(mapEl).setView([lat0, lng0], hasStart ? 17 : 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, attribution: '© OpenStreetMap'
            }).addTo(map);
            var marker = L.marker([lat0, lng0], { draggable: true }).addTo(map);
            setTimeout(function () { map.invalidateSize(); }, 250);

            function sync(lat, lng) {
                $wire.set('lat', Number(lat).toFixed(7));
                $wire.set('lng', Number(lng).toFixed(7));
            }
            function moveTo(lat, lng, zoom) {
                map.setView([lat, lng], zoom || 17);
                marker.setLatLng([lat, lng]);
                sync(lat, lng);
            }

            marker.on('dragend', function () { var p = marker.getLatLng(); sync(p.lat, p.lng); });
            map.on('click', function (e) { marker.setLatLng(e.latlng); sync(e.latlng.lat, e.latlng.lng); });

            // Pencarian alamat (Nominatim / OpenStreetMap)
            var input = document.getElementById('prSearchInput');
            var box = document.getElementById('prSearchResults');
            function doSearch() {
                var q = input.value.trim();
                if (!q) return;
                box.innerHTML = '<div class="list-group-item text-muted small">Mencari…</div>';
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=6&q=' + encodeURIComponent(q),
                    { headers: { 'Accept-Language': 'id' } })
                    .then(function (r) { return r.json(); })
                    .then(function (list) {
                        box.innerHTML = '';
                        if (!list.length) { box.innerHTML = '<div class="list-group-item text-muted small">Lokasi tidak ditemukan.</div>'; return; }
                        list.forEach(function (item) {
                            var a = document.createElement('button');
                            a.type = 'button';
                            a.className = 'list-group-item list-group-item-action small';
                            a.textContent = item.display_name;
                            a.addEventListener('click', function () {
                                moveTo(parseFloat(item.lat), parseFloat(item.lon), 17);
                                box.innerHTML = '';
                                input.value = item.display_name;
                            });
                            box.appendChild(a);
                        });
                    })
                    .catch(function () { box.innerHTML = '<div class="list-group-item text-danger small">Gagal mencari lokasi.</div>'; });
            }
            document.getElementById('prSearchBtn').addEventListener('click', doSearch);
            input.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); doSearch(); } });

            // GPS
            var gps = document.getElementById('prPakaiLokasi');
            if (gps) gps.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    if (window.fireGlossySwal) window.fireGlossySwal('Tidak Didukung', 'Browser tidak mendukung lokasi.', 'error');
                    return;
                }
                Swal.fire({
                    title: 'Membaca lokasi…', allowOutsideClick: false, background: 'rgba(255,255,255,0.95)',
                    customClass: { popup: 'swal-glossy-popup', title: 'swal-glossy-title' },
                    didOpen: function () { Swal.showLoading(); }
                });
                navigator.geolocation.getCurrentPosition(function (pos) {
                    Swal.close();
                    moveTo(pos.coords.latitude, pos.coords.longitude, 18);
                }, function () {
                    Swal.close();
                    if (window.fireGlossySwal) window.fireGlossySwal('Gagal', 'Tidak bisa membaca lokasi. Aktifkan GPS & izinkan akses.', 'error');
                }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
            });
        })();
    </script>
    @endscript
</div>
