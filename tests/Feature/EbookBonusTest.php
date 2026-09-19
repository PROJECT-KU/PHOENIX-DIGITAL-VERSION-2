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
