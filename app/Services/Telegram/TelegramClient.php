<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pembungkus tipis Bot API Telegram.
 *
 * Sengaja pakai Http facade (Guzzle bawaan Laravel), BUKAN paket composer:
 * /vendor di-gitignore dan deploy tidak menjalankan `composer install`, jadi
 * dependensi baru tidak akan sampai ke server. Lihat DEPLOYMENT.md.
 */
class TelegramClient
{
    private const BASIS = 'https://api.telegram.org/bot';

    /** Batas panjang satu pesan Telegram (4096); disisakan ruang untuk penanda potong. */
    private const BATAS_PESAN = 3900;

    public function __construct(private ?string $token = null)
    {
        $this->token = $token ?: config('telegram.token');
    }

    public function aktif(): bool
    {
        return ! empty($this->token);
    }

    /**
     * Kirim pesan. Otomatis dipecah bila melebihi batas Telegram.
     *
     * Pakai teks polos (tanpa parse_mode) supaya isi log/error yang mengandung
     * karakter Markdown tidak membuat Telegram menolak pesan.
     */
    public function kirim(int|string $chatId, string $teks): bool
    {
        if (! $this->aktif()) {
            return false;
        }

        $sukses = true;

        foreach ($this->pecah($teks) as $bagian) {
            $sukses = $this->panggil('sendMessage', [
                'chat_id' => $chatId,
                'text' => $bagian,
                'disable_web_page_preview' => true,
            ]) !== null && $sukses;
        }

        return $sukses;
    }

    /** Daftarkan webhook + secret. */
    public function pasangWebhook(string $url, string $secret): ?array
    {
        return $this->panggil('setWebhook', [
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => ['message'],
            'drop_pending_updates' => true,
        ]);
    }

    public function hapusWebhook(): ?array
    {
        return $this->panggil('deleteWebhook', ['drop_pending_updates' => true]);
    }

    public function infoWebhook(): ?array
    {
        return $this->panggil('getWebhookInfo');
    }

    public function saya(): ?array
    {
        return $this->panggil('getMe');
    }

    /**
     * Daftarkan menu perintah — yang muncul saat pengguna mengetik "/".
     *
     * @param  array<int,array{command:string,description:string}>  $perintah
     */
    public function pasangMenu(array $perintah): ?array
    {
        return $this->panggil('setMyCommands', ['commands' => $perintah]);
    }

    /**
     * Menu perintah yang sedang terpasang.
     *
     * Telegram mengembalikan array (bisa kosong), sedangkan panggil()
     * membungkus non-array jadi ['ok' => true]; karena itu hasilnya
     * dinormalkan di sini supaya pemanggil cukup membandingkan isi.
     *
     * @return array<int,array{command:string,description:string}>
     */
    public function menuSekarang(): array
    {
        $hasil = $this->panggil('getMyCommands');

        if (! is_array($hasil)) {
            return [];
        }

        return array_values(array_filter($hasil, 'is_array'));
    }

    /** Ambil update tertunda (dipakai `telegram:webhook id` untuk mencari chat ID). */
    public function ambilUpdate(int $timeout = 25): ?array
    {
        return $this->panggil('getUpdates', [
            'timeout' => $timeout,
            'allowed_updates' => ['message'],
        ], $timeout + 10);
    }

    /**
     * @return array<string,mixed>|null Isi `result`, atau null bila gagal.
     */
    private function panggil(string $metode, array $data = [], int $timeout = 15): ?array
    {
        if (! $this->aktif()) {
            return null;
        }

        try {
            $respons = Http::timeout($timeout)
                ->asJson()
                ->post(self::BASIS.$this->token.'/'.$metode, $data);

            $isi = $respons->json();

            if (! $respons->successful() || ! ($isi['ok'] ?? false)) {
                // Token TIDAK ikut dicatat — hanya nama metode & keterangan Telegram.
                Log::warning('Telegram '.$metode.' gagal', [
                    'status' => $respons->status(),
                    'keterangan' => $isi['description'] ?? null,
                ]);

                return null;
            }

            return is_array($isi['result'] ?? null) ? $isi['result'] : ['ok' => true];
        } catch (\Throwable $e) {
            Log::warning('Telegram '.$metode.' error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Pecah teks panjang di batas baris supaya tetap enak dibaca.
     *
     * @return array<int,string>
     */
    private function pecah(string $teks): array
    {
        $teks = trim($teks) !== '' ? $teks : '(kosong)';

        if (mb_strlen($teks) <= self::BATAS_PESAN) {
            return [$teks];
        }

        $bagian = [];
        $sekarang = '';

        foreach (explode("\n", $teks) as $baris) {
            // Baris tunggal yang kelewat panjang dipotong paksa.
            while (mb_strlen($baris) > self::BATAS_PESAN) {
                if ($sekarang !== '') {
                    $bagian[] = $sekarang;
                    $sekarang = '';
                }
                $bagian[] = mb_substr($baris, 0, self::BATAS_PESAN);
                $baris = mb_substr($baris, self::BATAS_PESAN);
            }

            if (mb_strlen($sekarang) + mb_strlen($baris) + 1 > self::BATAS_PESAN) {
                $bagian[] = $sekarang;
                $sekarang = $baris;
            } else {
                $sekarang = $sekarang === '' ? $baris : $sekarang."\n".$baris;
            }
        }

        if ($sekarang !== '') {
            $bagian[] = $sekarang;
        }

        return $bagian;
    }
}
