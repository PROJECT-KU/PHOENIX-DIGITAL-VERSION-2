<?php

use App\Console\Commands\HapusBerkasJasaKadaluarsa as Pembersih;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Masa simpan berkas jasa.
 *
 * Aturan lama menghapus naskah pelanggan DAN berkas hasil sekaligus, 7 hari
 * setelah link /cek mati — pada kode yang lebih tua lagi dihitung dari tanggal
 * pelanggan mengunggah. Hasil INV-20260828-0006 karena itu lenyap tiga hari
 * setelah diserahkan, dan admin tidak punya apa pun untuk dikirim ulang.
 *
 * Aturan sekarang: naskah pelanggan dihapus 30 hari setelah pekerjaannya
 * rampung; berkas hasil disimpan tanpa batas waktu.
 */
beforeEach(fn () => Storage::fake('local'));

function unggahanSimpan(array $isian = [], ?int $umurHari = null): OrderUpload
{
    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-SIMPAN-'.Str::upper(Str::random(6)),
        'customer_id' => Customer::create([
            'nama' => 'Pembeli Jasa',
            'no_hp' => '0812'.random_int(10000000, 99999999),
            'email' => 'simpan'.uniqid().'@contoh.test',
        ])->id,
        'subtotal' => 100000,
        'total' => 100000,
        'unique_code' => 0,
        'status' => 'completed',
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    foreach (['masuk/naskah.docx', 'masuk/acuan.pdf', 'hasil/plagiasi.pdf', 'hasil/ai.pdf', 'hasil/parafrase.docx'] as $berkas) {
        Storage::disk('local')->put('order-uploads/'.$order->id.'/'.$berkas, 'isi');
    }

    $up = OrderUpload::create(array_merge([
        'order_id' => $order->id,
        'jenis' => 'parafrase',
        'path' => 'order-uploads/'.$order->id.'/masuk/naskah.docx',
        'pdf_path' => 'order-uploads/'.$order->id.'/masuk/acuan.pdf',
        'nama_asli' => 'naskah.docx',
        'status' => 'selesai',
        'hasil_path' => 'order-uploads/'.$order->id.'/hasil/plagiasi.pdf',
        'hasil_ai_path' => 'order-uploads/'.$order->id.'/hasil/ai.pdf',
        'hasil_docx_path' => 'order-uploads/'.$order->id.'/hasil/parafrase.docx',
        'selesai_at' => now(),
    ], $isian));

    if ($umurHari !== null) {
        $up->forceFill(['created_at' => now()->subDays($umurHari)])->save();
    }

    return $up->fresh();
}

it('berkas HASIL tidak pernah dihapus, setua apa pun pesanannya', function () {
    $up = unggahanSimpan([], 400);

    $this->artisan('jasa:hapus-berkas-kadaluarsa')->assertSuccessful();

    $up->refresh();
    expect($up->hasil_path)->not->toBeNull()
        ->and($up->hasil_ai_path)->not->toBeNull()
        ->and($up->hasil_docx_path)->not->toBeNull();
    Storage::disk('local')->assertExists($up->hasil_path);
    Storage::disk('local')->assertExists($up->hasil_ai_path);
    Storage::disk('local')->assertExists($up->hasil_docx_path);
});

it('naskah pelanggan dihapus setelah 30 hari bila pekerjaannya sudah rampung', function () {
    $up = unggahanSimpan([], Pembersih::HARI_SIMPAN + 1);
    $naskah = $up->path;
    $acuan = $up->pdf_path;

    $this->artisan('jasa:hapus-berkas-kadaluarsa')->assertSuccessful();

    $up->refresh();
    expect($up->path)->toBeNull()->and($up->pdf_path)->toBeNull();
    Storage::disk('local')->assertMissing($naskah);
    Storage::disk('local')->assertMissing($acuan);
});

it('naskah yang belum 30 hari tetap utuh', function () {
    $up = unggahanSimpan([], Pembersih::HARI_SIMPAN - 2);

    $this->artisan('jasa:hapus-berkas-kadaluarsa')->assertSuccessful();

    expect($up->fresh()->path)->not->toBeNull();
    Storage::disk('local')->assertExists($up->path);
});

it('pekerjaan yang MASIH berjalan tidak pernah kehilangan naskahnya', function () {
    // Admin masih membutuhkan naskahnya — berapa pun umur unggahannya.
    foreach (['menunggu', 'diproses'] as $status) {
        $up = unggahanSimpan(['status' => $status, 'selesai_at' => null], 400);

        $this->artisan('jasa:hapus-berkas-kadaluarsa')->assertSuccessful();

        expect($up->fresh()->path)->not->toBeNull();
        Storage::disk('local')->assertExists($up->path);
    }
});

it('unggahan yang dibatalkan ikut dibersihkan', function () {
    $up = unggahanSimpan(['status' => 'dibatalkan'], Pembersih::HARI_SIMPAN + 1);

    $this->artisan('jasa:hapus-berkas-kadaluarsa')->assertSuccessful();

    expect($up->fresh()->path)->toBeNull();
});

it('--dry-run tidak menghapus apa pun', function () {
    $up = unggahanSimpan([], Pembersih::HARI_SIMPAN + 1);

    $this->artisan('jasa:hapus-berkas-kadaluarsa', ['--dry-run' => true])->assertSuccessful();

    expect($up->fresh()->path)->not->toBeNull();
    Storage::disk('local')->assertExists($up->path);
});

it('perintahnya hanya menyentuh kolom naskah pelanggan', function () {
    // Dijaga di SUMBER: satu nama kolom hasil yang tersasar ke daftar ini
    // menghapus laporan yang tidak bisa dibuat ulang, dan tidak ada galat
    // apa pun yang menandainya.
    $sumber = file_get_contents(app_path('Console/Commands/HapusBerkasJasaKadaluarsa.php'));

    expect($sumber)->toContain("BERKAS_PELANGGAN = ['path', 'pdf_path']")
        ->and($sumber)->not->toContain("'hasil_path',")
        ->and($sumber)->not->toContain("'hasil_ai_path',")
        ->and($sumber)->not->toContain("'hasil_docx_path',");
});
