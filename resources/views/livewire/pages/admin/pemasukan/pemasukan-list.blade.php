@section('title')
Pemasukan Lainnya || lemon
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

        /* Thumbnail bukti (form + tabel) */
        .pm-bukti { position: relative; width: 90px; height: 90px; border-radius: 10px; overflow: hidden; border: 1px solid #e6e8f2; background: #f8fafc; flex-shrink: 0; }
        .pm-bukti img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; }
        .pm-bukti .pm-bukti-file { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; text-decoration: none; color: #475569; padding: 4px; }
        .pm-bukti .pm-bukti-file i.bi { font-size: 1.4rem; color: #10b981; }
        .pm-bukti .pm-bukti-file span { font-size: .6rem; text-align: center; line-height: 1.1; word-break: break-all; }
        .pm-bukti-x { position: absolute; top: 2px; right: 2px; width: 20px; height: 20px; border: none; border-radius: 50%; background: rgba(220,38,38,.92); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .7rem; cursor: pointer; line-height: 1; }
        .pm-bukti-x:hover { background: #b91c1c; }
        .pm-bukti-mini { width: 38px; height: 38px; border-radius: 8px; overflow: hidden; border: 1px solid #e6e8f2; display: inline-block; }
        .pm-bukti-mini img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; }

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
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4">
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
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold flex-shrink-0">
                        <span class="pm-stat-ic" style="width:38px;height:38px;background:linear-gradient(135deg,#10b981,#059669);">
                            <i class="bi bi-funnel-fill" style="font-size:1rem;"></i>
                        </span>
                        <span>Periode</span>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <select wire:model.live="bulan" class="form-select rounded-3" style="min-width: 160px;">
                            @foreach ($daftarBulan as $num => $nama)
                            <option value="{{ $num }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="tahun" class="form-select rounded-3" style="min-width: 130px;">
                            @foreach ($daftarTahun as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                            @endforeach
                        </select>
                        <button type="button" wire:click="resetFilter"
                            class="btn btn-light-danger rounded-3 d-inline-flex align-items-center justify-content-center"
                            title="Reset filter">
                            <i class="bi bi-x-circle"></i>
                        </button>
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
                                <th>Bukti</th>
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
                                <td class="text-nowrap">
                                    @php
                                        $bk = $p->bukti ?? [];
                                        $imgs = collect($bk)->filter(fn ($x) => in_array(strtolower(pathinfo($x, PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp','gif']))->map(fn ($x) => \Storage::url($x))->values();
                                        $docs = collect($bk)->reject(fn ($x) => in_array(strtolower(pathinfo($x, PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp','gif']))->values();
                                    @endphp
                                    @if ($imgs->isNotEmpty() || $docs->isNotEmpty())
                                        <div class="d-inline-flex gap-1 align-items-center justify-content-center flex-wrap">
                                            @if ($imgs->isNotEmpty())
                                            <a href="javascript:void(0)" role="button" class="pm-bukti-trigger position-relative d-inline-block {{ $imgs->count() > 1 ? 'me-2' : '' }}" title="Lihat bukti"
                                                data-bukti='@json($imgs)'>
                                                <img src="{{ $imgs->first() }}" alt="bukti"
                                                    style="width:38px; height:38px; object-fit:cover; border-radius:8px; border:1px solid #e6e8f2; cursor:zoom-in; display:block;">
                                                @if ($imgs->count() > 1)<span class="badge bg-primary position-absolute" style="top:-5px; right:-6px; font-size:.5rem; z-index:3;">+{{ $imgs->count() - 1 }}</span>@endif
                                            </a>
                                            @endif
                                            @foreach ($docs as $path)
                                            <a href="{{ \Storage::url($path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border p-1" title="{{ basename($path) }}"><i class="bi bi-file-earmark-text text-success"></i></a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @if (auth()->user()->hasPermission('edit_pemasukan') || auth()->user()->hasPermission('delete_pemasukan'))
                                <td>
                                    <div class="d-inline-flex gap-1">
                                        @if (auth()->user()->hasPermission('edit_pemasukan'))
                                        <button type="button" wire:click="openEdit('{{ $p->id }}')"
                                            class="btn btn-sm btn-warning text-white p-2" title="Update">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete_pemasukan'))
                                        <button type="button" class="btn btn-sm btn-danger p-2 delete-pemasukan-btn"
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
                                <td colspan="8" class="text-center py-5">
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
                        <h5 class="fw-bold mb-0">{{ $editingId ? 'Update' : 'Tambah' }} Pemasukan Lainnya</h5>
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

                        {{-- ===== Bukti: file / gambar / foto langsung ===== --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Bukti <span class="text-muted fw-normal">(opsional — file, gambar, atau foto langsung)</span></label>
                            <div wire:loading.class="opacity-50" wire:target="tempUpload" class="d-flex flex-column flex-sm-row gap-2">
                                {{-- Opsi 1: pilih file / gambar --}}
                                <div class="flex-fill" style="border:1.5px dashed #d6d9e6; border-radius:12px; padding:16px; text-align:center; position:relative; background:#fbfcff;">
                                    <input type="file" wire:model="tempUpload" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple
                                        style="position:absolute; inset:0; opacity:0; cursor:pointer;">
                                    <i class="bi bi-paperclip" style="font-size:1.6rem; color:#10b981;"></i>
                                    <div class="fw-semibold text-dark" style="font-size:.9rem;">Pilih file / gambar</div>
                                    <div class="text-muted" style="font-size:.76rem;">Bisa banyak · gambar/PDF/DOC/XLS · maks 4 MB</div>
                                </div>
                                {{-- Opsi 2: ambil foto langsung dari kamera --}}
                                <div class="flex-fill" x-data="pemasukanCamera()" wire:ignore>
                                    <div @click="open()" class="h-100"
                                        style="border:1.5px dashed #d6d9e6; border-radius:12px; padding:16px; text-align:center; cursor:pointer; background:#fbfcff;">
                                        <i class="bi bi-camera" style="font-size:1.6rem; color:#10b981;"></i>
                                        <div class="fw-semibold text-dark" style="font-size:.9rem;">Ambil foto</div>
                                        <div class="text-muted" style="font-size:.76rem;">Langsung dari kamera</div>
                                    </div>
                                    <template x-teleport="body">
                                        <div x-show="showModal" x-cloak @keydown.escape.window="close()">
                                            <div @click="close()" style="position:fixed; inset:0; z-index:1080; background:rgba(0,0,0,.6);"></div>
                                            <div style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:1090; width:min(94vw,440px); max-height:92vh; overflow:auto; background:#fff; border-radius:14px; padding:16px; box-shadow:0 12px 40px rgba(0,0,0,.3);">
                                                <div class="fw-bold mb-2 d-flex align-items-center gap-2"><i class="bi bi-camera" style="line-height:1;"></i><span>Ambil Foto</span></div>
                                                <template x-if="error"><div class="alert alert-danger py-2 small mb-2" x-text="error"></div></template>
                                                <video x-ref="video" autoplay playsinline muted x-show="!error" style="width:100%; border-radius:10px; background:#000; aspect-ratio:4/3; object-fit:cover;"></video>
                                                <canvas x-ref="canvas" class="d-none"></canvas>
                                                <div class="d-flex gap-2 mt-3">
                                                    <button type="button" class="btn btn-danger flex-fill" @click="close()">Batal</button>
                                                    <button type="button" class="btn btn-success flex-fill d-inline-flex align-items-center justify-content-center gap-1" @click="capture()" x-show="!error">
                                                        <i class="bi bi-camera-fill"></i> <span>Jepret</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div wire:loading wire:target="tempUpload" class="text-primary small mt-1"><span class="spinner-border spinner-border-sm me-1"></span>Mengunggah...</div>
                            @error('tempUpload.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                            @if (count($buktiLama) || count($buktiBaru))
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach ($buktiLama as $i => $path)
                                    @php $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION)); $isImg = in_array($ext, ['jpg','jpeg','png','webp','gif']); @endphp
                                    <div class="pm-bukti">
                                        @if ($isImg)
                                            <a href="{{ \Storage::url($path) }}" target="_blank" rel="noopener"><img src="{{ \Storage::url($path) }}" alt="bukti"></a>
                                        @else
                                            <a href="{{ \Storage::url($path) }}" target="_blank" rel="noopener" class="pm-bukti-file" title="{{ basename($path) }}"><i class="bi bi-file-earmark-text"></i><span>{{ \Illuminate\Support\Str::limit(basename($path), 9) }}</span></a>
                                        @endif
                                        <button type="button" wire:click="removeBuktiLama({{ $i }})" class="pm-bukti-x" title="Hapus"><i class="bi bi-x"></i></button>
                                    </div>
                                @endforeach
                                @foreach ($buktiBaru as $i => $file)
                                    @php $isImg = str_starts_with((string) $file->getMimeType(), 'image/'); @endphp
                                    <div class="pm-bukti">
                                        @if ($isImg)
                                            <img src="{{ $file->temporaryUrl() }}" alt="bukti">
                                        @else
                                            <div class="pm-bukti-file" title="{{ $file->getClientOriginalName() }}"><i class="bi bi-file-earmark-text"></i><span>{{ \Illuminate\Support\Str::limit($file->getClientOriginalName(), 9) }}</span></div>
                                        @endif
                                        <button type="button" wire:click="removeBuktiBaru({{ $i }})" class="pm-bukti-x" title="Hapus"><i class="bi bi-x"></i></button>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-muted small mt-1">Total {{ count($buktiLama) + count($buktiBaru) }} bukti. Klik ✕ untuk menghapus.</div>
                            @endif
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
                background: 'rgba(255, 255, 255, 0.9)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
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

    {{-- Kamera "Ambil foto" — tampilan & perilaku sama seperti Pengeluaran --}}
    <script>
        window.PM_MAX_IMG_BYTES = 4 * 1024 * 1024;

        window.pmShowUploadError = function (title, text) {
            if (typeof Swal === 'undefined') { alert(title + '\n' + text); return; }
            Swal.fire({
                icon: 'error', title: title, text: text,
                background: 'rgba(255, 255, 255, 0.92)', backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold', confirmButton: 'btn-glossy-confirm' },
                buttonsStyling: false, confirmButtonText: 'Mengerti',
            });
        };

        window.pemasukanCamera = function () {
            return {
                showModal: false,
                stream: null,
                error: '',
                async open() {
                    this.error = '';
                    this.showModal = true;
                    await this.$nextTick();
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.error = 'Browser tidak mendukung akses kamera. Pastikan situs dibuka via HTTPS atau localhost.';
                        return;
                    }
                    try {
                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
                        } catch (e) {
                            this.stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                        }
                        this.$refs.video.srcObject = this.stream;
                    } catch (e) {
                        this.error = 'Tidak bisa mengakses kamera: ' + (e.message || e.name) + '. Pastikan izin kamera diberikan dan tidak sedang dipakai aplikasi lain.';
                    }
                },
                capture() {
                    const v = this.$refs.video, c = this.$refs.canvas;
                    if (!v || !v.videoWidth) { this.error = 'Kamera belum siap, coba sebentar lagi.'; return; }
                    c.width = v.videoWidth;
                    c.height = v.videoHeight;
                    c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
                    c.toBlob((blob) => {
                        if (!blob) { this.error = 'Gagal mengambil gambar.'; return; }
                        if (window.PM_MAX_IMG_BYTES && blob.size > window.PM_MAX_IMG_BYTES) {
                            this.close();
                            window.pmShowUploadError('Ukuran foto terlalu besar', 'Maksimal 4 MB. Hasil foto ' + (blob.size / 1024 / 1024).toFixed(1) + ' MB. Coba lagi dengan objek lebih sederhana.');
                            return;
                        }
                        const file = new File([blob], 'kamera-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                        this.$wire.uploadMultiple('tempUpload', [file], () => {}, () => {}, () => {});
                        this.close();
                    }, 'image/jpeg', 0.9);
                },
                close() {
                    if (this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.stream = null; }
                    this.showModal = false;
                },
            };
        };
    </script>

    {{-- Popup lihat bukti (gambar) — glossy, slider bila lebih dari satu (seperti Pengeluaran) --}}
    <script>
        window.pmShowBukti = function (images) {
            if (!images || !images.length) return;
            if (typeof Swal === 'undefined') { window.open(images[0], '_blank'); return; }

            const glossy = {
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0' },
                showConfirmButton: false,
                showCloseButton: true,
                width: 'auto',
                padding: '1rem',
            };

            if (images.length === 1) {
                Swal.fire(Object.assign({
                    html: '<div style="display:flex; align-items:center; justify-content:center; width:100%;"><img src="' + images[0] + '" alt="Bukti pemasukan" style="max-width:88vw; max-height:82vh; width:auto; height:auto; object-fit:contain; border-radius:12px;"></div>',
                }, glossy));
                return;
            }

            let idx = 0;
            const html =
                '<div style="position:relative; max-width:80vw;">' +
                '  <img id="pmBuktiImg" src="' + images[0] + '" style="max-width:100%; max-height:70vh; border-radius:12px; object-fit:contain;">' +
                '  <button type="button" id="pmBuktiPrev" class="btn btn-light rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center" style="position:absolute; top:50%; left:8px; transform:translateY(-50%); width:40px; height:40px;"><i class="bi bi-chevron-left"></i></button>' +
                '  <button type="button" id="pmBuktiNext" class="btn btn-light rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center" style="position:absolute; top:50%; right:8px; transform:translateY(-50%); width:40px; height:40px;"><i class="bi bi-chevron-right"></i></button>' +
                '  <div id="pmBuktiCounter" class="mt-2 fw-semibold text-muted">1 / ' + images.length + '</div>' +
                '</div>';

            Swal.fire(Object.assign({
                html: html,
                didOpen: function () {
                    const img = document.getElementById('pmBuktiImg');
                    const counter = document.getElementById('pmBuktiCounter');
                    const show = function (i) {
                        idx = (i + images.length) % images.length;
                        img.src = images[idx];
                        counter.textContent = (idx + 1) + ' / ' + images.length;
                    };
                    document.getElementById('pmBuktiPrev').addEventListener('click', function () { show(idx - 1); });
                    document.getElementById('pmBuktiNext').addEventListener('click', function () { show(idx + 1); });
                    const onKey = function (e) {
                        if (!document.getElementById('pmBuktiImg')) { document.removeEventListener('keydown', onKey); return; }
                        if (e.key === 'ArrowLeft') show(idx - 1);
                        if (e.key === 'ArrowRight') show(idx + 1);
                    };
                    document.addEventListener('keydown', onKey);
                },
            }, glossy));
        };

        if (!window.__pmBuktiBound) {
            window.__pmBuktiBound = true;
            document.addEventListener('click', function (e) {
                const trigger = e.target.closest && e.target.closest('.pm-bukti-trigger');
                if (!trigger) return;
                e.preventDefault();
                let images = [];
                try { images = JSON.parse(trigger.getAttribute('data-bukti') || '[]'); } catch (_) { images = []; }
                window.pmShowBukti(images);
            });
        }
    </script>
    @endpush
</div>
