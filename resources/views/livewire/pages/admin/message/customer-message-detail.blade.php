@section('title')
Detail Pesan Pelanggan || lemon
@stop
<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4 fixed-header-card">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">

                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Detail Pesan Pelanggan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Pesan Pelanggan', 'url' => route('admin.customer-message.index')],
                            ['name' => 'Detail']
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden"
        style="background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.5);">

        <div style="height: 6px; background: linear-gradient(90deg, #6c63ff, #3b82f6);"></div>

        <div class="card-body p-4 p-md-5">

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="p-3 rounded-4 border h-100" style="background: rgba(108, 99, 255, 0.05); border-color: rgba(108, 99, 255, 0.2) !important;">
                        <label class="text-uppercase small fw-black text-primary mb-1 d-block opacity-75">ID Tiket</label>
                        <p class="fs-4 fw-black text-primary mb-0 font-monospace">{{ $message->ticket }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-4 border h-100" style="background: rgba(255, 255, 255, 0.4);">
                        <label class="text-uppercase small fw-black text-muted mb-2 d-block opacity-75">Status Tiket</label>

                        <select wire:model.live="status"
                            class="form-select form-select-sm rounded-pill border-0 bg-white bg-opacity-50 fw-bold"
                            {{ in_array($status, ['resolved', 'closed']) ? '' : '' }}>

                            @if(in_array($status, ['resolved', 'closed']))
                            {{-- Hanya tampilkan pilihan untuk status final --}}
                            <option value="resolved">Selesai</option>
                            <option value="closed">Ditutup</option>
                            @else
                            {{-- Tampilkan semua pilihan jika belum final --}}
                            <option value="open">Terbuka</option>
                            <option value="pending">Tertunda</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Selesai</option>
                            <option value="closed">Ditutup</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-4 border h-100" style="background: rgba(255, 255, 255, 0.4);">
                        <label class="text-uppercase small fw-black text-muted mb-2 d-block opacity-75">Prioritas</label>
                        <select wire:model.live="priority" class="form-select form-select-sm rounded-pill border-0 bg-white bg-opacity-50 fw-bold">
                            <option value="low">Rendah</option>
                            <option value="medium">Sedang</option>
                            <option value="high">Tinggi</option>
                            <option value="urgent">Mendesak</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="my-4 border-dark opacity-10">

            <div class="row g-3">
                @php
                $fields = [
                ['label' => 'Nama Pengirim', 'value' => $message->name, 'icon' => 'bi-person-badge'],
                ['label' => 'Alamat Email', 'value' => $message->email, 'icon' => 'bi-envelope-at'],
                ['label' => 'No. WhatsApp', 'value' => $message->no_telp, 'icon' => 'bi-whatsapp', 'link' => true],
                ['label' => 'IP Address', 'value' => $message->ip_address, 'icon' => 'bi-globe2'],
                ];
                @endphp

                @foreach($fields as $field)
                <div class="col-md-6">
                    <div class="p-3 rounded-4 border h-100 d-flex align-items-center" style="background: rgba(255, 255, 255, 0.5);">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                            <i class="bi {{ $field['icon'] }} text-primary fs-5"></i>
                        </div>
                        <div>
                            <label class="text-uppercase small fw-black text-muted mb-0 opacity-75">{{ $field['label'] }}</label>
                            @if(isset($field['link']))
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', $field['value']) }}" target="_blank" class="fw-bold text-dark d-block text-decoration-none">
                                {{ $field['value'] }}
                            </a>
                            @else
                            <p class="fw-bold text-dark mb-0">{{ $field['value'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="col-12">
                    <div class="p-3 rounded-4 border d-flex align-items-start" style="background: rgba(255, 255, 255, 0.5);">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 mt-1">
                            <i class="bi bi-cpu text-primary fs-5"></i>
                        </div>
                        <div class="w-100">
                            <label class="text-uppercase small fw-black text-muted mb-0 opacity-75">User Agent</label>
                            <p class="fw-bold text-dark mb-0 small" style="word-break: break-all;">{{ $message->user_agent }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4 border-dark opacity-10">

            <div class="p-4 rounded-4" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.05); box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                <label class="text-uppercase small fw-black text-primary mb-3 d-flex align-items-center">
                    <i class="bi bi-chat-right-text me-2"></i> Isi Pesan Pelanggan
                </label>
                <div class="bg-light p-4 rounded-3 border-start border-4 border-primary" style="line-height: 1.8; white-space: pre-line; color: #334155;">
                    {{ $message->message }}
                </div>
            </div>

        </div>
    </div>
</div>
</div>

<!--================== SWEET ALERT UPDATE STATUS & PRIORITY TICKET ==================-->
<script>
    window.addEventListener('toast-success', (event) => {
        const message = event.detail.message || (event.detail[0] ? event.detail[0].message : '');

        Swal.fire({
            text: message,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.style.background = 'rgba(255, 255, 255, 0.9)';
                toast.style.backdropFilter = 'blur(10px)';
                toast.style.border = '1px solid rgba(255, 255, 255, 0.3)';
                toast.style.borderRadius = '15px';
                toast.style.color = '#333';
            }
        });
    });
</script>
<!--================== SWEET ALERT UPDATE STATUS & PRIORITY TICKET ==================-->