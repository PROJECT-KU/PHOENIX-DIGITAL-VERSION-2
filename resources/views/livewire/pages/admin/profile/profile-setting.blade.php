
@section('title')
Pengaturan Profil || PT. Asthana Cipta Mandiri
@stop
<div>
    <!--================== GLOSSY STYLE ==================-->
    <style>
        .profile-tabs {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            gap: 6px;
            padding: 6px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.08);
        }

        .profile-tab {
            display: inline-flex;
            flex: 1 1 0;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 22px;
            border: none;
            background: transparent;
            border-radius: 13px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #64748b;
            white-space: nowrap;
            transition: 0.3s;
        }

        .profile-tab i {
            font-size: 1.05rem;
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        .profile-tab i::before {
            display: block;
            line-height: 1;
        }

        /* Selaraskan ikon di dalam tombol & dropzone dengan teksnya */
        .up-icon i::before {
            display: block;
            line-height: 1;
        }

        @media (max-width: 575px) {
            .profile-tab {
                flex: 1 1 auto;
                padding: 10px 14px;
            }
        }

        /* Choose image yang menarik */
        .upload-box {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            border: 2px dashed #d6d3f5;
            background: #faf9ff;
            border-radius: 14px;
            padding: 12px 16px;
            cursor: pointer;
            transition: 0.2s;
            max-width: 320px;
            margin-inline: auto;
        }

        .upload-box:hover {
            border-color: #7c3aed;
            background: #f5f3ff;
        }

        .upload-box .up-icon {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 11px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .upload-box .up-text {
            text-align: left;
            line-height: 1.25;
            overflow: hidden;
        }

        .upload-box .up-title {
            font-weight: 600;
            font-size: 0.88rem;
            color: #4f46e5;
        }

        .upload-box .up-sub {
            font-size: 0.74rem;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }

        .profile-tab:hover {
            color: #7c3aed;
            background: rgba(139, 92, 246, 0.08);
        }

        .profile-tab.active {
            color: #fff;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.3);
        }

        .stat-icon-wrapper {
            line-height: 1 !important;
        }

        .stat-icon-wrapper i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        /* Avatar */
        .avatar-ring {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            padding: 5px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            box-shadow: 0 12px 30px rgba(124, 58, 237, 0.25);
        }

        .avatar-ring img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            background: #fff;
        }

        .avatar-cam {
            position: absolute;
            right: 8px;
            bottom: 8px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #fff;
            color: #7c3aed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            cursor: pointer;
            border: 2px solid #eef2ff;
            transition: 0.2s;
        }

        .avatar-cam:hover {
            transform: scale(1.06);
            color: #4f46e5;
        }

        .field-icon-wrap {
            position: relative;
        }

        .field-icon-wrap .field-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            z-index: 5;
        }

        .field-icon-wrap .form-control {
            padding-left: 42px;
        }

        .readonly-pretty {
            background-color: #f6f5ff !important;
            border-color: #e4e0ff !important;
            color: #4f46e5 !important;
            font-weight: 600;
        }

        .pwd-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            z-index: 5;
        }
    </style>

    <div class="container-fluid">
        <!--================== HEADER ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Pengaturan Profil</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Pengaturan Profil']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== TABS ==================-->
        <div class="profile-tabs mb-4">
            <button type="button" class="profile-tab @if ($activeTab === 'tab-profile') active @endif"
                wire:click="setTab('tab-profile')">
                <i class="bi bi-person-circle"></i>
                <span>Pengaturan Profil</span>
            </button>
            <button type="button" class="profile-tab @if ($activeTab === 'tab-password') active @endif"
                wire:click="setTab('tab-password')">
                <i class="bi bi-shield-lock"></i>
                <span>Ganti Password</span>
            </button>
        </div>

        @if($activeTab === 'tab-profile')
        <div class="row g-4">
            <!--========== FOTO PROFIL ==========-->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                                style="width: 42px; height: 42px; font-size: 1.2rem; border-radius: 13px;">
                                <i class="bi bi-camera-fill"></i>
                            </span>
                            <h5 class="fw-bold mb-0">Foto Profil</h5>
                        </div>

                        <form wire:submit="updatePhoto" enctype="multipart/form-data"
                            x-data="{ fileName: '' }">
                            <div class="position-relative d-inline-block mb-3">
                                <div class="avatar-ring mx-auto">
                                    <img src="{{ $photo ? $photo->temporaryUrl() : (auth()->user()->profile_photo ? Storage::url(auth()->user()->profile_photo) : auth()->user()->profile_photo_url) }}"
                                        alt="Foto Profil">
                                </div>
                                <label for="photo-input" class="avatar-cam" title="Pilih foto">
                                    <i class="bi bi-pencil-fill"></i>
                                </label>
                            </div>

                            <input type="file" id="photo-input" wire:model="photo" accept="image/*" class="d-none"
                                x-on:change="fileName = $event.target.files[0]?.name || ''">

                            <label for="photo-input" class="upload-box mb-3">
                                <span class="up-icon"><i class="bi bi-image"></i></span>
                                <span class="up-text">
                                    <span class="up-title" x-text="fileName ? 'Foto dipilih' : 'Pilih Foto'"></span>
                                    <span class="up-sub" x-text="fileName || 'Klik untuk pilih gambar (JPG/PNG)'"></span>
                                </span>
                            </label>

                            <div wire:loading wire:target="photo" class="text-muted small mb-2">
                                <span class="spinner-border spinner-border-sm me-1"></span> Memuat pratinjau...
                            </div>

                            @error('photo')
                            <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <button type="submit" class="btn btn-primary rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-2"
                                    wire:loading.attr="disabled" wire:target="updatePhoto">
                                    <span wire:loading.remove wire:target="updatePhoto" class="d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-upload"></i>Update Foto
                                    </span>
                                </button>
                                @if ($current_profile_photo)
                                <button type="button" wire:click="removePhoto"
                                    class="btn btn-outline-danger rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-trash"></i>Hapus
                                </button>
                                @endif
                            </div>
                            <p class="text-muted mt-3 mb-0" style="font-size: 0.78rem;">Format JPG/PNG, rasio 1:1 paling pas.</p>
                        </form>
                    </div>
                </div>
            </div>

            <!--========== DATA AKUN ==========-->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="stat-icon-wrapper bg-gradient-blue flex-shrink-0"
                                style="width: 42px; height: 42px; font-size: 1.2rem; border-radius: 13px;">
                                <i class="bi bi-person-vcard-fill"></i>
                            </span>
                            <h5 class="fw-bold mb-0">Data Akun</h5>
                        </div>

                        <form wire:submit="updateProfile">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-semibold">Nama Akun</label>
                                    <div class="field-icon-wrap">
                                        <span class="field-icon"><i class="bi bi-person"></i></span>
                                        <input type="text" id="name" wire:model="name"
                                            class="form-control @error('name') is-invalid @enderror" placeholder="Nama lengkap">
                                    </div>
                                    @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-semibold">Email Akun</label>
                                    <div class="field-icon-wrap">
                                        <span class="field-icon"><i class="bi bi-envelope"></i></span>
                                        <input type="email" id="email" wire:model="email"
                                            class="form-control @error('email') is-invalid @enderror" placeholder="email@contoh.com">
                                    </div>
                                    @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-4">
                                    <label for="role" class="form-label fw-semibold">Role Akun</label>
                                    <div class="field-icon-wrap">
                                        <span class="field-icon"><i class="bi bi-shield-check"></i></span>
                                        <input type="text" id="role" disabled class="form-control readonly-pretty"
                                            value="{{ auth()->user()->role->name ?? '-' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex gap-2">
                                <button type="submit"
                                    class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                                    style="height: 52px;" wire:loading.attr="disabled" wire:target="updateProfile">
                                    <span wire:loading.remove wire:target="updateProfile" class="d-inline-flex align-items-center">
                                        <i class="bi bi-check2-circle me-2 fs-5"></i>
                                        <span>Update Data Profil</span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($activeTab === 'tab-password')
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4" x-data="{ s1: false, s2: false, s3: false }">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-red flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.2rem; border-radius: 13px;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Ganti Password</h5>
                </div>

                <form wire:submit="updatePassword">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="current_password" class="form-label fw-semibold">Password Saat Ini</label>
                            <div class="position-relative">
                                <input :type="s1 ? 'text' : 'password'" id="current_password" wire:model="current_password"
                                    class="form-control pe-5 @error('current_password') is-invalid @enderror">
                                <span class="pwd-toggle" @click="s1 = !s1">
                                    <i class="bi" :class="s1 ? 'bi-eye-slash' : 'bi-eye'"></i>
                                </span>
                            </div>
                            @error('current_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-semibold">Password Baru</label>
                            <div class="position-relative">
                                <input :type="s2 ? 'text' : 'password'" id="password" wire:model="password"
                                    class="form-control pe-5 @error('password') is-invalid @enderror">
                                <span class="pwd-toggle" @click="s2 = !s2">
                                    <i class="bi" :class="s2 ? 'bi-eye-slash' : 'bi-eye'"></i>
                                </span>
                            </div>
                            @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Ulangi Password Baru</label>
                            <div class="position-relative">
                                <input :type="s3 ? 'text' : 'password'" id="password_confirmation" wire:model="password_confirmation"
                                    class="form-control pe-5">
                                <span class="pwd-toggle" @click="s3 = !s3">
                                    <i class="bi" :class="s3 ? 'bi-eye-slash' : 'bi-eye'"></i>
                                </span>
                            </div>
                            @error('password_confirmation')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex gap-2">
                        <button type="submit"
                            class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                            style="height: 52px;" wire:loading.attr="disabled" wire:target="updatePassword">
                            <span wire:loading.remove wire:target="updatePassword" class="d-inline-flex align-items-center">
                                <i class="bi bi-check2-circle me-2 fs-5"></i>
                                <span>Ganti Password</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>