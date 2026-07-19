@section('title')
Harga Modal Produk || lemon
@stop

<div>
    <style>
        .hm-act { width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px; }
        .hm-act i.bi { line-height:1; }
        .hm-ic { width:44px;height:44px;border-radius:13px;display:inline-flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0; }
        .hm-ic i.bi { display:inline-flex;align-items:center;justify-content:center;line-height:1; }
        .hm-hint { display:flex;align-items:center;gap:.5rem;background:rgba(245,158,11,.07);border:1px dashed rgba(245,158,11,.3);border-radius:10px;padding:.6rem .85rem;color:#475569;font-size:.82rem; }
        .hm-hint i.bi { color:#d97706; }
        .hm-modal-overlay { position:fixed;inset:0;z-index:1080;background:rgba(15,23,42,.42);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);display:flex;align-items:flex-start;justify-content:center;padding:5vh 1rem;overflow-y:auto;animation:hmFade .18s ease; }
        @keyframes hmFade { from{opacity:0}to{opacity:1} }
        .hm-modal { width:100%;max-width:480px;border-radius:28px;background:rgba(255,255,255,.92);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);box-shadow:0 25px 50px -12px rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.5);overflow:hidden;animation:hmPop .2s ease; }
        @keyframes hmPop { from{transform:translateY(-12px) scale(.98);opacity:0}to{transform:none;opacity:1} }
        .hm-modal-head { display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.1rem 1.35rem;border-bottom:1px solid #eef0f6; }
        .hm-modal-body { padding:1.35rem; }
        .hm-modal-foot { display:flex;gap:.6rem;padding:1rem 1.35rem 1.35rem; }
        .hm-rp-field { position:relative; }
        .hm-rp-prefix { position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#a3a9bd;font-weight:600;pointer-events:none;z-index:2; }
        .hm-rp-input { width:100%;border:1.5px solid #e7e9f2;border-radius:12px;background:#fff;padding:12px 14px 12px 40px;font-weight:700;font-size:1.1rem;color:#1e293b;transition:.18s; }
        .hm-rp-input:focus { outline:none;border-color:#7c3aed;box-shadow:0 0 0 .18rem rgba(124,58,237,.12); }

        /* Tombol pemilih produk (buka picker Swal, seperti Pengeluaran) */
        .hm-picker-btn { cursor:pointer; }
        .hm-picker-btn::after { content:"\F282"; font-family:"bootstrap-icons"; float:right; color:#94a3b8; font-size:.8rem; }
        /* Isi picker di dalam Swal */
        .hm-pick-list { max-height:320px; overflow-y:auto; text-align:left; display:flex; flex-direction:column; gap:.4rem; padding:.2rem; }
        .hm-pick-item { display:block; width:100%; text-align:left; border:1px solid #e6e8f2; background:#fff; border-radius:12px; padding:.7rem .9rem; font-weight:600; color:#1e293b; font-size:.92rem; transition:all .15s ease; }
        .hm-pick-item:hover { border-color:#7c3aed; background:linear-gradient(135deg,rgba(124,58,237,.10),rgba(78,70,229,.04)); transform:translateY(-1px); }
        .hm-pick-empty { text-align:center; color:#94a3b8; padding:1.5rem; font-size:.9rem; }
    </style>

    <div class="container-fluid">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Harga Modal Akun</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Harga Modal Akun']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5" placeholder="Cari produk, durasi, harga, atau tanggal (mis. Juni 2026)...">
                            @if ($search)
                            <span wire:click="$set('search', '')" class="position-absolute end-0 top-50 translate-middle-y pe-3" style="cursor:pointer;z-index:10;">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('manage_harga_modal'))
                        <button type="button" wire:click="openCreate" class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i> <span class="ms-2">Tambah Harga</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="hm-hint mb-4">
            <i class="bi bi-info-circle-fill"></i>
            <span>Harga modal akun <b>private</b> yang berlaku sejak <b>tanggal tertentu</b>. Isi <b>satu baris per perubahan
                harga</b> — harga terakhir otomatis dipakai bulan-bulan berikutnya. Menambah harga baru <b>tidak mengubah</b>
                periode sebelumnya. Untuk <b>sharing</b>, modal tetap dari Pengeluaran (kas nyata).</span>
        </div>

        {{-- Tabel --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align:center;">
                                <th style="width:50px;">No</th>
                                <th class="text-start">Produk</th>
                                <th>Durasi</th>
                                <th>Harga Modal / Akun</th>
                                <th>Berlaku Mulai</th>
                                @if (auth()->user()->hasPermission('manage_harga_modal'))
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prices as $p)
                            <tr style="text-align:center;">
                                <td>{{ $loop->iteration + ($prices->currentPage() - 1) * $prices->perPage() }}</td>
                                <td class="fw-bold text-start">
                                    <span class="badge bg-warning-subtle text-warning border border-warning">
                                        <i class="bi bi-key-fill"></i> {{ $p->product->nama_akun ?? '—' }}
                                    </span>
                                </td>
                                <td>{{ $p->durasi_value }} {{ $p->durasi_type }}</td>
                                <td class="fw-bold text-primary">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                                <td>{{ $p->berlaku_mulai->translatedFormat('d M Y') }}</td>
                                @if (auth()->user()->hasPermission('manage_harga_modal'))
                                <td>
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" wire:click="openEdit('{{ $p->id }}')" class="btn btn-sm btn-warning text-white p-2" title="Update">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger p-2 delete-hm-btn" data-id="{{ $p->id }}" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3"><i class="bi bi-tags"></i></div>
                                        <h5 class="fw-bold text-dark mb-1" style="color:#1e293b !important;">Belum Ada Harga Modal</h5>
                                        <p class="text-muted mb-0" style="font-size:0.95rem;">Tambahkan harga modal akun private.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $prices->links('vendor.pagination') }}</div>
            </div>
        </div>
    </div>

    {{-- Modal Form --}}
    @if (auth()->user()->hasPermission('manage_harga_modal'))
    @if ($showForm)
    <div class="hm-modal-overlay" wire:key="hm-form">
        <div class="hm-modal">
            <div class="hm-modal-head">
                <div class="d-flex align-items-center gap-2">
                    <span class="hm-ic" style="width:42px;height:42px;background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-tags-fill"></i></span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $editingId ? 'Update' : 'Tambah' }} Harga Modal</h5>
                        <small class="text-muted">Harga modal akun private (berlaku per tanggal).</small>
                    </div>
                </div>
                <button type="button" class="btn-close" wire:click="closeForm"></button>
            </div>
            <form wire:submit="save">
                <div class="hm-modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Produk (Private)</label>
                            @php $hmSelProduk = $products->firstWhere('id', $formProductId); @endphp
                            <button type="button" onclick="hmProductPicker(this)"
                                class="form-select text-start hm-picker-btn rounded-3 @error('formProductId') is-invalid @enderror" id="formProductId">
                                @if ($hmSelProduk)
                                    <span class="text-dark"><i class="bi bi-key-fill me-1" style="color:#d97706;"></i>{{ $hmSelProduk->nama_akun }}</span>
                                @else
                                    <span class="text-muted">— Pilih produk —</span>
                                @endif
                            </button>
                            @error('formProductId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        @if ($this->formIsJasa)
                        {{-- Produk jasa: modal per satuan kerja (durasi dikunci) --}}
                        <div class="col-12">
                            <div class="alert alert-warning border-0 rounded-3 mb-0 d-flex align-items-center gap-2 py-2 px-3" style="background:#fff7ed;color:#b45309;">
                                <i class="bi bi-info-circle-fill"></i>
                                @if ($this->formIsPerHalaman)
                                <span class="small">Jasa <b>per halaman</b>: modal dihitung <b>per 1 halaman</b>. Sistem otomatis mengalikan jumlah halaman yang dikerjakan (setelah dikurangi halaman yang dikecualikan customer).</span>
                                @else
                                <span class="small">Produk <b>jasa</b>: modal dihitung <b>per 1× pengecekan</b>. Sistem otomatis mengalikan sesuai paket (5×, 10×, dst).</span>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="col-6">
                            <label class="form-label fw-semibold">Durasi</label>
                            <input type="number" min="1" wire:model="formDurasiValue" class="form-control rounded-3 @error('formDurasiValue') is-invalid @enderror">
                            @error('formDurasiValue') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Satuan</label>
                            <select wire:model="formDurasiType" class="form-select rounded-3">
                                <option value="bulan">Bulan</option>
                                <option value="tahun">Tahun</option>
                            </select>
                        </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ $this->formIsJasa ? ($this->formIsPerHalaman ? 'Harga Modal / 1 Halaman' : 'Harga Modal / 1× Pengecekan') : 'Harga Modal / Akun' }}</label>
                            <div class="hm-rp-field">
                                <span class="hm-rp-prefix">Rp</span>
                                <input type="text" inputmode="numeric" wire:model="formHarga" class="hm-rp-input rp-money @error('formHarga') is-invalid @enderror" placeholder="0">
                            </div>
                            @error('formHarga') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Berlaku Mulai</label>
                            <input type="date" wire:model="formBerlakuMulai" class="form-control rounded-3 @error('formBerlakuMulai') is-invalid @enderror">
                            @error('formBerlakuMulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Order sejak tanggal ini pakai harga ini.</small>
                        </div>
                    </div>
                </div>
                <div class="hm-modal-foot">
                    <button type="button" wire:click="closeForm" class="btn btn-danger rounded-3 px-4 d-inline-flex align-items-center justify-content-center gap-2" style="height:48px;">
                        <i class="bi bi-x-lg"></i> <span>Batal</span>
                    </button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 flex-grow-1 d-inline-flex align-items-center justify-content-center" style="height:48px;">
                        <i class="bi bi-check2-circle me-2 fs-5"></i> <span>Simpan Harga</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endif

    @include('livewire.layout.sweetalert')

    @push('scripts')
    <script>
        (function () {
            // Data produk untuk picker (diperbarui tiap render, di luar guard)
            window.__hmProducts = @json($products->map(fn ($p) => ['id' => (string) $p->id, 'name' => $p->nama_akun])->values());

            if (window.__hmBound) return;
            window.__hmBound = true;

            // Picker produk glossy — pola select2 Swal seperti Pengeluaran
            window.hmProductPicker = function (btn) {
                if (typeof Swal === 'undefined') return;
                const el = btn.closest('[wire\\:id]'); if (!el) return;
                const cid = el.getAttribute('wire:id');
                const items = window.__hmProducts || [];
                const esc = (s) => String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
                const rows = items.length
                    ? items.map(it => '<button type="button" class="hm-pick-item" data-id="' + esc(it.id) + '" data-search="' + esc((it.name || '').toLowerCase()) + '">' + esc(it.name) + '</button>').join('')
                    : '<div class="hm-pick-empty">Tidak ada produk private</div>';
                Swal.fire({
                    title: 'Pilih Produk (Private)',
                    html: '<input id="hmPickSearch" class="form-control mb-2" placeholder="Ketik untuk mencari...">' +
                          '<div id="hmPickList" class="hm-pick-list">' + rows + '</div>',
                    background: 'rgba(255, 255, 255, 0.92)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                    buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem',
                    didOpen: () => {
                        const search = document.getElementById('hmPickSearch');
                        const listEl = document.getElementById('hmPickList');
                        if (search) {
                            search.addEventListener('input', () => {
                                const q = search.value.toLowerCase();
                                listEl.querySelectorAll('.hm-pick-item').forEach(b => { b.style.display = b.dataset.search.includes(q) ? '' : 'none'; });
                            });
                            setTimeout(() => search.focus(), 100);
                        }
                        listEl.querySelectorAll('.hm-pick-item').forEach(b => {
                            b.addEventListener('click', () => {
                                if (window.Livewire) window.Livewire.find(cid).set('formProductId', b.dataset.id);
                                Swal.close();
                            });
                        });
                    }
                });
            };

            function fmt(d){ return d.replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }
            document.addEventListener('input', function (e) {
                var el = e.target.closest && e.target.closest('.rp-money');
                if (!el) return;
                var before = el.value.slice(0, el.selectionStart).replace(/\D/g,'').length;
                var f = fmt(el.value.replace(/\D/g,'')); el.value = f;
                var c=0,i=0; for(; i<f.length && c<before; i++){ if(/\d/.test(f[i])) c++; }
                try { el.setSelectionRange(i,i); } catch(x){}
            });
            const g = { background:'rgba(255,255,255,0.8)', backdrop:'rgba(139,92,246,0.15)', customClass:{popup:'swal-glossy-popup',confirmButton:'btn-glossy-confirm',cancelButton:'btn-glossy-cancel',title:'swal-glossy-title'}, buttonsStyling:false };
            document.addEventListener('click', function (e) {
                const b = e.target.closest('.delete-hm-btn'); if(!b) return; e.preventDefault();
                Swal.fire({ title:'Hapus harga ini?', text:'Data harga modal akan dihapus.', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus!', cancelButtonText:'Batal', ...g }).then(function(r){
                    if(r.isConfirmed){ const c=b.closest('[wire\\:id]'); if(c) window.Livewire.find(c.getAttribute('wire:id')).call('deleteHarga', b.getAttribute('data-id')); }
                });
            });
            window.addEventListener('hm-saved', function(){ Swal.fire({title:'Tersimpan!',text:'Harga modal berhasil disimpan.',icon:'success',timer:2000,showConfirmButton:false,...g}); });
            window.addEventListener('hm-deleted', function(){ Swal.fire({title:'Terhapus!',text:'Harga modal berhasil dihapus.',icon:'success',timer:2000,showConfirmButton:false,...g}); });
            window.addEventListener('hm-error', function(e){ Swal.fire({title:'Gagal!',text:(e.detail&&(e.detail.message||(e.detail[0]&&e.detail[0].message)))||'Kesalahan.',icon:'error',timer:2400,showConfirmButton:false,...g}); });
            window.addEventListener('hm-deleteError', function(e){ Swal.fire({title:'Gagal!',text:(e.detail&&(e.detail.message||(e.detail[0]&&e.detail[0].message)))||'Kesalahan.',icon:'error',timer:2400,showConfirmButton:false,...g}); });
        })();
    </script>
    @endpush
</div>
