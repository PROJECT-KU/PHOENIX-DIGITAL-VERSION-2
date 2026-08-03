<?php

namespace App\Http\Controllers;

use App\Services\Telegram\AgenAi;
use App\Services\Telegram\AksiAgen;
use App\Services\Telegram\TelegramClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Webhook bot Telegram — pintu masuk perintah dari admin.
 *
 * Berlapis, gagal-tertutup (fail closed):
 *   1. Token bot kosong  => 404 (bot nonaktif).
 *   2. Secret kosong     => 404. Tanpa secret, webhook tidak bisa dipakai.
 *   3. Header secret dari Telegram harus cocok (hash_equals) => selain itu 404.
 *   4. Chat ID harus terdaftar di TELEGRAM_ALLOWED_CHAT_IDS => selain itu
 *      diabaikan diam-diam.
 *   5. Eksekusi HANYA lewat AksiAgen (daftar putih). Tidak ada shell/eval.
 *
 * Selalu balas 200 setelah lolos lapisan 3–4, supaya Telegram tidak mengulang
 * kiriman yang sama berkali-kali.
 */
class TelegramWebhookController extends Controller
{
    /** Batas perintah per chat per menit. */
    private const BATAS_PER_MENIT = 20;

    public function __invoke(Request $request, TelegramClient $telegram)
    {
        abort_unless($telegram->aktif(), 404);

        $secret = (string) config('telegram.secret');
        abort_if($secret === '', 404);

        $dikirim = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');
        abort_unless(hash_equals($secret, $dikirim), 404);

        $pesan = $request->input('message');

        if (! is_array($pesan)) {
            return response('ok', 200);
        }

        $chatId = $pesan['chat']['id'] ?? null;
        $teks = trim((string) ($pesan['text'] ?? ''));

        if ($chatId === null || $teks === '') {
            return response('ok', 200);
        }

        $diizinkan = config('telegram.chat_ids', []);

        // Mode pendaftaran: selama TELEGRAM_ALLOWED_CHAT_IDS masih kosong, bot
        // hanya memberitahu chat ID supaya bisa dipasang di .env. TIDAK ADA
        // aksi yang dijalankan pada mode ini.
        if (empty($diizinkan)) {
            $telegram->kirim($chatId,
                "Chat ID Anda: {$chatId}\n\n"
                ."Masukkan ke .env lalu jalankan `php artisan config:clear`:\n"
                ."TELEGRAM_ALLOWED_CHAT_IDS={$chatId}\n\n"
                .'Sebelum itu dipasang, bot tidak menjalankan perintah apa pun.'
            );

            return response('ok', 200);
        }

        if (! in_array((string) $chatId, array_map('strval', $diizinkan), true)) {
            return response('ok', 200);
        }

        if (RateLimiter::tooManyAttempts('telegram:'.$chatId, self::BATAS_PER_MENIT)) {
            $telegram->kirim($chatId, '⏳ Terlalu banyak perintah. Coba lagi sebentar lagi.');

            return response('ok', 200);
        }

        RateLimiter::hit('telegram:'.$chatId, 60);

        $telegram->kirim($chatId, $this->tanggapi($teks));

        return response('ok', 200);
    }

    /** Tentukan aksi dari teks, lalu jalankan lewat daftar putih. */
    private function tanggapi(string $teks): string
    {
        // 1. Perintah slash — jalur utama, selalu tersedia, tanpa biaya.
        if (str_starts_with($teks, '/')) {
            $perintah = strtolower(ltrim(explode(' ', $teks)[0], '/'));

            // Buang "@namabot" pada perintah di grup.
            $perintah = explode('@', $perintah)[0];

            if ($perintah === 'start' || $perintah === 'help') {
                return AksiAgen::bantuan();
            }

            return AksiAgen::jalankan($perintah);
        }

        // 2. Teks bebas — coba lewat AI bila dikonfigurasi.
        $aksi = AgenAi::pilihAksi($teks);

        if ($aksi !== null) {
            return AksiAgen::jalankan($aksi);
        }

        // 3. Tidak dikenali.
        return AgenAi::aktif()
            ? "Saya belum menangkap maksudnya.\n\n".AksiAgen::bantuan()
            : AksiAgen::bantuan();
    }
}
