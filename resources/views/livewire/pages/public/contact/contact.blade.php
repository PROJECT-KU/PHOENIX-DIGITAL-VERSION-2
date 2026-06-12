<div>
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0 text-muted">Hubungi Kami</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Home</a></li>
                    <li class="current">Hubungi Kami</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->
    <div class="container py-3">
        <div class="">
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @error('rate_limit')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            <form wire:submit="save">
                <div style="display: none; opacity: 0; position: absolute; left: -9999px;">
                    <label for="website_url">Website</label>
                    <input type="text" wire:model="website_url" id="website_url" tabindex="-1" autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name" placeholder="Masukkan nama Anda">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model="email" placeholder="nama@contoh.com">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="no_telp" class="form-label">No WhatsApp (dengan kode negara)</label>
                    <input type="tel"
                        class="form-control @error('no_telp') is-invalid @enderror"
                        id="no_telp"
                        wire:model="no_telp"
                        placeholder="+1234567890">
                    @error('no_telp') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <small class="text-muted">Contoh: +628123456789 atau +1555010999</small>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Pesan</label>
                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" rows="5" wire:model="message" placeholder="Tulis pesan Anda di sini..."></textarea>
                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Kirim Pesan</span>
                        <span wire:loading>Mengirim...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>