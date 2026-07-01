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
        {{-- <div class="">
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
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        wire:model="name" placeholder="Masukkan nama Anda">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        wire:model="email" placeholder="nama@contoh.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="no_telp" class="form-label">No WhatsApp (dengan kode negara)</label>
                    <input type="tel" class="form-control @error('no_telp') is-invalid @enderror" id="no_telp"
                        wire:model="no_telp" placeholder="+1234567890">
                    @error('no_telp')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Contoh: +628123456789 atau +1555010999</small>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Pesan</label>
                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" rows="5" wire:model="message"
                        placeholder="Tulis pesan Anda di sini..."></textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Kirim Pesan</span>
                        <span wire:loading>Mengirim...</span>
                    </button>
                </div>
            </form>
        </div> --}}
    </div>

    <!-- Contact 2 Section -->
    <section id="contact-2" class="contact-2 section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <!-- Contact Info Boxes -->
            <div class="row gy-4 mb-5">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="contact-info-box">
                        <div class="icon-box">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Our Address</h4>
                            <p>1842 Maple Avenue, Portland, Oregon 97204</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="contact-info-box">
                        <div class="icon-box">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email Address</h4>
                            <p>info@example.com</p>
                            <p>contact@example.com</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="contact-info-box">
                        <div class="icon-box">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div class="info-content">
                            <h4>Hours of Operation</h4>
                            <p>Sunday-Fri: 9 AM - 6 PM</p>
                            <p>Saturday: 9 AM - 4 PM</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Google Maps (Full Width) -->
        @forelse($banners as $banner)
            <div class="map-section" data-aos="fade-up" data-aos-delay="200">
                {{-- <iframe
                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d48389.78314118045!2d-74.006138!3d40.710059!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a22a3bda30d%3A0xb89d1fe6bc499443!2sDowntown%20Conference%20Center!5e0!3m2!1sen!2sus!4v1676961268712!5m2!1sen!2sus"
                width="100%" height="500" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe> --}}
                <img style="width: 100%;" src="{{ asset('storage/img/banners/' . $banner->gambar) }}"
                    alt="{{ $banner->judul ?? 'Banner' }}" class="img-fluid">
            </div>
        @endforeach

        <!-- Contact Form Section (Overlapping) -->
        <div class="container form-container-overlap">
            <div class="row justify-content-center" data-aos="fade-up" data-aos-delay="300">
                <div class="col-lg-10">
                    <div class="contact-form-wrapper">
                        <h2 class="text-center mb-4">Get in Touch</h2>

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
                            {{-- Honeypot --}}
                            <div style="display: none; opacity: 0; position: absolute; left: -9999px;">
                                <label for="website_url">Website</label>
                                <input type="text" wire:model="website_url" id="website_url" tabindex="-1"
                                    autocomplete="off">
                            </div>

                            <div class="row g-3">

                                {{-- Nama --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="input-with-icon">
                                            <i class="bi bi-person"></i>
                                            <input type="text"
                                                class="form-control @error('name') is-invalid @enderror" id="name"
                                                wire:model="name" placeholder="Nama Lengkap">

                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Email --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="input-with-icon">
                                            <i class="bi bi-envelope"></i>
                                            <input type="email"
                                                class="form-control @error('email') is-invalid @enderror" id="email"
                                                wire:model="email" placeholder="Alamat Email">

                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- WhatsApp --}}
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="input-with-icon">
                                            <i class="bi bi-whatsapp"></i>
                                            <input type="tel"
                                                class="form-control @error('no_telp') is-invalid @enderror"
                                                id="no_telp" wire:model="no_telp" placeholder="+628123456789">

                                            @error('no_telp')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <small class="text-muted">
                                            Contoh: +628123456789 atau +1555010999
                                        </small>
                                    </div>
                                </div>

                                {{-- Pesan --}}
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="input-with-icon">
                                            <i class="bi bi-chat-dots message-icon"></i>
                                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" wire:model="message"
                                                placeholder="Tulis pesan Anda di sini..." style="height: 180px"></textarea>

                                            @error('message')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit --}}
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-submit"
                                        wire:loading.attr="disabled">

                                        <span wire:loading.remove>
                                            <i class="bi bi-send-fill me-2"></i>
                                            KIRIM PESAN
                                        </span>

                                        <span wire:loading>
                                            <span class="spinner-border spinner-border-sm me-2"></span>
                                            Mengirim...
                                        </span>
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </section><!-- /Contact 2 Section -->
</div>
