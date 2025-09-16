<div>
    <h2 class="h3 fw-bold mb-4">Profile Settings</h2>
    <div class="row gap-5">
        <!-- Profile Information Section -->
        <div class="col-12 col-lg-6">
            <div class="card border-none">
                <div class="card-body">
                    <h3 class="card-title mb-4">Informasi Akun</h3>
                    <!-- Profile Photo -->
                    <div class="mb-5">
                        <label class="form-label fw-medium">Foto Profil</label>
                        <div class="d-flex align-items-center">
                            <div class="me-4">
                                <img class="rounded-circle object-fit-cover"
                                    src="{{ auth()->user()->profile_photo ? Storage::url(auth()->user()->profile_photo) : auth()->user()->profile_photo_url }}"
                                    alt="Profile photo" width="64" height="64">

                            </div>
                            <div class="flex-grow-1">
                                <form wire:submit="updatePhoto" enctype="multipart/form-data">
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="file" wire:model="photo" accept="image/*" class="form-control "
                                            style="max-width: 300px;">
                                        <button type="submit" class="btn btn-primary btn-sm"
                                            wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="updatePhoto">Update Foto
                                                Profil</span>
                                            <span wire:loading wire:target="updatePhoto">
                                                <span class="spinner-border spinner-border-sm me-1"
                                                    role="status"></span>
                                                Uploading...
                                            </span>
                                        </button>
                                        @if ($current_profile_photo)
                                            <button type="button" wire:click="removePhoto"
                                                class="btn btn-danger btn-sm">Hapus</button>
                                        @endif
                                    </div>
                                    @error('photo')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Info Form -->
                    <form wire:submit="updateProfile">
                        <div>
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
                        </div>
                        <div class="mb-4">
                            <p class="mb-0 text-muted">
                                <strong>Role:</strong> {{ auth()->user()->role->display_name }}
                            </p>
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
        </div>

        <!-- Change Password Section -->
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Ganti Password</h3>
                    <form wire:submit="updatePassword">
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-medium">Password Saat Ini</label>
                            <input type="password" id="current_password" wire:model="current_password"
                                class="form-control">
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
                            <label for="password_confirmation" class="form-label fw-medium">Ulangi Password Baru</label>
                            <input type="password" id="password_confirmation" wire:model="password_confirmation"
                                class="form-control">
                            @error('password_confirmation')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 mt-5">
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
            </div>
        </div>
    </div>
</div>
