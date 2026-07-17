<div>
    <!-- Flash Messages -->
    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form wire:submit="save">
        <div class="row">
            <!-- Nama Pengembalian (ambil dari user) -->
            <div class="col-md-6 mb-3">
                <label for="user_id" class="form-label">
                    Nama Pengembalian <span class="text-danger">*</span>
                </label>
                @php $pgbSelUser = $users->firstWhere('id', $user_id); @endphp
                <button type="button" onclick="pengembalianUserPicker(this)" id="user_id"
                    class="form-select text-start pgb-picker-btn @error('user_id') is-invalid @enderror">
                    @if ($pgbSelUser)
                        <span class="text-dark"><i class="bi bi-person-fill me-1" style="color:#7c3aed; vertical-align:-0.125em;"></i>{{ $pgbSelUser->name }}</span>
                    @else
                        <span class="text-muted">-- Pilih Pengembali --</span>
                    @endif
                </button>
                @error('user_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                {{-- <label for="user_id" class="form-label">
                            Nama Pengembalian <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            id="user_id"
                            class="form-control"
                            value="{{ auth()->user()->name }}"
                readonly> --}}
            </div>

            <!-- Tanggal Pengembalian -->
            <div class="col-md-6 mb-3">
                <label for="tanggal_pengembalian" class="form-label">
                    Tanggal Pengembalian <span class="text-danger">*</span>
                </label>
                <input type="date"
                    id="tanggal_pengembalian"
                    wire:model="tanggal_pengembalian"
                    class="form-control @error('tanggal_pengembalian') is-invalid @enderror">
                @error('tanggal_pengembalian')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Nominal Pengembalian -->
            <div class="col-md-12 mb-3"
                x-data="{
                            formatRupiah(v) {
                                if (!v) return '';
                                let number_string = v.toString().replace(/[^,\d]/g, '');
                                let split = number_string.split(',');
                                let sisa = split[0].length % 3;
                                let rupiah = split[0].substr(0, sisa);
                                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                                if (ribuan) {
                                    let separator = sisa ? '.' : '';
                                    rupiah += separator + ribuan.join('.');
                                }
                                rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                                return rupiah;
                            },
                            parseRaw(v) {
                                return (v || '').toString().replace(/[^0-9]/g, '');
                            }
                        }"
                x-init="$nextTick(() => {
                            // Inisialisasi tampilan dari nilai Livewire
                            $refs.display.value = formatRupiah($wire.nominal);
                        })">
                <label for="nominal_display" class="form-label">
                    Nominal Pengembalian <span class="text-danger">*</span>
                </label>

                <!-- Input tampil ke user (format Rupiah) -->
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                        style="pointer-events: none; z-index: 5;">
                        Rp
                    </span>
                    <input type="text"
                        id="nominal_display"
                        x-ref="display"
                        x-on:focus="$event.target.select()"
                        x-on:input="
                                let raw = parseRaw($event.target.value);
                                $event.target.value = formatRupiah(raw);
                                $wire.set('nominal', raw);
                        "
                        class="form-control @error('nominal') is-invalid @enderror"
                        style="padding-left: 45px;"
                        placeholder="0">
                </div>
                @error('nominal')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea id="deskripsi"
                wire:model="deskripsi"
                class="form-control @error('deskripsi') is-invalid @enderror"
                rows="4"
                placeholder="Masukkan deskripsi pengembalian..."></textarea>
            @error('deskripsi')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Buttons -->
        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                <span>{{ $this->mode === 'create' ? 'Simpan Data' : 'Update Data' }}</span>
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
    .pgb-picker-btn { cursor:pointer; }
    .pgb-picker-btn::after { content:"\F282"; font-family:"bootstrap-icons"; float:right; color:#94a3b8; font-size:.8rem; }
    .pgb-pick-list { max-height:320px; overflow-y:auto; text-align:left; display:flex; flex-direction:column; gap:.4rem; padding:.2rem; }
    .pgb-pick-item { display:block; width:100%; text-align:left; border:1px solid #e6e8f2; background:#fff; border-radius:12px; padding:.7rem .9rem; font-weight:600; color:#1e293b; font-size:.92rem; transition:all .15s ease; }
    .pgb-pick-item:hover { border-color:#7c3aed; background:linear-gradient(135deg,rgba(124,58,237,.10),rgba(78,70,229,.04)); transform:translateY(-1px); }
    .pgb-pick-empty { text-align:center; color:#94a3b8; padding:1.5rem; font-size:.9rem; }
</style>
@endpush

@push('scripts')
<script>
    window.__pgbUsers = @json($users->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])->values());

    if (!window.__pgbUserPickerBound) {
        window.__pgbUserPickerBound = true;
        window.pengembalianUserPicker = function (btn) {
            if (typeof Swal === 'undefined') return;
            const el = btn.closest('[wire\\:id]'); if (!el) return;
            const cid = el.getAttribute('wire:id');
            const items = window.__pgbUsers || [];
            const esc = (s) => String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
            const rows = items.length
                ? items.map(it => '<button type="button" class="pgb-pick-item" data-id="' + esc(it.id) + '" data-search="' + esc((it.name || '').toLowerCase()) + '">' + esc(it.name) + '</button>').join('')
                : '<div class="pgb-pick-empty">Tidak ada pengguna</div>';
            Swal.fire({
                title: 'Pilih Pengembali',
                html: '<input id="pgbPickSearch" class="form-control mb-2" placeholder="Ketik untuk mencari...">' +
                      '<div id="pgbPickList" class="pgb-pick-list">' + rows + '</div>',
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem',
                didOpen: () => {
                    const search = document.getElementById('pgbPickSearch');
                    const listEl = document.getElementById('pgbPickList');
                    if (search) {
                        search.addEventListener('input', () => {
                            const q = search.value.toLowerCase();
                            listEl.querySelectorAll('.pgb-pick-item').forEach(b => { b.style.display = b.dataset.search.includes(q) ? '' : 'none'; });
                        });
                        setTimeout(() => search.focus(), 100);
                    }
                    listEl.querySelectorAll('.pgb-pick-item').forEach(b => {
                        b.addEventListener('click', () => {
                            if (window.Livewire) window.Livewire.find(cid).set('user_id', b.dataset.id);
                            Swal.close();
                        });
                    });
                }
            });
        };
    }
</script>
@endpush