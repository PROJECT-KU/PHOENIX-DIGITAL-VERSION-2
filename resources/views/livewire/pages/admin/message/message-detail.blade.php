<div>
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <h3>Detail Pelamar Kerja</h3>
        @php
            $breadcrumbs = [
                ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                ['name' => 'Pesan Masuk', 'url' => route('admin.message.index')],
                ['name' => 'Detail Pesan'],
            ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="mb-4 card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label>nama pengirim</label>
                    <p class="fs-5 fw-bold">{{ $message->name }}</p>
                </div>
                <div class="col-md-6">
                    <label>alamat email</label>
                    <p class="fs-5 fw-bold">{{ $message->email }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label>IP pengirim</label>
                    <p class="fs-5 fw-bold">{{ $message->ip_address }}</p>
                </div>
                <div class="col-md-6">
                    <label>user agent</label>
                    <p class="fs-5 fw-bold">{{ $message->user_agent }}</p>
                </div>
            </div>
            <div class="my-3">
                <label>isi pesan</label>
                <p class="fw-bold fs-5">{{ $message->message }}</p>
            </div>
        </div>
    </div>
</div>
