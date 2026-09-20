<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramClient;
use App\Support\LaporanVps;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Peringatan otomatis ke grup Telegram tentang VPS bot Turnitin.
 *
 * Hanya berbunyi saat KEADAAN BERUBAH — bukan tiap sepuluh menit. Peringatan
 * yang berulang terus akan diabaikan orang, dan saat benar-benar penting ia
 * ikut terlewat.
 */
class PantauVps extends Command
{
    protected $signature = 'bot:pantau-vps {--kering : Tampilkan saja, jangan kirim}';

    protected $description = 'Kirim peringatan Telegram bila VPS bot Turnitin bermasalah';

    private const KUNCI = 'vps_bot_peringatan_terakhir';

    /** Kuota di bawah ini dianggap menipis. */
    private const KUOTA_MENIPIS = 3;

    public function handle(TelegramClient $telegram): int
    {
        $masalah = $this->periksa();
        $sebelumnya = (array) Cache::get(self::KUNCI, []);

        $baru = array_diff_key($masalah, $sebelumnya);
        $pulih = array_diff_key($sebelumnya, $masalah);

        Cache::put(self::KUNCI, $masalah, now()->addDays(7));

        if (empty($baru) && empty($pulih)) {
            $this->info('Tidak ada perubahan keadaan'.($masalah ? ' (masalah lama masih ada: '.implode(', ', array_keys($masalah)).')' : ' — semuanya sehat.'));

            return self::SUCCESS;
        }

        $baris = [];
        foreach ($baru as $pesan) {
            $baris[] = '⚠️ '.$pesan;
        }
        foreach (array_keys($pulih) as $kunci) {
            $baris[] = '✅ Pulih: '.$this->judul($kunci);
        }

        $teks = "🖥️ PEMANTAU VPS BOT\n".str_repeat('─', 24)."\n\n".implode("\n", $baris);
        $this->line($teks);

        if ($this->option('kering')) {
            return self::SUCCESS;
        }

        if (! $telegram->aktif()) {
            $this->warn('Bot Telegram tidak aktif — peringatan tidak terkirim.');

            return self::SUCCESS;
        }

        foreach (config('telegram.chat_ids', []) as $chat) {
            $telegram->kirim($chat, $teks);
        }

        return self::SUCCESS;
    }

    /** @return array<string,string> kunci masalah => kalimatnya */
    private function periksa(): array
    {
        $masalah = [];
        $data = LaporanVps::terakhir();

        if (! $data) {
            // Belum pernah melapor sama sekali: bisa jadi memang belum dipasang,
            // jadi jangan dijadikan alarm.
            return $masalah;
        }

        if (LaporanVps::basi()) {
            $masalah['mati'] = 'VPS bot tidak melapor sejak '.LaporanVps::umurMenit().' menit lalu — kemungkinan mati.';

            // Kalau botnya diam, angka lain di laporan itu sudah tidak berarti.
            return $masalah;
        }

        $kuota = $data['kuota'] ?? null;
        if ($kuota !== null && $kuota <= self::KUOTA_MENIPIS) {
            $masalah['kuota'] = $kuota <= 0
                ? 'Kuota paket submitin HABIS — pengecekan berhenti sampai paket diisi.'
                : 'Kuota paket submitin tinggal '.$kuota.'x.';
        }

        if (($data['disk_persen'] ?? 0) >= 85) {
            $masalah['disk'] = 'Disk VPS terpakai '.$data['disk_persen'].'%.';
        }

        if (($data['gagal_hari_ini'] ?? 0) > 0) {
            $masalah['gagal'] = ($data['gagal_hari_ini']).' pengecekan gagal hari ini. Terakhir: '
                .mb_strimwidth((string) ($data['galat_terakhir'] ?? '-'), 0, 160, '…');
        }

        if (($data['mode'] ?? '') !== 'penuh') {
            $masalah['mode'] = 'Bot tidak dalam mode penuh (sekarang: '.($data['mode'] ?? '?').') — antrean tidak dikerjakan.';
        }

        return $masalah;
    }

    private function judul(string $kunci): string
    {
        return [
            'mati' => 'VPS bot melapor lagi',
            'kuota' => 'kuota submitin sudah aman',
            'disk' => 'disk VPS lega lagi',
            'gagal' => 'tidak ada kegagalan baru',
            'mode' => 'bot kembali ke mode penuh',
        ][$kunci] ?? $kunci;
    }
}
