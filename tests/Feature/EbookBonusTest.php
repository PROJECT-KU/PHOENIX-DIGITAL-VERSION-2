<?php

use App\Livewire\Pages\Admin\Ebook\EbookForm;
use App\Livewire\Pages\Admin\Ebook\EbookList;
use App\Models\Ebook;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Ebook Bonus: rak kartu + ringkasan, saringan status, jendela detail
 * (tautan view-only & pesanan terakhir), form tambah/ubah.
 */
function adminEbook(array $izin = ['view_ebook', 'create_ebook', 'edit_ebook', 'delete_ebook']): \App\Models\User
{
    $peran = \App\Models\Role::create(['name' => 'uji-eb-'.Str::random(5), 'description' => 'uji']);
    foreach ($izin as $nama) {
        $p = \App\Models\Permission::firstOrCreate(['name' => $nama], ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']);
        $peran->permissions()->attach($p->id);
    }

    return \App\Models\User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);
}

it('daftar menampilkan ringkasan dan menyaring menurut status', function () {
    $this->actingAs(adminEbook());
    Ebook::create(['judul' => 'Panduan Aktif', 'status' => 'active', 'file' => 'a.pdf']);
    Ebook::create(['judul' => 'Panduan Arsip', 'status' => 'non-active', 'file' => 'b.pdf']);

    $t = Livewire::test(EbookList::class);
    expect($t->viewData('ringkas'))->toMatchArray(['total' => 2, 'aktif' => 1, 'nonaktif' => 1]);
    $t->assertSee('Panduan Aktif')->assertSee('Panduan Arsip');

    $t->set('statusFilter', 'non-active')->assertSee('Panduan Arsip')->assertDontSee('Panduan Aktif');
    Livewire::withQueryParams(['cari' => 'Aktif'])->test(EbookList::class)->assertSet('search', 'Aktif');
});

it('jendela detail memuat tautan view-only dan pesanan yang menerimanya', function () {
    $this->actingAs(adminEbook());
    $ebook = Ebook::create(['judul' => 'Panduan Scopus', 'status' => 'active', 'file' => 'x.pdf']);
    $order = \App\Models\Order::create([
        'id' => Str::uuid(), 'order_number' => 'INV-EBOOK-1', 'subtotal' => 1, 'total' => 1, 'unique_code' => 0,
        'status' => 'completed', 'payment_method' => 'transfer', 'expired_at' => now(),
        'customer_id' => \App\Models\Customer::create(['nama' => 'Rina', 'no_hp' => '081200000001'])->id,
    ]);
    $item = \App\Models\OrderItem::create(['order_id' => $order->id, 'product_id' => \App\Models\Product::create(['nama_akun' => 'Scopus'])->id, 'product_name' => 'Scopus', 'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 1, 'quantity' => 1, 'subtotal' => 1]);
    $item->ebooks()->attach($ebook->id);

    Livewire::test(EbookList::class)
        ->call('lihat', $ebook->id)
        ->assertSee($ebook->getViewUrl())
        ->assertSee('INV-EBOOK-1')
        ->assertSee('Rina')
        ->call('tutupLihat')
        ->assertDontSee('INV-EBOOK-1');
});

it('hapus ebook ditolak tanpa izin, dan menghapus berkasnya bila diizinkan', function () {
    Storage::fake('local');
    Storage::disk('local')->put('ebooks/hapus.pdf', 'isi');
    $ebook = Ebook::create(['judul' => 'Akan Dihapus', 'status' => 'active', 'file' => 'hapus.pdf']);

    $this->actingAs(adminEbook(['view_ebook']));
    Livewire::test(EbookList::class)->call('deleteEbook', $ebook->id)->assertDispatched('Ebook-deleteError');
    expect(Ebook::find($ebook->id))->not->toBeNull();

    $this->actingAs(adminEbook());
    Livewire::test(EbookList::class)->call('deleteEbook', $ebook->id)->assertDispatched('Ebook-deleted');
    expect(Ebook::find($ebook->id))->toBeNull();
    Storage::disk('local')->assertMissing('ebooks/hapus.pdf');
});

it('form tambah: PDF wajib, lalu tersimpan di disk privat', function () {
    Storage::fake('local');
    $this->actingAs(adminEbook());

    Livewire::test(EbookForm::class)
        ->set('judul', 'Panduan Baru')
        ->call('save')
        ->assertHasErrors('file');

    Livewire::test(EbookForm::class)
        ->set('judul', 'Panduan Baru')
        ->set('status', 'non-active')
        ->set('file', UploadedFile::fake()->create('panduan.pdf', 120, 'application/pdf'))
        ->assertSee('siap disimpan')
        ->call('save')
        ->assertRedirect(route('admin.ebook.index'));

    $ebook = Ebook::where('judul', 'Panduan Baru')->first();
    expect($ebook->status)->toBe('non-active');
    Storage::disk('local')->assertExists('ebooks/'.$ebook->file);
});

it('halaman tambah & ubah memakai kerangka dasbor', function () {
    $this->actingAs(adminEbook());
    $ebook = Ebook::create(['judul' => 'Panduan Ubah', 'status' => 'active', 'file' => 'tidak-ada.pdf']);

    Livewire::test(\App\Livewire\Pages\Admin\Ebook\EbookCreate::class)
        ->assertSee('Tambah Ebook')->assertSeeHtml('class="dsb-hero"');
    Livewire::test(\App\Livewire\Pages\Admin\Ebook\EbookEdit::class, ['ebook' => $ebook])
        ->assertSee('Ubah Ebook')->assertSee('Berkas tidak ditemukan di server');
});

it('produk bawaan disimpan dari form ebook; satu produk hanya punya satu ebook bawaan', function () {
    Storage::fake('local');
    $this->actingAs(adminEbook());
    $gpt = \App\Models\Product::create(['nama_akun' => 'Chat Gpt Plus']);
    $lama = Ebook::create(['judul' => 'Panduan Lama', 'status' => 'active', 'file' => 'a.pdf']);
    $gpt->update(['ebook_bawaan_id' => $lama->id]);
    $baru = Ebook::create(['judul' => 'Panduan Chat GPT', 'status' => 'active', 'file' => 'b.pdf']);

    Livewire::test(EbookForm::class, ['ebook' => $baru])
        ->assertSee('Bawaan saat ini: Panduan Lama')
        ->set('produkBawaan', [(string) $gpt->id])
        ->assertSee('Pindah dari: Panduan Lama')
        ->call('save');

    expect($gpt->fresh()->ebook_bawaan_id)->toBe($baru->id);

    Livewire::test(EbookForm::class, ['ebook' => $baru->fresh()])->set('produkBawaan', [])->call('save');
    expect($gpt->fresh()->ebook_bawaan_id)->toBeNull();
});

it('form ubah menyarankan produk yang sering menerima ebook ini', function () {
    $this->actingAs(adminEbook());
    $ebook = Ebook::create(['judul' => 'Panduan Grammarly', 'status' => 'active', 'file' => 'g.pdf']);
    $produk = \App\Models\Product::create(['nama_akun' => 'Grammarly Premium']);
    $order = \App\Models\Order::create([
        'id' => Str::uuid(), 'order_number' => 'INV-SARAN', 'subtotal' => 1, 'total' => 1, 'unique_code' => 0,
        'status' => 'completed', 'payment_method' => 'transfer', 'expired_at' => now(),
        'customer_id' => \App\Models\Customer::create(['nama' => 'A', 'no_hp' => '081200000009'])->id,
    ]);
    foreach (range(1, 3) as $i) {
        \App\Models\OrderItem::create(['order_id' => $order->id, 'product_id' => $produk->id, 'product_name' => 'Grammarly', 'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 1, 'quantity' => 1, 'subtotal' => 1])
            ->ebooks()->attach($ebook->id);
    }

    Livewire::test(EbookForm::class, ['ebook' => $ebook])
        ->assertSee('Sering dikirim bersama')
        ->assertSee('3×')
        ->call('tambahProdukBawaan', (string) $produk->id)
        ->assertSet('produkBawaan', [(string) $produk->id])
        ->assertDontSee('Sering dikirim bersama');
});

it('proses pesanan: ebook bawaan tercentang otomatis hanya untuk item yang belum diproses', function () {
    $this->actingAs(adminEbook(['view_ebook', 'edit_pemesanantoko']));
    $ebook = Ebook::create(['judul' => 'Panduan DeepL', 'status' => 'active', 'file' => 'd.pdf']);
    $produk = \App\Models\Product::create(['nama_akun' => 'DeepL Premium', 'ebook_bawaan_id' => $ebook->id]);
    $order = \App\Models\Order::create([
        'id' => Str::uuid(), 'order_number' => 'INV-BAWAAN', 'subtotal' => 1, 'total' => 1, 'unique_code' => 0,
        'status' => 'paid', 'payment_method' => 'transfer', 'expired_at' => now(),
        'customer_id' => \App\Models\Customer::create(['nama' => 'B', 'no_hp' => '081200000010'])->id,
    ]);
    $baru = \App\Models\OrderItem::create(['order_id' => $order->id, 'product_id' => $produk->id, 'product_name' => 'DeepL', 'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 1, 'quantity' => 1, 'subtotal' => 1]);
    $sudah = \App\Models\OrderItem::create(['order_id' => $order->id, 'product_id' => $produk->id, 'product_name' => 'DeepL', 'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 1, 'quantity' => 1, 'subtotal' => 1, 'delivery_status' => 'delivered', 'processed_at' => now()]);

    Livewire::test(\App\Livewire\Pages\Admin\Order\ProcessOrder::class, ['id' => $baru->id])
        ->assertSet('selectedEbooks', [$ebook->id])
        ->assertSee('Bawaan produk')
        ->assertSee('sudah tercentang otomatis');

    Livewire::test(\App\Livewire\Pages\Admin\Order\ProcessOrder::class, ['id' => $sudah->id])
        ->assertSet('selectedEbooks', [])
        ->assertDontSee('sudah tercentang otomatis');
});

it('halaman baca: dibuka tercatat; nonaktif/tautan lama menampilkan halaman tidak tersedia', function () {
    $ebook = Ebook::create(['judul' => 'Panduan Scite', 'status' => 'active', 'file' => 's.pdf']);

    $this->get('/e/'.$ebook->share_token)->assertOk()->assertSee('Panduan Scite');
    $this->get('/e/'.$ebook->share_token)->assertOk();
    expect($ebook->fresh()->dibuka_count)->toBe(2)->and($ebook->fresh()->terakhir_dibuka_at)->not->toBeNull();

    $tokenLama = $ebook->share_token;
    $this->actingAs(adminEbook());
    Livewire::test(EbookList::class)->call('buatTautanBaru', $ebook->id);
    $this->get('/e/'.$tokenLama)->assertNotFound()->assertSee('Ebook ini sudah tidak tersedia');
    $this->get('/e/'.$ebook->fresh()->share_token)->assertOk();

    $ebook->update(['status' => 'non-active']);
    $this->get('/e/'.$ebook->fresh()->share_token)->assertNotFound()->assertSee('Hubungi kami');
});

it('batas unggah PDF kini 10 MB', function () {
    Storage::fake('local');
    $this->actingAs(adminEbook());

    Livewire::test(EbookForm::class)->set('judul', 'Panduan Besar')
        ->set('file', UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf'))
        ->call('save')->assertHasNoErrors();

    Livewire::test(EbookForm::class)->set('judul', 'Panduan Raksasa')
        ->set('file', UploadedFile::fake()->create('raksasa.pdf', 11000, 'application/pdf'))
        ->call('save')->assertHasErrors('file');
});

it('halaman baca memakai logo Phoenix dan membedakan berkas yang hilang', function () {
    $ebook = Ebook::create(['judul' => 'Panduan Logo', 'status' => 'active', 'file' => 'l.pdf']);

    $this->get('/e/'.$ebook->share_token)
        ->assertOk()
        ->assertSee('icons/phoenix-192.png', false)
        ->assertDontSee('favicon.png', false)
        ->assertSee('MissingPDFException', false);
});

it('saran ebook bawaan: dari riwayat, pratinjau dulu, hanya pasangan sah yang diterapkan', function () {
    $this->actingAs(adminEbook());
    $pGpt = \App\Models\Product::create(['nama_akun' => 'Chat Gpt Plus']);
    $pGram = \App\Models\Product::create(['nama_akun' => 'Grammarly Premium']);
    $pJarang = \App\Models\Product::create(['nama_akun' => 'Jarang']);
    $eGpt = Ebook::create(['judul' => 'Panduan Chat GPT', 'status' => 'active', 'file' => 'a.pdf']);
    $eGram = Ebook::create(['judul' => 'Panduan Grammarly', 'status' => 'active', 'file' => 'b.pdf']);
    $eLain = Ebook::create(['judul' => 'Panduan Lain', 'status' => 'active', 'file' => 'c.pdf']);
    $pGram->update(['ebook_bawaan_id' => $eLain->id]);

    $order = \App\Models\Order::create([
        'id' => Str::uuid(), 'order_number' => 'INV-SARAN-2', 'subtotal' => 1, 'total' => 1, 'unique_code' => 0,
        'status' => 'completed', 'payment_method' => 'transfer', 'expired_at' => now(),
        'customer_id' => \App\Models\Customer::create(['nama' => 'C', 'no_hp' => '081200000011'])->id,
    ]);
    $kirim = function ($produk, $ebook, $kali) use ($order) {
        foreach (range(1, $kali) as $i) {
            \App\Models\OrderItem::create(['order_id' => $order->id, 'product_id' => $produk->id, 'product_name' => $produk->nama_akun, 'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 1, 'quantity' => 1, 'subtotal' => 1])->ebooks()->attach($ebook->id);
        }
    };
    $kirim($pGpt, $eGpt, 4);
    $kirim($pGram, $eGram, 3);
    $kirim($pJarang, $eGpt, 2); // di bawah ambang

    $t = Livewire::test(EbookList::class)->assertSee('Saran bawaan')->call('bukaSaran')
        ->assertSee('Chat Gpt Plus')->assertSee('Bawaan saat ini: Panduan Lain')->assertDontSee('Jarang');
    // Yang belum punya bawaan tercentang; yang sudah punya (berbeda) tidak.
    expect($t->get('saranPilih'))->toBe([(string) $pGpt->id => (string) $eGpt->id]);

    // Pasangan karangan dari peramban diabaikan.
    $t->set('saranPilih', [(string) $pGpt->id => (string) $eGpt->id, (string) $pJarang->id => (string) $eGpt->id])
        ->call('terapkanSaran');

    expect($pGpt->fresh()->ebook_bawaan_id)->toBe($eGpt->id)
        ->and($pGram->fresh()->ebook_bawaan_id)->toBe($eLain->id)
        ->and($pJarang->fresh()->ebook_bawaan_id)->toBeNull();
});

it('perintah pembersih hanya menghapus PDF yatim yang cukup tua', function () {
    Storage::fake('local');
    $disk = Storage::disk('local');
    Ebook::create(['judul' => 'Dipakai', 'status' => 'active', 'file' => 'dipakai.pdf']);
    $sampah = Ebook::create(['judul' => 'Di sampah', 'status' => 'active', 'file' => 'sampah.pdf']);
    $sampah->delete();
    foreach (['dipakai.pdf', 'sampah.pdf', 'yatim.pdf', 'baru.pdf'] as $f) {
        $disk->put('ebooks/'.$f, 'x');
    }
    touch($disk->path('ebooks/yatim.pdf'), now()->subDay()->getTimestamp());
    touch($disk->path('ebooks/dipakai.pdf'), now()->subDay()->getTimestamp());
    touch($disk->path('ebooks/sampah.pdf'), now()->subDay()->getTimestamp());

    $this->artisan('ebook:bersihkan-berkas', ['--kering' => true])->assertSuccessful();
    $disk->assertExists('ebooks/yatim.pdf');

    $this->artisan('ebook:bersihkan-berkas')->assertSuccessful();
    $disk->assertMissing('ebooks/yatim.pdf');
    $disk->assertExists('ebooks/dipakai.pdf');
    $disk->assertExists('ebooks/sampah.pdf');
    $disk->assertExists('ebooks/baru.pdf'); // lebih muda dari 60 menit
});
