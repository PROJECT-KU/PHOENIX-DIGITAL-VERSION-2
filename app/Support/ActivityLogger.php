<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Penulis log aktivitas (error & auth) yang AMAN untuk maintenance.
 *
 * Prinsip utama: logging TIDAK BOLEH PERNAH mengganggu alur aplikasi. Semua
 * penulisan dibungkus try/catch dan menelan errornya sendiri — jadi walau tabel
 * belum ada, DB sedang down, atau kolom berubah, aplikasi tetap jalan normal.
 * Karena itu memasang ini tidak mengubah perilaku fitur mana pun.
 */
class ActivityLogger
{
    /** Exception yang MEMANG wajar terjadi — bukan bug, jadi tak perlu dicatat sebagai error. */
    private const DIABAIKAN = [
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Http\Exceptions\ThrottleRequestsException::class,
    ];

    /** Catat sebuah exception sebagai baris error. */
    public static function exception(Throwable $e): void
    {
        foreach (self::DIABAIKAN as $kelas) {
            if ($e instanceof $kelas) {
                return;
            }
        }

        $status = null;
        if (method_exists($e, 'getStatusCode')) {
            try {
                $status = $e->getStatusCode();
            } catch (Throwable) {
                $status = null;
            }
        }

        self::write([
            'type' => 'error',
            'level' => 'error',
            'event' => 'exception',
            'message' => self::potong($e->getMessage() ?: class_basename($e), 2000),
            'exception_class' => get_class($e),
            'file' => self::potong($e->getFile(), 1000),
            'line' => $e->getLine(),
            'status_code' => $status,
            'duration_ms' => self::durasiSampaiSekarang(),
            'trace' => self::potong($e->getTraceAsString(), 20000),
        ]);
    }

    /**
     * Catat sebuah KUNJUNGAN halaman (public atau admin) beserta durasinya.
     * Dipanggil dari middleware pada tahap terminate (setelah response terkirim),
     * jadi tak menambah waktu tunggu pengguna. Level naik bila request lambat,
     * sehingga URL lambat mudah disaring.
     */
    public static function visit(int $durationMs, ?int $statusCode = null): void
    {
        $level = match (true) {
            $durationMs >= 3000 => 'error',
            $durationMs >= 1000 => 'warning',
            default => 'info',
        };

        self::write([
            'type' => 'visit',
            'level' => $level,
            'event' => 'page_view',
            'message' => $durationMs >= 1000
                ? 'Kunjungan lambat ('.number_format($durationMs).' ms)'
                : 'Kunjungan halaman',
            'status_code' => $statusCode,
            'duration_ms' => $durationMs,
        ]);
    }

    /** Catat sebuah peristiwa auth (login/logout/login gagal). */
    public static function auth(string $event, string $message, string $level = 'info', array $extra = []): void
    {
        self::write(array_merge([
            'type' => 'auth',
            'level' => $level,
            'event' => $event,
            'message' => self::potong($message, 2000),
        ], $extra));
    }

    /**
     * Tulis baris log. Melengkapi konteks request & user secara otomatis,
     * lalu menelan error apa pun agar tak pernah mengganggu aplikasi.
     */
    private static function write(array $data): void
    {
        try {
            $data += self::konteks();

            ActivityLog::create($data);
        } catch (Throwable) {
            // Sengaja diabaikan: logging tidak boleh menjatuhkan aplikasi.
        }
    }

    /** Konteks request & user saat ini (aman di web maupun console). */
    private static function konteks(): array
    {
        $ctx = [
            'user_id' => null,
            'user_name' => null,
            'url' => null,
            'method' => null,
            'ip' => null,
            'user_agent' => null,
        ];

        try {
            if (Auth::hasUser()) {
                $u = Auth::user();
                $ctx['user_id'] = $u->id ?? null;
                $ctx['user_name'] = $u->name ?? null;
            }
        } catch (Throwable) {
            // biarkan null
        }

        try {
            if (app()->runningInConsole()) {
                $ctx['url'] = 'console';
            } else {
                $req = request();
                $ctx['url'] = self::potong($req->fullUrl(), 2000);
                $ctx['method'] = $req->method();
                $ctx['ip'] = $req->ip();
                $ctx['user_agent'] = self::potong((string) $req->userAgent(), 1000);
            }
        } catch (Throwable) {
            // biarkan seadanya
        }

        return $ctx;
    }

    /** Milidetik sejak awal request (LARAVEL_START) sampai sekarang, bila tersedia. */
    private static function durasiSampaiSekarang(): ?int
    {
        if (! defined('LARAVEL_START')) {
            return null;
        }

        return (int) round((microtime(true) - LARAVEL_START) * 1000);
    }

    private static function potong(?string $s, int $max): ?string
    {
        if ($s === null) {
            return null;
        }

        return mb_strlen($s) > $max ? mb_substr($s, 0, $max).'…' : $s;
    }
}
