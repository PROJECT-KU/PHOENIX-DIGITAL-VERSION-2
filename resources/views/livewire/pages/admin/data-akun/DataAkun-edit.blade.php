<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Edit Data Akun</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data akun', 'url' => route('admin.DataAkun.index')],
        ['name' => 'Edit Data Akun'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.data-akun.DataAkun-form :dataAkun="$dataAkun" />
        </div>
    </div>
</div>