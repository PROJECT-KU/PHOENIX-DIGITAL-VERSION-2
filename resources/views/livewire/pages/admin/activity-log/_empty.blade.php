<div class="d-flex flex-column align-items-center justify-content-center">
    <div class="empty-state-icon-wrapper mb-3">
        <i class="bi bi-clipboard-check"></i>
    </div>
    <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Log Aktivitas</h5>
    <p class="text-muted mb-0" style="font-size: 0.95rem;">
        @if ($search || $filterType || $filterLevel || $filterTanggal)
        Tidak ada log yang cocok dengan filter. Coba reset filter.
        @else
        Sistem berjalan lancar — belum ada error, request lambat, atau aktivitas login yang tercatat.
        @endif
    </p>
</div>
