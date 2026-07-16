
@section('title')
Data Testimoni || lemon
@stop
<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Testimoni</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Testimoni']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>

                            <input wire:model.live.debounce.300ms="searchTestimoni" type="text"
                                class="form-control ps-5 pe-5" placeholder="Cari testimoni...">

                            @if ($searchTestimoni)
                            <span wire:click="$set('searchTestimoni', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_testimoni'))
                        <a wire:navigate href="{{ route('admin.testimoni.create') }}"
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Data</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th style="width: 50px;">No</th>
                                <th>Nama</th>
                                <th>Peran</th>
                                <th>Foto</th>
                                <th class="text-center">Rating</th>
                                <th>Pesan</th>
                                <th class="text-center">Status</th>
                                @if (auth()->user()->hasAnyPermission(['edit_testimoni', 'delete_testimoni']))
                                <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($Testimoni as $item)
                            <tr style="text-align: center;">
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold text-start">
                                    {{ $item->nama }}
                                    @if ($item->source === 'customer')
                                        <br>
                                        <span class="badge bg-info text-white mt-1" title="Dikirim langsung oleh pelanggan">
                                            <i class="bi bi-person-heart"></i> Dari Pelanggan
                                        </span>
                                    @endif

                                    {{-- Bekal admin menilai keaslian: sudah belanja berapa kali & sudah
                                         member atau belum, supaya penulis yang belum pernah belanja
                                         langsung ketahuan.
                                         Syaratnya ADA TAUTAN PELANGGAN atau kiriman pelanggan — bukan
                                         source-nya. Testimoni yang diinput admin dari WhatsApp tetap
                                         punya tautan pelanggan (source='admin'), dan dulu lencananya
                                         tidak muncul sama sekali. --}}
                                    @if ($item->customer_id || $item->source === 'customer')
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @if ($item->customer)
                                                <span class="badge bg-success-subtle text-success border border-success rounded-pill d-inline-flex align-items-center gap-1"
                                                    style="font-size:.66rem; line-height:1;" title="Nomor WhatsApp cocok dgn pelanggan terdaftar">
                                                    <i class="bi bi-bag-check-fill"></i>Sudah belanja {{ $item->customer->belanja_selesai_count ?? 0 }}&times;
                                                </span>
                                                @if ($item->customer->status_member === 'active')
                                                    <span class="badge bg-primary-subtle text-primary border border-primary rounded-pill d-inline-flex align-items-center gap-1"
                                                        style="font-size:.66rem; line-height:1;">
                                                        <i class="bi bi-star-fill"></i>Sudah member
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill d-inline-flex align-items-center gap-1"
                                                        style="font-size:.66rem; line-height:1;" title="Akan otomatis jadi member begitu testimoni ini diaktifkan">
                                                        <i class="bi bi-hourglass-split"></i>Belum member
                                                    </span>
                                                @endif
                                            @elseif ($item->no_hp)
                                                <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill d-inline-flex align-items-center gap-1"
                                                    style="font-size:.66rem; line-height:1;" title="Nomor tidak cocok dgn pelanggan mana pun, atau pesanannya belum ada yang Selesai">
                                                    <i class="bi bi-x-circle-fill"></i>Belum pernah belanja
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill d-inline-flex align-items-center gap-1"
                                                    style="font-size:.66rem; line-height:1;" title="Testimoni lama — dikirim sebelum nomor WhatsApp diwajibkan">
                                                    <i class="bi bi-question-circle"></i>Tanpa nomor (data lama)
                                                </span>
                                            @endif
                                        </div>

                                        @if ($item->customer)
                                            {{-- Info netral, BUKAN peringatan: orang lazim mengetik nama
                                                 panggilan ("Pak Berto" vs "berto"), jadi ketidaksamaan
                                                 nama itu wajar & bukan tanda kecurangan. Admin cukup
                                                 diberi tahu pemilik nomornya, biar dia menilai sendiri. --}}
                                            <div class="text-muted fw-normal mt-1" style="font-size:.68rem; line-height:1.35;">
                                                <i class="bi bi-person-vcard me-1" style="vertical-align:-0.125em;"></i>Pemilik nomor: <b>{{ $item->customer->nama }}</b>
                                                @if (mb_strtolower(trim($item->nama)) !== mb_strtolower(trim($item->customer->nama)))
                                                    <div class="mt-1" style="opacity:.85;">
                                                        <i class="bi bi-info-circle me-1" style="vertical-align:-0.125em;"></i>nama ketikan berbeda
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $item->peran ?: '-' }}</td>
                                <td>
                                    @if ($item->foto && \Storage::disk('public')->exists('img/testimoni/' . $item->foto))
                                    <img src="{{ asset('storage/img/testimoni/' . $item->foto) }}"
                                        class="rounded-circle shadow-sm"
                                        style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                                        onclick="showGlossyPreview('{{ asset('storage/img/testimoni/' . $item->foto) }}')">
                                    @else
                                    <span class="rounded-circle bg-light text-primary d-inline-block text-center align-middle shadow-sm"
                                        style="width: 50px; height: 50px;"><i class="bi bi-person-fill" style="font-size: 1.5rem; line-height: 50px;"></i></span>
                                    @endif
                                </td>
                                <td class="text-center text-warning text-nowrap">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= (int) $item->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </td>
                                <td class="text-truncate" style="max-width: 200px;">{{ $item->pesan }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                @if (auth()->user()->hasAnyPermission(['edit_testimoni', 'delete_testimoni']))
                                <td class="text-center text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_testimoni'))
                                    <button type="button"
                                        class="btn btn-sm p-2 {{ $item->status === 'active' ? 'btn-secondary' : 'btn-success' }}"
                                        title="{{ $item->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        wire:click="toggleStatus('{{ $item->id }}')">
                                        <i class="bi {{ $item->status === 'active' ? 'bi-eye-slash' : 'bi-check-circle' }}"></i>
                                    </button>
                                    <a wire:navigate href="{{ route('admin.testimoni.edit', $item) }}"
                                        class="btn btn-sm btn-warning text-white p-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_testimoni'))
                                    <button type="button" class="btn btn-sm btn-danger delete-testimoni-btn p-2"
                                        data-id="{{ $item->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-chat-quote"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Testimoni
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Silakan klik tombol tambah data untuk memasukkan testimoni baru.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $Testimoni->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== SWEET ALERT DELETE ==================-->
<script>
    const glossyConfigTestimoni = {
        background: 'rgba(255, 255, 255, 0.8)',
        backdrop: 'rgba(139, 92, 246, 0.15)',
        customClass: {
            popup: 'swal-glossy-popup',
            confirmButton: 'btn-glossy-confirm',
            cancelButton: 'btn-glossy-cancel',
            title: 'swal-glossy-title'
        },
        buttonsStyling: false
    };

    document.addEventListener('livewire:navigated', function() {
        document.body.addEventListener('click', function(event) {
            const button = event.target.closest('.delete-testimoni-btn');

            if (button) {
                event.preventDefault();
                const id = button.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin hapus data?',
                    text: "Data testimoni ini tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigTestimoni
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            const livewireComponentId = component.getAttribute('wire:id');
                            Livewire.find(livewireComponentId).call('deleteTestimoni', id);
                        }
                    }
                });
            }
        });
    });

    window.addEventListener('testimoni-status', (e) => {
        Swal.fire({
            title: e.detail.active ? 'Diaktifkan!' : 'Dinonaktifkan!',
            text: e.detail.active ? 'Testimoni sekarang tampil di homepage.' : 'Testimoni disembunyikan dari homepage.',
            icon: 'success',
            timer: 2200,
            showConfirmButton: false,
            ...glossyConfigTestimoni
        });
    });

    window.addEventListener('testimoni-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data testimoni berhasil dihapus.',
            icon: 'success',
            timer: 2500,
            showConfirmButton: false,
            ...glossyConfigTestimoni
        });
    });

    window.addEventListener('testimoni-deleteError', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: e.detail.message,
            icon: 'error',
            timer: 2500,
            showConfirmButton: false,
            ...glossyConfigTestimoni
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->

<!--================== SWEET ALERT PREVIEW IMAGE ==================-->
<script>
    function showGlossyPreview(imageUrl) {
        Swal.fire({
            imageUrl: imageUrl,
            imageAlt: 'Preview Gambar',
            showConfirmButton: false,
            showCloseButton: true,
            width: 'auto',
            padding: '1em',
            background: 'rgba(255, 255, 255, 0.65)',
            backdrop: 'rgba(0, 0, 0, 0.4)',
            didOpen: () => {
                const popup = Swal.getPopup();
                popup.style.backdropFilter = 'blur(15px)';
                popup.style.WebkitBackdropFilter = 'blur(15px)';
                popup.style.border = '1px solid rgba(255, 255, 255, 0.4)';
                popup.style.borderRadius = '20px';
                popup.style.boxShadow = '0 8px 32px 0 rgba(0, 0, 0, 0.2)';
                const swalImage = Swal.getImage();
                swalImage.style.borderRadius = '12px';
                swalImage.style.maxHeight = '80vh';
                swalImage.style.objectFit = 'contain';
            }
        });
    }
</script>
<!--================== END SWEET ALERT PREVIEW IMAGE ==================-->
