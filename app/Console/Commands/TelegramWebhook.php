<?php

namespace App\Console\Commands;

use App\Services\Telegram\AgenAi;
use App\Services\Telegram\AksiAgen;
use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;

/**
 * Pemasangan & pemeriksaan bot Telegram.
 *
 *   php artisan telegram:webhook info     -> status bot & webhook saat ini
 *   php artisan telegram:webhook id       -> cari chat ID Anda (sebelum webhook dipasang)
 *   php artisan telegram:webhook pasang   -> daftarkan webhook + secret
 *   php artisan telegram:webhook pastikan -> daftarkan HANYA bila belum//salah (dipakai scheduler)
 *   php artisan telegram:webhook tes      -> kirim pesan uji ke chat yang diizinkan
 *   php artisan telegram:webhook hapus    -> lepas webhook (matikan bot)
 */
class TelegramWebhook extends Command
{
    protected $signature = 'telegram:webhook
                            {aksi=info : info|id|pasang|pastikan|tes|hapus}
                            {--url= : URL webhook (default: APP_URL + /telegram/webhook)}';

    protected $description = 'Pasang, periksa, atau lepas webhook bot Telegram.';

    public function handle(TelegramClient $telegram): int
    {
        if (! $telegram->aktif()) {
            $this->error('TELEGRAM_BOT_TOKEN belum diisi di .env.');

            return self::FAILURE;
        }

        return match ($this->argument('aksi')) {
            'info' => $this->info_($telegram),
            'id' => $this->cariId($telegram),
            'pasang' => $this->pasang($telegram),
            'pastikan' => $this->pastikan($telegram),
            'tes' => $this->tes($telegram),
            'hapus' => $this->hapus($telegram),
            default => $this->salahAksi(),
        };
    }

    private function info_(TelegramClient $telegram): int
    {
        $saya = $telegram->saya();

        if (! $saya) {
            $this->error('Tidak bisa menghubungi Telegram. Cek token & koneksi internet server.');

            return self::FAILURE;
        }

        $this->line('Bot        : @'.($saya['username'] ?? '?').' ('.($saya['first_name'] ?? '?').')');

        $webhook = $telegram->infoWebhook() ?? [];
        $url = $webhook['url'] ?? '';

        $this->line('Webhook    : '.($url !== '' ? $url : '(belum dipasang)'));
        $this->line('Tertunda   : '.($webhook['pending_update_count'] ?? 0));

        if (! empty($webhook['last_error_message'])) {
            $this->warn('Error terakhir: '.$webhook['last_error_message']);
        }

        $chatIds = config('telegram.chat_ids', []);
        $this->line('Chat izin  : '.(empty($chatIds) ? '(KOSONG — bot dalam mode pendaftaran)' : implode(', ', $chatIds)));
        $this->line('Secret     : '.(config('telegram.secret') ? 'terpasang' : 'KOSONG — webhook akan menolak semua'));
        $this->line('Mode AI    : '.(AgenAi::aktif() ? 'aktif ('.config('telegram.ai.driver').')' : 'mati (perintah slash saja)'));

        return self::SUCCESS;
    }

    private function cariId(TelegramClient $telegram): int
    {
        $webhook = $telegram->infoWebhook() ?? [];

        if (! empty($webhook['url'])) {
            $this->warn('Webhook sedang aktif, jadi getUpdates tidak bisa dipakai.');
            $this->line('Cara lain: kosongkan TELEGRAM_ALLOWED_CHAT_IDS di .env, jalankan');
            $this->line('`php artisan config:clear`, lalu kirim pesan apa pun ke bot —');
            $this->line('bot akan membalas dengan chat ID Anda.');

            return self::FAILURE;
        }

        $this->info('Kirim pesan apa pun ke bot Anda sekarang. Menunggu ~25 detik...');

        $update = $telegram->ambilUpdate();

        if (empty($update)) {
            $this->warn('Belum ada pesan masuk. Kirim pesan ke bot lalu ulangi perintah ini.');

            return self::FAILURE;
        }

        $ketemu = [];

        foreach ($update as $u) {
            $chat = $u['message']['chat'] ?? null;

            if (is_array($chat) && isset($chat['id'])) {
                $ketemu[(string) $chat['id']] = trim(($chat['first_name'] ?? '').' '.($chat['username'] ?? ''));
            }
        }

        if (empty($ketemu)) {
            $this->warn('Ada update tapi tanpa pesan chat. Kirim pesan teks biasa ke bot.');

            return self::FAILURE;
        }

        $this->newLine();
        foreach ($ketemu as $id => $nama) {
            $this->line("  {$id}  {$nama}");
        }

        $this->newLine();
        $this->info('Salin ke .env:  TELEGRAM_ALLOWED_CHAT_IDS='.implode(',', array_keys($ketemu)));

        return self::SUCCESS;
    }

    private function pasang(TelegramClient $telegram): int
    {
        $secret = (string) config('telegram.secret');

        if ($secret === '') {
            $this->error('TELEGRAM_WEBHOOK_SECRET belum diisi di .env.');
            $this->line('Buat secret acak: php -r "echo bin2hex(random_bytes(24)).PHP_EOL;"');

            return self::FAILURE;
        }

        $url = (string) ($this->option('url') ?: rtrim((string) config('app.url'), '/').'/telegram/webhook');

        if (! str_starts_with($url, 'https://')) {
            $this->error('Telegram hanya menerima webhook HTTPS. URL sekarang: '.$url);
            $this->line('Perbaiki APP_URL di .env, atau pakai --url=https://...');

            return self::FAILURE;
        }

        if (! $telegram->pasangWebhook($url, $secret)) {
            $this->error('Gagal memasang webhook. Jalankan `telegram:webhook info` untuk keterangan.');

            return self::FAILURE;
        }

        $this->info('Webhook terpasang: '.$url);

        $this->pasangMenu($telegram, paksa: true);

        if (empty(config('telegram.chat_ids'))) {
            $this->warn('TELEGRAM_ALLOWED_CHAT_IDS masih kosong — bot dalam MODE PENDAFTARAN.');
            $this->line('Kirim pesan ke bot; ia akan membalas chat ID Anda. Isikan ke .env,');
            $this->line('jalankan `php artisan config:clear`, baru bot menerima perintah.');
        }

        return self::SUCCESS;
    }

    /**
     * Pasang webhook HANYA bila belum terpasang / URL-nya sudah tidak cocok.
     *
     * Dipanggil scheduler tiap jam supaya setelah deploy bot langsung hidup
     * begitu .env diisi — tanpa perlu SSH (crontab CLI diblokir di hosting ini).
     * Juga menyembuhkan diri bila webhook lepas sendiri.
     *
     * Diam total bila belum dikonfigurasi: tidak ada panggilan keluar sama
     * sekali, jadi aman ikut ter-deploy dalam keadaan kosong.
     */
    private function pastikan(TelegramClient $telegram): int
    {
        // Menu perintah SENGAJA disinkronkan lebih dulu dan terpisah dari
        // webhook: ia hanya butuh token, tidak butuh HTTPS maupun secret.
        // Menaruhnya di belakang penjaga HTTPS membuat menu tak pernah
        // terdaftar di lingkungan non-HTTPS (lokal/staging).
        $this->pasangMenu($telegram);

        $secret = (string) config('telegram.secret');
        $url = (string) ($this->option('url') ?: rtrim((string) config('app.url'), '/').'/telegram/webhook');

        if ($secret === '' || ! str_starts_with($url, 'https://')) {
            return self::SUCCESS;
        }

        $webhook = $telegram->infoWebhook();

        if ($webhook === null) {
            return self::FAILURE;
        }

        if (($webhook['url'] ?? '') === $url) {
            return self::SUCCESS;
        }

        if (! $telegram->pasangWebhook($url, $secret)) {
            $this->error('Gagal memasang webhook otomatis.');

            return self::FAILURE;
        }

        $this->info('Webhook dipasang otomatis: '.$url);

        return self::SUCCESS;
    }

    /**
     * Sinkronkan menu perintah Telegram ("/" → daftar perintah).
     *
     * Hanya menulis bila isinya berbeda, supaya panggilan per jam dari
     * scheduler tidak membebani API Telegram tanpa guna.
     */
    private function pasangMenu(TelegramClient $telegram, bool $paksa = false): void
    {
        $diinginkan = AksiAgen::menuTelegram();

        if (! $paksa && $telegram->menuSekarang() == $diinginkan) {
            return;
        }

        if ($telegram->pasangMenu($diinginkan)) {
            $this->info('Menu perintah disinkronkan ('.count($diinginkan).' perintah).');
        } else {
            $this->warn('Gagal menyinkronkan menu perintah.');
        }
    }

    private function tes(TelegramClient $telegram): int
    {
        $chatIds = config('telegram.chat_ids', []);

        if (empty($chatIds)) {
            $this->error('TELEGRAM_ALLOWED_CHAT_IDS masih kosong.');

            return self::FAILURE;
        }

        $gagal = 0;

        foreach ($chatIds as $id) {
            $ok = $telegram->kirim($id, "✅ Uji koneksi dari server.\n\nKetik /bantuan untuk daftar perintah.");
            $this->line(($ok ? '  terkirim  ' : '  GAGAL     ').$id);
            $gagal += $ok ? 0 : 1;
        }

        return $gagal === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function hapus(TelegramClient $telegram): int
    {
        if (! $telegram->hapusWebhook()) {
            $this->error('Gagal melepas webhook.');

            return self::FAILURE;
        }

        $this->info('Webhook dilepas. Bot tidak lagi menerima perintah.');

        return self::SUCCESS;
    }

    private function salahAksi(): int
    {
        $this->error('Aksi tidak dikenal. Pilih: info | id | pasang | tes | hapus');

        return self::FAILURE;
    }
}
