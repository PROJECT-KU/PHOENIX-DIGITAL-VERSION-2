<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lapisan AI OPSIONAL: menerjemahkan pesan bahasa bebas menjadi SATU kunci aksi
 * dari daftar putih AksiAgen.
 *
 * Batas kewenangan (penting):
 * AI TIDAK PERNAH mengeksekusi apa pun. Keluarannya cuma sebuah string kunci,
 * lalu AksiAgen::jalankan() memvalidasi ulang kunci itu terhadap daftar putih.
 * Kunci di luar daftar => ditolak. Jadi model yang halusinasi, atau balasan
 * yang disusupi, tetap tidak bisa menambah kemampuan bot.
 *
 * Privasi:
 * Yang dikirim keluar HANYA teks perintah Anda + nama & keterangan aksi
 * (lihat promptSistem()). Tidak ada data customer, isi pesanan, kredensial,
 * atau hasil eksekusi yang dikirim ke layanan AI.
 *
 * Mati total bila TELEGRAM_AI_* di .env kosong — bot tetap jalan lewat
 * perintah slash tanpa biaya.
 */
class AgenAi
{
    public static function aktif(): bool
    {
        $ai = config('telegram.ai');

        return ! empty($ai['api_key']) && ! empty(self::basisUrl());
    }

    /**
     * Terjemahkan teks bebas menjadi kunci aksi.
     *
     * @return string|null Kunci aksi yang SUDAH divalidasi, atau null bila
     *                     tidak cocok / AI tidak aktif / gagal.
     */
    public static function pilihAksi(string $teks): ?string
    {
        if (! self::aktif()) {
            return null;
        }

        try {
            $jawaban = config('telegram.ai.driver') === 'anthropic'
                ? self::lewatAnthropic($teks)
                : self::lewatOpenAi($teks);
        } catch (\Throwable $e) {
            Log::warning('AgenAi gagal: '.$e->getMessage());

            return null;
        }

        if ($jawaban === null) {
            return null;
        }

        $aksi = self::ambilAksiDariJson($jawaban);

        // Validasi ulang terhadap daftar putih — pertahanan terakhir.
        return ($aksi !== null && AksiAgen::ada($aksi)) ? $aksi : null;
    }

    // ------------------------------------------------------------- prompt --

    /**
     * Prompt sistem. Sengaja hanya memuat NAMA + KETERANGAN aksi.
     * Jangan pernah menambahkan data operasional/pelanggan ke sini.
     */
    private static function promptSistem(): string
    {
        $daftar = [];

        foreach (AksiAgen::daftar() as $kunci => $info) {
            $daftar[] = '- '.$kunci.': '.$info['jelas'];
        }

        return "Kamu router perintah untuk bot operasional server sebuah toko online.\n"
            ."Tugasmu: petakan pesan admin (Bahasa Indonesia) ke SATU aksi dari daftar berikut.\n\n"
            .implode("\n", $daftar)."\n\n"
            ."Jawab HANYA dengan JSON satu baris, tanpa penjelasan dan tanpa blok kode:\n"
            .'{"aksi":"<kunci_dari_daftar>"}'."\n\n"
            ."Bila tidak ada yang cocok, jawab: {\"aksi\":null}\n"
            .'Jangan mengarang kunci di luar daftar.';
    }

    // ------------------------------------------------------------ driver ---

    /** Endpoint OpenAI-compatible: 9Router, OpenRouter, dll. */
    private static function lewatOpenAi(string $teks): ?string
    {
        $ai = config('telegram.ai');

        $respons = Http::timeout((int) ($ai['timeout'] ?: 20))
            ->withToken((string) $ai['api_key'])
            ->asJson()
            ->post(rtrim(self::basisUrl(), '/').'/chat/completions', [
                'model' => $ai['model'] ?: 'gpt-4o-mini',
                'max_tokens' => 200,
                'messages' => [
                    ['role' => 'system', 'content' => self::promptSistem()],
                    ['role' => 'user', 'content' => $teks],
                ],
            ]);

        if (! $respons->successful()) {
            Log::warning('AgenAi (openai) HTTP '.$respons->status().': '.mb_substr($respons->body(), 0, 300));

            return null;
        }

        return $respons->json('choices.0.message.content');
    }

    /**
     * API Anthropic langsung.
     *
     * Catatan model terbaru: pada Claude Opus 5 thinking menyala secara bawaan
     * dan `max_tokens` membatasi thinking + teks sekaligus — karena itu
     * max_tokens dilonggarkan dan effort disetel 'low' (tugas routing ini
     * ringan). Blok teks diambil dengan menyaring type === 'text', bukan
     * content[0], sebab elemen pertama bisa berupa blok thinking.
     */
    private static function lewatAnthropic(string $teks): ?string
    {
        $ai = config('telegram.ai');

        $respons = Http::timeout((int) ($ai['timeout'] ?: 20))
            ->withHeaders([
                'x-api-key' => (string) $ai['api_key'],
                'anthropic-version' => '2023-06-01',
            ])
            ->asJson()
            ->post(rtrim(self::basisUrl(), '/').'/messages', [
                'model' => $ai['model'] ?: 'claude-opus-5',
                'max_tokens' => 1024,
                'output_config' => ['effort' => 'low'],
                'system' => self::promptSistem(),
                'messages' => [
                    ['role' => 'user', 'content' => $teks],
                ],
            ]);

        if (! $respons->successful()) {
            Log::warning('AgenAi (anthropic) HTTP '.$respons->status().': '.mb_substr($respons->body(), 0, 300));

            return null;
        }

        if ($respons->json('stop_reason') === 'refusal') {
            return null;
        }

        foreach ((array) $respons->json('content', []) as $blok) {
            if (($blok['type'] ?? null) === 'text' && ! empty($blok['text'])) {
                return $blok['text'];
            }
        }

        return null;
    }

    private static function basisUrl(): string
    {
        $basis = (string) config('telegram.ai.base_url');

        if ($basis !== '') {
            return $basis;
        }

        // Anthropic punya alamat baku; endpoint OpenAI-compatible harus diisi
        // sendiri karena bisa di mana saja (9Router lokal, VPS, dll).
        return config('telegram.ai.driver') === 'anthropic'
            ? 'https://api.anthropic.com/v1'
            : '';
    }

    // -------------------------------------------------------------- parse --

    /**
     * Ambil field "aksi" dari balasan model.
     *
     * Dibuat tahan banting: sebagian model membungkus JSON dalam ```json,
     * menambah prosa, atau membocorkan tag internal. Jadi JSON dicari dari
     * kurung kurawal pertama sampai terakhir, bukan json_decode mentah.
     */
    private static function ambilAksiDariJson(string $mentah): ?string
    {
        $awal = strpos($mentah, '{');
        $akhir = strrpos($mentah, '}');

        if ($awal === false || $akhir === false || $akhir <= $awal) {
            return null;
        }

        $data = json_decode(substr($mentah, $awal, $akhir - $awal + 1), true);

        if (! is_array($data)) {
            return null;
        }

        $aksi = $data['aksi'] ?? null;

        return is_string($aksi) && $aksi !== '' ? $aksi : null;
    }
}
