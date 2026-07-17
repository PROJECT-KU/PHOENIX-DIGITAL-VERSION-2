<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'type', 'level', 'event', 'message',
        'exception_class', 'file', 'line', 'status_code', 'duration_ms', 'trace',
        'url', 'method',
        'user_id', 'user_name', 'ip', 'user_agent',
    ];

    protected $casts = [
        'line' => 'integer',
        'status_code' => 'integer',
        'duration_ms' => 'integer',
    ];

    /** Warna badge Bootstrap sesuai level. */
    public function levelColor(): string
    {
        return match ($this->level) {
            'error' => 'danger',
            'warning' => 'warning',
            default => 'secondary',
        };
    }

    /** Label tipe yang ramah dibaca. */
    public function typeLabel(): string
    {
        return match ($this->type) {
            'error' => 'Error',
            'visit' => 'Kunjungan',
            'slow' => 'Lambat',
            'auth' => 'Auth',
            default => ucfirst((string) $this->type),
        };
    }

    /** Warna badge tipe. */
    public function typeColor(): string
    {
        return match ($this->type) {
            'error' => 'danger',
            'visit' => 'info',
            'slow' => 'warning',
            default => 'primary',
        };
    }

    /** Label event yang ramah dibaca. */
    public function eventLabel(): string
    {
        return match ($this->event) {
            'exception' => 'Error',
            'page_view' => 'Kunjungan',
            'slow_request' => 'Request Lambat',
            'login' => 'Login',
            'logout' => 'Logout',
            'login_failed' => 'Login Gagal',
            default => ucfirst((string) $this->event),
        };
    }

    /** Warna durasi: hijau cepat, kuning sedang, merah lambat. */
    public function durationColor(): string
    {
        $ms = (int) $this->duration_ms;
        if ($ms >= 1000) {
            return 'danger';
        }
        if ($ms >= 500) {
            return 'warning';
        }

        return 'success';
    }

    /**
     * Ringkasan perangkat & browser dari user agent (mis. "Windows · Chrome").
     * Deteksi ringan berbasis kata kunci — cukup untuk tracking, tanpa paket.
     */
    public function deviceLabel(): ?string
    {
        $ua = (string) $this->user_agent;
        if ($ua === '') {
            return null;
        }

        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'iPhone') => 'iPhone',
            str_contains($ua, 'iPad') => 'iPad',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Mac OS') || str_contains($ua, 'Macintosh') => 'Mac',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Perangkat',
        };

        $browser = match (true) {
            str_contains($ua, 'Edg') => 'Edge',
            str_contains($ua, 'OPR') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Chrome') => 'Chrome',
            str_contains($ua, 'Firefox') => 'Firefox',
            str_contains($ua, 'Safari') => 'Safari',
            default => 'Browser',
        };

        return $os.' · '.$browser;
    }
}
