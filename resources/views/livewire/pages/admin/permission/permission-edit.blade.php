<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Edit Permission Akun</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Permission', 'url' => route('admin.account.permission')],
        ['name' => 'Edit Permission']
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <!-- memanggil komponen PermissionForm -->
            <livewire:pages.admin.permission.permission-form :permission="$permission" />
        </div>
    </div>
</div>