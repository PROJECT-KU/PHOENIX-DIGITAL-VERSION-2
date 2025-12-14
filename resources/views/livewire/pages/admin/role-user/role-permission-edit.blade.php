<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Manajemen Role Permission {{$role->name}}</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Role', 'url' => route('admin.account.role')],
        ['name' => 'Manajemen Role Permission'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <a href="{{route('admin.account.role')}}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                <span>Kembali</span>
            </a>
            <div class="mt-4">
                <div class="">
                    <p>pilih permission yang dapat dimiliki oleh role <strong>{{$role->name}}</strong>.<br /> role admin memiliki deskripsi <strong>{{$role->description}}</strong></p>
                </div>
                <!-- Form -->
                <form wire:submit.prevent="save">
                    @if (count($groupedPermissions) > 0)
                    <!-- Permission Groups -->
                    @foreach ($groupedPermissions as $groupName => $permissions)
                    <div class="mb-3">
                        <div class="py-3 px-3 mb-2 alert alert-light-primary border-start border-2 border-primary border-top-0 border-end-0 border-bottom-0">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="group_{{ $groupName }}"
                                    wire:click="toggleGroup('{{ $groupName }}')"
                                    {{ $this->isGroupFullySelected($groupName) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="group_{{ $groupName }}">
                                    {{ ucfirst($groupName ?? 'Lainnya') }}
                                </label>
                            </div>
                        </div>
                        <div class="px-4 py-2">
                            <div class="row">
                                @foreach ($permissions as $permission)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="permission_{{ $permission->id }}"
                                            wire:click="togglePermission({{ $permission->id }})"
                                            {{ in_array($permission->id, $selectedPermissions) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            {{ $permission->display_name }}
                                            @if ($permission->description)
                                            <i
                                                class="bi bi-info-circle text-muted"
                                                data-bs-toggle="tooltip"
                                                title="{{ $permission->description }}"></i>
                                            @endif
                                        </label>
                                        <small class="text-muted d-block">{{ $permission->name }}</small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Summary -->
                    <div class="alert alert-light-secondary">
                        <strong>Total Permission Dipilih:</strong>
                        <span class="badge bg-secondary">{{ count($selectedPermissions) }}</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            Simpan Permission
                        </button>
                    </div>
                    @else
                    <div class="alert alert-light-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Tidak ada permission yang tersedia. Silakan buat permission terlebih dahulu.
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush