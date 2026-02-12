<div>
    <form wire:submit.prevent="save">
        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
                <!-- Name (key) -->
                <div class="mb-3">
                    <label for="name" class="form-label">
                        Nama Permission (Key) <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        id="name"
                        wire:model="name"
                        placeholder="contoh: view_pemesanantoko"
                        {{ $isEdit ? 'readonly' : '' }}>
                    <small class="form-text text-muted">
                        <i class="bi bi-info-circle"></i>
                        Gunakan format: action_module (contoh: view_users, create_pemesanantoko)
                    </small>
                    @error('name')
                    <div class="invalid-feedback d-block">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

                <!-- Display Name -->
                <div class="mb-3">
                    <label for="display_name" class="form-label">
                        Nama Tampilan <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        class="form-control @error('display_name') is-invalid @enderror"
                        id="display_name"
                        wire:model="display_name"
                        placeholder="contoh: Lihat Pemesanan Toko">
                    <small class="form-text text-muted">
                        <i class="bi bi-info-circle"></i>
                        Nama yang akan ditampilkan di interface admin
                    </small>
                    @error('display_name')
                    <div class="invalid-feedback d-block">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-md-6">
                <!-- Group -->
                <div class="mb-3">
                    <label for="group" class="form-label">
                        Group/Kategori
                    </label>
                    <input
                        type="text"
                        class="form-control @error('group') is-invalid @enderror"
                        id="group"
                        wire:model="group"
                        placeholder="contoh: pemesanantoko, users, roles"
                        list="groupSuggestions">

                    <!-- Datalist untuk suggestions -->
                    <datalist id="groupSuggestions">
                        <option value="pemesanantoko">
                        <option value="users">
                        <option value="roles">
                        <option value="settings">
                        <option value="reports">
                    </datalist>

                    <small class="form-text text-muted">
                        <i class="bi bi-info-circle"></i>
                        Untuk mengelompokkan permission berdasarkan modul/fitur
                    </small>
                    @error('group')
                    <div class="invalid-feedback d-block">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">
                        Deskripsi
                    </label>
                    <textarea
                        class="form-control @error('description') is-invalid @enderror"
                        id="description"
                        wire:model="description"
                        rows="3"
                        placeholder="Deskripsi singkat tentang permission ini"></textarea>
                    <small class="form-text text-muted">
                        <i class="bi bi-info-circle"></i>
                        Penjelasan fungsi permission untuk memudahkan admin
                    </small>
                    @error('description')
                    <div class="invalid-feedback d-block">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <button
                    type="submit"
                    class="btn w-100 btn-primary"
                    wire:loading.attr="disabled">
                    <i class="bi bi-send me-1"></i>
                    <span wire:loading.remove>
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data' }}
                    </span>
                    <span wire:loading>
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </form>

    <!-- Contoh Permission Format (Optional) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb"></i> Tips Penamaan Permission
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th width="30%">Format</th>
                                    <th width="35%">Contoh</th>
                                    <th width="35%">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td><code>view_[module]</code></td>
                                    <td><code>view_pemesanantoko</code></td>
                                    <td>Untuk melihat/mengakses halaman</td>
                                </tr>
                                <tr>
                                    <td><code>create_[module]</code></td>
                                    <td><code>create_users</code></td>
                                    <td>Untuk membuat data baru</td>
                                </tr>
                                <tr>
                                    <td><code>edit_[module]</code></td>
                                    <td><code>edit_pemesanantoko</code></td>
                                    <td>Untuk mengedit data</td>
                                </tr>
                                <tr>
                                    <td><code>delete_[module]</code></td>
                                    <td><code>delete_users</code></td>
                                    <td>Untuk menghapus data</td>
                                </tr>
                                <tr>
                                    <td><code>manage_[module]</code></td>
                                    <td><code>manage_roles</code></td>
                                    <td>Untuk full akses (CRUD)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>