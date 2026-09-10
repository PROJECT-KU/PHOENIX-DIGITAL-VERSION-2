<?php

use App\Models\Product;
use App\Support\KategoriBeranda;
use App\Support\MerekDipercaya;
use Illuminate\Support\Facades\Cache;

/**
 * Kategori & merek di beranda.
 *
 * Yang dijaga di sini satu hal: HALAMAN TIDAK BOLEH MENJANJIKAN ISI YANG TIDAK
 * ADA. Chip kategori yang membawa pengunjung ke halaman kosong, atau logo merek
 * yang produknya sudah berhenti dijual, adalah kebohongan kecil yang menggerus
 * kepercayaan justru di bagian halaman yang tugasnya membangun kepercayaan.
 */
beforeEach(function () {
    Cache::flush();
});

it('kategori tanpa produk tidak ditampilkan', function () {
    // Katalog hanya berisi satu produk AI; kategori lain tidak punya apa pun.
    Product::query()->delete();
    Product::create(['nama_akun' => 'Chat Gpt Plus Sharing', 'tipe_akun' => 'sharing', 'harga_perbulan' => 35000]);

    $label = collect(KategoriBeranda::tersedia())->pluck('label');

    expect($label)->toContain('AI Tools')
        ->and($label)->not->toContain('Jurnal & Riset')
        ->and($label)->not->toContain('Desain & Kreatif');
});

it('produk yang dijeda tidak ikut dihitung', function () {
    Product::query()->delete();
    Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000, 'dijeda' => true]);

    expect(collect(KategoriBeranda::tersedia())->pluck('label'))->not->toContain('Desain & Kreatif');
});

it('jumlah yang dijanjikan chip sama dengan isi halaman tujuannya', function () {
    Product::query()->delete();

    foreach (['Chat Gpt Plus Sharing', 'Chat Gpt Plus Private', 'Gemini Advance'] as $nama) {
        Product::create(['nama_akun' => $nama, 'tipe_akun' => 'sharing', 'harga_perbulan' => 35000]);
    }

    $ai = collect(KategoriBeranda::tersedia())->firstWhere('kunci', 'ai-tools');

    // Angka pada chip dan hasil penyaring halaman /shop berasal dari satu
    // sumber yang sama; kalau keduanya berpisah, salah satunya pasti berbohong.
    $isiHalaman = KategoriBeranda::saring(Product::query(), KategoriBeranda::kata('ai-tools'))->count();

    expect($ai['jumlah'])->toBe(3)->and($isiHalaman)->toBe(3);
});

it('kunci kategori yang tidak dikenal tidak menyaring apa pun', function () {
    expect(KategoriBeranda::kata('kategori-karangan'))->toBeNull();
});

it('nama merek dipendekkan jadi nama yang dikenali pengunjung', function () {
    expect(MerekDipercaya::merek('Chat Gpt Plus Sharing'))->toBe('ChatGPT')
        ->and(MerekDipercaya::merek('Chat Gpt Plus Private'))->toBe('ChatGPT')
        ->and(MerekDipercaya::merek('Scopus Lisensi + Scopus AI Private'))->toBe('Scopus')
        ->and(MerekDipercaya::merek('QuillBot Premium'))->toBe('QuillBot')
        ->and(MerekDipercaya::merek('Grammarly Premium'))->toBe('Grammarly');
});

it('produk yang namanya hanya imbuhan tidak menghasilkan merek kosong', function () {
    expect(MerekDipercaya::merek('Akun Premium'))->toBe('');
});
