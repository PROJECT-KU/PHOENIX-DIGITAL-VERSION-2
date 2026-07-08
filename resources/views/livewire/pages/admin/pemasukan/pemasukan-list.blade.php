@section('title')
Pemasukan Lainnya || PT. Asthana Cipta Mandiri
@stop

<div>
    <style>
        .pm-stat {
            border: none;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(240, 253, 244, 0.9));
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.12);
        }

        .pm-stat-ic {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }

        .pm-stat-ic i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            font-size: 1.3rem;
        }

        .pm-act {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .pm-act i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .pm-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-weight: 600;
        }

        .pm-hint {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: rgba(16, 185, 129, .07);
            border: 1px dashed rgba(16, 185, 129, .3);
            border-radius: 10px;
            padding: .6rem .85rem;
            color: #475569;
            font-size: .82rem;
        }

        .pm-hint i.bi {
            color: #059669;
        }

        .pm-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1080;
            background: rgba(30, 41, 59, .45);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 4vh 1rem;
            overflow-y: auto;
            animation: pmFade .18s ease;
        }

        @keyframes pmFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .pm-modal {
            width: 100%;
            max-width: 480px;
            border-radius: 1.25rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, .98), rgba(248, 249, 255, .98));
            box-shadow: 0 24px 60px rgba(30, 41, 59, .28);
            border: 1px solid rgba(16, 185, 129, .18);
            overflow: hidden;
            animation: pmPop .2s ease;
        }

        @keyframes pmPop {
            from { transform: translateY(-12px) scale(.98); opacity: 0; }
            to { transform: none; opacity: 1; }
        }

        .pm-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.35rem;
            border-bottom: 1px solid #eef0f6;
        }

        .pm-modal-body {
            padding: 1.35rem;
        }

        .pm-modal-foot {
            display: flex;
            gap: .6rem;
            padding: 1rem 1.35rem 1.35rem;
        }

        .pm-rp-field {
            position: relative;
        }

        .pm-rp-prefix {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #a3a9bd;
            font-weight: 600;
            pointer-events: none;
            z-index: 2;
        }

        .pm-rp-input {
            width: 100%;
            border: 1.5px solid #e7e9f2;
            border-radius: 12px;
            background: #fff;
            padding: 12px 14px 12px 40px;
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
            transition: .18s;
        }

        .pm-rp-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 .18rem rgba(16, 185, 129, .14);
        }

        .pm-rp-input::-webkit-outer-spin-button,
        .pm-rp-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .pm-rp-input[type=number] {
            -moz-appearance: textfield;
        }
    </style>

    <div class="container-fluid">
        {{-- ===== Header ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Pemasukan Lainnya</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Pemasukan Lainnya']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari pemasukan...">
                            @if ($search)
                            <span wire:click="$set('search', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_pemasukan'))
                        <button type="button" wire:click="openCreate"
                            class="btn btn-success d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Pemasukan</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Ringkasan ===== --}}
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card pm-stat h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <span class="pm-stat-ic" style="background: linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-graph-up-arrow"></i></span>
                        <div>
                            <div class="text-muted small">Total Pemasukan Lainnya — periode ini</div>
                            <div class="fw-bold fs-4 text-success">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Filter periode ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 px-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold flex-shrink-0">
                        <span class="pm-stat-ic" style="width:38px;height:38px;background:linear-gradient(135deg,#10b981,#059669);">
                            <i class="bi bi-funnel-fill" style="font-size:1rem;"></i>
                        </span>
                        <span>Periode</span>
                    </div>
                    <div class="row g-2 flex-grow-1 w-100 align-items-stretch">
                        <div class="col-6 col-md-5">
                            <select wire:model.live="bulan" class="form-select rounded-3 h-100">
                                @foreach ($daftarBulan as $num => $nama)
                                <option value="{{ $num }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-5">
                            <select wire:model.live="tahun" class="form-select rounded-3 h-100">
                                @foreach ($daftarTahun as $th)
                                <option value="{{ $th }}">{{ $th }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="button" wire:click="resetFilter"
                                class="btn btn-danger rounded-3 w-100 h-100 d-inline-flex align-items-center justify-content-center gap-1"
                                title="Reset filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span class="d-md-none">Reset</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="pm-hint mt-3">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>Pemasukan di luar pemesanan toko (mis. jasa web, pariwisata). Setiap entri otomatis dicatat
                        sebagai <b>income</b> di Cashflow.</span>
                </div>
            </div>
        </div>

        {{-- ===== Tabel ===== --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th style="width:50px;">No</th>
                                <th>Tanggal</th>
                                <th>Kategori / Sumber</th>
                                <th>Keterangan</th>
                                <th>Nominal</th>
                                <th>Diinput</th>
                                @if (auth()->user()->hasPermission('edit_pemasukan') || auth()->user()->hasPermission('delete_pemasukan'))
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pemasukans as $p)
                            <tr style="text-align: center;">
                                <td>{{ $loop->iteration + ($pemasukans->currentPage() - 1) * $pemasukans->perPage() }}</td>
                                <td>{{ $p->tanggal->translatedFormat('d M Y') }}</td>
                                <td>
                                    <span class="badge pm-badge bg-success-subtle text-success border border-success">
                                        <i class="bi bi-tag-fill"></i> {{ $p->kategori ?: '—' }}
                                    </span>
                                </td>
                                <td>{{ $p->deskripsi ?: '—' }}</td>
                                <td class="fw-bold text-success">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                <td>{{ $p->penginput->name ?? '—' }}</td>
                                @if (auth()->user()->hasPermission('edit_pemasukan') || auth()->user()->hasPermission('delete_pemasukan'))
                                <td>
                                    <div class="d-inline-flex gap-1">
                                        @if (auth()->user()->hasPermission('edit_pemasukan'))
                                        <button type="button" wire:click="openEdit('{{ $p->id }}')"
                                            class="btn btn-sm btn-primary pm-act" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete_pemasukan'))
                                        <button type="button" class="btn btn-sm btn-danger pm-act delete-pemasukan-btn"
                                            data-id="{{ $p->id }}" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-cash-coin"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color:#1e293b !important;">Belum Ada Pemasukan Lainnya</h5>
                                        <p class="text-muted mb-0" style="font-size:0.95rem;">Tambahkan pemasukan di luar pemesanan toko untuk periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $pemasukans->links('vendor.pagination') }}</div>
            </div>
        </div>
    </div>

    {{-- ===== Modal Form (Tambah/Edit) ===== --}}
    @if (auth()->user()->hasPermission('create_pemasukan') || auth()->user()->hasPermission('edit_pemasukan'))
    @if ($showForm)
    <div class="pm-modal-overlay" wire:key="pm-form-modal">
        <div class="pm-modal">
            <div class="pm-modal-head">
                <div class="d-flex align-items-center gap-2">
                    <span class="pm-stat-ic" style="width:42px;height:42px;background:linear-gradient(135deg,#10b981,#059669);">
                        <i class="bi bi-cash-coin"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $editingId ? 'Edit' : 'Tambah' }} Pemasukan Lainnya</h5>
                        <small class="text-muted">Pemasukan di luar pemesanan toko.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" wire:click="closeForm" aria-label="Tutup"></button>
            </div>

            <form wire:submit="save">
                <div class="pm-modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input type="date" wire:model="formTanggal"
                                class="form-control rounded-3 @error('formTanggal') is-invalid @enderror">
                            @error('formTanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori / Sumber</label>
                            <input type="text" wire:model="formKategori" list="pmKategoriList"
                                class="form-control rounded-3 @error('formKategori') is-invalid @enderror"
                                placeholder="mis. Jasa Web, Pariwisata">
                            <datalist id="pmKategoriList">
                                <option value="Jasa Web"></option>
                                <option value="Pariwisata"></option>
                                <option value="Jasa Desain"></option>
                                <option value="Konsultasi"></option>
                                <option value="Lainnya"></option>
                            </datalist>
                            @error('formKategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nominal</label>
                            <div class="pm-rp-field">
                                <span class="pm-rp-prefix">Rp</span>
                                <input type="text" inputmode="numeric" wire:model="formNominal"
                                    class="pm-rp-input rp-money @error('formNominal') is-invalid @enderror" placeholder="0">
                            </div>
                            @error('formNominal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Keterangan <span class="text-muted fw-normal">(opsional)</span></label>
                            <textarea wire:model="formDeskripsi" rows="2"
                                class="form-control rounded-3 @error('formDeskripsi') is-invalid @enderror"
                                placeholder="mis. Pembuatan website UMKM"></textarea>
                            @error('formDeskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="pm-modal-foot">
                    <button type="button" wire:click="closeForm"
                        class="btn btn-danger rounded-3 px-4 d-inline-flex align-items-center justify-content-center gap-2"
                        style="height: 48px;">
                        <i class="bi bi-x-lg"></i> <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="btn btn-success rounded-3 px-4 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                        style="height: 48px;">
                        <i class="bi bi-check2-circle me-2 fs-5"></i> <span>Simpan Pemasukan</span>
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
            if (window.__pemasukanListBound) return;
            window.__pemasukanListBound = true;

            // Format ribuan (rupiah) live pada input .rp-money
            function formatRibuan(digits) {
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            document.addEventListener('input', function (e) {
                var el = e.target.closest && e.target.closest('.rp-money');
                if (!el) return;
                var before = el.value.slice(0, el.selectionStart).replace(/\D/g, '').length;
                var formatted = formatRibuan(el.value.replace(/\D/g, ''));
                el.value = formatted;
                var count = 0, i = 0;
                for (; i < formatted.length && count < before; i++) {
                    if (/\d/.test(formatted[i])) count++;
                }
                try { el.setSelectionRange(i, i); } catch (err) {}
            });

            const glossyConfig = {
                background: 'rgba(255, 255, 255, 0.8)',
                backdrop: 'rgba(16, 185, 129, 0.15)',
                customClass: {
                    popup: 'swal-glossy-popup',
                    confirmButton: 'btn-glossy-confirm',
                    cancelButton: 'btn-glossy-cancel',
                    title: 'swal-glossy-title'
                },
                buttonsStyling: false
            };

            document.addEventListener('click', function (event) {
                const btn = event.target.closest('.delete-pemasukan-btn');
                if (!btn) return;
                event.preventDefault();
                const id = btn.getAttribute('data-id');
                Swal.fire({
                    title: 'Hapus pemasukan ini?',
                    text: 'Data pemasukan yang dihapus tidak bisa dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfig
                }).then(function (result) {
                    if (result.isConfirmed) {
                        const comp = btn.closest('[wire\\:id]');
                        if (comp) window.Livewire.find(comp.getAttribute('wire:id')).call('deletePemasukan', id);
                    }
                });
            });

            window.addEventListener('pemasukan-saved', function () {
                Swal.fire({ title: 'Tersimpan!', text: 'Pemasukan berhasil disimpan.', icon: 'success', timer: 2000, showConfirmButton: false, ...glossyConfig });
            });
            window.addEventListener('pemasukan-deleted', function () {
                Swal.fire({ title: 'Terhapus!', text: 'Pemasukan berhasil dihapus.', icon: 'success', timer: 2000, showConfirmButton: false, ...glossyConfig });
            });
            window.addEventListener('pemasukan-error', function (e) {
                Swal.fire({ title: 'Gagal!', text: (e.detail && (e.detail.message || (e.detail[0] && e.detail[0].message))) || 'Terjadi kesalahan.', icon: 'error', timer: 2500, showConfirmButton: false, ...glossyConfig });
            });
            window.addEventListener('pemasukan-deleteError', function (e) {
                Swal.fire({ title: 'Gagal!', text: (e.detail && (e.detail.message || (e.detail[0] && e.detail[0].message))) || 'Terjadi kesalahan.', icon: 'error', timer: 2500, showConfirmButton: false, ...glossyConfig });
            });
        })();
    </script>
    @endpush
</div>
