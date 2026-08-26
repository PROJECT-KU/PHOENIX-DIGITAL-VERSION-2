<?php

namespace App\Support;

/**
 * Berapa bukti transfer Orcha yang masih menunggu dicek admin.
 *
 * Dipakai bilah samping dan lonceng untuk menandai menu Bukti Pembayaran.
 * Sebelumnya tidak ada tanda apa pun: bukti yang masuk hanya ketahuan kalau
 * admin kebetulan membuka halamannya, dan pelanggan yang sudah mentransfer
 * menunggu tanpa tahu bahwa buktinya belum dibuka siapa pun.
 */
class OrchaMenungguDicek extends HitunganOrcha
{
    protected static function kunci(): string
    {
        return 'orcha.pembayaran.menunggu';
    }

    protected static function jalur(): string
    {
        return '/pembayaran/menunggu';
    }

    /** @return array<string, int> */
    protected static function bawaan(): array
    {
        return ['jumlah' => 0, 'nominal' => 0];
    }

    public static function jumlah(): int
    {
        return self::ambil()['jumlah'];
    }
}
