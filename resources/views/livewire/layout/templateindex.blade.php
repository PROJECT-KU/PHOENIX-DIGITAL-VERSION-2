@extends('layouts.app')

@section('content')
@push('styles')
<!--================== STYLE LUST BANNERS ==================-->
<style>
    body {
        background: #f8fafc !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* Card & Effect */
    .card {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        border-radius: 28px !important;
        transition: 0.3s;
    }

    .card:hover {
        box-shadow: 0 15px 40px rgba(139, 92, 246, 0.1) !important;
    }

    /* Header Action Seragam */
    .header-action .form-control,
    .header-action .btn {
        height: 48px !important;
        display: flex !important;
        align-items: center !important;
    }

    /* Table Styling */
    .table {
        border-collapse: separate !important;
        border-spacing: 0 12px !important;
    }

    .table tbody tr {
        background: #ffffff !important;
        border-radius: 15px !important;
        transition: 0.3s;
    }

    .table tbody tr:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(139, 92, 246, 0.1);
    }

    /* Input & Icons */
    .form-control {
        border-radius: 12px !important;
        border: 2px solid #e2e8f0 !important;
        padding: 0 20px 0 45px !important;
    }

    .form-control:focus {
        border-color: #8b5cf6 !important;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1) !important;
    }

    .form-control-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #8b5cf6;
        pointer-events: none;
    }

    /* Button Styling */
    .btn {
        border-radius: 12px !important;
        font-weight: 600;
        padding: 10px 20px !important;
        transition: 0.3s;
        position: relative;
        overflow: hidden;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7c3aed, #4f46e5) !important;
        border: none !important;
    }

    .btn-warning {
        background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
    }

    .btn-danger {
        background: linear-gradient(135deg, #f43f5e, #e11d48) !important;
    }

    .btn:hover {
        transform: translateY(-3px);
        filter: brightness(1.1);
    }

    .btn:active {
        transform: translateY(0) scale(0.95) !important;
    }

    /* Button Icons & Layout */
    .btn .bi {
        font-size: 1.25rem !important;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .btn-primary .bi-plus-lg {
        font-size: 1.35rem !important;
    }

    .btn i.me-1 {
        margin-right: 8px !important;
    }

    /* Typography & Badge */
    .gradient-text {
        background: linear-gradient(135deg, #7c3aed, #4f46e5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.75rem;
    }

    .breadcrumb-custom .breadcrumb {
        margin-bottom: 0 !important;
    }

    .badge {
        padding: 6px 12px !important;
        border-radius: 8px !important;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            min-width: 600px;
        }
    }

    @media (max-width: 767px) {
        .header-action {
            flex-direction: column !important;
        }

        .header-action .btn,
        .header-action .form-group {
            width: 100% !important;
        }
    }

    /* Blur Backdrop */
    .swal2-container.swal2-backdrop-show {
        backdrop-filter: blur(8px) !important;
    }

    /* Popup Kaca */
    .swal-glossy-popup {
        border-radius: 28px !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    }

    /* Tombol Glossy */
    .btn-glossy-confirm {
        background: linear-gradient(135deg, #7c3aed, #4f46e5) !important;
        color: white !important;
        padding: 12px 24px !important;
        border-radius: 12px !important;
        margin: 0 5px !important;
        border: none !important;
        font-weight: 600 !important;
    }

    .btn-glossy-cancel {
        background: #e2e8f0 !important;
        color: #475569 !important;
        padding: 12px 24px !important;
        border-radius: 12px !important;
        margin: 0 5px !important;
        border: none !important;
        font-weight: 600 !important;
    }

    /* Mengatur presisi ikon di dalam modal glossy */
    /* Memaksa kontainer ikon menjadi flex agar bisa diatur posisinya */
    .swal-glossy-popup .swal2-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 1.5rem auto 1rem auto !important;
        transform: scale(0.85);
        position: relative !important;
        /* Penting untuk mengunci posisi */
    }

    /* Mengatasi offset pada ikon sukses */
    .swal-glossy-popup .swal2-icon.swal2-success {
        border-color: #10b981 !important;
    }

    /* Memastikan lingkaran (ring) berada tepat di tengah */
    .swal-glossy-popup .swal2-icon.swal2-success .swal2-success-ring {
        display: none !important;
        /* Opsi: Sembunyikan ring jika masih menyebabkan pergeseran */
    }

    /* Mengatur posisi garis centang (check) agar benar-benar simetris */
    .swal-glossy-popup .swal2-icon.swal2-success [class^='swal2-success-line'] {
        background-color: #10b981 !important;
    }

    /* Menghilangkan margin bawaan SweetAlert pada ikon */
    .swal-glossy-popup .swal2-success-circular-line-left,
    .swal-glossy-popup .swal2-success-circular-line-right,
    .swal-glossy-popup .swal2-success-fix {
        display: none !important;
    }

    /* KUSTOMISASI TOMBOL ANGKA PAGINATION (MODERN) */
    .pagination {
        gap: 5px;
        margin-bottom: 0;
    }

    .page-item .page-link {
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        color: #64748b !important;
        font-weight: 600 !important;
        padding: 8px 16px !important;
        background-color: #ffffff !important;
        transition: all 0.3s ease !important;
    }

    .page-item .page-link:hover {
        background-color: #f8fafc !important;
        color: #7c3aed !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-2px);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, #7c3aed, #4f46e5) !important;
        color: white !important;
        border: none !important;
        box-shadow: 0 5px 15px rgba(124, 58, 237, 0.3) !important;
    }

    .page-item.disabled .page-link {
        background-color: #f1f5f9 !important;
        color: #94a3b8 !important;
        border-color: #e2e8f0 !important;
        pointer-events: none !important;
    }

    .page-item .page-link:focus {
        box-shadow: none !important;
    }

    /* POSISI PAGINATION (ATAS) & TEKS "SHOWING" (BAWAH) */
    nav .d-sm-flex {
        display: flex !important;
        flex-direction: column-reverse !important;
        justify-content: center !important;
        align-items: center !important;
        width: 100%;
        background: #ffffff;
        padding: 16px 20px;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        border: 1px solid #f1f5f9;
        margin-top: 10px;
        gap: 12px;
    }

    nav .d-sm-flex>div:first-child,
    nav .d-sm-flex>div:last-child {
        margin-bottom: 0 !important;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    nav p.text-muted {
        margin-bottom: 0 !important;
        color: #64748b !important;
        font-weight: 500;
        font-size: 0.9rem;
    }

    nav p.text-muted .fw-semibold {
        color: #7c3aed !important;
        font-weight: 800 !important;
        background: rgba(124, 58, 237, 0.1);
        padding: 2px 8px;
        border-radius: 6px;
        margin: 0 2px;
    }

    .btn-clear-hover {
        transition: color 0.2s ease;
    }

    .btn-clear-hover:hover {
        color: #dc3545 !important;
    }

    /* TAMPILAN EMPTY STATE (DATA KOSONG) */
    .empty-state-icon-wrapper {
        width: 80px;
        height: 80px;
        background: rgba(124, 58, 237, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .empty-state-icon-wrapper i {
        font-size: 2.5rem;
        color: #7c3aed;
        line-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Efek membesar saat kursor diarahkan ke area tabel kosong */
    .table tbody tr:hover .empty-state-icon-wrapper {
        transform: scale(1.1);
        background: rgba(124, 58, 237, 0.15);
        box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.2);
    }
</style>
<!--================== END STYLE LIST BANNERS ==================-->


<!--================== STYLE FORM BANNERS ==================-->
<style>
    /* Memaksa tinggi card header agar sama dengan halaman List */
    .fixed-header-card {
        min-height: 94.4px !important;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    /* Pastikan h3 tidak menambah tinggi secara tidak sengaja */
    .title-wrapper h3 {
        margin-bottom: 0 !important;
        line-height: 1.2;
    }

    /* Hilangkan margin bawaan breadcrumb agar tidak menambah tinggi */
    .breadcrumb-custom {
        margin-top: 2px;
    }

    /* Penyesuaian form-control untuk meniru style list */
    .form-control,
    .form-select {
        height: 48px !important;
        border-radius: 12px !important;
        border: 2px solid #e2e8f0 !important;
        transition: 0.3s;
    }

    .form-control:focus {
        border-color: #8b5cf6 !important;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1) !important;
    }

    .btn-secondary {
        background: #e2e8f0 !important;
        border: none !important;
        color: #475569 !important;
    }

    .btn-secondary:hover {
        background: #cbd5e1 !important;
    }

    /* Container Utama */
    .upload-container {
        position: relative;
        height: 160px;
        border: 2px dashed #cbd5e1;
        border-radius: 20px;
        background: #f8fafc;
        transition: 0.3s;
        cursor: pointer;
        overflow: hidden;
        /* Penting agar input tidak keluar batas */
    }

    .upload-container:hover {
        border-color: #8b5cf6;
        background: #f5f3ff;
    }

    /* Input file harus menutupi seluruh area */
    .file-input {
        opacity: 0;
        width: 100% !important;
        height: 100% !important;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 10;
        /* Pastikan paling atas */
        cursor: pointer;
    }

    .upload-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        pointer-events: none;

        /* Layout yang kaku dan rapi */
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 15px !important;
    }

    /* Memastikan ikon tidak punya margin bawaan */
    .upload-overlay i {
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1 !important;
        margin-bottom: 10px !important;
        /* Jarak tetap ke teks */
    }

    /* Memastikan teks tidak punya margin bawaan */
    .upload-overlay span {
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1.2 !important;
        display: block;
    }

    /* Styling Preview (Sudah Digabung & Dibersihkan) */
    .preview-box {
        background: #ffffff;
        min-width: 216px;
        border: 2px solid #e2e8f0 !important;
        border-radius: 20px !important;
        transition: 0.3s;

        /* Ukuran kaku agar tidak berubah-ubah */
        width: 100%;
        height: 160px !important;

        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        /* SANGAT PENTING: Memotong sisa gambar yang keluar */
    }

    .preview-box img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        /* Memastikan gambar terpotong proporsional */
        object-position: center !important;
        /* Memastikan titik tengah gambar yang tampil */
        border-radius: 15px !important;
    }

    /* Wrapper untuk memberikan kesan kedalaman */
    .textarea-wrapper {
        position: relative;
    }

    .description-input {
        width: 100%;
        height: auto !important;
        padding: 16px 20px !important;
        /* Padding lebih lega */
        border-radius: 20px !important;
        /* Radius lebih bulat agar senada dengan form lain */
        border: 2px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        font-size: 1rem;
        line-height: 1.6;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        resize: vertical;
        /* User bisa mengatur tinggi */
    }

    /* Efek saat fokus */
    .description-input:focus {
        border-color: #8b5cf6 !important;
        box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.15) !important;
    }

    /* Memberikan placeholder yang lembut */
    .description-input::placeholder {
        color: #94a3b8;
        font-size: 0.95rem;
    }

    /* ================== TOAST GLOSSY ================== */
    body.swal2-toast-shown .swal2-container,
    .swal2-container.swal2-top-end {
        backdrop-filter: none !important;
        background-color: transparent !important;
        box-shadow: none !important;
    }

    /* Pastikan blur HANYA bekerja pada kotak Toast itu sendiri */
    .swal-glossy-toast {
        background: rgba(255, 255, 255, 0.85) !important;
        /* Latar belakang kaca */
        backdrop-filter: blur(16px) !important;
        /* Blur hanya seukuran kotak */
        border: 1px solid rgba(255, 255, 255, 0.6) !important;
        border-radius: 16px !important;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
        padding: 10px 15px !important;
        margin-top: 15px !important;
        margin-right: 15px !important;
    }
</style>
<!--================== END STYLE FORM BANNERS ==================-->

<!--================== STYLE DASHBOARD ==================-->
<style>
    /* DASHBOARD STATISTIC CARDS */
    .stat-card {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(20px);
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1) !important;
    }

    /* Efek melayang saat di-hover */
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
    }

    /* Pembungkus Ikon Modern */
    .stat-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
    }

    /* Warna Gradasi Spesifik untuk Masing-masing Ikon */
    .bg-gradient-purple {
        background: linear-gradient(135deg, #7c3aed, #4f46e5);
        box-shadow: 0 8px 15px rgba(124, 58, 237, 0.25);
    }

    .bg-gradient-blue {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        box-shadow: 0 8px 15px rgba(59, 130, 246, 0.25);
    }

    .bg-gradient-green {
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 8px 15px rgba(16, 185, 129, 0.25);
    }

    .bg-gradient-red {
        background: linear-gradient(135deg, #f43f5e, #e11d48);
        box-shadow: 0 8px 15px rgba(244, 63, 94, 0.25);
    }
</style>
<!--================== END STYLE DASHBOARD ==================-->
@endpush

<div class="container-fluid p-4">
    <div class="row">
        <div class="col-12">
            {{ $slot }}
        </div>
    </div>
</div>
@endsection