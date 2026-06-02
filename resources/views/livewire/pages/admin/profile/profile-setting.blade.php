<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Pengaturan Profil</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Pengaturan Profil']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="row gap-5 card">
        <div class="card-body row">
            <ul class="mb-1 nav nav-tabs">
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'tab-profile') active @endif" wire:click="setTab('tab-profile')">
                        <i class="bi bi-person-circle me-1"></i>
                        <span>Pengaturan Profil</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'tab-password') active @endif"
                        wire:click="setTab('tab-password')">
                        <i class="bi bi-shield-lock me-1"></i>
                        <span>Ganti Password</span>
                    </button>
                </li>
            </ul>

            @if($activeTab === 'tab-profile')
            <div class="mt-3 row">
                <!-- Profile Photo -->
                <div class="mb-5 col-12 col-md-4">
                    <h5 class="mb-3">foto profil</h5>
                    <div class="">
                        <div class="mb-2">
                            <div style="width: 250px; height: 250px;" class="overflow-hidden rounded-lg">
                                <img class="rounded object-fit-cover"
                                    src="{{ auth()->user()->profile_photo ? Storage::url(auth()->user()->profile_photo) : auth()->user()->profile_photo_url }}"
                                    alt="Profile photo" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <form wire:submit="updatePhoto" enctype="multipart/form-data">
                                <input type="file" wire:model="photo" accept="image/*" class="form-control "
                                    style="max-width: 300px;">
                                <button type="submit" class="btn btn-primary mt-4" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="updatePhoto">Update Foto
                                        Profil</span>
                                    <span wire:loading wire:target="updatePhoto">
                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                        Uploading...
                                    </span>
                                </button>
                                @if ($current_profile_photo)
                                <button type="button" wire:click="removePhoto"
                                    class="btn btn-danger mt-4">Hapus</button>
                                @endif
                                @error('photo')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Basic Info Form -->
                <div class="col-md-8">
                    <h5 class="mb-3">Data Akun</h5>
                    <form wire:submit="updateProfile" class="w-100 w-lg-75">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-medium">Nama Akun</label>
                            <input type="text" id="name" wire:model="name" class="form-control">
                            @error('name')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium">Email Akun</label>
                            <input type="email" id="email" wire:model="email" class="form-control">
                            @error('email')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="role" class="form-label fw-medium">Role Akun</label>
                            <input type="role" disabled id="role" class="form-control"
                                value="{{ auth()->user()->role->name }}">
                            @error('role')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updateProfile">Update Data Akun</span>
                                <span wire:loading wire:target="updateProfile">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    Updating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($activeTab === 'tab-password')
            <div class="mt-3">
                <form wire:submit="updatePassword" class="w-100 w-lg-75">
                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-medium">Password Saat Ini</label>
                        <input type="password" id="current_password" wire:model="current_password" class="form-control">
                        @error('current_password')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">Password Baru</label>
                        <input type="password" id="password" wire:model="password" class="form-control">
                        @error('password')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-medium">Ulangi Password
                            Baru</label>
                        <input type="password" id="password_confirmation" wire:model="password_confirmation"
                            class="form-control">
                        @error('password_confirmation')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="updatePassword">Ganti Password</span>
                            <span wire:loading wire:target="updatePassword">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Changing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>