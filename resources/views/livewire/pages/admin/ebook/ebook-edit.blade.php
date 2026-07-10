@section('title')
Update Ebook || lemon
@stop
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4 mb-4 fixed-header-card">
        <div class="card-body p-4 d-flex align-items-center">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 header-action w-100">
                <div class="title-wrapper text-center text-md-start w-100">
                    <h3 class="gradient-text fw-bold mb-1">Update Ebook Bonus</h3>
                    <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                        @php
                        $breadcrumbs = [
                        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                        ['name' => 'Ebook Bonus', 'url' => route('admin.ebook.index')],
                        ['name' => 'Update Ebook'],
                        ];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <livewire:pages.admin.ebook.ebook-form :ebook="$ebook" :key="'ebook-form-'.$ebook->id" />
        </div>
    </div>
</div>